<?php

class PeepSoAsgarosForumConfigSection extends PeepSoConfigSectionAbstract
{
	/************************************************************************
	 * Add config section
	 ************************************************************************/
	public function register_config_groups()
	{
		$this->context='left';
		$this->profiles();
		$this->go_pro();

		$this->context='right';
		$this->actions();
		$this->uninstall();
	}
	
	private function profiles()
	{
		// Modify profile url
        $this->args('descript', __('Change Asgaros Forum profile URL to PeepSo&prime;s. Has no effect if &Prime;<i>Always link to PeepSo profile</i>&Prime; is set to &Prime;<i>yes</i>&Prime;. ','qnso-ps-af'));
        $this->set_field('asgarosforum_modify_profile_url', __('Change profile URL', 'qnso-ps-af'), 'yesno_switch');

		$this->set_group('peepso_asgarosforum_profiles', __('Profile integration', 'qnso-ps-af'));
	}
	
	private function go_pro()
	{		
		$this->set_field('go_pro_message', sprintf('<strong>%s:</strong><ul><li>- %s</li><li>- %s</li><li>- %s</li><li>- %s</li><li>- %s</li></ul>', __('All Premium Features', 'qnso-ps-af'), __('Show forum stats of members inside their PeepSo profile', 'qnso-ps-af'), __('Notifications for mentions and reactions', 'qnso-ps-af'), __('Fully integrates subscription settings in your members profiles', 'qnso-ps-af'), __('Show the PeepSo toolbar in forum pages to make your forum and PeepSo one big community', 'qnso-ps-af'), __('Forum related links can be shown in the navigation of PeepSo', 'qnso-ps-af')), 'message');
		$this->set_field('go_pro_button', '<a target="_blank" style="text-decoration: none; font-weight: bold; color: #fff;" href="https://quenso.de/plugin/peepso-asgaros-forum-integration-pro/"><div style="margin: 0px 20px; text-transform: uppercase; display: inline-block; border: none; background-color: #d24842; padding: 15px 32px; text-align: center; font-size: 16px;"><i class="fas fa-thumbs-up"></i> Go PRO now!</div></a>', 'message');
		
		$this->set_group('peepso_asgarosforum_go_pro', __('Premium Features', 'qnso-ps-af'), __('Get the PRO version to use all features of this integration', 'qnso-ps-af'));
	}
	private function actions()
	{
		// New post notification
		$this->args('descript', __('Send notifications to the author and subscribers if someone replies to a topic.','qnso-ps-af'));
		$this->set_field('asgarosforum_topic_new_post_notification', __('New post notifications', 'qnso-ps-af'), 'yesno_switch');
		
		// New post in activity stream
		$this->args('descript', __('Create an activity if someone replies to a topic.','qnso-ps-af'));
		$this->set_field('asgarosforum_topic_new_post_to_stream', __('New post activity', 'qnso-ps-af'), 'yesno_switch');
		
		// New post in activity stream content
		$this->args('descript', __('Show content of the new post in activity.','qnso-ps-af'));
		$this->set_field('asgarosforum_topic_new_post_to_stream_content', __('New post activity shows content', 'qnso-ps-af'), 'yesno_switch');
		
		// New topic notification
		$this->args('descript', __('Send notifications to subscribers if someone creates a new topic.','qnso-ps-af'));
		$this->set_field('asgarosforum_new_topic_notification', __('New topic notifications', 'qnso-ps-af'), 'yesno_switch');
		
		// New topic in activity stream
		$this->args('descript', __('Create an activity if someone create a new topic.','qnso-ps-af'));
		$this->set_field('asgarosforum_new_topic_to_stream', __('New topic activity', 'qnso-ps-af'), 'yesno_switch');
		
		// New topic in activity stream content
		$this->args('descript', __('Show content of the first topic post in activity.','qnso-ps-af'));
		$this->set_field('asgarosforum_new_topic_to_stream_content', __('New topic activity shows content', 'qnso-ps-af'), 'yesno_switch');
		
		$this->set_group('peepso_asgarosforum_actions',	__('Activity stream and notifications', 'qnso-ps-af'));
	}
	
	private function uninstall()
	{
		// Enable PeepSo toolbar
		$this->args('descript', __('Clean up all Asgaros Forum activities, notifications and config settings in PeepSo on deactivation. </br><b><font color="red">Be careful!</b> All data will be deleted after deactivation!</font>','qnso-ps-af'));
        $this->set_field('asgarosforum_delete_on_deactivate', __('Clean up PeepSo', 'qnso-ps-af'), 'yesno_switch');
		
		$this->set_group('peepso_asgarosforum_uninstall',	__('Uninstall', 'qnso-ps-af'));
	}
}