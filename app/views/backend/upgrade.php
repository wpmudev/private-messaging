<?php global $wpdb;

?>
<div class="wrap">
    <div class="ig-container">
        <div class="mmessage-container">
            <div class="page-header">
                <h2><?php echo sprintf(__("Diagnose Center", mmg()->domain)) ?></h2>
            </div>
            <div class="row no-margin">
                <div class="alert alert-danger hide">

                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="col-md-4">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th><?php _e("Table", mmg()->domain) ?></th>
                                    <th><?php _e("Status", mmg()->domain) ?></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><strong><?php echo $wpdb->base_prefix ?>mm_conversation</strong></td>
                                    <td>
                                        <?php if ($c_status == true): ?>
                                            <span class="label label-success">
                                            <?php _e("Healthy", mmg()->domain) ?>
                                        </span>
                                        <?php else: ?>
                                            <span class="label label-danger">
                                            <?php _e("Fail", mmg()->domain) ?>
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($c_status == false): ?>
                                            <button type="button" data-type="c-table"
                                                    class="btn btn-warning btn-xs fix-table"><?php _e("Fix", mmg()->domain) ?></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo $wpdb->base_prefix ?>mm_status</strong></td>
                                    <td>
                                        <?php if ($s_status == true): ?>
                                            <span class="label label-success">
                                            <?php _e("Healthy", mmg()->domain) ?>
                                        </span>
                                        <?php else: ?>
                                            <span class="label label-danger">
                                            <?php _e("Fail", mmg()->domain) ?>
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($s_status == false): ?>
                                            <button type="button" data-type="s-table"
                                                    class="btn btn-warning btn-xs fix-table"><?php _e("Fix", mmg()->domain) ?></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-8">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th><?php _e("Note:", mmg()->domain) ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <?php _e("If the diagnose tool cannot create table, that means the database user doesn't have enough permission to do that, you will need to copy the below sql code to create the tables.", mmg()->domain) ?>
                                        <?php
                                        $charset_collate = '';

                                        if (!empty($wpdb->charset)) {
                                            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
                                        }

                                        if (!empty($wpdb->collate)) {
                                            $charset_collate .= " COLLATE {$wpdb->collate}";
                                        }

                                        $sql = "-- ----------------------------;<br/>
CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}mm_conversation` (<br/>
  `id` int(11) NOT NULL AUTO_INCREMENT,<br/>
  `date_created` datetime DEFAULT NULL,<br/>
  `message_count` tinyint(3) DEFAULT NULL,<br/>
  `message_index` varchar(255) DEFAULT NULL,<br/>
  `user_index` varchar(255) DEFAULT NULL,<br/>
  `send_from` tinyint(3) DEFAULT NULL,<br/>
  `site_id` tinyint(1) DEFAULT NULL,<br/>
  `status` tinyint(1) DEFAULT 1,<br/>
  PRIMARY KEY (id)<br/>
) $charset_collate;<br/><br/>
CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}mm_status` (<br/>
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,<br/>
  `conversation_id` int(11) DEFAULT NULL,<br/>
  `message_id` int(11) DEFAULT NULL,<br/>
  `user_id` int(11) DEFAULT NULL,<br/>
  `status` int(11) DEFAULT NULL,<br/>
  `date_created` datetime DEFAULT NULL,<br/>
  `type` tinyint(4) DEFAULT NULL,<br/>
  PRIMARY KEY (id)<br/>
) $charset_collate;";
                                        ?><br/><br/>
                                        <code>
                                            <?php echo $sql; ?>
                                        </code>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        $('.fix-table').click(function () {
            var that = $(this);
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: 'mm_create_table',
                    _wpnonce: '<?php echo wp_create_nonce('mm_create_table') ?>',
                    type: that.data('type')
                },
                beforeSend: function () {
                    that.attr('disabled', 'disabled').text('Fixing...');
                },
                success: function (data) {
                    if (data.status == "success") {
                        location.reload();
                    } else {
                        that.removeAttr('disabled').text('Fix');
                        $('.mmessage-container .alert').html(data.error).removeClass('hide');
                    }
                }
            })
        })
    })
</script>