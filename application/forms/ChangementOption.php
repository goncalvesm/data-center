<?php

class Application_Form_ChangementOption extends Twitter_Bootstrap_Form_Inline
{
    public function init()
    {
		$this->setIsArray(true);
        $this->setElementsBelongTo('bootstrap');

        $this->_addClassNames('well');
		
		$this->addElement( 	'checkbox', 'dixMo', array(
							'prepend' => '10 Mo',
							'id'	 => 'dixMo',
							'onClick' => '$(\'#centMo\').attr("checked", false);',
		));
		
		$this->addElement( 	'checkbox', 'centMo', array(
							'prepend' => '100 Mo',
							'id'	 => 'centMo',
							'onClick' => '$(\'#dixMo\').attr("checked", false);',
		));
		
		$this->addElement('button', 'submit', array(
            'label'         => 'Changer de formule !',
            'type'          => 'submit',
            'buttonType'    => 'success',
            'icon'          => 'ok',
            'escape'        => false,
        ));
    }
}