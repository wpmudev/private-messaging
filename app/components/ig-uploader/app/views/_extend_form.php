<?php
$upload_new_id = uniqid();
$c_id = uniqid();
?>
<div id="<?php echo $c_id ?>">
    <div class="panel panel-default" style="border-width: 1px;position:relative;">
        <div class="panel-heading">
            <strong><?php _e('Attach spec examples or extra information', ig_uploader()->domain) ?></strong>
            <button type="button" data-target="#<?php echo $upload_new_id ?>" data-toggle="popover-x"
                    data-placement="left"
                    class="btn btn-primary btn-xs pull-right add-file"><?php _e('Add', ig_uploader()->domain) ?> <i
                    class="glyphicon glyphicon-plus"></i>
            </button>
            <div id="<?php echo $upload_new_id ?>" style="min-width: 360px;" class="popover popover-default popover-lg">
                <div class="arrow"></div>
                <div class="popover-content">
                    <?php $this->render_partial('_uploader_form') ?>
                </div>
            </div>
        </div>
        <div class="panel-body file-view-port">
            <?php if (is_array($models) && count($models)): ?>
                <?php foreach ($models as $model): ?>
                    <?php $this->render_partial('_single_file', array(
                        'model' => $model
                    )) ?>
                <?php endforeach; ?>
                <div class="clearfix"></div>
            <?php else: ?>
                <p class="no-file"><?php _e("No sample file.", ig_uploader()->domain) ?></p>
            <?php endif; ?>
            <div class="clearfix"></div>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#<?php echo $c_id ?>').on('submit', '.igu-upload-form', function () {
                var that = $(this);
                $.ajax('<?php echo add_query_arg('igu_uploading','1') ?>', {
                    type: 'POST',
                    data: $(this).find(':input').serializeArray(),
                    files: $(this).find(':file').first(),
                    iframe: true,
                    processData: false,
                    beforeSend: function () {
                        that.find('button').attr('disabled', 'disabled');
                    },
                    success: function (data) {
                        data = $(data).text();
                        data = $.parseJSON(data);
                        that.find('button').removeAttr('disabled');
                        if (data.status == 'success') {
                            //check case update or case insert
                            if (that.find('#ig_uploader_model-id').size() > 0) {
                                var html = $(data.html);
                                $('#igu-media-file-' + data.id).html(html.html());
                                that.closest('div').parent().popoverX('refreshPosition').hide();
                            } else {
                                var container = $('#<?php echo $c_id ?>');
                                var file_view_port = container.find('.file-view-port');

                                file_view_port.find('.no-file').remove();
                                file_view_port.prepend(data.html);

                                var form = $('#<?php echo $form_id ?>');
                                var input = form.find('#<?php echo $target_id ?>');

                                input.val(input.val() + ',' + data.id);
                                that.find(':input:not([type=hidden])').val('');
                                that.closest('div').parent().popoverX('refreshPosition').hide();
                            }
                        } else {
                            that.find('.form-group').removeClass('has-error has-success');
                            $.each(data.errors, function (i, v) {
                                var element = that.find('.error-' + i);
                                element.parent().addClass('has-error');
                                element.html(v);
                            });
                            that.find('.form-group').each(function () {
                                if (!$(this).hasClass('has-error')) {
                                    $(this).find('.m-b-none').text('');
                                    $(this).addClass('has-success');
                                }
                            });
                            //repos
                            //that.closest('div').parent().popoverX('refreshPosition');
                        }
                    }
                })
                return false;
            });

            $('body').on('click', '.igu-file-delete', function () {
                var id = $(this).data('id');
                var that = $(this);
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url('admin-ajax.php') ?>',
                    data: {
                        action: 'igu_file_delete',
                        id: id,
                        _wpnonce: '<?php echo wp_create_nonce('igu_file_delete') ?>'
                    },
                    beforeSend: function () {
                        that.parent().parent().find('button').attr('disabled', 'disabled');
                        that.parent().parent().css('opacity', 0.5);

                    },
                    success: function () {
                        $('#<?php echo $target_id ?>').val($('#<?php echo $target_id ?>').val().replace(id, ''));
                        that.parent().parent().remove();
                    }
                })
            })
        })
    </script>
</div>