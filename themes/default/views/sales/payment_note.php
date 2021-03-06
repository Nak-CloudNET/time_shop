<style type="text/css">
    @media print {
        #myModal .modal-content {
            display: none !important;
        }
    }
</style>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body print">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
			<button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                     alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
            <?php } ?>
            <div class="clearfix"></div>
            <div class="row padding10">
                <div class="col-xs-12 text-center">
                    <?php
                    echo $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country;

					echo "<br/>";
                    echo '<b>' . lang("tel") . ": " . $biller->phone . "</b><br />" . lang("email") . ": " . $biller->email;
                    ?>
                    <div class="clearfix"></div>
                </div>
			</div>

            <div class="row">
                <div class="col-sm-6">
                    <p><b><?= lang("payment_reference"); ?></b>: <?= $payment->reference_no; ?></p>
					<p><b><?= lang("date_invoice"); ?></b>: <?= $this->erp->hrld($inv->date); ?></p>
					<p><b><?= lang("date_received"); ?></b>: <?= $this->erp->hrld($payment->date); ?></p>
                </div>
				<div class="col-sm-6 text-left">
					<div class="pull-right">
						<p><b><?= lang("receipt"); ?></b>: <?= $inv->reference_no; ?></p>
						<p><b><?= lang("username"); ?></b>: <?= $this->session->userdata('username'); ?></p>
						<p><b><?= lang("customer"); ?></b>: <?= $inv->customer; ?></p>
					</div>
                </div>
            </div>
            <div class="well">
				<table class="table receipt">
					<thead>
						<tr>
							<th><?= lang("no"); ?></th>
							<th><?= lang("Product Name (Product Reference)"); ?></th>
							<th><?= lang("qty"); ?></th>
							<th><?= lang("unit"); ?></th>
							<?php if ($inv->total_discount != 0 || $total_disc != '') {
								echo '<th>'.lang('discount').'</th>';
							} ?>
							<th style="padding-left:10px;padding-right:10px;"><?= lang("amount"); ?> </th>
						</tr>
					</thead>
					<tbody>
						<?php
						$no = 1;
						$m_us = 0;
						$total_quantity = 0;
                        $t_discount = 0;
						foreach($rows as $row){

							$free = lang('free');
                            $t_discount += $row->discount;
							echo '<tr ><td class="text-left">#' . $no . "</td>";
							echo '<td class="text-left">' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '').' ('.$row->product_code.')</td>';
							echo '<td class="text-center">' . $this->erp->formatQuantity($row->quantity);
							
							echo '<td class="text-center">' . '$ '. $this->erp->formatMoney($row->real_unit_price) . '</td>';
							$colspan = 5;
							if ($inv->order_discount != 0 || $row->item_discount != 0) {
								echo '<td class="text-center">';
								echo '<span>' ;
									if(strpos($row->discount, '%') !== false){
										echo $row->discount;
									}else{
										echo $row->discount;
									}
								echo '</span> ';
								$colspan = 5;
								$total_col = 3;
								echo '</td>';
							}else{
								if($t_discount>0){
									echo '<td class="text-center"></td>';
									$colspan = 5;
									$total_col = 3;
								}else{
									$colspan = 4;
									$total_col = 2;
								}
							}
							echo '<td class="text-right">' . ($this->erp->formatMoney($row->subtotal) == 0 ? $free:'$ '. $this->erp->formatMoney($row->subtotal)) . '</td>';
							echo '</tr>';
							$no++;
							$total_quantity += $row->quantity;
						}
						?>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2"><?= lang("qty"); ?>= (<?=$this->erp->formatQuantity($total_quantity)?>)</th>
							<th colspan="<?=$total_col?>" class="text-right"><?= lang("total"); ?></th>
							<th class="text-right"><?= '$ '. $this->erp->formatMoney($inv->total); ?></th>
						</tr>
						
						<tr colspan="5">
							<table class="table table-striped">
							
								<tbody>
									<tr>
										<td class="text-left" width="25%">
											<strong><?= lang("current_balance"); ?></strong>
										</td>
										<td><strong><?php echo '$ '. $this->erp->formatMoney($curr_balance); ?></strong></td>
									</tr>
									
									<?php if($payment->extra_paid != 0) { ?>
									<tr>
										<td class="text-left" width="25%">
											<strong><?= lang("paid"); ?></strong>
										</td>
										<td><strong><?php echo '$ '. $this->erp->formatMoney(($payment->amount-$payment->extra_paid)); ?></strong></td>
									</tr>
									<tr>
										<td class="text-left" width="25%">
											<strong><?= lang("extra_paid"); ?></strong>
										</td>
										<td><strong><?php echo '$ '. $this->erp->formatMoney($payment->extra_paid); ?></strong></td>
									</tr>
									<?php } ?>
									<tr>
										<td class="text-left" width="25%">
											<strong><?= $payment->type == 'returned' ? lang("payment_returned") : lang("payment_received"); ?></strong>
										</td>
										<td>
											<strong>
												<?php echo '$ '. $this->erp->formatMoney($payment->amount) .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ( '. $this->erp->convert_number_to_words(($this->erp->fraction($payment->amount) > 0)? $this->erp->formatMoney($payment->amount) : number_format($payment->amount)) .' dolar )'; ?>
											</strong>
										</td>
									</tr>
									
									<tr>
										<td class="text-left" width="25%">
											<strong><?= lang("balance"); ?></strong>
										</td>
										<td><strong><?php echo '$ '. $this->erp->formatMoney($curr_balance - ($payment->amount - $payment->extra_paid)); ?></strong></td>
									</tr>
									<tr>
										<td><strong><?= lang("paid_by"); ?></strong></td>
										<td>
											<strong> : 
												<?php echo lang($payment->paid_by);
													if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC') {
														echo ' (' . $payment->cc_no . ')';
													} elseif ($payment->paid_by == 'Cheque') {
														echo ' (' . $payment->cheque_no . ')';
													}
												?>
											</strong>
										</td>
									</tr>
									<tr>
										<td>
											<strong><?= lang("note"); ?></strong>
										</td>
										<td><strong><?php echo $payment->note; ?></strong></td>
									</tr>
								</tbody>
							
							</table>
						</tr>
					</tfoot>
				</table>
            </div>
			<p class="alert text-center"><?= $this->erp->decode_html($biller->invoice_footer); ?></p>
            <div style="clear: both;"></div>
            <div class="row">
				<div class="col-sm-4 pull-left">
                    <p>&nbsp;</p>

                    <p>&nbsp;</p>

                    <p>&nbsp;</p>

                    <p style="border-bottom: 1px solid #666;">&nbsp;</p>

                    <p><?= lang("customer_signature"); ?></p>
                </div>
				<div class="col-sm-4 pull-left">
                </div>
                <div class="col-sm-4 pull-left">
                    <p>&nbsp;</p>

                    <p>&nbsp;</p>

                    <p>&nbsp;</p>

                    <p style="border-bottom: 1px solid #666;">&nbsp;</p>

                    <p><?= lang("receiver_signature"); ?></p>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>