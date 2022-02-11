<?php
/**
* Search from farm name
*
* @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
* @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
* @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
* @return json[{value:org_id, label:org_name},...]
*/

$debug = true;

function r_map_who_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) {
		$rs = $conditions;
	} else if (is_array($conditions)) $rs = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];

		if (empty($conditions->id)) return '[]';
		$rs = R::Model('map.get',$conditions->id);

	}

	$icons['prepare']='/library/img/geo/pin-prepare.png';
	$icons['during']='/library/img/geo/pin-during.png';
	$icons['after']='/library/img/geo/pin-after.png';
	$icons['place']='/library/img/geo/pin-place.png';
	$icons['city']='/library/img/geo/pin-city.png';
	$icons['cctv']='/library/img/geo/pin-camera.png';
	$icons['none']='/library/img/geo/pin-none.png';
	$icons['บ้าน']='/library/img/geo/pin-home.png';
	$icons['network']='/library/img/geo/pin-man.png';
	$icons['staffgauge']='/library/img/geo/pin-staffgauge.png';
	$icons['flag']='/library/img/geo/pin-flag.png';
	$icons['green']='/library/img/geo/pin-greenfood.png';
	$icons['forrest']='/library/img/geo/pin-forrest.png';
	$icons['rice']='/library/img/geo/pin-greenrice.png';

	if (preg_match('/CCTV/i',$rs->dowhat)) $icon=$icons['cctv'];
	else if (preg_match('/สวนผัก/',$rs->dowhat)) $icon=$icons['green'];
	else if (preg_match('/ข้าว/',$rs->dowhat)) $icon=$icons['rice'];
	else if (preg_match('/บุกรุกป่า/',$rs->dowhat)) $icon=$icons['forrest'];
	else if (preg_match('/ธงเตือนภัย/',$rs->dowhat)) $icon=$icons['flag'];
	else if (preg_match('/Staff gauge/i',$rs->dowhat)) $icon=$icons['staffgauge'];
	else if (preg_match('/เครือข่ายเตือนภัย/',$rs->dowhat)) $icon=$icons['network'];
	else if (preg_match('/หมู่บ้าน/',$rs->dowhat)) $icon=$icons['city'];
	else if (preg_match('/บ้าน/',$rs->dowhat)) {
	//			if (!user_access('access full maps')) return array();
		$icon=$icons['บ้าน'];
	} else if (preg_match('/สถานที่/',$rs->dowhat)) $icon=$icons['place'];
	else if ($rs->prepare) $icon=$icons['prepare'];
	else if ($rs->during) $icon=$icons['during'];
	else if ($rs->after) $icon=$icons['after'];
	else $icon=$icons['none'];
	unset($where);

	if ($rs->prepare) $where[]='ก่อนเกิดเหตุ';
	if ($rs->during) $where[]='ระหว่างเกิดเหตุ';
	if ($rs->after) $where[]='หลังเกิดเหตุ';

	$isEdit = user_access('administer maps','edit own maps content',$rs->uid)
					|| in_array($rs->membership, array('OFFICER','ADMIN','MANAGER'));

	$html='<div class="map-info map__info"><h4>'.$rs->who.'</h4>';


	$ui=new Ui();
	if (empty($rs->status) || ($rs->status=='lock' && $isEdit))
		$ui->add('<a class="sg-action" data-rel="#map-box" href="'.url('map/add','id='.$rs->mapid).'"><i class="icon -edit"></i></a>');
	if ($isEdit)
		$ui->add('<a class="sg-action" href="'.url('map/delete','id='.$rs->mapid).'" data-confirm="ต้องการลบหมุด กรุณายืนยัน?" data-rel="this" data-removeparent="div"><i class="icon -delete"></i></a>');
	if ($isEdit)
		$ui->add('<a href="'.url('map/lock/'.$rs->mapid).'" data-action="lock"><i class="icon -'.($rs->status=='lock'?'lock':'unlock').'"></i></a>');
	$ui->add('<a class="sg-action" data-rel="#map-box" href="'.url('map/history','id='.$rs->mapid).'" title="ประวัติการแก้ไขข้อมูล"><i class="icon -list"></i></a>');


	$html.='<div class="nav -page">'.$ui->build().'</div>';
	$html.='<p>ทำอะไร : '.$rs->dowhat.($where?'<br />เมื่อไหร่ : '.implode(',',$where):'').'<br />ที่ไหน : '.$rs->address.'</p><div class="map__info--detail">'.sg_text2html($rs->detail).'</div>';

	$html.='<div class="photo">'._NL;
	$html.='<ul class="photo">'._NL;
	if (debug('method')) $html.=$rs->photos.print_o($rs,'$rs');
	if ($rs->gallery) {
		$doc='';
		$photos=mydb::select('SELECT f.fid, f.type, f.file, f.title FROM %topic_files% f WHERE f.gallery=:gallery',':gallery',$rs->gallery);
		$ui=new ui();
		foreach ($photos->items as $item) {
			list($photoid,$photo)=explode('|',$item);
			if ($item->type=='photo') {
				$photo=model::get_photo_property($item->file);
				$photo_alt=$item->title;
				$html .= '<li>';
				$html.='<a group="photo'.$rs->mapid.'" href="'.$photo->_src.'" title="'.htmlspecialchars($photo_alt).'">';
				$html.='<img class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" height="60" alt="photo '.$photo_alt.'" ';
				$html.=' />';
				$html.='</a>';
				$ui->clear();
				if ($isEdit) $html.='<a class="sg-action" href="'.url('map/delphoto/'.$item->fid).'" data-rel="this" data-removeparent="li" data-mapid="'.$rs->mapid.'" data-confirm="ต้องการลบภาพนี้ใช่หรือไม่? กรุณายืนยัน"><i class="icon -cancel"></i></a>';
				$html .= '</li>'._NL;
			} else if ($item->type=='doc') {
				$doc.='<li>';
				$doc.='<a href="'.cfg('paper.upload.document.url').$item->file.'" title="'.htmlspecialchars($photo_alt).'">';
				$doc.=$item->title;
				$doc.='</a>';
				$ui->clear();
				if ($isEdit) {
					$ui->add('[<a href="'.url('project/edit/delphoto/'.$item->fid).'" action="delphoto" title="ลบไฟล์นี้"><i class="icon -cancel"></i></a>]');
				}
				$doc.=$ui->build();
				$doc.='</li>';
			}
		}
	}

	$html.='</ul>'._NL;
	if ($doc) $html.='<h3>ไฟล์ประกอบ</h3><ul class="doc">'.$doc.'</ul>';

	if (empty($rs->status) || ($rs->status=='lock' && $isEdit)) {
		$html.='<br clear="all" /><form method="post" enctype="multipart/form-data" action="'.url('map/sendphoto/'.$rs->mapid).'"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span class="-hidden">ส่งภาพ</span><input type="file" name="photo" class="inline-upload" data-mapid="'.$rs->mapid.'" /></span></form>';
			//$html.='<div class="progress"><div class="bar"></div ><div class="percent">0%</div ></div>';
	}
	$html.='</div><!--photo-->'._NL;
	$html.='<p class="poster">โดย '.SG\getFirst($rs->name,'ไม่ระบุชื่อ').' เมื่อ '.sg_date($rs->created,'ว ดด ปปปป H:i').' น.';
	//$html.=print_o($rs,'$rs');
	$html.='</div>';

	$result=array('mapid'=>$rs->mapid,
												'lat'=>$rs->lat,
												'lng'=>$rs->lnt,
												'title'=>$rs->who,
												'icon'=>$icon,
												'dowhat'=>$rs->dowhat,
												'content'=>$html,
												);
	return $result;
}
?>