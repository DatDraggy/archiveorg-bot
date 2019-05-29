<?php
require_once(__DIR__ . '/funcs.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/vendor/autoload.php');

use GuzzleHttp\Client;

$response = file_get_contents('php://input');
$data = json_decode($response, true);
$dump = print_r($data, true);
$chatId = $data['message']['chat']['id'];
if (isset($data['message']['text'])) {
  $text = $data['message']['text'];
} else if (isset($data['message']['caption'])) {
  $text = $data['message']['caption'];
}

if (!empty($data['message']['entities'])) {
  foreach ($data['message']['entities'] as $entity) {
    if ($entity['type'] == 'url') {
      mail($config['mail'], 'Debug', mb_substr($text, $entity['offset'], $entity['length']));
    }
  }
} else if (!empty($data['message']['caption_entities'])) {
  foreach ($data['message']['caption_entities'] as $entity) {
    if ($entity['type'] == 'url') {
      mail($config['mail'], 'Debug', mb_substr($text, $entity['offset'], $entity['length']));
    }
  }
}