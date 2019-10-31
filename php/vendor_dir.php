<?php
/**
 * PX Commands "publish" publish_vendor_dir operator
 */
namespace tomk79\pickles2\publishEx;

/**
 * PX Commands "publish" publish_vendor_dir operator
 */
class vendor_dir{

	/** Picklesオブジェクト */
	private $px;

	/** プラグイン設定 */
	private $plugin_conf;

	/** コンテンツルートディレクトリ */
	private $path_controot;

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $json プラグイン設定
	 */
	public function __construct( $px, $json ){
		$this->px = $px;
		$this->plugin_conf = $json;
		$this->path_controot = $px->conf()->path_controot;
	}

	/**
	 * vendorディレクトリのコピーを実行する
	 * @param object $device_list デバイス設定の一覧
	 * @return boolean 実行結果
	 */
	public function copy_vendor_to_publish_dirs( $device_list ){
		$realpath_original_vendor_dir = null;
		$tmp_path_autoload = __DIR__;
		while(1){
			if( is_file( $tmp_path_autoload.'/vendor/autoload.php' ) ){
				$realpath_original_vendor_dir = $tmp_path_autoload.'/vendor/';
				break;
			}

			if( $tmp_path_autoload == dirname($tmp_path_autoload) ){
				// これ以上、上の階層がない。
				break;
			}
			$tmp_path_autoload = dirname($tmp_path_autoload);
			continue;
		}
		unset($tmp_path_autoload);

		if( $realpath_original_vendor_dir && is_dir($realpath_original_vendor_dir) ){
			$tmp_done = array();
			foreach($device_list as $device_num => $device_info){
				// var_dump($device_info);

				if( array_key_exists($device_info->path_publish_dir, $tmp_done) && $tmp_done[$device_info->path_publish_dir] ){
					// すでに処理したパス
					continue;
				}

				if( !$this->px->fs()->mkdir_r( $device_info->path_publish_dir.$this->path_controot ) ){
					continue;
				}
				set_time_limit(5*60);
				if( !$this->px->fs()->copy_r( $realpath_original_vendor_dir, $device_info->path_publish_dir.$this->path_controot.'vendor/' ) ){
					continue;
				}
				set_time_limit(5*60);

				$tmp_done[$device_info->path_publish_dir] = true;
			}
			unset($tmp_done);
		}
		return true;
	}

}
