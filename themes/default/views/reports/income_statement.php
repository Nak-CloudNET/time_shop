<script>$(document).ready(function () {
        CURI = '<?= site_url('reports/income_statement'); ?>';
    });</script>
<style>@media print {
        .fa {
            color: #EEE;
            display: none;
        }

        .small-box {
            border: 1px solid #CCC;
        }
    }</style>
<?php
	$start_date=date('Y-m-d',strtotime($start));
	$rep_space_end=str_replace(' ','_',$end);
	$end_date=str_replace(':','-',$rep_space_end);

?>
<?php if ($Owner) {
    //echo form_open('reports/income_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-bars"></i><?= lang('income_statement'); ?></h2>

        <div class="box-icon">
            <div class="form-group choose-date hidden-xs">
                <div class="controls">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" value="<?= ($start ? $this->erp->hrld($start) : '') . ' - ' . ($end ? $this->erp->hrld($end) : ''); ?>"
                               id="daterange" class="form-control">
                        <span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
				<li class="dropdown"><a href="#" id="xls" data-action="export_excel" class="tip" title="<?= lang('download_excel') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="pdf" data-action="export_pdf" class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i
							class="icon fa fa-building-o tip" data-placement="left"
							title="<?= lang("billers") ?>"></i></a>
					<ul class="dropdown-menu pull-right" class="tasks-menus" role="menu"
						aria-labelledby="dLabel">
						<li><a href="<?= site_url('reports/income_statement') ?>"><i
									class="fa fa-building-o"></i> <?= lang('billers') ?></a></li>
						<li class="divider"></li>
						<?php
							foreach ($billers as $biller) {
								echo '<li ' . ($biller_id && $biller_id == $biller->id ? 'class="active"' : '') . '><a href="' . site_url('reports/income_statement/'.$start.'/'.$end.'/0/0/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '</a></li>';
							}
						?>
					</ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="SupData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered">
						<thead>
                        <tr class="primary">
                            
                            <th style="width:60%;text-align:left;" colspan="2"><?= lang("account_name"); ?></th>
							<th style="width:20%;"><?= lang("amount"); ?></th>
							<th style="width:20%;"><?= lang("total"); ?></th>
                            <th style="width:20%;"><?= lang("total") . ' ('.$totalBeforeAyear.')'; ?></th>
                        </tr>
                        </thead>
                        <thead>
                        <tr class="primary">
                            
                            <th style="width:40%;text-align:left;" colspan="3"><?= lang("income"); ?></th>
							
                        </tr>
                        </thead>
                        <tbody>
						<?php
							$total_income = 0;
                            $totalBeforeAyear_income = 0;
							foreach($dataIncome->result() as $row){
							$total_income += $row->amount;

                            $query = $this->db->query("SELECT
                                sum(erp_gl_trans.amount) AS amount
                            FROM
                                erp_gl_trans
                            WHERE
                                DATE(tran_date) = '$totalBeforeAyear' AND account_code = '" . $row->account_code . "';");
                            $totalBeforeAyearRows = $query->row();
                            $totalBeforeAyear_income += $totalBeforeAyearRows->amount;

						?>
							<tr>
								<td colspan="2" style="padding-left:30px"><?php echo $row->account_code;?> - <?php echo $row->accountname;?></td>
								<td><span class="pull-right"><?php echo number_format(abs($row->amount),2);?></span></td>
                                <td></td>
                                <td><span class="pull-right"><?php echo number_format(abs($totalBeforeAyearRows->amount),2);?></span></td>
							</tr>
						<?php
							}
						?>
							<tr>
							<td colspan="3"><?= lang("total_income"); ?></td>
							<td><span class="pull-right"><?php echo number_format((-1)*($total_income),2);?></span></td>
                            <td><span class="pull-right"><?php echo number_format((-1)*($totalBeforeAyear_income),2);?></span></td>
							</tr>
						                        
                        </tbody>
						
						<thead>
                        <tr class="primary">
                            
                            <th style="width:20%;text-align:left;" colspan="3"><?= lang("cost"); ?></th>
                       
                        </tr>
                        </thead>
                        <tbody>
						
						<?php
							$total_cost = 0;
                            $totalBeforeAyear_cost = 0;
							foreach($dataCost->result() as $rowcost){
							$total_cost += $rowcost->amount;

                            $query = $this->db->query("SELECT
                                sum(erp_gl_trans.amount) AS amount
                            FROM
                                erp_gl_trans
                            WHERE
                                DATE(tran_date) = '$totalBeforeAyear' AND account_code = '" . $rowcost->account_code . "';");
                            $totalBeforeAyearRows = $query->row();
                            $totalBeforeAyear_cost += $totalBeforeAyearRows->amount;
						?>
							<tr>
								<td colspan="2" style="padding-left:30px"><?php echo $rowcost->account_code;?> - <?php echo $rowcost->accountname;?></td>
								<td><span class="pull-right"><?php echo number_format(abs($rowcost->amount),2);?></span></td>
                                <td></td>
                                <td><span class="pull-right"><?php echo number_format(abs($totalBeforeAyearRows->amount),2);?></span></td>
							</tr>
						<?php
							}
						?>
							<tr>
							<td colspan="3"><?= lang("total_cost"); ?></td>
							<td><span class="pull-right"><?php echo number_format((-1)*$total_cost,2);?></span></td>
                            <td><span class="pull-right"><?php echo number_format((-1)*$totalBeforeAyear_cost,2);?></span></td>
							</tr>							
							<tr>
							<td colspan="3"><?= lang("gross_margin"); ?></td>
							<td><strong><span class="pull-right"><?php echo number_format((-1)*($total_cost+$total_income),2);?></span></strong></td>

                            <td><strong><span class="pull-right"><?php echo number_format((-1)*($totalBeforeAyear_income+$totalBeforeAyear_cost),2);?></span></strong></td>
							</tr>
                        </tbody>
						
						<thead>
                        <tr class="primary">
                            
                            <th style="width:20%;text-align:left;" colspan="3"><?= lang("operating_expense"); ?></th>
                       
                        </tr>
                        </thead>
                        <tbody>
						
						<?php
							$total_expense = 0;
                            $totalBeforeAyear_expense = 0;
							foreach($dataExpense->result() as $row){
							$total_expense += $row->amount;

                            $query = $this->db->query("SELECT
                                sum(erp_gl_trans.amount) AS amount
                            FROM
                                erp_gl_trans
                            WHERE
                                DATE(tran_date) = '$totalBeforeAyear' AND account_code = '" . $row->account_code . "';");
                            $totalBeforeAyearRows = $query->row();
                            $totalBeforeAyear_expense += $totalBeforeAyearRows->amount;
						?>
							<tr>
								<td colspan="2" style="padding-left:30px"><?php echo $row->account_code;?> - <?php echo $row->accountname;?></td>
								<td><span class="pull-right"><?php echo number_format(abs($row->amount),2);?></span></td>
                                <td></td>
                                <td><span class="pull-right"><?php echo number_format(abs($totalBeforeAyearRows->amount),2);?></span></td>
							</tr>
						<?php
							}
						?>							
							<tr>
							<td colspan="3"><?= lang("total_expense"); ?></td>
							<td><span class="pull-right"><?php echo number_format((-1)*$total_expense,2);?></span></td>
                            <td><span class="pull-right"><?php echo number_format((-1)*$totalBeforeAyear_expense,2);?></span></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">                            
                            <th colspan="3"><?= lang("profits"); ?></th>
                            <th><span class="pull-right"><?php echo number_format((-1)*$total_income-($total_cost+$total_expense),2);?></span></th>
                            <th><span class="pull-right"><?php echo number_format((-1)*$totalBeforeAyear_income-($totalBeforeAyear_cost+$totalBeforeAyear_expense),2);?></span></th>
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
    <?php form_close(); ?>
<?php } ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/income_statement/'. $start .'/'.$end.'/pdf/0/'.$biller_id)?>";
            return false;
        });
		
		$('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/income_statement/'. $start .'/'.$end.'/0/xls/'.$biller_id)?>";
            return false;
        });
		
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