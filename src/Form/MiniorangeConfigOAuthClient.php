<?php

/**
 * @file
 * Contains \Drupal\miniorange_oauth_client\Form\MiniorangeConfigOAuthClient.
 */

namespace Drupal\miniorange_oauth_client\Form;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\miniorange_oauth_client\Controller\miniorange_oauth_clientController;
use Drupal\miniorange_oauth_client\Utilities;
use Drupal\miniorange_oauth_client\mo_saml_visualTour;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MiniorangeConfigOAuthClient extends FormBase
{
    public function getFormId() {
       return 'miniorange_oauth_client_settings';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        global $base_url;
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_disabled', FALSE)->save();
        $moTour = mo_saml_visualTour::genArray();
        $form['tourArray'] = array(
            '#type' => 'hidden',
            '#value' => $moTour,
        );

        $baseUrl = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_base_url');
        $baseUrlValue = empty($baseUrl) ? Utilities::getOAuthBaseURL($base_url)  : $baseUrl ;

        $login_path = '<a href='.$baseUrlValue.'/moLogin>Enter what you want to display on the link</a>';
        $module_path = \Drupal::service('extension.list.module')->getPath('miniorange_oauth_client');

        if(!empty(\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_app_name')))
        {
            $baseUrl = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_base_url');
            if (isset($baseUrl) && !empty($baseUrl)){
              $callbackUrl = $baseUrl.'/mo_login';
            }
            else {
              $callbackUrl = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_callback_uri');
            }
        }
        else{
            $callbackUrl = $baseUrlValue."/mo_login";
            \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_callback_uri',$callbackUrl)->save();
        }

        $attachments['#attached']['library'][] = 'miniorange_oauth_client/miniorange_oauth_client.admin';
        $form['markup_library'] = array(
            '#attached' => array(
               'library' => array(
                    "miniorange_oauth_client/miniorange_oauth_client.oauth_config",
                    "miniorange_oauth_client/miniorange_oauth_client.admin",
                    "miniorange_oauth_client/miniorange_oauth_client.testconfig",
                    "miniorange_oauth_client/miniorange_oauth_client.returnAttribute",
                    "miniorange_oauth_client/miniorange_oauth_client.style_settings",
                    "miniorange_oauth_client/miniorange_oauth_client.Vtour",
               )
            ),
        );

        $app_name = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_app_name');
        $client_id = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_client_id');
        if(!empty($app_name) && !empty($client_id)){
            $disabled = TRUE;
            $v_tour_btn = 'style="display:none"';
        }

        $disableButton = NULL;
        if( empty($app_name)  || empty($client_id) ){
            $disableButton = 'disabled';
        }

        $app_name_selected = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_app_name');
        $client_id = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_client_id');
        if(!empty($app_name_selected) && !empty($client_id)){
            $disabled = TRUE;
            $attributes_arr =  array('style' => 'width:73%;background-color: hsla(0,0%,0%,0.08) !important;');
        }
        else{
            $disabled = FALSE;
            $attributes_arr =  array('style' => 'width:73%;');
        }

        $form['mo_oauth_top_div'] = array('#markup' => '<div class="mo_oauth_table_layout_1">');

        $form['mo_oauth_inside_div'] = array(
          '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container">',
        );

    if(empty($app_name)  || empty($client_id)){
        $form['markup_top_vt_start1'] = array(
            '#type' => 'fieldset',
            '#title' => t('CONFIGURE OAUTH APPLICATION'),
            '#prefix' => '<div id="tabhead">',
            '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
            '#markup' => '<a id="Restart_moTour" class="mo_oauth_btn mo_oauth_btn-primary-color mo_oauth_btn-large mo_oauth_btn_restart_tour">Take a Tour</a><a id="showMetaButton" class="mo_oauth_btn mo_oauth_btn-primary mo_btn-sm mo_oauth_btn_restart_tour" onclick="testConfig()" '.$disableButton.'>Backup/Import</a> <br><br><hr><br></div>',
        );
    }else{
        $form['markup_top_vt_start1'] = array(
            '#type' => 'fieldset',
            '#title' => t('CONFIGURE OAUTH APPLICATION'),
            '#prefix' => '<div id="tabhead">',
            '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
            '#markup' => '<a id="showMetaButton" class="mo_oauth_btn mo_oauth_btn-primary mo_btn-sm mo_oauth_btn_restart_tour" onclick="testConfig()" '.$disableButton.'>Backup/Import</a><br><br><hr><br></div>',
        );
    }
        $form['markup_top_vt_start2'] = array(
            '#type' => 'fieldset',
            '#title' => t('BACKUP/IMPORT CONFIGURATIONS'),
            '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
            '#prefix' => '<div border="1" id="backup_import_form" class="mo_oauth_backup_download">',
            '#markup' => '<a id="hideMetaButton" class="mo_oauth_btn mo_oauth_btn-sm mo_oauth_btn-danger mo_oauth_btn_restart_tour" onclick = "testConfig()">Cancel</a></span><br><hr><br>',
        );

        $module_path = \Drupal::service('extension.list.module')->getPath('miniorange_oauth_client');

        $form['markup_top_vt_start2']['markup_1'] = array(
            '#markup' => '<br><div class="mo_oauth_highlight_background_note"><p><b>NOTE: </b>This tab will help you to transfer your module configurations when you change your Drupal instance.
                          <br>Example: When you switch from test environment to production.<br>Follow these 3 simple steps to do that:<br>
                          <br><strong>1.</strong> Download module configuration file by clicking on the Download Configuration button given below.
                          <br><strong>2.</strong> Install the module on new Drupal instance.<br><strong>3.</strong> Upload the configuration file in Import module Configurations section.<br>
                          <br><b>And just like that, all your module configurations will be transferred!</b></p></div><br><div id="Exort_Configuration"><h3>Backup/ Export Configuration &nbsp;&nbsp;</h3><hr/><p>
                          Click on the button below to download module configuration.</p>',
        );

        $form['markup_top_vt_start2']['miniorange_saml_imo_option_exists_export'] = array(
            '#type' => 'submit',
            '#value' => t('Download Module Configuration'),
            '#submit' => array('::miniorange_import_export'),
            '#suffix'=> '<br/><br/></div>',
        );

        $form['markup_top_vt_start2']['markup_prem_plan'] = array(
            '#markup' => '<div id="Import_Configuration"><br/><h3>Import Configuration</h3><hr><br>
                          <div class="mo_oauth_highlight_background_note_1"><b>Note: </b>Available in
                          <a href="' . $base_url . '/admin/config/people/miniorange_oauth_client/licensing">Standard, Premium and Enterprise</a> versions of the module</div>',
        );

        $form['markup_top_vt_start2']['markup_import_note'] = array(
            '#markup' => '<p>This tab will help you to<span style="font-weight: bold"> Import your module configurations</span> when you change your Drupal instance.</p>
                 <p>choose <b>"json"</b> Extened module configuration file and upload by clicking on the button given below. </p>',
        );

        $form['markup_top_vt_start2']['import_Config_file'] = array(
            '#type' => 'file',
            '#disabled' => TRUE,
        );

        $form['markup_top_vt_start2']['miniorange_saml_idp_import'] = array(
            '#type' => 'submit',
            '#value' => t('Upload'),
            '#disabled' => TRUE,
            '#suffix' => '<br><br></div></div><div id="clientdata">'
        );

        $form['miniorange_oauth_client_app_options'] = array(
            '#type' => 'value',
            '#id' => 'miniorange_oauth_client_app_options',
            '#value' => array(
                'Azure AD' => t('Azure AD'),
                'Box' => t('Box'),
                'Discord' => t('Discord'),
                'Facebook' => t('Facebook'),
                'FitBit' => t('FitBit'),
                'GitHub' => t('GitHub'),
                'Google' => t('Google'),
                'Keycloak' => t('Keycloak'),
                'Line' => t('Line'),
                'LinkedIn' => t('LinkedIn'),
                'Okta' => t('Okta (OAuth)'),
                'Paypal' => t('Paypal'),
                'Salesforce' => t('Salesforce'),
                'Slack' => t('Slack'),
                'Strava' => t('Strava'),
                'Wild Apricot' => t('Wild Apricot'),
                'Zendesk' => t('Zendesk'),
                'Custom' => t('Custom OAuth 2.0 Provider'),
                'Azure AD B2C' => t('Azure AD B2C (Premium and Enterprise)'),
                'AWS Cognito' => t('AWS Cognito (Premium and Enterprise)'),
                'Onelogin' => t('Onelogin (Premium and Enterprise)'),
                'miniOrange' => t('miniOrange (Premium and Enterprise)'),
                'Okta_openid' => t('Okta (OpenID) (Premium and Enterprise)'),
                'Custom_openid' => t('Custom OpenID Provider (We support OpenID protocol in Premium and Enterprise version)')),
        );

        $form['markup_top_vt_start1']['miniorange_oauth_client_app'] = array(
            '#id' => 'miniorange_oauth_client_app',
            '#type' => 'select',
            '#disabled' => $disabled,
            '#title' => t('Select Application'),
            '#options' => $form['miniorange_oauth_client_app_options']['#value'],
            '#required' => TRUE,
            '#attributes' => array('style' => 'width:73%;'),
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_app'),
            '#description' => t('Select an OAuth Server'),
        );


        $form['markup_top_vt_start1']['mo_vt_id_start'] = array(
            '#markup' => '<div id = "mo_vt_callback_url">',
        );

        $form['markup_top_vt_start1']['miniorange_oauth_callback'] = array(
            '#type' => 'textfield',
            '#id'  => 'callbackurl',
            '#title' => t('Callback/Redirect URL'),
            '#default_value' => $callbackUrl,
            '#disabled' => true,
            '#attributes' => array('style' => 'width:73%;background-color: hsla(0,0%,0%,0.08) !important;'),
            '#description' => t('<b>Note:</b> If you want to change the <b>Redirect URL</b>, you can provide the site root URL/ base URL in <b>Sign In Settings</b> tab.'),
        );

        $form['markup_top_vt_start1']['mo_vt_id_end'] = array(
            '#markup' => '</div><div id = "mo_vt1_add_data">',
        );

        $form['markup_top_vt_start1']['miniorange_oauth_app_name'] = array(
            '#type' => 'textfield',
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_app_name'),
            '#id'  => 'miniorange_oauth_client_app_name',
            '#title' => t('Display Name'),
            '#disabled' => $disabled,
            '#required' => TRUE,
            '#attributes' => $attributes_arr,
        );

        $form['markup_top_vt_start1']['miniorange_oauth_client_display_name'] = array(
            '#type' => 'textfield',
            '#id'  => 'miniorange_oauth_client_display_name',
            '#title' => t('Login link on the login page'),
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_display_name'),
            '#attributes' => array('style' => 'width:73%','placeholder' => 'Login using ##app_name##'),
            '#description' => t('<b>Note:</b> The login link will appear on the user login page in this manner'),
        );

        $form['markup_top_vt_start1']['mo_vt_id_end0'] = array(
            '#markup' => '</div><div id = "mo_vt_add_data">',
        );

        $form['markup_top_vt_start1']['miniorange_oauth_client_id'] = array(
            '#type' => 'textfield',
            '#id'  => 'miniorange_oauth_client_client_id',
            '#title' => t('Client ID'),
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_client_id'),
            '#attributes' => array('style' => 'width:73%'),
            '#required' => TRUE,
        );

        $form['markup_top_vt_start1']['miniorange_oauth_client_secret'] = array(
            '#type' => 'textfield',
            '#title' => t('Client Secret'),
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_client_secret'),
            '#id'  => 'miniorange_oauth_client_client_secret',
            '#attributes' => array('style' => 'width:73%'),
            '#required' => TRUE,
        );

        $form['markup_top_vt_start1']['mo_vt_id_data1'] = array(
            '#markup' => '</div><div id = "mo_vt_add_data2">',
        );

        $form['markup_top_vt_start1']['miniorange_oauth_client_scope'] = array(
            '#type' => 'textfield',
            '#id'  => 'miniorange_oauth_client_scope',
            '#title' => t('Scope'),
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_scope'),
            '#attributes' => array('style' => 'width:73%'),
            '#required' => TRUE,
            '#description' => t('Scope decides the range of data that you will be getting from your OAuth Provider'),
        );

        $form['markup_top_vt_start1']['mo_vt_id_data4'] = array(
            '#markup' => '</div><div id = "mo_vt_add_data4">',
        );

        $form['markup_top_vt_start1']['miniorange_oauth_client_authorize_endpoint'] = array(
            '#type' => 'textfield',
            '#title' => t('Authorize Endpoint'),
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_authorize_endpoint'),
            '#id'  => 'miniorange_oauth_client_auth_ep',
            '#attributes' => array('style' => 'width:73%'),
            '#required' => TRUE,
        );

        $form['markup_top_vt_start1']['miniorange_oauth_client_access_token_endpoint'] = array(
            '#type' => 'textfield',
            '#title' => t('Access Token Endpoint'),
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_access_token_ep'),
            '#id'  => 'miniorange_oauth_client_access_token_ep',
            '#attributes' => array('style' => 'width:73%'),
            '#required' => TRUE,
        );

        $form['markup_top_vt_start1']['miniorange_oauth_client_userinfo_endpoint'] = array(
            '#type' => 'textfield',
            '#title' => t('Get User Info Endpoint'),
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_user_info_ep'),
            '#id'  => 'miniorange_oauth_client_user_info_ep',
            '#attributes' => array('style' => 'width:73%'),
            '#required' => TRUE,
        );

        $form['markup_top_vt_start1']['client_credentials'] = array(
            '#markup' => '<b>Send Client ID and secret in:</b> <div class="mo_oauth_tooltip"><img src="'.$base_url.'/'. $module_path . '/includes/images/info.png" alt="info icon" height="15px" width="15px"></div><div class="mo_oauth_tooltiptext"><b>Note:</b> This option depends upon the OAuth provider. In case you are unaware about what to save, keeping this default is the best practice.</div>',
        );

        $form['markup_top_vt_start1']['background_1'] = array(
            '#markup' => "<div class='mo_oauth_highlight_background_note_2 container-inline'>",
        );

        $form['markup_top_vt_start1']['miniorange_oauth_send_with_header_oauth'] = array(
            '#type' => 'checkbox',
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_send_with_header_oauth'),
            '#title' => t('<b>Header</b>'),
            '#prefix' => '&nbsp;&nbsp;&nbsp;',
        );

        $form['markup_top_vt_start1']['miniorange_oauth_send_with_body_oauth'] = array(
            '#type' => 'checkbox',
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_send_with_body_oauth'),
            '#title' => t('<b>Body</b>'),
            '#prefix' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        );

        $form['markup_top_vt_start1']['background_1_end'] = array(
            '#markup' => '</div></div><br><div id = "mo_vt_add_data3">',
        );

        $form['markup_top_vt_start1']['miniorange_oauth_enable_login_with_oauth'] = array(
            '#type' => 'checkbox',
            '#title' => t('<b>Enable Login with OAuth</b>'),
            '#default_value' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_enable_login_with_oauth'),
        );

        $form['markup_top_vt_start1']['mo_btn_breaks2'] = array(
            '#markup' => "</div><br>",
        );

        $form['markup_top_vt_start1']['miniorange_oauth_client_config_submit'] = array(
            '#type' => 'submit',
            '#id' => 'save_button',
            '#value' => t('Save Configuration'),
            '#button_type' => 'primary',
            '#attributes' => array('style' => 'border-radius: 4px;')
        );

        $baseUrlValue = Utilities::getOAuthBaseURL($base_url);

        $form['markup_top_vt_start1']['miniorange_oauth_client_test_config_button'] = array(
            '#value' => t('Test'),
            '#markup' => '<span id="base_Url" name="base_Url" data="'. $baseUrlValue.'"></span>
                                <a id="testConfigButton" class="mo_oauth_btn mo_oauth_btn-primary-color mo_oauth_btn-large mo_oauth_btn_fix" '.$disableButton.'>Test Configuration</a>',
        );

        $form['markup_top_vt_start1']['mo_reset'] = array(
            '#markup' => "<a class='mo_oauth_btn mo_oauth_btn-primary mo_oauth_btn-large' id ='vt_reset_config' ".$disableButton." href='".$baseUrlValue."/resetConfig'>Reset Configuration</a>",
        );

        $form['markup_top_vt_start1']['miniorange_oauth_login_link'] = array(
            '#id'  => 'miniorange_oauth_login_link',
            '#markup' => "<br><br><br><br><div class='mo_oauth_instruction_style'>
                <br><strong><div class='mo_custom_font_size_1'>Instructions to add login link to different pages in your Drupal site: </div></strong><br>
                <div class='mo_custom_font_size_2'>After completing your configurations, by default you will see a login link on your drupal site's login page.
                However, if you want to add login link somewhere else, please follow the below given steps:</div>
                <div class='mo_custom_font_size_3'>
                <li>Go to <b>Structure</b> -> <b>Blocks</b></li>
                <li> Click on <b>Add block</b></li>
                <li>Enter <b>Block Title</b> and the <b>Block description</b></li>
                <li>Under the <b>Block body</b> add the following URL to add a login link:
                    <ol> <h3><b>&lt;a href= '".$baseUrlValue."/moLogin'> Click here to Login&lt;/a&gt;</b></h3></ol>
                </li>
                <li>From the text filtered dropdown select either <b>Filtered HTML</b> or <b>Full HTML</b></li>
                <li>From the division under <b>REGION SETTINGS</b> select where do you want to show the login link</li>
                <li>Click on the <b>SAVE block</b> button to save your settings</li><br>
                </div>
                </div>",
            '#attributes' => array(),
        );

        $form['mo_header_style_end'] = array('#markup' => '</div></div>');


        Utilities::spConfigGuide($form, $form_state);
        $form['mo_markup_div_2_imp']=array('#markup'=>'</div>');
        $form['mo_markup_div__imp']=array('#markup'=>'<div id="mo_advertise">');
        Utilities::advertiseServer($form, $form_state);
        $form['mo_markup_div_imp']=array('#markup'=>'</div>');
        Utilities::AddSupportButton($form, $form_state);

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $enable_with_header = $form['markup_top_vt_start1']['miniorange_oauth_send_with_header_oauth']['#value'];
        $enable_with_body = $form['markup_top_vt_start1']['miniorange_oauth_send_with_body_oauth']['#value'];

        if ($enable_with_header == 0 && $enable_with_body == 0 ) {
            $form_state->setErrorByName('miniorange_oauth_client', t('This state is not allowed. Please select at least one of the options to send Client ID and Secret.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        global $base_url;
        $baseUrlValue = Utilities::getOAuthBaseURL($base_url);

        if(isset($form['markup_top_vt_start1']['miniorange_oauth_client_app']))
            $client_app =  trim( $form['markup_top_vt_start1']['miniorange_oauth_client_app']['#value'] );
        if(isset($form['markup_top_vt_start1']['miniorange_oauth_app_name']['#value']))
            $app_name = trim( $form['markup_top_vt_start1']['miniorange_oauth_app_name']['#value'] );
        $app_name = str_replace(' ', '', $app_name);
        if(isset($form['markup_top_vt_start1']['miniorange_oauth_client_display_name']['#value']))
            $display_name = trim( $form['markup_top_vt_start1']['miniorange_oauth_client_display_name'] ['#value'] );
        if(isset($form['markup_top_vt_start1']['miniorange_oauth_client_id']))
            $client_id = trim( $form['markup_top_vt_start1']['miniorange_oauth_client_id']['#value'] );
        if(isset($form['markup_top_vt_start1']['miniorange_oauth_client_secret']['#value']))
            $client_secret = trim( $form['markup_top_vt_start1']['miniorange_oauth_client_secret'] ['#value'] );
        if(isset($form['markup_top_vt_start1']['miniorange_oauth_client_scope']['#value']))
            $scope = trim( $form['markup_top_vt_start1']['miniorange_oauth_client_scope']['#value'] );
        if(isset($form['markup_top_vt_start1']['miniorange_oauth_client_authorize_endpoint']['#value']))
            $authorize_endpoint = trim($form['markup_top_vt_start1']['miniorange_oauth_client_authorize_endpoint'] ['#value']);
        if(isset($form['markup_top_vt_start1']['miniorange_oauth_client_access_token_endpoint']['#value']))
            $access_token_ep = trim($form['markup_top_vt_start1']['miniorange_oauth_client_access_token_endpoint']['#value']);
        if(isset($form['markup_top_vt_start1']['miniorange_oauth_client_userinfo_endpoint']['#value']))
            $user_info_ep = trim($form['markup_top_vt_start1']['miniorange_oauth_client_userinfo_endpoint']['#value']);
        $email_attr = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_email_attr_val');
        if(($client_app=='Select') || empty($client_app) || empty($app_name) || empty($client_id) || empty($client_secret) || empty($authorize_endpoint) || empty($access_token_ep)
            || empty($user_info_ep)) {
            if(empty($client_app)|| $client_app == 'Select'){
                \Drupal::messenger()->addMessage(t('The <b>Select Application</b> dropdown is required. Please Select your application.'), 'error');
                return;
            }
            \Drupal::messenger()->addMessage(t('The <b>Display name</b>, <b>Client ID</b>, <b>Client Secret</b>, <b>Authorize Endpoint</b>, <b>Access Token Endpoint</b>
                , <b>Get User Info Endpoint</b> fields are required.'), 'error');
            return;
        }

        if(empty($client_app))
        {
            $client_app =\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_app');
        }
        if(empty($app_name))
        {
            $client_app = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_app_name');
        }
        if (empty($display_name))
        {
            $display_name = '';
        }
        if(empty($client_id))
        {
            $client_id =\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_client_id');
        }
        if(empty($client_secret))
        {
            $client_secret = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_client_secret');
        }
        if(empty($scope))
        {
            $scope = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_scope');
        }
        if(empty($authorize_endpoint))
        {
            $authorize_endpoint = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_authorize_endpoint');
        }
        if(empty($access_token_ep))
        {
            $access_token_ep =\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_access_token_ep');
        }
        if(empty($user_info_ep))
        {
            $user_info_ep = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_userinfo_endpoint');
        }

        $callback_uri = $baseUrlValue."/mo_login";

        $app_values = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_appval');
        if(!is_array($app_values))
            $app_values = array();
        $app_values['client_id'] = $client_id;
        $app_values['client_secret'] = $client_secret;
        $app_values['app_name'] = $app_name;
        $app_values['display_name'] = $display_name;
        $app_values['scope'] = $scope;
        $app_values['authorize_endpoint'] = $authorize_endpoint;
        $app_values['access_token_ep'] = $access_token_ep;
        $app_values['user_info_ep'] = $user_info_ep;
        $app_values['callback_uri'] = $callback_uri;
        $app_values['client_app'] = $client_app;
        $app_values['miniorange_oauth_client_email_attr'] = $email_attr;
        $enable_login_with_oauth = $form['markup_top_vt_start1']['miniorange_oauth_enable_login_with_oauth']['#value'];
        $enable_login = $enable_login_with_oauth == 1 ? TRUE : FALSE;
        $enable_with_header = $form['markup_top_vt_start1']['miniorange_oauth_send_with_header_oauth']['#value'];
        $enable_with_body = $form['markup_top_vt_start1']['miniorange_oauth_send_with_body_oauth']['#value'];
        $enable_header = $enable_with_header == 1 ? TRUE : FALSE ;
        $enable_body = $enable_with_body == 1 ? TRUE : FALSE ;

        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_enable_login_with_oauth',$enable_login)->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_app',$client_app)->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_appval',$app_values)->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_app_name',$app_name)->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_display_name',$display_name)->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_client_id',$client_id)->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_client_secret',$client_secret)->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_scope',$scope)->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_authorize_endpoint',$authorize_endpoint)->save();         \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_access_token_ep',$access_token_ep)->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_user_info_ep',$user_info_ep)->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_stat',"Review Config")->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_callback_uri',$callback_uri)->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_send_with_header_oauth',$enable_header)->save();
      	\Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_send_with_body_oauth',$enable_body)->save();

      \Drupal::messenger()->addMessage(t('Configurations saved successfully. Please click on the <b>Test Configuration</b> button to test the connection.'), 'status');
    }

    /**
     * Send support query.
     */
    public function saved_support(array &$form, FormStateInterface $form_state) {

        $email = trim($form['miniorange_oauth_client_email_address']['#value']);
        $phone = $form['miniorange_oauth_client_phone_number']['#value'];
        $query = trim($form['miniorange_oauth_client_support_query']['#value']);
        Utilities::send_support_query($email, $phone, $query);
    }

    public function rfd(array &$form, FormStateInterface $form_state) {

        global $base_url;
        $response = new RedirectResponse($base_url."/admin/config/people/miniorange_oauth_client/request_for_demo");
        $response->send();
    }

    function miniorange_import_export()
	{
        $tab_class_name = array(
            'OAuth Client Configuration' => 'mo_options_enum_client_configuration',
            'Attribute Mapping' => 'mo_options_enum_attribute_mapping',
            'Sign In Settings' => 'mo_options_enum_signin_settings'
        );

		$configuration_array = array();
		foreach($tab_class_name as $key => $value) {
			$configuration_array[$key] = self::mo_get_configuration_array($value);
		}

		$configuration_array["Version_dependencies"] = self::mo_get_version_informations();
		header("Content-Disposition: attachment; filename = miniorange_oauth_client_config.json");
		echo(json_encode($configuration_array, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
		exit;
	}

    function mo_get_configuration_array($class_name)
    {
        $class_object = Utilities::getVariableArray($class_name);
        $mo_array = array();
        foreach($class_object as $key => $value) {
            $mo_option_exists = \Drupal::config('miniorange_oauth_client.settings')->get($value);
            if($mo_option_exists) {
                $mo_array[$key] = $mo_option_exists;
            }
        }
        return $mo_array;
    }

    function mo_get_version_informations() {
        $array_version = array();
        $array_version["PHP_version"] = phpversion();
        $array_version["Drupal_version"] = \DRUPAL::VERSION;
        $array_version["OPEN_SSL"] = self::mo_oauth_is_openssl_installed();
        $array_version["CURL"] = self::mo_oauth_is_curl_installed();
        $array_version["ICONV"] = self::mo_oauth_is_iconv_installed();
        $array_version["DOM"] = self::mo_oauth_is_dom_installed();
        return $array_version;
    }

	function mo_oauth_is_openssl_installed() {
		if ( in_array( 'openssl', get_loaded_extensions() ) ) {
			return 1;
		} else {
			return 0;
		}
	}
    function mo_oauth_is_curl_installed() {
        if ( in_array( 'curl', get_loaded_extensions() ) ) {
            return 1;
        } else {
            return 0;
        }
    }
    function mo_oauth_is_iconv_installed() {
        if ( in_array( 'iconv', get_loaded_extensions() ) ) {
            return 1;
        } else {
            return 0;
        }
    }
    function mo_oauth_is_dom_installed() {
        if ( in_array( 'dom', get_loaded_extensions() ) ) {
            return 1;
        } else {
            return 0;
        }
    }
}
