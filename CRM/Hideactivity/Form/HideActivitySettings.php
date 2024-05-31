<?php

use CRM_Hideactivity_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Hideactivity_Form_HideActivitySettings extends CRM_Core_Form {

  /**
   * @throws \CRM_Core_Exception
   */
  public function buildQuickForm(): void {

    $sql = "SELECT * FROM civicrm_hide_activity ic";
    $result = CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);

    $params = array();
    while ($result->fetch()) {
      $params[$result->param_name] = $result->param_value;
    }
    
    // Populate the setting with the saved settings from the db
    $defaults = array(
      'hide_activity_id' => isset($params['hide_activity_id']) ? $params['hide_activity_id'] : '',
      'selected_roles' => isset($params['selected_roles']) ? $params['selected_roles'] : '',
    );

    $this->setDefaults($defaults);


    // add form elements
    $this->add('select', 
    'hide_activity_id', 
    'Choose the Activity to hide', 
    $this->getActivityTypeOptions(), 
    FALSE, // Required or not
    ['multiple' => 'multiple', 'class' => 'crm-select2', 'placeholder' => ts('- select -')] // Placeholder attribute
    );
    $this->add(
      'select', // field type
      'selected_roles', // field name
      'Select the users that are not able to see the Selected Activities', // field label
      $this->getRoles(), // list of options
      FALSE, // is required
      ['multiple' => 'multiple', 'class' => 'crm-select2', 'placeholder' => ts('- select -')]// additional attributes for multi-select
  );
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ],
    ]);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess(): void {
    // Get the submitted form values
    $values = $this->exportValues();
  
    // Convert the form values to comma-separated strings if they are arrays
    $values['hide_activity_id'] = is_array($values['hide_activity_id']) 
        ? implode(',', $values['hide_activity_id']) 
        : (string)$values['hide_activity_id'];
  
    $values['selected_roles'] = is_array($values['selected_roles']) 
        ? implode(',', $values['selected_roles']) 
        : (string)$values['selected_roles'];
  
    // Clear the table before inserting new values
    $sql = "TRUNCATE TABLE civicrm_hide_activity";
    CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
  
    // Insert each setting into the table
    foreach ($values as $key => $value) {
      $sql = "INSERT INTO civicrm_hide_activity (param_name, param_value) VALUES (%1, %2)";
      $params = [
        1 => [$key, 'String'],
        2 => [$value, 'String']
      ];
      CRM_Core_DAO::executeQuery($sql, $params);
    }
  
    // Notify the user of success
    CRM_Core_Session::setStatus(E::ts('Your settings have been saved.'), '', 'success'); 
    parent::postProcess();
  }
  

  // Get Option Values 
  public function getActivityTypeOptions(): array {
    $result = civicrm_api4('OptionValue', 'get', [
      'where' => [
        ['option_group_id', '=', 2],
      ],
      'checkPermissions' => FALSE,
    ]);

    $options = [];
    if (!empty($result)) {
      foreach ($result as $value) {
        $options[$value['value']] = E::ts($value['label']);
      }
    return $options;
    }
  }

  public function getRoles(): array {
    $wp_roles = wp_roles();
    $roles = [];
    foreach ($wp_roles->roles as $role_name => $role_info) {
        $roles[$role_name] = $role_info['name'];
    }
    return $roles;
  }



  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames(): array {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
