<?php

/**
 * JsonRpcClient 
 * 
 * Usage example:
 *
 * $client = new JsonRpcClient($curlClient, $jsonRpcServerUrl);
 * $client->$method($params)
 * OR
 * $client->sendJsonRpcRequest($jsonRpcRequest)
 *
 * If you need to add more info to the payload you can extend JsonRpcClient and
 * override sendJsonRpcRequest adding the non json-rpc protocol fields you need.
 */
class JsonRpcClient {

	/**
	 * __construct 
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com> 
	 * @param CurlClientInterface $curlClient 
	 * @param mixed $url 
	 * @access public
	 * @return void
	 */
	public function __construct($url, CurlClientInterface $curlClient = NULL) {
		$this->curlClient = $curlClient;
		$this->url = $url;
	}

	/**
	 * sendJsonRpcRequest 
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com> 
	 * @param JsonRpcRequest $jsonRpcRequest 
	 * @access public
	 * @return mixed
	 * @throws JsonRpcError
	 */
	public function sendJsonRpcRequest(JsonRpcRequest $jsonRpcRequest) {
		if ($this->curlClient !== NULL) {
			$this->curlClient->setReuseCurlInstance(TRUE);
			$this->curlClient->addHeader(CURLINFO_CONTENT_TYPE, 'application/json');
			$jsonResponse = $this->curlClient->post($this->url, $jsonRpcRequest->toJson());
		} else {
			$context = stream_context_create(array(
						'http' => array(
							'method'  => 'POST',
							'header'  => 'Content-Type: application/json\r\n',
							'content' => $jsonRpcRequest->toJson()
							)
						));
			$jsonResponse = file_get_contents($this->url, FALSE, $context);
		}
		$jsonRpcResult = JsonRpcResponse::createFromJson($jsonResponse);
		if ($jsonRpcResult->hasError()) {
			$jsonRpcError = JsonRpcError::createFromArray($jsonRpcResult->getError());
			throw $jsonRpcError;
		}
		return $jsonRpcResult->getResult();
	}

	/**
	 * __call 
	 * 
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com> 
	 * @param mixed $method 
	 * @param mixed $params 
	 * @access public
	 * @return mixed
	 * @throws JsonRpcError
	 */
	public function __call($method, $params) {
		$jsonRpcRequest = new JsonRpcRequest($method, $params);
		return  $this->sendJsonRpcRequest($jsonRpcRequest);
	}

}
