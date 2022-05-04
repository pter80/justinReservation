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

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Justin GOBLET 2022</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->


		    <!-- Logout Modal-->
		    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
		        aria-hidden="true">
		        <div class="modal-dialog" role="document">
		            <div class="modal-content">
		                <div class="modal-header">
		                    <h5 class="modal-title" id="exampleModalLabel">Quitter ?</h5>
		                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
		                        <span aria-hidden="true">×</span>
		                    </button>
		                </div>
		                <div class="modal-body">Cliquer sur "Déconnexion" ci-dessous pour vous déconnecter.</div>
		                <div class="modal-footer">
		                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
		                    <a class="btn btn-primary" href="index.php">Déconnexion</a>
		                </div>
		            </div>
		        </div>
		    </div>

		    <!-- Scroll to Top Button-->
		    <a class="scroll-to-top rounded" href="#page-top">
		        <i class="fas fa-angle-up"></i>
		    </a>

		    <!-- Bootstrap core JavaScript-->
		    <script src="assets/jquery/jquery.min.js"></script>
		    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

		    <!-- Core plugin JavaScript-->
		    <script src="assets/jquery-easing/jquery.easing.min.js"></script>

		    <!-- Custom scripts for all pages-->
		    <script src="assets/sb-admin2/sb-admin-2.min.js"></script>
		    <script src="assets/base64/base64.min.js"></script>
		    <script src="assets/moment/moment-with-locales.min.js"></script>
		    <script src="assets/bootstrap-select/bootstrap-select.min.js"></script>
		    <script src="assets/bootstrap-select/i18n/defaults-fr_FR.min.js"></script>
		    <script src="assets/datetimepicker/tempusdominus-bootstrap-4.min.js"></script>
				<script src="assets/swal/sweetalert2.all.min.js"></script>
				<script src="assets/js/reserveSalle.js"></script>

</body>

</html>
