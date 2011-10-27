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
 * @version 1.0.2
 */
if( $_SERVER["SCRIPT_NAME"] === $_SERVER["REQUEST_URI"] ){
	return;
}

require_once 'lib/JSMinProxy.php';

preg_match( "/^(.+\/)([^\/]+)$/", $_SERVER["REQUEST_URI"], $matches );
$sub_directory = preg_replace( "/^\/js\//", "", $matches[1] );

$js_min_proxy = new JSMinProxy( $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REQUEST_URI"] );
$js_min_proxy->setCacheDir( $_SERVER["DOCUMENT_ROOT"] . '/js/build/' . $sub_directory );
$js_min_proxy->serve();
