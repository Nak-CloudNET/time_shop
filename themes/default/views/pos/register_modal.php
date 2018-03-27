<style type="text/css">
    @media print {
        #myModal .modal-content {
            display: block !important;
        }
		#myModal .modal-content .noprint {
			display: none !important;
		}
    }
</style>
<div class="modal-dialog">
    <div class="modal-content"style="width:400px;" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('Register_Form'); ?></h4>
        </div>
        <div class="modal-body print">
            <div class="table-responsive">
			   <div class="box-content">
					<div class="row">
						<div class="col-lg-12">
							<div class="well well-sm col-sm-12">
								<?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'Register Form');
									echo form_open("pos/open_register", $attrib); ?>
									<div class="form-group">
										<?= lang('Owner Password', 'cash_in_hand') ?>
										<input type="password" name="user_password" id="pwd" placeholder="<?= lang('enter_password'); ?>" class="form-control" />
										<input type="hidden" name="cash_in_hand" class="cash_in_hand" >
									</div>
									<?php echo form_submit('Submit', lang('Submit'), 'class="btn btn-primary"'); ?>
									<?php echo form_close(); ?>
								<div class="clearfix"></div>
							</div>
						</div>
					</div>
				</div>
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
		var case_hand = $("#cash_in_hand").val()-0;
		$(".cash_in_hand").val(case_hand);
    });
</script>
