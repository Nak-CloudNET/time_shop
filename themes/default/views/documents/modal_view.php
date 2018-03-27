<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= $product->product_name; ?></h4>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-xs-5">
                    <img id="pr-image" src="<?= base_url() ?>assets/uploads/<?= $product->image ?>"
                    alt="<?= $product->product_name ?>" class="img-responsive img-thumbnail"/>

                    <div id="multiimages" class="padding10">
                        <?php if (!empty($images)) {
                            echo '<a class="img-thumbnail change_img" href="' . base_url() . 'assets/uploads/' . $product->image . '" style="margin-right:5px;"><img class="img-responsive" src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" alt="' . $product->image . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" /></a>';
                            foreach ($images as $ph) {
                                echo '<div class="gallery-image" style="display:inline;"><a class="img-thumbnail change_img" href="' . base_url() . 'assets/uploads/' . $ph->photo . '" style="margin-right:5px;"><img class="img-responsive" src="' . base_url() . 'assets/uploads/thumbs/' . $ph->photo . '" alt="' . $ph->photo . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" /></a>';
                                if ($Owner || $Admin || $GP['products-edit']) {
                                    echo '<a href="#" class="delimg" data-item-id="'.$ph->id.'"><i class="fa fa-times"></i></a>';
                                }
                                echo '</div>';
                            }
                        }
                        ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="col-xs-7">
                    <div class="table-responsive">
                        <table class="table table-borderless table-striped dfTable table-right-left">
                            <tbody>
                                <tr>
                                    <td colspan="2" style="background-color:#FFF;"></td>
                                </tr>
                                <tr>
                                    <td><?= lang("product_name"); ?></td>
                                    <td><?= $product->product_name; ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang("brand"); ?></td>
                                    <td><?= $brand->name; ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang("category"); ?></td>
                                    <td><?= $category->name; ?></td>
                                </tr>
                                <?php if ($product->subcategory_id) { ?>
                                <tr>
                                    <td><?= lang("subcategory"); ?></td>
                                    <td><?= $subcategory->name; ?></td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <td><?= lang("product_unit"); ?></td>
                                    <td><?= $UOM->name; ?></td>
                                </tr>
								<tr>
                                    <td><?= lang("price"); ?></td>
                                    <td><?= $this->erp->formatMoney($product->price); ?></td>
                                </tr>
								<tr>
                                    <td><?= lang("description"); ?></td>
                                    <td><?= $product->description; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="clearfix"></div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.change_img').click(function(event) {
            event.preventDefault();
            var img_src = $(this).attr('href');
            $('#pr-image').attr('src', img_src);
            return false;
        });
    });
</script>
