<?php

function fetchAllArray($resultSet) {
    $results = [];

    while(($result = mysql_fetch_array($resultSet))) {
        $results[] = $result;
    }

    return $results;
}

?>