<?php

/**
 * This class implements JSON-RPC JsonRpcResponse object.
 *
 * Usage examples:
 *	Build a normal JsonRpcResponse:
 *		$rpcResponse = new JsonRpcResponse($resultData);
 *		$httpObject->setContent($rpcResponse->toJson());
 *
 *	Build a JsonRpcResponse with RpcError:
 *		$rpcResponse = new JsonRpcResponse(new JsonRpcError('Message', -104));
 *		$httpObject->setContent($rpcResponse->toJson());
 *
 *	Getting the response data in the client.
 *		$rpcResponse = JsonRpcResponse::createFromJson($requestResult);
 */
class JsonRpcResponse {

	const VERSION = '2.0';

	protected $id = 0;

	/**
	 * __construct 
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param mixed $result e.g: can be RpcError or result string
	 * @param int $id 
	 * @access public
	 * @return void
	 */
	public function __construct($result, $id = NULL) {
		if ($result instanceof JsonRpcError) {
			$this->error = $result->toArray();
		} else {
			$this->result = $result;
		}

		if ($id !== NULL) {
			$this->id = $id;
		}
	}

	/**
	 * Creates a validated JsonRpcResponse object from json-rpc input data.
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param array $data 
	 * @static
	 * @access public
	 * @return JsonRpcResponse
	 * @throws JsonRpcResponseMissingField
	 */
	protected static function createFromArray(array $data) {
		self::validateFields($data);
		if (isset($data['error'])) {
			$rpcError = JsonRpcError::createFromArray($data['error']);
			$rpcResponse = new JsonRpcResponse($rpcError, $data['id']);
		} else {
			$rpcResponse = new JsonRpcResponse($data['result'], $data['id']);
		}
		return $rpcResponse;
	}

	/**
	 * Creates a validated JsonRpcResponse object from json-rpc input data.
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param string $jsonJsonRpcResponseString 
	 * @static
	 * @access public
	 * @return JsonRpcResponse
	 * @throws JsonRpcResponseMissingField
	 * @throws InvalidJsonRpcResponse
	 */
	public static function createFromJson($jsonRpcResponseString) {
		$arrayResponse = json_decode($jsonRpcResponseString, TRUE);
		if ($arrayResponse === NULL) {
			throw new InvalidJsonRpcResponse($jsonRpcResponseString);
		}
		return self::createFromArray($arrayResponse);
	}

	/**
	 * Makes sure input data has the fields expected for a JsonRpcResponse object according to the standard protocol.
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param array $jsonRpcRequestData
	 * @return void
	 * @throws JsonRpcResponseMissingField
	 */
	protected static function validateFields(array $jsonRpcRequestData) {
		$requiredJsonRpcFields = array(
				'jsonrpc', 'id'
				);
		foreach($requiredJsonRpcFields as $requiredJsonRpcField) {
			if (!isset($jsonRpcRequestData[$requiredJsonRpcField])) {
				throw new JsonRpcResponseMissingField($requiredJsonRpcField);
			}
		}
		if (!isset($jsonRpcRequestData['result']) && !isset($jsonRpcRequestData['error'])) {
			throw new JsonRpcResponseMissingField('result or error');
		}
	}

	/**
	 * Returns a json-rpc Response Array
	 *
	 * See http://www.jsonrpc.org/specification , "5 Response object" chapter.
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @return array $responseData
	 */
	protected function toArray() {
		$responseData = array();
		// * jsonrpc
		// A String specifying the version of the JSON-RPC protocol. MUST be exactly "2.0".
		$responseData['jsonrpc'] = self::VERSION;
		// * id
		// This member is REQUIRED.
		// It MUST be the same as the value of the id member in the Request Object.
		// If there was an error in detecting the id in the Request object (e.g. Parse error/Invalid Request), it MUST be Null.
		$responseData['id'] = $this->id;
		// * result
		// This member is REQUIRED on success.
		// This member MUST NOT exist if there was an error invoking the method.
		// The value of this member is determined by the method invoked on the Server.
		// * error
		// This member is REQUIRED on error.
		// This member MUST NOT exist if there was no error triggered during invocation.
		// The value for this member MUST be an Object as defined in section 5.1.
		if ($this->hasError()) {
			$responseData['error'] = $this->error;
		} else {
			$responseData['result'] = $this->result;
		}
		return $responseData;
	}

	/**
	 * Returns a json-rpc response json string
	 * 
	 * See http://www.jsonrpc.org/specification , "5 Response object" chapter.
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @access public
	 * @return string $jsonResponse
	 */
	public function toJson() {
		return json_encode($this->toArray(), TRUE);
	}

	/**
	 * Gets the result member
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @access public
	 * @return mixed
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * Gets the error member
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @access public
	 * @return mixed
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * Returns true if error member exists.
	 * According to json-rpc standard, this means that there was an error and result member won't be set.
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @access public
	 * @return void
	 */
	public function hasError() {
		return isset($this->error);
	}


	/**
	 * Gets the id member
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @access public
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

}

/**
 * JsonRpcResponseMissingField
 * 
 * @uses Exception
 * @package general
 * @version $id$
 */
class JsonRpcResponseMissingField extends Exception {
	const CODE = JsonRpcError::ERROR_CODE_INVALID_REQUEST;
	public function __construct($message) {
		$this->message = $message . ' missing field';
		$this->code = self::CODE;
	}
}


/**
 * InvalidJsonRpcResponse 
 * 
 * @uses Exception
 * @package general
 * @version $id$
 */
class InvalidJsonRpcResponse extends Exception {
	const CODE = JsonRpcError::ERROR_CODE_INVALID_REQUEST;
	public function __construct($message) {
		$this->message = 'Invalid Json Rpc response : <' . $message . '>';
		$this->code = self::CODE;
	}
}
