<?php
$upload_new_id = uniqid();
$c_id = uniqid();
$f_id = uniqid();
?>
<div id="<?php echo $c_id ?>">
<div class="panel panel-default" style="margin-bottom: 5px;border-width: 1px;position:relative;">
    <div class="panel-heading">
        <strong class="hidden-xs hidden-sm"><?php _e('Attach images or files for extra information', ig_uploader()->domain) ?></strong>
        <small class="hidden-md hidden-lg"><?php _e('Attach images or files for extra information', ig_uploader()->domain) ?></small>
        <button type="button"
                rel="igu_popover"
                class="btn btn-primary btn-xs pull-right add-file"><?php _e('Add', ig_uploader()->domain) ?> <i
                class="glyphicon glyphicon-plus"></i>
        </button>
    </div>
    <div class="panel-body file-view-port">
        <?php if (is_array($models) && count($models)): ?>
            <?php foreach ($models as $model): ?>
                <?php $this->render_partial(apply_filters('igu_single_file_template', '_single_file'), array(
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
        $('#<?php echo $c_id ?> .add-file').popoverasync({
            "placement": "left",
            "trigger": "click",
            "title": "<?php echo esc_js(__("Upload Attachment",ig_uploader()->domain)) ?>",
            "html": true,
            "container": '#<?php echo $container ?>',
            "content": function (callback, extensionRef) {
                var that = $(this);
                $.ajax({
                    type: 'POST',
                    data: {
                        action: 'iup_load_upload_form',
                        _wpnonce: '<?php echo wp_create_nonce('iup_load_upload_form') ?>',
                        id: that.data('id')
                    },
                    async: true,
                    url: '<?php echo admin_url('admin-ajax.php') ?>',
                    success: function (html) {
                        extensionRef.options.content = html;
                        extensionRef.show();
                        //callback(extensionRef, html);
                    }
                });

            }
        }).on('shown.bs.popoverasync', function () {
            var that = $(this);
            var pop = that.data('bs.popoverasync');
            var form = pop.$tip.find('form').first();
            form.data('popover', pop.$tip.attr('id'));
            var container = pop.$tip;
            var cancel = container.find('.igu-close-uploader');
            cancel.unbind('click').on('click', function () {
                pop.hide();
            });
            //bind form data
            var cache_id = 'igu_cache_<?php echo $f_id ?>';
            if (window[cache_id] != undefined) {
                $.each(window[cache_id], function (i, v) {
                    form.find(':input[name="' + v.name + '"]').val(v.value);
                })
            }
        }).on('hide.bs.popoverasync', function () {
            var that = $(this);
            var pop = that.data('bs.popoverasync');
            //create a cache
            var form = pop.$tip.find('form').first();
            var cache_id = 'igu_cache_<?php echo $f_id ?>';
            window[cache_id] = form.serializeArray();
        });

        $('#<?php echo $c_id ?>').on('mouseenter', '.igu-file-update', function () {
            var that = $(this);
            if (that.data('bs.popoverasync') == null) {
                that.popoverasync({
                    "placement": "auto",
                    "trigger": "click",
                    "title": "<?php echo esc_js(__("Upload Attachment",ig_uploader()->domain)) ?>",
                    "html": true,
                    "container": '#<?php echo $container ?>',
                    "content": function (callback, extensionRef) {
                        var that = $(this);
                        $.ajax({
                            type: 'POST',
                            data: {
                                action: 'iup_load_upload_form',
                                _wpnonce: '<?php echo wp_create_nonce('iup_load_upload_form') ?>',
                                id: that.data('id')
                            },
                            async: true,
                            url: '<?php echo admin_url('admin-ajax.php') ?>',
                            success: function (html) {
                                //clear cache
                                var cache_id = 'igu_cache_' + that.data('id');
                                delete window[cache_id];
                                extensionRef.options.content = html;
                                extensionRef.show();
                                //callback(extensionRef, html);
                            }
                        });
                    }
                }).on('shown.bs.popoverasync', function () {
                    var that = $(this);
                    var pop = that.data('bs.popoverasync');
                    var form = pop.$tip.find('form').first();
                    form.data('popover', pop.$tip.attr('id'));
                    var container = pop.$tip;
                    var cancel = container.find('.igu-close-uploader');
                    cancel.unbind('click').on('click', function () {
                        pop.hide();
                    });
                    //bind form data
                    var cache_id = 'igu_cache_' + that.data('id');
                    if (window[cache_id] != undefined) {
                        $.each(window[cache_id], function (i, v) {
                            form.find(':input[name="' + v.name + '"]').val(v.value);
                        })
                    }
                }).on('hide.bs.popoverasync', function () {
                    var that = $(this);
                    var pop = that.data('bs.popoverasync');
                    //create a cache
                    var form = pop.$tip.find('form').first();
                    var cache_id = 'igu_cache_' + that.data('id');
                    window[cache_id] = form.serializeArray();
                })
            }
        });


        $('#<?php echo $container ?>').on('submit', '.igu-upload-form', function () {
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
                            $('#' + that.data('popover')).popoverasync('destroy');
                        } else {
                            var container = $('#<?php echo $c_id ?>');
                            var file_view_port = container.find('.file-view-port');

                            file_view_port.find('.no-file').remove();
                            file_view_port.prepend(data.html);

                            var form = $('#<?php echo $container ?>');
                            var input = form.find('#<?php echo $target_id ?>');

                            input.val(input.val() + ',' + data.id);
                            that.find(':input:not([type=hidden])').val('');
                            $('#' + that.data('popover')).popoverasync('hide');
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
                    }
                }
            })
            return false;
        });

        $('body').on('click', '.igu-file-delete', function () {
            var id = $(this).data('id');
            var that = $(this);
            var parent = that.closest('div').parent().parent();
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php') ?>',
                data: {
                    action: 'igu_file_delete',
                    id: id,
                    _wpnonce: '<?php echo wp_create_nonce('igu_file_delete') ?>'
                },
                beforeSend: function () {
                    /* that.parent().parent().find('button').attr('disabled', 'disabled');
                     that.parent().parent().css('opacity', 0.5);*/
                    parent.find('button').attr('disabled', 'disabled');
                    parent.css('opacity', 0.5);
                },
                success: function () {
                    $('#<?php echo $target_id ?>').val($('#<?php echo $target_id ?>').val().replace(id, ''));
                    parent.remove();
                }
            })
        });
    })
</script>
</div>