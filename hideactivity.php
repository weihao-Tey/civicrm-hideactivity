<?php

require_once 'hideactivity.civix.php';

use CRM_Hideactivity_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function hideactivity_civicrm_config(&$config): void {
  _hideactivity_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function hideactivity_civicrm_install(): void {
  _hideactivity_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function hideactivity_civicrm_enable(): void {
  _hideactivity_civix_civicrm_enable();
}

function getCurrentUsersRole(){
  $current_user = wp_get_current_user();
  $roleArray = $current_user->roles;
  $role = implode(",", $roleArray);
  return $role;
}

function getoptionActivityID(){
  $optionGroup = civicrm_api4('OptionGroup', 'get', [
    'select' => [
      'id',
    ],
    'where' => [
      ['title', '=', 'Activity Type'],
    ],
    'checkPermissions' => FALSE,
  ], 0);

  return $optionGroup['id'] ?? NULL;
}

function getHideActivitySettings($param){
  $settings = [];
        
    $sql = "SELECT * FROM civicrm_hide_activity";
    $result = CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);

    while ($result->fetch()) {
        // Store each setting in the associative array.
        $settings[$result->param_name] = $result->param_value;
    }

    return $settings[$param] ?? NULL;
}


function hideactivity_civicrm_selectWhereClause(string $entity, array &$clauses, int $userId, array $conditions){

  switch($entity) {
    case 'Activity':
      $ROLE = getCurrentUsersRole();
      $HidetoRole = explode(",",getHideActivitySettings('selected_roles'));
      $ActivitytoHide =explode(",",getHideActivitySettings('hide_activity_id'));

      if(!empty($HidetoRole)){
        if(!empty($ActivitytoHide)){
          if (in_array($ROLE, $HidetoRole)){
            $labelArray = [];
            foreach($ActivitytoHide as $activity){
              $clauses['activity_type_id'][] = ['!=' . $activity];
              
              $optionValue = civicrm_api4('OptionValue', 'get', [
                'select' => [
                  'label',
                ],
                'where' => [
                  ['option_group_id', '=', getoptionActivityID()],
                  ['value', '=', $activity],
                ],
                'checkPermissions' => FALSE,
              ], 0);

              $labelArray[] = $optionValue['label'];
            }
            CRM_Core_Resources::singleton()->addVars('hideActions', $labelArray);
            CRM_Core_Resources::singleton()->addScriptFile('hideactivity', 'hide.js');
          }
        }
      }
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function hideactivity_civicrm_navigationMenu(&$menu) {
  _hideactivity_civix_insert_navigation_menu($menu, 'Administer/System Settings', array(
    'label' => ts('Hide Activity Settings'),
    'name' => 'hide_activity',
    'url' => 'civicrm/hideactivitysettings?reset=1',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _hideactivity_civix_navigationMenu($menu);
}