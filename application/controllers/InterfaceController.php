<?php

class InterfaceController extends Zend_Controller_Action {
	
	/**
	 * Variable pour l'appel des methodes d'authentification
	 */
	protected $_auth;
	
	/**
	 * Variable pour l'appel des methodes sqlite
	 */
	protected $_sqlite;
	
	/**
	 * Variable pour l'appel des methodes de session
	 */
	protected $_session;
	
	/**
	 * Variable contenant le tableau à afficher dans l'index
	 */
	protected $_contenu = "";
	
	/**
	 * Variable utilisée par la methode recursive _getChemin pour stocker le chemin d'un dossier
	 */
	protected $_chemin = "";
	
	
	/**
	 * Fonction permetant à l'utilisateur de télécharger un fichier
	 * 
	 * @param filepath string Contient le chemin du fichier à télécharger
	 */
	protected function _download($filepath)
    {
    	//Récuperation des informations sur le fichier
		$file = basename($filepath);
    	$filesize = filesize($filepath);
        $filemd5 = md5_file($filepath);
 
        // Gestion du cache
        header('Pragma: public');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: must-revalidate, pre-check=0, post-check=0, max-age=0');
        // Informations sur le contenu à envoyer
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
	
	/**
	 * Fonction qui récupère les fichiers et dossiers enfants d'un dossier parent donné en parametre
	 * 
	 * @param dossierParent integer Contient l'id du dossier parent
	 */
	protected function _getFiles($dossierParent){
		//Lecture en base des dossiers enfants du dossier parent donné en paramètre
        $dossiers = $this->_sqlite->execute("SELECT * FROM dossiers WHERE dossierParent='".$dossierParent."'");
		
		//Récuperation des fichiers contenu dans le dossier parent
		$fichiers = $this->_sqlite->execute("SELECT * FROM fichiers WHERE dossierParent='".$dossierParent."'");
		
		if(count($dossiers)>0 || count($fichiers)>0){
			//Traitement individuel des données récupérées afin d'envoyer un affichage à la vue
			foreach ($dossiers as $value) {
				$this->_contenu .= "<tr>
								 		<td colspan='3'><img src='/images/dossier.png'/><a href='/interface/index?parent=".$value['id']."'>".$value['nom']."</a></td>
										<td>".$value['dateCreation']."</td>
									</tr>";
			}
	
			//Mise en forme des fichiers
			foreach ($fichiers as $subvalue) {
				$dossierParent = $this->_sqlite->execute("SELECT * FROM dossiers WHERE id='".$subvalue['dossierParent']."'");
				
				$this->_contenu .= "<tr>
										<td><img src='/images/doc.png'/><a href='/interface/download?path=".APPLICATION_PATH."/../data/".$dossierParent[0]['chemin'].$subvalue['nom']."'>".$subvalue['nom']."</a></td>
										<td>".round(($subvalue['taille'] / 1024)/1024, 2)." Mo</td>
										<td>".$subvalue['type']."</td>
										<td>".$subvalue['dateCreation']."</td>
									</tr>";
			}
		} else {
			$this->_contenu .= "<tr>
									<td colspan=\"4\">Dossier vide</td>
								</tr>";
		}
	}
	
	/**
	 * Fonction permetant de récuperer le chemin d'un dossier grace à son id
	 * 
	 * @param idDossier integer Contient l'id du dossier
	 * 
	 * @return Chemin du dossier
	 */
	/*protected function _getChemin($idDossier) {
		$dossier = $this->_sqlite->execute("SELECT * FROM dossiers WHERE id='".$idDossier."'");
		
		$this->_chemin = "".$dossier[0]['nom']."/".$this->_chemin;
		
		if($dossier[0]['dossierParent'] != ""){
			$this->_getChemin($dossier[0]['dossierParent']);
		}
		
		return $this->_chemin;
	}*/

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
		//Envois du message stocké en session à la vue
		$this->view->message = $this->_session->get("message");
		$this->_session->set('message', '');
		
		//Envois de l'erreur stockée en session à la vue
		$this->view->erreur = $this->_session->get("erreur");
		$this->_session->set('erreur', '');
		
		//Envois du formulaire d'upload a la vue
		$upload = new Application_Form_Upload();
		$this->view->upload = $upload;
		
		//Envois du formulaire de création de dossier à la racine
		$createFolder = new Application_Form_CreateFolder();
		$this->view->createFolder = $createFolder;
		
		//Récupération des infos de l'utilisateur
		$user = $this->_session->get('utilisateur');
		
		//Définition du dossier racine en fonction du parametre en get
		if(!isset($_GET['parent'])){
			$root = $this->_sqlite->execute("SELECT * FROM dossiers WHERE utilisateur='".$user['pseudo']."' AND root='1'");
			$root = $root[0]['id'];
		} else {
			$root = $_GET['parent'];
		}
		
		$this->_session->set('root', $root);
		
		//création du tableau contenant les dossiers et les fichiers de l'utilisateur
		$this->_contenu .= "	<table class='tableau'>
									<tr>
										<th class='nom'>Nom</th>
										<th class='taille'>Taille</th>
										<th class='type'>Type</th>
										<th class='date'>Date d'upload</th>
									</tr>";
		$this->_getFiles($root);
		$this->_contenu .= "	</table>";
		
		//envois du tableau à la vue
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
		//Définition du dossier racine en fonction du parametre en get
		$root = $this->_session->get('root');
		
		if(isset($_FILES['fichier']) && $_FILES['fichier']['name']!=""){
			$cheminFichier = $_FILES['fichier']['name'];
			$nomFichier = basename($cheminFichier);
			if(!strpos($nomFichier, "%22") && !strpos($nomFichier, "'")){
				$typeFichier = strrchr($nomFichier,'.');
				if($typeFichier != false) {
					$typeFichier = substr($typeFichier,1);
				} else {
					$typeFichier = "unknown";
				}
				$tailleFichier = $_FILES['fichier']['size'];
				
				$chemin = $this->_sqlite->execute("SELECT * FROM dossiers WHERE id=".$root);
				$chemin = $chemin[0]['chemin'];
				
				$adapter = new Zend_File_Transfer_Adapter_Http();
		
				$adapter->setDestination(APPLICATION_PATH.'/../data/'.$chemin);
				 
				if (!$adapter->receive()) {
				    $this->_session->set("message", $adapter->getMessages());
				} else {
					$resultat = $this->_sqlite->execute("SELECT * FROM fichiers WHERE nom='".$nomFichier."' AND dossierParent='".$root."'");
					if(count($resultat)>0){
						$requete = "UPDATE fichiers SET 'taille'='".$tailleFichier."', 'dateCreation'='".date("d/m/y")."' WHERE nom='".$nomFichier."' AND dossierParent='".$root."')";
						
						$this->_session->set("message", "Upload mis à jour");
					} else {
						$requete = "INSERT INTO fichiers ('nom','taille','type','utilisateur','dossierParent', 'dateCreation') VALUES ('".$nomFichier."','".$tailleFichier."','".$typeFichier."','".$user['pseudo']."','".$root."','".date("d/m/y")."')";
						$this->_sqlite->execute($requete);
						
						$this->_session->set("message", "Upload réussi");
					}
				}
			} else {
				$this->_session->set("erreur", "Les caractères suivant sont interdits dans les noms de fichier ', \"");
			}
		} else {
			$this->_session->set("erreur", "Aucun fichier specifié");
		}

		$this->_helper->redirector->gotoUrl("/interface/index?parent=".$root);
	}
	
	public function creerDossierAction() {
		$user = $this->_session->get('utilisateur');
		$idDossierCourant = $this->_session->get('root');
		
		if(isset($_POST['validerCreation'])){
			$nomDossier = $_POST['nom_dossier'];
			
			$dossierCourant = $this->_sqlite->execute("SELECT * FROM dossiers WHERE id=".$idDossierCourant);
			
			$chemin = $dossierCourant[0]['chemin'].$nomDossier."/";
			
			$requete = "INSERT INTO dossiers ('nom', 'chemin', 'utilisateur','root', 'dateCreation', 'dossierParent') VALUES ('".$nomDossier."', '".$chemin."', '".$user['pseudo']."','0', '".date("d/m/y")."', '".$dossierCourant[0]['id']."')";
			$this->_sqlite->execute($requete);
			
			mkdir(APPLICATION_PATH."/../data/".$chemin);
		}
		
		$this->_helper->redirector->gotoUrl("/interface/index?parent=".$idDossierCourant);
	}

	public function supprimerDossier() {
		
	}
}
