<?php
function flood_camera_config($self,$camid,$action) {
	if ($action=='get') {
		$config = "cam_refresh:600"."\n";
		$config .= "cam_flash:false"."\n";
		$config .= "zoom:0"."\n";
		die($config);
	}
	$config='// Time Start
activity_starttime:00:00
dropbox_datetime:false
// Zoom 0-100
zoom:0
motion_value:25
ftp_dir:
email_host:smtp.gmail.com
// Triggered by Intent Broadcast
cam_broadcast_activation:
ftp_keep_open:false
store_gps:false
imprint_location:false
// White Balance
whitebalance:auto
// email intent broadcast received
email_android_broadcast:false
// recolor ir light pictures
night_ir_light:false
mail_every:1
picture_autorotate:false
// email photo action triggered
email_external_trigger:false
// upload to ftp
ftpserver_upload:false
// Night Start
night_starttime:18:30
// Whitebalance
night_autoswhitebalance:incandescent
ftpserver_url:FTP.YOURDOMAIN.COM
// Scene Mode
scenemode:auto
cam_emailsubject:MobileWebCam
// Picture Size
picture_size_sel:1
store_every:1
ftp_port:21
// Night End
night_endtime:06:00
cam_intents_repeat:1
ftp_keepoldpics:0
dropbox_defaultname:current.jpg
picture_compression:85
// download stamp picture
imprint_picture_url:หนองนายขุ้ย ต.คลองแห
sdcard_dir:/MobileWebCam/
cam_passiveftp:false
// Dropbox Batch Upload
dropbox_batchupload:1
// Title
imprint_text:หนองนายขุ้ย
// send log
log_upload:false
sdcard_keepoldpics:0
// rotate picture to portrait
picture_rotate:false
// FTP reliable upload
ftp_batchalways:false
// upload to dropbox
dropbox_upload:false
motion_change:15
// Custom Picture Size Width
picture_size_custom_w:320
imprint_gps:false
// http website post
server_upload:true
dropbox_filenames:false
dropbox_every:1
// Cam Delay ms
cam_openeddelay:5000
email_sender:
dropbox_dir:
// no dark/night picture upload.
night_detect:false
imprint_datetimeformat:yyyy/MM/dd   HH:mm:ss
cam_email:
mobilewebcam_enabled:true
// Time between events
eventtrigger_pausetime:0
// flashlight enabled
cam_flash:false
// autofocus
picture_autofocus:false
// stretch stamp picture
imprint_picture_stretch:true
email_pausetime:300
cam_front:false
// Time End
activity_endtime:00:00
cam_login:
// Scenemode
night_autoscenemode:night
// Refresh Duration
cam_refresh:60
// flip picture
picture_flip:false
dropbox_keepoldpics:0
cam_filenames:false
// reload stamp picture
imprint_picture_refresh:false
email_ssl:false
// email picture
cam_mailphoto:false
// low battery pause
lowbattery_pause:false
// motion detection enabled
motion_detect:false
cam_url:http://hatyaicityclimate.org/flood/camera/upload/12
// Exposure
night_autoexposure:100
ftp_password:
email_port:465
// Exposure Compensation
exposurecompensation:40
// stamp picture over photo
imprint_picture:false
server_every:1
// Effect
coloreffect:none
email_password:
ftp_login:
cam_datetime:false
ftp_every:1
// Custom Picture Size Height
picture_size_custom_h:240
ftpserver_defaultname:current.jpg
reboot:0
// FTP Batch Upload
ftp_batchupload:1
// auto brightness for dark pictures
night_autobright:false
cam_password:
use_sftp:false
motion_keepalive_refresh:1800
cam_filename_datetime:false
// auto flashlight for dark pictures
night_autoflash:false
// store on SDCard
cam_storepictures:false
imprint_statusinfo:Battery %03d%% %3.1f°C';

$cfg_file = "config.txt";

// submission result?
if (array_key_exists('mobilewebcam_config', $_POST)) {
   // write config.txt
   $config = "cam_refresh:".$_POST['refresh']."\n";
   $set = $_POST['flash'] == "true" ? "true" : "false";
   $config .= "cam_flash:".$set."\n";
   $config .= "zoom:".$_POST['zoom']."\n";
   $ret.=$config;
//   file_put_contents($cfg_file, $config);
} else if(!file_exists($cfg_file)) {
//	file_put_contents($cfg_file, "cam_refresh:60\ncam_flash:false");
}

//$config = file_get_contents($cfg_file);
preg_match_all("/([^\r\n: ]+):([^\r\n: ]+)/", $config, $r);
$settings = array_combine($r[1], $r[2]);

$ret.= "<form name=\"configform\" action=\"config.php5\" method=\"POST\">";
$ret.= "<input type=\"hidden\" name=\"mobilewebcam_config\" value=\"1\"/>";

$ret.= "Refresh Duration: <input type=\"text\" name=\"refresh\" value=\"".$settings["cam_refresh"]."\"/><br>";

$flash = strtolower($settings["cam_flash"]);
$ret.= "Flashlight: <input type=\"checkbox\" name=\"flash\" value=\"true\" ".($flash == "true" ? "checked" : "")."/> Enabled<br>";

$zoom = $settings["zoom"];
$ret.= "Zoom: <select name=\"zoom\">";
for($i = 0; $i <= 100; $i += 10)
	$ret.= "<option value=\"".$i."\" ".($zoom == $i ? "selected" : "").">".$i."</option>";
$ret.= "</select><br>";

$ret.= "<br>";
$ret.= "<input type=\"submit\"/>";
$ret.= "</form>";
		return $ret;
		echo $ret;
//		die;
	}
?>