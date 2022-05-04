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

	if (isset($_GET['dateDeb']) && validerDate($_GET['dateDeb'], 'd/m/Y H:i') && isset($_GET['dateFin']) && validerDate($_GET['dateFin'], 'd/m/Y H:i') && !empty($_GET['numSalle']) && is_numeric($_GET['numSalle']) && $_GET['numSalle'] > 0 && !empty($_GET['numUser']) && is_numeric($_GET['numUser']) && $_GET['numUser'] > 0) {
		$sql = "DELETE FROM salles__reserv WHERE numSalle = :numSalle AND dateDeb = STR_TO_DATE(:dateDeb, '%d/%m/%Y %H:%i') AND dateFin = STR_TO_DATE(:dateFin, '%d/%m/%Y %H:%i') AND numUser = :numUser";
		$param = array(
			'numSalle' => $_GET['numSalle'],
			'dateDeb' => $_GET['dateDeb'],
			'dateFin' => $_GET['dateFin'],
			'numUser' => $_GET['numUser']
		);
		if (!req_maj($sql, $param)) {
			$message = "Erreur de suppression de la réservation";
		}
		else {
			$message = "Enregistrement effectué";
		}
	}

	$sql = "SELECT salles.*, DATE_FORMAT(salles__reserv.dateDeb, '%d/%m/%Y %H:%i') AS dateDeb, DATE_FORMAT(salles__reserv.dateFin, '%d/%m/%Y %H:%i') AS dateFin, salles__reserv.numUser, nomUser, prenomUser FROM salles__reserv LEFT JOIN salles ON salles__reserv.numSalle = salles.numSalle LEFT JOIN users ON salles__reserv.numUser = users.numUser ORDER BY salles__reserv.numSalle, dateDeb, dateFin";
	$listeSalles = req_select($sql, false);

	$page = 'reservationsAll';
	require('entete.php');
?>
					<input type="hidden" id="message" value="<?php echo $message; ?>">
					<div class="card shadow mb-3">
						<div class="card-header">
							<h6 class="font-weight-bold text-primary">Liste des réservations</h6>
						</div>
						<div class="card-body">
							<?php
								$numeroSalle = 0;
								if ($listeSalles) {
									foreach ($listeSalles as $salle) {
										if ($salle->numSalle != $numeroSalle) {
											if ($numeroSalle != 0) {
							?>
								</div>
							</div>
							<?php
											}
							?>
							<div class="card shadow mb-3">
								<div class="card-header">
									<h6 class="font-weight-bold text-primary"><?php echo $salle->nomSalle; ?></h6>
								</div>
								<div class="card-body">
							<?php
											$numeroSalle = $salle->numSalle;
										}
							?>
									<div class="row">
										<div class="col">
											Du <?php echo $salle->dateDeb; ?> au <?php echo $salle->dateFin; ?> (<?php echo $salle->nomUser . ' ' . $salle->prenomUser; ?>) 
										</div>
										<div class="col">
											<form action="reservationsAll.php" method="get">
												<input type="hidden" name="numSalle" value="<?php echo $salle->numSalle; ?>">
												<input type="hidden" name="dateDeb" value="<?php echo $salle->dateDeb; ?>">
												<input type="hidden" name="dateFin" value="<?php echo $salle->dateFin; ?>">
												<input type="hidden" name="numUser" value="<?php echo $salle->numUser; ?>">
												<button type="submit" class="btn btn-danger">Supprimer la réservation</button>
											</form>
										</div>
									</div>
							<?php
									}
							?>
								</div>
							</div>
							<?php
								}
								else {
							?>
							Aucune réservation
							<?php
								}
							?>
						</div>
					</div>
					<?php
						require('pied.php');
					?>
