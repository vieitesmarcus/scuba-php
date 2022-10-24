<?php

function valida_create_user($user)
{
    $errors = [];
    
    if(mb_strlen($user['name']) < 4 ){
        $errors['name_error'] = 'O nome deve ter pelo menos 4 caracteres';
    }
    if($user['password'] !== $user['password-confirm']){
        $errors['password-confirm'] = 'As senhas não conferem';
    }
    if(mb_strlen($user['password']) < 10){
        $errors['password'] = 'A senha deve ter pelo menos 10 caracteres';
    }
    if(count($errors) > 0){
        return $errors;
    }

    return false;
}

