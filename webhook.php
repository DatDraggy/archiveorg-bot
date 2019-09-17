<?php
require_once(__DIR__ . '/funcs.php');
require_once(__DIR__ . '/config.php');
require_once('/var/libraries/composer/vendor/autoload.php');
//^ guzzlehttp

$response = file_get_contents('php://input');
$data = json_decode($response, true);
$dump = print_r($data, true);
$chatId = $data['message']['chat']['id'];
if (isset($data['message']['text'])) {
  $text = $data['message']['text'];
} else if (isset($data['message']['caption'])) {
  $text = $data['message']['caption'];
}

try {
  mail($config['mail'], 'Debug Archive', print_r(getChat($chatId), true));
} catch (Exception $e) {

}

if (!empty($data['message']['entities'])) {
  foreach ($data['message']['entities'] as $entity) {
    if ($entity['type'] == 'url') {
      $url = mb_substr($text, $entity['offset'], $entity['length']);
      if (substr($url, '0', '4') !== 'http' && substr($url, '0', '5') !== 'https') {
        $url = 'https://' . $url;
        //No https? Sucks to be you. Add http:// yourself then.
      }
      if (archiveUrl($url)) {
        $archived[] = 'https://web.archive.org/99999999999999/' . $url;
        logUrl($url, $chatId, true);
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
} else if (isset($data['message']['photo'])) {
  $photo = $data['message']['photo'][count($data['message']['photo']) - 1];
  $fileId = $photo['file_id'];
  $fileDetails = getFile($fileId);
  $saveAs = explode('/', $fileDetails['file_path'], 2)[0] . '/' . $fileId . '.' . pathinfo($fileDetails['file_path'], PATHINFO_EXTENSION);
  $fileUrl = downloadFile($config['savedIn'], $saveAs, $fileDetails['file_path']);

  $uploader = new imgBbUploader($config['imgBBkey']);
  $data = $uploader->upload($config['savedIn'] . $saveAs);
  $url = $data['url'];
  $deleteUrl = $data['delete_url'];
  if (archiveUrl($url)) {
    $archived = 'https://web.archive.org/99999999999999/' . $url;
    logUrl($url, $chatId, true, $deleteUrl);
  }
  sendMessage($chatId, 'Successfully archived the image: 
' . $archived);

}
