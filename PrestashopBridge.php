<?php

namespace Hpar\PrestashopBridge;

use Symfony\Component\HttpFoundation\Request;


class PrestashopBridge {

	public function __construct($pathToPrestashop, $id_shop = 1) {
		$this->loadPrestaKernel($pathToPrestashop, $id_shop);
	}

	protected function loadPrestaKernel($pathToPrestashop, $id_shop) {

	}

	
	public function userExist($email) {

	}


	public function createUser($email, $lastname, $firstname, $password = null) {

	}

	public function login($email) {

	}
	
	public function logout() {

	}

}