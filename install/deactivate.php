<?php

/*
 * Performs deactivation process
 */
class PeepSoAsgarosForumUninstall
{
	/************************************************************************
	 * Called when plugin is deactivated
	 ************************************************************************/
	public static function plugin_deactivation()
	{
		// Only uninstall if option is enabled
		if (1 === intval(PeepSo::get_option('asgarosforum_delete_on_deactivate', 0))) {
			self::remove_activities();
			self::remove_notifications();

			// the is done last, in case above processes require any settings
			self::remove_settings();
		}
	}


	/************************************************************************
	 * Remove Asgaros Forum activities in PeepSo
	 ************************************************************************/
	private static function remove_activities()
	{
		global $wpdb;	
		
		$MODULE_ID = PeepSoAsgarosForum::MODULE_ID;
		
		$sql = "DELETE FROM `{$wpdb->prefix}peepso_activities` WHERE `act_MODULE_ID` = {$MODULE_ID} OR `act_comment_MODULE_ID` = {$MODULE_ID}";
		$wpdb->query($wpdb->prepare($sql));
	}
	
	/************************************************************************
	 * Remove Asgaros Forum notifications in PeepSo
	 ************************************************************************/
	private static function remove_notifications()
	{
		global $wpdb;	
		
		$MODULE_ID = PeepSoAsgarosForum::MODULE_ID;
		
		$sql = "DELETE FROM `{$wpdb->prefix}peepso_notifications` WHERE `not_MODULE_ID` = {$MODULE_ID}";
		$wpdb->query($wpdb->prepare($sql));
	}
	
	
	/************************************************************************
	 * Removes all configuration settings for Asgaros Forum in PeepSo
	 ************************************************************************/
	private static function remove_settings()
	{
		// remove settings
		$settings = PeepSoConfigSettings::get_instance();
		$options = array(
			'asgarosforum_modify_profile_url',
			'asgarosforum_topic_new_post_notification', 'asgarosforum_topic_new_post_to_stream', 'asgarosforum_topic_new_post_to_stream_content', 'asgarosforum_new_topic_notification', 'asgarosforum_new_topic_to_stream', 'asgarosforum_new_topic_to_stream_content',
			'asgarosforum_delete_on_deactivate'
		);
		
		$settings->remove_option($options);
		
		// Remove email templates
		$mail_options = array(
			// TODO: verify all of these config settings
			// TODO: check all add-ons and verify that they remove their settings if the add-on is deactivated but PeepSo is not.
			'email_asgarosforum_topic_new_post', 'email_asgarosforum_sub_topic_new_post'
		);

		foreach ($mail_options as $mail_option)
			delete_option('peepso_' . $mail_option);
	}

}

// EOF
