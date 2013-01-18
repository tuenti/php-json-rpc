<?php
/**
 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
 *
 * @package lib
 * @subpackage json-rpc
 *
 * Usage example:
 *
 * $request = json_decode(file_get_contents('php://input'));
 * try {
 *	$rpcRequest = JsonRpcRequest::createFromJson($request);
 * } catch (JsonRpcRequestMissingField $e) {
 *		// Do something
 * }
 *
 */

/**
 * JsonRpcRequest
 *
 * See http://www.jsonrpc.org/specification , "4 Request object" chapter.
 *
 * This is a partial implementation of JsonRpcRequest. It lacks notifications.
 */
class JsonRpcRequest {
	const VERSION = '2.0';
	const JSONRPC_PART_KEY = 'jsonrpc';
	const METHOD_PART_KEY = 'method';
	const PARAMS_PART_KEY = 'params';
	const ID_PART_KEY = 'id';

	protected $method;
	protected $params;
	private static $id = 0;

	/**
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param array $jsonRpcRequestData
	 */
	public function __construct($method, array $params, $id = NULL) {
		$this->method = $method;
		$this->params = $params;
		if ($id !== NULL) {
			self::$id = $id;
		}
	}

	/**
	 * Creates a validated JsonRpcRequest object from json-rpc input data.
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param array $data 
	 * @static
	 * @access public
	 * @return JsonRpcRequest
	 * @throws JsonRpcRequestMissingField
	 */
	protected static function createFromArray(array $data) {
		static::validateFields($data);
		$rpcRequest = new JsonRpcRequest($data['method'], $data['params'], $data['id']);
		return $rpcRequest;
	}

	/**
	 * Creates a validated JsonRpcRequest object from json-rpc json input data.
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param mixed $jsonRpcRequestString 
	 * @static
	 * @access public
	 * @return JsonRpcRequest
	 * @throws InvalidJsonRpcRequest
	 * @throws JsonRpcRequestMissingField
	 */
	public static function createFromJson($jsonRpcRequestString) {
		$arrayRequest = json_decode($jsonRpcRequestString, TRUE);
		if ($arrayRequest === NULL) {
			throw new InvalidJsonRpcRequest($jsonRpcRequestString);
		}
		return static::createFromArray($arrayRequest);
	}

	/**
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @return string $method
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @return array $params
	 */
	public function getParams() {
		return $this->params;
	}


	/**
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param $method
	 */
	public function setMethod($method) {
		$this->method = $method;
	}

	/**
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param $params
	 */
	public function setParams($params) {
		$this->params = $params;
	}

	/**
	 * Creates a validated RpcResponse object from json-rpc input data.
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param array $jsonRpcRequestData
	 * @return void
	 * @throws JsonRpcRequestMissingField
	 */
	protected static function validateFields(array $jsonRpcRequestData) {
		$requiredJsonRpcFields = array(
			self::JSONRPC_PART_KEY, self::ID_PART_KEY, self::METHOD_PART_KEY
		);
		foreach($requiredJsonRpcFields as $requiredJsonRpcField) {
			if (!isset($jsonRpcRequestData[$requiredJsonRpcField])) {
				throw new JsonRpcRequestMissingField($requiredJsonRpcField);
			}
		}
	}

	/**
	 * Returns a json-rpc Response array
	 *
	 * See http://www.jsonrpc.org/specification , "4 Request object" chapter.
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @return array $array
	 *
	 */
	public function toArray() {
		$requestArray = array();
		// * jsonrpc
		// A String specifying the version of the JSON-RPC protocol. MUST be exactly "2.0".
		$requestArray[self::JSONRPC_PART_KEY] = self::VERSION;
		// * id
		// An identifier established by the Client that MUST contain a String, Number, or NULL
		// value if included. If it is not included it is assumed to be a notification. The value SHOULD normally not
		// be Null [1] and Numbers SHOULD NOT contain fractional parts [2]
		$requestArray[self::ID_PART_KEY] = self::$id++;
		// * method
		// A String containing the name of the method to be invoked. Method names that begin with
		// the word rpc followed by a period character (U+002E or ASCII 46) are reserved for rpc-internal
		// methods and extensions and MUST NOT be used for anything else.
		$requestArray[self::METHOD_PART_KEY] = $this->method;
		// * params
		// A Structured value that holds the parameter values to be used during the invocation
		// of the method. This member MAY be omitted.
		// If present, parameters for the rpc call MUST be provided as a Structured value. Either by-position through an Array or by-name through an Object.
		//
		// + by-position: params MUST be an Array, containing the values in the Server expected order.
		// + by-name: params MUST be an Object, with member names that match the Server expected parameter names.
		//            The absence of expected names MAY result in an error being generated.
		//            The names MUST match exactly, including case, to the method's expected parameters.
		if ($this->params !== NULL) {
			$requestArray[self::PARAMS_PART_KEY] = $this->params;
		}
		return $requestArray;
	}

	/**
	 * Returns a json encoded json-rpc response
	 * 
	 * @access public
	 * @return string
	 */
	public function toJson() {
		return json_encode($this->toArray(), TRUE);
	}

	/**
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @static
	 * @return int
	 */
	public static function getId() {
		return self::$id;
	}

}


/**
 * JsonRpcRequestMissingField 
 * 
 * @uses Exception
 * @package general
 * @version $id$
 */
class JsonRpcRequestMissingField extends Exception {
	const CODE = JsonRpcError::ERROR_CODE_INVALID_REQUEST;
	public function __construct($message) {
		$this->message = $message . ' missing field';
		$this->code = self::CODE;
	}
}

/**
 * InvalidJsonRpcRequest 
 * 
 * @uses Exception
 * @package general
 * @version $id$
 */
class InvalidJsonRpcRequest extends Exception {
	const CODE = JsonRpcError::ERROR_CODE_INVALID_REQUEST;
	public function __construct($message) {
		$this->message = 'Invalid Json RPC request : <' . $message . ' >';
		$this->code = self::CODE;
	}
}
