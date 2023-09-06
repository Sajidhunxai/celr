<?php

defined( 'ABSPATH' ) || exit;
add_action(
    'comment_post',
    'wnbell_new_comment',
    10,
    2
);
add_action(
    'transition_comment_status',
    'wnbell_approve_comment_callback',
    10,
    3
);
function wnbell_new_comment( $comment_ID, $comment_approved )
{
    $enable_comments = get_option( 'wnbell_options' )['enable_new_comment'];
    if ( !isset( $enable_comments ) || $enable_comments == false ) {
        return;
    }
    // if (!is_user_logged_in()) {
    //     return;
    // }
    
    if ( 1 == $comment_approved ) {
        $new_comment = get_comment( $comment_ID, ARRAY_A );
        $parent_id = $new_comment['comment_parent'];
        $parent = get_comment( $parent_id, ARRAY_A );
        $parent_author = $parent['user_id'];
        $post = $new_comment['comment_post_ID'];
        $new_user_meta = array(
            'commenter'    => $new_comment['comment_author'],
            'post'         => $post,
            'date'         => $new_comment['comment_date'],
            'commenter_id' => $new_comment['user_id'],
            'comment_id'   => $comment_ID,
            'time'         => time(),
        );
        
        if ( $parent_author != 0 && $parent_author != $new_comment['user_id'] ) {
            wnbell_update_user_notification( $parent_author, 1, $new_user_meta );
            wnbell_update_user_count( $parent_author );
        }
        
        // if ($parent_author == $new_comment['user_id']) {
        //     return;
        // } //to check
        $author_id = get_post_field( 'post_author', $post );
        
        if ( $author_id != $new_comment['user_id'] ) {
            wnbell_update_user_notification( $author_id, 2, $new_user_meta );
            wnbell_update_user_count( $author_id );
        }
    
    }

}

function wnbell_approve_comment_callback( $new_status, $old_status, $comment )
{
    if ( $old_status != $new_status ) {
        
        if ( $new_status == 'approved' ) {
            $enable_comments = get_option( 'wnbell_options' )['enable_new_comment'];
            if ( !isset( $enable_comments ) || $enable_comments == false ) {
                return;
            }
            $new_comment = $comment;
            // if ($new_comment->comment_author == 0) {
            //     return;
            // }
            $parent_id = $new_comment->comment_parent;
            $parent = get_comment( $parent_id, ARRAY_A );
            $parent_author = $parent['user_id'];
            $post = $new_comment->comment_post_ID;
            $new_user_meta = array(
                'commenter'    => $new_comment->comment_author,
                'post'         => $post,
                'date'         => $new_comment->comment_date,
                'commenter_id' => $new_comment->user_id,
                'comment_id'   => $new_comment->comment_ID,
                'time'         => time(),
            );
            
            if ( $parent_author != 0 && $parent_author != $new_comment->user_id ) {
                wnbell_update_user_notification( $parent_author, 1, $new_user_meta );
                wnbell_update_user_count( $parent_author );
            }
            
            if ( $parent_author == 0 ) {
                //some function
            }
            $author_id = get_post_field( 'post_author', $post );
            
            if ( $author_id != $new_comment->user_id ) {
                wnbell_update_user_notification( $author_id, 2, $new_user_meta );
                wnbell_update_user_count( $author_id );
            }
        
        }
    
    }
}
