<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'helpers/Base.php';

/**
 * Social Media Updater module class
 *
 * @author glen
 */
class Smn extends Base
{

    /**
     * Catch the redirect after authorization process
     *
     * @return void
     */
    public function auth()
    {
        $error = ee()->input->get('error');
        $module_cp_url = $this->getModUrls()->module_cp_url;

        if($error) { // Yeah, auth returns an error.
            ee()->session->set_flashdata('message_failure', lang($error));
            ee()->functions->redirect($module_cp_url);
        } else { // Noxian Deplomacy. Redirect to module index to save the returned data.
            $current_settings = $this->getSettings();

            $result = $this->http()->withHeaders(['Content-Type: application/x-www-form-urlencoded'])->post('https://www.linkedin.com/uas/oauth2/accessToken', 'grant_type=authorization_code&code='.ee()->input->get('code').'&redirect_uri='.parent::getModUrls()->redirect_url.'&client_id='.$current_settings->platforms['linkedin']['accounts']['client_id'].'&client_secret='.$current_settings->platforms['linkedin']['accounts']['client_secret']);

            // Catch some sneaky errors
            if(isset($result->error)) {
                ee()->session->set_flashdata('message_failure', 'LinkedIn: ' . ucfirst($result->error_description));
                ee()->functions->redirect($module_cp_url);
            }

            ee()->functions->redirect($module_cp_url.AMP.'platform=linkedin'.AMP.'access_token='.$result->access_token);
        }
    }
}

/* End of file mod.smu.php */
/* Location: ./system/expressionengine/third_party/smu/mod.smu.php */
