<?php

class InterfaceController extends Zend_Controller_Action {

	protected $_auth;
	
	protected $_sql;
	
	protected $_session;

	public function init() {
		$this->_auth = new Zend_Perso_Authentification;
		$this->_sqlite = new Zend_Perso_Sqlite;
		$this->_session = new Zend_Perso_Session;
		
		if($this->_session->get('connecte') != true){
			$this->_helper->redirector('index', 'index');
		}
		
		$this->_helper->layout->setLayout('interface');
	}

	public function indexAction() {
		
	}

	public function logoutAction() {
		$result = $this->_auth->logout();
		
		$this->_helper->redirector('index', 'index');
	}
	
	public function lectureAction() {
	}
	
	public function addAction() {
		
	}
	
	public function amisAction() {
		
	}

}
