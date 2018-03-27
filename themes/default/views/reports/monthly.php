<style type="text/css">
    .dfTable th, .dfTable td {
        text-align: center;
        vertical-align: middle;
    }

    .dfTable td {
        padding: 2px;
    }

    .data tr:nth-child(odd) td {
        color: #2FA4E7;
    }

    .data tr:nth-child(even) td {
        text-align: right;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('monthly_sales'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <?php if (!empty($warehouses) && !$this->session->userdata('warehouse_id')) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?=lang("warehouses")?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?=site_url('reports/monthly_sales/0/'.$year)?>"><i class="fa fa-building-o"></i> <?=lang('all_warehouses')?></a></li>
                            <li class="divider"></li>
                            <?php
                                foreach ($warehouses as $warehouse) {
                                        echo '<li><a href="' . site_url('reports/monthly_sales/'.$warehouse->id.'/'.$year) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                    }
                                ?>
                        </ul>
                    </li>
                <?php } ?>
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("reports_calendar_text") ?></p>

                <div class="table-responsive" id="style">
                    <table class="table table-bordered table-striped dfTable reports-table">
                        <thead>
							<tr class="year_roller">
								<th><a class="white" href="reports/monthly_sales/<?php echo $year - 1; ?>">&lt;&lt;</a></th>
								<th colspan="10"> <?php echo $year; ?></th>
								<th><a class="white" href="reports/monthly_sales/<?php echo $year + 1; ?>">&gt;&gt;</a></th>
							</tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/01'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_january"); ?>
                                </a>
                            </td>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/02'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_february"); ?>
                                </a>
                            </td>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/03'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_march"); ?>
                                </a>
                            </td>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/04'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_april"); ?>
                                </a>
                            </td>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/05'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_may"); ?>
                                </a>
                            </td>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/06'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_june"); ?>
                                </a>
                            </td>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/07'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_july"); ?>
                                </a>
                            </td>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/08'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_august"); ?>
                                </a>
                            </td>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/09'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_september"); ?>
                                </a>
                            </td>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/10'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_october"); ?>
                                </a>
                            </td>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/11'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_november"); ?>
                                </a>
                            </td>
                            <td class="bold text-center">
                                <a href="<?= site_url('reports/monthly_profit/'.$year.'/12'); ?>" data-toggle="modal" data-target="#myModal">
                                    <?= lang("cal_december"); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <?php 
							$idex=array();
							$key=array();
							$total_cost=array();
							foreach($total_qty as $value){
								   $index[$value->date] =$value->total_qty;
							} 
							foreach($total_return as $return){
								 $key[$return->date] =$return->return_total;
								 $total_cost[$return->date] =$return->total_cost; 
							} 
							$ex=array();
							foreach($Exespence as $exespen){
								$ex[$exespen->date] =$exespen->amount;
							}
                            if (!empty($sales)){
								$array =array();
                                foreach ($sales as $value){ 
								  
                                    $array[$value->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tbody>
									<tr><td>" . $this->lang->line("quantity") . 
									"</td></tr><tr><td>" . $this->erp->formatMoney($index[$value->date]) . "</td></tr><tr><td>" . $this->lang->line("Sales' Revenue") . "</td></tr><tr><td>" . $this->erp->formatMoney($value->total) .  "</td></tr><tr><td>" . $this->lang->line("Order Discount") . "</td></tr><tr><td>" . $this->erp->formatMoney($value->discount) .  "</td></tr><tr><td>" . $this->lang->line("Sales Refund") . "</td></tr><tr><td>" . $this->erp->formatMoney($key[$value->date]).  "</td></tr><tr><td>" . $this->lang->line("Products' Cost") . "</td></tr><tr><td>" . $this->erp->formatMoney($value->total_cost-$total_cost[$value->date]) .  "</td></tr><tr><td>" . $this->lang->line("Expenses") . "</td></tr><tr><td>" . $this->erp->formatMoney($ex[$value->date]) .  "</td></tr><tr><td>" . $this->lang->line("Profit") . "</td></tr><tr><td>" . $this->erp->formatMoney($value->total-$value->discount-$key[$value->date]-($value->total_cost-$total_cost[$value->date])-$ex[$value->date]) .  "</td></tr></tbody></table>";
                                }
                                 
                                for ($i = 1; $i <= 12; $i++) {
                                    echo '<td width="8.3%">';
                                    if (isset($array[$i])) {
                                        echo $array[$i];
                                    } else {
                                        echo '<strong>0</strong>';
                                    }
                                    echo '</td>';
                                }
                            } else {
                                for ($i = 1; $i <= 12; $i++) {
                                    echo '<td width="8.3%"><strong>0</strong></td>';
                                }
                            }
                            ?>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/monthly_sales/'.$year.'/pdf')?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
		if ($(window).width() < 1024) {
		    $('#style').css('width', '100%');
			$('#style').css('overflow-x', 'scroll');
			$('#style').css('white-space', 'nowrap');
		}
    });
</script>
