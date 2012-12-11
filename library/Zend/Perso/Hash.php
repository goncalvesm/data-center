<?php

class Zend_Perso_Hash {
	
	public function hashPassword($password){
		$result = hash('sha512', $string);
		
		return $result;
	}
	
}
