<?php

class Application_Form_Connexion extends Twitter_Bootstrap_Form_Inline
{
    public function init()
    {
		$this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');
		
        $this->setMethod('post');
		$this->setAttrib('action', '/index/login');
		$this->setAttrib('id', 'connexion');

        $this->addElement(	'text', 'login', array(
            				'placeholder'  	=> 'Identifiant',
            				'name'			=> 'connexion_login',
            				'dimension'		=> 2,
		));

        $this->addElement('password', 'password', array(
            'placeholder'  	=> 'Mot de passe',
            'name'			=> 'connexion_password',
			'dimension'		=> 2,
		));
		
		$this->addElement('button', 'submit', array(
            'label'         => 'Connexion',
            'type'          => 'submit',
            'buttonType'    => 'primary',
            'escape'        => false,
        ));
		
		$this->addElement('button', 'inscription', array(
            'label'         => 'Inscription',
            'type'          => 'button',
            'buttonType'    => 'success',
            'escape'        => false,
            'onClick'		=> "$('#inscription').slideToggle(500);"
        ));
    }
}