<?php
// Load Goutte
require_once __DIR__ . '/../vendor/autoload.php';
// Load Slack token
require_once __DIR__ . '/../config/config.php';
// Load data
require_once __DIR__ . '/data.php';

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

for($i = 0; $i < count(URLS); $i++){
    // Request page
    $crawler = $client->request('GET', URLS[$i]);

    $index = 0;
    $entry_title = $crawler->filter('small')->each(function ($name) use ($i, &$index) {
        $file = __DIR__ . FILES[$i];
        if ($index == 2) {
            $new = $name->text();
            $old = file_get_contents($file);

            if (strcmp($old, $new) != 0) {
                $text = urlencode('【NEW ' . WORDS[$i] . ' IS AVAILABLE!!】'
                    . URLS[$i]);
                $slackUrl = "https://slack.com/api/chat.postMessage?token=" . SLACK_TOKEN . "&channel=@kento&text=${text}";

                // Send to Slack
                file_get_contents($slackUrl);

                // Save new date to file
                file_put_contents($file, $new);

                // Log
                logMessage("NEW " . WORDS[$i] . " IS AVAILABLE!! -> ${new}");
            } else {
                logMessage("no update " . WORDS[$i] . "!");
            }
        }
        $index++;
    });
}
