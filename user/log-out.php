<?php

require "../init.php";
include_once '../user/is-loged.php';
include_once "../model/user/userDB.php";

$userService = new UserDb();
$userService->revoke_refreshs_token($current_user);
$now = new DateTimeImmutable();
$refresh_expire_at = $now->modify('-1 year')->getTimestamp(); //L'utilisateur a cliquer sur "rester connecté"
setcookie("refresh", "", $refresh_expire_at, "/ZeFridgeWall", "localhost", false, true);

$output = array();
$output["data"]["status"] = "ok";
print_r (json_encode($output));
return http_response_code(200);


