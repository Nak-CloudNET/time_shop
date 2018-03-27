<style>
.error{
	color: #ef233c;
}
.margin-b-5{
	margin-bottom: 0;
}
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_journal'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("account/updateJournal", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
		<div class="row">
			<?php
			$description = '';
			$biller_id = '';
            if(isset($journals)){
                foreach($journals as $journal1){
                    if($journal1->description != ""){
                        $description = $journal1->description;
						$biller_id = $journal1->biller_id;
                    }
                }
            }
			
			?>
		
			<div class="col-md-12">
				<div class="col-md-4">
					<div class="form-group">
						<?= lang("date", "sldate"); ?>
						<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $journal1->tran_date), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
					</div>
				</div>
				
				<div class="col-md-4">
					<div class="form-group">
						<?= lang("reference_no", "reference_no"); ?>
						<?php echo form_input('reference_no', $journal1->reference_no, 'class="form-control" id="reference_no" '); ?>
					</div>
				</div>
				
				<div class="col-md-3">
					<div class="form-group">
						<label class="control-label" for="biller"><?= lang("biller"); ?></label>
						<?php
						$bl[""] = "";
						foreach ($billers as $biller) {
							$bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
						}
						echo form_dropdown('biller_id', $bl, ($biller_id ? $biller_id : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
						?>
					</div>
				</div>
				
				<div class="col-md-1">
					<div class="form-group">
						<button type="button" class="btn btn-primary" id="addDescription"><i class="fa fa-plus-circle"></i></button>
					</div>
				</div>
				
			</div>
				<div class="col-md-12">
					<div class="col-md-11">
						<div class="form-group">
							<?= lang("description", "description") ?>
							<?= form_textarea('description', strip_tags($description), 'class="form-control" id="description" required="required" '); ?>
						</div>
					</div>
					<div class="col-md-1"></div>
				</div>
				
			<div class="row journalContainer">
				<div class="col-md-12">
					<div class="col-md-4"><div class="form-group margin-b-5"><?= lang("chart_account", "chart_account"); ?></div></div>
					<div class="col-md-4"><div class="form-group margin-b-5"><?= lang("debit", "debit"); ?></div></div>
					<div class="col-md-4"><div class="form-group margin-b-5"><?= lang("credit", "credit"); ?></div></div>
				</div>
			<?php
			$n = 1;
            $debit = 0;
            $credit = 0;
			foreach($journals as $journal){
			?>
				<hr>
				<div class="col-md-12 journal-list">
					
					<div class="col-md-4">
						<div class="form-group company margin-b-5">	
							<?php
							
							$acc_section = array(""=>"");
							foreach($sectionacc as $section){
								$acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
							}
								echo form_dropdown('account_section[]', $acc_section, $journal->account_code, 'id="account_section" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("Account") . ' ' . $this->lang->line("Section") . '" required="required" style="width:100%;" ');
							?>
							<input type="hidden" name="tran_id[]" value="<?= $journal->tran_id ?>">
						</div>
					</div>
					
					<div class="col-md-4">
						<div class="form-group margin-b-5">	
							<?php echo form_input('debit[]', $journal->debit, 'class="form-control debit" id="debit debit'. $n .'"'); ?>
						</div>
					</div>
					
					<div class="col-md-3">
						<div class="form-group margin-b-5">
							<?php echo form_input('credit[]', $journal->credit, 'class="form-control credit" id="credit credit'.$n .'"'); ?>
						</div>
					</div>
					<div class="col-md-1">
						<div class="form-group margin-b-5">
						<label><label>
							<button type="button" class="removefiles btn btn-danger">&times;</button>
						</div>
					</div>
					
				</div>

				<?php 
				$debit += $journal->debit;
				$credit += $journal->credit;
			
				$n++;
				} ?>
			</div>

				<div class="col-md-12" style="border-top:1px solid #CCC"></div>
				
				<div class="col-md-6">
					<div class="col-md-offset-9">
						<div class="form-group">
							<label id="calDebit"><?=$debit?></label>
						</div>
					</div>
				</div>
				
				<div class="col-md-6">
					<div class="col-md-offset-4">
						<div class="form-group">
							<label id="calCredit" style="margin-left:18px !important"><?=$credit?></label>
						</div>
					</div>
				</div>
			
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_journal', lang('edit_journal'), 'class="btn btn-primary" id="checkSave"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">

	var MaxInputs       = 30;
	var InputsWrapper   = jQuery(".journalContainer");
	var AddButton       = jQuery("#addDescription");
	
	var InputCount = jQuery(".journal-list");
	var x = InputCount.length;
	
	var FieldCount=1;

	$(AddButton).click(function (e)
	{     
		if(x <= MaxInputs) 
		{ 
			FieldCount++; 
			
			var div = '<div class="col-md-12 journal-list divwrap'+FieldCount+'"">';
			div += '	<div class="col-md-4">';
			div += '			<div class="form-group company">';
			div += '				<select class="form-control input-tip select2" name="account_section[]" required="required">';
			div += '				<?php foreach($sectionacc as $section){ ?>';
			div += '					<option value="<?=$section->accountcode?>"><?=$section->accountcode . " | " . $section->accountname; ?></option>';
			div += '				<?php } ?>';
			div += '				</select>';
			div += '			</div>';
			div += '		</div>';
			
			div += '		<div class="col-md-4">';
			div += '			<div class="form-group">';
			div += '				<input type="text" name="debit[]" value="" class="form-control debit" id="debit"> ';
			div += '			</div>';
			div += '		</div>';
					
			div += '		<div class="col-md-3">';
			div += '			<div class="form-group">';
			div += '				<input type="text" name="credit[]" value="" class="form-control credit" id="credit"> ';
			div += '			</div>';
			div += '		</div>';
			div += '		<div class="col-md-1">';
			div += '			<label><button type="button" data="'+FieldCount+'" class="removefile btn btn-danger">&times;</button></label>';
			div += '		</div>';
			div += '	</div>';

			$(InputsWrapper).append(div);
			$(".select2").select2();
			x++;
		}
		return false;
	});

	$('.removefile').click(function(e){
		if( FieldCount == 1 ) {
			$(this).closest().find('.journal-list').remove();
			FieldCount--;
		}else{
			bootbox.alert('Journal must be at least two transaction!');
		}
		return false;
	});
	
	function AutoDebit(){
		var v_debit = 0;
		var i = 1;
		$('[name^=debit]').each(function(i, item) {
			v_debit +=  parseInt($(item).val()) || 0;
		});
		$("#calDebit").text(v_debit);
	}
	function AutoCredit(){
		var v_credit = 0;
		var j = 1;
		$('[name^=credit]').each(function(i, item) {
			v_credit +=  parseInt($(item).val()) || 0;
		});
		$("#calCredit").text(v_credit);
	}

	$(document).ready(function () {
		$('.removefiles').click(function(){
			var tr = $(this).parent().parent().parent().parent().parent();
			tr.remove();
		});

		$('.removefile').live('click', function(){
			var divId 	= $(this).attr('data');
			$('.divwrap'+divId+'').remove();
		});
		
		$('input[name="debit[]"], input[name="credit[]"]').live('change keyup paste',function(){	
			AutoDebit();
			AutoCredit();

			if($("#calDebit").text() != $("#calCredit").text()){
				$("#calDebit").addClass('error');
				$("#calCredit").addClass('error');
			}else{
				$("#calDebit").removeClass('error');
				$("#calCredit").removeClass('error');
			}
		});
		
		$("#checkSave").click(function(){
			if($("#calDebit").text() != $("#calCredit").text()){
				bootbox.alert('Your Debit Credit is difference ! \nPlease check your amount');
				return false;
			}
		});

		$('#account_section').change(function () {
			$(".sub_textbox").show();
			$(".sub_combobox").hide();
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('account/getSubAccount') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#sub_account").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: scdata
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            }
            $('#modal-loading').hide();
        });
	});
	
	$('#account_section').select2({
            placeholder: "Select Categories",
            maximumSelectionSize: 3
     });
</script>
