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
    $(document).ready(function () {
        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });
		$('#brand').change(function () {
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
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_product'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('update_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("products/edit/" . $product->id, $attrib)
                ?>
                <div class="col-md-5">
					
                    <div class="form-group">
                        <?= lang("product_type", "type") ?>
                        <?php
                        $opts = array('standard' => lang('standard'), 'combo' => lang('combo'), 'service' => lang('service'));
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
                        <?= lang("brand", "brand") ?>
                        <?php
                        $br[''] = "";
                        foreach ($brands as $brand) {
                            $br[$brand->id] = $brand->name;
                        }
                        echo form_dropdown('brand', $br, (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand_id : '')), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" required="required" style="width:100%"')
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
                        <?= lang("subcategory", "subcategory") ?>
                        <div class="controls" id="subcat_data"> <?php
                            echo form_input('subcategory', ($product ? $product->subcategory_id : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                            ?>
                        </div>
                    </div>
                    <div class="form-group all">
                        <label class="control-label" for="unit"><?= lang("product_unit") ?></label>
                        <?php
                        $ut[""] = "";
						foreach($unit as $uts){
							$ut[$uts->id] = $uts->name;
						}
                        echo form_dropdown('unit', $ut, (isset($_POST['unit']) ? $_POST['unit'] : ($product ? $product->unit : '')), 'class="form-control select" id="unit" required="required" placeholder="'.lang('select_units').'" style="width:100%;"');
                        ?>
                    </div>

                    <div class="form-group standard">
                        <?= lang("product_cost", "cost") ?>
                        <?= form_input('cost', '', 'class="form-control tip" id="cost" required="required"') ?>
                    </div>
					
					<!--
					<input type="hidden" name="cost" value="<?=(isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->erp->formatDecimal($product->cost) : ''))?>">
					-->
					
                    <div class="form-group all">
                        <?= lang("product_price", "price") ?>
                        <?= form_input('price', '', 'class="form-control tip" id="price" required="required"') ?>
                    </div>
					
					<div class="form-group">
						<label class="control-label" for="currency"><?= lang("default_currency"); ?></label>
						<div class="controls"> 
							<?php
								foreach ($currencies as $currency) {
									$cu[$currency->code] = $currency->name;
								}
								echo form_dropdown('currency', $cu, $product->currentcy_code, 'class="form-control tip" id="currency" style="width:100%;"');
							?>
						</div>
					</div>
					
					<div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="promotion" id="promotion" <?= $this->input->post('promotion') ? 'checked="checked"' : ''; ?>>
                        <label for="promotion" class="padding05">
                            <?= lang('promotion'); ?>
                        </label>
                    </div>

                    <div id="promo"<?= $product->promotion ? '' : ' style="display:none;"'; ?>>
                        <div class="well well-sm">
                            <div class="form-group">
                                <?= lang('promo_price', 'promo_price'); ?>
                                <?= form_input('promo_price', set_value('promo_price', $product->promo_price ? $this->erp->formatMoney($product->promo_price) : ''), 'class="form-control tip" id="promo_price"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?= form_input('start_date', set_value('start_date', $product->start_date ? $this->erp->hrsd($product->start_date) : ''), 'class="form-control tip date" id="start_date"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?= form_input('end_date', set_value('end_date', $product->end_date ? $this->erp->hrsd($product->end_date) : ''), 'class="form-control tip date" id="end_date"'); ?>
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
                            //echo form_input('tax_rate', (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : '')), 'class="form-control" ');
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
                            class="input-group"> <?= form_input('alert_quantity', (isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : ($product ? $this->erp->formatDecimal($product->alert_quantity) : '')), 'class="form-control tip" id="alert_quantity"') ?>
                            <span class="input-group-addon">
                            <input type="checkbox" name="track_quantity" id="inlineCheckbox1"
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
                                echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control ' . ($product ? '' : 'suppliers') . '" id="supplier1" placeholder="' . lang("select") . ' ' . lang("supplier") . '" style="width:100%;"')
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
                        <div class="<?= $product ? 'text-warning' : '' ?>">
                            <strong><?= lang("warehouse_quantity") ?></strong><br>
                            <?php
                            if (!empty($warehouses) || !empty($warehouses_products)) {
                                echo '<div class="row"><div class="col-md-12"><div class="well"><button type="button" class="btn btn-xs btn-danger pull-right tip" title="' . lang('close') . '" id="disable_wh"><i class="fa fa-times"></i></button><button type="button" class="btn btn-xs btn-warning pull-right tip" title="' . lang('edit') . '" id="enable_wh"><i class="fa fa-edit"></i></button>';
                                if (!empty($warehouses_products)) {
                                    foreach ($warehouses_products as $wh_pr) {
                                        echo '<span class="bold text-info">' . $wh_pr->name . ': <input type="hidden" value="'.$this->erp->formatDecimal($wh_pr->quantity).'" id="vwh_qty_' . $wh_pr->id . '"><span class="padding05" id="rwh_qty_' . $wh_pr->id . '">' . $this->erp->formatQuantity($wh_pr->quantity) . '</span>' . ($wh_pr->rack ? ' (<span class="padding05" id="rrack_' . $wh_pr->id . '">' . $wh_pr->rack . '</span>)' : '') . '</span><br>';
                                    }
                                }
                                echo '<div id="show_wh_edit" style="padding-top:20px"><!--<div class="alert alert-danger margin010"><p>' . lang('edit_quantity_not_recommended_here') . '<input type="hidden" value="0" name="warehouse_quantity" id="warehouse_quantity"></p></div>-->';
                                foreach ($warehouses as $warehouse) {
                                    //$whs[$warehouse->id] = $warehouse->name;
                                    echo '<div class="col-md-6 col-sm-6 col-xs-6" style="padding-bottom:15px;">' . $warehouse->name . '<br><div class="form-group">' . form_hidden('wh_' . $warehouse->id, $warehouse->id) . form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : (isset($warehouse->quantity) ? $warehouse->quantity : '')), 'class="form-control wh" id="wh_qty_' . $warehouse->id . '" placeholder="' . lang('quantity') . '" readonly="true"') . '</div>';
                                    if ($this->Settings->racks) {
                                        echo '<div class="form-group">' . form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : (isset($warehouse->rack) ? $warehouse->rack : '')), 'class="form-control wh" id="rack_' . $warehouse->id . '" placeholder="' . lang('rack') . '"') . '</div>';
                                    }
                                    echo '</div>';
                                }
                                echo '</div><div class="clearfix"></div></div></div></div>';
                            }
                            ?>
                        </div>
                        <div class="clearfix"></div>
                        <div id="attrs"></div>

                        <div class="well well-sm">
                            <?php
                            if ($product_options) { ?>
                            <table class="table table-bordered table-condensed table-striped"
                                   style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?> margin-top: 10px;">
                                <thead>
                                <tr class="active">
                                    <th><?= lang('name') ?></th>
                                    <!--<th><?= lang('warehouse') ?></th>-->
									<th><?= lang('quantity_unit') ?></th>
                                    <!--<th><?= lang('quantity') ?></th>
                                    <th><?= lang('cost') ?></th>-->
                                    <th><?= lang('price') ?></th>
								</tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($product_options as $option) {
                                    echo '<tr><td class="col-xs-3"><input type="hidden" name="attr_id[]" value="' . $option->id . '"><span>' . $option->name . '</span></td>'.
									//'<td class="code text-center col-xs-3"><span>' . $option->wh_name . '</span></td>'.
									'<td class="quantity_unit text-center col-xs-2"><span>' . $this->erp->formatQuantity($option->qty_unit) . '</span></td>'.
									//'<td class="quantity text-center col-xs-2"><span>' . $this->erp->formatQuantity($option->wh_qty) . '</span></td>'.
									//'<td class="cost text-right col-xs-2">' . $this->erp->formatMoney($option->cost) . '</td>'.
									'<td class="price text-right col-xs-2">' . $this->erp->formatMoney($option->price) . '</td></tr>';
                                }
                            ?>
                            </tbody>
                            </table>
                            <?php
                            }
                            if ($product_variants) { ?>
                                <h3 class="bold"><?=lang('update_variants');?></h3>
                                <table class="table table-bordered table-condensed table-striped" style="margin-top: 10px;">
                                <thead>
                                <tr class="active">
                                    <th class="col-xs-4"><?= lang('name') ?></th>
                                    <th class="col-xs-4"><?= lang('quantity_unit') ?></th>
                                    <th class="col-xs-4"><?= lang('price') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($product_variants as $pv) {
                                     //echo '<tr><td class="col-xs-3"><input type="hidden" name="variant_id_' . $pv->id . '" value="' . $pv->id . '"><input type="text" name="variant_name_' . $pv->id . '" value="' . $pv->name . '" class="form-control"></td><td class="cost text-right col-xs-2"><input type="text" name="variant_cost_' . $pv->id . '" value="' . $pv->cost . '" class="form-control"></td><td class="price text-right col-xs-2"><input type="text" name="variant_price_' . $pv->id . '" value="' . $pv->price . '" class="form-control"></td></tr>';
									 echo '<tr><td class="col-xs-3"><input type="hidden" name="variant_id_' . $pv->id . '" value="' . $pv->id . '"><input type="text" name="variant_name_' . $pv->id . '" value="' . $pv->name . '" class="form-control"></td><td class="qty_unit text-right col-xs-2"><input type="text" name="variant_qty_unit_' . $pv->id . '" value="' . $pv->qty_unit . '" class="form-control"></td><td class="price text-right col-xs-2"><input type="text" name="variant_price_' . $pv->id . '" value="' . $pv->price . '" class="form-control"></td></tr>';
                                }
                                ?>
                                </tbody>
                                </table>
                                <?php
                            }
                            ?>
                            <div class="form-group">
                                <input type="checkbox" class="checkbox" name="attributes" id="attributes" <?= $this->input->post('attributes') ? 'checked="checked"' : ''; ?>>
                                <label for="attributes" class="padding05"><?= lang('add_more_variants'); ?></label>
                                <?= lang('eg_sizes_colors'); ?>
                            </div>

                            <div id="attr-con" <?= $this->input->post('attributes') ? '' : 'style="display:none;"'; ?>>
                            <div class="form-group" id="ui" style="margin-bottom: 0;">
                                <div class="input-group">
                                    <?php
                                    echo form_input('attributesInput', '', 'class="form-control select-tags" id="attributesInput" placeholder="' . $this->lang->line("enter_attributes") . '"'); ?>
                                    <div class="input-group-addon" style="padding: 2px 5px;">
                                        <a href="#" id="addAttributes">
                                            <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                        </a>
                                    </div>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                            <div class="table-responsive">
                                <table id="attrTable" class="table table-bordered table-condensed table-striped" style="margin-bottom: 0; margin-top: 10px;">
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
                                                echo '<tr class="attr">
                                                <td><input type="hidden" name="attr_name[]" value="' . $_POST['attr_name'][$r] . '"><span>' . $_POST['attr_name'][$r] . '</span></td>
                                                <!--<td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $_POST['attr_warehouse'][$r] . '"><input type="hidden" name="attr_wh_name[]" value="' . $_POST['attr_wh_name'][$r] . '"><span>' . $_POST['attr_wh_name'][$r] . '</span></td>-->
												<td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="' . $_POST['attr_quantity'][$r] . '"><span>' . $_POST['attr_quantity'][$r] . '</span></td>
                                                <!--<td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="' . $_POST['attr_quantity'][$r] . '"><span>' . $_POST['attr_quantity'][$r] . '</span></td>	
                                                <td class="cost text-right"><input type="hidden" name="attr_cost[]" value="' . $_POST['attr_cost'][$r] . '"><span>' . $_POST['attr_cost'][$r] . '</span></td>-->		
                                                <td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $_POST['attr_price'][$r] . '"><span>' . $_POST['attr_price'][$r] . '</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td>
                                                </tr>';
                                            }
                                        }
                                    }
                                    ?></tbody>
                                </table>
                            </div>
                        </div>
                        </div>

                    </div>
					
					<div class="standard">
						<div class="form-group">
							<input type="checkbox" class="checkbox"  name="related_strap" id="related_strap" <?= $this->input->post('related_strap') || $product_options || $straps ? 'checked="checked"' : ''; ?>>
							<label for="related_strap" class="padding05"><?= lang('related_strap'); ?></label>
						</div>
						<div class="well well-sm" id="strap-con"
							 style="<?= $this->input->post('related_strap') || $product_options ? '' : 'display:none;'; ?>">
							<div class="form-group" id="ui" style="margin-bottom: 0;">
								<div class="input-group" style="width:100%;">
									<?php
										$related_strap = '';
										foreach ($products as $products) {
											$related_strap[$products->code] = $products->code;
										}
										$k = 1;
										$sp  = '';
										foreach($straps as $strap) {
											if($k == count($straps)) {
												$sp .= $strap->related_product_code;
											}else {
												$sp .= $strap->related_product_code.',';
											}
											$k++;
										}
										echo form_dropdown('related_strap[]', $related_strap, $sp, 'id="related_straps" class="form-control" multiple="multiple"');
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
                            <!--<div class="row"><div class="ccol-md-10 col-sm-10 col-xs-10"><label class="table-label" for="combo"><?= lang("combo_products"); ?></label></div>
                            <div class="ccol-md-2 col-sm-2 col-xs-2"><div class="form-group no-help-block" style="margin-bottom: 0;"><input type="text" name="combo" id="combo" value="" data-bv-notEmpty-message="" class="form-control" /></div></div></div>-->
                            <div class="controls table-controls">
                                <table id="prTable"
                                       class="table items table-striped table-bordered table-condensed table-hover">
                                    <thead>
                                    <tr>
                                        <th class="col-md-5 col-sm-5 col-xs-5" style="border-right:none;"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
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
					<!-- <div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="inactive" id="inactive" <?= $product->inactived ? 'checked="checked"' : ''; ?>>
                        <label for="inactive" class="padding05">
                            <?= lang('inactive'); ?>
                        </label>
                    </div> -->
					
					<?php if($Settings->purchase_serial){?>
					<div class="form-group">
                        <input type="checkbox" class="checkbox" value="1" name="is_serial" id="is_serial" <?= $product->is_serial ? 'checked="checked"' : ''; ?>>
                        <label for="Serial Key" class="padding05">
                            <?= lang('Serial Number'); ?>
                        </label>
                    </div>
					<?php }?>
					
                    <div class="form-group">
                        <input name="cf" type="checkbox" class="checkbox" id="extras" value="" checked="checked"/><label
                            for="extras" class="padding05"><?= lang('custom_fields') ?></label>
                    </div>
					
                    <div class="row" id="extras-con">

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf1', 'cf1') ?>
                                <?php
									$cased[''] = "";
									foreach ($case as $cases) {
										$cased[$cases->id] = $cases->name;
									}
									echo form_dropdown('cf1', $cased, (isset($_POST['cf1']) ? $_POST['cf1'] : ($product ? $product->cf1 : '')), 'class="form-control select" id="cf1" placeholder="' . lang("select") . " " . lang("pcf1") . '" style="width:100%"');
								?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf2', 'cf2') ?>
                                <?php
									$diam[''] = '';
									foreach ($diameters as $diameter) {
										$diam[$diameter->id] = $diameter->name;
									}
									echo form_dropdown('cf2', $diam, (isset($_POST['cf2']) ? $_POST['cf2'] : ($product ? $product->cf2 : '')), 'class="form-control select" id="cf2" placeholder="' . lang("select") . " " . lang("pcf2") . '" style="width:100%"');
								?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf3', 'cf3') ?>
                                <?php
									$dis[''] = '';
									foreach ($dial as $dials) {
										$dis[$dials->id] = $dials->name;
									}
									echo form_dropdown('cf3', $dis, (isset($_POST['cf3']) ? $_POST['cf3'] : ($product ? $product->cf3 : '')), 'class="form-control select" id="cf3" placeholder="' . lang("select") . " " . lang("pcf3") . '" style="width:100%"');
								?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf4', 'cf4') ?>
                                <?php
									$astraps[''] = '';
									foreach ($all_strap as $st) {
										$astraps[$st->id] = $st->name;
									}
									echo form_dropdown('cf4', $astraps, (isset($_POST['cf4']) ? $_POST['cf4'] : ($product ? $product->cf4 : '')), 'class="form-control select" id="cf4" placeholder="' . lang("select") . " " . lang("pcf4") . '" style="width:100%"');
								?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf5', 'cf5') ?>
                                <?php
									$wr[''] = '';
									foreach ($water as $wa) {
										$wr[$wa->id] = $wa->name;
									}
									echo form_dropdown('cf5', $wr, (isset($_POST['cf5']) ? $_POST['cf5'] : ($product ? $product->cf5 : '')), 'class="form-control select" id="cf5" placeholder="' . lang("select") . " " . lang("pcf5") . '" style="width:100%"');
								?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf6', 'cf6') ?>
                                <?php
									$wi[''] = '';
									foreach ($winding as $wd) {
										$wi[$wd->id] = $wd->name;
									}
									echo form_dropdown('cf6', $wi, (isset($_POST['cf6']) ? $_POST['cf6'] : ($product ? $product->cf6 : '')), 'class="form-control select" id="cf6" placeholder="' . lang("select") . " " . lang("pcf6") . '" style="width:100%"');
								?>
                            </div>
                        </div>
						
						<div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf7', 'cf7') ?>
                                <?php
									$prd[''] = '';
									foreach ($powerreserve as $pr) {
										$prd[$pr->id] = $pr->name;
									}
									echo form_dropdown('cf7', $prd, (isset($_POST['cf7']) ? $_POST['cf7'] : ($product ? $product->cf7 : '')), 'class="form-control select" id="cf7" placeholder="' . lang("select") . " " . lang("pcf7") . '" style="width:100%"');
								?>
                            </div>
                        </div>
						
						<div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf8', 'cf8') ?>
                                <?php
									$bcd[''] = '';
									foreach ($buckle as $bc) {
										$bcd[$bc->id] = $bc->name;
									}
									echo form_dropdown('cf8', $bcd, (isset($_POST['cf8']) ? $_POST['cf8'] : ($product ? $product->cf8 : '')), 'class="form-control select" id="cf8" placeholder="' . lang("select") . " " . lang("pcf8") . '" style="width:100%"');
								?>
                            </div>
                        </div>
						
						<div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('pcf9', 'cf9') ?>
                                <?php
									$cld[''] = '';
									foreach ($complication as $cl) {
										$cld[$cl->id] = $cl->name;
									}
									echo form_dropdown('cf9', $cld, (isset($_POST['cf9']) ? $_POST['cf9'] : ($product ? $product->cf9 : '')), 'class="form-control select" id="cf9" placeholder="' . lang("select") . " " . lang("pcf9") . '" style="width:100%"');
								?>
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
                        <?php echo form_submit('edit_product', $this->lang->line("edit_product"), 'class="btn btn-primary"'); ?>
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
		var string = '<?php echo $sp; ?>';
		var array = string.split(',');
		$("#related_straps").val(array);
		if(string != ''){
			
		}
        <?php
        if($combo_items) {
            echo '
                var ci = '.json_encode($combo_items).';
                $.each(ci, function() { add_product_item(this); });
                ';
        }
        ?>
        <?=isset($_POST['cf']) ? '$("#extras").iCheck("check");': '' ?>
        $('#extras').on('ifChecked', function () {
            $('#extras-con').slideDown();
        });
        $('#extras').on('ifUnchecked', function () {
            $('#extras-con').slideUp();
        });
		<?= isset($_POST['promotion']) || $product->promotion ? '$("#promotion").iCheck("check");': '' ?>
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
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
            } else {
                $('.standard').slideDown();
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
        });

        $("#add_item").autocomplete({
            source: '<?= site_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 5,
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
        $('#add_item').removeAttr('required');
        $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');

        function add_product_item(item) {
			var sum_price=0;
			var sum_cost =0;
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
				var tr_html = '';
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '"></tr>');
				tr_html = '<td style="border-right:none;"><input name="combo_item_id[]" type="hidden" value="' + this.id + '"><input name="combo_item_name[]" type="hidden" value="' + this.name + '"><input class="item_cost" type="hidden" value="' + this.cost + '" /><input name="combo_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.name + ' (' + this.code + ')</span></td>';
				
				tr_html += '<td style="border-left:none;"><i class="fa fa-clone combo_custom_field" id="' + this.id + '" aria-hidden="true" style="cursor:pointer;"></i></td>';
				
				tr_html += '<td><input class="form-control text-center" name="combo_item_quantity_unit[]" type="text" value="' + formatDecimal(this.cqty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
				
                tr_html += '<td><input class="form-control text-center item_price" name="combo_item_price[]" type="text" value="' + formatDecimal(this.price) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_price_' + row_no + '" onClick="this.select();"></td>';
				
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
				
				sum_price+=(formatDecimal(this.price)*formatDecimal(this.cqty));
				sum_cost +=(formatDecimal(this.cost)*formatDecimal(this.cqty));
				
                newTr.html(tr_html);
                newTr.prependTo("#prTable");
            });
            $('.item_' + item_id).addClass('warning');
			$('#price').val(sum_price);
			$('#cost').val(sum_cost);
            //audio_success.play();
            return true;

        }
		
		$('.combo_custom_field').live('click', function() {
			var combo_id = $(this).attr('id');
			$.ajax({
				type: 'GET',
				url: '<?= site_url('products/getComboItemInfo'); ?>',
				data: {combo_id:combo_id},
				cache: false,
				success: function (data) {
					if(data != null) {
						$('#extras').iCheck('check');
						$('#extras-con').slideDown();
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
						}
						$("#cf1").select2("val", cf1);
						$("#cf2").select2("val", cf2);
						$("#cf3").select2("val", cf3);
						$("#cf4").select2("val", cf4);
						$("#cf5").select2("val", cf5);
						$("#cf6").select2("val", cf6);
						$("#cf7").select2("val", cf7);
						$("#cf8").select2("val", cf8);
						$("#brand").select2("val", brand);
					}else{
						$('#extras').iCheck('uncheck');
						$('#extras-con').slideUp();
					}
				}
			});
		});

        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            $(this).closest('#row_' + id).remove();
            $.each(items, function (i, v) {
                if (v.id == id) {
                    delete items[i];
                }
            });
			del_product_item(items);
        });
		
		function del_product_item(item) {
			var sum_price = 0;
			var sum_cost  = 0;
			$("#prTable tbody").empty();
			$.each(item, function () {
				var row_no = this.id;
				var tr_html = '';
				
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '"></tr>');
				
				tr_html = '<td style="border-right:none;"><input name="combo_item_id[]" type="hidden" value="' + this.id + '"><input name="combo_item_name[]" type="hidden" value="' + this.name + '"><input class="item_cost" type="hidden" value="' + this.cost + '" /><input name="combo_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.name + ' (' + this.code + ')</span></td>';
				
				tr_html += '<td style="border-left:none;"><i class="fa fa-clone combo_custom_field" id="' + this.id + '" aria-hidden="true" style="cursor:pointer;"></i></td>';
				
				tr_html += '<td><input class="form-control text-center" name="combo_item_quantity_unit[]" type="text" value="' + formatDecimal(this.cqty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
				
                tr_html += '<td><input class="form-control text-center item_price" name="combo_item_price[]" type="text" value="' + formatDecimal(this.price) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_price_' + row_no + '" onClick="this.select();"></td>';
				
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
				
				sum_price += (formatDecimal(this.price)*formatDecimal(this.cqty));
				sum_cost  += (formatDecimal(this.cost)*formatDecimal(this.cqty));
				
                newTr.html(tr_html);
                newTr.prependTo("#prTable");
			});
			$('#price').val(sum_price);
			$('#cost').val(sum_cost);
            return true;

        }
		
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
            $('.attr-remove-all').trigger('click');
            $('#attr-con').slideUp();
        });
        $('#addAttributes').click(function (e) {
            e.preventDefault();
            var attrs_val = $('#attributesInput').val(), attrs;
            attrs = attrs_val.split(',');
            for (var i in attrs) {
                if (attrs[i] !== '') {
                    //$('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value=""><span></span></td><td class="quantity_unit text-center"><input type="hidden" name="attr_quantity_unit[]" value=""><span></span></td><td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value=""><span></span></td><td class="cost text-right"><input type="hidden" name="attr_cost[]" value="0"><span>0</span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
					$('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="quantity_unit text-center"><input type="hidden" name="attr_quantity_unit[]" value=""><span></span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
                }
            }
        });
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
			$('#aquantity_unit').val(row.children().eq(1).find('span').text());
            $('#aprice').val(row.children().eq(2).find('span').text());
            $('#aModal').appendTo('body').modal('show');
        });
		
		//=====================Related Strap=========================
		var check = $('#related_strap').attr('checked');
		if(check == 'checked'){
			$('#strap-con').slideDown();
		}
		$(document).on('ifChecked', '#related_strap', function (e) {
            $('#strap-con').slideDown();
        });
        $(document).on('ifUnchecked', '#related_strap', function (e) {
            $(".select-strap").select2("val", "");
            $('.attr-remove-all').trigger('click');
            $('#strap-con').slideUp();
        });
		//=====================end===================================

        $(document).on('click', '#updateAttr', function () {
            var wh = $('#awarehouse').val(), wh_name;
            $.each(warehouses, function () {
                if (this.id == wh) {
                    wh_name = this.name;
                }
            });
 
			row.children().eq(1).html('<input type="hidden" name="attr_quantity_unit[]" value="' + $('#aquantity_unit').val() + '"><span>' + $('#aquantity_unit').val() + '</span>');
 
            row.children().eq(2).html('<input type="hidden" name="attr_price[]" value="' + $('#aprice').val() + '"><span>' + currencyFormat($('#aprice').val()) + '</span>');

            $('#aModal').modal('hide');
        });
    });

    <?php if ($product) { ?>
    $(document).ready(function () {
        $('#enable_wh').click(function () {
            var whs = $('.wh');
            $.each(whs, function () {
                $(this).val($('#v' + $(this).attr('id')).val());
            });
            $('#warehouse_quantity').val(1);
            $('.wh').attr('disabled', false);
            $('#show_wh_edit').slideDown();
        });
        $('#disable_wh').click(function () {
            $('#warehouse_quantity').val(0);
            $('#show_wh_edit').slideUp();
        });
        $('#show_wh_edit').hide();
        $('.wh').attr('disabled', true);
        var t = "<?=$product->type?>";
        if (t !== 'standard') {
            $('.standard').slideUp();
            $('#cost').attr('required', 'required');
            $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
        } else {
            $('.standard').slideDown();
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
        $('#add_item').removeAttr('required');
        $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');
        //$("#code").parent('.form-group').addClass("has-error");
        //$("#code").focus();
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
        $('#supplier_price').val("<?= $this->erp->formatDecimal($product->supplier1price); ?>");
        <?php } else { ?>
            $('#supplier1').addClass('rsupplier');
        <?php } ?>
        <?php if ($product->supplier2) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_2', "<?= $product->supplier2; ?>");
        $('#supplier_2_price').val("<?= $this->erp->formatDecimal($product->supplier2price); ?>");
        <?php } ?>
        <?php if ($product->supplier3) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_3', "<?= $product->supplier3; ?>");
        $('#supplier_3_price').val("<?= $this->erp->formatDecimal($product->supplier3price); ?>");
        <?php } ?>
        <?php if ($product->supplier4) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_4', "<?= $product->supplier4; ?>");
        $('#supplier_4_price').val("<?= $this->erp->formatDecimal($product->supplier4price); ?>");
        <?php } ?>
        <?php if ($product->supplier5) { ?>
        $('#addSupplier').click();
        select_supplier('supplier_5', "<?= $product->supplier5; ?>");
        $('#supplier_5_price').val("<?= $this->erp->formatDecimal($product->supplier5price); ?>");
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
            });
        }
    });
    <?php } ?>
    $(document).ready(function () {
        $('#enable_wh').trigger('click');
    });
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
                    <!--<div class="form-group">
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
					
                     <!--<div class="form-group">
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
