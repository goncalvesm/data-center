<?php

class AdminController extends Zend_Controller_Action {
		
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
		
		$this->_helper->layout->setLayout('admin');
		
		$user = $this->_session->get('utilisateur');
		if(is_array($user)){
			$this->view->pseudo = $user['pseudo'];
			if($user['droit'] == "utilisateur"){
				$this->_session->set('erreur', 'Vous n\'avez pas accés à cette fonctionnalité');
				$this->_helper->redirector('interface', 'index');
			}
		} else {
			$this->_session->set('erreur', 'Vous devez vous connecter pour utiliser les fonctionnalités');
			$this->_helper->redirector('index', 'index');
		}
		
		//Envois du message stocké en session à la vue
		$this->view->message = $this->_session->get("message");
		$this->_session->set('message', '');
		
		//Envois de l'erreur stockée en session à la vue
		$this->view->erreur = $this->_session->get("erreur");
		$this->_session->set('erreur', '');
	}

	public function indexAction() {
		$utilisateurs = $this->_sqlite->execute("SELECT * FROM utilisateurs");
			
		$contenu = "";
		$contenu .= "	<table class='table table-striped table-hover'>
							<thead>
								<tr>
									<th>Pseudo</th>
									<th>Droit</th>
									<th>Formule</th>
									<th>Espace disponnible</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>";
							
		foreach ($utilisateurs as $value) {
			//Calcule de l'espace de stockage restant
			$formule = $value['formule'];
			$espaceOccupe = $this->_sqlite->execute("SELECT SUM(taille) AS total FROM fichiers WHERE utilisateur='".$value['pseudo']."'");
			$espaceOccupe = round((($espaceOccupe[0]['total']) / 1024)/1024, 2);
			$espaceRestant = $formule-$espaceOccupe;
			$poucentage = (int)($espaceOccupe*100)/$formule;
			$pourcentage = (string)$poucentage."%";
			
			$contenu .= "	<tr>
								<td>".$value['pseudo']."</td>
								<td>".$value['droit']."</td>
								<td>".$value['formule']." Mo</td>
								<td class='centrer'>";
								if($value['droit'] == "utilisateur"){
									$contenu .= "<div class=\"progress progress-striped active\">
									  	<div class=\"bar bar-success\" style=\"width: ".$pourcentage.";\"></div>
									</div>
									".$espaceRestant." Mo Restant";
								} else {
									$contenu .= "<b>desactivé</b>";
								}
			$contenu .=	"		</td>
								<td>
									<a href=\"/admin/supprimer-utilisateur?id=".$value['pseudo']."\"><img src=\"/images/supp_user.png\" title=\"Supprimer l'utilisateur\" /></a>
									<a href=\"/admin/editer-utilisateur?id=".$value['pseudo']."\"><img src=\"/images/edit_user.png\" title=\"Modifier la formule\" /></a>
								</td>
							</tr>";	
		}

		$contenu .= "	</tbody>
					</table>";
		
		$this->view->contenu = $contenu;
	}
	
	public function logoutAction() {
		$result = $this->_auth->logout();
		
		$this->_helper->redirector('index', 'index');
	}
	
	public function editerUtilisateurAction() {
		$params = $this->getAllParams();
		
		if(isset($params['id'])){
			//Envois du formulaire de changemeent d'option à la vue
			$changementOption = new Application_Form_ChangementOption();
			$this->view->changementOption = $changementOption;
			
			$pseudo = $params['id'];
			
			$user = $this->_sqlite->execute("SELECT * FROM utilisateurs WHERE pseudo='".$pseudo."'");
			$user = $user[0];
			
			$this->view->formule = $user['formule'];
			$this->view->changementOption = $changementOption;
			$this->view->user = $pseudo;
		} else {
			$this->_session->set('erreur', 'Vous ne pouvez pas editer un utilisateur sans fournir son pseudo');
			$this->_helper->redirector('index', 'admin');
		}
	}
	
	public function supprimerUtilisateurAction() {
		$params = $this->getRequest()->getParams();
		
		if(isset($params['id'])){
			$pseudo = $params['id'];
		} else {
			$this->_session->set('erreur', 'Vous ne pouvez pas supprimer un utilisateur sans fournir son pseudo');
			$this->_helper->redirector('index', 'admin');
		}
		
		$root = $this->_sqlite->execute("SELECT * FROM dossiers WHERE utilisateur='".$pseudo."' AND root='1'");
		
		if(is_array($root)){
			if(is_array($this->_sqlite->execute("DELETE FROM utilisateurs WHERE pseudo='".$pseudo."'"))){
				if(is_array($this->_sqlite->execute("DELETE FROM dossiers WHERE utilisateur='".$pseudo."'"))){
					if(is_array($this->_sqlite->execute("DELETE FROM fichiers WHERE utilisateur='".$pseudo."'"))){
						if($this->_rrmdir(APPLICATION_PATH."/../data/".$root[0]['chemin'])) {
							$this->_session->set('message', 'utilisateur supprimé avec succés');
							$this->_helper->redirector('index', 'admin');
						} else {
							$this->_session->set('erreur', 'Erreur lors de la suppression des fichiers de l\'utilisateur sur le serveur');
							$this->_helper->redirector('index', 'admin');
						}
					} else {
						$this->_session->set('erreur', 'Erreur lors de la suppression des fichiers de l\'utilisateur en base de données');
						$this->_helper->redirector('index', 'admin');
					}
				} else {
					$this->_session->set('erreur', 'Erreur lors de la suppression des dossiers de l\'utilisateur en base de données');
					$this->_helper->redirector('index', 'admin');
				}
			} else {
				$this->_session->set('erreur', 'Erreur lors de la suppression de l\'utilisateur en base de données');
				$this->_helper->redirector('index', 'admin');
			}
		} else {
			$this->_session->set('erreur', 'Erreur lors de la récupération du fichier racine de l\'utilisateur');
			$this->_helper->redirector('index', 'admin');
		}
	}

	public function changerFormuleAction() {
		$params = $this->getRequest()->getParams();
		
		if(isset($params['pseudo'])){
			$pseudo = $params['pseudo'];
		} else {
			$this->_session->set("erreur", "Vous ne pouvez pas changer la formule d'un utilisateur sans fournir son pseudo");
			$this->_helper->redirector->gotoUrl("/admin/index");
		}
		
		if($params['bootstrap']['dixMo'] == 1) {
			if(is_array($this->_sqlite->execute("UPDATE utilisateurs SET 'formule'='10' WHERE pseudo='".$user['pseudo']."'"))){
				$mail = new Zend_Mail();
				$mail->setBodyText('Vôtre compte possède desormais 10 Mo d\'espace de stockage');
				$mail->setFrom('contact@datacenter.fr', 'Administrateur datacenter');
				$mail->addTo('mickael.goncalves@webtales.fr', 'utilisateur');
				$mail->setSubject('Changement de formule');
				$mail->send();
				
				$this->_session->set("message", "Ce compte possède désormais 10 Mo d'espace de stockage !");
			} else {
				$this->_session->set("erreur", "Erreur lors du changement de la formule en base de données");
			}
		} elseif($params['bootstrap']['centMo'] == 1) {
			if(is_array($this->_sqlite->execute("UPDATE utilisateurs SET 'formule'='100' WHERE pseudo='".$user['pseudo']."'"))){
				$mail = new Zend_Mail();
				$mail->setBodyText('Vôtre compte possède desormais 100 Mo d\'espace de stockage');
				$mail->setFrom('contact@datacenter.fr', 'Administrateur datacenter');
				$mail->addTo('mickael.goncalves@webtales.fr', 'utilisateur');
				$mail->setSubject('Changement de formule');
				$mail->send();
				
				$this->_session->set("message", "Ce compte possède désormais 100 Mo d'espace de stockage !");
			} else {
				$this->_session->set("erreur", "Erreur lors du changement de la formule en base de données");
			}
		}
		
		$this->_helper->redirector->gotoUrl("/admin/index");
	}
}
