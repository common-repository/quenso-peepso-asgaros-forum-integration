<?php
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');

class PeepSoAsgarosForumInstall extends PeepSoInstall
{

	/************************************************************************
	 * Set default settings
	 ************************************************************************/
	protected $default_config = array(
	);

	public function plugin_activation( $is_core = FALSE )
	{
		$settings = PeepSoConfigSettings::get_instance();
		$settings->set_option('asgarosforum_modify_profile_url', 0);
		$settings->set_option('asgarosforum_topic_new_post_notification', 1);
		$settings->set_option('asgarosforum_topic_new_post_to_stream', 1);
		$settings->set_option('asgarosforum_topic_new_post_to_stream_content', 0);
		$settings->set_option('asgarosforum_new_topic_notification', 1);
		$settings->set_option('asgarosforum_new_topic_to_stream', 1);
		$settings->set_option('asgarosforum_new_topic_to_stream_content', 0);
		$settings->set_option('asgarosforum_delete_on_deactivate', 0);

		parent::plugin_activation($is_core);

		return (TRUE);
	}
	
	/************************************************************************
	 * Set default email templates
	 ************************************************************************/
	public function get_email_contents()
	{
		$emails = array(
			'email_asgarosforum_topic_new_post' => __('Hello {userfirstname},

{fromfirstname} replied on your topic!

You can see this post here:
{permalink}

Thank you.', 'qnso-ps-af'),
			'email_asgarosforum_sub_topic_new_post' => __('Hello {userfirstname},

{fromfirstname} replied on a subscribed topic!

You can see this post here:
{permalink}

Thank you.', 'qnso-ps-af'));
		
		return ($emails);
	}
	
}