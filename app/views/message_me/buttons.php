<?php
$disabled = null;
if (!is_user_logged_in()) {
    $disabled = null;
} elseif (get_current_user_id() == $user->ID) {
    $disabled = 'disabled';
} ?>
<div class="ig-container">
    <div class="mmessage-container">
        <a href="#message-me-modal" type="button" data-target="#<?php echo $this->button_id ?>" <?php echo $disabled ?>
           class="<?php echo $a['class'] ?> message-me-btn"><?php echo $a['text'] ?>
        </a>

        <div id="<?php echo $this->button_id ?>" class="hide">
            <span class="subject"><?php echo $a['subject'] ?></span>
            <span class="send_to"><?php echo $user->user_login ?></span>
        </div>
    </div>
</div>