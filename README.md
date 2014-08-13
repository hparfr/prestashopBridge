prestashopBridge
================

Allow Prestashop to work nicely with your existing PHP Application (Drupal, Symfony, Joomla, Wordpress, ...).

Use your authentication mechansim of choice with prestashop (SSO, Oauth, 2-factor, ... )

Benefits:

 - customers don't have to create an account in Prestashop
 - customers authenticate only in your application


Features:

 - Login / Logout customers
 - Create customers
 - Create carts


 Requirements:

 - should be installed in the same server than prestashop (because some  Prestashop code is loaded with _include()_  )
 - should be served from the same domain:port than Prestashop (because of the auth cookie)



En Français
=====

Ce projet vous est utile si vous voulez que :

- vos clients n'aient qu'un seul mot de passe pour votre site et pour votre boutique
- vos que vos clients ne s'authentifient que par votre site
- utiliser une autre méthode d'authentification (2-facteurs, OAuth, CAS, ...) que celles proposent par Prestashop.


Fonctionnalités :

- connecter un client
- déconnecter un client
- créer des clients dans la base de prestashop
- créer des commandes (optionnellement avec des références)

Contraintes :

- Doit être installé sur le même serveur que Prestashop (car utilise quelques fonctions internes de prestashop - via _include()_ )
- Doit être servi depuis le même nom de domaine / port que Prestashop (à cause du cookie d'authentification)


Installation
====

In your composer.json:

```json
{
	"require": {
		"hpar/prestashop-bridge": "dev-master"
	},
	"repositories": [{
		"type": "vcs",
		"url": "https://github.com/hparfr/prestashopBridge.git"
	}]
}
```


Example
=====

In a symfony application:

```php
<?php 

namespace Acme\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Hpar\PrestashopBridge\PrestashopBridge;

class PrestashopBridgeExampleController extends Controller
{
	public function loginAction()
	{
		if (!$this->get('security.context')->isGranted('ROLE_USER')) {
			throw new AccessDeniedException();
		}

		$prestaBridge = new PrestashopBridge('/path/to/prestashop/', 1);

		$user = $this->getUser(); //get connected user

		if (!$prestaBridge->userExist($user->getEmail())) //if user exist in prestahop database
			$prestaBridge->createUser(
				$user->getEmail(),
				$user->getLastName(),
				$user->getFirstName()
			);

		$prestaBridge->login($email);

		return $this->redirect('http://prestashop_url/');
	}
}

```