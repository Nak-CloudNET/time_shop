<script type="text/javascript">
    var count = 1, an = 1, DT = <?= $Settings->default_tax_rate ?>, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0, shipping = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(window).load(function(){
		$('.is_normal_quotation').iCheck('check').trigger('change');
		$(".type_payment").trigger('change');
		$(".paid_by_by1").trigger('change');
		$(".paid_by_by2").trigger('change');
		$(".paid_by_by3").trigger('change');
	});
    $(document).ready(function () {
        <?php if ($inv) { ?>
        localStorage.setItem('qudate', '<?= date($dateFormats['php_ldate'], strtotime($inv->date))?>');
        localStorage.setItem('qucustomer', '<?=$inv->customer_id?>');
        localStorage.setItem('qubiller', '<?=$inv->biller_id?>');
        localStorage.setItem('quref', '<?=$inv->reference_no?>');
        localStorage.setItem('quwarehouse', '<?=$inv->warehouse_id?>');
        localStorage.setItem('qustatus', '<?=$inv->status?>');
        localStorage.setItem('qunote', '<?= str_replace(array("\r", "\n"), "", $this->erp->decode_html($inv->note)); ?>');
        localStorage.setItem('qudiscount', '<?=$inv->order_discount_id?>');
        localStorage.setItem('qutax2', '<?=$inv->order_tax_id?>');
        localStorage.setItem('qushipping', '<?=$inv->shipping?>');
        localStorage.setItem('quitems', JSON.stringify(<?=$inv_items;?>));
		localStorage.setItem('paid_by', '<?= $payment_full->paid_by; ?>')
        <?php } ?>
        <?php if ($Owner || $Admin) { ?>
        $(document).on('change', '#qudate', function (e) {
            localStorage.setItem('qudate', $(this).val());
        });
        if (qudate = localStorage.getItem('qudate')) {
            $('#qudate').val(qudate);
        }
        $(document).on('change', '#qubiller', function (e) {
            localStorage.setItem('qubiller', $(this).val());
        });
        if (qubiller = localStorage.getItem('qubiller')) {
            $('#qubiller').val(qubiller);
        }
        <?php } ?>
        ItemnTotals();
        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#qucustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    //response('');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('quotes/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#quwarehouse").val(),
                        customer_id: $("#qucustomer").val()
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 200,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    //$(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
					// $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_invoice_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        $('#add_item').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $(this).autocomplete("search");
            }
        });

        $(window).bind('beforeunload', function (e) {
            $.get('<?= site_url('welcome/set_data/remove_quls/1'); ?>');
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function (e) {
            $(window).unbind('beforeunload');
        });
        $('#edit_quote').click(function () {
            $(window).unbind('beforeunload');
            $('form.edit-qu-form').submit();
        });
    });
	
	$(window).load(function() {
		$(".paid_by1, .paid_by2, .paid_by3").trigger('change');
		$('.paid_by_by, .paid_by_by1, .paid_by_by2, .paid_by_by3').trigger('change');
	});
	
	$(document).on('keyup','#amount_1', function(event){
		//var total_amount = $('#quick-payable').text()-0;
		var us_paid = $('#amount_1').val()-0;

		var balance = us_paid;
		
		var deposit_amount = parseFloat($(".deposit_total_amount1").text());
		var deposit_balance = parseFloat($(".deposit_total_balance1").text());
		deposit_balance = (deposit_amount - Math.abs(us_paid));
		$(".deposit_total_balance1").text(deposit_balance);
	});
	
	$(document).on('keyup','#amount_2', function(event){
		//var total_amount = $('#quick-payable').text()-0;
		var us_paid = $('#amount_2').val()-0;

		var balance = us_paid;
		
		var deposit_amount = parseFloat($(".deposit_total_amount2").text());
		var deposit_balance = parseFloat($(".deposit_total_balance2").text());
		deposit_balance = (deposit_amount - Math.abs(us_paid));
		$(".deposit_total_balance2").text(deposit_balance);

	});
	
	$(document).on('keyup','#amount_3', function(event){
		//var total_amount = $('#quick-payable').text()-0;
		var us_paid = $('#amount_3').val()-0;

		var balance = us_paid;
		
		var deposit_amount = parseFloat($(".deposit_total_amount3").text());
		var deposit_balance = parseFloat($(".deposit_total_balance3").text());
		deposit_balance = (deposit_amount - Math.abs(us_paid));
		$(".deposit_total_balance3").text(deposit_balance);
	});
	
	$(document).on('change', '#amount_2', function(){
		checkDeposit3();
	});
	
	$(document).on('change', '#amount_1', function(){
		checkDeposit2();
		checkDeposit3();
	});
	
	$(document).on('keydown','#amount', '#amount_1, #amount_2, #amount_3', function(event){
		if (event.shiftKey == true) {
            event.preventDefault();
        }
        if ((event.keyCode >= 48 && event.keyCode <= 57) || 
            (event.keyCode >= 96 && event.keyCode <= 105) || 
            event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 ||
            event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

        } else {
            event.preventDefault();
        }

        if($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
            event.preventDefault(); 
        //if a decimal has been added, disable the "."-button
   });
	
	$(document).on('change', '.paid_by1', function () {
		var p_val = $(this).val(),
		id = $(this).attr('id');
		if(p_val == 'none'){
			$('.dp').hide();
		}
		if(p_val == 'deposit') {
			$('.dp').show();
			checkDeposit();
		}
	});
	
	$(document).on('change', '.type_payment', function () {
		var val = $(this).val();
		if(val == 'partial'){
			$(".partial-type").show('fast');
			$(".full-payment").hide('fast');
		}
		if(val == 'full_payment'){
			$(".full-payment").show('fast');
			$(".partial-type").hide('fast');
		}
	});
	
	$(document).on('change', '.paid_by2', function () {
		var p_val = $(this).val(),
		id = $(this).attr('id');
		if(p_val == 'none'){
			$('.dp2').hide();
		}
		if(p_val == 'deposit') {
			$('.dp2').show();
			checkDeposit2();
		}
	});
	
	$(document).on('change', '.paid_by_by', function () {
		var p_val = $(this).val();
		localStorage.setItem('paid_by', p_val);
		$('#rpaidby').val(p_val);
		if (p_val == 'cash' ||  p_val == 'other') {
			$('.pcheque_1').hide();
			$('.pcc_1').hide();
			$('.depreciation_1').hide();
			$('.pcash_1').show();
			$('#payment_note_1').focus();
			$('.pbt_1').hide();
		} else if (p_val == 'CC') {
			$('.pcheque_1').hide();
			$('.pcash_1').hide();
			$('.depreciation_1').hide();
			$('.pcc_1').show();
			$('#pcc_no_1').focus();
			$('.pbt_1').hide();
		} else if (p_val == 'Cheque') {
			$('.pcc_1').hide();
			$('.pcash_1').hide();
			$('.depreciation_1').hide();
			$('.pcheque_1').show();
			$('#cheque_no_1').focus();
			$('.pbt_1').hide();
		} else if (p_val == 'bank_transfer') {
			$('.pcc_1').hide();
			$('.pcash_1').hide();
			$('.depreciation_1').hide();
			$('.pcheque_1').hide();
			$('.pbt_1').show();
			$('#bt_no_1').focus();
		} else {
			$('.pcheque_1').hide();
			$('.depreciation_1').hide();
			$('.pcc_1').hide();
			$('.pcash_1').hide();
			$('.pbt_1').hide();
		}
		if (p_val == 'gift_card') {
			$('.gc').show();
			$('.ngc').hide();
			$('#gift_card_no').focus();
			$('.pbt_1').hide();
		} else {
			$('.ngc').show();
			$('.gc').hide();
			$('#gc_details').html('');
		}
		if(p_val == 'deposit') {
			$('.dp').show();
			$('#customer1').trigger('change');
		}else{
			$('.dp').hide();
			$('#dp_details').html('');
		}
	}).trigger('change');
	
	if (paid_by = localStorage.getItem('paid_by')) {
		var p_val = paid_by;
		//$('.paid_by_by').select2("val", paid_by);
		$('#rpaidby').val(p_val);
		if (p_val == 'cash' ||  p_val == 'other') {
			$('.pcheque_1').hide();
			$('.pcc_1').hide();
			$('.depreciation_1').hide();
			$('.pcash_1').show();
			$('#payment_note_1').focus();
			$('.pbt_1').hide();
		} else if (p_val == 'CC') {
			$('.pcheque_1').hide();
			$('.pcash_1').hide();
			$('.depreciation_1').hide();
			$('.pcc_1').show();
			$('#pcc_no_1').focus();
			$('.pbt_1').hide();
		} else if (p_val == 'Cheque') {
			$('.pcc_1').hide();
			$('.pcash_1').hide();
			$('.depreciation_1').hide();
			$('.pcheque_1').show();
			$('#cheque_no_1').focus();
			$('.pbt_1').hide();
		} else if (p_val == 'bank_transfer') {
			$('.pcc_1').hide();
			$('.pcash_1').hide();
			$('.depreciation_1').hide();
			$('.pcheque_1').hide();
			$('.pbt_1').show();
			$('#bt_no_1').focus();
		} else {
			$('.pcheque_1').hide();
			$('.pcc_1').hide();
			$('.depreciation_1').hide();
			$('.pcash_1').hide();
			$('.pbt_1').hide();
		}
		if (p_val == 'gift_card') {
			$('.gc').show();
			$('.ngc').hide();
			$('#gift_card_no').focus();
			$('.pbt_1').hide();
		} else {
			$('.ngc').show();
			$('.gc').hide();
			$('#gc_details').html('');
		}
	}

	/* Deposit1 */
	$(document).on('change', '.paid_by_by1', function () {
		var p_val = $(this).val();
		localStorage.setItem('paid_by_by1', p_val);
		$('#rpaidby').val(p_val);
		if (p_val == 'cash' ||  p_val == 'other') {
			$('.pcheque_1_1').hide();
			$('.pcc_1_1').hide();
			$('.depreciation_1_1').hide();
			$('.pcash_1_1').show();
			$('#payment_note_1_1').focus();
			$('.pbt_1_1').hide();
		} else if (p_val == 'CC') {
			$('.pcheque_1_1').hide();
			$('.pcash_1_1').hide();
			$('.depreciation_1_1').hide();
			$('.pcc_1_1').show();
			$('#pcc_no_1_1').focus();
			$('.pbt_1_1').hide();
		} else if (p_val == 'Cheque') {
			$('.pcc_1_1').hide();
			$('.pcash_1_1').hide();
			$('.depreciation_1_1').hide();
			$('.pcheque_1_1').show();
			$('#cheque_no_1_1').focus();
			$('.pbt_1_1').hide();
		} else if (p_val == 'bank_transfer') {
			$('.pcc_1_1').hide();
			$('.pcash_1_1').hide();
			$('.depreciation_1_1').hide();
			$('.pcheque_1_1').hide();
			$('.pbt_1_1').show();
			$('#bt_no_1_1').focus();
		} else {
			$('.pcheque_1_1').hide();
			$('.depreciation_1_1').hide();
			$('.pcc_1_1').hide();
			$('.pcash_1_1').hide();
			$('.pbt_1_1').hide();
		}
		if (p_val == 'gift_card') {
			$('.gc_1_1').show();
			$('.ngc_1_1').hide();
			$('#gift_card_no_1_1').focus();
			$('.pbt_1_1').hide();
		} else {
			$('.ngc_1_1').show();
			$('.gc_1_1').hide();
			$('#gc_details_1_1').html('');
		}
		if(p_val == 'deposit') {
			$('.dp_1_1').show();
			$('#customer1').trigger('change');
		}else{
			$('.dp_1_1').hide();
			$('#dp_details_1_1').html('');
		}
	}).trigger('change');

	/* Deposit2 */
	$(document).on('change', '.paid_by_by2', function () {
		var p_val = $(this).val();
		localStorage.setItem('paid_by_by2', p_val);
		$('#rpaidby').val(p_val);
		if (p_val == 'cash' ||  p_val == 'other') {
			$('.pcheque_1_2').hide();
			$('.pcc_1_2').hide();
			$('.depreciation_1_2').hide();
			$('.pcash_1_2').show();
			$('#payment_note_1_2').focus();
			$('.pbt_1_2').hide();
		} else if (p_val == 'CC') {
			$('.pcheque_1_2').hide();
			$('.pcash_1_2').hide();
			$('.depreciation_1_2').hide();
			$('.pcc_1_2').show();
			$('#pcc_no_1_2').focus();
			$('.pbt_1_2').hide();
		} else if (p_val == 'Cheque') {
			$('.pcc_1_2').hide();
			$('.pcash_1_2').hide();
			$('.depreciation_1_2').hide();
			$('.pcheque_1_2').show();
			$('#cheque_no_1_2').focus();
			$('.pbt_1_2').hide();
		} else if (p_val == 'bank_transfer') {
			$('.pcc_1_2').hide();
			$('.pcash_1_2').hide();
			$('.depreciation_1_2').hide();
			$('.pcheque_1_1').hide();
			$('.pbt_1_2').show();
			$('#bt_no_1_2').focus();
		} else {
			$('.pcheque_1_2').hide();
			$('.depreciation_1_2').hide();
			$('.pcc_1_2').hide();
			$('.pcash_1_2').hide();
			$('.pbt_1_2').hide();
		}
		if (p_val == 'gift_card') {
			$('.gc_1_2').show();
			$('.ngc_1_2').hide();
			$('#gift_card_no_1_2').focus();
			$('.pbt_1_2').hide();
		} else {
			$('.ngc_1_2').show();
			$('.gc_1_2').hide();
			$('#gc_details_1_2').html('');
		}
		if(p_val == 'deposit') {
			$('.dp_1_2').show();
			$('#customer1').trigger('change');
		}else{
			$('.dp_1_2').hide();
			$('#dp_details_1_2').html('');
		}
	}).trigger('change');

	/* Deposit3 */
	$(document).on('change', '.paid_by_by3', function () {
		var p_val = $(this).val();
		localStorage.setItem('paid_by_by3', p_val);
		$('#rpaidby').val(p_val);
		if (p_val == 'cash' ||  p_val == 'other') {
			$('.pcheque_1_3').hide();
			$('.pcc_1_3').hide();
			$('.depreciation_1_3').hide();
			$('.pcash_1_3').show();
			$('#payment_note_1_3').focus();
			$('.pbt_1_3').hide();
		} else if (p_val == 'CC') {
			$('.pcheque_1_3').hide();
			$('.pcash_1_3').hide();
			$('.depreciation_1_3').hide();
			$('.pcc_1_3').show();
			$('#pcc_no_1_3').focus();
			$('.pbt_1_3').hide();
		} else if (p_val == 'Cheque') {
			$('.pcc_1_3').hide();
			$('.pcash_1_3').hide();
			$('.depreciation_1_3').hide();
			$('.pcheque_1_3').show();
			$('#cheque_no_1_3').focus();
			$('.pbt_1_3').hide();
		} else if (p_val == 'bank_transfer') {
			$('.pcc_1_3').hide();
			$('.pcash_1_3').hide();
			$('.depreciation_1_3').hide();
			$('.pcheque_1_3').hide();
			$('.pbt_1_3').show();
			$('#bt_no_1_3').focus();
		} else {
			$('.pcheque_1_3').hide();
			$('.depreciation_1_3').hide();
			$('.pcc_1_3').hide();
			$('.pcash_1_3').hide();
			$('.pbt_1_3').hide();
		}
		if (p_val == 'gift_card') {
			$('.gc_1_3').show();
			$('.ngc_1_3').hide();
			$('#gift_card_no_1_3').focus();
			$('.pbt_1_3').hide();
		} else {
			$('.ngc_1_3').show();
			$('.gc_1_3').hide();
			$('#gc_details_1_3').html('');
		}
		if(p_val == 'deposit') {
			$('.dp_1_3').show();
			$('#customer1').trigger('change');
		}else{
			$('.dp_1_3').hide();
			$('#dp_details_1_3').html('');
		}
	}).trigger('change');

	$(document).on('change', '#gift_card_no', function () {
		var cn = $(this).val() ? $(this).val() : '';
		if (cn != '') {
			$.ajax({
				type: "get", async: false,
				url: site.base_url + "sales/validate_gift_card/" + cn,
				dataType: "json",
				success: function (data) {
					if (data === false) {
						$('#gift_card_no').parent('.form-group').addClass('has-error');
						bootbox.alert('<?=lang('incorrect_gift_card')?>');
					} else if (data.customer_id !== null && data.customer_id !== $('#slcustomer').val()) {
						$('#gift_card_no').parent('.form-group').addClass('has-error');
						bootbox.alert('<?=lang('gift_card_not_for_customer')?>');

					} else {
						$('#gc_details').html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
						$('#gift_card_no').parent('.form-group').removeClass('has-error');
					}
				}
			});
		}
	});
	
	$(document).on('change', '.paid_by3', function () {
		var p_val = $(this).val(),
		id = $(this).attr('id');
		if(p_val == 'none'){
			$('.dp3').hide();
		}
		if(p_val == 'deposit') {
			$('.dp3').show();
			checkDeposit3();
		}
	});
	
	$(document).on('change', '.paid_by, .paid_by2, .paid_by3', function (){
		$('#qucustomer').trigger('change.select2');
	});
	
	var payment_quotes = <?php echo json_encode($payment_deposit) ?>;
	var old_dep_amount1 = 0,
		old_dep_amount2 = 0,
		old_dep_amount3 = 0;
	var old_paid_by1 = '',
		old_paid_by2 = '',
		old_paid_by3 = '';
	if(payment_quotes){
		$.each(payment_quotes, function(){
			var note = this.note;
			if(note == 'Deposit1'){
				old_dep_amount1 = parseFloat(this.amount);
				old_paid_by1 = this.paid_by;
			}
			if(note == 'Deposit2'){
				old_dep_amount2 = parseFloat(this.amount);
				old_paid_by2 = this.paid_by;
			}
			if(note == 'Deposit3'){
				old_dep_amount3 = parseFloat(this.amount);
				old_paid_by3 = this.paid_by;
			}
		});
	}
	
	function checkDeposit() {
		var customer_id = $("#qucustomer").val();

		if (customer_id != '') {
			$.ajax({
				type: "get", async: false,
				url: site.base_url + "sales/validate_deposit/" + customer_id,
				dataType: "json",
				success: function (data) {
					if (data === false) {
						$('#deposit_no').parent('.form-group').addClass('has-error');
						bootbox.alert('<?=lang('invalid_customer')?>');
					} else if (data.id !== null && data.id !== customer_id) {
						$('#deposit_no').parent('.form-group').addClass('has-error');
						bootbox.alert("<?=lang('this_customer_has_no_deposit')?>");
						$('select').select2("val", 'none');
					} else {
						var amount = $("#amount_1").val()-0;
						<?php if(isset($payment_deposit)){ ?>
						var old_dp_amount = old_dep_amount1 + parseFloat(old_dep_amount2) + parseFloat(old_dep_amount3);
						old_dp_amount = parseFloat(old_dp_amount);
						
						$('#dp_details1').html('<small>Customer Name: ' + data.name + '<br>Amount: <span class="deposit_total_amount1">' + (data.deposit_amount == null ? 0 : formatDecimal(parseFloat(data.deposit_amount) + parseFloat(old_dp_amount))) + '</span> - Balance: <span class="deposit_total_balance1">' +formatDecimal(parseFloat(data.deposit_amount) + parseFloat(old_dp_amount) - parseFloat(amount)) + '</span></small>');
						$('#deposit_no').parent('.form-group').removeClass('has-error');
						<?php }else{ ?>
							$('#dp_details1').html('<small>Customer Name: ' + data.name + '<br>Amount: <span class="deposit_total_amount1">' + (data.deposit_amount == null ? 0 : formatDecimal(parseFloat(data.deposit_amount))) + '</span> - Balance: <span class="deposit_total_balance1">' +formatDecimal(parseFloat(data.deposit_amount) - parseFloat(amount)) + '</span></small>');
						$('#deposit_no').parent('.form-group').removeClass('has-error');
						<?php } ?>
						//calculateTotals();
						//$('#amount_1').val(data.deposit_amount - amount).focus();
					}
				}
			});
		}
	}
	
	function checkDeposit2() {
		var customer_id = $("#qucustomer").val();

		if (customer_id != '') {
			$.ajax({
				type: "get", async: false,
				url: site.base_url + "sales/validate_deposit/" + customer_id,
				dataType: "json",
				success: function (data) {
					if (data === false) {
						$('#deposit_no2').parent('.form-group').addClass('has-error');
						bootbox.alert('<?=lang('invalid_customer')?>');
					} else if (data.id !== null && data.id !== customer_id) {
						$('#deposit_no2').parent('.form-group').addClass('has-error');
						bootbox.alert("<?=lang('this_customer_has_no_deposit')?>");
						$('select').select2("val", 'none');
					} else {
						var amount = $("#amount_2").val()-0;
						<?php if(isset($payment_deposit)){ ?>
						var old_dp_amount = old_dep_amount2;
						old_dp_amount = parseFloat(old_dp_amount);
		
						var deposit_amount1 = $(".deposit_total_balance1").text()-0;
						
						var total_amount_2 = 0;
						if(deposit_amount1 > 0) {
							total_amount_2 = formatDecimal(parseFloat(deposit_amount1));
						}else{
							total_amount_2 = formatDecimal(parseFloat(data.deposit_amount));
						}
						
						$('#dp_details2').html('<small>Customer Name: ' + data.name + '<br>Amount: <span class="deposit_total_amount2">' + (total_amount_2) + '</span> - Balance: <span class="deposit_total_balance2">' +formatDecimal(total_amount_2 - parseFloat(amount)) + '</span></small>');
						$('#deposit_no2').parent('.form-group').removeClass('has-error');
						<?php }else{ ?>
							$('#dp_details2').html('<small>Customer Name: ' + data.name + '<br>Amount: <span class="deposit_total_amount2">' + total_amount_2 + '</span> - Balance: <span class="deposit_total_balance2">' +formatDecimal(total_amount_2 - parseFloat(amount)) + '</span></small>');
						$('#deposit_no2').parent('.form-group').removeClass('has-error');
						<?php } ?>
						//calculateTotals();
						//$('#amount_1').val(data.deposit_amount - amount).focus();
					}
				}
			});
		}
	}
	
	function checkDeposit3() {
		var customer_id = $("#qucustomer").val();

		if (customer_id != '') {
			$.ajax({
				type: "get", async: false,
				url: site.base_url + "sales/validate_deposit/" + customer_id,
				dataType: "json",
				success: function (data) {
					if (data === false) {
						$('#deposit_no3').parent('.form-group').addClass('has-error');
						bootbox.alert('<?=lang('invalid_customer')?>');
					} else if (data.id !== null && data.id !== customer_id) {
						$('#deposit_no3').parent('.form-group').addClass('has-error');
						bootbox.alert("<?=lang('this_customer_has_no_deposit')?>");
						$('select').select2("val", 'none');
					} else {
						var amount = $("#amount_3").val()-0;
						<?php if(isset($payment_deposit)){ ?>
						var old_dp_amount = <?php echo $payment_deposit->amount?$payment_deposit->amount:0; ?>;
						old_dp_amount = parseFloat(old_dp_amount);
						
						var deposit_amount2 = $(".deposit_total_balance2").text()-0;
						
						var total_amount_3 = 0;
						if(deposit_amount2 > 0) {
							total_amount_3 = formatDecimal(parseFloat(deposit_amount2));
						}else{
							total_amount_3 = formatDecimal(parseFloat(data.deposit_amount));
						}
						
						$('#dp_details3').html('<small>Customer Name: ' + data.name + '<br>Amount: <span class="deposit_total_amount3">' + formatDecimal(total_amount_3) + '</span> - Balance: <span class="deposit_total_balance3">' +formatDecimal(parseFloat(total_amount_3 - parseFloat(amount))) + '</span></small>');
						$('#deposit_no3').parent('.form-group').removeClass('has-error');
						<?php }else{ ?>
							$('#dp_details3').html('<small>Customer Name: ' + data.name + '<br>Amount: <span class="deposit_total_amount3">' + total_amount_3 + '</span> - Balance: <span class="deposit_total_balance3">' +formatDecimal(total_amount_3 - parseFloat(amount)) + '</span></small>');
						$('#deposit_no3').parent('.form-group').removeClass('has-error');
						<?php } ?>
						//calculateTotals();
						//$('#amount_1').val(data.deposit_amount - amount).focus();
					}
				}
			});
		}
	}
	
	$(document).on('ready', function(){
		$('.is_service_quotation').on('ifChecked', function (event){
			$('.is_normal_quotation').iCheck('uncheck');
			$(".service-wrap").show('fast');
			$("#add_services").prop("disabled", false);
		});
		$('.is_service_quotation').on('ifUnchecked', function (event) {
			$('.is_normal_quotation').iCheck('check');
			$(".service-wrap").hide('fast');
			$("#add_services").prop("disabled", true);
		});
		
		$('.is_normal_quotation').on('ifChecked', function (event){
			$('.is_service_quotation').iCheck('uncheck');
			$(".service-wrap").hide('fast');
			$("#add_services").prop("disabled", true);
		});
		$('.is_normal_quotation').on('ifUnchecked', function (event) {
			$('.is_service_quotation').iCheck('check');
			$(".service-wrap").show('fast');
			$("#add_services").prop("disabled", false);
		});
		
		$('#add_services').live('change', function (){
			var id = $(this).val();
			$.ajax({
				type: 'GET',
				url: '<?= site_url('quotes/getServicesInfo'); ?>',
				data: {id:id},
				cache: false,
				success: function (data) {
					var item = eval(data);
					for(a in item){
						idd = item[a]['id'];
						name = item[a]['name'];
					}
					//$('#secustomer').val(name);
					//$('#secustomer').attr('type', 'text');
					$('#qucustomer').select2('data', {id: idd, text: name});
					$('#qucustomer').select2("readonly", true);
					$('.editable').val(name);					  
					$('.editable').html(name);
					//$('option.editable').attr('selected', 'selected');
					//$('.editOption').show();
					$('.editOption').val(name);
				}
			});
		});

	});
	
</script>
<?php 
$old_dep_amount1 = 0;
$old_dep_amount2 = 0;
$old_dep_amount3 = 0;

$old_dep_paid_by1 = '';
$old_dep_paid_by2 = '';
$old_dep_paid_by3 = '';
$old_ref1 = '';
$old_ref2 = '';
$old_ref3 = '';

$old_cheque_no1 = '';
$old_cheque_no2 = '';
$old_cheque_no3 = '';

$old_bank_transfer_no1 = '';
$old_bank_transfer_no2 = '';
$old_bank_transfer_no3 = '';

$old_cc_no1 = '';
$old_cc_no2 = '';
$old_cc_no3 = '';

$old_cc_holder1 = '';
$old_cc_holder2 = '';
$old_cc_holder3 = '';

$old_cc_month1 = '';
$old_cc_month2 = '';
$old_cc_month3 = '';

$old_cc_year1 = '';
$old_cc_year2 = '';
$old_cc_year3 = '';

$old_cc_type1 = '';
$old_cc_type2 = '';
$old_cc_type3 = '';

if(isset($payment_deposit)){
	foreach($payment_deposit as $payment){
		if($payment->note == 'Deposit1'){
			$old_dep_amount1 = $payment->amount;
			$old_dep_paid_by1 = $payment->paid_by;
			$old_ref1 = $payment->reference_no;
			$old_cheque_no1 = $payment->cheque_no;
			$old_bank_transfer_no1 = $payment->bank_transfer_no;
			$old_cc_no1 = $payment->cc_no;
			$old_cc_holder1 = $payment->cc_holder;
			$old_cc_month1 = $payment->cc_month;
			$old_cc_year1 = $payment->cc_year;
			$old_cc_type1 = $payment->cc_type;
		}
		if($payment->note == 'Deposit2'){
			$old_dep_amount2 = $payment->amount;
			$old_dep_paid_by2 = $payment->paid_by;
			$old_ref2 = $payment->reference_no;
			$old_cheque_no2 = $payment->cheque_no;
			$old_bank_transfer_no2 = $payment->bank_transfer_no;
			$old_cc_no2 = $payment->cc_no;
			$old_cc_holder2 = $payment->cc_holder;
			$old_cc_month2 = $payment->cc_month;
			$old_cc_year2 = $payment->cc_year;
			$old_cc_type2 = $payment->cc_type;
		}
		if($payment->note == 'Deposit3'){
			$old_dep_amount3 = $payment->amount;
			$old_dep_paid_by3 = $payment->paid_by;
			$old_ref3 = $payment->reference_no;
			$old_cheque_no3 = $payment->cheque_no;
			$old_bank_transfer_no3 = $payment->bank_transfer_no;
			$old_cc_no3 = $payment->cc_no;
			$old_cc_holder3 = $payment->cc_holder;
			$old_cc_month3 = $payment->cc_month;
			$old_cc_year3 = $payment->cc_year;
			$old_cc_type3 = $payment->cc_type;
		}
	}
} ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_quote'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
				<p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-qu-form');
                echo form_open_multipart("quotes/edit/" . $id, $attrib)
                ?>
				<input type="hidden" value="<?php echo $old_ref1 ?>" name="ref1">
				<input type="hidden" value="<?php echo $old_ref2 ?>" name="ref2">
				<input type="hidden" value="<?php echo $old_ref3 ?>" name="ref3">
                <div class="row">
					
					<div class="col-lg-12">
						<div class="panel panel-info">
							<div class="panel-heading"><?= lang('please_select_these_quotation_type') ?></div>
							<div class="panel-body" style="padding: 5px;">
								<div class="col-md-12">
									<span class="normal-quote">
										 <input id="is_normal_quotation" class="is_normal_quotation form-control" type="checkbox" name="is_normal_quotation">
										<label> <?= lang("product_quotation"); ?></label>
									</span>
									
									<span class="service-quote" style="margin-left:15px;">
										<input id="is_service_quotation" class="is_service_quotation form-control" type="checkbox" name="is_service_quotation">
										<label> <?= lang("Service_Quotation"); ?></label>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-lg-12">
						
						<div class="col-md-4 service-wrap">
							<div class="form-group">
								<?= lang("Service_No", "services"); ?>
								<?php
									
									$se[""] = "";
									foreach ($service as $ser) {
										$se[$ser->id] = $ser->service_number;
									}
									echo form_dropdown('services', $se, $inv->service_id, 'id="add_services" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("services") . '" class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
						</div>
						
						<?php if ($Owner || $Admin) { ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("date", "qudate"); ?>
									<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="qudate" required="required"'); ?>
								</div>
							</div>
						<?php } ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("Quotation_No", "quref"); ?>
								<?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="quref" required="required"'); ?>
							</div>
						</div>
						<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("biller", "qubiller"); ?>
									<?php
									$bl[""] = "";
									foreach ($billers as $biller) {
										$bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
									}
									echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $inv->biller_id), 'id="qubiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
									?>
								</div>
							</div>
						<?php } else {
							$biller_input = array(
								'type' => 'hidden',
								'name' => 'biller',
								'id' => 'qubiller',
								'value' => $this->session->userdata('biller_id'),
							);

							echo form_input($biller_input);
						} ?>

						<?php if ($Settings->tax2) { ?>
							<?php
							echo form_hidden('order_tax', $Settings->default_tax_rate2);
							?>
						<?php } ?>

						<?php if (($Owner || $Admin || $this->session->userdata('allow_discount')) || $inv->order_discount_id) { ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("discount", "qudiscount"); ?>
								<?php echo form_input('discount', '', 'class="form-control input-tip" id="qudiscount" '.(($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"')); ?>
							</div>
						</div>
						<?php } ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("status", "qustatus"); ?>
								<?php $st = array('pending' => lang('pending'),'completed' => lang('completed'));
								echo form_dropdown('status', $st, '', 'class="form-control input-tip" id="qustatus"'); ?>

							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								<?= lang("document", "document") ?>
								<input id="document" type="file" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("document", "document") ?>
								<input id="document1" type="file" name="document1" data-show-upload="false" data-show-preview="false" class="form-control file">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("document", "document") ?>
								<input id="document2" type="file" name="document2" data-show-upload="false" data-show-preview="false" class="form-control file">
							</div>
						</div>  

						<div class="col-md-12">
							<div class="panel panel-warning">
								<div
									class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
								<div class="panel-body" style="padding: 5px;">
									<div class="col-md-12">
										<?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { 
											echo form_hidden('warehouse', $user->warehouse_id, 'id="quwarehouse"');
										} else {
											echo form_hidden('warehouse', $user->warehouse_id, 'id="quwarehouse"');
										} ?>
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("customer", "qucustomer"); ?>
												<?php
												echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="qucustomer" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-12" id="sticker">
							<div class="well well-sm">
								<div class="form-group" style="margin-bottom:0;">
									<div class="input-group wide-tip">
										<div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
											<i class="fa fa-2x fa-barcode addIcon"></i></div>
										<?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
										<?php if ($Owner || $Admin || $GP['products-add']) { ?>
										<div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
											<a href="#" id="addManually" class="tip" title="<?= lang('add_product_manually') ?>">
												<i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
											</a>
										</div>
										<?php } ?>
									</div>
								</div>
								<div class="clearfix"></div>
							</div>
						</div>
						<div class="clearfix"></div>
						<div class="col-md-12">
							<div class="control-group table-group">
								<label class="table-label"><?= lang("Sale_items"); ?> *</label>

								<div class="controls table-controls">
									<table id="quTable"
										   class="table items table-striped table-bordered table-condensed table-hover">
										<thead>
										<tr>
											<th class="col-md-4"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
											<th class="col-md-1"><?= lang("Price_Before_Tax"); ?></th>
											<?php
												if ($Settings->tax1) {
													echo '<th class="col-md-1">' . $this->lang->line("product_tax") . '</th>';
												}
											?>
											<th class="col-md-1"><?= lang("price_after_tax"); ?></th>
											<th class="col-md-1"><?= lang("quantity"); ?></th>
											<th>
												<?= lang("subtotal"); ?> 
												(<span class="currency"><?= $default_currency->code ?></span>)
											</th>
											<?php
												if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
													echo '<th class="col-md-1">' . $this->lang->line("discount") . '</th>';
												}
											?>
											<th class="col-md-2">
												<?= lang("final_price"); ?> 
												(<span class="currency"><?= $default_currency->code ?></span>)
											</th>
											<th style="width: 30px !important; text-align: center;"><i
													class="fa fa-trash-o"
													style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
										</tr>
										</thead>
										<tbody></tbody>
										<tfoot></tfoot>
									</table>
								</div>
							</div>
						</div>

						<input type="hidden" name="total_items" value="" id="total_items" required="required"/>
						
						<div class="col-md-12">
							<div class="panel panel-info">
								<div class="panel-heading"><?= lang('payments') ?></div>
								<div class="panel-body" style="padding: 5px;">	
									<div class="col-md-12" style="padding-bottom:10px;">
										<div class="col-md-4">
											<div class="form-group">
											<?= lang("Type_of_payments", "type_payment"); ?>
											<?php 
												$type_payment = array('full_payment' => lang("Full_Payment"), 'partial' => lang("Partial_Payment"));
												echo form_dropdown('type_payment', $type_payment, 
												$payment_full?'full_payment':'partial', 'id="type_payment" class="form-control type_payment" style="pointer-events: none;"') ?>
											</div>
										</div>
										
									</div>
									
									<div class="col-md-12 full-payment">
										<div class="col-md-4">
											<div class="form-group">
												<?= lang("Payment_Method", "paid_by_1"); ?>
												<!--
												<select name="paid_by_by" id="paid_by_1" class="form-control paid_by_by">
													<option value="cash"><?= lang("cash"); ?></option>
													<option value="deposit"><?= lang("Store_Credit"); ?></option>
													<option value="CC"><?= lang("CC"); ?></option>
													<option value="Cheque"><?= lang("cheque"); ?></option>
													<option value="bank_transfer"><?= lang("Bank_Transfer"); ?></option>
												</select>
												-->
												
												<?php 
												$paid_by_f = array(
													'cash' 			=> lang("cash"),
													'deposit' 		=> lang("Store_Credit"),
													'CC' 			=> lang("CC"),
													'Cheque' 		=> lang("cheque"),
													'bank_transfer' => lang("Bank_Transfer")
												);
												echo form_dropdown('paid_by_by', $paid_by_f, $payment_full->paid_by, 'id="paid_by_1" class="form-control paid_by_by"');
												?>
												
											</div>
										</div>
										<div class="col-sm-4">
											<div class="payment">
												<div class="form-group ngc">
													<?= lang("amount", "amount_1"); ?>
													<input name="amount-paid" type="text" id="amount"
														   class="pa form-control kb-pad amount" value="<?php echo $payment_full?$this->erp->formatDecimal($payment_full->amount):0 ?>"/>
												</div>
												<div class="form-group gc" style="display: none;">
													<?= lang("gift_card_no", "gift_card_no"); ?>
													<input name="gift_card_no" type="text" id="gift_card_no"
														   class="pa form-control kb-pad"/>

													<div id="gc_details"></div>
												</div>
											</div>
										</div>
									</div>
									
									<div class="col-md-12 full-payment">

										<div class="pcc_1" style="display:none;">
											<input type="hidden" value="<?= $payment_full->reference_no?>" name="payment_reference"/>
											<div class="col-md-4">
												<div class="form-group">
													<input value="<?php echo $payment_full->cc_no ?>" name="pcc_no" type="text" id="pcc_no_1" class="form-control" placeholder="<?= lang('cc_no') ?>"/>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<input value="<?php echo $payment_full->cc_holder ?>" name="pcc_holder" type="text" id="pcc_holder_1" class="form-control" placeholder="<?= lang('cc_holder') ?>"/>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<?php 
														$cc_typef = array(
															'Visa' 			=> lang('Visa'),
															'MasterCard' 	=> lang("MasterCard"),
															'Amex' 			=> lang("Amex"),
															'Discover' 		=> lang("Discover"),
															'JCB' 			=> lang("JCB"),
															'Union_Pay' 	=> lang("Union Pay"),
															'Diners_Club' 	=> lang("Diners Club")
														);
														echo form_dropdown('pcc_type', $cc_typef, $payment_full->cc_type, 'id="pcc_type_1"
															class="form-control pcc_type"');
														?>
												</div>
											</div>
											<!--<div class="col-md-4">
												<div class="form-group">
													<input value="<?php echo $payment_full->cc_month ?>" name="pcc_month" type="text" id="pcc_month_1"
														   class="form-control" placeholder="<?= lang('month') ?>"/>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<input value="<?php echo $payment_full->cc_year ?>" name="pcc_year" type="text" id="pcc_year_1"
														   class="form-control" placeholder="<?= lang('year') ?>"/>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">

													<input name="pcc_ccv" type="text" id="pcc_cvv2_1"
														   class="form-control" placeholder="<?= lang('cvv2') ?>"/>
												</div>
											</div>-->
										</div>
										
										<div class="form-group dp" style="display: none;">
											<?= lang("customer", "customer1"); ?>
											<?php
												$customers1[] = array();
												foreach($customers as $customer){
													$customers1[$customer->id] = $customer->name;
												}
												echo form_dropdown('customer1', $customers1, '' , 'class="form-control" id="customer1"');
											?>
											<?= lang("deposit_amount", "deposit_amount"); ?>
											
											<div id="dp_details"></div>
										</div>
										
										<div class="col-md-12 pcheque_1" style="display:none;">
											<div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
												<input value="<?php echo $payment_full->cheque_no ?>" name="cheque_no" type="text" id="cheque_no_1" class="form-control cheque_no"/>
											</div>
										</div>
										
										<div class="pbt_1" style="display:none;">
											<div class="form-group"><?= lang("bt_no", "bt_no_1"); ?>
												<input value="<?php echo $payment_full->bank_transfer_no ?>" name="bt_no" type="text" id="bt_no_1" class="form-control bt_no_1"/>
											</div>
										</div>
										
									</div>
									
									<!-- Deposit1 -->
									<div class="col-md-12 partial-type" style="display:none">
										<div class="col-md-4">
											<div class="col-sm-12">
												<div class="form-group">
													<?= lang("Deposit1", "paid_by_1"); ?>
													<?php 
													$paid_by_f = array(
														'cash' => lang("cash"),
														'deposit' => lang("Store_Credit"),
														'CC' => lang("CC"),
														'Cheque' => lang("cheque"),
														'bank_transfer' => lang("Bank_Transfer")
													);
													echo form_dropdown('deposit1', $paid_by_f, $old_dep_paid_by1, 'id="paid_by_11" class="form-control paid_by_by1"');
													?>
													
												</div>
											</div>
											<input type="hidden" name="payment_reference1" value="<?php echo $old_ref1 ?>" />
											<div class="col-sm-12">
												<div class="payment">
													<div class="form-group ngc_1_1">
														<?= lang("amount", "amount_1_1"); ?>
														<input value="<?php echo $old_dep_amount1 ?>" name="amount-paid_1_1" type="text" id="amount_1_1"
															   class="pa_1_1 form-control kb-pad amount_1_1"/>
													</div>
													<div class="form-group gc_1_1" style="display: none;">
														<?= lang("gift_card_no", "gift_card_no"); ?>
														<input name="gift_card_no_1_1" type="text" id="gift_card_no_1_1"
															   class="pa_1_1 form-control kb-pad"/>

														<div id="gc_details_1_1"></div>
													</div>
												</div>
											</div>
											
											<div class="pcc_1_1" style="display:none;">
												<div class="col-md-12">
													<div class="form-group">
														<input value="<?php echo $old_cc_no1 ?>" name="pcc_no_1_1" type="text" id="pcc_no_1_1"
															   class="form-control" placeholder="<?= lang('cc_no') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<input value="<?php echo $old_cc_holder1 ?>" name="pcc_holder_1_1" type="text" id="pcc_holder_1_1"
															   class="form-control"
															   placeholder="<?= lang('cc_holder') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<?php 
														$cc_type1 = array(
															'Visa' => lang('Visa'),
															'MasterCard' => lang("MasterCard"),
															'Amex' => lang("Amex"),
															'Discover' => lang("Discover")
														);
														echo form_dropdown('pcc_type_1_1', $cc_type1, $old_cc_type1, 'id="pcc_type_1_1"
																class="form-control pcc_type_1_1"');
														?>
														<!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<input value="<?php echo $old_cc_month1 ?>" name="pcc_month_1_1" type="text" id="pcc_month_1_1"
															   class="form-control" placeholder="<?= lang('month') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">

														<input value="<?php echo $old_cc_year1 ?>" name="pcc_year_1_1" type="text" id="pcc_year_1_1"
															   class="form-control" placeholder="<?= lang('year') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">

														<input name="pcc_ccv_1_1" type="text" id="pcc_cvv2_1_1"
															   class="form-control" placeholder="<?= lang('cvv2') ?>"/>
													</div>
												</div>
											</div>
											
											<div class="form-group dp_1_1" style="display: none;">
												<?= lang("deposit_amount", "deposit_amount_1"); ?>
												<div id="dp_details_1_1"></div>
											</div>
											
											<div class="col-md-12 pcheque_1_1" style="display:none;">
												<div class="form-group"><?= lang("cheque_no", "cheque_no_1_1"); ?>
													<input value="<?php echo $old_cheque_no1 ?>" name="cheque_no1" type="text" id="cheque_no_1_1"
														   class="form-control cheque_no1"/>
												</div>
											</div>
											
											<div class="col-md-12 pbt_1_1" style="display:none;">
												<div class="form-group"><?= lang("bt_no", "bt_no_1_1"); ?>
													<input value="<?php echo $old_bank_transfer_no1 ?>" name="bt_no1" type="text" id="bt_no_1_1"
														   class="form-control bt_no_1_1"/>
												</div>
											</div>
											
											
										</div>
										
										<!-- Deposit2 -->
										<div class="col-md-4">
											<div class="col-sm-12">
												<div class="form-group">
													<?= lang("Deposit2", "paid_by_2"); ?>
													<?php 
													echo form_dropdown('deposit2', $paid_by_f, $old_dep_paid_by2, 'id="paid_by_12" class="form-control paid_by_by2"');
													?>
												</div>
											</div>
											<input type="hidden" name="payment_reference2" value="<?php echo $old_ref2 ?>" />
											
											<div class="col-sm-12">
												<div class="payment">
													<div class="form-group ngc_1_2">
														<?= lang("amount", "amount_1_2"); ?>
														<input value="<?php echo $old_dep_amount2 ?>" name="amount-paid_1_2" type="text" id="amount_1_2"
															   class="pa_1_2 form-control kb-pad amount_1_2"/>
													</div>
													<div class="form-group gc_1_2" style="display: none;">
														<?= lang("gift_card_no", "gift_card_no"); ?>
														<input name="gift_card_no_1_2" type="text" id="gift_card_no_1_2"
															   class="pa_1_2 form-control kb-pad"/>

														<div id="gc_details_1_2"></div>
													</div>
												</div>
											</div>
											
											<div class="pcc_1_2" style="display:none;">
												<div class="col-md-12">
													<div class="form-group">
														<input value="<?php echo $old_cc_no2 ?>" name="pcc_no_1_2" type="text" id="pcc_no_1_2"
															   class="form-control" placeholder="<?= lang('cc_no') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<input value="<?php echo $old_cc_holder1 ?>" name="pcc_holder_1_2" type="text" id="pcc_holder_1_2"
															   class="form-control"
															   placeholder="<?= lang('cc_holder') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<?php 
														echo form_dropdown('pcc_type_1_2', $cc_type1, $old_cc_type2, 'id="pcc_type_1_2"
																class="form-control pcc_type_1_2"');
														?>
														<!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<input value="<?php echo $old_cc_month2 ?>" name="pcc_month_1_2" type="text" id="pcc_month_1_2"
															   class="form-control" placeholder="<?= lang('month') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<input value="<?php echo $old_cc_year2 ?>" name="pcc_year_1_2" type="text" id="pcc_year_1_2"
															   class="form-control" placeholder="<?= lang('year') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<input name="pcc_ccv_1_2" type="text" id="pcc_cvv2_1_2"
															   class="form-control" placeholder="<?= lang('cvv2') ?>"/>
													</div>
												</div>
											</div>
											
											<div class="form-group dp_1_2" style="display: none;">
												<?= lang("deposit_amount", "deposit_amount_1"); ?>
												<div id="dp_details_1_2"></div>
											</div>

											<div class="col-md-12 pcheque_1_2" style="display:none;">
												<div class="form-group"><?= lang("cheque_no", "cheque_no_1_2"); ?>
													<input value="<?php echo $old_cheque_no2 ?>" name="cheque_no_1_2" type="text" id="cheque_no_1_2"
														   class="form-control cheque_no_1_2"/>
												</div>
											</div>
											
											<div class="col-md-12 pbt_1_2" style="display:none;">
												<div class="form-group"><?= lang("bt_no", "bt_no_1_2"); ?>
													<input value="<?php echo $old_bank_transfer_no2 ?>" name="bt_no2" type="text" id="bt_no_1_2"
														   class="form-control bt_no_1_2"/>
												</div>
											</div>
											
											<!--
											<div class="col-md-12">
												<div class="form-group">
													<?= lang("Deposit1", "qudeposit"); ?>
													
													<?php if ($Owner || $Admin || $GP['customers-add']) { ?>
													<div class="input-group"><?php } ?>

														<?php 
														$paid_by1 = array('none' => lang("none"), 'deposit' => lang("deposit"));
														echo form_dropdown('paid_by', $paid_by1, $old_dep_paid_by1, 'id="paid_by1" class="form-control paid_by1"') ?>
														<?php if ($Owner || $Admin || $GP['customers-add']) { ?>
														<div class="input-group-addon no-print" style="padding: 2px 5px;"><a
																href="<?= site_url('quotes/add_deposit'); ?>" id="add-deposit"
																class="external" data-toggle="modal" data-target="#myModal"><i
																	class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
													</div>
													<?php } ?>
												</div>
												<div class="form-group dp" style="display: none;">
													<?= lang("amount", "deposit_amount"); ?>
													<div class="">
														<input type="text" value="<?php echo $old_dep_amount1; ?>" name="amount" class="form-control amount_1" id="amount_1" placeholder="amount">
													</div>
													<div id="dp_details1"></div>
												</div>
											</div>
											-->
											
										</div>
										<!-- // End Deposit2 -->
										
										<!-- Deposit3 -->
										<div class="col-md-4">
											<div class="col-sm-12">
												<div class="form-group">
													<?= lang("Deposit3", "paid_by_3"); ?>
													<?php 
													echo form_dropdown('deposit3', $paid_by_f, $old_dep_paid_by3, 'id="paid_by_13" class="form-control paid_by_by3"');
													?>
												</div>
											</div>
											<input type="hidden" name="payment_reference3" value="<?php echo $old_ref3 ?>" />
											
											<div class="col-sm-12">
												<div class="payment">
													<div class="form-group ngc_1_3">
														<?= lang("amount", "amount_1_3"); ?>
														<input value="<?php echo $old_dep_amount3 ?>" name="amount-paid_1_3" type="text" id="amount_1_3"
															   class="pa_1_3 form-control kb-pad amount_1_3"/>
													</div>
													<div class="form-group gc_1_3" style="display: none;">
														<?= lang("gift_card_no", "gift_card_no_1_3"); ?>
														<input name="gift_card_no_1_3" type="text" id="gift_card_no_1_3"
															   class="pa_1_3 form-control kb-pad"/>

														<div id="gc_details_1_3"></div>
													</div>
												</div>
											</div>
											
											<div class="pcc_1_3" style="display:none;">
												<div class="col-md-12">
													<div class="form-group">
														<input value="<?php echo $old_cc_no3 ?>" name="pcc_no_1_3" type="text" id="pcc_no_1_3"
															   class="form-control" placeholder="<?= lang('cc_no') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<input value="<?php echo $old_cc_holder3 ?>" name="pcc_holder_1_3" type="text" id="pcc_holder_1_3"
															   class="form-control"
															   placeholder="<?= lang('cc_holder') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<?php 
														echo form_dropdown('pcc_type_1_3', $cc_type1, $old_cc_type3, 'id="pcc_type_1_3"
																class="form-control pcc_type_1_3"');
														?>
														<!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">
														<input value="<?php echo $old_cc_month3 ?>" name="pcc_month_1_3" type="text" id="pcc_month_1_3"
															   class="form-control" placeholder="<?= lang('month') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">

														<input value="<?php echo $old_cc_year3 ?>" name="pcc_year_1_3" type="text" id="pcc_year_1_3"
															   class="form-control" placeholder="<?= lang('year') ?>"/>
													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group">

														<input name="pcc_ccv_1_3" type="text" id="pcc_cvv2_1_3"
															   class="form-control" placeholder="<?= lang('cvv2') ?>"/>
													</div>
												</div>
											</div>
											
											<div class="form-group dp_1_3" style="display: none;">
												<?= lang("deposit_amount", "deposit_amount_1"); ?>
												<div id="dp_details_1_3"></div>
											</div>

											<div class="col-md-12 pcheque_1_3" style="display:none;">
												<div value="<?php echo $old_cheque_no3 ?>" class="form-group"><?= lang("cheque_no", "cheque_no_1_3"); ?>
													<input name="cheque_no_1_3" type="text" id="cheque_no_1_3"
														   class="form-control cheque_no_1_3"/>
												</div>
											</div>
											
											<div class="col-md-12 pbt_1_3" style="display:none;">
												<div class="form-group"><?= lang("bt_no", "bt_no_1_3"); ?>
													<input value="<?php echo $old_bank_transfer_no3 ?>" name="bt_no3" type="text" id="bt_no_1_3"
														   class="form-control bt_no_1_3"/>
												</div>
											</div>
											
											<!--
											<div class="col-md-12">
												<div class="form-group">
													<?= lang("Deposit1", "qudeposit"); ?>
													
													<?php if ($Owner || $Admin || $GP['customers-add']) { ?>
													<div class="input-group"><?php } ?>

														<?php 
														$paid_by1 = array('none' => lang("none"), 'deposit' => lang("deposit"));
														echo form_dropdown('paid_by', $paid_by1, $old_dep_paid_by1, 'id="paid_by1" class="form-control paid_by1"') ?>
														<?php if ($Owner || $Admin || $GP['customers-add']) { ?>
														<div class="input-group-addon no-print" style="padding: 2px 5px;"><a
																href="<?= site_url('quotes/add_deposit'); ?>" id="add-deposit"
																class="external" data-toggle="modal" data-target="#myModal"><i
																	class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
													</div>
													<?php } ?>
												</div>
												<div class="form-group dp" style="display: none;">
													<?= lang("amount", "deposit_amount"); ?>
													<div class="">
														<input type="text" value="<?php echo $old_dep_amount1; ?>" name="amount" class="form-control amount_1" id="amount_1" placeholder="amount">
													</div>
													<div id="dp_details1"></div>
												</div>
											</div>
											-->
											
										</div>
										<!-- // End Deposit3 -->
										
									</div>
									
								</div>
							</div>
						</div>

						<div class="row" id="bt">
							<div class="col-sm-12">
								<div class="col-sm-12">
									<div class="form-group">
										<?= lang("note", "qunote"); ?>
										<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="qunote" style="margin-top: 10px; height: 100px;"'); ?>
									</div>
								</div>
							</div>

						</div>
						<div class="col-sm-12">
							<div class="fprom-group"><?php echo form_submit('edit_quote', $this->lang->line("submit"), 'id="edit_quote" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
								<button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
							</div>
						</div>
					</div>
				</div>
				<div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
					<table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
						<tr class="warning">
							<td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
							<td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
							<?php if ($Settings->tax2) { ?>
								<td><?= lang('total_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
							<?php } ?>
							<td><?= lang('total_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
							<td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
						</tr>
					</table>
					<?php echo form_close(); ?>
				</div>
			</div>
		</div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($Settings->product_serial) { ?>
                        <div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pserial">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>

                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label">
                                <?= lang('product_discount') ?>
                            </label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount" <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"'; ?>>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('price_after_tax') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('Price_Before_Tax'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_price" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mcode">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mname">
                        </div>
                    </div>
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?= lang('product_tax') ?> *</label>

                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="mdiscount" class="col-sm-4 control-label">
                                <?= lang('product_discount') ?>
                            </label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mdiscount" <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? '' : 'readonly="true"'; ?>>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mprice">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('Price_Before_Tax'); ?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
