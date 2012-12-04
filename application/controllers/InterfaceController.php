<?php

class InterfaceController extends Zend_Controller_Action {

	protected $_auth;
	protected $_sql;

	public function init() {
		$this->_auth = new Zend_Perso_Authentification;
		$this->_sql = new Zend_Perso_Sqlite;
	}

	public function indexAction() {
		$this->_helper->layout->setLayout('interface');
	}

	public function logoutAction() {
		$result = $this->_auth->logout();

		if($result){
			$this->_helper->redirector('index', 'index');
		} else {
			
		}
	}
	
	public function lectureAction() {
		$this->_sql->connect();
		$this->_sql->execute("INSERT INTO users VALUES('test','test','test','test','test','test','test')");
		$this->_helper->viewRenderer->setNoRender(true);
	}

}
