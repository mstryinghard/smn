<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'helpers/base.smu.php';

/**
 * Social Media Updater fieldtype class
 * @author glen
 */
class Smu_ft extends EE_Fieldtype {

  var $info = array(
      'name'      => 'Social Media Updater',
      'version'   => '1.0',
      'settings_exist' => 'n'
  );
  var $delimiter = '[smu:pipe]';
  var $key = '[smu:key]';

  public function __construct() {
      parent::__construct();
  }
  /**
   * Display fieldtype
   * @param data array the data of the field
   * @return string the generated html field.
   */
  function display_field($data) {
    $helper = new SMU_Base();
    $settings = $helper->getSettings();
    $vars = array();
    $vars['data'] = $this->parse_data($data);
    $vars['smu'] = ee()->db->where('entry_id', $this->content_id)->limit(1)->get('smu')->first_row();
    $vars['field_data'] = $data;
    $vars['delimiter'] = $this->delimiter;
    $vars['key'] = $this->key;
    $vars['field_name'] = $this->field_name;
    $vars['field_id'] = $this->field_id;
    $vars['enabled'] = (!empty($settings)) ? TRUE : FALSE;
    $vars['urls'] = $helper->getModUrls();

    return ee()->load->view('fieldtype', $vars, TRUE);
  }

  /**
   * Parse custom date and convert to array
   * @param data string the custom data string
   * @return array converted data
   */
  function parse_data($data) {
    $result = array();
    if(!empty($data)) {
      $data_arr = explode($this->delimiter, $data);
      if(count($data_arr)) {
        foreach ($data_arr as $key => $value) {
          $temp = explode($this->key, $value);
          $result[$temp[0]] = (isset($temp[1])) ? $temp[1] : '';
        }
      }
    }
    return $result;
  }

  /**
   * Individual field settings
   * @return [type] [description]
   */
  function display_settings($data) {
        $platform   = isset($data['platform']) ? $data['platform'] : $this->settings['platform'];
        $settings = array(
            array(
                'title' => 'Platform',
                'desc' => 'Select Platform',
                'fields' => array(
                    'platform' => array(
                        'type' => 'select',
                        'choices' => array(
                                    'linkedin' => 'LinkedIn',
                                    'facebook' => 'Facebook'
                                ),
                        'value' => $platform,
                    )
                )
            )
        );

        return array('field_options_smu' => array(
            'label' => 'field_options',
            'group' => 'smu',
            'settings' => $settings
        ));
  }
  /**
   * Save individual settings
   * @param  [type] $data [description]
   * @return [type]       [description]
   */
  function save_settings($data) {

  }

  /**
   * Install fieldtype
   * @return array fieldtype default settings
   */
  function install() {
    return array(
        'smu_linkedin_enabled'  => 'yes',
        'platform'              => '',
    );
  }

}

/* End of file ft.smu.php */
/* Location: ./system/expressionengine/third_party/smu/ft.smu.php */
