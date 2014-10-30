<?php if (count($models)): ?>
    <br/>
    <div class="row">
        <div class="col-md-5 no-padding">
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
        <div class="col-md-7 no-padding">
            <div id="mmessage-content" class="ps-container ps-active-x ps-active-y">
                <?php $this->render_inbox_message(reset($models)) ?>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
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
                    success: function (html) {
                        that.css('cursor', 'pointer');
                        $('.load-conv').removeClass('active');
                        that.addClass('active read');
                        $('#mmessage-content').html(html);

                        $('#mmessage-content').perfectScrollbar({
                            suppressScrollX: true
                        });
                    }
                })
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