<?php

class IndexController extends Zend_Controller_Action {
	
	protected $_auth;

	public function init() {
		$this->_auth = new Zend_Perso_Authentification;
	}

	public function indexAction() {
		$this->_helper->actionStack('login', 'index', array());
	}

	public function loginAction() {
		$request = $this->getRequest();
		
        $login = new Application_Form_Connexion();
		$inscription = new Application_Form_Inscription();
		
		$this->_helper->ViewRenderer->setResponseSegment('login');
 
        if ($this->getRequest()->isPost()) {
            if ($login->isValid($request->getPost())) {
                $id = $_POST['login'];
				$password = $_POST['password'];
				$this->_helper->redirector('index', 'interface');
            }
        }
 		
 		$this->view->inscription = $inscription;
        $this->view->login = $login;
	}

	public function inscriptionAction() {
		
	}

}
