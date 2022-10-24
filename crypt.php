<?php

function ssl_crypt($data)
{
    $ciphertext = openssl_encrypt(
        json_encode($data),
        "aes-128-cbc",
        pack('a16', "Testando"),
        $options = 0,
        pack('a16', 'teste')
    );

    return base64_encode($ciphertext);
}

function ssl_decrypt($data)
{
    $open_ssl = openssl_decrypt(base64_decode($data), "aes-128-cbc", pack('a16', "Testando"), $options = 0, pack('a16', 'teste'));
    return json_decode($open_ssl);
}
