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

if (!defined('STDERR')) {
   define('STDERR', fopen('php://stderr', 'w'));
}

function die_error ($msg, $exit = 2) {
	if(is_array($msg)) {
		$msg = print_r($msg,true);
	}
	fwrite (STDERR, "ERROR:\n$msg\n");
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

  if(!$fp = fopen( "/tmp/SugarCRMContacts.csv" , "w" )) {
	  die_error ("Could not open /tmp/SugarCRMContacts.csv for writing");
}

  echo("=> Parsing SugarCRM data into CSV for Zimbra\n");

	$headers = array (
		'assistantPhone',
		'birthday',
		'callbackPhone',
		'carPhone',
		'company',
		'companyPhone',
		'description',
		'department',
		'dlist',
		'email',
		'email2',
		'email3',
		'fileAs',
		'firstName',
		'fullName',
		'homeCity',
		'homeCountry',
		'homeFax',
		'homePhone',
		'homePhone2',
		'homePostalCode',
		'homeState',
		'homeStreet',
		'homeURL',
		'initials',
		'jobTitle',
		'lastName',
		'middleName',
		'mobilePhone',
		'namePrefix',
		'nameSuffix',
		'nickname',
		'notes',
		'office',
		'otherCity',
		'otherCountry',
		'otherFax',
		'otherPhone',
		'otherPostalCode',
		'otherState',
		'otherStreet',
		'otherURL',
		'pager',
		'workCity',
		'workCountry',
		'workFax',
		'workPhone',
		'workPhone2',
		'workPostalCode',
		'workState',
		'workStreet',
		'workURL',
		'type'
	);
	$fields = array (
		'account_id',
		'account_name',
		'alt_address_city',
		'alt_address_country',
		'alt_address_postalcode',
		'alt_address_state',
		'alt_address_street',
		'assigned_user_id',
		'assigned_user_name',
		'assistant',
		'assistant_phone',
		'birthdate',
		'c_accept_status_fields',
		'campaign_id',
		'created_by',
		'date_entered',
		'date_modified',
		'deleted',
		'department',
		'description',
		'do_not_call',
		'email_opt_out',
		'email1',
		'email2',
		'first_name',
		'id',
		'invalid_email',
		'last_name',
		'lead_source',
		'm_accept_status_fields',
		'modified_user_id',
		'opportunity_role_fields',
		'phone_fax',
		'phone_home',
		'phone_mobile',
		'phone_other',
		'phone_work',
		'portal_active',
		'portal_app',
		'portal_name',
		'portal_password',
		'primary_address_city',
		'primary_address_country',
		'primary_address_postalcode',
		'primary_address_state',
		'primary_address_street',
		'report_to_name',
		'reports_to_id',
		'salutation',
		'team_id',
		'team_name',
		'title');
	$headerstring = '';
	foreach ($headers as $header) {
		if ($headerstring){
			$headerstring .= ',';
		}
		$headerstring .= "\"$header\"";
	}
	fwrite($fp, "$headerstring\n");

  for ($c=0; $c<sizeOf($contacts); $c++) {
    $contact = $contacts[$c];
    $elements = $contact[name_value_list];
	$data = array ();
	foreach ($fields as $field) {
		$data[$field] = '';
	}
    for ($e=0; $e<sizeOf($elements); $e++) {
      $element = $elements[$e];
	foreach ($fields as $field) {
		if($element[name] == $field) {
			$data[$field] = my_replace($element[value]);
		}
	}
	extract ($data);
    }
    fwrite($fp, utf8_encode("\"$assistant_phone\",\"$birthdate\",\"\",\"\",\"$account_name\",\"$phone_work\",\"$description\",\"$department\",\"\",\"$email1\",\"$email2\",\"\",\"7\",\"$first_name\",\"$first_name $last_name\",\"\",\"\",\"\",\"$phone_home\",\"\",\"\",\"\",\"\",\"\",\"\",\"$title\",\"$last_name\",\"\",\"$phone_mobile\",\"$salutation\",\"\",\"\",\"$description\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"$primary_address_city\",\"$primary_address_country\",\"$phone_fax\",\"$phone_work\",\"$phone_other\",\"$primary_address_postalcode\",\"$primary_address_state\",\"$primary_address_street\",\"$sugarURL/index.php?action=DetailView&module=Contacts&record=$id\",\"\""));
    fwrite($fp, "\n");
  }
  fclose($fp);

	$msg_clear = "Clear contacts  folder in Zimbra";
	$msg_fill  = "Add Contacts to folder in Zimbra";
	$cmd_clear = "/opt/zimbra/bin/zmprov sm $zimbra_account emptyFolder '/$zimbra_folder'";
	$cmd_fill  = "/usr/bin/curl -Ss -k -u '$zimbra_username:$zimbra_password' -T /tmp/SugarCRMContacts.csv '$zimbra_url/zimbra/home/" . urlencode ("$zimbra_account") . '/' . urlencode("$zimbra_folder")."?fmt=csv'";
	if ($DEBUG) {
		echo("===== DEBUG MODE IS ON - not changing data, just printing out the commands =====\n");
		echo("=> $msg_clear: $cmd_clear\n");
		echo("=> $msg_fill: $cmd_fill\n");
	} else {
		echo("=> $msg_clear\n");
		exec($cmd_clear, $output, $ret);
		if ($ret) {
			die_error ("Command failed: $cmd_clear\n" . print_r($output,true). "\nexitcode $ret");
		}
		echo("=> $msg_fill\n");
		// curl is pretty lame. it might exit(0) even though the server gave http 400, luckily we can parse the output
		exec  ($cmd_fill, $output);
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
