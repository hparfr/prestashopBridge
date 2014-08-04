<?php

namespace Hpar\PrestashopBridge;

use Symfony\Component\HttpFoundation\Request;


class PrestashopBridge {

	protected $pathToPrestashop;
	protected $id_shop;

	public function __construct($pathToPrestashop, $id_shop = 1) {
		$this->pathToPrestashop = $pathToPrestashop;
		$this->id_shop = $id_shop;
	}

	/*
	* Load Prestashop core files
	*/
	protected function loadPrestaKernel() {

		//if id_shop is not found in $_GET or $_POST
		//a redirection will be done in Prestashop/classes/shop/Shop.php:initialize()

		//add id_shop in $_GET in a new HttpFundation\Request
		$requestClean = Request::create('', 'GET', array('id_shop'=> $this->id_shop));
		$requestClean->overrideGlobals();

		//init prestashop
		include($this->pathToPrestashop.'/config/config.inc.php');
	}


	public function userExist($email) {

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