<?php
namespace Core;

use ErrorException;
use App\App;

abstract class Model implements ICrud {


    protected static  $lenght_collection = 5;
    protected static  $pointer_collection = 0;

    protected const _RULES_REQUIRED_ = 'required';
    protected const _RULES_UNIQUE_ = 'unique';
    protected const _RULES_PRIMARY_KEY_ = 'primary_key';
    protected const _RULES_FOREIGN_KEY_ = 'foreign_key';
    protected const _RULE_DOMAIN_ = 'domain';
    protected const _RULE_AUTOINCREMENT_ = 'auto_increment';
    protected const _RULE_TABLE_KEY_ATTRIBUTE_ = 'table_attribute_id'; 

    abstract protected static function rules();

    abstract protected static function validate(string $attribute, $value);


    public static function setPointerOfCollection(int $pointer)
    {
        if($pointer>-1){
            self::$pointer_collection = $pointer;
        } else {
            self::$pointer_collection = 0;
        }
    }

    public static function getPointerOfCollection()
    {
        return self::$pointer_collection;
    }

    public static function setLenghtOfCollection(int $num)
    {
        self::$lenght_collection = $num;
    }

    public static function getLenghtOfCollection()
    {
        return self::$lenght_collection;
    }

    public static function getTableName(string $class):string
    {
        $string = substr($class, strrpos($class,'\\')+1);
        $string = preg_replace('/(.)([A-Z])/', '$1_$2', $string );
        return strtolower($string);
    }

    public static function getClassName(string $class):string
    {
        return substr($class, strrpos($class,'\\')+1);
    }

    public static function getAttrIdName(string $class):string
    {
       return self::getClassName($class)."ID";
    }

    protected function setAllAtributes(int $id)
    {
        $class = get_called_class();
        $rules = $class::rules();
        $res = $this->read($id);
        foreach($this as $attribute => $value)
        {
            if(!is_null($res[$attribute]) && !is_null($rules[$attribute])) 
            {
                if(key_exists($attribute,$rules) && key_exists('domain',$rules[$attribute]))
                {
                    $domen = $rules[$attribute]['domain'];
                    if(key_exists('type',$domen) && $domen['type']=== 'decimal')
                    {
                        $res[$attribute] = (float)$res[$attribute];
                    }
                }
                $this->$attribute = $res[$attribute];
            }
        }
    }


    //read
    public  function read(int $_id = null, array $condition = [], array $ids=[])
    {
        $class = get_called_class();
        $tableName = self::getTableName($class);
        $id = self::getAttrIdName($class);
        try
        {
            if($_id === null)
            {     
                $sql = "SELECT * FROM $tableName";
                if(!empty($ids)){
                    $sql .= " WHERE $id IN (".implode(",",$ids).")";
                }
                if(!empty($condition)){
                    $sql .= empty($ids)?" WHERE ":" AND ";
                    $array_of_keys_condition = array_keys($condition);
                    $sql .= implode(" AND ", array_map("self::formatCondition", $array_of_keys_condition, $condition ));
                }
                $rules = $class::rules();
                if (!(count($rules) === 2 && $rules[$class]["type"] = "enum")) {
                    $sql .= " LIMIT :offset, :lenght;";
                    $stmt = App::$app->db->prepare($sql);
                    $stmt->bindParam(':offset', self::$pointer_collection, \PDO::PARAM_INT);
                    $stmt->bindParam(':lenght', self::$lenght_collection, \PDO::PARAM_INT);
                } else {
                    $stmt = App::$app->db->prepare($sql);
                }
                if ($stmt->execute()) {
                    $res = $stmt->fetchAll();
                    return $res;
                } else {
                    throw new ErrorException(error(201), 201);
                }
            } else {
                $sql = "SELECT * FROM $tableName WHERE $id = :id ;";
                $stmt = App::$app->db->prepare($sql);
                $stmt->bindParam(':id', $_id, \PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetch();
                if($res !== false)
                {
                    return $res;
                } else {
                    throw new ErrorException(error(202),202);
                }
            }
        }
        catch (\PDOException $e)
        {
            $msg = error(200)."  ".$e->getCode().":".$e->getMessage();
            throw new ErrorException($msg,200);
        }
        catch (ErrorException $e)
        {
            switch($e->getCode())
            {
                case 201:
                    $msg = preg_replace('/{table}/', "\"".$tableName."\"", $e->getMessage());
                    throw new ErrorException($msg,201);
                    break;
                case 202:
                    $msg = preg_replace('/{value}/', $_id, $e->getMessage());
                    $msg = preg_replace('/{key}/',$id, $msg);
                    throw new ErrorException($msg, 202);
            }
        }
        
    }

    //create
    public function create()
    {
        $id_name = self::getAttrIdName(get_called_class());
        $classname = self::getClassName(get_called_class());
        foreach($this as $attribute => $value)
        {
           
                $validation = $this->validate($attribute,$value);
                if(!$validation[0])
                {
                    $failures = implode(", ",$validation[2]);
                    $msg = preg_replace('/{value}/', "\"".$value."\"", error(301));
                    $msg = preg_replace('/{proprety}/', "\"".$attribute."\"", $msg);
                    $msg = preg_replace('/{validation}/', "\"".$failures."\"", $msg);
                    $msg = preg_replace('/{class}/', "\"".$classname."\"", $msg);
                    throw new ErrorException($msg,301);
                }
                $data[$attribute]=$value;
                
        }
        $tableName = self::getTableName(get_called_class());
        unset($data[$id_name]);
        $array_of_keys = array_keys($data);
        $sql = "INSERT INTO $tableName  SET ".implode(',', array_map( function ($key) { return $key.'=:'.$key; } , $array_of_keys )).";";
        $stmt = App::$app->db->prepare($sql);
        foreach($data as $attribute => $value){
            //if(is_string($value)) $value = App::$app->db->quote($value);
            $data[':'.$attribute] = $value;
            unset($data[$attribute]);
        } 
        $stmt->execute($data);
        return true;
    }

    //update
    public function update()
    {
        $id_name = self::getAttrIdName(get_called_class());
        $classname = self::getClassName(get_called_class());
        $data = array();
        foreach($this as $attribute => $value)
        {
           
                $validation = $this->validate($attribute,$value);
                if(!$validation[1])
                {
                    $failures = implode(", ",$validation[2]);
                    $msg = preg_replace('/{value}/', "\"".$value."\"", error(301));
                    $msg = preg_replace('/{proprety}/', "\"".$attribute."\"", $msg);
                    $msg = preg_replace('/{validation}/', "\"".$failures."\"", $msg);
                    $msg = preg_replace('/{class}/', "\"".$classname."\"", $msg);
                    throw new ErrorException($msg,301);
                }
                $data[$attribute]=$value;
                
        }
        $tableName = self::getTableName(get_called_class());
        unset($data[$id_name]);
        $array_of_keys = array_keys($data);
        $sql = "UPDATE $tableName  SET ".implode(',', array_map( function ($key) { return $key.'=:'.$key; } , $array_of_keys ))." WHERE $id_name=:$id_name;";
        $stmt = App::$app->db->prepare($sql);
        foreach($data as $attribute => $value){
            //if(is_string($value)) $value = App::$app->db->quote($value);
            $data[':'.$attribute] = $value;
            unset($data[$attribute]);
        } 
        $data[":".$id_name] = $this->$id_name;
        $stmt->execute($data);
        return true;

    }

    //delete
    public function delete()
    {
        $id_name = self::getAttrIdName(get_called_class());
        $id = $this->$id_name;
        $tableName = self::getTableName(get_called_class());
        $sql = "DELETE FROM $tableName WHERE $id_name = $id;";
        $stmt = App::$app->db->prepare($sql);
        return $stmt->execute();
    }

    //join
    /**
     * @param id is value of id attribute of @param attribute in @param othertable.
     * 
     * if @param id is set to null then function @return array of @param attribute
     * with limit @var lenght_collection and @var pointer_collection. 
     * 
     * Opposite of latter @return int,  if id exists in table otherwise @return false
     *     
     */
    public function getFromOtherTable($othertable, $attribute, $id=null)
    {
        $table = self::getTableName(get_called_class());
        $id_name = self::getAttrIdName(get_called_class());
        $otherclass = preg_replace_callback(
            '/(.)_([a-z])/',
             function ($matches){
                return $matches[1].strtoupper($matches[2]);
             },
             $othertable);
        $otherclass = "\\App\\Models\\".ucfirst($otherclass);
        if(!(class_exists($otherclass) && property_exists($otherclass, $attribute))) return false;
        $sql = "SELECT $attribute FROM (SELECT * FROM ".$table." JOIN ".$othertable. " USING (".$id_name.") WHERE ".
            $id_name." = :".$id_name." ) AS tbl";
        if($id !== null){
            $sql .= " WHERE $attribute = :id;";
            $stmt = App::$app->db->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        } else {
            $stmt = App::$app->db->prepare($sql); 
        }
        $stmt->bindParam( ":".$id_name, $this->$id_name, \PDO::PARAM_INT);
        if($stmt->execute())
        {
            $res = $stmt->fetchAll(\PDO::FETCH_COLUMN,0);
            return array_unique($res);
        } else {
            return false;
        }

    }

    protected static function formatCondition($key, $value)
    {
        $op = "=";
        if(preg_match('/(^.*)\[/', $key, $match) === 1)
        {
            preg_match('/(^.+)\[(.*)\]$/', $key, $match);
            $attribute = $match[1];
            switch($match[2])
            {
                case "eq": $op = "="; break;
                case "gt": $op = ">"; break;
                case "ge": $op = ">="; break;
                case "lt": $op = "<"; break;
                case "ne": $op = "<>"; break;
                default: $op = "=";
            }          
        } else {
            $attribute = $key;
        };
        if(is_string($value))
            {
                $value = App::$app->db->quote($value);
            }; 
        return $attribute." ".$op." ".$value;
    }

}