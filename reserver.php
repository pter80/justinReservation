<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
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

	$message = "";
	$afficheResult = false;

	$sql = "SELECT * FROM carac ORDER BY nomCarac";
	$listeCarac = req_select($sql, false);

	$caracSelect = array();
	$nbPlaces = 0;

	if (isset($_GET['dateDeb']) && validerDate($_GET['dateDeb'], 'd/m/Y H:i') && isset($_GET['dateFin']) && validerDate($_GET['dateFin'], 'd/m/Y H:i') && !empty($_GET['nbPlaces']) && is_numeric($_GET['nbPlaces']) && $_GET['nbPlaces'] > 0) {
		$nbPlaces = $_GET['nbPlaces'];
		$dateDebAff = $_GET['dateDeb'];
		$dateFinAff = $_GET['dateFin'];

		$nbCarac = 0;
		if (!empty($_GET['listeCarac']) && is_array($_GET['listeCarac'])) {
			$caracSelect = $_GET['listeCarac'];
			$sql = "SELECT * FROM carac WHERE numCarac = :numCarac";
			foreach ($_GET['listeCarac'] as $value) {
				$param = array('numCarac' => $value);
				if (!is_numeric($value) || !req_select($sql, $param)) {
					$message = "Caractéristique de salle inconnue";
				}
			}
			$nbCarac = count($_GET['listeCarac']);
		}
		if ($message == '') {
			$afficheResult = true;

			// Recherche d'une salle disponible avec les caractéristiques demandées
			$listeJourSem = array(false, false, false, false, false, false, false);
			$debut = DateTime::createFromFormat('d/m/Y H:i', $_GET['dateDeb']);
			$fin = DateTime::createFromFormat('d/m/Y H:i', $_GET['dateFin']);
			$jourSemDeb = $debut->format('w');
			$intervalle = $jourSemDeb + $debut->diff($fin)->format('%a');
			for ($i = $jourSemDeb; $i <= $intervalle; $i++) {

				$listeJourSem[$i%7] = true;
			}

			$listeJours = '';
			foreach ($listeJourSem as $key => $value) {
				if ($value) {
					$listeJours .= " AND jour" . $key . " = true";
				}
			}

			$sqlListeCarac = '';
			if ($nbCarac != 0) {
				$sqlListeCarac = " AND numSalle IN (SELECT numSalle FROM salles__carac WHERE numCarac IN (" . implode(',', $_GET['listeCarac']) . ") GROUP BY numSalle HAVING COUNT(*) = :nbCarac)";
			}

			$sql = "SELECT * FROM salles WHERE numSalle NOT IN (SELECT numSalle FROM salles__indispo WHERE STR_TO_DATE(:dateDebReserv, '%d/%m/%Y %H:%i') BETWEEN dateDeb AND dateFin OR STR_TO_DATE(:dateFinReserv, '%d/%m/%Y %H:%i') BETWEEN dateDeb AND dateFin UNION SELECT numSalle FROM salles__reserv WHERE STR_TO_DATE(:dateDebReserv, '%d/%m/%Y %H:%i') BETWEEN dateDeb AND dateFin OR STR_TO_DATE(:dateFinReserv, '%d/%m/%Y %H:%i') BETWEEN dateDeb AND dateFin) AND numSalle IN (SELECT numSalle FROM salles__dispo WHERE DATE_FORMAT(STR_TO_DATE(:dateDebReserv, '%d/%m/%Y %H:%i'), '%H:%i') BETWEEN heureDeb AND heureFin AND DATE_FORMAT(STR_TO_DATE(:dateFinReserv, '%d/%m/%Y %H:%i'), '%H:%i') BETWEEN heureDeb AND heureFin" . $listeJours . ")" . $sqlListeCarac . " AND nbPlaces >= :nbPlaces";
			$param = array(
				'dateDebReserv' => $_GET['dateDeb'],
				'dateFinReserv' => $_GET['dateFin'],
				'nbPlaces' => $_GET['nbPlaces']
			);
			if ($nbCarac != 0) {
				$param['nbCarac'] = $nbCarac;
			}

			$sallesDispo = req_select($sql, $param);
			if (!$sallesDispo) {
				$message = "Aucune salle disponible";
			}
		}
	}
	else {
		$dateDebAff = (new DateTime())->format("d/m/Y H:i");
		$dateFinAff = (new DateTime())->add(new DateInterval('P1D'))->format("d/m/Y H:i");
	}

	if (isset($_GET['numSalle']) && is_numeric($_GET['numSalle']) && isset($_GET['dateDeDebut']) && validerDate($_GET['dateDeDebut'], 'd/m/Y H:i') && isset($_GET['dateDeFin']) && validerDate($_GET['dateDeFin'], 'd/m/Y H:i')) {
		$sql = "SELECT * FROM salles WHERE numSalle = :numSalle";
		$param = array('numSalle' => $_GET['numSalle']);
		if (!req_select($sql, $param)) {
			$message = "Salle inconnue";
		}
		else {
			$sql = "INSERT INTO salles__reserv (numSalle, dateDeb, dateFin, numUser) VALUES (:numSalle, STR_TO_DATE(:dateDeb, '%d/%m/%Y %H:%i'), STR_TO_DATE(:dateFin, '%d/%m/%Y %H:%i'), :numUser)";
			$param['dateDeb'] = $_GET['dateDeDebut'];
			$param['dateFin'] = $_GET['dateDeFin'];
			$param['numUser'] = $_SESSION['numUser'];
			if (!req_maj($sql, $param)) {
				$message = "Erreur d'enregistrement de la réservation";
			}
			else {
				$message = "Réservation enregistrée";
			}
		}
	}

	$page = 'reserver';
	require('entete.php');
?>
					<input type="hidden" id="message" value="<?php echo $message; ?>">
					<div class="card shadow mb-3">
						<div class="card-header">
							<h6 class="font-weight-bold text-primary">Réserver une salle</h6>
						</div>
						<div class="card-body">
							<form action="reserver.php" method="get">
								<div class="row">
									<div class="col">
										<div class="form-group">
											<label for="dateDeb">Du</label>
											<input type="text" class="form-control datetimepicker-input" id="dateDeb" name="dateDeb" data-toggle="datetimepicker" data-target="#dateDeb" value="<?php echo $dateDebAff; ?>">
										</div>
									</div>
									<div class="col">
										<div class="form-group">
											<label for="dateFin">Au</label>
											<input type="text" class="form-control datetimepicker-input" id="dateFin" name="dateFin" data-toggle="datetimepicker" data-target="#dateFin" value="<?php echo $dateFinAff; ?>">
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
															<option value="<?php echo $carac->numCarac; ?>"<?php echo (in_array($carac->numCarac, $caracSelect) ? ' selected' : '');?>><?php echo $carac->nomCarac; ?></option>
												<?php
														}
													}
												?>
											</select>
										</div>
									</div>
									<div class="col">
										<div class="form-group">
											<label for="nbPlaces">Nombre de places</label>
											<input type="text" class="form-control" id="nbPlaces" name="nbPlaces" value="<?php echo $nbPlaces; ?>">
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-1 d-flex d-flex-column">
										<button type="submit" class="btn btn-primary mt-auto mb-3">Valider</button>
									</div>
								</div>
							</form>
							<?php
								if ($afficheResult && $sallesDispo) {
							?>
							<div class="card shadow mb-3">
								<div class="card-header">
									<h6 class="font-weight-bold text-primary">Salles disponibles</h6>
								</div>
								<div class="card-body">
							<?php
									if ($sallesDispo) {
										foreach ($sallesDispo as $salle) {
							?>
									<form action="reserver.php" method="get">
										<input type="hidden" name="numSalle" value="<?php echo $salle->numSalle; ?>">
										<input type="hidden" name="dateDeDebut" value="<?php echo $_GET['dateDeb']; ?>">
										<input type="hidden" name="dateDeFin" value="<?php echo $_GET['dateFin']; ?>">
										<div class="row">
											<div class="col">
												<?php echo $salle->nomSalle; ?>
											</div>
											<div class="col text-right">
												<button type="submit" class="btn btn-primary">Réserver</button>
											</div>
										</div>
									</form>
							<?php
										}
									}
									else {
							?>
									Aucune salle trouvée
							<?php
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
