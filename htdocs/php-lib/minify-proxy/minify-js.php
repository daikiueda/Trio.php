<?php

/**
 * minify js proxy
 * JSファイルの圧縮
 *
 * このPHPプログラムは、サーバ内のリダイレクト処理を経由して呼び出されることを
 * 前提としています。
 * リダイレクト前のURLから対象のJSファイルを決定し、圧縮処理を実行します。
 *
 * @author Daiki UEDA
 * @version 1.0.3
 */
if( $_SERVER["SCRIPT_NAME"] === $_SERVER["REQUEST_URI"] ){
	return;
}

require_once 'lib/JSMinProxy.php';

$req_uri = preg_replace( "/\?.*$/", "", $_SERVER["REQUEST_URI"] );

$path_source_js = $req_uri;


if( is_file( $_SERVER["DOCUMENT_ROOT"] . $path_source_js ) ){
	preg_match( "/^(.+\/)([^\/]+)$/", $req_uri, $matches );
	$sub_directory = $matches[1];
	
	$js_min_proxy = new JSMinProxy( $_SERVER["DOCUMENT_ROOT"] . $path_source_js );
	$js_min_proxy->setCacheDir( $_SERVER["DOCUMENT_ROOT"] . $sub_directory . 'build' );
	$js_min_proxy->serve();
}
else {
	if( file_exists( $_SERVER["DOCUMENT_ROOT"] . $req_uri ) ){
		header('Content-type: text/javascript');
		readfile( $_SERVER["DOCUMENT_ROOT"] . $req_uri );
	}
	else {
		header( "HTTP/1.0 404 Not Found" );
	}
}
