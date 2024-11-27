<?php

include('inc/init_for_ajax.php');

$post = json_decode(file_get_contents('php://input'), true);

if (isset($post['pass'])) {
    $pass = $post['pass'];

    $encrypt = new cast128();
    $encrypt->setkey(SALT);
    $pass2 = utf8_encode($encrypt->encrypt($pass));

    echo json_encode([
        'success' => true,
        'encrypted' => $pass2,
    ]);
}
else {
    echo json_encode([
        'success' => false,
        'post' => $post,
    ]);
}
?>