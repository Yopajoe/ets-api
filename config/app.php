<?php

$dev=filter_var(env("DEV"),FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE);
set_error_handler('Core\Error::errorHandler');
if($dev)
    set_exception_handler('Core\Error::exceptionHandler');
else
    set_exception_handler('Core\Error::exceptionProductionHandler');
if(!defined('_ENV_')) define('_ENV_',_MAIN_DIR_.'.env');


/** 
 * @prama niz paratmetra koje prosledjujete glavnoj App instanci 
 * 
 */
return [

    "app_state" => $dev,

    "base_path" => "api/v1/",

    "model_dir" => _MAIN_DIR_."App\\Models\\",

    "model_namespace" => "App\\Models\\",

    "connection_table" => array()

];
