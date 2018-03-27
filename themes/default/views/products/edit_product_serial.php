<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
  <h4 class="modal-title" id="myModalLabel"><?php echo lang('Edit Product Serial'); ?>&nbsp;(<?= $product->code ?>)</h4>
        </div>
       <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("products/edit_serial/" . $edit_product_id, $attrib); ?>
        <div class="modal-body">
            <p></p>

            <p style="font-weight: bold;"></p>
            
                <div class="form-group"> 
                     <div class="col-xs-4">
					  <?= lang("Product Serial :", "Product Serial :"); ?> 
					 </div>
					 <div class="col-xs-8">
					     <input type="text" name="serial_number"value="<?=$product_list_serial->serial_number?>" class="form-control">
						  <?php
						     
							//foreach($product_list_serial as $pro_serial){
								
								// echo form_input('product_sr[]', $pro_serial->serial_number, ' style="width:70%;" class="form-control tip"  required="required"');
								 //echo '<input type="hidden" name="biller[]" value="'.$pro_serial->biller_id.'" style="margin:5px 102px ;width:50%;" class="form-control tip" />';
								 //echo '<input type="hidden" name="warehouse[]" value="'.$pro_serial->warehouse.'" style="margin:5px 102px ;width:50%;" class="form-control tip" /> ';
								// echo '<input type="hidden" name="st_serial[]" value="'.$pro_serial->serial_status.'" style="margin:5px 102px ;width:50%;" class="form-control tip" />';
							 
							//}
							
						  ?>
					 </div> 
                </div>
				<div class="clearfix"></div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_serial', lang('save'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>

<?= $modal_js ?>

<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $.fn.datetimepicker.dates['erp'] = <?=$dp_lang?>;
    });
</script>

