<div class="modal-dialog" style="width:80%;">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('purchasing_tax'); ?></h4>
        </div>
		<?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("taxes/add_purchasing_tax_form", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th style="width:17%;"><?= $this->lang->line("date"); ?></th>
                        <th style="width:13%;"><?= $this->lang->line("reference_no"); ?></th>
                        <th style="width:15%;"><?= $this->lang->line("amount"); ?></th>
                        <th style="width:15%;"><?= $this->lang->line("amount_tax"); ?></th>
						<th style="width:20%;"><?= $this->lang->line("amount_declear"); ?></th>
						<th style="width:15%;"><?= $this->lang->line("tax_ref"); ?></th>
						<th style="width:5%;"><?= $this->lang->line("action"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($purchase_taxes)) {
                        foreach ($purchase_taxes->result() as $purchases) { ?>
                            <tr class="row<?= $purchases->id ?>">
                                <td>
									<?= $purchases->date; ?>
									<input type="hidden" name="purchase_id[]" class="purchase_id" value="<?= $purchases->id; ?>" />
								</td>
                                <td>
									<?= $purchases->reference_no; ?>
									<input type="hidden" name="supplier_id[]" class="supplier_id" value="<?= $purchases->supplier_id; ?>" />
									<input type="hidden" name="warehouse_id[]" class="warehouse_id" value="<?= $purchases->warehouse_id; ?>" />
									<input type="hidden" name="purchase_ref[]" class="purchase_ref" value="<?= $purchases->reference_no; ?>" />
								</td>                           
								<td class="balance">
									<?= $this->erp->formatMoney($purchases->amount); ?>
									<input type="hidden" name="amount[]" class="amount" value="<?= $purchases->amount; ?>" />
								</td>
                                <td>
									<input type="text" name="amount_tax[]" class="amount_tax form-control" value="<?= $this->erp->formatMoney($purchases->amount_declear); ?>" />
									<input type="hidden" name="tax_id[]" class="tax_id" value="<?= $purchases->order_tax_id; ?>" />
								</td>
								<td class="text-right">
									<span name="amount_declear[]" class="amount_declear" id="amount_declear" rate="<?= $exchange_rate->rate ?>"><?= $this->erp->formatMoney($purchases->amount_declear*$exchange_rate->rate); ?></span>
									<input type="hidden" name="amount_decleared[]" class="amount_decleared" value="<?= $this->erp->formatMoney($purchases->amount_declear*$exchange_rate->rate); ?>" />
								</td>
								<td>
									<input type="text" name="tax_ref[]" class="tax_ref form-control">
								</td>
								<td class="text-center">
									<button type="button" class="btn btn-primary remove_line"><i class="fa fa-trash-o"></i></button>
								</td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='4'>" . lang('no_data_available') . "</td></tr>";
                    } ?>
                    </tbody>
                </table>
            </div>
			<div class="row">
				<?php if ($Owner || $Admin) { ?>
					<div class="col-sm-3">
						<div class="form-group">
							<?= lang("declear_date", "date"); ?>
							<?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('Y/m/d h:m')), 'class="form-control datetime" id="date" required="required"'); ?>
						</div>
					</div>
				<?php } ?>
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_purchasing_tax', lang('add_purchasing_tax'), 'class="btn btn-primary" id="add_submit"'); ?>
        </div>
    <?php echo form_close(); ?>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		$('.amount_tax').keyup(function() {
			var parent = $(this).parent().parent();
			var us_dolar = $(this).val()-0;
			var kh_rate = parent.find('.amount_declear').attr('rate')-0;
			var amount_declear = us_dolar * kh_rate;
			parent.find('.amount_declear').text(formatMoney(amount_declear));
			parent.find('.amount_decleared').val(formatMoney(amount_declear));
		});
		$(".remove_line").on('click',function() {
			var row = $(this).closest('tr').focus();
			row.remove();
		});
	});
</script>