<?php
/**
 * @author:Hoang Ngo
 */

if (!function_exists('mm_display_contact_button')) {
    function mm_display_contact_button($user_id_or_login = '', $class = '', $text = '', $subject = '', $ouput = true)
    {
        $shortcode = "[pm_user ";
        if (!empty($user_id_or_login)) {
            if (filter_var($user_id_or_login, FILTER_VALIDATE_INT)) {
                $shortcode .= sprintf('user_id="%s" ', $user_id_or_login);
            } else {
                $shortcode .= sprintf('name="%s" ', $user_id_or_login);
            }
        }

        if (!empty($class)) {
            $shortcode .= sprintf('class="%s" ', $class);
        }

        if (!empty($text)) {
            $shortcode .= sprintf('text="%s" ', $text);
        }

        if (!empty($subject)) {
            $shortcode .= sprintf('subject="%s" ', $subject);
        }

        $shortcode .= "]";
        if ($ouput) {
            echo do_shortcode($shortcode);
        } else {
            return do_shortcode($shortcode);
        }
    }
}