<?php

namespace App\Controllers;

use App\Views\BaseView;
use ErrorException;

class ShemaController
{
    protected $request;
    protected $tables =[];

    public function __construct($_request)
    {
        $this->request=$_request;
        $this->getAllTables();
    }


    public function response(){
        $data = $this->getShema();
        $view = new BaseView($data);
        $view->send();
    }

    protected function getAllTables()
    {
        $namespase = config("app.model_namespace");
        $dir = config("app.model_dir");
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                $file = substr($file, 0, -4);
                if ($file) {
                    $class = $namespase . $file;
                    array_push($this->tables,\Core\Model::getTableName($class));
                }
            }
            closedir($dh);
        }
    }

    private function getShema(){
        $path=$this->request->getPath();
        $table=current($path);
        if(is_null($table))
            return config("shema.index");
        else if(in_array($table,$this->tables)) {
            if(config("shema.".$table)===false) {
                $msg = preg_replace('/{table}/',"'".$table."'",error(600));
                throw new ErrorException($msg,600);
            } else   
                return config("shema.".$table);
        } else 
            throw new ErrorException(error(400),400);
     
    }
}
