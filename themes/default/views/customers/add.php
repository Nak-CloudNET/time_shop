<script>
    $(document).ready(function(){
		
		$("#date_of_birth").datetimepicker( {
			format: "dd/mm",
			todayBtn: 1,
			autoclose: 1,
			minView: 2
		});
		if (!localStorage.getItem('sldate')) {
            $("#sldate").datetimepicker({
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
        }
        $(document).on('change', '#sldate', function (e) {
            localStorage.setItem('sldate', $(this).val());
        });
		if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
        if (sldate = localStorage.getItem('sldate')) {
            $('#sldate').val(sldate);
        }
	});
	
</script>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_customer'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
        echo form_open_multipart("customers/add", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label class="control-label"
                       for="customer_group"><?php echo $this->lang->line("default_customer_group"); ?></label>

                <div class="controls"> <?php
                    foreach ($customer_groups as $customer_group) {
                        $cgs[$customer_group->id] = $customer_group->name;
                    }
                    echo form_dropdown('customer_group', $cgs, $this->Settings->customer_group, 'class="form-control tip select" id="customer_group" style="width:100%;" required="required"');
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    
                    <div class="form-group">
                          <?= lang("creation_date", "creation_date"); ?>
                          <?php echo form_input('creation_date', (isset($_POST['creation_date']) ? $_POST['creation_date'] : ""), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                    </div>
                            
					<div class="form-group person">
                        <?= lang("name", "name"); ?>
                        <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                    </div>					
                    <div class="form-group">                        
                        <?= lang("nationality", "nationality"); ?>                        
                        <?php echo form_input('country', '', 'class="form-control" id="country"'); ?>                    
                    </div>					
                    <div class="form-group">
                        <?= lang("email_address", "email_address"); ?>
                        <input type="email" name="email" class="form-control" id="email_address"/>
                    </div>
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_input('address', '', 'class="form-control" id="address"'); ?>
                    </div>	
	
				    <div class="form-group all">
                        <?= lang("comment_note", "comment_note") ?>
                        <?= form_textarea('comment_note',"",'class="form-control"'); ?>
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
						<?php echo form_input('date_of_birth', isset($customer->date_of_birth)?date('d-m-Y', strtotime($customer->date_of_birth)):'', 'class="form-control" id="date_of_birth"'); ?>       
					</div>
					<div class="form-group">                        
						<?= lang("phone", "phone"); ?>						
						<?php echo form_input('phone', '', 'class="form-control" id="phone" type="tel" '); ?>                    
					</div>				
					<div class="form-group">
                        <?= lang("gender", "gender"); ?>
                        <?php
                        $gender[""] = "Select Gender";
                        $gender['male'] = "Male";
                        $gender['female'] = "Female";
                        echo form_dropdown('gender', $gender, isset($customer->gender)?$customer->gender:'', 'class="form-control select" id="gender" placeholder="' . lang("select") . ' ' . lang("gender") . '" style="width:100%"')
                        ?>
                    </div>					
                    <div class="form-group">
                        <?= lang("attachment", "cf4"); ?><input id="attachment" type="file" name="userfile[]" data-show-upload="false" data-show-preview="false" multiple="true" class="file">

                    </div>
                </div>
            </div>
		
		</div>
        <div class="modal-footer">
            <?php echo form_submit('add_customer', lang('add_customer'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>