<?php
// Load Goutte
require_once '../vendor/autoload.php';
// Load Slack token
require_once __DIR__ . '/../config/config.php';
use Goutte\Client;

// Create Goutte client
$client = new Client();
$client->setMaxRedirects(3);

// Request page
$crawler = $client->request('GET', 'http://bbs.jpcanada.com/search.php?w=%BF%E6%C8%D3%B4%EF&bbs=1&num=25');

$index = 0;
$entry_title = $crawler->filter('small')->each(function($name) use (&$index) {
    $file = 'date.txt';
    if($index == 2) {
      $new = $name->text();
      echo "new: ${new}\n";
      $old = file_get_contents($file);
      echo "old: ${old}\n";
      if (strcmp($old, $new) != 0) {
          echo "NEW RICE COOKER IS AVAILABLE!!\n";
          $text = '【NEW RICE COOKER IS AVAILABLE!!】
    https://www.jpcanada.com/search.php?w=%BF%E6%C8%D3%B4%EF&encode=EUC-JP&bbs=1&num=25';
          $text = urlencode($text);
          $url = "https://slack.com/api/chat.postMessage?token=" . SLACK_TOKEN . "&channel=@kento&text=${text}";
          file_get_contents($url);

          file_put_contents($file, $new);
      } else {
          echo "no update!\n";
      }
    }
    $index++;
});


 ?>