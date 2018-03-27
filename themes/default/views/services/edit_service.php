<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
        body {
			margin-left: auto;
			margin-right: auto;
            background: #FFF;
        }

		.table{
			border-collapse: collapse;
		}

		.table {
			color: #000000;
			font-size: 12px;
		}

		.put-border td{border:1px solid black;}
		.put-border-right{border-right:1px solid black;}
		.no-border{border:none;}
		.text-info{
			margin-left:35px;
		}
		.t_c{text-align:center;}
		li {list-style-type: square;}
		.editOption{			
			width: 28%;			
			position: absolute;			
			top: 0;			
			border: 0;			
			padding-left: 5px;		
		}				
		.typeOption{			
			width: 22%;			
			position: absolute;			
			top: 0;			
			border: 0;			
			padding-left: 5px;		
		}
		.show-model{			
			width: 13%;			
			position: absolute;			
			top: 0;			
			border: 0;			
			padding-left: 5px;		
		}
    </style>
</head>

<body>
	<div class="container">
		<div class="col-sm-12" style="text-align:center;">
			<?php if ($Settings->logo2) {
				echo '<img src="' . site_url() . 'assets/uploads/logos/' . $Settings->logo2 . '" alt="' . $Settings->site_name . '" style="margin-bottom:10px;" />';
			} ?>
			<div style="font-size:12px;">
				<div>Vattanac Capital Mall Unit G1</div>
				<div>No 66, Preah Monivong, Phnom Penh, Cambodia</div>
				<div>Tel.(855)78 777 938</div>
			</div>
		</div>
		 <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("services/edit_service/".$id, $attrib); ?>
		<div class="row">
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:0;">
				<div class="col-md-9 col-xs-9 col-lg-9" style="padding:0;">
					<div style="width:13%;float:left;">Name<span style=" float: right; padding-right: 5px;text-align: right;">:</span></div>
					<div style="float: left; width: 22%;">
						<?php
							$cu[''] = "";
							foreach ($customer as $customers) {
								$cu[$customers->id] = $customers->name;
							}
							echo form_dropdown('name', $cu, $services->name, 'class="form-control select" id="name" required="required" style="width:100%;" placeholder="'.lang('select').' '.lang('name').'"');
						?>
					</div>
					<div style="float: left; width: 16%; text-align: center;">IC/Passport No</div>
					<div style="float: left; width: 33%;"><input type="text" name="ic_no" value="<?=$services->ic_passport_no;?>" class="form-control"/></div>
					<div style="float: left; width: 15%; text-align: center;">Date of reception</div>
				</div>
				<div class="col-md-3 col-xs-3 col-lg-3" style="padding:0;"><input name="recept_date" value="<?=$this->erp->hrld($services->date_of_reception);?>" class="form-control datetime input-xs" id="recept_date" type="text"></div>
			</div>
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:0;">
				<div class="col-md-9 col-xs-9 col-lg-9" style="padding:0;">
					<div style="width:13%;float:left;">Contact<span style=" float: right; padding-right: 5px;text-align: right;">:</span></div>
					<div style="float: left; width: 24%;"><input type="text" name="HPline" value="<?=$services->hpline;?>" class="form-control"/></div>
					<div style="float: left; width: 5%; text-align: center;">(HP)</div>
					<div style="float: left; width: 24%;"><input type="text" name="Hline" value="<?=$services->hline;?>" class="form-control"/></div>
					<div style="float: left; width: 5%; text-align: center;">(H)</div>
					<div style="float: left; width: 29%;">
						Type of Customers 
					</div>
				</div>
				<div class="col-md-3 col-xs-3 col-lg-3" style="padding:0;"></div>
			</div>
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:0;">
				<div class="col-md-9 col-xs-9 col-lg-9" style="padding:0;">
					<div style="width:13%;float:left;">Email<span style=" float: right; padding-right: 5px;text-align: right;">:</span></div>
					<div style="float: left; width: 57%;padding-right:2%;"><input type="email" name="email" value="<?=$services->email;?>" class="form-control"/></div>
					<div style="float: left; width: 30%;">
						<input id="exist_cust" type="checkbox" value="1" class="checkbox" name="type_cust" <?= $services->type_customer == 1 ? 'checked="checked"' : ''; ?> />&nbsp; Existing Customer 
						<input id="new_cust" type="checkbox" value="0" class="checkbox" name="type_cust" <?= $services->type_customer != 1 ? 'checked="checked"' : ''; ?> />&nbsp; Walk in 
					</div>
				</div>
				<div class="col-md-3 col-xs-3 col-lg-3" style="padding:0;"></div>
			</div>
		</div>
		<br/>
		<div class="row">
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:10px 0;background-color:black;color:white;text-align:center;">
				PRODUCT INFORMATION
			</div>
		</div>
		<br/>
		<div class="row">
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding: 0;">
				<div style="width:10%;float:left;">Brand:</div>
				<div style="width:30%;float:left;">
					<div class="from-group">
						<select id="test" name="brand" class="form-control select" placeholder="<?php echo lang("select").' '.lang("brand")?>">		
							<option></option>
							<?php								
								foreach($brands as $brand){							
							?>							
								<option value="<?php echo $brand->id;?>" <?= ($brand->id == $services->brand? 'selected':'')?>><?php echo $brand->name;?></option>						
							<?php							
								}							
							?>	
							<option class="editable" <?= ($services->tbrand != "" ? 'selected':'')?> value="<?= ($services->tbrand != "" ? $services->tbrand:'&nbsp;')?>"><?= ($services->tbrand != "" ? $services->tbrand:'Other')?></option>					
						</select>						
						<input class="editOption form-control" name="other_brand" <?= ($services->tbrand != "" ? 'style="display:block;"':'style="display:none;"')?> placeholder="You can type" value="<?= ($services->tbrand != "" ? $services->tbrand:'&nbsp;')?>"></input>
					</div>
				</div>
				<div style="width:10%;float:left;text-align:center;">Jewelry:</div>
				<div style="width:20%;float:left;text-align:left;">
					Yes <input type="checkbox" <?= $services->is_jewelry == 1 ? 'checked="checked"' : ''; ?> value="1" name="is_jewelry" class="checkbox je_yes"/>
					No <input type="checkbox" <?= $services->is_jewelry != 1 ? 'checked="checked"' : ''; ?> value="0" name="is_jewelry" class="checkbox je_no"/>
				</div>
				<div style="width:5%;float:left;">Type</div>
				<div style="width:25%;float:left;">
					<div class="from-group">
						<?php
							$ty[''] = "";
							foreach ($type as $types) {
								$ty[$types->id] = $types->name;
							}
							//echo form_dropdown('category', $ty, $services->type, 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"');
							if($services->type != 'Watch' && $services->type != 'Ring' && $services->type != 'Necklace' && $services->type != 'Earring' && $services->type != 'Bracelet' && $services->type != 'Bangle' && $services->type != 'Pendent' && $services->type != 'Other'){
								$other = $services->type;
							}else{
								$other = '';
							}
						?>
						<!--<input type="text" class="form-control" value="<?= $services->type;?>" name="category" />-->
						<select id="type" name="category" class="form-control" placeholder="<?php echo lang("select").' '.lang("Type")?>">
							<option></option>
							<option value="Watch" <?= ($services->type == 'Watch'? 'selected':'')?> >Watch</option>	
							<option value="Ring" <?= ($services->type == 'Ring'? 'selected':'')?> >Ring</option>							
							<option value="Necklace" <?= ($services->type == 'Necklace'? 'selected':'')?> >Necklace</option>							
							<option value="Earring" <?= ($services->type == 'Earring'? 'selected':'')?> >Earring</option>							
							<option value="Bracelet" <?= ($services->type == 'Bracelet'? 'selected':'')?> >Bracelet</option>							
							<option value="Bangle" <?= ($services->type == 'Bangle'? 'selected':'')?> >Bangle</option>							
							<option value="Pendent" <?= ($services->type == 'Pendent'? 'selected':'')?> >Pendant</option>							
							<option value="Other" <?= ($services->type == 'Other'? 'selected':'')?> >Other</option>							
							<option class="editype" <?= ($other != "" ? 'selected' :'')?> value="<?= ($other != "" ? $other :'&nbsp;')?>"><?= ($other != "" ? $other :'Type')?></option>						
						</select>						
						<input class="typeOption form-control" <?= ($other != "" ? 'style="display:block;"' :'style="display:none;"')?> placeholder="Text" value="<?= ($other != "" ? $other :'&nbsp;')?>"></input>
					</div>
				</div>
			</div>
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding: 0;">
				<div style="width:11%;float:left;">Model/ Ref<span style=" float: right; padding-right: 5px;text-align: right;">:</span></div>
				<div style="width:15%;float:left;">
					<div class="from-group">
						<input class="form-control" name="model" value="<?= ($services->tmodal != "" ? $services->tmodal :'&nbsp;')?>" ></input>
					</div>
				</div>
				<div style="width:10%;float:left;text-align:center;">Description:</div>			
				<div style="width:18%;float:left;">
					<input type="text" name="description" value="<?=$services->detail2;?>" class="form-control"/>
				</div>
				<div style="width:8%;float:left;text-align:center;">Serial No:</div>
				<div style="width:15%;float:left;"><input type="text" name="serial_no" value="<?=$services->serial_no?>" class="form-control"/></div>
				<div style="width:8%;float:left;text-align:center;">Warranty:</div>
				<div style="width:15%;float:left;">
					Yes <input type="checkbox" <?= $services->is_warranty == 1 ? 'checked="checked"' : ''; ?> value="1" name="is_warranty" class="checkbox wa_yes"/>
					No <input type="checkbox" <?= $services->is_warranty != 1 ? 'checked="checked"' : ''; ?> value="0" name="is_warranty" class="checkbox wa_no"/>
				</div>
			</div>
		</div>
		<br/>
		<div class="row">
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding: 0;">
				<div class="table-responsive">
					<table class="table table-bordered">
						<tr>
							<th style="width:10%;text-align:center;">Functionality</th>
							<th style="width:15%;text-align:center;">Movement</th>
							<th style="width:15%;text-align:center;">Style</th>
							<th style="width:60%;text-align:center;"></th>
						</tr>
						<tr>
							<td>
								<div>Running 
									<span style="float:right;"><input type="checkbox" value="1" class="checkbox" <?= $services->rinning ? 'checked="checked"' : ''; ?> name="rinning" /></span>
								</div>
								<div style="margin-top:10px;">		
									Stopped 
									<span style="float:right;"><input type="checkbox" <?= $services->stopped ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="stopped" /></span>
								</div>
							</td>
							<td>
								<div>Manual-winding
									<span style="float:right;"><input type="checkbox" <?= $services->manual_winding ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="manual_winding" /></span>
								</div>
								<div style="margin-top:10px;">		
									Self-winding 
									<span style="float:right;"><input type="checkbox" <?= $services->self_winding ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="self_winding"/></span>
								</div>
								<div style="margin-top:10px;">		
									Quartz 
									<span style="float:right;"><input type="checkbox" value="1" class="checkbox" <?= $services->quartz ? 'checked="checked"' : ''; ?> name="quartz"/></span>
								</div>
							</td>
							<td>
								<div>Gents 
									<span style="float:right;"><input type="checkbox" value="1" class="checkbox" <?= $services->gents ? 'checked="checked"' : ''; ?> name="gents"/></span>
								</div>
								<div style="margin-top:10px;">		
									Ladies 
									<span style="float:right;"><input type="checkbox" <?= $services->ladies ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="ladies"/></span>
								</div>
							</td>
							<td>
								<div style="float:left; width:33.33%;padding-left:10px;">
									<div>Platinum 
										<span style="float:right;"><input type="checkbox" <?= $services->platinum ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="platinum"/></span>
									</div>
									<div style="margin-top:10px;">White Gold 
										<span style="float:right;"><input type="checkbox" <?= $services->white_gold ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="white_gold" /></span>
									</div>
									<div style="margin-top:10px;">Rose Gold
										<span style="float:right;"><input type="checkbox" <?= $services->rose_gold ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="rose_gold" /></span>
									</div>
									<div style="margin-top:10px;">	Yellow Gold 
										<span style="float:right;"><input type="checkbox" <?= $services->yellow_gold ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="yellow_gold" /></span>
									</div>
								</div>
								<div style="float:left; width:33.33%;padding-left:10px;">
									<div>Titanium 
										<span style="float:right;"><input type="checkbox" <?= $services->titanium ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="titanium"/></span>
									</div>
									<div style="margin-top:10px;">	Steel 
										<span style="float:right;"><input type="checkbox" <?= $services->steel ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="steel" /></span>
									</div>
									<div style="margin-top:10px;">Two Tone
										<span style="float:right;"><input type="checkbox" <?= $services->bi_color ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="bi_color" /></span>
									</div>
								</div>
								<div style="float:left; width:33.33%;padding-left:10px;">
									<div>Leather 
										<span style="float:right;"><input type="checkbox" <?= $services->leather ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="leather" /></span>
									</div>
									<div style="margin-top:10px;">	Calf 
										<span style="float:right;"><input type="checkbox" <?= $services->calf ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="calf" /></span>
									</div>
									<div style="margin-top:10px;">Rubber/Canvas 
										<span style="float:right;"><input type="checkbox" <?= $services->rubber_canvas ? 'checked="checked"' : ''; ?> value="1" class="checkbox" name="rubber_canvas" /></span>
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
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:10px 0;background-color:black;color:white;text-align:center;">
				VISUAL EXAMINATION
			</div>
		</div>
		<br/>
		<div class="row">
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:0;">
				<div style="width:15%;float:left;">
					<img src="<?=base_url().'/assets/images/1.png'?>" style="width:100%"/>
				</div>
				<div style="width:15%;float:left;">
					<img src="<?=base_url().'/assets/images/2.png'?>" style="width:100%"/>
				</div>
				<div style="width:15%;float:left;">
					<img src="<?=base_url().'/assets/images/3.png'?>" style="width:100%"/>
				</div>
				<div style="width:45%;float:right;border:1px solid #ccc;padding:0 30px;">
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
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:0;">
				<div class="table-responsive">
					<table class="table table-bordered">
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
										<span style="float:right;"><input type="checkbox" <?= $services->normal_scratched ? 'checked="checked"' : ''; ?> value="1" name="normal_scratched" class="checkbox" /></span>
										<span style="float:right;padding-right:5px;">Normal Scratched</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->case_scratched ? 'checked="checked"' : ''; ?> value="1" name="case_scratched" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Scratched</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->lugs_scratched ? 'checked="checked"' : ''; ?> value="1" name="lugs_scratched" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Scratched</span>
									</div>
									<div style="width:100%;float:left;">		
										<span style="float:right;"><input type="checkbox" <?= $services->bezel_scratched ? 'checked="checked"' : ''; ?> value="1" name="bezel_scratched" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Scratched</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->crown_missing ? 'checked="checked"' : ''; ?> value="1" name="crown_missing" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Crown Missing</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->dial_scratched ? 'checked="checked"' : ''; ?> value="1" name="dial_scratched" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Scratched</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->hand_oxidized ? 'checked="checked"' : ''; ?> value="1" name="hand_oxidized" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Oxidized</span>
									</div>
									<div style="width:100%;float:left;">	
										<span style="float:right;"><input type="checkbox" <?= $services->caseback_scratched ? 'checked="checked"' : ''; ?> value="1" name="caseback_scratched" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Scratched</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->strap_warm ? 'checked="checked"' : ''; ?> value="1" name="strap_warm" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Worm</span>
									</div>
									<div style="width:100%;float:left;">	
										<span style="float:right;"><input type="checkbox" <?= $services->bracelet_scratched ? 'checked="checked"' : ''; ?> value="1" name="bracelet_scratched" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Scratched</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->buckle_scratched ? 'checked="checked"' : ''; ?> value="1" name="buckle_scratched" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Scratched</span>
									</div>
								</div>
								<div style="float:left;width:20%;padding-left:15px;">
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->heavy_scratches ? 'checked="checked"' : ''; ?> value="1" name="heavy_scratches" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Heavy Scratches</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->case_dent ? 'checked="checked"' : ''; ?> value="1" name="case_dent" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Dent</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->lugs_dent ? 'checked="checked"' : ''; ?> value="1" name="lugs_dent" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Dent</span>
									</div>
									<div style="width:100%;float:left;">		
										<span style="float:right;"><input type="checkbox" <?= $services->bezel_dent ? 'checked="checked"' : ''; ?> value="1" name="bezel_dent" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Dent</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->crown_demaged ? 'checked="checked"' : ''; ?> value="1" name="crown_demaged" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Crown Damaged</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->dial_stained ? 'checked="checked"' : ''; ?> value="1" name="dial_stained" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Stained</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->hand_jammed ? 'checked="checked"' : ''; ?> value="1" name="hand_jammed" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Jammed</span>
									</div>
									<div style="width:100%;float:left;">	
										<span style="float:right;"><input type="checkbox" <?= $services->caseback_dent ? 'checked="checked"' : ''; ?> value="1" name="caseback_dent" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Dent</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->strap_damagged ? 'checked="checked"' : ''; ?> value="1" name="strap_damagged" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Damaged</span>
									</div>
									<div style="width:100%;float:left;">	
										<span style="float:right;"><input type="checkbox" <?= $services->bracelet_oxidized ? 'checked="checked"' : ''; ?> value="1" name="bracelet_oxidized" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Oxidized</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->buckle_demagged ? 'checked="checked"' : ''; ?> value="1" name="buckle_demagged" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Damaged</span>
									</div>
								</div>
								<div style="float:left;width:20%;padding-left:15px;">
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->severely_scratches ? 'checked="checked"' : ''; ?> value="1" name="severely_scratches" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Severely Scratches</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->case_deep_dent ? 'checked="checked"' : ''; ?> value="1" name="case_deep_dent" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Deep Dent</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->lugs_deep_dent ? 'checked="checked"' : ''; ?> value="1" name="lugs_deep_dent" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Deep Dent</span>
									</div>
									<div style="width:100%;float:left;">		
										<span style="float:right;"><input type="checkbox" <?= $services->bezel_deep_dent ? 'checked="checked"' : ''; ?> value="1" name="bezel_deep_dent" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Deep Dent</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->crown_pusher_missing ? 'checked="checked"' : ''; ?> value="1" name="crown_pusher_missing" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Pusher Missing</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->dial_oxidized ? 'checked="checked"' : ''; ?> value="1" name="dial_oxidized" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Oxidized</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->hands_disloged ? 'checked="checked"' : ''; ?> value="1" name="hands_disloged" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Disloged</span>
									</div>
									<div style="width:100%;float:left;">	
										<span style="float:right;"><input type="checkbox" <?= $services->caseback_chipped ? 'checked="checked"' : ''; ?> value="1" name="caseback_chipped" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Chipped</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->strap_other ? 'checked="checked"' : ''; ?> value="1" name="strap_other" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Other</span>
									</div>
									<div style="width:100%;float:left;">	
										<span style="float:right;"><input type="checkbox" <?= $services->bracelet_damagged ? 'checked="checked"' : ''; ?> value="1" name="bracelet_damagged" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Damaged</span>
									</div>
								</div>
								<div style="float:left;width:20%;padding-left:15px;">
									<div style="width:100%;float:left;">
										<span style="float:right;"></span>
										<span style="float:right;padding-right:5px;">&nbsp;</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->case_impacted ? 'checked="checked"' : ''; ?> value="1" name="case_impacted" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Impacted</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->lugs_impacted ? 'checked="checked"' : ''; ?> value="1" name="lugs_impacted" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Impacted</span>
									</div>
									<div style="width:100%;float:left;">		
										<span style="float:right;"><input type="checkbox" value="1" name="bezel_craked" <?= $services->bezel_craked ? 'checked="checked"' : ''; ?> class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Cracked</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->crown_pusser_damaged ? 'checked="checked"' : ''; ?> value="1" name="crown_pusser_damaged" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Pusher Damaged</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->dial_damaged ? 'checked="checked"' : ''; ?> value="1" name="dial_damaged" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Damaged</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"></span>
										<span style="float:right;padding-right:5px;">&nbsp;</span>
									</div>
									<div style="width:100%;float:left;">	
										<span style="float:right;"><input type="checkbox" value="1" name="caseback_craked" <?= $services->caseback_craked ? 'checked="checked"' : ''; ?> class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Craked</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" value="1" name="strap_no_strap" <?= $services->strap_no_strap ? 'checked="checked"' : ''; ?> class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">No Strap</span>
									</div>
								</div>
								<div style="float:left;width:20%;padding-left:15px;">
									<div style="width:100%;float:left;">
										<span style="float:right;"></span>
										<span style="float:right;padding-right:5px;">&nbsp;</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->case_out_of_strap ? 'checked="checked"' : ''; ?> value="1" name="case_out_of_strap" class="checkbox"/></span>
										<span style="float:right;padding-right:5px;">Out of Strap</span>
									</div>
									<div style="width:100%;float:left;">
										<span style="float:right;"><input type="checkbox" <?= $services->lugs_out_of_strap ? 'checked="checked"' : ''; ?> value="1" name="lugs_out_of_strap" class="checkbox"/></span>
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
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:15px 0 15px 5px;border:1px solid #ccc;text-align:left;">
				REMARKS
			</div>
		</div>
		<br/>
		<div class="row">
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:0;">
				<textarea name="detail1"><?=$services->detail1?></textarea>
			</div>
		</div>
		<br/>
		<div class="row">
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:10px 0;background-color:black;color:white;text-align:center;">
				TERMS & CONDITIONS
			</div>
		</div>
		<br/>
		<div class="row">
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:0 0 0 15px;">
				<ul>
					<li>This serves as an acknowledgement for reception of watch for repair/service without payment.</li>
					<li>Client will be notified by staff of Timepieces Boutique on the repair charges via email and/or preffered mode of contact as per stipulated.</li>
					<li>Payment terms will be COD in either cash or credit card only. Personal cheque is not accepted.</li>
					<li>Original copy of this Repair Slip must be presented during collection of watch/es.</li>
					<li>Payment terms will be COD in either cash or credit card only. Personal cheque is not accepted.</li>
					<li>In the event that the repair item (as per model indicated in this repair slip) is left uncollected after 18 months from date of collection, the client shall deemed to have given up all rights, interest and ownership to this item. Horography & Karat Co .Ltd, shall gain ownership and deal with the item with absolute discretion including the right to dispose it by way of sale.</li>
				</ul>
			</div>
		</div>
		<br/>
		<div class="row">
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:0;">
				<div class="table-responsive">
					<table class="table table-bordered">
						<tr><td colspan="2" style="background-color:black;color:white;text-align:center;padding:10px 0;border-bottom:1px solid #fff;">TIMEPIECES</td></tr>
						<tr>
							<td style="background-color:black;color:white;text-align:center;border:none !important;">RECEIVED BY</td>
							<td style="background-color:black;color:white;text-align:center;border:none !important;">CLIENT'S ACKNOWLEDGEMENT</td>
						</tr>
						<tr style="border:1px solid #000;">
							<td style="border:1px solid #000;width:50%;">
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Staff Name</div>
									<div style="width:80%;float:left;"><input type="text" value="<?=$services->staff_name?>" name="staff_name" class="form-control" name="staff_name"/></div>
								</div>
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Signature</div>
									<div style="width:80%;float:left;"><input type="text" value="<?=$services->staff_signature?>" name="staff_signature" class="form-control"/></div>
								</div>
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Date</div>
									<div style="width:80%;float:left;"><input name="staff_date" value="<?=$this->erp->hrld($services->staff_date);?>" class="form-control datetime" id="staff_date" type="text"></div>
								</div>
							</td>
							<td style="border:1px solid #000;width:50%;">
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Client Name</div>
									<div style="width:80%;float:left;"><input type="text" value="<?=$services->client_name?>" name="client_name" class="form-control" id="client_name"/></div>
								</div>
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Signature</div>
									<div style="width:80%;float:left;"><input value="<?=$services->client_signature?>" type="text" name="client_signature" class="form-control"/></div>
								</div>
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Date</div>
									<div style="width:80%;float:left;"><input name="client_date" value="<?=$this->erp->hrld($services->client_date);?>" class="form-control datetime input-xs" id="client_date" type="text"></div>
								</div>
							</td>
						</tr>
						<tr><td colspan="2" style="background-color:black;color:white;text-align:center;padding:10px 0;border-bottom:1px solid #fff;">SERVICE CENTER</td></tr>
						<tr>
							<td style="background-color:black;color:white;text-align:center;border:none !important;">RECEIVED BY</td>
							<td style="background-color:black;color:white;text-align:center;border:none !important;">SENT BACK TO TIMEPIECES BY</td>
						</tr>
						<tr style="border:1px solid #000;">
							<td style="border:1px solid #000;width:50%;">
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Name</div>
									<div style="width:80%;float:left;"><input type="text" value="<?=$services->staff_name1?>" name="staff_name1" class="form-control"/></div>
								</div>
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Signature</div>
									<div style="width:80%;float:left;"><input type="text" name="staff_signature1" value="<?=$services->staff_signature1?>" class="form-control"/></div>
								</div>
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Date</div>
									<div style="width:80%;float:left;"><input name="staff_date1" value="<?=$this->erp->hrld($services->staff_date1)?>" class="form-control datetime input-xs" id="staff_date1" type="text"></div>
								</div>
							</td>
							<td style="border:1px solid #000;width:50%;">
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Name</div>
									<div style="width:80%;float:left;"><input type="text" value="<?=$services->client_name1?>" name="client_name1" class="form-control" /></div>
								</div>
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Signature</div>
									<div style="width:80%;float:left;"><input type="text" class="form-control" value="<?=$services->client_signature1?>" name="client_signature1"/></div>
								</div>
								<div class="" style="width:100%;float:left;">
									<div style="width:20%;float:left;">Date</div>
									<div style="width:80%;float:left;"><input name="client_date1" value="<?=$this->erp->hrld($services->client_date1);?>" class="form-control datetime input-xs" id="client_date1" type="text"></div>
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
		<div class="row">
			<div class="col-md-12 col-xs-12 col-lg-12" style="padding:0;">
				<ul style="list-style-type:none;">
					<li style="border-bottom:1px solid #000;list-style-type:none;margin-bottom:20px;"></li>
					<li style="border-bottom:1px solid #000;list-style-type:none;margin-bottom:20px;"></li>
					<li style="border-bottom:1px solid #000;list-style-type:none;margin-bottom:20px;"></li>
					<li style="border-bottom:1px solid #000;list-style-type:none;margin-bottom:20px;"></li>
					<li style="border-bottom:1px solid #000;list-style-type:none;margin-bottom:20px;"></li>
				</ul>
			</div>
		</div>
		<br/>
		<?php echo form_submit('edit_service', lang('edit_service'), 'class="btn btn-primary"'); ?>
		<!--<button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>-->
		
		<?php echo form_close(); ?>
		<br/>
	</div>
	<script type="text/javascript">
		$(document).ready(function() {
			$('.checkbox.je_yes').on('ifChanged', function(){
				$(".checkbox.je_no").iCheck('uncheck');
			});
			
			$('.checkbox.je_no').on('ifChanged', function(){
				$(".checkbox.je_yes").iCheck('uncheck');
			});
			
			$('.checkbox.wa_yes').on('ifChanged', function(){
				$(".checkbox.wa_no").iCheck('uncheck');
			});
			
			$('.checkbox.wa_no').on('ifChanged', function(){
				$(".checkbox.wa_yes").iCheck('uncheck');
			});
			
			var initialText = $('.editable').val();			
			$('.editOption').val(initialText);			
			$('#test').change(function(){				
				var selected = $('option:selected', this).attr('class');				
				var optionText = $('.editable').text();				
				if(selected == "editable"){				  
					$('.editOption').show();				  				  
					$('.editOption').keyup(function(){					  
						var editText = $('.editOption').val();
						$('.editable').val(editText);					  
						$('.editable').html(editText);				  
					});				
				}else{				  
					$('.editOption').hide();					
				}			
			});						
			var initialText = $('.editype').val();			
			$('.typeOption').val(initialText);						
			$('#type').change(function(){				
				var selected = $('option:selected', this).attr('class');				
				var optionText = $('.editype').text();				
				if(selected == "editype"){				  
					$('.typeOption').show();				  				  
					$('.typeOption').keyup(function(){					  
						var editText = $('.typeOption').val();					  
						$('.editype').val(editText);					  
						$('.editype').html(editText);				  
					});				
				}else{				  
					$('.typeOption').hide();				
				}			
			});
			//Add open box in Model and Ref
			var initialText = $('.edit-model').val();			
			$('.show-model').val(initialText);						
			$('#model').change(function(){				
				var selected = $('option:selected', this).attr('class');				
				var optionText = $('.edit-model').text();				
				if(selected == "edit-model"){				  
					$('.show-model').show();				  				  
					$('.show-model').keyup(function(){					  
						var editText = $('.show-model').val();					  
						$('.edit-model').val(editText);					  
						$('.edit-model').html(editText);				  
					});				
				}else{				  
					$('.show-model').hide();				
				}			
			});
		});
	</script>
</body>

</html>
