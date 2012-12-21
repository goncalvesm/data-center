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
		
       	$this->setMethod('post');
		$this->setAttrib('action', '/interface/upload');
		
		$fichier = new Zend_Form_Element_File('fichier');
		$fichier->setLabel('Uploadez un fichier :');
		$fichier->addValidator('Size', false, 104857600);
		$this->addElement($fichier, 'fichier');
		
		$dossier = new Zend_Form_Element_Select('dossier');
		$dossier	->setAttrib('id', 'dossier')
					->addMultiOptions($dossiersSelect)
					->setLabel('Dossier de destination :');
		$this->addElement($dossier);
		
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Upload',
        ));
    }
}