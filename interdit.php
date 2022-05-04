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

	$page = 'interdit';
	require('entete.php');
?>
	<div class="row">
		<div class="col-lg-6 mx-auto">
			<div class="card shadow mb-3 mx-auto">
				<div class="card-header">
					<h6 class="font-weight-bold text-primary">Accès non autorisé</h6>
				</div>
				<div class="card-body">
					<div class="alert alert-danger text-center" role="alert">Vous n'avez pas l'autorisation d'accèder à cette page.</div>
					<img class="mt-2 mb-2 d-block mx-auto" src="assets/img/interdit.gif" alt="Interdit" style="width: 30vw;">
					<a class="text-center text-decoration-none d-block" href="reserver.php">Cliquez ici pour retourner à l'accueil</a>
				</div>
			</div>
		</div>
	</div>
<?php
	require('pied.php');
?>
