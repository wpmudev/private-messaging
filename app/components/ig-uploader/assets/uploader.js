jQuery(function ($) {
    var file_port;
    var igu_uploader;
    $('body').on('mouseenter', '.add-file', function () {
        var key = 'igu_uploader_' + $(this).parent().parent().parent().attr('id');
        igu_uploader = window[key];
        $(this).webuiPopover({
            type: 'async',
            width: 'auto',
            height: 'auto',
            title: igu_uploader.title,
            url: igu_uploader.add_url,
            content: function (data) {
                return data;
            }
        }).on('show.webui.popover', function () {
            var that = $(this);
            var pop = that.data('plugin_webuiPopover');
            var container = pop.$target;
            var form = container.find('form').first();
            if (form.size() > 0) {
                $('body').on('click', '.igu-close-uploader', function () {
                    pop.destroy();
                });
            }
            igu_uploader.instance = that;
            file_port = that.parent().parent().find('.file-view-port');
            //make as fixed
        })
    });
    $('body').on('mouseenter', '.igu-file-update', function () {
        var key = 'igu_uploader_' + $(this).closest('section').parent().parent().attr('id');
        igu_uploader = window[key];
        $(this).webuiPopover({
            type: 'async',
            width: 'auto',
            height: 'auto',
            title: igu_uploader.title,
            url: igu_uploader.edit_url + $(this).data('id'),
            content: function (data) {
                return data;
            }
        }).on('show.webui.popover', function () {
            var that = $(this);
            var pop = that.data('plugin_webuiPopover');
            var container = pop.$target;
            var form = container.find('form').first();
            if (form.size() > 0) {
                $('body').on('click', '.igu-close-uploader', function () {
                    pop.destroy();
                });
            }
            igu_uploader.instance = that;
            file_port = that.closest('section');
        });
    })
    var file_frame;
    $('body').on('click', '.upload_image_button', function () {
        if (file_frame) {
            // Open frame
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: igu_uploader.file_frame_title,
            multiple: false  // Set to true to allow multiple files to be selected
        });
        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            // Do something with attachment.id and/or attachment.url here
            $('#attachment').first().val(attachment.id);
            $('.file-upload-name').first().text(attachment.filename);
        });

        file_frame.on('open', function () {
            file_frame.uploader.uploader.param("igu_uploading", "1");
        });

        // Finally, open the modal
        file_frame.open();
    });
    $('body').on('submit', '.igu-upload-form', function () {
        var that = $(this);
        $.ajax(igu_uploader.form_submit_url, {
            type: 'POST',
            data: $(this).find(':input').serialize(),
            processData: false,
            beforeSend: function () {
                that.find('button').attr('disabled', 'disabled');
            },
            success: function (data) {
                //data = $.parseJSON(data);
                that.find('button').removeAttr('disabled');
                if (data.status == 'success') {
                    //check case update or case insert
                    if (igu_uploader.instance.hasClass('add-file') == false) {
                        var html = $(data.html);
                        igu_uploader.instance.webuiPopover('destroy');
                        $('#igu-media-file-' + data.id).html(html.html());
                    } else {
                        var file_view_port = file_port;
                        var att = $(data.html);
                        att.css('display', 'none');

                        file_view_port.find('.no-file').remove();
                        file_view_port.prepend(att);
                        att.css('display', 'none');

                        file_view_port.find('.no-file').remove();
                        file_view_port.prepend(att);
                        if (file_view_port.width() <= (180 * 3)) {
                            att.css('width', '49%');
                        }
                        if (file_view_port.width() >= (180 * 4)) {
                            att.css('width', '25%');
                        }
                        att.css('display', 'block');
                        var input = file_view_port.closest('form').find('#' + igu_uploader.target_id);
                        input.val(input.val() + ',' + data.id);
                        that.find(':input:not([type=hidden])').val('');
                        igu_uploader.instance.webuiPopover('destroy');
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
    $('body').on('click', '.igu-file-delete', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var that = $(this);
        var parent = $('#igu-media-file-' + id);
        $.ajax({
            type: 'POST',
            url: igu_uploader.ajax_url,
            data: {
                action: 'igu_file_delete',
                id: id,
                _wpnonce: igu_uploader.delete_nonce
            },
            beforeSend: function () {
                /* that.parent().parent().find('button').attr('disabled', 'disabled');
                 that.parent().parent().css('opacity', 0.5);*/
                parent.find('button').attr('disabled', 'disabled');
                parent.css('opacity', 0.5);
            },
            success: function () {
                var element = $('#' + igu_uploader.target_id);
                element.val(element.val().replace(id, ''));
                parent.remove();
            }
        })
    });
    $('.file-view-port').each(function () {
        if ($(this).width() >= (180 * 4)) {
            $(this).find('.igu-media-file-land').css('width', '25%');
        }
        if ($(this).width() <= (180 * 3)) {
            $(this).find('.igu-media-file-land').css('width', '49%');
        }
    })
    $(window).scroll(function () {
        if (igu_uploader != undefined && igu_uploader.instance != undefined && typeof igu_uploader.instance == 'object') {
            var pop = igu_uploader.instance.data('plugin_webuiPopover');
            if (pop!=undefined && pop.$target.is(':visible')) {
                igu_uploader.instance.webuiPopover('reposition');
            }
        }
    })
})