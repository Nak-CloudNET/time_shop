<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-6">
                <div class="small-box padding1010 col-sm-4 bblue">
                    <h3><?= isset($sales->total_amount) ? $this->erp->formatMoney($sales->total_amount) : '0.00' ?></h3>

                    <p><?= lang('sales_amount') ?></p>
                </div>
                <div class="small-box padding1010 col-sm-4 bdarkGreen">
                    <h3><?= isset($sales->paid) ? $this->erp->formatMoney($sales->paid) : '0.00' ?></h3>

                    <p><?= lang('total_paid') ?></p>
                </div>
                <div class="small-box padding1010 col-sm-4 borange">
                    <h3><?= (isset($sales->total_amount) || isset($sales->paid)) ? $this->erp->formatMoney($sales->total_amount - $sales->paid) : '0.00' ?></h3>

                    <p><?= lang('due_amount') ?></p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
				
					<div class="col-sm-3">
                        <div class="small-box padding1010 bblue">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $total_deposits ?></h3>

                                    <p><?= lang('total_deposits') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
				
                    <div class="col-sm-3">
                        <div class="small-box padding1010 bblue">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $total_sales ?></h3>

                                    <p><?= lang('total_sales') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="small-box padding1010 blightBlue">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $total_quotes ?></h3>

                                    <p><?= lang('total_quotes') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="small-box padding1010 borange">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $total_returns ?></h3>

                                    <p><?= lang('total_returns') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<ul id="myTab" class="nav nav-tabs">
    <li class=""><a href="#sales-con" class="tab-grey"><?= lang('sales') ?></a></li>
    <li class=""><a href="#payments-con" class="tab-grey"><?= lang('payments') ?></a></li>
    <li class=""><a href="#quotes-con" class="tab-grey"><?= lang('quotes') ?></a></li>
    <li class=""><a href="#returns-con" class="tab-grey"><?= lang('return_sales') ?></a></li>
	<li class=""><a href="#deposits-con" class="tab-grey"><?= lang('deposits') ?></a></li>
	<li class=""><a href="#products-con" class="tab-grey"><?= lang('products') ?></a></li>
</ul>

<div class="tab-content">
    <div id="sales-con" class="tab-pane fade in">
        <?php
			$v = "&customer=" . $user_id;

			if ($this->input->post('submit_sale_report')) {
				if ($this->input->post('biller')) {
					$v .= "&biller=" . $this->input->post('biller');
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
				if ($this->input->post('start_date')) {
					$v .= "&start_date=" . $this->input->post('start_date');
				}
				if ($this->input->post('end_date')) {
					$v .= "&end_date=" . $this->input->post('end_date');
				}	
			}
        ?>
        <script>
			$(document).ready(function () {
				var date_c = '<?= $date ?>';
				var oTable = $('#SlRData').dataTable({
					"aaSorting": [[0, "desc"]],
					"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
					"iDisplayLength": <?= $Settings->rows_per_page ?>,
					'bProcessing': true, 'bServerSide': true,
					'sAjaxSource': '<?= site_url('reports/getSalesReport/?v=1' .$v) ?>',
					'fnServerData': function (sSource, aoData, fnCallback) {
						aoData.push({
							"name": "<?= $this->security->get_csrf_token_name() ?>",
							"value": "<?= $this->security->get_csrf_hash() ?>"
						});
						$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
					},
					"aoColumns": [{"bSortable": false, "mRender": checkbox}, {"mRender": fld}, null, null, null, {
						"bSearchable": false,
						"mRender": pqFormatSaleReports
					}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat},{"mRender": row_status}],
					"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
						var qty = 0, gtotal = 0, paid = 0, balance = 0,costs=0,profit=0;
						for (var i = 0; i < aaData.length; i++) { 
							gtotal += parseFloat(aaData[aiDisplay[i]][7]);
							paid += parseFloat(aaData[aiDisplay[i]][8]);
							balance += parseFloat(aaData[aiDisplay[i]][9]);
							costs += parseFloat(aaData[aiDisplay[i]][10]);
							profit += parseFloat(aaData[aiDisplay[i]][11]);
						}
						var nCells = nRow.getElementsByTagName('th'); 
						nCells[6].innerHTML = currencyFormat(parseFloat(gtotal));
						nCells[7].innerHTML = currencyFormat(parseFloat(paid));
						nCells[8].innerHTML = currencyFormat(parseFloat(balance));
						nCells[9].innerHTML = currencyFormat(parseFloat(costs));
						nCells[10].innerHTML = currencyFormat(parseFloat(profit));
					}
				}).fnSetFilteringDelay().dtFilter([
					{column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
					{column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
					{column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
					{column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
					{column_number: 11, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
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
			});
        </script>

        <div class="box sales-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('customer_sales_report'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
                }
                ?></h2>

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
                            <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                                <i
                                class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                                <i
                                class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                                <i
                                class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?= lang('customize_report'); ?></p>

                        <div id="form">

                            <?php echo form_open("reports/customer_report/" . $user_id); ?>
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                        <?php
                                        $us[""] = "";
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . " " . $user->last_name;
                                        }
                                        echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
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
                                <div class="col-sm-4">
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
                                <?php if($this->Settings->product_serial) { ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('serial_no', 'serial'); ?>
                                            <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                        </div>
                                    </div>
                                    <?php } ?>
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
                                    class="controls"> <?php echo form_submit('submit_sale_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                                </div>
                                <?php echo form_close(); ?>

                        </div>
                            <div class="clearfix"></div>


                        <div class="table-responsive">
							<table id="SlRData" class="table table-bordered table-hover table-striped table-condensed reports-table reports-table">
                                <thead>
                                    <tr>
										<th style="min-width:30px; width: 30px; text-align: center;">
											<input class="checkbox checkth" type="checkbox" name="check"/>
										</th>
                                        <th><?= lang("date"); ?></th>
                                        <th><?= lang("reference_no"); ?></th>
                                        <th><?= lang("biller"); ?></th>
                                        <th><?= lang("customer"); ?></th>
                                        <th><?= lang("product_qty_serial"); ?></th> 
                                        <th><?= lang("grand_total"); ?></th>
                                        <th><?= lang("paid"); ?></th>
                                        <th><?= lang("balance"); ?></th>
										<th><?= lang("costs"); ?></th>
										<th><?= lang("profits"); ?></th>
                                        <th><?= lang("payment_status"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                    </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                    <tr class="active">
                                        <th style="min-width:30px; width: 30px; text-align: center;">
											<input class="checkbox checkth" type="checkbox" name="check"/>
										</th>
										<th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th><?= lang("product_qty_serial"); ?></th> 
                                        <th><?= lang("grand_total"); ?></th>
                                        <th><?= lang("paid"); ?></th>
                                        <th><?= lang("balance"); ?></th>
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

    </div>
    <div id="payments-con" class="tab-pane fade in">

        <?php
			$p = "&customer=" . $user_id;
			if ($this->input->post('submit_payment_report')) {
				if ($this->input->post('pay_user')) {
					$p .= "&user=" . $this->input->post('pay_user');
				}
				if ($this->input->post('pay_start_date')) {
					$p .= "&start_date=" . $this->input->post('pay_start_date');
				}
				if ($this->input->post('pay_end_date')) {
					$p .= "&end_date=" . $this->input->post('pay_end_date');
				}
			}
        ?>
        <script>
        $(document).ready(function () {
            var pb = ['<?=lang('cash')?>', '<?=lang('CC')?>', '<?=lang('Cheque')?>', '<?=lang('paypal_pro')?>', '<?=lang('stripe')?>', '<?=lang('gift_card')?>'];

            function paid_by(x) {
                if (x == 'cash') {
                    return pb[0];
                } else if (x == 'CC') {
                    return pb[1];
                } else if (x == 'Cheque') {
                    return pb[2];
                } else if (x == 'ppp') {
                    return pb[3];
                } else if (x == 'stripe') {
                    return pb[4];
                } else if (x == 'gift_card') {
                    return pb[5];
                } else {
                    return x;
                }
            }

            var oTable = $('#PayRData').dataTable({
                "aaSorting": [[0, "desc"]],
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true, 'bServerSide': true,
                'sAjaxSource': '<?= site_url('reports/getPaymentsReport/?v=1' . $p) ?>',
                'fnServerData': function (sSource, aoData, fnCallback) {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                },
                "aoColumns": [{"bSortable": false, "mRender": checkbox}, {"mRender": fld}, null, null,{"bVisible": false}, null, {"mRender": paid_by}, {"mRender": currencyFormat}, {"mRender": row_status}],
                'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                    var oSettings = oTable.fnSettings();
                    if (aData[7] == 'returned') {
                        nRow.className = "danger";
                    }
                    return nRow;
                },
                "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                    var total = 0;
                    for (var i = 0; i < aaData.length; i++) {
                        total += parseFloat(aaData[aiDisplay[i]][6]);
                    }
                    var nCells = nRow.getElementsByTagName('th');
                    nCells[6].innerHTML = currencyFormat(parseFloat(total));
                }
            }).fnSetFilteringDelay().dtFilter([
                {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                {column_number: 2, filter_default_label: "[<?=lang('payment_ref');?>]", filter_type: "text", data: []},
                {column_number: 3, filter_default_label: "[<?=lang('sale_ref');?>]", filter_type: "text", data: []},
                {column_number: 4, filter_default_label: "[<?=lang('purchase_ref');?>]", filter_type: "text", data: []},
                {column_number: 5, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
                {column_number: 6, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
                {column_number: 8, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
            ], "footer");
        });
        </script>
        <script type="text/javascript">
        $(document).ready(function () {
            $('#payform').hide();
            $('.paytoggle_down').click(function () {
                $("#payform").slideDown();
                return false;
            });
            $('.paytoggle_up').click(function () {
                $("#payform").slideUp();
                return false;
            });
        });
        </script>

        <div class="box payments-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-money nb"></i><?= lang('customer_payments_report'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
                }
                ?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" class="paytoggle_up tip" title="<?= lang('hide_form') ?>">
                                <i
                                class="icon fa fa-toggle-up"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="paytoggle_down tip" title="<?= lang('show_form') ?>">
                                <i
                                class="icon fa fa-toggle-down"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" id="pdf1" class="tip" title="<?= lang('download_pdf') ?>">
                                <i class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xls1" class="tip" title="<?= lang('download_xls') ?>">
                                <i class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image1" class="tip" title="<?= lang('save_image') ?>">
                                <i class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?= lang('customize_report'); ?></p>

                        <div id="payform">

                            <?php echo form_open("reports/customer_report/" . $user_id."/#payments-con"); ?>
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                        <?php
                                        $us[""] = "";
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . " " . $user->last_name;
                                        }
                                        echo form_dropdown('pay_user', $us, (isset($_POST['pay_user']) ? $_POST['pay_user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("start_date", "start_date"); ?>
                                        <?php echo form_input('pay_start_date', (isset($_POST['pay_start_date']) ? $_POST['pay_start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("end_date", "end_date"); ?>
                                        <?php echo form_input('pay_end_date', (isset($_POST['pay_end_date']) ? $_POST['pay_end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div
                                class="controls"> <?php echo form_submit('submit_payment_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                            </div>
                            <?php echo form_close(); ?>

                        </div>
                        <div class="clearfix"></div>

                        <div class="table-responsive">
                            <table id="PayRData"
                            class="table table-bordered table-hover table-striped table-condensed reports-table reports-table">

                            <thead>
                                <tr>
									<th style="min-width:30px; width: 30px; text-align: center;">
										<input class="checkbox checkth" type="checkbox" name="check"/>
									</th>
                                    <th><?= lang("date"); ?></th>
                                    <th><?= lang("payment_ref"); ?></th>
                                    <th><?= lang("sale_ref"); ?></th>
                                    <th><?= lang("purchase_ref"); ?></th>
                                    <th><?= lang("note"); ?></th>
                                    <th><?= lang("paid_by"); ?></th>
                                    <th><?= lang("amount"); ?></th>
                                    <th><?= lang("type"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                            </tbody>
                            <tfoot class="dtFilter">
                                <tr class="active">
									<th style="min-width:30px; width: 30px; text-align: center;">
										<input class="checkbox checkth" type="checkbox" name="check"/>
									</th>
                                    <th></th>
									<th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><?= lang("amount"); ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
		</div>

	</div>
	<div id="quotes-con" class="tab-pane fade in">
		<script type="text/javascript">
		$(document).ready(function () {
			var oTable = $('#QuRData').dataTable({
				"aaSorting": [[0, "desc"]],
				"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
				"iDisplayLength": <?= $Settings->rows_per_page ?>,
				'bProcessing': true, 'bServerSide': true,
				'sAjaxSource': '<?= site_url('reports/getQuotesReport/?v=1&customer='.$user_id) ?>',
				'fnServerData': function (sSource, aoData, fnCallback) {
					aoData.push({
						"name": "<?= $this->security->get_csrf_token_name() ?>",
						"value": "<?= $this->security->get_csrf_hash() ?>"
					});
					$.ajax({
						'dataType': 'json',
						'type': 'POST',
						'url': sSource,
						'data': aoData,
						'success': fnCallback
					});
				},
				"aoColumns": [{"mRender": fld}, null, null, null, {
					"bSearchable": false,
					"mRender": pqFormat
				}, {"mRender": currencyFormat}, {"mRender": row_status}],
			}).fnSetFilteringDelay().dtFilter([
				{column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
				{column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
				{column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
				{column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
				{column_number: 5, filter_default_label: "[<?=lang('grand_total');?>]", filter_type: "text", data: []},
				{column_number: 6, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
			], "footer");
		});
		</script>
		<div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa-fw fa fa-heart-o nb"></i><?=  lang('quotes'); ?>
				</h2>

				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a href="#" id="pdf1" class="tip" title="<?= lang('download_pdf') ?>">
								<i class="icon fa fa-file-pdf-o"></i>
							</a>
						</li>
						<li class="dropdown">
							<a href="#" id="xls1" class="tip" title="<?= lang('download_xls') ?>">
								<i class="icon fa fa-file-excel-o"></i>
							</a>
						</li>
						<li class="dropdown">
							<a href="#" id="image1" class="tip image" title="<?= lang('save_image') ?>">
								<i class="icon fa fa-file-picture-o"></i>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?php echo lang('list_results'); ?></p>

						<div class="table-responsive">
							<table id="QuRData" class="table table-bordered table-hover table-striped table-condensed">
								<thead>
									<tr>
										<th><?= lang("date"); ?></th>
										<th><?= lang("reference_no"); ?></th>
										<th><?= lang("biller"); ?></th>
										<th><?= lang("customer"); ?></th>
										<th><?= lang("product_qty"); ?></th>
										<th><?= lang("grand_total"); ?></th>
										<th><?= lang("status"); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="7"
										class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
									</tr>
								</tbody>
								<tfoot class="dtFilter">
									<tr class="active">
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th><?= lang("product_qty"); ?></th>
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
	</div>
	<div id="returns-con" class="tab-pane fade in">
		<script>
		$(document).ready(function () {
			var oTable = $('#PRESLData').dataTable({
				"aaSorting": [[0, "desc"]],
				"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
				"iDisplayLength": <?= $Settings->rows_per_page ?>,
				'bProcessing': true, 'bServerSide': true,
				'sAjaxSource': '<?= site_url('reports/getReturnsReport/?v=1&customer='.$user_id) ?>',
				'fnServerData': function (sSource, aoData, fnCallback) {
					aoData.push({
						"name": "<?= $this->security->get_csrf_token_name() ?>",
						"value": "<?= $this->security->get_csrf_hash() ?>"
					});
					$.ajax({
						'dataType': 'json',
						'type': 'POST',
						'url': sSource,
						'data': aoData,
						'success': fnCallback
					});
				},
				'fnRowCallback': function (nRow, aData, iDisplayIndex) {
					var oSettings = oTable.fnSettings();
					nRow.id = aData[7];
					nRow.className = "return_link";
					return nRow;
				},
				"aoColumns": [{"mRender": fld}, null, null, null, null, {
					"bSearchable": false,
					"mRender": pqFormat
				}, {"mRender": currencyFormat}, {"mRender": currencyFormat}],
				"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
					var sc = 0, gtotal = 0;
					for (var i = 0; i < aaData.length; i++) {
						sc += parseFloat(aaData[aiDisplay[i]][6]);
						gtotal += parseFloat(aaData[aiDisplay[i]][7]);
					}
					var nCells = nRow.getElementsByTagName('th');
					nCells[6].innerHTML = currencyFormat(parseFloat(sc));
					nCells[7].innerHTML = currencyFormat(parseFloat(gtotal));
				}
			}).fnSetFilteringDelay().dtFilter([
				{column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
				{column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
				{
					column_number: 2,
					filter_default_label: "[<?=lang('sale_reference');?>]",
					filter_type: "text",
					data: []
				},
				{column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
				{column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
			], "footer");
		});
		</script>
		<div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa-fw fa fa-random nb"></i><?= lang('return_sales'); ?>
				</h2>

				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a href="#" id="pdf5" class="tip" title="<?= lang('download_pdf') ?>">
								<i class="icon fa fa-file-pdf-o"></i>
							</a>
						</li>
						<li class="dropdown">
							<a href="#" id="xls5" class="tip" title="<?= lang('download_xls') ?>">
								<i class="icon fa fa-file-excel-o"></i>
							</a>
						</li>
						<li class="dropdown">
							<a href="#" id="image5" class="tip image" title="<?= lang('save_image') ?>">
								<i class="icon fa fa-file-picture-o"></i>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?php echo lang('list_results'); ?></p>

						<div class="table-responsive">
							<table id="PRESLData" class="table table-bordered table-hover table-striped">
								<thead>
									<tr>
										<th><?= lang("date"); ?></th>
										<th><?= lang("reference_no"); ?></th>
										<th><?= lang("sale_reference"); ?></th>
										<th><?= lang("biller"); ?></th>
										<th><?= lang("customer"); ?></th>
										<th class="col-sm-2"><?= lang("product_qty"); ?></th>
										<th class="col-sm-1"><?= lang("surcharges"); ?></th>
										<th class="col-sm-1"><?= lang("grand_total"); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="9" class="dataTables_empty"><?= lang("loading_data"); ?></td>
									</tr>
								</tbody>
								<tfoot class="dtFilter">
									<tr class="active">
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th class="col-sm-2"><?= lang("product_qty"); ?></th>
										<th class="col-sm-1"><?= lang("surcharges"); ?></th>
										<th class="col-sm-1"><?= lang("grand_total"); ?></th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="deposits-con" class="tab-pane fade in">
		<script>
		$(document).ready(function () {
			
			var oTable = $('#DEPData').dataTable({
					"aaSorting": [[1, "asc"]],
					"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
					"iDisplayLength": <?= $Settings->rows_per_page ?>,
					'bProcessing': true, 'bServerSide': true,
					'sAjaxSource': '<?= site_url('reports/getDepositsReport/?v=1&customer='.$user_id) ?>',
					'fnServerData': function (sSource, aoData, fnCallback) {
						aoData.push({
							"name": "<?= $this->security->get_csrf_token_name() ?>",
							"value": "<?= $this->security->get_csrf_hash() ?>"
						});
						$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
					},
					"aoColumns": [{"mRender": fld}, {"mRender": currencyFormat}, null, null, {"bSortable": false}]
			});
		});
		</script>
		<div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa-fw fa fa-random nb"></i><?= lang('deposits'); ?>
				</h2>
				
				<!--
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a href="#" id="pdf5" class="tip" title="<?= lang('download_pdf') ?>">
								<i class="icon fa fa-file-pdf-o"></i>
							</a>
						</li>
						<li class="dropdown">
							<a href="#" id="xls5" class="tip" title="<?= lang('download_xls') ?>">
								<i class="icon fa fa-file-excel-o"></i>
							</a>
						</li>
						<li class="dropdown">
							<a href="#" id="image5" class="tip image" title="<?= lang('save_image') ?>">
								<i class="icon fa fa-file-picture-o"></i>
							</a>
						</li>
					</ul>
				</div>
				-->
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?php echo lang('list_results'); ?></p>

						<div class="table-responsive">
							<table id="DEPData" class="table table-bordered table-hover table-striped">
								<thead>
								<tr class="primary">
									<th class="col-xs-3"><?= lang("date"); ?></th>
									<th class="col-xs-2"><?= lang("amount"); ?></th>
									<th class="col-xs-3"><?= lang("paid_by"); ?></th>
									<th class="col-xs-3"><?= lang("created_by"); ?></th>
									<th style="width:85px;"><?= lang("actions"); ?></th>
								</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="products-con" class="tab-pane fade in">
		<?php
			$p = "&customer=" . $user_id;

			if ($this->input->post('submit_sale_report')) {
				if ($this->input->post('start_date')) {
					$p .= "&start_date=" . $this->input->post('start_date');
				}
				if ($this->input->post('end_date')) {
					$p .= "&end_date=" . $this->input->post('end_date');
				}	
			}
        ?>
		<script>
		$(document).ready(function () {
			
			var oTable = $('#PRPData').dataTable({
					"aaSorting": [[1, "asc"]],
					"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
					"iDisplayLength": <?= $Settings->rows_per_page ?>,
					'bProcessing': true, 'bServerSide': true,
					'sAjaxSource': '<?= site_url('reports/getProductsReports/?v=1'.$p) ?>',
					'fnServerData': function (sSource, aoData, fnCallback) {
						aoData.push({
							"name": "<?= $this->security->get_csrf_token_name() ?>",
							"value": "<?= $this->security->get_csrf_hash() ?>"
						});
						$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
					},
					"aoColumns": [
						{"bSortable": false, "mRender": checkbox}, {"bSortable": false,"mRender": img_hl}, null, null, null, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": formatQuantity}
					],
					"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
						var co = 0, pr = 0, qty = 0;
						for (var i = 0; i < aaData.length; i++) {
							co += parseFloat(aaData[aiDisplay[i]][5]);
							pr += parseFloat(aaData[aiDisplay[i]][6]);
							qty += parseFloat(aaData[aiDisplay[i]][7]);
						}
						var nCells = nRow.getElementsByTagName('th');
						nCells[5].innerHTML = currencyFormat(parseFloat(co));
						nCells[6].innerHTML = currencyFormat(parseFloat(pr));
						nCells[7].innerHTML = currencyFormat(parseFloat(qty));
					}
			}).fnSetFilteringDelay().dtFilter([
				{column_number: 2, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
				{column_number: 3, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
				{column_number: 4, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
			], "footer");
		});
		</script>
		<script type="text/javascript">
			$(document).ready(function () {
				$('#proform').hide();
				$('.toggle_down').click(function () {
					$("#proform").slideDown();
					return false;
				});
				$('.toggle_up').click(function () {
					$("#proform").slideUp();
					return false;
				});
			});
        </script>
		<div class="box">
			<div class="box-header">
				<h2 class="blue"><i class="fa-fw fa fa-barcode nb"></i><?= lang('products'); ?>
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
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?php echo lang('list_results'); ?></p>

						<div id="proform">

                            <?php echo form_open("reports/customer_report/" . $user_id); ?>
                            <div class="row">
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
								class="controls"> <?php echo form_submit('submit_sale_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
							</div>
							<?php echo form_close(); ?>

                        </div>
						<div class="clearfix"></div>
						
						<div class="table-responsive">
							<table id="PRPData" class="table table-bordered table-hover table-striped">
								<thead>
								<tr class="primary">
									<th style="min-width:30px; width: 30px; text-align: center;">
										<input class="checkbox checkth" type="checkbox" name="check"/>
									</th>
									<th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
									<th><?= lang("product_code") ?></th>
									<th><?= lang("product_name") ?></th>
									<th><?= lang("category") ?></th>
									<th><?= lang("total_cost");?></th>
									<th><?= lang("total_price");?></th>
									<th><?= lang("quantity") ?></th>
								</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
	</div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
	$(document).ready(function () {
		$('#pdf').click(function (event) {
			event.preventDefault();
			window.location.href = "<?=site_url('reports/getSalesReport/pdf/?v=1'.$v)?>";
			return false;
		});
		$('#xls').click(function (event) {
			event.preventDefault();
			window.location.href = "<?=site_url('reports/getSalesReport/0/xls/?v=1'.$v)?>";
			return false;
		});
		$('#image').click(function (event) {
			event.preventDefault();
			html2canvas($('.sales-table'), {
				onrendered: function (canvas) {
					var img = canvas.toDataURL()
					window.open(img);
				}
			});
			return false;
		});
		$('#pdf1').click(function (event) {
			event.preventDefault();
			window.location.href = "<?=site_url('reports/getPaymentsReport/pdf/?v=1'.$p)?>";
			return false;
		});
		$('#xls1').click(function (event) {
			event.preventDefault();
			window.location.href = "<?=site_url('reports/getPaymentsReport/0/xls/?v=1'.$p)?>";
			return false;
		});
		$('#image1').click(function (event) {
			event.preventDefault();
			html2canvas($('.payments-table'), {
				onrendered: function (canvas) {
					var img = canvas.toDataURL()
					window.open(img);
				}
			});
			return false;
		});
	});
</script>
