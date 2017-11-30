<?php
use CRM_Mosaicomsgtpl_ExtensionUtil as E;

/**
 * Job.mosaico_msg_sync API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_job_mosaico_msg_sync_spec(&$spec) {
  $spec['id']['api.required'] = 0;
}

/**
 * Job.mosaico_msg_sync API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_job_mosaico_msg_sync($params) {
  $count = 0;
  CRM_Core_Transaction::create()->run(function () use ($params, &$count) {

    $existingMosTplParams = array('options' => array('limit' => 0));
    if (isset($params['id'])) {
      $existingMosTplParams['id'] = CRM_Utils_Type::validate($params['id'], 'Positive');
    }
    $existingMosTpls = civicrm_api3('MosaicoTemplate', 'get', $existingMosTplParams);

    foreach ($existingMosTpls['values'] as $existingMosTpl) {

      if (isset($existingMosTpl['msg_tpl_id'])) {
        civicrm_api3('MessageTemplate', 'create', array(
          'id' => $existingMosTpl['msg_tpl_id'],
          'msg_html' => $existingMosTpl['html'],
        ));
      }
      else {
        $newTpl = array();
        $newTpl['msg_title'] = $existingMosTpl['title'];
        $newTpl['msg_subject'] = $existingMosTpl['title'];
        $newTpl['msg_html'] = $existingMosTpl['html'];
        $newTpl['is_reserved'] = 1;

        $newTplResult = civicrm_api3('MessageTemplate', 'create', $newTpl);

        CRM_Core_DAO::executeQuery('UPDATE civicrm_mosaico_template SET msg_tpl_id = %1 WHERE id = %2', array(
          1 => array($newTplResult['id'], 'Positive'),
          2 => array($existingMosTpl['id'], 'Positive'),
        ));
      }

      $count++;
    }
  });

  return civicrm_api3_create_success(array('processed' => $count), $params, 'Job', 'mosaico_msg_sync');
}
