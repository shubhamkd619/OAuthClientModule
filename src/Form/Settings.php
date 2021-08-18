<?php

namespace Drupal\miniorange_oauth_client\Form;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\miniorange_oauth_client\Utilities;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Settings extends FormBase
{
    public function getFormId() {
        return 'miniorange_oauth_client_settings';
    }
/**
 * Showing Settings form.
 */
 public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $baseUrlValue = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_base_url');

    $attachments['#attached']['library'][] = 'miniorange_oauth_client/miniorange_oauth_client.admin';

    $form['markup_library'] = array(
        '#attached' => array(
            'library' => array(
                "miniorange_oauth_client/miniorange_oauth_client.admin",
                "miniorange_oauth_client/miniorange_oauth_client.style_settings",
                "miniorange_oauth_client/miniorange_oauth_client.slide_support_button",
            )
        ),
    );

    $form['header_top_style_1'] = array('#markup' => '<div class="mo_oauth_table_layout_1">');

    $form['markup_top'] = array(
         '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container">',
    );

    $form['markup_custom_role_mapping'] = array(
        '#type' => 'fieldset',
        '#title' => t('SIGN IN SETTINGS'),
        '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
    );

    $module_path = \Drupal::service('extension.list.module')->getPath('miniorange_oauth_client');

    $form['markup_custom_role_mapping']['markup_top'] = array(
        '#markup' => '<br><hr><br><div class="container-inline">',
    );

    $form['markup_custom_role_mapping']['miniorange_oauth_client_base_url_title'] = array(
         '#markup' => '<b>Base/Site URL:</b> <div class="mo_oauth_tooltip"><img src="'.$base_url.'/'. $module_path . '/includes/images/info.png" alt="info icon" height="15px" width="15px"></div><div class="mo_oauth_tooltiptext"><b>Note: </b>You can customize base URL here. (For eg: https://www.xyz.com or http://localhost/abc)</div>',
    );

    $form['markup_custom_role_mapping']['miniorange_oauth_client_base_url'] = array(
        '#type' => 'textfield',
        '#default_value' => $baseUrlValue,
        '#attributes' => array('id'=>'mo_oauth_vt_baseurl','style' => 'margin-left: 12%; width:80%;','placeholder' => 'Enter Base URL'),
    );

    $form['markup_custom_role_mapping']['markup_top_end_div'] = array(
        '#markup' => '</div><br>',
    );

    $form['markup_custom_role_mapping']['miniorange_oauth_client_siginin1'] = array(
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#attributes' => array('style' => '	margin: auto; display:block; '),
        '#value' => t('Update'),
    );



    $form['markup_custom_signin'] = array(
        '#type' => 'fieldset',
        '#title' => t('ADVANCED SIGN IN SETTINGS'),
        '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
    );

    $form['markup_custom_signin']['markup_top'] = array(
        '#markup' => '<br><hr><br>',
    );

    $form['markup_custom_signin']['miniorange_oauth_force_auth'] = array(
        '#type' => 'checkbox',
        '#title' => t('Protect website against anonymous access <a href="' . $base_url . '/admin/config/people/miniorange_oauth_client/licensing"><b>[Premium, Enterprise]</b></a>'),
        '#disabled' => TRUE,
        '#description' => t('<b>Note: </b>Users will be redirected to your OAuth server for login in case user is not logged in and tries to access website.<br><br>'),
    );

    $form['markup_custom_signin']['miniorange_oauth_auto_redirect'] = array(
        '#type' => 'checkbox',
        '#title' => t('Check this option if you want to <b> Auto-redirect to OAuth Provider/Server </b><a href="' . $base_url . '/admin/config/people/miniorange_oauth_client/licensing"><b>[Premium, Enterprise]</b></a>'),
        '#disabled' => TRUE,
        '#description' => t('<b>Note: </b>Users will be redirected to your OAuth server for login when the login page is accessed.<br><br>'),
    );

    $form['markup_custom_signin']['miniorange_oauth_enable_backdoor'] = array(
        '#type' => 'checkbox',
        '#title' => t('Check this option if you want to enable <b>backdoor login </b><a href="' . $base_url . '/admin/config/people/miniorange_oauth_client/licensing"><b>[Premium, Enterprise]</b></a>'),
        '#disabled' => TRUE,
        '#description' => t('<b>Note:</b> Checking this option creates a backdoor to login to your Website using Drupal credentials<br> incase you get locked out of your OAuth server.
                <b>Note down this URL: </b>Available in <a href="' . $base_url . '/admin/config/people/miniorange_oauth_client/licensing"><b>Premium, Enterprise</b></a> versions of the module.<br><br><br><br>'),
    );

    $form['markup_custom_signin']['markup_bottom_vt_start_auto_create_users'] = array(
        '#markup' => '<div class="custom-login-logout mo_oauth_custom_login_logout"><b>Auto Create Users</b><hr></div><p>This feature provides you with an option to automatically create a user if the user is not already present in Drupal</p>'
    );

    $form['markup_custom_signin']['miniorange_oauth_disable_autocreate_users'] = array(
        '#type' => 'checkbox',
        '#title' => t('Check this option if you want to disable <b>auto creation</b> of users if user does not exist.<a href="' . $base_url . '/admin/config/people/miniorange_oauth_client/licensing"><b>[Standard, Premium, Enterprise]</b></a>'),
	    '#disabled' => TRUE,
      );

    $form['markup_custom_signin1'] = array(
        '#type' => 'fieldset',
        '#title' => t('DOMAIN & PAGE RESTRICTION  &nbsp;<a href="' . $base_url . '/admin/config/people/miniorange_oauth_client/licensing"><b>[Enterprise]</b></a>'),
        '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
    );

     $form['markup_custom_signin1']['markup_bottom_vt_start'] = array(
         '#markup' => '<br><hr><br>',
     );

     $form['markup_custom_signin1']['miniorange_oauth_client_white_list_url'] = array(
         '#type' => 'textfield',
         '#title' => t('Allowed Domains'),
         '#attributes' => array('style' => 'width:80%','placeholder' => 'Enter semicolon(;) separated domains (Eg. xxxx.com; xxxx.com)'),
         '#disabled' => TRUE,
         '#description' => t("<b>Note:</b> In this feature, you can allow only some of the domains to login using the SSO."),
     );

     $form['markup_custom_signin1']['miniorange_oauth_client_black_list_url'] = array(
         '#type' => 'textfield',
         '#title' => t('Restricted Domains'),
         '#attributes' => array('style' => 'width:80%','placeholder' => 'Enter semicolon(;) separated domains (Eg. xxxx.com; xxxx.com)'),
         '#disabled' => TRUE,
         '#description' => t("<b>Note:</b> In this feature, you can restrict some of the domains to login using the SSO."),
    );

     $form['markup_custom_signin1']['miniorange_oauth_client_page_restrict_url'] = array(
         '#type' => 'textfield',
         '#title' => t('Page Restriction'),
         '#attributes' => array('style' => 'width:80%','placeholder' => 'Enter semicolon(;) separated page URLs (Eg. xxxx.com/yyy; xxxx.com/yyy)'),
         '#disabled' => TRUE,
         '#description' => t("<b>Note:</b> In this feature, you can restrict unauthorized access for some of the URLs of the site."),
         '#suffix' => '<br>',
    );

    $form['markup_custom_signin1']['miniorange_oauth_client_siginin'] = array(
            '#type' => 'button',
            '#value' => t('Save Configuration'),
            '#button_type' => 'primary',
            '#disabled' => TRUE,
            '#attributes' => array('style' => '	margin: auto; display:block; '),
    );

   $form['markup_custom_login_button'] = array(
     '#type' => 'fieldset',
     '#title' => t('LOGIN BUTTON CUSTOMIZATION &nbsp;<a href="' . $base_url . '/admin/config/people/miniorange_oauth_client/licensing"><b>[Standard, Premium, Enterprise]</b></a>'),
     '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
   );

   $form['markup_custom_login_button']['markup_top1'] = array(
     '#markup' => '<br><hr><br>',
   );

   $form['markup_custom_login_button']['miniorange_oauth_icon_width'] = array(
     '#type' => 'textfield',
     '#title' => t('Icon width'),
     '#disabled' => TRUE,
     '#description' => t('For eg.200px or 10% <br>'),
   );

   $form['markup_custom_login_button']['miniorange_oauth_icon_height'] = array(
     '#type' => 'textfield',
     '#title' => t('Icon height'),
     '#disabled' => TRUE,
     '#description' => t('For eg.60px or auto <br>'),
   );

   $form['markup_custom_login_button']['miniorange_oauth_icon_margins'] = array(
     '#type' => 'textfield',
     '#title' => t('Icon Margins'),
     '#disabled' => TRUE,
     '#description' => t('For eg. 2px 3px or auto <br>'),
   );

   $form['markup_custom_login_button']['miniorange_oauth_custom_css'] = array(
     '#type' => 'textarea',
     '#title' => t('Custom CSS'),
     '#disabled' => TRUE,
     '#attributes' => array('style'=> 'width:80%', 'placeholder' => 'For eg.  .oauthloginbutton{ background: #7272dc; height:40px; padding:8px; text-align:center; color:#fff; }'),
   );

   $form['markup_custom_login_button']['miniorange_oauth_btn_txt'] = array(
     '#type' => 'textfield',
     '#title' => t('Custom Button Text'),
     '#disabled' => TRUE,
     '#attributes' => array('placeholder'=> 'Login Using appname'),
   );

    $form['mo_header_style_end'] = array('#markup' => '</div>');

    Utilities::nofeaturelisted($form, $form_state);

    Utilities::AddSupportButton($form, $form_state);
    return $form;
 }

 public function submitForm(array &$form, FormStateInterface $form_state) {
    $baseUrlvalue = trim($form['markup_custom_role_mapping']['miniorange_oauth_client_base_url']['#value']);
     if(!empty($baseUrlvalue) && filter_var($baseUrlvalue, FILTER_VALIDATE_URL) == FALSE) {
         \Drupal::messenger()->adderror(t('Please enter a valid URL'));
         return;
     }
    \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_base_url', $baseUrlvalue)->save();
    \Drupal::messenger()->addMessage(t('Configurations saved successfully.'));
 }

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
}
