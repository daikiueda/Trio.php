<?php

/**
 * minify less proxy
 * lessファイルのパース
 *
 * このPHPプログラムは、サーバ内のリダイレクト処理を経由して呼び出されることを
 * 前提としています。
 * リダイレクト前のURLから対象のlessファイルを決定し、パース・出力処理を実行します。
 *
 * @author Daiki UEDA
 * @version 1.0.1
 */
if( $_SERVER["SCRIPT_NAME"] === $_SERVER["REQUEST_URI"] ){
	return;
}

require_once 'lib/lessphpProxy.php';

$req_uri = preg_replace( "/\?.*$/", "", $_SERVER["REQUEST_URI"] );

$path_source_less = preg_replace( '/\.css$/', '.less', $req_uri );


if( is_file( $_SERVER["DOCUMENT_ROOT"] . $path_source_less ) ){
	preg_match( "/^(.+\/)([^\/]+)$/", $req_uri, $matches );
	$sub_directory = $matches[1];

	$lessphp_proxy = new lessphpProxy( $_SERVER["DOCUMENT_ROOT"] . $path_source_less );
	$lessphp_proxy->setCacheDir( $_SERVER["DOCUMENT_ROOT"] . '/' . $sub_directory );
	$lessphp_proxy->serve();
}
else {
	if( file_exists( $_SERVER["DOCUMENT_ROOT"] . $req_uri ) ){
		header('Content-type: text/css');
		readfile( $_SERVER["DOCUMENT_ROOT"] . $req_uri );
	}
	else {
		header( "HTTP/1.0 404 Not Found" );
	}
}
