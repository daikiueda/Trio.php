<?php

/**
 * minify less proxy
 * lessファイルの圧縮
 *
 * このPHPプログラムは、サーバ内のリダイレクト処理を経由して呼び出されることを
 * 前提としています。
 * リダイレクト前のURLから対象のlessファイルを決定し、圧縮処理を実行します。
 *
 * @author Daiki UEDA
 * @version 1.0.0
 */
if( $_SERVER["SCRIPT_NAME"] === $_SERVER["REQUEST_URI"] ){
	return;
}

require_once 'lib/lessphp/lessc.inc.php';

preg_match( "/^(.+\/)([^\/]+)$/", $_SERVER["REQUEST_URI"], $matches );
$sub_directory = preg_replace( "/^\/ueda\/common\/css\//", "", $matches[1] );

$path_source_less = preg_replace( '/\.css$/', '.less', $_SERVER["REQUEST_URI"] );
$less = new lessc( $_SERVER["DOCUMENT_ROOT"] . $path_source_less );

$test_str = $less->parse();

header('Content-type: text/css');
echo $test_str ;
