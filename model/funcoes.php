<?php

class funcoes {
  
    private $_user = [];

    function __construct($token = '') { 
      header("Content-Type: application/json");
      $db_token = new dataBase('TOKEN_ACESSO');
      $db_user = new dataBase('USUARIO');
      $token = str_replace('Bearer ','',$token);
      $token = $db_token->fetchRow("TOKEN = '{$token}'");
     
      if(empty($token)){
        $resp['msg'] = 'Acesso Negado';
        $resp['status'] = '-1';
        http_response_code(300);
        echo json_encode($resp);
        exit;
      }
      else{
        $this->_user = $db_user->fetchRow("ID = '{$token['ID_USUARIO']}'");
        unset($this->_user['SENHA']);
      }

    }
    
    public function saldo(){
      http_response_code(200);
      echo json_encode($this->_user);
      exit;
    }

    public function meusdados(){
      http_response_code(200);
      echo json_encode($this->_user);
      exit;
    }
    

    public function trasferir($valor, $id_aluno){
      if($this->_user['ADMIN']){
        $db_user = new dataBase('USUARIO');
        $aluno  = $db_user->fetchRow("ID = {$id_aluno}");
        if(!empty($aluno)){

          $aluno['SALDO'] += $valor;
          $db_user->update($aluno,"ID = {$id_aluno}");

          $resp['msg'] = "Saldo atual do aluno é de: {$aluno['SALDO']}  "; 
          $resp['status'] = '1';
          http_response_code(201);
          echo json_encode($resp);
          exit;
        }
        $resp['msg'] = 'Aluno não cadastrado no Juniedas';
        $resp['status'] = '0';
        http_response_code(300);
        echo json_encode($resp);
        exit;


       
      }
      $resp['msg'] = 'Acesso Negado';
      $resp['status'] = '-1';
      http_response_code(300);
      echo json_encode($resp);
      exit;
     
    }

    public function cadastraritem($id = null,$codigo,$nome, $valor){
      if($this->_user['ADMIN']){
        $db_itens = new dataBase('ITEMS');
        
        $idwhereValidar ='';
        if(!empty($id)){
          $idwhereValidar = "AND ID != {$id}";
          if(empty($db_itens->fetchRow(" ID = {$id}"))){
            $resp['msg'] = 'Não foi possivel salvar pois o produto a ser editado não foi localizado. ';
            $resp['status'] = '0';
            http_response_code(300);
            echo json_encode($resp);
            exit;
          }
        }

        $validaCodigo  = $db_itens->fetchRow("CODIGO = '{$codigo}' {$idwhereValidar}  ");

        if(!empty($validaCodigo)){
          $resp['msg'] = 'Não foi possivel salvar pois o codigo ' . $codigo .' já foi cadastrado. ';
          $resp['status'] = '0';
          http_response_code(300);
          echo json_encode($resp);
          exit;
        }
       

        $itenInsert['NOME'] = $nome;
        $itenInsert['CODIGO'] = $codigo;
        $itenInsert['VALOR'] = $valor;
        if(empty($id)){
          $db_itens->insert($itenInsert);
        }
        else{
          $db_itens->update($itenInsert,"ID = {$id}");
        }

        $produto  = $db_itens->fetchRow("NOME = '{$nome}' AND VALOR = '{$valor}' AND CODIGO = '{$codigo}'  ");

        $resp['msg'] = 'Produto ' . $nome .' criado custando $J:'.number_format($valor, 2, ',', '.').' juniedas. ';
        $resp['status'] = '1';
        $resp['dadosProduto'] = $produto;
        http_response_code(201);
        echo json_encode($resp);
        exit;
       
      }
      $resp['msg'] = 'Acesso Negado';
      $resp['status'] = '-1';
      http_response_code(300);
      echo json_encode($resp);
      exit;
     
    }

    public function removeritem($id_item){
      if($this->_user['ADMIN']){
        $db_itens = new dataBase('ITEMS');
        $produto  = $db_itens->fetchRow(" ID = '{$id_item}' ");
        if(!empty($produto)){
      
          $db_itens->delete("ID = '{$id_item}'");
          $resp['msg'] = 'Produto ' . $produto['NOME'] .'  Apagado do sistema ';
          $resp['status'] = '1';
          $resp['dadosProduto'] = $produto;
          http_response_code(201);
          echo json_encode($resp);
          exit;
         
        }
        $resp['msg'] = 'Produto não localizado.';
        $resp['status'] = '0';
        http_response_code(300);
        echo json_encode($resp);
        exit;
      }
      $resp['msg'] = 'Acesso Negado';
      $resp['status'] = '-1';
      http_response_code(300);
      echo json_encode($resp);
      exit;
     
    }

    public function getItem($id_item=null,$codigo=null){
      $db_itens = new dataBase('ITEMS');
      if(!empty($id_item)){
        $produto  = $db_itens->fetchRow(" ID = '{$id_item}' ");
      }
      else{
        $produto  = $db_itens->fetchRow(" CODIGO = '{$codigo}' ");
      }
      if(!empty($produto)){
        $resp['msg'] = 'Produto ' . $produto['NOME'] .'  custando $J:'.number_format($produto['VALOR'], 2, ',', '.').' juniedas. ';
        $resp['status'] = '1';
        $resp['dadosProduto'] = $produto;
        http_response_code(201);
        echo json_encode($resp);
        exit;
       
      }
      $resp['msg'] = 'Produto não localizado.';
      $resp['status'] = '0';
      http_response_code(300);
      echo json_encode($resp);
      exit;
     
    }
    
    public function getItens(){
      if($this->_user['ADMIN']){
        
        $db_itens = new dataBase('ITEMS');
        $produtos  = $db_itens->fetchAll();
      

        $resp['msg'] = 'Lista de produtos ';
        $resp['status'] = '1';
        $resp['dadosProdutos'] = $produtos;
        http_response_code(201);
        echo json_encode($resp);
        exit;
       
      }
      $resp['msg'] = 'Acesso Negado';
      $resp['status'] = '-1';
      http_response_code(300);
      echo json_encode($resp);
      exit;
     
    }

    public function compraritem($id_item){
      $db_itens = new dataBase('ITEMS');
      $db_compras = new dataBase('COMPRAS');
      $produto  = $db_itens->fetchRow(" ID = '{$id_item}' ");
      if(!empty($produto)){
        
        if( $this->_user['SALDO'] < $produto['VALOR'] ){
          $resp['msg'] = 'Saldo insuficiente.';
          $resp['status'] = '0';
          http_response_code(300);
          echo json_encode($resp);
          exit;
        }
        $compraInset['ID_USUARIO'] = $this->_user['ID'];
        $compraInset['ID_ITEM'] = $id_item;
        $db_compras->insert($compraInset);
      
        $db_user = new dataBase('USUARIO');
        $this->_user['SALDO'] -= $produto['VALOR'];
        $db_user->update($this->_user,"ID = {$this->_user['ID']}");

        $resp['msg'] = 'Produto ' . $produto['NOME'] .'  foi comprado por  $J:'.number_format($produto['VALOR'], 2, ',', '.').' juniedas. Saldo atual de  J$ ' .number_format($this->_user['SALDO']);
        $resp['status'] = '1';
        $resp['dadosProduto'] = $produto;
        http_response_code(201);
        echo json_encode($resp);
        exit;
       
      }
      $resp['msg'] = 'Produto não localizado.';
      $resp['status'] = '0';
      http_response_code(300);
      echo json_encode($resp);
      exit;
     
    }

    public function getItensComprados(){
      $db_compra = new dataBase();
      $produtos  = $db_compra->fetchAll(" SELECT * FROM COMPRAS AS COMPRAS
                                            JOIN ITEMS AS ITEMS ON ITEMS.ID = COMPRAS.ID_ITEM
                                            WHERE COMPRAS.ID_USUARIO =  '{$this->_user['ID']}'");
      if(!empty($produtos)){
        
        $resp['msg'] = 'Lista de produtos comprados ';
        $resp['status'] = '1';
        $resp['dadosProdutosComprados'] = $produtos;
        http_response_code(201);
        echo json_encode($resp);
        exit;
       
      }
      $resp['msg'] = 'Não a Produtos Comprados no momento.';
      $resp['status'] = '0';
      http_response_code(300);
      echo json_encode($resp);
      exit;
     
    }

}



