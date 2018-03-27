<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_catalog'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'addDoc');
        echo form_open_multipart("products/add_catalogs", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
				<div class="col-sm-6">
					<div class="form-group all">
						<?= lang("brand", "brand") ?>
						
						<?= form_input('brand',$product->brand, 'class="form-control tip" id="brand"') ?>
					    <input type="hidden" name="brand_id" value="<?= $product->brand_id ?>">
					</div>
				</div>
				<div class="col-sm-6">
					
					<div class="form-group all">
						<?= lang("category", "category") ?>
						<?= form_input('category',$product->category, 'class="form-control tip" id="brand"') ?>
						<input type="hidden" name="cate_id" value="<?= $product->cate_id ?>">
					</div>
					
				</div>
				<div class="col-sm-6">
					
					<div class="form-group all">
						<?= lang("product_line", "subcategory") ?>
						<?= form_input('subcategory',$product->subcategory, 'class="form-control tip" id="product_line"') ?>
						<input type="hidden" name="subcate_id" value="<?= $product->brand_id ?>">
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
                        <?= lang("product_code", "code") ?>
						<?php echo form_input('code',$product->code, 'class="form-control" id="code" style="width:100%;" required="required"');?>
                    </div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
						<?= lang("product_name", "product_name") ?>
						<?= form_input('product_name',$product->product_name, 'class="form-control tip" required="required" id="name"') ?>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
                        <?= lang("price", "price") ?>
                        <?= form_input('price',$product->price, 'class="form-control" id="price" required="required"'); ?>
                    </div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
                        <?= lang("cost", "cost") ?>
                        <?= form_input('cost',$product->cost, 'class="form-control" id="cost" required="required"'); ?>
                    </div>
				</div>
				<div class="col-sm-6">
					<div class="form-group all">
                        <?= lang("unit", "unit") ?>
                        <?= form_input('unit',$product->unit, 'class="form-control" id="unit" required="required"'); ?>
						<input type="hidden" name="unit_id" value="<?= $product->unit_id ?>">
                    </div>
				</div>
            <!--    <div class="col-sm-6">
					<div class="form-group all">
                        <?= lang("product_image", "product_image") ?>
                        <input id="product_image" type="file" name="product_image" data-show-upload="false" data-show-preview="true" value="<?= $product->image ?>" class="form-control file">
						<div class="image">
						<?=
							$product->image ? '<img alt="" src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" class="image">' :
							'<img alt="" src="' . base_url() . 'assets/images/' . $product->image . '.png" class="image">';
						?>
						</div>
						<input type="hidden" name="exsit_image"  value="<?= $product->image ?>">
                    </div>
				</div>
				<div class="col-sm-6">
				
					<?php 
						$photo ="";
					   	foreach($gallary as $images)
					   	{
							
							$photo.=($images->photo ? base_url() .'assets/uploads/' . $images->photo  :"");
							$img[] = $images->photo;
							echo '<input type="hidden" name="img[]" value="'. $images->photo . '">';
							
					   	}
					?>
				
                    <div class="form-group all">
                        <?= lang("product_gallery_images", "images") ?>
                        <input id="images" type="file" name="userfile[]" multiple="true" data-show-upload="false" data-show-preview="false"value="<?= $photo ?>" class="form-control" >
					    <div class="image">
						
						</div>

                    </div>
				</div> -->
				<div class="col-sm-6">
					<div class="form-group" style="padding-top:30px;">
						<input type="checkbox" class="checkbox" value="1" name="is_serial" id="is_serial" <?= $this->input->post('is_serial') ? 'checked="checked"' : ''; ?>>
						<label for="Serial Key" class="padding05">
							<?= lang('serial_number'); ?>
						</label>
						<input type="hidden" name="serial" id="serial" value="<?=$product->is_serial?>">
					</div>
				</div>
				<div class="col-sm-12">
					<div class="form-group all">
                        <?= lang("description", "description") ?>
                        <?= form_textarea('description', $product->product_details, 'class="form-control" id="description"'); ?>
                    </div>
				</div>
			</div>
			<div class="modal-footer">
				<?php echo form_submit('add_document', lang('add_catalog'), 'class="btn btn-primary" id="add_document"'); ?>
			</div>
		</div>
		<?php echo form_close(); ?>
	</div>
</div>
<?= $modal_js ?>
<script type="text/javascript">
	$(document).ready(function(){
		 
		 var serial=$("#serial").val(); 
		  if(serial){  
			 $('#is_serial').iCheck('check');
	      }
		 
	});	
		
</script>
