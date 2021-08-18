jQuery(document).ready( function () {

    jQuery("#edit-miniorange-saml-idp-support-side-button").click(function (e) {
        e.preventDefault();
        if (jQuery("#mosaml-feedback-form").css("right") != "0px") {
            //show
            jQuery("#mosaml-feedback-overlay").show();
            jQuery("#mosaml-feedback-form").animate({
                "right": "0px"
            });
        } else {
            jQuery("#mosaml-feedback-overlay").hide();
            jQuery("#mosaml-feedback-form").animate({
                "right": "-391px"
            });
        }
    });
});


//Textfield added in case if other is selected from drop-down box in mapping tab for email or username
jQuery('#mo_oauth_email_attribute').change( function () {
  if (jQuery('#mo_oauth_email_attribute').val() == 'other'){
    jQuery('#miniorange_oauth_client_other_field_for_email').css('display','');
  }
  else{
    jQuery('#miniorange_oauth_client_other_field_for_email').css('display','none');
  }
} )

if (jQuery('#mo_oauth_email_attribute').val() == 'other'){
  jQuery('#miniorange_oauth_client_other_field_for_email').css('display','');
}

jQuery('#mo_oauth_name_attribute').change( function () {
  if (jQuery('#mo_oauth_name_attribute').val() == 'other'){
    jQuery('#miniorange_oauth_client_other_field_for_name').css('display','');
  }
  else{
    jQuery('#miniorange_oauth_client_other_field_for_name').css('display','none');
  }
} )

  if (jQuery('#mo_oauth_name_attribute').val() == 'other'){
  jQuery('#miniorange_oauth_client_other_field_for_name').css('display','');
}

