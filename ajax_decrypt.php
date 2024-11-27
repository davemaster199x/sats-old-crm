<?php

include('inc/init_for_ajax.php');

$post = json_decode(file_get_contents('php://input'), true);

if (isset($post['pass'])) {
    $pass = $post['pass'];

    $encrypt = new cast128();
    $encrypt->setkey(SALT);

    $decryptedPassword = addslashes($encrypt->decrypt(utf8_decode($pass)));

    echo json_encode([
        'success' => true,
        'decrypted' => $decryptedPassword,
    ]);
}
else {
    echo json_encode([
        'success' => false,
        'post' => $post,
    ]);
}
?>