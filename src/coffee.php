<?php
// Load Goutte
require_once __DIR__ . '/../vendor/autoload.php';
// Load Slack token
require_once __DIR__ . '/../config/config.php';

use Goutte\Client;
const URL = 'https://www.jpcanada.com/search.php?w=%A5%B3%A1%BC%A5%D2%A1%BC%A5%E1%A1%BC%A5%AB%A1%BC&encode=EUC-JP&bbs=1&num=25';
const WORD = 'COFFEE MAKER';
const TEXT = '/coffee_date.txt';

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
$crawler = $client->request('GET', URL);

$index = 0;
$entry_title = $crawler->filter('small')->each(function ($name) use (&$index) {
    $file = __DIR__ . TEXT;
    if ($index == 2) {
        $new = $name->text();
        $old = file_get_contents($file);

        if (strcmp($old, $new) != 0) {
            $text = urlencode('【NEW '.WORD.' IS AVAILABLE!!】'
.URL);
            $url = "https://slack.com/api/chat.postMessage?token=" . SLACK_TOKEN . "&channel=@kento&text=${text}";

            // Send to Slack
            file_get_contents($url);

            // Save new date to file
            file_put_contents($file, $new);

            // Log
            logMessage("NEW ".WORD." IS AVAILABLE!! -> ${new}");
        } else {
            logMessage("no update ".WORD."!");
        }
    }
    $index++;
});
