function CerrarSesion(base_url = null) {
    $.ajax({
            url: `${typeof homeURL != 'undefined' ? homeURL : base_url}/api/auth/logout`,
            type: 'post',
		        dataType: "json",
            success: function(response) {
              console.log(response);
                if(response.status == 'OK')
                    window.location.href = `${typeof homeURL != 'undefined' ? `${homeURL}` : `${base_url}` ?? ''}/autenticacion/`;
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

function initDatePicker(id, onChangeCallback) {
  const elem = document.getElementById(id);
  if (!elem) return null;

  if (elem.datepicker) {
    return elem.datepicker;
  }

  const dp = new Datepicker(elem, {
    language: 'es',
    format: 'dd/mm/yyyy',
    nextArrow: '<i class="uil uil-angle-right-b"></i>',
    prevArrow: '<i class="uil uil-angle-left-b"></i>'
  });

  // Escuchar el evento de selección de fecha
  elem.addEventListener('changeDate', function(e) {
    if (onChangeCallback && typeof onChangeCallback === 'function') {
      // e.detail.date contiene el objeto de fecha seleccionado
      onChangeCallback(e.detail.date, elem.value);
    }
  });

  return dp;
}

function ActiveMenu(l) {
  const links = document.querySelectorAll(`a[href="${l}"]`);

  links.forEach(link => {
      link.classList.add('active');
  });
}

function escapeHTML(str) {
	return str
	  .replace(/&/g, "&amp;")
	  .replace(/'/g, "\\'")
	  .replace(/"/g, '&quot;')
	  .replace(/</g, "&lt;")
	  .replace(/>/g, "&gt;");
}

function formatTimeTo12h(time24) {
    if (!time24) return '';

    const [hoursStr, minutes] = time24.split(':');
    let hours = parseInt(hoursStr, 10);

    const period = hours >= 12 ? 'PM' : 'AM';

    hours = hours % 12;
    hours = hours === 0 ? 12 : hours; // 0 → 12

    return `${hours}:${minutes} ${period}`;
}

function callUrlTimer(seconds, span, url) {
    document.querySelector(`.${span}`).textContent = `${seconds} ${seconds == 1 ? 'segundo' : 'segundos'}`;
    const intervalId = setInterval(() => {
      seconds -= 1;

        if (seconds < 0) {
            clearInterval(intervalId);
            window.open(url, '_self');
        } else {
          document.querySelector(`.${span}`).textContent = `${seconds} ${seconds == 1 ? 'segundo' : 'segundos'}`;
        }
    }, 1000);
}