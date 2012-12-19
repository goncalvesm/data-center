<?php

class Application_Form_Upload extends Zend_Form
{
    public function init()
    {
       	$this->setMethod('post');
		$this->setAttrib('action', '/index/upload');
		
		$element = new Zend_Form_Element_File('fichier');
		$element->setLabel('Uploadez un fichier : ');
		$element->addValidator('Size', false, 104857600);
		$this->addElement($element, 'fichier');
		
		 $this->addElement('submit', 'upload', array(
            'ignore'   => true,
            'label'    => 'Valider',)
		);
		
		$inscription = new Zend_Form_Element_Button('Fermer');
		$inscription	->setAttrib('onCLick', "$('#inscription').slideUp(500);");
		$this->addElement($inscription);
    }
}