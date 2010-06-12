<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
	require_once '../../../../tests/config.tests.inc.php';
}
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PluginHook.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Crawler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Webapp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/classes/mock.FlickrAPIAccessor.php';

require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkTankPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.CrawlerPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.WebappPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTab.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTabDataset.php';

require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/model/class.FlickrThumbnailsPlugin.php';
//require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/model/class.FlickrAPIAccessor.php';

/* Replicate all the global objects a plugin depends on; normally this is done in init.php */
// TODO Figure out a better way to do all this than global objects in init.php
$crawler = new Crawler();
$webapp = new Webapp();
// Instantiate global database variable
try {
	$db = new Database($THINKTANK_CFG);
	$conn = $db->getConnection();
}
catch(Exception $e) {
	echo $e->getMessage();
}

class TestOfFlickrThumbnailsPlugin extends ThinkTankUnitTestCase {

	function TestOfFlickrThumbnailsPlugin() {
		$this->UnitTestCase('FlickrThumbnailsPlugin class test');
	}

	function setUp() {
		global $webapp;
		global $crawler;
		global $THINKTANK_CFG;
		$webapp->registerPlugin('flickrthumbnails', 'FlickrThumbnailsPlugin');
		$crawler->registerCrawlerPlugin('FlickrThumbnailsPlugin');

		//use fake Flickr API key
		$THINKTANK_CFG['flickr_api_key'] = 'dummykey';


		parent::setUp();

		//Insert test links (not images, not expanded)
		$counter = 0;
		while ($counter < 40) {
			$post_id = $counter + 80;
			$pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

			$q = "INSERT INTO tt_links (url, title, clicks, post_id, is_image) VALUES ('http://example.com/".$counter."', 'Link $counter', 0, $post_id, 0);";
			$this->db->exec($q);

			$counter++;
		}

		//Insert test links (images on Flickr that don't exist, not expanded)
		$counter = 0;
		while ($counter < 2) {
			$post_id = $counter + 80;
			$pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

			$q = "INSERT INTO tt_links (url, title, clicks, post_id, is_image) VALUES ('http://flic.kr/p/".$counter."', 'Link $counter', 0, $post_id, 1);";
			$this->db->exec($q);

			$counter++;
		}

		// Insert legit Flickr shortened link, not expanded
		$q = "INSERT INTO tt_links (url, title, clicks, post_id, is_image) VALUES ('http://flic.kr/p/7QQBy7', 'Link', 0, 200, 1);";
		$this->db->exec($q);


		//Insert test links with errors (images from Flickr, not expanded)
		$counter = 0;
		while ($counter < 5) {
			$post_id = $counter + 80;
			$pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

			$q = "INSERT INTO tt_links (url, title, clicks, post_id, is_image, error) VALUES ('http://flic.kr/p/".$counter."', 'Link $counter', 0, $post_id, 1, 'Generic test error message, Photo not found');";
			$this->db->exec($q);

			$counter++;
		}

	}

	function tearDown() {
		parent::tearDown();
	}

	function testFlickrCrawl() {
		global $crawler;
		$crawler->crawl();

		$ldao = DAOFactory::getDAO('LinkDAO');

		$link = $ldao->getLinkById(43);
		$this->assertEqual($link->expanded_url, 'http://farm3.static.flickr.com/2755/4488149974_04d9558212_m.jpg');
		$this->assertEqual($link->error, '');

		$link = $ldao->getLinkById(42);
		$this->assertEqual($link->expanded_url, '');
		$this->assertEqual($link->error, 'No response from Flickr API');

		$link = $ldao->getLinkById(41);
		$this->assertEqual($link->expanded_url, '');
		$this->assertEqual($link->error, 'No response from Flickr API');
	}

}
?>
