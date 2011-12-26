<?php

/**
 * JSMinProxy
 *
 * ローダー用のJavaScriptファイル（以下、ローダーJS）のコードを解析して
 * 必要なJSファイルを抽出し、結合・圧縮をほどこし、1つのファイルとして出力します。
 * コードの圧縮処理は、JSMinライブラリを使用しています。
 *
 * setCacheDirメソッドでキャッシュファイルの格納ディレクトリを設定すれば、
 * 結合・圧縮したファイルをキャッシュすることができます。
 *
 * @author Daiki UEDA
 * @version 1.0.2
 */
class JSMinProxy {

	/**
	 * JavaScriptコードを圧縮する
	 * @param string $code_str 圧縮する対象のJSコード
	 * @return string 圧縮されたJSコード
	 */
	public static function minify( $code_str ){
		require_once 'JSMin/JSMin.php';
		return JSMin::minify( $code_str );
	}

	/**
	 * 結合対象のソースファイルが見つからない場合に
	 * 代替のコードを埋め込む
	 * @param string $filepath 存在しないJSファイルのパス
	 * @return string 代替コード
	 */
	public static function onFileNotFound( $filepath ){
		return '' .
			'(function(){' .
			'	var message = "\'' . $filepath . '\' is not found.";' .
			'	!!window.console ? console.warn(message): alert(message);' .
			'})();';
	}

	/** ローダーJSが設置されたディレクトリのパス */
	private $loaderSourceDir;

	/** ローダーJSのファイル名 */
	private $loaderSourceFilename;

	/** キャッシュファイルを格納するディレクトリのパス */
	private $cacheDir;

	/**
	 * コンストラクタ
	 * @param string $loader_filepath ローダーJSのファイルパス
	 */
	public function __construct( $loader_filepath ){
		preg_match( "/^(.+\/)([^\/]+)$/", $loader_filepath, $matches );
		$this->loaderSourceDir = $matches[1];
		$this->loaderSourceFilename = $matches[2];
	}

	/**
	 * 読み込む必要のあるJSファイルのパスを、ローダーJSの設置ディレクトリを基準に調整する
	 * @param string $filepath 対象のJSファイルのパス記述
	 * @return string 妥当なファイルパス
	 */
	private function adjustSourceFilePath( $filepath ){
		return $this->loaderSourceDir . $filepath;
	}

	/**
	 * ローダーJSから、読み込む必要のあるJSファイルのパス記述を抽出する
	 * @return array JSファイルのファイルパスの配列
	 */
	private function getSourceFilePathes(){
		$loader_source_code = file_get_contents( $this->loaderSourceDir . $this->loaderSourceFilename );
		
		// ローダーJSの内容から、読込ファイルのパス指定記述を抽出して、配列に格納
		preg_match( "/var required[ ]?=[ ]?\[([^\]]*)\]/s", $loader_source_code, $matches );
		preg_match_all( "/['\"](.+.js)['\"]/", $matches[1], $file_pathes );
		
		// 読込ファイルの配列について、パス指定を調整。調整後の配列を返す
		return array_map( array( $this, 'adjustSourceFilePath' ), $file_pathes[1] );
	}

	/**
	 * キャッシュファイルが有効であるかテストする
	 * キャッシュファイルが存在し、かつ、ソースとなるJSファイルより新しい場合は、
	 * そのキャッシュファイルを有効なものと判断する
	 * @param string $cache_filepath キャッシュファイルのファイルパス
	 * @param array $source_filepathes ソースとなるJSファイルのファイルパスの配列
	 * @return boolean キャッシュが有効な場合はtrue、そうでない場合はfalse
	 */
	private function testCacheValidity( $cache_filepath, $source_filepathes ){
		
		// キャッシュファイルのパスを取得
		$cache_filepath = $this->cacheDir . $this->loaderSourceFilename;
		
		// ファイルが存在しない場合は、falseを返す
		if( !file_exists( $cache_filepath ) ){
			return false;
		}
		
		// キャッシュファイルの変更日時を取得
		$cache_filetime = filemtime( $cache_filepath );
		
		// ローダーJSが、キャッシュファイルより新しい場合は、falseを返す
		if( $cache_filetime < filemtime( $this->loaderSourceDir . $this->loaderSourceFilename ) ){
			return false;
		}
		
		// 読込ファイルのいずれかが、キャッシュファイルより新しい場合は、falseを返す
		for( $i = 0, $fileCount = count( $source_filepathes ); $i < $fileCount; $i++ ){
			if(
				file_exists( $source_filepathes[$i] ) &&
				$cache_filetime < filemtime( $source_filepathes[$i] )
			){
				return false;
			}
		}
		
		// 以上の条件に合致しない場合は、キャッシュを有効とみなす
		return true;
	}

	/**
	 * キャッシュファイルを格納するディレクトリを設定する
	 * @param type $dirname キャッシュファイルを格納するディレクトリのパス
	 */
	public function setCacheDir( $dirname ){
		$this->cacheDir = rtrim( $dirname, '/' ) . '/';
	}

	/**
	 * 必要なJavaScriptファイルを読込み、結合・圧縮して出力する
	 */
	public function serve(){
		// 結合・圧縮の対象となるファイルを取得
		$target_files = $this->getSourceFilePathes();
		
		// 対象ファイルが0個の場合は、ローダーJSの内容をそのまま出力して処理を終える
		if( count( $target_files ) == 0 ){
			readfile($this->loaderSourceDir . $this->loaderSourceFilename );
			exit;
		}
		
		// キャッシュファイルのパスを取得
		$cache_filepath = $this->cacheDir . $this->loaderSourceFilename;
		
		// キャッシュが有効な場合は、キャッシュファイルの内容を出力して処理を終える
		if(
			isset( $this->cacheDir ) &&
			$this->testCacheValidity( $cache_filepath, $target_files )
		){
			readfile( $cache_filepath );
			exit;
		}
		
		// 対象ファイルの内容を結合する
		$code_str = '';
		for( $i = 0, $fileCount = count( $target_files ); $i < $fileCount; $i++ ){
			if( file_exists( $target_files[$i] ) ){
				$code_str .= file_get_contents( $target_files[$i] );
			}
			else {
				$short_filepath = substr( $target_files[$i], strlen( $this->loaderSourceDir ) );
				$code_str .= JSMinProxy::onFileNotFound( $short_filepath );
			}
		}
		
		// 圧縮
		$code_str = JSMinProxy::minify( $code_str );
		
		// キャッシュファイルの格納ディレクトリが設定されている場合は、
		// 結合・圧縮した内容をファイルに保存する
		if( file_exists( $this->cacheDir ) ){
			$cache_file_handle = fopen( $cache_filepath, "w+" );
			fwrite( $cache_file_handle, $code_str );
			fclose( $cache_file_handle );
		}
		
		// 結合・圧縮した内容を出力する
		echo $code_str;
	}

}
