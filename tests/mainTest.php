<?php
/**
 * test for tomk79\px2-publish-for-multi-device
 */
class mainTest extends PHPUnit_Framework_TestCase{
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
			__DIR__.'/testdata/standard/.px_execute.php' ,
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
			__DIR__.'/testdata/standard/.px_execute.php' ,
			'/?PX=publish.run' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->helper->common_error( $output ) );

		$this->assertTrue( is_dir( __DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs/' ) );
		$this->assertTrue( is_dir( __DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs1/' ) );
		$this->assertTrue( is_dir( __DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs2/' ) );

		$this->assertTrue( is_file( __DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs/index.smt2.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs/default_only/default.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs/default_only/default.smt2.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs1/index.smt.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs1/default_only/default.smt.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs2/_tab/index.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs2/_tab/default_only/default.html' ) );

		$file = file_get_contents(__DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs/index.html');
		$this->assertTrue( !!preg_match( '/<p>USER_AGENT: <\/p>/s', $file ) );

		$file = file_get_contents(__DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs/index.smt2.html');
		$this->assertTrue( !!preg_match( '/<p>USER_AGENT: iPhone2\/PicklesCrawler<\/p>/s', $file ) );

		$file = file_get_contents(__DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs1/index.smt.html');
		$this->assertTrue( !!preg_match( '/<p>USER_AGENT: iPhone\/PicklesCrawler<\/p>/s', $file ) );

		$file = file_get_contents(__DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs2/_tab/index.html');
		$this->assertTrue( !!preg_match( '/<p>USER_AGENT: iPad\/PicklesCrawler<\/p>/s', $file ) );

		$this->assertEquals(
			md5_file(__DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs/index.html'),
			md5_file(__DIR__.'/testdata/standard/px-files/dist/index.html')
		);
		$this->assertEquals(
			md5_file(__DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs/index.smt2.html'),
			md5_file(__DIR__.'/testdata/standard/px-files/dist/index.smt2.html')
		);
		$this->assertEquals(
			md5_file(__DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs1/index.smt.html'),
			md5_file(__DIR__.'/testdata/standard/px-files/dist_smt/index.smt.html')
		);
		$this->assertEquals(
			md5_file(__DIR__.'/testdata/standard/px-files/_sys/ram/publish/htdocs2/_tab/index.html'),
			md5_file(__DIR__.'/testdata/standard/px-files/dist_tab/_tab/index.html')
		);

	}//testPublishMultiDevice();

}
