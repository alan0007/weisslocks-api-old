<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getdata($csv_file){
    $filename = $csv_file;
    $i = 0;
    $fields = array();

    $assoc_array = [];
    // open for reading
    if (($handle = fopen($filename, "r")) !== false) {
        // extract header data
        if (($data = fgetcsv($handle, 1000, ",")) !== false) {
            // save as keys
            $keys = $data;
        }
        // loop remaining rows of data
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            // push associative subarrays
            $assoc_array[] = array_combine($keys, $data);
        }
        // close when done
        fclose($handle);
    }
    return $assoc_array;
}

$body = $_REQUEST['Body'];

// Decode HTTP to plain text, like space or special symbol, %20,...
$message_decoded = strval(urldecode($body));
// echo $message_decoded;

// Set path to CSV file
$csv_file = dirname(dirname(__FILE__)) . '/assets/match_access_code.csv';

// Get csv data in array form
$csv_array = getdata($csv_file);
// print_r($csv_array);

$key = array_search($message_decoded, array_column($csv_array, 'key'));
// print_r($key);
$result = $csv_array[$key]['value'];
echo $result;

