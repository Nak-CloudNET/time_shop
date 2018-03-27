<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_catelog'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'addDoc');
        echo form_open_multipart("documents/add", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
				<div class="col-sm-6">
					<div class="form-group all">
                        <label class="control-label" for="unit"><?= lang("brand") ?></label>
                        <?php
                        $bran[''] = '';
						foreach($brands as $brand){
							$bran[$brand->id] = $brand->name;
						}
                        echo form_dropdown('brand', $bran, '', 'class="form-control select" id="brand" placeholder="'.lang('select_brand').'" style="width:100%;"');
                        ?>
                    </div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
                        <label class="control-label" for="unit"><?= lang("category") ?></label>
                        <?php
                        $cate[''] = '';
						foreach($categories as $category){
							$cate[$category->id] = $category->name;
						}
                        echo form_dropdown('category', $cate, '', 'class="form-control select" id="category" placeholder="'.lang('select_category').'" style="width:100%;"');
                        ?>
                    </div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
                        <?= lang("product_line", "subcategory") ?>
						<?php echo form_input('subcategory', ($product ? $product->subcategory_id : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '" style="width:100%;"');?>
                    </div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
                        <?= lang("product_code", "code") ?>
						<?php echo form_input('code', '', 'class="form-control" id="code" style="width:100%;" required="required"');?>
                    </div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
						<?= lang("product_name", "product_name") ?>
						<?= form_input('product_name', (isset($_POST['product_name']) ? $_POST['product_name'] : ''), 'class="form-control tip" id="price"') ?>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
                        <?= lang("price", "price") ?>
                        <?= form_input('price', '', 'class="form-control" id="price" required="required"'); ?>
                    </div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
                        <?= lang("cost", "cost") ?>
                        <?= form_input('cost', '', 'class="form-control" id="cost" required="required"'); ?>
                    </div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
                        <label class="control-label" for="unit"><?= lang("product_unit") ?></label>
                        <?php
                        $ut = '';
						foreach($unit as $uts){
							$ut[$uts->id] = $uts->name;
						}
                        echo form_dropdown('unit', $ut, '', 'class="form-control select" id="unit" placeholder="'.lang('select_units').'" style="width:100%;"');
                        ?>
                    </div>
				</div>
                <div class="col-sm-6">
					<div class="form-group all">
                        <?= lang("product_image", "product_image") ?>
                        <input id="product_image" type="file" name="product_image" data-show-upload="false"
                               data-show-preview="false" accept="image/*" class="form-control file">
                    </div>
				</div>
				<div class="col-sm-6">
                    <div class="form-group all">
                        <?= lang("product_gallery_images", "images") ?>
                        <input id="images" type="file" name="userfile[]" multiple="true" data-show-upload="false"
                               data-show-preview="false" class="form-control file" accept="image/*">
                    </div>
				</div>
				<div class="col-sm-6">
					<div class="form-group" style="padding-top:30px;">
						<input type="checkbox" class="checkbox" value="1" name="is_serial" id="is_serial" <?= $this->input->post('is_serial') ? 'checked="checked"' : ''; ?>>
						<label for="Serial Key" class="padding05">
							<?= lang('serial_number'); ?>
						</label>
					</div>
				</div>
				<div class="col-sm-12">
					<div class="form-group all">
                        <?= lang("description", "description") ?>
                        <?= form_textarea('description', '', 'class="form-control" id="description"'); ?>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<?php echo form_submit('add_document', lang('add_catelog'), 'class="btn btn-primary" id="add_document"'); ?>
			</div>
		</div>
		<?php echo form_close(); ?>
	</div>
</div>
<?= $modal_js ?>
<script type="text/javascript">
	$(document).ready(function () {
		$('#brand').change(function () {
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#category").select2("destroy").empty();
							var newOptions = '';
							var option = '<option></option>';
							$("#category").select2('destroy').append(option).select2();
							$.each(scdata, function(i, item) {
								newOptions = '<option value="'+ item.id +'">'+ item.text +'</option>';
								$("#category").select2('destroy').append(newOptions ).select2();
							});
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            }else {
				$("#category").select2("destroy").empty().attr("placeholder", "<?= lang('select_brand_to_load') ?>").select2({
					placeholder: "<?= lang('select_brand_to_load') ?>",
					data: [{id: '', text: '<?= lang('select_brand_to_load') ?>'}]
				});
			}
            $('#modal-loading').hide();
        });
        $('#category').change(function () {
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: scdata
                            });
                        }else{
							$("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: 'not found'
                            });
						}
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }
            $('#modal-loading').hide();
        });
		$('#product').change(function () {
			var v = $(this).val(); 
			if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('documents/getProducts') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        $('#quantity').val(formatDecimal(scdata.quantity));
                    }
                });
            }
		});
		$('#code').bind('change', function (e) {
            var code = $(this).val();
			$.ajax({
				type: 'GET',
				url: '<?= site_url('documents/check_code_available'); ?>',
				data: {term:code},
				cache: false,
				success: function (data) {
					if(data == 1){
						alert('Product code already exists...');
						$('#add_document').prop("disabled", true);
					}
				},
				error: function(){
					alert('error ajax');
				}
			});
        });
		/*
		$('#is_have').on('ifChanged', function(){
			if($(this).is(':checked')) {
				$('#product').show();
				$('#product_code').hide();
			}else{
				$('#product_code').show();
				$('#product').hide();
			}
		});
		*/
	});
</script>
