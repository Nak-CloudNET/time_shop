<?php
	if (!empty($variants)) {
		foreach ($variants as $variant) {
			$vars[] = addslashes($variant->name);
		}
	} else {
		$vars = array();
	}
?>

<script type="text/javascript">
	$(window).load(function(){
		if (pr_customer_fields = localStorage.getItem('pr_customer_fields')) {
			$('#extrasc').iCheck('check').trigger('ifChecked');
        }
	});
    $(document).ready(function () {
        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });
		$('#brands').change(function () {
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#category").select2("destroy").empty();
							var newOptions = '';
							var option = '<option></option>';
							$("#category").select2('destroy').append(option).select2();
							$.each(scdata, function(i, item) {
								newOptions = '<option value="'+ item.id +'">'+ item.text +'</option>';
								$("#category").select2('destroy').append(newOptions ).select2();
							});
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            }else {
				$("#category").select2("destroy").empty().attr("placeholder", "<?= lang('select_brand_to_load') ?>").select2({
					placeholder: "<?= lang('select_brand_to_load') ?>",
					data: [{id: '', text: '<?= lang('select_brand_to_load') ?>'}]
				});
			}
            $('#modal-loading').hide();
        });
        $('#category').change(function () {
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: scdata
                            });
                        }else{
							$("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: 'not found'
                            });
						}
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }
            $('#modal-loading').hide();
        });
        $('#code').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        });
		$('#code').bind('change', function (e) {
            var code = $(this).val();
			$.ajax({
				type: 'GET',
				url: '<?= site_url('products/check_product_available'); ?>',
				data: {term:code},
				cache: false,
				success: function (data) {
					if(data == 1){
						alert('Product code already exists...');						
						$('#code').bootstrapValidator({
							feedbackIcons: {
								valid: 'fa fa-check',
								invalid: 'fa fa-times',
								validating: 'fa fa-refresh'
							}, excluded: [':disabled']
						});
						//document.getElementById("code").value = "";
					}
				},
				error: function(){
					alert('error ajax');
				}
			});
        });
		$("#code").on('keyup', function(){
			var code = $(this).val();
			$.ajax({
				type: 'GET',
				url: '<?= site_url('products/check_product_available'); ?>',
				data: {term:code},
				cache: false,
				success: function (data) {
					var parent = $(this).closest('.form-control');
					if(data == 0){
						parent.removeClass('error');
					}else{
						parent.addClass('error');
					}
				},
				error: function(){
					alert('error ajax');
				}
			});
			
		});
    });
</script>

<script type="text/javascript">

    $(document).ready(function () {
        if (localStorage.getItem('remove_slls')) {
			
            if (localStorage.getItem('pr_type')) {
                localStorage.removeItem('pr_type');
            }
            if (localStorage.getItem('pr_name')) {
                localStorage.removeItem('pr_name');
            }
            if (localStorage.getItem('pr_code')) {
                localStorage.removeItem('pr_code');
            }
            if (localStorage.getItem('pr_reference')) {
                localStorage.removeItem('pr_reference');
            }
            if (localStorage.getItem('pr_brand')) {
                localStorage.removeItem('pr_brand');
            }
            if (localStorage.getItem('pr_category')) {
                localStorage.removeItem('pr_category');
            }
            if (localStorage.getItem('pr_line')) {
                localStorage.removeItem('pr_line');
            }
            if (localStorage.getItem('pr_unit')) {
                localStorage.removeItem('pr_unit');
            }
            if (localStorage.getItem('pr_cost')) {
                localStorage.removeItem('pr_cost');
            }
            if (localStorage.getItem('pr_price')) {
                localStorage.removeItem('pr_price');
            }
            if (localStorage.getItem('pr_tax')) {
                localStorage.removeItem('pr_tax');
            }
            if (localStorage.getItem('pr_tax_method')) {
                localStorage.removeItem('pr_tax_method');
            }
			if (localStorage.getItem('pr_alert_quantity')) {
                localStorage.removeItem('pr_alert_quantity');
            }
            if (localStorage.getItem('pr_supplier')) {
                localStorage.removeItem('pr_supplier');
            }
            if (localStorage.getItem('pr_serial')) {
                localStorage.removeItem('pr_serial');
            }
            if (localStorage.getItem('pr_customer_fields')) {
                localStorage.removeItem('pr_customer_fields');
            }
            if (localStorage.getItem('pr_c_case_material')) {
                localStorage.removeItem('pr_c_case_material');
            }
            if (localStorage.getItem('pr_c_diameter')) {
                localStorage.removeItem('pr_c_diameter');
            }
            if (localStorage.getItem('pr_c_dial')) {
                localStorage.removeItem('pr_c_dial');
            }
            if (localStorage.getItem('pr_c_strap')) {
                localStorage.removeItem('pr_c_strap');
            }
            if (localStorage.getItem('pr_c_water_resistance')) {
                localStorage.removeItem('pr_c_water_resistance');
            }
            if (localStorage.getItem('pr_c_winding')) {
                localStorage.removeItem('pr_c_winding');
            }
            if (localStorage.getItem('pr_c_power_reserve')) {
                localStorage.removeItem('pr_c_power_reserve');
            }
            if (localStorage.getItem('pr_c_buckle')) {
                localStorage.removeItem('pr_c_buckle');
            }
            if (localStorage.getItem('pr_c_complication')) {
                localStorage.removeItem('pr_c_complication');
            }
			if (localStorage.getItem('pr_details')) {
                localStorage.removeItem('pr_details');
            }
			if (localStorage.getItem('pr_details_invoice')) {
                localStorage.removeItem('pr_details_invoice');
            }
            localStorage.removeItem('remove_slls');
        }
		// Product Type
        $(document).on('change', '#type', function (e) {
            localStorage.setItem('pr_type', $(this).val());
        });
        if (pr_type = localStorage.getItem('pr_type')) {
            $('#type').val(pr_type).trigger('change');
        }
		
		// Product Name
		$(document).on('change', '#name', function (e) {
            localStorage.setItem('pr_name', $(this).val());
        });
        if (pr_name = localStorage.getItem('pr_name')) {
            $('#name').val(pr_name);
        }
		
		// Product Code or Reference
		$(document).on('change', '#code', function (e) {
            localStorage.setItem('pr_code', $(this).val());
        });
        if (pr_code = localStorage.getItem('pr_code')) {
            $('#code').val(pr_code);
        }
		
		// Product Brand
		$(document).on('change', '#brand', function (e) {
            localStorage.setItem('pr_brand', $(this).val());
        });
        if (pr_brand = localStorage.getItem('pr_brand')) {
            $('#brand').val(pr_brand).trigger('change');;
        }
		
		// Product Category
		$(document).on('change', '#category', function (e) {
            localStorage.setItem('pr_category', $(this).val());
        });
        if (pr_category = localStorage.getItem('pr_category')) {
            $('#category').val(pr_category).trigger('change');;
        }
		
		// Product Line
		$(document).on('change', '#subcategory', function (e) {
            localStorage.setItem('pr_line', $(this).val());
        });
        if (pr_line = localStorage.getItem('pr_line')) {
            $('#subcategory').val(pr_line).trigger('change');;
        }
		
		// Product Unit
		$(document).on('change', '#unit', function (e) {
            localStorage.setItem('pr_unit', $(this).val());
        });
        if (pr_unit = localStorage.getItem('pr_unit')) {
            $('#unit').val(pr_unit);
        }
		
		// Product Cost
		$(document).on('change', '#cost', function (e) {
            localStorage.setItem('pr_cost', $(this).val());
        });
        if (pr_cost = localStorage.getItem('pr_cost')) {
            $('#cost').val(pr_cost);
        }
		
		// Product Price
		$(document).on('change', '#price', function (e) {
            localStorage.setItem('pr_price', $(this).val());
        });
        if (pr_price = localStorage.getItem('pr_price')) {
            $('#price').val(pr_price);
        }
		
		// Product Tax
		$(document).on('change', '#tax_rate', function (e) {
            localStorage.setItem('pr_tax', $(this).val());
        });
        if (pr_tax = localStorage.getItem('pr_tax')) {
            $('#tax_rate').val(pr_tax);
        }
		
		// Product Tax Method
		$(document).on('change', '#tax_method', function (e) {
            localStorage.setItem('pr_tax_method', $(this).val());
        });
        if (pr_tax_method = localStorage.getItem('pr_tax_method')) {
            $('#tax_method').val(pr_tax_method);
        }
		
		// Product Alert Quantity
		$(document).on('change', '#alert_quantity', function (e) {
            localStorage.setItem('pr_alert_quantity', $(this).val());
        });
        if (pr_alert_quantity = localStorage.getItem('pr_alert_quantity')) {
            $('#alert_quantity').val(pr_alert_quantity);
        }
		
		// Product Supplier
		$(document).on('change', '#supplier', function (e) {
            localStorage.setItem('pr_supplier', $(this).val());
        });
        if (pr_supplier = localStorage.getItem('pr_supplier')) {
            $('#supplier').val(pr_supplier);
        }
		
		// Product Serial Key
		$(document).on('ifChecked', '#is_serial', function (e) {
            localStorage.setItem('pr_serial', $(this).val());
        });
		$(document).on('ifUnchecked', '#is_serial', function () {
			localStorage.removeItem('pr_serial');
		});
        if (pr_serial = localStorage.getItem('pr_serial')) {
			$('#is_serial').iCheck('check');
            // $('#is_serial').val(pr_serial);
        }
		
		// Product Customer Fileds
		$(document).on('ifChecked', '#extrasc', function (e) {
            localStorage.setItem('pr_customer_fields', 1);
        });
		$(document).on('ifUnchecked', '#extrasc', function () {
			localStorage.removeItem('pr_customer_fields');
		});
		
        if (pr_customer_fields = localStorage.getItem('pr_customer_fields')) {
			$('#extrasc').iCheck('check').trigger('ifChecked');
			// $('#extras').val(pr_customer_fields);
        }
		
		// Customer Filed : Case Material
		$(document).on('change', '#cf1', function (e) {
            localStorage.setItem('pr_c_case_material', $(this).val());
        });
        if (pr_c_case_material = localStorage.getItem('pr_c_case_material')) {
            $('#cf1').val(pr_c_case_material);
        }
		
		// Customer Filed : Diameter
		$(document).on('change', '#cf2', function (e) {
            localStorage.setItem('pr_c_diameter', $(this).val());
        });
        if (pr_c_diameter = localStorage.getItem('pr_c_diameter')) {
            $('#cf2').val(pr_c_diameter);
        }
		
		// Customer Filed : Dial
		$(document).on('change', '#cf3', function (e) {
            localStorage.setItem('pr_c_dial', $(this).val());
        });
        if (pr_c_dial = localStorage.getItem('pr_c_dial')) {
            $('#cf3').val(pr_c_dial);
        }
		
		// Customer Filed : Strap
		$(document).on('change', '#cf4', function (e) {
            localStorage.setItem('pr_c_strap', $(this).val());
        });
        if (pr_c_strap = localStorage.getItem('pr_c_strap')) {
            $('#cf4').val(pr_c_strap);
        }
		
		// Customer Filed : Water Resistance
		$(document).on('change', '#cf5', function (e) {
            localStorage.setItem('pr_c_water_resistance', $(this).val());
        });
        if (pr_c_water_resistance = localStorage.getItem('pr_c_water_resistance')) {
            $('#cf5').val(pr_c_water_resistance);
        }
		
		// Customer Filed : Winding
		$(document).on('change', '#cf6', function (e) {
            localStorage.setItem('pr_c_winding', $(this).val());
        });
        if (pr_c_winding = localStorage.getItem('pr_c_winding')) {
            $('#cf6').val(pr_c_winding);
        }
		
		// Customer Filed : Power Reserve
		$(document).on('change', '#cf7', function (e) {
            localStorage.setItem('pr_c_power_reserve', $(this).val());
        });
        if (pr_c_power_reserve = localStorage.getItem('pr_c_power_reserve')) {
            $('#cf7').val(pr_c_power_reserve);
        }
		
		// Customer Filed : Buckle
		$(document).on('change', '#cf8', function (e) {
            localStorage.setItem('pr_c_buckle', $(this).val());
        });
        if (pr_c_buckle = localStorage.getItem('pr_c_buckle')) {
            $('#cf8').val(pr_c_buckle);
        }
		
		// Customer Filed : Complication
		$(document).on('change', '#cf9', function (e) {
            localStorage.setItem('pr_c_complication', $(this).val());
        });
        if (pr_c_complication = localStorage.getItem('pr_c_complication')) {
            $('#cf9').val(pr_c_complication);
        }
		
		// Product Details
		$(document).on('change', 'input[name="product_details"]', function (e) {
			alert('he');
            localStorage.setItem('pr_details', $(this).val());
        });
        if (pr_details = localStorage.getItem('pr_details')) {
            $('input[name="product_details"]').val(pr_details);
        }
		
		// Product Details for Invoice
		$(document).on('change', 'input[name="details"]', function (e) {
            localStorage.setItem('pr_details_invoice', $(this).val());
        });
        if (pr_details_invoice = localStorage.getItem('pr_details_invoice')) {
            $('input[name="details"]').val(pr_details_invoice);
        }

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_product'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>

                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' =>'product-form');
                echo form_open_multipart("products/add", $attrib)
                ?>
				
                <div class="col-md-5">
                    <div class="form-group">
                        <?= lang("product_type", "type") ?>
                        <?php
						if($GP['only_service']){
							$opts = array('service' => lang('service'));
							
						}else{
							$opts = array('standard' => lang('standard'), 'combo' => lang('combo'), 'service' => lang('service'));
						}
                      
                        echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control" id="type" required="required"');
                        ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_name", "name") ?>
                        <?= form_input('name', (isset($_POST['name']) ? $_POST['name'] : ($product ? $product->name : '')), 'class="form-control" id="name" required="required"'); ?>
                    </div>
                    <div class="form-group all">
                        <?= lang("product_code", "code") ?>
                        <?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : ($product ? $product->code : '')), 'class="form-control" id="code" '.($product ? "readonly": "").' required="required"') ?>
						<input type="hidden" value="<?= $product->code; ?>" name="q_code">
                        <span class="help-block"><?= lang('you_scan_your_barcode_too') ?></span>
                    </div>
                    <div class="form-group all">
                        <?= lang("barcode_symbology", "barcode_symbology") ?>
                        <?php
                        $bs = array('code25' => 'Code25', 'code39' => 'Code39', 'code128' => 'Code128', 'ean8' => 'EAN8', 'ean13' => 'EAN13', 'upca ' => 'UPC-A', 'upce' => 'UPC-E');
                        echo form_dropdown('barcode_symbology', $bs, (isset($_POST['barcode_symbology']) ? $_POST['barcode_symbology'] : ($product ? $product->barcode_symbology : 'code128')), 'class="form-control select" id="barcode_symbology" required="required" style="width:100%;"');
                        ?>

                    </div>
					
					<div class="form-group">
						<?= lang("brand", "brand") ?>
						<?php if ($Owner || $Admin) { ?><div class="input-group"><?php } ?>
								<?php
								if ($Owner || $Admin ) { 
								$br[''] = "";
								foreach ($brands as $brand) {
									$br[$brand->id] = $brand->name;
								}
								echo form_dropdown('brand', $br, (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand_id : '')), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
								?>
							
								<div class="input-group-addon no-print" style="padding: 2px 5px;"><a href="<?= site_url('system_settings/add_brand'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
							</div>
							<?php }else{
								$br[''] = "";
							foreach ($brands as $brand) {
								$br[$brand->id] = $brand->name;
							}
							echo form_dropdown('brand', $br, (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand_id : '')), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
							?>
						<?php
						} 
						?>
                    </div>
					
                    <div class="form-group">
						<?= lang("category", "category") ?>
						<?php if ($Owner || $Admin) { ?><!--<div class="input-group">--><?php } ?>
								<?php
								if ($Owner || $Admin ) { 
								$cat[''] = "";
								foreach ($categories as $category) {
									$cat[$category->id] = $category->name;
								}
								//$ca = array('' => '', '1' => 'Watch', '2' => 'Jewelry', '3' => 'Strap', '4' => 'Service', '5' => 'Combination');
								echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : '')), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
								?>
								<!--
								<div class="input-group-addon no-print" style="padding: 2px 5px;"><a
										href="<?= site_url('system_settings/add_category'); ?>" id="add-supplier"
										class="external" data-toggle="modal" data-target="#myModal"><i
											class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
											
							</div>-->
							<?php }else{
								$cat[''] = "";
							foreach ($categories as $category) {
								$cat[$category->id] = $category->name;
							}
							//$ca = array('1' => 'Watch', '2' => 'Jewelry', '3' => 'Strap', '4' => 'Service', '5' => 'Combination');
							echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : '')), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
							?>
						<?php
						} 
						?>
                    </div>
                    
					<div class="form-group all">
                        <?= lang("subcategory", "subcategory") ?>
						<?php if ($Owner || $Admin) { ?><div class="input-group"><?php } ?>
							<?php
							if ($Owner || $Admin ) { 
								echo form_input('subcategory', ($product ? $product->subcategory_id : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
							?>
						
							<div class="input-group-addon no-print" style="padding: 2px 5px;"><a
									href="<?= site_url('system_settings/add_subcategory?type=product_add'); ?>" id="add-supplier"
									class="external" data-toggle="modal" data-target="#myModal"><i
										class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
						</div>
						<?php }else{
							echo form_input('subcategory', ($product ? $product->subcategory_id : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
						} ?>
                    </div>
                    
					<div class="form-group all">
                        <label class="control-label" for="unit"><?= lang("product_unit") ?></label>
                        <?php
                        $ut["2"] = $uts->name;
						foreach($unit as $uts){
							$ut[$uts->id] = $uts->name;
						}
                        echo form_dropdown('unit', $ut, (isset($_POST['unit']) ? $_POST['unit'] : ($product ? $product->unit : '')), 'class="form-control select" id="unit" required="required" placeholder="'.lang('select_units').'" style="width:100%;"');
                        ?>
                    </div>
                    
					<div class="form-group standard cost">
                        <?= lang("product_cost", "cost") ?>
                        <?= form_input('cost', (isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->erp->formatDecimal($product->cost) : '')), 'class="form-control tip" id="cost"') ?>
                    </div>
                    
					<div class="form-group all">
                        <?= lang("product_price", "price") ?>
                        <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->erp->formatDecimal($product->price) : '')), 'class="form-control tip" id="price" required="required"') ?>
                    </div>
					
					<div class="form-group">
						<label class="control-label" for="currency"><?= lang("default_currency"); ?></label>
						<div class="controls"> 
							<?php
								foreach ($currencies as $currency) {
									$cu[$currency->code] = $currency->name;
								}
								echo form_dropdown('currency', $cu, $Settings->default_currency, 'class="form-control tip" id="currency" style="width:100%;"');
							?>
						</div>
					</div>
					
					<div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="promotion" id="promotion" <?= $this->input->post('promotion') ? 'checked="checked"' : ''; ?>>
                        <label for="promotion" class="padding05">
                            <?= lang('promotion'); ?>
                        </label>
                    </div>

                    <div id="promo" style="display:none;">
                        <div class="well well-sm">
                            <div class="form-group">
                                <?= lang('promo_price', 'promo_price'); ?>
                                <?= form_input('promo_price', set_value('promo_price'), 'class="form-control tip" id="promo_price"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?= form_input('start_date', set_value('start_date'), 'class="form-control tip date" id="start_date"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?= form_input('end_date', set_value('end_date'), 'class="form-control tip date" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    
					<?php if ($Settings->tax1) { ?>
                        <div class="form-group all">
                            <?= lang("product_tax", "tax_rate") ?>
                            <?php
                            $tr[""] = "";
                            foreach ($tax_rates as $tax) {
                                $tr[$tax->id] = $tax->name;
                            }
                            //echo form_input('tax_rate', (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : set_value('tax_rate')), 'class="form-control" ');
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
                    
					<div class="form-group standard">
                        <?= lang("supplier", "supplier") ?>
                        <button type="button" class="btn btn-primary btn-xs" id="addSupplier"><i class="fa fa-plus"></i>
                        </button>
                        <div class="row" id="supplier-con">
                            <div class="col-md-8 col-sm-8 col-xs-8">
                                <?php
                                echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control ' . ($product ? '' : 'suppliers') . '" id="' . ($product && ! empty($product->supplier1) ? 'supplier1' : 'supplier') . '" placeholder="' . lang("select") . ' ' . lang("supplier") . '" style="width:100%;"')
                                ?></div>
                            <div
                                class="col-md-4 col-sm-4 col-xs-4"><?= form_input('supplier_price', (isset($_POST['supplier_price']) ? $_POST['supplier_price'] : ""), 'class="form-control tip" id="supplier_price" placeholder="' . lang('supplier_price') . '"') ?></div>
                        </div>
                        <div id="ex-suppliers"></div>
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
					<!-- Add New -->
					
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
                                    echo '<div class="row"><div class="col-md-12"><div class="well">';
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
                        <div class="form-group">
                            <input type="checkbox" class="checkbox" name="attributes"
                                   id="attributes" <?= $this->input->post('attributes') || $product_options ? 'checked="checked"' : ''; ?>><label
                                for="attributes"
                                class="padding05"><?= lang('product_has_attributes'); ?></label> <?= lang('eg_sizes_colors'); ?>
                        </div>
                        <div class="well well-sm" id="attr-con" style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?>">
                            <div class="form-group" id="ui" style="margin-bottom: 0;">
                                <div class="input-group">
                                    <?php echo form_input('attributesInput', '', 'class="form-control select-tags" id="attributesInput" placeholder="' . $this->lang->line("enter_attributes") . '"'); ?>
                                    <div class="input-group-addon" style="padding: 2px 5px;"><a href="#" id="addAttributes"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
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
                                            echo '<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' . $option->name . '"><span>' . $option->name . '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $option->warehouse_id . '"><input type="hidden" name="attr_wh_name[]" value="' . $option->wh_name . '"><span>' . $option->wh_name . '</span></td><td class="quantity_unit text-center"><input type="hidden" name="attr_quantity_unit[]" value="' . $this->erp->formatQuantity($option->wh_qty) . '"><span>' . $this->erp->formatQuantity($option->wh_qty) . '</span></td><td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="' . $this->erp->formatQuantity($option->wh_qty) . '"><span>' . $this->erp->formatQuantity($option->wh_qty) . '</span></td><td class="cost text-right"><input type="hidden" name="attr_cost[]" value="' . $this->erp->formatMoney($option->cost) . '"><span>' . $this->erp->formatMoney($option->cost) . '</span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $this->erp->formatMoney($option->price) . '"><span>' . $this->erp->formatMoney($option->price) . '</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>';
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
                                <table id="prTable" class="table items table-striped table-bordered table-condensed table-hover">
                                    <thead>
                                    <tr>
                                        <th style="border-right:none;" class="col-md-5 col-sm-5 col-xs-5"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                        <th style="border-left:none;"></th>
                                        <th class="col-md-2 col-sm-2 col-xs-2"><?= lang("quantity"); ?></th>
                                        <th class="col-md-3 col-sm-3 col-xs-3"><?= lang("unit_price"); ?></th>
                                        <th class="col-md-1 col-sm-1 col-xs-1 text-center">
											<i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
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
				<!--	<div class="form-group">
						<input type="checkbox" class="checkbox" value="1" name="inactive" id="inactive" <?= $this->input->post('inactive') ? 'checked="checked"' : ''; ?>>
						<label for="inactive" class="padding05">
							<?= lang('inactive'); ?>
						</label>
					</div> -->
					<?php if($Settings->purchase_serial){?>
					<div class="form-group">
						<input type="checkbox" class="checkbox" value="1" name="is_serial" id="is_serial" <?= $this->input->post('is_serial') ? 'checked="checked"' : ''; ?>>
						<label for="Serial Key" class="padding05">
							<?= lang('Serial Number'); ?>
						</label>
					</div>
					<?php }?>
					<div class="form-group">
						<input name="cf" type="checkbox" class="checkbox" id="extrasc"
						   value="" <?= isset($_POST['cf']) ? 'checked="checked"' : '' ?>/><label for="extras" class="padding05"><?= lang('custom_fields') ?></label>
					</div>
					
                    <div class="row" id="extrasc-con" style="display: none;">

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf1', 'cf1') ?>
								<div class="input-group">
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
								<div class="input-group">
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
								<div class="input-group">
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
								<div class="input-group">
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
								<div class="input-group">
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
								<div class="input-group">
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
								<div class="input-group">
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
								<div class="input-group">
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
								<div class="input-group">
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
                        <?= lang("product_description", "product_details") ?>
                        <?= form_textarea('product_details', (isset($_POST['product_details']) ? $_POST['product_details'] : ($product ? $product->product_details : '')), 'class="form-control" id="details"'); ?>
                    </div>
					
                    <div class="form-group all">
                        <?= lang("product_description_for_invoice", "details") ?>
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
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
        var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
        var items = {};
        <?php
			if($combo_items) {
				foreach($combo_items as $item) {
					if($item->code) {
						echo 'add_product_item('.  json_encode($item).');';
					}
				}
			}
        ?>
        <?=isset($_POST['cf']) ? '$("#extrasc").iCheck("check");': '' ?>
        $('#extrasc').on('ifChecked', function () {
            $('#extrasc-con').slideDown();
        });
        $('#extrasc').on('ifUnchecked', function () {
            $('#extrasc-con').slideUp();
        });
		<?= isset($_POST['inactive']) ? '$("#inactive").iCheck("check");': '' ?>
		<?= isset($_POST['promotion']) ? '$("#promotion").iCheck("check");': '' ?>
        $('#promotion').on('ifChecked', function (e) {
            $('#promo').slideDown();
        });
        $('#promotion').on('ifUnchecked', function (e) {
            $('#promo').slideUp();
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
			if (t !== 'service') {
                //$('.cost').slideUp();
				$('#cost').attr('required', 'required');
            } else {
                $('.cost').slideDown();
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
		
		if (t !== 'service') {
			//$('.cost').slideUp();
			$('#cost').attr('required', 'required');
		} else {
			$('.cost').slideDown();
		}

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
			var sum_price=0;
			var sum_cost=0;
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
				
                tr_html = '<td style="border-right:none;"><input name="combo_item_id[]" type="hidden" value="' + this.id + '"><input name="combo_item_name[]" type="hidden" value="' + this.name + '"><input name="combo_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.name + ' (' + this.code + ')</span></td>';
				
				tr_html += '<td style="border-left:none;"><i class="fa fa-clone combo_custom_field" id="' + this.id + '" aria-hidden="true" style="cursor:pointer;"></i></td>';
				
				tr_html += '<td><input class="form-control text-center" name="combo_item_quantity_unit[]" type="text" value="' + formatDecimal(this.cqty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_unit_' + row_no + '" onClick="this.select();"></td>';

                tr_html += '<td><input class="form-control text-center" name="combo_item_price[]" type="text" value="' + formatDecimal(this.price) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_price_' + row_no + '" onClick="this.select();"></td>';
				
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
				
				sum_price+=(formatDecimal(this.price)*formatDecimal(this.cqty));
				sum_cost +=(formatDecimal(this.cost)*formatDecimal(this.cqty));
				
				newTr.html(tr_html);
                newTr.prependTo("#prTable");
				
				
            });
            $('.item_' + item_id).addClass('warning');
            //audio_success.play();
		
			$('#price').val(sum_price);
			$('#cost').val(sum_cost);
            return true;
			
        }
		
		$( ".box-header" ).click(
			  function() {
				  
			alert(sum_product_price[count_add]);}
			);
		
		$('.combo_custom_field').live('click', function() {
			var combo_id = $(this).attr('id');
			$.ajax({
				type: 'GET',
				url: '<?= site_url('products/getComboItemInfo'); ?>',
				data: {combo_id:combo_id},
				cache: false,
				success: function (data) {
					if(data != null) {
						$('#extrasc').iCheck('check');
						$('#extrasc-con').slideDown();
						var item = eval(data);
						var combo_ = '';
						for(a in item){
							id = item[a]['id'];
							cf1 = item[a]['cf1'];
							cf2 = item[a]['cf2'];
							cf3 = item[a]['cf3'];
							cf4 = item[a]['cf4'];
							cf5 = item[a]['cf5'];
							cf6 = item[a]['cf6'];
							cf7 = item[a]['cf7'];
							cf8 = item[a]['cf8'];
							cf9 = item[a]['cf9'];
							brand = item[a]['brand'];
							image = item[a]['image'];
						}
						$("#cf1").select2("val", cf1);
						$("#cf2").select2("val", cf2);
						$("#cf3").select2("val", cf3);
						$("#cf4").select2("val", cf4);
						$("#cf5").select2("val", cf5);
						$("#cf6").select2("val", cf6);
						$("#cf7").select2("val", cf7);
						$("#cf8").select2("val", cf8);
						$("#cf9").select2("val", cf9);
						$("#brand").select2("val", brand);
						//$("#product_image").val(image);
					}else{
						$('#extrasc').iCheck('uncheck');
						$('#extrasc-con').slideUp();
					}
				}
			});
		});

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
        var variants = <?=json_encode($vars);?>;
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

    <?php if ($product) { ?>
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
            url: "<?= site_url('products/getSubCategories') ?>/" + '<?= $product->category_id ?>',
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
        $('#supplier_price').val("<?= $product->supplier1price == 0 ? '' : $this->erp->formatDecimal($product->supplier1price); ?>");
        <?php } ?>
        <?php if ($product->supplier2) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_2', "<?= $product->supplier2; ?>");
        $('#supplier_2_price').val("<?= $product->supplier2price == 0 ? '' : $this->erp->formatDecimal($product->supplier2price); ?>");
        <?php } ?>
        <?php if ($product->supplier3) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_3', "<?= $product->supplier3; ?>");
        $('#supplier_3_price').val("<?= $product->supplier3price == 0 ? '' : $this->erp->formatDecimal($product->supplier3price); ?>");
        <?php } ?>
        <?php if ($product->supplier4) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_4', "<?= $product->supplier4; ?>");
        $('#supplier_4_price').val("<?= $product->supplier4price == 0 ? '' : $this->erp->formatDecimal($product->supplier4price); ?>");
        <?php } ?>
        <?php if ($product->supplier5) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_5', "<?= $product->supplier5; ?>");
        $('#supplier_5_price').val("<?= $product->supplier5price == 0 ? '' : $this->erp->formatDecimal($product->supplier5price); ?>");
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
                            echo form_dropdown('warehouse', $wh, '', 'id="warehouse" class="form-control"');
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