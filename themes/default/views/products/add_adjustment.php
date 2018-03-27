<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('adjust_quantity').' - '. $product->name .'('.$product->code.')'; ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("products/add_adjustment/" . $product_id, $attrib);
        ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <p style="font-weight: bold;"><?= lang("product_code") . ": " . $product->code . " " . lang("product_name") . ": " . $product->name ?></p>
            <?php if ($Owner || $Admin) { ?>
                <div class="form-group">
                    <?php echo lang('date', 'date'); ?>
                    <div class="controls">
                        <?php echo form_input('date', '', 'class="form-control datetime" id="date" required="required"'); ?>
                    </div>
                </div>
            <?php } ?>
            <input type="hidden" name="pro_id" value="<?= $product->id;?>" class="pro_id">
            <?= form_hidden('code', $product->code) ?>
            <?= form_hidden('name', $product->name) ?>
            <div class="form-group">
                <?= lang('type', 'type'); ?>
                <?php $opts = array('addition' => lang('addition'), 'subtraction' => lang('subtraction')); ?>
                <?= form_dropdown('type', $opts, set_value('type', 'subtraction'), 'class="form-control tip" id="type"  required="required"'); ?>
            </div>
            <div class="form-group">
                <label for="quantity"><?php echo $this->lang->line("quantity"); ?></label>

                <div class="controls"> <?php echo form_input('quantity', (isset($_POST['quantity']) ? $_POST['quantity'] : ""), 'class="form-control input-tip" id="quantity" required="required"'); ?> </div>
            </div>
            
			<?php if (!empty($options)) { ?>
                <div class="form-group">
                    <label for="option"><?php echo $this->lang->line("product_variant"); ?></label>

                    <div class="controls">  <?php
                        $op[''] = '';
                        foreach ($options as $option) {
                            $op[$option->id] = $option->name;
                        }
                        echo form_dropdown('option', $op, (isset($_POST['option']) ? $_POST['option'] : ''), 'id="option" class="form-control input-pop" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("option") . '" required="required"');
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
                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $warehouse_id ? $warehouse_id : $Settings->default_warehouse), 'id="warehouse" class="form-control input-pop" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required"');
                    ?> </div>
            </div>
			<?php if($have_serial) {?>
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

                <div class="controls"> <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note" required="required" style="margin-top: 10px; height: 100px;"'); ?> </div>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('adjust_quantity', lang('adjust_quantity'), 'class="btn btn-primary adjust_quantity"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    
    $(document).ready(function () {
        $.fn.datetimepicker.dates['erp'] = <?=$dp_lang?>;
        $('.choose_serial').show();
        $('.add_serial').hide();
		//var have_serial = <?=$have_serial?>;
        //================== Show Serial On load ====================

        var warehouse_id = $('#warehouse').val();
        var id           = $('.pro_id').val();
        $.ajax({
            url:"<?=base_url()?>products/getProductsSerialByWarehouse",
            type:"GET",
            data: {'warehouse_id':warehouse_id, 'product_id':id},
            success:function(result){
                var data = jQuery.parseJSON(result);
                $.each(data, function(index, item) {
                    newOptions = '<option value="'+ item.id +'">'+ item.serial_number +'</option>';
                        $("#choose_serial").select2('destroy').append(newOptions ).select2();
                });
            }
        });

        //========================= End =============================

        //=============== Show Serial On Click type =================

        $('#type').change(function(){
            var type  = $(this).val();
			var input = '';
			var qty   = 0;
            if(type == "addition"){
                $('.choose_serial').hide();
                $('.add_serial').show();
				qty    = $('#quantity').val();
				if(qty == ''){
					input += "<div class='form-group'><input type='text' value='' name='add_serial[]' class='form-control input-tip add_ser' id='add_serial' /></div>";
				}else{
					for(i=0; i<qty; i++){
						input += "<div class='form-group'><input type='text' value='' name='add_serial[]' class='form-control input-tip add_ser' id='add_serial' /></div>"; 
					}
				}
				$('.more_serial').html(input);
            }else{
                $('.choose_serial').show();
                $('.add_serial').hide();
                var warehouse_id = $('#warehouse').val();
                var id         = $('.pro_id').val();
                $.ajax({
                    url:"<?=base_url()?>products/getProductsSerialByWarehouse",
                    type:"GET",
                    data: {'warehouse_id':warehouse_id, 'product_id':id},
                    success:function(result){
                        var data = jQuery.parseJSON(result);
                        $("#choose_serial").empty();
                        $.each(data, function(index, item) {
                            newOptions = '<option value="'+ item.id +'">'+ item.serial_number +'</option>';
                                $("#choose_serial").select2('destroy').append(newOptions ).select2();
                        });
                    }
                });
            }
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
                            newOptions = '<option value="'+ item.id +'">'+ item.serial_number +'</option>';
                                $("#choose_serial").select2('destroy').append(newOptions ).select2();
                        });
                    }
                });
            }
        });

        //======================== End ==============================

        //================== Check if qty = serial ==================

        var qty    = 0;
        var serial = 0;
        $(document).on('change', '#choose_serial, #quantity', function(){
			 
            qty       = $('#quantity').val();
            serial    = $("#choose_serial :selected").length;
			var input = '';
			var type  = $('#type').val();
			if(type == "addition"){
				if(qty < 0){
					input += "<div class='form-group'><input type='text' value='' name='add_serial[]' class='form-control input-tip add_ser' id='add_serial' /></div>";
				}else{
					for(i=0; i<qty; i++){
						input += "<div class='form-group'><input type='text' value='' name='add_serial[]' class='form-control input-tip add_ser' id='add_serial'/></div>"; 
					}
				}
				$('.more_serial').html(input);
			}else{
				if(qty != serial){
					$(':input[type="submit"]').prop('disabled', true);
				}else{
					$(':input[type="submit"]').prop('disabled', false);
				}
			}
        });
		var add_serial = 0;
		$(document).on('change', '#quantity, #warehouse', function(){
			$('#add_serial').each(function(){
				add_serial = $(this).val();
				if(add_serial == ""){
					$(':input[type="submit"]').prop('disabled', true);	
				}
			});
		});
				
		$(document).on('change', '#type', function(){
			var qty = $('#quantity').val();
			var type = $(this).val();
			if(type == "addition"){
				$('#add_serial').each(function(){
					add_serial = $(this).val();
					if(add_serial == ""){
						$(':input[type="submit"]').prop('disabled', true);	
					}
				});
			}else{
				if(qty != serial){
					$(':input[type="submit"]').prop('disabled', true);
				}else{
					$(':input[type="submit"]').prop('disabled', false);
				}
			}
		});
		
        $(document).on('change', '#add_serial', function(){
			var serial 	= 0;
			var qty     = $('#quantity').val();
			var serial_number     = $(this).val();
			var allValue  = [];
			$('.add_ser').each(function(){
				var ser = $(this).val();
				if(ser){
					allValue.push(ser);
					serial++;
				}
			});
			var sorted_arr = allValue.sort();

			for (var i = 0; i < allValue.length - 1; i++) {
				if (sorted_arr[i + 1] == sorted_arr[i]) {
					alert("Please enter different serial number in each TextBox.");
					$(':input[type="submit"]').prop('disabled', true);
					return false;
				}
			}
			
			$.ajax({
				type: 'GET',
				url: '<?= site_url('products/getProductSerialAjax'); ?>',
				data: {serial:serial_number},
					cache: false,
					success: function (data) {
							if(data == 1){
								alert('Product serial number already exists...');
								$(':input[type="submit"]').prop('disabled', true);
							}
						},
						error: function(){
							alert('error ajax');
						}
				});
			if(qty != serial){
				$('.adjust_quantity').attr('disabled','disabled');
			}else{
				$('.adjust_quantity').removeAttr('disabled');
			}
		});
        if(qty != serial){
            alert('Quantity and Products Serial need to be the same!');
            $('.adjust_quantity').attr('disabled','disabled');
        }

        //========================== End ============================
		
    });
</script>

