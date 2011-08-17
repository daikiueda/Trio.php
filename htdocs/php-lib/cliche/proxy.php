<?php
define('CLICHE_PHP_DIR', '/php-lib/cliche/');
define('MINIFY_PHP_DIR', '/php-lib/min/');



//if( $_SERVER["REDIRECT_URL"] ) return;

$cliche_documentRoot = substr(dirname(__FILE__), 0, -1*(strlen(rtrim(CLICHE_PHP_DIR,'/'))));
$loader_js_dir = preg_replace( "/[^\/]+$/", '', $_SERVER["REDIRECT_URL"] );
$minify_dir = $cliche_documentRoot . '/' . MINIFY_PHP_DIR;

function getJSFilePathes($my_documentRoot){

  $loader_js = file_get_contents($my_documentRoot . '/' . $_SERVER["REDIRECT_URL"]);
  preg_match( "/required = \[([^\]]*)\]/s", $loader_js, $matches );
  preg_match_all( "/['\"](.+.js)['\"]/", $matches[1], $file_pathes );

  function adjustPath($value){
    global $loader_js_dir;
    return $loader_js_dir . $value;
  };
  return array_map( "adjustPath", $file_pathes[1]);
}

function setParams($my_documentRoot){
  $_GET['f'] = implode( ",", getJSFilePathes($my_documentRoot) );
}

setParams($cliche_documentRoot);

require $minify_dir . '/index.php';
