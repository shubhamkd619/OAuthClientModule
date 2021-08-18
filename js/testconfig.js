document.getElementById('testConfigButton').onclick = function () {
  var baseurl = document.getElementById('base_Url');
  var url = baseurl.getAttribute("data");
  var finalUrl = url + '/testConfig';
  var myWindow = window.open(finalUrl, "TEST OAuth Client", "scrollbars=1 width=1050, height=900");
}

document.getElementById('showMetaButton').onclick = function () {
  jQuery('#backup_import_form').show();
  jQuery('#clientdata').hide();
  jQuery('#tabhead').hide();
  jQuery('#mo_advertise').hide();
}

document.getElementById('hideMetaButton').onclick = function () {
  jQuery('#backup_import_form').hide();
  jQuery('#clientdata').show();
  jQuery('#tabhead').show();
  jQuery('#mo_advertise').show();
}
