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
								 		<td colspan='3'><img src='/images/dossier.png' title='Dossier'><a href='/interface/index?parent=".$value['id']."'>".$value['nom']."</a></td>
										<td>".$value['dateCreation']."</td>
										<td><a href='/interface/supprimer-dossier?id=".$value['id']."'><img src='/images/supp_dossier.png' title='Supprimer le dossier'></a></td>
									</tr>";
			}
	
			//Mise en forme des fichiers
			foreach ($fichiers as $subvalue) {
				$dossierParent = $this->_sqlite->execute("SELECT * FROM dossiers WHERE id='".$subvalue['dossierParent']."'");
				
				$this->_contenu .= "<tr>
										<td><img src='/images/doc.png' title='Fichier'><a href='/interface/download?path=".APPLICATION_PATH."/../data/".$dossierParent[0]['chemin'].$subvalue['nom']."'>".$subvalue['nom']."</a></td>
										<td>".round(($subvalue['taille'] / 1024)/1024, 2)." Mo</td>
										<td>".$subvalue['type']."</td>
										<td>".$subvalue['dateCreation']."</td>
										<td><a href='/interface/supprimer-fichier?id=".$subvalue['id']."'><img src='/images/supp_fichier.png' title='Supprimer le fichier'></a></td>
									</tr>";
			}
		} else {
			$this->_contenu .= "<tr>
									<td colspan=\"5\">Dossier vide</td>
								</tr>";
		}
	}

	protected function _rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir"){
						$this->_rrmdir($dir."/".$object); 
					} else {
						unlink($dir."/".$object);
					}
				}
			}
			reset($objects);
			return rmdir($dir);
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
		
		$user = $this->_session->get('utilisateur');
		if(is_array($user)){
			$this->view->pseudo = $user['pseudo'];
			$this->view->user = $user;
			
			if($user['droit'] == "admin"){
				$this->_helper->redirector('index', 'admin');
			}
		}
	}

	public function indexAction() {
		$twitter = new Application_Form_Twitter();
		$this->view->twitter = $twitter;
		
		//Envois du formulaire d'upload a la vue
		$upload = new Application_Form_Upload();
		$this->view->upload = $upload;
		
		//Envois du formulaire de création de dossier à la racine
		$createFolder = new Application_Form_CreateFolder();
		$this->view->createFolder = $createFolder;
		
		//Envois du formulaire de changemeent d'option à la vue
		$changementOption = new Application_Form_ChangementOption();
		$this->view->changementOption = $changementOption;
		
		//Récupération des infos de l'utilisateur
		$user = $this->_session->get('utilisateur');
		
		//Calcule de l'espace de stockage restant
		$formule = $user['formule'];
		$espaceOccupe = $this->_sqlite->execute("SELECT SUM(taille) AS total FROM fichiers WHERE utilisateur='".$user['pseudo']."'");
		$espaceOccupe = round(($espaceOccupe[0]['total'] / 1024)/1024, 2);
		$espaceRestant = $formule-$espaceOccupe;
		$this->view->espaceRestant = $espaceRestant;
		
		//Affichage du formulaire de changement de formule en fonction de la valeur en session
		if($this->_session->get('changerOption')){
			$this->view->changerOption = true;
			
			$this->_session->set('changerOption', false);
		}
		
		//Définition du dossier racine en fonction du parametre en get
		if(!isset($_GET['parent'])){
			$root = $this->_sqlite->execute("SELECT * FROM dossiers WHERE utilisateur='".$user['pseudo']."' AND root='1'");
			$root = $root[0]['id'];
		} else {
			$root = $_GET['parent'];
			$folders = $this->_sqlite->execute("SELECT id FROM dossiers WHERE utilisateur='".$user['pseudo']."'");
			
			foreach ($folders as $key => $value) {
				foreach ($value as $subValue) {
					$foldersArray[] = $subValue;
				}	
			}

			if(!in_array($root, $foldersArray)){
				$root = $this->_sqlite->execute("SELECT * FROM dossiers WHERE utilisateur='".$user['pseudo']."' AND root='1'");
				$root = $root[0]['id'];
				$this->_session->set('erreur', 'Vous ne pouvez pas acceder à ce dossier');
			}
		}
		
		$this->_session->set('root', $root);
		
		//Envois du message stocké en session à la vue
		$this->view->message = $this->_session->get("message");
		$this->_session->set('message', '');
		
		//Envois de l'erreur stockée en session à la vue
		$this->view->erreur = $this->_session->get("erreur");
		$this->_session->set('erreur', '');
		
		//création du tableau contenant les dossiers et les fichiers de l'utilisateur
		$this->_contenu .= "	<table class='table table-striped table-hover'>
									<thead>
										<tr>
											<th class='nom'>Nom</th>
											<th class='taille'>Taille</th>
											<th class='type'>Type</th>
											<th class='date'>Date d'upload</th>
											<th class='actions'>Actions</th>
										</tr>
									</thead>
									<tbody>";
		$this->_getFiles($root);
		
		$this->_contenu .= "		</tbody>
								</table>";
		
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
				
				//Calcule de l'espace de stockage restant
				$formule = $user['formule'];
				$espaceOccupe = $this->_sqlite->execute("SELECT SUM(taille) AS total FROM fichiers WHERE utilisateur='".$user['pseudo']."'");
				$espaceOccupe = round((($espaceOccupe[0]['total']+$tailleFichier) / 1024)/1024, 2);
				$espaceRestant = $formule-$espaceOccupe;
				
				if($espaceRestant>0){
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
					$this->_session->set("erreur", "Vous ne disposez pas d'assez d'espace de stockage pour uploader ce fichier !");
					
					$this->_session->set('changerOption', true);
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
		$idDossierCourant = $this->_session->get('root');
		$params = $this->getRequest()->getParams();
		
		if(isset($params['bootstrap']['validerCreation'])){
			$user = $this->_session->get('utilisateur');
			
			$nomDossier = $params['bootstrap']['nom_dossier'];
			
			$dossierCourant = $this->_sqlite->execute("SELECT * FROM dossiers WHERE id=".$idDossierCourant);
			
			$chemin = $dossierCourant[0]['chemin'].$nomDossier."/";
			
			if(mkdir(APPLICATION_PATH."/../data/".$chemin)){
				$requete = "INSERT INTO dossiers ('nom', 'chemin', 'utilisateur','root', 'dateCreation', 'dossierParent') VALUES ('".$nomDossier."', '".$chemin."', '".$user['pseudo']."','0', '".date("d/m/y")."', '".$dossierCourant[0]['id']."')";

				if(is_array($this->_sqlite->execute($requete))){
					$this->_session->set("message", "Dossier créé avec succés");
				} else {
					$this->_session->set("erreur", "Erreur serveur lors de la création du dossier");
				}
			} else {
				$this->_session->set("erreur", "Erreur serveur lors de la création du dossier");
			}
		}
		
		$this->_helper->redirector->gotoUrl("/interface/index?parent=".$idDossierCourant);
	}

	public function supprimerDossierAction() {
		$idDossierCourant = $this->_session->get('root');
		$user = $this->_session->get('utilisateur');
		
		if(isset($_GET['id'])){
			$idDossier = $_GET['id'];
			$folders = $this->_sqlite->execute("SELECT id FROM dossiers WHERE utilisateur='".$user['pseudo']."'");
			
			foreach ($folders as $key => $value) {
				foreach ($value as $subValue) {
					$foldersArray[] = $subValue;
				}	
			}

			if(!in_array($idDossier, $foldersArray)){
				$this->_session->set('erreur', 'Vous ne pouvez pas supprimer un dossier qui ne vous appartient pas');
				$this->_helper->redirector->gotoUrl("/interface/index?parent=".$idDossierCourant);
			}
			
			$dossier = $this->_sqlite->execute("SELECT * FROM dossiers WHERE id='".$idDossier."'");
			$dossier = $dossier[0];
			
			$resultat = $this->_rrmdir(APPLICATION_PATH."/../data/".$dossier['chemin']);

			if($resultat) {
				$dossiersEnfant = $this->_sqlite->execute("SELECT * FROM dossiers WHERE id='".$idDossier."' OR dossierParent='".$idDossier."'");

				foreach ($dossiersEnfant as $value) {
					$this->_sqlite->execute("DELETE FROM fichiers WHERE dossierParent='".$value['id']."'");
				}

				$resultat = $this->_sqlite->execute("DELETE FROM dossiers WHERE id='".$idDossier."' OR dossierParent='".$idDossier."'");

			} else {
				$this->_session->set("erreur", "Erreur serveur lors de la suppression du dossier");
			}
		}

		$this->_helper->redirector->gotoUrl("/interface/index?parent=".$idDossierCourant);
	}
	
	public function supprimerFichierAction() {
		$idDossierCourant = $this->_session->get('root');
		$user = $this->_session->get('utilisateur');
		
		if(isset($_GET['id'])){
			$idFichier = $_GET['id'];
			
			$fichiers = $this->_sqlite->execute("SELECT id FROM fichiers WHERE utilisateur='".$user['pseudo']."'");
			
			foreach ($fichiers as $key => $value) {
				foreach ($value as $subValue) {
					$fichiersArray[] = $subValue;
				}	
			}

			if(!in_array($idFichier, $fichiersArray)){
				$this->_session->set('erreur', 'Vous ne pouvez pas supprimer un fichier qui ne vous appartient pas');
				$this->_helper->redirector->gotoUrl("/interface/index?parent=".$idDossierCourant);
			}
			
			$fichier = $this->_sqlite->execute("SELECT * FROM fichiers WHERE id='".$idFichier."'");
			
			if(count($fichier)>0){
				$dossierParent = $this->_sqlite->execute("SELECT * FROM dossiers WHERE id='".$fichier[0]['dossierParent']."'");
				$nomFichier = $fichier[0]['nom'];
				
				if(count($dossierParent)>0){
					$chemin = APPLICATION_PATH."/../data/".$dossierParent[0]['chemin'].$nomFichier;
				} else {
					$chemin = APPLICATION_PATH."/../data/".$nomFichier;
				}
				
				$resultat = unlink($chemin);
				
				if($resultat) {
					$this->_sqlite->execute("DELETE FROM fichiers WHERE id='".$idFichier."'");
				} else {
					$this->_session->set("erreur", "Erreur du serveur lors de la suppression du fichier");
				}
			} else {
				$this->_session->set("erreur", "Le fichier demandé n'existe pas ou n'est pas référencé");
			}
		}
		
		$this->_helper->redirector->gotoUrl("/interface/index?parent=".$idDossierCourant);
	}
	
	public function changerFormuleAction() {
		$idDossierCourant = $this->_session->get('root');
		$params = $this->getRequest()->getParams();
		$user = $this->_session->get('utilisateur');
		
		if($params['bootstrap']['dixMo'] == 1) {
			$this->_sqlite->execute("UPDATE utilisateurs SET 'formule'='10' WHERE pseudo='".$user['pseudo']."'");
			$user['formule'] = 10;
			$this->_session->set('utilisateur', $user);
			
			$this->_session->set("message", "Votre compte possède désormais 10 Mo d'espace de stockage !");
		} elseif($params['bootstrap']['centMo'] == 1) {
			$this->_sqlite->execute("UPDATE utilisateurs SET 'formule'='100' WHERE pseudo='".$user['pseudo']."'");
			$user['formule'] = 100;
			$this->_session->set('utilisateur', $user);
			
			$this->_session->set("message", "Votre compte possède désormais 100 Mo d'espace de stockage !");
		}
		
		$this->_helper->redirector->gotoUrl("/interface/index?parent=".$idDossierCourant);
	}
}
