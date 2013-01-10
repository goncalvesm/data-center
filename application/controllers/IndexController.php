<?php

class IndexController extends Zend_Controller_Action {
	
	protected $_auth;
	
	protected $_session;
	
	protected $_sqlite;
	
	protected $_hash;

	public function init() {
		$this->_auth = new Zend_Perso_Authentification;
		$this->_session = new Zend_Perso_Session;
		$this->_sqlite = new Zend_Perso_Sqlite;
		$this->_hash = new Zend_Perso_Hash;
		
		if($this->_session->get('connecte') == true){
			$this->_helper->redirector('index', 'interface');
		}
	}

	public function indexAction() {
		if($this->_session->get('message') != null){
			$this->view->message = $this->_session->get('message');
			$this->_session->set('message', null);
		}
		if($this->_session->get('erreur') != null){
			$this->view->erreur = $this->_session->get('erreur');
			$this->_session->set('erreur', null);
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
                $params = $this->getRequest()->getParams();
                $id = $params['bootstrap']['connexion_login'];
				$password = $params['bootstrap']['connexion_password'];
				$password = $this->_hash->hashPassword($password);
				
				$resultat = $this->_auth->login($id, $password);
				
				if($resultat['succes']){
					$this->_helper->redirector('index', 'interface');
				} else {
					$this->_session->set('erreur', $resultat['message']);
					$this->_helper->redirector('index', 'index');
				}
            }
        }
	}

	public function inscriptionAction() {
		$inscription = new Application_Form_Inscription();
		
		$request = $this->getRequest();
		
		if ($this->getRequest()->isPost()) {
            if ($inscription->isValid($request->getPost())) {
            	$params = $this->getRequest()->getParams();
                $nom = $params['bootstrap']['nom'];
				$prenom = $params['bootstrap']['prenom'];
				$pseudo = $params['bootstrap']['pseudo'];
				$mail = $params['bootstrap']['email'];
				$password = $params['bootstrap']['password'];
				$confirmPassword = $params['bootstrap']['confirmPassword'];
				$formule = $params['bootstrap']['formule'];
				$dateCreation = time();
				
				if($pseudo != '' && $nom != '' && $prenom != '' && $password != '' && $confirmPassword != ''){
					$requete = "SELECT * FROM utilisateurs WHERE  pseudo='".$pseudo."'";
					$resultat = $this->_sqlite->execute($requete);
					
					if(count($resultat) == 0){
						if($password === $confirmPassword){
							$password = $this->_hash->hashPassword($password);
							
							$requete = "INSERT INTO utilisateurs ('nom', 'prenom', 'pseudo','email', 'motDePasse', 'dateCreation', 'formule') VALUES('".$nom."','".$prenom."','".$pseudo."','".$mail."','".$password."','".$dateCreation."','".$formule."')";
							$this->_sqlite->execute($requete);
							
							$requete = "SELECT * FROM utilisateurs WHERE  pseudo='".$pseudo."'";
							$resultat = $this->_sqlite->execute($requete);
							
							if(count($resultat) == 1){
								$requete = "INSERT INTO dossiers ('nom', 'chemin', 'utilisateur','root', 'dateCreation') VALUES ('".$pseudo."', '".$pseudo."/', '".$pseudo."','1', '".date("d/m/y")."')";
								$this->_sqlite->execute($requete);
								mkdir(APPLICATION_PATH."/../data/".$pseudo."/");
								$this->_session->set('utilisateur', $resultat[0]);
								$this->_session->set('connecte', true);
								$this->_helper->redirector('index', 'interface');
							} else {
								$this->_session->set('erreur', "Erreur lors de l'inscription en base de données de l'utilisateur");
								$this->_helper->redirector('index', 'index');
							}
						} else {
							$this->_session->set('erreur', "Les mots de passe doivent etre identiques");
							$this->_helper->redirector('index', 'index');
						}
					} else {
						$this->_session->set('erreur', "Ce pseudonyme est déja atribué");
						$this->_helper->redirector('index', 'index');
					}
				} else {
					$this->_session->set('erreur', "Les champs marqué d'un asterix sont obligatoires");
					$this->_helper->redirector('index', 'index');
				}
            } else {
            	$this->_session->set('erreur', "Erreur lors du renseignement de l'un des champs, merci de réessayer");
				$this->_helper->redirector('index', 'index');
            }
        } else {
        	$this->_session->set('erreur', "Les données doivent etre envoyées en POST et non en GET");
			$this->_helper->redirector('index', 'index');
        }
		
		$this->_helper->viewRenderer->setNoRender(true);
	}

}
