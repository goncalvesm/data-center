<?php

class Zend_Perso_Session {
	
	protected static $_sessionName = 'Default';
	
	protected $_sessionObject = null;

    protected function _getSessionObject() {
        if (!$this->_sessionObject instanceof \Zend_Session_Namespace) {
            $this->_sessionObject = new \Zend_Session_Namespace(static::$_sessionName);

        }
        return $this->_sessionObject;
    }
	
	public function set($nom, $valeur) {
		$this->_getSessionObject()->$nom = $valeur;
	}
	
	public function get($nom){
		return $this->_getSessionObject()->$nom;
	}
	
}
