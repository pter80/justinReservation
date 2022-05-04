<?php
	date_default_timezone_set("Europe/Paris");

	/***Fichiers requis***/
	require_once('./protected/parametres.php');
	require_once('./protected/fonctions.php');
	require_once('./protected/sessions.php');

	if (empty(session_id())) {
		$session = new Session();
	}

	if (!verif_session()) {
		$sessionExpiree = true;
		require "index.php";
		exit();
	}
	else {
		if (empty($_SESSION['admin']) || $_SESSION['admin'] != 'O') {
			require "interdit.php";
			exit();
		}
	}

	$sql = "SELECT * FROM users ORDER BY nomUser, prenomUser";
	$listeUsers = req_select($sql, false);

	$message  = '';
	$numUser = 0;
	$nomUser = '';
	$prenomUser = '';
	$mailUser = '';
	$mdpUser = '';
	$adminUser = false;
	$modifMdp = false;
	
	$erreurNumUser = false;
	$erreurNomUser = false;
	$erreurPrenomUser = false;
	$erreurMailUser = false;
	$erreurMdpUser = false;
	$erreurEnreg = false;

	$listeReq = array();

	if (isset($_GET['numUser']) && is_numeric($_GET['numUser'])) {
		if ($_GET['numUser'] != 0) {
			$sql = "SELECT * FROM users WHERE numUser = :numUser";
			$param = array('numUser' => $_GET['numUser']);
			$infosUserTemp = req_select($sql, $param);
			if (!$infosUserTemp) {
				$erreurNumUser = true;
			}
			else {
				$numUser = $_GET['numUser'];
				$nomUser = $infosUserTemp[0]->nomUser;
				$prenomUser = $infosUserTemp[0]->prenomUser;
				$mailUser = $infosUserTemp[0]->mailUser;

				$sql = "SELECT * FROM admin WHERE numUser = :numUser";
				if (req_select($sql, $param)) {
					$adminUser = true;
				}
			}
		}
		else {
			$sql = "SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :nomBDD AND TABLE_NAME = :nomTable";
			$param = array(
				'nomBDD' => DB_MYSQL_NOM,
				'nomTable' => 'users'
			);
			$numUser = req_select($sql, $param)[0]->AUTO_INCREMENT;
		}

		if (empty($_GET['nomUser'])) {
			$erreurNomUser = true;
		}
		else {
			$nomUser = trim($_GET['nomUser']);
		}

		if (empty($_GET['prenomUser'])) {
			$erreurPrenomUser = true;
		}
		else {
			$prenomUser = trim($_GET['prenomUser']);
		}

		if (empty($_GET['mailUser']) || !filter_var($_GET['mailUser'], FILTER_VALIDATE_EMAIL)) {
			$erreurMailUser = true;
		}
		else {
			$sql = "SELECT * FROM users WHERE mailUser = :mailUser AND numUser <> :numUser";
			$param = array(
				'numUser' => $numUser,
				'mailUser' => $_GET['mailUser']
			);
			if (req_select($sql, $param)) {
				$erreurMailUser = true;
			}
		}
		
		if (!empty($_GET['mdp'])) {
			if (empty($_GET['mdp2'])) {
				$erreurMdpUser = true;
			}
			else {
				if ($_GET['mdp'] != $_GET['mdp2']) {
					$erreurMdpUser = true;
				}
				else {
					$modifMdp = true;
				}
			}
		}
		else {
			if ($_GET['numUser'] == 0) {
				$erreurMdpUser = true;
			}
		}

		if (!$erreurNumUser && !$erreurNomUser && !$erreurPrenomUser && !$erreurMailUser && !$erreurMdpUser) {
			$param = array(
				'numUser' => $numUser,
				'nomUser' => trim($_GET['nomUser']),
				'prenomUser' => $_GET['prenomUser'],
				'mailUser' => $_GET['mailUser']
			);
			if ($modifMdp) {
				$param['mdpUser'] = password_hash($_GET['mdp'], PASSWORD_BCRYPT);
				$sql = "INSERT INTO users (numUser, nomUser, prenomUser, mailUser, mdpUser) VALUES (:numUser, :nomUser, :prenomUser, :mailUser, :mdpUser) ON DUPLICATE KEY UPDATE nomUser = :nomUser, prenomUser = :prenomUser, mailUser = :mailUser, mdpUser = :mdpUser";
				
			}
			else {
				$sql = "INSERT INTO users (numUser, nomUser, prenomUser, mailUser) VALUES (:numUser, :nomUser, :prenomUser, :mailUser) ON DUPLICATE KEY UPDATE nomUser = :nomUser, prenomUser = :prenomUser, mailUser = :mailUser";
			}
			$listeReq[] = array($sql, $param);
			
			$sql = "DELETE FROM admin WHERE numUser = :numUser";
			$param = array('numUser' => $numUser);
			$listeReq[] = array($sql, $param);
			
			if (isset($_GET['admin'])) {
				$sql = "INSERT INTO admin (numUser) VALUES (:numUser)";
				$listeReq[] = array($sql, $param);
			}

			if (req_maj_transaction(...$listeReq)) {
				$message = "Enregistrement effectué";
				$sql = "SELECT * FROM users ORDER BY nomUser";
				$listeUsers = req_select($sql, false);

				$sql = "SELECT * FROM users WHERE numUser = :numUser";
				$param = array('numUser' => $numUser);
				$infosUserTemp = req_select($sql, $param);
				$numUser = $_GET['numUser'];
				$nomUser = $infosUserTemp[0]->nomUser;
				$prenomUser = $infosUserTemp[0]->prenomUser;
				$mailUser = $infosUserTemp[0]->mailUser;

				$sql = "SELECT * FROM admin WHERE numUser = :numUser";
				if (req_select($sql, $param)) {
					$adminUser = true;
				}
			}
			else {
				$erreurEnreg = true;
				$message = "Erreur d'enregistrement";
			}
		}
		else {
			if ($erreurNumUser) {
				$message = "Utilisateur inconnu";
			}
			else {
				$message = "Veuillez saisir tous les champs obligatoires";
			}
		}
	}
	else {
		if (isset($_GET['user']) && is_numeric($_GET['user'])) {
			$numUser = $_GET['user'];
			$sql = "SELECT * FROM users WHERE numUser = :numUser";
			$param = array('numUser' => $numUser);
			$infosUserTemp = req_select($sql, $param);
			if ($infosUserTemp) {
				$nomUser = $infosUserTemp[0]->nomUser;
				$prenomUser = $infosUserTemp[0]->prenomUser;
				$mailUser = $infosUserTemp[0]->mailUser;
	
				$sql = "SELECT * FROM admin WHERE numUser = :numUser";
				if (req_select($sql, $param)) {
					$adminUser = true;
				}
			}
			else {
				$numUser = 0;
			}
		}
	}

	$page = 'listeUsers';
	require('entete.php');
?>
	<input type="hidden" id="message" value="<?php echo $message; ?>">
	<div class="card shadow mb-3">
		<div class="card-header">
			<h6 class="font-weight-bold text-primary">Gestion des utilistateurs</h6>
		</div>
		<div class="card-body">
			<form action="listeUsers.php" method="get">
				<div class="row">
					<div class="col">
						<div class="form-group">
							<label for="user">Utilisateur</label>
							<select class="form-control selectpicker show-tick" data-live-search="true" id="user" name="user" onchange="this.form.submit();">
									<option value="0"<?php echo ($numUser == 0 ? ' selected' : '');?>>Nouveau...</option>
<?php
									if ($listeUsers) {
										foreach ($listeUsers as $user) {
?>
											<option value="<?php echo $user->numUser; ?>"<?php echo ($numUser == $user->numUser ? ' selected' : '');?>><?php echo $user->nomUser . ' ' . $user->prenomUser; ?></option>
<?php
										}
									}
?>
							</select>
						</div>
					</div>
				</div>
			</form>
			<div class="card shadow mb-3">
				<div class="card-header">
					<h6 class="font-weight-bold text-primary">Détail</h6>
				</div>
				<form action="listeUsers.php" method="get">
					<div class="card-body">
						<input type="hidden" name="numUser" value="<?php echo $numUser; ?>">
						<div class="row">
							<div class="col">
								<div class="form-group">
									<label for="nomUser">Nom de l'utilisateur</label>
									<input type="text" class="form-control<?php  echo ($erreurNomUser ? ' is-invalid' : ''); ?>" id="nomUser" name="nomUser" value="<?php echo $nomUser; ?>">
								</div>
							</div>
							<div class="col">
								<div class="form-group">
									<label for="prenomUser">Prénom de l'utilisateur</label>
									<input type="text" class="form-control<?php  echo ($erreurPrenomUser ? ' is-invalid' : ''); ?>" id="prenomUser" name="prenomUser" value="<?php echo $prenomUser; ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col">
								<div class="form-group">
									<label for="mailUser">Adresse mail</label>
									<input type="text" class="form-control<?php  echo ($erreurMailUser ? ' is-invalid' : ''); ?>" id="mailUser" name="mailUser" value="<?php echo $mailUser; ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col d-flex d-flex-column">
								<div class="form-group form-check mt-auto mb-4">
									<input type="checkbox" class="form-check-input" id="admin" name="admin"<?php echo ($adminUser ? ' checked' : ''); ?>>
									<label class="form-check-label" for="admin">Administrateur</label>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col">
								<div class="form-group">
									<label for="mdp">Nouveau mot de passe</label>
									<input type="password" class="form-control<?php  echo ($erreurMdpUser ? ' is-invalid' : ''); ?>" id="mdp" name="mdp">
								</div>
							</div>
							<div class="col">
								<div class="form-group">
									<label for="mdp2">Confirmation</label>
									<input type="password" class="form-control<?php  echo ($erreurMdpUser ? ' is-invalid' : ''); ?>" id="mdp2" name="mdp2">
								</div>
							</div>
						</div>
					</div>
					<div class="card-footer">
						<button type="submit" class="btn btn-primary">Enregistrer</button>
					</div>
				</form>
			</div>
		</div>
	</div>
<?php
	require('pied.php');
?>
