<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    $(document).ready(function () {
        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
            }
            if (localStorage.getItem('sltax2')) {
                localStorage.removeItem('sltax2');
            }
            if (localStorage.getItem('slref')) {
                localStorage.removeItem('slref');
            }
            if (localStorage.getItem('slshipping')) {
                localStorage.removeItem('slshipping');
            }
            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }
            if (localStorage.getItem('slbiller')) {
                localStorage.removeItem('slbiller');
            }
            if (localStorage.getItem('slcurrency')) {
                localStorage.removeItem('slcurrency');
            }
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            if (localStorage.getItem('slsale_status')) {
                localStorage.removeItem('slsale_status');
            }
            if (localStorage.getItem('slpayment_status')) {
                localStorage.removeItem('slpayment_status');
            }
            if (localStorage.getItem('paid_by')) {
                localStorage.removeItem('paid_by');
            }
            if (localStorage.getItem('amount_1')) {
                localStorage.removeItem('amount_1');
            }
            if (localStorage.getItem('paid_by_1')) {
                localStorage.removeItem('paid_by_1');
            }
            if (localStorage.getItem('pcc_holder_1')) {
                localStorage.removeItem('pcc_holder_1');
            }
            if (localStorage.getItem('pcc_type_1')) {
                localStorage.removeItem('pcc_type_1');
            }
            if (localStorage.getItem('pcc_month_1')) {
                localStorage.removeItem('pcc_month_1');
            }
            if (localStorage.getItem('pcc_year_1')) {
                localStorage.removeItem('pcc_year_1');
            }
            if (localStorage.getItem('pcc_no_1')) {
                localStorage.removeItem('pcc_no_1');
            }
            if (localStorage.getItem('cheque_no_1')) {
                localStorage.removeItem('cheque_no_1');
            }
            if (localStorage.getItem('payment_note_1')) {
                localStorage.removeItem('payment_note_1');
            }
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
            }
            localStorage.removeItem('remove_slls');
        }
        <?php if($quote_id) { ?>
			localStorage.setItem('sldate', '<?= $this->erp->hrld($quote->date) ?>');
			localStorage.setItem('slcustomer', '<?= $quote->customer_id ?>');
			localStorage.setItem('slbiller', '<?= $quote->biller_id ?>');
			localStorage.setItem('slwarehouse', '<?= $quote->warehouse_id ?>');
			localStorage.setItem('slnote', '<?= str_replace(array("\r", "\n"), "", $this->erp->decode_html($quote->note)); ?>');
			localStorage.setItem('sldiscount', '<?= $quote->order_discount_id ?>');
			localStorage.setItem('sltax2', '<?= $quote->order_tax_id ?>');
			localStorage.setItem('slshipping', '<?= $quote->shipping ?>');
			localStorage.setItem('slitems', JSON.stringify(<?= $quote_items; ?>));
			localStorage.setItem('payment_deposit', JSON.stringify(<?= $payment_deposit; ?>));
        <?php } ?>
        
        <?php if($this->input->get('customer')) { ?>
			if (!localStorage.getItem('slitems')) {
				localStorage.setItem('slcustomer', <?=$this->input->get('customer');?>);
			}
        <?php } ?>
        <?php if ($Owner || $Admin) { ?>
			if (!localStorage.getItem('sldate')) {
				$("#sldate").datetimepicker({
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
			}
			$(document).on('change', '#sldate', function (e) {
				localStorage.setItem('sldate', $(this).val());
			});
			if (sldate = localStorage.getItem('sldate')) {
				$('#sldate').val(sldate);
			}
			$(document).on('change', '#slbiller', function (e) {
				localStorage.setItem('slbiller', $(this).val());
			});
			if (slbiller = localStorage.getItem('slbiller')) {
				$('#slbiller').val(slbiller);
			}
        <?php } ?>
        if (!localStorage.getItem('slref')) {
            localStorage.setItem('slref', '<?=$slnumber?>');
        }
		if (!localStorage.getItem('slrefnote')) {
            localStorage.setItem('slrefnote', '<?=$slnumber?>');
        }
        if (!localStorage.getItem('sltax2')) {
            localStorage.setItem('sltax2', <?=$Settings->default_tax_rate2;?>);
        }
        ItemnTotals();
        $('.bootbox').on('hidden.bs.modal', function (e) {
            $('#add_item').focus();
        });
        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#slcustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_cust_ware');?>');
                    $('#add_item').focus();
                    return false;
                }
                var test = request.term;
				if($.isNumeric(test)){
					$.ajax({
						type: 'get',
						url: '<?= site_url('sales/suggests'); ?>',
						dataType: "json",
						data: {
							term: request.term,
							warehouse_id: $("#slwarehouse").val(),
							customer_id: $("#slcustomer").val()
						},
						success: function (data) {
							response(data);
						}
					});
				}else{
					$.ajax({
						type: 'get',
						url: '<?= site_url('sales/suggestionsSale'); ?>',
						dataType: "json",
						data: {
							term: request.term,
							warehouse_id: $("#slwarehouse").val(),
							customer_id: $("#slcustomer").val()
						},
						success: function (data) {
							response(data);
						}
					});
				}
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
                    $(this).removeClass('ui-autocomplete-loading');
                    // $(this).val('');
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
        $('#add_item').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $(this).autocomplete("search");
            }
        });

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_sale'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                    $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                    echo form_open_multipart("sales/add", $attrib);
                    if ($quote_id) {
                        echo form_hidden('quote_id', $quote_id);
                    }
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "sldate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
						<div class="col-md-4">
                            <div class="form-group">
                                <?= lang("sale_no", "slref"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $slnumber), 'class="form-control input-tip" id="slref"'); ?>
                            </div>
                        </div>
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("pos_", "slbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ($this->session->userdata('biller_id') ? $this->session->userdata('biller_id') : $Settings->default_biller)), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'slbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div
                                    class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("warehouse", "slwarehouse"); ?>
                                                <?php
                                                /* $wh[''] = '';
                                                foreach ($warehouses as $warehouse) {
                                                    $wh[$warehouse->id] = $warehouse->name;
                                                }*/
                                                echo form_dropdown('warehouse', '', (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
                                                ?>
                                            </div>
                                        </div>
                                    <?php } else {
                                        $warehouse_input = array(
                                            'type' => 'hidden',
                                            'name' => 'warehouse',
                                            'id' => 'slwarehouse',
                                            'value' => $this->session->userdata('warehouse_id'),
                                        );

                                        echo form_input($warehouse_input);
                                    } ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("customer", "slcustomer"); ?>
                                            <?php if ($Owner || $Admin || $GP['customers-add']) { ?><div class="input-group"><?php } ?>
                                                <?php
                                                echo form_input('customer_1', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="min-width:100%;"');
                                                ?>
                                                <?php if ($Owner || $Admin || $GP['customers-add']) { ?>
												
												<div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
													<a href="#" id="view-customer" class="external" data-toggle="modal" data-target="#myModal">
														<i class="fa fa-2x fa-user" id="addIcon"></i>
													</a>
												</div>
												
                                                <div class="input-group-addon no-print" style="padding: 2px 5px;"><a
                                                        href="<?= site_url('customers/add'); ?>" id="add-customer"
                                                        class="external" data-toggle="modal" data-target="#myModal"><i
                                                            class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
									<div class="col-md-4">
										<div class="form-group">
										<?= lang("saleman", "saleman"); ?>
										<select name="saleman" id="saleman" class="form-control saleman">
											<?php 
												foreach($agencies as $agency){
													if($this->session->userdata('username') == $agency->username){
														echo '<option value="'.$this->session->userdata('user_id').'" selected>'.lang($this->session->userdata('username')).'</option>';
													}else{
														echo '<option value="'.$agency->id.'">'.$agency->username.'</option>';
													}
												}
											?>
										</select>
										<?php
										/*$sm[''] = '';
										foreach($agencies as $agency){
											$sm[$agency->id] = $agency->username;
										}
										echo form_dropdown('saleman', $sm, (isset($_POST['saleman']) ? $_POST['saleman'] : ''), 'id="slsaleman" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("saleman") . '" style="width:100%;" ');*/
										?>
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
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_product_to_order") . '"'); ?>
                                        <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addManually" class="tip" title="<?= lang('add_product_manually') ?>">
                                                <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                            </a>
                                        </div>
                                        <?php } if ($Owner || $Admin || $GP['sales-add_gift_card']) { ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="sellGiftCard" class="tip" title="<?= lang('sell_gift_card') ?>">
                                               <i class="fa fa-2x fa-credit-card addIcon" id="addIcon"></i>
                                            </a>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="slTable"
                                           class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
											<th class=""><?= lang("no"); ?></th>
                                            <th class="col-md-3"><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
                                            <?php
                                            if ($Settings->product_serial) {
                                                //echo '<th class="col-md-2">' . lang("serial_no") . '</th>';
                                            }
                                            ?>
											<th class="col-md-1"><?= lang("Price_Before_Tax"); ?></th>
                                            <?php
                                            if ($Settings->tax1) {
                                                echo '<th class="col-md-1">' . lang("product_tax") . '</th>';
                                            }
                                            ?>
                                            <th class="col-md-1"><?= lang("Price_After_Tax"); ?></th>
                                            <th class="col-md-1"><?= lang("quantity"); ?></th>
											<th class="col-md-2"><?= lang("serial_number"); ?></th>
                                            <th>
                                                <?= lang("subtotal"); ?>
                                                (<span class="currency"><?= $default_currency->code ?></span>)
                                            </th>
                                            <?php
                                            if ($Settings->product_discount || ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                echo '<th class="col-md-1">' . lang("discount") . '</th>';
                                            }
                                            ?>
                                            <th>
                                                <?= lang("final_price"); ?>
                                                (<span class="currency"><?= $default_currency->code ?></span>)
                                            </th>
                                            <th style="width: 30px !important; text-align: center;">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

						<div class="col-sm-12">

							<?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
								<div class="col-sm-4">
									<div class="form-group">
										<?= lang("Sale_Discount", "sldiscount"); ?>
										<?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount"'); ?>
									</div>
								</div>
							<?php } ?>

							<?php echo form_hidden('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("document", "document") ?>
									<input id="document" type="file" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
								</div>
							</div>
							
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("document", "document") ?>
									<input id="document1" type="file" name="document1" data-show-upload="false" data-show-preview="false" class="form-control file">
								</div>
							</div>

						</div>
						<div class="col-sm-12">
							
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("document", "document") ?>
									<input id="document2" type="file" name="document2" data-show-upload="false" data-show-preview="false" class="form-control file">
								</div>
							</div>
							
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("sale_status", "slsale_status"); ?>
									<?php $sst = array('completed' => lang('completed'), 'pending' => lang('pending'));
									echo form_dropdown('sale_status', $sst, '', 'class="form-control input-tip" required="required" id="slsale_status"'); ?>

								</div>
							</div>

						</div>

                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>

                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("sale_note", "slnote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="slnote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("staff_note", "slinnote"); ?>
                                        <?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : ""), 'class="form-control" id="slinnote" style="margin-top: 10px; height: 100px;"'); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="fprom-group">
								<?php echo form_submit('add_sale', lang("submit"), 'id="add_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0; display:none;"'); ?>
                                <button type="button" class="btn btn-primary" id="before_sub"><?= lang('submit') ?></button>
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
                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                            <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            <?php }?>
                            <?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>

                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
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
                    
					<!--
					<?php if ($Settings->product_serial) { ?>
                        <div class="form-group serial_item">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>

							<div class="col-sm-8 serial" id="serial"></div>
                        </div>
                    <?php } ?>
					-->
					
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" readonly class="form-control" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>

                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount || ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
					
                    <div class="form-group">
						<label for="pprice" class="col-sm-4 control-label"><?= lang('Price_After_Tax') ?></label>

                        <div class="col-sm-8">
							<?php if ($Owner || $Admin || $GP['sales-price']) { ?>
								<input type="text" class="form-control" id="pprice">
							<?php } else { ?>
								<input type="text" readonly class="form-control" id="pprice">
							<?php }  ?>
                        </div>
                    </div>
					
					<div class="form-group">
                        <label for="pnote" class="col-sm-4 control-label"><?= lang('Product_Note') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="pnote">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <!-- <th style="width:25%;"><?= lang('net_unit_price'); ?></th> -->
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
					<input type="hidden" id="all_serial" value=""/>
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
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="mdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mdiscount">
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
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
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

<div class="modal" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="myModalLabel"><?= lang('sell_gift_card'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>

                <div class="alert alert-danger gcerror-con" style="display: none;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span id="gcerror"></span>
                </div>
                <div class="form-group">
                    <?= lang("card_no", "gccard_no"); ?> *
                    <div class="input-group">
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#"
                                                                                                           id="genNo"><i
                                    class="fa fa-cogs"></i></a></div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?= lang('gift_card') ?>" id="gcname"/>

                <div class="form-group">
                    <?= lang("value", "gcvalue"); ?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("price", "gcprice"); ?> *
                    <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("customer", "gccustomer"); ?>
                    <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("expiry_date", "gcexpiry"); ?>
                    <?php echo form_input('gcexpiry', $this->erp->hrsd(date("Y-m-d", strtotime("+2 year"))), 'class="form-control date" id="gcexpiry"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="addGiftCard" class="btn btn-primary"><?= lang('sell_gift_card') ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
	
	var $biller = $("#slbiller");
		$(window).load(function(){
			<?php if($Admin || $Owner){ ?>
			billerChange();
			<?php } ?>
		});
	
    $(document).ready(function () {
        $('#gccustomer').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
        $('#genNo').click(function () {
            var no = generateCardNo();
            $(this).parent().parent('.input-group').children('input').val(no);
            return false;
        });
		
		$biller.change(function(){
			<?php if($Admin || $Owner){ ?>
			billerChange();
			<?php } ?>
			//$("#slwarehouse").select2("val", "<?=$Settings->default_warehouse;?>");
			$('#slwarehouse').val($('#slwarehouse option:first-child').val()).trigger('change');
		});
		
		$('#view-customer').click(function(){
            $('#myModal').modal({remote: site.base_url + 'customers/view/' + $("input[name=customer_1]").val()});
            $('#myModal').modal('show');
        });
		
		$('#before_sub').click(function () {
            var GP = '<?= $GP['sales-discount'];?>';
            var Owner = '<?= $Owner?>';
            var Admin = '<?= $Admin?>';
            var user_log = '<?= $this->session->userdata('user_id');?>';
            if(Owner || Admin || (GP == 1)){
                $('#add_sale').trigger('click');
            }else{
                var val = '';
                $('.sdiscount').each(function(){
                    var parent = $(this).parent().parent();
                    var value  = parent.find('.sdiscount').text();
                    if(value != 0){
                        val = value;
                    }
                });
                if(val == ''){
                    $('#add_sale').trigger('click');
                }else{
                    bootbox.prompt("Please insert password", function(result){                
                        $.ajax({
                            type: 'get',
                            url: '<?= site_url('auth/checkPassDiscount'); ?>',
                            dataType: "json",
                            data: {
                                password: result
                            },
                            success: function (data) {
                                if(data == 1){
                                    $('#add_sale').trigger('click');
                                }else{
                                    alert('Incorrect passord');
                                }       
                            }
                        });
                    });
                }
            }

        });
		
		/*
		$(document).on('click', '.edit',function(){
			var tr = $(this).parent().parent();
			var qty = tr.find('.rquantity').val();
			var input = '';
			for(i=0;i < qty; i++){
				input += '<input type="text" class="form-control pserial"><br/>';
			}
			$('#serial').html(input);
		});
		$(document).on('change', '#pquantity',function(){
			var qty = $(this).val();
			var input = '';
			for(i=0;i < qty; i++){
				input += '<input type="text" class="form-control pserial"><br/>';
			}
			//$(input).appendTo("#serial");
			$('#serial').html(input);
		});	
		*/
		
    });
	
	function billerChange(){
        var id = $biller.val();
        $("#slwarehouse").empty();
        $.ajax({
            url: '<?= base_url() ?>auth/getWarehouseByProject/'+id,
            dataType: 'json',
            success: function(result){
                $.each(result, function(i,val){
                    var b_id = val.id;
                    var name = val.name;
                    var opt = '<option value="' + b_id + '">' + name + '</option>';
                    $("#slwarehouse").append(opt);
                });
                $('#slwarehouse option[selected="selected"]').each(
                    function() {
                        $(this).removeAttr('selected');
                    }
                );
				$('#slwarehouse').val($('#slwarehouse option:first-child').val()).trigger('change');
                //$("#slwarehouse").select2("val", "<?=$Settings->default_warehouse;?>");
            }
        });	
    }
    
    var $warehouse = $('#slwarehouse');
		$warehouse.change(function (e) {
			localStorage.setItem('slwarehouse', $(this).val());
    });

		$('#print_depre').click(function () {	
			PopupPayments();
		});
		
		$('#export_depre').click(function () {	
			var customer_id = $('#slcustomer').val();
			var customer_name = '';
			var customer_address = '';
			var customer_tel ='';
			var customer_mail = '';
			
			$.ajax({
				type: "get",
				url: "<?= site_url('sales/getCustomerInfo'); ?>",
				data: {customer_id: customer_id},
				dataType: "html",
				async: false,
				success: function (data) {
					var obj = jQuery.parseJSON(data);
					customer_name = obj.company;
					customer_address = obj.address+', '+obj.city+', '+obj.state;
					customer_tel = obj.phone;
					customer_mail = obj.email;
				}
			});
			var issued_date = $('.current_date').val();
			var myexport = '<tbody>';
				myexport+= 		'<tr><td colspan="7" style="vertical-align:middle;"><center><h4 style="font-family:Verdana,Geneva,sans-serif; font-weight:bold;"><?= lang("loan_amortization_schedule") ?></h4></center></td></tr>';
				myexport+=		'<tr>';
				myexport+=			'<td colspan="2" width="25%"  style="padding-left:50px;"><?= lang('issued_date') ?></td>';
				myexport+=			'<td colspan="2" width="25%"><?= lang(": ") ?>'+ issued_date +'</td>';
				myexport+=			'<td colspan="3" width="50%">&nbsp;</td>';
				myexport+=		'</tr>';
				myexport+=		'<tr>';
				myexport+=			'<td colspan="2" style="padding-left:50px;"><?= lang('customer') ?></td>';
				myexport+=			'<td colspan="2"><?= lang(": ") ?>'+ customer_name +'</td>';
				myexport+=			'<td style="text-align:right; padding-right:30px;"><?= lang('address') ?></td>';
				myexport+=			'<td colspan="2"><?= lang(": ") ?>'+ customer_address +'</td>';
				myexport+=		'</tr>'+
								'<tr>'+
									'<td colspan="2" style="padding-left:50px;"><?= lang('tel') ?></td>'+
									'<td colspan="2"><?= lang(": ") ?>'+ customer_tel +'</td>'+
									'<td style="text-align:right; padding-right:30px;"><?= lang('email') ?></td>'+
									'<td colspan="2"><?= lang(": ") ?>'+ customer_mail +'</td>'+
								'</tr>';
				myexport+=		'<tr style="height:50px; vertical-align:middle;">'+
									'<th class="td_bor_style"><?= lang('No') ?></th>'+
									'<th class="td_bor_style td_align_center"><?= lang('item_code') ?></th>'+
									'<th colspan="2" class="td_bor_style"><?= lang('decription') ?></th>'+
									'<th class="td_bor_style"><?= lang('unit_price') ?></th>'+
									'<th class="td_bor_style"><?= lang('qty') ?></th>'+
									'<th class="td_bor_botton"><?= lang('amount') ?></th>'+
								  '</tr>';
			var type = $('#depreciation_type_1').val();
			var no = 0;
			var total_amt = 0;
			var total_amount = $('#total_balance').val()-0;
			var us_down = $('#amount_1').val()-0;
			var down_pay = us_down;
			var interest_rate = Number($('#depreciation_rate_1').val()-0);
			var term_ = Number($('#depreciation_term_1').val()-0);
			$('.rcode').each(function(){	
				no += 1;
				var parent = $(this).parent().parent();
				var unit_price = parent.find('.realuprice').val();
				var qtt = parent.find('.rquantity').val();
				var amt = unit_price * qtt;
				total_amt += amt;
					myexport +=	'<tr>'+
									'<td class="td_color_light td_align_center" align="center">'+ no +'</td>'+
									'<td class="td_color_light">'+ parent.find('.rcode').val() +'</td>'+
									'<td colspan="2" class="td_color_light td_align_center">'+ parent.find('.rname').val() +'</td>'+
									'<td class="td_color_light td_align_right" align="right">$ &nbsp;'+ formatMoney(unit_price) +'</td>'+
									'<td class="td_color_light" align="right">'+ qtt +'</td>'+
									'<td class="td_color_bottom_light td_align_right" align="right">$ &nbsp;'+ formatMoney(amt) +'</td>'+
								'</tr>';  
			});
			var loan_amount = total_amt;
			//if(type != 4){
				loan_amount = total_amt - down_pay;
			//}
				if(down_pay != 0 || down_pay != ''){
			myexport+=			'<tr>'+
									'<td colspan="6" style="text-align:right; padding:5px;"><?= lang('total_amount') ?></td>'+
									'<td class="td_align_right" align="right"><b>$ &nbsp;'+ formatMoney(total_amt) +'</b></td>'+
								'</tr>';
			myexport+=			'<tr>'+
									'<td colspan="6" style="text-align:right; padding:5px;"><?= lang('down_payment') ?></td>'+
									'<td class="td_align_right" align="right"><b>$ &nbsp;'+ formatMoney(down_pay) +'</b></td>'+
								'</tr>';
				}
			myexport+=			'<tr>'+
									'<td colspan="6" style="text-align:right; padding:5px;"><?= lang('loan_amount') ?></td>'+
									'<td class="td_align_right" align="right"><b>$ &nbsp;'+ formatMoney(loan_amount) +'</b></td>'+
								'</tr>'+
								'<tr>'+
									'<td colspan="6" style="text-align:right; padding:5px;"><?= lang('interest_rate_per_month') ?></td>'+
									'<td class="td_align_right" align="right"><b>'+ formatMoney(interest_rate/12) +'&nbsp; %</b></td>'+
								'</tr>';
			myexport+=			'<tr><td colspan="7" style="height:70px; vertical-align:middle; text-align:center; font-weight:bold; font-size:14px;"><?= lang('payment_term')?></td></tr>';
			myexport+=			'<tr style="height:50px; vertical-align:middle;">'+
									'<th width="10%" class="td_bor_style"><?= lang('Pmt No.') ?></th>'+
									'<th width="15%" class="td_bor_style"><?= lang('payment_date') ?></th>';
									if(type == 2){
			myexport+=				'<th width="10%" class="td_bor_style"><?= lang('rate') ?></th>';
			myexport+=				'<th width="10%" class="td_bor_style"><?= lang('percentage') ?></th>';
			myexport+=				'<th width="10%" class="td_bor_style"><?= lang('payment') ?></th>'+
									'<th width="15%" class="td_bor_style"><?= lang('total_payment') ?></th>';			
									}else{
			myexport+=				'<th width="10%" class="td_bor_style"><?= lang('interest') ?></th>'+
									'<th width="10%" class="td_bor_style"><?= lang('principle') ?></th>'+
									'<th width="15%" class="td_bor_style"><?= lang('total_payment') ?></th>';
									}
			myexport+=				'<th width="10%" class="td_bor_style"><?= lang('balance') ?></th>'+
									'<th width="25%" class="td_bor_botton"><?= lang('note') ?></th>'+
								  '</tr>';
			var k = 0;
			var total_interest = 0;
			var total_princ = 0;
			var amount_total_pay = 0;
			var total_pay_ = 0;
			$('.dep_tbl .no').each(function(){
				k += 1;
				var tr = $(this).parent().parent();
				var balance = formatMoney(tr.find('.balance').val()-0);
			if(type == 2){
				total_interest += Number(tr.find('.rate').val()-0);
				total_princ += Number(tr.find('.percentage').val()-0);
				amount_total_pay += Number(tr.find('.total_payment').val()-0);
			}else{
				total_interest += Number(tr.find('.interest').val()-0);
				total_princ += Number(tr.find('.principle').val()-0);
			}
				total_pay_ += Number(tr.find('.payment_amt').val()-0);
			myexport+=			'<tr>'+
									'<td class="td_color_light td_align_center" align="center">'+ k +'</td>'+
									'<td class="td_color_light td_align_center" align="center">'+ tr.find('.dateline').val() +'</td>';
				if(type == 2){
			myexport+=				'<td class="td_color_light td_align_center" align="right">$ &nbsp;'+ formatMoney(tr.find('.rate').val()-0) +'</td>';
			myexport+=				'<td class="td_color_light td_align_center" align="right">$ &nbsp;'+ formatMoney(tr.find('.percentage').val()-0) +'</td>';
			myexport+=				'<td class="td_color_light td_align_center" align="right">$ &nbsp;'+ formatMoney(tr.find('.payment_amt').val()-0) +'</td>';
			myexport+=				'<td class="td_color_light td_align_center" align="right">$ &nbsp;'+ formatMoney(tr.find('.total_payment').val()-0) +'</td>';
				}else{
			myexport+=				'<td class="td_color_light td_align_center" align="right">$ &nbsp;'+ formatMoney(tr.find('.interest').val()-0) +'</td>';
			myexport+=				'<td class="td_color_light td_align_center" align="right">$ &nbsp;'+ formatMoney(tr.find('.principle').val()-0) +'</td>';
			myexport+=				'<td class="td_color_light td_align_center" align="right">$ &nbsp;'+ formatMoney(tr.find('.payment_amt').val()-0) +'</td>';									
				}
			myexport+=				'<td class="td_color_light td_align_right" align="right">$ &nbsp;'+ balance +'</td>'+
									'<td class="td_color_bottom_light" style="padding-left:20px;">'+ tr.find('.note_1').val() +'</td>'+
								'</tr>';	
			});		
			if(type == 2){
			myexport+=			'<tr>'+
									'<td style="text-align:right; padding:5px;"><b> Total </b></td>'+
									'<td style="text-align:right; padding:5px;"> &nbsp; </td>'+
									'<td style="text-align:right; padding:5px;"><b>$ &nbsp;'+ formatMoney(total_princ) +'</b></td>'+
									'<td style="text-align:right; padding:5px;"><b>$ &nbsp;'+ formatMoney(total_pay_) +'</b></td>'+
									'<td style="text-align:right; padding:5px;"><b>$ &nbsp;'+ formatMoney(amount_total_pay) +'</b></td>'+
									'<td style="text-align:right; padding:5px;"> &nbsp; </td>'+
									'<td style="text-align:right; padding:5px;"> &nbsp; </td>'+
								'</tr>';								
			}else{
			myexport+=			'<tr>'+
									'<td style="text-align:right; padding:5px; border-top:1px solid black;"><b> Total </b></td>'+
									'<td style="text-align:right; padding:5px; border-top:1px solid black;"> &nbsp; </td>'+
									'<td style="text-align:right; padding:5px; border-top:1px solid black;"><b>$ &nbsp;'+ formatMoney(total_interest) +'</b></td>'+
									'<td style="text-align:right; padding:5px; border-top:1px solid black;"><b>$ &nbsp;'+ formatDecimal(total_princ) +'</b></td>'+
									'<td style="text-align:right; padding:5px; border-top:1px solid black;"><b>$ &nbsp;'+ formatMoney(total_pay_) +'</b></td>'+
									'<td style="text-align:right; padding:5px; border-top:1px solid black;"> &nbsp; </td>'+
									'<td style="text-align:right; padding:5px;"> &nbsp; </td>'+
								'</tr>';
			}
			myexport+= '</tbody>';
			$('#export_tbl').append(myexport);
			var htmltable= document.getElementById('export_tbl');
			var html = htmltable.outerHTML;
			window.open('data:application/vnd.ms-excel,' + encodeURIComponent(html));
		});


		function PopupPayments() {
			var customer_id = $('#slcustomer').val();
			var customer_name = '';
			var customer_address = '';
			var customer_tel ='';
			var customer_mail = '';
			
			$.ajax({
				type: "get",
				url: "<?= site_url('sales/getCustomerInfo'); ?>",
				data: {customer_id: customer_id},
				dataType: "html",
				async: false,
				success: function (data) {
					var obj = jQuery.parseJSON(data);
					customer_name = obj.company;
					customer_address = obj.address+', '+obj.city+', '+obj.state;
					customer_tel = obj.phone;
					customer_mail = obj.email;

					//alert(customer_name +"|"+customer_address+"|"+customer_tel+"|"+customer_mail);
				}
			});
				
				
			var mywindow = window.open('', 'erp_pos_print', 'height=auto,max-width=480,min-width=250px');
			mywindow.document.write('<html><head><title>Print</title>');
			mywindow.document.write('<link rel="stylesheet" href="<?= $assets ?>styles/helpers/bootstrap.min.css" type="text/css" />');
			mywindow.document.write('</head><body >');
			mywindow.document.write('<center>');
			var issued_date = $('.current_date').val();
			/*mywindow.document.write('<table class="table-condensed" style="width:95%; font-family:Verdana,Geneva,sans-serif; font-size:12px; padding-bottom:10px;">'+
										'<tr>'+
											'<td width="15%"><b style="font-size:18px;"><?= lang('Depreciation List') ?></b></td>'+
											'<td width="35%"></td>'+
											'<td width="15%"><?= lang('To') ?></td>'+
											'<td width="35%"><?= lang(": ") ?></td>'+
										'</tr>'+
										'<tr>'+
											'<td><?= lang('Invoice No ') ?></td>'+
											'<td><?= lang(": ") ?></td>'+
											'<td><?= lang('Contact Person') ?></td>'+
											'<td><?= lang(": ") ?></td>'+
										'</tr>'+
										'<tr>'+
											'<td><?= lang('Issued Date ') ?></td>'+
											'<td><?= lang(": ") ?>'+ issued_date +'</td>'+
											'<td><?= lang('HP') ?></td>'+
											'<td><?= lang(": ") ?></td>'+
										'</tr>'+
										'<tr>'+
											'<td></td>'+
											'<td></td>'+
											'<td><?= lang('Address') ?></td>'+
											'<td><?= lang(": ") ?></td>'+
										'</tr>'+
									'</table><br/>'
								  );*/
			mywindow.document.write("<center><h4 style='font-family:Verdana,Geneva,sans-serif;'>Loan Amortization Schedule</h4></center><br/>");
			mywindow.document.write('<table class="table-condensed" style="width:95%; font-family:Verdana,Geneva,sans-serif; font-size:12px; padding-bottom:10px;">'+
										'<tr>'+
											'<td><?= lang('Issued Date ') ?><?= lang(": ") ?>'+ issued_date +'</td>'+
										'</tr>'+
										'<tr>'+
											'<td style="width:50% !important;"><?= lang('customer') ?> <?= lang(": ") ?>'+ customer_name +'</td>'+
											'<td style="width:50% !important;"><?= lang('address') ?> <?= lang(": ") ?>'+ customer_address +'</td>'+
										'</tr>'+
										'<tr>'+
											'<td style="width:50% !important;"><?= lang('tel') ?> <?= lang(": ") ?>'+ customer_tel +'</td>'+
											'<td style="width:50% !important;"><?= lang('email') ?> <?= lang(": ") ?>'+ customer_mail +'</td>'+
										'</tr>'+
									'</table><br/>'
								  );
			mywindow.document.write('<table border="2px" class="table table-bordered table-condensed table_shape" style="width:95%; font-family:Verdana,Geneva,sans-serif; font-size:12px; border-collapse:collapse;">'+
										'<thead>'+
											 '<tr>'+
												'<th width="5%" class="td_bor_style"><?= lang('No') ?></th>'+
												'<th width="15%" class="td_bor_style td_align_center"><?= lang('Item Code') ?></th>'+
												'<th width="45%" class="td_bor_style"><?= lang('Decription') ?></th>'+
												'<th width="10%" class="td_bor_style"><?= lang('Unit Price') ?></th>'+
												'<th width="10%" class="td_bor_style"><?= lang('Qty') ?></th>'+
												'<th width="15%" class="td_bor_botton"><?= lang('Amount') ?></th>'+                
											  '</tr>'+
										'</thead>'+
											'<tbody>');
											var type = $('#depreciation_type_1').val();
											var no = 0;
											var total_amt = 0;
											var total_amount = $('#total_balance').val()-0;
											var us_down = $('#amount_1').val()-0;
											var down_pay = us_down;
											var interest_rate = Number($('#depreciation_rate_1').val()-0);
											var term_ = Number($('#depreciation_term_1').val()-0);
											$('.rcode').each(function(){	
												no += 1;
												var parent = $(this).parent().parent();
												var unit_price = parent.find('.realuprice').val();
												var qtt = parent.find('.rquantity').val();
												var amt = unit_price * qtt;
												total_amt += amt;
			mywindow.document.write(			'<tr>'+
													'<td class="td_color_light td_align_center" >'+ no +'</td>'+
													'<td class="td_color_light">'+ parent.find('.rcode').val() +'</td>'+
													'<td class="td_color_light td_align_center">'+ parent.find('.rname').val() +'</td>'+
													'<td class="td_color_light td_align_right">$ '+ formatMoney(unit_price) +'</td>'+
													'<td class="td_color_light td_align_center">'+ qtt +'</td>'+
													'<td class="td_color_bottom_light td_align_right">$ '+ formatMoney(amt) +'</td>'+
												'</tr>');  
											});
											var loan_amount = total_amt;
											//if(type != 4){
												loan_amount = total_amt - down_pay;
											//}
												if(down_pay != 0 || down_pay != ''){
			mywindow.document.write(			'<tr>'+
													'<td colspan="5" style="text-align:right; padding:5px;"><?= lang('Total Amount') ?></td>'+
													'<td class="td_align_right"><b>$ '+ formatMoney(total_amt) +'</b></td>'+
												'</tr>');
			mywindow.document.write(			'<tr>'+
													'<td colspan="5" style="text-align:right; padding:5px;"><?= lang('Down Payment') ?></td>'+
													'<td class="td_align_right"><b>$ '+ formatMoney(down_pay) +'</b></td>'+
												'</tr>');
												}
			mywindow.document.write(			'<tr>'+
													'<td colspan="5" style="text-align:right; padding:5px;"><?= lang('Loan Amount') ?></td>'+
													'<td class="td_align_right"><b>$ '+ formatMoney(loan_amount) +'</b></td>'+
												'</tr>'+
													'<td colspan="5" style="text-align:right; padding:5px;"><?= lang('interest_rate_per_month') ?></td>'+
													'<td class="td_align_right"><b>'+ formatDecimal(interest_rate/12) +' %</b></td>'+
												'</tr>');
			mywindow.document.write(		'</tbody>'+
									'</table><br/>'
									);	
			mywindow.document.write('<div class="payment_term"><b><?= lang('Payment Term')?></b></div>');
			mywindow.document.write('<table border="2px" class="table table-bordered table-condensed table_shape" style="width:95%; font-family:Verdana,Geneva,sans-serif; font-size:12px; border-collapse:collapse;">'+
										 '<thead>'+
											  '<tr>'+
												'<th width="10%" class="td_bor_style"><?= lang('Pmt No.') ?></th>'+
												'<th width="15%" class="td_bor_style"><?= lang('Payment Date') ?></th>'
									);
											if(type == 2){
			mywindow.document.write(			'<th width="10%" class="td_bor_style"><?= lang('Rate') ?></th>');
			mywindow.document.write(			'<th width="10%" class="td_bor_style"><?= lang('Percentage') ?></th>');
			mywindow.document.write(			'<th width="10%" class="td_bor_style"><?= lang('Payment') ?></th>'+
												'<th width="15%" class="td_bor_style"><?= lang('Total Payment') ?></th>'
									);			
											}else{
			mywindow.document.write(			'<th width="10%" class="td_bor_style"><?= lang('Interest') ?></th>'+
												'<th width="10%" class="td_bor_style"><?= lang('Principle') ?></th>'+
												'<th width="15%" class="td_bor_style"><?= lang('Total Payment') ?></th>'
									);
											}
			mywindow.document.write(			'<th width="10%" class="td_bor_style"><?= lang('Balance') ?></th>'+
												'<th width="25%" class="td_bor_botton"><?= lang('Note') ?></th>'+                
											  '</tr>'+
										'</thead>'+
										'<tbody>');	
										var k = 0;
										var total_interest = 0;
										var total_princ = 0;
										var amount_total_pay = 0;
										var total_pay_ = 0;
										$('.dep_tbl .no').each(function(){
											k += 1;
											var tr = $(this).parent().parent();
											var balance = formatMoney(tr.find('.balance').val()-0);
										if(type == 2){
											total_interest += Number(tr.find('.rate').val()-0);
											total_princ += Number(tr.find('.percentage').val()-0);
											amount_total_pay += Number(tr.find('.total_payment').val()-0);
										}else{
											total_interest += Number(tr.find('.interest').val()-0);
											total_princ += Number(tr.find('.principle').val()-0);
										}
											total_pay_ += Number(tr.find('.payment_amt').val()-0);
			mywindow.document.write(		'<tr>'+
													'<td class="td_color_light td_align_center">'+ k +'</td>'+
													'<td class="td_color_light td_align_center">'+ tr.find('.dateline').val() +'</td>'
													);
											if(type == 2){
			mywindow.document.write(				'<td class="td_color_light td_align_center">$ '+ formatMoney(tr.find('.rate').val()-0) +'</td>');
			mywindow.document.write(				'<td class="td_color_light td_align_center">$ '+ formatMoney(tr.find('.percentage').val()-0) +'</td>');
			mywindow.document.write(				'<td class="td_color_light td_align_center">$ '+ formatMoney(tr.find('.payment_amt').val()-0) +'</td>');
			mywindow.document.write(				'<td class="td_color_light td_align_center">$ '+ formatMoney(tr.find('.total_payment').val()-0) +'</td>');
											}else{
			mywindow.document.write(				'<td class="td_color_light td_align_center">$ '+ formatMoney(tr.find('.interest').val()-0) +'</td>');
			mywindow.document.write(				'<td class="td_color_light td_align_center">$ '+ formatMoney(tr.find('.principle').val()-0) +'</td>');
			mywindow.document.write(				'<td class="td_color_light td_align_center">$ '+ formatMoney(tr.find('.payment_amt').val()-0) +'</td>');									
											}
			mywindow.document.write(				'<td class="td_color_light td_align_right">$ '+ balance +'</td>'+
													'<td class="td_color_bottom_light">'+ tr.find('.note_1').val() +'</td>'+
												'</tr>');	
										});		
										if(type == 2){
			mywindow.document.write(			'<tr>'+
													'<td style="text-align:right; padding:5px;" colspan="2"><b> Total </b></td>'+
													'<td style="text-align:left; padding:5px;"> &nbsp; </td>'+
													'<td style="text-align:left; padding:5px;"><b>$ '+ formatMoney(total_princ) +'</b></td>'+
													'<td style="text-align:left; padding:5px;"><b>$ '+ formatMoney(total_pay_) +'</b></td>'+
													'<td style="text-align:left; padding:5px;"><b>$ '+ formatMoney(amount_total_pay) +'</b></td>'+
													'<td style="text-align:left; padding:5px;"> &nbsp; </td>'+
													'<td style="text-align:left; padding:5px;"> &nbsp; </td>'+
												'</tr>');								
										}else{
			mywindow.document.write(			'<tr>'+
													'<td style="text-align:right; padding:5px;"><b> Total </b></td>'+
													'<td style="text-align:left; padding:5px;"> &nbsp; </td>'+
													'<td style="text-align:left; padding:5px;"><b>$ '+ formatMoney(total_interest) +'</b></td>'+
													'<td style="text-align:left; padding:5px;"><b>$ '+ formatMoney(total_princ) +'</b></td>'+
													'<td style="text-align:left; padding:5px;"><b>$ '+ formatMoney(total_pay_) +'</b></td>'+
													'<td style="text-align:left; padding:5px;"> &nbsp; </td>'+
													'<td style="text-align:left; padding:5px;"> &nbsp; </td>'+
												'</tr>');
										}
			mywindow.document.write(	'</tbody>'+
									'</table>'
									);

			mywindow.document.write('</center>');
			mywindow.document.write('</body></html>');
			mywindow.print();
			//mywindow.close();
			return true;
		}
	
</script>
