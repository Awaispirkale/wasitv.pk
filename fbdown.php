<?php

//header('Content-Type: application/json');

$msg = [];

try {
    $url = $_GET['url'];

    if (empty($url)) {
        throw new Exception('Please provide the URL', 1);
    }

    $context = [
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.47 Safari/537.36',
        ],
    ];
    $context = stream_context_create($context);
    $data = file_get_contents($url, false, $context);

    $msg['success'] = true;

    $msg['id'] = generateId($url);
    $msg['title'] = getTitle($data);
	$sdLink = getSDLink($data);
	$hdLink = getHDLink($data);
    if ($hdLink) {
		//echo $hdLink;
		//echo '<style>video{width:100%;height: auto}</style><video controls="" autoplay="" name="media"><source src="'.$hdLink.'" type="video/mp4"></video>';
		//header('Location:'.$hdLink);
		echo "<script>window.location = '".$hdLink."'</script>";
    }else{
		//header('Location:'.$sdLink);
		echo "<script>window.location = '".$sdLink."'</script>";
    }
} catch (Exception $e) {
    $msg['success'] = false;
    $msg['message'] = $e->getMessage();
}

//echo json_encode($msg);

function generateId($url)
{
    $id = '';
    if (is_int($url)) {
        $id = $url;
    } elseif (preg_match('#(\d+)/?$#', $url, $matches)) {
        $id = $matches[1];
    }

    return $id;
}

function cleanStr($str)
{
    return html_entity_decode(strip_tags($str), ENT_QUOTES, 'UTF-8');
}

function getSDLink($curl_content)
{
    $regexRateLimit = '/sd_src_no_ratelimit:"([^"]+)"/';
    $regexSrc = '/sd_src:"([^"]+)"/';

    if (preg_match($regexRateLimit, $curl_content, $match)) {
        return $match[1];
    } elseif (preg_match($regexSrc, $curl_content, $match)) {
        return $match[1];
    } else {
        return false;
    }
}

function getHDLink($curl_content)
{
    $regexRateLimit = '/hd_src_no_ratelimit:"([^"]+)"/';
    $regexSrc = '/hd_src:"([^"]+)"/';

    if (preg_match($regexRateLimit, $curl_content, $match)) {
        return $match[1];
    } elseif (preg_match($regexSrc, $curl_content, $match)) {
        return $match[1];
    } else {
        return false;
    }
}

function getTitle($curl_content)
{
    $title = null;
    if (preg_match('/h2 class="uiHeaderTitle"?[^>]+>(.+?)<\/h2>/', $curl_content, $matches)) {
        $title = $matches[1];
    } elseif (preg_match('/title id="pageTitle">(.+?)<\/title>/', $curl_content, $matches)) {
        $title = $matches[1];
    }

    return cleanStr($title);
}

function getDescription($curl_content)
{
    if (preg_match('/span class="hasCaption">(.+?)<\/span>/', $curl_content, $matches)) {
        return cleanStr($matches[1]);
    }

    return false;
}

//      https://video-iad3-1.xx.fbcdn.net/v/t39.24130-2/10000000_250295792779841_7920433794763451664_n.mp4?_nc_cat=103&_nc_sid=985c63&efg=eyJ2ZW5jb2RlX3RhZyI6Im9lcF9oZCJ9&_nc_ohc=2RZ0OYz0yIIAX9AJVKl&_nc_ht=video-iad3-1.xx&oh=8e73b68ff81f0a90786fcd80d9dbbe1a&oe=5EF85346
