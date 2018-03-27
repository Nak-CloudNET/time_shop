<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
			<button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('Sales (All Warehouses)'); ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th><?php echo $this->lang->line("no"); ?></th>
                            <th><?php echo $this->lang->line("date"); ?></th>
                            <th><?php echo $this->lang->line("reference_no"); ?></th>
                            <th><?php echo $this->lang->line("biller"); ?></th>
                            <th><?php echo $this->lang->line("customer"); ?></th>
                            <th><?php echo $this->lang->line("sale_status"); ?></th>
                            <th><?php echo $this->lang->line("grand_total"); ?></th>
                            <th><?php echo $this->lang->line("paid"); ?></th>
                            <th><?php echo $this->lang->line("balance"); ?></th>
							<th><?php echo $this->lang->line("payment_status"); ?></th>
                            <!--<th style="width:100px;"><?php //echo $this->lang->line("actions"); ?></th>-->
                        </tr>
                        </thead>
                        <tbody>
                        
						<?php
							if($sale_info->num_rows()>0){
								$total_grand_total =0;
								$total_paid =0;
								$total_balance =0;
								$i=0;
								foreach($sale_info->result() as $row){
									$total_grand_total+=$row->grand_total;
									$total_paid+=$row->paid;
									$total_balance+=$row->balance;
						?>
								<tr>
									<td><?php echo ++$i;?></td>
									<td><?php echo $row->date;?></td>
									<td><?php echo $row->reference_no;?></td>
									<td><?php echo $row->biller;?></td>
									<td><?php echo $row->customer;?></td>
									<td><?php echo $row->sale_status;?></td>
									<td><?php echo $row->grand_total;?></td>
									<td><?php echo $row->paid;?></td>
									<td><?php echo $row->balance;?></td>
									<td><?php echo $row->payment_status;?></td>
								</tr>
						<?php
								}
							}else{
						?>	
								<tr>
									<td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
								</tr>
						<?php
							}
						?>
                            
                        
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
                            <th><?php echo number_format($total_grand_total,2); ?></th>
                            <th><?php echo number_format($total_paid,2); ?></th>
                            <th><?php echo number_format($total_balance,2); ?></th>
                            <th></th>
                            <!--<th style="width:100px; text-align: center;"><?php //echo $this->lang->line("actions"); ?></th>-->
                        </tr>
                        </tfoot>
                    </table>
                </div>
        </div>
    </div>
</div>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $(document).on('click', '.po-delete', function () {
            var id = $(this).attr('id');
            $(this).closest('tr').remove();
        });
    });
</script>    