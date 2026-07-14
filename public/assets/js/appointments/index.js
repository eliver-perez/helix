// JavaScript Document

var homeURL;

var calendar = null;
var selected_appointment = null;

let modalElement = null;
let calendarInstance = null;
let appointmentData = null;

function InitializeValues(home) {
	homeURL = home;
	if(action != '') {
		callBackActions();
	}
	$('#btn-new-appointment').on('click', function() {
		window.location.href = `${homeURL}/appointments/add`;
	});
	$('#btn-appointment-check-in').on('click', CheckInAppointment);
	$('#btn-appointment-cancel').on('click', CancelAppointment);
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

function callBackActions() {
	switch(action) {
		case 'schedule-success':
			ShowToastMessage('Cita agendada con exito.', 'success');
			break;
	}
}

function SetCalendar() {
    calendarEl = document.getElementById("calendario-agenda");
    if (calendarEl) {
      calendarInstance = new FullCalendar.Calendar(calendarEl, {
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
		eventMinHeight: 50,
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
		slotMinTime: '08:00:00',
    	slotMaxTime: '20:00:00',
		locale: 'es',
        eventDidMount: function (view) {
          document.querySelectorAll(".fc-list-day").forEach(function (item) {});
        },
        eventClick: function (infoEvent) {
			const event = infoEvent.event;
			appointmentData = event;

			selected_appointment = event.id;

          	modalElement = document.getElementById('e-info-modal');

			modalElement.querySelector('.e-info-title').textContent =
				(event.extendedProps.patient + ' - ' + event.extendedProps.appointment_type) || '';

			modalElement.querySelector('.e-info-date').textContent =
				event.start
					? event.start.toLocaleDateString('es-MX', {
						year: 'numeric',
						month: 'long',
						day: 'numeric'
					})
					: '';

			if(event.extendedProps.status == 'agendada' && modalElement.querySelector('.sec-check-in').classList.contains('hidden'))
				modalElement.querySelector('.sec-check-in').classList.remove('hidden');
			else if(event.extendedProps.status != 'agendada')
				modalElement.querySelector('.sec-check-in').classList.add('hidden');

			modalElement.querySelector('.e-info-time').textContent =
				formatEventTime(event);

			modalElement.querySelector('.e-info-age').textContent =
				event.extendedProps.age;

			modalElement.querySelector('.e-info-dob').textContent =
				event.extendedProps.dob;

			modalElement.querySelector('.e-info-patient-code').textContent =
				event.extendedProps.patient_code;

			modalElement.querySelector('.e-info-email').textContent =
				event.extendedProps.email;

			modalElement.querySelector('.e-info-phone').textContent =
				event.extendedProps.phone;

			modalElement.querySelector('.e-info-description').textContent =
				event.extendedProps.description || 'Sin descripción';

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

					failureCallback();
				}
			});
		}
      });

      calendarInstance.render();
      const listMonthButton = document.querySelector(".fc-button-group .fc-listMonth-button");
      if (listMonthButton) {
        const icon = document.createElement("i");
        icon.className = "uil uil-list-ul";
        listMonthButton.insertBefore(icon, listMonthButton.firstChild);
      }
    }
	modalElement.addEventListener('hidden.te.modal', function () {
		selected_appointment = null;
	});
}

function CheckInAppointment() {
	if(selected_appointment != null) {
		$.ajax({
			url: `${homeURL}/api/appointments/check-in`,
			type: 'post',
			data: {
				appointment: selected_appointment
			},
			dataType: "json",
			success: function(response) {
				console.log(response);
				if(response.success) {
					ShowToastMessage(response.message, 'success');
					if(response.data.appointment == selected_appointment)
						modalElement.querySelector('.sec-check-in').classList.add('hidden');
					modalElement.querySelector('.appointment-modal-close').click();
					calendarInstance.refetchEvents();
				} else {
					ShowToastMessage(response.message, 'error');
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				console.log('STATUS:', textStatus);
				console.log('ERROR:', errorThrown);
				console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);
			}  
		});
	}
}

function CancelAppointment() {
	if(selected_appointment != null) {
		const startDateObj = new Date(appointmentData.start);
		const endDateObj = new Date(appointmentData.end);

		const appointmentDate = startDateObj.toLocaleDateString('es-MX'); 
		console.log(appointmentDate);

		const appointmentStart = startDateObj.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
		console.log(appointmentStart);

		const appointmentEnd = endDateObj.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
		console.log(appointmentEnd);

		ShowSweetAlertConfirmCancelCallback('warning',
											'Cancelar Cita',
											`¿Deseas cancelar la cita de ${appointmentData.extendedProps.patient} 
											del día ${appointmentDate} en el horario ${appointmentStart} a ${appointmentEnd}?`,
											'Si',
											'No',
											(result) => {
			if(result.isConfirmed) {
				$.ajax({
					url: `${homeURL}/api/appointments/cancel`,
					type: 'post',
					data: {
						appointment: selected_appointment
					},
					dataType: "json",
					success: function(response) {
						console.log(response);
						if(response.success) {
							ShowToastMessage(response.message, 'success');
							modalElement.querySelector('.appointment-modal-close').click();
							calendarInstance.refetchEvents();
						} else {
							ShowToastMessage(response.message, 'error');
						}
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) {
						try {
							var response = JSON.parse(XMLHttpRequest.responseText);
							console.log(response.message);
							ShowToastMessage(response.message, 'error');
							
						} catch (e) {
							ShowToastMessage(XMLHttpRequest.responseText, 'error');
						}
					}  
				});
			}
		})
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