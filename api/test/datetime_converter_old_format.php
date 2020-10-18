<?php
// Check Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response['status'] = 'true';

if( isset($_REQUEST['datetime']) )
{
    $datetime = strtotime($_REQUEST['datetime']);
    $myDateTime = DateTime::createFromFormat('d F Y, H:i A', $_REQUEST['datetime']);
    $newDateString = $myDateTime->format('c');
    $response['data'] = $newDateString;
}
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
