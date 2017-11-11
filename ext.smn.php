<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'helpers/Base.php';

/**
 * Social media notifier extention class
 *
 * @author glen
 */
class Smn_ext extends Base
{
    protected $name = 'smn';
    protected $version = '1.0';
    protected $description = 'Social media notifier.';
    protected $settings_exist = 'n';
    protected $docs_url = '';

    protected $settings = [];

    /**
     * Install and activate the extension
     *
     * @return void
     */
    public function activate_extension()
    {
        ee()->db->insert('extensions', [
            'class' => __CLASS__,
            'method' => 'push_to_media',
            'hook' => 'after_channel_entry_save',
            'settings' => serialize($this->settings),
            'priority' => 10,
            'version' => $this->version,
            'enabled' => 'y'
        ]);
    }

    /**
     * Update extensions
     *
     * @return void
     */
    public function update_extension($current)
    {
        if ($current == '' || $current == $this->version) {
            return FALSE;
        }

        if ($current < '1.0') {
            // Update to version 1.0
        }

        ee()->db->where('class', __CLASS__);
        ee()->db->update('extensions', ['version' => $this->version]);
    }

    /**
     * Disable the extensions
     * @return void
     */
    public function disable_extension() {
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('extensions');
    }

    /**
     * Do the sharing
     *
     * @param object  $entry
     * @param array $data
     * @return void
     */
    public function push_to_media($entry, $data)
    {
        $current_settings = $this->getSettings();
        $entry_id = $entry->entry_id;

        $meta = [
            'channel_id' => $entry->channel_id,
            'url_title' => $entry->url_title
        ];

        $default_share_url = $this->getDefaultShareURL($entry_id, $meta, $data, $current_settings->default_url);

        // Trigger only if entry's status is same in the settings
        if(!empty($current_settings) &&
            isset($current_settings->entry_statuses) &&
            in_array($data['status'],$current_settings->entry_statuses)) {

            echo 'smn triggered status!<br><br>';
            // Fire LinkedIn
            if(isset($current_settings->platforms['linkedin'])) {
                echo 'linkedin is on<br><br>';

                $smn = ee()->db->where('entry_id', $entry_id)->limit(1)->get('smn')->first_row();

                // Run if new or repost is checked
                if((empty($smn) || (!empty($smn) && isset($data['smn']['repost']))) && !empty($data['smn']['content'])) {
                    echo 'new/repost<br><br>';

                    $api_urls = [
                        'profile' => 'https://api.linkedin.com/v1/people/~/shares',
                        'company' => 'https://api.linkedin.com/v1/companies/'.$current_settings->platforms['linkedin']['accounts']['company'].'/shares'
                    ];

                    // Start sharing to LinkedIn
                    $linkedin_api_url = $api_urls[$current_settings->platforms['linkedin']['accounts']['post_to']].'?oauth2_access_token='.$current_settings->platforms['linkedin']['accounts']['access_token'].'&format=json';
                    $linkedin_payload = json_encode([
                        'comment' => $data['smn']['content'] . ' ' . $default_share_url,
                        'visibility' => ['code' => 'anyone']
                    ]);
                    $linkedin_headers = [
                        'Content-Type: application/json',
                        'x-li-format: json'
                    ];
                    $linkedin = $this->http()->withHeaders($linkedin_headers)->post($linkedin_api_url, $linkedin_payload);


                    // Update or Insert
                    $save_data = [
                        'details' => serialize((ee()->input->post('smn')) ? ee()->input->post('smn') : []),
                        'platform' => 'linkedin',
                        'response' => serialize(json_decode($linkedin))
                    ];

                    if (count($smn) == 0) {
                        $save_data = array_merge($save_data, ['entry_id' => $entry_id]);
                    }

                    $this->save($entry_id, count($smn) == 0, $save_data);

                }
            }
        }
    }

    /**
     * Get channel details
     *
     * @param  int $channel_id                          the channel's id to lookup
     * @return object
     */
    private function getChannelDetails($channel_id)
    {
        $channels = ee()->db->select('*')->from('channels')->where('channel_id', $channel_id)->get();
        return $channels->first_row();
    }

    /**
     * Get default share url base from the entry details and smn settings
     *
     * @param  int $entry_id                            the entry's id
     * @param  array $meta                              the meta data
     * @param  array $data                              the data
     * @param  string $type                             the type of url to share
     * @return string
     */
    private function getDefaultShareURL($entry_id, $meta, $data, $type)
    {

        $channel = $this->getChannelDetails($meta['channel_id']);
        $share_url = ee()->functions->create_url('');

        if($type == 'url_title') {
            $share_url = $this->addSlashToEnd($this->addBaseURL($channel->channel_url)) . $meta['url_title'] . '/';
        } else if($type == 'entry_id') {
            $share_url = $this->addBaseURL($this->addSlashToEnd($channel->channel_url)) . $entry_id;
        } else if($type == 'channel_url') {
            $share_url = $this->addBaseURL($channel->channel_url);
        }

        return (isset($data['smn']['url']) && !empty($data['smn']['url'])) ? $data['smn']['url'] : $share_url;
    }

    /**
     * Add slash to end of base url
     *
     * @param string $url
     * @return string
     */
    private function addSlashToEnd($url)
    {
        return (substr($url, (strlen($url) -1)) == '/') ? $url : $url . '/';
    }

    /**
     * Remove slash in front
     *
     * @param string $segment
     * @return string
     */
    private function removeSlashInFront($segment)
    {
        return (substr($segment, 0, 1) == '/') ? substr($segment, 1, strlen($segment)-1) : $segment;
    }

    /**
    * Add base url
    *
    * @param string $url
    * @return string
    */
    private function addBaseURL($url)
    {
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
     *
     * @param string $url
     * @return boolean
     */
    private function urlHasNoProtocol($url)
    {
        return ((strpos($url, 'http') === FALSE) || (strpos($url, 'https') === FALSE));
    }

    /**
     * Checks url if its relative i.e (/some/segment)
     *
     * @param string $url
     * @return boolean
     */
    private function isRelative($url)
    {
        return (strpos($url, $_SERVER['SERVER_NAME']) === FALSE && strpos($url, ee()->functions->create_url('')) === FALSE);
    }

    /**
     * Save API call details
     *
     * @param int $entry_id                              the entry's ID
     * @param boolean $is_new                            the marker to check if insert or update
     * @param array $data                                the data to insert or update
     * @return void
     */
    protected function save($entry_id, $is_new, $data) {
        if($is_new) {
            ee()->db->insert('smn', array(
                'entry_id' => $entry_id,
                'details' => $data['details'],
                'platform' => $data['platform'],
                'response' => $data['response']
            ));
        } else {
            ee()->db->where('entry_id', $entry_id);
            ee()->db->update('smn', array(
                'details' => $data['details'],
                'platform' => $data['platform'],
                'response' => $data['response']
            ));
        }
    }

}

/* End of file ext.smn.php */
/* Location: ./system/expressionengine/third_party/smn/ext.smn.php */
