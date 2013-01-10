<?php

class Application_Form_Inscription extends Twitter_Bootstrap_Form_Inline
{
    public function init()
    {
        $this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');	
			
        $this->setMethod('post');
		$this->setAttrib('action', '/index/inscription');
 
        $this->addElement(	'text', 'nom', array(
            				'placeholder'      => '* Nom',
		));
		
		$this->addElement(	'text', 'prenom', array(
            				'placeholder'      => '* Prenom',
		));
		
		$this->addElement(	'text', 'pseudo', array(
            				'placeholder'      => '* Pseudonyme',
		));
		
		$this->addElement('text', 'email', array(
            'placeholder'      => 'Adresse mail',
            'validators' => array(
                'EmailAddress',
            )
        ));

        $this->addElement('password', 'password', array(
            'placeholder'      => '* Mot de passe',
		));
		
		$this->addElement('password', 'confirmPassword', array(
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
		        "font" => APPLICATION_PATH."/../public_html/fonts/DejaVuSans.ttf",
				"height" => 100,
				"width" => 190,
				"fontSize" => 22,
				"imgDir" => APPLICATION_PATH."/../public_html/captcha/",
				"imgUrl" => "/captcha/",
		)));
 
        $this->addElement('button', 'Inscription', array(
            'type'          => 'submit',
            'buttonType'    => 'success',
            'name'			=> 'inscription',
        ));
    }
}