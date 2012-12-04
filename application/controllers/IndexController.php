<?php

class IndexController extends Zend_Controller_Action {
	
	protected $_auth;

	public function init() {
		$this->_auth = new Zend_Perso_Authentification;
	}

	public function indexAction() {
		
	}

	public function loginAction() {
		$result = $this->_auth->login();

		if($result){
		 	$this->_helper->redirector('index', 'interface');
		} else {
		 	//return error message
		}
		 
		 
	}

	public function inscriptionAction() {
		
	}

}
