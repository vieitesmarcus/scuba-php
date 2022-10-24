<?php

function crud_create($user)
{

    if(crud_verifica_email($user['person']['email'])){
        return ['email_error'=> 'Email existente'];
    }

    $validacaoUser = valida_create_user($user['person']);

    if($validacaoUser){
        return $validacaoUser;
    }
    
    $user['person']['mail_validation'] = false;
    unset($user['person']['password-confirm']);

    $user['person']['password'] = md5($user['person']['password']);

    $jsonArr = load_crud();

    $jsonArr[] = (object)$user['person'];
    
    $userInJson = json_encode($jsonArr);

    return flush_crud($userInJson);
}

function crud_verifica_email($user)
{
    // require_once __DIR__ . '/config.php';
    $jsonArr = load_crud();

    if($jsonArr){
        foreach($jsonArr as $obUser){
            if($obUser->email === $user){
                return true;
            } 
        }
    }
    return false;
    
}

function crud_update($email)
{
    if(!crud_verifica_email($email)){
        return false;
    }

    $jsonArr = load_crud();

    if($jsonArr){
        foreach($jsonArr as $obUser){
            if($obUser->email === $email){
                $obUser->mail_validation = true;
                break;
            } 
        }
    }

    $jsonArr = json_encode($jsonArr);

    return flush_crud($jsonArr);
}

function crud_restore_user($email, $password)
{
   
    $jsonArr = load_crud();
    if($jsonArr){
        foreach($jsonArr as $obUser){
            if($obUser->email === $email && $obUser->password === $password){
                if($obUser->mail_validation === true){
                    return $obUser;
                }
                if($obUser->mail_validation === false){
                    header("Location:/?page=login&from=tokenErro", true, 302);
                    exit();
                }
            } 
        }
    }
    
}
function load_crud() //BUSCA OS USUARIOS
{
    $json = file_get_contents(DATA_LOCATION);
    return json_decode($json);
}

function flush_crud($data) // ATUALIZA OS USUARIOS
{
    file_put_contents(DATA_LOCATION,$data);
    return true;
}

function crud_delete($dados)
{
    
    $jsonArr = load_crud();
    // $jsonAre = array_splice($jsonArr,1,1);
    // echo '<pre>';
    // var_dump($jsonAre);
    // echo '</pre>';
    // echo '<pre>';
    // var_dump($jsonArr);
    // echo '</pre>';
    if($jsonArr){
        foreach($jsonArr as $key => $obUser){
            if($obUser->email === $dados->email){
                array_splice($jsonArr,$key,1); //remove do array o usuario
                unset($obUser); //remove do array o usuario 
            } 
        }
    }
   
    $jsonUsers = json_encode($jsonArr);
    // echo '<pre>';
    // var_dump($jsonUsers);
    // echo '</pre>';exit();

    return flush_crud($jsonUsers);
    
}