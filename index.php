<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
	date_default_timezone_set("Europe/Paris");

	/***Fichiers requis***/
	require_once('./protected/parametres.php');
	require_once('./protected/fonctions.php');
	require_once('./protected/sessions.php');

	kill_session();

	$erreurLogin = false;
	$accesInterdit = false;

	if (!empty($_POST['username']) && filter_var($_POST['username'], FILTER_VALIDATE_EMAIL) && !empty($_POST['password'])) {
		$sql = "SELECT * FROM users WHERE mailUser = :mailUser";
		$param = array('mailUser' => $_POST['username']);
		$utilisateur = req_select($sql, $param);

		if (!$utilisateur) {
			$erreurLogin = true;
		}
		else {
		    if (!password_verify($_POST['password'], $utilisateur[0]->mdpUser)) {
				$erreurLogin = true;
		    }
			else {
				$erreurLogin = false;
				kill_session();
				if (empty(session_id())) {
					$session = new Session();
					$_SESSION['numUser'] = $utilisateur[0]->numUser;
					$_SESSION['nom'] = ucfirst(strtolower($utilisateur[0]->prenomUser)) . ' ' . strtoupper($utilisateur[0]->nomUser);
					$sql = "SELECT * FROM admin WHERE numUser = :numUser";
					$param = array('numUser' => $_SESSION['numUser']);
					if (req_select($sql, $param)) {
						$_SESSION['admin'] = 'O';
					}
				}
				require "reserver.php";
				exit();
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="fr">

<head>

    <link rel="icon" href="favicon.ico">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Connexion - Réservation de salle</title>

    <!-- Custom fonts for this template-->
    <link href="assets/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/sb-admin2/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .bg-login-image {
            background: url("//<?php echo $_SERVER['HTTP_HOST']; ?>/justin/html/reservation/assets/img/room-for-business-meeting.jpg") no-repeat !important;
            background-position: center !important;
            background-size: contain !important;
        }
    </style>
</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image mt-4 mb-4"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Réservation de salle - Bienvenue !</h1>
                                    </div>
                                    <form class="user" method="POST" action!="#">
                                        <div class="form-group">
																						<label for="username">Adresse mail</label>
                                            <input type="text" class="form-control form-control-user" id="username" name="username" placeholder="Adresse mail">
                                        </div>
                                        <div class="form-group">
																						<label for="password">Mot de passe</label>
                                            <input type="password" class="form-control form-control-user" id="password" name="password" placeholder="Mot de passe">
                                        </div>
										<?php
										if ($erreurLogin) {
										?>
											<div class="alert alert-danger alert-dismissible fade show" role="alert">
												Adresse mail/Mot de passe invalides
												<button type="button" class="close" data-dismiss="alert">
													<span>&times;</span>
												</button>
											</div>
										<?php
										}
										if ($accesInterdit) {
										?>
											<div class="alert alert-danger alert-dismissible fade show" role="alert">
												Accès non autorisé
												<button type="button" class="close" data-dismiss="alert">
													<span>&times;</span>
												</button>
											</div>
										<?php
										}
										if (isset($sessionExpiree) || (isset($_GET['sessionExpiree']) && $_GET['sessionExpiree'] == 'O')) {
										?>
											<div class="alert alert-info alert-dismissible fade show" role="alert">
												Session expirée
												<button type="button" class="close" data-dismiss="alert">
													<span>&times;</span>
												</button>
											</div>
										<?php
										}
										?>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Connexion
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="assets/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="assets/sb-admin2/sb-admin-2.min.js"></script>
</body>

</html>
