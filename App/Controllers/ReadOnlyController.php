<?php

namespace App\Controllers;

use ErrorException;
use Core\Model;
use Core\Request;
use Core\Traits\HyperMedia;
use Core\Storage;

class ReadOnlyController{

    use HyperMedia;

    const  MODEL_NAMESPACE = "App\\Models\\";

    protected $request;
    protected $guard;
    protected $mapping = [];
    protected $attr_table = [];
    protected $connection_table = [];


    public function __construct(Request $_request)
    {
        $this->connection_table = config("app.connection_table");
        $this->request = $_request;
        $this->getAllTables();
    }


    protected function getAllTables(){
        
        $dir= config("app.model_dir");
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                $file = substr($file,0,-4);
                if($file){
                    $class = self::MODEL_NAMESPACE.$file;
                    $table = Model::getTableName($class);
                    $this->mapping[$table]=[$class];
                }
            }
            closedir($dh);
        }
    }

    

    protected function get()
    {
        $this->setOffestandLimit();
        $query_string = $this->removeOffsetAndLimitFromQString($this->request->getQueryStrings());
        $path = $this->request->getPath();
        $this->getIdAttrTable($path,$query_string);
        $uri = "";
        $table_conn=false;
        $output = array(
            "links" => []
        );
        $ids=[];
        try {
            do {
                $current_id = current($path);
                $current_tbl = key($path);
                $next_id = next($path);
                if ($next_id === false) {
                    //last collection
                    if($current_id !== null){
                        $model = new $this->mapping[$current_tbl][0]();
                        $data = $model->read($current_id);
                        //cashe
                        $etag = md5(serialize($data));
                        header("Etag: $etag");
                        if($this->conditonGetCheck($etag)) return;
                        //end cashe
                        $data=$this->formatAttributesFromItem($data,$current_tbl);
                        $output = array_merge($output,$data);
                        $this->putEmbeddedForeignAttr($output,$current_tbl,$data);
                    } else {
                        $model = new $this->mapping[$current_tbl][0]();
                        $data = $model->read($current_id,$query_string,$ids);
                        //cashe
                        $etag = md5(serialize($data));
                        header("Etag: $etag");
                        if($this->conditonGetCheck($etag)) return;
                        //end cashe
                        $data=$this->formatAttributesFromCollection($data,$current_tbl);
                        $output = array_merge($output,$data);
                        $this->putOffestandLimit($output);
                    }
                    $this->putHAL($output,$path,$current_tbl,$this->connection_table);
                    break;
                }
                $next_tbl = key($path);
                $uri .= "/" . $current_tbl;
                if($current_id != null) $uri.= "/" . $current_id;
                $model = new $this->mapping[$current_tbl][0]((int)$current_id);
                $data = (array) $model->read((int)$current_id);
                $data=$this->formatAttributesFromItem($data,$current_tbl);
                $this->putEmbeddedConnectionsRes($output, $current_tbl, $data, [$current_tbl=> $current_id]);
                $table_conn = false;
                foreach ($this->connection_table as $conn => $tbls) {
                    if (key_exists($current_tbl, $tbls) && key_exists($next_tbl, $tbls)) {
                        $table_conn = $conn;
                        break;
                    }
                }
                if ($table_conn === false){
                    $msg = preg_replace('/{collection1}/', $current_tbl, error(401));
                    $msg = preg_replace('/{collection2}/', $next_tbl, $msg);
                    $msg = preg_replace('/{path}/', $uri, $msg);
                    throw new ErrorException($msg, 401);
                }
                $ids = $model->getFromOtherTable($table_conn, $this->connection_table[$table_conn][$next_tbl], $next_id);
                if(empty($ids)) {
                    $msg = preg_replace('/{id}/', $current_id, error(402));
                    $msg = preg_replace('/{current}/', $current_tbl, $msg);
                    $msg = preg_replace('/{next}/', $next_tbl, $msg);
                    throw new ErrorException($msg, 402);
                }

            } while (true);
        } catch (ErrorException $e) {
            throw $e;
        }
        return $output;
        
    }

    //hal
    private function putHAL(&$_out, $_uri, $_table, $_conn_tables)
    {

        $id = $_uri[$_table];
        foreach ($_conn_tables as $tbl => $attrs) {
            if (array_key_exists($_table, $attrs))
                $_out["links"] = $this->getLinks($_uri, $tbl, $attrs);
        }

        $_out["links"] = array_merge($this->getNextPreviousItems($this->mapping[$_table][0], $id), $_out["links"]);
    }

    //embedded atr
    private function putEmbeddedForeignAttr(&$_out, $_table, $_data)
    {
        $class = new $this->mapping[$_table][0]();
        $rules = $class::rules();
        $foreigns = [];
        foreach ($rules as $attribute => $rule) {
            if (is_null($rule)) continue;
            if (array_key_exists("foreign_key", $rule) && !array_key_exists("table_attribute_id",$rule)) array_push($foreigns, $attribute);
        }
        $out = [];
        foreach ($foreigns as $foreign_atrr) {
            $f_out = array(
                "links" => []
            );
            $f_id = $_data[$foreign_atrr];
            $f_table = substr($foreign_atrr, 0, -2);
            $f_table = strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $f_table));
            $model = new $this->mapping[$f_table][0]();
            $f_data = $model->read($f_id);
            foreach ($f_data as $attribute => $value) {
                $f_out[$attribute] = $value;
            }
            $this->putHAL($f_out, [$f_table => $f_id], $f_table, $this->connection_table);
            array_push($out, [$f_table => $f_out]);
        }
        if (!empty($out)) $_out = array_merge_recursive($_out, ["embedded" => $out]);
    }

    //embedded res
    private function putEmbeddedConnectionsRes(array &$_out, $_table, $_data, $_uri)
    {
        if (empty($_data)) return;
        $r_out = array(
            "links" => []
        );
        $this->putHAL($r_out,$_uri,$_table,$this->connection_table);
        $r_out = array_merge($r_out,$_data);
        $_out=array_merge_recursive($_out,["embedded" => [$_table => $r_out]]);

    }

    //set limit and offset
    private function setOffestandLimit(){
        $params = $this->request->getPath();
        if(!is_null(end($params))) return;
        $offset = \Core\Model::getPointerOfCollection();
        $limit =  \Core\Model::getLenghtOfCollection();
        $q_strings = $this->request->getQueryStrings();
        if(array_key_exists("limit",$q_strings)) {
            $limit = (int)$q_strings["limit"];
            \Core\Model::setLenghtOfCollection($limit);
            $offset -= $offset%$limit;
            $offset = max($offset,0);
            \Core\Model::setPointerOfCollection($offset);
        }
        if(array_key_exists("page",$q_strings)){
            $page = $q_strings["page"];
            if(is_numeric($page) && (int)$page > -1){
                \Core\Model::setPointerOfCollection((int)$page*$limit);
            }
        }

    }


    //put limit and offset
    private function putOffestandLimit(array &$_out){
        $offset = \Core\Model::getPointerOfCollection();
        $limit = \Core\Model::getLenghtOfCollection();
        $_out["page"] = intdiv($offset,$limit);
        $_out["limit"] = $limit;
    }

    //remove limit and offset
    private function removeOffsetAndLimitFromQString(array $_condtion){
        if(key_exists("limit",$_condtion)) unset($_condtion["limit"]);
        if(key_exists("page",$_condtion)) unset($_condtion["page"]);
        return $_condtion;
    }
    //format attr_tables
    private function formatAttributesFromItem(array $data, $table){
        $class = $this->mapping[$table][0];
        $rules = $class::rules();
        array_walk($rules,function($rls,$attr){
            if(!is_null($rls) && array_key_exists("table_attribute_id",$rls)){
                array_push($this->attr_table,$attr);
            }
        });
        if(empty($this->attr_table)) return $data;
        foreach($this->attr_table as $attr){
            $attr_table = substr($attr, 0, -2);
            $attr_table = strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $attr_table));
            if(!Storage::exists($attr_table)){
                $attr_model = new $this->mapping[$attr_table][0]();
                $attr_class = $this->mapping[$attr_table][0]::getClassName($this->mapping[$attr_table][0]);
                $attr_data = $attr_model->read();
                Storage::put($attr_table,$attr_data);
            } else {
                $attr_class = $this->mapping[$attr_table][0]::getClassName($this->mapping[$attr_table][0]);
                $attr_data = Storage::get($attr_table);  
            }
            $attr_item = array_search($data[$attr],array_column($attr_data,$attr));
            unset($data[$attr]);
            $data[$attr_class]=$attr_data[$attr_item][$attr_class];
        }
        $this->attr_table =[];
        return $data;
       
    }

    private function formatAttributesFromCollection(array $data, $table){
        reset($data);
        foreach($data as $key=>$dta){
            $dta=$this->formatAttributesFromItem($dta,$table);
            $data[$key]=$dta;
        }
        return $data;
    }

    protected function getIdAttrTable($uri,&$params){
        if(empty($params)) return;
        $this->attr_table =[];
        $table = array_key_last($uri);
        $class = $this->mapping[$table][0];
        $rules=$class::rules();
        array_walk($rules,function($rls,$attr){
            if(!is_null($rls) && array_key_exists("table_attribute_id",$rls)){
                array_push($this->attr_table,$attr);
                $this->attr_table=array_unique($this->attr_table);
            }
        });
        if(empty($this->attr_table)) return;
        foreach($this->attr_table as $attr){
            $attr_class = substr($attr, 0, -2);
            if(empty($params[$attr_class])) break;
            $attr_table = strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $attr_class));
            if(!\Core\Storage::exists($attr_table)){
                $attr_model = new $this->mapping[$attr_table][0]();
                $attr_data = $attr_model->read();
                Storage::put($attr_table,$attr_data);
            } else {
                $attr_data = Storage::get($attr_table);  
            }
            $attr_item = array_search($params[$attr_class],array_column($attr_data,$attr_class));
            unset($params[$attr_class]);
            $params[$attr]=$attr_data[$attr_item][$attr];
        }
        $this->attr_table =[];
    }

    protected function conditonGetCheck($_etag){
        if(isset($_SERVER['HTTP_IF_NONE_MATCH'])  && $_SERVER['HTTP_IF_NONE_MATCH'] == $_etag) {
            header('HTTP/1.1 304 Not Modified', true, 304);
            return true;
        } else return false;
    }
}
