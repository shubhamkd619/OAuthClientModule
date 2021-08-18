<?php
/**
 * @file
 * Contains support form for miniOrange OAuth Server Module.
 */

/**
 * Showing Support form info.
 */
namespace Drupal\miniorange_oauth_client\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\miniorange_oauth_client\MiniorangeOAuthClientSupport;

class MiniorangeSupport extends FormBase {

    public function getFormId() {
        return 'miniorange_oauth_client_support';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "miniorange_oauth_client/miniorange_oauth_client.style_settings",
                )
            ),
        );

        $form['header_top_style_1'] = array('#markup' => '<div class="mo_oauth_table_layout_1">');

        $form['markup_top'] = array(
            '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container">',
        );

        $form['markup_1'] = array(
            '#markup' => '<div><h2>Support</h2><hr><div></br>Need any help? Just send us a query so we can help you.<br/><br/></div>',
        );

        $form['miniorange_oauth_client_email_address'] = array(
            '#type' => 'textfield',
            '#title' => t('Email Address'),
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'Enter your email'),
            '#required' => TRUE,
        );

        $form['miniorange_oauth_client_phone_number'] = array(
            '#type' => 'textfield',
            '#title' => t('Phone number'),
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'Enter your phone number'),
        );

        $form['miniorange_oauth_client_support_query'] = array(
            '#type' => 'textarea',
            '#title' => t('Query'),
            '#cols' => '10',
            '#rows' => '5',
            '#attributes' => array('style' => 'width:73%;','placeholder' => 'Write your query here'),
            '#required' => TRUE,
        );

        $form['miniorange_oauth_client_support_submit'] = array(
            '#type' => 'submit',
            '#value' => t('Submit Query'),
            '#attributes' => array('style' => 'border-radius:4px;background: #337ab7;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;box-shadow: 0 1px 0 #337ab7;border-color: #337ab7 #337ab7 #337ab7;display:block;margin-left:auto;margin-right:auto;'),
        );

        $form['miniorange_oauth_client_support_note'] = array(
            '#markup' => '<div><br/>If you want custom features in the module, just drop an email to <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a></div></div></div>'
        );

        return $form;

    }

    /**
     * Send support query.
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        $email = trim($form['miniorange_oauth_client_email_address']['#value']);
        $phone = $form['miniorange_oauth_client_phone_number']['#value'];
        $query = trim($form['miniorange_oauth_client_support_query']['#value']);
        $support = new MiniorangeOAuthClientSupport($email, $phone, $query);
        $support_response = $support->sendSupportQuery();
        if($support_response) {
            \Drupal::messenger()->addMessage(t('Support query successfully sent. We will get back to you shortly.'));
        }
        else {
            \Drupal::messenger()->addMessage(t('Error sending support query'), 'error');
        }
    }
}
