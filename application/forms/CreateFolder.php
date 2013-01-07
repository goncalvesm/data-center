<?php

class Application_Form_CreateFolder extends Twitter_Bootstrap_Form_Inline
{
    public function init()
    {
		$this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');	
		
       	$this->setMethod('post');
		$this->setAttrib('action', '/interface/creer-dossier');
		
		$this->addElement(	'text', 'nom_dossier', array(
            				'label'      => 'Nom du dossier :'
		));
		
		$this->addElement('button', 'submit', array(
            'label'         => 'Créer le dossier',
            'type'          => 'submit',
            'buttonType'    => 'success',
            'icon'          => 'ok',
            'escape'        => false,
            'name'			=> 'validerCreation'
        ));
    }
}