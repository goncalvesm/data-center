<?php

class Application_Form_Connexion extends Zend_Form
{
    public function init()
    {

        $this->setMethod('post');
		$this->setAttrib('action', '/index/login');

        $this->addElement(	'text', 'connexion_login', array(
            				'label'      => 'Identifiant :',
            				'required'   => false,
		));

        $this->addElement('password', 'connexion_password', array(
            'label'      => 'Mot de passe :',
            'required'   => false)
		);
 
        // Un bouton d'envoi
        $this->addElement('submit', 'connexion_submit', array(
            'ignore'   => true,
            'label'    => 'Connexion',)
		);
		
		$inscription = new Zend_Form_Element_Button('Inscription');
		$inscription	->setAttrib('onCLick', "$('#inscription').slideDown(500);")
						->setAttrib('id', 'connexion_inscription');
		$this->addElement($inscription);
 
        // Et une protection anti CSRF
        /*$this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));*/
    }
}