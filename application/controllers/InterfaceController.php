<?php

class InterfaceController extends Zend_Controller_Action {

	protected $_auth;
	
	protected $_sql;
	
	protected $_session;
	
	protected $_contenu;
	
	protected function _download($filepath)
    {
		$file = basename($filepath);
    	$filesize = filesize($filepath);
        $filemd5 = md5_file($filepath);
 
        // Gestion du cache
        header('Pragma: public');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: must-revalidate, pre-check=0, post-check=0, max-age=0');
        // Informations sur le contenu à envoyer
       // header('Content-Tranfer-Encoding: ' . $type . "\n");
        header('Content-Length: ' . $filesize);
        header('Content-MD5: ' . base64_encode($filemd5));
        header('Content-Type: application/force-download; name="' . $file . '"');
        header('Content-Disposition: attachement; filename="' . $file . '"');
        // Informations sur la réponse HTTP elle-même
        header('Date: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 1) . ' GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
        readfile($filepath);
        exit;
    }
	
	protected function _getFiles($chemin){
	    $this->_contenu .= "<ul class='decale'>";   
	    $folder = opendir ($chemin);
	   
	    while ($file = readdir ($folder)) {
	        if ($file != "." && $file != "..") {           
	            $pathfile = $chemin.'/'.$file;           
	            if(filetype($pathfile) == 'dir'){
	            	$this->_contenu .= "<li class='rouge'>$file</li>"; 
	                $this->_getFiles($pathfile);               
	            } else {
	            	$this->_contenu .= "<li><a href='/interface/download/?path=$pathfile' class='souligne vert'>$file</a></li>";
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
		$user = $this->_session->get('utilisateur');
		$this->_contenu = "<ul><li class='root'>".$user['pseudo']."</li>";
		$this->_getFiles(APPLICATION_PATH."/../data/".$user['pseudo']);
		$this->_contenu .= "</ul>";
		$this->view->contenu = $this->_contenu;
	}

	public function logoutAction() {
		$result = $this->_auth->logout();
		
		$this->_helper->redirector('index', 'index');
	}
	
	public function downloadAction() {
		if($_GET['path']){
			$path = $_GET['path'];
			
			$this->_download($path);
			
			$this->_helper->redirector('index', 'interface');
		}
	}
	
	public function uploadAction() {
		$user = $this->_session->get('utilisateur');
		$adapter = new Zend_File_Transfer_Adapter_Http();
 
		$adapter->setDestination(APPLICATION_PATH.'/../data/'.$user['pseudo']);
		 
		if (!$adapter->receive()) {
		    $messages = $adapter->getMessages();
		    echo implode("\n", $messages);
		}
	}
	
	public function suppressionAction() {
		
	}
}
