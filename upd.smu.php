<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Smn_upd {

  var $version = '1.0';

  /**
   * Install module
   * @return boolean
   */
  function install() {
    ee()->load->dbforge();

    $data = array(
      'module_name' => 'smn',
      'module_version' => $this->version,
      'has_cp_backend' => 'y',
      'has_publish_fields' => 'n'
    );

    ee()->db->insert('modules', $data);

    $data = array(
      'class'     => 'smn' ,
      'method'    => 'auth'
    );

    ee()->db->insert('actions', $data);

    $fields = array(
      'smn_id'   => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
      'entry_id' => array('type' => 'varchar', 'constraint' => '250'),
      'details' => array('type' => 'text', 'null' => TRUE),
      'platform' => array('type' => 'varchar', 'constraint' => '250'),
      'response' => array('type' => 'text', 'null' => TRUE)
    );

    ee()->dbforge->add_field($fields);
    ee()->dbforge->add_key('smn_id', TRUE);
    ee()->dbforge->create_table('smn');

    unset($fields);

    $fields = array(
      'id' => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
      'redirect_url' => array('type' => 'text', 'null' => TRUE),
      'module_cp_url' => array('type' => 'text', 'null' => TRUE)
    );

    ee()->dbforge->add_field($fields);
    ee()->dbforge->add_key('id', TRUE);
    ee()->dbforge->create_table('smn_urls');

    return TRUE;
  }

  /**
   * Uninstall module and remove all data associated with it.
   * @return boolean
   */
  function uninstall() {
    ee()->load->dbforge();

    ee()->db->select('module_id');
    $query = ee()->db->get_where('modules', array('module_name' => 'smn'));

    ee()->db->where('module_id', $query->row('module_id'));
    ee()->db->delete('module_member_groups');

    ee()->db->where('module_name', 'smn');
    ee()->db->delete('modules');

    ee()->db->where('class', 'smn');
    ee()->db->delete('actions');

    ee()->dbforge->drop_table('smn');
    ee()->dbforge->drop_table('smn_urls');

    return TRUE;
  }

  /**
   * Update module
   * @return boolean
   */
  function update($current = '1.0') {
      return FALSE;
  }
}

/* End of file upd.smn.php */
/* Location: ./system/expressionengine/third_party/smn/upd.smn.php */
