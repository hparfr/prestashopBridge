<?php

namespace Hpar\PrestashopBridge;

use Symfony\Component\HttpFoundation\Request;


class PrestashopBridge {

	protected $pathToPrestashop;
	protected $id_shop;

	public function __construct($pathToPrestashop, $id_shop = 1) {
		$this->id_shop = $id_shop;

		if (!$pathToPrestashop || $pathToPrestashop == '')
			$pathToPrestashop = '.';

		$this->pathToPrestashop = $pathToPrestashop;

	}

	/*
	* Load Prestashop core files
	*/
	protected function loadPrestaKernel() {

		//if id_shop is not found in $_GET or $_POST
		//a redirection will be done in Prestashop/classes/shop/Shop.php:initialize()

		//we need also $_SERVER['SERVER_NAME'] for setting the Cookie in the right domain
		$currentRequest = Request::createFromGlobals();

		//create new HttpFundation\Request
		//add id_shop in $_GET
		//copy $_SERVER from currentRequest
		$cleanRequest = Request::create('', 'GET', array('id_shop'=> $this->id_shop), array(), array(), $currentRequest->server->all());
		$cleanRequest->overrideGlobals();

		//init prestashop
		include($this->pathToPrestashop.'/config/config.inc.php');
	}


	public function userExist($email) {
		$this->loadPrestaKernel();

		$customer = new \Customer();
		$authentication = $customer->getByEmail($email);

		if (!$authentication)
			return false;
		return true;
	}


	public function createUser($email, $lastname, $firstname, $password = null) {

	}

	public function login($email) {

	}

	public function logout() {
		$this->loadPrestaKernel();

		$ctx = \Context::getContext();
		if (!$ctx)
			$ctx->customer->logout();
	}

}