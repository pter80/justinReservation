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

	if (isset($_GET['dateDeb']) && validerDate($_GET['dateDeb'], 'd/m/Y H:i') && isset($_GET['dateFin']) && validerDate($_GET['dateFin'], 'd/m/Y H:i') && !empty($_GET['numSalle']) && is_numeric($_GET['numSalle']) && $_GET['numSalle'] > 0) {
		$sql = "DELETE FROM salles__reserv WHERE numSalle = :numSalle AND dateDeb = STR_TO_DATE(:dateDeb, '%d/%m/%Y %H:%i') AND dateFin = STR_TO_DATE(:dateFin, '%d/%m/%Y %H:%i') AND numUser = :numUser";
		$param = array(
			'numSalle' => $_GET['numSalle'],
			'dateDeb' => $_GET['dateDeb'],
			'dateFin' => $_GET['dateFin'],
			'numUser' => $_SESSION['numUser']
		);
		if (!req_maj($sql, $param)) {
			$message = "Erreur de suppression de la réservation";
		}
		else {
			$message = "Enregistrement effectué";
		}
	}

	$sql = "SELECT salles.*, DATE_FORMAT(salles__reserv.dateDeb, '%d/%m/%Y %H:%i') AS dateDeb, DATE_FORMAT(salles__reserv.dateFin, '%d/%m/%Y %H:%i') AS dateFin FROM salles__reserv LEFT JOIN salles ON salles__reserv.numSalle = salles.numSalle WHERE numUser = :numUser";
	$param = array('numUser' => $_SESSION['numUser']);
	$listeSalles = req_select($sql, $param);

	$page = 'reservations';
	require('entete.php');
?>
					<input type="hidden" id="message" value="<?php echo $message; ?>">
					<div class="card shadow mb-3">
						<div class="card-header">
							<h6 class="font-weight-bold text-primary">Mes réservations</h6>
						</div>
						<div class="card-body">
							<?php
								if ($listeSalles) {
									foreach ($listeSalles as $salle) {
							?>
							<div class="card shadow mb-3">
								<div class="card-header">
									<h6 class="font-weight-bold text-primary"><?php echo $salle->nomSalle; ?></h6>
								</div>
								<form action="reservations.php" method="get">
									<div class="card-body">
										<input type="hidden" name="numSalle" value="<?php echo $salle->numSalle; ?>">
										<input type="hidden" name="dateDeb" value="<?php echo $salle->dateDeb; ?>">
										<input type="hidden" name="dateFin" value="<?php echo $salle->dateFin; ?>">
										<div class="row">
											<div class="col">Réservation du <?php echo $salle->dateDeb; ?> au <?php echo $salle->dateFin; ?></div>
										</div>
									</div>
									<div class="card-footer">
										<button type="submit" class="btn btn-danger">Supprimer la réservation</button>
									</div>
								</form>
							</div>
							<?php
									}
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
