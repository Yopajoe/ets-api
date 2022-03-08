<?php

namespace Core;

/** 
 * Error
 * 
 * PHP 7.3.28
 */

 class Error
 {
 
     /**
      * Error handler. Convert all errors to Exceptions by throwing an ErrorException.
      *
      * @param int $level  Error level
      * @param string $message  Error message
      * @param string $file  Filename the error was raised in
      * @param int $line  Line number in the file
      *
      * @return void
      */
     public static function errorHandler($level, $message, $file, $line)
     {
         if (error_reporting() !== 0) {  // to keep the @ operator working
             throw new \ErrorException($message, 0, $level, $file, $line);
         }
     }
 
     /**
      * Exception handler.
      *
      * @param Exception $exception  The exception
      *
      * @return void
      */
     public static function exceptionHandler($exception)
     {
         echo "<h1>Fatal error</h1>";
         echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
         echo "<p>Message: '" . $exception->getMessage() . "'</p>";
         echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
         echo "<p>Thrown in '" . $exception->getFile() . "' on line " . 
              $exception->getLine() . "</p>";
     }

     public static function exceptionProductionHandler($exception){
         $code = $exception->getCode();
         if($code===0){
             $msg=str_repeat("*", 30)."\n";
             $msg.=date("Y-m-d H:i:s").":Uncaught exception: '" . get_class($exception) . "'\n";
             $msg.=str_repeat(" ",10)."Message: '" . $exception->getMessage() . "'\n";
             $msg.=str_repeat(" ",10)."Stack trace:" . $exception->getTraceAsString() . "\n";
             $msg.=str_repeat(" ",10)."Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() ."\n";
             file_put_contents(_MAIN_DIR_."\\logs\\logs.txt",$msg,FILE_APPEND);
             http_response_code(500);
             echo json_encode(["error"=>error(500)]);
         } else {
            switch($code){
                case 100: 
                case 101:
                case 102:
                case 300:
                case 301:
                case 302:
                case 401:
                case 402:
                case 403:
                case 404:{
                    http_response_code(503);
                    break;
                }
                case 200:{
                    http_response_code(500);
                    break;
                }
                case 201:
                case 202:
                case 203:{
                    http_response_code(400);
                    break;
                }
                case 400:
                case 405:
                case 406:{
                    http_response_code(404);
                    break;
                }
            }
            echo json_encode(["error"=> $exception->getMessage()]);
         }
     }
 }