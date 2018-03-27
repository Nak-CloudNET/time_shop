<style>
.error{
	color: #ef233c;
}
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_journal'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("account/save_journal", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
		<div class="row">
			<?php
			$description = '';
            if(isset($journals)){
                foreach($journals as $journal1){
                    if($journal1->description != ""){
                        $description = $journal1->description;
                    }
                }
            }
			?>
			<div class="col-md-12">
				<div class="col-md-4">
					<div class="form-group">
						<?= lang("date", "sldate"); ?>
						<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y h:i')), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
					</div>
				</div>
				
				<div class="col-md-4">
					<div class="form-group">
						<?= lang("reference_no", "reference_no"); ?>
						<?php echo form_input('reference_no', '', 'class="form-control" id="reference_no"'); ?>
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
						echo form_dropdown('biller_id', $bl, (isset($_POST['biller_id']) ? $_POST['biller_id'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
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
						<?= form_textarea('description', '', 'rows="5" class="form-control" id="details" required="required" '); ?>
					</div>
				</div>
				<div class="col-md-1"></div>
			</div>
			<div class="journalContainer">
				<div class="col-md-12 journal-list">
					<div class="col-md-4">
						<div class="form-group company">
							<?= lang("chart_account", "chart_account"); ?>
							<?php
							$acc_section = array(""=>"");
							foreach($sectionacc as $section){
								$acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
							}
							echo form_dropdown('account_section[]', $acc_section, '', 'id="account_section" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("Account") . ' ' . $this->lang->line("Section") . '" required="required" style="width:100%;" ');
							?>
						</div>
					</div>
					
					<div class="col-md-4">
						<div class="form-group">
							<?= lang("debit", "debit"); ?>
							<?php echo form_input('debit[]', '', 'class="form-control debit1" id="debit"'); ?>
						</div>
					</div>
					
					<div class="col-md-3">
						<div class="form-group">
							<?= lang("credit", "credit"); ?>
							<?php echo form_input('credit[]', '', 'class="form-control credit1" id="credit"'); ?>
						</div>
					</div>
				</div>
				
				
				<div class="col-md-12 journal-list">
					<div class="col-md-4">
						<div class="form-group company">
						<?php
						$acc_section = array(""=>"");
						foreach($sectionacc as $section){
							$acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
						}
							echo form_dropdown('account_section[]', $acc_section, isset($journal->account_code)?$journal->account_code:'', 'id="account_section" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("Account") . ' ' . $this->lang->line("Section") . '" required="required" style="width:100%;" ');
						?>
						</div>
					</div>
				
					<div class="col-md-4">
						<div class="form-group">
							<?php echo form_input('debit[]', '', 'class="form-control debit2" id="debit"'); ?>
						</div>
					</div>
					
					<div class="col-md-3">
						<div class="form-group">

							<?php echo form_input('credit[]', '', 'class="form-control credit2" id="credit"'); ?>
						</div>
					</div>
					<div class="col-md-1">
						
					</div>
				</div>
				
			</div>
				<div class="col-md-12 get-journal-list"></div>
				
				<div class="col-md-12" style="border-top:1px solid #CCC"></div>
				<div class="col-md-6">
					<div class="col-md-offset-9">
						<div class="form-group">
							<label id="calDebit"></label>
						</div>
					</div>
				</div>
				
				<div class="col-md-6">
					<div class="col-md-offset-4">
						<div class="form-group">
							<label id="calCredit" style="margin-left:18px !important"></label>
						</div>
					</div>
				</div>
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_journal', lang('add_journal'), 'class="btn btn-primary" id="checkSave"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['erp'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript">
	var MaxInputs       = 30;
	var InputsWrapper   = jQuery(".journalContainer");
	var AddButton       = jQuery("#addDescription");
	
	var InputCount = jQuery(".journal-list");
	var x = InputCount.length;
	
	var FieldCount=2;

	$(AddButton).click(function (e)
	{     
		if(x <= MaxInputs) 
		{ 
			FieldCount++; 
			var div = '<div class="col-md-12 journal-list divwrap'+FieldCount+'">';
			div += '	<div class="col-md-4">';
			div += '			<div class="form-group company">';
			div += '				<select class="form-control input-tip select" id="select" name="account_section[]" style="width:100%;" required="required">';
			div += '				<?php foreach($sectionacc as $section){ ?>';
			div += '					<option value="<?=$section->accountcode?>"><?=$section->accountcode . " | " . $section->accountname; ?></option>';
			div += '				<?php } ?>';
			div += '				</select>';
			div += '			</div>';
			div += '		</div>';
			
			div += '		<div class="col-md-4">';
			div += '			<div class="form-group">';
			div += '				<input type="text" name="debit[]" value="" class="form-control debit'+FieldCount+'" id="debit"> ';
			div += '			</div>';
			div += '		</div>';
					
			div += '		<div class="col-md-3">';
			div += '			<div class="form-group">';
			div += '				<input type="text" name="credit[]" value="" class="form-control credit'+FieldCount+'" id="credit"> ';
			div += '			</div>';
			div += '		</div>';
			div += '		<div class="col-md-1">';
			div += '			<label><label><button type="button" data="'+FieldCount+'" class="removefile btn btn-danger">&times;</button></label></label>';
			div += '		</div>';
			div += '	</div>';

			$(InputsWrapper).append(div);
			x++;
		}
		return false;
	});

	/*
	function AutoDebit(){
		var textDebit = $('input[name="debit[]"]');
		var calDebit = 0;
		//$('input[name="debit[]"]').each(function(){
			var valuesDebit = $('input[name="debit[]"]').map(function(){
			   calDebit += parseInt(this.value) || 0;
			   return calDebit
			}).get()
		//});
		$("#calDebit").text(calDebit);
	}
	
	function AutoCredit(){
		var calCredit = 0;
		var textCredit = $('input[name="credit[]"]');
		//$('input[name="credit[]"]').each(function(){
		   var valuesCredit = $('input[name="credit[]"]').map(function(){
			   calCredit += parseInt(this.value)|| 0;
			   return calCredit
		   }).get()
		//});
		$("#calCredit").text(calCredit);
	}
	*/
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
		$('.removefile').live('click', function(){
			var divId 	= $(this).attr('data');
			if( FieldCount == 2 ) {
				bootbox.alert('Journal must be at least two transaction!');
				
				return false;
			}else{
				$('.divwrap'+divId+'').remove();
			}
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
        
        $(".datetime").datetimepicker({
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
		
		function chart_account(){
			$('#account_section').bind("change", function(){
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
		}		
		chart_account();	
	});
</script>
