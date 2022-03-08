<?php

namespace App\Controllers;

use ErrorException;
use App\Views\BaseView;

class BaseController extends ReadOnlyController{


    public function __construct($_request)
    {
        parent::__construct($_request);
    }

    public function response(){
        $data = $this->execute();
        $view = new BaseView($data);
        $view->send();
    }


    protected function execute(){
        $method = $this->request->getMethod();
        switch($method){
            case "GET":{
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Headers: access");
                header("Access-Control-Allow-Methods: GET");
                header("Access-Control-Allow-Credentials: true");
                header('Cache-Control: max-age=86400');
                header('Content-Type: application/json');
                http_response_code(200);
                return $this->get();  
            }
            case "POST":{
                header("Access-Control-Allow-Origin: *");
                header("Content-Type: application/json; charset=UTF-8");
                header("Access-Control-Allow-Methods: POST");
                header("Access-Control-Max-Age: 3600");
                header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
                http_response_code(201);
                $this->post();
                break;
            }
            case "PUT":{
                header("Access-Control-Allow-Origin: *");
                header("Content-Type: application/json; charset=UTF-8");
                header("Access-Control-Allow-Methods: PUT");
                header("Access-Control-Max-Age: 3600");
                header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
                $this->put();
                http_response_code(200);
                break;
            }
            case "DELETE":{
                header("Access-Control-Allow-Origin: *");
                header("Content-Type: application/json; charset=UTF-8");
                header("Access-Control-Allow-Methods: POST");
                header("Access-Control-Max-Age: 3600");
                header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
                http_response_code(200);
                $this->delete();
                break;
            }
        }
    }


    protected function post(){
        $path = $this->request->getPath();
        reset($path);
        $table = key($path);
        if(count($path) !== 1) {
            $msg = preg_replace('/{table}/',array_key_last($path),error(403));
            throw new ErrorException($msg,403);
        }
        if(current($path)!== null){
            $msg = preg_replace('/{id}/',current($path),error(404));
            $msg = preg_replace('/{table}/',$table,$msg);
            throw new ErrorException($msg,404);
        }
        $row_data = $this->request->getParams();
        $this->getIdAttrTable($path,$row_data);
        $model = new $this->mapping[$table][0]();
        //secure not idempotent
        $check=$model->read(null,$row_data);
        if(!empty($check)){
            header('HTTP/1.1 304 Not Modified', true, 304);
            return;
        }
        foreach($row_data as $attr => $value){
            if(property_exists($model,$attr)){
                $model->$attr=$value;
            } else {
                $msg = preg_replace('/{property}/',$attr,error(405));
                $msg = preg_replace('/{table}/',$table,$msg);
                throw new ErrorException($msg,405);
            }
        }
        return $model->create();
    }

    protected function put(){
        $path = $this->request->getPath();
        reset($path);
        $table = key($path);
        if(count($path) !== 1) {
            $msg = preg_replace('/{table}/',array_key_last($path),error(403));
            throw new ErrorException($msg,403);
        }
        if(current($path) === null ){
            $msg = preg_replace('/{table}/',$table,error(406));
            throw new ErrorException($msg,406); 
        }
        $row_data = $this->request->getParams();
        $this->getIdAttrTable($path,$row_data);
        $model = new $this->mapping[$table][0]((int)current($path));
        foreach($row_data as $attr => $value){
            if(property_exists($model,$attr)){
                $model->$attr=$value;
            } else {
                $msg = preg_replace('/{property}/',$attr,error(405));
                $msg = preg_replace('/{table}/',$table,$msg);
                throw new ErrorException($msg,405);
            }
        }
        return $model->update();
    }

    protected function delete(){
        $path = $this->request->getPath();
        reset($path);
        $table = key($path);
        if(count($path) !== 1) {
            $msg = preg_replace('/{table}/',array_key_last($path),error(403));
            throw new ErrorException($msg,403);
        }
        if(current($path) === null ){
            $msg = preg_replace('/{table}/',$table,error(406));
            throw new ErrorException($msg,406); 
        }
        $model = new $this->mapping[$table][0]((int)current($path));

        return $model->delete();
    }

}