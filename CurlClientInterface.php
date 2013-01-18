<?php
/**
* Interface that curl clients need to abide to
* nothing fancy, just making it more formal
* @author Nuria Ruiz <nruiz@tuenti.com>
**/
interface CurlClientInterface {

	/**
	* @param boolean $reuse
	**/
	public function setReuseCurlInstance($reuse);
	
	/**
	* @param String $header
	* @param String valuie
	**/
	public function addHeader($header,$value);
	/**
	* @param	String $url url to post request to
	* @param	String $json json request payload
	* @returns	String $jsonResponse
	**/
	public function post($url,$json);


}
