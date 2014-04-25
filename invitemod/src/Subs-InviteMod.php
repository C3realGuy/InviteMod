<?php

class im {
	public $invitekeys = array();
	public $invitedusers = array();
	public $inviteinfo = array("id" => 0,
			"active_keys" => 0,
			"invited_users" => 0,
			"available_slots" => 0);

			
	public function im($userid){
		$this->inviteinfo['id'] = $userid;


	}
	public function update_invitekeys(){
		$query = wesql::query("SELECT id, id_member, invite, time FROM {db_prefix}im_keys WHERE id_member = {int:id_member}",
					array("id_member" => $this->inviteinfo['id'],));
		$this->inviteinfo['active_keys'] = wesql::num_rows($query) -1;
		$this->invitekeys = array();
		if($this->inviteinfo['active_keys'] > 0){
			$tmp = wesql::fetch_assoc($query);
			while($r = wesql::fetch_assoc($query)){
				$tmparray = array();
				$tmparray['id'] = $r['id'];
				$tmparray['key'] = $r['invite'];
				$tmparray['time'] = $r['time'];
				$this->invitekeys[] = $tmparray;
			}
		}
						
	}

	public function update_availableslots(){
		//Update Availableslots and checks if user has an invite entry. If not it creates one.
 
		$query = wesql::query("SELECT id_member, slots_left 
			FROM {db_prefix}im_member 
			WHERE id_member = {int:id_user} LIMIT 1", array('id_user' => $this->inviteinfo['id']));
		if(wesql::num_rows($query) == 0){
			//User has no entry, therefore create one
			create_default_user_entry($this->inviteinfo['id']);
			$this->update_availableslots(); //call function again for correct info
			return; //stop
		}
		if($this->infiniteslots()){
			$this->inviteinfo['available_slots'] = -1;
		}else{
			$tmp = wesql::fetch_assoc($query);
			$this->inviteinfo['available_slots'] = $tmp['slots_left'];
		}
	}

	public function update_invitedusers(){
		$query = wesql::query("SELECT id_member, id_invited_from FROM {db_prefix}im_invited WHERE id_invited_from = {int:id_member}", array("id_member" => $this->inviteinfo['id'],));
		$this->inviteinfo['invited_users'] = wesql::num_rows($query);
		$this->invitedusers = array();
		if($this->inviteinfo['invited_users'] > 0){
			while($r = wesql::fetch_assoc($query)){
				$tmparray = array();
				$tmparray['id'] = $r['id_member'];
				$tmpuserinfo = get_user_info($tmparray['id']);
				$tmparray['username'] = $tmpuserinfo['member_name'];
				$tmparray['posts'] = $tmpuserinfo['posts'];
				$this->invitedusers[] = $tmparray;
			}
		}

	}

	public function update_all(){
		//Calls all update_* functions
		$this->update_availableslots();
		$this->update_invitedusers();
		$this->update_invitekeys();
	
	}

	public function create_invitekey(){
		$key = create_invitekey($this->inviteinfo['id']);
		wesql::query('INSERT INTO {db_prefix}im_keys (id, id_member, invite, time) VALUES (NULL, {int:id_member}, {string:invitekey}, UNIX_TIMESTAMP())', array("id_member" => $this->inviteinfo['id'], "invitekey" => $key));
		
		return $key;
	}

	public function del_invitekey($keyid){
		wesql::query('DELETE FROM {db_prefix}im_keys WHERE id = {int:keyid}', array('keyid' => $keyid));
			
	}

	public function infiniteslots(){
		return allowedTo('invitemodinfiniteslots');
	
	}

	public function addslot($add=+1){
		if($add<0){$add="".$add;}else{$add="+".$add;}
		wesql::query('UPDATE {db_prefix}im_member SET slots_left = slots_left '.$add.' WHERE id_member = {int:id_member}', array('id_member' => $this->inviteinfo['id'],));
		
	}
	public function create_invitedmember_entry($invitedid){
		wesql::query('INSERT INTO {db_prefix}im_invited (id_member, id_invited_from) VALUES ({int:id_member}, {int:id_invited_from})', array('id_member' => $invitedid, 'id_invited_from' => $this->inviteinfo['id']));

	}

	

}
class invitekey {
	public $valid = false;
	public $invitekey = array("id" => null,
				  "id_member" => null,
                                  "invite" => "null",
                                  "time" => 0,);
	public function invitekey($key){
		global $settings, $context, $txt;
		$this->invitekey['invite'] = $key;
		$query = wesql::query('SELECT id, id_member, invite, time FROM {db_prefix}im_keys WHERE invite = {string:key} LIMIT 1', array('key' => $key));
		if(wesql::num_rows($query) >= 1){
			$this->valid = true;
		}
		$this->invitekey = wesql::fetch_assoc($query);
	}
	public function valid(){
		if($this->valid == true){
			return true;
		}
		return false;
	}
}
function create_default_user_entry($userid, $defaultslots = 0){
	wesql::query('INSERT INTO {db_prefix}im_member
			(id_member, slots_left)
		VALUES ({int:id_member}, {int:defaultslots})',
		array('id_member' => $userid, 'defaultslots' => $defaultslots));


}

function get_user_info($userid){
	$query = wesql::query('SELECT id_member, member_name, posts FROM {db_prefix}members WHERE id_member = {int:id_member} LIMIT 1', array('id_member' => $userid));
	return wesql::fetch_assoc($query);

}

function create_invitekey($userid){
	$date = new DateTime();
	$str = $userid.$date->getTimestamp().rand(100, 999);	
	$invkey = md5($str);
	return $invkey;
}

function in_multisetting($in, $multisetting){
	/*
	  multisetting is just a string like "1;2;3;4" which defines multiple settings (1,2,3 & 4)
	  this func explodes the string and checks if $in is in it
	*/
	$multiarray = explode(";", $multisetting);
	//var_dump($multiarray);
	return in_array($in, $multiarray);
}

function id_to_username($userid){
	$query = wesql::query('SELECT member_name FROM {db_prefix}members WHERE id_member = {int:id_member} LIMIT 1',
		array('id_member' => $userid));
	if(wesql::num_rows($query) == 0){
		return "Niemand";
	}else{
		return wesql::fetch_assoc($query)['member_name'];
	}

}

function invited_by($userid){
	$query = wesql::query('SELECT id_invited_from FROM {db_prefix}im_invited WHERE id_member = {int:id_member} LIMIT 1',
		array('id_member' => $userid));
	if(wesql::num_rows($query) == 0){
		return 0;
	}else{
		return wesql::fetch_assoc($query)['id_invited_from'];
	}

}
