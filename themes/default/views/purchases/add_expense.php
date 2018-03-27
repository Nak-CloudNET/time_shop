<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_expense'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("purchases/add_expense", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<?php if ($Owner || $Admin) { ?>
				<div class="form-group">
					<?= lang("List Point of Sale","List Point of Sale"); ?>
					<?php
					foreach ($billers as $biller) {
						$bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
					}
					echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller), 'class="form-control" id="posbiller" required="required"');
					?>
				</div>
			<?php } else {
				$biller_input = array(
					'type' => 'hidden',
					'name' => 'biller',
					'id' => 'posbiller',
					'value' => $this->session->userdata('biller_id'),
				);

				echo form_input($biller_input);
			}
			?>

            <?php if ($Owner || $Admin) { ?>

                <div class="form-group">
                    <?= lang("date", "date"); ?>
                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="date" required="required"'); ?>
                </div>
            <?php } ?>

            <div class="form-group">
                <?= lang("reference", "reference"); ?>
                <?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $exnumber), 'class="form-control tip" id="reference"'); ?>
            </div>
			
			<div class="form-group">
				<?= lang("category_expense", "chart_account"); ?>
				<?php
				
				$acc_section = array(""=>"");
				foreach($chart_accounts as $section){
					$acc_section[$section->id] = $section->code.' | '.$section->name;
				}
					echo form_dropdown('account_section', $acc_section, '' ,'id="account_section" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("category_expense"). '" required="required" style="width:100%;" ');
				?>
			</div>
			
			<div class="form-group">
				<?= lang("paid_by", "paid_by"); ?>
				<?php
				
				$acc_section = array(""=>"");
				foreach($paid_by as $section){
					$acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
				}
					echo form_dropdown('paid_by', $acc_section, '' ,'id="paid_by" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("paid_by") . '" required="required" style="width:100%;" ');
				?>
			</div>

            <div class="form-group">
                <?= lang("amount", "amount"); ?> (USD)
                <input name="amount" type="text" id="amount" value="" class="pa form-control kb-pad amount"
                       required="required"/>
            </div>
			
			<?php
				foreach($currency as $money){
					if($money->code == 'USD'){

					}else{
			?>
				<div class="form-group">
					<?= lang("amount", "amount").($money->code == 'USD' ? ' (USD)' : ' (Rate: USD1 = '.$money->code.' '.number_format($money->rate).')'); ?>
					<input name="other_amount[]" type="text" id="<?=$money->code;?>" value="" rate="<?=$money->rate?>" class="pa form-control kb-pad amount_other"/>
				</div>
			<?php
					}
				}
			?>
			
            <div class="form-group">
                <?= lang("attachment", "attachment") ?>
                <input id="attachment" type="file" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_expense', lang('add_expense'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['erp'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $.fn.datetimepicker.dates['erp'] = <?=$dp_lang?>;
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'erp',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
		function formatDecimals(x) {
			return parseFloat(parseFloat(x).toFixed(7));
		}
		var code = 0;
		var value = 0;
		var rate = 0;
		function autotherMoney(value){
            $(".amount_other").each(function(){
                var rate = $(this).attr('rate');
				if(value != 0){
					$(this).val(formatDecimals(value*rate));
				}else{
					$(this).val('0');
				}
            });
        }
		
        function autoMoney(value, rate){
        	$(".amount_other").each(function(){
				if(value != 0){
					$('input[name="amount"]').val(formatDecimals(value / rate));
				}else{
					$('input[name="amount"]').val('0');
				}
            });
        }
		
		$('input[name="amount"]').live('change keyup paste',function(){
			value = $(this).val();
			autotherMoney(value);
		});

		$('input[name="other_amount[]"]').live('change keyup paste',function(){
			value = $(this).val();
			rate = $(this).attr('rate');
			var val = value / rate;
			autoMoney(value, rate);
			autotherMoney(val);
		});
    });
</script>
