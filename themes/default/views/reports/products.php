<?php
$v = "";

if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
}
if ($this->input->post('supplier')) {
    $v .= "&supplier=" . $this->input->post('supplier');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
if ($this->input->post('cf1')) {
    $v .= "&cf1=" . $this->input->post('cf1');
}
if ($this->input->post('cf2')) {
    $v .= "&cf2=" . $this->input->post('cf2');
}
if ($this->input->post('cf3')) {
    $v .= "&cf3=" . $this->input->post('cf3');
}
if ($this->input->post('cf4')) {
    $v .= "&cf4=" . $this->input->post('cf4');
}
if ($this->input->post('cf5')) {
    $v .= "&cf5=" . $this->input->post('cf5');
}
if ($this->input->post('cf6')) {
    $v .= "&cf6=" . $this->input->post('cf6');
}
if (isset($biller_id)) {
    $v .= "&biller_id=" . $biller_id;
}
?>
<script>
    $(document).ready(function () {
        function spb(x) {
            v = x.split('__');
            return formatQuantity2(v[0]);


            //return '('+formatQuantity2(v[0])+') <strong>'+formatMoney(v[1])+'</strong>'kk;
        }
        var oTable = $('#PrRData').dataTable({
            "aaSorting": [[2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getProductsReport/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null,null,null, null, {"mRender": spb}, {"mRender": spb}, {"mRender": currencyFormat}, {"mRender": spb},null],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var pq = 0, sq = 0, bq = 0, pa = 0, sa = 0, ba = 0, pl = 0;
                for (var i = 0; i < aaData.length; i++) {
                    p = (aaData[aiDisplay[i]][5]).split('__');
                    s = (aaData[aiDisplay[i]][6]).split('__');
                    b = (aaData[aiDisplay[i]][8]).split('__');
                    pq += parseFloat(p[0]);
                    pa += parseFloat(p[1]);
                    sq += parseFloat(s[0]);
                    sa += parseFloat(s[1]);
                    bq += parseFloat(b[0]);
                    ba += parseFloat(b[1]);
                    pl += parseFloat(aaData[aiDisplay[i]][7]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = '<div class="text-right">'+formatQuantity2(pq)+' '+'</div>';
                nCells[6].innerHTML = '<div class="text-right">'+formatQuantity2(sq)+' '+'</div>';
                nCells[7].innerHTML = currencyFormat(parseFloat(pl));
                nCells[8].innerHTML = '<div class="text-right">'+formatQuantity2(bq)+''+'</div>';
               // nCells[6].innerHTML = '<div class="text-right">('+formatQuantity2(bq)+') '+formatMoney(ba)+'</div>';
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('brand');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('product_category');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},

        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
        $("#product").autocomplete({
            source: '<?= site_url('reports/suggestions'); ?>',
            select: function (event, ui) {
                $('#product_id').val(ui.item.id);
                //$(this).val(ui.item.label);
            },
            minLength: 1,
            autoFocus: false,
            delay: 300,
        });
    });
</script>
<?php if ($Owner) {
    echo form_open('reports/products_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('products_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
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
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i
							class="icon fa fa-building-o tip" data-placement="left"
							title="<?= lang("billers") ?>"></i></a>
					<ul class="dropdown-menu pull-right" class="tasks-menus" role="menu"
						aria-labelledby="dLabel">
						<li><a href="<?= site_url('reports/products') ?>"><i
									class="fa fa-building-o"></i> <?= lang('billers') ?></a></li>
						<li class="divider"></li>
						<?php
						foreach ($billers as $biller) {
							echo '<li ' . ($biller_id && $biller_id == $biller->id ? 'class="active"' : '') . '><a href="' . site_url('reports/products/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '</a></li>';
						}
						?>
					</ul>
				</li>
            </ul>
        </div>
		
		
		
    </div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?php form_close(); ?>
<?php } ?>
<?php 
?>    
	<div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo form_open("reports/products"); ?>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("product", "product"); ?>
                                    <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="product"'); ?>
                                    <input type="hidden" name="product"
                                           value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>"
                                           id="product_id"/>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("category", "category") ?>
                                    <?php
                                        $cat[''] = "";
                                        foreach ($categories as $category) {
                                            $cat[$category->id] = $category->name;
                                        }
                                        echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("supplier", "supplier") ?>
                                    <?php
                                        $sup[''] = "";
                                        foreach ($suppliers as $supplier) {
                                            $sup[$supplier->id] = $supplier->name;
                                        }
                                        echo form_dropdown('supplier', $sup, (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control select" id="suppliers" placeholder="' . lang("select") . " " . lang("supplier") . '" style="width:100%"')
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang('pcf1', 'cf1') ?>
                                    <?= form_input('cf1', (isset($_POST['cf1']) ? $_POST['cf1'] : ''), 'class="form-control tip" id="cf1"') ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang('pcf2', 'cf2') ?>
                                    <?= form_input('cf2', (isset($_POST['cf2']) ? $_POST['cf2'] : ''), 'class="form-control tip" id="cf2"') ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang('pcf3', 'cf3') ?>
                                    <?= form_input('cf3', (isset($_POST['cf3']) ? $_POST['cf3'] : ''), 'class="form-control tip" id="cf3"') ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang('pcf4', 'cf4') ?>
                                    <?= form_input('cf4', (isset($_POST['cf4']) ? $_POST['cf4'] : ''), 'class="form-control tip" id="cf4"') ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang('pcf5', 'cf5') ?>
                                    <?= form_input('cf5', (isset($_POST['cf5']) ? $_POST['cf5'] : ''), 'class="form-control tip" id="cf5"') ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang('pcf6', 'cf6') ?>
                                    <?= form_input('cf6', (isset($_POST['cf6']) ? $_POST['cf6'] : ''), 'class="form-control tip" id="cf6"') ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("start_date", "start_date"); ?>
                                    <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang("end_date", "end_date"); ?>
                                    <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                        </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="PrRData"
                           class="table table-striped table-bordered table-condensed table-hover dfTable reports-table"
                           style="margin-bottom:5px;">
                        <thead>
                        <tr class="active">
							<th style="min-width:5%; width: 5%; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("brand"); ?></th>
                            <th><?= lang("product_category"); ?></th>
                            <th><?= lang("product_code"); ?></th>
                            <th><?= lang("product_name"); ?></th>
                            <th><?= lang("purchased"); ?></th>
                            <th><?= lang("sold"); ?></th>
                            <th><?= lang("profit_loss"); ?></th>
                            <th><?= lang("stock_in_hand"); ?></th>
							<th><?= lang("action"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
							<th style="min-width:5%; width: 5%; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                        <th></th>
                        <th></th>
                            <th></th>
                            <th></th>
                        <th><?= lang("purchased"); ?></th>
                        <th><?= lang("sold"); ?></th>
                        <th><?= lang("profit_loss"); ?></th>
                        <th><?= lang("stock_in_hand"); ?></th>
                        <th><?= lang("action"); ?></th>
                        </tr>
                        </tfoot>
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
		$('.datetime').datetimepicker({
			format: site.dateFormats.js_ldate, 
			fontAwesome: true, 
			language: 'sma', 
			weekStart: 1, 
			todayBtn: 1, 
			autoclose: 1, 
			todayHighlight: 1, 
			startView: 2, 
			forceParse: 0
		});
    });
</script>
