<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header"> 
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
			<button type="button" class="btn btn-primary btn-xs no-print pull-right " onclick="window.print()">
				<i class="fa fa-print"></i>&nbsp;<?= lang("print"); ?>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" style="margin-bottom:0;">
                    <tbody>
                    <tr>
                        <td><strong><?= lang("company"); ?></strong></td>
                        <td><?= $customer->company; ?></strong></td>
                    </tr>
					<tr>
                        <td><strong><?= lang("customer_group"); ?></strong></td>
                        <td><?= $customer->customer_group_name; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("name"); ?></strong></td>
                        <td><?= $customer->name; ?></strong></td>
                    </tr>
					<tr>
                        <td><strong><?= lang("gender"); ?></strong></td>
                        <td><?= $customer->gender; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("phone"); ?></strong></td>
                        <td><?= $customer->phone; ?></strong></td>
                    </tr>
					<tr>
                        <td><strong><?= lang("amount_spent"); ?></strong></td>
                        <td><?= $customer->amount_spent; ?></strong></td>
                    </tr>
					<tr>
                        <td><strong><?= lang("store_credit"); ?></strong></td>
                        <td><?= $customer->deposit_amount; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("address"); ?></strong></td>
                        <td><?= $customer->address; ?></strong></td>
                    </tr>
					<tr>
                        <td><strong><?= lang("country"); ?></strong></td>
                        <td><?= $customer->country; ?></strong></td>
                    </tr>
					<tr>
                        <td><strong><?= lang("created_by"); ?></strong></td>
                        <td><?= $customer->username; ?></strong></td>
                    </tr>
					<tr>
                        <td><strong><?= lang("creation_date"); ?></strong></td>
                        <td><?= $customer->created_at; ?></strong></td>
                    </tr>
					<tr>
                        <td><strong><?= lang("comment_note"); ?></strong></td>
                        <td><?= $customer->comment_note; ?></strong></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= lang('close'); ?></button>
                <?php if ($Owner || $Admin || $GP['reports-customers']) { ?>
                    <a href="<?=site_url('reports/customer_report/'.$customer->id);?>" target="_blank" class="btn btn-primary"><?= lang('customers_report'); ?></a>
                <?php } ?>
                <?php if ($Owner || $Admin || $GP['customers-edit']) { ?>
                    <a href="<?=site_url('customers/edit/'.$customer->id);?>" data-toggle="modal" data-target="#myModal2" class="btn btn-primary"><?= lang('edit_customer'); ?></a>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>