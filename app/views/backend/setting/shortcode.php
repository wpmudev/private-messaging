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
                    <?php _e("This shortcode will display the private message interface.There are no parameters for this shortcode.", mmg()->domain) ?>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-md-6 col-xs-6 col-sm-6 text-center">
        <p><strong><?php _e("PM User", mmg()->domain) ?></strong></p>

        <div class="clearfix"></div>

        <div class="text-left">
            <p><code>[pm_user]</code></p>
            <ul>
                <li>
                    <mark><?php _e("user_id", mmg()->domain) ?></mark>
                    : <?php _e("This is the id of the user this message form will send email to", mmg()->domain) ?>
                </li>
                <li>
                    <mark><?php _e("user_name", mmg()->domain) ?></mark>
                    : <?php _e("This is the user name of the user this message form will send email to, less priority than user_id", mmg()->domain) ?>
                </li>
                <li>
                    <mark><?php _e("in_the_loop", mmg()->domain) ?></mark>
                    : <?php _e("Only accept 1|0, if both user_id and user_name is empty, and if this shortcode in a loop, it's will get the author user_id", mmg()->domain) ?>
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
                <li class="text-info">
                    <?php _e("Please note that, <strong>user_id</strong> or <strong>user_name</strong> or <strong>in_the_loop</strong> must be defined.") ?>
                </li>
            </ul>

        </div>
    </div>
    <div class="clearfix"></div>
</div>