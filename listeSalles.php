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

	$sql = "SELECT * FROM salles ORDER BY nomSalle";
	$listeSalles = req_select($sql, false);

	$sql = "SELECT * FROM carac ORDER BY nomCarac";
	$listeCarac = req_select($sql, false);

	$caracSalle = array();
	

	$message  = '';
	$numSalle = 0;
	$nomSalle = '';
	
	$erreurNumSalle = false;
	$erreurNomSalle = false;
	$erreurNbPlaces = false;
	$erreurListeCarac = false;
	$erreurHeureDeb = false;
	$erreurHeureFin = false;
	$erreurEnreg = false;
	$erreurDateDeb = false;
	$erreurDateFin = false;
	$erreurCommentaire = false;
	$nbPlaces = 0;
	$jour0 = 0;
	$jour1 = 1;
	$jour2 = 1;
	$jour3 = 1;
	$jour4 = 1;
	$jour5 = 1;
	$jour6 = 0;
	$heureDeb = '08:00';
	$heureFin = '17:00';
	$listeIndispo = false;
	
	$listeReq = array();
	
	if (isset($_GET['action'])) {
		if ($_GET['action'] == 'indispoAdd') {
			if (isset($_GET['numSalle']) && is_numeric($_GET['numSalle'])) {
				$numSalle = $_GET['numSalle'];
				$sql = "SELECT salles.*, salles__dispo.jour0, salles__dispo.jour1, salles__dispo.jour2, salles__dispo.jour3, salles__dispo.jour4, salles__dispo.jour5, salles__dispo.jour6, DATE_FORMAT(salles__dispo.heureDeb, '%H:%i') AS heureDeb, DATE_FORMAT(salles__dispo.heureFin, '%H:%i') AS heureFin FROM salles LEFT JOIN salles__dispo ON salles.numSalle = salles__dispo.numSalle WHERE salles.numSalle = :numSalle";
				$param = array('numSalle' => $numSalle);
				$infosSalle = req_select($sql, $param);
				if ($infosSalle) {
					$nomSalle = $infosSalle[0]->nomSalle;
					$nbPlaces = $infosSalle[0]->nbPlaces;
					$jour0 = $infosSalle[0]->jour0;
					$jour1 = $infosSalle[0]->jour1;
					$jour2 = $infosSalle[0]->jour2;
					$jour3 = $infosSalle[0]->jour3;
					$jour4 = $infosSalle[0]->jour4;
					$jour5 = $infosSalle[0]->jour5;
					$jour6 = $infosSalle[0]->jour6;
					$heureDeb = $infosSalle[0]->heureDeb;
					$heureFin = $infosSalle[0]->heureFin;
					$sql = "SELECT numCarac FROM salles__carac WHERE numSalle = :numSalle";
					$caracSalleTempo = req_select($sql, $param);
					$caracSalle = array();
					if ($caracSalleTempo) {
						foreach ($caracSalleTempo as $caracTempo) {
							$caracSalle[] = $caracTempo->numCarac;
						}
					}
	
					$sql = "SELECT DATE_FORMAT(dateDeb, '%d/%m/%Y %H:%i') AS dateDeb, DATE_FORMAT(dateFin, '%d/%m/%Y %H:%i') AS dateFin, commentaire FROM salles__indispo WHERE numSalle = :numSalle ORDER BY dateDeb, dateFin";
					$listeIndispo = req_select($sql, $param);
				}
				else {
					$erreurNumSalle = true;
				}
			}
			if (empty($_GET['dateDeb']) || !validerDate($_GET['dateDeb'], 'd/m/Y H:i')) {
				$erreurDateDeb = true;
			}
			if (empty($_GET['dateFin']) || !validerDate($_GET['dateFin'], 'd/m/Y H:i')) {
				$erreurDateFin = true;
			}
			
			if (empty($_GET['commentaire'])) {
				$erreurCommentaire = true;
			}

			if (!$erreurNumSalle && !$erreurDateDeb && !$erreurDateFin && !$erreurCommentaire) {
				$sql = "INSERT INTO salles__indispo (numSalle, dateDeb, dateFin, commentaire) VALUES (:numSalle, STR_TO_DATE(:dateDeb, '%d/%m/%Y %H:%i'), STR_TO_DATE(:dateFin, '%d/%m/%Y %H:%i'), :commentaire) ON DUPLICATE KEY UPDATE commentaire = :commentaire";
				$param = array(
					'numSalle' => $numSalle,
					'dateDeb' => trim($_GET['dateDeb']),
					'dateFin' => $_GET['dateFin'],
					'commentaire' => $_GET['commentaire']
				);

				if (req_maj($sql, $param)) {
					$message = "Enregistrement effectué";
					$sql = "SELECT * FROM salles ORDER BY nomSalle";
					$listeSalles = req_select($sql, false);
	
					$sql = "SELECT salles.*, salles__dispo.jour0, salles__dispo.jour1, salles__dispo.jour2, salles__dispo.jour3, salles__dispo.jour4, salles__dispo.jour5, salles__dispo.jour6, DATE_FORMAT(salles__dispo.heureDeb, '%H:%i') AS heureDeb, DATE_FORMAT(salles__dispo.heureFin, '%H:%i') AS heureFin FROM salles LEFT JOIN salles__dispo ON salles.numSalle = salles__dispo.numSalle WHERE salles.numSalle = :numSalle";
					$param = array('numSalle' => $numSalle);
					$infosSalle = req_select($sql, $param);
					$nomSalle = $infosSalle[0]->nomSalle;
					$nbPlaces = $infosSalle[0]->nbPlaces;
					$jour0 = $infosSalle[0]->jour0;
					$jour1 = $infosSalle[0]->jour1;
					$jour2 = $infosSalle[0]->jour2;
					$jour3 = $infosSalle[0]->jour3;
					$jour4 = $infosSalle[0]->jour4;
					$jour5 = $infosSalle[0]->jour5;
					$jour6 = $infosSalle[0]->jour6;
					$heureDeb = $infosSalle[0]->heureDeb;
					$heureFin = $infosSalle[0]->heureFin;
	
					$sql = "SELECT numCarac FROM salles__carac WHERE numSalle = :numSalle";
					$caracSalleTempo = req_select($sql, $param);
					$caracSalle = array();
					if ($caracSalleTempo) {
						foreach ($caracSalleTempo as $caracTempo) {
							$caracSalle[] = $caracTempo->numCarac;
						}
					}
	
					$sql = "SELECT DATE_FORMAT(dateDeb, '%d/%m/%Y %H:%i') AS dateDeb, DATE_FORMAT(dateFin, '%d/%m/%Y %H:%i') AS dateFin, commentaire FROM salles__indispo WHERE numSalle = :numSalle ORDER BY dateDeb, dateFin";
					$listeIndispo = req_select($sql, $param);
				}
				else {
					$erreurEnreg = true;
					$message = "Erreur d'enregistrement";
				}
			}
			else {
				if ($erreurNumSalle) {
					$message = "Salle inconnue";
				}
				else {
					$message = "Veuillez saisir tous les champs obligatoires";
				}
			}
			
		}
		if ($_GET['action'] == 'indispoSuppr') {
			if (isset($_GET['numSalle']) && is_numeric($_GET['numSalle'])) {
				$numSalle = $_GET['numSalle'];
				$sql = "SELECT salles.*, salles__dispo.jour0, salles__dispo.jour1, salles__dispo.jour2, salles__dispo.jour3, salles__dispo.jour4, salles__dispo.jour5, salles__dispo.jour6, DATE_FORMAT(salles__dispo.heureDeb, '%H:%i') AS heureDeb, DATE_FORMAT(salles__dispo.heureFin, '%H:%i') AS heureFin FROM salles LEFT JOIN salles__dispo ON salles.numSalle = salles__dispo.numSalle WHERE salles.numSalle = :numSalle";
				$param = array('numSalle' => $numSalle);
				$infosSalle = req_select($sql, $param);
				if ($infosSalle) {
					$nomSalle = $infosSalle[0]->nomSalle;
					$nbPlaces = $infosSalle[0]->nbPlaces;
					$jour0 = $infosSalle[0]->jour0;
					$jour1 = $infosSalle[0]->jour1;
					$jour2 = $infosSalle[0]->jour2;
					$jour3 = $infosSalle[0]->jour3;
					$jour4 = $infosSalle[0]->jour4;
					$jour5 = $infosSalle[0]->jour5;
					$jour6 = $infosSalle[0]->jour6;
					$heureDeb = $infosSalle[0]->heureDeb;
					$heureFin = $infosSalle[0]->heureFin;
					$sql = "SELECT numCarac FROM salles__carac WHERE numSalle = :numSalle";
					$caracSalleTempo = req_select($sql, $param);
					$caracSalle = array();
					if ($caracSalleTempo) {
						foreach ($caracSalleTempo as $caracTempo) {
							$caracSalle[] = $caracTempo->numCarac;
						}
					}
	
					$sql = "SELECT DATE_FORMAT(dateDeb, '%d/%m/%Y %H:%i') AS dateDeb, DATE_FORMAT(dateFin, '%d/%m/%Y %H:%i') AS dateFin, commentaire FROM salles__indispo WHERE numSalle = :numSalle ORDER BY dateDeb, dateFin";
					$listeIndispo = req_select($sql, $param);
				}
				else {
					$erreurNumSalle = true;
				}
			}
			if (empty($_GET['dateDeb']) || !validerDate($_GET['dateDeb'], 'd/m/Y H:i')) {
				$erreurDateDeb = true;
			}
			if (empty($_GET['dateFin']) || !validerDate($_GET['dateFin'], 'd/m/Y H:i')) {
				$erreurDateFin = true;
			}

			if (!$erreurNumSalle && !$erreurDateDeb && !$erreurDateFin) {
				$sql = "DELETE FROM salles__indispo WHERE numSalle = :numSalle AND dateDeb = STR_TO_DATE(:dateDeb, '%d/%m/%Y %H:%i') AND dateFin = STR_TO_DATE(:dateFin, '%d/%m/%Y %H:%i')";
				$param = array(
					'numSalle' => $numSalle,
					'dateDeb' => $_GET['dateDeb'],
					'dateFin' => $_GET['dateFin']
				);
				$listeReq[] = array($sql, $param);
				if (req_maj_transaction(...$listeReq)) {
					$message = "Enregistrement effectué";
					$sql = "SELECT * FROM salles ORDER BY nomSalle";
					$listeSalles = req_select($sql, false);
	
					$sql = "SELECT salles.*, salles__dispo.jour0, salles__dispo.jour1, salles__dispo.jour2, salles__dispo.jour3, salles__dispo.jour4, salles__dispo.jour5, salles__dispo.jour6, DATE_FORMAT(salles__dispo.heureDeb, '%H:%i') AS heureDeb, DATE_FORMAT(salles__dispo.heureFin, '%H:%i') AS heureFin FROM salles LEFT JOIN salles__dispo ON salles.numSalle = salles__dispo.numSalle WHERE salles.numSalle = :numSalle";
					$param = array('numSalle' => $numSalle);
					$infosSalle = req_select($sql, $param);
					$nomSalle = $infosSalle[0]->nomSalle;
					$nbPlaces = $infosSalle[0]->nbPlaces;
					$jour0 = $infosSalle[0]->jour0;
					$jour1 = $infosSalle[0]->jour1;
					$jour2 = $infosSalle[0]->jour2;
					$jour3 = $infosSalle[0]->jour3;
					$jour4 = $infosSalle[0]->jour4;
					$jour5 = $infosSalle[0]->jour5;
					$jour6 = $infosSalle[0]->jour6;
					$heureDeb = $infosSalle[0]->heureDeb;
					$heureFin = $infosSalle[0]->heureFin;
	
					$sql = "SELECT numCarac FROM salles__carac WHERE numSalle = :numSalle";
					$caracSalleTempo = req_select($sql, $param);
					$caracSalle = array();
					if ($caracSalleTempo) {
						foreach ($caracSalleTempo as $caracTempo) {
							$caracSalle[] = $caracTempo->numCarac;
						}
					}
	
					$sql = "SELECT DATE_FORMAT(dateDeb, '%d/%m/%Y %H:%i') AS dateDeb, DATE_FORMAT(dateFin, '%d/%m/%Y %H:%i') AS dateFin, commentaire FROM salles__indispo WHERE numSalle = :numSalle ORDER BY dateDeb, dateFin";
					$listeIndispo = req_select($sql, $param);
				}
				else {
					$erreurEnreg = true;
					$message = "Erreur d'enregistrement";
				}
			}
			else {
				if ($erreurNumSalle) {
					$message = "Salle inconnue";
				}
				else {
					$message = "Veuillez saisir tous les champs obligatoires";
				}
			}
		}
	}
	else {
		if (isset($_GET['numSalle']) && is_numeric($_GET['numSalle'])) {
			if ($_GET['numSalle'] != 0) {
				$sql = "SELECT salles.*, salles__dispo.jour0, salles__dispo.jour1, salles__dispo.jour2, salles__dispo.jour3, salles__dispo.jour4, salles__dispo.jour5, salles__dispo.jour6, DATE_FORMAT(salles__dispo.heureDeb, '%H:%i') AS heureDeb, DATE_FORMAT(salles__dispo.heureFin, '%H:%i') AS heureFin FROM salles LEFT JOIN salles__dispo ON salles.numSalle = salles__dispo.numSalle WHERE salles.numSalle = :numSalle";
				$param = array('numSalle' => $_GET['numSalle']);
				$infosSalleTemp = req_select($sql, $param);
				if (!$infosSalleTemp) {
					$erreurNumSalle = true;
				}
				else {
					$numSalle = $_GET['numSalle'];
					$nomSalle = $infosSalleTemp[0]->nomSalle;
					$nbPlaces = $infosSalleTemp[0]->nbPlaces;
					$jour0 = $infosSalleTemp[0]->jour0;
					$jour1 = $infosSalleTemp[0]->jour1;
					$jour2 = $infosSalleTemp[0]->jour2;
					$jour3 = $infosSalleTemp[0]->jour3;
					$jour4 = $infosSalleTemp[0]->jour4;
					$jour5 = $infosSalleTemp[0]->jour5;
					$jour6 = $infosSalleTemp[0]->jour6;
					$heureDeb = $infosSalleTemp[0]->heureDeb;
					$heureFin = $infosSalleTemp[0]->heureFin;
					$sql = "SELECT numCarac FROM salles__carac WHERE numSalle = :numSalle";
					$caracSalleTempo = req_select($sql, $param);
					$caracSalle = array();
					if ($caracSalleTempo) {
						foreach ($caracSalleTempo as $caracTempo) {
							$caracSalle[] = $caracTempo->numCarac;
						}
					}
					$sql = "SELECT DATE_FORMAT(dateDeb, '%d/%m/%Y %H:%i') AS dateDeb, DATE_FORMAT(dateFin, '%d/%m/%Y %H:%i') AS dateFin, commentaire FROM salles__indispo WHERE numSalle = :numSalle ORDER BY dateDeb, dateFin";
					$listeIndispo = req_select($sql, $param);
				}
			}
			else {
				$sql = "SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :nomBDD AND TABLE_NAME = :nomTable";
				$param = array(
					'nomBDD' => DB_MYSQL_NOM,
					'nomTable' => 'salles'
				);
				$numSalle = req_select($sql, $param)[0]->AUTO_INCREMENT;
			}
	
			if (empty($_GET['nomSalle'])) {
				$erreurNomSalle = true;
			}
			else {
				$nomSalle = trim($_GET['nomSalle']);
			}
	
			if (empty($_GET['nbPlaces']) || !is_numeric($_GET['nbPlaces']) || $_GET['nbPlaces'] < 1) {
				$erreurNbPlaces = true;
			}
	
			$sql = "DELETE FROM salles__carac WHERE numSalle = :numSalle";
			$param = array('numSalle' => $numSalle);
			$listeReq[] = array($sql, $param);
	
			if (!empty($_GET['listeCarac'])) {
				if (!is_array($_GET['listeCarac'])) {
					$erreurListeCarac = true;
				}
				else {
					$sql = "SELECT * FROM carac WHERE numCarac = :numCarac";
					$sqlCarac = "INSERT INTO salles__carac (numSalle, numCarac) VALUES (:numSalle, :numCarac)";
					foreach ($_GET['listeCarac'] as $carac) {
						$param = array('numCarac' => $carac);
						if (!is_numeric($carac) || !req_select($sql, $param)) {
							$erreurListeCarac = true;
						}
						else {
							$paramCarac = array(
								'numSalle' => $numSalle,
								'numCarac' => $carac
							);
							$listeReq[] = array($sqlCarac, $paramCarac);
						}
					}
				}
			}
	
			if (empty($_GET['heureDeb']) || !validerDate($_GET['heureDeb'], 'H:i')) {
				$erreurHeureDeb = true;
			}
	
			if (empty($_GET['heureFin']) || !validerDate($_GET['heureFin'], 'H:i')) {
				$erreurHeureFin = true;
			}
	
			if (!$erreurNumSalle && !$erreurNomSalle && !$erreurNbPlaces && !$erreurListeCarac && !$erreurHeureDeb && !$erreurHeureFin) {
				$sql = "INSERT INTO salles (numSalle, nomSalle, nbPlaces) VALUES (:numSalle, :nomSalle, :nbPlaces) ON DUPLICATE KEY UPDATE nomSalle = :nomSalle, nbPlaces = :nbPlaces";
				$param = array(
					'numSalle' => $numSalle,
					'nomSalle' => trim($_GET['nomSalle']),
					'nbPlaces' => $_GET['nbPlaces']
				);
				$listeReq[] = array($sql, $param);
				
				$sql = "INSERT INTO salles__dispo (numSalle, jour0, jour1, jour2, jour3, jour4, jour5, jour6, heureDeb, heureFin) VALUES (:numSalle, :jour0, :jour1, :jour2, :jour3, :jour4, :jour5, :jour6, STR_TO_DATE(:heureDeb, '%H:%i'), STR_TO_DATE(:heureFin, '%H:%i')) ON DUPLICATE KEY UPDATE jour0 = :jour0, jour1 = :jour1, jour2 = :jour2, jour3 = :jour3, jour4 = :jour4, jour5 = :jour5, jour6 = :jour6, heureDeb = STR_TO_DATE(:heureDeb, '%H:%i'), heureFin = STR_TO_DATE(:heureFin, '%H:%i')";
				$param = array(
					'numSalle' => $numSalle,
					'jour0' => isset($_GET['jour0']) ? 1 : 0,
					'jour1' => isset($_GET['jour1']) ? 1 : 0,
					'jour2' => isset($_GET['jour2']) ? 1 : 0,
					'jour3' => isset($_GET['jour3']) ? 1 : 0,
					'jour4' => isset($_GET['jour4']) ? 1 : 0,
					'jour5' => isset($_GET['jour5']) ? 1 : 0,
					'jour6' => isset($_GET['jour6']) ? 1 : 0,
					'heureDeb' => $_GET['heureDeb'],
					'heureFin' => $_GET['heureFin']
				);
				$listeReq[] = array($sql, $param);
	
				if (req_maj_transaction(...$listeReq)) {
					$message = "Enregistrement effectué";
					$sql = "SELECT * FROM salles ORDER BY nomSalle";
					$listeSalles = req_select($sql, false);
	
					$sql = "SELECT salles.*, salles__dispo.jour0, salles__dispo.jour1, salles__dispo.jour2, salles__dispo.jour3, salles__dispo.jour4, salles__dispo.jour5, salles__dispo.jour6, DATE_FORMAT(salles__dispo.heureDeb, '%H:%i') AS heureDeb, DATE_FORMAT(salles__dispo.heureFin, '%H:%i') AS heureFin FROM salles LEFT JOIN salles__dispo ON salles.numSalle = salles__dispo.numSalle WHERE salles.numSalle = :numSalle";
					$param = array('numSalle' => $numSalle);
					$infosSalle = req_select($sql, $param);
					$nomSalle = $infosSalle[0]->nomSalle;
					$nbPlaces = $infosSalle[0]->nbPlaces;
					$jour0 = $infosSalle[0]->jour0;
					$jour1 = $infosSalle[0]->jour1;
					$jour2 = $infosSalle[0]->jour2;
					$jour3 = $infosSalle[0]->jour3;
					$jour4 = $infosSalle[0]->jour4;
					$jour5 = $infosSalle[0]->jour5;
					$jour6 = $infosSalle[0]->jour6;
					$heureDeb = $infosSalle[0]->heureDeb;
					$heureFin = $infosSalle[0]->heureFin;
	
					$sql = "SELECT numCarac FROM salles__carac WHERE numSalle = :numSalle";
					$caracSalleTempo = req_select($sql, $param);
					$caracSalle = array();
					if ($caracSalleTempo) {
						foreach ($caracSalleTempo as $caracTempo) {
							$caracSalle[] = $caracTempo->numCarac;
						}
					}
	
					$sql = "SELECT DATE_FORMAT(dateDeb, '%d/%m/%Y %H:%i') AS dateDeb, DATE_FORMAT(dateFin, '%d/%m/%Y %H:%i') AS dateFin, commentaire FROM salles__indispo WHERE numSalle = :numSalle ORDER BY dateDeb, dateFin";
					$listeIndispo = req_select($sql, $param);
				}
				else {
					$erreurEnreg = true;
					$message = "Erreur d'enregistrement";
				}
			}
			else {
				if ($erreurNumSalle) {
					$message = "Salle inconnue";
				}
				else {
					$message = "Veuillez saisir tous les champs obligatoires";
				}
			}
		}
		else {
			if (isset($_GET['salle']) && is_numeric($_GET['salle'])) {
				$numSalle = $_GET['salle'];
				$sql = "SELECT salles.*, salles__dispo.jour0, salles__dispo.jour1, salles__dispo.jour2, salles__dispo.jour3, salles__dispo.jour4, salles__dispo.jour5, salles__dispo.jour6, DATE_FORMAT(salles__dispo.heureDeb, '%H:%i') AS heureDeb, DATE_FORMAT(salles__dispo.heureFin, '%H:%i') AS heureFin FROM salles LEFT JOIN salles__dispo ON salles.numSalle = salles__dispo.numSalle WHERE salles.numSalle = :numSalle";
				$param = array('numSalle' => $numSalle);
				$infosSalle = req_select($sql, $param);
				if ($infosSalle) {
					$nomSalle = $infosSalle[0]->nomSalle;
					$nbPlaces = $infosSalle[0]->nbPlaces;
					$jour0 = $infosSalle[0]->jour0;
					$jour1 = $infosSalle[0]->jour1;
					$jour2 = $infosSalle[0]->jour2;
					$jour3 = $infosSalle[0]->jour3;
					$jour4 = $infosSalle[0]->jour4;
					$jour5 = $infosSalle[0]->jour5;
					$jour6 = $infosSalle[0]->jour6;
					$heureDeb = $infosSalle[0]->heureDeb;
					$heureFin = $infosSalle[0]->heureFin;
					$sql = "SELECT numCarac FROM salles__carac WHERE numSalle = :numSalle";
					$caracSalleTempo = req_select($sql, $param);
					$caracSalle = array();
					if ($caracSalleTempo) {
						foreach ($caracSalleTempo as $caracTempo) {
							$caracSalle[] = $caracTempo->numCarac;
						}
					}
	
					$sql = "SELECT DATE_FORMAT(dateDeb, '%d/%m/%Y %H:%i') AS dateDeb, DATE_FORMAT(dateFin, '%d/%m/%Y %H:%i') AS dateFin, commentaire FROM salles__indispo WHERE numSalle = :numSalle ORDER BY dateDeb, dateFin";
					$listeIndispo = req_select($sql, $param);
				}
				else {
					$numSalle = 0;
				}
			}
		}
	}

	$page = 'listeSalles';
	require('entete.php');
?>
	<input type="hidden" id="message" value="<?php echo $message; ?>">
	<div class="card shadow mb-3">
		<div class="card-header">
			<h6 class="font-weight-bold text-primary">Gestion des salles</h6>
		</div>
		<div class="card-body">
			<form action="listeSalles.php" method="get">
				<div class="row">
					<div class="col">
						<div class="form-group">
							<label for="salle">Salle</label>
							<select class="form-control selectpicker show-tick" data-live-search="true" id="salle" name="salle" onchange="this.form.submit();">
									<option value="0"<?php echo ($numSalle == 0 ? ' selected' : '');?>>Nouveau...</option>
<?php
									if ($listeSalles) {
										foreach ($listeSalles as $salle) {
?>
											<option value="<?php echo $salle->numSalle; ?>"<?php echo ($numSalle == $salle->numSalle ? ' selected' : '');?>><?php echo $salle->nomSalle; ?></option>
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
				<form action="listeSalles.php" method="get">
					<div class="card-body">
						<input type="hidden" name="numSalle" value="<?php echo $numSalle; ?>">
						<div class="row">
							<div class="col">
								<div class="form-group">
									<label for="nomSalle">Nom de la salle</label>
									<input type="text" class="form-control<?php  echo ($erreurNomSalle ? ' is-invalid' : ''); ?>" id="nomSalle" name="nomSalle" value="<?php echo $nomSalle; ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col">
								<div class="form-group">
									<label for="nbPlaces">Nombre de places</label>
									<input type="text" class="form-control<?php  echo ($erreurNbPlaces ? ' is-invalid' : ''); ?>" id="nbPlaces" name="nbPlaces" value="<?php echo $nbPlaces; ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col">
								<div class="form-group">
									<label for="listeCarac">Caractéristiques</label>
									<select class="form-control selectpicker show-tick" multiple data-actions-box="true" data-live-search="true" id="listeCarac" name="listeCarac[]">
		<?php
											if ($listeCarac) {
												foreach ($listeCarac as $carac) {
		?>
													<option value="<?php echo $carac->numCarac; ?>"<?php echo (in_array($carac->numCarac, $caracSalle) ? ' selected' : '');?>><?php echo $carac->nomCarac; ?></option>
		<?php
												}
											}
		?>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col text-primary">Disponibilité</div>
						</div>
						<div class="row">
							<div class="col">
								<div class="form-group">
									<label for="heureDeb">Heure de début</label>
									<input type="text" class="form-control<?php  echo ($erreurHeureDeb ? ' is-invalid' : ''); ?>" id="heureDeb" name="heureDeb" value="<?php echo $heureDeb; ?>">
								</div>
							</div>
							<div class="col">
								<div class="form-group">
									<label for="heureDeb">Heure de fin</label>
									<input type="text" class="form-control<?php  echo ($erreurHeureFin ? ' is-invalid' : ''); ?>" id="heureFin" name="heureFin" value="<?php echo $heureFin; ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col d-flex d-flex-column">
								<div class="form-group form-check mt-auto mb-4">
									<input type="checkbox" class="form-check-input" id="jour1" name="jour1"<?php echo ($jour1 == 1 ? ' checked' : ''); ?>>
									<label class="form-check-label" for="jour1">Lundi</label>
								</div>
							</div>
							<div class="col d-flex d-flex-column">
								<div class="form-group form-check mt-auto mb-4">
									<input type="checkbox" class="form-check-input" id="jour2" name="jour2"<?php echo ($jour2 == 1 ? ' checked' : ''); ?>>
									<label class="form-check-label" for="jour2">Mardi</label>
								</div>
							</div>
							<div class="col d-flex d-flex-column">
								<div class="form-group form-check mt-auto mb-4">
									<input type="checkbox" class="form-check-input" id="jour3" name="jour3"<?php echo ($jour3 == 1 ? ' checked' : ''); ?>>
									<label class="form-check-label" for="jour3">Mercredi</label>
								</div>
							</div>
							<div class="col d-flex d-flex-column">
								<div class="form-group form-check mt-auto mb-4">
									<input type="checkbox" class="form-check-input" id="jour4" name="jour4"<?php echo ($jour4 == 1 ? ' checked' : ''); ?>>
									<label class="form-check-label" for="jour4">Jeudi</label>
								</div>
							</div>
							<div class="col d-flex d-flex-column">
								<div class="form-group form-check mt-auto mb-4">
									<input type="checkbox" class="form-check-input" id="jour5" name="jour5"<?php echo ($jour5 == 1 ? ' checked' : ''); ?>>
									<label class="form-check-label" for="jour5">Vendredi</label>
								</div>
							</div>
							<div class="col d-flex d-flex-column">
								<div class="form-group form-check mt-auto mb-4">
									<input type="checkbox" class="form-check-input" id="jour6" name="jour6"<?php echo ($jour6 == 1 ? ' checked' : ''); ?>>
									<label class="form-check-label" for="jour6">Samedi</label>
								</div>
							</div>
							<div class="col d-flex d-flex-column">
								<div class="form-group form-check mt-auto mb-4">
									<input type="checkbox" class="form-check-input" id="jour0" name="jour0"<?php echo ($jour0 == 1 ? ' checked' : ''); ?>>
									<label class="form-check-label" for="jour0">Dimanche</label>
								</div>
							</div>
						</div>
					</div>
					<div class="card-footer">
						<button type="submit" class="btn btn-primary">Enregistrer</button>
					</div>
				</form>
			</div>
			<?php
			if ($numSalle != 0) {
			?>
			<div class="card shadow mb-3">
				<div class="card-header">
					<h6 class="font-weight-bold text-primary">Indisponibilités</h6>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col text-primary">Ajouter</div>
					</div>
					<form action="listeSalles.php" method="get">
						<input type="hidden" name="action" value="indispoAdd">
						<input type="hidden" name="numSalle" value="<?php echo $numSalle; ?>">
						<div class="row">
							<div class="col">
								<div class="form-group">
									<label for="dateDeb">Du</label>
									<input type="text" class="form-control datetimepicker-input" id="dateDeb" name="dateDeb" data-toggle="datetimepicker" data-target="#dateDeb">
								</div>
							</div>
							<div class="col">
								<div class="form-group">
									<label for="dateFin">Au</label>
									<input type="text" class="form-control datetimepicker-input" id="dateFin" name="dateFin" data-toggle="datetimepicker" data-target="#dateFin">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col">
								<div class="form-group">
									<label for="commentaire">Commentaire</label>
									<input type="text" class="form-control" id="commentaire" name="commentaire">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col text-right">
								<button type="submit" class="btn btn-primary">Ajouter</button>
							</div>
						</div>
					</form>
					<?php
					if ($listeIndispo) {
					?>
					<div class="row">
						<div class="col text-primary">Liste</div>
					</div>
					<?php
						foreach ($listeIndispo as $indispo) {
					?>
					<form action="listeSalles.php" method="get">
						<input type="hidden" name="action" value="indispoSuppr">
						<input type="hidden" name="numSalle" value="<?php echo $numSalle; ?>">
						<input type="hidden" name="dateDeb" value="<?php echo $indispo->dateDeb; ?>">
						<input type="hidden" name="dateFin" value="<?php echo $indispo->dateFin; ?>">
						<div class="row">
							<div class="col">Du <?php echo $indispo->dateDeb; ?> au <?php echo $indispo->dateFin; ?> (<?php echo $indispo->commentaire; ?>)</div>
							<div class="col">
								<button type="submit" class="btn btn-primary">Supprimer</button>
							</div>
						</div>
					</form>
					<?php
						}
					}
					?>
				</div>
			</div>
			<?php
			}
			?>
		</div>
	</div>
<?php
	require('pied.php');
?>
