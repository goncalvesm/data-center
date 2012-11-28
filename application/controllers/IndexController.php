<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
	
	public function loginAction(){
		$view = new Zend_View();
		
		$view->a = "test";
	}

}

