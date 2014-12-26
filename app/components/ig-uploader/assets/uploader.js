jQuery(function ($) {
    var file_port;
    $('body').on('mouseenter', '.add-file', function () {
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
                    pop.hide();
                });
            }
            igu_uploader.instance = that;
            file_port = that.parent().parent().find('.file-view-port');
        })
    });
    $('body').on('mouseenter', '.igu-file-update', function () {
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
                    pop.hide();
                });
            }
            igu_uploader.instance = that;
            file_port = that.closest('section');
        });
    })
    if (igu_uploader.is_admin) {
        $('body').on('submit', '.igu-upload-form', function () {
            var that = $(this);
            $.ajax(igu_uploader.form_submit_url, {
                type: 'POST',
                data: $(this).find(':input').serialize(),
                iframe: false,
                cache: false,
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

                            file_view_port.find('.no-file').remove();
                            file_view_port.prepend(data.html);

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
                $('#attachment').val(attachment.id);
            });

            // Finally, open the modal
            file_frame.open();
        });
    } else {
        $('body').on('submit', '.igu-upload-form', function () {
            var that = $(this);
            $.ajax(igu_uploader.form_submit_url, {
                type: 'POST',
                data: $(this).find(':input').serializeArray(),
                files: $(this).find(':file').first(),
                iframe: true,
                cache: false,
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
                        if (igu_uploader.instance.hasClass('add-file') == false) {
                            var html = $(data.html);
                            igu_uploader.instance.webuiPopover('destroy');
                            $('#igu-media-file-' + data.id).html(html.html());
                        } else {
                            var file_view_port = file_port;

                            file_view_port.find('.no-file').remove();
                            file_view_port.prepend(data.html);

                            var input = file_view_port.closest('form').find('#' + igu_uploader.target_id);
                            console.log(input);
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
    }
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
})