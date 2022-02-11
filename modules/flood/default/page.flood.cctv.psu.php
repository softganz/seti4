<?php
/**
 * Get image from public camera
 *
 * @param String $camera use 1,2,3,... for many camera
 * @return String

 เหล็กใต้ CCB8A8B463CC
 สถานีสูบน้ำ ข7 CCB8A8B4D3CE
 แก้มลิงคลองเรียน B0F1EC11729A
 ปากคลองเตย PT2 CCB8A8B4641C
 วัดหาดใหญ่ใน CCB8A8B47440
 สถานีสูบน้ำคลอง ร.1 B0F1EC117264
 บางศาลา B0F1EC113E7A
 ม่วงก็อง CCB8A8B4E430

 เวลา 00,05,10,20,25,30,35
 */
function flood_cctv_psu($self,$camid = NULL) {
	$camHost = 'https://storage.googleapis.com/sparkcam/pub/';
	$camId = 'CCB8A8B463CC'.'_Camera/camera_';
	$prefix = SG\getFirst(post('d'), date('Ymd_Hi'));

	$camList = array(
		'เหล็กใต้' => 'CCB8A8B463CC',
		//'สถานีสูบน้ำ ข7' => 'CCB8A8B4D3CE',
		//'แก้มลิงคลองเรียน' => 'B0F1EC11729A',
		//'ปากคลองเตย PT2' => 'CCB8A8B4641C',
		//'วัดหาดใหญ่ใน' => 'CCB8A8B47440',
		//'สถานีสูบน้ำคลอง ร.1' => 'B0F1EC117264',
		//'บางศาลา' => 'B0F1EC113E7A',
		//'ม่วงก็อง' => 'CCB8A8B4E430',
	);

	//$photoUrl = 'https://storage.googleapis.com/sparkcam/pub/CCB8A8B47440_Camera/camera_20191013_010508.jpeg';

	// Update Google Firebase
	$firebaseCfg = cfg('firebase');
	$firebase = new Firebase($firebaseCfg['projectId'], $firebaseCfg['flood'].'realtime');

	foreach ($camList as $camId => $camCode) {
		$camName = $camCode.'_Camera/camera_';
		for ($i=0; $i <= 20; $i++) {
			$photoUrl = $camHost.$camName.$prefix.sprintf('%02d',$i).'.jpeg';
			$ret .= $photoUrl.'<br />';
			$result = R::Page('flood.cctv.save', NULL, $camCode, $photoUrl);


			if ($result) {
				$ret .= $result.'<br />';
				break;
			}
		}

		$data = array(
			'camid' => intval($camid),
			'name' => $camCode,
			'photo' => $photoUrl,
			'url' => _DOMAIN.$post->url,
			'thumb' => _DOMAIN.$post->url,
			'date' => sg_date('ว ดด ปป'),
			'time' => sg_date('H:i'),
			'timestamp' => array('.sv' => 'timestamp'),
			'result' => $result,
		);

		$fbresult = $firebase->put('TEST',$data);

		if (!$result) $ret .= $camCode.' ERROR!!!<br />';
	}

	$data = array(
		'camid' => intval($camid),
		'name' => 'TEST',
		'photo' => $post->photo,
		'url' => _DOMAIN.$post->url,
		'thumb' => _DOMAIN.$post->url,
		'date' => sg_date('ว ดด ปป'),
		'time' => sg_date('H:i'),
		'timestamp' => array('.sv' => 'timestamp')
	);

	//$fbresult = $firebase->put('TEST',$data);
	//https://storage.googleapis.com/sparkcam/pub/CCB8A8B463CC_Camera/camera_20191018_234007.jpeg
	//https://storage.googleapis.com/sparkcam/pub/CCB8A8B47440_Camera/camera_20191018_234007.jpeg
	//https://storage.googleapis.com/sparkcam/pub/CCB8A8B463CC_Camera/camera_20191019_005307.jpeg
	//https://storage.googleapis.com/sparkcam/pub/CCB8A8B463CC_Camera/camera_20191019_020320.jpeg
	return $ret;
}
?>