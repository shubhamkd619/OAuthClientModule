<?php

/**
 * @file
 * Contains \Drupal\miniorange_oauth_client\Form\MiniorangeOAuthClientCustomerSetup.
 */

namespace Drupal\miniorange_oauth_client\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\miniorange_oauth_client\MiniorangeOAuthClientCustomer;
use Drupal\Core\Form\FormBase;
use Drupal\miniorange_oauth_client\MiniorangeOAuthClientSupport;
use Drupal\miniorange_oauth_client\Utilities;
use Symfony\Component\HttpFoundation\RedirectResponse;

    class MiniorangeOAuthClientCustomerSetup extends FormBase
    {
        public function getFormId() {
            return 'miniorange_oauth_client_customer_setup';
        }

        public function buildForm(array $form, FormStateInterface $form_state)
        {
                global $base_url;

                $current_status = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_status');
                $form['markup_library'] = array(
                    '#attached' => array(
                        'library' => array(
                            "miniorange_oauth_client/miniorange_oauth_client.admin",
                            "miniorange_oauth_client/miniorange_oauth_client.style_settings",
                            "miniorange_oauth_client/miniorange_oauth_client.module",
                            "miniorange_oauth_client/miniorange_oauth_client.slide_support_button",
                        )
                    ),
                );

                if ($current_status == 'VALIDATE_OTP')
                {
                    $form['header_top_style_1'] = array('#markup' => '<div class="mo_oauth_table_layout_1">');

                    $form['markup_top'] = array(
                        '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container">',
                    );

                    $admin_mail = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_email');

                    $form['markup_top_vt_start'] = array(
                        '#type' => 'fieldset',
                        '#title' => t('OTP VALIDATION'),
                        '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
                        '#markup' => '<br><hr><br>',
                    );

                    $form['markup_top_vt_start']['mo_user_profile'] = array(
                        '#markup' => '<h3>Please enter the OTP sent to <i>'.$admin_mail.'</i>: </h3><br>'
                    );

                    $form['markup_top_vt_start']['miniorange_oauth_client_customer_otp_token'] = array(
                        '#type' => 'textfield',
                        '#title' => t('OTP'),
                        '#attributes' => array('style' => 'width:30%;', 'placeholder' => 'Enter OTP'),
                    );

                    $form['markup_top_vt_start']['mo_btn_brk'] = array('#markup' => '<br><br>');

                    $form['markup_top_vt_start']['miniorange_oauth_client_customer_validate_otp_button'] = array(
                        '#type' => 'submit',
                        '#value' => t('Validate OTP'),
                        '#submit' => array('::miniorange_oauth_client_validate_otp_submit'),
                    );

                    $form['markup_top_vt_start']['miniorange_oauth_client_customer_setup_resendotp'] = array(
                        '#type' => 'submit',
                        '#value' => t('Resend OTP'),
                        '#submit' => array('::miniorange_oauth_client_resend_otp'),
                    );

                    $form['markup_top_vt_start']['miniorange_oauth_client_customer_setup_back'] = array(
                    '#type' => 'submit',
                    '#value' => t('Back'),
                    '#submit' => array('::miniorange_oauth_client_back'),
                    );

                    Utilities::nofeaturelisted($form, $form_state);
                    $form['markup_top_vt_start']['header_top_div_end'] = array('#markup' => '</div>');
                    Utilities::AddSupportButton($form, $form_state);
                    return $form;
                }
                elseif ($current_status == 'PLUGIN_CONFIGURATION')
                {

                    $form['header_top_style_1'] = array('#markup' => '<div class="mo_oauth_table_layout_1">');

                    $form['markup_top'] = array(
                        '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container">',
                    );

                    $form['markup_top_vt_start'] = array(
                        '#type' => 'fieldset',
                        '#title' => t('PROFILE'),
                        '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
                        '#markup' => '<br><hr><br>',
                    );

                    $form['markup_top_vt_start']['mo_message_wlcm'] = array(
                        '#markup' => '<div class="mo_oauth_client_welcome_message">Thank you for registering with miniOrange',
                    );

                    $form['markup_top_vt_start']['mo_user_profile'] = array(
                        '#markup' => '</div><br><br><h4>Your Profile: </h4>'
                    );

                    $header = array(
                        'email' => array(
                        'data' => t('Customer Email')
                        ),
                        'customerid' => array(
                            'data' => t('Customer ID')
                        ),
                        'token' => array(
                            'data' => t('Token Key')
                        ),
                        'apikey' => array(
                            'data' => t('API Key')
                        ),
                    );

                    $options = [];

                    $options[0] = array(
                        'email' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_email'),
                        'customerid' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_id'),
                        'token' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_token'),
                        'apikey' => \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_api_key'),
                    );

                    $form['markup_top_vt_start']['fieldset']['customerinfo'] = array(
                        '#theme' => 'table',
                        '#header' => $header,
                        '#rows' => $options,
                    );

                    $form['markup_top_vt_start']['miniorange_oauth_client_support_div_cust'] = array(
                        '#markup' => '<br><br><br><br></div>'
                    );

                    Utilities::nofeaturelisted($form, $form_state);
                    $form['mo_markup_div_end2']=array('#markup'=>'</div>');

                    Utilities::AddSupportButton($form, $form_state);
                    return $form;
                }

                $form['header_top_style_1'] = array('#markup' => '<div class="mo_oauth_table_layout_1">');

                $form['markup_top'] = array(
                    '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container">',
                );

                $form['markup_top_vt_start'] = array(
                    '#type' => 'fieldset',
                    '#title' => t('REGISTER/LOGIN WITH MINIORANGE'),
                    '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
                    '#markup' => '<br><hr><br>',
                );

            $form['markup_top_vt_start']['markup_2'] = array(
                '#markup' => '<div class="mo_oauth_highlight_background_note_export"><p><h3>Why Should I register?</h3></p>
            <b> You will be needing a miniOrange account to upgrade to the Standard, Premium or Enterprise versions of the modules.</b>
             If you face any problem during registration, you can create an account by clicking <a href="https://www.miniorange.com/businessfreetrial" target="_blank">here.</a><br>
             We do not store any information except the email that you will use to register with us.<br></div><br>',
            );

                $form['markup_top_vt_start']['miniorange_oauth_client_customer_setup_username'] = array(
                    '#type' => 'email',
                    '#title' => t('Email'),
                    '#attributes' => array('style' => 'width:50%;', 'placeholder' => 'Enter your email'),
                    '#required' => TRUE,
                );

                $form['markup_top_vt_start']['miniorange_oauth_client_customer_setup_phone'] = array(
                    '#type' => 'textfield',
                    '#title' => t('Phone'),
                    '#attributes' => array('style' => 'width:50%;'),
                    '#description' => '<b>NOTE:</b> We will only call if you need support.'
                );

                $form['markup_top_vt_start']['miniorange_oauth_client_customer_setup_password'] = array(
                    '#type' => 'password_confirm',
                    '#required' => TRUE,
                );

                $form['markup_top_vt_start']['miniorange_oauth_client_customer_setup_button'] = array(
                    '#type' => 'submit',
                    '#value' => t('Submit'),
                    '#prefix' => '<br>',
                );

                $form['markup_top_vt_start']['markup_divEnd'] = array(
                    '#markup' => '</div>'
                );

                Utilities::nofeaturelisted($form, $form_state);
                $form['mo_markup_div_end2']=array('#markup'=>'</div>');

                Utilities::AddSupportButton($form, $form_state);
                return $form;
        }

        public function submitForm(array &$form, FormStateInterface $form_state) {
                $username = trim($form['markup_top_vt_start']['miniorange_oauth_client_customer_setup_username']['#value']);
                $phone = $form['markup_top_vt_start']['miniorange_oauth_client_customer_setup_phone']['#value'];
                $password = trim($form['markup_top_vt_start']['miniorange_oauth_client_customer_setup_password']['#value']['pass1']);
                if(empty($username)||empty($password)){
                    \Drupal::messenger()->addMessage(t('The <b><u>Email </u></b> and <b><u>Password</u></b> fields are mandatory.'), 'error');
                    return;
                }
                if (!\Drupal::service('email.validator')->isValid($username)) {
                    \Drupal::messenger()->addMessage(t('The email address <i>' . $username . '</i> is not valid.'), 'error');
                    return;
                }
                $customer_config = new MiniorangeOAuthClientCustomer($username, $phone, $password, NULL);
                $check_customer_response = json_decode($customer_config->checkCustomer());
                if ($check_customer_response->status == 'CUSTOMER_NOT_FOUND') {
                    \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_admin_email', $username)->save();
                    \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_admin_phone', $phone)->save();
                    \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_admin_password', $password)->save();
                    $send_otp_response = json_decode($customer_config->sendOtp());

                    if ($send_otp_response->status == 'SUCCESS') {
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_tx_id', $send_otp_response->txId)->save();
                        $current_status = 'VALIDATE_OTP';
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_status', $current_status)->save();
                        \Drupal::messenger()->addMessage(t('Verify email address by entering the passcode sent to @username', [
                            '@username' => $username
                        ]));
                    }
                }
                elseif ($check_customer_response->status == 'CURL_ERROR') {
                    \Drupal::messenger()->addMessage(t('cURL is not enabled. Please enable cURL'), 'error');
                }
                else {
                    $customer_keys_response = json_decode($customer_config->getCustomerKeys());
                    if (json_last_error() == JSON_ERROR_NONE) {
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_id', $customer_keys_response->id)->save();
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_admin_token', $customer_keys_response->token)->save();
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_admin_email', $username)->save();
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_admin_phone', $phone)->save();
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_api_key', $customer_keys_response->apiKey)->save();
                        $current_status = 'PLUGIN_CONFIGURATION';
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_status', $current_status)->save();
                        \Drupal::messenger()->addMessage(t('Successfully retrieved your account.'));
                        $register_to_upgrade = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_redi_upgrade');

                        if($register_to_upgrade == 1){
                            \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_redi_upgrade', '0')->save();
                            global $base_url;
                            $response = new RedirectResponse($base_url."/admin/config/people/miniorange_oauth_client/licensing");
                            $response->send();
                        }
                    }
                    else if ($check_customer_response->status == 'TRANSACTION_LIMIT_EXCEEDED'){
                        \Drupal::messenger()->addMessage(t('An error has been occured. Please try after some time or contact us at <a href="mailto:drupalsupport@xecurify.com" target="_blank">drupalsupport@xecurify.com</a>.'), 'error');
                    }
                    else {
                        \Drupal::messenger()->addMessage(t('Invalid credentials'), 'error');
                    }
                }
        }

        public function miniorange_oauth_client_back(&$form, $form_state) {
                $current_status = 'CUSTOMER_SETUP';
                \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_status', $current_status)->save();
                \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_miniorange_oauth_client_customer_admin_email')->save();
                \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_oauth_client_customer_admin_phone')->save();
                \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_oauth_client_tx_id')->save();
                \Drupal::messenger()->addMessage(t('Register/Login with your miniOrange Account'),'status');
        }

        public function miniorange_oauth_client_resend_otp(&$form, $form_state) {
                \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_oauth_client_tx_id')->save();
                $username = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_email');
                $phone = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_phone');
                $customer_config = new MiniorangeOAuthClientCustomer($username, $phone, NULL, NULL);
                $send_otp_response = json_decode($customer_config->sendOtp());
                if ($send_otp_response->status == 'SUCCESS') {
                    // Store txID.
                    \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_tx_id', $send_otp_response->txId)->save();
                    $current_status = 'VALIDATE_OTP';
                    \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_status', $current_status)->save();
                    \Drupal::messenger()->addMessage(t('Verify email address by entering the passcode resent to @username', array('@username' => $username)));
                }
        }

        public function miniorange_oauth_client_validate_otp_submit(&$form, $form_state) {
                $otp_token = trim($form['markup_top_vt_start']['miniorange_oauth_client_customer_otp_token']['#value']);
                if ($otp_token == NULL)
                {
                    \Drupal::messenger()->addMessage(t('Please enter OTP first.'), 'error');
                    return;
                }
                $username = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_email');
                $phone = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_phone');
                $tx_id = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_tx_id');
                $customer_config = new MiniorangeOAuthClientCustomer($username, $phone, NULL, $otp_token);
                $validate_otp_response = json_decode($customer_config->validateOtp($tx_id));
                if ($validate_otp_response->status == 'SUCCESS')
                {
                    \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_oauth_client_tx_id')->save();
                    $password = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_password');
                    $customer_config = new MiniorangeOAuthClientCustomer($username, $phone, $password, NULL);
                    $create_customer_response = json_decode($customer_config->createCustomer());
                    if ($create_customer_response->status == 'SUCCESS') {
                        $current_status = 'PLUGIN_CONFIGURATION';
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_status', $current_status)->save();
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_admin_email', $username)->save();
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_admin_phone', $phone)->save();
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_admin_token', $create_customer_response->token)->save();
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_id', $create_customer_response->id)->save();
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_customer_api_key', $create_customer_response->apiKey)->save();
                        \Drupal::messenger()->addMessage(t('Account created successfully.'));
                    }
                    else if(trim($create_customer_response->message) == 'Email is not enterprise email.' || ($create_customer_response->status) == "INVALID_EMAIL_QUICK_EMAIL" )
                    {
                        \Drupal::messenger()->addMessage(t('There was an error creating an account for you. You may have entered an invalid Email-Id
                        <strong>(We discourage the use of disposable emails) </strong>
                        <br>Please try again with a valid email.'), 'error');
                        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_status', 'CUSTOMER_SETUP')->save();
                    }
                    else {
                        \Drupal::messenger()->addMessage(t('Error in creating an account for you. Please try again.'), 'error');
                    }
                }
                else {
                    \Drupal::messenger()->addMessage(t('Invalid OTP provided. Please enter the correct OTP'), 'error');
                }
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
