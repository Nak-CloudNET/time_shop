
<div class="box">
       <div class="box-header">
	          <h2 class="blue"><i class="fa fa-barcode" aria-hidden="true"></i><?=lang('reprot_stock_movement')?>
             </h2>
	          <div class="box-icon">
				<ul class="btn-tasks">
					<li class="dropdown">
						<a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
							<i class="icon fa fa-toggle-up"></i>
						</a>
					</li>
					<li class="dropdown">
						<a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
							<i class="icon fa fa-toggle-down"></i>
						</a>
					</li>
				</ul>
			</div>
	   </div>
</div>
<div class="box-content">
			<div id="form">

                    <?php echo form_open("reports/stock_movement"); ?>
                    <div class="row">
					    <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("from_date", "from_date"); ?>
                                <?php echo form_input('from_date', (isset($_POST['from_date']) ? $_POST['from_date'] : ""), 'class="form-control datetime" id="from_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("to_date", "to_date"); ?>
                                <?php echo form_input('to_date', (isset($_POST['to_date']) ? $_POST['to_date'] : ""), 'class="form-control datetime" id="to_date"'); ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouses"><?= lang("warehouses"); ?></label>
                                <?php
								foreach($warehouses as $row){
									
									$wh[$row->id]=$row->name;
								}
                                echo form_dropdown('warehouses', $wh, (isset($_POST['warehouses']) ? $_POST['warehouses'] : ""), 'class="form-control" id="warehouses" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("item", "item"); ?>
                                <?php echo form_input('item', (isset($_POST['item']) ? $_POST['item'] : ""), 'class="form-control" id="item"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
				
<div class="box-content">
		       <div class="table table-responsive">
			          <table class="table table-bordered">
							          <tr class="active">
									       <th rowspan="2" width="38%"style="padding:0px 9%;"><div style="color:green;float:left;margin-left:20px;"><?= lang('warehouse')?>-</div><div style="color:orange;float:left;"><?= lang('categories')?>-</div><div style="float:left;"><?= lang('item')?></div></th>
										   <th rowspan="2" class="text-center"><?= lang('begin')?></th>
										   <th class="text-center"><?= lang('in')?></th>
										   <th colspan="2"class="text-center"><?= lang('out')?></th>
										   <th rowspan="2" class="text-center"><?= lang('balance')?></th>
										   
									  </tr>
									  <tr class="active">
										   <th class="text-center"style="color:blue;"><?= lang('purchase')?></th>
										   <th class="text-center" style="color:blue;"><?= lang('invoice')?></th>
										   <th class="text-center" style="color:blue;"><?= lang('driver_delivery')?></th>
										   
									  </tr>
							          <tr>
									      <td colspan="6" class="text-left"style="color:green;padding:2px 5px"><?= lang('warehouse')?></td>
										  
									  </tr>
									  </tbody>
									      <?php 
										     if(isset($search_term)){
										  ?>
									         <tr>
											     <td style="padding:2px 5px" colspan="6"><?= $search_term->name?></td>
												 
											 </tr>
											 <?php }
											 ?>
									  </tbody>
					  </table>
			   </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
        $("#product").autocomplete({
            source: '<?= site_url('reports/suggestions'); ?>',
            select: function (event, ui) {
                $('#product_id').val(ui.item.id);
                //$(this).val(ui.item.label);
            },
            minLength: 1,
            autoFocus: false,
            delay: 300,
        });
    });
</script>