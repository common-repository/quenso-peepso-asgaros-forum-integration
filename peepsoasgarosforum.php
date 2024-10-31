<?php
/*
 * Plugin Name: PeepSo - Asgaros Forum Integration by Quenso
 * Plugin URI: https://quenso.de/plugin/peepso-asgaros-forum-integration-pro/
 * Description: Connect PeepSo with Asgaros Forum.
 * Author: Quenso
 * Author URI: https://quenso.de
 * Version: 1.1.2
 * Copyright: (c) 2020 Marcel Hellmund, All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: qnso-ps-af
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 */
if (!defined('ABSPATH')) exit;

class PeepSoAsgarosForumFree
{
	private static $_instance = NULL;
	
	// Define plugin data
	const PLUGIN_NAME				= 'PeepSo - Asgaros Forum Integration';
	const PLUGIN_VERSION			= '1.1.2';
	const PLUGIN_RELEASE			= ''; //ALPHA1, BETA1, RC1, '' for STABLE
	
	// Const for PeepSo compatibility
	const READY_VERSION				= '2.7.8';
	const READY_RELEASE				= ''; //ALPHA1, BETA1, RC1, '' for STABLE
	const MODULE_ID					= 10548;
	
	// Const for post meta
    const POST_META			= 'peepsoasgarosforum_type';
    const POST_META_POST		= 'peepsoasgarosforum_type_post';
	const POST_META_POST_URL	= 'peepsoasgarosforum_type_post_url';
    const POST_META_TOPIC		= 'peepsoasgarosforum_type_topic';
	const POST_META_PARENT_ID		= 'peepsoasgarosforum_parent_id';
	
	private function __construct()
	{
		// Admin
        if (is_admin()) {
            add_action('admin_init', array(&$this, 'check_required'));
			add_action('admin_init', array(&$this, 'pro'));
			
			// Register config tab in PeepSo config
			add_filter('peepso_admin_config_tabs', array(&$this, 'config_tab'));
        } else {
            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
        }
		
		if (self::ready()) {
			//Register hook for Activation / Deactivation
			register_activation_hook(__FILE__, array(&$this, 'activate'));
			register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));

			add_action('peepso_init', array(&$this, 'init'));
			add_action('plugins_loaded', array(&$this, 'load_textdomain'));
		}
	}

	/************************************************************************
	 * Load Translations
	 ************************************************************************/
	public function load_textdomain()
    {
        $path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
        load_plugin_textdomain('qnso-ps-af', FALSE, $path);
    }
	
	/************************************************************************
	 * Check for ready status
	 ************************************************************************/
    public static function ready()
	{
		if (class_exists('PeepSo') && self::READY_VERSION.self::READY_RELEASE < PeepSo::PLUGIN_VERSION.PeepSo::PLUGIN_RELEASE){
			add_action('admin_notices', function(){
				?>
				<div class="notice notice-warning qnso-ps-af">
					<strong>
						<?php echo sprintf(__('The version of %1$s plugin hasn&rsquo;t been testet with actual version of PeepSo and can cause errors. Please check for updates on %1$s.', 'qnso-ps-af'), self::PLUGIN_NAME, PeepSo::PLUGIN_VERSION.PeepSo::PLUGIN_RELEASE);?>
					</strong><br/><?php echo self::PLUGIN_NAME?><small style="opacity:0.5">(<?php echo sprintf(__('last testet: %s', 'qnso-ps-af'), self::READY_VERSION); ?>)</small>, <?php echo PeepSo::PLUGIN_NAME?><small style="opacity:0.5">(<?php echo PeepSo::PLUGIN_VERSION.PeepSo::PLUGIN_RELEASE ?>)</small>
				</div>
				<?php
            });
		}
		
		if (!class_exists('PeepSo') || !class_exists('AsgarosForum')) {
			return FALSE;
		}
		
		return TRUE;
    }
	
	/************************************************************************
	 * Make sure required plugins are activated
	 ************************************************************************/
	public function check_required ()
	{
		if (!class_exists('PeepSo') || !class_exists('AsgarosForum')){

			add_action('admin_notices', function(){
                ?>
                <div class="error peepso">
                    <strong>
                        <?php echo sprintf(__('The %s plugin requires the PeepSo and Asgaros Forum plugins to be installed and activated.', 'qnso-ps-af'), self::PLUGIN_NAME);?>
                    </strong>
                </div>
                <?php
            });

			unset($_GET['activate']);
			deactivate_plugins(plugin_basename(__FILE__));
			return FALSE;
		}
		
		return TRUE;
	}	

	public static function get_instance()
	{
		if (NULL === self::$_instance) {
			self::$_instance = new self();
		}
		return (self::$_instance);
	}

	public function pro ()
	{
		add_action( 'plugin_action_links_'. plugin_basename( __FILE__ ), function($settings) {
			$admin_anchor = sprintf ('<a target="_blank" href="https://quenso.de/plugin/peepso-asgaros-forum-integration-pro/" title="%s" style="font-weight:bold;color:darkred;">Go Pro</a>', __('Get PRO version of this plugin', 'qnso-ps-af'));
			if (! is_array( $settings  )) {
				return array( $admin_anchor );
			} else {
				return array_merge( array( $admin_anchor ), $settings) ;
			}
		});
	}

	public function init()
	{
		// Autoload classes
		$classes_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
		foreach (new DirectoryIterator(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes') as $file) {
			if ($file->isDot()) continue;
                
			require_once($classes_path . $file->getFilename());
		}
		
		// Hooks and filter: Notifications and activity stream
		add_action('asgarosforum_after_add_post_submit', array(&$this, 'topic_new_post'), 10, 6);
		add_action('asgarosforum_after_add_topic_submit', array(&$this, 'new_topic'), 10, 6);
		add_action('peepso_profile_notification_link', array(&$this, 'filter_profile_notification_link'), 10, 2);
		add_action('asgarosforum_after_delete_post', array(&$this, 'delete_post'), 10, 1);
		add_filter('peepso_activity_stream_action', array(&$this, 'activity_stream_action'), 10, 2);
		add_filter('peepso_profile_alerts', array(&$this, 'profile_alerts'), 10, 1);
		
		// Hooks and filter: Profile URL
		add_filter('asgarosforum_filter_profile_link', array(&$this, 'modify_profile_url'), 10, 2);
	}
	
	/************************************************************************
	 * Enqueue assets
	 ************************************************************************/
	public function enqueue_scripts()
	{
		wp_enqueue_style('peepsoasgarosforum', plugin_dir_url(__FILE__) . 'assets/css/style.css', array('peepso'), self::READY_VERSION, 'all');
		wp_enqueue_script('peepsoasgarosforum', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('peepso'), self::READY_VERSION, TRUE);
    }
	
	/************************************************************************
	 * Plugin Activation and Deactivation
	 ************************************************************************/
	public function activate()
	{
		if (!$this->ready()) {
            return (FALSE);
        }

        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'activate.php');
        $install = new PeepSoAsgarosForumInstall();
        $res = $install->plugin_activation();
        if (FALSE === $res) {
            // error during installation - disable
            deactivate_plugins(plugin_basename(__FILE__));
        }
		
        return (TRUE);
	}
	
	public function deactivate()
	{
		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'deactivate.php');
		PeepSoAsgarosForumUninstall::plugin_deactivation();
	}
	
	/************************************************************************
	 * Create admin configuration tab
	 ************************************************************************/
	public function config_tab ($tabs) 
	{
		$tabs['asgarosforum'] = array(
			'label' => 'Asgarsos Forum Integration',
			'icon' => plugin_dir_url(__FILE__) . 'assets/img/af-icon.png',
			'tab' => 'asgarosforum',
			'function' => 'PeepSoAsgarosForumConfigSection',
		);

		return $tabs;
	}
	
	/************************************************************************
	 * Send notification to topic author and subscribers for new post 
	 * Create activity item for new post
	 ************************************************************************/
	public function topic_new_post($post_id, $topic_id, $subject, $content, $link, $author_id)
    {
        global $asgarosforum;
		// Create notifications
		if(1 == PeepSo::get_option('asgarosforum_topic_new_post_notification',1)) {
			$PeepSoNotifications = new PeepSoNotifications();
			
			$topic_author_id = $asgarosforum->get_topic_starter($topic_id);
			
			// Send notification to topic author
			$title = sprintf(__('replied to your topic: %s', 'qnso-ps-af'), $subject);
			
			// Don't send notification if post auhor is topic author
			if (get_current_user_id() != $topic_author_id) {				
				$PeepSoNotifications->add_notification($author_id, $topic_author_id, $title, 'asgarosforum_topic_new_post', self::MODULE_ID, $post_id);
			}
			
			// Send notification to topic subscribers if subscriptions are enabled
			if ($asgarosforum->options['allow_subscriptions']) {

				global $wpdb;

				$sub_ids = $wpdb->get_col("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'asgarosforum_subscription_topic' AND meta_value = {$topic_id}");
				foreach ($sub_ids as $sub_id) {
					$title = sprintf(__('replied to a subscribed topic: %s', 'qnso-ps-af'), $subject);
					
					if (get_current_user_id() != $sub_id && $sub_id != $topic_author_id) {						
						$PeepSoNotifications->add_notification($author_id, $sub_id, $title, 'asgarosforum_sub_topic_new_post', self::MODULE_ID, $post_id);
					}
				}				
			}
		}

        // Create activity item
        if(1 == PeepSo::get_option('asgarosforum_topic_new_post_to_stream',1)) {
			$this->parent_id = $post_id;
			add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
			// Show new post content
			if(0 == PeepSo::get_option('asgarosforum_topic_new_post_to_stream_content',0)) {
				$content = '';
			}
			
			$extra = array(
                'module_id' => self::MODULE_ID,
                'act_access' => PeepSo::ACCESS_PUBLIC,
                'post_date_gmt' => date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ))
            );
			
			$peepso_activity = PeepSoActivity::get_instance();
            $act_id = $peepso_activity->add_post(get_current_user_id(), get_current_user_id(), $content, $extra);
			// Add post meta
            add_post_meta($act_id, self::POST_META, self::POST_META_POST, true);
			add_post_meta($act_id, self::POST_META_PARENT_ID, $post_id);
			add_post_meta($act_id, self::POST_META_POST_URL, $link);
			
			remove_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'));
		}
	}
	
	/************************************************************************
	 * Send notification to topic author and subscribers for new post 
	 * Create activity item for new topic
	 ************************************************************************/
	public function new_topic($post_id, $topic_id, $subject, $content, $link, $author_id)
    {
		$PeepSoNotifications = new PeepSoNotifications();
		
		// Create notifications
		if(1 == PeepSo::get_option('asgarosforum_new_topic_notification',1)) {
			// Send notification to topic subscribers
			global $wpdb, $asgarosforum;
			
			$topic = $asgarosforum->content->get_topic($topic_id);
			$forum = $asgarosforum->content->get_forum($topic->parent_id);			
			$sub_ids = $wpdb->get_col("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'asgarosforum_subscription_forum' AND meta_value = {$forum->id}");
			foreach ($sub_ids as $sub_id) {
				$title = sprintf(__('created a topic in a subscribed forum: %s', 'qnso-ps-af'), $subject);
				
				if (get_current_user_id() != $sub_id && $sub_id != $topic_author_id) {						
					$PeepSoNotifications->add_notification($author_id, $sub_id, $title, 'asgarosforum_sub_new_topic', self::MODULE_ID, $post_id);
				}
			}			
		}
		
		// Create activity item
        if(1 == PeepSo::get_option('asgarosforum_new_topic_to_stream',1)) {
			$this->parent_id = $post_id;
			add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
			
			// Show first post content
			if(0 == PeepSo::get_option('asgarosforum_new_topic_to_stream_content',0)) {
				$content = '';
			}
			
			$extra = array(
                'module_id' => self::MODULE_ID,
                'act_access' => PeepSo::ACCESS_PUBLIC,
                'post_date_gmt' => date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ))
            );
			
			$peepso_activity = PeepSoActivity::get_instance();
            $act_id = $peepso_activity->add_post(get_current_user_id(), get_current_user_id(), $content, $extra);
			// Add post meta
            add_post_meta($act_id, self::POST_META, self::POST_META_TOPIC, true);
			add_post_meta($act_id, self::POST_META_PARENT_ID, $post_id);
			add_post_meta($act_id, self::POST_META_POST_URL, $link);
			
			remove_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'));
		}
	}
	
	/************************************************************************
	 * Create activity item for approved topic
	 ************************************************************************/
	public function approved_topic ($topic_id)
	{
		global $asgarosforum;
		// Get topic data
		$topic = $asgarosforum->content->get_topic($topic_id);
		$post = $asgarosforum->content->get_first_post($topic_id);
		
		// Run new_topic function
		$this->new_topic($post->id, $topic_id, $topic->name, $post->text, $link, $post->author_id);
	}
	
	/************************************************************************
	 * Delete activities and notifications after delete post or topic
	 ************************************************************************/
	public function delete_post ($forum_post_id)
	{
		global $wpdb;
		// Delete post notification
		$wpdb->delete($wpdb->prefix . PeepSoNotifications::TABLE, array('not_external_id' => $forum_post_id));
		// Delete post activity
		$post_id = $wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '" . self::POST_META_PARENT_ID . "' AND meta_value = {$forum_post_id}");
		$wpdb->delete($wpdb->prefix . PeepSoActivity::TABLE_NAME, array('act_external_id' => $post_id));
		wp_delete_post($post_id, true);
	}
		
	
	/************************************************************************
     * Allow empty activity item content
     ************************************************************************/
    public function activity_allow_empty_content($allowed)
    {
        if(isset($this->parent_id)) {
            $allowed = TRUE;
        }

        return ($allowed);
    }
	
	/************************************************************************
	 * Change activity stream actions
	 ************************************************************************/
	public function activity_stream_action($action, $post)
    {
        if (self::MODULE_ID === intval($post->act_module_id)) {
            $post_type = get_post_meta($post->ID, self::POST_META, true);
			
			$forum_post_id = get_post_meta($post->ID, self::POST_META_PARENT_ID, true);
			$forum_post_link = get_post_meta($post->ID, self::POST_META_POST_URL, true);
			
			global $wpdb;
			
			$forum_topic_id = $wpdb->get_var("SELECT parent_id FROM {$wpdb->prefix}forum_posts WHERE id = {$forum_post_id}");
			$forum_topic_name = $wpdb->get_var("SELECT name FROM {$wpdb->prefix}forum_topics WHERE id = {$forum_topic_id}");
			// If type is new forum post
            if($post_type === self::POST_META_POST) {
                $action = sprintf(__('replied to a topic: <a href="%1$s">%2$s</a>', 'qnso-ps-af'), $forum_post_link, $forum_topic_name);
            }
			// If type is new topic
			else if($post_type === self::POST_META_TOPIC) {
				$action = sprintf(__('created a new topic: <a href="%1$s">%2$s</a>', 'qnso-ps-af'), $forum_post_link, $forum_topic_name);
            }
		// Check for reposted	
        } else if (FALSE === empty($post->act_repost_id)) {
            $peepso_activity = PeepSoActivity::get_instance();
            $repost_act = $peepso_activity->get_activity($post->act_repost_id);

            // fix "Trying to get property of non-object" errors
            if (is_object($repost_act) && self::MODULE_ID === intval($repost_act->act_module_id)) {
                $action = __('shared a forum activity', 'qnso-ps-af');
            }
        }
		
		return ($action);
    }
	
	/************************************************************************
	 * Add profile alerts
	 ************************************************************************/
	public function profile_alerts($alerts)
    {
		$items_topic_new_post = array(
			array(
				'label' => __('Someone replies to your topic', 'qnso-ps-af'),
				'setting' => 'asgarosforum_topic_new_post',
				'loading' => TRUE,
			),
			array(
				'label' => __('Someone replies to a subscribed topic', 'qnso-ps-af'),
				'setting' => 'asgarosforum_sub_topic_new_post',
				'loading' => TRUE,
			));
	
		$items_new_topic = array(
			array(
				'label' => __('Someone creates a topic in a subscribed forum', 'qnso-ps-af'),
				'setting' => 'asgarosforum_sub_new_topic',
				'loading' => TRUE,
			));
		
		if (0 == PeepSo::get_option('asgarosforum_topic_new_post_notification',1) && 0 == PeepSo::get_option('asgarosforum_new_topic_notification',1)){
			return ($alerts);
		} elseif (0 == PeepSo::get_option('asgarosforum_topic_new_post_notification',1)) {
			$alerts['asgarosforum'] = array(
                'title' => __('Forum', 'qnso-ps-af'),
                'items' => $items_new_topic,
			);
		} elseif (0 == PeepSo::get_option('asgarosforum_new_topic_notification',1)) {
			$alerts['asgarosforum'] = array(
                'title' => __('Forum', 'qnso-ps-af'),
                'items' => $items_topic_new_post,
			);
		} else {
			$alerts['asgarosforum'] = array(
                'title' => __('Forum', 'qnso-ps-af'),
                'items' => array_merge($items_topic_new_post, $items_new_topic),
			);
		}
		
        return ($alerts);
    }
	
	/************************************************************************
	 * Change notification links to forum post links
	 ************************************************************************/
	public function filter_profile_notification_link($link, $note_data)
    {
        $not_types = array(
            'asgarosforum_topic_new_post',
			'asgarosforum_sub_topic_new_post',
			'asgarosforum_sub_new_topic',
        );

		global $wpdb;
		
		$forum_post_id = $note_data['not_external_id'];
		$post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %d", self::POST_META_PARENT_ID, $forum_post_id));
		$not_type = $note_data['not_type'];

        if (in_array($not_type, $not_types)) {
            $link = get_post_meta($post_id, self::POST_META_POST_URL, true);
        }
        return $link;
    }
	
	/************************************************************************
	 * Change Asgaros Forum profile URL
	 ************************************************************************/
	public function modify_profile_url($link, $user) 
	{
		if(intval($user->ID) === 0) {
            return $link;
        }

        if(1 === intval(PeepSo::get_option('always_link_to_peepso_profile')) || 1 === intval(PeepSo::get_option('asgarosforum_modify_profile_url', 0))) {
            $user = PeepSoUser::get_instance($user->ID);
            if($user){
                $link = $user->get_profileurl();
            }
        }
        return $link;
    }
}

PeepSoAsgarosForumFree::get_instance();

// EOF