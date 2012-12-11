<?php

class IndexController extends Zend_Controller_Action {
	
	protected $_auth;
	
	protected $_session;

	public function init() {
		$this->_auth = new Zend_Perso_Authentification;
		$this->_session = new Zend_Perso_Session;
	}

	public function indexAction() {
		if($this->_session->get('message') != null){
			$this->view->message = $this->_session->get('message');
			$this->_session->set('message', null);
		}
		
		$this->_helper->actionStack('login', 'index', array());
	}

	public function loginAction() {
		//Initialise les formulaires
        $login = new Application_Form_Connexion();
		$inscription = new Application_Form_Inscription();
		
		$this->_helper->ViewRenderer->setResponseSegment('login');
 		
 		$this->view->inscription = $inscription;
        $this->view->login = $login;
		
		//Récupère le résultat de la connexion
		$request = $this->getRequest();
		
		if ($this->getRequest()->isPost()) {
            if ($login->isValid($request->getPost())) {
                $id = $_POST['connexion_login'];
				$password = $_POST['connexion_password'];
				
				$resultat = $this->_auth->login($id, $password);
				
				if($resultat['succes']){
					$this->_helper->redirector('index', 'interface');
				} else {
					$this->_session->set('message', $resultat['message']);
					$this->_helper->redirector('index', 'index');
				}
				
            }
        }
	}

	public function inscriptionAction() {
		
	}

}
