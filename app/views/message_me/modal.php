<div class="ig-container">
    <div class="mmessage-container">
        <div class="modal" id="message-me-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <?php if (!is_user_logged_in()) {
                        ?>
                        <button type="button" class="compose-close btn btn-xs"
                                style="position: absolute;top:5px;right:5px;z-index:999">x
                        </button>
                        <div class="modal-body text-left">
                            <?php $this->render_partial('shortcode/login') ?>
                        </div>
                    <?php
                    } else {
                        $model = new MM_Message_Model();
                        $model = apply_filters('mm_message_me_before_init', $model);
                        ?>
                        <?php $form = new IG_Active_Form($model);
                        ?>
                        <?php $form->open(array("attributes" => array("class" => "", "id" => 'message-me-form'))); ?>
                        <div class="modal-header">
                            <h4 class="modal-title text-left"><?php _e("Compose Message", mmg()->domain) ?></h4>
                        </div>
                        <div class="modal-body text-left">
                            <div class="alert alert-success hide mm-notice">
                                <?php _e("Your message has been sent", mmg()->domain) ?>
                            </div>
                            <div class="message-me-has-subject hide">
                                <?php $form->hidden('subject') ?>
                            </div>
                            <div class="message-me-no-subject hide">
                                <div class="form-group <?php echo $model->has_error("subject") ? "has-error" : null ?>">
                                    <?php $form->label("subject", array("text" => __("Subject", mmg()->domain), "attributes" => array("class" => "col-lg-2 control-label"))) ?>
                                    <div class="col-lg-10">
                                        <?php $form->text("subject", array("attributes" => array("class" => "form-control", "disabled" => "disabled"))) ?>
                                        <span
                                            class="help-block m-b-none error-subject"><?php $form->error("subject") ?></span>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <?php $form->hidden('send_to', array('attributes' => array(
                                'class' => 'message-me-send-to'
                            ))); ?>
                            <div style="margin-bottom: 0"
                                 class="form-group <?php echo $model->has_error("content") ? "has-error" : null ?>">
                                <?php $form->text_area("content", array("attributes" => array("class" => "form-control mm_wsysiwyg", "style" => "height:100px", "id" => "mm_compose_content"))) ?>
                                <span class="help-block m-b-none error-content"><?php $form->error("content") ?></span>

                                <div class="clearfix"></div>
                            </div>
                            <?php wp_nonce_field('compose_message') ?>
                            <input type="hidden" name="action" value="mm_send_message">
                            <?php $form->hidden('attachment') ?>
                            <?php
                            if (mmg()->can_upload()) {
                                ig_uploader()->show_upload_control($model, 'attachment', false, array(
                                    'title' => __("Attach media or other files.", mmg()->domain),
                                    'c_id' => 'message_me_modal_container'
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
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        $('.message-me-btn').leanModal({
            closeButton: ".compose-close",
            top: '5%',
            width: '90%',
            maxWidth: 659
        });
        $('body').on('click', '.message-me-btn', function () {
            var data = $($(this).data('target'));
            var subject = data.find('.subject').first().text();
            var send_to = data.find('.send_to').first().text();
            if ($.trim(subject).length != 0) {
                $('.message-me-no-subject').addClass('hide').find('input').attr('disabled', 'disabled');
                ;
                $('.message-me-has-subject').removeClass('hide').find('input').val(subject);
            } else {
                $('.message-me-has-subject').addClass('hide');
                $('.message-me-no-subject').removeClass('hide').find('input').removeAttr('disabled');
            }
            $('.message-me-send-to').val(send_to);
        });
        $('body').on('submit', '#message-me-form', function () {
            var that = $(this);
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php') ?>',
                data: $(that).find(":input").serialize(),
                beforeSend: function () {
                    that.parent().parent().find('button').attr('disabled', 'disabled');
                },
                success: function (data) {
                    that.find('.form-group').removeClass('has-error has-success');
                    that.parent().parent().find('button').removeAttr('disabled');
                    if (data.status == 'success') {
                        that.find('.form-control').val('');
                        $('#message-me-modal').find('.mm-notice').removeClass('hide');
                        location.reload();
                    } else {
                        $.each(data.errors, function (i, v) {
                            var element = that.find('.error-' + i);
                            element.parent().parent().addClass('has-error');
                            element.html(v);
                        });
                        that.find('.form-group').each(function () {
                            if (!$(this).hasClass('has-error')) {
                                $(this).addClass('has-success');
                            }
                        })
                    }
                }
            })
            return false;
        });
    })
</script>