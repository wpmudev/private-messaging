<div class="page-header">
    <h3><?php _e('Shortcodes') ?></h3>
</div>
<div class="row">
    <div class="col-md-6 col-xs-6 col-sm-6 text-center">
        <p><strong><?php _e("Inbox Page", mmg()->domain) ?></strong></p>
        <div class="clearfix"></div>

        <div class="text-left">
            <p><code>[message_inbox]</code></p>
            <ul>
                <li>
                    <?php _e("This shortcode will display the inbox page. No parameters for this", mmg()->domain) ?>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-md-6 col-xs-6 col-sm-6 text-center">
        <p><strong><?php _e("Message Me", mmg()->domain) ?></strong></p>
        <div class="clearfix"></div>

        <div class="text-left">
            <p><code>[message_me]</code></p>
            <ul>
                <li>
                    <mark><?php _e("user_id", mmg()->domain) ?></mark>
                    : <?php _e("This is the id of the user this message form will send email too", mmg()->domain) ?>
                </li>
                <li>
                    <mark><?php _e("text", mmg()->domain) ?></mark>
                    : <?php _e("The text display, default is \"Message me\"", mmg()->domain) ?>
                </li>
                <li>
                    <mark><?php _e("class", mmg()->domain) ?></mark>
                    : <?php _e("Class of the message me button", mmg()->domain) ?>
                </li>
                <li>
                    <mark><?php _e("subject", mmg()->domain) ?></mark>
                    : <?php _e("Subject of the email people send to this user via the button", mmg()->domain) ?>
                </li>

            </ul>
        </div>
    </div>
    <div class="clearfix"></div>
</div>