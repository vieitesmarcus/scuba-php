<?php

function do_register()
{


    if ($_POST) { // verifica se tem algo enviado no POST

        $user = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS); //valida os inputs
        if (!$user) { // se não houver nada redireciona para a página de registro
            header('location:/?page=register', true, 302);
            exit();
        }
        require_once __DIR__ . '/../crud.php';
        $validar = crud_create($user); // valida o usuario e se retornar true é porque ocorreu tudo corretamente no cadastro senão retorna um array com os erros 
        unset($user['person']['password-confirm']);
        if (is_array($validar)) {
            $validar['name'] = $user['person']['name'];
            $validar['email'] = $user['person']['email'];
            render_view('register', $validar);
            exit();
        }

        // enviar email com token para autenticar
        $urlBody = APP_URL . '?page=mail-validation&token=';
        $urlBody .= ssl_crypt($user['person']['email']);

        $body = "Olá, <br/> Bem-vindo à ScubaPHP! <br> Confirme seu endereço de email. <br>  <a href='$urlBody'>Confirmar Email</a>";

        sendEmail($user['person']['email'],"Confirmação de Email", $body ,"AltBody");
        header('location:?page=login&from=register', true, 302);
        exit();
    }

    render_view('register');
}

function do_login()
{


    // echo '<pre>';
    // var_dump($_POST);
    // echo '</pre>';exit();
    // $url = filter_input(INPUT_GET,'from',FILTER_SANITIZE_URL);

    switch (filter_input(INPUT_GET, 'from', FILTER_SANITIZE_URL)) {
        case 'tokenErro':
            render_view('login', ['erro' => "email não autênticado"]);
            exit();
            break;
        case 'tokenSuccess':
            render_view('login', ['success' => "Email autênticado com sucesso"]);
            exit();
            break;
        case 'register':
            render_view('login', ['success' => "Cadastrado com Sucesso"]);
            exit();
            break;
        case 'login':
            if ($_POST) {
                $personArr = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                if (!authentication($personArr['person']['email'], $personArr['person']['password'])) {
                    render_view('login', ['erro' => 'Login ou senha inválidos', 'email' => $personArr['person']['email']]);
                    break;
                }
            }
            render_view('login');
            exit();
            break;
        default:
            render_view('login', ['success', "Sucesso"]);
            exit();
            break;
    }
}

function do_validation()
{
    $token = filter_input(INPUT_GET, 'token');

    $tokenDescriptografado = ssl_decrypt($token);

    if (!crud_update($tokenDescriptografado)) {
        header('Location:/?page=login&from=tokenErro', true, 302);
        exit();
    }

    header('Location:/?page=login&from=tokenSuccess', true, 302);
    exit();
}


function do_home()
{
    render_view('home');
}

function do_logout()
{
    auth_logout();
}

function do_delete_account()
{
    $dadosUser = $_SESSION['user'];

    if (!crud_delete($dadosUser)) {
        header('Location:/?page=home&from=delete', 302, true);
        exit();
    }
    session_destroy();
    header('Location:/', true, 302);
    exit();
}



function do_forget_password()
{

$message = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $email = filter_input_array(
            INPUT_POST,
            [
                'person' => 
                [
                    'filter' => FILTER_VALIDATE_EMAIL,
                    'flags'  => FILTER_REQUIRE_ARRAY,
                ]
            ]
        );
        if(!crud_verifica_email($email['person']['email'])){
            $message['erro'] ='Email inexistente';
        }


    }
    render_view('forget_password', $message);
}

function do_change_password()
{
    render_view('change_password');
}

function do_not_found()
{

    render_view('not_found');
    http_response_code(404);
}
