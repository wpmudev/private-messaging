<div class="ig-container">
    <?php do_action('mm_before_layout') ?>
    <div class="mmessage-container">
        <div class="row">
            <div class="col-md-10 co-sm-12 col-xs-12 no-padding mm-toolbar-btn">
                <div class="btn-group btn-group-sm">
                    <a href="<?php echo add_query_arg('box', 'inbox', get_permalink(mmg()->setting()->inbox_page)) ?>"
                       class="mm-tooltip btn btn-default btn-sm <?php echo mmg()->get('box', 'inbox') == 'inbox' ? 'active' : null ?>"
                       title="<?php echo MM_Conversation_Model::count_all() ?> <?php _e("message(s)", mmg()->domain) ?>">
                        <i class="fa fa-inbox"></i> <?php _e("Inbox", mmg()->domain) ?>
                    </a>
                    <a href="<?php echo add_query_arg('box', 'unread', get_permalink(mmg()->setting()->inbox_page)) ?>"
                       class="mm-tooltip unread-count btn btn-default btn-sm <?php echo mmg()->get('box') == 'unread' ? 'active' : null ?>"

                       data-text="<?php _e("message(s)", mmg()->domain) ?>"
                       title="<?php echo MM_Conversation_Model::count_unread() ?> <?php _e("message(s)", mmg()->domain) ?>">
                        <i class="fa fa-envelope"></i> <?php _e("Unread", mmg()->domain) ?>
                    </a>
                    <a href="<?php echo add_query_arg('box', 'read', get_permalink(mmg()->setting()->inbox_page)) ?>"
                       class="mm-tooltip btn read-count btn-default btn-sm <?php echo mmg()->get('box') == 'read' ? 'active' : null ?>"

                       data-text="<?php _e("message(s)", mmg()->domain) ?>"
                       title="<?php echo MM_Conversation_Model::count_read() ?> <?php _e("message(s)", mmg()->domain) ?>">
                        <i class="glyphicon glyphicon-eye-open"></i> <?php _e("Read", mmg()->domain) ?>
                    </a>

                    <a href="<?php echo add_query_arg('box', 'sent', get_permalink(mmg()->setting()->inbox_page)) ?>"
                       class="btn btn-default btn-sm <?php echo mmg()->get('box') == 'sent' ? 'active' : null ?>">
                        <i class="glyphicon glyphicon-send"></i> <?php _e("Sent", mmg()->domain) ?>
                    </a>
                    <a href="<?php echo add_query_arg('box', 'archive', get_permalink(mmg()->setting()->inbox_page)) ?>"
                       class="btn btn-default btn-sm <?php echo mmg()->get('box') == 'archive' ? 'active' : null ?>">
                        <i class="glyphicon glyphicon-briefcase"></i> <?php _e("Archive", mmg()->domain) ?>
                    </a>
                    <a class="btn btn-default btn-sm hidden-xs hidden-sm"
                       href="<?php echo add_query_arg('box', 'setting') ?>">
                        <i class="fa fa-gear"></i> <?php _e("Settings", mmg()->domain) ?>
                    </a>
                </div>
            </div>
            <div class="col-md-2 hidden-xs hidden-sm no-padding text-right">
                <a class="btn btn-primary btn-sm mm-compose" href="#compose-form-container">
                    <?php _e("Compose", mmg()->domain) ?>
                </a>
            </div>
            <!--For small viewport-->
            <div class="col-sm-12 col-xs-12 hidden-md hidden-lg no-padding">
                <br/>
                <a class="btn btn-default btn-sm" href="<?php echo add_query_arg('box', 'setting') ?>">
                    <i class="fa fa-gear"></i> <?php _e("Settings", mmg()->domain) ?>
                </a>
                <a class="btn btn-primary btn-sm mm-compose" href="#compose-form-container">
                    <?php _e("Compose", mmg()->domain) ?>
                </a>
            </div>
            <div class="clearfix"></div>
        </div>

        <?php echo $content; ?>

    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        if ($.fn.tooltip != undefined) {
            $('.mm-tooltip').tooltip({
                position: {
                    my: "center bottom-15",
                    at: "center top",
                    using: function (position, feedback) {
                        $(this).css(position);
                        $("<div>")
                            .addClass("arrow bottom")
                            .addClass(feedback.vertical)
                            .addClass(feedback.horizontal)
                            .appendTo(this);
                    },
                    open: function (event, ui) {
                        console.log(event);
                        console.log(ui);
                    }
                },
                tooltipClass: 'ig-container'
            });
        }
    })
</script>