<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
			<button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('customer') . " (" . $company->name . ")"; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th><?php echo $this->lang->line("company"); ?></th>
                            <th><?php echo $this->lang->line("name"); ?></th>
                            <th><?php echo $this->lang->line("gender"); ?></th>
                            <th><?php echo $this->lang->line("date_of_birth"); ?></th>
							<th><?php echo $this->lang->line("status"); ?></th>
                            <th><?php echo $this->lang->line("phone"); ?></th>
                            <th><?php echo $this->lang->line("address"); ?></th>
							<!--<th style="width:100px;"><?php //echo $this->lang->line("actions"); ?></th>-->
                        </tr>
                        </thead>
                        <tbody>
                        
						<?php
							if($customer_info->num_rows()>0){
								foreach($customer_info->result() as $row){
									/*$total_grand_total+=$row->grand_total;
									$total_paid+=$row->paid;
									$total_balance+=$row->balance;
									$amount+=$row->amount;
									$type+=$row->type;*/
									$amount+=$row->amount;
						?>
									<tr>
										<td><?php echo $row->company;?></td>
										<td><?php echo $row->name;?></td>
										<td><?php echo $row->gender;?></td>
										<td><?php echo $row->date_of_birth;?></td>
										<td><?php echo $row->status;?></td>
										<td><?php echo $row->phone;?></td>
										<td><?php echo $row->address;?>&nbsp;<?php echo $row->city;?>&nbsp;<?php echo $row->country;?></td>
										
									</tr>
						<?php
								}
							}else{
						?>	
								<tr>
									<td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
								</tr>
						<?php
							}
						?>
                            
                        
                        </tbody>
                        <tfoot class="dtFilter">
                       
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