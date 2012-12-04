<?php

class Zend_Perso_Sqlite {
	
	protected $_db;
	
	public function connect(){
		$this->_db = Zend_Registry::get('db');
		
	}
	
	public function execute($requete) {
		$reponse = $this->_db->query($requete);
		
		$resultat = $reponse->fetchAll();

		var_dump($resultat);
		return $resultat;
	}
	
	public function disconnect(){
		unset($this->_db);
	}
	
}
