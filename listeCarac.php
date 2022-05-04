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

	$sql = "SELECT * FROM carac ORDER BY nomCarac";
	$listeCarac = req_select($sql, false);

	$message  = '';
	$numCarac = 0;
	$nomCarac = '';

	$erreurNumCarac = false;
	$erreurNomCarac = false;
	$erreurEnreg = false;

	if (isset($_GET['numCarac']) && is_numeric($_GET['numCarac'])) {
		if ($_GET['numCarac'] != 0) {
			$sql = "SELECT * FROM carac WHERE numCarac = :numCarac";
			$param = array('numCarac' => $_GET['numCarac']);
			if (!req_select($sql, $param)) {
				$erreurNumCarac = true;
			}
			else {
				$numCarac = $_GET['numCarac'];
			}
		}
		else {
			$sql = "SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :nomBDD AND TABLE_NAME = :nomTable";
			$param = array(
				'nomBDD' => DB_MYSQL_NOM,
				'nomTable' => 'carac'
			);
			$_GET['numCarac'] = req_select($sql, $param)[0]->AUTO_INCREMENT;
		}

		if (empty($_GET['nomCarac'])) {
			$erreurNomCarac = true;
		}
		else {
			$nomCarac = trim($_GET['nomCarac']);
		}

		if (!$erreurNumCarac && !$erreurNomCarac) {
			$param = array(
				'numCarac' => $_GET['numCarac'],
				'nomCarac' => trim($_GET['nomCarac']),
			);
			$sql = "INSERT INTO carac (numCarac, nomCarac) VALUES (:numCarac, :nomCarac) ON DUPLICATE KEY UPDATE nomCarac = :nomCarac";
			if (req_maj($sql, $param)) {
				$numCarac = $_GET['numCarac'];
				$message = "Enregistrement effectué";
				$sql = "SELECT * FROM carac ORDER BY nomCarac";
				$listeCarac = req_select($sql, false);
			}
			else {
				$erreurEnreg = true;
				$message = "Erreur d'enregistrement";
			}
		}
		else {
			if ($erreurNumCarac) {
				$message = "Caractéristique inconnue";
			}
			else {
				$message = "Veuillez saisir tous les champs obligatoires";
			}
		}
	}
	else {
		if (isset($_GET['carac']) && is_numeric($_GET['carac'])) {
			$numCarac = $_GET['carac'];
			$sql = "SELECT * FROM carac WHERE numCarac = :numCarac";
			$param = array('numCarac' => $numCarac);
			$infosCarac = req_select($sql, $param);
			if ($infosCarac) {
				$nomCarac = $infosCarac[0]->nomCarac;
			}
			else {
				$numCarac = 0;
			}
		}
	}

	$page = 'listeCarac';
	require('entete.php');
?>
	<input type="hidden" id="message" value="<?php echo $message; ?>">
	<div class="card shadow mb-3">
		<div class="card-header">
			<h6 class="font-weight-bold text-primary">Gestion des caractéristiques de salles</h6>
		</div>
		<div class="card-body">
			<form action="listeCarac.php" method="get">
				<div class="row">
					<div class="col">
						<div class="form-group">
							<label for="carac">Caractéristique</label>
							<select class="form-control selectpicker show-tick" data-live-search="true" id="carac" name="carac" onchange="this.form.submit();">
									<option value="0"<?php echo ($numCarac == 0 ? ' selected' : '');?>>Nouveau...</option>
<?php
									if ($listeCarac) {
										foreach ($listeCarac as $carac) {
?>
											<option value="<?php echo $carac->numCarac; ?>"<?php echo ($numCarac == $carac->numCarac ? ' selected' : '');?>><?php echo $carac->nomCarac; ?></option>
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
				<form action="listeCarac.php" method="get">
					<div class="card-body">
						<input type="hidden" name="numCarac" value="<?php echo $numCarac; ?>">
						<div class="row">
							<div class="col">
								<div class="form-group">
									<label for="nomCarac">Nom de la caractéristique</label>
									<input type="text" class="form-control<?php  echo ($erreurNomCarac ? ' is-invalid' : ''); ?>" id="nomCarac" name="nomCarac" value="<?php echo $nomCarac; ?>">
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
