<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'helpers/base.smu.php';

/**
 * Social Media Updater module control panel class
 * @author glen
 */
class Smu_mcp extends SMU_Base {

    /**
     * Consttructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::saveModUrls(array(
            'redirect_url' => ee()->config->item('site_url').'index.php?ACT='.ee()->cp->fetch_action_id('Smu', 'auth'),
            'module_cp_url' => ee()->config->item('site_url').$this->getSettingsPageURL()
        ));
    }

    /**
     * Fix issues on redirection error for EE CPanel when user used system
     * as the cpanel error
     *
     * @return string
     */
    private function getSettingsPageURL()
    {
        $matches = array();
        $result = '';
        preg_match_all("/(admin.php)/", BASE, $matches);
        return (!empty($matches) && count($matches[0])) ? ee('CP/URL', 'addons/settings/smu') : SYSDIR.'/'.ee('CP/URL', 'addons/settings/smu');
    }

  /**
   * Show module index pager
   * @return page the module's index page
   */
    function index()
    {
        ee()->load->helper('form');
        ee()->load->library('table');

        ee()->view->cp_page_title = lang('smu_module_page_title');

        $access_token = ee()->input->get('access_token');
        $platform = ee()->input->get('platform');

        $vars = array();

        $results = ee()->db->where('class', 'Smu_ext')->limit(1)->get('extensions')->first_row();

        $current_settings = (!empty($results)) ? (object)unserialize($results->settings) : array();

        $entry_statuses = ee()->db->select('status, status_id')->get('statuses')->result_object();
        $entry_statuses_fields = '';

        foreach ($entry_statuses as $key => $value) {
            $entry_statuses_fields .= form_label(form_checkbox(array('name' => 'entry_statuses[]', 'id' => 'entry_statuses_' . $value->status_id, 'value' => $value->status, 'checked' => (!empty($current_settings) && isset($current_settings->entry_statuses)) ? in_array($value->status, $current_settings->entry_statuses) : false)) . ' ' . ucwords($value->status), 'entry_statuses_' . $value->status_id, array('class' => 'block'));
        }

        // Generate linkedin companies field
        // API Call to LinkedIn
        // If and only if platform and access_token is set.
        $linkedin_companies_fields = '';
        $linkedin_companies = array();
        if(isset($current_settings->platforms['linkedin']['accounts']['access_token']) || (isset($platform) && $platform == 'linkedin' && isset($access_token) && !empty($access_token))) {
            $ac = (isset($current_settings->platforms['linkedin']['accounts']['access_token'])) ? $current_settings->platforms['linkedin']['accounts']['access_token'] : (($platform == 'linkedin' && !empty($access_token)) ? $access_token : '');


            if(!empty($ac)) {
                $linkedin_api_url = 'https://api.linkedin.com/v1/companies?oauth2_access_token='.$ac.'&format=json&is-company-admin=true';
                $linkedin_companies = json_decode($this->http->get($linkedin_api_url));

                if(isset($linkedin_companies->_total) && $linkedin_companies->_total > 0) {
                    $options = array();
                    foreach ($linkedin_companies->values as $key => $value) {
                        $options[$value->id] = $value->name;
                    }
                    $linkedin_companies_fields = form_dropdown('platforms[linkedin][accounts][company]', $options, (isset($current_settings->platforms['linkedin']['accounts']['company'])) ? $current_settings->platforms['linkedin']['accounts']['company'] : '');
                }

            }

        }

        // Set global settings field
        $vars['settings'] = array(
            'redirect_url' => '<code>'.parent::getModUrls()->redirect_url.'</code>',
            'entry_statuses' => $entry_statuses_fields,
            'default_url' => form_dropdown(
                'default_url',
                array(
                    'url_title' => 'Channel URL + url_title',
                    'entry_id' => 'Channel URL + entry_id',
                    'channel_url' => 'Channel URL',
                    'site_url' => 'Site URL'
                ),
            (isset($current_settings->default_url)) ? $current_settings->default_url : 'url_title'
            ),
        );

        // Platform specific settings
        $vars['platforms'] = array(
            'linkedin' => array(
                'accounts' => array(
                    'client_id' => form_input('platforms[linkedin][accounts][client_id]', (isset($current_settings->platforms['linkedin']['accounts']['client_id'])) ? $current_settings->platforms['linkedin']['accounts']['client_id'] : ''),
                    'client_secret' => form_input('platforms[linkedin][accounts][client_secret]', (isset($current_settings->platforms['linkedin']['accounts']['client_secret'])) ? $current_settings->platforms['linkedin']['accounts']['client_secret'] : ''),
                    'access_token' => (isset($current_settings->platforms['linkedin']['accounts']['access_token'])) ? form_input('platforms[linkedin][accounts][access_token]', (isset($current_settings->platforms['linkedin']['accounts']['access_token'])) ? $current_settings->platforms['linkedin']['accounts']['access_token'] : '') : '',
                    'post_to' => (isset($current_settings->platforms['linkedin']['accounts']['access_token']) || (!empty($platform) && !empty($access_token))) ? form_dropdown('platforms[linkedin][accounts][post_to]', array('profile' => 'Profile', 'company' => 'Company'), (isset($current_settings->platforms['linkedin']['accounts']['post_to']) ? $current_settings->platforms['linkedin']['accounts']['post_to'] : '')) : '',
                    'company' => (isset($current_settings->platforms['linkedin']['accounts']['access_token']) || (!empty($platform) && !empty($access_token))) ? $linkedin_companies_fields : '',
                    '&nbsp;' => form_submit('authorize_linkedin', (isset($current_settings->platforms['linkedin']['accounts']['access_token']) || (!empty($platform) && !empty($access_token))) ? 'Re-authorize' : 'Authorize', 'class="submit"') . (($platform == 'linkedin' && !empty($access_token)) ? NBS.NBS.NBS.'<strong><em>Save changes now!</em></strong>' : ''),
                )
            ),
          'facebook' => array(
                'accounts' => array(
                    'application_id' => form_input('platforms[facebook][accounts][application_id]', (isset($current_settings->platforms['facebook']['accounts']['application_id'])) ? $current_settings->platforms['facebook']['accounts']['application_id'] : ''),
                    'application_key' => form_input('platforms[facebook][accounts][application_key]', (isset($current_settings->platforms['facebook']['accounts']['application_key'])) ? $current_settings->platforms['facebook']['accounts']['application_key'] : ''),
                    'access_token' => (isset($current_settings->platforms['facebook']['accounts']['access_token'])) ? form_input('platforms[facebook][accounts][access_token]', (isset($current_settings->platforms['facebook']['accounts']['access_token'])) ? $current_settings->platforms['facebook']['accounts']['access_token'] : '') : '',
                )
            ),

            // add platforms here i.e. (twiter, facebook)
        );

        // Inject access_token field for LinkedIn if authorizing
        if(isset($platform) && $platform == 'linkedin') {
            $vars['platforms'][$platform]['accounts']['access_token'] = form_input('platforms[linkedin][accounts][access_token]', $access_token);
        }

        // Debug stuffs
        $vars['temps'] = array(
            'current_settings' => $current_settings,
            'linkedin_companies' => $linkedin_companies
        );

        //$vars['redirect_url'] = $this->redirect_url;
        $vars['redirect_url'] = parent::getModUrls()->redirect_url;
        //$vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=smu'.AMP.'method=save';
        $vars['action_url'] = parent::getModUrls()->module_cp_url.'/save';

        return ee()->load->view('index', $vars, TRUE);
    }

  /**
   * Save settings
   * @return void will be redirected to module settings index
   */
  function save() {

    if (empty($_POST)){
        show_error(lang('unauthorized_access'));
    }

    $errors = 0;
    $messages = array();
    $platforms = ee()->input->post('platforms');
    $redirect_url = ee()->input->post('redirect_url');

    // Validate required fields
    foreach ($platforms as $platforms_key => $platform) {
      foreach ($platform as $accounts_key => $accounts) {
        foreach ($accounts as $variable_key => $value) {
          if(empty($value)) {
            $errors++;
            array_push($messages, lang($variable_key.'_empty'));
          }
        }
      }
    }

    if($errors) { // Yeah, validation failed.

      ee()->session->set_flashdata('message_failure', implode($messages, '<br>'));
      ee()->functions->redirect(parent::getModUrls()->module_cp_url);
    } else { // Demacia!!! Settings has been saved.

      ee()->db->where('class', 'Smu_ext');
      ee()->db->update('extensions', array('settings' => serialize($_POST)));
      ee()->session->set_flashdata(
          'message_success',
          lang('preferences_updated')
      );
    }

    // Listen if this is an authorize request for LinkedIn
    // If so then redirect to LinkedIn auth processor
    if(ee()->input->post('authorize_linkedin')) {
      $current_settings = parent::getSettings();
      $authorize_url  = 'https://www.linkedin.com/uas/oauth2/authorization';
      $authorize_url .= '?response_type=code';
      $authorize_url .= '&client_id='.$current_settings->platforms['linkedin']['accounts']['client_id'];
      $authorize_url .= '&redirect_uri='.urlencode(parent::getModUrls()->redirect_url);
      $authorize_url .= '&state='.ee()->session->userdata('session_id');

      //exit($authorize_url);
      ee()->functions->redirect($authorize_url);
    }

    ee()->functions->redirect(parent::getModUrls()->module_cp_url);
  }
}

/* End of file mcp.smu.php */
/* Location: ./system/expressionengine/third_party/smu/mcp.smu.php */
