<?php

class InterfaceController extends Zend_Controller_Action {

	protected $_auth;
	
	protected $_sqlite;
	
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
	
	protected function _getFiles($dossierParent){
		         
        $dossiers = $this->_sqlite->execute("SELECT * FROM dossiers WHERE dossierParent='".$dossierParent."'");
		$fichiers = $this->_sqlite->execute("SELECT * FROM fichiers WHERE dossierParent='".$dossierParent."'");
		
		foreach ($dossiers as $value) {
			$this->_contenu .= "<tr>
									<td colspan='3'>".$value['nom']."</td>
									<td>".$value['dateCreation']."</td>
								</tr>";
								
			$this->_getFiles($value['dossierParent']);
		}
		foreach ($fichiers as $value) {
			$this->_contenu .= "<tr>
									<td>".$value['nom']."</td>
									<td>".$value['taille']."</td>
									<td>".$value['type']."</td>
									<td>".$value['dateCreation']."</td>
								</tr>";
		}
		
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
		
		$root = $this->_sqlite->execute("SELECT * FROM dossiers WHERE utilisateur='".$user['pseudo']."' AND root='1'");
		
		$this->_contenu = "		<table class='tableau'>
									<tr>
										<th class='nom'>Nom</th>
										<th class='taille'>Taille</th>
										<th class='type'>Type</th>
										<th class='date'>Date de création</th>
									</tr>
									<tr>
										<td colspan='3'><img src='/images/root.png' />".$user['pseudo']." (root)</td>
										<td>".$root[0]['dateCreation']."</td>
									</tr>";
		$this->_getFiles($root[0]['id']);
		$this->_contenu .= "	</table>";
		
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
		
		$dossiers = $this->_sqlite->execute("SELECT * FROM dossiers WHERE utilisateur='".$user['pseudo']."'");
		
		$this->view->dossiers .= "<select name='dossier'>";
		foreach ($dossiers as $value) {
			if($value['root'] == 1){
				$value['nom'] .= " (root)";
			}
			$this->view->dossiers .= "<option value='".$value['id']."'>".$value['nom']."</option>";
		}
		$this->view->dossiers .= "</select>";
		
		if(isset($_FILES['fichier']) && $_FILES['fichier']['name']!=""){
			$cheminFichier = $_FILES['fichier']['name'];
			$nomFichier = basename($cheminFichier);
			$typeFichier = $_FILES['fichier']['type'];
			$tailleFichier = $_FILES['fichier']['size'];
		
			$adapter = new Zend_File_Transfer_Adapter_Http();
	
			$adapter->setDestination(APPLICATION_PATH.'/../data/'.$user['pseudo']);
			 
			if (!$adapter->receive()) {
			    $this->view->message = $adapter->getMessages();
			} else {
				$requete = "INSERT INTO fichiers ('nom','chemin','taille','type','utilisateur','dossierParent') VALUES ('".$nomFichier."','".$cheminFichier."','".$tailleFichier."','".$typeFichier."','".$user['pseudo']."','1')";
				$this->_sqlite->execute($requete);
				
				$this->view->message = "Upload réussi";
			}
		}
	}
	
	public function suppressionAction() {
		
	}
}
