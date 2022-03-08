<?php
require '../vendor/autoload.php';
// $_url = trim(fgets(STDIN));
// $route = preg_replace('/\//','\\/', $route);
// $route = preg_replace('/\{([a-z-]+[0-9]*)\}/', '(?P<\1>[a-z-]+)', $route);
// $_url = preg_replace('/^\/(.*)$/','\1', $_url);
// $_url = preg_replace('/(^.*[^\/])$/','\1/', $_url);
// echo var_dump($_url);


/**
 * 
 * 29.6.2021.
 * 
 */



//echo config('app.base_path');

//echo env('test');

/**
 * 
 * 8.7.2021.
 * 
 */

//  $class= "App\\Core\\NaciniPlacanja";


//  function getAttrIdName(string $class)
//  {
//     $string = substr($class, strrpos($class,'\\')+1);
//     return $string."ID";
//  }

//  function getTableName(string $class):string
//  {
//      $string = substr($class, strrpos($class,'\\')+1);
//      $string = preg_replace('/(.)([A-Z])/', '$1_$2', $string );
//      return strtolower($string);
//  }

//  echo getAttrIdName($class);

// $data = [
//     "GostID" => 1,
//     "Ime" => "Mitar"
//  ];

// $array_of_keys = array_keys($data);
// $sql = "INSERT INTO $tableName (".implode(',',$array_of_keys)
//                 .")VALUES(".implode(',', array_map( function ($key) { return ':'.$key; } , $array_of_keys )).");";
// $sql = "INSERT INTO $tableName (".implode(',',$array_of_keys).")VALUES(".implode(',', $data).");";
// echo $sql;


/**
 * 
 * 13.7.2021.
 * 
 */

//  $data = [
//     "GostID" => 1,
//     "Ime" => "Mitar"
//  ];

//  function join_1(array $connetions)
//  {
//      if(empty($connetions)) return false;
//      $table = "Pera";
//      $sql = "SELECT * FROM $table";
//      foreach($connetions as $other_table => $attribute)
//      {
//          $sql .= "\nJOIN \t\n".$other_table." USING (".$attribute.")";
//      }
//      $sql .= ";";
//      return $sql;
//  }

//  $res = join_1($data);
//  echo var_dump($res);

/**
 * 
 * 
 * 14.7.2021
 * 
 */




// function formatCondition($key, $value)
// {
//     $op = "=";
//     if(preg_match('/[^\[]/', $key, $match) === 1)
//     {
//         $attribute = $key;
//     } else {
//         preg_match('/^(.+)\[(.*)\]$/', $key, $match);
//         $attribute = $match[1];
//         switch($match[2])
//         {
//             case "eq": $op = "="; break;
//             case "gt": $op = ">"; break;
//             case "ge": $op = ">="; break;
//             case "lt": $op = "<"; break;
//             case "le": $op = "<="; break;
//             case "ne": $op = "<>"; break;
//             default: $op = "=";
//         }   
//     }
//     if(is_string($value))
//     {
//         $value ="'".$value."'";
//     };
//     return $attribute." ".$op." ".$value;
// }

// $condition = [
//     "a[gt]" => 4,
//     "b" => "abc"
// ];

// $sql = "SELECT * FROM tabela";
// if(!empty($condition)){
//     $sql .= " WHERE ";
//     $array_of_keys_condition = array_keys($condition);
//     $sql .= implode(" AND ", array_map("formatCondition", $array_of_keys_condition, $condition ));
// }
// $sql .= " LIMIT :offset, :lenght;";

// $res = formatCondition("a", "trr");
// var_dump($sql);

