<?php 
class dataBase {
    
    private $table = '';//nome da tabela 
    private $connect = [];//conexão do banco de dados 
 

    public function __construct($tabela = '', $client =false) {
        
        $system = new system();
        
        
        $this->connect = $system->con;
        
        if( !empty($tabela) )
            $this->table = $tabela;
    }

    //get a conexão para utilizar funções do mysqli 
    public function getConnect(){
        
        return $this->connect;

    }

    //query (realmente passar qualquer coisa de banco de dados)
    public function query($query = ''){

        return mysqli_query($this->connect, $query);

    }

    // select 
    public function fetchRow($where_or_select = '',$group_by = '',$order_by = '', $asc_desc = ''){
        
        if(!empty($this->table)){
               //condições da busca no banco de dados 
               $where   = !empty($where_or_select)?"WHERE {$where_or_select}":" ";

               //agrupamento de informações 
               $groupBy = !empty($group_by)?"GROUP BY {$group_by}":" ";
   
               //ordenação das informações 
               $orderby = !empty($group_by)?"ORDER BY {$order_by} {$asc_desc}":" ";

   
               //montar a query para o banco de dados 
               $query   = " SELECT * FROM {$this->table} {$where} {$groupBy} {$orderby} ;";     
        }
        else
            $query = $where_or_select;
        
        
        $result = mysqli_query($this->connect, $query);
        
        return  mysqli_fetch_assoc($result);
        
    }
    
    public function fetchAll($where_or_select = '',$group_by = '',$order_by = '', $asc_desc = '',$limit = '', $offset = ''){
        
        if(!empty($this->table)){

            //condições da busca no banco de dados 
            $where   = !empty($where_or_select)?"WHERE {$where_or_select}":" ";

            //agrupamento de informações 
            $groupBy = !empty($group_by)?"GROUP BY {$group_by}":" ";

            //ordenação das informações 
            $orderby = !empty($group_by)?"ORDER BY {$order_by} {$asc_desc}":" ";

            //limit de informações 
            if(!empty($offset)){
                $limit_offset = !empty($group_by)?"LIMIT {$offset},{$limit}":" ";           
            }
            else{
                $limit_offset = !empty($group_by)?"LIMIT {$limit}":" ";
            }   

            //montar a query para o banco de dados 
            $query   = " SELECT * FROM {$this->table} {$where} {$groupBy} {$orderby} {$limit_offset};";     
        }
        else
            $query = $where_or_select;
        

        
        $result = mysqli_query($this->connect, $query);
        return  mysqli_fetch_all($result,MYSQLI_ASSOC);
        
    }


    //insert
    public function insert($params = []){

        if(empty($params))
            return "ERRO VOCE TA TENTANDO INSERIR DADOS VARIOS NA TABELA;";
        else if(!empty($this->table)){

            $result = mysqli_query($this->connect, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}';");

            $_colunas_existentes =  mysqli_fetch_all($result);

            $params_insert = [];
            foreach($_colunas_existentes as $val ){
                if(!is_null(@$params[$val[0]])){
                    $params_insert[$val[0]] = "'".$params[$val[0]]."'";
                }
            }
                
            if(empty($params_insert))
                return "ERRO VOCE TA TENTANDO INSERIR DADOS VARIOS NA TABELA;";

            $query = "INSERT INTO {$this->table} (".implode(',',array_keys($params_insert)).") VALUES ( ".implode(',',$params_insert).")";   
        }
        else
            return "ERRO INFORME A TABELA QUE SERÁ FEITA ESSA EXECUÇÃO!!!!;";
        
        
            
        mysqli_query($this->connect, $query);
        
        return "SUCCESS";
    }

    //update
    public function update($params = [],$where){


        if(empty($where))
            return "ERRO VOCE TA TENTANDO UPDATE TODAS AS INFORMAÇÕES DA TABELA;";
        else {
            
            if(empty($params))
                return "ERRO VOCE TA TENTANDO INSERIR DADOS VARIOS NA TABELA;";
            else if(!empty($this->table)){

                $result = mysqli_query($this->connect, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$this->table}';");

                $_colunas_existentes =  mysqli_fetch_all($result);

                $params_insert = [];
                foreach($_colunas_existentes as $val ){
                    if(!is_null(@$params[$val[0]])){
                        if(empty(@$params[$val[0]]))
                            $params_insert[$val[0]] = $val[0] ." = NULL ";
                        else
                            $params_insert[$val[0]] = $val[0] ." = '". $params[$val[0]] ."' ";
                    }
                }
                    
                if(empty($params_insert))
                    return "ERRO VOCE TA TENTANDO INSERIR DADOS NULOS NA TABELA;";

                $query = "UPDATE {$this->table} SET ".implode(',',$params_insert)." WHERE {$where} ";   
                
            }
            else
                return "ERRO INFORME A TABELA QUE SERÁ FEITA ESSA EXECUÇÃO!!!!;";
    
        }


        
            
        mysqli_query($this->connect, $query);
        
        return "SUCCESS";
    }


    //delete
    public function delete($where){
        
        if(empty($where))
            return "ERRO VOCE TA TENTANDO EXCLUIR TODAS AS INFORMAÇÕES DA TABELA;";
        else if(!empty($this->table))
            $query = " DELETE FROM  {$this->table}  WHERE {$where} ;";   
        else
            return "ERRO INFORME A TABELA QUE SERÁ FEITA ESSA EXECUÇÃO!!!!;";
        
        
        mysqli_query($this->connect, $query);
        
        return "SUCCESS";
    }


    //drop
    public function drop(){
        
        if(!empty($this->table))
            $query = " DROP TABLE {$this->table} ;";   
        else
            return "ERRO INFORME A TABELA QUE SERÁ FEITA ESSA EXECUÇÃO!!!!;";
        
        
        mysqli_query($this->connect, $query);
        
        return "SUCCESS";
    }
    

    
}




?>
