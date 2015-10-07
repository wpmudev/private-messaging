<div class="wrap">
    <div class="ig-container">
        <div class="mmessage-container">

            <div class="row">
                <div class="col-md-12">
                    <div class="page-heading">
                        <h2><?php _e("Messages", mmg()->domain) ?></h2>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <?php
                            $table = new MM_Messages_Table();
                            $table->prepare_items();
                            $table->display();
                            ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        $('.lock-conv').click(function (e) {
            e.preventDefault();
            var that = $(this);
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'mm_lock_conversation',
                    type: that.data('type'),
                    id: that.data('id')
                },
                beforeSend: function () {
                    that.attr('disabled', 'disabled')
                },
                success: function (data) {
                    that.removeAttr('disabled');
                    that.data('type', data.type);
                    that.html(data.text)
                }
            })
        });
        $('.delete-message-frm').submit(function () {
            if (confirm('<?php echo __("Are you sure",mmg()->domain) ?>')) {
                var that = $(this);
                $.ajax({
                    type: 'POST',
                    data: $(this).serializeAssoc(),
                    url: ajaxurl,
                    beforeSend: function () {
                        that.find('button').attr('disabled');
                    },
                    success: function () {
                        that.closest('tr').remove();
                    }
                })
            }
            return false;
        })
    })
</script>
