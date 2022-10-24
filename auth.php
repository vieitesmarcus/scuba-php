<?php

function authentication($email, $password)
{
    if(!crud_verifica_email($email)){
        return false;
        exit();
    }

    $password = md5($password);
    
    $userBancoDeDados = crud_restore_user($email, $password); //faz as verificações e retorna o usuario
    if(!$userBancoDeDados){
        return false;
    }
    
    $_SESSION['user'] = $userBancoDeDados;
    header('Location:/?page=home',true, 302);
    exit();
}

function auth_user()
{
    return $_SESSION['user'];
}
function auth_logout()
{
    session_destroy();
    header('Location:/?page=login', true, 302);
    exit();
}