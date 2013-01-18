<?php

/**
 *
 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
 */
require_once '../JsonRpcRequest.php';
require_once '../JsonRpcError.php';

class JsonRpcRequestTest extends PHPUnit_Framework_TestCase {

	protected $testMethod = 'AgentExample:getUserMainData';
	protected $testParams = array('userId' => 22);

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
	 * Tests instantiation with an incomplete input
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 */
	public function testConstructInvalidObjectGiven() {
		$this->setExpectedException('JsonRpcRequestMissingField');
		$input = array();
		JsonRpcRequest::createFromJson(json_encode($input));
	}

	/**
	 * Tests instantiation with a valid input.
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 */
	public function testConstructValid() {
		$this->getTestRcpRequest();
	}

	/**
	 * Tests getMethod
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 */
	public function testGetMethod() {
		$this->assertEquals($this->getTestRcpRequest()->getMethod(), $this->testMethod);
	}

	/**
	 * Tests getParams
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 */
	public function testGetParams() {
		$this->assertEquals($this->getTestRcpRequest()->getParams(), $this->testParams);
	}

	/**
	 * Tests getId
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 */
	public function testGetId() {
		$this->getTestRcpRequest();
		$this->assertEquals(JsonRpcRequest::getId(), 1);
	}

	/**
	 * Tests setParams
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 */
	public function testSetParams() {
		$rpcRequest = $this->getTestRcpRequest();
		$testArray = array('foo' => 'bar');
		$rpcRequest->setParams($testArray);
		$this->assertEquals($rpcRequest->getParams(), $testArray);
	}

	/**
	 * Tests toJson
	 *
	 * @author Iván Mosquera Paulo <imosquera@tuenti.com>
	 */
	public function testToJson() {
		$rpcRequest = $this->getTestRcpRequest();
		$this->assertTrue($rpcRequest instanceof JsonRpcRequest);
		$expectedArray = array(
			'jsonrpc' => '2.0',
			'id' => 1,
			'method' => $this->testMethod,
			'params' => $this->testParams
		);
		$this->assertEquals($expectedArray, json_decode($rpcRequest->toJson(), TRUE));
	}

	/**
	 * @return JsonRpcRequest to be reused among tests
	 */
	protected function getTestRcpRequest() {
		$input = array();
		$input['jsonrpc'] = '2.0';
		$input['id'] = 1;
		$input['method'] = $this->testMethod;
		$input['params'] = $this->testParams;
		$rpcRequest = JsonRpcRequest::createFromJson(json_encode($input));
		return $rpcRequest;
	}

}
