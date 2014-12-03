<?php
$disabled = null;
if (!is_user_logged_in()) {
    $disabled = null;
} elseif (get_current_user_id() == $user->ID) {
    $disabled = 'disabled';
} ?>
<button type="button" id="<?php echo $this->button_id ?>" <?php echo $disabled ?> class="<?php echo $a['class'] ?>"><?php echo $a['text'] ?></button>