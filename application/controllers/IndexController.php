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

	public function createAction(){
		$m = new Mongo("mongodb://localhost/datacenter");
        $db = $m->selectDB('datacenter');
		$collection = $m->selectCollection('datacenter', 'users');
		
		$array = array('name' => 'test');
		
		$result = $collection->insert($array);
		
		return $this->_helper->json($result);
			
		//$obj = $this->getRequest()->getParam('data');
		
		
	}

}

