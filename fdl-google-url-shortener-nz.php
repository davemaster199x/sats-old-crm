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
echo "<h1><a href='https://firebase.google.com/docs/dynamic-links'>Firebase Dynamic Link - Google</a></h1>";
          

?>
<br><br>
<a href="https://firebase.google.com/docs/dynamic-links/rest">REFERENCE NOTE</a>: Requests are limited to 5 requests/IP address/second, and 200,000 requests/day. <br />
If exceeded, then the response will return HTTP error code 429. To request for more quota, <br>fill out this <a href="https://docs.google.com/a/google.com/forms/d/e/1FAIpQLSfPS2BfT5x9nwYrnQIrqu8Pzgcy2XF45Uv8ZsqBoL4RFEoQNg/viewform">form</a>.