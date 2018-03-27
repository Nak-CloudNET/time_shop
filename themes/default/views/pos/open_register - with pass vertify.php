<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-briefcase"></i><?= lang("open_register"); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="well well-sm col-sm-6">
                    <?php 
                        $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'open-register-form');
                        if($Owner || $Admin){
                            echo form_open_multipart("pos/open_register", $attrib);
                        }

                    ?>
                    <div class="form-group">
                        <?= lang('cash_in_hand', 'cash_in_hand') ?>
                        <?= form_input('cash_in_hand', '', 'id="cash_in_hand" class="form-control" required'); ?>
                    </div>
                    <?php 
                        if($Owner || $Admin){
                    ?>
                    <?php echo form_submit('open_register', lang('open_register'), 'class="btn btn-primary"'); ?>
                    <?php echo form_close(); ?>
                    <?php
                        }else{
                    ?>
                        <a class="btn btn-primary" href="<?= site_url('pos/register_modal'); ?>" id="test" data-toggle="modal" data-target="#myModal">Open Register</a>
                    <?php
                        }
                    ?>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>
