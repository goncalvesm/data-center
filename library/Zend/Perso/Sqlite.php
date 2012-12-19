<?php

class Zend_Perso_Sqlite {
	
	protected $_db;
	
	public function execute($requete) {
		$this->_db = Zend_Registry::get('db');
		
		$reponse = $this->_db->query($requete);
		
		$resultat = $reponse->fetchAll();
		
		unset($this->_db);
		
		return $resultat;
	}
	
}
