<?php
/**
 * test for tomk79\px2-publish-for-multi-device
 */
class skipDefaultDeviceTest extends PHPUnit_Framework_TestCase{
	private $helper;
	private $fs;

	public function setup(){
		require_once(__DIR__.'/helper/test_helper.php');
		mb_internal_encoding('UTF-8');
		$this->helper = new test_helper();
		$this->fs = new tomk79\filesystem();
	}




	/**
	 * Ping
	 */
	public function testPing(){

		// -------------------
		// api.get.vertion
		$output = $this->helper->passthru( [
			'php',
			__DIR__.'/testdata/skip_default_device/.px_execute.php' ,
			'/' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->helper->common_error( $output ) );
		$this->assertTrue( true );

	}//testPing();



	/**
	 * publish
	 */
	public function testPublishMultiDevice(){

		// -------------------
		// Execute Multi Device Publish
		$output = $this->helper->passthru( [
			'php',
			__DIR__.'/testdata/skip_default_device/.px_execute.php' ,
			'/?PX=publish.run' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->helper->common_error( $output ) );

		$this->assertTrue( is_dir( __DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs/' ) );
		$this->assertTrue( is_dir( __DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs1/' ) );
		$this->assertTrue( is_dir( __DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs2/' ) );

		$this->assertFalse( is_file( __DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs/index.smt1.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs1/index.smt2.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs2/_tab/index.html' ) );

		$file = file_get_contents(__DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs/index.smt1.html');
		$this->assertTrue( !!preg_match( '/<p>USER_AGENT: iPhone1\/PicklesCrawler<\/p>/s', $file ) );

		$file = file_get_contents(__DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs1/index.smt2.html');
		$this->assertTrue( !!preg_match( '/<p>USER_AGENT: iPhone2\/PicklesCrawler<\/p>/s', $file ) );

		$file = file_get_contents(__DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs2/_tab/index.html');
		$this->assertTrue( !!preg_match( '/<p>USER_AGENT: iPad\/PicklesCrawler<\/p>/s', $file ) );

		$this->assertEquals(
			md5_file(__DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs/index.smt1.html'),
			md5_file(__DIR__.'/testdata/skip_default_device/px-files/dist/index.smt1.html')
		);
		$this->assertEquals(
			md5_file(__DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs1/index.smt2.html'),
			md5_file(__DIR__.'/testdata/skip_default_device/px-files/dist_smt/index.smt2.html')
		);
		$this->assertEquals(
			md5_file(__DIR__.'/testdata/skip_default_device/px-files/_sys/ram/publish/htdocs2/_tab/index.html'),
			md5_file(__DIR__.'/testdata/skip_default_device/px-files/dist_tab/_tab/index.html')
		);

		// パブリッシュしたスタイルシート内のパス解決
		$this->assertFalse( is_file( __DIR__.'/testdata/skip_default_device/px-files/dist/common/styles/path_resolve.css' ) );
		$this->assertTrue( is_file( __DIR__.'/testdata/skip_default_device/px-files/dist_smt/common/styles/path_resolve.smt2.css' ) );
		$this->assertTrue( is_file( __DIR__.'/testdata/skip_default_device/px-files/dist_tab/common/styles/_tab/path_resolve.css' ) );
		$output = file_get_contents( __DIR__.'/testdata/skip_default_device/px-files/dist_smt/common/styles/path_resolve.smt2.css' );
		// var_dump($output);
		$this->assertEquals( 1, preg_match('/'.preg_quote('background-image: url( "./././../images/path_not_resolved(1).gif" );', '/').'/s', $output) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('background-image: url("./../images/path_resolved(2).gif");', '/').'/s', $output) );
		$output = file_get_contents( __DIR__.'/testdata/skip_default_device/px-files/dist_tab/common/styles/_tab/path_resolve.css' );
		// var_dump($output);
		$this->assertEquals( 1, preg_match('/'.preg_quote('background-image: url( "./././../images/path_not_resolved(1).gif" );', '/').'/s', $output) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('background-image: url("./../../images/_tab/path_resolved(2).gif");', '/').'/s', $output) );

	}//testPublishMultiDevice();

}
