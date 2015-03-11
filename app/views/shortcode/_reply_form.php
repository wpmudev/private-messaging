<?php $c_id = uniqid();
if (!isset($message)) {
    //get the current
    $messages = $this->messages;
    $message = array_shift($messages);
    if (!is_object($message)) {
        return;
    }
}
?>
<div class="ig-container">
    <div class="mmessage-container">
        <div class="modal" id="reply-form-c">
            <div class="modal-dialog">
                <div class="modal-content" id="reply-compose">
                    <?php $model = new MM_Message_Model() ?>
                    <?php $form = new IG_Active_Form($model);
                    ?>
                    <div class="modal-header">
                        <h4 class="modal-title"><?php _e("Reply", mmg()->domain) ?></h4>
                    </div>
                    <?php $form->open(array(
                        "attributes" => array(
                            "class" => "form-horizontal compose-form",
                            "id" => "reply-form"
                        )
                    )); ?>
                    <div class="modal-body">
                        <?php do_action('mm_before_reply_form', $message, $form) ?>
                        <div class="form-group <?php echo $model->has_error("content") ? "has-error" : null ?>">
                            <div class="col-lg-12">
                                <?php $form->text_area("content", array(
                                    "attributes" => array(
                                        "class" => "form-control mm_wsysiwyg",
                                        "id" => "mm_reply_content",
                                        "style" => "height:100px"
                                    )
                                )) ?>
                                <span
                                    class="help-block m-b-none error-content"><?php $form->error("content") ?></span>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <?php wp_nonce_field('compose_message') ?>
                        <input type="hidden" name="is_reply" value="1">
                        <input type="hidden" name="action" value="mm_send_message">
                        <input type="hidden" name="parent_id"
                               value="<?php echo mmg()->encrypt($message->conversation_id) ?>">
                        <input type="hidden" name="id" value="<?php echo mmg()->encrypt($message->id) ?>">
                        <?php $form->hidden('attachment');
                        ?>

                        <?php if (mmg()->can_upload() == true) {
                            ig_uploader()->show_upload_control($model, 'attachment', false, array(
                                'title' => __("Attach media or other files.", mmg()->domain),
                                'c_id' => 'mm_reply_compose_container'
                            ));
                        } ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-default compose-close"><?php _e("Close", mmg()->domain) ?></button>
                        <button type="submit"
                                class="btn btn-primary reply-submit"><?php _e("Send", mmg()->domain) ?></button>
                    </div>
                    <?php $form->close(); ?>
                </div>
                <!-- /.modal-content -->
            </div>
        </div>
    </div>
</div>