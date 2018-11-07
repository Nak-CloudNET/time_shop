<?php

$v = "";

if ($this->input->post('brand')) {
    $v .= "&brand=" . $this->input->post('brand');
} 
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
if (isset($biller_id)) {
    $v .= "&biller_id=" . $biller_id;
}

?>

<script>
    $(document).ready(function () {
        var oTable = $('#SlRDatas').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getbrands/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
			"bAutoWidth": true,
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, {"mRender": fld},null, {"mRender": formatQuantity}, {"mRender": currencyFormat}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay){
                var balance = 0, qtotal = 0;
                for (var i = 0; i < aaData.length; i++){ 
					
                    qtotal += parseFloat(aaData[aiDisplay[i]][3]); 
					balance += parseFloat(aaData[aiDisplay[i]][4]); 
                }
                var nCells = nRow.getElementsByTagName('th'); 
               nCells[3].innerHTML = formatQuantity(parseFloat(qtotal)); 
			   nCells[4].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('brands');?>]", filter_type: "text", data: []},
             
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
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
                }
            },
			$('#customer').val(<?= $this->input->post('customer') ?>);
        });

        //$('#customer').val(<?= $this->input->post('customer') ?>);
        <?php } ?>
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		$('#excel').click(function(){
		    var date1 =	$('#start_date').val(); 
            var date2 =	$('#end_date').val();  
            $('#start').val(date1);
            $('#end').val(date2);			
		});
		$('#pdf').click(function(){
		    var date1 =	$('#start_date').val(); 
            var date2 =	$('#end_date').val();  
            $('#start').val(date1);
            $('#end').val(date2);			
		});
    });
</script>

<?php if ($Owner) {
    echo form_open('reports/brand_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('brand_sales_daily'); ?><?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?></h2>
        <input type="hidden" name="starts" value='' id="start">
		<input type="hidden" name="ends" value='' id="end">
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
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i
							class="icon fa fa-building-o tip" data-placement="left"
							title="<?= lang("billers") ?>"></i></a>
					<ul class="dropdown-menu pull-right" class="tasks-menus" role="menu"
						aria-labelledby="dLabel">
						<li><a href="<?= site_url('reports/sales') ?>"><i
									class="fa fa-building-o"></i> <?= lang('billers') ?></a></li>
						<li class="divider"></li>
						<?php
						foreach ($billers as $biller) {
							echo '<li ' . ($biller_id && $biller_id == $biller->id ? 'class="active"' : '') . '><a href="' . site_url('reports/sales/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '</a></li>';
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
    <?= form_close() ?>
<?php } ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form">

                    <?php echo form_open("reports/getsale_brand_report"); ?>
                    <div class="row">
                          
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("brand"); ?></label>
                                <?php
                                $b[""] = "";
                                foreach ($brands as $brand){
                                  $b[$brand->id] =$brand->name;  
                                }
                                echo form_dropdown('brand', $b, (isset($_POST['brand']) ? $_POST['user'] : ""), 'class="form-control" id="brand" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("brand") . '"');
                                ?>
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
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary" id="btn1"'); ?> </div>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                    

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="SlRDatas"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
							<th style=" text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("brands"); ?></th>
                            <th style="width:150px;"><?= lang("quantity"); ?></th>
                            <th style="width:200px;"><?= lang("balance"); ?></th> 
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
							<th style=" text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th> 
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
    });
</script>