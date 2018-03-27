<style>
        @media print{
			 #wrapper{
				 margin:0px auto;
			 }
			 #bg{
				 background-color:Silver !important;
			 }
		}
</style>
<div id="wrapper" class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
			<div id="bd" class="row" style="margin-bottom:20px;">
                 <div class="col-sm-12 col-xs-12">
				      
						   <div style="margin:0px; padding-bottom:20px">
								  <img style="margin-left:-32px;" src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                                  alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
						   </div>
		
					   <div  style="border-bottom:solid 20px;"></div>
				 </div>
			</div>
            <div class="row" style="margin-bottom:15px;">
                <div class="col-sm-12 col-xs-12">
                     <table>
					        <tr>
							     <td>
								     	Vattanac Capital Mall, No. 68, Unit G1	
								 </td>
							</tr>
							<tr>
							     <td>
								      Preah Monivong Boulevard,	
								 </td>
							</tr>
							<tr>
							     <td>
								     	Phnom Penh, Cambodia
								 </td>
							</tr>
							<tr>
							     <td>
								      Phone: +855 78 777 938	
								 </td>
							</tr>
							<tr>
							     <td>
								     Email: timepiecesvc@horographykarat.com	
								 </td>
							</tr>
					 </table>
                </div>
               
				<div class="col-sm-12 col-xs-12 bold"style="margin-top:20px;">
				      <p>
					    Created by:&nbsp;<?=$notification->username; ?><br/>
						Date:&nbsp;<?= $notification->date; ?>
					  </p>
					 
				</div>
            </div>

            <div class="row">
                  <div class="col-sm-12 col-xs-12">
				     <table border="1px" width="100%"  style="border-bottom:solid 1px white !important;">
					        <tr id="bg" bgcolor="Silver" height="30px" style="border-bottom:solid 1px white !important;">
							    <th>&nbsp;Sender / Transfer From		
								</th>
							</tr>
							<tr>
							    <td>
								      
								      &nbsp;Name:&nbsp;<?= $notification->sender;?><br/><br/>
									  &nbsp;Signature:
										<br/><br/>
								</td>
							</tr>
					 </table>
					 <table border="1px" width="100%">
					        <tr id="bg" bgcolor="Silver" height="30px" style="border-bottom:solid 1px white !important;">
							    <th>&nbsp;&nbsp;Recipient / Transfer To
								</th>
							</tr>
							<tr>
							    <td>
								      
								      &nbsp;Name:&nbsp;&nbsp;<?= $notification->recipient;?><br/><br/>
									  &nbsp;Signature:
										<br/><br/>
								</td>
							</tr>
					 </table>
					 <table border="1px" width="100%">
					        <tr id="bg" bgcolor="Silver" height="30px" >
							    <th colspan="5">
								   &nbsp;Items Details
								</th>
							</tr>
							<tr height="29px">
							    <th class="text-center col-sm-1 col-xs-1">
								    No
								</th>
								<th class="text-center col-sm-8 col-xs-8">
								    Items
								</th>
								<th class="text-center col-sm-3 col-xs-3">
								     Qauntity
								</th>
								
							</tr>
						<?php $n=1;?>
						<?php foreach($notification_item as $item){ ?>	
						
							<tr class="text-center" height="29px">
								<td><?= $n; ?></td>
								<td><?= $item->item; ?></td>
								<td><?=$this->erp->formatQuantity($item->quantity); ?></td>
							</tr>

							
						<?php $n++; } ?>	
							
					 </table>
					  <table border="1px" width="100%">
					        <tr height="30px" style="border-bottom:solid 1px white !important;">
							    <th id="bg" bgcolor="Silver" colspan="2">
								  &nbsp;Comments

								</th>
							</tr>
							<tr>
							    <td height="150px" valign="top">
								     <div style="width:100%;float:left;margin:20px 0px;padding-left:10px;"><?=$notification->comment;?></div> 
								     <div style="float:left; width:50%;padding-left:10px;">Delivered by / Signature:</div>
								
								     <div style="float:left;width:50%;"><p style="float:right;padding-right:10%;">Received by/ Signature:</p></div>	
								</td>
							</tr>
					 </table>
				  </div>
            </div>

            <div class="row">
                
            </div>
            
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready( function() {
        $('.tip').tooltip();
    });
</script>
