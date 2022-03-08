<?php

namespace Core;

use \App\App;
use ErrorException;

/**
 *  Request
 * 
 * PHP 7.3.28
 */
class Request{

    
    protected $header;

    protected $method = "GET";

    protected $uri = [];

    protected $params = [];

    protected $query_string = [];


    function __construct()
    {
        $this->header = getallheaders();

        if(array_key_exists('Content-Type', $this->header) && preg_match('/multipart\/form-data/',$this->header['Content-Type'] ))
        {
            // Ne podrzava multipart/form-data format tela zahteva
            throw new \ErrorException(error(100),100);
        }

        $this->method = $_SERVER['REQUEST_METHOD'];
  
        $this->uri = $this->setPath();

        $this->params = $this->setParams();

        $this->query_string = $this->setQueryStrings();
        
    }

    /**
     * Dodeljuje vrednosti polja @var paramas na osnovu prosledjenih parametra u telu 
     * zahteva klijenta
     * 
     * @return array
     */
    protected function setParams(){

        $tmp = [];

        $input  = file_get_contents('php://input','r');
        if(isset($input) && $input != '')
        {         
            $tmp = json_decode($input, true,JSON_NUMERIC_CHECK);
        }
        return $tmp;
    }


    /**
     * 
     * @return array, elemnti niza su delovi URI putanje, formata {kolekcija} => {id},
     * gde svaki sledeci element niza je deo putanje u nastavku predhodog elementa
     */
    protected function setPath()
    {
        $res = [];
        $uri = $_SERVER['REQUEST_URI'];
        $tmp = strpos($uri,config('app.base_path'));
        if($tmp!==1) throw new ErrorException(error(400),400);
        $uri = substr($uri, strlen(config('app.base_path')));
        $uri = explode('?', $uri);
        $uri = $uri[0];
        $uri=ltrim($uri,'/');
        if($uri==='') return $res;
        while(true){
            $tmp = explode('/',$uri,2);
            if($tmp[0]==='shema'){
                if(isset($tmp[1]))
                    $res[$tmp[0]]=$tmp[1];
                else
                    $res[$tmp[0]] = null;
                break;
            }
            $res[$tmp[0]] = null;
            if(!isset($tmp[1])) break;
            $tmp = explode('/',$tmp[1],2);
            if(is_numeric($tmp[0]))
            {
                $res[array_key_last($res)] = $tmp [0];
                if(!isset($tmp[1])) break;
            } else {
                throw new \ErrorException(error(101),101);
            }
            $uri = $tmp[1];
        };
        return $res;
    }

    /**
     * @return array of parametras of URL query strings
     */
    protected function setQueryStrings()
    {
        $res = [];
        $query_string= explode('?', $_SERVER['REQUEST_URI']);
        if(!isset($query_string[1])) return $res;
        $query_string = $query_string[1];
        try
        {
            $params = explode('&',$query_string);
            foreach($params as $value)
            {
                preg_match('/^([^=]+)=([^=]+)$/', $value, $match);
                $res[$match[1]] = $match[2];
            }
        }
        catch(\ErrorException $err){
            $msg = error(102)." -> ".$err->getMessage();
            throw new \ErrorException($msg,102);
        }
        return $res;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getPath()
    {
        return $this->uri;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getQueryStrings()
    {
        return $this->query_string;
    }


}