<?php

function getDynamicLink($apiKey, $params) {
    $url = "https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=" . $apiKey;
    $options = array(
        'http' => array(
            'header' => "Content-type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($params)
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) { /* Handle error */
    }

    return $result;
}
$url = "https://crmdev.sats.com.au/view_entry_notice_new.php?letterhead=1&i=318&m=15606308615c1f60890cce9adb439aab";
echo "<h1><a href='https://firebase.google.com/docs/dynamic-links'>Firebase Dynamic Link - Google</a></h1>";
echo "orig link: <a href='$url'>" . $url . "</a>";
echo "<br />";
$api_key = "AIzaSyB88Wb3cS0dxCVED3a7T5pj_Sf1vfvHYlY";
$domainUriPrefix = "https://url.sats.com.au";


$data = [
    "dynamicLinkInfo" => [
        "domainUriPrefix" => $domainUriPrefix,
        "link" => $url
    ],
    "suffix" => [
        "option" => "UNGUESSABLE"
    ]
];
$dynamic_link = getDynamicLink($api_key, $data);
$return = array();
if (!$dynamic_link) {
    echo "Error accessing API";
    exit;
}
$dynamic_link = json_decode($dynamic_link, true);
if (isset($dynamic_link['shortLink'])) {

    echo "short link AU: <a href='" . $dynamic_link['shortLink'] . "'>" . $dynamic_link['shortLink'] . "</a><br>";
} else {
    echo "Error generating AU shortened url<br>";
}
$api_key = "AIzaSyA9EQfCyG6NprM6ws1JXoh83DDKBkWoBjY";
$domainUriPrefix = "https://url.sats.co.nz";

    $data = [
        "dynamicLinkInfo" => [
            "domainUriPrefix" => $domainUriPrefix,
            "link" => $url
        ],
        "suffix" => [
            "option" => "UNGUESSABLE"
        ]
    ];
    $dynamic_link = getDynamicLink($api_key, $data);
    $return = array();
    if (!$dynamic_link) {
        echo "Error accessing API";
        exit;
    }
    $dynamic_link = json_decode($dynamic_link, true);
    if (isset($dynamic_link['shortLink'])) {

        echo "short link NZ: <a href='" . $dynamic_link['shortLink'] . "'>" . $dynamic_link['shortLink'] . "</a><br>";
    } else {
        echo "Error generating shortened url<br>";
    }

?>
<br><br>
<a href="https://firebase.google.com/docs/dynamic-links/rest">REFERENCE NOTE</a>: Requests are limited to 5 requests/IP address/second, and 200,000 requests/day. <br />
If exceeded, then the response will return HTTP error code 429. To request for more quota, <br>fill out this <a href="https://docs.google.com/a/google.com/forms/d/e/1FAIpQLSfPS2BfT5x9nwYrnQIrqu8Pzgcy2XF45Uv8ZsqBoL4RFEoQNg/viewform">form</a>.