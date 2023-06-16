<?php
     if (isset($_SERVER['HTTP_ORIGIN'])) {
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 86400');    // cache for 1 day
  }
   if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      
      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
          // may also be using PUT, PATCH, HEAD etc
          header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      
      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
          header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
  
      exit(0);
  }  
 

// incluindo as configurações de sistema.
include('configs/system.php');
$system = new system();
include ('model/login.php');
include ('model/funcoes.php');
include ('DataBaseFunction.php');

if(empty($_POST) && !empty(file_get_contents("php://input"))){
  $_POST =  (array)json_decode(file_get_contents("php://input"));
}
switch (@$_GET['url']) {
  case 'login':
      $login = new login();
      $login->logar(trim(@$_POST['Usuario']),trim(@$_POST['Senha']));
      break;
  case 'saldo':
      $headers = apache_request_headers();
      $funcoes = new funcoes(@$headers['Authorization']);
      $funcoes->saldo();
      break;
  case 'trasferir':
    $headers = apache_request_headers();
    $funcoes = new funcoes(@$headers['Authorization']);
    $funcoes->trasferir($_POST['Valor'],$_POST['id_Junior']);
    break;
  case 'meusdados':
    $headers = apache_request_headers();
    $funcoes = new funcoes(@$headers['Authorization']);
    $funcoes->meusdados();
    break;  
  case 'cadastraritem':
    $headers = apache_request_headers();
    $funcoes = new funcoes(@$headers['Authorization']);
    $funcoes->cadastraritem(@$_POST['Id'],$_POST['Codigo'],$_POST['Nome'],$_POST['Valor']);
    break;       
    case 'removeritem':
      $headers = apache_request_headers();
      $funcoes = new funcoes(@$headers['Authorization']);
      $funcoes->removeritem($_POST['Produto']);
    break;  
  case 'getitem':
    $headers = apache_request_headers();
    $funcoes = new funcoes(@$headers['Authorization']);
    $funcoes->getItem(@$_GET['Produto'],@$_GET['Codigo']);
    break;  
  case 'getitens':
    $headers = apache_request_headers();
    $funcoes = new funcoes(@$headers['Authorization']);
    $funcoes->getItens();
    break;  
  case 'compraritem':
    $headers = apache_request_headers();
    $funcoes = new funcoes(@$headers['Authorization']);
    $funcoes->compraritem($_POST['Produto']);
    break;  
  case 'getlistadecompra':
    $headers = apache_request_headers();
    $funcoes = new funcoes(@$headers['Authorization']);
    $funcoes->getItensComprados();
    break;  
  default:
    echo "API DO JUNIEDAS ONLINE";
}

exit;





