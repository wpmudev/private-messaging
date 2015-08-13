<?php $model = new MM_Message_Model();
?>
<div class="ig-container">
    <div class="mmessage-container">
        <div>
            <div class="modal" id="inject-message">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"><?php _e("Compose Message", mmg()->domain) ?></h4>
                        </div>
                        <?php $form = new IG_Active_Form($model);
                        $form->open(array("attributes" => array("class" => "form-horizontal", "id" => "inject-message-form"))); ?>
                        <div class="modal-body">
                            <div style="margin-bottom: 0"
                                 class="form-group <?php echo $model->has_error("subject") ? "has-error" : null ?>">
                                <?php $form->label("subject", array(
                                    "text" => __("Subject", mmg()->domain),
                                    "attributes" => array("class" => "control-label col-sm-2 hidden-xs hidden-sm")
                                )) ?>
                                <div class="col-md-10 col-sm-12 col-xs-12">
                                    <?php $form->text("subject", array("attributes" => array("class" => "form-control", "placeholder" => __("Subject", mmg()->domain)))) ?>
                                    <span
                                        class="help-block m-b-none error-subject"><?php $form->error("subject") ?></span>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div style="margin-bottom: 0"
                                 class="form-group <?php echo $model->has_error("content") ? "has-error" : null ?>">
                                <?php $form->label("content", array(
                                    "text" => __("Content", mmg()->domain),
                                    "attributes" => array("class" => "control-label col-sm-2 hidden-xs hidden-sm")
                                )) ?>
                                <div class="col-md-10 col-sm-12 col-xs-12">
                                    <?php $form->text_area("content", array(
                                        "attributes" => array(
                                            "class" => "form-control mm_wsysiwyg",
                                            "placeholder" => __("Content", mmg()->domain),
                                            "style" => "height:100px",
                                            "id" => "mm_compose_content"
                                        )
                                    )) ?>
                                    <span
                                        class="help-block m-b-none error-content"><?php $form->error("content") ?></span>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <?php echo $form->hidden('attachment') ?>
                            <input type="hidden" name="action" value="mm_inject_message">
                            <input type="hidden" name="conversation_id" value="<?php echo $conversation_id ?>">
                            <?php if (mmg()->can_upload() == true) {
                                ig_uploader()->show_upload_control($model, 'attachment', false, array(
                                    'title' => __("Attach media or other files.", mmg()->domain)
                                ));
                            } ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default compose-close"
                                    data-dismiss="modal"><?php _e("Close", mmg()->domain) ?></button>
                            <button type="submit"
                                    class="btn btn-primary compose-submit"><?php _e("Send", mmg()->domain) ?></button>
                        </div>
                        <?php $form->close(); ?>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
        </div>
    </div>
</div>
