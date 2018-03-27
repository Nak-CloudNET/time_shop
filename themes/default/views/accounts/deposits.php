<style type="text/css">
	#CusData tr:hover>td{cursor:pointer;}
</style>
<script>
    $(document).ready(function () {
        function attachment(x) {
            if (x != null) {
                return '<a href="' + site.base_url + 'assets/uploads/' + x + '" target="_blank"><i class="fa fa-chain"></i></a>';
            }
            return x;
        }
        function center(x){
            return '<div style="text-align:center">' + x +'</div>';
        }

        $(document).ready(function () {
            $('.tip').tooltip();
            var oTable = $('#DepData').dataTable({
                "aaSorting": [[0, "desc"]],
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true, 'bServerSide': true,
                'sAjaxSource': '<?= site_url('account/getDeposits/') ?>',
                'fnServerData': function (sSource, aoData, fnCallback) {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                },
                "aoColumns": [{"mRender": fld}, null, {"mRender": null}, null, null, {"bSortable": false}]
            });
            $('div.dataTables_length select').addClass('form-control');
            $('div.dataTables_length select').addClass('select2');
            //$('div.dataTables_filter input').attr('placeholder', 'Date (yyyy-mm-dd)');
            $('select.select2').select2({minimumResultsForSearch: 7});
        });
    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('customers/customer_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('deposits'); ?></h2>
		<!--
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= site_url('customers/add'); ?>" data-toggle="modal" data-target="#myModal" id="add">
                                <i class="fa fa-plus-circle"></i> <?= lang("add_customer"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('customers/import_csv'); ?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-plus-circle"></i> <?= lang("import_by_csv"); ?>
                            </a>
                        </li>
                        <?php if ($Owner) { ?>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="pdf" data-action="export_pdf">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_customers") ?></b>" 
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_customers') ?>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
		-->
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                <table id="DepData" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-condensed table-hover table-striped">
                    <thead>
                    <tr class="primary">
                        <th class="col-xs-3"><?= lang("date"); ?></th>
						<th class="col-xs-2"><?= lang("customer"); ?></th>
                        <th class="col-xs-2"><?= lang("amount"); ?></th>
                        <th class="col-xs-2"><?= lang("paid_by"); ?></th>
                        <th class="col-xs-2"><?= lang("created_by"); ?></th>
                        <th class="col-xs-3"><?= lang("actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<?php if ($action && $action == 'add') {
    echo '<script>$(document).ready(function(){$("#add").trigger("click");});</script>';
}
?>
	

