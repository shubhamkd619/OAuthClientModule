<?php
namespace Drupal\miniorange_oauth_client;
class feedback{
    public static function miniorange_oauth_client_feedback()
    {
        global $base_url;
        if(!empty(\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_base_url')))
            $baseUrlValue = \Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_base_url');
        else
            $baseUrlValue = $base_url;
        $feedback_url = $baseUrlValue.'/feedback';

        $_SESSION['mo_other']= "True";
        $form_id=$_POST['form_id'];
        $form_token=$_POST['form_token'];

        ?>
        <html>
        <head>
            <title>Feedback</title>
            <link href="https://fonts.googleapis.com/css?family=PT+Serif" rel="stylesheet">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
            <style>
                .sp_loader {
                    margin: auto;
                    display: block;
                    border: 5px solid #f3f3f3; /* Light grey */
                    border-top: 5px solid #3498db; /* Blue */
                    border-radius: 50%;
                    width: 50px;
                    height: 50px;
                    animation: spin 2s linear infinite;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
            <script type="text/javascript">
                $(document).ready(function () {
                    $("#myModal").modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                });
                $(function () {
                    $(".button").click(function () {
                        document.getElementById('sp_loader').style.display = 'block';
                        var reason = $("input[name='performance']:checked").val();
                        var q_feedback = document.getElementById("sso_feedback").value;
                        return false;
                    });
                })
            </script>
        </head>
        <body>
        <div class="container">
            <div class="modal fade" id="myModal" role="dialog" style="background: rgba(0,0,0,0.1);">
                <div class="modal-dialog" style="width: 500px;">
                    <div class="modal-content" style="border-radius: 20px">
                        <div class="modal-header"
                             style="padding: 25px; border-top-left-radius: 20px; border-top-right-radius: 20px; background-color: #8fc1e3;">

                            <h4 class="modal-title" style="color: white; text-align: center;"> Hey, it seems like you want to deactivate miniOrange OAuth Client Module</h4>
                            <hr>
                            <h4 style="text-align: center; color: white;">What happened?</h4>
                        </div>
                        <div class="modal-body"
                             style="font-size: 11px; padding-left: 25px; padding-right: 25px; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px; background-color: #ececec;">
                            <form name="f" action="<?php echo $feedback_url;  ?>" id="mo_feedback">
                                <div>
                                    <p>
                                        <?php
                                        if(empty(\Drupal::config('miniorange_oauth_client.settings')->get('miniorange_oauth_client_customer_admin_email')))
                                        { ?>
                                        <br>Email ID: <input onblur="validateEmail(this)" class="form-control"
                                                             type="email" id="miniorange_feedback_email" required
                                                             name="miniorange_feedback_email"/>
                                    <p style="display: none;color:red" id="email_error">Invalid Email</p>
                                    <?php } ?>
                                    <br>
                                    <?php
                                    $deactivate_reasons = array
                                    (
                                      "Does not have the features I'm looking for",
                                      "Confusing Interface",
                                      "Redirecting back to login page after Authentication",
                                      "Not Working",
                                      "Not Receiving OTP During Registration",
                                      "Bugs in the module",
                                      "Other Reasons:"
                                    );
                                    foreach ( $deactivate_reasons as $deactivate_reasons )
                                    {
                                        ?>
                                        <div  class="radio" style="padding:2px;font-size: 8px">
                                            <label style="font-weight:normal;font-size:14.6px;color:maroon" for="<?php echo $deactivate_reasons; ?>">

                                                <input type="radio" name="deactivate_plugin" value="<?php echo $deactivate_reasons;?>" required>
                                                <?php echo $deactivate_reasons; ?>
                                            </label>
                                        </div>

                                    <?php } ?>
                                    <input type="hidden" name="mo_oauth_client_check" value="True">
                                    <input type="hidden" name="form_token" value=<?php echo $form_token ?> >
                                    <input type="hidden" name="form_id" value= <?php echo $form_id ?>>
                                    <br>
                                    <textarea  class="form-control" id="query_feedback" name="query_feedback"  rows="4" style="margin-left:2%" cols="50" placeholder="Write your query here"></textarea>
                                    <br><br>
                                    <div class="mo2f_modal-footer">
                                        <input type="submit" style="margin: auto; display: block; font-size: 11px; float: left;" name="miniorange_feedback_submit" class="btn btn-primary btn-large" value="Submit and Continue" />
                                        <input type="submit" formnovalidate="formnovalidate" style="margin: auto; display: block; font-size: 11px; float: right;" name="miniorange_feedback_skip" class="btn btn-link" value="Skip" />
                                    </div>
                                    <div class="sp_loader" id="sp_loader" style="display: none;"></div>
                                    <?php
                                    echo "<br><br>";
                                    foreach($_POST as $key => $value) {
                                        self::hiddenOauthClientFields($key,$value);
                                    }
                                    ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </body>

        </html>
        <?php
        exit;
    }
    public static function hiddenOauthClientFields($key,$value)
    {
        $hiddenOauthClientField = "";
        $value2 = array();
        if(is_array($value)) {
            foreach($value as $key2 => $value2)
            {
                if(is_array($value2)){
                    self::hiddenOauthClientFields($key."[".$key2."]",$value2);
                } else {
                    $hiddenOauthClientField = "<input type='hidden' name='".$key."[".$key2."]"."' value='".$value2."'>";
                }
            }
        }else{
            $hiddenOauthClientField = "<input type='hidden' name='".$key."' value='".$value."'>";
        }

        echo $hiddenOauthClientField;
    }
}
?>
