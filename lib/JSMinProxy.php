<?php

/**
 * JSMinProxy
 *
 * ローダー用のJavaScriptファイル（以下、ローダーJS）のコードを解析して
 * 必要なJSファイルを抽出し、結合・圧縮をほどこし、1つのファイルとして出力します。
 * コードの圧縮処理は、JSMinライブラリを使用しています。
 *
 * setCashDirメソッドでキャッシュファイルの格納ディレクトリを設定すれば、
 * 結合・圧縮したファイルをキャッシュすることができます。
 *
 * @author Daiki UEDA
 * @version 1.0
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

	/** ローダーJSが設置されたディレクトリのパス */
	private $loaderSourceDir;

	/** ローダーJSのファイル名 */
	private $loaderSourceFilename;

	/** キャッシュファイルを格納するディレクトリのパス */
	private $cashDir;

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

		preg_match( "/var required[ ]?=[ ]?\[([^\]]*)\]/s", $loader_source_code, $matches );
		preg_match_all( "/['\"](.+.js)['\"]/", $matches[1], $file_pathes );

		return array_map( array( $this, 'adjustSourceFilePath' ), $file_pathes[1] );
	}

	/**
	 * キャッシュファイルが有効であるかテストする
	 * キャッシュファイルが存在し、かつ、ソースとなるJSファイルより新しい場合は、
	 * そのキャッシュファイルを有効なものと判断する
	 * @param string $cash_filepath キャッシュファイルのファイルパス
	 * @param array $source_filepathes ソースとなるJSファイルのファイルパスの配列
	 * @return boolean キャッシュが有効な場合はtrue、そうでない場合はfalse
	 */
	private function isCashValid( $cash_filepath, $source_filepathes ){

		if( !file_exists( $cash_filepath = $this->cashDir . $this->loaderSourceFilename ) ){
			return false;
		}

		$cash_filetime = filemtime( $cash_filepath );

		if( $cash_filetime < filemtime( $this->loaderSourceDir . $this->loaderSourceFilename ) ){
			return false;
		}

		for( $i = 0, $fileCount = count( $source_filepathes ); $i < $fileCount; $i++ ){
			if( $cash_filetime < filemtime( $source_filepathes[$i] ) ){
				return false;
			}
		}

		return true;
	}

	/**
	 * キャッシュファイルを格納するディレクトリを設定する
	 * @param type $dirname キャッシュファイルを格納するディレクトリのパス
	 */
	public function setCashDir( $dirname ){
		$this->cashDir = rtrim( $dirname, '/' ) . '/';
	}

	/**
	 * 必要なJavaScriptファイルを読込み、結合・圧縮して出力する
	 */
	public function serve(){
		$target_files = $this->getSourceFilePathes();

		if(
			isset( $this->cashDir ) &&
			$this->isCashValid( $cash_filepath = $this->cashDir . $this->loaderSourceFilename, $target_files )
		){
			readfile( $cash_filepath );
			exit;
		}

		$code_str = '';
		for( $i = 0, $fileCount = count( $target_files ); $i < $fileCount; $i++ ){
			$code_str .= file_get_contents( $target_files[$i] );
		}

		$code_str = JSMinProxy::minify( $code_str );

		if( file_exists( $this->cashDir ) ){
			$cash_file_handle = fopen( $cash_filepath, "w+" );
			fwrite( $cash_file_handle, $code_str );
			fclose( $cash_file_handle );
		}

		echo $code_str;
	}

}
