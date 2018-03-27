<div class="modal-dialog" style="width:80%;">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_condition_tax'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'addAcc');
        echo form_open_multipart("account/condition_tax", $attrib); ?>
        <div class="modal-body" >
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
				
                               <table id="CompTable" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th style="width:5%;"  rowspan="2"><?= $this->lang->line("#"); ?></th>
                        <th style="width:10%;"  rowspan="2"><?= $this->lang->line("Tax Type"); ?></th>
						<th style="width:15%;" colspan="2"><?= $this->lang->line("Exchange Rate"); ?></th>
						<th style="width:15%;" rowspan="2"><?= $this->lang->line("Month"); ?></th>
						<th style="width:10%;" rowspan="2"><?= $this->lang->line("Year"); ?></th>
						<th style="width:2%;"  rowspan="2"><button type="button" class="btn btn-primary" id="addDescription"><i class="fa fa-plus-circle"></i></button></th>
					</tr>
					<tr>
						<th style="width:15%;"><?= $this->lang->line("USD"); ?></th>
                        <th style="width:15%;"><?= $this->lang->line("KHR"); ?></th>
					</tr>
                    </thead>
                    <tbody class="tbody">
					<?php if($edit!=""){?>
					<tr>
						<td>#</td>
						<td>
						<select name="tax_type[]" class="form-control"   style="width:100%" >
							<option value=""></option>
							<option value="salary">Salary</option>
							<option value="average">Average</option>
						</select>
						</td>
						<td><?= form_input('usd_rate[]', '', 'class="form-control usd_rate" id="usd_rate"'); ?></td>
						<td><?= form_input('khr_rate[]', '', 'class="form-control khr_rate" id="khr_rate"'); ?></td>
						<td>
						<select name="month[]" class="form-control" style="width:100%">
						<?php 
							for($i=1;$i<=12;$i++){
								$dateObj   = DateTime::createFromFormat('!m', $i);
								$monthName = $dateObj->format('F');
								echo "<option value=".$i.">".$monthName."</option>";
							}
						?>
						</select>
						</td>
						<td>
						<select name="year[]" class="form-control" style="width:100%">
						<?php
							$startingYear = date('Y');
							$endingYear = $startingYear + 20;
							for ($i = $startingYear;$i <= $endingYear;$i++)
							{
								echo '<option value='.$i.'>'.$i.'</option>';
							}
						?>
						</select>
						</td>
						<td style="text-align:center;"><span style="cursor:pointer;" class="btn_delete"><i style="font-size: 15px; color:#2A79B9;" class="fa fa-trash-o"></i></span></td>
					</tr>
                    <?php }?>
                    </tbody>
                </table> 
			
			</div>
        <div class="modal-footer">
            <?php echo form_submit('add_condition_tax', lang('add_condition_tax'), 'class="btn btn-primary" id="add_condition_tax"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>

<script type="text/javascript">
var InputsWrapper   = jQuery(".tbody");
var AddButton       = jQuery("#addDescription");
$("#addDescription").change();
$(AddButton).click(function (e){
		var my_i = ($(".tbody tr").size())-0+1;
			var div  ='<tr>';
				div +='<td class="no">'+my_i+'</td>';
				div +='<td><select name="tax_type[]" style="width:100%" class="form-control"><option value=""></option><option value="salary">Salary</option><option value="average">Average</option></select></td>';
				div +='<td><input type="text" name="usd_rate[]" class="form-control usd_rate" id="usd_rate" /></td>';
				div +='<td><input type="text" name="khr_rate[]" class="form-control khr_rate" id="khr_rate" /></td>';
				div +='<td>';
				div +='<select name="month[]" style="width:100%" class="form-control"><?php for($i=1;$i<=12;$i++){$dateObj   = DateTime::createFromFormat('!m', $i);$monthName = $dateObj->format('F');echo "<option value=".$i.">".$monthName."</option>";}?>"</select></td>';
				div +='<td><select name="year[]" style="width:100%" class="form-control"><?php $startingYear = date('Y');$endingYear = $startingYear + 20;for ($i = $startingYear;$i <= $endingYear;$i++){echo '<option value='.$i.'>'.$i.'</option>';}?>"</select></td>';
				div +='<td style="text-align:center;"><span style="cursor:pointer;" class="btn_delete"><i style="font-size: 15px; color:#2A79B9;" class="fa fa-trash-o"></i></span></td>';
				div +='</tr>';

			$(InputsWrapper).append(div);
		
		return false;
	});
	
	$(document).on("click",".btn_delete",function() {
			var row = $(this).closest('tr').focus();
			row.remove();
			$('.no').each(function(i){
				var j=i-0+1;
				$(this).html(j);
			});
	});
</script>

