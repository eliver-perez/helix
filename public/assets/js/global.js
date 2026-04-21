function CerrarSesion() {
    $.ajax({
            url: `${homeURL}/api/auth/logout`,
            type: 'post',
            success: function(data) {
                alert(data);
                var returnedValue = typeof data === 'string' ? JSON.parse(data) : data;
            
                if(returnedValue.status == 'OK')
                    window.location.href = `${homeURL}`;
                else
                    ShowSweetAlert('error', '¡Ocurrio un Error!', 'Ocurrio un error al intentar realizar la autenticacion.', 'Entendido');	
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log('STATUS:', textStatus);
                console.log('ERROR:', errorThrown);
                console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);

                alert(XMLHttpRequest.responseText);
            }  
    });
}