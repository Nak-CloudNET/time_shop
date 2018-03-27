<style>
    .image img{
		  display:none;
		
	}
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_notification'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("notifications/edit", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('update_info'); ?></p>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php echo lang('from', 'from_date'); ?>
                        <div class="controls">
                            <?php echo form_input('from_date', date($dateFormats['php_ldate'], strtotime($notification->from_date)), 'class="form-control datetime" id="from_date" required="required"'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php echo lang('till', 'to_date'); ?>
                        <div class="controls">
                            <?php echo form_input('to_date', date($dateFormats['php_ldate'], strtotime($notification->till_date)), 'class="form-control datetime" id="to_date" required="required"'); ?>
                        </div>
                    </div>
                </div>								
				<div class="col-sm-6">                  
					<div class="form-group">                       
						<?php echo lang('sender', 'sender'); ?>                
						<div class="controls">                         
							<?php echo form_input('sender',$notification->sender, 'class="form-control" id="sender"'); ?>        
						</div>   
					</div>               
  				</div>								
				<div class="col-sm-6">                  
                    <div class="form-group">                   
                        <?php echo lang('recipient', 'recipient'); ?>                     
                        <div class="controls">                         
                            <?php echo form_input('recipient', $notification->recipient, 'class="form-control" id="sender"'); ?>                      
                        </div>                   
                    </div>                
                </div>  
                <div class="col-sm-12"><button class="btn btn-primary" type="button" id="AddMore">More</button></div>
                <?php 
                    foreach ($note_item as $item) {
                ?>
                    <div class="col-sm-6">             
                        <div class="form-group">            
                            <?php echo lang('items', 'items'); ?>                       
                            <div class="controls">                           
                                <?php echo form_input('items[]', $item->item, 'class="form-control" id="items"'); ?>                
                            </div>                 
                        </div>                
                    </div>                              
                    <div class="col-sm-6">               
                        <div class="form-group">                      
                            <?php echo lang('quantity', 'quantity'); ?>        
                            <div class="controls">            
                               <?php echo form_input('quantity[]', $item->quantity, 'class="form-control" id="quantity" '); ?>        
                            </div>                   
                        </div>              
                    </div>
                    <div class="col-sm-6">               
                        <div class="form-group all">
                            <?= lang("image", "image") ?>
                            <input id="userfile" type="file" name="userfile[]" data-show-upload="false"  data-show-preview="false" accept="image/*" class="form-control file">
                            <input type="hidden" name="images[]" value="<?= $item->image;?>">
                        </div>           
                    </div>
                <?php
                    }
                ?>
                <div class="moreItems"></div>
            </div>

            <div class="form-group">
                <?php echo lang('comment', 'comment'); ?>
                <div class="controls">
                    <?php echo form_textarea($comment); ?>
                </div>
            </div>

            <div class="form-group">
                <input type="radio" class="checkbox" name="scope" value="1"
                       id="customer" <?= $notification->scope == '1' ? 'checked="checked"' : ''; ?>/><label
                    for="customer" class="padding05"><?= lang('for_customers_only') ?></label>
                <input type="radio" class="checkbox" name="scope" value="2"
                       id="staff" <?= $notification->scope == '2' ? 'checked="checked"' : ''; ?>><label for="staff"
                                                                                                        class="padding05"><?= lang('for_staff_only') ?></label>
                <input type="radio" class="checkbox" name="scope" value="3"
                       id="both" <?= $notification->scope == '3' ? 'checked="checked"' : ''; ?>><label for="both"
                                                                                                       class="padding05"><?= lang('for_both') ?></label>
            </div>

            <?php echo form_hidden('id', $id); ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_notification', lang('edit_notification'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['erp'] = <?=$dp_lang?>;
	$("#product_image").parent('.form-group').addClass("text-warning");
    $('#AddMore').click(function(){
        var div = '';
        div += '<div class="col-sm-6">';             
            div += '<div class="form-group">';            
                div += '<?php echo lang('items', 'items'); ?>';                       
                div += '<div class="controls">';                          
                    div += '<input type="text" name="items[]" class="form-control" >';
                div += '</div>';                 
            div += '</div>';                
        div += '</div>';                              
        div += '<div class="col-sm-6">';               
            div += '<div class="form-group">';                      
                div += '<?php echo lang('quantity', 'quantity'); ?>';        
                div += '<div class="controls">';  
                   div += '<input type="text" name="quantity[]" class="form-control" >';     
                div += '</div>';                  
            div += '</div>';              
        div += '</div>';
        div += '<div class="col-sm-6">';               
            div += '<div class="form-group all">';
                div += '<?= lang("image", "image") ?>';
                div += '<input id="userfile" type="file" name="userfile[]" data-show-upload="false"  data-show-preview="false" accept="image/*" class="form-control file">';
            div += '</div>';           
        div += '</div>';
        $(".moreItems").append(div) 
    });
</script>
