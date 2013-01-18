<?php

/**
 * JsonRpcError class
 * 
 * This class implements JSON-RPC's JsonRpcError object.
 * 
 */

class JsonRpcError extends Exception {
	/**
	* * Parse Error
	*
	* Invalid JSON was received by the server.
	* An error occurred on the server while parsing the JSON text.
	*/
	const ERROR_CODE_PARSE_ERROR = -32700;
	/**
	* * Invalid Request
	*
	* The JSON sent is not a valid Request object.
	*/
	const ERROR_CODE_INVALID_REQUEST = -32600;
	/**
	* * Method not found
	*
	* The method does not exist / is not available.
	*/
	const ERROR_CODE_METHOD_NOT_FOUND = -32601;
	/**
	* * Invalid params
	*
	* Invalid method parameter(s)
	*/
	const ERROR_CODE_INVALID_PARAMS = -32602;
	/**
	* * Internal error
	*
	* Internal JSON-RPC error
	*/
	const ERROR_CODE_INTERNAL_ERROR = -32603;

	protected $code;
	protected $message;
	protected $data;

	/**
	 * __construct 
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param string $errorMessage 
	 * @param int $errorCode 
	 * @param mixed $errorData 
	 * @access public
	 * @return void
	 */
	public function __construct($errorMessage, $errorCode, $errorData = NULL) {
		$this->message = $errorMessage;
		$this->code = $errorCode;
		$this->data = $errorData;
	}

	/**
	 * createFromArray 
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param array $data 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function createFromArray(array $data) {
		self::validateFields($data);
		$dataField = isset($data['data'])? $data['data'] : NULL;
		return new JsonRpcError($data['message'], $data['code'], $dataField);
	}

	/**
	 * validateFields 
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @param array $data 
	 * @static
	 * @access protected
	 * @return void
	 */
	protected static function validateFields(array $data) {
		$requiredFields = array('message', 'code');
		foreach($requiredFields as $requiredField) {
			if (!isset($data[$requiredField])) {
				throw new JsonRpcErrorMissingField($requiredField);
			}
		}
	}

	/**
	 * Gets the data member
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @access public
	 * @return string
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Returns a array Object with the JsonRpcError data
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @access public
	 * @return array $array
	 */
	public function toArray() {
		$array = array();
		//When a rpc call encounters an error, the Response Object MUST contain the error member with a value 
		// that is a Object with the following members:
		// * code
		//	A Number that indicates the error type that occurred.
		//	This MUST be an integer.
		$array['code'] = (int) $this->code;
		// * message
		// A String providing a short description of the error.
		// The message SHOULD be limited to a concise single sentence.
		$array['message'] = $this->message;
		// * data
		// A Primitive or Structured value that contains additional information about the error.
		// This may be omitted.
		// The value of this member is defined by the Server (e.g. detailed error information, nested errors etc.).
		if ($this->data !== NULL) {
			$array['data'] = $this->data;
		}
		return $array;
	}
}


/**
 * JsonRpcErrorMissingField
 * 
 * @uses Exception
 * @package general
 * @version $id$
 */
class JsonRpcErrorMissingField extends Exception {
    const CODE = JsonRpcError::ERROR_CODE_INVALID_REQUEST;
    public function __construct($message) {
        $this->message = $message . ' missing field';
        $this->code = self::CODE;
    }
}
