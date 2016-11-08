<?php
/**
 * Created by biplob.
 * User: biplob
 * Date: 10/21/16
 * Time: 1:56 PM
 */

$started_at = microtime(true);

$file   = 'files/site_list.csv';
$handle = fopen($file, 'r');

// Read each row
$i = 0;
while ( ($row = fgetcsv($handle)) !== false )
{
    $i++;
    $url = 'http://www.' . $row[0];

    echo '<h3>' . $i . '. <a href="' .$url. '" target="_blank">' . $url  . '</a></h3>';
    echo '<p>' . getTitleByCurl($url) . '</p>';

    // Please, comment out this line to get full result
    if($i == 10) break;
}

$ended_at = microtime(true);
echo "Completed in " . ($ended_at - $started_at) . 's';


function getTitle($url) {
    $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => 2
            )
        )
    );

    $html = file_get_contents($url, false, $ctx, null, 1000);
    $title = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $html, $matches) ? $matches[1] : 'Not found!';
    return $title;
}

function getTitleByCurl($url){
    // get html via url
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $html = curl_exec($ch);
    curl_close($ch);

    // get title
    preg_match('/(?<=<title>).+(?=<\/title>)/iU', $html, $match);
    $title = empty($match[0]) ? 'Untitled' : $match[0];
    $title = trim($title);

    // convert title to utf-8 character encoding
    if ($title != 'Untitled') {
        preg_match('/(?<=charset\=).+(?=\")/iU', $html, $match);
        if (!empty($match[0])) {
            $charset = str_replace('"', '', $match[0]);
            $charset = str_replace("'", '', $charset);
            $charset = strtolower( trim($charset) );
            if ($charset != 'utf-8') {
                $title = iconv($charset, 'utf-8', $title);
            }
        }
    }

    return $title;
}
