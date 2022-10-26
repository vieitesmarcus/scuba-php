<?php

function render_view($template, $data = [])
{
    $tratamento = [
        'erro',
        'name',
        'email',
        'success',
        'name_error',
        'email_error',
        'password',
        'password-confirm'
    ];
    // echo '<pre>';
    // var_dump($tratamento, $data);
    // echo '</pre>';exit();
    foreach($tratamento as $item){
        if(!key_exists($item, $data)){          
            $data[$item] = ""; 
        }
    }
    $page = $template . '.html';
    $content = file_get_contents(VIEW_FOLDER . $page);
    

    $keys = array_keys($data);

    $keys = array_map(function ($item) {
        if($item){
            return '{{' . $item . '}}';
        }
        
    }, $keys);


    $content = str_replace($keys, array_values($data), $content);
    
    echo $content;
}