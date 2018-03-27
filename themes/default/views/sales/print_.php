<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line("purchase") . " " . $inv->reference_no; ?></title>
    <link href="<?php echo $assets ?>styles/theme.css" rel="stylesheet">
    <style type="text/css">
        html, body {
            height: 100%;
            background: #FFF;
        }
		
		.col-xs-5{
			width:50%;
		}

        body:before, body:after {
            display: none !important;
        }

        .table th {
            text-align: center;
            padding: 5px;
        }

        .table td {
            padding: 4px;
        }
		#wrap {
                max-width: 480px;
                margin: 0 auto;
                padding-top: 20px;
            }
		@media print {
                .no-print {
                    display: none;
                }

                #wrap {
                    max-width: 480px;
                    width: 100%;
                    min-width: 250px;
                    margin: 0 auto;
                }
            }

    </style>

    <script type="text/javascript">
        window.print();
    </script>
</head>

<body>

<div id="wrap">
    <div class="row">
	
        <div class="col-lg-12">
		
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px; text-align: center;">
                    <!--<img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>">-->
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                         alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
            <?php } ?>
            <?php
            echo "<p style='text-align:center;'>" . $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country .
                "<br>" . lang("tel") . ": " . $biller->phone . "<br>";
            ?>
            <?php
            /*if ($pos_settings->cf_title1 != "" && $pos_settings->cf_value1 != "") {
                echo $pos_settings->cf_title1 . ": " . $pos_settings->cf_value1 . "<br>";
            }
            if ($pos_settings->cf_title2 != "" && $pos_settings->cf_value2 != "") {
                echo $pos_settings->cf_title2 . ": " . $pos_settings->cf_value2 . "<br>";
            }*/
            echo '</p></div>';
            if ($Settings->invoice_view == 1) { ?>
                <div class="col-sm-12 text-center">
                    <h4 style="font-weight:bold;"><?= lang('tax_invoice'); ?></h4>
                </div>
            <?php }
            echo "<p>" . lang("reference_no") . ": " . $inv->reference_no . "<br>";
            echo lang("customer") . ": " . $inv->customer . "<br>";
            echo lang("date") . ": " . $this->erp->hrld($inv->date) . "</p>";
            ?>
            <div style="clear:both;"></div>
			<!--
			<div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                    <p class="bold">
                        <?= lang("ref"); ?>: <?= $inv->reference_no; ?><br>
                        <?= lang("date"); ?>: <?= $this->erp->hrld($inv->date); ?><br>
                        <?= lang("sale_status"); ?>: <?= lang($inv->sale_status); ?><br>
                        <?= lang("payment_status"); ?>: <?= lang($inv->payment_status); ?>
                    </p>
                    </div>
					<!--
                    <div class="col-xs-7 text-right">
                        <?php $br = $this->erp->save_barcode($inv->reference_no, 'code39', 70, false); ?>
                        <img src="<?= base_url() ?>assets/uploads/barcode<?= $this->session->userdata('user_id') ?>.png"
                             alt="<?= $inv->reference_no ?>"/>
                        <?php $this->erp->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2); ?>
                        <img src="<?= base_url() ?>assets/uploads/qrcode<?= $this->session->userdata('user_id') ?>.png"
                             alt="<?= $inv->reference_no ?>"/>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
			
            <div class="row padding10">
                <div class="col-xs-5" style="float: left;">
                    <h4 class=""><b><?= $biller->company != '-' ? $biller->company : $biller->name; ?></b></h4>
                    <?= $biller->company ? "" : "Attn: " . $biller->name ?>
                    <?php
                    echo $biller->address . "<br />" . $biller->city . " " . $biller->postal_code . " " . $biller->state . "<br />" . $biller->country;
                    echo "<p>";
                    if ($biller->cf1 != "-" && $biller->cf1 != "") {
                        echo "<br>" . lang("bcf1") . ": " . $biller->cf1;
                    }
                    if ($biller->cf2 != "-" && $biller->cf2 != "") {
                        echo "<br>" . lang("bcf2") . ": " . $biller->cf2;
                    }
                    if ($biller->cf3 != "-" && $biller->cf3 != "") {
                        echo "<br>" . lang("bcf3") . ": " . $biller->cf3;
                    }
                    if ($biller->cf4 != "-" && $biller->cf4 != "") {
                        echo "<br>" . lang("bcf4") . ": " . $biller->cf4;
                    }
                    if ($biller->cf5 != "-" && $biller->cf5 != "") {
                        echo "<br>" . lang("bcf5") . ": " . $biller->cf5;
                    }
                    if ($biller->cf6 != "-" && $biller->cf6 != "") {
                        echo "<br>" . lang("bcf6") . ": " . $biller->cf6;
                    }
                    echo "</p>";
                    echo lang("tel") . ": " . $biller->phone . "<br />" . lang("email") . ": " . $biller->email;
                    ?>
                    <div class="clearfix"></div>
                </div>
                <div class="col-xs-5"  style="float: right;">
                    <h4 class=""><b><?= $customer->company ? $customer->company : $customer->name; ?></b></h4>
                    <?= $customer->company ? "" : "Attn: " . $customer->name ?>
                    <?php
                    echo $customer->address . "<br />" . $customer->city . " " . $customer->postal_code . " " . $customer->state . "<br />" . $customer->country;
                    echo "<p>";
                    if ($customer->cf1 != "-" && $customer->cf1 != "") {
                        echo "<br>" . lang("ccf1") . ": " . $customer->cf1;
                    }
                    if ($customer->cf2 != "-" && $customer->cf2 != "") {
                        echo "<br>" . lang("ccf2") . ": " . $customer->cf2;
                    }
                    if ($customer->cf3 != "-" && $customer->cf3 != "") {
                        echo "<br>" . lang("ccf3") . ": " . $customer->cf3;
                    }
                    if ($customer->cf4 != "-" && $customer->cf4 != "") {
                        echo "<br>" . lang("ccf4") . ": " . $customer->cf4;
                    }
                    if ($customer->cf5 != "-" && $customer->cf5 != "") {
                        echo "<br>" . lang("ccf5") . ": " . $customer->cf5;
                    }
                    if ($customer->cf6 != "-" && $customer->cf6 != "") {
                        echo "<br>" . lang("ccf6") . ": " . $customer->cf6;
                    }
                    echo "</p>";
                    echo lang("tel") . ": " . $customer->phone . "<br />" . lang("email") . ": " . $customer->email;
                    ?>
                </div>
            </div>
			<!--
            <div class="clearfix"></div>
            <div class="row padding10">
                <div class="col-xs-5" style="float: left;">
                    <span class="bold"><?= $Settings->site_name; ?></span><br>
                    <?= $warehouse->name ?>

                    <?php
                    echo $warehouse->address . "<br>";
                    echo ($warehouse->phone ? lang("tel") . ": " . $warehouse->phone . "<br>" : '') . ($warehouse->email ? lang("email") . ": " . $warehouse->email : '');
                    ?>
                    <div class="clearfix"></div>
                </div>
                <div class="col-xs-5" style="float: right;">
                    <div class="bold">
                        <?= lang("date"); ?>: <?= $this->erp->hrld($inv->date); ?><br>
                        <?= lang("ref"); ?>: <?= $inv->reference_no; ?>
                        <div class="clearfix"></div>
                        <?php $this->erp->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 1); ?>
                        <img src="<?= base_url() ?>assets/uploads/qrcode<?= $this->session->userdata('user_id') ?>.png"
                             alt="<?= $inv->reference_no ?>" class="pull-right"/>
                        <?php $br = $this->erp->save_barcode($inv->reference_no, 'code39', 50, false); ?>
                        <img src="<?= base_url() ?>assets/uploads/barcode<?= $this->session->userdata('user_id') ?>.png"
                             alt="<?= $inv->reference_no ?>" class="pull-left"/>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
			-->
		
            <div class="clearfix"></div>
            <div class="-table-responsive">
                <table class="table table-striped table-condensed" style="width: 100%;">
                    <tbody style="font-size: 13px;">
                    <?php $r = 1;
                    foreach ($rows as $row):
							echo '<tr><td>#' . $r . ': &nbsp;&nbsp;' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '') . '</td></tr>';
							
							echo '<tr><td style="border-right:none"><span class="text-left">' . $this->erp->formatQuantity($row->quantity) . ' x ';

							if ($row->item_discount != 0) {
								echo '<del>' . $this->erp->formatMoney($row->net_unit_price + ($row->item_discount / $row->quantity) + ($row->item_tax / $row->quantity)) . '</del> ';
							}

							echo $this->erp->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)) . '</span><span class="pull-right" style="border-left:none">' . $this->erp->formatMoney($row->subtotal) . '</td></tr>';
							
							?>

                            <?php
							/*
                            if ($Settings->product_serial) {
                                echo '<td>' . $row->serial_no . '</td>';
                            }
							
                            ?>
                            <td style="text-align:right; width:90px; vertical-align:middle;"><?= $this->erp->formatMoney($row->real_unit_price); ?></td>
                            <?php
							/*
                            if ($Settings->tax1) {
                                echo '<td style="width: 90px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->erp->formatMoney($row->item_tax) . '</td>';
                            }
							*/
							/*
                            if ($Settings->product_discount) {
                                echo '<td style="width: 90px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->erp->formatMoney($row->item_discount) . '</td>';
                            }
							*/
                            ?>
                            <!--<td style="vertical-align:middle; text-align:right; width:110px;"><?= $this->erp->formatMoney($row->subtotal); ?></td>-->
                        <?php
						
                        $r++;
                    endforeach;
                    ?>
                    </tbody>
                    <tfoot style="font-size: 13px;">
                    <?php
                    $col = 2;
                    if ($Settings->product_serial) {
                        $col++;
                    }
                    if ($Settings->product_discount) {
                        $col++;
                    }
                    if ($Settings->tax1) {
                        $col++;
                    }
                    if ($Settings->product_discount && $Settings->tax1) {
                        $tcol = $col - 2;
                    } elseif ($Settings->product_discount) {
                        $tcol = $col - 1;
                    } elseif ($Settings->tax1) {
                        $tcol = $col - 1;
                    } else {
                        $tcol = $col;
                    }
                    ?>
                    <?php if ($inv->grand_total != $inv->total) { ?>
                        <tr>
                            <td style="font-weight:bold;border-top: 3px solid #ddd;"><?= lang("total"); ?>
                                (<?= $default_currency->code; ?>)
								<span style="text-align:right;" class="pull-right"><?= $this->erp->formatMoney($inv->total + $inv->product_tax); ?></span>
                            </td>
                            <?php
							/*
                            if ($Settings->tax1) {
                                echo '<td style="text-align:right;">' . $this->erp->formatMoney($inv->product_tax) . '</td>';
                            }
                            if ($Settings->product_discount) {
                                echo '<td style="text-align:right;">' . $this->erp->formatMoney($inv->product_discount) . '</td>';
                            } */
                            ?>
                            <!--<td style="text-align:right;"><?= $this->erp->formatMoney($inv->total + $inv->product_tax); ?></td>-->
                        </tr>
                    <?php } ?>
                    <?php if ($return_sale && $return_sale->surcharge != 0) {
                        echo '<tr><td style="font-weight:bold;">' . lang("surcharge") . ' (' . $default_currency->code . ')<span style="text-align:right;" class="pull-right">' . $this->erp->formatMoney($return_sale->surcharge) . '</span></tr>';
                    }
                    ?>
                    <?php if ($inv->order_discount != 0) {
                        echo '<tr><td style="font-weight:bold;">' . lang("order_discount") . ' (' . $default_currency->code . ')<span style="text-align:right;" class="pull-right">' . $this->erp->formatMoney($inv->order_discount) . '</span></td></tr>';
                    }
                    ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td style="font-weight:bold;">' . lang("order_tax") . ' (' . $default_currency->code . ')<span style="text-align:right;" class="pull-right">' . $this->erp->formatMoney($inv->order_tax) . '</span></tr>';
                    }
                    ?>
                    <?php if ($inv->shipping != 0) {
                        echo '<tr><td style="font-weight:bold;">' . lang("shipping") . ' (' . $default_currency->code . ')<span style="text-align:right;" class="pull-right">' . $this->erp->formatMoney($inv->shipping) . '</span></tr>';
                    }
                    ?>
					
					<?php print_r($payments);
					$pos_paid = 0;
					if($payments){
						foreach($payments as $payment) {
							$pos_paid = $payment->pos_paid;
						}
					}
					?>
					
                    <tr>
                        <td style="font-weight:bold;"><?= lang("total_amount"); ?>
                            (<?= $default_currency->code; ?>)
							<span style="text-align:left; font-weight:bold;" class="pull-right"><?= $this->erp->formatMoney($inv->grand_total); ?></span>
                        </td>
                        
                    </tr>

                    <tr>
                        <td style="font-weight:bold;"><?= lang("paid"); ?>
                            (<?= $default_currency->code; ?>)
							<span style="text-align:right; font-weight:bold;" class="pull-right"><?= $this->erp->formatMoney($pos_paid); ?></span>
                        </td>
                        
                    </tr>
                    <tr>
                        <td style="font-weight:bold;"><?= lang("balance"); ?>
                            (<?= $default_currency->code; ?>)
							<span style="text-align:right; font-weight:bold;" class="pull-right"><?= $this->erp->formatMoney($pos_paid - $inv->grand_total); ?></span>
                        </td>
                        
                    </tr>

                    </tfoot>
                </table>
            </div> 
			<!--
            <div class="row">
                <div class="col-xs-12">
                    <?php if ($inv->note || $inv->note != "") { ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang("note"); ?>:</p>

                            <div><?= $this->erp->decode_html($inv->note); ?></div>
                        </div>
                    <?php } ?>
                </div>
                <div class="clearfix"></div>
                <div class="col-xs-4  pull-left" style="float: left;">
                    <p style="height: 80px;"><?= lang("seller"); ?>
                        : <?= $biller->company != '-' ? $biller->company : $biller->name; ?> </p>
                    <hr>
                    <p><?= lang("stamp_sign"); ?></p>
                </div>
                <div class="col-xs-4  pull-right" style="float: right;">
                    <p style="height: 80px;"><?= lang("customer"); ?>
                        : <?= $customer->company ? $customer->company : $customer->name; ?> </p>
                    <hr>
                    <p><?= lang("stamp_sign"); ?></p>
                </div>
            </div>
			-->
        </div>
    </div>
</div>
</body>
</html>