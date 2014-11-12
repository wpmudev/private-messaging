<?php $model = new MM_Message_Model();
?>
<div class="mmessage-container">
    <div class="modal fade" id="compose-form-container-admin-bar">
        <div class="modal-dialog">
            <div class="modal-content" id="compose-modal-admin-bar">
                <div class="modal-header">
                    <h4 class="modal-title"><?php _e("Compose Message", mmg()->domain) ?></h4>
                </div>
                <?php $form = new IG_Active_Form($model);
                $form->open(array("attributes" => array("id" => "compose-form-admin-bar")));?>
                <div class="modal-body ">
                    <div class="alert alert-success compose-admin-bar-alert hide">
                        <?php _e("Your message has been sent.", mmg()->domain) ?>
                    </div>
                    <div style="margin-bottom: 0"
                         class="form-group <?php echo $model->has_error("send_to") ? "has-error" : null ?>">
                        <?php $form->label("send_to", array("text" => "Send To", "attributes" => array("class" => "control-label"))) ?>
                        <?php $form->text("send_to", array("attributes" => array("class" => "form-control", "id" => "admin-bar-mm-send-to"))) ?>
                        <!--<span
                                class="help-block m-b-none"><?php /*_e("Please enter the username, separate by commas", mmg()->domain) */ ?></span>-->
                        <span class="help-block m-b-none error-send_to text-left"><?php $form->error("send_to") ?></span>

                        <div class="clearfix"></div>
                    </div>
                    <div style="margin-bottom: 0"
                         class="form-group <?php echo $model->has_error("subject") ? "has-error" : null ?>">
                        <?php $form->label("subject", array("text" => "Subject", "attributes" => array("class" => "control-label"))) ?>
                        <?php $form->text("subject", array("attributes" => array("class" => "form-control"))) ?>
                        <span class="help-block m-b-none error-subject text-left"><?php $form->error("subject") ?></span>

                        <div class="clearfix"></div>
                    </div>
                    <div style="margin-bottom: 0"
                         class="form-group <?php echo $model->has_error("content") ? "has-error" : null ?>">
                        <?php $form->label("content", array("text" => "Content", "attributes" => array("class" => "control-label"))) ?>
                        <?php $form->text_area("content", array("attributes" => array("class" => "form-control mm_wsysiwyg", "style" => "height:100px", "id" => "mm_compose_content"))) ?>
                        <span class="help-block m-b-none error-content text-left"><?php $form->error("content") ?></span>

                        <div class="clearfix"></div>
                    </div>
                    <?php echo wp_nonce_field('compose_message') ?>
                    <?php echo $form->hidden('attachment') ?>
                    <input type="hidden" name="action" value="mm_send_message">

                    <?php ig_uploader()->show_upload_control($model, 'attachment', "compose-modal-admin-bar") ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
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
    <!-- /.modal -->
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('body').on('click', '.mm-compose-admin-bar', function (e) {
                e.preventDefault();
                $('#compose-form-container-admin-bar').modal({
                    keyboard: false
                })
            });

            $('body').on('submit', '#compose-form-admin-bar', function () {
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
                            $('.compose-admin-bar-alert').removeClass('hide');
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

            $('#admin-bar-mm-send-to').selectize({
                valueField: 'name',
                labelField: 'name',
                searchField: 'name',
                options: [],
                create: false,
                load: function (query, callback) {
                    if (!query.length) return callback();

                    $.ajax({
                        type: 'POST',
                        url: '<?php echo admin_url('admin-ajax.php?action=mm_suggest_users&_wpnonce='.wp_create_nonce('mm_suggest_users')) ?>',
                        data: {
                            'query': query
                        },
                        beforeSend: function () {
                            $('.selectize-input').append('<i style="position: absolute;right: 10px;" class="fa fa-circle-o-notch fa-spin"></i>');
                        },
                        success: function (data) {
                            $('.selectize-input').find('i').remove();
                            callback(data);
                        }
                    });
                }
            });

            var delay = (function () {
                var timer = 0;
                return function (callback, ms) {
                    clearTimeout(timer);
                    timer = setTimeout(callback, ms);
                };
            })();
        })
    </script>
</div>
