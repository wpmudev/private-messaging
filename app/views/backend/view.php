<?php $mes = new MM_Message_Model(); ?>
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
                            <?php
                            $messages = $model->get_messages();
                            $this->render_partial('shortcode/_inbox_message', array(
                                'messages' => $messages,
                                'render_reply' => false
                            ));
                            ?>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>