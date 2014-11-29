<?php if ($this->has_flash("mm_sent_" . get_current_user_id())): ?>
    <div class="row">
        <br/>

        <div class="col-md-12 no-padding">
            <div class="alert alert-success">
                <?php echo $this->get_flash("mm_sent_" . get_current_user_id()) ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
<?php endif; ?>

<?php if (count($models)): ?>
    <br/>
    <div class="row">
        <div class="col-md-5 col-sm-3 col-xs-3 no-padding">
            <div class="message-list">
                <form class="mm-search-form" method="get" action="<?php echo fURL::get() ?>">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control"
                               value="<?php echo fRequest::get('query', 'string', '') ?>" name="query"
                               placeholder="<?php _e("Search", mmg()->domain) ?>">
                        <button class="btn btn-link" type="submit">
                            <i class="fa fa-search"></i>
                        </button>

                        <div class="clearfix"></div>
                    </div>
                </form>
                <div class="ps-container ps-active-x ps-active-y" id="mmessage-list">
                    <ul class="list-group no-margin">
                        <?php foreach ($models as $key => $model): ?>
                            <?php $message = $model->get_last_message(); ?>
                            <li data-id="<?php echo mmg()->encrypt($model->id) ?>"
                                class="load-conv <?php echo $model->has_unread() == false ? 'read' : null ?> list-group-item <?php echo $key == 0 ? 'active' : null ?>">
                                <div class="row">
                                    <div class="col-md-3 no-padding">
                                        <img style="width: 90%" class="img-responsive img-circle center-block"
                                             src="<?php echo mmg()->get_avatar_url(get_avatar($message->send_from)) ?>">
                                    </div>
                                    <div class="col-md-9">
                                        <div>
                                            <strong class="small">
                                                <?php echo $message->get_name($message->send_from) ?>
                                            </strong>
                                            <label
                                                class="pull-right label label-primary"><?php echo date('j M', strtotime($message->date)) ?></label>
                                        </div>
                                        <div>
                                            <strong><?php
                                                $fmessage = $model->get_first_message();
                                                $subject = trim(strip_tags($fmessage->subject), "\n");

                                                echo mmg()->mb_word_wrap($subject, 50) ?></strong>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-12">
                                        <p class="text-muted"><?php
                                            $content = trim(strip_tags($message->content), "\n");
                                            echo mmg()->mb_word_wrap($content, 150) ?></p>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="clearfix"></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-7 col-xs-9 col-sm-9 no-padding">
            <div id="mmessage-content" class="ps-container ps-active-x ps-active-y">
                <?php echo $this->render_inbox_message(reset($models)) ?>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php if ($total_pages > 1): ?>
        <div class="row mm-paging">
            <div class="col-md-12 no-padding">
                <?php if ($paged <= 1): ?>
                    <a disabled href="#"
                       class="btn btn-default btn-sm pull-left"><?php _e("Previous", mmg()->domain) ?></a>
                <?php else: ?>
                    <a href="<?php echo add_query_arg('mpaged', $paged - 1) ?>"
                       class="btn btn-default btn-sm pull-left"><?php _e("Previous", mmg()->domain) ?></a>
                <?php endif; ?>
                <?php if ($paged >= $total_pages): ?>
                    <a disabled href="#"
                       class="btn btn-default btn-sm pull-right"><?php _e("Next", mmg()->domain) ?></a>
                <?php else: ?>
                    <a href="<?php echo add_query_arg('mpaged', $paged + 1) ?>"
                       class="btn btn-default btn-sm pull-right"><?php _e("Next", mmg()->domain) ?></a>
                <?php endif; ?>
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
        </div>
    <?php endif; ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.load-conv').click(function () {
                var that = $(this);
                $.ajax({
                    type: 'POST',
                    url: '<?php echo admin_url('admin-ajax.php') ?>',
                    data: {
                        action: 'mm_load_conversation',
                        id: $(this).data('id'),
                        _wpnonce: '<?php echo wp_create_nonce('mm_load_conversation') ?>'
                    },
                    beforeSend: function () {
                        that.css('cursor', 'wait');
                    },
                    success: function (data) {
                        that.css('cursor', 'pointer');
                        $('.load-conv').removeClass('active');
                        that.addClass('active read');
                        $('.mm-admin-bar span').text(data.count_unread);
                        $('.unread-count').attr('data-original-title', data.count_unread + ' ' + $('.unread-count').data('text'));
                        $('.read-count').attr('data-original-title', data.count_read + ' ' + $('.read-count').data('text'));
                        $('#mmessage-content').html(data.html);
                        $('#mmessage-content').perfectScrollbar('destroy');
                        $('#mmessage-content').perfectScrollbar({
                            suppressScrollX: true
                        });
                    }
                })
            });
            $('body').on('click', '.mm-status', function (e) {
                e.preventDefault();
                var that = $(this);
                var status = $(this).data('type');
                if (status == '<?php echo MM_Message_Status_Model::STATUS_DELETE ?>') {
                    if (confirm('<?php echo esc_js(__("Are you sure?",mmg()->domain)) ?>')) {
                        $.ajax({
                            type: 'POST',
                            url: '<?php echo admin_url('admin-ajax.php') ?>',
                            data: {
                                action: 'mm_status',
                                id: $(this).data('id'),
                                _wpnonce: '<?php echo wp_create_nonce('mm_status') ?>',
                                type: status
                            },
                            beforeSend: function () {
                                that.attr('disabled', 'disabled');
                            },
                            success: function () {
                                $('.load-conv.active').remove();
                                $('.load-conv').first().trigger('click');
                            }
                        })
                    }
                }else{
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo admin_url('admin-ajax.php') ?>',
                        data: {
                            action: 'mm_status',
                            id: $(this).data('id'),
                            _wpnonce: '<?php echo wp_create_nonce('mm_status') ?>',
                            type: status
                        },
                        beforeSend: function () {
                            that.attr('disabled', 'disabled');
                        },
                        success: function () {
                            $('.load-conv.active').remove();
                            $('.load-conv').first().trigger('click');
                        }
                    })
                }

            })
        })
    </script>
<?php else: ?>
    <br/>
    <div class="row">
        <div class="col-md-12 no-padding">
            <div class="well well-sm">
                <?php _e("No message found!", mmg()->domain) ?>
            </div>
        </div>
    </div>
<?php endif; ?>