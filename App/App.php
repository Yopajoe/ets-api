<?php

namespace App;

require '../vendor/autoload.php';



use App\Controllers\BaseController;
use App\Controllers\ShemaController;
use Core\Database;
use Core\Request;
use Core\Storage;
use Core\RouteGuard;

/**
 * Glavna Instanca Aplikacije
 * 
 * PHP 7.3.28
 */

 class  App {


    public static $app;
    public $db;
    public $storage;
    private $controller;
    private $request;
    private $guard;
    private $shema_controller;

    function __construct()
    {
       $this->config();
       $this->db = new Database(env('DB_HOST'),env('DB_NAME'),env('DB_USERNAME'),env('DB_PASSWORD'));
       $this->request = new Request ();
       $this->storage = new Storage();
       $this->guard = new RouteGuard($this->request);
       $this->controller = new BaseController($this->request);
       $this->shema_controller = new ShemaController($this->request);
       self::$app = $this;
    }


    private function config(){

        // Sva podesavanja aplikacije mozete definisati u app.php fajlu
        $this->params = require _CONFIG_PATH_.'app.php';
    }

    public  function run()
    {
        
        $this->guard->validate();
        $path = $this->request->getPath();
        if(array_key_first($path)==="shema")
            $this->shema_controller->response();
        else
            $this->controller->response();
    }  

 }