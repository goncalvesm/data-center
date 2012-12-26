<?php

class Application_Form_ChangementOption extends Zend_Form
{
    public function init()
    {
		
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
		
       	$this->setMethod('post');
		$this->setAttrib('action', '/interface/changer-option');
		
		$this->addElement(	'text', 'option', array(
            				'label'      => 'Option :',
            				'decorators' => $decorateur,
		));
		
		$this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Changer d\'option',
            'name'	   => 'validerChangement',
            'decorators' => array(
	            'ViewHelper',
	            array(array('td' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	            array(array('tr' => 'HtmlTag'), array('tag' => 'tr'))
	        ),
        ));
    }
}