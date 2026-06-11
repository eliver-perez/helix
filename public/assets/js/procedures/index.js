// JavaScript Document

var homeURL;
var new_procedure = modify_procedure = registering_procedure = modifying_procedure = false;
var selected_procedure = '';

function InitializeValues(home) {
	homeURL = home;
	$('#btn-nuevo-servicio').on('click', NewProcedure);
	$('#btn-cancelar-servicio').on('click', CancelProcedure);
	GetProcedures();
}

function NewProcedure() {
	if(!new_procedure && !modify_procedure && !registering_procedure && !modifying_procedure) {
		EnableProcedure(false);
		new_procedure = true;
		$('#btn-nuevo-servicio').prop('disabled', true);
		$('#btn-modificar-servicio').prop('disabled', true);
		$('#btn-registrar-servicio').prop('disabled', false);
		$('#btn-cancelar-servicio').prop('disabled', false);
		$('#btn-nuevo-servicio').addClass('!visible hidden');
		$('#btn-modificar-servicio').addClass('!visible hidden');
		$('#btn-registrar-servicio').removeClass('!visible hidden');
		$('#btn-cancelar-servicio').removeClass('!visible hidden');
	}
}

function EnableProcedure(v) {
	$('#field-servicio-codigo').prop('disabled', v);
	$('#field-servicio').prop('disabled', v);
	$('#field-servicio-descripcion').prop('disabled', v);
	$('#field-servicio-duracion').prop('disabled', v);
	$('#field-servicio-costo-base').prop('disabled', v);
	$('#chk-servicio-requiere-material').prop('disabled', v);
	$('#chk-servicio-es-procedimiento').prop('disabled', v);
	$('#chk-servicio-activo').prop('disabled', v);
}

function CancelProcedure() {
	if(registering_procedure || modifying_procedure)
		return;
	if(new_procedure || modify_procedure) {
		EnableProcedure(true);
		ClearProcedure();
		new_procedure = false;
		modify_procedure = false;
		$('#btn-nuevo-servicio').prop('disabled', false);
		if(selected_procedure != '') {
			$('#btn-modificar-servicio').prop('disabled', false);
			$('#btn-modificar-servicio').removeClass('!visible hidden');
		}
		$('#btn-registrar-servicio').prop('disabled', true);
		$('#btn-cancelar-servicio').prop('disabled', true);
		$('#btn-nuevo-servicio').removeClass('!visible hidden');
		$('#btn-registrar-servicio').addClass('!visible hidden');
		$('#btn-cancelar-servicio').addClass('!visible hidden');
	}
}

function ClearProcedure() {
	$('#field-servicio-codigo').val('');
	$('#field-servicio').val('');
	$('#field-servicio-descripcion').val('');
	$('#field-servicio-duracion').val('');
	$('#field-servicio-costo-base').val('');
	$('#chk-servicio-requiere-material').prop('checked', false);
	$('#chk-servicio-es-procedimiento').prop('checked', false);
	$('#chk-servicio-activo').prop('checked', false);
}

function GetProcedures() {
	$.ajax({
        url: `${homeURL}/api/procedures`,
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
			$.each(response.data.procedures, function(k, v) {
				rows += `<tr onclick="SelectProcedure('${v.id}')" class="transition duration-300 ease-in-out border-b hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600 cursor-pointer">
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                <span class="font-medium capitalize text-dark dark:text-title-dark text-15">${v.procedure}</span>
                            </td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
								${v.duration} min
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
								${accounting.formatMoney(v.base_cost)}
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                ${v.material_required == 1 ? 'Si' : 'No'}
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                ${v.is_procedure == 1 ? 'Si' : 'No'}
							</td>
                            <td class="px-4 py-2.5 font-normal last:text-end capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
                                ${v.active == 1 ? 'Activo' : 'Inactivo'}
							</td>
                        </tr>`;
			});
			$('#table-procedures').append(rows);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			console.log('STATUS:', textStatus);
			console.log('ERROR:', errorThrown);
			console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);

			alert(XMLHttpRequest.responseText);
		}  
	});
}

function SelectProcedure(id) {
	alert(id);
}