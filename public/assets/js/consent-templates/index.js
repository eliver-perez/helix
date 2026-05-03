// JavaScript Document

var homeURL;
var selected_template = '';
var new_template = false, modify_template = false, registering_template = false, modifying_template = false;
var template = null;

function InitializeValues(home) {
	homeURL = home;
	$('#btn-plantilla-nueva').on('click', function() {
		if(!new_template && !modify_template && !registering_template && !modifying_template) {
			NewTemplate();
		}
	});
	$('#btn-guardar-cambios').on('click', UpdateTemplate);
	SetupEditor();
	GetTemplates();
}

const Embed = Quill.import('blots/embed');

class VariableBlot extends Embed {
  static create(value) {
    const node = super.create();

	const key = value?.key || '';
    const label = value?.label || '';

    node.setAttribute('contenteditable', 'false');
    node.setAttribute('data-key', key);
    node.setAttribute('data-label', label);
    node.classList.add('ql-variable');

    node.innerText = `{{${label}}}`;

    return node;
  }

  static value(node) {
	const key = node.getAttribute('data-key');
	const label = node.getAttribute('data-label');

	if (!key || !label) return false;

	return { key, label };
  }
}

VariableBlot.blotName = 'variable';
VariableBlot.tagName = 'span';
VariableBlot.className = 'ql-variable';

Quill.register(VariableBlot);

function insertarVariable(quill, key, label) {
  if (!label) return; // <-- esto evita {{null}}

  const range = quill.getSelection(true);
  const index = range ? range.index : quill.getLength();

  quill.insertEmbed(index, 'variable', { key, label }, 'user');
  quill.insertText(index + 1, ' ', 'user');
  quill.setSelection(index + 2, 0, 'user');
}

function SetupEditor() {
	console.log($('#toolbar'));
	// const selector = document.getElementById('#editor-plantilla');
  	const selector = document.querySelector('.editor-plantilla');
	template = new Quill('#editor-plantilla', {
		modules: {
			toolbar: {
				container: '#toolbar',
				handlers: {
					'convenio': function () {
						insertarVariable(this.quill, 'no_convenio', 'NO. CONVENIO');
					},
					'organismo': function () {
						insertarVariable(this.quill, 'organismo', 'ORGANISMO');
					},
				}
			}
		},
		placeholder: 'Diseña tu formato...',
		theme: 'snow'
	});
}

function GetTemplates() {
	$('#table-templates').find('tbody').empty();
	$.ajax({
        url: `${homeURL}/api/consent-templates`,
		type: 'get',
		data: {
			search: ''
		},
		processData: false,
		contentType: false,
		dataType: "json",
		success: function(response) {
			$.each(response.data.templates, function(k, v) {
				$('#table-templates').find('tbody').append(`<tr onclick="javascript:SelectTemplate('${v.id}');" class="transition duration-300 ease-in-out border-b hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600 cursor-pointer">
													<td class="px-4 py-2.5 font-normal capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
														<span class="font-medium capitalize text-dark dark:text-title-dark text-15">${v.template_name}</span>
													</td>
													<td class="px-4 py-2.5 font-normal capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
														${v.version}
													</td>
													<td class="px-4 py-2.5 font-normal capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
														${v.status}
													</td>
													<td class="px-4 py-2.5 font-normal capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
														${v.registered_by}
													</td>
													<td class="px-4 py-2.5 font-normal last:text-start capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
														${v.registered_date}
													</td>
												</tr>`)
			});
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			console.log('STATUS:', textStatus);
			console.log('ERROR:', errorThrown);
			console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);

			alert(XMLHttpRequest.responseText);
		}  
	});
}

function SelectTemplate(id) {
	if(selected_template != id) {
		ClearTemplateData();
		selected_template = id;
		$.ajax({
			url: `${homeURL}/api/consent-templates/${id}`,
			type: 'get',
			dataType: "json",
			success: function(response) {
				if($('#accordion-sector-datos-generales').find('button').attr('aria-expanded') === 'false')
					$('#accordion-sector-datos-generales').find('button').trigger('click');
				$('#field-codigo').val(response.data.template.codigo);
				$('#field-nombre').val(response.data.template.nombre);
				$('#field-version').val(response.data.template.version);
				$('#field-estatus').val(response.data.template.estatus);
				$('#field-fecha-actualizado').val(response.data.template.f_actualizacion);
				$('#field-fecha-registrado').val(response.data.template.f_registro);
				$('#field-registro').val(response.data.template.registro);
				if(response.data.template.estatus_codigo == 'borrador')
					$('#btn-guardar-cambios').removeClass('!visible hidden');
				
				$('#btn-visualizar-plantilla').attr('disabled', false);
				template.setContents(JSON.parse(response.data.template.delta));
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				console.log('STATUS:', textStatus);
				console.log('ERROR:', errorThrown);
				console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);
				alert(XMLHttpRequest.responseText);
			}  
		});
	}
}

function EnableRegister(v) {
	$('#btn-plantilla-baja').attr('disabled', v);
	$('#btn-plantilla-activar').attr('disabled', v);
	$('#btn-plantilla-nueva').attr('disabled', v);
	$('#field-codigo').attr('disabled', !v);
	$('#field-nombre').attr('disabled', !v);
	if(v) {
		$('#sector-new-template-buttons').html(`<button id="btn-cancelar-plantilla" type="button" class="flex items-center px-[20px] text-sm text-white rounded-md font-semibold bg-danger border-danger hover:bg-danger-hbr h-10 gap-[6px] transition-[0.3s]">
													<i class="uil uil-trash-alt"></i>
													<span class="m-0">Cancelar</span>
												</button>
												<button id="btn-registrar-plantilla" type="submit" class="flex items-center px-[20px] text-sm text-white rounded-md font-semibold bg-primary border-primary hover:bg-primary-hbr h-10 gap-[6px] transition-[0.3s]">
													<i class="uil uil-plus"></i>
													<span class="m-0">Guardar Plantilla</span>
												</button>`);
		$('#btn-cancelar-plantilla').on('click', CancelTemplateRegister);
	} else {
		$('#sector-new-template-buttons').html(``);
	}
}

function RegisterTemplate() {
    var formElement = $('#form-register-template')[0]; 
    var formData = new FormData(formElement);

	$.ajax({
        url: `${homeURL}/api/consent-templates`,
		type: 'post',
		data: formData,
		processData: false,
		contentType: false,
		dataType: "json",
		success: function(response) {
			ShowToastMessage('Plantilla registrada con exito', 'success');
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			console.log('STATUS:', textStatus);
			console.log('ERROR:', errorThrown);
			console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);

			alert(XMLHttpRequest.responseText);
		}  
	});
}

function CancelTemplateRegister() {
	if(new_template) {
		if(registering_template) {
			ShowToastMessage('Se esta registrando una plantilla, no es posible cancelar.', 'error');
			return;
		}
		new_template = false;
		ClearTemplateData();
		EnableRegister(false);
		if($('#accordion-sector-datos-generales').find('button').attr('aria-expanded') === 'true')
			$('#accordion-sector-datos-generales').find('button').trigger('click');
	}
}

function ClearTemplateData() {
	$('#field-codigo').val('');
	$('#field-nombre').val('');
	$('#field-version').val('');
	$('#field-estatus').val('');
	$('#field-fecha-registrado').val('');
	$('#field-fecha-actualizado').val('');
	$('#field-registro').val('');
	$('#btn-visualizar-plantilla').attr('disabled', true);
	if(!$('#btn-guardar-cambios').hasClass('hidden'))
		$('#btn-guardar-cambios').addClass('!visible hidden');
}

function NewTemplate() {
	new_template = true;
	ClearTemplateData();
	EnableRegister(true);
	if($('#accordion-sector-datos-generales').find('button').attr('aria-expanded') === 'false')
		$('#accordion-sector-datos-generales').find('button').trigger('click');
	$('#field-codigo').focus();
	
}

function UpdateTemplate() {
	if(selected_template != '') {
		var formElement = $('#form-register-template')[0]; 
		var formData = new FormData(formElement);
		
		const html = template.root.innerHTML;
		const delta = template.getContents();
    	const logo = document.getElementById('file-logo').files[0] || null;
		
		formData.append('template_html', html);
		formData.append('template_delta', JSON.stringify(delta));
		formData.append('_method', 'PUT');

		if (logo) {
			formData.append('file-logo-1', logo);
		}

		$.ajax({
			url: `${homeURL}/api/consent-templates/${selected_template}`,
			type: 'post',
			data: formData,
			processData: false,
			contentType: false,
			dataType: "json",
			success: function(response) {
				ShowToastMessage('Plantilla registrada con exito', 'success');
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				console.log('STATUS:', textStatus);
				console.log('ERROR:', errorThrown);
				console.log('RESPONSE TEXT:', XMLHttpRequest.responseText);

				alert(XMLHttpRequest.responseText);
			}  
		});
	}
}