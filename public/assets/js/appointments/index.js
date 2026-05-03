// JavaScript Document

var homeURL;

var calendar = null;

function InitializeValues(home) {
	homeURL = home;
	$('#btn-registrar-paciente').on('click', function() {
		window.location.href = `${homeURL}/appointments/add`;
	});
	document.addEventListener('click', function (e) {
		if (e.target.closest('.e-info-close')) {
			closeEventInfoModal();
		}

		if (e.target.id === 'e-info-modal') {
			closeEventInfoModal();
		}
	});
	initDatePicker('mini-calendario');
	GetAppointmentsStatus();
	SetCalendar();
}

function SetCalendar() {
    calendar = document.getElementById("calendario-agenda");
    if (calendar) {
      var calendar = new FullCalendar.Calendar(calendar, {
        headerToolbar: {
          left: "today,prev,title,next",
          right: "timeGridDay,timeGridWeek,dayGridMonth,listMonth",
        },
        views: {
          listMonth: {
            buttonText: "Agenda",
            titleFormat: { month: "short", weekday: "short" },
          }
        },
		buttonText: {
			today: 'Hoy',
			month: 'Mes',
			week: 'Semana',
			day: 'Día',
			list: 'Agenda'
		},
		noEventsText: 'No hay citas para mostrar',
		titleFormat: function(date) {
			const formatter = new Intl.DateTimeFormat('es-MX', {
				month: 'long',
				year: 'numeric'
			});

			const text = formatter.format(date.date.marker);
			return text.charAt(0).toUpperCase() + text.slice(1);
		},
		eventTimeFormat: {
			hour: 'numeric',
			minute: '2-digit',
			meridiem: 'short',
			hour12: true
		},
        listDayFormat: true,
        allDaySlot: false,
        editable: false,
		lazyFetching: false,
        eventSources: [ ],
        contentHeight: 800,
        initialView: "timeGridWeek",
		locale: 'es',
        eventDidMount: function (view) {
          document.querySelectorAll(".fc-list-day").forEach(function (item) {});
        },
        eventClick: function (infoEvent) {
          	const modalElement = document.getElementById('e-info-modal');

			modalElement.querySelector('.e-info-title').textContent =
				infoEvent.event.title || '';

			// modalElement.querySelector('.e-info-date').textContent =
			// 	infoEvent.event.start
			// 		? infoEvent.event.start.toLocaleDateString('es-MX', {
			// 			weekday: 'long',
			// 			year: 'numeric',
			// 			month: 'long',
			// 			day: 'numeric'
			// 		})
			// 		: '';
			modalElement.querySelector('.e-info-date').textContent =
				infoEvent.event.start
					? infoEvent.event.start.toLocaleDateString('es-MX', {
						year: 'numeric',
						month: 'long',
						day: 'numeric'
					})
					: '';

			modalElement.querySelector('.e-info-time').textContent =
				formatEventTime(infoEvent.event);

			modalElement.querySelector('.e-info-age').textContent =
				infoEvent.event.extendedProps.age;

			modalElement.querySelector('.e-info-dob').textContent =
				infoEvent.event.extendedProps.dob;

			modalElement.querySelector('.e-info-patient-code').textContent =
				infoEvent.event.extendedProps.patient_code;

			modalElement.querySelector('.e-info-email').textContent =
				infoEvent.event.extendedProps.email;

			modalElement.querySelector('.e-info-phone').textContent =
				infoEvent.event.extendedProps.phone;

			modalElement.querySelector('.e-info-description').textContent =
				infoEvent.event.extendedProps.description || 'Sin descripción';

			const modal = new te.Modal(modalElement);
			modal.show();
        },
		events: function(info, successCallback, failureCallback) {
			$.ajax({
				url: `${homeURL}/api/appointments/calendar`,
				method: 'GET',
				dataType: 'json',
				data: {
					start: info.startStr,
					end: info.endStr
				},
				success: function(response) {
					console.log(response);
					successCallback(response.data.appointments);
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) { 
					console.log('STATUS:', textStatus);
					console.log('ERROR:', errorThrown);
					console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);

					alert(XMLHttpRequest.responseText);
					failureCallback();
				}
			});
		}
      });

      calendar.render();
      const listMonthButton = document.querySelector(".fc-button-group .fc-listMonth-button");
      if (listMonthButton) {
        const icon = document.createElement("i");
        icon.className = "uil uil-list-ul";
        listMonthButton.insertBefore(icon, listMonthButton.firstChild);
      }
    }
}

function GetAppointmentsStatus() {
	$('#sector-estatus').html('');
	$.ajax({
        url: `${homeURL}/api/appointments/status`,
		type: 'get',
		data: {
			search: ''
		},
		processData: false,
		contentType: false,
		dataType: "json",
		success: function(response) {
			var options = '';
			$.each(response.data.appointments_status, function(k, v) {
				options += `<li class="flex items-center mb-[10px]">
                                    <span 
                                        class="appointment-li-status-item text-sm capitalize"
                                        style="--dot-color: ${v.text_color};">
                                        ${v.estatus}
                                    </span>
                            </li>`;
			});
			$('#sector-estatus').html(options);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			console.log('STATUS:', textStatus);
			console.log('ERROR:', errorThrown);
			console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);

			alert(XMLHttpRequest.responseText);
		}  
	});
}

function GetAppointments() {
	$('#table-appointments').find('tbody').empty();
	$.ajax({
        url: `${homeURL}/api/appointments`,
		type: 'get',
		data: {
			search: ''
		},
		processData: false,
		contentType: false,
		dataType: "json",
		success: function(response) {
			console.log(response);
			var rows = '';
			$.each(response.data.appointments, function(k, v) {
				rows += `<tr class="transition duration-300 ease-in-out border-b hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600 cursor-pointer">
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                <span class="font-medium capitalize text-dark dark:text-title-dark text-15">${v.clave}</span>
                            </td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
								${v.nombre}
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
								${v.f_nacimiento}
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                ${v.genero}
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                ${v.telefono}
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                ${v.movil}
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                ${v.f_ultima_visita}
							</td>
                        </tr>`;
			});
			$('#table-appointments').find('tbody').append(rows);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			console.log('STATUS:', textStatus);
			console.log('ERROR:', errorThrown);
			console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);

			alert(XMLHttpRequest.responseText);
		}  
	});
}

function formatEventTime(event) {
    if (!event.start) return '';

    const start = event.start.toLocaleTimeString('es-MX', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });

    if (!event.end) return start;

    const end = event.end.toLocaleTimeString('es-MX', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });

    return `${start} – ${end}`;
}