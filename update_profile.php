<?php
// This file duplicated edit_profile.php but used a mismatched DB schema
// (id/name/email/password instead of U_Id/F_name/Email/Tele) and root-level
// paths that don't match the /citizen/ folder structure — it would have
// thrown SQL errors if it ever ran. edit_profile.php is the correct,
// working version, so this now just forwards there to avoid a dead/broken page.
header("Location: edit_profile.php");
exit();
