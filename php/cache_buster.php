<?php
/**
 * PX Commands "publish" cache buster
 */
namespace tomk79\pickles2\publishEx;

/**
 * PX Commands "publish" cache buster
 */
class cache_buster{

	/** Picklesオブジェクト */
	private $px;

	/** プラグイン設定 */
	private $plugin_conf;

	/** コンテンツハッシュ値のキャッシュ */
	private $content_hash_cache = array();

	/** 現在のパス */
	private $path_original;

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $json プラグイン設定
	 */
	public function __construct( $px, $json ){
		$this->px = $px;
		$this->plugin_conf = $json;
	}

	/**
	 * キャッシュバスティングの対象となるパスか判定する
	 * 
	 * @param string $path 対象コンテンツのパス
	 * @return boolean キャッシュバスティングの対象となる場合に true, それ以外の場合に false
	 */
	public function is_enabled_path($path){
		if( preg_match('/\.(?:css|js|gif|png|jpg|jpeg|jpe|webp)$/i', $path) ){
			return true;
		}
		return false;
	}

	/**
	 * コンテンツハッシュ値をキャッシュに記憶する
	 * 
	 * @param string $path 対象コンテンツのパス
	 * @param string $hash 対象コンテンツのハッシュ値
	 * @return boolean 成功時に true を返します。
	 */
	public function set_content_hash($path, $hash){
		if( !strlen($hash ?? '') ){
			return false;
		}
		if( strlen($this->content_hash_cache[$path] ?? '') ){
			return true;
		}
		$this->content_hash_cache[$path] = $hash;
		return true;
	}

	/**
	 * コンテンツハッシュ値を取得する
	 * 
	 * @param string $path 対象コンテンツのパス
	 * @return string ハッシュ値を返します。
	 */
	public function get_content_hash($path){
		return $this->content_hash_cache[$path] ?? false;
	}

	/**
	 * Resolve path
	 * @param  string $path_original コンテンツのパス
	 * @param  string $src      ソース全体
	 * @return string           変換後のソース全体
	 */
	public function resolve($path_original, $src){
		if( !$this->plugin_conf->allow_cache_buster ){
			// キャッシュバスターが無効の場合、
			// この処理はスキップする。(無加工のまま返す)
			return $src;
		}

		$this->path_original = $path_original;

		$ext = $this->px->fs()->get_extension($this->path_original);

		switch( strtolower($ext) ){
			case 'html':
			case 'htm':
				$src = $this->path_resolve_in_html($src);
				break;
			case 'css':
				$src = $this->path_resolve_in_css($src);
				break;
		}

		return $src;
	}

	/**
	 * HTMLファイル中のパスを解決する
	 * @param string $src HTMLソース
	 * @return string 解決された後の HTMLソース
	 */
	private function path_resolve_in_html( $src ){

		// Simple HTML Parser を通したときに、
		// もとの文字セットが無視されて DEFAULT_TARGET_CHARSET (=UTF-8) に変換されてしまう問題に対して、
		// もとの文字セットを記憶 → UTF-8 に一時変換 → Simple HTML Parser → 最後にもとの文字セットに変換しなおす
		// という処理で対応した。
		$detect_encoding = mb_detect_encoding($src);


		// HTMLをパース
		$html = str_get_html(
			mb_convert_encoding( $src, DEFAULT_TARGET_CHARSET, $detect_encoding ) ,
			false, // $lowercase
			false, // $forceTagsClosed
			DEFAULT_TARGET_CHARSET, // $target_charset
			false, // $stripRN
			DEFAULT_BR_TEXT, // $defaultBRText
			DEFAULT_SPAN_TEXT // $defaultSpanText
		);

		if($html === false){
			// HTMLパースに失敗した場合、無加工のまま返す。
			$this->px->error('HTML Parse ERROR. $src size '.strlen($src ?? '').' byte(s) given; '.__FILE__.' ('.__LINE__.')');
			return $src;
		}

		$conf_dom_selectors = array(
			'*[href]'=>'href',
			'*[src]'=>'src',
			'form[action]'=>'action',
		);

		foreach( $conf_dom_selectors as $selector=>$attr_name ){
			$ret = $html->find($selector);
			foreach( $ret as $retRow ){
				$val = $retRow->getAttribute($attr_name);
				$val = $this->get_new_path($val);
				$retRow->setAttribute($attr_name, $val);
			}
		}

		$ret = $html->find('*[style]');
		foreach( $ret as $retRow ){
			$val = $retRow->getAttribute('style');
			$val = str_replace('&quot;', '"', $val);
			$val = str_replace('&lt;', '<', $val);
			$val = str_replace('&gt;', '>', $val);
			$val = $this->path_resolve_in_css($val);
			$val = str_replace('"', '&quot;', $val);
			$val = str_replace('<', '&lt;', $val);
			$val = str_replace('>', '&gt;', $val);
			$retRow->setAttribute('style', $val);
		}

		$ret = $html->find('style');
		foreach( $ret as $retRow ){
			$val = $retRow->innertext;
			$val = $this->path_resolve_in_css($val);
			$retRow->innertext = $val;
		}

		$src = $html->outertext;

		// もとの文字セットを復元
		$src = mb_convert_encoding( $src, $detect_encoding );

		return $src;
	}

	/**
	 * CSSファイル中のパスを解決する
	 * @param string $bin CSSソース
	 * @return string 解決された後の CSSソース
	 */
	private function path_resolve_in_css( $bin ){

		$rtn = '';

		// url()
		while( 1 ){
			if( !preg_match( '/^(.*?)(\/\*|url\s*\\(\s*(\"|\'|))(.*)$/si', $bin, $matched ) ){
				$rtn .= $bin;
				break;
			}
			$rtn .= $matched[1];
			$start = $matched[2];
			$delimiter = $matched[3];
			$bin = $matched[4];

			if( $start == '/*' ){
				$rtn .= '/*';
				preg_match( '/^(.*?)\*\/(.*)$/si', $bin, $matched );
				$rtn .= $matched[1];
				$rtn .= '*/';
				$bin = $matched[2];
			}else{
				$rtn .= 'url("';
				preg_match( '/^(.*?)'.preg_quote($delimiter, '/').'\s*\)(.*)$/si', $bin, $matched );
				$res = trim( $matched[1] );
				$res = $this->get_new_path( $res );
				$rtn .= $res;
				$rtn .= '")';
				$bin = $matched[2];
			}

		}

		// @import
		$bin = $rtn;
		$rtn = '';
		while( 1 ){
			if( !preg_match( '/^(.*?)@import\s*([^\s\;]*)(.*)$/si', $bin, $matched ) ){
				$rtn .= $bin;
				break;
			}
			$rtn .= $matched[1];
			$rtn .= '@import ';
			$res = trim( $matched[2] );
			if( !preg_match('/^url\s*\(/', $res) ){
				$rtn .= '"';
				if( preg_match( '/^(\"|\')(.*)\1$/si', $res, $matched2 ) ){
					$res = trim( $matched2[2] );
				}
				$res = $this->get_new_path( $res );
				$rtn .= $res;
				$rtn .= '"';
			}else{
				$rtn .= $res;
			}
			$bin = $matched[3];
		}

		return $rtn;
	}

	/**
	 * 書き換え後の新しいパスを取得する
	 * @param string $path 書き換え前のリンク先のパス
	 * @return string 書き換え後のリンク先のパス
	 */
	private function get_new_path( $path ){
		if( preg_match( '/^(?:[a-zA-Z0-9]+\:|\/\/|\#)/', ''.$path ) ){
			return $path;
		}

		$params = '';
		if( preg_match( '/^(.*?)([\?\#].*)$/', ''.$path, $matched ) ){
			$path = $matched[1];
			$params = $matched[2];
		}

		// $rewrite_direction = $this->device_info->rewrite_direction ?? null;
		// preg_match('/^(.*)2(.*)$/', $rewrite_direction ?? '', $matched);
		// $rewrite_from = $matched[1] ?? null;
		// $rewrite_to   = $matched[2] ?? null;
		// if( !strlen(''.$rewrite_from) ){
		// 	$rewrite_from = 'rewrited';
		// }
		// if( !strlen(''.$rewrite_to) ){
		// 	$rewrite_to = 'origin';
		// }

		$type = 'relative';
		if( preg_match('/^\//', ''.$path) ){
			$type = 'absolute';
		}elseif( preg_match('/^\.\//', ''.$path) ){
			$type = 'relative_dotslash';
		}
		$is_slash_closed = false;
		if( preg_match('/\/$/', ''.$path) ){
			$is_slash_closed = true;
			$path .= $this->px->get_directory_index_primary();
		}

		// ------------------

		$cd_origin = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $this->path_original ) );
		$cd_origin = preg_replace( '/^(.*)(\/.*?)$/si', '$1', $cd_origin );
		if( !strlen($cd_origin) ){
			$cd_origin = '/';
		}

		// $cd_rewrited = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $this->path_rewrited ) );
		// $cd_rewrited = preg_replace( '/^(.*)(\/.*?)$/si', '$1', $cd_rewrited );
		// if( !strlen($cd_rewrited) ){
		// 	$cd_rewrited = '/';
		// }

		// ------------------

		$realpath_from = $cd_origin;
		$realpath_to = $this->px->fs()->normalize_path($this->px->fs()->get_realpath($path, $cd_origin));

		if( $this->is_enabled_path($realpath_to) ){
			$content_hash = $this->get_content_hash($realpath_to);
			if( is_string($content_hash ?? null) ){
				$params .= ( strlen($params??'') ? '&' : '?' ).urlencode($content_hash).'=1';
			}
		}

		// if( $rewrite_from == 'rewrited' ){
		// 	$realpath_from = $cd_rewrited;
		// }
		// if( $rewrite_to == 'rewrited' ){
		// 	$realpath_to = $this->path_rewriter->rewrite($realpath_to, $this->device_info->path_rewrite_rule);
		// 	$realpath_to = $this->px->fs()->normalize_path($this->px->fs()->get_realpath($realpath_to, $cd_origin));
		// }

		// ------------------

		if( $type == 'relative' || $type == 'relative_dotslash' ){
			$realpath_to = $this->px->fs()->normalize_path($this->px->fs()->get_relatedpath($realpath_to, $realpath_from));
			if( $type == 'relative' ){
				$realpath_to = preg_replace( '/^\.\//si', '', $realpath_to );
			}elseif( $type == 'relative_dotslash' ){
				$realpath_to = preg_replace( '/^(\.\/)?/si', './', $realpath_to );
			}
		}

		$realpath_to = $this->px->fs()->normalize_path($realpath_to);
		if( $is_slash_closed ){
			$realpath_to = preg_replace( '/'.$this->px->get_directory_index_preg_pattern().'$/', '', ''.$realpath_to );
		}
		$realpath_to .= $params;

		return $realpath_to;
	}

}
