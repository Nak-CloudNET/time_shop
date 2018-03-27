<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_customer'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("customers/edit/" . $customer->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label class="control-label"
                       for="customer_group"><?php echo $this->lang->line("default_customer_group"); ?></label>

                <div class="controls"> <?php
                    foreach ($customer_groups as $customer_group) {
                        $cgs[$customer_group->id] = $customer_group->name;
                    }
                    echo form_dropdown('customer_group', $cgs, $customer->customer_group_id, 'class="form-control tip select" id="customer_group" style="width:100%;" required="required"');
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
					<div class="form-group">
                        <?= lang("creation_date", "creation_date"); ?>
                        <?php echo form_input('creation_date', (isset($_POST['creation_date']) ? $_POST['creation_date'] : $this->erp->hrld($customer->created_at)), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                    </div>
                    <div class="form-group person">
                        <?= lang("name", "name"); ?>
                        <?php echo form_input('name', $customer->name, 'class="form-control tip" id="name" required="required"'); ?>
                    </div>
					<div class="form-group">
                        <?= lang("nationality", "nationality"); ?>
                        <?php echo form_input('country', $customer->country, 'class="form-control" id="country"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("email_address", "email_address"); ?>
                        <input type="email" name="email" class="form-control" id="email_address"
                               value="<?= $customer->email ?>"/>
                    </div>
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_input('address', $customer->address, 'class="form-control" id="address"'); ?>
                    </div>
					<div class="form-group all">
                        <?= lang("comment_note", "comment_note") ?>
                        <?= form_textarea('comment_note',(isset($_POST['comment_note']) ? $_POST['comment_note'] : $customer->comment_note ? $customer->comment_note : ''),'class="form-control"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
					<div class="form-group">
						<?= lang("created_by", "saleman"); ?>
							<select name="created_by" id="saleman" class="form-control saleman">
								<?php 
									foreach($agencies as $agency){
										if($this->session->userdata('username') == $agency->username){
											echo '<option value="'.$this->session->userdata('user_id').'" selected>'.lang($this->session->userdata('username')).'</option>';
										}else{
											echo '<option value="'.$agency->id.'">'.$agency->username.'</option>';
											}
										}
								?>
							</select>
					</div>
                    <div class="form-group">
                        <?= lang("date_of_birth", "cf5"); ?> <?= lang("Ex: YYYY-MM-DD"); ?>
                         <?php echo form_input('date_of_birth', isset($customer->date_of_birth)?date('d/m', strtotime($customer->date_of_birth)):'', 'class="form-control" id="date_of_birth"'); ?>
					</div>
					
					<div class="form-group">
                        <?= lang("phone", "phone"); ?>
                        <input type="tel" name="phone" class="form-control" id="phone"
                               value="<?= $customer->phone ?>"/>
                    </div>
					
					<div class="form-group">
                        <?= lang("gender", "gender"); ?>
                        <?php
                        $gender[""] = "Select Gender";
                        $gender['male'] = "Male";
                        $gender['female'] = "Female";
                        echo form_dropdown('gender', $gender, $customer->gender, 'class="form-control select" id="gender" placeholder="' . lang("select") . ' ' . lang("gender") . '" style="width:100%"')
                        ?>
                    </div>
					
                    <div class="form-group">
                        <?= lang("attachment", "cf4"); ?><input id="attachment" type="file" name="userfile[]" data-show-upload="false" data-show-preview="false" class="file" multiple="true">

                    </div>
                    
                </div>
            </div>
            
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_customer', lang('edit_customer'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $.fn.datetimepicker.dates['erp'] = <?=$dp_lang?>;
        $(".date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'erp',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        });
		$("#date_of_birth").datetimepicker( {
			format: "dd/mm",
			todayBtn: 1,
			autoclose: 1,
			minView: 2
		});
    });
</script>

