<?php

/**
 *  Globalne funkcije i konstante
 * 
 */

 const _CONFIG_PATH_ = __DIR__.'/config/';
 const _MAIN_DIR_ = __DIR__.'\\';


 /**
  * @param int $code, statusni kod greske
  * @return string|null, vraca poruku greske pridruzenom kodu
  */
function error(int $code)
{
    $errors = require _CONFIG_PATH_.'error.php';
    return $errors[$code];
};

/**
 * Dohvata parametre u /config folderu @param string $key ima format "{file}.{parametar}",
 * gde je {file}.php u /config folderu
 * @param string $key
 * @return mixed|fasle
 */
function config(string $key)
{
  $key = explode('.',$key,2);
  $params = require _CONFIG_PATH_.$key[0].'.php';
  if(array_key_exists($key[1],$params))
  {
    return $params[$key[1]];
  } else {
    return false;
  }

}


/**
 * Vraca {vrednost} {parametra} u .env datoteci, svaki red je jedan par {parametar}={vrednost}
 * @param string $key, parametar u .env datoteci
 * @return string|false 
 */
function env(string $key, string $file = _MAIN_DIR_.'.env')
{
  if(!function_exists("filter")){
    function filter($value)
    {
      if(preg_match('/^[#%].*/',$value)) return 0;
      return $value;
    }
  }

  $file = file($file,FILE_IGNORE_NEW_LINES);
  $file = array_filter($file,"filter");
  foreach($file as $k => $v)
  {
    preg_match('/^([^=]+)=([^=]*)$/', $v, $match);
    $file[$match[1]] = $match[2];
    unset($file[$k]);
  }
  if(array_key_exists($key,$file))
  {
    return $file[$key];
  } else {
    return false;
  }

}


function log_timestamp(string $tag=null){
  $timestamp = microtime(true)-$_SERVER['REQUEST_TIME_FLOAT'];
  $msg = date('Y-m-d H:i:s')." ".$tag." ".$timestamp." ". $_SERVER['REQUEST_URI']."\n";
  file_put_contents(_MAIN_DIR_."\\logs\\timestamps.txt",$msg,FILE_APPEND);
}



