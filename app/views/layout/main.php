<div class="mmessage-container">
    <div class="row">
        <div class="col-md-8 no-padding mm-toolbar-btn">
            <div class="btn-group btn-group-sm">
                <a href="<?php echo add_query_arg('box', 'inbox') ?>"
                   class="mm-tooltip btn btn-default <?php echo fRequest::get('box', 'string', 'inbox') == 'inbox' ? 'active' : null ?>"
                   data-container=".mm-toolbar-btn"
                   data-placement="top"
                   title="<?php echo MM_Conversation_Model::count_all() ?> <?php _e("message(s)", mmg()->domain) ?>">
                    <i class="fa fa-inbox"></i> <?php _e("Inbox", mmg()->domain) ?>
                </a>
                <a href="<?php echo add_query_arg('box', 'unread') ?>"
                   class="mm-tooltip btn btn-default <?php echo fRequest::get('box') == 'unread' ? 'active' : null ?>"
                   data-placement="top"
                   data-container=".mm-toolbar-btn"
                   title="<?php echo MM_Conversation_Model::count_unread() ?> <?php _e("message(s)", mmg()->domain) ?>">
                    <i class="fa fa-envelope"></i> <?php _e("Unread", mmg()->domain) ?>
                </a>
                <a href="<?php echo add_query_arg('box', 'read') ?>"
                   class="mm-tooltip btn btn-default <?php echo fRequest::get('box') == 'read' ? 'active' : null ?>"
                   data-placement="top"
                   data-container=".mm-toolbar-btn"
                   title="<?php echo MM_Conversation_Model::count_read() ?> <?php _e("message(s)", mmg()->domain) ?>">
                    <i class="glyphicon glyphicon-eye-open"></i> <?php _e("Read", mmg()->domain) ?>
                </a>
                <a href="<?php echo add_query_arg('box', 'sent') ?>"
                   class="btn btn-default <?php echo fRequest::get('box') == 'sent' ? 'active' : null ?>">
                    <i class="glyphicon glyphicon-send"></i> <?php _e("Sent", mmg()->domain) ?>
                </a>

            </div>
        </div>
        <div class="col-md-4 no-padding text-right">
            <a class="btn btn-default btn-sm" href="<?php echo add_query_arg('box', 'setting') ?>">
                <i class="fa fa-gear"></i> <?php _e("Settings", mmg()->domain) ?>
            </a>
            <a class="btn btn-primary btn-sm mm-compose" href="#">
                <?php _e("Compose", mmg()->domain) ?>
            </a>
        </div>
        <div class="clearfix"></div>
    </div>

    <?php echo $content; ?>
    <?php $this->render_partial('shortcode/_compose_form') ?>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('[data-toggle="tooltip"]').tooltip();
        $('#mmessage-list').perfectScrollbar({
            suppressScrollX: true
        });
        $('#mmessage-content').perfectScrollbar({
            suppressScrollX: true
        });
        $('body').on('click', '.mm-compose', function (e) {
            e.preventDefault();
            $('#compose-form-container').modal({
                keyboard: false
            })
        });
        $('.mm-tooltip').tooltip();
        $('body').on('submit', '.compose-form', function () {
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