<?php $model = new MM_Message_Model();
?>
    <div class="ig-container">
        <div class="mmessage-container">
            <div>
                <div class="modal" id="compose-form-container">
                    <div class="modal-dialog">
                        <div class="modal-content" id="compose-modal">
                            <div class="modal-header">
                                <h4 class="modal-title"><?php _e("Compose Message", mmg()->domain) ?></h4>
                            </div>
                            <?php $form = new IG_Active_Form($model);
                            $form->open(array("attributes" => array("class" => "compose-form form-horizontal", "id" => "compose-form"))); ?>
                            <div class="modal-body">
                                <div style="margin-bottom: 0"
                                     class="form-group <?php echo $model->has_error("send_to") ? "has-error" : null ?>">
                                    <?php $form->label("send_to", array(
                                        "text" => __("Send To", mmg()->domain),
                                        "attributes" => array("class" => "control-label col-sm-2 hidden-xs hidden-sm")
                                    )) ?>
                                    <div class="col-md-10 col-sm-12 col-xs-12">
                                        <?php $form->text("send_to", array("attributes" => array("class" => "form-control", "placeholder" => __("Send to", mmg()->domain)))) ?>
                                        <?php do_action('mm_compose_form_after_send_to', $form, $model) ?>
                                        <span class="help-block m-b-none error-send_to">
                                        <?php $form->error("send_to") ?>
                                    </span>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <?php do_action('mm_before_subject_field', $model, $form, 'compose_form') ?>
                                <div style="margin-bottom: 0"
                                     class="form-group <?php echo $model->has_error("subject") ? "has-error" : null ?>">
                                    <?php $form->label("subject", array(
                                        "text" => __("Subject", mmg()->domain),
                                        "attributes" => array("class" => "control-label col-sm-2 hidden-xs hidden-sm")
                                    )) ?>
                                    <div class="col-md-10 col-sm-12 col-xs-12">
                                        <?php $form->text("subject", array("attributes" => array("class" => "form-control", "placeholder" => __("Subject", mmg()->domain)))) ?>
                                        <?php do_action('mm_compose_form_after_subject', $form, $model) ?>
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
                                        <?php do_action('mm_compose_form_after_content', $form, $model) ?>
                                        <span
                                            class="help-block m-b-none error-content"><?php $form->error("content") ?></span>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <?php echo wp_nonce_field('compose_message') ?>
                                <?php echo $form->hidden('attachment') ?>
                                <input type="hidden" name="action" value="mm_send_message">
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
    <!-- /.modal -->
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            window.mm_compose_select = $('#mm_message_model-send_to').selectize({
                valueField: 'id',
                labelField: 'name',
                searchField: 'name',
                options: [],
                create: false,
                load: function (query, callback) {
                    if (!query.length) return callback();
                    var instance = window.mm_compose_select[0].selectize;
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo admin_url('admin-ajax.php?action=mm_suggest_users&_wpnonce='.wp_create_nonce('mm_suggest_users')) ?>',
                        data: {
                            'query': query
                        },
                        beforeSend: function () {
                            instance.$control.append('<i style="position: absolute;right: 10px;" class="fa fa-circle-o-notch fa-spin"></i>');
                        },
                        success: function (data) {
                            instance.$control.find('i').remove();
                            callback(data);
                        }
                    });
                }
            });
        })
    </script>
<?php do_action('mm_compose_form_end') ?>