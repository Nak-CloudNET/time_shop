<script>
    $(document).ready(function () {
        var oTable = $('#PrRData').dataTable({
            "aaSorting": [[3, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getCategoriesValueReport/?v=1') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, null, {"mRender": formatQuantity, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var st = 0, pr = 0, ta = 0;
                for (var i = 0; i < aaData.length; i++) {
                    st += parseFloat(aaData[aiDisplay[i]][2]);
					pr += parseFloat(aaData[aiDisplay[i]][3]);
					ta += parseFloat(aaData[aiDisplay[i]][4]);
                }
                var nCells = nRow.getElementsByTagName('th');
				nCells[2].innerHTML = formatQuantity(parseFloat(st));
				nCells[3].innerHTML = currencyFormat(parseFloat(pr));
                nCells[4].innerHTML = currencyFormat(parseFloat(ta));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('category_code');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('category_name');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('categories_value_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a id="pdf" href="#" id="pdf" data-action="export_pdf"  class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a id="xls" href="#" id="excel" data-action="export_excel"  class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a id="image" href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
<?php 
?>    
	<div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="PrRData"
                           class="table table-striped table-bordered table-condensed table-hover dfTable reports-table"
                           style="margin-bottom:5px;">
                        <thead>
                        <tr class="active">
                            <th><?= lang("category_code"); ?></th>
                            <th><?= lang("category_name"); ?></th>
                            <th><?= lang("category_stock"); ?></th>
                            <th><?= lang("costs"); ?></th>
							<th><?= lang("price"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th><?= lang("category_stock"); ?></th>
                            <th><?= lang("total_costs"); ?></th>
                            <th><?= lang("total_price"); ?></th>
                        </tr>
                        </tfoot>
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
            window.location.href = "<?=site_url('reports/getCategoriesValueReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getCategoriesValueReport/0/xls/?v=1'.$v)?>";
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
    });
</script>