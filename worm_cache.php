<?php

$urls = array('www.example1.com', 'www.example2.com', 'example3.com');
$user_agent_desktop = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246";
$user_agent_mobile = "Mozilla/5.0 (Linux; U; Android 4.4.2; en-us; SCH-I535 Build/KOT49H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30";

function warm_cache_mobile($url) {
    global $user_agent_mobile;
    echo "Warming cache for $url on mobile\n";
    $sitemap = file_get_contents("http://$url/sitemap_index.xml");
    preg_match_all("/http(s?):\/\/$url[^ \"\'()<>]+/", $sitemap, $matches);
    $urls = $matches[0];
    foreach ($urls as $line) {
        if (substr($line, -4) == ".xml") {
            $sub_sitemap = file_get_contents($line);
            preg_match_all("/http(s?):\/\/$url[^ \"\'()<>]+/", $sub_sitemap, $sub_matches);
            $sub_urls = $sub_matches[0];
            foreach ($sub_urls as $sub_line) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $sub_line);
                curl_setopt($ch, CURLOPT_USERAGENT, $user_agent_mobile);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($ch);
                echo $sub_line . "\n";
                curl_close($ch);
            }
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $line);
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent_mobile);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_exec($ch);
            echo $line . "\n";
            curl_close($ch);
        }
    }
    echo "Done warming cache for $url on mobile\n";
}


function warm_cache_desktop($url) {
    global $user_agent_desktop;
    echo "Warming cache for $url on desktop\n";
    $sitemap = file_get_contents("http://$url/sitemap_index.xml");
    preg_match_all("/http(s?):\/\/$url[^ \"'()<>]+/", $sitemap, $matches);
    $links = $matches[0];
    foreach ($links as $link) {
        if (substr($link, -4) == ".xml") {
            $new_sitemap = file_get_contents($link);
            preg_match_all("/http(s?):\/\/$url[^ \"'()<>]+/", $new_sitemap, $new_matches);
            $new_links = $new_matches[0];
            foreach ($new_links as $new_link) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $new_link);
                curl_setopt($ch, CURLOPT_USERAGENT, $user_agent_desktop);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $output = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                echo "$httpcode $new_link\n";
                curl_close($ch);
            }
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $link);
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent_desktop);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            echo "$httpcode $link\n";
            curl_close($ch);
        }
    }
    echo "Done warming cache for $url on desktop\n";
}

foreach ($urls as $url) {
    warm_cache_mobile($url);
    warm_cache_desktop($url);
}
