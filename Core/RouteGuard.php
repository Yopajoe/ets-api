<?php

namespace Core;

use Core\Request;
use ErrorException;


class RouteGuard {


    protected $mapping =[];
    private $path;
    protected $request;


    public function __construct(Request $req)
    {  
        $this->request = $req;
        $this->path = $this->request->getPath();
        $this->getAllTabels();
        
    }

    protected function getAllTabels(){
        $model_namespace = config("app.model_namespace");
        $dir= config("app.model_dir");
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                $file = substr($file,0,-4);
                if($file){
                    $class = $model_namespace.$file;
                    $table = Model::getTableName($class);
                    $this->mapping[$table]=[$class];
                    array_push($this->mapping[$table],...$this->getFields($class));

                }
            }
            closedir($dh);
        }
    }

    private function getFields($class){
        $model = new $class();
        $rules = $class::rules();
        $attributes = [];
        foreach($model as $attribute => $value){
            if(!is_null($rules[$attribute]) && key_exists("table_attribute_id",$rules[$attribute])) 
                $attribute = substr($attribute,0,-2);
            array_push($attributes,$attribute);
        }
        return $attributes;
    }

    public function validate(){
        
        if(array_key_first($this->path)==="shema")
            if(is_null($this->path["shema"])) return;
            else if(!key_exists($this->path["shema"],$this->mapping))
                throw new ErrorException(error(400),400);
            else return;
     
         
    
        array_walk($this->path, function ($v,$k){
            if(!key_exists($k,$this->mapping))
                throw new ErrorException(error(400),400);
        });
    }
    
}