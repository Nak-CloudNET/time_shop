<script type="text/javascript">
	$(document).ready(function(){ 
		$('body').on('click', '#excel1', function(e) {
		   e.preventDefault();
		   var k = false;
		   $.each($("input[name='val[]']:checked"), function(){
			k = true;

		   });
		   if(k == false){
			bootbox.alert('Please select!');
			return false;
		   }
		   $('#form_action').val($('#excel1').attr('data-action'));
		   $('#action-form-submit').trigger('click');
		});
		$('body').on('click', '#pdf1', function(e) {
		   e.preventDefault();
		   var k = false;
		   $.each($("input[name='val[]']:checked"), function(){
			
			k = true;
		   });
		   if(k == false){
			bootbox.alert('Please select tag5!');
			return false;
		   }
		   $('#form_action').val($('#pdf1').attr('data-action'));
		   $('#action-form-submit').trigger('click');
		});
	});
</script>
<style>
	#tbstock .shead th{
		background-color: #428BCA;border-color: #357EBD;color:white;text-align:center;
	}

</style>
<?php 
if ($Owner) {
   echo form_open('reports/warehouse_reports_action' ,'id="action-form"');
} 
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('warehouse_products') ; ?>
        </h2>
		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:void(0);" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
				<li class="dropdown">
					<a href="#" id="pdf1" data-action="export_pdf"  class="tip" title="<?= lang('download_pdf') ?>">
						<i class="icon fa fa-file-pdf-o"></i>
					</a>
				</li>
                <li class="dropdown">
					<a href="#" id="excel1" data-action="export_excel"  class="tip" title="<?= lang('download_xls') ?>">
						<i class="icon fa fa-file-excel-o"></i>
					</a>
				</li>
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
				
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
				<?php echo form_open('reports/warehouse_reports', 'id="action-form" method="GET"'); ?>
					<div class="row">
                       <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="cat"><?= lang("products"); ?></label>
                                <?php
								$cat[""] = "ALL";
                                foreach ($products as $product){
                                    $cat[$product->id] = $product->name;
                                }
                                echo form_dropdown('product', $cat, (isset($_GET['product']) ? $_GET['product'] : ''), 'class="form-control" id="product" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("producte") . '"');
                                ?>
								
                            </div>
                        </div>
                        
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = "ALL";
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_GET['category']) ? $_GET['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>

                            </div>
                        </div>
						<!-- <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("from_date", "from_date"); ?>
                                <?php echo form_input('from_date', (isset($_GET['from_date']) ? $_GET['from_date'] : ''), 'class="form-control date" id="from_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("to_date", "to_date"); ?>
                                <?php echo form_input('to_date', (isset($_GET['to_date']) ? $_GET['to_date'] : ''), 'class="form-control date" id="to_date"'); ?>
                            </div>
                        </div>		
					-->
						
						</div>
					<div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary sub"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
					
                </div>
                <div class="clearfix"></div>
				
                <div class="table-responsive" style="width:100%;overflow:auto;">
                    <table id="tbstock" class="table table-condensed table-bordered table-hover table-striped" >
                        <thead>
							<tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkth" type="checkbox" name="val" />
								</th>							
								<th><?= lang("product_code") ?></th>
								<th><?= lang("product_name") ?></th>
								<?php
								if(is_array($warefull)){
									foreach($warefull as $w){
										echo "<th>".$w->name."</th>";
									}
								}
								
								?>
								<th><?= lang("total") ?></th>
							</tr>
							
						</thead>
                        <tbody>
						<?php 
							$total_q = 0;
							$str = "";
							$tt_qty= array() ;
							$tt_qty_p= array() ;
							foreach($products_details as $pro){
								if($pro->uname){
									$str= "(".$pro->uname.")";
								}else{
									$str  = "";
								}
						?>
							<tr>
							    <td style="min-width:30px; width: 30px; text-align: center;background-color:#E9EBEC">
									<input type="checkbox" name="val[]" class="checkbox multi-select input-xs" value="<?= $pro->id; ?>" />
								</td>
								<td><?=$pro->code?></td>
								<td><?=$pro->name." ".$str?></td>
								<?php
								if(is_array($warefull)){
									foreach($warefull as $w){
										$qty = $this->reports_model->getQtyByWare($pro->id,$w->id,$product2,$category2);
										$tt_qty[$w->id] += $qty->wqty;
										$tt_qty_p[$pro->id] += $qty->wqty;
										echo "<td  class='text-right'>".$this->erp->formatDecimal($qty->wqty)."</td>";
									}
								}
								?>
								
								<?php 
									echo "<td class='text-right'><b>".$this->erp->formatDecimal($tt_qty_p[$pro->id])."</b></td>";
								?>
							</tr>
						<?php
							$total_q+=$tt_qty_p[$pro->id];
							}
						?>
						</tbody>
						<tfoot>
							<tr>
							    <td style="min-width:5%; width: 5%; text-align: center;background-color: #428BCA;">
                                  <input class="checkbox checkth" type="checkbox" name="check"/>
                                </td>
								<td colspan="2" style='background-color: #428BCA;color:white;text-align:right;'><b><?= lang("total") ?></b></td>
								<?php
								if(is_array($warefull)){
									foreach($warefull as $w){	
										echo "<td style='background-color: #428BCA;color:white;text-align:right;'><b>".$this->erp->formatDecimal($tt_qty[$w->id])."</b></td>";
									}
								}
								?>
								<td style='background-color: #428BCA;color:white;text-align:right;'><b><?=$this->erp->formatDecimal($total_q)?></b></td>
							</tr>
                        </tfoot>                       
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

<script type="text/javascript">
$(document).ready(function () {

	$(document).on('focus','.date-year', function(t) {
			$(this).datetimepicker({
				format: "yyyy",
				startView: 'decade',
				minView: 'decade',
				viewSelect: 'decade',
				autoclose: true,
			});
	});
    $('#form').hide();
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