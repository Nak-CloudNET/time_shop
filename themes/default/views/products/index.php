<?php
	$v = "";$v = "";
	if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
	}
	if ($this->input->post('category')) {
		$v .= "&category=" . $this->input->post('category');
	} 
	if ($this->input->post('product_actions')) {
		$v .= "&product_actions=" . $this->input->post('product_actions');
	}
	if ($this->input->post('product_type')) {
		$v .= "&product_type=" . $this->input->post('product_type');
	}
    if ($this->input->get('cate')) {
        $v .= "&cate=" . $this->input->get('cate');
    }
    if ($this->input->get('brand')) {
        $v .= "&brand=" . $this->input->get('brand');
    }
?>
<style type="text/css" media="screen">
    #PRData td:nth-child(6), #PRData td:nth-child(7) {
        text-align: right;
    }
    #PRData th:nth-child(2) {
		width: 40px !important;
	}
    #PRData th:nth-child(6) {
		width: 20% !important;
	}
	#PRData td:nth-child(6) {
		text-align:left;
	}
    
    <?php if($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
    #PRData td:nth-child(8) {
        text-align: right;
    }
    <?php } ?>
</style>
<script>
    var oTable;
    $(document).ready(function () {
		
		/* Clear LocalStorage Data from add product */
		<?php if($this->session->flashdata('from_ls_pr')){ ?>
			localStorage.clear();
		<?php } ?>
		/* ** */
		
        oTable = $('#PRData').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getProducts'.($warehouse_id ? '/'.$warehouse_id : '').'/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "product_link";
                return nRow;
            },
            "aoColumns": [
					{"bSortable": false, "mRender": checkbox}, 
					{"bSortable": false, "mRender": img_hl}, 
					null, null, null, null,
					<?php if($Owner || $Admin || $GP['products-cost']){?>
					{"mRender": currencyFormat},
					<?php }?>
					<?php if($Owner || $Admin || $GP['products-price']){?>
					{"mRender": currencyFormat},
					<?php }?>
					{"mRender": formatQuantity}, 
					{"bVisible": false},
					{"bVisible": false}, 
					{"bVisible": false}, 
					{"bVisible": false}, 
					{"bVisible": false}, 
					{"bVisible": false}, 
					{"bVisible": false}, 
					{"bVisible": false}, 
					{"bVisible": false}, 
					{"bVisible": false}, 
					{"bVisible": false}, 
					{"bSortable": false}
            ]
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 2, filter_default_label: "[<?=lang('brand');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
            <?php 
				$col = 6;
				if($Owner || $Admin || $GP['products-cost']) {
					echo '{column_number : '.$col.', filter_default_label: "['.lang('product_cost').']", filter_type: "text", data: [] },';
					$col = $col + 1;
				}
				if($Owner || $Admin || $GP['products-price']) {
					echo '{column_number : '.$col.', filter_default_label: "['.lang('product_price').']", filter_type: "text", data: [] },';
					$col = $col + 1;
				}
				echo '{column_number: '.$col.', filter_default_label: "['.lang('quantity').']", filter_type: "text", data: []},'
            ?>
			
		], "footer");

    });
</script>
<?php 

    echo form_open('products/product_actions'.($warehouse_id ? '/'.$warehouse_id : ''), 'id="action-form"');

?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('products') . ' (' . ($warehouse_id ? $warehouse_id == 'no'?'No Warehouses':$warehouse->name : lang('all_warehouses')) . ')'; ?>
        </h2>
		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
						<?php if ($Owner || $Admin || $GP['products-add']) {?>
                        <li>
							<a href="<?= site_url('products/add') ?>">
								<i class="fa fa-plus-circle"></i> <?= lang('add_product') ?>
							</a>
						</li>
						<?php }?>
						<?php if ($Owner || $Admin) {?>
                        <li>
							<a href="#" id="barcodeProducts" data-action="barcodes">
								<i class="fa fa-print"></i> <?= lang('print_barcodes') ?>
							</a>
						</li>
						<li>
                            <a href="#" id="sync_quantity" data-action="sync_quantity">
                                <i class="fa fa-arrows-v"></i> <?= lang('sync_quantity') ?>
                            </a>
                        </li>
						<?php }?>
                        
						<?php if ($Owner || $Admin) {?>
							<li>
								<a href="#" id="excel" data-action="export_excel">
									<i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
								</a>
							</li>
							
							<li>
								<a href="#" id="pdf" data-action="export_pdf">
									<i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
								</a>
							</li>
							
							<li>
								<a href="<?= site_url('products/import_csv'); ?>">
									<i class="fa fa-file-text-o"></i>
									<span class="text"> <?= lang('import_products'); ?></span>
								</a>
							</li>
							
							<li>
								<a href="<?= site_url('products/update_products'); ?>">
									<i class="fa fa-file-text-o"></i>
									<span class="text"> <?= lang('update_products'); ?></span>
								</a>
							</li>
							
							<li>
								<a href="<?= site_url('products/update_quantity'); ?>">
									<i class="fa fa-file-text-o"></i>
									<span class="text"> <?= lang('update_quantity'); ?></span>
								</a>
							</li>
							
							<li>
								<a href="<?= site_url('products/update_price'); ?>">
									<i class="fa fa-file-text-o"></i>
									<span class="text"> <?= lang('update_price'); ?></span>
								</a>
							</li>
                            <li>
								<a href="<?= site_url('products/upload_image'); ?>">
									<i class="fa fa-file-text-o"></i>
									<span class="text"> <?= lang('upload_image'); ?></span>
								</a>
							</li>
						<?php }else{ ?>
							<?php if($GP['products-export']) { ?>
								<li>
									<a href="#" id="excel" data-action="export_excel">
										<i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
									</a>
								</li>
								
								<li>
									<a href="#" id="pdf" data-action="export_pdf">
										<i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
									</a>
								</li>
							<?php }?>
							
							<?php if($GP['products-import']) { ?>
								<li>
									<a href="<?= site_url('products/import_csv'); ?>">
										<i class="fa fa-file-text-o"></i>
										<span class="text"> <?= lang('import_products'); ?></span>
									</a>
								</li>
								
								<li>
									<a href="<?= site_url('products/update_products'); ?>">
										<i class="fa fa-file-text-o"></i>
										<span class="text"> <?= lang('update_products'); ?></span>
									</a>
								</li>
								
								<li>
									<a href="<?= site_url('products/update_quantity'); ?>">
										<i class="fa fa-file-text-o"></i>
										<span class="text"> <?= lang('update_quantity'); ?></span>
									</a>
								</li>
								
								<li>
									<a href="<?= site_url('products/update_price'); ?>">
										<i class="fa fa-file-text-o"></i>
										<span class="text"> <?= lang('update_price'); ?></span>
									</a>
								</li>
								<li>
									<a href="<?= site_url('products/upload_image'); ?>">
										<i class="fa fa-file-text-o"></i>
										<span class="text"> <?= lang('upload_image'); ?></span>
									</a>
								</li>
							<?php }?>
						<?php }?>					
						
						<?php if ($Owner || $Admin || $GP['products-delete']) {?>
                        <li class="divider"></li>
                        <li>
							<a href="#" class="bpo" title="<?= $this->lang->line("delete_products") ?>"
                               data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_products') ?>
							</a>
						</li>
						<?php }?>
						
                    </ul>
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('products') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li><a href="<?= site_url('products/index/no') ?>"><i class="fa fa-building-o"></i> <?= lang('No_Warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                               echo '<li><a href="' . site_url('products/index/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
	<div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo form_open("products"); ?>
                    <div class="row">
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="product_id"><?= lang("product"); ?></label>
                                <?php
								
                                $pr[0] = $this->lang->line("all");;
                                foreach ($products as $product) {
                                    $pr[$product->id] = $product->name . " | " . $product->code ;
                                }
                                echo form_dropdown('product', $pr, (isset($_POST['product']) ? $_POST['product'] : ""), 'class="form-control" id="product" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("product") . '"');
                                ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[0] = $this->lang->line("all");
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product_type", "product_type"); ?>
                                <?php
								$pst = array('0' => lang('all'), 'standard' => lang('standard'), 'combo' => lang('combo'), 'service' => lang('service'));
                                echo form_dropdown('product_type', $pst, (isset($_POST['product_type']) ? $_POST['product_type'] : ''), 'class="form-control input-tip" id="product_type"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_product', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="PRData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
							<tr class="primary">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkth" type="checkbox" name="check"/>
								</th>
								<th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
								<th><?= lang("brand") ?></th>
								<th><?= lang("category") ?></th>
								<th><?= lang("product_code") ?></th>
								<th style="width: 100% !important;">
									<?= lang("product_name") ?>
								</th>
								<?php
								if ($Owner || $Admin) {
									echo '<th>' . lang("product_cost") . '</th>';
									echo '<th>' . lang("product_price") . '</th>';
								} else {
									if($GP['products-cost']) {
										echo '<th>' . lang("product_cost") . '</th>';
									}
									if($GP['products-price']) {
										echo '<th>' . lang("product_price") . '</th>';
									}
								}
								?>
								<th><?= lang("quantity") ?></th>
								<th><?= lang("rack") ?></th>
								<th><?= lang("cf1") ?></th>
								<th><?= lang("cf2") ?></th>
								<th><?= lang("cf3") ?></th>
								<th><?= lang("cf4") ?></th>
								<th><?= lang("cf5") ?></th>
								<th><?= lang("cf6") ?></th>
								<th><?= lang("cf7") ?></th>
								<th><?= lang("cf8") ?></th>
								<th><?= lang("note") ?></th>
								<th><?= lang("cf9") ?></th>
								<th style="min-width:65px; text-align:center;"><?= lang("actions") ?></th>
							</tr>
                        </thead>
						
                        <tbody>
							<tr>
								<td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
							</tr>
                        </tbody>

                        <tfoot class="dtFilter">
							<tr class="active">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<?php
									if ($Owner || $Admin) {
										echo '<th></th>';
										echo '<th></th>';
									} else {
										if($GP['products-cost']) {
											echo '<th></th>';
										}
										if($GP['products-price']) {
											echo '<th></th>';
										}
									}
								?>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th style="width:65px; text-align:center;"><?= lang("actions") ?></th>
							</tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#form').hide();
    $('.toggle_down').click(function () {
        $("#form").slideDown();
        return false;
    });
    $('.toggle_up').click(function () {
        $("#form").slideUp();
        return false;
    });
	$(document).ready(function(){
		/*
		$("#excel").click(function(e){
			e.preventDefault();
			window.location.href = "<?=site_url('products/getProductAll/0/xls/')?>";
			return false;
		});
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('products/getProductAll/pdf/?v=1'.$v)?>";
            return false;
        });
		*/
	});
</script>
