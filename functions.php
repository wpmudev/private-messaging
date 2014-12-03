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


if (!function_exists('mm_login_form')) {
    /**
     * @param array $args
     *
     * @return string
     * Getting from Worpdress, we can have custom design
     */
    function mm_login_form($args = array())
    {
        $defaults = array(
            'echo' => true,
            'redirect' => (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            // Default redirect is back to the current page
            'form_id' => 'loginform',
            'label_username' => __('Username'),
            'label_password' => __('Password'),
            'label_remember' => __('Remember Me'),
            'label_log_in' => __('Sign In'),
            'id_username' => 'user_login',
            'id_password' => 'user_pass',
            'id_remember' => 'rememberme',
            'id_submit' => 'wp-submit',
            'remember' => true,
            'value_username' => '',
            'value_remember' => false,
            // Set this to true to default the "Remember me" checkbox to checked
        );

        /**
         * Filter the default login form output arguments.
         *
         * @since 3.0.0
         *
         * @see wp_login_form()
         *
         * @param array $defaults An array of default login form arguments.
         */
        $args = wp_parse_args($args, apply_filters('login_form_defaults', $defaults));

        /**
         * Filter content to display at the top of the login form.
         *
         * The filter evaluates just following the opening form tag element.
         *
         * @since 3.0.0
         *
         * @param string $content Content to display. Default empty.
         * @param array $args Array of login form arguments.
         */
        $login_form_top = apply_filters('login_form_top', '', $args);

        /**
         * Filter content to display in the middle of the login form.
         *
         * The filter evaluates just following the location where the 'login-password'
         * field is displayed.
         *
         * @since 3.0.0
         *
         * @param string $content Content to display. Default empty.
         * @param array $args Array of login form arguments.
         */
        $login_form_middle = apply_filters('login_form_middle', '', $args);

        /**
         * Filter content to display at the bottom of the login form.
         *
         * The filter evaluates just preceding the closing form tag element.
         *
         * @since 3.0.0
         *
         * @param string $content Content to display. Default empty.
         * @param array $args Array of login form arguments.
         */
        $login_form_bottom = apply_filters('login_form_bottom', '', $args);

        $form = '
		<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . esc_url(site_url('wp-login.php', 'login_post')) . '" method="post">
			' . $login_form_top . '
			 <div class="form-group">
				<label for="' . esc_attr($args['id_username']) . '">' . esc_html($args['label_username']) . '</label>
				<input type="text" name="log" id="' . esc_attr($args['id_username']) . '" class="form-control" value="' . esc_attr($args['value_username']) . '" size="20" />
			</div>
			<div class="form-group">
				<label for="' . esc_attr($args['id_password']) . '">' . esc_html($args['label_password']) . '</label>
				<input type="password" name="pwd" id="' . esc_attr($args['id_password']) . '" class="form-control" value="" size="20" />
			</div>
			' . $login_form_middle . '
			' . ($args['remember'] ? '<p class="login-remember"><label><input name="rememberme" type="checkbox" id="' . esc_attr($args['id_remember']) . '" value="forever"' . ($args['value_remember'] ? ' checked="checked"' : '') . ' /> ' . esc_html($args['label_remember']) . '</label>
			<a class="pull-right" href="' . wp_lostpassword_url() . '">' . __("Forgot password?", mmg()->domain) . '</a></p>' : '') . '
			<p class="login-submit">
				<button type="submit" name="wp-submit" id="' . esc_attr($args['id_submit']) . '" class="btn btn-primary">' . esc_attr($args['label_log_in']) . '</button>
				<input type="hidden" name="redirect_to" value="' . esc_url($args['redirect']) . '" />
			</p>
			' . $login_form_bottom . '
		</form>';

        if ($args['echo']) {
            echo $form;
        } else {
            return $form;
        }
    }
}