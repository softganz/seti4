<?php
$debug=false;

function view_error($code, $ext_msg = NULL) {
	// static $message=array();

	$message['class_not_exists']='Request class not exists.';
	$message['method_not_exists']='Request method not exists.';

	$message['invalid_signin']='Invalid username or password.';
	$message['Invalid parameter']='Invalid parameter for command request.';
	$message['Duplicate poster name']='มีสมาชิกท่านอื่นใช้ชื่อนี้อยู่แล้ว ท่านไม่สามารถใช้ชื่อที่ซ้ำกับผู้อื่นได้ หากชื่อนี้เป็นชื่อของท่าน กรุณา Sign in ก่อน จึงจะสามารถใช้ชื่อนี้ได้';
	$message['invalid_time_format']='Invalid date-time format. Plese use hr.mn.ss or hr:mn:ss<br />hr is hour , mn is minute , ss is second and numeric only';

	$message['Data not found']='Request data was not found on database.';
	$message['dup_record']='Duplicate data record.';
	$message['record_in_used']='Data record was in used by other table';

	$message['Invalid file format']='Your upload file is invalid format type.';
	$message['Invalid file size']='Your upload file is invalid size.';
	$message['Duplicate upload filename']='Your upload file was existsing in website upload folder.';
	$message['Saving upload file error']='There was an error while saving upload file.';
	$message['Upload file request']='';

	$message['Access denied']='You are not authorized to access this page.';
	$message['No blog']='You have no blog name. Do you want to create new blog? <a href="'.url('dashboard/blogs/create').'">Create new blog</a>';

	$message['Input field missing']='Some input field was missing.';
	$message['Invalid Anti-spam word']=sg_client_convert('Anti-spam word เป็นอักษรที่ป้อนเพื่อป้องกันข้อมูลขยะ ที่ไม่ได้เกิดจากผู้ใช้งานทั่วไป ท่านต้องป้อนตัวอักษรหรือตัวเลขที่แสดงไว้ ในช่องกรอกข้อมูลของ Anti-spam word ให้ถูกต้อง');

	$message['Page signout not found']='ไม่พบหน้าเว็บ signout ในระบบ.';

	if (array_key_exists($code,$message)) return $message[$code];

	return $ret;
}
?>