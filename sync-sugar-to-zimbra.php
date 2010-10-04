#!/usr/bin/env php
<?php

// AUTHORS:
// eleventeenth.com
// Dieter Plaetinck, Kangaroot
// Anonymous ( http://pastebin.ca/691313 )
// http://www.sugarcrm.com/forums/printthread.php?t=24807&page=2&pp=10
// http://www.sugarcrm.com/wiki/index.php?title=SOAP_Intro_and_Practical_Examples
// https://www.sugarcrm.com/forums/showthread.php?p=88449


if(!defined('sugarEntry')) define('sugarEntry', true);
require_once('zimbra-nusoap.php');
require_once('config.php');


function die_error ($msg, $exit = 2) {
	if(is_array($msg)) {
		$msg = print_r($msg,true);
	}
	echo ("ERROR:\n$msg\n");
	exit ($exit);
}

echo("=> Connecting to SugarCRM\n");
$soapClient = new nusoapclient($sugarURL.'/soap.php?wsdl', true, $proxyHost, $proxyPort);

$err = $soapClient->getError();
if ($err) {
	die_error($err);
}

echo("=> Logging in to SugarCRM\n");

$auth_array = array(
	'user_auth' => array(
		'user_name' => $sugarUser,
		'password' => md5($sugarPwd),
	)
);

$login_results = $soapClient->call('login',$auth_array);
$err = $soapClient->getError();
if ($err) {
	die_error($err);
}

$session =  $login_results['id'];

try {

  echo("=> Pulling contacts from SugarCRM\n");

  $result = $soapClient->call('get_entry_list',array('session'=>$session, 'module_name'=>'Contacts', 'query'=>'', 'order_by'=>'contacts.first_name asc', 'offset'=>0, 'select_fields'=>array(), 'max_results'=>100000));
	$err = $soapClient->getError();
	if ($err) {
		die_error($err);
	}
  $contacts = $result[entry_list];

  $fp = fopen( "/tmp/SugarCRMContacts.csv" , "w" );

  echo("=> Parsing SugarCRM data into CSV for Zimbra\n");

  fwrite($fp, '"assistantPhone","birthday","callbackPhone","carPhone","company","companyPhone","description","department","dlist","email","email2","email3","fileAs","firstName","fullName","homeCity","homeCountry","homeFax","homePhone","homePhone2","homePostalCode","homeState","homeStreet","homeURL","initials","jobTitle","lastName","middleName","mobilePhone","namePrefix","nameSuffix","nickname","notes","office","otherCity","otherCountry","otherFax","otherPhone","otherPostalCode","otherState","otherStreet","otherURL","pager","workCity","workCountry","workFax","workPhone","workPhone2","workPostalCode","workState","workStreet","workURL","type"');
  fwrite($fp, "\n");

  for ($c=0; $c<sizeOf($contacts); $c++) {
    $contact = $contacts[$c];
    $elements = $contact[name_value_list];

    $account_id = '';
    $account_name = '';
    $alt_address_city = '';
    $alt_address_country = '';
    $alt_address_postalcode = '';
    $alt_address_state = '';
    $alt_address_street = '';
    $assigned_user_id = '';
    $assigned_user_name = '';
    $assistant = '';
    $assistant_phone = '';
    $birthdate = '';
    $c_accept_status_fields = '';
    $campaign_id = '';
    $created_by = '';
    $date_entered = '';
    $date_modified = '';
    $deleted = '';
    $department = '';
    $description = '';
    $do_not_call = '';
    $email_opt_out = '';
    $email1 = '';
    $email2 = '';
    $first_name = '';
    $id = '';
    $invalid_email = '';
    $last_name = '';
    $lead_source = '';
    $m_accept_status_fields = '';
    $modified_user_id = '';
    $opportunity_role_fields = '';
    $phone_fax = '';
    $phone_home = '';
    $phone_mobile = '';
    $phone_other = '';
    $phone_work = '';
    $portal_active = '';
    $portal_app = '';
    $portal_name = '';
    $portal_password = '';
    $primary_address_city = '';
    $primary_address_country = '';
    $primary_address_postalcode = '';
    $primary_address_state = '';
    $primary_address_street = '';
    $report_to_name = '';
    $reports_to_id = '';
    $salutation = '';
    $team_id = '';
    $team_name = '';
    $title = '';

    for ($e=0; $e<sizeOf($elements); $e++) {
      $element = $elements[$e];
      if($element[name] == 'account_id') { $account_id = my_replace($element[value]); }
      if($element[name] == 'account_name') { $account_name = my_replace($element[value]); }
      if($element[name] == 'alt_address_city') { $alt_address_city = my_replace($element[value]); }
      if($element[name] == 'alt_address_country') { $alt_address_country = my_replace($element[value]); }
      if($element[name] == 'alt_address_postalcode') { $alt_address_postalcode = my_replace($element[value]); }
      if($element[name] == 'alt_address_state') { $alt_address_state = my_replace($element[value]); }
      if($element[name] == 'alt_address_street') { $alt_address_street = my_replace($element[value]); }
      if($element[name] == 'assigned_user_id') { $assigned_user_id = my_replace($element[value]); }
      if($element[name] == 'assigned_user_name') { $assigned_user_name = my_replace($element[value]); }
      if($element[name] == 'assistant') { $assistant = my_replace($element[value]); }
      if($element[name] == 'assistant_phone') { $assistant_phone = my_replace($element[value]); }
      if($element[name] == 'birthdate') { $birthdate = my_replace($element[value]); }
      if($element[name] == 'c_accept_status_fields') { $c_accept_status_fields = my_replace($element[value]); }
      if($element[name] == 'campaign_id') { $campaign_id = my_replace($element[value]); }
      if($element[name] == 'created_by') { $created_by = my_replace($element[value]); }
      if($element[name] == 'date_entered') { $date_entered = my_replace($element[value]); }
      if($element[name] == 'date_modified') { $date_modified = my_replace($element[value]); }
      if($element[name] == 'deleted') { $deleted = my_replace($element[value]); }
      if($element[name] == 'department') { $department = my_replace($element[value]); }
      if($element[name] == 'description') { $description = my_replace($element[value]); }
      if($element[name] == 'do_not_call') { $do_not_call = my_replace($element[value]); }
      if($element[name] == 'email_opt_out') { $email_opt_out = my_replace($element[value]); }
      if($element[name] == 'email1') { $email1 = my_replace($element[value]); }
      if($element[name] == 'email2') { $email2 = my_replace($element[value]); }
      if($element[name] == 'first_name') { $first_name = my_replace($element[value]); }
      if($element[name] == 'id') { $id = my_replace($element[value]); }
      if($element[name] == 'invalid_email') { $invalid_email = my_replace($element[value]); }
      if($element[name] == 'last_name') { $last_name = my_replace($element[value]); }
      if($element[name] == 'lead_source') { $lead_source = my_replace($element[value]); }
      if($element[name] == 'm_accept_status_fields') { $m_accept_status_fields = my_replace($element[value]); }
      if($element[name] == 'modified_user_id') { $modified_user_id = my_replace($element[value]); }
      if($element[name] == 'opportunity_role_fields') { $opportunity_role_fields = my_replace($element[value]); }
      if($element[name] == 'phone_fax') { $phone_fax = my_replace($element[value]); }
      if($element[name] == 'phone_home') { $phone_home = my_replace($element[value]); }
      if($element[name] == 'phone_mobile') { $phone_mobile = my_replace($element[value]); }
      if($element[name] == 'phone_other') { $phone_other = my_replace($element[value]); }
      if($element[name] == 'phone_work') { $phone_work = my_replace($element[value]); }
      if($element[name] == 'portal_active') { $portal_active = my_replace($element[value]); }
      if($element[name] == 'portal_app') { $portal_app = my_replace($element[value]); }
      if($element[name] == 'portal_name') { $portal_name = my_replace($element[value]); }
      if($element[name] == 'portal_password') { $portal_password = my_replace($element[value]); }
      if($element[name] == 'primary_address_city') { $primary_address_city = my_replace($element[value]); }
      if($element[name] == 'primary_address_country') { $primary_address_country = my_replace($element[value]); }
      if($element[name] == 'primary_address_postalcode') { $primary_address_postalcode = my_replace($element[value]); }
      if($element[name] == 'primary_address_state') { $primary_address_state = my_replace($element[value]); }
      if($element[name] == 'primary_address_street') { $primary_address_street = my_replace($element[value]); }
      if($element[name] == 'report_to_name') { $report_to_name = my_replace($element[value]); }
      if($element[name] == 'reports_to_id') { $reports_to_id = my_replace($element[value]); }
      if($element[name] == 'salutation') { $salutation = my_replace($element[value]); }
      if($element[name] == 'team_id') { $team_id = my_replace($element[value]); }
      if($element[name] == 'team_name') { $team_name = my_replace($element[value]); }
      if($element[name] == 'title') { $title = my_replace($element[value]); }
    }
    fwrite($fp, "\"$assistant_phone\",\"$birthdate\",\"\",\"\",\"$account_name\",\"$phone_work\",\"$description\",\"$department\",\"\",\"$email1\",\"$email2\",\"\",\"7\",\"$first_name\",\"$first_name $last_name\",\"\",\"\",\"\",\"$phone_home\",\"\",\"\",\"\",\"\",\"\",\"\",\"$title\",\"$last_name\",\"\",\"$phone_mobile\",\"$salutation\",\"\",\"\",\"$description\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"$primary_address_city\",\"$primary_address_country\",\"$phone_fax\",\"$phone_work\",\"$phone_other\",\"$primary_address_postalcode\",\"$primary_address_state\",\"$primary_address_street\",\"$sugarURL/index.php?action=DetailView&module=Contacts&record=$id\",\"\"");
    fwrite($fp, "\n");
  }
  fclose($fp);

  if ($DEBUG === true) {
  		echo("===== DEBUG MODE IS ON - not changing data, just printing out the commands =====\n");
  		// clear out existing contacts
  		echo('=> Clear Zimbra:    /opt/zimbra/bin/zmprov sm '.$zimbra_account.' emptyFolder \'/'.$zimbra_folder.'\''."\n");
  		// add in the contacts harvested from sugar
  		echo('=> Add Sugar Data:  /usr/bin/curl -k -u '.$zimbra_username.':'.$zimbra_password.' -T @/tmp/SugarCRMContacts.csv "'.$zimbra_url.'/zimbra/home/'.$zimbra_account.'/'.urlencode($zimbra_folder).'?fmt=csv"'."\n");
  } {
        echo("=> Clearing Zimbra Data\n");
  		system('/opt/zimbra/bin/zmprov sm '.$zimbra_account.' emptyFolder \'/'.$zimbra_folder.'\'');
        echo("=> Adding parsed data from SugarCRM into Zimbra\n");
		// curl is pretty lame. it might exit(0) even though the server gave http 400, luckily we can parse the output
		exec  ('/usr/bin/curl -k -u '.$zimbra_username.':'.$zimbra_password.' -T /tmp/SugarCRMContacts.csv "'.$zimbra_url.'/zimbra/home/'.$zimbra_account.'/'.urlencode($zimbra_folder).'?fmt=csv"', $output);
		$output = implode ($output,"\n");
		if (stripos($output,'error') !== false) {
			die_error($output);
		}
}
echo("=> Done!\n");

}
catch (SoapFault $soapFault) {
  die_error("Second connection" . var_export ($soapFault, true));
}

function my_replace($data) {
  $data = str_replace("&amp;","&",$data);
  $data = str_replace("&#039;","'",$data);
  return $data;
}

unset($soapClient);
?>
