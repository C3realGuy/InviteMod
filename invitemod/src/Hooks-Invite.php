<?php

function im_menu_items(&$items) {
    loadPluginSource('CerealGuy:InviteMod', 'src/Subs-InviteMod');
	loadPluginLanguage('CerealGuy:InviteMod', 'lang/InviteMod');

	if(($keys = cache_get_data('im_keys_'.MID)) === null){
		$im = new im(MID);
		$im->update_availableslots();
		$keys = ($im->inviteinfo['available_slots'] == -1 ? "∞" : $im->inviteinfo['available_slots']);
		cache_put_data('im_keys_'.MID, $keys, 900);
	}

    // Add it to menu
    array_splice($items['profile']['items'], 4, 0, [
        'invite' => [
            'title' => 'Invite',
            'href' => '<URL>?action=invite',
            'show' => true,
            'notice' => $keys,
        ],
    ]);
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
