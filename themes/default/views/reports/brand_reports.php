<style>
	#tbstock .shead th{
		background-color: #428BCA;border-color: #357EBD;color:white;text-align:center;
	}

</style>
<script>
    $(document).ready(function () {
        function spb(x) {
            v = x.split('__');
            return '('+formatQuantity2(v[0])+') <strong>'+formatMoney(v[1])+'</strong>';
        }
        var oTable = $('#tbstock').dataTable({
            "aaSorting": [[2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getbrandReport2/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null, null,null,null],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {

            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php 
	if ($Owner) {
	   echo form_open('reports/warehouse_reports_action' ,'id="action-form"');
	} 
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('brand_reports') ; ?>
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
								<th><?= lang("brand_name") ?></th>
								<th><?= lang("category") ?></th>
								<th><?= lang("quantity") ?></th>
								<th><?= lang("action") ?></th>
							</tr>
							
						</thead>
                        <tbody>
							<tr>
								<td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
							    <th></th>							
								<th></th>
								<th></th>
								<th></th>
								<th></th>
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