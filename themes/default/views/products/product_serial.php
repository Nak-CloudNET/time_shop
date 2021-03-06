<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>
    $(document).ready(function () {
		var lang = {'1': '<?=lang('in_stock');?>', '0': '<?=lang('sold_out')?>', '2': '<?= lang('pending') ?>'}
		function status(x){
			if(x == 1){
				return '<div class="text-center"><span class="label label-primary">'+lang[x]+'</span></div>';
			}else{
				return '<div class="text-center"><span class="label label-warning">'+lang[x]+'</span></div>';
			}
		}
        oTable = $('#dmpData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getProductSerial'); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            },null, null, null, null, null, null, {'mRender': status}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([			
			{column_number: 1, filter_default_label: "[<?=lang('brand');?>]", filter_type: "text", data: []},		
            {column_number: 2, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('warehouse');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('serial_number');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");

        /*$('#pdf').click(function (e) {
            e.preventDefault();
            window.location.href = "<?=site_url('products/getadjustments/pdf')?>";
            return false;
        });
        $('#xls').click(function (e) {
            e.preventDefault();
            window.location.href = "<?=site_url('products/getadjustments/0/xls/')?>";
            return false;
        });*/
        $('#image').click(function (e) {
            var box = $(this).closest('.box');
            e.preventDefault();
            html2canvas(box, {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>
<?php 
if ($Owner) {
    echo form_open('products/product_serial_actions'.($warehouse_id ? '/'.$warehouse_id : ''), 'id="action-form"');
} 
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-file-text"></i><?= lang('product_serial'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" data-action="export_pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
				<li class="dropdown">
                    <a href="#" id="excel" data-action="export_excel" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip image" itle="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="dmpData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr>
							<th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
							<th class="col-xs-2"><?= lang("brand"); ?></th>						
                            <th class="col-xs-2"><?= lang("product_code"); ?></th>
                            <th class="col-xs-2"><?= lang("product_name"); ?></th>
                            <th class="col-xs-2"><?= lang("biller"); ?></th>
                            <th class="col-xs-1"><?= lang("warehouse"); ?></th>
                            <th class="col-xs-2"><?= lang("serial_number"); ?></th>
                            <th class="col-xs-1"><?= lang("status"); ?></th>
                            <th class="col-xs-1" style="text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
							<th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
                            <th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>