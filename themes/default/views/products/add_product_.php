<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
			<h4 class="modal-title" id="mModalLabel"><?= lang('add_standard_product') ?></h4>
		</div>
		<div class="modal-body" id="pr_popover_content">
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
								$opts = array('standard' => lang('standard'), 'combo' => lang('combo'), 'service' => lang('service'));
								echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control" id="types"');
								?>
							</div>
							<div class="form-group all">
								<?= lang("product_name", "name") ?>
								<?= form_input('name', (isset($_POST['name']) ? $_POST['name'] : ($product ? $product->name : '')), 'class="form-control" id="name" required="required"'); ?>
							</div>
							<div class="form-group all">
								<?= lang("product_code", "code") ?>
								<?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : ($product ? $product->code : '')), 'class="form-control" id="code" required="required"') ?>
								<span class="help-block"><?= lang('you_scan_your_barcode_too') ?></span>
							</div>
							<div class="form-group all">
								<?= lang("barcode_symbology", "barcode_symbology") ?>
								<?php
								$bs = array(
									'code25' 	=> 'Code25', 
									'code39' 	=> 'Code39', 
									'code128' 	=> 'Code128', 
									'ean8' 		=> 'EAN8', 
									'ean13' 	=> 'EAN13', 
									'upca ' 	=> 'UPC-A', 
									'upce' 		=> 'UPC-E'
								);
								echo form_dropdown('barcode_symbology', $bs, (isset($_POST['barcode_symbology']) ? $_POST['barcode_symbology'] : ($product ? $product->barcode_symbology : 'code128')), 'class="form-control select" id="barcode_symbology" required="required" style="width:100%;"');
								?>
							</div>
							<div class="form-group">
								<?= lang("brand", "brand") ?>
								<?php 
									$br[''] = "";
									foreach ($brands as $brand) {
										$br[$brand->id] = $brand->name;
									}
									echo form_dropdown('brand', $br, (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand_id : '')), 'class="form-control select" id="brands" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
								?>
							</div>
							<div class="form-group">
								<?= lang("category", "category") ?>
								<?php 
									$cat[''] = "";
									foreach ($categories as $category) {
										$cat[$category->id] = $category->name;
									}
									echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : '')), 'class="form-control select" id="categories" placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
								?>
							</div>
							<div class="form-group all">
								<?= lang("subcategories", "subcategories") ?>
								<?php 
									echo form_input('subcategory', ($product ? $product->subcategory_id : ''), 'class="form-control" id="subcategories"  placeholder="' . lang("select_category_to_load") . '"');
								?>
							</div>
							<div class="form-group all">
								<label class="control-label" for="unit"><?= lang("product_unit") ?></label>
								<?php
								$ut["2"] = $uts->name;
								foreach($units as $uts){
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
								<div class="input-group"> <?= form_input('alert_quantity', (isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : ($product ? $this->erp->formatQuantity($product->alert_quantity) : '')), 'class="form-control tip" id="alert_quantity"') ?>
									<span class="input-group-addon">
										<input type="checkbox" name="track_quantity" id="track_quantity" value="1" <?= ($product ? (isset($product->track_quantity) ? 'checked="checked"' : '') : 'checked="checked"') ?>>
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
							<?php if($Settings->purchase_serial){?>
							<div class="form-group">
								<input type="checkbox" class="checkbox" value="1" name="is_serial" id="is_serial" <?= $this->input->post('is_serial') ? 'checked="checked"' : ''; ?>>
								<label for="Serial Key" class="padding05">
									<?= lang('Serial Number'); ?>
								</label>
							</div>
							<?php }?>
							<div class="form-group">
								<input name="cf" type="checkbox" class="checkbox" id="extrasc" value="" <?= isset($_POST['cf']) ? 'checked="checked"' : '' ?>/><label for="extras" class="padding05"><?= lang('custom_fields') ?></label>
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
</div>
<?= $modal_js;?>

<script type="text/javascript">
    
	
	//=====================Related Strap=========================
		$(document).on('ifChecked', '#related_strapp', function (e) {
            $('#strapp-con').slideDown();
        });
        $(document).on('ifUnchecked', '#related_strapp', function (e) {
            $(".select-strap").select2("val", "");
            $('.attr-remove-all').trigger('click');
            $('#strapp-con').slideUp();
        });
	//=====================end===================================
		
	$(document).on('ifChecked', '#attributess', function (e) {
            $('#attrs-con').slideDown();
     });
     $(document).on('ifUnchecked', '#attributess', function (e) {
         $(".select-tags").select2("val", "");
         $('#attrs-con').slideUp();
     });
    $(document).ready(function () {
		 
		 // Product Supplier
		
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
                            $("#categories").select2("destroy").empty();
							var newOptions = '';
							var option = '<option></option>';
							$("#categories").select2('destroy').append(option).select2();
							$.each(scdata, function(i, item) {
								newOptions = '<option value="'+ item.id +'">'+ item.text +'</option>';
								$("#categories").select2('destroy').append(newOptions ).select2();
							});
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            }else {
				$("#categories").select2("destroy").empty().attr("placeholder", "<?= lang('select_brand_to_load') ?>").select2({
					placeholder: "<?= lang('select_brand_to_load') ?>",
					data: [{id: '', text: '<?= lang('select_brand_to_load') ?>'}]
				});
			}
            $('#modal-loading').hide();
        });
		
		$('#categories').change(function () {
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
                            $("#subcategories").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: scdata
                            });
                        }else{
							$("#subcategories").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
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
		
		 $("#subcategories").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });
		
		<?= isset($_POST['inactive']) ? '$("#inactive").iCheck("check");': '' ?>
		<?= isset($_POST['promotion']) ? '$("#promotion").iCheck("check");': '' ?>
        $('#promotion').on('ifChecked', function (e) {
            $('#promo').slideDown();
        });
        $('#promotion').on('ifUnchecked', function (e) {
            $('#promo').slideUp();
        });
		
		
		
		$("#product_image").parent('.form-group').addClass("text-warning");
		$("#images").parent('.form-group').addClass("text-warning");
		
		 
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
		
		// Product Supplier
		$(document).on('change', '#supplier', function (e) {
            localStorage.setItem('pr_supplier', $(this).val());
        });
        if (pr_supplier = localStorage.getItem('pr_supplier')) {
            $('#supplier').val(pr_supplier);
        }
		
		// Product Customer Fileds
		/*
		$(document).on('ifChecked', '#extras', function (e) {
            localStorage.setItem('pr_customer_fields', 1);
        });
		$(document).on('ifUnchecked', '#extras', function () {
			localStorage.removeItem('pr_customer_fields');
		});*/
		
		$('.combo_custom_field').live('click', function() {
			var combo_id = $(this).attr('id');
			$.ajax({
				type: 'GET',
				url: '<?= site_url('products/getComboItemInfo'); ?>',
				data: {combo_id:combo_id},
				cache: false,
				success: function (data) {
					if(data != null) {
						$('#ex').iCheck('check');
						$('#ex-con').slideDown();
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
						$('#ex').iCheck('uncheck');
						$('#ex-con').slideUp();
					}
				}
			});
		});

		
        
	   
	   $('.combo_custom_field').live('click', function() {
			var combo_id = $(this).attr('id');
			$.ajax({
				type: 'GET',
				url: '<?= site_url('products/getComboItemInfo'); ?>',
				data: {combo_id:combo_id},
				cache: false,
				success: function (data) {
					if(data != null) {
						$('#ex').iCheck('check');
						$('#ex-con').slideDown();
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
						$('#ex').iCheck('uncheck');
						$('#ex-con').slideUp();
					}
				}
			});
		});
	
		<?=isset($_POST['cf']) ? '$("#extras").iCheck("check");': '' ?>
        $('#ex').on('ifChecked', function () {
            $('#ex-con').slideDown();
        });
        $('#ex').on('ifUnchecked', function () {
            $('#ex-con').slideUp();
        });
		
		$('#types').change(function () {
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

        var t = $('#types').val();
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
		
		$('#supplier, #rsupplier, .rsupplier').select2({
		   minimumInputLength: 1,
		   ajax: {
				url: site.base_url+"suppliers/suggestions",
				dataType: 'json',
				quietMillis: 15,
				data: function (term, page) {
					return {
						term: term,
						limit: 10
					};
				},
				results: function (data, page) {
					if(data.results != null) {
						return { results: data.results };
					} else {
						return { results: [{id: '', text: 'No Match Found'}]};
					}
				}
			}
		});
       
	});
	

</script>