﻿<script type="text/javascript">
    <?php if ($this->session->userdata('remove_pols')) { ?>
    if (localStorage.getItem('poitems')) {
        localStorage.removeItem('poitems');
    }
    if (localStorage.getItem('podiscount')) {
        localStorage.removeItem('podiscount');
    }
    if (localStorage.getItem('potax2')) {
        localStorage.removeItem('potax2');
    }
    if (localStorage.getItem('poshipping')) {
        localStorage.removeItem('poshipping');
    }
    if (localStorage.getItem('poref')) {
        localStorage.removeItem('poref');
    }
    if (localStorage.getItem('powarehouse')) {
        localStorage.removeItem('powarehouse');
    }
    if (localStorage.getItem('ponote')) {
        localStorage.removeItem('ponote');
    }
	if (localStorage.getItem('slpayment_term')) {
        localStorage.removeItem('slpayment_term');
    }
    if (localStorage.getItem('posupplier')) {
        localStorage.removeItem('posupplier');
    }
	if (localStorage.getItem('psupplier')) {
        localStorage.removeItem('psupplier');
    }
    if (localStorage.getItem('pocurrency')) {
        localStorage.removeItem('pocurrency');
    }
    if (localStorage.getItem('poextras')) {
        localStorage.removeItem('poextras');
    }
    if (localStorage.getItem('podate')) {
        localStorage.removeItem('podate');
    }
	if (localStorage.getItem('expected_date')) {
        localStorage.removeItem('expected_date');
    }
   
    <?php $this->erp->unset_data('remove_pols');
} ?>
    <?php if($quote_id) { ?>
    localStorage.setItem('powarehouse', '<?= $quote->warehouse_id ?>');
    localStorage.setItem('ponote', '<?= str_replace(array("\r", "\n"), "", $this->erp->decode_html($quote->note)); ?>');
    localStorage.setItem('podiscount', '<?= $quote->order_discount_id ?>');
    localStorage.setItem('potax2', '<?= $quote->order_tax_id ?>');
    localStorage.setItem('poshipping', '<?= $quote->shipping ?>');
    localStorage.setItem('poitems', JSON.stringify(<?= $quote_items; ?>));
    <?php } ?>
	
    <?php if($catelog_id) { ?>
			localStorage.setItem('poitems', JSON.stringify(<?= $catelog_items; ?>));
	<?php } ?>
	
	<?php if ($inv) { ?>
		localStorage.setItem('poid', '<?=$inv->id?>');
		localStorage.setItem('po_ordered', 'ordered');
		localStorage.setItem('podate', '<?= date($dateFormats['php_sdate'], strtotime($inv->date))?>');
		localStorage.setItem('slbiller', '<?=$inv->biller_id ?>');
        localStorage.setItem('posupplier', '<?=$inv->supplier_id?>');
        localStorage.setItem('powarehouse', '<?=$inv->warehouse_id?>');
		localStorage.setItem('order_ref', '<?=$inv->order_ref?>');
        localStorage.setItem('postatus', 'ordered');
        localStorage.setItem('ponote', '<?= str_replace(array("\r", "\n"), "", $this->erp->decode_html($inv->note)); ?>');
        localStorage.setItem('podiscount', '<?=$inv->order_discount_id?>');
        localStorage.setItem('potax2', '<?=$inv->order_tax_id?>');
        localStorage.setItem('poshipping', '<?=$inv->shipping?>');
        localStorage.setItem('popayment_term', '<?=$inv->payment_term?>');
        localStorage.setItem('slpayment_status', '<?=$inv->payment_status?>');
        if (parseFloat(localStorage.getItem('potax2')) >= 1 || localStorage.getItem('podiscount').length >= 1 || parseFloat(localStorage.getItem('poshipping')) >= 1) {
            localStorage.setItem('poextras', '1');
        }
        //localStorage.setItem('posupplier', '<?=$inv->supplier_id?>');
        localStorage.setItem('poitems', JSON.stringify(<?=$inv_items;?>));
		localStorage.setItem('fDisable','yes');
        <?php }?>

    var count = 1, an = 1, po_edit = false, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, DC = '<?= $default_currency->code ?>', shipping = 0,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>, poitems = {};
        //audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
        //audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function () {
        <?php if($this->input->get('supplier')) { ?>
        if (!localStorage.getItem('poitems')) {
            localStorage.setItem('posupplier', <?=$this->input->get('supplier');?>);
        }
		if (!localStorage.getItem('poitems')) {
            localStorage.setItem('psupplier', <?=$this->input->get('supplier');?>);
        }
        <?php } ?>
		
        <?php if ($Owner || $Admin) { ?>
        if (!localStorage.getItem('podate')) {
            $("#podate").datetimepicker({
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
		if (!localStorage.getItem('expected_date')) {
            $("#expected_date").datetimepicker({
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
        $(document).on('change', '#podate', function (e) {
            localStorage.setItem('podate', $(this).val());
        });
		 $(document).on('change', '#expected_date', function (e) {
            localStorage.setItem('expected_date', $(this).val());
        });
        if (podate = localStorage.getItem('podate')) {
            $('#podate').val(podate);
        }
		if (expected_date = localStorage.getItem('expected_date')) {
            $('#expected_date').val(expected_date);
        }
        <?php } ?>
        if (!localStorage.getItem('potax2')) {
            localStorage.setItem('potax2', <?=$Settings->default_tax_rate2;?>);
            setTimeout(function(){ $('#extras').iCheck('check'); }, 1000);
        }
        ItemnTotals();
		$("#add_item").autocomplete({
			//source: '<?= site_url('purchases/suggestions'); ?>',
            source: function (request, response) {
				var test = request.term;
				if($.isNumeric(test)){
					$.ajax({
						type: 'get',
						url: '<?= site_url('purchases/suggests'); ?>',
						dataType: "json",
						data: {
							term: request.term,
							warehouse_id: $("#poswarehouse").val(),
							customer_id: $("#poscustomer").val()
						},
						success: function (data) {
							response(data);
						}
					});
				}else{
					$.ajax({
						type: 'get',
						url: '<?= site_url('purchases/suggestions'); ?>',
						dataType: "json",
						data: {
							term: request.term,
							warehouse_id: $("#poswarehouse").val(),
							customer_id: $("#poscustomer").val()
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
					/* bootbox.confirm({
						message: '<?= lang('no_match_found') ?>',
						buttons: {
							'cancel': {
								label: 'Close',
								className: 'btn-danger'
							},
							'confirm': {
								label: 'Create',
								className: 'btn-primary'
							}
						},
						callback: function(result) {
							if (result) {
								
							}
						}
					}); */
					
                    $(this).val('');
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
					//$(location).attr("href", "<?= site_url('products/add'); ?>");
                    //$(this).removeClass('ui-autocomplete-loading');
                    // $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_purchase_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

		//=====================Related Strap=========================
		$(document).on('ifChecked', '#related_strap', function (e) {
            $('#strap-con').slideDown();
        });
        $(document).on('ifUnchecked', '#related_strap', function (e) {
            $(".select-strap").select2("val", "");
            $('.attr-remove-all').trigger('click');
            $('#strap-con').slideUp();
        });
		//=====================end===================================

		$(document).on('change', '#cf1', function (e) {
            localStorage.setItem('pr_c_case_material', $(this).val());
        });
		if (pr_c_case_material = localStorage.getItem('pr_c_case_material')) {
            $('#cf1').val(pr_c_case_material);
        }
		
		$('#add_item').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $(this).autocomplete("search");
            }
        });
		
        $(document).on('click', '#addItemManually', function (e) {
            if (!$('#type').val()) {
                $('#mError').text('<?= lang('product_type_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
			if (!$('#code').val()) {
                $('#mError').text('<?= lang('product_code_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#name').val()) {
                $('#mError').text('<?= lang('product_name_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
			if (!$('#barcode_symbology').val()) {
                $('#mError').text('<?= lang('barcode_symbology_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#category').val()) {
                $('#mError').text('<?= lang('product_category_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#unit').val()) {
                $('#mError').text('<?= lang('product_unit_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#cost').val()) {
                $('#mError').text('<?= lang('product_cost_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#price').val()) {
                $('#mError').text('<?= lang('product_price_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            var msg, row = null, product = {
                type: $('#type').val(),
                code: $('#code').val(),
                name: $('#name').val(),
				barcode_symbology: $('#barcode_symbology').val(),
				subcategory: $('#subcategory').val(),
                tax_rate: $('#tax').val(),
                tax_method: $('#tax_method').val(),
                category_id: $('#category').val(),
                unit: $('#unit').val(),
                cost: $('#cost').val(),
                price: $('#price').val(),
				alert_quantity: $('#alert_quantity').val(),
				supplier1: $('#supplier'),
				image: $('#product_image').val(),
				product_details: $('#details').val(),
				warehouse_id: $('#rwh_qty_'+<?= isset($wh_pr->id)?$wh_pr->id:1; ?>).val()
            };

            $.ajax({
                type: "get", async: false,
                url: site.base_url + "products/addByAjax",
                data: {token: "<?= $csrf; ?>", product: product},
                dataType: "json",
                success: function (data) {
                    if (data.msg == 'success') {
                        row = add_purchase_item(data.result);
                    } else {
                        msg = data.msg;
                    }
                }
            });
            if (row) {
                $('#mModal').modal('hide');
                //audio_success.play();
            } else {
                $('#mError').text(msg);
                $('#mError-con').show();
            }
            return false;

        });
    });

</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_purchase'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
				
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo form_open_multipart("purchases/add", $attrib)
                ?>

                <div class="row">
                    <div class="col-lg-12">

                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "podate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
						
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("reference_no", "reference_no"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $ponumber), 'class="form-control input-tip" id="reference_no"'); ?>
                            </div>
                        </div>
						
						<?php if($inv->reference_no) { ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("pur_order_reference", "pur_order_reference"); ?>
									<?php echo form_input('pur_order_reference', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $inv->reference_no), 'class="form-control input-tip" id="pur_order_reference" style="pointer-events: none;"'); ?>
								</div>
							</div>
						<?php } ?>
						
                        <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("warehouse_of_reception", "powarehouse"); ?>
                                    <?php
                                    $wh[''] = '';
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
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
                    
						<div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang("supplier", "posupplier"); ?>
                                            <?php if ($Owner || $Admin || $GP['suppliers-add']) { ?><div class="input-group"><?php } ?>
                                                <input type="hidden" name="supplier" value="" id="posupplier"
                                                       class="form-control" style="width:100%;"
                                                       placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
                                                <input type="hidden" name="supplier_id" value="" id="supplier_id"
                                                       class="form-control">
                                                <?php if ($Owner || $Admin || $GP['suppliers-add']) { ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 5px;"><a
                                                        href="<?= site_url('suppliers/add'); ?>" id="add-supplier"
                                                        class="external" data-toggle="modal" data-target="#myModal"><i
                                                            class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="clearfix"></div>
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
                                            <a href="<?= site_url('products/add') ?>" id="addManually"><i
                                                    class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i></a></div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?></label>

                                <div class="controls table-controls">
                                    <table id="poTable"
                                           class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
											<th  class=""><?= lang("no"); ?></th>
                                            <th class="col-md-4"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                            <?php
                                            if ($Settings->product_expiry) {
                                                echo '<th class="col-md-2">' . $this->lang->line("expiry_date") . '</th>';
                                            }
                                            ?>
											<th class="col-md-1"><?= lang("price"); ?></th>
                                            <th class="col-md-1"><?= lang("net_unit_cost"); ?></th>
                                            <th class="col-md-1"><?= lang("quantity"); ?></th>
											<th class="col-md-1"><?= lang("stock_in_hand"); ?></th>
											<?php if($inv) { ?>
												<th class="col-md-1"><?= lang("quantity_receieved"); ?></th>
											<?php } ?>
											
                                            <?php
                                            if ($Settings->product_discount) {
                                                echo '<th class="col-md-1">' . $this->lang->line("discount") . '</th>';
                                            }
                                            ?>
                                            <?php
                                            if ($Settings->tax1) {
                                                echo '<th class="col-md-1">' . $this->lang->line("product_tax") . '</th>';
                                            }
                                            ?>
                                            <th><?= lang("subtotal"); ?> (<span
                                                    class="currency"><?= $default_currency->code ?></span>)
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
                        
						<div class="clearfix"></div>
                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>
                        <input type="hidden" name="po_id" value="<?= $inv->id ? $inv->id : 0; ?>" id="po_id"/>

                        <div class="col-md-12">
						
                            <div class="form-group">
                                <input type="checkbox" class="checkbox" id="extras" value=""/><label for "extras" class="padding05"><?= lang('more_options') ?></label>
                            </div>
							
                            <div class="row" id="extras-con" style="display: none;">
    						  
								<div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("discount_label", "podiscount"); ?>
                                        <?php echo form_input('discount', '', 'class="form-control input-tip" id="podiscount"'); ?>
                                    </div>
                                </div>

                            </div>
							
                            <div class="clearfix"></div>
							
                            <div class="form-group">
                                <?= lang("note", "ponote"); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>

                        </div>
						
                        <div class="col-md-12">
                            <div class="from-group"><?php echo form_submit('add_pruchase', $this->lang->line("submit"), 'id="add_pruchase" class="btn btn-primary btn_purchase" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
                            <td><?= lang('discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>

                <!-- <input type="hidden" name="psupplier[]"> -->

                <?php echo form_close(); ?>

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
					<div class="form-group">
						<label class="col-sm-4 control-label"><?= lang('supplier_products') ?></label>
						<div class="col-sm-8">
							<input type="hidden" name="psupplier[]" value="" id="psupplier" class="" style="width:100%;" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
						</div>
					</div>
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
					<?php if ($Settings->purchase_serial) { ?>
                        <div class="form-group">
							<label for="serial_no" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
							 <input type="hidden" class="form-control" id="get_is_serial">
							<div class="col-sm-8" id="serial"></div>
						</div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_expiry) { ?>
                        <div class="form-group">
                            <label for="pexpiry" class="col-sm-4 control-label"><?= lang('product_expiry') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="pexpiry">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>

                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pcost" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pcost">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_cost'); ?></th>
                            <th style="width:25%;"><span id="net_cost"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_cost" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_cost" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary editItem" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_standard_product') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="alert alert-danger" id="mError-con" style="display: none;">
                    <!--<button data-dismiss="alert" class="close" type="button">×</button>-->
                    <span id="mError"></span>
                </div>
                <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>

                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("products/add", $attrib)
                ?>

                <div class="col-md-5">
                    <div class="form-group">
                        <?= lang("product_type", "type") ?>
                        <?php
                        $opts = array('standard' => lang('standard'), 'combo' => lang('combo'), 'digital' => lang('digital'), 'service' => lang('service'));
                        echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control" id="type" required="required"');
                        ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_name", "name") ?>
                        <?= form_input('name', (isset($_POST['name']) ? $_POST['name'] : ($product ? $product->name : '')), 'class="form-control" id="name" required="required"'); ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_code", "code") ?>
                        <?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : ($product ? $product->code : '')), 'class="form-control" id="code"  required="required"') ?>
                        <span class="help-block"><?= lang('you_scan_your_barcode_too') ?></span>
                    </div>
                    <div class="form-group all">
                        <?= lang("barcode_symbology", "barcode_symbology") ?>
                        <?php
                        $bs = array('code25' => 'Code25', 'code39' => 'Code39', 'code128' => 'Code128', 'ean8' => 'EAN8', 'ean13' => 'EAN13', 'upca ' => 'UPC-A', 'upce' => 'UPC-E');
                        echo form_dropdown('barcode_symbology', $bs, (isset($_POST['barcode_symbology']) ? $_POST['barcode_symbology'] : ($product ? $product->barcode_symbology : 'code128')), 'class="form-control select" id="barcode_symbology" required="required" style="width:100%;"');
                        ?>

                    </div>
                    <div class="form-group all">
                        <?= lang("category", "category") ?>
                        <?php
                        $cat[''] = "";
                        foreach ($categories as $category) {
                            $cat[$category->id] = $category->name;
                        }
                        echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : '')), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
                        ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_line", "product_line") ?>
                        <div class="controls" id="subcat_data"> <?php
                            echo form_input('product_line', ($product ? $product->subcategory_id : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                            ?>
                        </div>
                    </div>
                    <div class="form-group all">
                        <label class="control-label" for="unit"><?= lang("product_unit") ?></label>
                        <?= form_input('unit', (isset($_POST['unit']) ? $_POST['unit'] : ($product ? $product->unit : '')), 'class="form-control tip" id="unit" required="required"') ?>
                    </div>
                    <div class="form-group standard">
                        <?= lang("product_cost", "cost") ?>
                        <?= form_input('cost', (isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->erp->formatPurDecimal($product->cost) : '')), 'class="form-control tip" id="cost" required="required"') ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_price", "price") ?>
                        <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->erp->formatPurDecimal($product->price) : '')), 'class="form-control tip" id="price" required="required"') ?>
                    </div>

                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group all">
                            <?= lang("product_tax", "tax_rate") ?>
                            <?php
                            $tr[""] = "";
                            foreach ($tax_rates as $tax) {
                                $tr[$tax->id] = $tax->name;
                            }
                            echo form_dropdown('tax_rate', $tr, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : $Settings->default_tax_rate)), 'class="form-control select" id="tax_rate" placeholder="' . lang("select") . ' ' . lang("product_tax") . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group all">
                            <?= lang("tax_method", "tax_method") ?>
                            <?php
                            $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                            echo form_dropdown('tax_method', $tm, (isset($_POST['tax_method']) ? $_POST['tax_method'] : ($product ? $product->tax_method : '')), 'class="form-control select" id="tax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                            ?>
                        </div>
                    <?php } ?>
                    <div class="form-group standard">
                        <?= lang("alert_quantity", "alert_quantity") ?>
                        <div
                            class="input-group"> <?= form_input('alert_quantity', (isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : ($product ? $this->erp->formatQuantity($product->alert_quantity) : '')), 'class="form-control tip" id="alert_quantity"') ?>
                            <span class="input-group-addon">
                            <input type="checkbox" name="track_quantity" id="track_quantity"
                                   value="1" <?= ($product ? (isset($product->track_quantity) ? 'checked="checked"' : '') : 'checked="checked"') ?>>
                        </span>
                        </div>
                    </div>
            

                    <div class="form-group all">
                        <?= lang("product_image", "product_image") ?>
                        <input id="product_image" type="file" name="product_image" data-show-upload="false"
                               data-show-preview="false" accept="image/*" class="form-control file">
                    </div>

                    <div class="form-group all">
                        <?= lang("product_gallery_images", "images") ?>
                        <input id="images" type="file" name="userfile[]" multiple="true" data-show-upload="false"
                               data-show-preview="false" class="form-control file" accept="image/*">
                    </div>
                    <div id="img-details"></div>
                </div>
                <div class="col-md-6 col-md-offset-1">
                <div class="standard">
                        <div class="<?= $product ? 'text-warning' : '' ?>">
                            <strong><?= lang("warehouse_quantity") ?></strong><br>
                            <?php
                            if (!empty($warehouses)) {
                                if ($product) {
                                    echo '<div class="row"><div class="col-md-12"><div class="well"><div id="show_wh_edit">';
                                    if (!empty($warehouses_products)) {
                                        echo '<div style="display:none;">';
                                        foreach ($warehouses_products as $wh_pr) {
                                            echo '<span class="bold text-info">' . $wh_pr->name . ': <span class="padding05" id="rwh_qty_' . $wh_pr->id . '">' . $this->erp->formatQuantity($wh_pr->quantity) . '</span>' . ($wh_pr->rack ? ' (<span class="padding05" id="rrack_' . $wh_pr->id . '">' . $wh_pr->rack . '</span>)' : '') . '</span><br>';
                                        }
                                        echo '</div>';
                                    }
                                    foreach ($warehouses as $warehouse) {
                                        //$whs[$warehouse->id] = $warehouse->name;
                                        echo '<div class="col-md-6 col-sm-6 col-xs-6" style="padding-bottom:15px;">' . $warehouse->name . '<br><div class="form-group">' . form_hidden('wh_' . $warehouse->id, $warehouse->id) . form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : (isset($warehouse->quantity) ? $warehouse->quantity : '')), 'class="form-control wh" id="wh_qty_' . $warehouse->id . '" placeholder="' . lang('quantity') . '"') . '</div>';
                                        if ($this->Settings->racks) {
                                            echo '<div class="form-group">' . form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : (isset($warehouse->rack) ? $warehouse->rack : '')), 'class="form-control wh" id="rack_' . $warehouse->id . '" placeholder="' . lang('rack') . '"') . '</div>';
                                        }
                                        echo '</div>';
                                    }
                                    echo '</div><div class="clearfix"></div></div></div></div>';
                               
							   } else {
                                    
									echo '<div class="row"><div class="col-md-12"> <div class="well" style="padding-left:8px;padding-right:5px;">';
                                   
								   foreach ($warehouses as $warehouse) {
                                        //$whs[$warehouse->id] = $warehouse->name;
                                        
										echo '<div class="col-md-6 col-sm-6 col-xs-6" style="padding-bottom:15px;">' . $warehouse->name . '<br><div class="form-group">' . form_hidden('wh_' . $warehouse->id, $warehouse->id) . form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : ''), 'class="form-control" id="wh_qty_' . $warehouse->id . '" placeholder="' . lang('quantity') . '"') . '</div>';
                                        
										if ($this->Settings->racks) {
                                            
											echo '<div class="form-group">' . form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : ''), 'class="form-control" id="rack_' . $warehouse->id . '" placeholder="' . lang('rack') . '"') . '</div>';
                                        }
                                        
										echo '</div>';
                                    }
                                    echo '<div class="clearfix"></div></div></div></div>';
                                }
                            }
                            ?>
                        </div>
                        <div class="clearfix"></div>
                        <div id="attrs"></div>
                        <?php /* if ($this->Settings->attributes) { ?>

                          <strong><?= lang("attributes", "attr") ?></strong><br>
                          <?php
                          if (!empty($attributes)) {
                          echo '<div class="row"><div class="col-md-12"><div class="well">';

                          foreach ($attributes as $attribute) {
                          echo '<div class="col-md-12"><label for="'.$attribute->id.'
						  
						  
						  "><input class="checkbox attributes" type="checkbox" name="attr_'.$attribute->id.'" id="'.$attribute->id.'" value="1" '.(isset($_POST['attr_'.$attribute->id]) ? 'checked="checked"' : '').' /> ' . lang($attribute->title) . '</label><br><div id="options_'.$attribute->id.'" '.(isset($_POST['attr_'.$attribute->id]) ? '' : 'style="display:none;"').'>';
                          if($attribute->options) { $options = explode('|', $attribute->options);
                          foreach($options as $option) {
                          echo '<div style="font-weight:bold;">'.$option.'</div><div class="clearfix"></div>';
                          $option = url_title($option, '_');
                          foreach ($warehouses as $warehouse) {
                          echo '<div class="col-md-6 col-sm-6 col-xs-6"><label>'.$warehouse->name.'</label>'. form_hidden('attr_wh_'.$warehouse->id, $warehouse->id).form_hidden('option_' . url_title($option, '_').'_'. $warehouse->id, $option).form_input('qty_'.$option.'_wh_'.$warehouse->id, (isset($_POST['qty_'.$option.'_wh_'.$warehouse->id]) ? $_POST['qty_'.$option.'_wh_'.$warehouse->id] : ''), 'class="form-control" placeholder="'.lang('quantity').'"').'</div>';
                          }
                          echo '<div style="clear:both;height:15px;"></div>';
                          } }
                          echo '</div></div>';
                          }
                          echo '<div class="clearfix"></div></div></div></div>';
                          }
                          ?>
                          <div class="clearfix"></div>
                          <?php } */ ?>
                        <div class="form-group">
                            <input type="checkbox" class="checkbox" name="attributes"
                                   id="attributes" <?= $this->input->post('attributes') || $product_options ? 'checked="checked"' : ''; ?>><label
                                for="attributes"
                                class="padding05"><?= lang('product_has_attributes'); ?></label> <?= lang('eg_sizes_colors'); ?>
                        </div>
                        <div class="well well-sm" id="attr-con"
                             style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?>">
                            <div class="form-group" id="ui" style="margin-bottom: 0;">
                                <div class="input-group">
                                    <?php echo form_input('attributesInput', '', 'class="form-control select-tags" id="attributesInput" placeholder="' . $this->lang->line("enter_attributes") . '"'); ?>
                                    <div class="input-group-addon" style="padding: 2px 5px;"><a href="#"
                                                                                                id="addAttributes"><i
                                                class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                            <div class="table-responsive">
                                <table id="attrTable" class="table table-bordered table-condensed table-striped"
                                       style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?>margin-bottom: 0; margin-top: 10px;">
                                    <thead>
                                    <tr class="active">
                                        <th><?= lang('name') ?></th>
                                        <!--<th><?= lang('warehouse') ?></th>-->
                                        <th><?= lang('quantity_unit') ?></th>
										<!--<th><?= lang('quantity') ?></th>
                                        <th><?= lang('cost') ?></th>-->
                                        <th><?= lang('price') ?></th>
                                        <th><i class="fa fa-times attr-remove-all"></i></th>
                                    </tr>
                                    </thead>
                                    <tbody><?php
                                    if ($this->input->post('attributes')) {
                                        $a = sizeof($_POST['attr_name']);
                                        for ($r = 0; $r <= $a; $r++) {
                                            if (isset($_POST['attr_name'][$r]) && (isset($_POST['attr_warehouse'][$r]) || isset($_POST['attr_quantity_unit'][$r]) || isset($_POST['attr_quantity'][$r]))) {
                                                echo '<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' . $_POST['attr_name'][$r] . '"><span>' . $_POST['attr_name'][$r] . '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $_POST['attr_warehouse'][$r] . '"><input type="hidden" name="attr_wh_name[]" value="' . $_POST['attr_wh_name'][$r] . '"><span>' . $_POST['attr_wh_name'][$r] . '</span></td><td class="quantity_unit text-center"><input type="hidden" name="attr_quantity_unit[]" value="' . $_POST['attr_quantity_unit'][$r] . '"><span>' . $_POST['attr_quantity_unit'][$r] . '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $_POST['attr_warehouse'][$r] . '"><input type="hidden" name="attr_wh_name[]" value="' . $_POST['attr_wh_name'][$r] . '"><span>' . $_POST['attr_wh_name'][$r] . '</span></td><td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="' . $_POST['attr_quantity'][$r] . '"><span>' . $_POST['attr_quantity'][$r] . '</span></td><td class="cost text-right"><input type="hidden" name="attr_cost[]" value="' . $_POST['attr_cost'][$r] . '"><span>' . $_POST['attr_cost'][$r] . '</span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $_POST['attr_price'][$r] . '"><span>' . $_POST['attr_price'][$r] . '</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>';
                                            }
                                        }
                                    } elseif ($product_options) {
                                        foreach ($product_options as $option) {
                                            echo '<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' . $option->name . '"><span>' . $option->name . '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $option->warehouse_id . '"><input type="hidden" name="attr_wh_name[]" value="' . $option->wh_name . '"><span>' . $option->wh_name . '</span></td><td class="quantity_unit text-center"><input type="hidden" name="attr_quantity_unit[]" value="' . $this->erp->formatQuantity($option->wh_qty) . '"><span>' . $this->erp->formatQuantity($option->wh_qty) . '</span></td><td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="' . $this->erp->formatQuantity($option->wh_qty) . '"><span>' . $this->erp->formatQuantity($option->wh_qty) . '</span></td><td class="cost text-right"><input type="hidden" name="attr_cost[]" value="' . $this->erp->formatMoneyPurchase($option->cost) . '"><span>' . $this->erp->formatMoneyPurchase($option->cost) . '</span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $this->erp->formatMoneyPurchase($option->price) . '"><span>' . $this->erp->formatMoneyPurchase($option->price) . '</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>';
                                        }
                                    }
                                    ?></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
					<div class="standard">
						<div class="form-group">
							<input type="checkbox" class="checkbox" name="related_strap"
								   id="related_strap" <?= $this->input->post('related_strap') || $product_options ? 'checked="checked"' : ''; ?>><label
								for="related_strap"
								class="padding05"><?= lang('related_strap'); ?></label>
						</div>
						<div class="well well-sm" id="strap-con"
							 style="<?= $this->input->post('related_strap') || $product_options ? '' : 'display:none;'; ?>">
							<div class="form-group" id="ui" style="margin-bottom: 0;">
								<div class="input-group" style="width:100%;">
									<?php
										$related_strap = '';
										foreach ($products as $products) {
											//$related_strap[$products->code] = $products->name;
											$related_strap[$products->code] = $products->code;
										}
										echo form_dropdown('related_strap[]', $related_strap, '', 'id="related_strap" class="form-control" multiple="multiple"');
									?>
								</div>
								<div style="clear:both;"></div>
							</div>
						</div>
					</div>
                    <div class="combo" style="display:none;">

                        <div class="form-group">
                            <?= lang("add_product", "add_item") . ' (' . lang('not_with_variants') . ')'; ?>
                            <?php echo form_input('add_item', '', 'class="form-control ttip" id="add_item" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                        </div>
                        <div class="control-group table-group">
                            <label class="table-label" for="combo"><?= lang("combo_products"); ?></label>

                            <div class="controls table-controls">
                                <table id="prTable"
                                       class="table items table-striped table-bordered table-condensed table-hover">
                                    <thead>
                                    <tr>
                                        <th class="col-md-5 col-sm-5 col-xs-5"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                        <th class="col-md-2 col-sm-2 col-xs-2"><?= lang("quantity"); ?></th>
                                        <th class="col-md-3 col-sm-3 col-xs-3"><?= lang("unit_price"); ?></th>
                                        <th class="col-md-1 col-sm-1 col-xs-1 text-center"><i class="fa fa-trash-o"
                                                                                              style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <div class="digital" style="display:none;">
                        <div class="form-group digital">
                            <?= lang("digital_file", "digital_file") ?>
                            <input id="digital_file" type="file" name="digital_file" data-show-upload="false"
                                   data-show-preview="false" class="form-control file">
                        </div>
                    </div>

                </div>

                <div class="col-md-12">
					<div class="form-group">
						<input type="checkbox" class="checkbox" value="1" name="inactive" id="inactive" <?= $this->input->post('inactive') ? 'checked="checked"' : ''; ?>>
						<label for="inactive" class="padding05">
							<?= lang('inactive'); ?>
						</label>
					</div>
					<?php if($Settings->purchase_serial){?>
					<div class="form-group">
						<input type="checkbox" class="checkbox" value="1" name="is_serial" id="is_serial" <?= $this->input->post('is_serial') ? 'checked="checked"' : ''; ?>>
						<label for="Serial Key" class="padding05">
							<?= lang('Serial Key'); ?>
						</label>
					</div>
					<?php }?>
					
                    <div class="form-group">
                        <input name="cf" type="checkbox" class="checkbox" id="extras_slide"
                               value="" <?= isset($_POST['cf']) ? 'checked="checked"' : '' ?>/><label for="extras"
                                  class="padding05"><?= lang('custom_fields') ?></label>
                    </div>
                    <div class="row" id="extras-con_slide" style="display: none;">

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf1', 'cf1') ?>
								<?php if ($Owner || $Admin) { ?>
								<div class="input-group"><?php } ?>
                                <?php
									$cased[''] = "";
									foreach ($case as $cases) {
										$cased[$cases->id] = $cases->name;
									}
									echo form_dropdown('cf1', $cased, (isset($_POST['cf1']) ? $_POST['cf1'] : ($product ? $product->cf1 : '')), 'class="form-control select" id="cf1" placeholder="' . lang("select") . " " . lang("pcf1") . '" style="width:100%"');
								?>
								<div class="input-group-addon no-print" style="padding: 2px 5px;">
								<a href="<?= site_url('system_settings/add_case'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
								</div>
							</div>
						</div>
						
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf2', 'cf2') ?>
								<?php if ($Owner || $Admin) { ?>
								<div class="input-group"><?php } ?>
                                <?php
									$diam[''] = '';
									foreach ($diameters as $diameter) {
										$diam[$diameter->id] = $diameter->name;
									}
									echo form_dropdown('cf2', $diam, (isset($_POST['cf2']) ? $_POST['cf2'] : ($product ? $product->cf2 : '')), 'class="form-control select" id="cf2" placeholder="' . lang("select") . " " . lang("pcf2") . '" style="width:100%"');
								?>
								<div class="input-group-addon no-print" style="padding: 2px 5px;">
								<a href="<?= site_url('system_settings/add_diameters'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
								</div>
							</div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf3', 'cf3') ?>
								<?php if ($Owner || $Admin) { ?>
								<div class="input-group"><?php } ?>
                                <?php
									$dis[''] = '';
									foreach ($dial as $dials) {
										$dis[$dials->id] = $dials->name;
									}
									echo form_dropdown('cf3', $dis, (isset($_POST['cf3']) ? $_POST['cf3'] : ($product ? $product->cf3 : '')), 'class="form-control select" id="cf3" placeholder="' . lang("select") . " " . lang("pcf3") . '" style="width:100%"');
								?>
								<div class="input-group-addon no-print" style="padding: 2px 5px;">
								<a href="<?= site_url('system_settings/add_dials'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
								</div>
							</div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf4', 'cf4') ?>
								<?php if ($Owner || $Admin) { ?>
								<div class="input-group"><?php } ?>
                                <?php
									$straps[''] = '';
									foreach ($strap as $st) {
										$straps[$st->id] = $st->name;
									}
									echo form_dropdown('cf4', $straps, (isset($_POST['cf4']) ? $_POST['cf4'] : ($product ? $product->cf4 : '')), 'class="form-control select" id="cf4" placeholder="' . lang("select") . " " . lang("pcf4") . '" style="width:100%"');
								?>
								<div class="input-group-addon no-print" style="padding: 2px 5px;">
								<a href="<?= site_url('system_settings/add_straps'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
								</div>
							</div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf5', 'cf5') ?>
								<?php if ($Owner || $Admin) { ?>
								<div class="input-group"><?php } ?>
                                <?php
									$wr[''] = '';
									foreach ($water as $wa) {
										$wr[$wa->id] = $wa->name;
									}
									echo form_dropdown('cf5', $wr, (isset($_POST['cf5']) ? $_POST['cf5'] : ($product ? $product->cf5 : '')), 'class="form-control select" id="cf5" placeholder="' . lang("select") . " " . lang("pcf5") . '" style="width:100%"');
								?>
								<div class="input-group-addon no-print" style="padding: 2px 5px;">
								<a href="<?= site_url('system_settings/add_water_resistance'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
								</div>
							</div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf6', 'cf6') ?>
								<?php if ($Owner || $Admin) { ?>
								<div class="input-group"><?php } ?>
                                <?php
									$wi[''] = '';
									foreach ($winding as $wd) {
										$wi[$wd->id] = $wd->name;
									}
									echo form_dropdown('cf6', $wi, (isset($_POST['cf6']) ? $_POST['cf6'] : ($product ? $product->cf6 : '')), 'class="form-control select" id="cf6" placeholder="' . lang("select") . " " . lang("pcf6") . '" style="width:100%"');
								?>
								<div class="input-group-addon no-print" style="padding: 2px 5px;">
								<a href="<?= site_url('system_settings/add_winding'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
								</div>
							</div>
                        </div>
						<div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf7', 'cf7') ?>
								<?php if ($Owner || $Admin) { ?>
								<div class="input-group"><?php } ?>
                                <?php
									$prd[''] = '';
									foreach ($powerreserve as $pr) {
										$prd[$pr->id] = $pr->name;
									}
									echo form_dropdown('cf7', $prd, (isset($_POST['cf7']) ? $_POST['cf7'] : ($product ? $product->cf7 : '')), 'class="form-control select" id="cf7" placeholder="' . lang("select") . " " . lang("pcf7") . '" style="width:100%"');
								?>
								<div class="input-group-addon no-print" style="padding: 2px 5px;">
								<a href="<?= site_url('system_settings/add_power_reserve'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
								</div>
							</div>
                        </div>
						<div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf8', 'cf8') ?>
								<?php if ($Owner || $Admin) { ?>
								<div class="input-group"><?php } ?>
                                <?php
									$bcd[''] = '';
									foreach ($buckle as $bc) {
										$bcd[$bc->id] = $bc->name;
									}
									echo form_dropdown('cf8', $bcd, (isset($_POST['cf8']) ? $_POST['cf8'] : ($product ? $product->cf8 : '')), 'class="form-control select" id="cf8" placeholder="' . lang("select") . " " . lang("pcf8") . '" style="width:100%"');
								?>
								<div class="input-group-addon no-print" style="padding: 2px 5px;">
								<a href="<?= site_url('system_settings/add_buckle'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
								</div>
							</div>
                        </div>
						<div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf9', 'cf9') ?>
								<?php if ($Owner || $Admin) { ?>
								<div class="input-group"><?php } ?>
                                <?php
									$cpd[''] = '';
									foreach ($complication as $cp) {
										$cpd[$cp->id] = $cp->name;
									}
									echo form_dropdown('cf9', $cpd, (isset($_POST['cf9']) ? $_POST['cf9'] : ($product ? $product->cf9 : '')), 'class="form-control select" id="cf9" placeholder="' . lang("select") . " " . lang("pcf9") . '" style="width:100%"');
								?>
								<div class="input-group-addon no-print" style="padding: 2px 5px;">
								<a href="<?= site_url('system_settings/add_complication'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
								</div>
                            </div>
                        </div>

                    </div>

                    <div class="form-group all">
                        <?= lang("product_details", "product_details") ?>
                        <?= form_textarea('product_details', (isset($_POST['product_details']) ? $_POST['product_details'] : ($product ? $product->product_details : '')), 'class="form-control" id="details"'); ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_details_for_invoice", "details") ?>
                        <?= form_textarea('details', (isset($_POST['details']) ? $_POST['details'] : ($product ? $product->details : '')), 'class="form-control" id="details"'); ?>
                    </div>

                    <div class="form-group">
                        <?php echo form_submit('add_product', $this->lang->line("add_product"), 'class="btn btn-primary"'); ?>
                    </div>

                </div>
                <?= form_close(); ?>

            </div>

        </div>
            </div>
            <!--<div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div> -->
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {
		<?=isset($_POST['cf']) ? '$("#extras").iCheck("check");': '' ?>
		$('#extras').on('ifChecked', function () {
            $('#extras-con').slideDown();
        });
        $('#extras').on('ifUnchecked', function () {
            $('#extras-con').slideUp();
        });
		
		$('#purchase_type').change(function(){
			if(($(this).val())!=1 || ($(this).val())==3){
			$('#potax2').attr("disabled", true);
			$('.one_use').show();
			$('.two_use').hide();

			}
			if(($(this).val())==1 || ($(this).val())==3){
			$('#potax2').attr("disabled", false);
			$('.one_use').hide();
			$('.two_use').show();
			}
			});
		
        //var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
        //var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
        var items = {};
        <?php
        if(isset($combo_items)) {
            foreach($combo_items as $item) {
            //echo 'ietms['.$item->id.'] = '.$item.';';
                if($item->code) {
                    echo 'add_product_item('.  json_encode($item).');';
                }
            }
        }
        ?>
        <?=isset($_POST['cf']) ? '$("#extras_slide").iCheck("check");': '' ?>
        $('#extras_slide').on('ifChecked', function () {
            $('#extras-con_slide').slideDown();
        });
        $('#extras_slide').on('ifUnchecked', function () {
            $('#extras-con_slide').slideUp();
        });

        $('.attributes').on('ifChecked', function (event) {
            $('#options_' + $(this).attr('id')).slideDown();
        });
        $('.attributes').on('ifUnchecked', function (event) {
            $('#options_' + $(this).attr('id')).slideUp();
        });
        //$('#cost').removeAttr('required');
        $('#type').change(function () {
            var t = $(this).val();
            if (t !== 'standard') {
                $('.standard').slideUp();
                $('#cost').attr('required', 'required');
                $('#track_quantity').iCheck('uncheck');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
            } else {
                $('.standard').slideDown();
                $('#track_quantity').iCheck('check');
                $('#cost').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'cost');
            }
            if (t !== 'digital') {
                $('.digital').slideUp();
                $('#digital_file').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'digital_file');
            } else {
                $('.digital').slideDown();
                $('#digital_file').attr('required', 'required');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'digital_file');
            }
            if (t !== 'combo') {
                $('.combo').slideUp();
            } else {
                $('.combo').slideDown();
            }
        });

        var t = $('#type').val();
        if (t !== 'standard') {
            $('.standard').slideUp();
            $('#cost').attr('required', 'required');
            $('#track_quantity').iCheck('uncheck');
            $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
        } else {
            $('.standard').slideDown();
            $('#track_quantity').iCheck('check');
            $('#cost').removeAttr('required');
            $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'cost');
        }
        if (t !== 'digital') {
            $('.digital').slideUp();
            $('#digital_file').removeAttr('required');
            $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'digital_file');
        } else {
            $('.digital').slideDown();
            $('#digital_file').attr('required', 'required');
            $('form[data-toggle="validator"]').bootstrapValidator('addField', 'digital_file');
        }
        if (t !== 'combo') {
            $('.combo').slideUp();
        } else {
            $('.combo').slideDown();
        }

		/*
        $("#add_item").autocomplete({
            source: '<?= site_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 200,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        $('#add_item').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $(this).autocomplete("search");
            }
        });
		*/
        <?php
        if($this->input->post('type') == 'combo') {
            $c = sizeof($_POST['combo_item_code']);
            for ($r = 0; $r <= $c; $r++) {
                if(isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                    $items[] = array('id' => $_POST['combo_item_id'][$r], 'name' => $_POST['combo_item_name'][$r], 'code' => $_POST['combo_item_code'][$r], 'qty' => $_POST['combo_item_quantity'][$r], 'price' => $_POST['combo_item_price'][$r]);
                }
            }
            echo '
            var ci = '.json_encode($items).';
            $.each(ci, function() { add_product_item(this); });
            ';
        }
        ?>
        function add_product_item(item) {
            if (item == null) {
                return false;
            }
            item_id = item.id;
            if (items[item_id]) {
                items[item_id].qty = (parseFloat(items[item_id].qty) + 1).toFixed(2);
            } else {
                items[item_id] = item;
            }

            $("#prTable tbody").empty();
            $.each(items, function () {
                var row_no = this.id;
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '"></tr>');
                tr_html = '<td><input name="combo_item_id[]" type="hidden" value="' + this.id + '"><input name="combo_item_name[]" type="hidden" value="' + this.name + '"><input name="combo_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.name + ' (' + this.code + ')</span></td>';
				tr_html += '<td><input class="form-control text-center" name="combo_item_quantity_unit[]" type="text" value="' + formatPurDecimal(this.qty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_unit_' + row_no + '" onClick="this.select();"></td>';
                //tr_html += '<td><input class="form-control text-center" name="combo_item_quantity[]" type="text" value="' + formatPurDecimal(this.qty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td><input class="form-control text-center" name="combo_item_price[]" type="text" value="' + formatPurDecimal(this.price) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_price_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#prTable");
            });
            $('.item_' + item_id).addClass('warning');
            //audio_success.play();
            return true;

        }

        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            delete items[id];
            $(this).closest('#row_' + id).remove();
        });
        var su = 2;
        $('#addSupplier').click(function () {
            if (su <= 5) {
                $('#supplier_1').select2('destroy');
                var html = '<div style="clear:both;height:15px;"></div><div class="row"><div class="col-md-8 col-sm-8 col-xs-8"><input type="hidden" name="supplier_' + su + '", class="form-control" id="supplier_' + su + '" placeholder="<?= lang("select") . ' ' . lang("supplier") ?>" style="width:100%;display: block !important;" /></div><div class="col-md-4 col-sm-4 col-xs-4"><input type="text" name="supplier_' + su + '_price" class="form-control tip" id="supplier_' + su + '_price" placeholder="<?= lang('supplier_price') ?>" /></div></div>';
                $('#ex-suppliers').append(html);
                var sup = $('#supplier_' + su);
                suppliers(sup);
                su++;
            } else {
                bootbox.alert('<?= lang('max_reached') ?>');
                return false;
            }
        });

        var _URL = window.URL || window.webkitURL;
        $("input#images").on('change.bs.fileinput', function () {
            var ele = document.getElementById($(this).attr('id'));
            var result = ele.files;
            $('#img-details').empty();
            for (var x = 0; x < result.length; x++) {
                var fle = result[x];
                for (var i = 0; i <= result.length; i++) {
                    var img = new Image();
                    img.onload = (function (value) {
                        return function () {
                            ctx[value].drawImage(result[value], 0, 0);
                        }
                    })(i);
                    img.src = 'images/' + result[i];
                }
            }
        });
        var variants = <?=json_encode(isset($vars)?$vars:'');?>;
        $(".select-tags").select2({
            tags: variants,
            tokenSeparators: [","],
            multiple: true
        });
        $(document).on('ifChecked', '#attributes', function (e) {
            $('#attr-con').slideDown();
        });
        $(document).on('ifUnchecked', '#attributes', function (e) {
            $(".select-tags").select2("val", "");
            $('.attr-remove-all').trigger('click');
            $('#attr-con').slideUp();
        });
        $('#addAttributes').click(function (e) {
            e.preventDefault();
            var attrs_val = $('#attributesInput').val(), attrs;
            attrs = attrs_val.split(',');
            console.log(attrs);
            for (var i in attrs) {
                if (attrs[i] !== '') {
                   // $('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value=""><span></span></td><td class="quantity_unit text-center"><input type="hidden" name="attr_quantity_unit[]" value=""><span></span></td><td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value=""><span></span></td><td class="cost text-right"><input type="hidden" name="attr_cost[]" value="0"><span>0</span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
				   $('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="quantity_unit text-center"><input type="hidden" name="attr_quantity_unit[]" value=""><span></span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
                }
            }
        });
		//$('#attributesInput').on('select2-blur', function(){
		//    $('#addAttributes').click();
		//});
        $(document).on('click', '.delAttr', function () {
            $(this).closest("tr").remove();
        });
        $(document).on('click', '.attr-remove-all', function () {
            $('#attrTable tbody').empty();
            $('#attrTable').hide();
        });
        var row, warehouses = <?= json_encode($warehouses); ?>;
        $(document).on('click', '.attr td:not(:last-child)', function () {
            row = $(this).closest("tr");
            $('#aModalLabel').text(row.children().eq(0).find('span').text());
            //$('#awarehouse').select2("val", (row.children().eq(1).find('input').val()));
			$('#aquantity_unit').val(row.children().eq(1).find('input').val());
            //$('#aquantity').val(row.children().eq(3).find('input').val());
           // $('#acost').val(row.children().eq(4).find('span').text());
            $('#aprice').val(row.children().eq(2).find('span').text());
            $('#aModal').appendTo('body').modal('show');
        });

        $(document).on('click', '#updateAttr', function () {
            var wh = $('#awarehouse').val(), wh_name;
            $.each(warehouses, function () {
                if (this.id == wh) {
                    wh_name = this.name;
                }
            });
            //row.children().eq(1).html('<input type="hidden" name="attr_warehouse[]" value="' + wh + '"><input type="hidden" name="attr_wh_name[]" value="' + wh_name + '"><span>' + wh_name + '</span>');
			row.children().eq(1).html('<input type="hidden" name="attr_quantity_unit[]" value="' + $('#aquantity_unit').val() + '"><span>' + decimalFormat($('#aquantity_unit').val()) + '</span>');
            //row.children().eq(3).html('<input type="hidden" name="attr_quantity[]" value="' + $('#aquantity').val() + '"><span>' + decimalFormat($('#aquantity').val()) + '</span>');
            //row.children().eq(4).html('<input type="hidden" name="attr_cost[]" value="' + $('#acost').val() + '"><span>' + currencyFormat($('#acost').val()) + '</span>');
            row.children().eq(2).html('<input type="hidden" name="attr_price[]" value="' + $('#aprice').val() + '"><span>' + currencyFormat($('#aprice').val()) + '</span>');
            $('#aModal').modal('hide');
        });
		
	});

    <?php if (isset($product)) { ?>
    $(document).ready(function () {
		
        var t = "<?=$product->type?>";
        if (t !== 'standard') {
            $('.standard').slideUp();
            $('#cost').attr('required', 'required');
            $('#track_quantity').iCheck('uncheck');
            $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
        } else {
            $('.standard').slideDown();
            $('#track_quantity').iCheck('check');
            $('#cost').removeAttr('required');
            $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'cost');
        }
        if (t !== 'digital') {
            $('.digital').slideUp();
            $('#digital_file').removeAttr('required');
            $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'digital_file');
        } else {
            $('.digital').slideDown();
            $('#digital_file').attr('required', 'required');
            $('form[data-toggle="validator"]').bootstrapValidator('addField', 'digital_file');
        }
        if (t !== 'combo') {
            $('.combo').slideUp();
            //$('#add_item').removeAttr('required');
            //$('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');
        } else {
            $('.combo').slideDown();
            //$('#add_item').attr('required', 'required');
            //$('form[data-toggle="validator"]').bootstrapValidator('addField', 'add_item');
        }
        $("#code").parent('.form-group').addClass("has-error");
        $("#code").focus();
        $("#product_image").parent('.form-group').addClass("text-warning");
        $("#images").parent('.form-group').addClass("text-warning");
        $.ajax({
            type: "get", async: false,
            url: "<?= site_url('products/getSubCategories') ?>/" + <?= $product->category_id ?>,
            dataType: "json",
            success: function (scdata) {
                if (scdata != null) {
                    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                        placeholder: "<?= lang('select_category_to_load') ?>",
                        data: scdata
                    });
                }
            }
        });
        <?php if ($product->supplier1) { ?>
        select_supplier('supplier1', "<?= $product->supplier1; ?>");
        $('#supplier_price').val("<?= $product->supplier1price == 0 ? '' : $this->erp->formatPurDecimal($product->supplier1price); ?>");
        <?php } ?>
        <?php if ($product->supplier2) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_2', "<?= $product->supplier2; ?>");
        $('#supplier_2_price').val("<?= $product->supplier2price == 0 ? '' : $this->erp->formatPurDecimal($product->supplier2price); ?>");
        <?php } ?>
        <?php if ($product->supplier3) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_3', "<?= $product->supplier3; ?>");
        $('#supplier_3_price').val("<?= $product->supplier3price == 0 ? '' : $this->erp->formatPurDecimal($product->supplier3price); ?>");
        <?php } ?>
        <?php if ($product->supplier4) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_4', "<?= $product->supplier4; ?>");
        $('#supplier_4_price').val("<?= $product->supplier4price == 0 ? '' : $this->erp->formatPurDecimal($product->supplier4price); ?>");
        <?php } ?>
        <?php if ($product->supplier5) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_5', "<?= $product->supplier5; ?>");
        $('#supplier_5_price').val("<?= $product->supplier5price == 0 ? '' : $this->erp->formatPurDecimal($product->supplier5price); ?>");
        <?php } ?>
        function select_supplier(id, v) {
            $('#' + id).val(v).select2({
                minimumInputLength: 1,
                data: [],
                initSelection: function (element, callback) {
                    $.ajax({
                        type: "get", async: false,
                        url: "<?= site_url('suppliers/getSupplier') ?>/" + $(element).val(),
                        dataType: "json",
                        success: function (data) {
                            callback(data[0]);
                        }
                    });
                },
                ajax: {
                    url: site.base_url + "suppliers/suggestions",
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
            });//.select2("val", "<?= $product->supplier1; ?>");
        }

        var whs = $('.wh');
        $.each(whs, function () {
            $(this).val($('#r' + $(this).attr('id')).text());
        });
		
		
    });
	
    <?php } ?>
</script>

<div class="modal" id="aModal" tabindex="-1" role="dialog" aria-labelledby="aModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="aModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                   <!-- <div class="form-group">
                        <label for="awarehouse" class="col-sm-4 control-label"><?= lang('warehouse') ?></label>

                        <div class="col-sm-8">
                            <?php
                            $wh[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('warehouse', $wh, '', 'id="awarehouse" class="form-control"');
                            ?>
                        </div>
                    </div> -->
					
					<div class="form-group">
							<label for="aquantity_unit" class="col-sm-4 control-label"><?= lang('quantity_unit') ?></label>

							<div class="col-sm-8">
								<input type="text" class="form-control" id="aquantity_unit">
							</div>
					</div>
					<!--
                    <div class="form-group">
					
                        <label for="aquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="aquantity">
                        </div>
                    </div>
					
                    <div class="form-group">
                        <label for="acost" class="col-sm-4 control-label"><?= lang('cost') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="acost">
                        </div>
                    </div>
					-->
                    <div class="form-group">
                        <label for="aprice" class="col-sm-4 control-label"><?= lang('price') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="aprice">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="updateAttr"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>