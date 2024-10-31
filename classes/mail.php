<?php

class PeepSoAsgarosForumMail
{
	private static $_instance = NULL;

	/************************************************************************
	 * return singleton instance
	 ************************************************************************/
	public static function get_instance()
	{
		if (self::$_instance === NULL)
			self::$_instance = new self();
		return (self::$_instance);
	}

	/************************************************************************
	 * Constructor as private since class is singleton
	 ************************************************************************/
	private function __construct()
	{
		add_filter('peepso_config_email_messages', array(&$this, 'config_email'));
		add_filter('peepso_config_email_messages_defaults', array(&$this, 'config_email_messages_defaults'));
	}

	/************************************************************************
     * Add the new post emails to the list of editable emails on the config page
     ************************************************************************/
    public static function config_email($emails)
    {

    	if(is_array($emails)) {
    		$asgarosforumemails = array(
	    		'email_asgarosforum_topic_new_post' => array(
		            'title' => __('New forum post in topic', 'qnso-ps-af'),
		            'description' => __('This will be sent when a user replied to another user\'s forum topic.', 'qnso-ps-af')),
		        'email_asgarosforum_sub_topic_new_post' => array(
		        	'title' => __('New forum post in subscribed topic', 'qnso-ps-af'),
					'description' => __('This will be sent when a user replied to another user\'s subscribed forum topic.', 'qnso-ps-af'))
			);

			$emails = array_merge($emails, $asgarosforumemails);
		}
        return ($emails);
    }

    public function config_email_messages_defaults( $emails )
    {
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../install' . DIRECTORY_SEPARATOR . 'activate.php');
        $install = new PeepSoAsgarosForumInstall();
        $defaults = $install->get_email_contents();

        return array_merge($emails, $defaults);
    } 
}

// EOF
