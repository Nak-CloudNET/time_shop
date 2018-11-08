<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('brand_reports') ; ?>
        </h2>
		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:void(0);" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
				    <?php echo form_open('reports/brand_reports', 'id="action-form" method="GET"'); ?>
                        <div class="row">

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="cat"><?= lang("brands"); ?></label>
                                    <?php
                                        $bra[""] = "ALL";
                                        foreach ($brand_search as $brand_){
                                            $bra[$brand_->id] = $brand_->name;
                                        }
                                        echo form_dropdown('brand_id', $bra, $_GET["brand_id"], 'class="form-control" ');
                                    ?>

                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="cat"><?= lang("categories"); ?></label>
                                    <?php
                                    $cat[""] = "ALL";
                                    foreach ($categories_search as $cate_){
                                        $cat[$cate_->id] = $cate_->name;
                                    }
                                    echo form_dropdown('category_id', $cat, $_GET["category_id"], 'class="form-control" ');
                                    ?>

                                </div>
                            </div>

                        </div>
                        <div class="form-group">
                            <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary sub"'); ?> </div>
                        </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table class="table table-bordered table-condensed table-striped">
                        <thead>
                            <tr class="info-head">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check" />
                                </th>
                                <th style="min-width:15px; width: 15px; text-align: center;"></th>
                                <th style="min-width:15px; width: 15px; text-align: center;"></th>
                                <th class="center"><?= lang("no"); ?></th>
                                <th><?= lang("image"); ?></th>
                                <th><?= lang("product_code"); ?></th>
                                <th><?= lang("product_name"); ?></th>
                                <th><?= lang("serial_number"); ?></th>
                                <th><?= lang("quantity"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $total_brands = 0;
                                foreach ($brands as $brand_id => $category) {
                                    $brand_name = $this->db->query("SELECT `name` FROM erp_brands WHERE id = {$brand_id}")->row()->name;
                                    $categories = $this->erp->groupArray($category, 'category_id');
                            ?>
                                    <tr>
                                        <td style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox multi-select" value="<?= $brand_id; ?>" type="checkbox" name="val[]" />
                                        </td>
                                        <td colspan="8" style="color: blueviolet;font-weight: bold">Brand Name: <?= $brand_name?$brand_name:'No Brand'; ?></td>
                                    </tr>
                                    <?php
                                        $ci = 1;
                                        $total_categories = 0;
                                        foreach ($categories as $category_id => $products) {
                                            $category_name = $this->db->query("SELECT `name` FROM erp_categories WHERE id = {$category_id}")->row()->name;
                                    ?>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td colspan="7" style="color: #013f50;font-weight: bold">Product Category: <?= $category_name; ?></td>
                                            </tr>
                                            <?php foreach ($products as $product){ ?>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="text-center">
                                                        <?= $ci; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <img src="<?=base_url()?>assets/uploads/thumbs/<?= $product['image']?>" alt="<?= $product['image']?>"
                                                             style="width:50px; height:50px;" class="img-circle">
                                                    </td>
                                                    <td><?= $product['code']; ?></td>
                                                    <td><?= $product['name']; ?></td>
                                                    <td>
                                                        <?php
                                                            $serial_number = $this->db->query("SELECT group_concat(`serial_number` separator '<br/>') AS `serial` FROM erp_serial WHERE product_id = {$product['id']} AND serial_status = 1")->row()->serial;
                                                            echo $serial_number;
                                                        ?>
                                                    </td>
                                                    <td class="text-center"><?= $this->erp->formatQuantity($product['quantity']); ?></td>
                                                </tr>
                                            <?php
                                                $total_categories += $product['quantity'];
                                                $ci++;
                                            }
                                            ?>
                                            <tr style="color: #013f50;font-weight: bold">
                                                <td></td>
                                                <td></td>
                                                <td colspan="6">Total Product Category: <?= $category_name; ?></td>
                                                <td class="text-center"><?= $this->erp->formatQuantity($total_categories); ?></td>
                                            </tr>
                                    <?php
                                            $total_brands += $total_categories;
                                            $total_categories = 0;
                                            $ci = 1;
                                        }
                                    ?>
                                    <tr style="color: blueviolet;font-weight: bold">
                                        <td></td>
                                        <td colspan="7">Total Brand: <?= $brand_name?$brand_name:'No Brand'; ?> </td>
                                        <td class="text-center"> <?= $this->erp->formatQuantity($total_brands); ?></td>
                                    </tr>
                            <?php
                                }
                            ?>
                        </tbody>
                        <tfoot>

                        </tfoot>

                    </table>
                </div>
                <div class=" text-right">
                    <div class="dataTables_paginate paging_bootstrap">
                        <?= $pagination; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
