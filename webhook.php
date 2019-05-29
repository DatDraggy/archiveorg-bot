<?php
require_once(__DIR__ . '/funcs.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/vendor/autoload.php');

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
      $url = mb_substr($text, $entity['offset'], $entity['length']);
      if (archiveUrl($url)) {
        $archived[] = 'https://web.archive.org/99999999999999/' . $url;
        logUrl($url, $chatId,true);
      } else {
        $notArchived[] = $url;
        logUrl($url, $chatId, false);
      }

    }
  }
  if (!empty($archived)) {
    sendMessage($chatId, 'Successfully archived following sites: 
' . implode('\n', $archived));
  }
  if (!empty($notArchived)) {
    sendMessage($chatId, 'Failed to archive following sites: 
' . implode("\n", $notArchived));
  }
}