<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/*
Plugin Name: YYDevelopment - Comments Alert Notification
Plugin URI:  https://www.yydevelopment.com/yydevelopment-wordpress-plugins/
Description: Simple plugin that show notification alert on the website when you have pending comment that require approval
Version:     1.5.0
Author:      YYDevelopment
Author URI:  https://www.yydevelopment.com/
Domain Path: /languages/
Text Domain: yydevelopment-comments-notification
*/

// Adding lanagues support to the plugin
function enable_yycomments_languages() {
  load_plugin_textdomain( 'yydevelopment-comments-notification', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
} // function enable_yycomments_languages() {
	
add_action( 'plugins_loaded', 'enable_yycomments_languages' );

// ================================================
// ajax function that save the close timestamp on the database
// we are adding timestamp in the feuture to try and close this box
// and not show it for some time
// ================================================

function yydev_close_comments_box() {

	$strtotime_now = strtotime("NOW");

	$add_time = 0;
	$close_time = esc_attr($_POST['yy_comments_close_time']);

	if( $close_time === '10-minutes' ) { $add_time = 60*10; } 
	if( $close_time === '1-hour' ) { $add_time = 60*60; }
	if( $close_time === '24-hours' ) { $add_time = 60*60*24; }

	$strtotime_close = intval( $strtotime_now + $add_time );

	update_option('yydev_close_comments_box_time', $strtotime_close);

    die(); // we have to end ajax functions with die();

} // function yydev_close_comments_box() {

add_action( 'wp_ajax_yydev_close_comments_box', 'yydev_close_comments_box' ); // create ajax function we can call with javascript
add_action( 'wp_ajax_nopriv_yydev_close_comments_box', 'yydev_close_comments_box' ); // add access for users who are not logged in


// ================================================
// display the plugin we have create on the wordpress
// post blog and pages
// ================================================

// function that will output the code to the page
function output_comments_motification() {

	// ======================================================
	// Adding commment note to the site if there is a comment
	// that we need to approve
	// ======================================================

	// checking if the user loged in
	if( is_user_logged_in() ) {

		if( current_user_can('editor') || current_user_can('administrator') ) {
			
			$strtotime_now = strtotime("NOW");
			$close_strtotime = intval( get_option('yydev_close_comments_box_time') );

			// making sure we don't want to leave the box closed for some time
			if( $close_strtotime <= $strtotime_now ) {

				// checking if there are comments that need to be approved
	            global $wpdb;
				$comments_need_approval = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_approved = '0' ");
				$comments_amount = count($comments_need_approval);
				
				// if there are comments that need to be approved echo the message
				if($comments_amount > 0) {

					// getting the url to the comment page
					$get_admin_url = esc_url( admin_url() ) . "edit-comments.php?comment_status=moderated";

	                $output_code = "";


				    // --------------------------------------
				    // Getting the ajax url
				    // --------------------------------------

				    $site_url = esc_url( site_url() ); // getting the url of the site
				    $site_wordpress_ajax = $site_url . "/wp-admin/admin-ajax.php"; // the ajax file path, we need it for the ajax call

					// --------------------------------------
					// output the javascript code
					// --------------------------------------

	                $output_code .= "<script>";
							$output_code .= "jQuery(document).ready(function($) {";

								// open close options when clicking on the button
								$output_code .= "$('.approve-comment-message a.close-button').click(function() {";
										$output_code .= "$('.approve-comment-message ul.close-options').css('display', 'block');";
										$output_code .= "return false;";
								$output_code .= "});";

								// saving close time ajax in the database
								$output_code .= "$('.approve-comment-message ul.close-options li').click(function() {";
										
										$output_code .= "var yy_comments_close_time = $(this).attr('data-closeTime');";

										// making the ajax call
			                            $output_code .= "let data = {
			                                                                'action': 'yydev_close_comments_box',
			                                                                 yy_comments_close_time:yy_comments_close_time,
			                                                             };";

			                            $output_code .= "jQuery.ajax({ url: '" . $site_wordpress_ajax . "', type: 'POST', data: data,";
			                                $output_code .= "success: function (response) {";
			                                    // $output_code .= "alert(response);";
			                                $output_code .= "}"; //success: function (response) {
			                            $output_code .= "});";

										// hide the box from the page
										$output_code .= "$('.approve-comment-message').animate({ opacity: '0' }, 500, ";
										$output_code .= "function() { $('.approve-comment-message').css('display', 'none') } );";

										$output_code .= "return false;";
								$output_code .= "});";


							$output_code .= "});";
					$output_code .= "</script>";
					





	                $output_code .= "<style>";
						$output_code .= ".approve-comment-message {background: #565656;position: fixed;top: 300px;right: 50px;width: 210px !important; box-sizing: border-box; padding: 20px 15px 20px 15px !important;border: 2px solid #fff !important; z-index: 999999999999999999999999 !important; text-align: center !important; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; font-size: 16px; line-height: 22px;}";
						$output_code .= ".approve-comment-message-inside {position: relative;}";
						$output_code .= ".approve-comment-message img {margin: 0px auto !important; padding: 0px !important; text-align: center !important;}";
						$output_code .= ".approve-comment-message p {font-weight: bold; font-size:16px; color: #fff; margin: 10px 0px !important; padding: 0px !important; line-height: 23px; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;}";
						$output_code .= ".approve-comment-message a.comments-button {background: #e30010 !important; color: #fff !important; padding: 10px 10px 10px 10px !important; border: 2px solid #e8ff7e !important; margin: 10px 0px 0px 0px !important; display: inline-block !important; font-weight: bold !important;}";
						$output_code .= ".approve-comment-message a:hover {text-decoration: none;background: #800009;}";

						$output_code .= ".approve-comment-message a.close-button, .approve-comment-message a.close-button:hover {background: #ff0000;color: #fff;display: inline-block;padding: 6px 11px 6px 11px;border-radius: 50%;text-decoration: none;font-weight: bold;font-size: 16px;position: absolute; top: -32px;right: -32px;border: 2px solid #fff;}";
						$output_code .= ".approve-comment-message .close-button-wrap {position: absolute; top: 0px;}";
						$output_code .= ".approve-comment-message ul.close-options {background: #fff; width: 130px; padding: 0px; margin: 0px; display: none;}";
						$output_code .= ".approve-comment-message ul.close-options li {font-size: 13px; list-style: none; border-bottom: 1px solid gray; cursor: pointer; padding: 4px 0px; margin: 0px; direction: ltr;}";

	                $output_code .= "</style>";

	                $output_code .= "<div class='approve-comment-message'>";
						$output_code .= "<div class='approve-comment-message-inside'>";

							$output_code .= "<img src='" . plugin_dir_url( __FILE__ ) . 'images/comments-icon.png' . "' alt='' />";

							// if there is more than one comment
							if( $comments_amount > 1 ) {
								$output_code .= "<p>" . __('There are', 'yydevelopment-comments-notification') . " " . intval($comments_amount) . " " . __('comments pending that require your approval', 'yydevelopment-comments-notification') . "</p>";
							} // if( $comments_amount > 1 ) {

							// if there is only one comment
							if( $comments_amount == 1 ) {
								$output_code .= "<p>" . __('There is 1 comment pending that require your approval', 'yydevelopment-comments-notification') . "</p>";
							} // if( $comments_amount > 1 ) {

							
							$output_code .= "<a class='comments-button' href='" . esc_url($get_admin_url) . "'>" . __('View Comments', 'yydevelopment-comments-notification') . "</a>";
							
							$output_code .= "<div class='close-button-wrap'>";
								$output_code .= "<a class='close-button' href='#'>X</a>";

								$output_code .= "<ul class='close-options'>";
									$output_code .= "<li data-closeTime='once' >Close Once</li>";
									$output_code .= "<li data-closeTime='10-minutes'>10 Minutes Close</li>";
									$output_code .= "<li data-closeTime='1-hour'>1 Hour Close</li>";
									$output_code .= "<li data-closeTime='24-hours'>24 Hours Close</li>";
								$output_code .= "</ul>";
							$output_code .= "</div><!--close-button-wrap-->";

						$output_code .= "</div><!--approve-comment-message-inside-->";
	                $output_code .= "</div><!--approve-comment-message-->";
					echo $output_code;

				} // if($comments_amount > 0) {

			} // if( $close_strtotime <= $strtotime_now ) {

		} // if( current_user_can('editor') || current_user_can('administrator') ) {

	} // if( is_user_logged_in() ) {

} // function output_comments_motification() {


add_action( 'wp_footer', 'output_comments_motification' );

// ================================================
// Add donate page to the plugin menu info
// ================================================

add_filter( 'plugin_action_links', function($actions, $plugin_file) {

	static $plugin;

    if (!isset($plugin)) { $plugin = plugin_basename(__FILE__); }
    
	if ($plugin == $plugin_file) {

            $admin_page_url = esc_url( menu_page_url( 'yydevelopment-comments-notification', false ) );
            $donate = array('donate' => '<a target="_blank" href="https://www.yydevelopment.com/coffee-break/?plugin=yydevelopment-comments-notification">Donate</a>');
		
            $actions = array_merge($donate, $actions);
        
    } // if ($plugin == $plugin_file) {
		
    return $actions;

}, 10, 5 );

// ================================================
// including admin notices flie
// ================================================

if( is_admin() ) {
	include_once('notices.php');
} // if( is_admin() ) {