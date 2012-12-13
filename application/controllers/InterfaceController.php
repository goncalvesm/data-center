<?php

class InterfaceController extends Zend_Controller_Action {

	protected $_auth;
	
	protected $_sql;
	
	protected $_session;
	
	protected $_contenu = "";
	
	protected function _getFiles($chemin){
	    $this->_contenu .= "<ul>";   
	    $folder = opendir ($chemin);
	   
	    while ($file = readdir ($folder)) {   
	        if ($file != "." && $file != "..") {           
	            $pathfile = $chemin.'/'.$file;           
	            if(filetype($pathfile) == 'dir'){
	            	$this->_contenu .= "<li class='rouge'>$file</li>"; 
	                $this->_getFiles($pathfile);               
	            } else {
	            	$this->_contenu .= "<li><a href='/interface/download/?file=$file' class='souligne vert'>$file</a></li>";
	            }
	        }       
	    }
	    closedir ($folder);    
	    $this->_contenu .= "</ul>";   
	}

	public function init() {
		$this->_auth = new Zend_Perso_Authentification;
		$this->_sqlite = new Zend_Perso_Sqlite;
		$this->_session = new Zend_Perso_Session;
		
		if($this->_session->get('connecte') != true){
			$this->_helper->redirector('index', 'index');
		}
		
		$this->_helper->layout->setLayout('interface');
	}

	public function indexAction() {
		$this->_getFiles(APPLICATION_PATH);
		
		$this->view->contenu = $this->_contenu;
	}

	public function logoutAction() {
		$result = $this->_auth->logout();
		
		$this->_helper->redirector('index', 'index');
	}
	
	public function downloadAction() {
		$file = $_GET['file'];
		
		header('Content-Type: text/html');
		header('Content-Disposition: attachment; filename="index.phtml"');
		readfile(APPLICATION_PATH.'/views/scripts/index/index.phtml');
		
		$this->_helper->redirector('index', 'interface');
	}
	
	public function uploadAction() {
		
	}
	
	public function suppressionAction() {
		
	}
}
