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

Datepicker.locales.es = {
  days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"],
  daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
  daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
  months: [
    "Enero", "Febrero", "Marzo", "Abril",
    "Mayo", "Junio", "Julio", "Agosto",
    "Septiembre", "Octubre", "Noviembre", "Diciembre"
  ],
  monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
  today: "Hoy",
  clear: "Limpiar",
  titleFormat: "MM yyyy", // título arriba (ej: Abril 2026)
  weekStart: 0 // 0 = domingo, 1 = lunes
};

function initDatePicker(id) {
  const elem = document.getElementById(id);
  if (!elem) return null;

  if (elem.datepicker) {
    return elem.datepicker;
  }

  return new Datepicker(elem, {
    language: 'es',
    format: 'dd/mm/yyyy',
    nextArrow: '<i class="uil uil-angle-right-b"></i>',
    prevArrow: '<i class="uil uil-angle-left-b"></i>'
  });
}

function ActiveMenu(l) {
  const links = document.querySelectorAll(`a[href="${l}"]`);

  links.forEach(link => {
      link.classList.add('active');
  });
}