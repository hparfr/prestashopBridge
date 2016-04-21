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

		$this->loadPrestaKernel();
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

		$customer = new \Customer();
		$authentication = $customer->getByEmail($email);

		if (!$authentication)
			return false;
		return true;
	}


	public function login($email) {

		$customer = new \Customer();
		$authentication = $customer->getByEmail($email);

		if (!$authentication) //user doesn't exist
			return false;

		$ctx = \Context::getContext();

		$ctx->cookie->id_compare = isset($ctx->cookie->id_compare) ? $ctx->cookie->id_compare: \CompareProduct::getIdCompareByIdCustomer($customer->id);
		$ctx->cookie->id_customer = (int)($customer->id);
		$ctx->cookie->customer_lastname = $customer->lastname;
		$ctx->cookie->customer_firstname = $customer->firstname;
		$ctx->cookie->logged = 1;
		$customer->logged = 1;
		$ctx->cookie->is_guest = $customer->isGuest();
		$ctx->cookie->passwd = $customer->passwd;
		$ctx->cookie->email = $customer->email;

		// Add customer to the context
		$ctx->customer = $customer;

		$id_cart = (int)\Cart::lastNoneOrderedCart($ctx->customer->id);
		if ($id_cart) {
			$ctx->cart = new \Cart($id_cart);
		} else {
			$ctx->cart = new \Cart();
			$ctx->cart->id_currency = \Currency::getDefaultCurrency()->id; //mandatory field
		}

		$ctx->cart->id_customer = (int)$customer->id;
		$ctx->cart->secure_key = $customer->secure_key;
		$ctx->cart->save();
		$ctx->cookie->id_cart = (int)$ctx->cart->id;

		\CartRule::autoRemoveFromCart($ctx);
		\CartRule::autoAddToCart($ctx);

		$ctx->cookie->write();
		return true;
	}

	/**
	* @param string password  md5 string or null
	* if password = null, login will only be possible by the current bridge
	*/
	public function createUser($email, $lastname, $firstname, $password = null) {

		if (\Customer::customerExists($email)) {
			return false;
		}

		$customer = new \Customer();

		$customer->active = 1;
		$customer->firstname = $firstname;
		$customer->lastname = $lastname;
		$customer->email = $email;
		$customer->active = 1;
		$customer->passwd  = $password ? $password : md5(bin2hex(openssl_random_pseudo_bytes(10)));

		if ($customer->add())
			return true;
		else
			return false;
	}

	public function logout() {

		$ctx = \Context::getContext();
		if ($ctx)
			$ctx->customer->logout();
	}

	//add a product to the cart with quantity and a reference
	public function addToCurrentCart($idProduct, $quantity = 1, $ref = null) {

		$ctx = \Context::getContext();
		$cart = $ctx->cart;

		if ($ref) { //do not add twice the same ref

			$customiziations = $ctx->cart->getProductCustomization($idProduct);
			$customWithRef = array_filter($customiziations, function ($c) use ($ref) {
				return $ref == $c['value'];
			});

			if (!$customWithRef) { //not already present
				$cart->addTextFieldToProduct($idProduct, 1, \Product::CUSTOMIZE_TEXTFIELD, $ref);
				$cart->updateQty($quantity, $idProduct);
			} else { //already present, remplace quantity (if needed)
				$custom = $customWithRef[0];
				$qtyDiff = $quantity - $custom['quantity'];
				$upOrDown = ($qtyDiff > 0) ? 'up' : 'down'; //updateQty only change relatively
				if ($qtyDiff !== 0)
					$cart->updateQty( abs($qtyDiff), $idProduct, null, $custom['id_customization'], $upOrDown);
			}
		} else
			$cart->updateQty($quantity, $idProduct);
	}


}
