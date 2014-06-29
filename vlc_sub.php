<?php

//kill existing vlc processes
shell_exec('sh ./killVlcProc.sh '. getmypid());

$movieArray = explode('/', $argv[1]);
$movieName = end($movieArray);
$movieName = urlencode($movieName);

$url = 'http://www.opensubtitles.org/en/search/sublanguageid-fre/moviename-' . $movieName;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$arr = explode('.', $argv[1]);
array_pop($arr);
$srtPath = implode('.', $arr).'.srt';

$result = str_replace("\n", " ", $result);
preg_match_all("/<table\s+id=\"search_results\">(.*)<\/table>/m", $result, $match);

function isBlackListed($str) {

	$handle = fopen(dirname(__FILE__) . '/blacklist.txt', "r");
	while (($line = fgets($handle)) !== false) {
        if (strpos($line, end(explode('/', $str)))) {
        	fclose($handle);
        	return 1;
        }
    }
	fclose($handle);
	return 0;
}

$compt = 0;
$isOk = 0;
$srtUrl = "";
while ($isOk == 0) {

	preg_match_all("/<td(.*)<\/td>/m", $match[1][$compt], $td);
	preg_match_all("/href=\"([^\"]*)\"/m", $td[1][$compt], $href);
	
	for ($i = 0; $i < count($href[1]); $i += 1) {
		if ((strpos($href[1][$i], 'subtitleserve') > 0) && (isBlackListed($href[1][$i]) == 0)) {
			$srtUrl = $href[1][$i];
			$isOk = 1;
			break;
		}
	}
	$compt += 1;
	if (($compt == 100) || ($isOk == 1)) {
		break;
	}
}

$srtId = end(explode('/', $srtUrl));
$srtUrl = 'http://dl.opensubtitles.org/en/download/sub/'.$srtId;
shell_exec('curl '.$srtUrl.' > sub.zip');
shell_exec('rm -f *.srt *.nfo');
shell_exec('unzip sub.zip');
shell_exec("mv *.srt '$srtPath'");

echo ("\n\n" . 'Launching [' . $argv[1] . '] with french subtitles downloaded from [' . $srtUrl . ']' . "\n\n");
file_put_contents(dirname(__FILE__) . '/blacklist.txt', $srtUrl . "\n", FILE_APPEND);

echo ("\n\n" . 'Just relaunch me if subtitles sucks ^^' . "\n\n");
shell_exec("/Applications/VLC.app/Contents/MacOS/VLC --fullscreen --video-on-top '$argv[1]'");

//kill existing vlc processes
shell_exec('sh ./killVlcProc.sh '. getmypid());

?>
