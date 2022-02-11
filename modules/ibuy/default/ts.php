<?php
$smf='/home/rap3g/domains/rap3g.com/public_html/forum/SSI.php';
//echo $smf.' ';
//echo file_exists($smf)?'File exists':'File not exists';
require_once($smf);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-EN">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="th" />
<title>RIFF Box - JTAC Revolution | RAP3G Shop</title>
<meta name="generator" content="www.softganz.com" />
<meta name="formatter" content="Little Bear by SoftGanz Group" />
<meta name="author" content="RAP3G Shop" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">

</head>

<body id="paper">
<?php
//echo __FILE__.'<br />';
print_r($_COOKIE);
echo '<hr />';
//ssi_welcome();
$_SESSION['login_url']='http://rap3g.com/nshop/seti3/modules/ibuy/default/func.ibuy.admin.smf.php';
//echo 'Load complete<br />';
if ($context['user']['is_guest'])
{
	//ssi_login();
	echo '<h1>Login form</h1>';
}
else
{
	echo 'Logout form<br />';
	//You can show other stuff here.  Like ssi_welcome().  That will show a welcome message like.
	//Hey, username, you have 552 messages, 0 are new.
	//ssi_logout();
}
print_r($context['user']);
echo '<hr />';
function ibuy_admin_smf($self) {
	$ret='<h3>SMF User</h3>';

if ($context['user']['is_guest'])
{
	ssi_login();
}
else
{
	//You can show other stuff here.  Like ssi_welcome().  That will show a welcome message like.
	//Hey, username, you have 552 messages, 0 are new.
	ssi_logout();
}
	return $ret;
}
?>