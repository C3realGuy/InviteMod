InviteMod for Wedge
-------------------

With this plugin, new users need an Invitekey to register.
Those Invitekeys can be generated from already registered Members.


Features:
---------
 - Permissions for infinite slots
 - User recieves on x posts an invitekey
 - Notificationsupport


Installation
------------

1. Drop the 'invitemod' directory in your plugins folder.

2. Now you have to add the custom hooks. You need to do this after each upgrade/update!

   In */core/app/Register.php*<br> 
   Search 
````
   loadTemplate('Register');
````
   and add AFTER
````
   call_hook('register_form_pre', array());
````



   In */core/app/ManagePlugins.php*<br>
   Search:
   ``'register',``
   and add AFTER:
````
			'register_form_pre',
````

3. Activate plugin in acp
4. (optional) Configurate the plugin

ToDo
----

- Better Permissions
- more admin options

