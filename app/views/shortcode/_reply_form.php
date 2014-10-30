<?php $c_id = uniqid(); ?>

<div class="modal fade" id="reply-form-c">
    <div id="<?php echo $c_id ?>">
        <div class="modal-dialog">
            <div class="modal-content" id="reply-compose">
                <?php $model = new MM_Message_Model() ?>
                <?php $form = new IG_Active_Form($model);
                ?>
                <div class="modal-header">
                    <h4 class="modal-title"><?php _e("Reply Message", mmg()->domain) ?></h4>
                </div>
                <?php $form->open(array("attributes" => array("class" => "form-horizontal compose-form", "id" => "reply-form"))); ?>
                <div class="modal-body">
                    <div class="form-group <?php echo $model->has_error("content") ? "has-error" : null ?>">
                        <?php /*$form->label("content", array("text" => "Content", "attributes" => array("class" => "col-lg-2 control-label"))) */ ?>
                        <div class="col-lg-12">
                            <?php $form->text_area("content", array("attributes" => array("class" => "form-control mm_wsysiwyg", "id" => "mm_reply_content", "style" => "height:100px"))) ?>
                            <span class="help-block m-b-none error-content"><?php $form->error("content") ?></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <?php wp_nonce_field('compose_message') ?>
                    <input type="hidden" name="is_reply" value="1">
                    <input type="hidden" name="action" value="mm_send_message">
                    <?php echo $form->hidden('attachment') ?>
                    <?php ig_uploader()->show_upload_control($model, 'attachment', "reply-compose") ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal"><?php _e("Close", mmg()->domain) ?></button>
                    <button type="submit"
                            class="btn btn-primary reply-submit"><?php _e("Send", mmg()->domain) ?></button>
                </div>
                <?php $form->close(); ?>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</div>