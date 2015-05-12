<?php $mes = new MM_Message_Model(); ?>
<?php
$messages = $model->get_messages();
?>
<div class="wrap">
    <div class="ig-container">
        <div class="mmessage-container">
            <div class="page-header">
                <h2><?php _e("Message #" . $model->id, mmg()->domain) ?></h2>
            </div>
            <div class="row">
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <table class="table table-striped table-condensed">
                                <thead>
                                <tr>
                                    <th style="width: 10%"><?php _e("Sender", mmg()->domain) ?></th>
                                    <th style="width: 20%"><?php _e("Date", mmg()->domain) ?></th>
                                    <th style="width: 60%"><?php _e("Content", mmg()->domain) ?></th>
                                    <th style="width: 10%"><?php _e("", mmg()->domain) ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($messages as $message): ?>
                                    <tr>
                                        <td><?php echo $message->get_name($message->send_from) ?></td>
                                        <td><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($message->date)); ?></td>
                                        <td><?php echo wpautop($message->content) ?></td>
                                        <td>
                                            <a href="#message-<?php echo $message->id ?>"
                                               class="button button-small leanmodal-trigger"><i class="fa fa-edit"></i>
                                            </a>
                                            &nbsp;
                                            <button type="button" class="button button-small"><i
                                                    class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <?php foreach ($messages as $message): ?>
                <div class="modal" id="message-<?php echo $message->id ?>">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post" class="message-save-form" data-id="<?php echo $message->id ?>">
                                <div class="modal-header">
                                    <h4 class="modal-title"><?php _e("Edit Message", mmg()->domain) ?></h4>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?php echo $message->id ?>">

                                    <div class="form-group">
                                        <label class="label-control">
                                            <?php _e("Subject", mmg()->domain) ?>
                                        </label>
                                        <input type="text" name="subject" class="form-control"
                                               value="<?php echo $message->subject ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="label-control">
                                            <?php _e("Content", mmg()->domain) ?>
                                        </label>
                                        <?php wp_editor(stripslashes($message->content), 'message-content-' . $message->id) ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default compose-close"
                                                data-dismiss="modal"><?php _e("Close", mmg()->domain) ?></button>
                                        <button type="submit"
                                                class="btn btn-primary"><?php _e("Save Changes", mmg()->domain) ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $(".leanmodal-trigger").leanModal({
            closeButton: ".compose-close",
            top: '5%',
            width: '90%',
            maxWidth: 659
        });
        $('.message-save-form').submit(function () {
            var that = $(this);
            $.ajax({
                type: 'POST',
                data: {
                    action: 'mmg_message_edit',
                    data: that.serializeAssoc()
                },
                url: ajaxurl,
                beforeSend: function () {

                },
                success: function () {
                
                }
            })
            return false;
        })
    })
</script>