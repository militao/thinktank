<?php 
if (!isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Config.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PluginHook.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Crawler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Webapp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkTankPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.CrawlerPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/expandurls/model/class.ExpandURLsPlugin.php';


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

class TestOfExpandURLsPlugin extends ThinkTankUnitTestCase {

    function TestOfExpandURLsPlugin() {
        $this->UnitTestCase('ExpandURLs plugin class test');
    }
    
    function setUp() {
        global $crawler;
        parent::setUp();
        
        //Insert test links (not images, not expanded)
        $q = "INSERT INTO tt_links (url, title, clicks, post_id, is_image) VALUES ('http://bit.ly/a5VmbO', '', 0, 1, 0);";
        $this->db->exec($q);
        
        $crawler->registerCrawlerPlugin('ExpandURLsPlugin');
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function testExpandURLsCrawl() {
        global $crawler;
        $crawler->emitObjectMethod("crawl");
        
        //the crawler closes the log so we have to re-open it
        $logger = Logger::getInstance();
        $ldao = DAOFactory::getDAO('LinkDAO');
        
        $link = $ldao->getLinkById(1);
        $this->assertEqual($link->expanded_url, 'http://www.thewashingtonnote.com/archives/2010/04/communications/');
        $this->assertEqual($link->error, '');
    }
}
?>
