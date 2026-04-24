// JavaScript Document

var homeURL;

function InitializeValues(home) {
	homeURL = home;
	$('#btn-registrar-personal').on('click', function() {
		window.location.href = `${homeURL}/staff/add`;
	});
	GetStaff();
}

function GetStaff() {
	$.ajax({
        url: `${homeURL}/api/staff`,
		type: 'get',
		data: {
			search: ''
		},
		processData: false,
		contentType: false,
		dataType: "json",
		success: function(response) {
			var rows = '';
			console.log(response);
			$.each(response.data.staff, function(k, v) {
				rows += `<tr class="transition duration-300 ease-in-out border-b hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600 cursor-pointer">
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                <span class="font-medium capitalize text-dark dark:text-title-dark text-15">${v.nombre}</span>
                            </td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
								${v.domicilio}
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
								${v.puesto}
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                ${v.f_nacimiento}
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                ${v.telefono}
							</td>
                            <td class="ps-4 pe-4 py-2.5 font-normal last:text-end text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent rounded-e-[4px]">
                                <span class="${v.usuario != '' ? 'bg-primary' : 'bg-danger/10'} font-medium inline px-[11.85px] py-[4.5px] rounded-[15px] text-[13px] text-white">${v.usuario}</span>
                            </td>
                        </tr>`;
			});
			$('#table-staff').append(rows);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			console.log('STATUS:', textStatus);
			console.log('ERROR:', errorThrown);
			console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);

			alert(XMLHttpRequest.responseText);
		}  
	});
}