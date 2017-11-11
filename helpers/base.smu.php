<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'Cttp.php';

/**
 * Social Media Updater base class
 *
 * @author glen
 */
class SMU_Base
{

    /**
     * Cttp
     *
     * @return Cttp
     */
    public function http()
    {
        return new Cttp();
    }

    function curl($url, $data, $headers, $is_post = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if($is_post) {
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

  /**
   * Get smu settings
   * @return [object] the save smu settings
   */
  public function getSettings() {
    $results = ee()->db->where('class', 'Smu_ext')->limit(1)->get('extensions')->first_row();
    $settings = unserialize($results->settings);
    return (!empty($settings)) ? (object)$settings : array();
  }

  public function saveModUrls($data) {
    $urls = $this->getModUrls();
    if(empty($urls)) {
      ee()->db->insert('smu_urls', $data);
    } else {
      ee()->db->where('id', $urls->id);
      ee()->db->update('smu_urls', $data);
    }
  }

  public function getModUrls() {
    return ee()->db->get('smu_urls')->first_row();
  }
}
