<link href="<?= $assets ?>styles/helpers/bootstrap.min.css" rel="stylesheet"/>
<style>
  .container {
        width: 29.7cm;
        margin: 20px auto;
        /*padding: 10px;*/
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
		height: auto;
    }
	@media print {
		.no-print {
			display:none !important;
		}
		.title{
			font-size:10px !important;
		}
		.table-content{
			font-size:10px !important;
		}
		.address{
			font-size:10px !important;
		}
		.biller-name{
			font-family:'Arial'; 
			font-size:14px !important; 
			font-weight:bold;
			margin-top:50px;
		}
		.footer{
			font-size:10px !important; 
		}
		.print-table{
			font-size:10px !important; 
		}
	}
	.ths{
		text-align:center; 
		vertical-align: middle !important;
	}
	.thd{
		text-align:right; 
		vertical-align: middle !important;
	}
	.biller-name{
		font-family:'Arial'; 
		font-size:24px; 
		font-weight:bold;
		margin-top:50px;
	}
	.print-table{
		font-size:11px; 
	}
	img{
		width:150px;
		height:60px;
	}
</style>


<div class="container" style="width: 821px;margin: 0 auto;">
	
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
				<?php if ($logo) { ?>
					<div class="text-center" style="margin-bottom:20px;">
						<img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->name != '-' ? $biller->name : $biller->name; ?>">
					</div>
				<?php } ?>
				<p style="font-size:12px;margin-top:-20px;" class="text-center address"><?= $biller->address;?></p>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
				<div class="biller-name"><?= $biller->name != '-' ? $biller->name : $biller->name; ?></div>
			</div>
		</div>
		<div class="row title">
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
				<div class="row">
					<span class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
						<?= lang('customer');?>
					</span>
					<span class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
						: <?= $customer->name ? $customer->name : $customer->name; ?>
					</span>
				</div>
				<div class="row">
					<span class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
						<?= lang('Telephone');?>
					</span> 
					<span class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
						: <?= $customer->phone; ?>
					</span>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
				<div class="row">
					<span class="col-lg-4 col-md-4 col-sm-4 col-xs-4" style="padding:0;">
						<?= lang('invoice_no');?>
					</span> 
					<span class="col-lg-8 col-md-8 col-sm-8 col-xs-8" style="padding:0;">
						: <?= $inv->reference_no; ?>
					</span>
				</div>
				<div class="row">
					<span class="col-lg-4 col-md-4 col-sm-4 col-xs-4" style="padding:0;">
						<?= lang('cashier');?>
					</span>
					<span class="col-lg-8 col-md-8 col-sm-8 col-xs-8" style="padding:0;">
						: <?= $cashier->username; ?>
					</span>
				</div>
				<div class="row">
					<span class="col-lg-4 col-md-4 col-sm-4 col-xs-4" style="padding:0;">
						<?= lang('date');?>
					</span>
					<span class="col-lg-8 col-md-8 col-sm-8 col-xs-8" style="padding:0;">
						: <?= $this->erp->hrld($inv->date); ?>
					</span>
				</div>
			</div>
		</div>
		<div class="row table-content">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-striped print-table">
						<thead>
							<tr>
								<th class="ths">
									<?= lang("no"); ?>
								</th>
								<th class="ths">
									<?= lang("description"); ?>
								</th>
								<th class="ths">
									<?= lang("quantity"); ?>
								</th>
								<th class="ths">
									<?= lang("Price_Before_Tax"); ?>
								</th>
								<?php
									if ($Settings->tax1) {
										echo '<th class="ths">' . 
											lang("product_tax") . 
										'</th>';
									}
								?>
								<th class="ths">
									<?= lang("price_after_tax"); ?>
								</th>
								<?php
									if ($Settings->product_discount && $inv->product_discount != 0) {
										echo '<th class="ths">' . 
											lang("discount") . 
										'</th>';
									}
								?>
								<th class="ths">
									<?= lang("amount"); ?> (USD)
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$r 				= 1;
								$tax_summary = array();
								foreach ($rows as $row):
									$free 			= lang('free');
									$product_unit 	= '';
									if($row->variant){
										$product_unit = $row->variant;
									}else{
										$product_unit = $row->unit;
									}
									$total 		+= $row->unit_price;
									$stotal 	+= $row->subtotal;
									$colspan 	= 6;
									$product_name_setting;
									if($row->product_type === 'combo') {
										if($pos->show_product_code == 0) {
											$product_name_setting =  $row->variant ;
										}else{
											$product_name_setting = $row->product_code;
										}
									}else{
										if($pos->show_product_code == 0) {
											$product_name_setting = $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');
										}else{
											$product_name_setting = $row->product_name . " (" . $row->product_code . ")" . ($row->variant ? ' (' . $row->variant . ')' : '');
										}
									}
							?>
							<tr>
								<td class="ths" style="width:20px;">
									<?= $r; ?>
								</td>
								<td class="thss" style="width:140px;">
									<?= $product_name_setting ?>
									<?= $row->serial_no ? '<br>* Serial: ' . $row->serial_no : ''; ?>
								</td>
								<td class="ths" style="width: 50px;">
									<?= $this->erp->formatQuantity($row->quantity); ?>
								</td>
								<td class="thd" style="width:65px;">
									<?php
										$pbt = ($row->net_unit_price / (1 + $row->tax_rate / 100));
										echo $this->erp->formatMoney($pbt); 
									?>
								</td>
								<?php
									if ($Settings->tax1) {
										echo '<td class="thd" style="width: 50px;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->erp->formatMoney($row->item_tax/$row->quantity) . '</td>';
									}
								?>
								<td class="thd" style="width:60px;"><?= $row->subtotal!=0?$this->erp->formatMoney($row->unit_price):$free; ?></td>
								<?php
									if ($Settings->product_discount && $inv->product_discount != 0) {
										echo '<td class="thd" style="width: 60px;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->erp->formatMoney($row->item_discount) . '</td>';
										$colspan ++;
									}
								?>
								<td class="thd" style="width:50px;"><?= $row->subtotal!=0?$this->erp->formatMoney($row->subtotal):$free; ?></td>
							</tr>
							<?php
								$r++;
								if($row->product_type === 'combo') {
									$this->db->select('*, (select name from erp_products p where p.code = erp_combo_items.item_code) as p_name');
									$this->db->where('erp_combo_items.product_id = "' . $row->product_id . '"');
									$comboLoop = $this->db->get('erp_combo_items');
									$c = 1;
									$cTotal = count($comboLoop->result());
									foreach ($comboLoop->result() as $val) {
										echo '<tr ' . ($c === $cTotal ? 'class="item"' : '') . '>';
											echo '<td></td>';
											echo '<td colspan="'.$colspan.'"><span style="padding-right: 5px;">' . $c . '. ' . $val->p_name . ' ('.$val->item_code.')</span></td>';
										echo '</tr>';
											$c++;
									}
								}
								endforeach;
							?>
						</tbody>
						<tfoot>
							<?php
								$col = 5;
								if ($Settings->product_discount && $inv->product_discount != 0) {
									$col++;
								}
								if ($Settings->tax1) {
									$col++;
								}
								if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1) {
									$tcol = $col - 2;
								} elseif ($Settings->product_discount && $inv->product_discount != 0) {
									$tcol = $col - 1;
								} elseif ($Settings->tax1) {
									$tcol = $col - 1;
								} else {
									$tcol = $col;
								}
								$other_paid = 0;
								if($inv->other_cur_paid){
									$other_paid = $inv->other_cur_paid / $inv->other_cur_paid_rate;
								}
								$usd  = '';
								$riel = '';
								if($inv->other_cur_paid){
									$usd  = ' (USD)';
									$riel = ' ('.$khmer_curr->country.')';
								}
							?>
							<?php if ($inv->grand_total != $inv->total) { ?>
								<tr>
									<td colspan="<?= $tcol + 1; ?>"
										style="text-align:right; padding-right:10px;vertical-align:middle;"><?= lang("total"); ?>
									</td>
									<?php
									if ($Settings->product_discount && $inv->product_discount != 0) {
										echo '<td style="text-align:right; vertical-align:middle;">' . $this->erp->formatMoney($inv->product_discount) . '</td>';
									}
									?>
									<td style="text-align:right; padding-right:10px; vertical-align:middle;"><?= $this->erp->formatMoney($inv->grand_total + $inv->order_discount); ?></td>
								</tr>
							<?php } ?>
							<?php 
								if ($inv->order_discount != 0) {
									echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px; font-weight:bold;vertical-align:middle;">' . lang("Sale_Discount") . '</td><td style="text-align:right; padding-right:10px; font-weight:bold; padding-top:20px;vertical-align:middle;">' . $this->erp->formatMoney($inv->order_discount) . '</td></tr>';
								}
							?>
							<?php 
								if ($Settings->tax2 && $inv->order_tax != 0) {
									echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px; font-weight:bold;vertical-align:middle;">' . lang("VAT(".number_format($vattin->rate)."%)") . '</td><td style="text-align:right; padding-right:10px; font-weight:bold; padding-top:20px;vertical-align:middle;">' . $this->erp->formatMoney($inv->order_tax) . '</td></tr>';
								}
							?>
							<?php 
								if ($inv->shipping != 0) {
									echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;vertical-align:middle;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->erp->formatMoney($inv->shipping) . '</td></tr>';
								}
							?>
							<?php 
							$total_paid= 0;
							foreach($payments as $paid){
								$total_paid+=$paid->pos_paid;
							}
							?>
							<tr>
								<td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold; vertical-align:middle;"><?= lang("grand_total"); ?>
								</td>
								<td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->erp->formatMoney($inv->grand_total); ?></td>
							</tr>
							<tr>
								<td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold; vertical-align:middle;"><?= lang("paid"). $usd; ?>
								</td>
								<td style="text-align:right; font-weight:bold; vertical-align:middle;"><?= $this->erp->formatMoney($total_paid); ?></td>
							</tr>
							<?php if($inv->other_cur_paid){ ?>
								<tr>
									<td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold; vertical-align:middle;"><?= lang("paid"). $riel; ?>
									</td>
									<td style="text-align:right; font-weight:bold;vertical-align:middle;"><?= $this->erp->formatMoney($inv->other_cur_paid); ?></td>
								</tr>
							<?php } ?>
							<tr>
								<td colspan="<?= $col; ?>"
									style="text-align:right; font-weight:bold;vertical-align:middle;"><?= lang("balance"); ?>
								</td>
								<td style="text-align:right; font-weight:bold;vertical-align:middle;"><?= $this->erp->formatMoney($inv->grand_total - $inv->paid); ?></td>
							</tr>
							<?php if($total_paid > $inv->grand_total){ ?>
							<tr>
								<td colspan="<?= $col; ?>"
									style="text-align:right; font-weight:bold;vertical-align:middle;"><?= lang("Change"); ?>
								</td>
								<td style="text-align:right; font-weight:bold;vertical-align:middle;"><?= $this->erp->formatMoney($total_paid - $inv->grand_total); ?></td>
							</tr>
							<?php } ?>
							
						</tfoot>
					</table>
				</div>
			</div>
		</div>
		<div class="row footer">
			<div class=" col-sm-12">
				<div>
					<span class="col-sm-3" style="padding:0;" >
						<?= lang('in_word'); ?> (USD)
					</span>
					<span>
						: <?= $this->erp->convert_number_to_words($this->erp->formatMoney($inv->paid)); ?>
					</span>
				</div>
				<div>
					<span class="col-sm-3" style="padding:0;">
						<?= lang('payment_method'); ?>
					</span>
					<span>
						: 
						<?php
							foreach($payments as $paid){
								echo $paid->paid_by .' ('. $this->erp->formatMoney($paid->pos_paid) .') ';
							}
							
						?>
					</span>
				</div>
				<div>
					<span class="col-sm-3" style="padding:0;">
						Note
					</span>
					<span>
						: <?= $payment->note; ?>
					</span>
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="col-sm-12 no-print">&nbsp;&nbsp;</div>
	<div class="col-sm-12 no-print">

		
		
	</div>
	<div class="col-sm-12 no-print">&nbsp;&nbsp;</div>


</div>















