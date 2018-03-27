<link href="<?= $assets ?>styles/helpers/bootstrap.min.css" rel="stylesheet"/>
<?php
	$address = '';
	$address.=$biller->address;
	$address.=($biller->city != '')? ', '.$biller->city : '';
	$address.=($biller->postal_code != '')? ', '.$biller->postal_code : '';
	$address.=($biller->state != '')? ', '.$biller->state : '';
	$address.=($biller->country != '')? ', '.$biller->country : '';
?>
<center>
	<div class="row" style="padding:5px; padding-left:10px; padding-right:10px;">
		<?php for($i=0;$i<2;$i++){ ?>
		<div class="col-sm-6">
		<table class="table-responsive" width="90%" border="0" cellspacing="0">
		
			<tr>
				<td colspan="2">
					<div class="table-responsive" style="margin-top:60%;">
						<table class="table table-bordered table-hover table-striped print-table order-table" style="'font-family:Khmer OS'; font-size:12px; width:100%;">

							<tbody>

							<?php $r = 1;
							$tax_summary = array();
							foreach ($rows as $row):
							$free = lang('free');
							$product_unit = '';
							if($row->variant){
								$product_unit = $row->variant;
							}else{
								$product_unit = $row->unit;
							}
							
							$product_name_setting;
							if($pos->show_product_code == 0) {
								$product_name_setting = $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');
							}else{
								$product_name_setting = $row->product_name . " (" . $row->product_code . ")" . ($row->variant ? ' (' . $row->variant . ')' : '');
							}
							?>
								<tr>
									<td style="text-align:center; width:35px; vertical-align:middle;"><?= $r; ?></td>
									<td style="vertical-align:middle;">
										<?= $product_name_setting ?>
										<?= $row->details ? '<br>' . $row->details : ''; ?>
										<?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
									</td>
									<td style="width: 18%; text-align:center; vertical-align:middle;"><?= $this->erp->formatQuantity($row->quantity); ?></td>
									<td style="text-align:right; width:18%; vertical-align:middle;"><?= $row->subtotal!=0?$this->erp->formatMoney($row->unit_price):$free; ?></td>
									<td style="text-align:right; width:18%; vertical-align:middle;"><?= $row->subtotal!=0?$this->erp->formatMoney($row->subtotal):$free; ?></td>
								</tr>
								<?php
								$r++;
							endforeach;
							?>
							</tbody>
							<tfoot>
							
							</tfoot>
						</table>
					</div>
				</td>
			</tr>
		</table>
		</div>
		<?php } ?>
	</div>
</center>