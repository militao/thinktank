<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/Loremipsum/LoremIpsum.class.php';


require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PDODAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PostErrorMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';

/**
 * Test PostErrorMySQLDAO
 * 
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfPostErrorMySQLDAO extends ThinkTankUnitTestCase {

    function _construct() {
        $this->UnitTestCase('PostErrorMySQLDAO class test');
    }

    function setUp() {
        parent::setUp();
    }

    function tearDown() {
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    function testConstructor(){
        $dao = new PostErrorMySQLDAO();
        $this->assertTrue(isset($dao));

    }

    /**
     * Test error insertion
     */
    function testInsert() {
        $dao = new PostErrorMySQLDAO();
        $result = $dao->insertError(10, 404, 'Status not found', 930061);
        $this->assertEqual($result, 1);

        $result = $dao->insertError(11, 403, 'You are not autorized to see this status', 930061);
        $this->assertEqual($result, 2);
    }
}