<?php
/**
 * Plugin Name: Like for bbPress
* Plugin URI:  https://www.bbp.design/
* Description: Like for bbPress
* Author:      https://www.bbp.design/
* Author URI:  https://www.bbp.design/
* Version:     1.1.9
* Text Domain: tomas-bbp-most-liked-topics
* Domain Path: /languages
* License: GPLv3 or later
*/
if (!defined('ABSPATH'))
{
	exit;
}

define ('LFB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define ('LFB_PLUGIN_URL', plugin_dir_url(__FILE__));

add_filter( 'bbp_topic_admin_links', 'lfb_like_button_topic', 10, 2);
add_filter( 'bbp_reply_admin_links', 'lfb_like_button_topic', 10, 2);


function lfb_like_button_topic($links, $id)
{
	$args = array();
	$r = bbp_parse_args( $args, array (
			'id'     => $id,
			'before' => '<span class="bbp-admin-links">',
			'after'  => '</span>',
			'sep'    => ' | ',
			'links'  => array()
	), 'get_topic_admin_links' );
	
	$like_post_link = array('like' => lfb_bl_bbp_most_liked_topic_like_link( $r ) );

	return array_merge($like_post_link,$links);	
}

function lfb_bl_bbp_most_liked_topic_like_link($args = '')
{
	$r = bbp_parse_args( $args, array(
			'id'           => 0,
			'link_before'  => '',
			'link_after'   => '',
			'edit_text'    => esc_html__( 'Likes', 'bbpress' )
	), 'get_topic_edit_link' );
	
	$liking_user = get_current_user_id();
	$actionurl     = add_query_arg( array( 'action' => 'bbp_like_this_topic', 'topic_id' => $r['id'],'user_id' => $liking_user ) );
	$actionurl = str_replace( '&amp;', '&', $actionurl );
	$action = 'liketopic_' . $r['id'];
	$bbp_most_liked_name = 'wpnonce';
	$bbp_most_like_created_nonce = wp_create_nonce( $action );
	$uri = esc_html( add_query_arg( $bbp_most_liked_name, $bbp_most_like_created_nonce, $actionurl ) );
	
	$bbp_most_like_topic_link_class = '';

	if (lfb_bl_bbp_liked_already_topic($r['id'],$liking_user))
	{
		$bbp_most_like_topic_link_class = 'bbp_liked_already_topic_link_class';
		$r['edit_text'] = 'Liked';
	}
	else 
	{
		$bbp_most_like_topic_link_class = 'bbp_unlike_topic_link_class';
		$r['edit_text'] = 'Like it';
	}
	

	$var_lfb_bl_bbp_liked_this_topic_count = lfb_bl_bbp_liked_this_topic_count($r['id']);
	if (empty($var_lfb_bl_bbp_liked_this_topic_count))
	{
		$var_lfb_bl_bbp_liked_this_topic_count = 0;
	}
	
	//$bbp_like_img_src_link = get_option('siteurl') . '/wp-content/plugins/bbp-like/asset/images/likeit.png';
	$bbp_like_img_src_link = LFB_PLUGIN_URL. '/asset/images/likeit.png';
	$bbp_like_img_url = "<img src = '$bbp_like_img_src_link' class='bbplikeimg' />";
	$bbp_like_img_url_section = '<span class="bbp_like_this_topic_image">'.$bbp_like_img_url.'</span>';
	
	//$bbp_like_it_text = 'Like it now';
	$bbp_like_it_text = 'Like it';
	$bbp_liked_it_text = 'You liked it';
	
	$bbp_most_liked_user_liked_already =  lfb_bl_bbp_liked_already_topic($r['id'],$liking_user);
	if ($bbp_most_liked_user_liked_already == false)
	{
		$bbp_current_user_liked_it_or_not = $bbp_like_it_text;
	}
	else 
	{
		$bbp_current_user_liked_it_or_not = $bbp_liked_it_text;
	}
	
	$bbp_like_it_text_section = '<span class="bbp_like_this_topic_text">'.$bbp_current_user_liked_it_or_not.'</span>';
	$bbp_like_it_section = $bbp_like_img_url_section.' '.$bbp_like_it_text_section;
	$bbp_like_it_section = '<span class="bbp_like_this_topic_section">'.$bbp_like_it_section.'</span>';
	
	$total_links_html = '<span class="var_lfb_bl_bbp_liked_this_topic_count_number">'.$var_lfb_bl_bbp_liked_this_topic_count.'</span>';
	$total_links_html .= '<span class="var_lfb_bl_bbp_liked_this_topic_count_text"> Likes</span>';
	$total_links_html .= '<span class="var_lfb_bl_bbp_liked_this_topic_count_split"> | </span>';
	$bbp_like_topic_class = 'bbp_like_topic_class_' .$r['id'] ;
	
	if ( empty( $uri ) )
	{
		return false; 
	}

	//!!! $retval = $r['link_before'] . '<a href="' . esc_url( $uri ) . '" class="bbp-most-like-link '.$bbp_like_topic_class.'" topicid="'.$r['id'].'" action="bbp_like_this_topic" userid="'.$liking_user.'" nonce="'.$bbp_most_like_created_nonce.'">' . $total_links_html. $bbp_like_it_section .  '</a>' . $r['link_after'];
	//!!! 1.1.5
	$retval = $r['link_before'] . '<a href="' . esc_url( $uri ) . '" class="bbp-most-like-link '.esc_attr($bbp_like_topic_class).'" topicid="'.esc_attr($r['id']).'" action="bbp_like_this_topic" userid="'.esc_attr($liking_user).'" nonce="'.esc_attr($bbp_most_like_created_nonce).'">' . $total_links_html. $bbp_like_it_section .  '</a>' . $r['link_after'];
	//end 1.1.5
	
	return apply_filters( 'lfb_bl_bbp_most_liked_topic_like_link', $retval, $r );	
}


function lfb_bl_bbp_liked_this_topic_count($topic_id)
{
	$bbp_liked_this_topic_current_number = get_post_meta($topic_id,'liked_this_topic_total',true);
	return $bbp_liked_this_topic_current_number;
}


function lfb_bl_bbp_liked_already_topic($id,$liking_user)
{
	$user_liked_topics_lists = get_user_meta($liking_user,'user_liked_topics_lists',true);
	if (empty($user_liked_topics_lists))
	{
		$user_liked_topics_lists = array();
	}
	
	if (in_array($id, $user_liked_topics_lists))
	{
		return true;
	}
	else
	{
		return false;
	}
	return false;
}

add_action('wp_footer','lfb_bl_bbp_most_liked_ajax_client');

function lfb_bl_bbp_most_liked_ajax_client()
{
?>	
	<script type="text/javascript">
	jQuery(document).ready(function()
	{
			    jQuery('.bbp-most-like-link').click(function(e)
			    {
			        e.preventDefault();
			        var most_liked_ajax = {};
			        most_liked_ajax.wpnonce= jQuery(this).attr("nonce");
			        most_liked_ajax.topic_id= jQuery(this).attr("topicid");
			        most_liked_ajax.user_id = jQuery(this).attr("userid");
			        most_liked_ajax.action= jQuery(this).attr("action");

					jQuery.ajax
					(
						{
							type: "post",
							url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
							dataType: "json",
							data: most_liked_ajax,
				
							success: function(ajax_result)
							{
								if (ajax_result.result == 'success')
								{
									var likednumber = ajax_result.total;
									var likedmessage = ajax_result.message;
									var likedreason = ajax_result.reason;
									
									var bbp_most_like_success_id_link_class = 'bbp_like_topic_class_' + most_liked_ajax.topic_id ;
									var bbp_most_like_success_id_text_class = 'bbp_like_this_topic_text';
									var bbp_most_like_success_total_number_class = 'var_lfb_bl_bbp_liked_this_topic_count_number';
									jQuery('.'+bbp_most_like_success_id_link_class+' .'+bbp_most_like_success_id_text_class).text('You liked it');
									jQuery('.'+bbp_most_like_success_id_link_class+' .'+bbp_most_like_success_total_number_class).text(likednumber);
								}
								else
								{
									var likednumber = ajax_result.total;
									var likedmessage = ajax_result.message;
									var likedreason = ajax_result.reason;
								}
							},
				            error: function (err) {
				            	window.console.log( err );
				            },
				            complete: function() {

				            }						
						}
					)
			        return false;
			    });

			});

	</script>
<?php 
}

function lfb_bl_bbp_most_liked_ajax_server()
{
	$likedresponse = array();
	$user_id = '';
	$topic_id = '';
	$nonce_state = '';
	$action = '';
	$bbp_liked_this_topic_current_number = '';
	
	if (!(isset($_POST['topic_id']))) 
	{
		return false;
	}
	else
	{
		$topic_id = sanitize_textarea_field($_POST['topic_id']);
	}
	
	
	if ((!(isset($_POST['user_id']))) || (sanitize_textarea_field($_POST['user_id']) == 0))
	{
		return false;
	}
	else
	{
		$user_id = sanitize_textarea_field($_POST['user_id']);
	}	
	
	$nonce = 'liketopic_' . $topic_id;
	if (!(isset($_POST['wpnonce'])))
	{
		return false;
	}
	else
	{		
		$nonce_state = wp_verify_nonce( $_POST['wpnonce'], $nonce );
		if ( false === $nonce_state )
		{
			return false;
		}		
	}
	
	if (!(isset($_POST['action'])))
	{
		return false;
	}
	else
	{	
		$action = sanitize_textarea_field($_POST['action']);
	}
	
	
	if ($action <> 'bbp_like_this_topic')
	{
		return false;
	}

	
	if ($action == 'bbp_like_this_topic')
	{
		$bbp_liked_this_topic_users_lists = get_post_meta($topic_id,'liked_this_topic_users_lists',true);
		
		$bbp_liked_this_topic_current_number = get_post_meta($topic_id,'liked_this_topic_total',true);
		if (empty($bbp_liked_this_topic_current_number))
		{
			$bbp_liked_this_topic_current_number = 0;
		}
		
		$bbp_liked_this_topic_new_total = $bbp_liked_this_topic_current_number + 1;
		
		if ((!(empty($bbp_liked_this_topic_users_lists))) && (is_array($bbp_liked_this_topic_users_lists)) && (count($bbp_liked_this_topic_users_lists) >0))
		{
			if (in_array($user_id, $bbp_liked_this_topic_users_lists))
			{
				$likedresponse['result'] = 'failed';
				$likedresponse['total'] = $bbp_liked_this_topic_current_number;
				$likedresponse['reason'] = 'liked already';
				$likedresponse['message'] = 'You have liked this topic already, one user can only like the topic one time';
				header('Content-type: application/json');
				echo json_encode($likedresponse);
				die();				
			}
		}
		else
		{
			$bbp_liked_this_topic_users_lists = array();
			$bbp_liked_this_topic_users_lists[] =  $user_id;
		}

		update_post_meta($topic_id, 'liked_this_topic_total', $bbp_liked_this_topic_new_total);
		$bbp_liked_this_topic_users_lists = get_post_meta($topic_id,'liked_this_topic_users_lists',true);
		//!!! 1.1.5
		if (empty($bbp_liked_this_topic_users_lists))
		{
		    $bbp_liked_this_topic_users_lists = array();
		}
		//end 1.1.5
		$bbp_liked_this_topic_users_lists[] = $user_id;
		update_post_meta($topic_id, 'liked_this_topic_users_lists', $bbp_liked_this_topic_users_lists);
		
		$user_liked_topics_lists = get_user_meta($user_id,'user_liked_topics_lists',true);
		if (empty($user_liked_topics_lists))
		{
			$user_liked_topics_lists = array();
		}
		$user_liked_topics_lists[] = $topic_id;
		update_user_meta($user_id, 'user_liked_topics_lists', $user_liked_topics_lists);
		
		$bbp_liked_this_topic_current_number = get_post_meta($topic_id,'liked_this_topic_total',true);
		
		if (empty($bbp_liked_this_topic_current_number))
		{
			$bbp_liked_this_topic_current_number = 0;
		}
		
		
		if ((isset($likedresponse['result'])) && (($likedresponse['result']) == 'failed'))
		{
			$likedresponse['total'] = $bbp_liked_this_topic_current_number;
		}
		else 
		{
			$likedresponse['total'] = $bbp_liked_this_topic_current_number;
			$likedresponse['reason'] = 'liked success';
			$likedresponse['message'] = 'Thanks for liked this topic, have a good day!';			
			$likedresponse['result'] = 'success';
		}
	}
	
	header('Content-type: application/json');
	echo json_encode($likedresponse);
	die();
}

add_action('wp_ajax_bbp_like_this_topic', 'lfb_bl_bbp_most_liked_ajax_server');
add_action('wp_ajax_nopriv_bbp_like_this_topic', 'lfb_bl_bbp_most_liked_ajax_server');

function lfb_bl_bbplostLikeCss()
{
	wp_enqueue_style('lfb_bl_bbplostLikeCss', plugin_dir_url( __FILE__ ) .'/asset/css/style.css');
}

add_action('bbp_init', 'lfb_bl_bbplostLikeCss');

