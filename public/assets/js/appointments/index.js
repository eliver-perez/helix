// JavaScript Document

var homeURL;

var calendar = null;

function InitializeValues(home) {
	homeURL = home;
	$('#btn-registrar-paciente').on('click', function() {
		window.location.href = `${homeURL}/appointments/add`;
	});
	SetCalendar();
	// GetAppointments();
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
        listDayFormat: true,
        allDaySlot: false,
        editable: true,
        eventSources: [],
        contentHeight: 800,
        initialView: "timeGridWeek",
		locale: 'es',
        eventDidMount: function (view) {
          document.querySelectorAll(".fc-list-day").forEach(function (item) {});
        },
        eventClick: function (infoEvent) {
          console.log(infoEvent.event.title);
          let infoModal = document.getElementById('e-info-modal');
          infoModal.modal("show");
          console.log(infoModal.find(".e-info-title"));
          infoModal.find(".e-info-title").text(infoEvent.event.title);
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
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) { 
					console.log('STATUS:', textStatus);
					console.log('ERROR:', errorThrown);
					console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);

					alert(XMLHttpRequest.responseText);
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