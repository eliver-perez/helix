// JavaScript Document

var homeURL;

let clientsModal = null;
let salesModal = null;
let productsModal = null;
let shiftModal = null;
let searchClientsTimer = null;
let searchSalesTimer = null;
let clientsSearch = 'empty';
let salesSearch = 'empty';

let cart = null;

let emptying_cart = false;

function InitializeValues(home) {
	homeURL = home;
	clientsModal = document.getElementById('modal-clients');
	salesModal = document.getElementById('modal-sales');
	productsModal = document.getElementById('modal-products');
	shiftModal = document.getElementById('modal-shifts');
	$('#btn-clients-modal').on('click', ShowClientsModal);
	$('#btn-sales-modal').on('click', ShowSalesModal);
	$('#btn-products-modal').on('click', ShowProductsModal);
	$('#btn-shift-modal').on('click', ShowShiftModal);
	$('#btn-register-payment').on('click', ValidateRegisterPayment);
	$('#btn-start-shift').on('click', InitializeCashReconciliation);
	document.addEventListener('click', function (e) {
		if (e.target.closest('.e-info-close')) {
			closeEventInfoModal();
		}

		if (e.target.id === 'e-info-modal') {
			closeEventInfoModal();
		}
	});
	$('#field-busqueda-ventas').on('keyup', () => {
		clearTimeout(searchSalesTimer);

		searchSalesTimer = setTimeout(() => {
			if(salesSearch != $('#field-busqueda-ventas').val())
				SearchSales();
			searchSalesTimer = null;
		}, 1500);
	});
	$('#field-busqueda-clientes').on('keyup', () => {
		clearTimeout(searchClientsTimer);

		searchClientsTimer = setTimeout(() => {
			if(clientsSearch != $('#field-busqueda-clientes').val())
				SearchClients();
			searchClientsTimer = null;
		}, 1500);
	});
	$('#field-cart-pay-amount').on('input', function() {
		const caret = getCaretPosition(this);

		let value = $(this).text();

		value = value.replace(/[^0-9.]/g, '');
		value = value.replace(/(\..*)\./g, '$1');

		const parts = value.split('.');

		if (parts[1]?.length > 2) {
			parts[1] = parts[1].substring(0, 2);
			value = parts.join('.');
		}

		if(parseFloat(value) > cart.balance_due)
			value = cart.balance_due;
		cart.payment_amount = value;

		$(this).text(accounting.formatMoney(value, ''));

		setCaretPosition(this, Math.min(caret, value.length));
	});
	$('.payment-method').on('click', function() {
		SelectPaymentMethod($(this).data('metodo'));
	});
	$('#field-payment-reference').on('change', ChangePaymentReference);
	$('#btn-empty-cart').on('click', EmptyCart);
	SearchSales();
	SearchClients();
	GetCashRegisters();
	cart = new CartClass();
	GetCart();
}

class CartClass {
	constructor() {
		this.id = null;
		this.client = null;
		this.client_name = null;
		this.patient = null;
		this.patient_name = null;
		this.sales = [];
		this.products = [];
		this.subtotal = 0;
		this.taxes = 0;
		this.total = 0;
		this.coupon = 0;
		this.discount = 0;
		this.balance_due = 0;
		this.payment_method = 'efectivo'
		this.payment_reference = '';
		this.payment_amount = 0
		this.payment_details = [];
	}
}
			
class SaleClass {
	constructor(id, folio, client_id, client, patient_id, patient, subtotal, taxes, total, discount, paid, balance_due, observations, details) {
		this.id = id;
		this.folio = folio;
		this.client_id = client_id;
		this.client = client;
		this.patient_id = patient_id;
		this.patient = patient;
		this.subtotal = subtotal;
		this.taxes = taxes;
		this.total = total;
		this.discount = discount;
		this.paid = paid;
		this.balance_due = balance_due;
		this.observations = observations;
		this.details = details;
	}
}

class SaleDetailsClass {
	constructor(id, service_id, service_code, service, product_id, product_code, product, description, quantity, unit_price, subtotal, taxes, total, discount, balance_due) {
		this.id = id;
		this.service_id = service_id;
		this.service_code = service_code;
		this.service = service;
		this.product_id = product_id;
		this.product_code = product_code;
		this.product = product;
		this.description = description;
		this.quantity = quantity;
		this.unit_price = unit_price;
		this.subtotal = subtotal;
		this.taxes = taxes;
		this.total = total;
		this.discount = discount;
		this.balance_due = balance_due;
	}
}

function ShowClientsModal() {
	const modal = new te.Modal(clientsModal);
	modal.show();
}

function ShowSalesModal() {
	const modal = new te.Modal(salesModal);
	modal.show();
}

function ShowProductsModal() {
	const modal = new te.Modal(productsModal);
	modal.show();
}

function ShowShiftModal() {
	const modal = new te.Modal(shiftModal);
	modal.show();
}

function ChangePaymentReference() {
	cart.payment_reference = $('#field-payment-reference').val();
}

function GetCashRegisters() {
	$.ajax({
        url: `${homeURL}/api/cash-register`,
		type: 'get',
		dataType: "json",
		success: function(response) {
			console.log(response);
			$.each(response.data.cash_registers, function(k, v) {
				$('#select-cortes-inicio-caja').append($('<option>', {
					value: v.id,
					text: `${v.register}`,
					'data-codigo': `${v.code}`
				}));
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

function GetCart() {
	cart = new CartClass();
	$('#table-cart tbody').empty();
	$.ajax({
        url: `${homeURL}/api/pos/cart`,
		type: 'get',
		dataType: "json",
		success: function(response) {
			console.log(response);
			if(response.success) {
				cart.id = response.data.cart.id;
				cart.client = response.data.cart.client;
				cart.client_name = response.data.cart.client_name;
				cart.patient = response.data.cart.patient;
				cart.patient_name = response.data.cart.patient_name;
				cart.coupon = response.data.cart.coupon;
				cart.discount = parseFloat(response.data.cart.discount ?? 0);
				cart.subtotal = parseFloat(response.data.cart.subtotal ?? 0);
				cart.taxes = parseFloat(response.data.cart.taxes ?? 0);
				cart.total = parseFloat(response.data.cart.total ?? 0);
				cart.balance_due = parseFloat(response.data.cart.balance_due ?? 0);
				cart.payment_amount = parseFloat(response.data.cart.balance_due ?? 0);
				$('#field-cliente').val(cart.client_name);
				$('#field-cart-subtotal').html(accounting.formatMoney(response.data.cart.balance_due));
				$('#field-cart-total').html(accounting.formatMoney(response.data.cart.balance_due));
				$('#field-cart-pay-amount').html(accounting.formatMoney(response.data.cart.balance_due, ''));
				$.each(response.data.cart.sales, function(k, v) {
					var sale = new SaleClass(
						v.id,
						v.folio,
						v.client_id,
						v.client,
						v.patient_id,
						v.patient,
						parseFloat(v.subtotal ?? 0),
						parseFloat(v.taxes ?? 0),
						parseFloat(v.total ?? 0),
						parseFloat(v.discount ?? 0),
						parseFloat(v.paid ?? 0),
						parseFloat(v.balance_due ?? 0),
						v.observations,
						[]
					);
					$.each(v.details, function(l, m) {
						var sale_details = new SaleDetailsClass(
							m.id,
							m.service_id,
							m.service_code,
							m.service,
							m.product_id,
							m.product_code,
							m.product,
							m.description,
							m.quantity,
							m.unit_price,
							m.subtotal,
							m.taxes,
							m.total,
							m.discount,
							m.balance_due
						);
						var balance_due = accounting.formatMoney(m.balance_due);
						if(parseFloat(m.balance_due) == 0)
							balance_due = `<span class="bg-primary rounded-[15px] py-[4px] px-[8.23px] text-[12px] font-medium leading-[13px] text-center text-white">Liquidado</span>`;
							var row = `<tr class="${sale.id}">
											<td class="ps-[25px] pe-4 py-2.5 text-start last:text-end group-hover:bg-transparent border-none before:hidden rounded-s-[4px]">
												<div class="flex items-center gap-x-[20px] gap-y-[10px]">
													<figure class="min-w-[80px] w-[80px] h-[80px] flex items-center justify-center bg-regular dark:bg-box-dark-up rounded-4 p-[5px]">
														<img class="object-cover placeholder-image" src="${homeURL}/assets/images/procedure_profile_pic.jpeg" alt="">
													</figure>
													<figcaption>
														<h6 class="capitalize mb-[8px] leading-[21px] text-dark dark:text-title-dark text-[15px] font-medium">${sale_details.description}</h6>
														<ul class="flex items-center gap-[10px] mb-0 capitalize">
															${
																sale.patient != null 
																	? '<li><span class="text-dark dark:text-title-dark me-[5px] text-[14px] font-medium">Paciente:</span><span class="text-body dark:text-subtitle-dark text-[13px]">' + sale.patient + '</span></li>' 
																	: ''
															}
														</ul>
													</figcaption>
												</div>
											</td>
											<td class="px-4 py-2.5 font-normal text-center capitalize text-[14px] text-light dark:text-subtitle-dark border-none group-hover:bg-transparent">
												${accounting.formatMoney(sale_details.unit_price)}
											</td>
											<td class="px-4 py-2.5 font-normal text-center capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
												<div class="flex items-center justify-center">
													<label class="text-[14px] font-medium text-dark">${parseInt(sale_details.quantity)}</label>
												</div>
											</td>
											<td class="ps-4 pe-[25px] py-2.5 font-normal text-center capitalize text-[14px] text-primary border-none group-hover:bg-transparent rounded-e-[4px]">
												${accounting.formatMoney(sale_details.total)}
											</td>
											<td class="ps-4 pe-[25px] py-2.5 font-normal text-center capitalize text-[14px] text-primary border-none group-hover:bg-transparent rounded-e-[4px]">
												${accounting.formatMoney(sale_details.paid)}
											</td>
											<td class="ps-4 pe-[25px] py-2.5 font-normal text-center capitalize text-[14px] text-primary border-none group-hover:bg-transparent rounded-e-[4px]">
												${balance_due}
											</td>
											<td class="px-4 py-2.5 font-normal text-center capitalize border-none group-hover:bg-transparent">
												<div class="flex items-center justify-center">
													<button
														type="button"
														class="w-[38px] h-[38px] flex items-center text-light-extra hover:bg-danger/10 justify-center text-[18px] cursor-pointer remove-event-wrapper uil uil-trash-alt hover:text-danger rounded-full"
														onclick="javascript:RemoveCartSale('${sale.id}')">
													</button>
												</div>
											</td>
										</tr>`;
							$('#table-cart tbody').append(row);
						sale.details.push(sale_details);
					});
					cart.sales.push(sale);
				});
				if(cart.sales.length == 0 && cart.products.length == 0)
					setHeaderCartIconEmpty();
				else
					setHeaderCartIcon(cart.sales.length + cart.products.length);
				console.log('cart: ', cart);
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			let response = JSON.parse(XMLHttpRequest.responseText);
			ShowToastMessage(response.message, 'error')
		}  
	});
}

function RemoveCartSale(id) {
	let data = JSON.stringify({
		sale: id
	});
	$.ajax({
        url: `${homeURL}/api/pos/cart`,
		type: 'post',
		data: {
			action: 'delete_sale',
			data: data,
		},
		dataType: "json",
		success: function(response) {
			console.log(response);
			if(response.success) {
				cart.id = response.data.cart.id;
				cart.client = response.data.cart.client;
				cart.client_name = response.data.cart.client_name;
				cart.patient = response.data.cart.patient;
				cart.patient_name = response.data.cart.patient_name;
				cart.coupon = response.data.cart.coupon;
				cart.discount = parseFloat(response.data.cart.discount ?? 0);
				cart.subtotal = parseFloat(response.data.cart.subtotal ?? 0);
				cart.taxes = parseFloat(response.data.cart.taxes ?? 0);
				cart.total = parseFloat(response.data.cart.total ?? 0);
				cart.balance_due = parseFloat(response.data.cart.balance_due ?? 0);
				cart.payment_amount = parseFloat(response.data.cart.balance_due ?? 0);
				$('#field-cliente').val(cart.client_name);
				$('#field-cart-subtotal').html(accounting.formatMoney(response.data.cart.balance_due));
				$('#field-cart-total').html(accounting.formatMoney(response.data.cart.balance_due));
				$('#field-cart-pay-amount').html(accounting.formatMoney(response.data.cart.balance_due, ''));
				$(`.${response.data.cart.sale}`).remove();
				for(var i = 0; i < cart.sales.length; i++) {
					if(response.data.cart.sale === cart.sales[i].id) {
						cart.sales.splice(i, 1);
					}
				}
				if(cart.sales.length == 0 && cart.products.length == 0)
					setHeaderCartIconEmpty();
				else
					setHeaderCartIcon(cart.sales.length + cart.products.length);
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			// alert(XMLHttpRequest.responseText);
			let response = JSON.parse(XMLHttpRequest.responseText);
			ShowToastMessage(response.message, 'error')
		}  
	});
}

function SearchClients() {
	clientsSearch = $('#field-busqueda-clientes').val();
	$('#table-clientes tbody').empty();
	$.ajax({
        url: `${homeURL}/api/clients`,
		type: 'get',
		data: {
			search: clientsSearch
		},
		dataType: "json",
		success: function(response) {
			// console.log(response);
			if(response.success) {
				$.each(response.data.clients, function(k, v) {
					$('#table-clientes tbody').append(`<tr class="group cursor-pointer transition duration-300 ease-in-out border-b hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600" onclick="javascript:SelectClient('${v.id}', '${escapeHTML(v.name)}');">
															<td class="ps-0 pe-4 py-2.5 text-start last:text-end text-dark dark:text-title-dark group-hover:bg-transparent text-15 font-medium border-none before:hidden rounded-s-[4px]">
																<div class="flex items-center">
																	<span class="font-medium capitalize text-dark dark:text-title-dark text-15">${v.code}</span>
																</div>
															</td>
															<td class="ps-0 pe-4 py-2.5 text-start last:text-end text-dark dark:text-title-dark group-hover:bg-transparent text-15 font-medium border-none before:hidden rounded-s-[4px]">
																<div class="flex items-center">
																	<span class="font-medium capitalize text-dark dark:text-title-dark text-15">${v.name}</span>
																</div>
															</td>
															<td class="px-4 py-2.5 font-normal last:text-end text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">${v.address}</td>
															<td class="px-4 py-2.5 font-normal last:text-end text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">${v.phone}</td>
															<td class="px-4 py-2.5 font-normal last:text-end text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">${v.email}</td>
														</tr>`);
				});
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			let response = JSON.parse(XMLHttpRequest.responseText);
			ShowToastMessage(response.message, 'error')
		}  
	});
}

function SearchSales() {
	salesSearch = $('#field-busqueda-ventas').val();
	$('#table-ventas tbody').empty();
	$.ajax({
        url: `${homeURL}/api/sales`,
		type: 'get',
		data: {
			search: salesSearch,
			status: 'pendiente'
		},
		dataType: "json",
		success: function(response) {
			// console.log(response);
			if(response.success) {
				$.each(response.data.sales, function(k, v) {
					$('#table-ventas tbody').append(`<tr class="group cursor-pointer transition duration-300 ease-in-out border-b hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600" onclick="javascript:SelectSale('${v.id}');">
															<td class="px-4 py-2.5 font-normal text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
																<div class="flex items-center">
																	<span class="font-medium capitalize text-dark dark:text-title-dark text-15">${v.name}</span>
																</div>
															</td>
															<td class="px-4 py-2.5 font-normal text-[14px] text-center text-dark dark:text-title-dark border-none group-hover:bg-transparent">${v.sale_date}</td>
															<td class="px-4 py-2.5 font-normal text-[14px] text-center text-dark dark:text-title-dark border-none group-hover:bg-transparent">${accounting.formatMoney(v.total)}</td>
															<td class="px-4 py-2.5 font-normal text-[14px] text-center text-dark dark:text-title-dark border-none group-hover:bg-transparent">${accounting.formatMoney(v.paid)}</td>
															<td class="px-4 py-2.5 font-normal text-[14px] text-center text-dark dark:text-title-dark border-none group-hover:bg-transparent">${accounting.formatMoney(v.debt)}</td>
														</tr>`);
				});
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			let response = JSON.parse(XMLHttpRequest.responseText);
			ShowToastMessage(response.message, 'error')
		}  
	});
}

function SelectClient(id, name) {
	$('#btn-close-clients-modal').trigger('click');
	let data = JSON.stringify({
		client: id
	});
	$.ajax({
        url: `${homeURL}/api/pos/cart`,
		type: 'post',
		data: {
			action: 'select_client',
			data: data
		},
		dataType: "json",
		success: function(response) {
			console.log(response);
			if(response.success) {
				cart.client = response.data.client;
				cart.client_name = response.data.client_name;
				$('#field-cliente').val(response.data.cart.client_name);
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert(XMLHttpRequest.responseText);
			console.log(XMLHttpRequest.responseText);
			let response = JSON.parse(XMLHttpRequest.responseText);
			ShowToastMessage(response.message, 'error')
		}  
	});
}

function SelectSale(id) {
	$('#btn-close-sales-modal').trigger('click');
	let data = JSON.stringify({
		sale: id
	});
	$.ajax({
        url: `${homeURL}/api/pos/cart`,
		type: 'post',
		data: {
			action: 'add_sale',
			data: data
		},
		dataType: "json",
		success: function(response) {
			console.log(response);
			if(response.success) {
				cart.id = response.data.cart.id;
				cart.client = response.data.cart.client;
				cart.client_name = response.data.cart.client_name;
				cart.patient = response.data.cart.patient;
				cart.patient_name = response.data.cart.patient_name;
				cart.coupon = response.data.cart.coupon;
				cart.discount = parseFloat(response.data.cart.discount ?? 0);
				cart.subtotal = parseFloat(response.data.cart.subtotal ?? 0);
				cart.taxes = parseFloat(response.data.cart.taxes ?? 0);
				cart.total = parseFloat(response.data.cart.total ?? 0);
				cart.balance_due = parseFloat(response.data.cart.balance_due ?? 0);
				cart.payment_amount = parseFloat(response.data.cart.balance_due ?? 0);
				var sale = new SaleClass(
						response.data.cart.sale.id,
						response.data.cart.sale.folio,
						response.data.cart.sale.client_id,
						response.data.cart.sale.client,
						response.data.cart.sale.patient_id,
						response.data.cart.sale.patient,
						parseFloat(response.data.cart.sale.subtotal ?? 0),
						parseFloat(response.data.cart.sale.taxes ?? 0),
						parseFloat(response.data.cart.sale.total ?? 0),
						parseFloat(response.data.cart.sale.discount ?? 0),
						parseFloat(response.data.cart.sale.paid ?? 0),
						parseFloat(response.data.cart.sale.balance_due ?? 0),
						response.data.cart.sale.observations,
						[]
					);
				$('#field-cliente').val(cart.client_name);
				$('#field-cart-subtotal').html(accounting.formatMoney(response.data.cart.balance_due));
				$('#field-cart-total').html(accounting.formatMoney(response.data.cart.balance_due));
				$('#field-cart-pay-amount').html(accounting.formatMoney(response.data.cart.balance_due, ''));
				$.each(response.data.cart.sale.details, function(k, v) {
					var sale_details = new SaleDetailsClass(
						v.id,
						v.service_id,
						v.service_code,
						v.service,
						v.product_id,
						v.product_code,
						v.product,
						v.description,
						v.quantity,
						v.unit_price,
						v.subtotal,
						v.taxes,
						v.total,
						v.discount,
						v.balance_due
					);
					var balance_due = accounting.formatMoney(v.balance_due);
					if(parseFloat(v.balance_due) == 0)
						balance_due = `<span class="bg-primary rounded-[15px] py-[4px] px-[8.23px] text-[12px] font-medium leading-[13px] text-center text-white">Liquidado</span>`;
					$('#table-cart').append(`<tr class="${sale.id}">
												<td class="ps-[25px] pe-4 py-2.5 text-start last:text-end group-hover:bg-transparent border-none before:hidden rounded-s-[4px]">
													<div class="flex items-center gap-x-[20px] gap-y-[10px]">
														<figure class="min-w-[80px] w-[80px] h-[80px] flex items-center justify-center bg-regular dark:bg-box-dark-up rounded-4 p-[5px]">
															<img class="object-cover placeholder-image" src="${homeURL}/assets/images/procedure_profile_pic.jpeg" alt="">
														</figure>
														<figcaption>
															<h6 class="capitalize mb-[8px] leading-[21px] text-dark dark:text-title-dark text-[15px] font-medium">${v.description}</h6>
															<ul class="flex items-center gap-[10px] mb-0 capitalize">
																<li>
																	<span class="text-dark dark:text-title-dark me-[5px] text-[14px] font-medium">Paciente:</span>
																	<span class="text-body dark:text-subtitle-dark text-[13px]">${response.data.cart.sale.patient ?? ''}</span>
																</li>
															</ul>
														</figcaption>
													</div>
												</td>
												<td class="px-4 py-2.5 font-normal text-center capitalize text-[14px] text-light dark:text-subtitle-dark border-none group-hover:bg-transparent">
													${accounting.formatMoney(v.unit_price)}
												</td>
												<td class="px-4 py-2.5 font-normal text-center capitalize text-[14px] text-dark dark:text-title-dark border-none group-hover:bg-transparent">
													<div class="flex items-center justify-center">
														<label class="text-[14px] font-medium text-dark">${parseInt(v.quantity)}</label>
													</div>
												</td>
												<td class="ps-4 pe-[25px] py-2.5 font-normal text-center capitalize text-[14px] text-primary border-none group-hover:bg-transparent rounded-e-[4px]">
													${accounting.formatMoney(v.total)}
												</td>
												<td class="ps-4 pe-[25px] py-2.5 font-normal text-center capitalize text-[14px] text-primary border-none group-hover:bg-transparent rounded-e-[4px]">
													${accounting.formatMoney(v.paid)}
												</td>
												<td class="ps-4 pe-[25px] py-2.5 font-normal text-center capitalize text-[14px] text-primary border-none group-hover:bg-transparent rounded-e-[4px]">
													${balance_due}
												</td>
												<td class="px-4 py-2.5 font-normal text-center capitalize border-none group-hover:bg-transparent">
													<div class="flex items-center justify-center">
														<button
															type="button"
															class="w-[38px] h-[38px] flex items-center text-light-extra hover:bg-danger/10 justify-center text-[18px] cursor-pointer remove-event-wrapper uil uil-trash-alt hover:text-danger rounded-full"
															onclick="javascript:RemoveCartSale('${sale.id}')">
														</button>
													</div>
												</td>
											</tr>`);
					sale.details.push(sale_details);
				});
				cart.sales.push(sale);
				if(cart.sales.length == 0 && cart.products.length == 0)
					setHeaderCartIconEmpty();
				else
					setHeaderCartIcon(cart.sales.length + cart.products.length);
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			// alert(XMLHttpRequest.responseText);
			console.log(XMLHttpRequest.responseText);
			let response = JSON.parse(XMLHttpRequest.responseText);
			ShowToastMessage(response.message, 'error')
		}  
	});
}

function SelectPaymentMethod(method) {
	if(cart.payment_method != method) {
		$('#label-payment-method').html(method);
		if(method === 'efectivo') {
			if(!$('.sector-payment-reference').hasClass('hidden')) {
				$('.sector-payment-reference').addClass('hidden');
			}
			$("#field-payment-reference").val('');
			cart.payment_reference = '';
		} else {
			if($('.sector-payment-reference').hasClass('hidden')) {
				$('.sector-payment-reference').removeClass('hidden');
			}
		}
		$(`#btn-payment-method-${cart.payment_method}`).removeClass('text-primary');
		$(`#btn-payment-method-${cart.payment_method}`).addClass('text-[#a0a0a0]');
		cart.payment_method = method;
		$(`#btn-payment-method-${cart.payment_method}`).removeClass('text-[#a0a0a0]');
		$(`#btn-payment-method-${cart.payment_method}`).addClass('text-primary');
	}
}

function ValidateRegisterPayment() {
	if(cart.balance_due <= 0) {
		ShowToastMessage('No hay un adeudo pendiente de pagar.', 'error');
		return;
	}
	if(cart.payment_amount <= 0) {
		ShowToastMessage('Ingresa un monto de pago mayor a 0.', 'error');
		return;
	}
	ShowSweetAlertConfirmCancelCallback('question',
										'Registrar Pago',
										'Confirma si los datos capturados son correctos para proceder con el registro de pago de las ventas/productos.',
										'Registrar Pago',
										'Cancelar',
										(result) => {
											console.log(result)
											if(result.isConfirmed) {
												RegisterPayment();
											}
										});
}

function RegisterPayment() {
	$.ajax({
        url: `${homeURL}/api/pos/checkout`,
		type: 'post',
		data: {
			cart: JSON.stringify(cart)
		},
		dataType: "json",
		success: function(response) {
			console.log(response);
			if(response.success) {
				ShowToastMessage('Pago Registrado.', 'success');
				GetCart();
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			console.log(XMLHttpRequest.responseText);
			let response = JSON.parse(XMLHttpRequest.responseText);
			ShowToastMessage(response.message, 'error')
		}  
	});
}

function InitializeCashReconciliation() {
	if($('#field-corte-inicio-monto').val() != '' && !isNaN($('#field-corte-inicio-monto').val()) && parseFloat($('#field-corte-inicio-monto').val()) > 0) {
		$.ajax({
			url: `${homeURL}/api/cash-reconciliation`,
			type: 'post',
			data: {
				initialize_amount: $('#field-corte-inicio-monto').val(),
				cash_register: $('#select-cortes-inicio-caja').val()
			},
			dataType: "json",
			success: function(response) {
				console.log(response);
				if(response.success) {
					$('#btn-close-shifts-modal').trigger('click');
					$('#btn-shift-modal').remove();
					$('#modal-shifts').remove();
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				// console.log(XMLHttpRequest.responseText);
				let response = JSON.parse(XMLHttpRequest.responseText);
				ShowToastMessage(response.message, 'error')
			}  
		});
	}
}

function EmptyCart() {
	if(!emptying_cart) {
		$.ajax({
			url: `${homeURL}/api/pos/empty-cart`,
			type: 'post',
			dataType: "json",
			success: function(response) {
				console.log(response);
				if(response.success) {
					GetCart();
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				let response = JSON.parse(XMLHttpRequest.responseText);
				ShowToastMessage(response.message, 'error')
			}  
		});
	}
}