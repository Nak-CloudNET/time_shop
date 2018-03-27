<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
			<button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('Expanses (All Warehouses)'); ?></h4>
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
                            <th><?php echo $this->lang->line("amount"); ?></th>
                            <th><?php echo $this->lang->line("note"); ?></th>
                            <th><?php echo $this->lang->line("created_by"); ?></th>
                            <!--<th style="width:100px;"><?php //echo $this->lang->line("actions"); ?></th>-->
                        </tr>
                        </thead>
                        <tbody>
                        
						<?php
							$total_grand_total =0;
							$total_paid =0;
							$total_balance =0;
							if($expense_info->num_rows()>0){
								$i=0;
								foreach($expense_info->result() as $row){
									/*$total_grand_total+=$row->grand_total;
									$total_paid+=$row->paid;
									$total_balance+=$row->balance;*/
									$total_grand_total+=$row->amount;
						?>
								<tr>
									<td><?php echo ++$i;?></td>
									<td><?php echo $row->date;?></td>
									<td><?php echo $row->reference;?></td>
									<td><?php echo $row->amount;?></td>
									<td><?php echo $row->note;?></td>
									<td><?php echo $row->created_by;?></td>
								</tr>
						<?php
								}
							}else{
						?>	
								<tr>
									<td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
                            <th><?php echo number_format($total_grand_total,2); ?></th>
                            <th></th>
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