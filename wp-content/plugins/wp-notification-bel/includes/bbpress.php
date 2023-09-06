<?php

defined('ABSPATH') || exit;

add_action('bbp_new_reply', 'wnbell_bbp_notify', 11, 5);
function wnbell_bbp_notify(
    $reply_id = 0,
    $topic_id = 0,
    $forum_id = 0,
    $anonymous_data = false,
    $reply_author = 0
) {
    $options = get_option('wnbell_options');
    $enable_replies = $options['enable_new_bbpress_reply'];
    $enable_engaged = $options['enable_bbpress_engaged'];
    if ((!isset($enable_replies) || $enable_replies == false)
        && (!isset($enable_engaged) || $enable_engaged == false)) {
        return;
    }
    // Bail if topic is not published
    if (!bbp_is_topic_published($topic_id)) {
        return false;
    }

    // Bail if reply is not published
    if (!bbp_is_reply_published($reply_id)) {
        return false;
    }

    // User Subscribers
    if (isset($enable_engaged) && $enable_engaged) {
        $user_ids = bbp_get_topic_engagements($topic_id);
    } else {
        $user_ids = bbp_get_subscribers($topic_id);
    }
    $user_ids = apply_filters('wnbell_bbpress_receivers', $user_ids, $topic_id);
    // wnbell_log_file(bbp_get_topic_subscribers($topic_id, true), 'subs 1');
    // wnbell_log_file(bbp_get_subscribers($topic_id), 'subs 2');
    if (empty($user_ids)) {
        return false;
    }
    // Strip tags from text and setup mail data
    $topic_title = strip_tags(bbp_get_topic_title($topic_id));
    $reply_url = bbp_get_reply_url($reply_id);
    // Poster name
    $reply_author_name = bbp_get_reply_author_display_name($reply_id);
    // Loop through users
    foreach ((array) $user_ids as $user_id) {
        // Don't send notifications to the person who made the post
        if (!empty($reply_author) && (int) $user_id === (int) $reply_author) {
            continue;
        }
        $user_meta = get_user_meta($user_id, 'wnbell_unseen_bbpress_replies', true);
        $new_user_meta = array('topic_title' => $topic_title, 'reply_url' => $reply_url, 'reply_author_name' => $reply_author_name, 'date' => bbp_get_reply_post_date($reply_id), 'reply_id' => $reply_id, 'time' => time()); //true for humanized date
        if (!$user_meta) {
            $user_meta = array();
        }
        array_unshift($user_meta, $new_user_meta);
        if (sizeof($user_meta) > 20) {
            $removed = array_pop($user_meta);
        }
        update_user_meta($user_id, 'wnbell_unseen_bbpress_replies', $user_meta);

        wnbell_update_user_count($user_id);
    }
    return true;

}
