<?php
$bid = $this->button_id;
$mid = 'modal-' . $this->button_id;
$fid = 'form-' . $bid;
?>
<div class="mmessage-container">
    <div class="modal fade" id="<?php echo $mid ?>">
        <div class="modal-dialog">
            <div class="modal-content">
                <?php if (!is_user_logged_in()) {
                    ?>
                    <div class="modal-body text-left">
                        <?php $this->render_partial('shortcode/login') ?>
                    </div>
                    <script type="text/javascript">
                        jQuery(function ($) {
                            $('body').on('click', '#<?php echo $bid ?>', function () {
                                $('#<?php echo $mid ?>').modal({
                                    keyboard: false
                                })
                            });
                        })
                    </script>
                <?php
                } else {
                $model = new MM_Message_Model();
                $model->send_to = $user->user_login;
                $model->subject = $a['subject'];
                $model = apply_filters('mm_message_me_before_init', $model);
                ?>
                <?php $form = new IG_Active_Form($model);
                ?>
                <?php $form->open(array("attributes" => array("class" => "", "id" => $fid))); ?>
                    <div class="modal-header">
                        <h4 class="modal-title text-left"><?php _e("Compose Message", mmg()->domain) ?></h4>
                    </div>
                    <div class="modal-body text-left">
                        <div class="alert alert-success hide mm-notice">
                            <?php _e("Your message has been sent", mmg()->domain) ?>
                        </div>
                        <?php if ($a['subject']): ?>
                            <?php $form->hidden('subject') ?>
                        <?php else: ?>
                            <div class="form-group <?php echo $model->has_error("subject") ? "has-error" : null ?>">
                                <?php $form->label("subject", array("text" => "Subject", "attributes" => array("class" => "col-lg-2 control-label"))) ?>
                                <div class="col-lg-10">
                                    <?php $form->text("subject", array("attributes" => array("class" => "form-control mm-wysiwyg"))) ?>
                                    <span
                                        class="help-block m-b-none error-subject"><?php $form->error("subject") ?></span>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        <?php endif; ?>
                        <?php $form->hidden('send_to') ?>
                        <div style="margin-bottom: 0"
                             class="form-group <?php echo $model->has_error("content") ? "has-error" : null ?>">
                            <?php $form->text_area("content", array("attributes" => array("class" => "form-control mm_wsysiwyg", "style" => "height:100px", "id" => "mm_compose_content"))) ?>
                            <span class="help-block m-b-none error-content"><?php $form->error("content") ?></span>

                            <div class="clearfix"></div>
                        </div>
                        <?php wp_nonce_field('compose_message') ?>
                        <input type="hidden" name="action" value="mm_send_message">
                        <?php $form->hidden('attachment') ?>
                        <?php ig_uploader()->show_upload_control($model, 'attachment', $mid) ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal"><?php _e("Close", mmg()->domain) ?></button>
                        <button type="submit"
                                class="btn btn-primary reply-submit"><?php _e("Send", mmg()->domain) ?></button>
                    </div>
                <?php $form->close(); ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            $('body').on('click', '#<?php echo $bid ?>', function () {
                                $('#<?php echo $mid ?>').modal({
                                    keyboard: false
                                })
                            });
                            $('body').on('submit', '#<?php echo $fid ?>', function () {
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
                                            $('#<?php echo $mid ?>').find('.mm-notice').removeClass('hide');
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
                <?php } ?>
            </div>
        </div>
    </div>
</div>