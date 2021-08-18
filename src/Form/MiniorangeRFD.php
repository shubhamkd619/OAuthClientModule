<?php
/**
 * @file
 * Contains Attribute for miniOrange OAuth Client Module.
 */

 /**
 * Showing Settings form.
 */
namespace Drupal\miniorange_oauth_client\Form;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Render;
use Drupal\miniorange_oauth_client\Utilities;

class MiniorangeRFD extends FormBase {

  public function getFormId() {
    return 'miniorange_oauth_client_rfd';
  }


    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "miniorange_oauth_client/miniorange_oauth_client.admin",
                    "miniorange_oauth_client/miniorange_oauth_client.style_settings",
                    "miniorange_oauth_client/miniorange_oauth_client.module",
                )
            ),
        );

        $user_email = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_email');

        $form['markup_1'] = array(
            '#markup' =>'<div class="mo_oauth_table_layout_1"><div class="mo_oauth_table_layout mo_oauth_container">'
        );

        $form['markup_top_vt_start'] = array(
            '#type' => 'fieldset',
            '#title' => t('REQUEST FOR DEMO'),
            '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
            '#markup' => '<br><hr><br>',
        );

        $form['markup_top_vt_start']['markup_2'] = array(
            '#markup' => '<div class="mo_oauth_highlight_background_note_export"><p><strong>Want to test any of the paid modules before purchasing? </strong></p>
                          <p>Just send us a request, We will setup a demo site for you on our cloud with any of the paid modules and provide you with the administrator credentials.
                          You can configure it with your OAuth/OpenID Connect Server and test all the features as per your requirement.</p>  
                          </div><br>',
        );

        $form['markup_top_vt_start']['customer_email'] = array(
            '#type' => 'email',
            '#title' => t('Email'),
            '#required' => TRUE,
            '#default_value' => t(strval($user_email)),
            '#attributes' => array('style' => 'width:65%;', 'placeholder' => 'Enter your email'),
            '#description' => t('<b>Note:</b> Use valid Email ID. ( We discourage the use of disposable emails )'),
        );

        $form['markup_top_vt_start']['demo_plan'] = array(
            '#type' => 'select',
            '#title' => t('Demo Plan'),
            '#attributes' => array('style' => 'width:65%;'),
            '#options' => [
                'Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Standard Module' => t('Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Standard Module'),
                'Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Premium Module' => t('Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Premium Module'),
                'Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Enterprise Module' => t('Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Enterprise Module'),
                'Not Sure' => t('Not Sure'),
            ],
        );

        $form['markup_top_vt_start']['description_doubt'] = array(
            '#type' => 'textarea',
            '#title' => t('Description'),
            '#attributes' => array('style' => 'width:65%', 'placeholder' => 'Describe your requirement'),
            '#required' => TRUE,
        );

        $form['markup_top_vt_start']['submit_button'] = array(
            '#type' => 'submit',
            '#value' => t('Submit'),
            '#prefix' => '<br>',
            '#suffix' => '<br><br></div>',
        );

        Utilities::nofeaturelisted($form, $form_state);
        $form['mo_markup_div_end1']=array('#markup'=>'</div>');
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $email = trim($form['markup_top_vt_start']['customer_email']['#value']);
        $demo_plan = $form['markup_top_vt_start']['demo_plan']['#value'];
        $description_doubt = trim($form['markup_top_vt_start']['description_doubt']['#value']);
        $query = $demo_plan.' -> '.$description_doubt;
        Utilities::send_demo_query($email, $query,$description_doubt);
    }
}
