<?php

class Application_Form_Inscription extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
		$this->setAttrib('action', '/index/inscription');
 
        $this->addElement(	'text', 'inscription_nom', array(
            				'label'      => '* Nom :',
            				'required'   => true,
            				'filters'    => array('StringTrim')
		));
		
		$this->addElement(	'text', 'inscription_prenom', array(
            				'label'      => '* Prenom :',
            				'required'   => true,
            				'filters'    => array('StringTrim')
		));
		
		$this->addElement(	'text', 'inscription_pseudo', array(
            				'label'      => '* Pseudonyme :',
            				'required'   => true,
            				'filters'    => array('StringTrim')
		));
		
		$this->addElement('text', 'inscription_email', array(
            'label'      => 'Adresse mail :',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
            )
        ));

        $this->addElement('password', 'inscription_password', array(
            'label'      => '* Mot de passe :',
            'required'   => true)
		);
		
		$this->addElement('password', 'inscription_confirmPassword', array(
            'label'      => '* Confirmer le mot de passe :',
            'required'   => true)
		);
		
		$formule = new Zend_Form_Element_Select('formule');
		$formule	->setAttrib('id', 'formule')
					->addMultiOptions(array('1' => '1 Mo', '10' => '10 Mo', '100' => '100 Mo'));
		$this->addElement($formule);
 
        $this->addElement('submit', 'inscription_submit', array(
            'ignore'   => true,
            'label'    => 'Valider',)
		);
		
		$inscription = new Zend_Form_Element_Button('Fermer');
		$inscription	->setAttrib('onCLick', "$('#inscription').slideUp(500);")
						->setAttrib('id', 'inscription_fermer');
		$this->addElement($inscription);
    }
}