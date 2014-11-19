<?php $model = new MM_Message_Model();
?>
<div class="modal fade" id="compose-form-container">
    <div class="modal-dialog" style="width: 659px">
        <div class="modal-content" id="compose-modal">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e("Compose Message", mmg()->domain) ?></h4>
            </div>
            <?php $form = new IG_Active_Form($model);
            $form->open(array("attributes" => array("class" => "compose-form", "id" => "compose-form")));?>
            <div class="modal-body">

                <div style="margin-bottom: 0" class="form-group <?php echo $model->has_error("send_to") ? "has-error" : null ?>">
                    <?php $form->label("send_to", array("text" => "Send To", "attributes" => array("class" => "control-label"))) ?>
                    <?php $form->text("send_to", array("attributes" => array("class" => "form-control"))) ?>
                    <!--<span
                                class="help-block m-b-none"><?php /*_e("Please enter the username, separate by commas", mmg()->domain) */ ?></span>-->
                    <span class="help-block m-b-none error-send_to"><?php $form->error("send_to") ?></span>
                    <div class="clearfix"></div>
                </div>
                <div style="margin-bottom: 0" class="form-group <?php echo $model->has_error("subject") ? "has-error" : null ?>">
                    <?php $form->label("subject", array("text" => "Subject", "attributes" => array("class" => "control-label"))) ?>
                    <?php $form->text("subject", array("attributes" => array("class" => "form-control"))) ?>
                    <span class="help-block m-b-none error-subject"><?php $form->error("subject") ?></span>
                    <div class="clearfix"></div>
                </div>
                <div style="margin-bottom: 0" class="form-group <?php echo $model->has_error("content") ? "has-error" : null ?>">
                    <?php $form->label("content", array("text" => "Content", "attributes" => array("class" => "control-label"))) ?>
                    <?php $form->text_area("content", array("attributes" => array("class" => "form-control mm_wsysiwyg", "style" => "height:100px", "id" => "mm_compose_content"))) ?>
                    <span class="help-block m-b-none error-content"><?php $form->error("content") ?></span>
                    <div class="clearfix"></div>
                </div>
                <?php echo wp_nonce_field('compose_message') ?>
                <?php echo $form->hidden('attachment') ?>
                <input type="hidden" name="action" value="mm_send_message">

                <?php ig_uploader()->show_upload_control($model, 'attachment', "compose-modal") ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?php _e("Close", mmg()->domain) ?></button>
                <button type="submit" class="btn btn-primary compose-submit"><?php _e("Send", mmg()->domain) ?></button>
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
        $('#mm_message_model-send_to').selectize({
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
