<?php
 /**
 * @file
 * Contains \Drupal\miniorange_oauth_client\Controller\DefaultController.
 */

namespace Drupal\miniorange_oauth_client\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\miniorange_oauth_client\sso_feedback;
use Drupal\miniorange_oauth_client\Utilities;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Component\Utility;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Extension;
use Drupal\Component\Utility\Html;
use Drupal\miniorange_oauth_client\handler;
class miniorange_oauth_clientController extends ControllerBase {
    public function miniorange_oauth_client_feedback_func(){
        global $base_url;
        if (isset($_GET['miniorange_feedback_submit'])){
        $modules_info = \Drupal::service('extension.list.module')->getExtensionInfo('miniorange_oauth_client');
        $modules_version = $modules_info['version'];
        $_SESSION['mo_other']="False";
        $reason=$_GET['deactivate_plugin'];
        $q_feedback=$_GET['query_feedback'];
        $message='Reason: '.$reason.'<br>Feedback: '.$q_feedback;
        $url = 'https://login.xecurify.com/moas/api/notify/send';
        $ch = curl_init($url);
        $email =\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_email');
        if(empty($email))
            $email = $_GET['miniorange_feedback_email'];
        $phone = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_phone');
        $customerKey= \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_id');
        $apikey = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_api_key');
        if($customerKey==''){
        $customerKey="16555";
        $apikey="fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
        }
        $currentTimeInMillis = self::get_oauth_timestamp();
        $stringToHash 		= $customerKey .  $currentTimeInMillis . $apikey;
        $hashValue 			= hash("sha512", $stringToHash);
        $customerKeyHeader 	= "Customer-Key: " . $customerKey;
        $timestampHeader 	= "Timestamp: " .  $currentTimeInMillis;
        $authorizationHeader= "Authorization: " . $hashValue;
        $fromEmail 			= $email;
        $subject            = 'Drupal ' . \DRUPAL::VERSION . ' OAuth Client Module Feedback | '.$modules_version;
        $query        = '[Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Client | '.$modules_version.']: ' . $message;
        $content='<div >Hello, <br><br>Company :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>Phone Number :'.$phone.'<br><br>Email :<a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br>Query :'.$query.'</div>';
        $fields = array(
            'customerKey'	=> $customerKey,
            'sendEmail' 	=> true,
            'email' 		=> array(
            'customerKey' 	=> $customerKey,
            'fromEmail' 	=> $fromEmail,
            'fromName' 		=> 'miniOrange',
            'toEmail' 		=> 'drupalsupport@xecurify.com',
            'toName' 		=> 'drupalsupport@xecurify.com',
            'subject' 		=> $subject,
            'content' 		=> $content
            ),
        );
        $field_string = json_encode($fields);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $customerKeyHeader,
            $timestampHeader, $authorizationHeader));
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);
        if(curl_errno($ch)){
            return json_encode(array("status"=>'ERROR','statusMessage'=>curl_error($ch)));
        }
        curl_close($ch);
    }
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_uninstall_status',1)->save();
        \Drupal::service('module_installer')->uninstall(['miniorange_oauth_client']);
        if(!empty(\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_base_url')))
            $baseUrlValue = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_base_url');
        else
            $baseUrlValue = $base_url;
        $uninstall_redirect = $baseUrlValue.'/admin/modules';
        return new RedirectResponse($uninstall_redirect);
    }


  /**
	 * This function is used to get the timestamp value
	 */
	public static function get_oauth_timestamp() {
		$url = 'https://login.xecurify.com/moas/rest/mobile/get-timestamp';
		$ch  = curl_init( $url );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false ); // required for https urls
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_POST, true );
		$content = curl_exec( $ch );
		if ( curl_errno( $ch ) ) {
			echo 'Error in sending curl Request';
			exit ();
		}
		curl_close( $ch );
		if(empty( $content )){
			$currentTimeInMillis = round( microtime( true ) * 1000 );
			$currentTimeInMillis = number_format( $currentTimeInMillis, 0, '', '' );
		}
		return empty( $content ) ? $currentTimeInMillis : $content;
	}
    public function miniorange_oauth_client_mo_login(){
        $code = isset($_GET['code']) ? $_GET['code'] : '';
        $code = Html::escape($code);
        $state = isset($_GET['state']) ? $_GET['state'] : '';
        $state = Html::escape($state);

        if( isset( $code) && isset($state ) ){
            if(session_id() == '' || !isset($_SESSION))
				session_start();
            if (!isset($code)){
                if(isset($_GET['error_description']))
                    exit($_GET['error_description']);
			    else if(isset($_GET['error']))
			        exit($_GET['error']);
			    exit('Invalid response');
            }
            else {
                $currentappname = "";
                if (isset($_SESSION['appname']) && !empty($_SESSION['appname']))
                    $currentappname = $_SESSION['appname'];
                else if (isset($state) && !empty($state)) {
                    $currentappname = base64_decode($state);
                }
                if (empty($currentappname)) {
                    exit('No request found for this application.');
                }
            }
        }
        // Getting Access Token
        $app = array();
        $app = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_appval');
        $name_attr = "";
        $email_attr = "";
        $name = "";
        $email ="";
		if(isset($app['miniorange_oauth_client_email_attr'])){
		      $email_attr = trim($app['miniorange_oauth_client_email_attr']);
        }
		if(isset($app['miniorange_oauth_client_name_attr'])){
            $name_attr = trim($app['miniorange_oauth_client_name_attr']);
        }
        $parse_from_header = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_send_with_header_oauth');
        $parse_from_body = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_send_with_body_oauth');

        if (!$parse_from_header == TRUE || !$parse_from_header == 1)
            $parse_from_header = false;
        if (!$parse_from_body == TRUE || !$parse_from_body == 1)
            $parse_from_body = false;

        $accessToken = self::getAccessToken($app['access_token_ep'], 'authorization_code',
            $app['client_id'], $app['client_secret'], $code, $app['callback_uri'], $parse_from_header, $parse_from_body);

        if(!$accessToken){
            print_r('Invalid token received.');
            exit;

        }
        $resourceownerdetailsurl = $app['user_info_ep'];
        if (substr($resourceownerdetailsurl, -1) == "=") {
            $resourceownerdetailsurl .= $accessToken;
        }
        $resourceOwner = self::getResourceOwner($resourceownerdetailsurl, $accessToken);

        /*
        *   Test Configuration
        */
        if (isset($_COOKIE['Drupal_visitor_mo_oauth_test']) && ($_COOKIE['Drupal_visitor_mo_oauth_test'] == true)){
            $_COOKIE['Drupal_visitor_mo_oauth_test'] = 0;
            $module_path = \Drupal::service('extension.list.module')->getPath('miniorange_oauth_client');
            $username = isset($resourceOwner['email']) ? $resourceOwner['email']:'User';
            $someattrs = '';
            Utilities::show_attr($resourceOwner, $someattrs, 0, '', '<tr style="text-align:center;">', "<td style='font-weight:bold;padding:2%;border:2px solid #949090; word-wrap:break-word;'>" );
            $resourceOwner_encoded = json_encode($resourceOwner);
          \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_attr_list_from_server',$resourceOwner_encoded)->save();
            echo '<div style="font-family:Calibri;padding:0 3%;">';

            echo    '<div style="display:block;text-align:center;margin-bottom:4%;">
                        <img style="width:15%;"src="'. $module_path . '/includes/images/green_check.png">
                      </div>';

            echo '<span style="font-size:13pt;"><b>Hello</b>, '.$username.'</span><br><br><div style="background-color:#dff0d8;padding:1%;">Your Test Connection is successful. Now, follow the below steps to complete the last step of your configuration:</div><span style="font-size:13pt;"><br><b></b>Please select the <b>Attribute Name</b> in which you are getting <b>Email ID.</b><br><br></span><div style="background-color: #dddddd; margin-left: 2%; margin-right: 3%">';

            self::miniorange_oauth_client_update_email_username_attribute($resourceOwner);

            echo '<br>&emsp;<i style="font-size: small">You can also map the Username attribute from the Attribute and Role Mapping tab in the module.</i><br><br></div>
                    <br><i>Click on the <b>Done</b> button to save your changes.</i><br>';

          echo '<div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;
                            border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;
                            box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="save_and_done();"></div>
                    <script>
                        function close_and_redirect(){
                            window.opener.redirect_to_attribute_mapping();
                            self.close();
                        }
                        function redirect_to_attribute_mapping(){
                            var baseurl = window.location.href.replace("config_clc","mapping");
                            window.location.href= baseurl;
                          }

                        function save_and_done(){
                          var email_attr = document.getElementById("mo_oauth_email_attribute").value;
                          var index = window.location.href.indexOf("?");
                          var url = window.location.href.slice(0,index).replace("mo_login","mo_post_testconfig/?field_selected="+email_attr);
                          window.opener.location.href= url;
                          self.close();
                        }
                    </script>';



             echo       '<p><b> ATTRIBUTES RECEIVED:</b></p><table style="border-collapse:collapse;border-spacing:0; display:table;width:100%; font-size:13pt;background-color:#EDEDED;">
                          <tr style="text-align:center;">
                              <td style="font-weight:bold;border:2px solid #949090;padding:2%;width: fit-content;">ATTRIBUTE NAME</td>
                              <td style="font-weight:bold;padding:2%;border:2px solid #949090; word-wrap:break-word;">ATTRIBUTE VALUE</td>
                          </tr>';
            echo $someattrs;
            echo '</table></div>';

                    return new Response();
                    exit();
        }
        if(!empty($email_attr))
            $email = self::getnestedattribute($resourceOwner, $email_attr);          //$resourceOwner[$email_attr];
        if(!empty($name_attr))
            $name = self::getnestedattribute($resourceOwner, $name_attr);          //$resourceOwner[$name_attr];
        global $base_url;
        /*************==============Attributes not mapped check===============************/
        if(empty($email))
        {
            echo '<div style="font-family:Calibri;padding:0 3%;">';
            echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div><div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Email address does not received.</p><p>Check your <b>Attribute Mapping</b> configuration.</p><p><strong>Possible Cause: </strong>Email Attribute field is not configured.</p></div><div style="margin:3%;display:block;text-align:center;"></div><div style="margin:3%;display:block;text-align:center;"><form action="'.$base_url.'" method ="post"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="Done"></form></div>';
            exit;
            return new Response();
        }
        //Validates the email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          echo '<div style="font-family:Calibri;padding:0 3%;">';
          echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div><div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Invalid email format of the received value.</p><p>Check your <b>Attribute Mapping</b> configuration.</p><p><strong>Possible Cause: </strong>Email Attribute field is incorrectly configured.</p></div><div style="margin:3%;display:block;text-align:center;"></div><div style="margin:3%;display:block;text-align:center;"><form action="'.$base_url.'" method ="post"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="Done"></form></div>';
          exit;
          return new Response();
        }
        if(empty($name)){
            $name = $email;
        }
        $account ='';
        if(!empty($email))
            $account = user_load_by_mail($email);
        if($account == null){
            if(!empty($name) && isset($name))
                $account = user_load_by_name($name);
        }

	    global $user;

        /*************================================== Create user if not already present. ======================================*************/
        if (!isset($account->uid)) {

            echo '<div style="font-family:Calibri;padding:0 3%;">';
            echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div><div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>User Not Found in Drupal.</p><p>You can only log in the existing Drupal users in this version of the module.<br><br>Please upgrade to either the <a href="https://plugins.miniorange.com/drupal-oauth-client#pricing" target="_blank">Standard, Premium or the Enterprise </a> version of the module in order to create unlimited new users.</p></div><div style="margin:3%;display:block;text-align:center;"></div><div style="margin:3%;display:block;text-align:center;"><form action="'.$base_url.'" method ="post"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="submit" value="Done"></form></div>';
            exit;
            return new Response();
        }
        $user = User::load($account->id());
        $edit = array();
        if(!empty(\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_base_url')))
            $baseUrlValue = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_base_url');
        else
            $baseUrlValue = $base_url;
        $edit['redirect'] = $baseUrlValue;
      user_login_finalize($account);

      $redi = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_redirect_url');
      $response = new RedirectResponse($redi);
        $response->send();
        return new Response();
    }

    public function mo_post_testconfig(){
      $email_attr = $_GET['field_selected'];
      $config = \Drupal::config('miniorange_oauth_client.settings');
      $app_name = $config->get('miniorange_auth_client_app_name');
      $app_values = $config->get('miniorange_oauth_client_appval');
      $app_values['miniorange_oauth_client_email_attr'] = $email_attr;
      \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_appval',$app_values)->save();
      \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_client_email_attr_val',$email_attr)->save();

      \Drupal::messenger()->addMessage(t('Configurations saved successfully. Please go to your Drupal site’s login page where you will automatically find a <b> Login with ' . $app_name . ' </b> link.'));

      global $base_url;
      $response = new RedirectResponse($base_url."/admin/config/people/miniorange_oauth_client/mapping");
      $response->send();
      return new Response();
    }


    /**
     * This function gets the access token from the server
     */
    public function getAccessToken($tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url, $send_headers, $send_body) {
        $ch = curl_init($tokenendpoint);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_POST, true);

        if($send_headers && !$send_body) {

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Basic ' . base64_encode( $clientid . ":" . $clientsecret ),
                'Accept: application/json'
            ));
            curl_setopt( $ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($redirect_url).'&grant_type='.$grant_type.'&code='.$code);

        }else if(!$send_headers && $send_body){

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json'
            ));
            curl_setopt( $ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($redirect_url).'&grant_type='.$grant_type.'&client_id='.$clientid.'&client_secret='.$clientsecret.'&code='.$code);
        }else {

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Basic ' . base64_encode( $clientid . ":" . $clientsecret ),
                'Accept: application/json'
            ));
            curl_setopt( $ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($redirect_url).'&grant_type='.$grant_type.'&client_id='.$clientid.'&client_secret='.$clientsecret.'&code='.$code);
        }

        $content = curl_exec($ch);
        if(curl_error($ch)){
            echo "<b>Response : </b><br>";print_r($content);echo "<br><br>";
            exit( curl_error($ch) );
        }

        if(!is_array(json_decode($content, true))){
            echo "<b>Response : </b><br>";print_r($content);echo "<br><br>";
            exit("Invalid response received.");
        }
        $content = json_decode($content,true);
        if (isset($content["error"])) {
            if (is_array($content["error"])) {
                $content["error"] = $content["error"]["message"];
            }
            exit($content["error"]);
        }
        else if(isset($content["error_description"])){
            exit($content["error_description"]);
        }
        else if(isset($content["access_token"])) {
            $access_token = $content["access_token"];
        } else {
            exit('Invalid response received from OAuth Provider. Contact your administrator for more details.');
        }
        return $access_token;
    }
    public function getResourceOwner($resourceownerdetailsurl, $access_token){
        $ch = curl_init($resourceownerdetailsurl);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt( $ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.$access_token
        ));
        $t_vers = curl_version();
        curl_setopt( $ch, CURLOPT_USERAGENT, 'curl/' . $t_vers['version']);
        $content = curl_exec($ch);
        if(curl_error($ch)){
            exit( curl_error($ch) );
        }
        if(!is_array(json_decode($content, true))) {
            exit("Invalid response received.");
        }

        $content = json_decode($content,true);
        if (isset($content['@odata.context'])){
        	unset($content['@odata.context']);
        }
        if(isset($content["error_description"])){
            if(is_array($content["error_description"]))
                print_r($content["error_description"]);
            else
                echo $content["error_description"];
            exit;
        } else if(isset($content["error"])){
            if(is_array($content["error"]))
                print_r($content["error"]);
            else
                echo $content["error"];
            exit;
        }
        return $content;
    }

    public static function getnestedattribute($resource, $key){
        if(empty($key))
            return "";
        $keys = explode(".",$key);
        $currentkey = "";
        if(sizeof($keys)>1){
            $currentkey = $keys[0];
            if(isset($resource[$currentkey]))
                return self::getnestedattribute($resource[$currentkey], str_replace($currentkey.".","",$key));
        }else{
            $currentkey = $keys[0];
            if(isset($resource[$currentkey]))
            {
                if(is_array($resource[$currentkey]))
                {
                    $resource = $resource[$currentkey];
                    return $resource[0];
                }
                else{
                    return $resource[$currentkey];
                }
            }
        }
    }

    public static function mo_oauth_client_initiateLogin() {
        global $base_url;
        isset($_SERVER['HTTP_REFERER']) ? $redi = $_SERVER['HTTP_REFERER'] : $redi = $base_url;
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_oauth_redirect_url',$redi)->save();
        $app_name = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_app_name');
        $client_id = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_client_id');
        $client_secret = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_client_secret');
        $scope = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_scope');
        $authorizationUrl =\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_authorize_endpoint');
        $access_token_ep =\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_access_token_ep');
        $user_info_ep = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_user_info_ep');

        if ($app_name==NULL||$client_secret==NULL||$client_id==NULL||$scope==NULL||$authorizationUrl==NULL||$access_token_ep==NULL||$user_info_ep==NULL) {
            echo '<div style="font-family:Calibri;padding:0 3%;">';
            echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div><div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>OAuth Server configurations could not be found.</p><p>Check your <b>OAuth Server</b> configuration.</p><p><strong>Possible Cause: </strong>OAuth Server configurations are not completed.</p></div><div style="margin:3%;display:block;text-align:center;"></div><div style="margin:3%;display:block;text-align:center;"></div>';
            exit;
            return new Response();
        }

        $callback_uri = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_auth_client_callback_uri');
        $state = base64_encode($app_name);
        if (strpos($authorizationUrl,'?') !== false) {
            $authorizationUrl =$authorizationUrl. "&client_id=".$client_id."&scope=".$scope."&redirect_uri=".$callback_uri."&response_type=code&state=".$state;
        } else {
            $authorizationUrl =$authorizationUrl. "?client_id=".$client_id."&scope=".$scope."&redirect_uri=".$callback_uri."&response_type=code&state=".$state;
        }
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['oauth2state'] = $state;
        $_SESSION['appname'] = $app_name;
        $response = new RedirectResponse($authorizationUrl);
        $response->send();
        return new Response();
    }
    public function test_mo_config(){
        user_cookie_save(array("mo_oauth_test" => true));
        self::mo_oauth_client_initiateLogin();
        return new Response();
    }

    public function reset_mo_config(){
        global $base_url;
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_oauth_client_app')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_oauth_client_appval')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_auth_client_client_id')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_auth_client_app_name')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_auth_client_display_name')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_auth_client_client_secret')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_auth_client_scope')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_auth_client_authorize_endpoint')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_auth_client_access_token_ep')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_oauth_client_email_attr_val')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_oauth_client_name_attr_val')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_auth_client_user_info_ep')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_auth_client_stat')->save();
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->clear('miniorange_oauth_client_attr_list_from_server')->save();
        \Drupal::messenger()->addMessage("Your Configurations have been deleted successfully");
        if(!empty(\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_base_url')))
            $baseUrlValue = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_base_url');
        else
            $baseUrlValue = $base_url;

        $miniorange_auth_client_callback_uri = $baseUrlValue."/mo_login";
        \Drupal::configFactory()->getEditable('miniorange_oauth_client.settings')->set('miniorange_auth_client_callback_uri',$miniorange_auth_client_callback_uri)->save();

        $response = new RedirectResponse($baseUrlValue."/admin/config/people/miniorange_oauth_client/config_clc");
        $response->send();
        exit;
    }
    public static function miniorange_oauth_client_mologin(){
        global $base_url;
        user_cookie_save(array("mo_oauth_test" => false));
        $enable_login = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_enable_login_with_oauth');
        if ($enable_login) {
            self::mo_oauth_client_initiateLogin();
            return new Response();
        }else {
            \Drupal::messenger()->addMessage(t('Please enable <b>Login with OAuth</b> to initiate the SSO.'), 'error');
            return new RedirectResponse($base_url);
        }
    }

    public function miniorange_oauth_client_update_email_username_attribute($data){
      $options = '';
      $selected_flag = 0;
      foreach ($data as $key => $value) {
        if ( $selected_flag == 0 && ($key == 'email' || $key == 'email.0')){
          $options = $options . '<option value="email" selected> email </option>';
          $selected_flag = 1;
        }
        elseif ( $selected_flag == 0 && ($key == 'emails' || $key == 'emails.0')){
          $options = $options . '<option value="emails" selected> emails </option>';
          $selected_flag = 1;
        }
        else {
          $options = $options . '<option value="' . $key . '"> ' . $key . ' </option>';
        }
      }

      $html_string = '<p style="display: inline-block;">&emsp;<b> Email Attribute </b></p> &nbsp;&nbsp;&nbsp; <select id="mo_oauth_email_attribute" style="height: 32px;">' . $options . '</select>
                        &nbsp;&nbsp;&nbsp; <input style="display: none;" id="miniorange_oauth_client_other_field_for_email" placeholder="Enter Email Attribute">';

      echo $html_string. '';
      return new Response();
    }
}
