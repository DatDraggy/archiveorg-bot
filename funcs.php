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
    $response = $client->post($method, array('query' => $data));
  } catch (\GuzzleHttp\Exception\BadResponseException $e) {
    $body = $e->getResponse()->getBody();
    mail($config['mail'], 'Test', print_r($body->getContents(), true));
  }
  return json_decode($response->getBody(), true);
}

function logUrl($url, $userId, $success) {
  $file = 'log.txt';
  file_put_contents($file, $userId . '|' . $url . '|' . $success . "\n", FILE_APPEND);
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

function downloadFile($path, $filePath, $origFilePath){
  global $config;
  $fullPath = $path . $filePath;
  if (!file_exists(dirname($fullPath))) {
    mkdir(dirname($fullPath), 0770, true);
  }
  if(!file_exists($fullPath)){
    $url = 'https://api.telegram.org/file/bot'.$config['token'].'/'.$origFilePath;
    file_put_contents($fullPath, fopen($url, 'r'));
  }
  return $config['mediaPath'] . $filePath;
}
