<?php

class InterfaceController extends Zend_Controller_Action {

	protected $_auth;
	
	protected $_sql;

	public function init() {
		$this->_auth = new Zend_Perso_Authentification;
		$this->_sql = new Zend_Perso_Sqlite;
		
		$this->_helper->layout->setLayout('interface');
	}

	public function indexAction() {
		
	}

	public function logoutAction() {
		$result = $this->_auth->logout();

		if($result){
			$this->_helper->redirector('index', 'index');
		} else {
			
		}
	}
	
	public function lectureAction() {
		//$this->_sql->execute("INSERT INTO users VALUES('Mickael','Goncalves','Micka','test@test.fr','insset','04/12/2012','100')");
		//$this->_sql->execute("INSERT INTO users VALUES('Toto','Rototo','Torototo','test2@test.fr','insset','04/12/2012','1')");
		//$this->_sql->execute("DELETE FROM users WHERE firstName='Mickael'");
		$this->_sql->execute("SELECT * FROM users");
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	public function addAction() {
		
	}
	
	public function amisAction() {
		
	}

}
