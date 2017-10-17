<?php
// Load Goutte
require_once __DIR__ . '/../vendor/autoload.php';
// Load Slack token
require_once __DIR__ . '/../config/config.php';

use Goutte\Client;

date_default_timezone_set('America/Vancouver');
function logMessage($message)
{
    $log = __DIR__ . '/log.txt';
    file_put_contents($log, date("Y/m/d H:i:s", time()) . " ${message}\n", FILE_APPEND | LOCK_EX);
}

// Create Goutte client
$client = new Client();
$client->setMaxRedirects(3);

// Request page
$crawler = $client->request('GET', 'http://bbs.jpcanada.com/search.php?w=%BF%E6%C8%D3%B4%EF&bbs=1&num=25');

$index = 0;
$entry_title = $crawler->filter('small')->each(function ($name) use (&$index) {
    $file = __DIR__ . '/date.txt';
    if ($index == 2) {
        $new = $name->text();
        $old = file_get_contents($file);

        if (strcmp($old, $new) != 0) {
            $text = urlencode('【NEW RICE COOKER IS AVAILABLE!!】
https://www.jpcanada.com/search.php?w=%BF%E6%C8%D3%B4%EF&encode=EUC-JP&bbs=1&num=25');
            $url = "https://slack.com/api/chat.postMessage?token=" . SLACK_TOKEN . "&channel=@kento&text=${text}";

            // Send to Slack
            file_get_contents($url);

            // Save new date to file
            file_put_contents($file, $new);

            // Log
            logMessage("NEW RICE COOKER IS AVAILABLE!! -> ${new}");
        } else {
            logMessage('no update!');
        }
    }
    $index++;
});
