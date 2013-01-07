<?php

class Application_Form_ChangementOption extends Twitter_Bootstrap_Form_Inline
{
    public function init()
    {
		$this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');
		
       	$this->setMethod('post');
		$this->setAttrib('action', '/interface/changer-option');
		
		$this->addElement(	'select', 'formule', array(
            				'label'      => 'Option :',
            				'multiOptions' => array(
            					'1' => '1 Mo', 
            					'10' => '10 Mo',
            					'100' => '100 Mo',
							)
            				
		));
		
		$this->addElement('button', 'submit', array(
            'label'         => 'Changer de formule !',
            'type'          => 'submit',
            'buttonType'    => 'success',
            'icon'          => 'ok',
            'escape'        => false,
            'name'			=> 'validerChangement'
        ));
    }
}