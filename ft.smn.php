<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'helpers/Base.php';

/**
 * Social Media Updater fieldtype class
 * @author glen
 */
class Smu_ft extends EE_Fieldtype
{

    /**
     * Info container
     *
     * @var array
     */
    protected $info = [
        'name' => 'Social Media Updater',
        'version' => '1.0',
        'settings_exist' => 'n'
    ];

    /**
     * Delimiter container
     *
     * @var string
     */
    protected $delimiter = '[smn:pipe]';

    /**
     * Key container
     *
     * @var string
     */
    protected $key = '[smn:key]';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display fieldtype
     *
     * @param array $data                            the data of the field
     * @return string
     */
    public function display_field($data)
    {
        $helper = new SMU_Base();

        return ee()->load->view('fieldtype', [
            'data' => $this->parse_data($data),
            'smn' => ee()->db->where('entry_id', $this->content_id)->limit(1)->get('smn')->first_row(),
            'field_data' => $data,
            'delimiter' => $this->delimiter,
            'key' => $this->key,
            'field_name' => $this->field_name,
            'field_id' => $this->field_id,
            'enabled' => (!empty($helper->getSettings())) ? TRUE : FALSE,
            'urls' => $helper->getModUrls()
        ], TRUE);
    }

    /**
     * Parse custom date and convert to array
     *
     * @param string $data                              the custom data string
     * @return array
     */
    public function parse_data($data)
    {
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
     * Display settings
     *
     * @param array $data                               the settings data to display
     * @return array
     */
    public function display_settings($data) {
        $platform = isset($data['platform']) ? $data['platform'] : $this->settings['platform'];
        $settings = [
            [
                'title' => 'Platform',
                'desc' => 'Select Platform',
                'fields' => [
                    'platform' => [
                        'type' => 'select',
                        'choices' => [
                            'linkedin' => 'LinkedIn',
                            'facebook' => 'Facebook'
                        ],
                        'value' => $platform,
                    ]
                ]
            ]
        ]

        return [
            'field_options_smn' => [
                'label' => 'field_options',
                'group' => 'smn',
                'settings' => $settings
            ]
        ];
    }

    /**
     * Save settings
     *
     * @param  array $data                              the settings to save
     * @return void
     */
    public function save_settings($data) {

    }

    /**
     * Install
     *
     * @return array
     */
    public function install()
    {
        return [
            'smn_linkedin_enabled' => 'yes',
            'platform' => '',
        ];
    }

}

/* End of file ft.smn.php */
/* Location: ./system/expressionengine/third_party/smn/ft.smn.php */
