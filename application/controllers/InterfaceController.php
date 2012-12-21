<?php

class InterfaceController extends Zend_Controller_Action {

	protected $_auth;
	
	protected $_sqlite;
	
	protected $_session;
	
	protected $_contenu = "";
	
	protected $_chemin = "";
	
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
        $dossiers = $this->_sqlite->execute("SELECT * FROM dossiers WHERE dossierParent='".$dossierParent."' OR id='".$dossierParent."'");
		
		foreach ($dossiers as $value) {
			$fichiers = $this->_sqlite->execute("SELECT * FROM fichiers WHERE dossierParent='".$value['id']."'");
			
			$this->_contenu .= "<tr>";
			if($value['root'] == 1){
				$this->_contenu .= 	"<td colspan='3'><img src='/images/root.png'/>".$value['nom']." (root)</td>";
			} else {
				$this->_contenu .= 	"<td colspan='3'><img src='/images/dossier.png'/>".$value['nom']."</td>";
			}
			
			$this->_contenu	.= 		"<td>".$value['dateCreation']."</td>
								</tr>";
			foreach ($fichiers as $subvalue) {
				$this->_contenu .= "<tr>
										<td><img src='/images/doc.png'/><a href='/interface/download?path=".APPLICATION_PATH."/../data/".$value['chemin'].$subvalue['nom']."'>".$subvalue['nom']."</a></td>
										<td>".round(($subvalue['taille'] / 1024)/1024, 2)." Mo</td>
										<td>".$subvalue['type']."</td>
										<td>".$subvalue['dateCreation']."</td>
									</tr>";
			}
			
			if(!is_null($value['dossierParent'])){
				$this->_getFiles($value['dossierParent']);
			}
		}
	}
	
	protected function _getChemin($idDossier) {
		$dossier = $this->_sqlite->execute("SELECT * FROM dossiers WHERE id='".$idDossier."'");
		
		$this->_chemin = "".$dossier[0]['nom']."/".$this->_chemin;
		
		if($dossier[0]['dossierParent'] != ""){
			$this->_getChemin($dossier[0]['dossierParent']);
		}
		
		return $this->_chemin;
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
		$this->view->message = $this->_session->get("message");
		$this->_session->set('message', '');
		
		//$this->_helper->actionStack('index', 'interface', array());
		$upload = new Application_Form_Upload();
		//$this->_helper->ViewRenderer->setResponseSegment('upload');
		$this->view->upload = $upload;
		
		$user = $this->_session->get('utilisateur');
		
		$root = $this->_sqlite->execute("SELECT * FROM dossiers WHERE utilisateur='".$user['pseudo']."' AND root='1'");
		
		$this->_contenu .= "	<table class='tableau'>
									<tr>
										<th class='nom'>Nom</th>
										<th class='taille'>Taille</th>
										<th class='type'>Type</th>
										<th class='date'>Date d'upload</th>
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
		
		if(isset($_FILES['fichier']) && $_FILES['fichier']['name']!=""){
			$cheminFichier = $_FILES['fichier']['name'];
			$nomFichier = basename($cheminFichier);
			if(!strpos($nomFichier, "'")){
				
			}
			$typeFichier = strrchr($nomFichier,'.');
			if($typeFichier != false) {
				$typeFichier = substr($typeFichier,1);
			} else {
				$typeFichier = "unknow";
			}
			$tailleFichier = $_FILES['fichier']['size'];
			$dossierParent = $_POST['dossier'];
		
			$adapter = new Zend_File_Transfer_Adapter_Http();
	
			$adapter->setDestination(APPLICATION_PATH.'/../data/'.$user['pseudo']);
			 
			if (!$adapter->receive()) {
			    $this->_session->set("message", $adapter->getMessages());
			} else {
				$resultat = $this->_sqlite->execute("SELECT * FROM fichiers WHERE nom='".$nomFichier."' AND dossierParent='".$dossierParent."'");
				if(count($resultat)>0){
					$requete = "UPDATE fichiers SET 'taille'='".$tailleFichier."', 'dateCreation'='".date("d/m/y")."' WHERE nom='".$nomFichier."' AND dossierParent='".$dossierParent."')";
					
					$this->_session->set("message", "Upload mis à jour");
				} else {
					$requete = "INSERT INTO fichiers ('nom','taille','type','utilisateur','dossierParent', 'dateCreation') VALUES ('".$nomFichier."','".$tailleFichier."','".$typeFichier."','".$user['pseudo']."','".$dossierParent."','".date("d/m/y")."')";
					$this->_sqlite->execute($requete);
					
					$this->_session->set("message", "Upload réussi");
				}
			}
		} else {
			$this->_session->set("message", "Aucun fichier specifié");
		}

		$this->_helper->redirector('index', 'interface');
	}
	
	public function suppressionAction() {
		
	}
}
