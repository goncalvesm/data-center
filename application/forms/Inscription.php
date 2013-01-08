<?php

class Application_Form_Inscription extends Twitter_Bootstrap_Form_Vertical
{
    public function init()
    {
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');	
			
        $this->setMethod('post');
		$this->setAttrib('action', '/index/inscription');
 
        $this->addElement(	'text', 'inscription_nom', array(
            				'placeholder'      => '* Nom',
		));
		
		$this->addElement(	'text', 'inscription_prenom', array(
            				'placeholder'      => '* Prenom',
		));
		
		$this->addElement(	'text', 'inscription_pseudo', array(
            				'placeholder'      => '* Pseudonyme',
		));
		
		$this->addElement('text', 'inscription_email', array(
            'placeholder'      => 'Adresse mail',
            'validators' => array(
                'EmailAddress',
            )
        ));

        $this->addElement('password', 'inscription_password', array(
            'placeholder'      => '* Mot de passe',
		));
		
		$this->addElement('password', 'inscription_confirmPassword', array(
            'placeholder'      => '* Confirmer le mot de passe',
		));
		
		$this->addElement(	'select', 'formule', array(
            				'id'			=> 'formule',
            				'multiOptions'	=> array(
            					'1' => '1 Mo', 
            					'10' => '10 Mo',
            					'100' => '100 Mo',
							)
            				
		));
		
		$this->addElement ('captcha', 'captcha', array(
		    'placeholder' => "Recopiez le code ci-dessus",
		    
			// paramétrage en reprenant les noms de méthodes vus précédemment
			'captcha' => array(
		        "captcha" => "Image",
		        "wordLen" => 6,
		        "font" => APPLICATION_PATH."/../public/fonts/DejaVuSans.ttf",
				"height" => 100,
				"width" => 190,
				"fontSize" => 22,
				"imgDir" => APPLICATION_PATH."/../public/captcha/",
				"imgUrl" => "/captcha/",
		)));
 
        $this->addElement('button', 'Inscription', array(
            'type'          => 'submit',
            'buttonType'    => 'success',
        ));
    }
}