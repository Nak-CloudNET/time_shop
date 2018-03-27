<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_deposit') . " (" . $company->name . ")"; ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("customers/edit_deposit/" . $deposit->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-sm-12">
				
					<?php if ($Owner || $Admin) { ?>
						<div class="form-group">
							<?= lang("biller", "biller"); ?>
							<?php
							foreach ($billers as $biller) {
								$bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
							}
							echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $deposit->biller_id), 'class="form-control" id="posbiller" required="required"');
							?>
						</div>
					<?php } else {
						$biller_input = array(
							'type' => 'hidden',
							'name' => 'biller',
							'id' => 'posbiller',
							'value' => $this->session->userdata('biller_id'),
						);

						echo form_input($biller_input);
					}
					?>
				
                    <?php if ($Owner || $Admin) { ?>
                    <div class="form-group">
                        <?php echo lang('date', 'date'); ?>
                        <div class="controls">
                            <?php echo form_input('date', set_value('date', $this->erp->hrld($deposit->date)), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="form-group">
                        <?php echo lang('amount', 'amount'); ?>
                        <div class="controls">
                            <?php echo form_input('amount', set_value('amount', $this->erp->formatMoney($deposit->amount)), 'class="form-control" id="amount" required="required"'); ?>
                        </div>
                    </div>

					<div class="form-group">
						<?= lang("paying_by", "paid_by"); ?>
						<?php 
						$paid_by_arr = array(
							'cash' 		=> lang("cash"), 
							'CC' 		=> lang("CC"),  
							'Cheque' 	=> lang("cheque")
						);
						echo form_dropdown('paid_by', $paid_by_arr, $deposit->paid_by, 'class="form-control paid_by" id="paid_by"') ?>
					</div>
					
					<div class="form-group gc" style="display: none;">
						<?= lang("gift_card_no", "gift_card_no"); ?>
						<input name="gift_card_no" type="text" id="gift_card_no" class="pa form-control kb-pad"/>

						<div id="gc_details"></div>
					</div>
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
					<div class="pcheque_1" style="display:none;">
						<div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
							<input name="cheque_no" value="<?= $payment->cheque_no; ?>" type="text" id="cheque_no_1" class="form-control cheque_no"/>
						</div>
					</div>

					<div class="form-group">
                        <?php echo lang('note', 'note'); ?>
                        <div class="controls">
                            <?php echo form_textarea('note', $deposit->note, 'class="form-control" id="note"'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_deposit', lang('edit_deposit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
		$('#pcc_type_1').val('<?= $payment->cc_type;?>').trigger('change'); 
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
                        } else if (data.customer_id != null && data.customer_id != <?= $deposit->id ?>) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('gift_card_not_for_customer')?>');
                        } else {
                            //var due = <?=$inv->grand_total-$inv->paid?>;
							var due = parseFloat($("#amount").val());
                            if (due > data.balance) {
                                $('#amount_1').val(formatDecimal(data.balance));
                            }
                            $('#gc_details').html('<small>Card No: <span style="max-width:60%;float:right;">' + data.card_no + '</span><br>Value: <span style="max-width:60%;float:right;">' + currencyFormat(data.value) + '</span><br>Balance: <span style="max-width:60%;float:right;">' + currencyFormat(data.balance) + '</span></small>');
                            $('#gift_card_no').parent('.form-group').removeClass('has-error');
                        }
                    }
                });
            }
        });
        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val();
            $('#rpaidby').val(p_val);
            if (p_val == 'cash') {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').show();
                $('#amount_1').focus();
            } else if (p_val == 'CC') {
                $('.pcheque_1').hide();
                $('.pcash_1').hide();
                $('.pcc_1').show();
                $('#pcc_no_1').focus();
            } else if (p_val == 'Cheque') {
                $('.pcc_1').hide();
                $('.pcash_1').hide();
                $('.pcheque_1').show();
                $('#cheque_no_1').focus();
            } else {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').hide();
            }
            if (p_val == 'gift_card') {
                $('.gc').show();
                $('#gift_card_no').focus();
            } else {
                $('.gc').hide();
            }
        });
		var p_val = '<?= $deposit->paid_by; ?>';
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
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'erp',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
    });
</script>


