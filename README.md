
# Description
Tuenti's implementation of json rpc according to
the json-rpc spec: http://www.jsonrpc.org/specification

We implements the Json-Rpc 2.0 specificationminus events and batches

The lib provides the implementation of the objects described in the implementation and a json-rpc client to be used.

<table>
	<tr>
		<td>json-rpc 2.0 spec object</td>
		<td>json-rpc lib class</td>
	</tr>
	<tr>
		<td>Request object	</td>
		<td>JsonRpcRequest</td>
	</tr>
	<tr>
		<td>Response object</td>	 				
		<td>JsonRpcResponse</td>
	</tr>
	<tr>
		<td>Error object</td>	 					
		<td>JsonRpcError</td>
	</tr>
</table>


It also implements a json rpc client (JsonRpcClient class). 
That client class delegates the connection handling to a 
generic CurlClient that implements CurlClientInterface.

The library does not provide a curl client but any that implements the interface would do.


# Usage

## Client Side

Puting the request together

```php
$client = new JsonRpcClient($jsonRpcServerUrl, $curlClient); [1]

$jsonRpcRequest = new JsonRpcRequest($method, $params);

try { 
      $result = $client->sendJsonRpcRequest($jsonRpcRequest)
} catch (JsonRpcError $e) {
      // Manage error.
}
```
OR

```php
try {
       $client->$method($params) // [2]
} catch (JsonRpcError $e) {
       // Manage error
}
```
[1] $curlClient is a CurlClient object. The parameter is optional and a stream is prepared instead if it's missing but it should not be used in production.
[2] In this case there is a __call functions which already prepares the jsonRpcRequest internally.


##Server side

```php
$request = JsonRpcRequest::createFromJson($inputData);
$jsonRpcResponse = new JsonRpcResponse($content);
```
On the server side you need to parse the request

```php
.....
protected function parseRequest() {
       ....
       $request = JsonRpcRequest::createFromJson($inputData);
       ....
}

.....
protected function processRequest(JsonRpcRequest $request, $response) {
       $parts = explode(self::NAMESPACE_SEPARATOR, $request->getMethod());                                     
       list($this->class, $this->action) = $parts;
       $args = (array) $request->getParams();
       ....
       try {
            $content = $this->application->execute($this->class, $this->action, $args);
            $jsonRpcResponse = new JsonRpcResponse($content); [1]
       } catch (Exception $e) {
             $jsonRpcResponse = new JsonRpcResponse(new JsonRpcError($e->getMessage(), JsonRpcError::ERROR_CODE_INTERNAL_ERROR));
       }
       $httpResponse->addContent($jsonRpcResponse->toJson());
}

```
