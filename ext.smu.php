<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'helpers/base.smu.php';

/**
 * Social Media Updater extention class
 * @author glen
 */
class Smu_ext extends SMU_Base {
  var $name = 'smu';
  var $version = '1.0';
  var $description = 'Social media auto updater for channel entries.';
  var $settings_exist = 'n';
  var $docs_url = '';

  var $settings = array();

  /**
   * Install and activate the extension
   * @return void
   */
  function activate_extension() {
    $this->settings = array();
    $data = array(
        'class'     => __CLASS__,
        'method'    => 'push_to_media',
        'hook'      => 'after_channel_entry_save',
        'settings'  => serialize($this->settings),
        'priority'  => 10,
        'version'   => $this->version,
        'enabled'   => 'y'
    );
    ee()->db->insert('extensions', $data);
  }

  /**
   * Update extensions
   * @return void
   */
  function update_extension($current) {
    if ($current == '' OR $current == $this->version) {
        return FALSE;
    }

    if ($current < '1.0') {
        // Update to version 1.0
    }

    ee()->db->where('class', __CLASS__);
    ee()->db->update('extensions', array('version' => $this->version));
  }

  /**
   * Disable the extensions
   * @return void
   */
  function disable_extension() {
      ee()->db->where('class', __CLASS__);
      ee()->db->delete('extensions');
  }

  /**
   * Do the sharing
   * @param int  $entry the entry ID
   * @param array $meta the entry's meta data
   * @param array $data the entry's submitted data
   * @param string $view_url the redirect url
   * @return void
   */
  function push_to_media_old($entry_id, $meta, $data, $view_url) {
    $current_settings = parent::getSettings();
    $default_share_url = $this->getDefaultShareURL($entry_id, $meta, $data, $current_settings->default_url);

    // Trigger only if entry's status is same in the settings else nope.
    if(!empty($current_settings) && isset($current_settings->entry_statuses) && in_array($data['revision_post']['status'], $current_settings->entry_statuses)) {

      // Fire LinkedIn
      if(isset($current_settings->platforms['linkedin'])) {
        $smu = ee()->db->where('entry_id', $entry_id)->limit(1)->get('smu')->first_row();

        // Run if new or repost is checked
        if((empty($smu) || (!empty($smu) && isset($data['smu']['repost']))) && !empty($data['smu']['content'])) {
          $api_urls = array(
            'profile' => 'https://api.linkedin.com/v1/people/~/shares',
            'company' => 'https://api.linkedin.com/v1/companies/'.$current_settings->platforms['linkedin']['accounts']['company'].'/shares'
          );

          // Start sharing to LinkedIn
          $linkedin = parent::curl($api_urls[$current_settings->platforms['linkedin']['accounts']['post_to']].'?oauth2_access_token='.$current_settings->platforms['linkedin']['accounts']['access_token'].'&format=json',
            json_encode(array(
              'comment' => $data['smu']['content'] . ' ' . $default_share_url,
              'visibility' => array(
                'code' => 'anyone'
              )
            )),
            array(
              'Content-Type: application/json',
              'x-li-format: json'
            )
          );

          // Update or Insert
          if(count($smu) > 0) {
            $this->save($entry_id, false, array(
              'details' => serialize((ee()->input->post('smu')) ? ee()->input->post('smu') : array()),
              'platform' => 'linkedin',
              'response' => serialize(json_decode($linkedin))
            ));
          } else {
            $this->save($entry_id, true, array(
              'entry_id' => $entry_id,
              'details' => serialize((ee()->input->post('smu')) ? ee()->input->post('smu') : array()),
              'platform' => 'linkedin',
              'response' => serialize(json_decode($linkedin))
            ));
          }
        }
      }
    }
  }

  /**
   * Do the sharing
   * @param obj  $entry
   * @param array $data
   * @return void
   */
  function push_to_media($entry, $data) {
      $current_settings = parent::getSettings();
      $entry_id = $entry->entry_id;
      $meta = array('channel_id' => $entry->channel_id, 'url_title' => $entry->url_title);
      $default_share_url = $this->getDefaultShareURL($entry_id, $meta, $data, $current_settings->default_url);

      // Trigger only if entry's status is same in the settings else nope.
      if(!empty($current_settings) && isset($current_settings->entry_statuses) && in_array($data['status'],$current_settings->entry_statuses)) {
          echo 'smu triggered status!<br><br>';
          // Fire LinkedIn
          if(isset($current_settings->platforms['linkedin'])) {
              echo 'linkedin is on<br><br>';

              $smu = ee()->db->where('entry_id', $entry_id)->limit(1)->get('smu')->first_row();

              // Run if new or repost is checked
              if((empty($smu) || (!empty($smu) && isset($data['smu']['repost']))) && !empty($data['smu']['content'])) {
                  echo 'new/repost<br><br>';

                $api_urls = array(
                      'profile' => 'https://api.linkedin.com/v1/people/~/shares',
                      'company' => 'https://api.linkedin.com/v1/companies/'.$current_settings->platforms['linkedin']['accounts']['company'].'/shares'
                );

                // Start sharing to LinkedIn
                $linkedin = parent::curl($api_urls[$current_settings->platforms['linkedin']['accounts']['post_to']].'?oauth2_access_token='.$current_settings->platforms['linkedin']['accounts']['access_token'].'&format=json',
                  json_encode(array(
                    'comment' => $data['smu']['content'] . ' ' . $default_share_url,
                    'visibility' => array(
                      'code' => 'anyone'
                    )
                  )),
                  array(
                    'Content-Type: application/json',
                    'x-li-format: json'
                  )
                );

                // Update or Insert
                if(count($smu) > 0) {
                  $this->save($entry_id, false, array(
                    'details' => serialize((ee()->input->post('smu')) ? ee()->input->post('smu') : array()),
                    'platform' => 'linkedin',
                    'response' => serialize(json_decode($linkedin))
                  ));
                } else {
                  $this->save($entry_id, true, array(
                    'entry_id' => $entry_id,
                    'details' => serialize((ee()->input->post('smu')) ? ee()->input->post('smu') : array()),
                    'platform' => 'linkedin',
                    'response' => serialize(json_decode($linkedin))
                  ));
                }
            }
          }
      }
  }
  /**
   * Get channel details
   * @param channel_id [int] the channel id to look for
   * @return [object] the channel details object
   */
  private function getChannelDetails($channel_id) {
    $channels = ee()->db->select('*')->from('channels')->where('channel_id', $channel_id)->get();
    return $channels->first_row();
  }

  /**
   * Get default share url base from the entry details and smu settings
   * @param entry_id [int] the entry ID
   * @param meta [array] the entrys meta details
   * @param data [array] the entry data
   * @param type [string] the url type
   * @return [string] url
   */
  private function getDefaultShareURL($entry_id, $meta, $data, $type) {

    $channel = $this->getChannelDetails($meta['channel_id']);
    $share_url = '';
    if($type == 'url_title') {
      //$share_url = $this->addBaseURL($this->addSlashToEnd($channel->channel_url)) . $meta['url_title'];
      $share_url = $this->addSlashToEnd($this->addBaseURL($channel->channel_url)) . $meta['url_title'] . '/';
    } else if($type == 'entry_id') {
      $share_url = $this->addBaseURL($this->addSlashToEnd($channel->channel_url)) . $entry_id;
    } else if($type == 'channel_url') {
      $share_url = $this->addBaseURL($channel->channel_url);
    } else {
      $share_url = ee()->functions->create_url('');
    }
    return (isset($data['smu']['url']) && !empty($data['smu']['url'])) ? $data['smu']['url'] : $share_url;
  }

  /**
   * Add slash to end of base url
   * @param url [string]
   * @return string
   */
  private function addSlashToEnd($url) {
    return (substr($url, (strlen($url) -1)) == '/') ? $url : $url . '/';
  }

  /**
   * Remove slash in front
   * @param segment [string]
   * @return string
   */
  private function removeSlashInFront($segment) {
    return (substr($segment, 0, 1) == '/') ? substr($segment, 1, strlen($segment)-1) : $segment;
  }

  /**
   * Add base url
   * @param url [string]
   * @return string
   */
  private function addBaseURL($url) {
    $base_url = ee()->functions->create_url('');
    if(filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
      if($this->isRelative($url)) {
        return ee()->functions->create_url($url);
      } elseif($this->urlHasNoProtocol($url)) {
        return 'http://'.$url;
      } else {
        return $url;
      }
    }
    return $url;
  }

  /**
   * Checks url if it has no protocol
   * @param url [string]
   * @return boolean
   */
  private function urlHasNoProtocol($url) {
    return ((strpos($url, 'http') === FALSE) || (strpos($url, 'https') === FALSE));
  }

  /**
   * Checks url if its relative i.e (/some/segment)
   * @param url [string]
   * @return boolean
   */
  private function isRelative($url) {
    return (strpos($url, $_SERVER['SERVER_NAME']) === FALSE && strpos($url, ee()->functions->create_url('')) === FALSE);
  }

  /**
   * Save API call details
   * @param int $entry_id the entry's ID
   * @param boolean $is_new the marker to check if insert or update
   * @param array $data array the data to insert or update
   * @return void
   */
  function save($entry_id, $is_new, $data) {
    if($is_new) {
      ee()->db->insert('smu', array(
        'entry_id' => $entry_id,
        'details' => $data['details'],
        'platform' => $data['platform'],
        'response' => $data['response']
      ));
    } else {
      ee()->db->where('entry_id', $entry_id);
      ee()->db->update('smu', array(
        'details' => $data['details'],
        'platform' => $data['platform'],
        'response' => $data['response']
      ));
    }
  }
}

/* End of file ext.smu.php */
/* Location: ./system/expressionengine/third_party/smu/ext.smu.php */
