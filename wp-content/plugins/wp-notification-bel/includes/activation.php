<?php

defined('ABSPATH') || exit;

//try
function wnbell_create_tables()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wnbell_recipients_role';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		notification_id bigint(20) NOT NULL,
		user_role varchar(255) NOT NULL,
		PRIMARY KEY  (id),
        key notification_id (notification_id)
	) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    //try next

    $table_name = $wpdb->prefix . 'wnbell_recipients';
    $sql_role = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        notification_id bigint(20) NOT NULL,
        usernames longtext NOT NULL,
        PRIMARY KEY  (id),
        key notification_id (notification_id)
    ) $charset_collate;";

    // //require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_role);
}
function wnbell_activate()
{
    $options = get_option('wnbell_options');
    if (!$options) {
        $options = array();
        $options['wnbell_name'] = array();
        $options['wnbell_name'][0] = 'Notification Content';
        $options['wnbell_default_value'] = array();
        $options['wnbell_default_value'][0] = 'Default notification';
        update_option('wnbell_options', $options);
    }
}
function wnbell_install()
{
    global $wnbell_db_version;
    $installed_ver = get_option("wnbell_db_version");

    if ($installed_ver != $wnbell_db_version) {
        wnbell_create_tables();

        update_option('wnbell_db_version', $wnbell_db_version);
    }
    wnbell_activate();
}
function wnbell_update_version()
{
    global $wnbell_db_version;
    $installed_ver = get_option("wnbell_db_version");
    if (!$installed_ver) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wnbell_recipients_role';

        $sql_check = "SELECT * FROM $table_name LIMIT 2";
        $check_results = $wpdb->get_results($sql_check, ARRAY_A);
        if (count($check_results) == 0) {
            $postmeta_table = $wpdb->prefix . 'postmeta';
            $sql = "INSERT INTO $table_name (notification_id, user_role)
   select * from (select distinct post_id,
       max(case when meta_key = 'wnbell_recipient_role' then meta_value end) as recipient_role
FROM $postmeta_table
group by post_id) as sbq
where recipient_role is NOT NULL;";
            $rows = $wpdb->query($sql);
        }

    }
    if (!$installed_ver || $installed_ver < 3) {
        wnbell_create_tables();
        global $wpdb;
        $table_recipients = $wpdb->prefix . 'wnbell_recipients';
        $postmeta_table = $wpdb->prefix . 'postmeta';
        $sql_check = "SELECT * FROM $table_name LIMIT 2";
        $check_results = $wpdb->get_results($sql_check, ARRAY_A);
        if (count($check_results) == 0) {
            $table_name = $wpdb->prefix . 'posts';
            $postmeta_table = $wpdb->prefix . 'postmeta';
            $sql_unm = "SELECT posts.ID as ID, pname.meta_value as usernames FROM $table_name as posts
             INNER JOIN $postmeta_table AS pname ON (posts.ID = pname.post_id
             AND CASE
        WHEN pname.meta_key='wnbell_recipient_username' AND pname.meta_value NOT LIKE '%\"0\"%'
        THEN 1 ELSE 0 END)
        WHERE posts.post_type = 'wnbell_notifications'
        GROUP BY posts.ID";
            $unm_results = $wpdb->get_results($sql_unm, ARRAY_A);
            if (count($unm_results) > 0) {
                foreach ($unm_results as $post) {
                    $post_id = $post['ID'];
                    $usernames = $post['usernames'];
                    // $usernames = unserialize($post['usernames']);
                    // foreach ($usernames as $username) {
                    //     $results = $wpdb->get_results(
                    //         $wpdb->prepare("SELECT id FROM $table_recipients WHERE notification_id=%d and username LIKE %s", $post_id, $username)
                    //     );
                    //     if (count($results) == 0) {
                    //         $wpdb->insert($table_recipients, array(
                    //             'notification_id' => $post_id,
                    //             'username' => $username,
                    //         ));
                    //     }
                    // }

                    $results = $wpdb->get_results(
                        $wpdb->prepare("SELECT id FROM $table_recipients WHERE notification_id=%d and usernames LIKE %s", $post_id, $usernames)
                    );
                    if (count($results) == 0) {
                        $wpdb->insert($table_recipients, array(
                            'notification_id' => $post_id,
                            'usernames' => $usernames,
                        ));
                    }

                }
            }
        }
    }
    update_option('wnbell_db_version', $wnbell_db_version);
}
function wnbell_update_db_check()
{
    global $wnbell_db_version;
    if (get_site_option('wnbell_db_version') != $wnbell_db_version) {
        wnbell_update_version();
    }
}
