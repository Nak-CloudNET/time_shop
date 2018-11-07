<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_payment'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("pos/edit_payment/" . $payment->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <?php if ($Owner || $Admin) { ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang("date", "date"); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->erp->hrld($payment->date)), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang("reference_no", "reference_no"); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment->reference_no), 'class="form-control tip" id="reference_no" required="required"'); ?>
                    </div>
                </div>

                <input type="hidden" value="<?php echo $payment->sale_id; ?>" name="sale_id"/>
            </div>
            <div class="clearfix"></div>
            <div id="payments">

                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang("amount", "amount_1"); ?>
                                        <input name="amount-paid" value="<?= $this->erp->formatDecimal($payment->amount); ?>" type="text" amount="<?= (($inv->grand_total - $inv->paid) + $payment->amount + $payment->discount) ?>" id="amount_1" class="pa form-control kb-pad amount"/>
                                        <input name="amounts" type="hidden" value="<?= $inv->grand_total ?>"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang("paying_by", "paid_by_1"); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                        <option
                                            value="cash"<?= $payment->paid_by == 'cash' ? ' checked="checcked"' : '' ?>><?= lang("cash"); ?></option>
                                        <option
                                            value="CC"<?= $payment->paid_by == 'CC' ? ' checked="checcked"' : '' ?>><?= lang("cc"); ?></option>
                                        <option
                                            value="Cheque"<?= $payment->paid_by == 'Cheque' ? ' checked="checcked"' : '' ?>><?= lang("cheque"); ?></option>
										<option 
											value="deposit"<?= $payment->paid_by == 'deposit' ? ' checked="checcked"' : '' ?>><?= lang("store_credit"); ?></option>
                                        <option 
											value="bank_transfer"<?= $payment->paid_by == 'bank_transfer' ? ' checked="checcked"' : '' ?>><?= lang("bank_transfer"); ?></option>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="clearfix"></div>
                        <div class="pcc_1" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input name="pcc_no" value="<?= $payment->cc_no; ?>" type="text" id="pcc_no_1" class="form-control" placeholder="<?= lang('cc_no') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
										<input name="pcc_holder" value="<?= $payment->cc_holder; ?>" type="text" id="pcc_holder_1" class="form-control" placeholder="<?= lang('cc_holder') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <select name="pcc_type" id="pcc_type_1" class="form-control pcc_type"
                                                placeholder="<?= lang('card_type') ?>">
                                            <option
                                                value="Visa"<?= $payment->cc_type == 'Visa' ? ' checked="checcked"' : '' ?>><?= lang("Visa"); ?></option>
                                            <option
                                                value="MasterCard"<?= $payment->cc_type == 'MasterCard' ? ' checked="checcked"' : '' ?>><?= lang("MasterCard"); ?></option>
                                            <option
                                                value="Amex"<?= $payment->cc_type == 'Amex' ? ' checked="checcked"' : '' ?>><?= lang("Amex"); ?></option>
                                            <option
                                                value="Discover"<?= $payment->cc_type == 'Discover' ? ' checked="checcked"' : '' ?>><?= lang("Discover"); ?></option>
											<option 
												value="diners_club"<?= $payment->cc_type == 'diners_club' ? ' checked="checcked"' : '' ?>><?= lang("Diners Club"); ?></option>
											<option 
												value="jcb"<?= $payment->cc_type == 'jcb' ? ' checked="checcked"' : '' ?>><?= lang("JCB"); ?></option>
											<option 
												value="union_pay"<?= $payment->cc_type == 'union_pay' ? ' checked="checcked"' : '' ?>><?= lang("Union Pay"); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
						<div class="form-group dp" style="display: none;">
							<?= lang("deposit_amount", "deposit_amount"); ?>
							<div id="dp_details"></div>
						</div>
                        <div class="pcheque_1" style="display:none;">
                            <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                <input name="cheque_no" value="<?= $payment->cheque_no; ?>" type="text" id="cheque_no_1" class="form-control cheque_no"/>
                            </div>
                        </div>
						<div class="pbank_1" style="display:none;">
							<div class="form-group">
								<?= lang("bank_transfer", "bank_transfer"); ?>
								<input name="bank_transfer" value="<?= $payment->bank_transfer_no; ?>" type="text" id="bank_transfer_1" class="form-control bank_transfer"/>
							</div>
						</div>
                    </div>
                    <div class="clearfix"></div>
                </div>

            </div>

            <div class="form-group">
                <?= lang("attachment", "attachment") ?>
                <input id="attachment" type="file" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $payment->note), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_payment', lang('edit_payment'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['erp'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
		$('#pcc_type_1').val('<?= $payment->cc_type;?>').trigger('change'); 
		function checkDeposit() {
			var customer_id = '<?= $inv->customer_id; ?>';
            if (customer_id != '') {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "sales/validate_deposit/" + customer_id,
                    dataType: "json",
                    success: function (data) {
                        if (data === false) {
                            $('#deposit_no_1').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('invalid_customer')?>');
                        } else if (data.deposit_amount === null) {
                            $('#deposit_no_1').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('this_customer_has_no_deposit')?>');
                            $('#paid_by_1').select2('val','cash').trigger('change');
                        } else {
							var amount           = $("#amount_1").val();
							var old_amt          = '<?= $payment->amount; ?>';
                            var paid_by          = '<?= $payment->paid_by; ?>';
                            var deposit_amount   = data.deposit_amount==null?0: (parseFloat(data.deposit_amount));
                            if (paid_by == 'deposit') {
                                deposit_amount   = data.deposit_amount==null?0: (parseFloat(data.deposit_amount) + parseFloat(old_amt) );
                            }
							
							var deposit_balance  = (parseFloat(data.deposit_amount) + parseFloat(old_amt) - amount);
                            if (amount > deposit_amount) {
                                var deposit_balance  = deposit_amount;
                                $("#amount_1").val(deposit_amount);
                            } else {
                                var deposit_balance  = (data.deposit_amount - amount);
                            }
                            
                            $('#dp_details').html('<small>Customer Name: ' + data.name + '<br>Amount: <span class="deposit_total_amount">' + deposit_amount + '</span> - Balance: <span class="deposit_total_balance">' + deposit_balance + '</span></small>');
                            $('#deposit_no').parent('.form-group').removeClass('has-error');
                        }
                    }
                });
            }
		}
        $.fn.datetimepicker.dates['erp'] = <?=$dp_lang?>;
		$(document).on('keyup', '#amount_1', function () {
            var us_paid = parseFloat($('#amount_1').val()-0);
            var amount  = parseFloat($('#amount_1').attr('amount')-0);
            var p_val   = $('#paid_by_1').val();
            var new_deposit_balance = 0;
            if(p_val == 'deposit') {
                var deposit_balance = parseFloat($('.deposit_total_amount').html()-0);
                new_deposit_balance = deposit_balance - us_paid;
                if(!us_paid) {
                    $('#amount_1').val(0);
                    $('#amount_1').select();
                }else if(new_deposit_balance < 0) {
                    $('#amount_1').val(deposit_balance);
                    $(".deposit_total_balance").text(0);
                    $('#amount_1').select();
                }else {
                    $(".deposit_total_balance").text(new_deposit_balance);
                }
                
            }
			
		});
        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val();
            localStorage.setItem('paid_by', p_val);
            if (p_val == 'cash') {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
				$('.pbank_1').hide();
                $('.pcash_1').show();
                $('#amount_1').focus();
            } else if (p_val == 'CC') {
                $('.pcheque_1').hide();
                $('.pcash_1').hide();
				$('.pbank_1').hide();
                $('.pcc_1').show();
                $('#pcc_no_1').focus();
            } else if (p_val == 'Cheque') {
                $('.pcc_1').hide();
                $('.pcash_1').hide();
				$('.pbank_1').hide();
                $('.pcheque_1').show();
                $('#cheque_no_1').focus();
            } else if (p_val == 'bank_transfer') {
                $('.pcc_1').hide();
                $('.pcash_1').hide();
                $('.pcheque_1').hide();
				$('.pbank_1').show();
                $('#cheque_no_1').focus();
            } else {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pbank_1').hide();
                $('.pcash_1').hide();
            }
			if(p_val == 'deposit') {
				$('.dp').show();
				checkDeposit();
			}else{
				$('.dp').hide();
                $('#dp_details').html('');
			}
        });
        var p_val = '<?=$payment->paid_by?>';
        localStorage.setItem('paid_by', p_val);
        if (p_val == 'cash') {
            $('.pcheque_1').hide();
            $('.pcc_1').hide();
			$('.pbank_1').hide();
            $('.pcash_1').show();
            $('#amount_1').focus();
        } else if (p_val == 'CC') {
            $('.pcheque_1').hide();
            $('.pcash_1').hide();
			$('.pbank_1').hide();
            $('.pcc_1').show();
            $('#pcc_no_1').focus();
        } else if (p_val == 'Cheque') {
            $('.pcc_1').hide();
            $('.pcash_1').hide();
			$('.pbank_1').hide();
            $('.pcheque_1').show();
            $('#cheque_no_1').focus();
        } else if (p_val == 'bank_transfer') {
			$('.pcc_1').hide();
			$('.pcash_1').hide();
			$('.pcheque_1').hide();
			$('.pbank_1').show();
			$('#cheque_no_1').focus();
		} else {
            $('.pcheque_1').hide();
			$('.pbank_1').hide();
            $('.pcc_1').hide();
            $('.pcash_1').hide();
        }
		if(p_val == 'deposit') {
			$('.dp').show();
			checkDeposit();
		}else{
			$('.dp').hide();
			$('#dp_details').html('');
		}
        $(document).on('keyup', '#amount_1', function () {
			var us_paid = $('#amount_1').val()-0;
			var deposit_amount = parseFloat($(".deposit_total_amount").text());
			var deposit_balance = parseFloat($(".deposit_total_balance").text());
			deposit_balance = (deposit_amount - us_paid);
			$(".deposit_total_balance").text(deposit_balance);
		});
		$('#pcc_no_1').change(function (e) {
            var pcc_no = $(this).val();
            localStorage.setItem('pcc_no_1', pcc_no);
            var CardType = null;
            var ccn1 = pcc_no.charAt(0);
            if (ccn1 == 4)
                CardType = 'Visa';
            else if (ccn1 == 5)
                CardType = 'MasterCard';
            else if (ccn1 == 3)
                CardType = 'Amex';
            else if (ccn1 == 6)
                CardType = 'Discover';
            else
                CardType = 'Visa';

            $('#pcc_type_1').select2("val", CardType);
        });
		
        $('#paid_by_1').select2("val", '<?=$payment->paid_by?>');
    });
</script>
