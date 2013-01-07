<?php

class Application_Form_Upload extends Twitter_Bootstrap_Form_Inline
{
    public function init()
    {
			
		$this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');
		
       	$this->setMethod('post');
		$this->setAttrib('action', '/interface/upload');
		
		$fichier = new Zend_Form_Element_File('fichier');
		$fichier->setLabel('Sélectionnez un fichier :');
		$fichier->addValidator('Size', false, 104857600);
		$this->addElement($fichier, 'fichier');
		
		$this->addElement('button', 'submit', array(
            'label'         => 'Télécharger le fichier',
            'type'          => 'submit',
            'buttonType'    => 'success',
            'icon'          => 'ok',
            'escape'        => false
        ));
    }
}