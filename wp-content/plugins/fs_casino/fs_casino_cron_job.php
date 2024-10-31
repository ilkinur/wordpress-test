<?php 

function fs_reminder_settings_init() {
    add_settings_section('fs_reminder_section', 'Reminder Settings', null, 'general');

    add_settings_field('fs_reminder_time', 'Email Send Time', 'fs_reminder_time_callback', 'general', 'fs_reminder_section');
    register_setting('general', 'fs_reminder_time');

    add_settings_field('fs_inactivity_days', 'Inactivity Period (Days)', 'fs_inactivity_days_callback', 'general', 'fs_reminder_section');
    register_setting('general', 'fs_inactivity_days');

    add_settings_field('fs_email_subject', 'Email Subject', 'fs_email_subject_callback', 'general', 'fs_reminder_section');
    register_setting('general', 'fs_email_subject');

    add_settings_field('fs_email_body', 'Email Body', 'fs_email_body_callback', 'general', 'fs_reminder_section');
    register_setting('general', 'fs_email_body');

    add_settings_field('fs_smtp_host', 'SMTP Host', 'fs_smtp_host_callback', 'general', 'fs_reminder_section');
    register_setting('general', 'fs_smtp_host');

    add_settings_field('fs_smtp_port', 'SMTP Port', 'fs_smtp_port_callback', 'general', 'fs_reminder_section');
    register_setting('general', 'fs_smtp_port');

    add_settings_field('fs_smtp_user', 'SMTP User', 'fs_smtp_user_callback', 'general', 'fs_reminder_section');
    register_setting('general', 'fs_smtp_user');

    add_settings_field('fs_smtp_pass', 'SMTP Password', 'fs_smtp_pass_callback', 'general', 'fs_reminder_section');
    register_setting('general', 'fs_smtp_pass');
}
add_action('admin_init', 'fs_reminder_settings_init');


function fs_reminder_time_callback() { echo '<input type="time" name="fs_reminder_time" value="' . esc_attr(get_option('fs_reminder_time')) . '">'; }
function fs_inactivity_days_callback() { echo '<input type="number" name="fs_inactivity_days" value="' . esc_attr(get_option('fs_inactivity_days', 1)) . '">'; }
function fs_email_subject_callback() { echo '<input type="text" name="fs_email_subject" value="' . esc_attr(get_option('fs_email_subject')) . '">'; }
function fs_email_body_callback() { echo '<textarea name="fs_email_body">' . esc_attr(get_option('fs_email_body')) . '</textarea>'; }
function fs_smtp_host_callback() { echo '<input type="text" name="fs_smtp_host" value="' . esc_attr(get_option('fs_smtp_host')) . '">'; }
function fs_smtp_port_callback() { echo '<input type="text" name="fs_smtp_port" value="' . esc_attr(get_option('fs_smtp_port')) . '">'; }
function fs_smtp_user_callback() { echo '<input type="text" name="fs_smtp_user" value="' . esc_attr(get_option('fs_smtp_user')) . '">'; }
function fs_smtp_pass_callback() { echo '<input type="password" name="fs_smtp_pass" value="' . esc_attr(get_option('fs_smtp_pass')) . '">'; }


function fs_schedule_reminder() {
    if (!wp_next_scheduled('fs_daily_reminder_event')) {
        wp_schedule_event(strtotime(get_option('fs_reminder_time', '14:00')), 'daily', 'fs_daily_reminder_event');
    }
}
add_action('wp', 'fs_schedule_reminder');

function fs_daily_reminder_event() {
    $inactivity_days = (int)get_option('fs_inactivity_days', 1);
    $subject_template = get_option('fs_email_subject', 'Come Back and Play, {user_name}!');
    $body_template = get_option('fs_email_body', 'Hi {user_name}, we miss you!');

    $users = get_users(array(
        'meta_key' => 'last_activity',
        'meta_value' => strtotime("-{$inactivity_days} days"),
        'meta_compare' => '<',
    ));

    foreach ($users as $user) {
        $user_name = $user->user_login;
        $user_full_name = $user->display_name;
        $user_email = $user->user_email;
        $user_balance = get_user_meta($user->ID, 'balance', true);

        $subject = str_replace(
            array('{user_name}', '{user_full_name}', '{user_email}', '{user_balance}'),
            array($user_name, $user_full_name, $user_email, $user_balance),
            $subject_template
        );

        $body = str_replace(
            array('{user_name}', '{user_full_name}', '{user_email}', '{user_balance}'),
            array($user_name, $user_full_name, $user_email, $user_balance),
            $body_template
        );

        wp_mail($user_email, $subject, $body);
    }
}
add_action('fs_daily_reminder_event', 'fs_daily_reminder_event');


add_filter('cron_schedules', 'fs_add_every_minute_schedule');
function fs_add_every_minute_schedule($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60, 
        'display'  => __('Every Minute')
    );
    return $schedules;
}