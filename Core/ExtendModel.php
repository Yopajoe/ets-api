<?php

namespace Core;

use App\App;
use ErrorException;

abstract class ExtendModel extends Model implements IGetAttributeTable
{
    use \Core\Traits\Validations;

    abstract protected static function rules();

    final protected static function validate(string $attribute, $value)
    {
        $class = get_called_class();
        //$res is validation array for create and update queries, first member is for create, second is for update
        $valditaion = [true, true];
        if(is_null($class::rules()[$attribute])) return $valditaion;
        $id = 0;
        //check validtion for primary key
        $check = self::check_primary_key($class::rules()[$attribute]);
        if($check[0]){
            $id = self::getAttrIdName(get_called_class());
            $table = self::getTableName(get_called_class());
            $sql = "SELECT * FROM $table WHERE $id = :id ;";
            $stmt = App::$app->db->prepare($sql);
            $stmt->bindParam(':id', $value,\PDO::PARAM_INT);
            if($stmt->execute()){
                // does allready exists?
                if($stmt->fetch() === false){
                    // if doesnt, true for create
                    $valditaion[1] = false;
                } else {
                    // if do, true for update
                    $valditaion[0] = false;
                }
            } else {
                throw new ErrorException(error(200),200);
            }
        }
        
        //check if autoincrement
        $check = self::check_auto_increment($class::rules()[$attribute]);
        if($check[0]){
            //need to be null for create
            if(!is_null($value)){
                $valditaion[0] = false;
                array_push($valditaion, array_slice($check, 1));
            }
        }


        //checks if exist foregin key in his table
        $check = self::check_foreign_key($class::rules()[$attribute]);
        if($check[0]){
            $foreign_class =  substr($attribute, 0 , -2);
            $table = strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $foreign_class ));
            $sql = "SELECT * FROM $table WHERE $attribute = :id ;";
            $stmt = App::$app->db->prepare($sql);
            $stmt->bindParam(':id', $value,\PDO::PARAM_INT);
            if($stmt->execute()){
                // does allready exists?
                if($stmt->fetch() === false){
                    // if doesnt, breaks integrity
                    $valditaion[1] = false;
                    $valditaion[0] = false;
                    array_push($valditaion, array_slice($check, 1));
                }
            } else {
                throw new ErrorException(error(200),200);
            }
        }

        //check if unique
        $check = self::check_unique($class::rules()[$attribute]);
        if($check[0]){
            $table = self::getTableName(get_called_class());
            $sql = "SELECT * FROM $table WHERE $attribute = :uni ;";
            $stmt = App::$app->db->prepare($sql);
            $stmt->bindParam(':uni', $value,\PDO::PARAM_INT);
                        if($stmt->execute()){
                // does allready exists?
                $res =$stmt->fetch();
                if($res){
                    // if does, breaks integrity
                    $valditaion[0] = false;
                    if($id == $res[self::getAttrIdName(get_class())])$valditaion[1] = false;
                    array_push($valditaion, array_slice($check, 1));
                }
            } else {
                throw new ErrorException(error(200),200);
            }
        }
        //check domain value
        $check = self::check_domen($class::rules()[$attribute],$value);
        if(!$check[0]){
            $valditaion[0]=$valditaion[1]=false;
            array_push($valditaion, array_slice($check, 1));
        }
        return $valditaion;
    }

    public function getAtrribute(string $attribute, int $id)
    {
        $class = get_called_class();
        $rules = $class::rules();
        if(array_key_exists($class::_RULE_TABLE_KEY_ATTRIBUTE_, $rules) === false)
        {
            $msg = preg_replace('/{attribute}/', "\"".$attribute."\"", error(302));
            throw new ErrorException($msg,302);
        };
        $attribute_class_name = substr($attribute, 0, strlen($attribute) - 2);
        $table_name = preg_replace('/(.)([A-Z])/', '$1_$2', $attribute_class_name );
        $table_name = strtolower($table_name);
        $data = [];
        if(App::$app->storage::exists($table_name))
        {
            $data = App::$app->storage::get($table_name);
            

        } else {

            $sql = "SELECT * FROM $table_name;";
            $stmt = App::$app->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();
            App::$app->storage::put($table_name, $data);

        }
        $id_items_array = array_column($data, $attribute);
        $item_index = array_search($id, $id_items_array);
        return $data[$item_index];

    }
}