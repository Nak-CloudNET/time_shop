<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <base href="<?= site_url() ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $page_title ?> -
            <?= $Settings->site_name ?>
    </title>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png" />
    <link href="<?= $assets ?>styles/theme.css" rel="stylesheet" />
    <link href="<?= $assets ?>styles/style.css" rel="stylesheet" />
    <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>

    <noscript>
        <style type="text/css">
            #loading {
                display: none;
            }
        </style>
    </noscript>
    <?php if ($Settings->rtl) { ?>
        <link href="<?= $assets ?>styles/helpers/bootstrap-rtl.min.css" rel="stylesheet" />
        <link href="<?= $assets ?>styles/style-rtl.css" rel="stylesheet" />
        <script type="text/javascript">
            $(document).ready(function () {
                $('.pull-right, .pull-left').addClass('flip');
            });
        </script>
    <?php } ?>
	<script type="text/javascript">
		$(window).load(function () {
			$("#loading").fadeOut("slow");
		});
	</script>
</head>

<body>
    <noscript>
        <div class="global-site-notice noscript">
            <div class="notice-inner">
                <p><strong>JavaScript seems to be disabled in your browser.</strong>
                    <br>You must have JavaScript enabled in your browser to utilize the functionality of this website.</p>
            </div>
        </div>
    </noscript>
    <div id="loading"></div>
    <div id="app_wrapper">
        <header id="header" class="navbar">
            <div class="container">
                <a class="navbar-brand" href="<?= site_url() ?>"><span class="logo"><?= $Settings->site_name ?></span></a>

                <div class="btn-group visible-xs pull-right btn-visible-sm">
                    <a class="btn bdarkGreen" style="margin-left:10px !important;margin-right:10px !important;margin-top:1px !important;padding-right:10px !important" title="<?= lang('pos') ?>" data-placement="left" href="<?= site_url('pos') ?>">
                        <i class="fa fa-th-large"></i> <span class="padding02"><?= lang('pos') ?></span>
                    </a>

                    <button class="navbar-toggle btn" type="button" data-toggle="collapse" data-target="#sidebar_menu">
                        <span class="fa fa-bars"></span>
                    </button>

                    <a href="<?= site_url('users/profile/' . $this->session->userdata('user_id')); ?>" class="btn">
                        <span class="fa fa-user"></span>
                    </a>
                    <a href="<?= site_url('logout'); ?>" class="btn">
                        <span class="fa fa-sign-out"></span>
                    </a>
                </div>
                <div class="header-nav">
                    <ul class="nav navbar-nav pull-right">
                        <li class="dropdown">
                            <a class="btn account dropdown-toggle" data-toggle="dropdown" href="#">
								<img alt="" src="<?= $this->session->userdata('avatar') ? site_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : $assets . 'images/' . $this->session->userdata('gender') . '.png'; ?>" class="mini_avatar img-rounded">                        
								<br>
								<div class="user">
									<p><?= $this->session->userdata('username'); ?></p>
								</div>
							</a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="<?= site_url('users/profile/' . $this->session->userdata('user_id')); ?>">
                                        <i class="fa fa-user"></i>
                                        <?= lang('profile'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= site_url('users/profile/' . $this->session->userdata('user_id') . '/#cpassword'); ?>"><i class="fa fa-key"></i> <?= lang('change_password'); ?>
                                </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="<?= site_url('logout'); ?>">
                                        <i class="fa fa-sign-out"></i>
                                        <?= lang('logout'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav pull-right">
                        <li class="dropdown hidden-xs"><a class="btn tip" title="<?= lang('dashboard') ?>" data-placement="left" href="<?= site_url('welcome') ?>"><i class="fa fa-dashboard"></i><p><?= lang('dashboard') ?></p></a>
						</li>
                        <?php if ($Owner) { ?>
                            <li class="dropdown hidden-sm">
                                <a class="btn tip" title="<?= lang('settings') ?>" data-placement="left" href="<?= site_url('system_settings') ?>">
                                    <i class="fa fa-cogs"></i><p><?= lang('settings') ?></p>
                                </a>
                            </li>
                            	
                        <?php } ?>
						<li class="dropdown hidden-xs">
							<a class="btn tip" title="<?= lang('calculator') ?>" data-placement="left" href="#" data-toggle="dropdown">
								<i class="fa fa-calculator"></i><p><?= lang('calculator') ?></p>
							</a>
							<ul class="dropdown-menu pull-right calc">
								<li class="dropdown-content">
									<span id="inlineCalc"></span>
								</li>
							</ul>
						</li>
                                	
						<?php if ($info) { ?>
						<li class="dropdown hidden-sm">
							<a class="btn tip" title="<?= lang('notifications') ?>" data-placement="left" href="#" data-toggle="dropdown">
								<i class="fa fa-comments"></i><p><?= lang('notifications') ?></p>
								<span class="number blightOrange black"><?= sizeof($info) ?></span>
							</a>
							<ul class="dropdown-menu pull-right content-scroll">
								<li class="dropdown-header"><i class="fa fa-comments"></i>
									<?= lang('notifications'); ?>
								</li>
								<li class="dropdown-content">
									<div class="scroll-div">
										<div class="top-menu-scroll">
											<ol class="oe">
												<?php foreach ($info as $n) {
													echo '<li>' . $n->comment . '</li>';
												} ?>
											</ol>
										</div>
									</div>
								</li>
							</ul>
						</li>
						<?php } ?>
									<?php if ($events) { ?>
										<li class="dropdown hidden-xs">
											<a class="btn tip" title="<?= lang('calendar') ?>" data-placement="left" href="#" data-toggle="dropdown">
												<i class="fa fa-calendar"></i><p><?= lang('Calendar') ?></p>
												<span class="number blightOrange black"><?= sizeof($events) ?>Calander Me</span>
											</a>
											<ul class="dropdown-menu pull-right content-scroll">
												<li class="dropdown-header">
													<i class="fa fa-calendar"></i><p><?= lang('calendar') ?></p>
													<?= lang('upcoming_events'); ?>
												</li>
												<li class="dropdown-content">
													<div class="top-menu-scroll">
														<ol class="oe">
															<?php foreach ($events as $event) {
											echo '<li><strong>' . date($dateFormats['php_sdate'], strtotime($event->date)) . ':</strong><br>' . $this->erp->decode_html($event->data) . '</li>';
										} ?>
														</ol>
													</div>
												</li>
												<li class="dropdown-footer">
													<a href="<?= site_url('calendar') ?>" class="btn-block link">
														<i class="fa fa-calendar"></i><p><?= lang('calendar') ?></p>
													</a>
												</li>
											</ul>
										</li>
									<?php } else { ?>
										<li class="dropdown hidden-xs">
											<a class="btn tip" title="<?= lang('calendar') ?>" data-placement="left" href="<?= site_url('calendar') ?>">
												<i class="fa fa-calendar"></i><p><?= lang('calendar') ?></p>
											</a>
										</li>
									<?php } ?>
									<li class="dropdown hidden-sm">
										<a class="btn tip" title="<?= lang('styles') ?>" data-placement="left" data-toggle="dropdown" href="#">
											<i class="fa fa-css3"></i><p><?= lang('styles') ?></p>
										</a>
										<ul class="dropdown-menu pull-right">
											<li class="bwhite noPadding">
												<a href="#" id="fixed" class="">
													<i class="fa fa-angle-double-left"></i>
													<span id="fixedText">Fixed</span>
												</a>
												<a href="#" id="cssLight" class="grey">
													<i class="fa fa-stop"></i> Grey
												</a>
												<a href="#" id="cssBlue" class="blue">
													<i class="fa fa-stop"></i> Blue
												</a>
												<a href="#" id="cssBlack" class="black">
													<i class="fa fa-stop"></i> Black
												</a>
												<a href="#" id="cssPurpie" class="purple">
													<i class="fa fa-stop"></i> Purple
												</a>
												<a href="#" id="cssGreen" class="green">
													<i class="fa fa-stop"></i> Green
												</a>
											</li>
										</ul>
									</li>
									<li class="dropdown hidden-xs">
										<a class="btn tip" title="<?= lang('language') ?>" data-placement="left" data-toggle="dropdown" href="#"><img src="<?= base_url('assets/images/' . $Settings->language . '.png'); ?>" alt=""><p><?= lang('language') ?></p></a>
										<ul class="dropdown-menu pull-right">
											<?php $scanned_lang_dir = array_map(function ($path) {
												return basename($path);
											}, glob(APPPATH . 'language/*', GLOB_ONLYDIR));
											foreach ($scanned_lang_dir as $entry) { ?>
												<li>
													<a href="<?= site_url('welcome/language/' . $entry); ?>">
													<img src="<?= base_url(); ?>assets/images/<?= $entry; ?>.png" class="language-img"> &nbsp;&nbsp;<?= ucwords($entry); ?>
													</a>
												</li>
											<?php } ?>
										</ul>

									</li>
									<?php if ($Owner && $Settings->update) { ?>
										<li class="dropdown hidden-sm">
											<a class="btn blightOrange tip" title="<?= lang('update_available') ?>" data-placement="bottom" data-container="body" href="<?= site_url('system_settings/updates') ?>">
												<i class="fa fa-download"></i>
											</a>
										</li>
									<?php } ?>
									<?php if (($Owner || $Admin) || ($qty_alert_num > 0 || $exp_alert_num > 0 || !empty($payment_customer_alert_num) || !empty($payment_purchase_alert_num) || !empty($delivery_alert_num))) { ?>
										<li class="dropdown hidden-sm">
											<a class="btn blightOrange tip" title="<?= lang('alerts') ?>" data-placement="left" data-toggle="dropdown" href="#">
												<i class="fa fa-exclamation-triangle"></i><p><?= lang('alerts') ?></p>
											</a>
											<ul class="dropdown-menu pull-right">
												<li>
													<a href="<?= site_url('reports/quantity_alerts') ?>" class="">
														<span class="label label-danger pull-right" style="margin-top:3px;"><?= $qty_alert_num; ?></span>
														<span style="padding-right: 35px;"><?= lang('quantity_alerts') ?></span>
													</a>
												</li>
												<li>
													<a href="<?= site_url('reports/expiry_alerts') ?>" class="">
														<span class="label label-danger pull-right" style="margin-top:3px;"><?= $exp_alert_num; ?></span>
														<span style="padding-right: 35px;"><?= lang('expiry_alerts') ?></span>
													</a>
												</li>

												<li>
													<?php foreach($payment_customer_alert_num as $customer_payment) {} ?>
													<a href="<?= site_url('sales/?d='. date('Y-m-d', strtotime($payment_customer_alert_num->date))) ?>" class="">
														<span class="label label-danger pull-right" style="margin-top:3px;"><?= $payment_customer_alert_num->alert_num; ?></span>
														<span style="padding-right: 35px;"><?= lang('customer_payment_alerts') ?></span>
													</a>
												</li>
												<li>
													<?php foreach($payment_purchase_alert_num as $purchase_payment) {} ?>
													<a href="<?= site_url('purchases/?d='. date('Y-m-d', strtotime($payment_purchase_alert_num->date))) ?>" class="">
														<span class="label label-danger pull-right" style="margin-top:3px;"><?= $payment_purchase_alert_num->alert_num; ?></span>
														<span style="padding-right: 35px;"><?= lang('supplier_payment_alerts') ?></span>
													</a>
												</li><?php if($pos_settings->show_suspend_bar){ ?>
												<li>
													<a href="<?= site_url('sales/suspend/?d='. date('Y-m-d', strtotime($sale_suspend_alert_num->date))) ?>" class="">
														<span class="label label-danger pull-right" style="margin-top:3px;"><?= $sale_suspend_alert_num->alert_num; ?></span>
														<span style="padding-right: 35px;"><?= lang('sale_suspend_alerts') ?></span>
													</a>
												</li><?php } ?>
												<!-- Delivery Alert -->
												<li>
													<a href="<?= site_url('sales/deliveries_alerts/'. date('Y-m-d', strtotime($delivery_alert_num->date))) ?>" class="">
														<span class="label label-danger pull-right" style="margin-top:3px;"><?= $delivery_alert_num->alert_num; ?></span>
														<span style="padding-right: 35px;"><?= lang('deliveries_alerts') ?></span>
													</a>
												</li>
												
												<!-- Customer Alert -->
												<li>
													<a href="<?= site_url('sales/customers_alerts/') ?>" class="">
														<span class="label label-danger pull-right" style="margin-top:3px;"><?= $customers_alert_num; ?></span>
														<span style="padding-right: 35px;"><?= lang('customers_alerts') ?></span>
													</a>
												</li>

											</ul>
										</li>
									<?php } ?>
									<?php if (POS) { ?>
										<li class="dropdown hidden-xs">
											<a class="btn bdarkGreen tip" title="<?= lang('pos') ?>" data-placement="left" href="<?= site_url('pos') ?>">
												<i class="fa fa-th-large"></i><p><?= lang('pos') ?></p>
											</a>
										</li>
									<?php } ?>
									<?php if ($Owner) { ?>
										<li class="dropdown">
											<a class="btn bdarkGreen tip" id="today_profit" title="<span><?= lang('today_profit') ?></span>" data-placement="bottom" data-html="true" href="<?= site_url('reports/profit') ?>" data-toggle="modal" data-target="#myModal">
												<i class="fa fa-hourglass-2"></i><p><?= lang('profit') ?></p>
											</a>
										</li>
									<?php } ?>
									<?php if ($Owner || $Admin) { ?>
																																						<?php if (POS) { ?>
																																							<li class="dropdown hidden-xs">
																																								<a class="btn bblue tip" title="<?= lang('list_open_registers') ?>" data-placement="bottom" href="<?= site_url('pos/registers') ?>">
																																									<i class="fa fa-list"></i><p><?= lang('register'); ?></p>                                                                                                
																																								</a>
																																							</li>
																																						<?php } ?>
																																					<?php } ?>
																																		<li class="dropdown hidden-xs">
																																			<a class="btn bred tip" title="<?= lang('clear_ls') ?>" data-placement="bottom" id="clearLS" href="#">
																																				<i class="fa fa-eraser"></i><p><?= lang('clear') ?></p> 
																																			</a>
																																		</li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="container bblack" id="container">
            <div class="row" id="main-con">
                <div id="sidebar-left" class="col-lg-2 col-md-2">
                    <div class="sidebar-nav nav-collapse collapse navbar-collapse" id="sidebar_menu">
                        <ul class="nav main-menu">
                            <li class="mm_welcome">
                                <a href="<?= site_url() ?>">
                                    <i class="fa fa-dashboard"></i>
                                    <span class="text"> <?= lang('dashboard'); ?></span>
                                </a>
                            </li>

                            <?php if ($Owner || $Admin) { ?>

                                <li class="mm_products">
                                    <a class="dropmenu" href="#">
                                        <i class="fa fa-barcode"></i>
                                        <span class="text"> <?= lang('manage_products'); ?> </span>
                                        <span class="chevron closed"></span>
                                    </a>
                                    <ul>
                                        <li id="products_index" class="sub_navigation">
                                            <a class="submenu" href="<?= site_url('products'); ?>">
                                                <i class="fa fa-barcode"></i>
                                                <span class="text"> <?= lang('list_products'); ?></span>
                                            </a>
                                        </li>
                                        
										<li id="products_add" class="sub_navigation">
                                            <a class="submenu" href="<?= site_url('products/add'); ?>">
                                                <i class="fa fa-plus-circle"></i>
                                                <span class="text"> <?= lang('add_product'); ?></span>
                                            </a>
                                        </li>
										
                                        <li id="products_print_barcodes" class="sub_navigation">
                                            <a class="submenu" href="<?= site_url('products/print_barcodes'); ?>">
                                                <i class="fa fa-tags"></i>
                                                <span class="text"> <?= lang('print_barcode_label'); ?></span>
                                            </a>
                                        </li>
										
                                        <li id="products_quantity_adjustments" class="sub_navigation">
                                            <a class="submenu" href="<?= site_url('products/quantity_adjustments'); ?>">
                                                <i class="fa fa-filter"></i>
                                                <span class="text"> <?= lang('adjustment_quantity'); ?></span>
                                            </a>
                                        </li>
										
										<li id="products_product_serial" class="sub_navigation">
                                            <a class="submenu" href="<?= site_url('products/product_serial'); ?>">
                                                <i class="fa fa-file-text"></i>
                                                <span class="text"> <?= lang('product_serial'); ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
								
                                <li class="mm_sales <?= strtolower($this->router->fetch_method()) == 'settings' ? '' : 'mm_pos' ?>">
                                    <a class="dropmenu" href="#">
                                        <i class="fa fa-heart"></i>
                                        <span class="text"> 
											<?= lang('manage_sales'); ?> 
										</span> 
										<span class="chevron closed"></span>
                                    </a>
                                    <ul>
										<?php if (POS) { ?>
                                            <li id="pos_sales">
                                                <a class="submenu" href="<?= site_url('pos/sales'); ?>">
                                                    <i class="fa fa-heart"></i>
                                                    <span class="text"> <?= lang('pos_sales'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
										
                                        <li id="sales_return_sales">
                                            <a class="submenu" href="<?= site_url('sales/return_sales'); ?>">
                                                <i class="fa fa-reply"></i>
                                                <span class="text"> <?= lang('list_sales_return'); ?></span>
                                            </a>
                                        </li>
										
										<li id="sales_product_serial">
                                            <a class="submenu" href="<?= site_url('sales/product_serial'); ?>">
                                                <i class="fa fa-plus-circle"></i>
                                                <span class="text"> <?= lang('list_product_serial'); ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="mm_quotes">
                                    <a class="dropmenu" href="#">
                                        <i class="fa fa-heart-o"></i>
                                        <span class="text"> <?= lang('manage_quotes'); ?> </span>
                                        <span class="chevron closed"></span>
                                    </a>
                                    <ul>
                                        <li id="quotes_index">
                                            <a class="submenu" href="<?= site_url('quotes'); ?>">
                                                <i class="fa fa-heart-o"></i>
                                                <span class="text"> <?= lang('list_quotes'); ?></span>
                                            </a>
                                        </li>
                                        <li id="quotes_add">
                                            <a class="submenu" href="<?= site_url('quotes/add'); ?>">
                                                <i class="fa fa-plus-circle"></i>
                                                <span class="text"> <?= lang('add_quote'); ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="mm_purchases mm_purchases_order">
                                    <a class="dropmenu" href="#">
                                        <i class="fa fa-star"></i>
                                        <span class="text"> 
											<?= lang('manage_purchases'); ?> 
										</span> 
										<span class="chevron closed"></span>
                                    </a>
                                    <ul>
										<li id="purchases_order_index">
                                            <a class="submenu" href="<?= site_url('purchases_order'); ?>">
                                                <i class="fa fa-star"></i>
                                                <span class="text"> <?= lang('list_purchase_order'); ?></span>
                                            </a>
                                        </li>
										
										<li id="purchases_order_add">
                                            <a class="submenu" href="<?= site_url('purchases_order/add'); ?>">
                                                <i class="fa fa-plus-circle"></i>
                                                <span class="text"> <?= lang('add_purchase_order'); ?></span>
                                            </a>
                                        </li>
                                        
										<li id="purchases_index">
                                            <a class="submenu" href="<?= site_url('purchases'); ?>">
                                                <i class="fa fa-star"></i>
                                                <span class="text"> <?= lang('list_purchases'); ?></span>
                                            </a>
                                        </li>
                                        
										<li id="purchases_add">
                                            <a class="submenu" href="<?= site_url('purchases/add'); ?>">
                                                <i class="fa fa-plus-circle"></i>
                                                <span class="text"> <?= lang('add_purchase'); ?></span>
                                            </a>
                                        </li>
										
                                        <li id="purchases_return_purchases">
                                            <a class="submenu" href="<?= site_url('purchases/return_purchases'); ?>">
                                                <i class="fa fa-reply"></i>
                                                <span class="text"> <?= lang('list_purchases_return'); ?></span>
                                            </a>
                                        </li>
                                        
                                        <li id="purchases_expenses">
                                            <a class="submenu" href="<?= site_url('purchases/expenses'); ?>">
                                                <i class="fa fa-dollar"></i>
                                                <span class="text"> <?= lang('list_expenses'); ?></span>
                                            </a>
                                        </li>
										
                                        <li id="purchases_add_expense">
                                            <a class="submenu" href="<?= site_url('purchases/add_expense'); ?>" data-toggle="modal" data-target="#myModal">
                                                <i class="fa fa-plus-circle"></i>
                                                <span class="text"> <?= lang('add_expense'); ?></span>
                                            </a>
                                        </li>
									</ul>
                                </li>

                                <li class="mm_transfers">
                                    <a class="dropmenu" href="#">
                                        <i class="fa fa-star-o"></i>
                                        <span class="text"> <?= lang('manage_transfers'); ?> </span>
                                        <span class="chevron closed"></span>
                                    </a>
                                    <ul>
										<li id="transfers_list_in_transfer">
                                            <a class="submenu" href="<?= site_url('transfers/list_in_transfer'); ?>">
                                                <i class="fa fa-star-o"></i><span class="text"> <?= lang('list_transfer'); ?></span>
                                            </a>
                                        </li>
										<li id="transfers_add_in_transfer">
                                            <a class="submenu" href="<?= site_url('transfers/add_in_transfer'); ?>">
                                                <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_transfers'); ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
								
                                <li class="mm_auth mm_customers mm_suppliers mm_billers">
                                    <a class="dropmenu" href="#">
                                        <i class="fa fa-users"></i>
                                        <span class="text"> <?= lang('manage_people'); ?> </span>
                                        <span class="chevron closed"></span>
                                    </a>
                                    <ul>
                                        <?php if ($Owner) { ?>
                                            <li id="auth_users">
                                                <a class="submenu" href="<?= site_url('users'); ?>">
                                                    <i class="fa fa-users"></i><span class="text"> <?= lang('list_users'); ?></span>
                                                </a>
                                            </li>
                                            <li id="auth_create_user">
                                                <a class="submenu" href="<?= site_url('users/create_user'); ?>">
                                                    <i class="fa fa-user-plus"></i><span class="text"> <?= lang('new_user'); ?></span>
                                                </a>
                                            </li>
                                            <li id="billers_index">
                                                <a class="submenu" href="<?= site_url('billers'); ?>">
                                                    <i class="fa fa-users"></i><span class="text"> List Point of Sale</span>
                                                </a>
                                            </li>
                                            <li id="billers_index">
                                                <a class="submenu" href="<?= site_url('billers/add'); ?>" data-toggle="modal" data-target="#myModal">
                                                    <i class="fa fa-plus-circle"></i><span class="text"> Add Point of Sale</span>
                                                </a>
                                            </li>
                                        <?php } ?>
										<li id="customers_index">
											<a class="submenu" href="<?= site_url('customers'); ?>">
												<i class="fa fa-users"></i><span class="text"> <?= lang('list_customers'); ?></span>
											</a>
										</li>
										<li id="customers_index">
											<a class="submenu" href="<?= site_url('customers/add'); ?>" data-toggle="modal" data-target="#myModal">
												<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_customer'); ?></span>
											</a>
										</li>
										<li id="suppliers_index">
											<a class="submenu" href="<?= site_url('suppliers'); ?>">
												<i class="fa fa-users"></i><span class="text"> <?= lang('list_suppliers'); ?></span>
											</a>
										</li>
										<li id="suppliers_index">
											<a class="submenu" href="<?= site_url('suppliers/add'); ?>" data-toggle="modal" data-target="#myModal">
												<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_supplier'); ?></span>
											</a>
										</li>
                                    </ul>
                                </li>
								
								<li class="mm_services">
									<a class="dropmenu" href="#">
										<i class="fa fa-bar-chart-o"></i>
										<span class="text"> <?= lang('services'); ?> </span>
										<span class="chevron closed"></span>
									</a>
									<ul>
										<li id="services_index">
											<a href="<?= site_url('services') ?>">
												<i class="fa fa-bars"></i><span class="text"> <?= lang('list_services'); ?></span>
											</a>
										</li>
										<li id="services_add_service">
											<a href="<?= site_url('services/add_service') ?>">
												<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_service'); ?></span>
											</a>
										</li>
									</ul>
								</li>
								
                                <li class="mm_notifications">
                                    <a class="submenu" href="<?= site_url('notifications'); ?>">
                                        <i class="fa fa-comments"></i><span class="text"> <?= lang('notifications'); ?></span>
                                    </a>
                                </li>
								
                                <li class="mm_documents">
                                    <a class="submenu" href="<?= site_url('documents'); ?>">
                                        <i class="fa fa-book"></i><span class="text"> <?= lang('documents'); ?></span>
                                    </a>
                                </li>
								     
                                <?php if ($Owner) { ?>
                                    <li class="mm_system_settings <?= strtolower($this->router->fetch_method()) != 'settings' ? '' : 'mm_pos' ?>">
                                        <a class="dropmenu" href="#">
                                            <i class="fa fa-cog"></i><span class="text"> <?= lang('settings'); ?> </span>
                                            <span class="chevron closed"></span>
                                        </a>
                                        <ul>
                                            <li id="system_settings_index">
                                                <a href="<?= site_url('system_settings') ?>">
                                                    <i class="fa fa-cog"></i><span class="text"> <?= lang('system_settings'); ?></span>
                                                </a>
                                            </li>
                                            
											<?php if (POS) { ?>
                                                <li id="pos_settings">
                                                    <a href="<?= site_url('pos/settings') ?>">
                                                        <i class="fa fa-th-large"></i><span class="text"> <?= lang('pos_settings'); ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>													
											
											<li id="system_settings_change_logo">
												<a href="<?= site_url('system_settings/change_logo') ?>" data-toggle="modal" data-target="#myModal">
													<i class="fa fa-upload"></i><span class="text"> <?= lang('change_logo'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_currencies">
												<a href="<?= site_url('system_settings/currencies') ?>">
													<i class="fa fa-money"></i><span class="text"> <?= lang('currencies'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_customer_groups">
												<a href="<?= site_url('system_settings/customer_groups') ?>">
													<i class="fa fa-chain"></i><span class="text"> <?= lang('customer_groups'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_price_groups">
												<a href="<?= site_url('system_settings/price_groups') ?>">
													<i class="fa fa-dollar"></i><span class="text"> <?= lang('price_groups'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_categories">
												<a href="<?= site_url('system_settings/categories') ?>">
													<i class="fa fa-sitemap"></i><span class="text"> <?= lang('categories'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_expense_categories">
												<a href="<?= site_url('system_settings/expense_categories') ?>">
													<i class="fa fa-folder-open"></i><span class="text"> <?= lang('expense_categories'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_units">
												<a href="<?= site_url('system_settings/units') ?>">
													<i class="fa fa-wrench"></i><span class="text"> <?= lang('units'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_brands">
												<a href="<?= site_url('system_settings/brands') ?>">
													<i class="fa fa-th-list"></i><span class="text"> <?= lang('brands'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_cases">
												<a href="<?= site_url('system_settings/cases') ?>">
													<i class="fa fa-th-list"></i><span class="text"> <?= lang('cases'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_diameters">
												<a href="<?= site_url('system_settings/diameters') ?>">
													<i class="fa fa-th-list"></i><span class="text"> <?= lang('diameters'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_dials">
												<a href="<?= site_url('system_settings/dials') ?>">
													<i class="fa fa-th-list"></i><span class="text"> <?= lang('dials'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_straps">
												<a href="<?= site_url('system_settings/straps') ?>">
													<i class="fa fa-th-list"></i><span class="text"> <?= lang('straps'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_water_resistance">
												<a href="<?= site_url('system_settings/water_resistance') ?>">
													<i class="fa fa-th-list"></i><span class="text"> <?= lang('water_resistance'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_winding">
												<a href="<?= site_url('system_settings/winding') ?>">
													<i class="fa fa-th-list"></i><span class="text"> <?= lang('winding'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_power_reserve">
												<a href="<?= site_url('system_settings/power_reserve') ?>">
													<i class="fa fa-th-list"></i><span class="text"> <?= lang('power_reserve'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_buckle">
												<a href="<?= site_url('system_settings/buckle') ?>">
													<i class="fa fa-th-list"></i><span class="text"> <?= lang('buckle'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_complication">
												<a href="<?= site_url('system_settings/complication') ?>">
													<i class="fa fa-th-list"></i><span class="text"> <?= lang('complication'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_variants">
												<a href="<?= site_url('system_settings/variants') ?>">
													<i class="fa fa-tags"></i><span class="text"> <?= lang('variants'); ?></span>
												</a>
											</li>
                                                    
											<li id="system_settings_tax_rates">
												<a href="<?= site_url('system_settings/tax_rates') ?>">
													<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('tax_rates'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_warehouses">
												<a href="<?= site_url('system_settings/warehouses') ?>">
													<i class="fa fa-building-o"></i><span class="text"> <?= lang('warehouses'); ?></span>
												</a>
											</li>
											
											<li id="system_settings_email_templates">
												<a href="<?= site_url('system_settings/email_templates') ?>">
													<i class="fa fa-envelope"></i><span class="text"> <?= lang('email_templates'); ?></span>
												</a>
											</li>
											
											<li id="system_settings_user_groups">
												<a href="<?= site_url('system_settings/user_groups') ?>">
													<i class="fa fa-key"></i><span class="text"> <?= lang('group_permissions'); ?></span>
												</a>
											</li>
											
											<li id="system_settings_backups">
												<a href="<?= site_url('system_settings/backups') ?>">
													<i class="fa fa-database"></i><span class="text"> <?= lang('backups'); ?></span>
												</a>
											</li>
													
											<li id="system_settings_updates">
												<a href="<?= site_url('system_settings/updates') ?>">
													<i class="fa fa-upload"></i><span class="text"> <?= lang('updates'); ?></span>
												</a>
											</li>
											
											<li id="system_settings_audit_trail">
												<a href="<?= site_url('system_settings/audit_trail') ?>">
													<i class="fa fa-pencil"></i><span class="text"> <?= lang('Audit_Trail'); ?></span>
												</a>
											</li>
                                        </ul>
                                    </li>
                                <?php } ?>

								<li class="mm_reports">
									<a class="dropmenu" href="#">
										<i class="fa fa-bar-chart-o"></i>
										<span class="text"> <?= lang('reports'); ?> </span>
										<span class="chevron closed"></span>
									</a>
									<ul>
										<li id="reports_index">
											<a class="dropmenu" href="#">
												<i class="fa fa-line-chart" aria-hidden="true"></i>
												<span class="text"> <?= lang('chart_report'); ?> </span>
												<span class="chevron closed"></span>
											</a>
											<ul>
												<li id="report_chart">
												   <a href="<?= site_url('reports') ?>">
												   <i class="fa fa-bars"></i><span class="text"> <?= lang('overview_chart'); ?> </span>
												   </a>
												</li>
												
												<li id="reports_warehouse_stock">
													<a href="<?= site_url('reports/warehouse_stock') ?>">
														<i class="fa fa-building"></i><span class="text"> <?= lang('warehouse_stock'); ?></span>
													</a>
												</li>
												
												<li id="reports_profit_chart">
													<a href="<?= site_url('reports/profit_chart') ?>">
														<i class="fa fa-building"></i><span class="text"> <?= lang('profit_chart'); ?></span>
													</a>
												</li>
												
												<li id="reports_cash_chart">
													<a href="<?= site_url('reports/cash_chart') ?>">
														<i class="fa fa-building"></i><span class="text"> <?= lang('cash_analysis_chart'); ?></span>
													</a>
												</li>
											</ul>
										</li>
										
										<li id="reports_index">
											<a class="dropmenu" href="#">
												<i class="fa fa-money" aria-hidden="true"></i>
												<span class="text"> <?= lang('profit_report'); ?> </span>
												<span class="chevron closed"></span>
											</a>
											<ul>
												<li id="reports_register">
													<a href="<?= site_url('reports/register') ?>">
														<i class="fa fa-th-large"></i><span class="text"> <?= lang('register_report'); ?></span>
													</a>
												</li>
												
												<li id="reports_payments">
													<a href="<?= site_url('reports/payments') ?>">
														<i class="fa fa-money"></i><span class="text"> <?= lang('payments_report'); ?></span>
													</a>
												</li>
												
<!--												<li id="reports_profit_loss">-->
<!--													<a href="--><?//= site_url('reports/profit_loss') ?><!--">-->
<!--														<i class="fa fa-money"></i><span class="text"> --><?//= lang('profit_and_loss'); ?><!--</span>-->
<!--													</a>-->
<!--												</li>-->
												 
											</ul>
										</li>
										
										<li id="reports_index">
											<a class="dropmenu" href="#">
												<i class="fa fa-barcode" aria-hidden="true"></i>
												<span class="text"> <?= lang('product_report'); ?> </span>
												<span class="chevron closed"></span>
											</a>
											<ul>
												<li id="reports_quantity_alerts">
													<a href="<?= site_url('reports/quantity_alerts') ?>">
														<i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_quantity_alerts'); ?></span>
													</a>
												</li>
												
												<?php if ($this->Settings->product_expiry) { ?>
													<li id="reports_expiry_alerts">
														<a href="<?= site_url('reports/expiry_alerts') ?>">
															<i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_expiry_alerts'); ?></span>
														</a>
													</li>
												<?php } ?>
												
												<li id="reports_products">
													<a href="<?= site_url('reports/products') ?>">
														<i class="fa fa-barcode"></i><span class="text"> <?= lang('products_report'); ?></span>
													</a>
												</li>
														
												<li id="reports_warehouse_reports">
													<a href="<?= site_url('reports/warehouse_reports') ?>">
														<i class="fa fa-barcode"></i><span class="text"> <?= lang('warehouse_reports'); ?></span>
													</a>
												</li>
												
												<!--<li id="reports_brand_reports">
													<a href="<?/*= site_url('reports/brand_reports') */?>">
														<i class="fa fa-barcode"></i><span class="text"> <?/*= lang('brand_reports'); */?></span>
													</a>
												</li>-->
												
												<li id="reports_product_customers" style="display:none;">
													<a href="<?= site_url('reports/customer_by_items') ?>">
														<i class="fa fa-barcode"></i><span class="text"> <?= lang('product_customers'); ?></span>
													</a>
												</li>
											</ul>
										</li>
										
										<li id="reports_index">
											<a class="dropmenu" href="#">
												<i class="fa fa-heart" aria-hidden="true"></i>
												<span class="text"> <?= lang('sales_report'); ?> </span>
												<span class="chevron closed"></span>
											</a>
											<ul>
												<li id="reports_daily_sales">
													<a href="<?= site_url('reports/daily_sales') ?>">
														<i class="fa fa-calendar-o"></i><span class="text"> <?= lang('daily_sales'); ?></span>
													</a>
												</li>
												
												<li id="reports_monthly_sales">
													<a href="<?= site_url('reports/monthly_sales') ?>">
														<i class="fa fa-calendar-o"></i><span class="text"> <?= lang('monthly_sales'); ?></span>
													</a>
												</li>
														
												<li id="reports_sales">
													<a href="<?= site_url('reports/sales') ?>">
														<i class="fa fa-heart"></i><span class="text"> <?= lang('sales_report'); ?></span>
													</a>
												</li>
														
												<li id="reports_sales_profit">
													<a href="<?= site_url('reports/sales_profit') ?>">
														<i class="fa fa-heart"></i><span class="text"> <?= lang('sales_profit_report'); ?></span>
													</a>
												</li>
												
												<li id="reports_sales_discount">
													<a href="<?= site_url('reports/sales_discount') ?>">
														<i class="fa fa-gift"></i><span class="text"> <?= lang('sales_discount_report'); ?></span>
													</a>
												</li>
														
												<li id="reports_customers">
													<a href="<?= site_url('reports/customers') ?>">
														<i class="fa fa-users"></i><span class="text"> <?= lang('customers_report'); ?></span>
													</a>
												</li>
											
												<li id="reports_getSalesReportDetail">
													<a href="<?= site_url('reports/getSalesReportDetail') ?>">
														<i class="fa fa-money"></i><span class="text"> <?= lang('report_sales_detail'); ?></span>
													</a>
												</li>
												
												<li id="reports_brands">
													<a href="<?= site_url('reports/getsale_brand_report') ?>">
														<i class="fa fa-money"></i><span class="text"> <?= lang('brand_sales_daily'); ?></span>
													</a>
												</li>
											</ul>
										</li> 
										
										<li id="reports_index">
											<a class="dropmenu" href="#">
												<i class="fa fa-star" aria-hidden="true"></i>
												<span class="text"> <?= lang('purchases_report'); ?> </span>
												<span class="chevron closed"></span>
											</a>
											<ul>
												<li id="reports_purchases">
													<a href="<?= site_url('reports/purchases') ?>">
														<i class="fa fa-star"></i><span class="text"> <?= lang('purchases_report'); ?></span>
													</a>
												</li>
												
												<li id="reports_daily_purchases">
													<a href="<?= site_url('reports/daily_purchases') ?>">
														<i class="fa fa-calendar-o"></i><span class="text"> <?= lang('daily_purchases'); ?></span>
													</a>
												</li>
												
												<li id="reports_monthly_purchases">
													<a href="<?= site_url('reports/monthly_purchases') ?>">
														<i class="fa fa-calendar-o"></i><span class="text"> <?= lang('monthly_purchases'); ?></span>
													</a>
												</li>
														
												<li id="reports_suppliers">
													<a href="<?= site_url('reports/suppliers') ?>">
														<i class="fa fa-users"></i><span class="text"> <?= lang('suppliers_report'); ?></span>
													</a>
												</li>
											</ul>
										</li>                
									</ul>
								</li>
                                        
                            <?php } else { ?>
								<!--- User Permission --->
								<?php if ($GP['products-index']) { ?>
								<li class="mm_products">
									<a class="dropmenu" href="#">
										<i class="fa fa-barcode"></i>
										<span class="text"> <?= lang('manage_products'); ?> 
										</span> <span class="chevron closed"></span>
									</a>
									<ul>
										<li id="products_index">
											<a class="submenu" href="<?= site_url('products'); ?>">
												<i class="fa fa-barcode"></i><span class="text"> <?= lang('list_products'); ?></span>
											</a>
										</li>
										<?php if ($GP['products-add']) { ?>
											<li id="products_add">
												<a class="submenu" href="<?= site_url('products/add'); ?>">
													<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_product'); ?></span>
												</a>
											</li>
										<?php } ?>
										<li id="products_print_barcodes" class="sub_navigation">
											<a class="submenu" href="<?= site_url('products/print_barcodes'); ?>">
												<i class="fa fa-tags"></i>
												<span class="text"> <?= lang('print_barcode_label'); ?></span>
											</a>
										</li>
										<?php if ($GP['products-adjustments']) { ?>
											<li id="products_quantity_adjustments">
												<a class="submenu" href="<?= site_url('products/quantity_adjustments'); ?>">
													<i class="fa fa-filter"></i><span class="text"> <?= lang('quantity_adjustments'); ?></span>
												</a>
											</li>
										<?php } ?>
										<?php if($GP['products-serial']){?>
											<li id="products_product_serial" class="sub_navigation">
												<a class="submenu" href="<?= site_url('products/product_serial'); ?>">
													<i class="fa fa-file-text"></i>
													<span class="text"> <?= lang('product_serial'); ?></span>
												</a>
											</li>
										<?php }?>
									</ul>
								</li>
								<?php } ?>

								<?php if ($GP['sales-index']) { ?>
									<li class="mm_sales <?= strtolower($this->router->fetch_method()) == 'settings' ? '' : 'mm_pos' ?>">
										<a class="dropmenu" href="#">
											<i class="fa fa-heart"></i>
											<span class="text"> <?= lang('manage_sales'); ?> </span> <span class="chevron closed"></span>
										</a>
										<ul>
											<?php if (POS && $GP['pos-index']) { ?>
												<li id="pos_sales">
													<a class="submenu" href="<?= site_url('pos/sales'); ?>">
														<i class="fa fa-heart"></i><span class="text"> <?= lang('pos_sales'); ?></span>
													</a>
												</li>
											<?php } ?>
											<?php if ($GP['sales-return_sales']) { ?>
												<li id="sales_return_sales">
													<a class="submenu" href="<?= site_url('sales/return_sales'); ?>">
														<i class="fa fa-reply"></i><span class="text"> <?= lang('list_sales_return'); ?></span>
													</a>
												</li>
											<?php } ?>
										</ul>
									</li>
								<?php } ?>

								<?php if ($GP['quotes-index'] || $GP['quotes-add']) { ?>
									<li class="mm_quotes">
										<a class="dropmenu" href="#">
											<i class="fa fa-heart-o"></i>
											<span class="text"> <?= lang('manage_quotes'); ?> </span>
											<span class="chevron closed"></span>
										</a>
										<ul>
											<?php if ($GP['quotes-index']) { ?>
												<li id="quotes_index">
													<a class="submenu" href="<?= site_url('quotes'); ?>">
														<i class="fa fa-heart-o"></i><span class="text"> <?= lang('list_quotes'); ?></span>
													</a>
												</li>
											<?php } ?>
											<?php if ($GP['quotes-add']) { ?>
												<li id="quotes_add">
													<a class="submenu" href="<?= site_url('quotes/add'); ?>">
														<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_quote'); ?></span>
													</a>
												</li>
											<?php } ?>
										</ul>
									</li>
								<?php } ?>

								<?php if ($GP['purchases-index'] || $GP['purchases-add'] || $GP['purchases-order-index'] || $GP['purchases-order-add'] || $GP['purchases-add-expenses'] || $GP['purchases-expense'] || $GP['purchases-list-return']) { ?>
									<li class="mm_purchases mm_purchases_order">
										<a class="dropmenu" href="#">
											<i class="fa fa-star"></i>
											<span class="text"> <?= lang('manage_purchases'); ?> 
											</span> <span class="chevron closed"></span>
										</a>
										<ul>
											<?php if ($GP['purchases-order-index']) { ?>
												<li id="purchases_order_index">
													<a class="submenu" href="<?= site_url('purchases_order'); ?>">
														<i class="fa fa-star"></i>
														<span class="text"> <?= lang('list_purchase_order'); ?></span>
													</a>
												</li>
											<?php } ?>
											
											<?php if ($GP['purchases-order-add']) { ?>
												<li id="purchases_order_add">
													<a class="submenu" href="<?= site_url('purchases_order/add'); ?>">
														<i class="fa fa-plus-circle"></i>
														<span class="text"> <?= lang('add_purchase_order'); ?></span>
													</a>
												</li>
											<?php } ?>
											
											<?php if ($GP['purchases-index']) { ?>
												<li id="purchases_index">
													<a class="submenu" href="<?= site_url('purchases'); ?>">
														<i class="fa fa-star"></i><span class="text"> <?= lang('list_purchases'); ?></span>
													</a>
												</li>
											<?php } ?>
											
											<?php if ($GP['purchases-add']) { ?>
												<li id="purchases_add">
													<a class="submenu" href="<?= site_url('purchases/add'); ?>">
														<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_purchase'); ?></span>
													</a>
												</li>
											<?php } ?>
											
											<?php if ($GP['purchases-list-return']) { ?>
												<li id="purchases_return_purchases">
													<a class="submenu" href="<?= site_url('purchases/return_purchases'); ?>">
														<i class="fa fa-reply"></i>
														<span class="text"> <?= lang('list_purchases_return'); ?></span>
													</a>
												</li>
											<?php } ?>
											
											<?php if ($GP['purchases-expense']) { ?>
												<li id="purchases_expenses">
													<a class="submenu" href="<?= site_url('purchases/expenses'); ?>">
														<i class="fa fa-dollar"></i><span class="text"> <?= lang('list_expenses'); ?></span>
													</a>
												</li>
											<?php } ?>
											
											<?php if ($GP['purchases-add-expenses']) { ?>
												<li id="purchases_add_expense">
													<a class="submenu" href="<?= site_url('purchases/add_expense'); ?>" data-toggle="modal" data-target="#myModal">
														<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_expense'); ?></span>
													</a>
												</li>
											<?php } ?>
										</ul>
									</li>
								<?php } ?>

								<?php if ($GP['transfers-index'] || $GP['transfers-add']) { ?>
									<li class="mm_transfers">
										<a class="dropmenu" href="#">
											<i class="fa fa-star-o"></i>
											<span class="text"> <?= lang('manage_transfers'); ?> </span>
											<span class="chevron closed"></span>
										</a>
										<ul>
											<?php if ($GP['transfers-index']) { ?>
												<li id="transfers_index">
													<a class="submenu" href="<?= site_url('transfers/list_in_transfer'); ?>">
														<i class="fa fa-star-o"></i><span class="text"> <?= lang('list_transfers'); ?></span>
													</a>
												</li>
											<?php } ?>
											<?php if ($GP['transfers-add']) { ?>
												<li id="transfers_add">
													<a class="submenu" href="<?= site_url('transfers/add_in_transfer'); ?>">
														<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_transfer'); ?></span>
													</a>
												</li>
											<?php } ?>
										</ul>
									</li>
								<?php } ?>
							
								<?php if($GP['documents-index']){?>
									<li class="mm_documents">
										<a class="submenu" href="<?= site_url('documents'); ?>">
											<i class="fa fa-book"></i><span class="text"> <?= lang('catalog'); ?></span>
										</a>
									</li>
								<?php } ?>
							
								<?php if($GP['services-index']){?>
									<li class="mm_services">
										<a class="dropmenu" href="#">
											<i class="fa fa-bar-chart-o"></i>
											<span class="text"> <?= lang('services'); ?> </span>
											<span class="chevron closed"></span>
										</a>
										<ul>
											<li id="services_index">
												<a href="<?= site_url('services') ?>">
													<i class="fa fa-bars"></i><span class="text"> <?= lang('list_services'); ?></span>
												</a>
											</li>
											<?php if($GP['services-add']){?>
												<li id="services_add_service">
													<a href="<?= site_url('services/add_service') ?>">
														<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_service'); ?></span>
													</a>
												</li>
											<?php } ?>
										</ul>
									</li>
								<?php } ?>
																					
								<?php if ($GP['customers-index'] || $GP['suppliers-index'] || $GP['customers-add']) { ?>
									<li class="mm_auth mm_customers mm_suppliers mm_billers">
										<a class="dropmenu" href="#">
											<i class="fa fa-users"></i>
											<span class="text"> <?= lang('manage_people'); ?> </span>
											<span class="chevron closed"></span>
										</a>
										<ul>
											<?php if ($GP['customers-index']) { ?>
											<li id="customers_index">
												<a class="submenu" href="<?= site_url('customers'); ?>">
													<i class="fa fa-users"></i><span class="text"> <?= lang('list_customers'); ?></span>
												</a>
											</li>
											<?php } if ($GP['customers-add']) { ?>
												<li id="customers_index">
													<a class="submenu" href="<?= site_url('customers/add'); ?>" data-toggle="modal" data-target="#myModal">
														<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_customer'); ?></span>
													</a>
												</li>
											<?php }if ($GP['suppliers-index']) { ?>
												<li id="suppliers_index">
													<a class="submenu" href="<?= site_url('suppliers'); ?>">
														<i class="fa fa-users"></i><span class="text"> <?= lang('list_suppliers'); ?></span>
													</a>
												</li>
											<?php } if ($GP['suppliers-add']) { ?>
												<li id="suppliers_index">
													<a class="submenu" href="<?= site_url('suppliers/add'); ?>" data-toggle="modal" data-target="#myModal">
														<i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_supplier'); ?></span>
													</a>
												</li>
											<?php } ?>
										</ul>
									</li>
								<?php } ?>

								<?php if ($GP['reports-products'] || $GP['reports-sales'] || $GP['reports-purchases'] || $GP['reports-profit']) { ?>
									<li class="mm_reports">
										<a class="dropmenu" href="#">
											<i class="fa fa-bar-chart-o"></i>
											<span class="text"> <?= lang('reports'); ?> </span>
											<span class="chevron closed"></span>
										</a>
										<ul>
											<?php if ($GP['reports-profit']) { ?>
											<li id="reports_index">
												<a class="dropmenu" href="#">
													<i class="fa fa-money" aria-hidden="true"></i>
													<span class="text"> <?= lang('profit_report'); ?> </span>
													<span class="chevron closed"></span>
												</a>
												<ul>
													<li id="reports_register">
														<a href="<?= site_url('reports/register') ?>">
															<i class="fa fa-th-large"></i><span class="text"> <?= lang('register_report'); ?></span>
														</a>
													</li>
													<li id="reports_payments">
														<a href="<?= site_url('reports/payments') ?>">
															<i class="fa fa-money"></i><span class="text"> <?= lang('payments_report'); ?></span>
														</a>
													</li>
													<li id="reports_profit_loss">
														<a href="<?= site_url('reports/profit_loss') ?>">
															<i class="fa fa-money"></i><span class="text"> <?= lang('profit_and_loss'); ?></span>
														</a>
													</li>
												</ul>
											</li>
											<?php } ?>
											
											<?php if ($GP['reports-products']) { ?>
											<li id="reports_index">
												<a class="dropmenu" href="#">
													<i class="fa fa-barcode" aria-hidden="true"></i>
													<span class="text"> <?= lang('product_report'); ?> </span>
													<span class="chevron closed"></span>
												</a>
												<ul>
													<li id="reports_quantity_alerts">
														<a href="<?= site_url('reports/quantity_alerts') ?>">
															<i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_quantity_alerts'); ?></span>
														</a>
													</li>
													<?php if ($this->Settings->product_expiry) { ?>
													<li id="reports_expiry_alerts">
														<a href="<?= site_url('reports/expiry_alerts') ?>">
															<i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_expiry_alerts'); ?></span>
														</a>
													</li>
													<?php } ?>
													<li id="reports_products">
														<a href="<?= site_url('reports/products') ?>">
															<i class="fa fa-barcode"></i><span class="text"> <?= lang('products_report'); ?></span>
														</a>
													</li>
													<li id="reports_warehouse_reports">
														<a href="<?= site_url('reports/warehouse_reports') ?>">
															<i class="fa fa-barcode"></i><span class="text"> <?= lang('warehouse_reports'); ?></span>
														</a>
													</li>
													<!--<li id="reports_brand_reports">
														<a href="<?/*= site_url('reports/brand_reports') */?>">
															<i class="fa fa-barcode"></i><span class="text"> <?/*= lang('brand_reports'); */?></span>
														</a>
													</li>-->
													<li id="reports_product_customers" style="display:none;">
														<a href="<?= site_url('reports/customer_by_items') ?>">
															<i class="fa fa-barcode"></i><span class="text"> <?= lang('product_customers'); ?></span>
														</a>
													</li>
												</ul>
											</li>
											<?php } ?>	
											
											<?php if ($GP['reports-sales']) { ?>
												<li id="reports_index">
													<a class="dropmenu" href="#">
														<i class="fa fa-heart" aria-hidden="true"></i>
														<span class="text"> <?= lang('sales_report'); ?> </span>
														<span class="chevron closed"></span>
													</a>
													<ul>
														<li id="reports_daily_sales">
															<a href="<?= site_url('reports/daily_sales') ?>">
																<i class="fa fa-calendar-o"></i><span class="text"> <?= lang('daily_sales'); ?></span>
															</a>
														</li>
														<li id="reports_monthly_sales">
															<a href="<?= site_url('reports/monthly_sales') ?>">
																<i class="fa fa-calendar-o"></i><span class="text"> <?= lang('monthly_sales'); ?></span>
															</a>
														</li>
														<li id="reports_sales">
															<a href="<?= site_url('reports/sales') ?>">
																<i class="fa fa-heart"></i><span class="text"> <?= lang('sales_report'); ?></span>
															</a>
														</li>
														<li id="reports_sales_profit">
															<a href="<?= site_url('reports/sales_profit') ?>">
																<i class="fa fa-heart"></i><span class="text"> <?= lang('sales_profit_report'); ?></span>
															</a>
														</li>
														<li id="reports_sales_discount">
															<a href="<?= site_url('reports/sales_discount') ?>">
																<i class="fa fa-gift"></i><span class="text"> <?= lang('sales_discount_report'); ?></span>
															</a>
														</li>
														<li id="reports_customers">
															<a href="<?= site_url('reports/customers') ?>">
																<i class="fa fa-users"></i><span class="text"> <?= lang('customers_report'); ?></span>
															</a>
														</li>
														<li id="reports_getSalesReportDetail">
															<a href="<?= site_url('reports/getSalesReportDetail') ?>">
																<i class="fa fa-money"></i><span class="text"> <?= lang('report_sales_detail'); ?></span>
															</a>
														</li>
														<li id="reports_brands">
															<a href="<?= site_url('reports/getsale_brand_report') ?>">
																<i class="fa fa-money"></i><span class="text"> <?= lang('brand_sales_daily'); ?></span>
															</a>
														</li>
													</ul>
												</li>										
											<?php } ?>
											
											<?php if ($GP['reports-purchases']) { ?>
												<li id="reports_index">
													<a class="dropmenu" href="#">
														<i class="fa fa-star" aria-hidden="true"></i>
														<span class="text"> <?= lang('purchases_report'); ?> </span>
														<span class="chevron closed"></span>
													</a>
													<ul>
														<li id="reports_purchases">
															<a href="<?= site_url('reports/purchases') ?>">
																<i class="fa fa-star"></i><span class="text"> <?= lang('purchases_report'); ?></span>
															</a>
														</li>
														<li id="reports_daily_purchases">
															<a href="<?= site_url('reports/daily_purchases') ?>">
																<i class="fa fa-calendar-o"></i><span class="text"> <?= lang('daily_purchases'); ?></span>
															</a>
														</li>
														<li id="reports_monthly_purchases">
															<a href="<?= site_url('reports/monthly_purchases') ?>">
																<i class="fa fa-calendar-o"></i><span class="text"> <?= lang('monthly_purchases'); ?></span>
															</a>
														</li>
														<li id="reports_suppliers">
															<a href="<?= site_url('reports/suppliers') ?>">
																<i class="fa fa-users"></i><span class="text"> <?= lang('suppliers_report'); ?></span>
															</a>
														</li>
													</ul>
												</li>										
											<?php } ?>
										</ul>
									</li>
								<?php } ?>

                        <?php } ?>
                        </ul>
                    </div>
                    <a href="#" id="main-menu-act" class="full visible-md visible-lg">
                        <i class="fa fa-angle-double-left"></i>
                    </a>
                </div>

                <div id="content" class="col-lg-10 col-md-10">
                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <ul class="breadcrumb">
                                <?php
									foreach ($bc as $b) {
										if ($b['link'] === '#') {
											echo '<li class="active">' . $b['page'] . '</li>';
										} else {
											echo '<li><a href="' . $b['link'] . '">' . $b['page'] . '</a></li>';
										}
									}
								?>
								<li class="right_log hidden-xs">
									<?= lang('your_ip') . ' ' . $ip_address . " <span class='hidden-sm'>( " . lang('last_login_at') . ": " . date($dateFormats['php_ldate'], $this->session->userdata('old_last_login')) . " " . ($this->session->userdata('last_ip') != $ip_address ? lang('ip:') . ' ' . $this->session->userdata('last_ip') : '') . " )</span>" ?>
								</li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <?php if ($message) { ?>
							<div class="alert alert-success">
								<button data-dismiss="alert" class="close" type="button">×</button>
								<?= $message; ?>
							</div>
							<?php } ?>
							<?php if ($error) { ?>
								<div class="alert alert-danger">
									<button data-dismiss="alert" class="close" type="button">×</button>
									<?= $error; ?>
								</div>
							<?php } ?>
							<?php if ($warning) { ?>
								<div class="alert alert-warning">
									<button data-dismiss="alert" class="close" type="button">×</button>
									<?= $warning; ?>
								</div>
							<?php } ?>
                            <?php
								if ($info) {
									foreach ($info as $n) {
										if (!$this->session->userdata('hidden' . $n->id)) {
                            ?>
										<div class="alert alert-info">
											<a href="#" id="<?= $n->id ?>" class="close hideComment external" data-dismiss="alert">&times;</a>
											<?= $n->comment; ?>
										</div>
								<?php } } } ?>
                            <div id="alerts"></div>