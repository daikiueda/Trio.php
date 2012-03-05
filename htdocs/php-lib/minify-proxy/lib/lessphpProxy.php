<?php

/**
 * lessphpProxy
 *
 * LESSファイルをパースして、CSSファイルを出力します。
 * LESSのパースには、lessphpライブラリを使用します。
 *
 * setCacheDirメソッドによるキャッシュファイルの格納ディレクトリの設定がある場合、
 * 出力したファイルをキャッシュします。
 *
 * @author Daiki UEDA
 * @version 1.0.0
 */
class lessphpProxy {

	/**
	 * LESSコードをパースする
	 * @return string 出力されたCSSコード
	 */
	public function parse(){
		require_once 'lessphp/lessc.inc.php';
		$less = new lessc( $this->lessSourceDir . $this->lessSourceFilename . '.less' );
		return $less->parse();
	}

	/** LESSファイルが設置されたディレクトリのパス */
	private $lessSourceDir;

	/** ローダーJSのファイル名 */
	private $lessSourceFilename;

	/** キャッシュファイルを格納するディレクトリのパス */
	private $cacheDir;

	/**
	 * コンストラクタ
	 * @param string $source_less_filepath LESSファイルのファイルパス
	 */
	public function __construct( $source_less_filepath ){
		preg_match( "/^(.+\/)([^\/]+).less$/", $source_less_filepath, $matches );
		$this->lessSourceDir = $matches[1];
		$this->lessSourceFilename = $matches[2];
	}

	/**
	 * 読み込む必要のあるLESSファイルのパスを、読み込み元のLESSファイルの
	 * 設置ディレクトリを基準に調整する
	 * @param string $filepath 対象のLESSファイルのパス記述
	 * @return string 妥当なファイルパス
	 */
	private function adjustSourceFilePath( $filepath ){
		return $this->lessSourceDir . $filepath;
	}

	/**
	 * 読み込み元のLESSファイルから、読み込む必要のあるJSファイルのパス記述を抽出する
	 * @return array LESSファイルのファイルパスの配列
	 */
	private function getSourceFilePathes(){
		$less_source_code = file_get_contents( $this->lessSourceDir . $this->lessSourceFilename . '.less' );
		
		// 読み込み元のLESSファイルの内容から、読込ファイルのパス指定記述を抽出する
		preg_match_all( "/@import url\([\"\' ]*[^\"\' ]+[\"\' ]*\)/", $less_source_code, $importStrs );
		
		// パス指定記述が抽出された場合は、パス指定の配列を返す
		if( count( $importStrs[0] ) > 0 ){
			$file_pathes = array();
			
			foreach( $importStrs[0] as $importStr ){
				preg_match( "/\([\'\" ]?([^\"\' ]+\.less)[\'\" ]?\)/", $importStr, $file_path );
				if( count($file_path) > 0 ){
					array_push($file_pathes, $file_path[1]);
				}
			}
			
			// パス指定を調整。調整後の配列を返す
			return array_map( array( $this, 'adjustSourceFilePath' ), $file_pathes );
		}
		// パス指定記述が抽出されない場合は、空の配列を返す
		else {
			return array();
		}
	}

	/**
	 * キャッシュファイルが有効であるかテストする
	 * キャッシュファイルが存在し、かつ、ソースとなるLESSファイルより新しい場合は、
	 * そのキャッシュファイルを有効なものと判断する
	 * @param array $source_filepathes ソースとなるLESSファイルのファイルパスの配列
	 * @return boolean キャッシュが有効な場合はtrue、そうでない場合はfalse
	 */
	private function testCacheValidity( $source_filepathes ){
		
		// キャッシュファイルのパスを取得
		$cache_filepath = $this->cacheDir . $this->lessSourceFilename . '.css';
		
		// ファイルが存在しない場合は、falseを返す
		if( !file_exists( $cache_filepath ) ){
			return false;
		}
		
		// キャッシュファイルの変更日時を取得
		$cache_filetime = filemtime( $cache_filepath );
		
		// 読み込み元のLESSファイルが、キャッシュファイルより新しい場合は、falseを返す
		if( $cache_filetime < filemtime( $this->lessSourceDir . $this->lessSourceFilename . '.less' ) ){
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
	 * LESSファイルをパースし、CSSコードを出力する
	 */
	public function serve(){
		
		// HTTPヘッダー
		header('Content-type: text/css');
		
		// キャッシュファイルのパスを取得
		$cache_filepath = $this->cacheDir . $this->lessSourceFilename . '.css';
		
		// パースの対象となるファイルを取得
		$target_files = $this->getSourceFilePathes();
		
		// キャッシュが有効な場合は、キャッシュファイルの内容を出力して処理を終える
		if(
			isset( $this->cacheDir ) &&
			$this->testCacheValidity( $target_files )
		){
			readfile( $cache_filepath );
			exit;
		}

		// パース
		$code_str = lessphpProxy::parse();
		
		// キャッシュファイルの格納ディレクトリが設定されている場合は、
		// パースした内容をファイルに保存する
		if( file_exists( $this->cacheDir ) ){
			$cache_file_handle = fopen( $cache_filepath, "w+" );
			fwrite( $cache_file_handle, $code_str );
			fclose( $cache_file_handle );
		}
		
		// 結合・圧縮した内容を出力する
		echo $code_str;
	}

}
