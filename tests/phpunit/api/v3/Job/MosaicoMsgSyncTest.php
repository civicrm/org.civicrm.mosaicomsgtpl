<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use CRM_Mosaicomsgtpl_ExtensionUtil as E;

/**
 * Job.mosaico_msg_sync API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_Job_MosaicoMsgSyncTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  /**
   * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
   * See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install(['org.civicrm.flexmailer', 'uk.co.vedaconsulting.mosaico', 'org.civicrm.mosaicomsgtpl'])
      ->apply();
  }

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * The tearDown() method is executed after the test was executed (optional)
   * This can be used for cleanup.
   */
  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Synchronize all msg templates.
   */
  public function testUpdateAll() {
    $this->assertEquals('MosaicoTemplate', CRM_Core_DAO_AllCoreTables::getBriefName('CRM_Mosaico_DAO_MosaicoTemplate'));

    $myHtml = '<p>placeholder</p>';
    $first = $this->createMosaicoTemplate(array('title' => 'First example', 'html' => $myHtml));
    $second = $this->createMosaicoTemplate(array('title' => 'Second example', 'html' => $myHtml));

    $this->assertEquals(NULL, $first['msg_tpl_id']);
    $this->assertEquals(NULL, $second['msg_tpl_id']);
    $oldCount = CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_msg_template WHERE msg_html = %1', array(1 => array($myHtml, 'String')));

    $result = civicrm_api3('Job', 'mosaico_msg_sync', array());

    $newCount = CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_msg_template WHERE msg_html = %1', array(1 => array($myHtml, 'String')));
    $this->assertEquals(2, $result['values']['processed']);
    $this->assertEquals(2 + $oldCount, $newCount);

    $getResult = civicrm_api3('MosaicoTemplate', 'get', array());
    $this->assertEquals(2, count($getResult['values']));
    foreach ($getResult['values'] as $value) {
      $this->assertTrue(is_numeric($value['msg_tpl_id']));
      $msgTpl = civicrm_api3('MessageTemplate', 'getsingle', array('id' => $value['msg_tpl_id']));
      $this->assertEquals($myHtml, $msgTpl['msg_html']);
    }
  }

  /**
   * Synchronize one msg templates.
   */
  public function testUpdateOne() {
    $this->assertEquals('MosaicoTemplate', CRM_Core_DAO_AllCoreTables::getBriefName('CRM_Mosaico_DAO_MosaicoTemplate'));

    $first = $this->createMosaicoTemplate(array('title' => 'First example'));
    $second = $this->createMosaicoTemplate(array('title' => 'Second example'));

    $this->assertEquals(NULL, $first['msg_tpl_id']);
    $this->assertEquals(NULL, $second['msg_tpl_id']);
    $oldCount = CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_msg_template');

    $result = civicrm_api3('Job', 'mosaico_msg_sync', array(
      'id' => $second['id'],
    ));

    $newCount = CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_msg_template');
    $this->assertEquals(1, $result['values']['processed']);
    $this->assertEquals(1 + $oldCount, $newCount);
  }

  /**
   * Test clones work.
   */
  public function testClone() {
    $this->assertEquals('MosaicoTemplate', CRM_Core_DAO_AllCoreTables::getBriefName('CRM_Mosaico_DAO_MosaicoTemplate'));

    // Create the first template and run the sync.
    $first = $this->createMosaicoTemplate(array('title' => 'First example'));
    $result = civicrm_api3('Job', 'mosaico_msg_sync', ['id' => $first['id']]);
    $oldCount = CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_msg_template');

    // Create the clone and run the sync on it passing is_new (which is what the post hook does).
    $second = civicrm_api3('MosaicoTemplate', 'clone', ['id' => $first['id'], 'title' => 'clone']);
    $result = civicrm_api3('Job', 'mosaico_msg_sync', ['id' => $second['id'], 'is_new' => TRUE]);

    // Count templates - should be one more.
    $newCount = CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_msg_template');
    $this->assertEquals(1 + $oldCount, $newCount);
  }


  /**
   * Test deletion of message template corresponding to masaico template
   */
  public function testDelete() {
    $this->assertEquals('MosaicoTemplate', CRM_Core_DAO_AllCoreTables::getBriefName('CRM_Mosaico_DAO_MosaicoTemplate'));

    // Create the first template and run the sync.
    $first = $this->createMosaicoTemplate(array('title' => 'First example'));
    civicrm_api3('Job', 'mosaico_msg_sync', ['id' => $first['id']]);

    // Delete mosaico template
    civicrm_api3('MosaicoTemplate', 'delete', array('id' => $first['id']));

    // make sure message template is deleted
    $message = NULL;
    try {
      // A significant number of times this will fail if there are no message templates matching the criteria with a `CiviCRM_API3_EXCEPTION`.
      $result = civicrm_api3('MessageTemplate', 'getsingle', ['id' => $first['msg_tpl_id']]);
      $this->assertEquals($result['count'], 0);
    }
    catch (CiviCRM_API3_Exception $e) {
      $message = $e->getMessage();
    }
    // If this exception is returned it means Civi cannot find a MessageTemplate matching the criteria - this is what we want/expect.
    $this->assertContains('Expected one MessageTemplate but found 0', $message);
  }

  /**
   * Test title/subject parsing.
   *
   * @dataProvider titleSubjectParsingProvider
   */
  public function testTitleSubjectParsing($input, $title, $subject) {
    $this->assertEquals('MosaicoTemplate', CRM_Core_DAO_AllCoreTables::getBriefName('CRM_Mosaico_DAO_MosaicoTemplate'));

    $first = $this->createMosaicoTemplate(array('title' => $input));
    civicrm_api3('Job', 'mosaico_msg_sync', ['id' => $first['id']]);

    // Reload to get the msg_tpl_id.
    $first = civicrm_api3('MosaicoTemplate', 'getsingle', ['id' => $first['id']]);
    $this->assertGreaterThan(0, (int) $first['msg_tpl_id']);

    $tpl = civicrm_api3('MessageTemplate', 'getsingle', ['id' => $first['msg_tpl_id']]);
    $this->assertEquals($title, $tpl['msg_title']);
    $this->assertEquals($subject, $tpl['msg_subject']);
  }
  /**
   * Data provider
   */
  public function titleSubjectParsingProvider() {
    return [
      ['input' => 'A simple subject', 'title' => 'A simple subject', 'subject' => 'A simple subject'],
      ['input' => 'internal title|public subject', 'title' => 'internal title', 'subject' => 'public subject'],
      ['input' => 'internal title | public subject', 'title' => 'internal title', 'subject' => 'public subject'],
      ['input' => 'internal title | public subject | with pipe', 'title' => 'internal title', 'subject' => 'public subject | with pipe'],
    ];
  }

  protected function createMosaicoTemplate($params = array()) {
    $defaults = array(
      'title' => 'The Title',
      'base' => 'versafix-1',
      'html' => '<p>placeholder</p>',
      'metadata' => json_encode(array('template' => 'placeholder')),
      'content' => json_encode(array('template' => 'placeholder')),
    );
    $msgTpl = civicrm_api3('MosaicoTemplate', 'create', array_merge($defaults, $params));
    return $msgTpl['values'][$msgTpl['id']];
  }
}
