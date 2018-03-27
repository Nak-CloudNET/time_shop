<script>

	$(document).ready(function(){
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

<?php
	echo form_open('reports/saleReportDetail_actions', 'id="action-form"');
?>
<div class="box">
	<div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('sales_report_detail'); ?></h2>   
		<div class="box-icon" style="">
            <div class="form-group choose-date hidden-xs">
                <div class="controls">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input type="text"
								   value="<?= ($start ? $this->erp->hrld($start) : '') . ' - ' . ($end ? $this->erp->hrld($end) : ''); ?>"
								   id="daterange" class="form-control">
						<span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>
                    </div>
                </div>
            </div>
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
					<li class="dropdown"><a href="#" id="pdf" data-action="export_pdf" class="tip" title="<?= lang('download_pdf') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li>
					<li class="dropdown"><a href="#" id="excel" data-action="export_excel" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i
								class="icon fa fa-building-o tip" data-placement="left"
								title="<?= lang("billers") ?>"></i></a>
						<ul class="dropdown-menu pull-right" class="tasks-menus" role="menu"
							aria-labelledby="dLabel">
							<li><a href="<?= site_url('reports/getSalesReportDetail/'.$start.'/'.$end) ?>"><i
										class="fa fa-building-o"></i> <?= lang('billers') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($billers as $biller) {
								echo '<li ' . ($biller_id && $biller_id == $biller->id ? 'class="active"' : '') . '><a href="' . site_url('reports/getSalesReportDetail/'.$start.'/'.$end.'/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '</a></li>';
							}
							?>
						</ul>
					</li>
				</ul>
			</div>			
		</div>
    </div>
	<?php if ($Owner) { ?>
		<div style="display: none;">
			<input type="hidden" name="form_action" value="" id="form_action"/>
			<?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
		</div>
		<?php echo form_close(); ?>
	<?php } ?>
	<div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('customize_report'); ?></p>
                <div id="form">
                    <?php echo form_open("reports/getSalesReportDetail"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat['0'] = lang("all");
                                foreach ($cate as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category_name', $cat, (isset($_POST['category_name']) ? $_POST['category_name'] : ''), 'class="form-control select" id="category_name" placeholder="' . lang("select") . " " . lang("category_name") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product_name", "product_name") ?>
                                <?php
                                $pro['0'] = lang("all");
                                foreach ($products as $product) {
                                    $pro[$product->id] = $product->name;
                                }
                                echo form_dropdown('product_name', $pro, (isset($_POST['product_name']) ? $_POST['product_name'] : ''), 'class="form-control select" id="category_name" placeholder="' . lang("select") . " " . lang("product_name") . '" style="width:100%"')
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

            </div>
        </div>
    </div>
	<div class="box-content">
        <div class="row">
		    <div class="col-lg-12" style="margin-top: -46px;">
			<?php 
				foreach($categories as $category)
				{					
			?>					
					<div style="width:25%;border:1px solid #a9c8d6; height: 34px;padding-top:7px;background-color:#D9EDEF;padding-left:5px;"><input type="checkbox" name="check[]" value="<?= $category->id; ?>"/> <span style="color:blue;padding-left:10px;"><b><?= strtoupper($category->name);?></b></span></div>   
					<table class="table table-bordered table-hover table-striped table-condensed">
						<thead>
							<tr>						
								<th style="width: 227px;"><?php echo $this->lang->line("product_name"); ?></th>
								
								<?php foreach($warehouse as $w){ ?>
									<th><?= $w->code ?></th>
								<?php } ?>
								
								<th><?= $this->lang->line("qty_sale"); ?></th>
								<th><?= $this->lang->line("unit_cost"); ?></th>
								<th><?= $this->lang->line("unit_price"); ?></th>
								<th><?= $this->lang->line('dis_by_item');?></th>
								<th><?= $this->lang->line("revenue"); ?></th>
								<th><?= $this->lang->line("coms"); ?></th>
								<th><?= $this->lang->line("profit"); ?></th>                       
							</tr>
						</thead>
						<tbody>					 
							<?= $this->reports_model->getDataReportDetail($category->id)?>
						</tbody>
					</table>
			<?php				
				}	
			?>
            </div>
        </div>
    </div>
</div>