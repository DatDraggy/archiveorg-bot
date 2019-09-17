<?php
function sendMessage($chatId, $text, $replyTo = '', $replyMarkup = '') {
  global $config;
  $data = array(
    'disable_web_page_preview' => true,
    'parse_mode' => 'html',
    'chat_id' => $chatId,
    'text' => $text,
    'reply_to_message_id' => $replyTo,
    'reply_markup' => $replyMarkup
  );
  makeApiRequest('sendMessage', $data);
}

function makeApiRequest($method, $data) {
  global $config, $client;
  if (!($client instanceof \GuzzleHttp\Client)) {
    $client = new \GuzzleHttp\Client(['base_uri' => $config['url']]);
  }
  try {
    $response = $client->request('POST', $method, array('json' => $data));
  } catch (\GuzzleHttp\Exception\BadResponseException $e) {
    $body = $e->getResponse()->getBody();
    mail($config['mail'], 'Error', print_r($body->getContents(), true) . "\n" . print_r($data, true) . "\n" . __FILE__);
    return false;
  }
  return json_decode($response->getBody(), true)['result'];
}

function logUrl($url, $userId, $success, $extra = '') {
  $file = 'log.txt';
  file_put_contents($file, $userId . '|' . $url . '|' . $success . '|' . $extra . "\n", FILE_APPEND);
}

function archiveUrl($url) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://web.archive.org/save/' . $url);
  curl_exec($ch);
  if (curl_error($ch)) {
    $error_msg = curl_error($ch);
  }
  curl_close($ch);
  if (isset($error_msg)) {
    mail($config['mail'], 'Error', 'Archive Bot | ' . $error_msg);
    return false;
  }
  return true;
}

function downloadFile($path, $filePath, $origFilePath) {
  global $config;
  $fullPath = $path . $filePath;
  if (!file_exists(dirname($fullPath))) {
    mkdir(dirname($fullPath), 0770, true);
  }
  if (!file_exists($fullPath)) {
    $url = 'https://api.telegram.org/file/bot' . $config['token'] . '/' . $origFilePath;
    file_put_contents($fullPath, fopen($url, 'r'));
  }
  return $config['mediaPath'] . $filePath;
}

function getFile($fileId) {
  $data = array('file_id' => $fileId);
  return makeApiRequest('getFile', $data);
}

function getChat($chatId){
  $data= array('chat_id'=>$chatId);
  return makeApiRequest('getChat', $data);
}

class imgBBuploader {
  private $key;

  function __construct($key) {
    $this->key = $key;
  }

  public function upload($imagePath) {
    $apitoken = $this->key;
    $image = file_get_contents($imagePath);
    $encodedImage = base64_encode($image);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key=' . $apitoken);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = array(
      'image' => $encodedImage
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $output = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $output['data'];
  }
}