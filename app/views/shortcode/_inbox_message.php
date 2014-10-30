<?php
$message = array_shift($messages);
if (!isset($render_reply)) {
    $render_reply = true;
}
?>
<section class="message-content">
    <div class="message-content-actions pull-right">
        <?php if (fRequest::get('box') != 'sent' && $render_reply==true): ?>
            <?php
            $from_data = get_userdata($message->send_from);
            ?>
            <button
                data-username="<?php echo esc_attr($from_data->user_login) ?>"
                data-parentid="<?php echo esc_attr(mmg()->encrypt($message->conversation_id)) ?>"
                data-id="<?php echo esc_attr(mmg()->encrypt($message->id)) ?>" type="button"
                class="btn btn-info btn-sm mm-reply">
                <i class="fa fa-reply"></i>
            </button>
        <?php endif; ?>
        <!--<button type="button" class="btn btn-danger btn-sm">
            <i class="glyphicon glyphicon-trash"></i>
        </button>-->
    </div>
    <div class="clearfix"></div>
    <div class="page-header">
        <h3 class="mm-message-subject"><?php echo $message->subject ?></h3>

        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-3">
                        <img style="width: 100%;max-width: 100px" class="img-responsive img-circle center-block"
                             src="<?php echo mmg()->get_avatar_url(get_avatar($message->send_from)) ?>">
                    </div>
                    <div class="col-md-9">
                        <strong><?php
                            if ($message->send_from == get_current_user_id()) {
                                echo __("me", mmg()->domain) . ' (' . $message->get_name($message->send_from) . ')';
                            } else {
                                $message->get_name($message->send_from);
                            }?></strong>

                        <p><?php echo date('F j, Y, g:i a', strtotime($message->date)) ?></p>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="message-body">
        <?php echo mmg()->html_beautifier($message->content) ?>
    </div>
    <?php if (!empty($message->attachment)): ?>
        <?php $ids = explode(',', $message->attachment);
        $ids = array_filter($ids);
        if (count($ids)):?>
            <hr/>
            <div class="message-footer">
                <div class="row">
                    <?php foreach ($ids as $id): ?>
                        <?php $a_m = IG_Uploader_Model::find($id); ?>
                        <div class="col-md-6 message-attachment">
                            <a class="load-attachment-info" data-target="#<?php echo $id ?>" href="#">
                                <i class="fa fa-paperclip fa-2x pull-left"></i>
                                test.png </a>

                            <div class="clearfix"></div>
                            <!-- Modal -->
                            <div style="top:10%" class="modal fade" id="<?php echo $id ?>"
                                 tabindex="-1"
                                 role="dialog"
                                 aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title"><?php echo $a_m->name ?></h4>
                                        </div>
                                        <div class="modal-body sample-pop" style="max-height:450px;overflow-y:scroll">
                                            <?php
                                            $file = $a_m->file;

                                            $file_url = '';
                                            $show_image = false;

                                            if ($file) {
                                                $file_url = wp_get_attachment_url($file);
                                                $mime = explode('/', get_post_mime_type($file));
                                                if (array_shift($mime) == 'image') {
                                                    $show_image = true;
                                                }
                                            }
                                            if ($show_image) {
                                                echo '<img src="' . $file_url . '"/>';
                                            } elseif ($file) {
                                                //show meta
                                                ?>
                                                <ul class="list-group">
                                                    <li class="list-group-item upload-item">
                                                        <i class="glyphicon glyphicon-floppy-disk"></i>
                                                        <?php _e('Size', mmg()->domain) ?>:
                                                        <strong><?php
                                                            $f = new fFile(get_attached_file($file));
                                                            echo $f->getSize(true);
                                                            ?></strong>
                                                    </li>
                                                    <li class="list-group-item upload-item">
                                                        <i class="glyphicon glyphicon-file"></i>
                                                        <?php _e('Type', mmg()->domain) ?>:
                                                        <strong><?php echo ucwords(get_post_mime_type($file)) ?></strong>
                                                    </li>
                                                </ul>
                                            <?php
                                            } else {
                                                ?>
                                                <ul class="list-group">
                                                    <li class="list-group-item">
                                                        <i class="glyphicon glyphicon-link"></i>
                                                        <strong><?php _e('Link', mmg()->domain) ?></strong>:
                                                        <?php echo $a_m->url ?>
                                                    </li>
                                                    <div class="clearfix"></div>
                                                </ul>
                                            <?php
                                            }?>
                                        </div>
                                        <div class="modal-footer">
                                            <?php if ($a_m->url): ?>
                                                <a class="btn btn-info" rel="nofollow"
                                                   href="<?php echo esc_attr($a_m->url) ?>" target="_blank">
                                                    <?php _e("Visit Link", mmg()->domain) ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($a_m->file): ?>
                                                <a href="<?php echo $file_url ?>" download
                                                   class="btn btn-info"><?php _e('Download File', mmg()->domain) ?></a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="clearfix"></div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
<!--render history-->
<?php if (is_array($messages) && count($messages)): ?>
    <div class="well well-sm no-margin">
        <?php foreach ($messages as $key => $message): ?>
            <section class="message-content">

                <div class="page-header">
                    <h3 class="mm-message-subject"><?php echo $message->subject ?></h3>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <img style="max-width:100px;width: 100%"
                                         class="img-responsive img-circle center-block"
                                         src="<?php echo mmg()->get_avatar_url(get_avatar($message->send_from)) ?>">
                                </div>
                                <div class="col-md-9">
                                    <strong><?php echo $message->get_name($message->send_from) ?></strong>

                                    <p><?php echo date('F j, Y, g:i a', strtotime($message->date)) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="message-body">
                    <?php echo mmg()->html_beautifier($message->content) ?>
                </div>

            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php $this->render_partial('shortcode/_reply_form') ?>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('body').on('click', '.mm-reply', function () {
            var that = $(this);
            var name = that.data('username');
            var subject = that.closest('section').find('.mm-message-subject').first().text();
            var modal = $('#reply-form-c').find('form');
            modal.find('#mm_message_model-send_to').val(name);
            modal.find('#mm_message_model-subject').val('<?php echo esc_js(__('Reply: ',mmg()->domain)) ?>' + subject);
            modal.find('.mm_message_id').remove();
            modal.append('<input type="hidden" class="mm_message_id" name="id" value="' + that.data('id') + '">');
            modal.find('.mm_message_parent_id').remove();
            modal.append('<input type="hidden" class="mm_message_parent_id" name="parent_id" value="' + that.data('parentid') + '">');
            $('#reply-form-c').modal({
                keyboard: false
            })
        });
        $('body').on('click', '.load-attachment-info', function (e) {
            e.preventDefault();
            $($(this).data('target')).modal()
        })
    })
</script>
