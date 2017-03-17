<?php

class InviteReward_Notifier extends Notifier
{
	/*
	 * This notifier should be used if a user gets a new inviteslot (if user wrotes x posts or one of his invited users)
	 * 
         * @param data['invite']['reason']    Describe why user gets this inviteslot (will be showed if clicked on notification (getPreview)
	
	 */
	public function __construct()
	{
		global $txt;
		loadPluginLanguage('CerealGuy:InviteMod', 'lang/InviteMod-Notifier');
	}

	public function getURL(Notification $notification)
	{
		// We will not redirect anywhere else
		return '<URL>?action=invite';
	}

	public function getText(Notification $notification, $is_email = false)
	{
		global $txt;

		$data = $notification->getData();
		$object = $notification->getObject();
		$object_url = $notification->getURL();

		return strtr(
			$txt['notifier_invite_reward' . ($is_email ? '_text' : '_html')],
			array()
		);
	}

	public function getEmail(Notification $notification)
	{
		global $txt;

		$name = $this->getName();
		return array($txt['notifier_invite_reward_subject'], $this->getText($notification, true));
	}


	public function getPreview(Notification $notification)
	{
		global $txt;
		$data = $notification->getData();
		// Perhaps we will display the amount of invites or something like that
		return strtr($txt['notifier_invite_reward_preview'], array("{REASON}" => $data['invite']['reason'],));
	}

	public function getProfile($id_member)
	{
		global $txt;

		$name = $this->getName();
		return array($txt['notifier_invite_reward_title'], $txt['notifier_invite_reward_desc'], array());
	}
}

class InviteNewUser_Notifier extends Notifier {
	/*
	 * This notifier should be used if a user has successful registered with an invitekey. The donator of the invitekey will recieve a notify about it.
	 * 
         * @param data['invite']['invited_id']    Id of member who has been invited
	
	 */
	public function __construct()
	{
		global $txt;
		loadPluginLanguage('CerealGuy:InviteMod', 'lang/InviteMod-Notifier');
	}

	public function getURL(Notification $notification)
	{
		// We will not redirect anywhere else
		$data = $notification->getData();
		return '<URL>?action=profile;u='.$data['invite']['invited_id'];
	}

	public function getUserURL($id)
	{
		global $memberContext;
		// We will not redirect anywhere else
		loadMemberData($id);
		loadMemberContext($id);
		$name = $memberContext[$id]['name'];
		return "<a href=\"".SCRIPT."?action=profile;u={$id}\">{$name}</a>";
	}
	public function getText(Notification $notification, $is_email = false)
	{
		global $txt;
		$data = $notification->getData();
		$object = $notification->getObject();
		$user_href = $this->getUserURL($data['invite']['invited_id']);
		return strtr(
			$txt['notifier_invite_newuser' . ($is_email ? '_text' : '_html')],
			array("{USER_HREF}" => $user_href)
		);
		

	}

	public function getRewardPosts()
	{
		// Return reward posts for inviter
		global $settings;
		return explode(";", $settings['invitemod_posts_reward_inviter'])[0];
	}

	public function getEmail(Notification $notification)
	{
		global $txt;

		return array($txt['notifier_invite_newuser_subject'], $this->getText($notification, true));
	}


	public function getPreview(Notification $notification)
	{
		global $txt;
		$data = $notification->getData();
		$reward_posts = $this->getRewardPosts();
		// Perhaps we will display the amount of invites or something like that
		$user_href = $this->getUserURL($data['invite']['invited_id']);
		return strtr($txt['notifier_invite_newuser_preview'], array("{USER_HREF}" => $user_href, "{REWARD_POSTS}" => $reward_posts));
	}

	public function getProfile($id_member)
	{
		global $txt;

		return array($txt['notifier_invite_newuser_title'], $txt['notifier_invite_reward_desc'], array());
	}

	public function getIcon(Notification $notification)
	{
		// We want that the Icon is the avatar from the new invited user. Normally this should be empty but who knows?!
		global $memberContext;
		$data = $notification->getData();
		$member = $data['invite']['invited_id'];

		if (empty($memberContext[$member]['avatar']))
			loadMemberAvatar($member, true);
		if (empty($memberContext[$member]['avatar']))
			return '';
		return $memberContext[$member]['avatar']['image'];
	}



}
