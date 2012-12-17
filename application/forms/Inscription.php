<?php

class Application_Form_Inscription extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
		$this->setAttrib('action', '/index/inscription');
 
        $this->addElement(	'text', 'inscription_nom', array(
            				'label'      => '* Nom :',
		));
		
		$this->addElement(	'text', 'inscription_prenom', array(
            				'label'      => '* Prenom :',
		));
		
		$this->addElement(	'text', 'inscription_pseudo', array(
            				'label'      => '* Pseudonyme :',
		));
		
		$this->addElement('text', 'inscription_email', array(
            'label'      => 'Adresse mail :',
            'validators' => array(
                'EmailAddress',
            )
        ));

        $this->addElement('password', 'inscription_password', array(
            'label'      => '* Mot de passe :',
		));
		
		$this->addElement('password', 'inscription_confirmPassword', array(
            'label'      => '* Confirmer le mot de passe :',
		));
		
		$formule = new Zend_Form_Element_Select('inscription_formule');
		$formule	->setAttrib('id', 'formule')
					->addMultiOptions(array('1' => '1 Mo', '10' => '10 Mo', '100' => '100 Mo'));
		$this->addElement($formule);
		
		$captcha = new Zend_Form_Element_Captcha('captcha', array(
		    'label' => "Recopiez le code ci-dessous",
		    
			// paramétrage en reprenant les noms de méthodes vus précédemment
			'captcha' => array(
		        "captcha" => "Image",
		        "wordLen" => 6,
		        "font" => APPLICATION_PATH."/../public/fonts/DejaVuSans.ttf",
				"height" => 50,
				"width" => 190,
				"fontSize" => 22,
				"imgDir" => APPLICATION_PATH."/../public/captcha/",
				"imgUrl" => "/captcha/"
		    )
		));
		$this->addElement($captcha);
 
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