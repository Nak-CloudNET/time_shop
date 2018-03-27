<?php

	/*$v = "";
	
	if ($this->input->post('reference_no')) {
		$v .= "&reference_no=" . $this->input->post('reference_no');
	}
	if ($this->input->post('customer')) {
		$v .= "&customer=" . $this->input->post('customer');
	}
	if ($this->input->post('driver')) {
		$v .= "&driver=" . $this->input->post('driver');
	}
	if ($this->input->post('warehouse')) {
		$v .= "&warehouse=" . $this->input->post('warehouse');
	}
	if ($this->input->post('user')) {
		$v .= "&user=" . $this->input->post('user');
	}
	if ($this->input->post('serial')) {
		$v .= "&serial=" . $this->input->post('serial');
	}
	if ($this->input->post('start_date')){
		$v .= "&start_date=" . $this->input->post('start_date');
	}
	if ($this->input->post('end_date')){
		$v .= "&end_date=" . $this->input->post('end_date');
	}
	if (isset($biller_id)){
		$v .= "&biller_id=" . $biller_id;
	}*/

?>

<script type="text/javascript">
    $(document).ready(function (){
        $('#form').hide();
        <?php if ($this->input->post('customer')) { ?>
        $('#customer').val(<?= $this->input->post('customer') ?>).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "customers/suggestions/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data.results[0]);
                    }
                });
            },
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
					
					$('#customer').val(<?= $this->input->post('customer') ?>);
				}
            },
			
        });
		
        <?php } ?>
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>

<?php if ($Owner) {
    echo form_open('reports/sale_report_action', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('sale_detail_report'); ?><?php
            if ($this->input->post('start_date')) {
                echo " From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i
                            class="icon fa fa-toggle-up"></i></a></li>
                <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i
                            class="icon fa fa-toggle-down"></i></a></li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="pdf" data-action="export_pdf" class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a href="#" id="excel" data-action="export_excel"  class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
				 
            </ul>
        </div>
		
    </div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo form_open("reports/getSalesReportDetail",'method="GET"'); ?>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = "";
                                foreach ($users as $user){
                                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = "";
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = "";
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
						 
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="type"><?= lang("sale_type"); ?></label>
                                <?php
									$types = array(""=> "...", 1 => lang("pos"), 2 => lang("return"));
									echo form_dropdown('type', $types, isset($type) ? $type :'', 'class="form-control" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("type") . '"');
                                ?>
                            </div>
                        </div> 
						
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table class="table table-bordered table-condensed table-striped">
						<thead>
							<tr class="info-head">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkth" type="checkbox" name="val" />
								</th>
								<th style="width:150px;" class="center"><?= lang("product_code"); ?></th> 
								<th style="width:130px;"><?= lang("product_name"); ?></th> 
								<th style="width:130px;"><?= lang("brands"); ?></th>
								<th style="width:70px;"><?= lang("serial_no"); ?></th>
								<th style="width:130px;"><?= lang("quantity"); ?></th>
								<th style="width:70px;"><?= lang("unit_price"); ?></th> 
								<th style="width:130px;"><?= lang("discount"); ?></th>
								<th style="width:130px;"><?= lang("subtotal"); ?></th> 
							</tr>
						</thead>
						<tbody>
						<?php 
					    $totals=0;
						$total_order_discount=0;
						foreach($sales as $sale){
                           	  $return_item="erp_return_items";
							  $sale_item="erp_sale_items";
							  
						      $query="Select erp_sale_items.id,erp_sale_items.product_code,erp_sale_items.product_name,erp_sale_items.serial_no,erp_sale_items.quantity,erp_sale_items.unit_price,erp_sale_items.item_discount,discount,subtotal,erp_brands.name as brands from ";
							  
							  $sales_item =$this->db->query("{$query}{$sale_item} As erp_sale_items LEFT JOIN erp_products ON erp_products.id=erp_sale_items.product_id LEFT JOIN erp_brands ON erp_brands.id=erp_products.brand_id where erp_sale_items.sale_id ={$sale->id} ")->result();
							  
							  $return_items=$this->db->query("{$query}{$return_item} As erp_sale_items
							  LEFT JOIN erp_products ON erp_products.id=erp_sale_items.product_id LEFT JOIN erp_brands ON erp_brands.id=erp_products.brand_id where erp_sale_items.return_id ={$sale->id} ")->result();
							   
		             ?>
					           <tr class="bold">
							      <td style="min-width:30px; width: 30px; text-align: center;background-color:#E9EBEC">
									<input type="checkbox" name="val[]" class="checkbox multi-select input-xs" value="<?= $sale->id; ?>" />
								  </td>
							       <td colspan="8" style="font-size:14px !important;background-color:#E9EBEC; <?php if($sale->type==2){ echo "color:red"; } ?>"><?= $sale->date ." <i class='fa fa-angle-double-right' aria-hidden='true'></i> ".$sale->reference_no ." <i class='fa fa-angle-double-right' aria-hidden='true'></i> ".$sale->customer ." <i class='fa fa-angle-double-right' aria-hidden='true'></i> ".$sale->warehouse?></td>
							   </tr>
					 <?php if($sale->type==1){
						   $subtotals=0;
                           					   
					 foreach($sales_item as $q){  
					         $subtotals += $q->subtotal;
					          
					
					?>  
						       <tr>
							       <td></td> 
							       <td><?=$q->product_code?></td> 
								   <td><?=$q->product_name ?></td>
								   <td><?=$q->brands?></td>
								   <td class="text-center"><?= $q->serial_no; ?></td> 
								   <td class="text-center"><?= $this->erp->formatDecimal($q->quantity); ?></td>
                                   <td class="text-center"><?= $this->erp->formatMoney($q->unit_price);?></td>
                                   <td class="text-center"><?= $q->discount? '('.$q->discount .')'.$this->erp->formatMoney($q->item_discount):$this->erp->formatMoney($q->item_discount);?></td> 
								   <td class="text-right"><?=$this->erp->formatMoney($q->subtotal) ?></td>   
							   </tr>
							    
					    <?php } 
						   $totals +=$subtotals-$sale->order_discount;
						   $total_order_discount +=$sale->order_discount;
						?> 
						     <tr>
							     <td></td> 
							     <td colspan="6"></td>
								 <td class="bold text-right"><?= lang("total")?></td> 
                                 <td class="bold text-right"><?=$this->erp->formatMoney($subtotals)?></td> 
							  </tr> 
						      <tr>
							      <td></td> 
							     <td colspan="6"></td>
								 <td class="bold text-right" ><?= lang("order_discount")?></td> 
                                 <td class="bold text-right"><?= $this->erp->formatMoney($sale->order_discount);?></td> 
							  </tr> 
						       <tr>
							      <td></td> 
							     <td colspan="6"></td>
								 <td class="bold text-right" ><?= lang("subtotal")?></td> 
                                  <td class="bold text-right"><?= $this->erp->formatMoney($subtotals-$sale->order_discount);?></td> 
							  </tr>
					       
						   <?php }else{ ?>
						    
					 <?php
					  
					   $subtotals=0;
					 foreach($return_items as $q){  
					   $subtotals += $q->subtotal;
					               
					?> 
						       <tr>
							       <td></td> 
							       <td><?=$q->product_code?></td> 
								   <td><?=$q->product_name ?></td>
								   <td><?=$q->brands?></td>
								   <td class="text-center"><?= $q->serial_no; ?></td> 
								   <td class="text-center"><?= $this->erp->formatDecimal($q->quantity); ?></td>
                                   <td class="text-center"><?= $this->erp->formatMoney($q->unit_price);?></td>
                                   <td class="text-center"><?= $q->discount? '('.$q->discount .')'.$this->erp->formatMoney($q->item_discount):$this->erp->formatMoney($q->item_discount);?></td> 
								   <td class="text-right"><?=$this->erp->formatMoney($q->subtotal) ?></td>   
							   </tr>
							    
					    <?php } 
						 $totals -=$subtotals-$sale->order_discount;
                         $total_order_discount -=$sale->order_discount;
						  
						?>
						       <tr>
							     <td></td> 
							     <td colspan="6"></td>
								 <td class="bold text-right"><?= lang("total")?></td> 
                                 <td class="bold text-right"><?=$this->erp->formatMoney($subtotals)?></td> 
							  </tr> 
						      <tr>
							      <td></td> 
							     <td colspan="6"></td>
								 <td class="bold text-right"><?= lang("order_discount")?></td> 
                                 <td class="bold text-right"><?= $this->erp->formatMoney($sale->order_discount);?></td> 
							  </tr> 
						       <tr>
							      <td></td> 
							     <td colspan="6"></td>
								 <td class="bold text-right" ><?= lang("subtotal")?></td> 
                                 <td class="bold text-right"><?= $this->erp->formatMoney($subtotals-$sale->order_discount);?></td> 
							  </tr>
						   <?php } }  
						   ?>
						      <tr>
							     <td></td> 
							     <td colspan="6"></td>
								 <td class="bold text-right" style="color:blue;"><?= lang("total")?></td> 
                                 <td class="bold text-right"><?=$this->erp->formatMoney($totals)?></td> 
							  </tr> 
						      <tr>
							      <td></td> 
							     <td colspan="6"></td>
								 <td class="bold text-right" style="color:blue;"><?= lang("total_order_discount")?></td> 
                                 <td class="bold text-right"><?=$this->erp->formatMoney($total_order_discount)?></td> 
							  </tr> 
						       <tr>
							      <td></td> 
							     <td colspan="6"></td>
								 <td class="bold text-right" style="color:blue;"><?= lang("grand_total")?></td> 
                                 <td class="bold text-right"><?=$this->erp->formatMoney($totals-$total_order_discount)?></td> 
							  </tr>   
					</tbody> 
                       
                   					
                    </table>
                </div>
				<div class=" text-right">
					<div class="dataTables_paginate paging_bootstrap">
						<?= $pagination; ?>
					</div>
				</div>
				
			 
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		$('.reset').click(function(){
			window.location.reload(true);
		});
       /* $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getSalesReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getSalesReport/0/xls/?v=1'.$v)?>";
            return false;
        });
		*/
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>
<style type="text/css">
	
</style>