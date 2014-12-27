<?php
function template_invitemod_popup()
{
	global $context, $txt;

	$title = isset($_POST['t']) ? $_POST['t'] : '';
	$script = isset($context['popup_js']) ? $context['popup_js'] : '';

	// Since this is a popup of its own we need to start the html, unless we're coming from jQuery.
	if (AJAX)
	{
		// By default, this is a help popup.
		echo '
	<header>', $title ? $title : $txt['help'], '</header>
	<section class="nodrag">
		', $context['popup_contents'], '
	</section>
	
	<footer><input type="button" class="delete" onclick="location.reload(); $(\'#popup\').fadeOut(function () { $(this).remove(); });" value="', $txt['close_window'], '" /></footer>'. $script;
	}
	else
	{
		echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
<head>
	<meta charset="utf-8">
	<meta name="robots" content="noindex">
	<title>', $context['page_title'], '</title>',
	theme_base_css(), '
</head>
<body id="helf">
	<header>', $title ? $title : $txt['help'], '</header>
	<section>
		', $context['popup_contents'], '
	</section>
	<footer><input type="button" class="delete" onclick="window.close();" value="', $txt['close_window'], '" /></footer>
</body>
</html>';
	}
}
function template_invitemod_message(){
	// Can be deleted?! Not sure...
	global $context;
	echo '<header class="cat">Invite</header>';
	if(!empty($context['success_msg'])){
		echo '<div class="windowbg" id="profile_success">
			'.$context['success_msg'].'</div>';
	}elseif(!empty($context['fail_msg'])){
		echo '<div class="errorbox">
				<h3>Der folgende Fehler ist aufgetreten:</h3>
				<ul class="error">
					'.$context['fail_msg'].'
				</ul></div>';
			
	}
	echo '<div class="right"><a href="<URL>?action=invite"><input type="submit" value="Zurück"></input></a></div>';

}
function template_invitemod(){

	global $invitekeys, $inviteinfo, $invitedusers, $txt, $settings;
	//$invitekeys = array(array("key" => "b9ce391e970ede27e9eb0c3f6ef3f274", "id" => 1, "time_create" => 'xxxx'),);
	//$inviteinfo = array("available_slots" => 1, "active_keys" => 2, "invited_user" => 0,);
	//$invitedusers = array(array("id" => 0, "username" => "Test", "posts" => 7),); 
	// Header
	$split_set_reward_inviter = (isset($settings['invitemod_posts_reward_inviter']) ? explode(";",$settings['invitemod_posts_reward_inviter']) : array());
	echo '<header class="cat">'.$txt['im_main_cat_value'].'</header>';
	// Show Info
	echo '<div class="windowbg2 wrc">
				<dl class="settings">
					<dt>
						<label ><span>'.$txt['im_main_inviteslots'].'</span></label>
					</dt>
					<dd>';
	if($inviteinfo['available_slots']==-1){echo $txt['im_infinite_symbol'].' ('.$txt['im_infinite'].')';}else{echo $inviteinfo['available_slots'];}
	echo '</dd>
					<dt>
						<label><a></a> <span>'.$txt['im_main_activekeys'].'<dfn>'.$txt['im_main_activekeys_desc'].'</dfn></span></label>
					</dt>
					<dd>'.$inviteinfo['active_keys'].'</dd>
					<dt>
						<label><a></a> <span>'.$txt['im_main_inviteduser'].'<dfn>'.$txt['im_main_inviteduser_desc'].'</dfn></span></label>
					</dt>
					<dd>'.$inviteinfo['invited_users'].'</dd>

				</dl>

			</div>';
	// List Invited Users
	echo '<br><header class="title">
		<div>'.$txt['im_main_list_inviteduser'].'</div>

	     </header>';
	if(empty($invitedusers)){
		echo '<table class="table_grid cs0" style="width: 100%">
				<thead>
					<tr class="catbg">
						<th><a href="" rel="nofollow">'.$txt['im_main_list_inviteduser_error1'].'</a></th>

					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>';
	}else{
		echo '<table class="table_grid cs0" style="width: 100%">
				<thead>
					<tr class="catbg">
						<th><a href="" rel="nofollow">'.$txt['im_main_list_inviteduser_username'].'</a></th>
						<th><a href="" rel="nofollow">'.$txt['im_main_list_inviteduser_posts'].'</a></th>
						<th><a href="" rel="nofollow">'.$txt['im_main_list_inviteduser_reward'].'</a></th>
						<th class="center"></th>
					</tr>
				</thead>
				<tbody>';

		foreach($invitedusers as $iu){
					$str_post = $iu['posts'];
					$next_reward = "-";
					foreach($split_set_reward_inviter as $i){
						if(intval($iu['posts']) < intval($i))
							$next_reward = $i;
							break;
							
					}
					echo '<tr class="windowbg" id="list_member_list_0">
						<td class="center"><a href="index.php?action=profile;u='.$iu['id'].'">'.$iu["username"].'</a></td>
						<td class="center">'.$str_post.'</td>
						<td class="center">'.$next_reward.'</td>
						<td></td>
					</tr>';	
		}
		echo '</tbody>
			</table>';
	}
	// List Invitekeys + options
	echo '<br><header class="title">
		<div>'.$txt['im_main_list_invitekeys'].'</div>

	     </header>';
	if(empty($invitekeys)){
		echo '<table id="table_invitekeys" class="table_grid cs0" style="width: 100%">
				<thead>
					<tr class="catbg">
						<th><a href="" rel="nofollow">'.$txt['im_main_list_invitekeys_error1'].'</a></th>

					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>';
	}else{
		echo '<table id="table_invitekeys"  class="table_grid cs0" style="width: 100%">
				<thead>
					<tr class="catbg">
						<th><a href="" rel="nofollow">'.$txt['im_main_list_invitekeys_invitekey'].'</a></th>
						<th class="center"></th>
					</tr>
				</thead>
				<tbody>';

		foreach($invitekeys as $i){
					echo '<tr class="windowbg" id="list_member_list_0">
						<td class="center">'.$i["key"].'</td>
						<td class="center"><button onclick="reqWin(\'index.php?action=invite&delinvitekey='.$i['id'].'\')">'.$txt['im_main_list_invitekeys_butdelete'].'</button></td>
					</tr>';	
		}
		echo '</tbody>
			</table>';
	}

	echo '<br><div class="right"><input type="submit" value="'.$txt['im_main_createinvitekey'].'" onclick="reqWin(\'index.php?action=invite&createinvitekey\')"></input></div>';


}
