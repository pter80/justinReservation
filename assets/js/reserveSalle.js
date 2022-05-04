var ready = (callback) => {
	if (document.readyState != "loading") callback();
	else document.addEventListener("DOMContentLoaded", callback);
};

ready( () => {
	window.scrollTo(0, 0);
	$('.selectpicker').selectpicker({
		iconBase: 'fa',
		tickIcon: 'fa-check'
	});

	if (document.querySelector("#dateDeb") !== null) {
		let dateDeb = document.querySelector("#dateDeb").value;
		$("#dateDeb").datetimepicker({
			icons: {
				time: 'fas fa-clock'
			},
			locale: "fr"
		});
		document.querySelector("#dateDeb").value = dateDeb;

		$("#dateDeb").on("change.datetimepicker", function(e) {
			$('#dateFin').datetimepicker('minDate', e.Date);
		});
	}

	if (document.querySelector("#dateFin") !== null) {
		let dateFin = document.querySelector("#dateFin").value;
		$("#dateFin").datetimepicker({
			icons: {
				time: 'fas fa-clock'
			},
			locale: "fr"
		});
		document.querySelector("#dateFin").value = dateFin;

		$("#dateFin").on("change.datetimepicker", function(e) {
			$('#dateDeb').datetimepicker('maxDate', e.Date);
		});
	}

	let champs = document.querySelectorAll("input, textarea");
	champs.forEach((item, i) => {
		item.addEventListener('input', (e) => {
			e.target.setCustomValidity("");
			e.target.classList.remove("is-invalid");
		});
	});

	champs = document.querySelectorAll("select");
	champs.forEach((item, i) => {
		item.addEventListener('change', (e) => {
			e.target.setCustomValidity("");
			e.target.classList.remove("is-invalid");
		});
	});

	if (document.querySelector("#message") !== null  && document.querySelector("#message").value != "") {
		let message = document.querySelector("#message").value;
		if (message != "Enregistrement effectué" && message != "Réservation enregistrée") {
			if (Swal.isVisible()) {
				Swal.close();
			}
			Swal.fire({
				allowOutsideClick: false,
				allowEscapeKey: false,
				icon: "error",
				title: "Erreur",
				text: message
			});
		}
		else {
			const Toast = Swal.mixin({
				toast: true,
				position: "top-end",
				showConfirmButton: false,
				timer: 3000
			});

			Toast.fire({
				icon: "success",
				title: message
			});
		}
	}
});