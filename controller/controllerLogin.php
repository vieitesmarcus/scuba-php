<?php

function do_register()
{


    if ($_POST) { // verifica se tem algo enviado no POST

        $user = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS); //valida os inputs
        if (!$user) { // se não houver nada redireciona para a página de registro
            header('location:/?page=register', true, 302);
            exit();
        }
        // require_once __DIR__ . '/../crud.php';
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

        sendEmail($user['person']['email'], "Confirmação de Email", $body, "AltBody");
        header('location:?page=login&from=register', true, 302);
        exit();
    }

    render_view('register');
}

function do_login()
{
    if (isset($_GET['from'])) {

        switch (filter_input(INPUT_GET, 'from', FILTER_SANITIZE_URL)) {
            case 'tokenErro':
                render_view('login', ['erro' => "email não autênticado"]);
                exit();
                break;
            case 'tokenSuccess':
                render_view('login', ['success' => "Email autênticado com sucesso"]);
                exit();
                break;
            case 'tokenSucesso':
                render_view('login', ['success' => "Senha alterada com sucesso"]);
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
                render_view('login');
                exit();
                break;
        }
    }
    render_view('login');
    exit();
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
    render_view('home', [
        'name' => $_SESSION["user"]->name,
        'email' => $_SESSION["user"]->email
    ]);
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
        if (!crud_verifica_email($email['person']['email'])) {
            $message['email_error'] = 'Email inexistente';
            render_view('forget_password', $message);
            exit();
        }

        $time = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
        // echo date('d-m-y', $time);
        $urlBody = APP_URL . '?page=change-password&token=';
        $urlBody .= ssl_crypt($email['person']['email'] . ':' . $time->format(trim('d.m.Y:H.i.s')));
        $body = "Olá, <br/> Bem-vindo à ScubaPHP! <br> Clique no link para recuperar sua senha. <br>  <a href='$urlBody'>Recuperar Senha</a>";
        if (!sendEmail($email['person']['email'], 'Recupeção de senha', $body, "Recuperação de Senha")) {
            $message['erro'] = 'email não enviado';
        }
        $message['success'] = 'email enviado com sucesso';
    }
    render_view('forget_password', $message);
}

function do_change_password()
{
    $messages = [];
    if ($_SERVER["REQUEST_METHOD"] === 'GET' && isset($_GET['token']) === true) { // verifica se o method passado é 'GET' e se possui uma chave 'token'
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_URL);

        $tokenDecrypt = ssl_decrypt($token); //descriptografa o ssl passado na chave token 

        if (!$tokenDecrypt) { //verifica se o token existe 
            render_view('forget_password', ['erro' => 'token inválido']);
            exit();
        }
        $tokenDecrypt = explode(":", $tokenDecrypt); //cria um array com o email, data do dia do token e horário

        //valida se existe o usuario no banco de dados
        $userExiste = crud_verifica_email($tokenDecrypt[0]); // verifica se o email passado no token existe no banco de dados
        if (!$userExiste) {
            render_view('forget_password', ['erro' => 'usuário inexistente']);
            exit();
        }
        $tempo = date($tokenDecrypt[1] . $tokenDecrypt[2]);
        // $tempo = date('25-10-2022 08:44:00'); // teste para verificar horario
        $tempo = new DateTime($tempo, new DateTimeZone('America/Sao_Paulo'));

        $verificarTempoToken = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

        $dataToken = $tempo->diff($verificarTempoToken);
        // echo '<pre>';
        //     var_dump($dataToken);
        // echo '</pre>';exit();
        if ($dataToken->d >= 1 || $dataToken->h >= 1 || $dataToken->i >= 15) {
            render_view('forget_password', ['erro' => 'token expirado']);
            exit();
        }
        $_SESSION['email'] = $tokenDecrypt[0];
        render_view('change_password');
        exit();
    }

    if ($_POST) {
        $user = filter_input_array(
            INPUT_POST,
            [
                'person' =>
                [
                    'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                    'flags' => FILTER_REQUIRE_ARRAY
                ]
            ]
        );
        $user['person']['email'] = isset($_SESSION['email']) ? $_SESSION['email'] : false;

        if ($user['person']['password'] !== $user['person']['password-confirm']) {
            $messages['password-confirm'] = 'password precisa ser igual ao de cima';
            render_view('change_password', $messages);
            exit();
        }

        crud_update_password($user['person']['email'], md5($user['person']['password']));
        session_destroy();
        header("Location:/?page=login&from=tokenSucesso", true, 302);
        exit();
        // render_view('change_password', $messages);exit();

    }
    render_view('forget_password', $messages);
}

function do_not_found()
{

    render_view('not_found');
    http_response_code(404);
}
