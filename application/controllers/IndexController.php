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
		 	//got to the index

		 	//set the session
		 } else {
		 	//return error message
		 }
		 
		 
	}

	public function logoutAction() {
		//desactiver la vue

		$result = $this->_auth->login();

		if(!$result){
			//return error message
		}
	}

	public function inscriptionAction() {
		
	}

	public function interfaceAction() {
		
	}

}
