var moTour;
var pointerNumber = 0;
var nextTabOn =false;
var lastTab =false;
var tabtourArray;

jQuery(document).ready( function () {

    jQuery("#edit-miniorange-saml-idp-support-side-button").click(function (e) {
        e.preventDefault();
        if (jQuery("#mosaml-feedback-form").css("right") != "0px") {
            //show
            jQuery("#mosaml-feedback-overlay").show();
            jQuery("#mosaml-feedback-form").animate({
                "right": "0px"
            });
        }
        else {
            jQuery("#mosaml-feedback-overlay").hide();
            jQuery("#mosaml-feedback-form").animate({
                "right": "-391px"
            });
        }
    });

    moTour = JSON.parse(jQuery("input[name='tourArray']").val());
    addIDs();

    if(!moTour.tourTaken) {
        moTour = JSON.parse(jQuery("input[name='tourArray']").val());
        resetTour();
        startTour(pointerNumber);
    }
    document.getElementById('Restart_moTour').onclick = function() {
        moTour = JSON.parse(jQuery("input[name='tourArray']").val());
        resetTour();
        startTour(pointerNumber);
    }
});

function startTour(pointerNumber){
    addOverlay();
    createCard(pointerNumber);
}

function addOverlay() {
    var overlay = ' <div class="mo-tour-backdrop" ><div id="overlay" class="mo-tour-overlay"></div></div>';
    jQuery(overlay).insertAfter('div.dialog-off-canvas-main-canvas');
}
function createCard(pointerNumber)
{
    var tourElement =   moTour.tourData[pointerNumber];
    // change content when target is reached
    if((tourElement.contentHTML=='Click here to see the detailed documentation of OAuth Server. Click on Next to proceed ahead.') || (tourElement.contentHTML=='Provide this <b>Callback/Redirect URL</b> to OAuth Server to configure with Drupal.') || (tourElement.contentHTML=='Enter Client ID and Client Secret to configure with OAuth Server. You can get these details from OAuth Server.')){
       let selectedOauthApp = document.getElementById('miniorange_oauth_client_app').value;
       if(selectedOauthApp.length != 0){
            tourElement.contentHTML = tourElement.contentHTML.replace(/OAuth Server/g, selectedOauthApp);
       }
    }

    var card        =   '<div id="mo-card" class="mo-card mo-'+tourElement.cardSize+'">'
        +'  <div class="mo-tour-arrow mo-point-'+tourElement.pointToSide+'">' +
        '<div style="color:#ffffff;position: relative;" class="mo-saml-arrow mo-saml-arrow-'+tourElement.pointToSide+'">' + '</div>' +
        '</div>'
        +'  <div class="mo-tour-content-area mo-point-'+tourElement.pointToSide+'">' +
        '<div class="mo-tour-title mo-saml-header">'+tourElement.titleHTML+'</div>' +
        '<div class="mo-tour-content">'+tourElement.contentHTML+'</div>'+
        '<img id="mo-card-content-img" '+(tourElement.img ? '':'hidden')+' src="'+tourElement.img+'" alt=""> '
        +'  <div class="mo-tour-button-area"></div>'
        +'  <div hidden class="mo-tour-card-bottom"></div>' +
        '</div>' +
        '</div>';    

    var nextButton  =   '<input type="button"  class="mo-tour-button mo-tour-primary-btn" value="'+tourElement.buttonText+'">';
    var skipButton  =   '<input type="button" '+ (tourElement.ifskip? tourElement.ifskip : '') +' class="mo-tour-button mo-skip-btn" value="Skip Tour"> '

    jQuery(card).insertAfter('#overlay');
    if(moTour) jQuery('.mo-tour-button-area').append(skipButton);
    if(tourElement.ifNext) jQuery('.mo-tour-button-area').append(nextButton);

    if(tourElement.pointToSide=='' || tourElement.pointToSide=='center') jQuery('.mo-card').attr('style','box-shadow:0px 0px 20px 7px #979393;');

    jQuery('.mo-target-index').removeClass('mo-target-index');
    jQuery('.mo-target-position').removeClass('mo-target-position');

    if(tourElement.targetE)    getPointerPosition(tourElement.targetE,tourElement.pointToSide);

    jQuery('.mo-tour-primary-btn').click( function(){
        pointerNumber+=1;

        if(moTour.tourData[pointerNumber]){
            jQuery('.mo-card').remove();
            createCard(pointerNumber);
        }
        else {
            resetTour();

        }
    });
    jQuery(".mo-skip-btn").click( function() {
        let size_arr = (moTour.tourData).length;
        jQuery('.mo-card').remove();
        createCard(size_arr-1);
    });
}
function resetTour() {
    pointerNumber = 0;
    nextTabOn = false;
    if(jQuery('.mo-tour-backdrop'))
        jQuery('.mo-tour-backdrop').remove();
    jQuery('.mo-target-index').removeClass('mo-target-index');
}

function addIDs() {
    jQuery(moTour.addID).each( function(){
        if(jQuery(this.selector))
            jQuery(this.selector).attr('id',this.newID);
    });
}

function getPointerPosition(targetE, pointToSide) {
    document.getElementById(targetE).scrollIntoView({
        behavior: 'auto',
        block: 'center',
        inline: 'center'
    });
    var targetDimentions = document.getElementById(targetE).getBoundingClientRect();
    var cardDimentions   = document.getElementById('mo-card').getBoundingClientRect();
    var finalLeft,finalTop;
    switch(pointToSide) {
        case 'up' :
            finalLeft   =   targetDimentions.left + (targetDimentions.width - cardDimentions.width)/2 ;
            finalTop    =   targetDimentions.top + targetDimentions.height +15;
            break;
        case 'down' :
            finalLeft   =   targetDimentions.left + (targetDimentions.width - cardDimentions.width)/2 ;
            finalTop    =   targetDimentions.top - cardDimentions.height ;
            break;
        case 'left' :
            finalLeft   =   targetDimentions.left + targetDimentions.width;
            finalTop    =   targetDimentions.top + (targetDimentions.height - cardDimentions.height)/2 ;
            break;
        case 'right' :
            finalLeft   =   targetDimentions.left - cardDimentions.width;
            finalTop    =   targetDimentions.top + (targetDimentions.height - cardDimentions.height)/2 ;
            break;
    }
    if(targetE == 'mosaml-feedback-form'){
        finalTop-=130;
        finalLeft-=32;
    }
    jQuery('.mo-card').css({'top':finalTop,'left':finalLeft,'margin-top':'0','margin-left':'0'});
    jQuery('#'+targetE).addClass('mo-target-index');
    if(targetE != 'mosaml-feedback-form'){
        jQuery('#'+targetE).addClass('mo-target-position');
    }
}
