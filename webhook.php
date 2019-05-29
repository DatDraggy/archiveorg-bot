<?php
require_once(__DIR__ . '/funcs.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/vendor/autoload.php');

use GuzzleHttp\Client;

$response = file_get_contents('php://input');
$data = json_decode($response, true);
$dump = print_r($data, true);
$chatId = $data['message']['chat']['id'];
$chatType = $data['message']['chat']['type'];

sendMessage($chatId, 'Test');