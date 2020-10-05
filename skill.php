<?php

$JSONRequest = file_get_contents("php://input");
$request = json_decode($JSONRequest, TRUE);
if(empty($request) || (!isset($request))){
    http_response_code(400);
    exit;
}

$guid = 'b03c80e1-48ec-43f3-abe9-dbfb685b57a7';
$userId = $request["context"]["System"]["user"]["userId"];
$useridShort = str_replace("amzn1.ask.account.", "", $userId);

require "valid_request.php";
$valid = validate_request($guid, $useridShort);
if (!$valid['success'] )  {
    error_log( 'Request failed: ' . $valid['message'] );
    header("HTTP/1.1 400 Bad Request");
    die();
}

$intent = !empty($request["request"]["intent"]["name"]) ? $request["request"]["intent"]["name"] : "default";
$type = $request["request"]["type"];
$requestId = $request["request"]["requestId"];
$slots = !empty($request["request"]["intent"]["slots"]) ? $request["request"]["intent"]["slots"] : null;
$sessionDate = $request["session"]["attributes"];

require "Actions.php";
$action = new Actions($useridShort, $intent, $type, $requestId, $slots, $sessionDate);
$action->process();

header('Content-Type: application/json');
echo json_encode($action->getResponse());
