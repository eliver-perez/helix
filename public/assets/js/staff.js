// JavaScript Document

var homeURL;

function InitializeValues(home) {
	homeURL = home;
	$('#btn-registrar-personal').on('click', function() {
		window.location.href = `${homeURL}staff`;
	});
}

var registrando = false;

function GetStaff() {
	try {
		$.ajax({
				url: homeURL + 'scripts/staff/',
				type: 'post',
				data: {
					usuario: $('#field-usuario').val(),
					password: calc($('#field-password').val())
				},
				success: function(data) {
					console.log(data);
					var returnedValue = JSON.parse(data);
					
					if(returnedValue.status == 'OK') {
						window.location.href = homeURL;
					} else if(returnedValue.status == 'ERROR_AUTENTICACION') {
						ShowSweetAlert('error', '¡Error en Autenticación!', 'Usuario o contraseña incorrectas, revise los datos y vuelva a intentarlo', 'Entendido');
					} else if(returnedValue.status == 'FAIL_PENDING_ACTIVATION') {
						ShowSweetAlert('error', '¡Sin Activar!', 'No se ha activado la cuenta, revise su correo y siga las instrucciones de activación.', 'Entendido');
					} else if(returnedValue.status == 'FAIL_NOT_ACTIVE') {
						ShowSweetAlert('error', '¡Cuenta Deshabilitada!', 'La cuenta esta deshabilitada.', 'Entendido');
					} else
						ShowSweetAlert('error', '¡Ocurrio un Error!', 'Ocurrio un error al intentar realizar la autenticacion.', 'Entendido');
					
					registrando = false;
						
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) { 
					ShowToastMessage(errorThrown + ' - ' + textStatus, 'error');
					registrando = false;
				}  
		});
	} catch(E) {
		alert(E.message);
	}
}