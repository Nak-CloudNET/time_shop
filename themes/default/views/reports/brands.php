<?php 
if ($Owner) {
    echo form_open('reports/suppliers_actions', 'id="action-form"');
} 
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('brands_report'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="pdf" data-action="export_pdf"  class="tip" title="<?= lang('download_pdf') ?>"><i
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
                <div class="table-responsive">
				    <table width="20%" style="margin-bottom:0px;">
					    <tbody>
						    <?php foreach($brand as $row){?>
						        <tr>
								    <td style="padding-left:30px;background-color:#F4F5F5;height:40px;"><?= $row->brand; ?></td>
								</tr>
							<?php }?>
						</tbody>
					</table>
                    <table id="CusData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-condensed table-hover table-striped reports-table">
                        
						<thead>
							<tr class="primary">
								<th style="text-align: center;">
									<input class="checkbox checkth" type="checkbox" name="check"/>
								</th>
								<th><?= lang("p.category"); ?></th>
								<th><?= lang("product_line"); ?></th>
								<th><?= lang("product_name"); ?></th>
								<th><?= lang("serial"); ?></th>
								<th><?= lang("quantity"); ?></th>
								<th><?= lang("unit"); ?></th>
								<th><?= lang("total"); ?></th>
							</tr>
                        </thead>
                        <tbody>
						 <?php foreach($brands as $row) {?>
							<tr>
							    <td class="text-center"><input class="checkbox checkth" type="checkbox" name="check"/></td>
								<td><?= $row->category?></td>
								<td><?= $row->subcate?></td>
								<td><?= $row->product_name?>(<?= $row->product_code?>)</td>
								<td><?= $row->serial_number?></td>
								<td><?= $row->quantity?></td>
								<td><?= $row->unit_price?></td>
								<td><?= $row->subtotal?></td>
							</tr>
						 <? }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
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