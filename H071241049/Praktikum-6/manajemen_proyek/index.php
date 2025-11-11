<?php
require_once "config.php";
check_login(); 
switch ($_SESSION["role"]) {
    case 'superadmin':
        header("location: admin.php");
        break;
    case 'project manager':
        header("location: manager.php");
        break;
    case 'team member':
        header("location: member.php");
        break;
    default:
        header("location: logout.php");
        break;
}
exit;
?>