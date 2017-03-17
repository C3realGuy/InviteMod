InviteMod for Wedge
-------------------

With this plugin, new users need an Invitekey to register.
Those Invitekeys can be generated from already registered Members.


Features:
---------
 - Permissions for infinite slots
 - User recieves on x posts an invitekey
 - Notificationsupport
 - Invite donators can get rewarded


Installation
------------

1. Drop the 'invitemod' directory in your plugins folder.
2. Activate plugin in acp
3. Set permissions
4. Configure plugin (optional)

Recalculate Inviteslots
----------------------

If you want to recalculate the inviteslots the users should have,
create a new php file in your wedge folder (eg fix_inviteslots.php).
Open the file and insert:
```
<?php
require_once('core/SSI.php');
loadPluginSource('CerealGuy:InviteMod', 'src/Subs-InviteMod');
recalculate_inviteslots();
```

If you have ssh access or something similar, you can run it from the console.
Otherwise open it up in your browser. Now it should have recalculated the inviteslots.
It also creates all invitemod entries for the users, so maybe its helpful if you have
many members which were already registered before you enabled this plugin.
If some members have to many inviteslots, you can change the last line to
`recalculate_invitekeys(true);` and it will set the inviteslots also if the user had
more inviteslots before. Otherwise not.

This is just some quick hack, maybe i will implement it better into wedge, but i dont
think that this is something which is often needed so i just did it the fast and hacky
way.

ToDo
----

- Better Permissions
- more admin options
