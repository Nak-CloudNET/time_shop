<style>
	.detail p{
		border-bottom:1px solid #000 !important;
	}
	@media print
   {
	   .title{
		   background-color:#000 !important;color:#fff !important;
		   -webkit-print-color-adjust: exact; 
			-moz-print-color-adjust: exact;
			-ms-print-color-adjust:exact;
			print-color-adjust:exact;
			color-adjust:exact;
			-webkit-color-adjust:exact;
			-moz-color-adjust:exact;
			-ms-color-adjust:exact;
	   }
	   .title > tr > th {
			font-size: 15px;
			background-color:#000 !important;color:#fff !important;
			-webkit-print-color-adjust: exact; 
			-moz-print-color-adjust: exact;
			-ms-print-color-adjust:exact;
			print-color-adjust:exact;
			color-adjust:exact;
			-webkit-color-adjust:exact;
			-moz-color-adjust:exact;
			-ms-color-adjust:exact;
			
		}
		.padding{
			padding-right:10px !important;
		}
		#table1 { 
			border-collapse: collapse !important;
		}
		#table1 td,
		#table1 th {
			border:1px solid #ccc !important;
		}
		#table2 { 
			font-size:12px !important;
			margin-top:5px !important;
			border-collapse: collapse !important;
		}
		#table2 td,
		#table2 th {
			border:1px solid #ccc !important;
		}
		
		#table3 { 
			border-collapse: collapse !important;
		}
		#table3 td,
		#table3 th {
			border:1px solid #000 !important;
		}
		#title1{
			background-color:#000 !important;
			color:#fff !important;
			border-bottom:1px solid #fff !important;
			margin-bottom:1px !important;
		}
		#title2, #title3{
			background-color:#000 !important;
			color:#fff !important;
		}
		#title4{
			background-color:#000 !important;
			color:#fff !important;
			border-bottom:1px solid #fff !important;
			margin-bottom:1px !important;
		}
		#title5, #title6{
			background-color:#000 !important;
			color:#fff !important;
		}
		.detail p{
			border-bottom:1px solid #000 !important;
		}
   }
</style>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <div class="row">
				<div class="col-sm-12" style="text-align:center;">
					<img src="<?= base_url().'assets/uploads/logos/logo.png';?>" style="height:50px;"/>
					<div style="font-size:35px;">
						<span style="font-size:50px;">T</span>
						<span>IMEPIECE</span>
						<span  style="font-size:50px;">S</span>
					</div>
					<div style="font-size:12px;">
						<div>Vattanac Capital Mall Unit G1</div>
						<div>No 66, Preah Monivong, Phnom Penh, Cambodia</div>
						<div>Tel.(855)78 777 938</div>
					</div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div class="col-md-9 col-xs-9 col-lg-9" style="padding:0;">
						<div style="width:13%;float:left;">Name
							<span style=" float: right; padding-right: 5px;text-align: right;">:</span>
						</div>
						<div style="float: left; width: 22%;border-bottom:1px solid #000;"><?= $services->name;?></div>
						<div style="float: left; width: 16%; text-align: center;">IC/Passport No</div>
						<div style="float: left; width: 29%;border-bottom:1px solid #000;"><?= $services->ic_passport_no;?></div>
						<div style="float: left; width: 20%; text-align: center;">Date of reception</div>
					</div>
					<div class="col-md-3 col-xs-3 col-lg-3" style="padding:0;border-bottom:1px solid #000;"><?= $services->date_of_reception;?></div>
				</div>
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div class="col-md-9 col-xs-9 col-lg-9" style="padding:0;">
						<div style="width:13%;float:left;">Address<span style=" float: right; padding-right: 5px;text-align: right;">:</span></div>
						<div style="float: left; width: 87%;border-bottom:1px solid #000;"><?= $services->address;?></div>
					</div>
					<div class="col-md-3 col-xs-3 col-lg-3" style="padding:0;"></div>
				</div>
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div class="col-md-9 col-xs-9 col-lg-9" style="padding:0;">
						<div style="width:13%;float:left;">Contact<span style=" float: right; padding-right: 5px;text-align: right;">:</span></div>
						<div style="float: left; width: 24%;border-bottom:1px solid #000;"><?= $services->hpline;?></div>
						<div style="float: left; width: 5%; text-align: center;">(HP)</div>
						<div style="float: left; width: 24%;border-bottom:1px solid #000;"><?= $services->hline;?></div>
						<div style="float: left; width: 5%; text-align: center;">(H)</div>
						<div style="float: left; width: 24%;border-bottom:1px solid #000;"><?= $services->oline;?></div>
						<div style="float: left; width: 5%; text-align: center;">(O)</div>
					</div>
					<div class="col-md-3 col-xs-3 col-lg-3" style="padding:0;"></div>
				</div>
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div class="col-md-9 col-xs-9 col-lg-9" style="padding:0;">
						<div style="width:13%;float:left;">Email<span style=" float: right; padding-right: 5px;text-align: right;">:</span></div>
						<div style="float: left; width: 87%;border-bottom:1px solid #000;"><?= $services->email;?></div>
					</div>
					<div class="col-md-3 col-xs-3 col-lg-3" style="padding:0;"></div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12" style="color:white;text-align:center;">
					<div class="title" style="background-color:#000;padding:10px 0;">PRODUCT INFORMATION</div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div style="width:10%;float:left;">Brand:</div>
					<div style="width:30%;float:left;border-bottom:1px solid #000;">
						<?= $services->brand_name;?>
					</div>
					<div style="width:10%;float:left;text-align:center;">Jewelry:</div>
					<div style="width:20%;float:left;text-align:left;">
						<div style="float:left;padding-left:10px;">
							<span style="float:left;">Yes</span> 
							<span style="float:left;padding-left:10px;">
								<?php
									if($services->is_jewelry == 1){
										echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
									}else{
										echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
									}
								?>
							</span>
						</div>
						<div style="float:left;padding-left:10px;"><span style="float:left;">No</span> 
							<span style="float:left;padding-left:10px;">
								<?php
									if($services->is_jewelry != 1){
										echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
									}else{
										echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
									}
								?>
							</span>
						</div>
					</div>
					<div style="width:5%;float:left;">Type</div>
					<div style="width:25%;float:left;border-bottom:1px solid #000;">
						<?= $services->cat_name;?>
					</div>
				</div>
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div style="width:12%;float:left;">Modal/ Ref<span style=" float: right; padding-right: 5px;text-align: right;">:</span></div>
					<div style="width:17%;float:left;border-bottom:1px solid #000;">
						<?= $services->pro_name;?>
					</div>
					<div style="width:8%;float:left;text-align:center;">Serial No:</div>
					<div style="width:15%;float:left;border-bottom:1px solid #000;"><?= $services->serial_no;?></div>
					<div style="width:8%;float:left;text-align:center;">Warranty:</div>
					<div style="width:20%;float:left;border-bottom:1px solid #000;">
						&nbsp;<?= $services->wfrom_date;?>
					</div>
					<div style="width:20%;float:left;border-bottom:1px solid #000;">
						/&nbsp;<?= $services->wto_date;?>
					</div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div class="table-responsive">
						<table class="table table-bordered" id="table1">
							<tr>
								<th style="width:15%;text-align:center;">Functionality</th>
								<th style="width:20%;text-align:center;">Movement</th>
								<th style="width:15%;text-align:center;">Style</th>
								<th style="width:50%;text-align:center;"></th>
							</tr>
							<tr>
								<td>
									<div>Rinning 
										<span style="float:right;">
											<?php
												if($services->rinning){
													echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
												}else{
													echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
												}
											?>
										</span>
									</div>
									<div style="margin-top:10px;">		
										Stopped 
										<span style="float:right;">
											<?php
												if($services->stopped){
													echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
												}else{
													echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
												}
											?>
										</span>
									</div>
								</td>
								<td>
									<div>Manual-winding
										<span style="float:right;">
											<?php
												if($services->manual_winding){
													echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
												}else{
													echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
												}
											?>
										</span>
									</div>
									<div style="margin-top:10px;">		
										Self-winding 
										<span style="float:right;">
											<?php
												if($services->self_winding){
													echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
												}else{
													echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
												}
											?>
										</span>
									</div>
								</td>
								<td>
									<div>Gents 
										<span style="float:right;">
											<?php
												if($services->gents){
													echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
												}else{
													echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
												}
											?>
										</span>
									</div>
									<div style="margin-top:10px;">		
										Ladies 
										<span style="float:right;">
											<?php
												if($services->ladies){
													echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
												}else{
													echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
												}
											?>
										</span>
									</div>
								</td>
								<td>
									<div style="float:left; width:33.33%;padding-left:10px;">
										<div>Platinum 
											<span style="float:right;">
												<?php
													if($services->platinum){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
										</div>
										<div style="margin-top:10px;">White Gold 
											<span style="float:right;">
												<?php
													if($services->white_gold){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
										</div>
										<div style="margin-top:10px;">Rose Gold
											<span style="float:right;">
												<?php
													if($services->rose_gold){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
										</div>
										<div style="margin-top:10px;">	Yellow Gold 
											<span style="float:right;">
												<?php
													if($services->yellow_gold){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
										</div>
									</div>
									<div style="float:left; width:33.33%;padding-left:10px;">
										<div>Titanium 
											<span style="float:right;">
												<?php
													if($services->titanium){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
										</div>
										<div style="margin-top:10px;">	Steel 
											<span style="float:right;">
												<?php
													if($services->steel){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
										</div>
										<div style="margin-top:10px;">Bi-color 
											<span style="float:right;">
												<?php
													if($services->bi_color){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
										</div>
									</div>
									<div style="float:left; width:33.33%;padding-left:10px;">
										<div>Leather 
											<span style="float:right;">
												<?php
													if($services->leather){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
										</div>
										<div style="margin-top:10px;">	Calf 
											<span style="float:right;">
												<?php
													if($services->calf){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
										</div>
										<div style="margin-top:10px;">Rubber/Canvas 
											<span style="float:right;">
												<?php
													if($services->rubber_canvas){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12" style="color:white;text-align:center;">
					<div class="title" style="padding:10px 0;background-color:black;">VISUAL EXAMINATION</div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div style="width:15%;float:left;">
						<img src="<?=base_url().'/assets/images/1.png'?>" style="width:100%"/>
					</div>
					<div style="width:15%;float:left;">
						<img src="<?=base_url().'/assets/images/1.png'?>" style="width:100%"/>
					</div>
					<div style="width:15%;float:left;">
						<img src="<?=base_url().'/assets/images/1.png'?>" style="width:100%"/>
					</div>
					<div class="padding" style="width:45%;float:right;border:1px solid #ccc;padding:0 30px;">
						<div style="padding:10px;">
							<div style="float:left;line-height:2;">
								<div><span>A.</span><span>Chipped</span></div>
								<div><span>B.</span><span>Oxidized</span></div>
								<div><span>C.</span><span>Cracked</span></div>
								<div><span>D.</span><span>Damaged</span></div>
								<div><span>E.</span><span>Dented</span></div>
							</div>
							<div style="float:right;line-height:2;">
								<div><span>F.</span><span>Discolored</span></div>
								<div><span>G.</span><span>Missing</span></div>
								<div><span>H.</span><span>Stopped</span></div>
								<div><span>I.</span><span>Scratches</span></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div class="table-responsive">
						<table class="table table-bordered" id="table2">
							<tr>
								<td style="line-height:2;width:15%;">
									<div>
										General
									</div>
									<div>
										Case
									</div>
									<div>
										Lugs
									</div>
									<div>
										Bezel
									</div>
									<div>
										Crown/Pushers
									</div>
									<div>
										Dial
									</div>
									<div>
										Hands
									</div>
									<div>
										Caseback
									</div>
									<div>
										Strap
									</div>
									<div>
										Bracelet
									</div>
									<div>
										Buckle
									</div>
								</td>
								<td style="line-height:2;width:85%;">
									<div style="float:left;width:20%;">
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->normal_scratched){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Normal Scratched</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->case_scratched){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Scratched</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->lugs_scratched){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Scratched</span>
										</div>
										<div style="width:100%;float:left;">		
											<span style="float:right;">
												<?php
													if($services->bezel_scratched){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Scratched</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->crown_missing){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Crown Missing</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->dial_scratched){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Scratched</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->hand_oxidized){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Oxidized</span>
										</div>
										<div style="width:100%;float:left;">	
											<span style="float:right;">
												<?php
													if($services->caseback_scratched){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Scratched</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->strap_warm){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Worm</span>
										</div>
										<div style="width:100%;float:left;">	
											<span style="float:right;">
												<?php
													if($services->bracelet_scratched){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Scratched</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->buckle_scratched){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Scratched</span>
										</div>
									</div>
									<div style="float:left;width:20%;padding-left:10px;">
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->heavy_scratches){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Heavy Scratches</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->case_dent){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Dent</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->lugs_dent){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Dent</span>
										</div>
										<div style="width:100%;float:left;">		
											<span style="float:right;">
												<?php
													if($services->bezel_dent){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Dent</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->crown_demaged){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Crown Damaged</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->dial_stained){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Stained</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->hand_jammed){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Jammed</span>
										</div>
										<div style="width:100%;float:left;">	
											<span style="float:right;">
												<?php
													if($services->caseback_dent){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Dent</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->strap_damagged){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Damagged</span>
										</div>
										<div style="width:100%;float:left;">	
											<span style="float:right;">
												<?php
													if($services->bracelet_oxidized){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Oxidized</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->buckle_demagged){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Damagged</span>
										</div>
									</div>
									<div style="float:left;width:20%;padding-left:10px;">
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->severely_scratches){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Severely Scratches</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->case_deep_dent){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Deep Dent</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->lugs_deep_dent){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Deep Dent</span>
										</div>
										<div style="width:100%;float:left;">		
											<span style="float:right;">
												<?php
													if($services->bezel_deep_dent){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Deep Dent</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->crown_pusher_missing){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Pusher Missing</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->dial_oxidized){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Oxidized</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->hands_disloged){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Disloged</span>
										</div>
										<div style="width:100%;float:left;">	
											<span style="float:right;">
												<?php
													if($services->caseback_chipped){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Chipped</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->strap_other){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Other</span>
										</div>
										<div style="width:100%;float:left;">	
											<span style="float:right;">
												<?php
													if($services->bracelet_damagged){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Damagged</span>
										</div>
									</div>
									<div style="float:left;width:20%;padding-left:10px;">
										<div style="width:100%;float:left;">
											<span style="float:right;"></span>
											<span style="float:right;padding-right:5px;">&nbsp;</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->case_impacted){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Impacted</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->lugs_impacted){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Impacted</span>
										</div>
										<div style="width:100%;float:left;">		
											<span style="float:right;">
												<?php
													if($services->bezel_craked){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Craked</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->crown_pusser_damaged){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Pusser Damaged</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->dial_damaged){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Damaged</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;"></span>
											<span style="float:right;padding-right:5px;">&nbsp;</span>
										</div>
										<div style="width:100%;float:left;">	
											<span style="float:right;">
												<?php
													if($services->caseback_craked){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Craked</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->strap_no_strap){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">No Strap</span>
										</div>
									</div>
									<div style="float:left;width:20%;padding-left:10px;">
										<div style="width:100%;float:left;">
											<span style="float:right;"></span>
											<span style="float:right;padding-right:5px;">&nbsp;</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->case_out_of_strap){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Out of Strap</span>
										</div>
										<div style="width:100%;float:left;">
											<span style="float:right;">
												<?php
													if($services->lugs_out_of_strap){
														echo '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
													}else{
														echo '<i class="fa fa-square-o" aria-hidden="true"></i>';
													}
												?>
											</span>
											<span style="float:right;padding-right:5px;">Out of Strap</span>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12" style="text-align:left;">
					<div style="padding:15px 0 15px 5px;border:1px solid #ccc;">REMARKS</div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div class="detail"><?= $services->detail1;?></div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12" style="color:white;text-align:center;">
					<div class="title" style="padding:10px 0;background-color:black;">TERMS & CONDITIONS</div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12">
					<ul style="padding-left:15px;list-style-type: square;">
						<li>This serves as an acknowledgement for reception of watch for repair/service without payment.</li>
						<li>Client will be notified by staff of Timepieces Boutique on the repair charges vai email and/or preferrend mode of contact as per stipulated.</li>
						<li>Payment terms will be COD in either cash or credit card only. Personal cheque is not accepted.</li>
						<li>Original copy of this Repair Slip must be presented during collection of watch/es.</li>
						<li>Payment terms will be COD in either cash or credit card only. Personal cheque is not accepted.</li>
						<li>In the event that the repair item (as per model indicated in this repair slip) is left uncollected after 18 months from date of collection, the client shall deemed to have given up all rights, interest and ownership to this item. Horograph & Karat Co .Ltd, shall gain ownership and deal with the item with absolute discretion including the right to dispose it by way of sale.</li>
					</ul>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div class="table-responsive">
						<table class="table table-bordered" id="table3">
							<tr><td colspan="2" id="title1" style="background-color:black;color:white;text-align:center;padding:10px 0;margin-bottom:1px !important;">TIMEPIECES</td></tr>
							<tr>
								<td id="title2" style="background-color:black;color:white;text-align:center;border:none !important;">RECEIVED BY</td>
								<td id="title3" style="background-color:black;color:white;text-align:center;border:none !important;">ACKNOWLEDGEMENT</td>
							</tr>
							<tr style="border:1px solid #000;">
								<td style="border:1px solid #000;width:50%;">
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Staff Name</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->staff_name;?></div>
									</div>
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Signature</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->staff_signature;?></div>
									</div>
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Date</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->staff_date;?></div>
									</div>
								</td>
								<td style="border:1px solid #000;width:50%;">
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Client Name</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->client_name;?></div>
									</div>
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Signature</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->client_signature;?></div>
									</div>
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Date</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->client_date;?></div>
									</div>
								</td>
							</tr>
							<tr><td colspan="2" id="title4" style="background-color:black;color:white;text-align:center;padding:10px 0;border-bottom:1px solid #fff;">SERVICE CENTER</td></tr>
							<tr>
								<td id="title5" style="background-color:black;color:white;text-align:center;border:none !important;">RECEIVED BY</td>
								<td id="title6" style="background-color:black;color:white;text-align:center;border:none !important;">SENT BACK TO TIMEPIECES BY</td>
							</tr>
							<tr style="border:1px solid #000;">
								<td style="border:1px solid #000;width:50%;">
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Name</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->staff_name1;?></div>
									</div>
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Signature</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->staff_signature1;?></div>
									</div>
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Date</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->staff_date1;?></div>
									</div>
								</td>
								<td style="border:1px solid #000;width:50%;">
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Name</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->client_name1;?></div>
									</div>
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Signature</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->client_signature1;?></div>
									</div>
									<div class="" style="width:100%;float:left;">
										<div style="width:30%;float:left;">Date</div>
										<div style="width:70%;float:left;border-bottom:1px solid #000;"><?= $services->client_date1;?></div>
									</div>
								</td>
							</tr>
							<tr>
								<td style="border:1px solid #000;width:100%;" colspan="2">
									REMARKS
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-12 col-xs-12 col-lg-12">
					<div class="detail"><?= $services->detail2;?></div>
				</div>
			</div>
			<br/>
		</div>
	</div>
</div>
