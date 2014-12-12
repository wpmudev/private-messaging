<?php
$upload_new_id = uniqid();
$c_id = uniqid();
$f_id = uniqid();
?>
<div id="<?php echo $c_id ?>">
<div class="panel panel-default" style="margin-bottom: 5px;border-width: 1px;position:relative;">
    <div class="panel-heading">
        <strong class="hidden-xs hidden-sm"><?php _e('Attach media or other files.', ig_uploader()->domain) ?></strong>
        <small class="hidden-md hidden-lg"><?php _e('Attach media or other files.', ig_uploader()->domain) ?></small>
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
        //$('.dropdown-toggle').dropdown();
        $('#<?php echo $c_id ?> .add-file').popoverasync({
            "placement": function(){
                function check_mob() {
                    var check = false;
                    (function(a,b){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
                    return check;
                }
                if(check_mob()==true){
                    return 'auto';
                }
                return 'left';
            },
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
                    //$('.dropdown-toggle').dropdown();
                }
            })
            return false;
        });

        $('body').on('click', '.igu-file-delete', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var that = $(this);
            var parent = that.closest('div').parent().parent().parent();
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