<div class="modal-dialog">
    <div class="modal-content" id="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_adjustment'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("products/edit_adjustment/" . $product_id . "/" . $damage->id, $attrib);
        ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <p style="font-weight: bold;"><?= lang("product_code") . ": " . $product->code . " " . lang("product_name") . ": " . $product->name ?></p>
            <?php if ($Owner || $Admin) { ?>
                <div class="form-group">
                    <?php echo lang('date', 'date'); ?>
                    <div class="controls">
                        <?php echo form_input('date', date($dateFormats['php_ldate'], strtotime($damage->date)), 'class="form-control datetime" id="date" required="required"'); ?>
                    </div>
                </div>
            <?php } ?>
			<input type="hidden" name="pro_id" value="<?= $product->id;?>" class="pro_id">
            <?= form_hidden('code', $product->code) ?>
            <?= form_hidden('name', $product->name) ?>
            <div class="form-group">
                <?= lang('type', 'type'); ?>
                <?php $opts = array('addition' => lang('addition'), 'subtraction' => lang('subtraction')); ?>
                <?= form_dropdown('type', $opts, set_value('type', ($damage->type ? $damage->type : 'subtraction')), 'class="form-control tip" id="type" style="pointer-events: none;" required="required"'); ?>
            </div>
            <div class="form-group">
                <label for="quantity"><?php echo $this->lang->line("damage_quantity"); ?></label>

                <div class="controls"> <?php echo form_input('quantity', (isset($_POST['quantity']) ? $_POST['quantity'] : $this->erp->formatQuantity($damage->quantity)), 'class="form-control input-tip" id="quantity" required="required"'); ?> </div>
            </div>
            <?php if ($damage->option_id) { ?>
                <div class="form-group">
                    <label for="option"><?php echo $this->lang->line("product_variant"); ?></label>

                    <div class="controls">  <?php
                        $op[''] = '';
                        foreach ($options as $option) {
                            $op[$option->id] = $option->name;
                        }
                        echo form_dropdown('option', $op, (isset($_POST['option']) ? $_POST['option'] : $damage->option_id), 'id="option" class="form-control input-pop" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("option") . '" required="required"');
                        ?> </div>
                </div>
            <?php } else {
                echo form_hidden('option', 0);
            } ?>
            <div class="form-group">
                <label for="warehouse"><?php echo $this->lang->line("warehouse"); ?></label>

                <div class="controls">  <?php
                    $wh[''] = '';
                    foreach ($warehouses as $warehouse) {
                        $wh[$warehouse->id] = $warehouse->name;
                    }
                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $damage->warehouse_id), 'id="warehouse" class="form-control input-pop" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required"');
                    ?> </div>
            </div>
			<?php if($have_serial) { ?>
				<div class="form-group choose_serial">
					<label for="choose_serial"><?php echo $this->lang->line("choose_serial"); ?></label>
					<div class="controls">
						<select name="choose_serial[]" id="choose_serial" class="form-control select" multiple="multiple"></select>
					</div>
				</div>
				<div class="form-group add_serial">
					<label for="add_serial"><?php echo $this->lang->line("add_serial"); ?></label>
					<div class="more_serial"></div>
				</div>
			<?php } ?>
            <div class="form-group">
                <label for="note"><?php echo $this->lang->line("note"); ?></label>

                <div class="controls"> <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $this->erp->decode_html($damage->note)), 'class="form-control" id="note" required="required"'); ?> </div>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_adjustment', lang('edit_adjustment'), 'class="btn btn-primary edit_adjustment" id="edit_adjustment" '); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
	$(document).ready(function () {
		$.fn.datetimepicker.dates['erp'] = <?=$dp_lang?>;
		
		//=================== Show / Hide Serial ====================
		var type = $('#type').val();
		var arr_serial 	= "<?=$damage->serial_number?>";
		var serial 		= arr_serial.split(',');
		var input 		= '';
		var qty    		= 0;
        var serial 		= 0;
		var remove		= '<button type="button" data="" class="removefile btn btn-danger">&times;</button>';
		$('.removefile').live('click', function(){
			qty    = $('#quantity').val();
			qty = qty - 1;
			$(this).closest('.remove').remove();	
			$('#quantity').val(qty );
		});
		
		//=================== Show / Hide Serial ====================
		checkSerial();
		
		//=============== Show Serial On Click type =================
		$('#type').change(function(){
            checkSerial();
		});
		//======================== End ==============================
		
		//============ Show Serial On Click Warehouse ===============
		$('#warehouse').change(function(){
            var type = $('#type').val();
            if(type == "addition"){
                $('.choose_serial').hide();
                $('.add_serial').show();
            }else{
                $('.choose_serial').show();
                $('.add_serial').hide();
                var warehouse_id = $(this).val();
                var id         = $('.pro_id').val();
                $.ajax({
                    url:"<?=base_url()?>products/getProductsSerialByWarehouse",
                    type:"GET",
                    data: {'warehouse_id':warehouse_id, 'product_id':id},
                    success:function(result){
                        var data = jQuery.parseJSON(result);
                        $("#choose_serial").empty();
                        $.each(data, function(index, item) {
                            newOptions = '<option value="'+ item.serial_number +'">'+ item.serial_number +'</option>';
                                $("#choose_serial").select2('destroy').append(newOptions ).select2();
                        });
                    }
                });
            }
        });
		//======================== End ==============================
		$(document).on('change', '#quantity', function(){
			checkSerial();
		});
		$(document).on('change', '#choose_serial', function(){
			var qty  			= $('#quantity').val();
			var serial  		= $('#choose_serial :selected').length;
			//console.log(qty + '==' + serial);
			if(parseFloat(qty) == parseFloat(serial)){
				$(':input[type="submit"]').prop('disabled', false);
			}else{
				$(':input[type="submit"]').prop('disabled', true);
			}
		});
		$(document).on('change', '#add_serial', function(){
			var serial_num 		= 0;
			var qty  			= $('#quantity').val();
			$('.add_ser').each(function(){
				var ser = $(this).val();
				if(ser){
					serial_num++;
				}
			});
			if(qty != serial_num){
				$(':input[type="submit"]').prop('disabled', true);
			}else{
				$(':input[type="submit"]').prop('disabled', false);
			}
		});
		function checkSerial(){
			var type 			= $('#type').val();
			var qty  			= $('#quantity').val();
			var serial  		= $("#choose_serial").length;
			var arr_serial 		= "<?= $damage->serial_number?>";
			var data_serial 	= arr_serial.split(',');
			var input			= '';
			var allValue  		= [];
			var serial_num 		= 0;

			if(type == "addition"){
				$('.choose_serial').hide();
				$('.add_serial').show();
				if(data_serial.length > 0){
					for(var i = 0; i < data_serial.length ; i++){
						input += '<div style="display:flex;padding:5px 0px 5px 0px" class="remove"><input type="text" class="form-control add_ser" name="add_serial[]" id="add_serial" value='+data_serial[i]+'>'+remove+'</div>';
					}
				}
				if(data_serial.length < qty){
					var rowcount= qty - data_serial.length;
					
					for(var j=0;j<(rowcount);j++){
						input += '<div style="display:flex;padding:5px 0px 5px 0px" class="remove"><input type="text" class="form-control add_ser" name="add_serial[]" id="add_serial">'+remove+'</div>';
					}
				}
				$('.more_serial').empty().append(input);
				$('.add_ser').each(function(){
					var ser = $(this).val();
					if(ser){
						allValue.push(ser);
						serial_num++;
					}
				});
				if(qty != serial_num){
					$(':input[type="submit"]').prop('disabled', true);
				}else{
					$(':input[type="submit"]').prop('disabled', false);
				}
			}else{
				$('.choose_serial').show();
				$('.add_serial').hide();
				var warehouse_id 	= $('#warehouse').val();
				var id         		= $('.pro_id').val();
				var newOptions		= '';
				$.ajax({
					url:"<?=base_url()?>products/getProductsSerialByWarehouse",
					type:"GET",
					data: {'warehouse_id':warehouse_id, 'product_id':id},
					success:function(result){
						var data = jQuery.parseJSON(result);
						$("#choose_serial").empty();
						if(data_serial.length > 0){
							for(var i = 0; i < data_serial.length ; i++){
								newOptions += '<option selected value="'+ data_serial[i] +'">'+ data_serial[i] +'</option>';
							}
						}
						$.each(data, function(index, item) {
							newOptions += '<option value="'+ item.serial_number +'">'+ item.serial_number +'</option>';
						});
						$("#choose_serial").select2('destroy').append(newOptions).select2();
					}
				});
				$('#choose_serial').each(function(){
					var ser = $(this).val();
					if(ser){
						allValue.push(ser);
						serial_num++;
					}
				});
				
				if(parseFloat(qty) == parseFloat(serial)){
					$(':input[type="submit"]').prop('disabled', false);
				}else{
					$(':input[type="submit"]').prop('disabled', true);
				}
			}
			
		}
	});
</script>

