<?php

/**
 *
 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
 */
require_once '../JsonRpcResponse.php';
require_once '../JsonRpcError.php';

class JsonRpcResponseTest extends PHPUnit_Framework_TestCase {

	protected $testData = 'The data';
	protected $testId = 2;

	/**
	 * SetUp the test.
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * TearDrown the test.
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Tests instantiation with a valid input.
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 */
	public function testConstruct() {
		$this->getTestRcpResponse();
	}


	/**
	 * Tests getObject
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 */
	public function testGetObject() {
		$rpcResponse = $this->getTestRcpResponse();
		$expectedArray = array(
			'jsonrpc' => '2.0',
			'id' => $this->testId,
			'result' => $this->testData,
		);
		$this->assertEquals(json_encode($expectedArray), $rpcResponse->toJson());
	}

	/**
	 * Tests JsonRpcResponse with Error
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 */
	public function testResponseWithError() {
		$rpcError = new JsonRpcError('Message', -104);
		$rpcResponse = new JsonRpcResponse($rpcError, $this->testId);
		$expectedArray = array(
			'jsonrpc' => '2.0',
			'id' => $this->testId,
			'error' => $rpcError->toArray()
		);
		$object = $rpcResponse->toJson();
		$this->assertEquals(json_encode($expectedArray), $object);
	}


	/**
	 * @return JsonRpcResponse to be reused among tests
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 */
	protected function getTestRcpResponse() {
		return new JsonRpcResponse($this->testData, $this->testId);
	}

}
