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

if (!function_exists('mm_in_the_loop_contact_button')) {
    function mm_in_the_loop_contact_button($class = '', $text = '', $subject = '', $ouput = true)
    {
        if (!in_the_loop()) {
            return;
        }

        //this is in the loop, we can get author
        $username = get_the_author();
        $user = null;
        if (!empty($username)) {
            $user = get_user_by('login', $username);
        }

        if (!$user instanceof WP_User) {
            return;
        }
        $shortcode = "[pm_user ";
        $shortcode .= sprintf('user_id="%s" ', $user->ID);
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