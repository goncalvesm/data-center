<?php

use \services;

class IndexController extends Zend_Controller_Action {

	protected $_authentification;


	public function init() {
		//$this->_authentification = new \services\authentification();
	}

	public function indexAction() {
		// action body
	}

	public function loginAction() {
		/*$result = $authentification->login();

		 if($result){
		 //got to the index

		 //set the session
		 } else {
		 //return error message
		 }*/
	}

	public function logoutAction() {
		//desactiver la vue

		/*$result = $authentification->login();

		 if(!$result){
		 //return error message
		 }*/
	}

	public function inscriptionAction() {
		
	}

	public function interfaceAction() {
		
	}

}
