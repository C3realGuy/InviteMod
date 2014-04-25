InviteMod for Wedge
-------------------

If you enable this Plugin new users have to have an Invitekey to register. Already registered
Members can genereate those invitekeys. 


Features:
---------
 - Permissions for infinite slots
 - User recieves on x posts an invitekey
 - Notificationsupport


Installation
------------

Drop the 'invitemod' directory in your plugins folder.
Now you have to add the custom hooks. You need to do this after each upgrade/update!

In /core/app/Register.php
Search ``// Process any errors.``
and add BEFORE
 ``call_hook("register2_check", array($_POST, &$reg_errors));``

Search 
````
// If COPPA has been selected then things get complicated, setup the template.
````
 and add BEFORE
````
call_hook('register2_done', array($memberID));
````

Search 
````
loadTemplate('Register');
````
and add AFTER
````
call_hook('register_form_pre', array());
````



In /core/app/ManagePlugins.php
Search:
``'infraction_issue_content',``
and add AFTER:
````
			// Register
			'register_form_custom_field',
			'register2_check',
			'register2_done',
````

Activate plugin in acp

ToDo
----

- Better Permissions
- more admin options


