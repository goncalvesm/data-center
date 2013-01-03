<?php

class AdminController extends Zend_Controller_Action {
	
	public function init() {
		$this->_auth = new Zend_Perso_Authentification;
		$this->_sqlite = new Zend_Perso_Sqlite;
		$this->_session = new Zend_Perso_Session;
		
		if($this->_session->get('connecte') != true){
			$this->_helper->redirector('index', 'index');
		}
		
		$this->_helper->layout->setLayout('admin');
		
		$user = $this->_session->get('utilisateur');
		$this->view->pseudo = $user['pseudo'];
		if($user['droit'] == "utilisateur"){
			$this->_helper->redirector('index', 'index');
		}
	}

	public function indexAction() {
		$utilisateurs = $this->_sqlite->execute("SELECT * FROM utilisateurs");
			
		$contenu = "";
		$contenu .= "	<table class='tableau'>
							<tr>
								<th class='nom'>Nom</th>
								<th class='prenom'>Prenom</th>
								<th class='pseudo'>Pseudo</th>
								<th class='formule'>Formule</th>
								<th class='espaceDispo'>Espace disponnible</th>
								<th class='actions'>Actions</th>
							</tr>";
							
		foreach ($utilisateurs as $value) {
			//Calcule de l'espace de stockage restant
			$formule = $value['formule'];
			$espaceOccupe = $this->_sqlite->execute("SELECT SUM(taille) AS total FROM fichiers WHERE utilisateur='".$value['pseudo']."'");
			$espaceOccupe = round((($espaceOccupe[0]['total']) / 1024)/1024, 2);
			$espaceRestant = $formule-$espaceOccupe;
			
			$contenu .= "	<tr>
								<td>".$value['nom']."</td>
								<td>".$value['prenom']."</td>
								<td>".$value['pseudo']."</td>
								<td>".$value['formule']."</td>
								<td>".$espaceRestant."</td>
								<td><img src='/images/supp_user.png' /></td>
							</tr>";	
		}

		$contenu .= "</table>";
		
		$this->view->contenu = $contenu;
	}
	
	public function logoutAction() {
		$result = $this->_auth->logout();
		
		$this->_helper->redirector('index', 'index');
	}
}
