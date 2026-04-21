// JavaScript Document


		
		function ShowSweetAlert(type, title, message, confirmButton) {
			Swal.fire({
				title: title,
				text: message,
				icon: type,
				padding: '2em',
				confirmButtonText: confirmButton
			});
		}
		
		function ShowToastMessage(message, type) {
			Swal.fire({
				toast: true,
				icon: type,
				title: message,
				animation: true,
				position: 'top-right',
				showConfirmButton: false,
				timer: 3000,
				timerProgressBar: true,
				didOpen: (toast) => {
				  toast.addEventListener('mouseenter', Swal.stopTimer)
				  toast.addEventListener('mouseleave', Swal.resumeTimer)
				}
			})
		}