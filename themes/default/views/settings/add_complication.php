<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_complication'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/addnew_complication", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<!--
            <div class="form-group">
                <label class="control-label" for="code"><?php echo $this->lang->line("code"); ?></label>

                <div
                    class="controls"> <?php echo form_input('code', '', 'class="form-control" id="code" required="required"'); ?> </div>
            </div>
			-->
			<div class="form-group">
                <label class="control-label" for="code"><?php echo $this->lang->line("name"); ?></label>

                <div
                    class="controls"> <?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?> </div>
            </div>
        <div class="modal-footer">
            <?php echo form_submit('add_complication', lang('add_complication'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
