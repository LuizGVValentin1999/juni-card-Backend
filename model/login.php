<?php

class login {
  
    function __construct() { 
        
    }
    public function logar($usuario, $senha){


      header("Content-Type: application/json");
      $resp =[];
      $db_user = new dataBase('USUARIO');
      $db_token = new dataBase('TOKEN_ACESSO');

      $user = $db_user->fetchRow("USUARIO = '{$usuario}'");

      $size = 38; // até 40
      $seed = time(); // time() só para exemplo!
      $token = substr(sha1($seed), 40 - min($size,40)) ."_". $usuario;

      if(empty($user) && $senha === 'Juniedas'){
        $senhacryp = password_hash($senha, PASSWORD_DEFAULT);
        $userInset['USUARIO'] = $usuario;
        $userInset['SENHA'] = $senhacryp;
        $userInset['ADMIN'] = '0';
        $userInset['SALDO'] = 10.00;
        $db_user->insert($userInset);
        $user = $db_user->fetchRow("USUARIO = '{$usuario}'");
        $resp['msg'] = 'Bem vindo a Juniedas';
      }
      else if(password_verify(@$senha, @$user['SENHA'])){
        $resp['msg'] = 'Bem vindo a Juniedas';
       
      }else{
        $resp['msg'] = 'Usuário ou senha invalida';
        $resp['status'] = '0';
        http_response_code(300);
        echo json_encode($resp);
        exit;
      }

      $tokenInset['ID_USUARIO'] = $user['ID'];
      $tokenInset['TOKEN'] = $token;
      $db_token->insert($tokenInset);


      $resp['token'] = $token;
      http_response_code(201);
      echo json_encode($resp);
      exit;



    
    }
}



