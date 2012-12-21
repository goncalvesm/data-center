<?php

class Application_Form_Upload extends Zend_Form
{
    public function init()
    {
    	$sqlite = new Zend_Perso_Sqlite;
		$session = new Zend_Perso_Session;	
			
    	$user = $session->get('utilisateur');
		
		$dossiersBdd = $sqlite->execute("SELECT * FROM dossiers WHERE utilisateur='".$user['pseudo']."'");
		$dossiersSelect = array();
		
		foreach ($dossiersBdd as $value) {
			if($value['root'] == 1){
				$value['nom'] .= " (root)";
			}
			$dossiersSelect[$value['id']] = $value['nom'];
		}
		
		//Modification du décorateur
		$this->setDecorators(
		    array(
		        'FormElements',
		        array('HtmlTag', array('tag' => 'table')),
		        'Form'
		    )
		);
		
		$decorateur = array(
		    'ViewHelper',
		    'Errors',
		    array('Description', array('tag' => 'p', 'class' => 'description')),
		    array('HtmlTag', array('tag' => 'td')),
		    array('Label', array('tag' => 'th')),
		    array(array('tr' => 'HtmlTag'), array('tag' => 'tr'))
		);
		
		$decorateurFichier = array(
		    'File',
		    'Errors',
		    array('Description', array('tag' => 'p', 'class' => 'description')),
		    array('HtmlTag', array('tag' => 'td')),
		    array('Label', array('tag' => 'th')),
		    array(array('tr' => 'HtmlTag'), array('tag' => 'tr'))
		);
		
       	$this->setMethod('post');
		$this->setAttrib('action', '/interface/upload');
		
		$fichier = new Zend_Form_Element_File('fichier');
		$fichier->setLabel('Selectionnez un fichier :');
		$fichier->addValidator('Size', false, 104857600);
		$fichier->setDecorators($decorateurFichier);
		$this->addElement($fichier, 'fichier');
		
		$dossier = new Zend_Form_Element_Select('dossier');
		$dossier->setLabel('Dossier de destination :');
		$dossier->setAttrib('id', 'dossier');
		$dossier->addMultiOptions($dossiersSelect);
		$dossier->setDecorators($decorateur);
		$this->addElement($dossier);
		
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Télécharger le fichier',
            'decorators' => array(
	            'ViewHelper',
	            array(array('td' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	            array(array('tr' => 'HtmlTag'), array('tag' => 'tr'))
	        ),
        ));
    }
}