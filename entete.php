<?php
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

    <title>Réservation de salle</title>

    <!-- Custom fonts for this template-->
    <link href="assets/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/sb-admin2/sb-admin-2.min.css" rel="stylesheet">
    <link href="assets/datetimepicker/tempusdominus-bootstrap-4.min.css" rel="stylesheet" type="text/css">
    <link href="assets/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
    <link href="assets/cropper/cropper.min.css" rel="stylesheet">
    <link href="assets/swal/sweetalert2.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion<?php echo ($page == 'interdit' ? ' d-none': ''); ?>" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="brq.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-pen"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Réservation de salle</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Ajout -->
            <li class="nav-item<?php echo (($page == 'reserver') ? ' active' : ''); ?>">
                <a class="nav-link" href="reserver.php">
                    <i class="fas fa-fw fa-plus"></i>
                    <span>Réserver une salle</span>
				        </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                CONSULTATION
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item<?php echo (($page == 'reservations') ? ' active' : ''); ?>">
                <a class="nav-link" href="reservations.php">
                    <i class="fas fa-fw fa-edit"></i>
                    <span>Mes réservations</span>
                </a>
            </li>
			<?php
				if ($_SESSION['admin'] == 'O') {
			?>
            <li class="nav-item<?php echo (($page == 'reservationsAll') ? ' active' : ''); ?>">
                <a class="nav-link" href="reservationsAll.php">
                    <i class="fas fa-fw fa-edit"></i>
                    <span>Liste des réservations</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                PARAMETRES
            </div>
			<li class="nav-item<?php echo ($page == 'listeSalles' ? ' active' : ''); ?>">
                <a class="nav-link" href="listeSalles.php">
                    <i class="fas fa-fw fa-house-user"></i>
                    <span>Salles</span>
                </a>
			      </li>
            <li class="nav-item<?php echo ($page == 'listeCarac' ? ' active' : ''); ?>">
                <a class="nav-link" href="listeCarac.php">
                    <i class="fas fa-fw fa-book"></i>
                    <span>Caractéristiques de salles</span>
                </a>
			 </li>
			<li class="nav-item<?php echo ($page == 'listeUsers' ? ' active' : ''); ?>">
                <a class="nav-link" href="listeUsers.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
			</li>
			<?php
				}
			?>
            <!-- Nav Item - Pages Collapse Menu -->

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['nom']; ?></span>
                                <img class="img-profile rounded-circle"
                                    src="assets/img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Déconnexion
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

				        <!-- Begin Page Content -->
                <div class="container-fluid mt-3">
