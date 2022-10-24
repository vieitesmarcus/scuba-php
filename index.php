<?php
session_start();
include 'boot.php';
$page = $_GET['page']??'login';

if(!isset($_SESSION['user'])){
    guest_routes($page);
    exit();
}
    
auth_routes($page);
// echo '<pre>';
// var_dump($_SESSION['user']);
// echo '</pre>';exit();
exit();
