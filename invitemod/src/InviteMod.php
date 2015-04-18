<?php

function imActionInvite(){
	//Main function ?action=invite
	global $settings, $context, $txt;
	global $invitekeys, $inviteinfo, $invitedusers, $pid;
	$invitekeys = array();
	$invitedusers = array();
	$inviteinfo = array("id" => 0,
			"active_keys" => 0,
			"invited_users" => 0,
			"available_slots" => 0);

	$pid = 'CerealGuy:InviteMod';

	loadPluginSource($pid, 'src/Subs-InviteMod');
	loadPluginLanguage($pid, 'lang/InviteMod');
	$context['page_title'] = strtr($txt['im_main_title'], array("{FORUM_NAME}" =>$context['forum_name']));
	add_linktree('Invite', '<URL>?action=invite');
	if(!empty($_GET['test'])){
		$context['invitekey'] = new invitekey($_GET['test']);
		$memberID = 2;
		$im = new im($context['invitekey']->invitekey['id_member']);
		//$im->del_invitekey($context['invitekey']->invitekey['id']);
		$im->create_invitedmember_entry($memberID);

	}
	if(!allowedTo("invitemodallowed")){
		fatal_lang_error('no_access');
	}elseif(isset($_GET['createinvitekey'])){
		imCreateInviteKey();
	}elseif(!empty($_GET['delinvitekey'])){
		imDelInviteKey($_GET['delinvitekey']);
	}else{
		$imuser = array();
		loadPluginTemplate($pid, 'src/InviteMod');

        	$im = new im(MID);
		$im->update_all();
		$inviteinfo = $im->inviteinfo;
		$invitekeys = $im->invitekeys;
		$invitedusers = $im->invitedusers;	
		wetem::load('invitemod');
	}

	
}

function imCreateInviteKey(){
	// Main Sub function ?action=invite&createinvitekey
	global $settings, $context, $txt, $pid;

	loadPluginTemplate($pid, 'src/InviteMod');
	$im = new im(MID);
		
	$im->update_availableslots();
	if($im->inviteinfo['available_slots'] > 0 or allowedto("invitemodinfiniteslots")){
		$key = $im->create_invitekey();
		if(!allowedto("invitemodinfiniteslots")){
			$im->addslot(-1);
		}
		$txt['help'] = $txt['im_pop_crinv_help'];
		$context['popup_contents'] = $txt['im_pop_crinv_contents']." <br><p style=\"color: green;\">{$key}</p>";

	}else{
		$txt['help'] = $txt['im_pop_crinv_help_error1'];
		$context['popup_contents'] = $txt['im_pop_crinv_contents_error1'];
	}

	$txt['close_window'] = $txt['im_pop_close'];
	$context['page_title'] = $txt['help'];
	loadPluginTemplate($pid, 'src/InviteMod');
	wetem::load('invitemod_popup');

}


function imDelInviteKey($keyid){
	// Main Sub function ?action=invite&delinvitekey
	global $settings, $context, $txt, $pid;
	$keyid = intval($keyid);
	$allowed = false;
	loadPluginTemplate($pid, 'src/InviteMod');
	
	$im = new im(MID);
	
	$im->update_invitekeys();
	foreach($im->invitekeys as $k){
		if($k['id'] == $keyid){
			$allowed = true;
			break;
		}
	}
	if($allowed == true){
		$im->del_invitekey($keyid);
		if(!allowedto("invitemodinfiniteslots")){
			$im->addslot();
		}
		$txt['help'] = $txt['im_pop_delinv_help'];
		$context['popup_contents'] = $txt['im_pop_delinv_contents'];
	}else{
		$txt['help'] = $txt['im_pop_delinv_help_error1'];
		$context['popup_contents'] = $txt['im_pop_delinv_contents_error1'];
	}
	$txt['close_window'] = $txt['im_pop_close'];
	$context['page_title'] = $txt['help'];
	loadPluginTemplate($pid, 'src/InviteMod');
	wetem::load('invitemod_popup');
}

function im_register_form_pre(){
	global $txt;
	//Add invitekey field on registration
	/*echo '<dt><strong><label for="we_autov_invitekey">Invitekey:</label></strong></dt><dd>
							<input name="invitekey" id="" size="30" tabindex="1" maxlength="32" value="" required="">
						</dd>';
	*/
	add_js('$(".windowbg2.wrc").first().find("fieldset").append("<dl class=\"register_form\" id=\"invitekey\"><dt><strong><label for=\"we_autov_invitekey\">Invitekey:</label></strong></dt><dd><input name=\"invitekey\" size=\"30\" tabindex=\"5\" ></dd></dl>");');

}

function im_register_validate(&$regOptions){
	//Validate registration key
	global $settings, $context, $txt;
	$invite_errors = array();
	if($regOptions['interface'] != 'guest'){
		// Of course we dont need a invitekey if an admin tries to create a new member
		return array(); 
	}
	
	loadPluginLanguage('CerealGuy:InviteMod', 'lang/InviteMod');
	loadPluginSource('CerealGuy:InviteMod', 'src/Subs-InviteMod');
	
	if(!isset($_POST['invitekey'])){
		$invite_errors[] = array('lang', 'im_reg_err_nokey');
	}else{ 
		$key = $_POST['invitekey'];
		$context['invitekey'] = new invitekey($key);
		if(!$context['invitekey']->valid()){
			$invite_errors[] = array('lang', 'im_reg_err_invalid');
		}
	}


	return $invite_errors;
}

function im_register_post(&$regOptions, &$theme_vars, &$memberID){
	//Registration was successful, now update user info
	global $settings, $context, $txt;
	if($regOptions['interface'] == 'guest'){ // Only if user is guest => "real" registration
		
		loadPluginSource('CerealGuy:InviteMod', 'src/Subs-InviteMod');
		$im = new im($context['invitekey']->invitekey['id_member']);
		$im->del_invitekey($context['invitekey']->invitekey['id']);
		$im->create_invitedmember_entry($memberID);
	
		//Send notification that user has successful registered
	
		Notification::issue('invitenewuser', $context['invitekey']->invitekey['id_member'], $context['invitekey']->invitekey['id'], array('invite' => array('invited_id' => $memberID)));
	}


}
function im_create_post_after(&$msgOptions, &$topicOptions, &$posterOptions, &$new_topic){
	//Check if user should be rewarded (or inviter)
	global $settings, $txt;
	loadPluginSource('CerealGuy:InviteMod', 'src/Subs-InviteMod');
	loadPluginLanguage('CerealGuy:InviteMod', 'lang/InviteMod-Notifier');
	$user_posts = we::$user['posts'];
	$user_href = "";
	$notify = array("notify" => false, "reason" => "", "object" => $msgOptions['id'], "member_id" => MID); // we use postid as "notify object id"
	//User reward
	if(in_multisetting($user_posts, $settings['invitemod_posts_recieve'])){
		$im = new im(MID);
		$im->addslot();
		$notify['reason'] = $txt['notifier_invite_reward_reason_posts'];
	}

	//Inviter reward
	if(in_multisetting($user_posts, $settings['invitemod_posts_reward_inviter'])){
		$inviter_id = invited_by(MID);
		if($inviter_id != 0){
			$im = new im($inviter_id);
			$im->addslot();
			$notify['member_id'] = $inviter_id;
			$user_href = "<a href=\"". SCRIPT ."?action=profile&id=".MID."\">".we::$user['username']."</a>";
			//['reason'] = "Der von dir eingeladene User {$user_href} hat {$user_posts} Posts geschrieben, daher erhälst du deinen Inviteslot zurück."
			$notify['reason'] = $txt['notifier_invite_reward_reason_inviter_posts'];
		}
	}
	if(!empty($notify['reason'])){
		$notify['reason'] = strtr($notify['reason'], array("{USER-HREF}" => $user_href, "{POSTS}" => $user_posts));
		Notification::issue('invitereward', $notify['member_id'], $notify['object'], array('invite' => array('reason' => $notify['reason'])));
	} 
}

function im_load_theme(){
	//Add link to invitemod on sidebar
	global $txt;
	loadPluginSource('CerealGuy:InviteMod', 'src/Subs-InviteMod');
	loadPluginLanguage('CerealGuy:InviteMod', 'lang/InviteMod');
	
	if(($keys = cache_get_data('im_keys_'.MID)) === null){
		$im = new im(MID);
		$im->update_availableslots();
		$keys = ($im->inviteinfo['available_slots'] == -1 ? "∞" : $im->inviteinfo['available_slots']);
		cache_put_data('im_keys_'.MID, $keys, 900);
	}
	$ps_string = strtr($txt['im_ps_invites'] , array("{A_KEYS}" => $keys));
	add_js('$( document ).ready(function() {var inv = "<li><a href=\"index.php?action=invite\">'.$ps_string.'</a></li>";
		if($("#noava").length){$("#noava").append(inv);}else{$( ".now" ).before( "<ul>"+inv+"<\/ul>" );}});');
	
}
function im_profile_areas(&$profile_areas){
	global $context, $txt;
	if(empty($_GET['area'])){
		loadPluginSource('CerealGuy:InviteMod', 'src/Subs-InviteMod');
		loadPluginLanguage('CerealGuy:InviteMod', 'lang/InviteMod');
		$inviter_id = invited_by($context['id_member']);
		$context['invited_href'] = ($inviter_id == 0 ? "<a>{$txt['im_nobody']}</a>" : "<a href=\"<URL>?action=profile;u=".$inviter_id."\">".id_to_username($inviter_id)."</a>");
		
	}
}

function im_notification_callback(array &$notifiers){
	loadPluginSource('CerealGuy:InviteMod', 'src/InviteMod-Notifier');
	
	$notifiers['invite_reward'] = new InviteReward_Notifier();
	$notifiers['invite_newuser'] = new InviteNewUser_Notifier();
}


