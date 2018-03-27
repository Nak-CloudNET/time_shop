<style>
	.show1{
		display: none;
	}
	.ths{
		text-align:center; 
		vertical-align: middle !important;
	}
	.thd{
		text-align:right; 
		vertical-align: middle !important;
	}
	
	@media print {
		.show1{
			display: block !important;
		}
		.modal-body{
			padding:0 !important;
		}
	}
</style>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
			<div class="row">
				<?php for($i=0; $i<2; $i++){ ?>	
					<div class="col-sm-12 show<?=$i?>">
						<table class="table-responsive" width="100%" border="0" cellspacing="0">
							<thead>
								<tr>
									<td width="50%" style="virticle-align:middle;">
										<?php if ($logo) { ?>
											<div class="text-center" style="margin-bottom:20px;">
												<img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->name != '-' ? $biller->name : $biller->name; ?>">
											</div>
										<?php } ?>
										<p style="font-size:12px;margin-top:-20px;" class="text-center"><?= $biller->address;?></p>
									</td>
									<td width="50%" colspan="3" style="padding-left:50px;">
										<div style="font-family:'Arial'; font-size:24px; font-weight:bold;"><?= $biller->name != '-' ? $biller->name : $biller->name; ?></div>
									</td>
								</tr>
								<tr>
									<td colspan="2" style="padding-top:5px; font-size:12px;">
										<div class="col-sm-6">
											<div class="row">
												<span class="col-sm-4" style="padding:0;">
													<?= lang('customer');?>
												</span>
												<span class="col-sm-8" style="padding-left:0;">
													: <?= $customer->name ? $customer->name : $customer->name; ?>
												</span>
											</div>
											<div class="row">
												<span class="col-sm-4" style="padding:0;">
													<?= lang('Telephone');?>
												</span> 
												<span class="col-sm-8" style="padding-left:0;">
													: <?= $customer->phone; ?>
												</span>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="row">
												<span class="col-sm-4" style="padding:0;">
													<?= lang('invoice_no');?>
												</span> 
												<span class="col-sm-8" style="padding:0;">
													: <?= $inv->reference_no; ?>
												</span>
											</div>
											<div class="row">
												<span class="col-sm-4" style="padding:0;">
													<?= lang('cashier');?>
												</span>
												<span class="col-sm-8" style="padding:0;">
													: <?= $cashier->username; ?>
												</span>
											</div>
											<div class="row">
												<span class="col-sm-4" style="padding:0;">
													<?= lang('date');?>
												</span>
												<span class="col-sm-8" style="padding:0;">
													: <?= $this->erp->hrld($inv->date); ?>
												</span>
											</div>
										</div>
									</td>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="2">
										<div class="table-responsive">
											<table class="table table-bordered table-hover table-striped print-table order-table" style="font-size:10px;">
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
															if($pos->show_product_code == 0) {
																$product_name_setting = $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');
															}else{
																$product_name_setting = $row->product_name . " (" . $row->product_code . ")" . ($row->variant ? ' (' . $row->variant . ')' : '');
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
																echo '<tr>';
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
													<tr>
														<td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold; vertical-align:middle;"><?= lang("grand_total"); ?>
														</td>
														<td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->erp->formatMoney($inv->grand_total); ?></td>
													</tr>
													<tr>
														<td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold; vertical-align:middle;"><?= lang("paid"). $usd; ?>
														</td>
														<td style="text-align:right; font-weight:bold; vertical-align:middle;"><?= $this->erp->formatMoney($inv->paid - $other_paid); ?></td>
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
												</tfoot>
											</table>
										</div>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="2">
										<table width="100%" style="font-family:'Arial'; font-size:12px;">
											<tr>
												<td width="25%"> <?= lang('in_word'); ?> (USD)</td>
												<td width="75%">: <?= $this->erp->convert_number_to_words($this->erp->formatMoney($inv->paid)); ?> </td>
											</tr>
											<tr>
												<td width="25%"> <?= lang('payment_method'); ?> </td>
												<td width="75%">: <?= $payment->paid_by; ?> </td>
											</tr>
											<tr>
												<td width="25%"> <?= lang('note'); ?> </td>
												<td width="75%">: <?= $payment->note; ?> </td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<table border="0" cellspacing="0">
											<tr>
												<td colspan="3" width="33%" valign="bottom" style="text-align:center;padding-top:70px;">
													<hr style="border:dotted 1px; width:160px; vertical-align:bottom !important;  margin-bottom:2px;" />
													<b style="font-size:10px;text-align:center;margin-left:3px;"><?= lang('​<br/> Customer`s Signature & Name'); ?></b>
												</td><td>&nbsp;</td><td>&nbsp;</td>
												<td colspan="3" width="33%" valign="bottom" style="text-align:center;padding-top:50px;">
													&nbsp;
												</td><td>&nbsp;</td><td>&nbsp;</td>
											</tr>						
										</table>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
				<?php } ?>	
			</div>
        </div>
    </div>
</div>













