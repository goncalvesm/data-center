<?php

class Zend_Perso_Authentification {
	
	protected $_sqlite;
	
	protected $_session;
	
	public function __construct(){
		$this->_sqlite = new Zend_Perso_Sqlite;
		$this->_session = new Zend_Perso_Session;
	}
	
	public function login($pseudo, $motDePasse){
		$requete = "SELECT * FROM utilisateurs WHERE pseudo='".$pseudo."'";
		$resultat = $this->_sqlite->execute($requete);
		$reponse = array();
		
		if(is_array($resultat)){
			if(count($resultat) == 1){
				if($resultat[0]['motDePasse'] === $motDePasse){
					$reponse['succes'] = true;
					$this->_session->set('utilisateur', $resultat[0]);
					$this->_session->set('connecte', true);
				} else {
					$reponse['succes'] = false;
					$reponse['message'] = "Mot de passe invalide";
				}
			} else {
				$reponse['succes'] = false;
				$reponse['message'] = "L'utilisateur spécifié n'existe pas";
			}
		} else {
			$reponse['succes'] = false;
			$reponse['message'] = "Erreur lors de la lecture en base de données";
		}
		
		return $reponse;
	}
	
	public function logout(){
		$this->_session->set('connecte', null);
		$this->_session->set('utilisateur', null);
	}
	
}
