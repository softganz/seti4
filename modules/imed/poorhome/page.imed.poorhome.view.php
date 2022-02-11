<?php
/**
* Poor System
*
* @param Object $self
* @return String
*/

function imed_poorhome_view($self,$poorid) {
	$self->theme->title='ชื่อผู้นำครอบครัว '.$poorid;
	$self->theme->toolbar=R::Page('imed.poorhome.toolbar',$self);

	$action=post('act');
	$tranId=post('id');

	$rs=__imed_poor_get($poorid);
	if ($rs->_empty) return message('error','ไม่มีข้อมูล');

	$isAdmin=i()->admin;
	$isAccess=$isAdmin || user_access('access imed poorhomes');
	$isEdit=false;
	if ($isAdmin || $rs->uid==i()->uid) {
		$isEdit=true;
	}

	switch ($action) {
		case 'addmember':
			if ($isEdit) $ret.=__imed_poor_view_addmember($poorid);
			return $ret;
			break;
		
		case 'removemember' :
			if ($isEdit && $poorid && $tranId && SG\confirm()) {
				$stmt='DELETE FROM %poormember% WHERE `poorid`=:poorid AND `psnid`=:psnid LIMIT 1';
				mydb::query($stmt,':poorid',$poorid ,':psnid',$tranId);
				//$ret.=mydb()->_query;
			}
			$ret.=$poorid.' | '.$tranId;
			return $ret;
			break;

		case 'photo' :
			if ($isEdit AND $_FILES['photo']) $ret.=__imed_poor_savephoto($poorid);
			$ret.=__imed_poor_photo($poorid,$isEdit);
			return $ret;
			break;

		case 'removephoto' :
			if ($isEdit && $poorid && $tranId && SG\confirm()) {
				$ret.=__imed_poor_delphoto($poorid,$tranId);
			}
			return $ret;
			break;

		default:
			# code...
			break;
	}




	if ($isEdit) {
		$inlineAttr['class']='sg-inline-edit';
		$inlineAttr['data-update-url']=url('imed/poorhome/edit/'.$poorid);
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}

	include_once('qt.poor.php');

	$ret.='<div id="imed-poor" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret.='<h4>ข้อมูลที่อยู่</h4>';
	if ($isEdit || $isAccess) $ret.='<p><label>บ้านเลขที่<sup class="tooltip" tooltip-uri="'.url('imed/help/input_address').'">?</sup></label>'
					.view::inlineedit(
						array(
							'group'=>'poor',
							'fld'=>'address',
							'class'=>'w-8',
							'areacode' => $rs->changwat.$rs->ampur.$rs->tambon.sprintf('%02d',$rs->village),
							'options' => '{autocomplete: {minlength: 5, target:"areacode", query: "'.url('api/address').'"}}',
						),
						$rs->address,
						$isEdit,
						'autocomplete'
					)
				.'</p>';

	$ret .= '<p><label>ชื่อชุมชน</label>'
		.view::inlineedit(
			array(
				'group'=>'poor',
				'fld'=>'commune',
				'options' => '{class: "", autocomplete: {query: "'.url('api/commune').'", minlength: 2}}',
			),
			$rs->commune,
			$isEdit,
			'autocomplete'
		)
		.'</p>';
	$ret.='<p><label>เทศบาล</label>'.view::inlineedit(array('group'=>'poor','fld'=>'municipality'),$rs->municipality,$isEdit,'select',array('เทศบาลนครสงขลา'=>'เทศบาลนครสงขลา','เทศบาลนครหาดใหญ่'=>'เทศบาลนครหาดใหญ่')).'</p>';

	if ($isEdit || $isAccess) {
		$ret.='<h4>1. ข้อมูลสมาชิกในครัวเรือนที่อาศัยอยู่จริงในปีปัจจุบัน</h4>';

		$stmt='SELECT pm.*, p.`uid`, p.`prename`, p.`name`, p.`lname`, p.`birth`, p.`cid`, p.`educate`, p.`occupa`, p.`religion`
						, e.`edu_desc`
						, o.`occu_desc`
						, r.`reli_desc`
					FROM %poormember% pm
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %co_educate% e ON e.`edu_code`=p.`educate`
						LEFT JOIN %co_occu% o ON o.`occu_code`=p.`occupa`
						LEFT JOIN %co_religion% r ON r.`reli_code`=p.`religion`
					WHERE `poorid`=:poorid';
		$dbs=mydb::select($stmt,':poorid',$poorid);

		$tables = new Table();
		$tables->thead = [
			'no'=>'ที่',
			'ชื่อ-สกุล',
			'date'=>'วันเกิด',
			'amt age'=>'อายุ(ปี)',
			'center 1'=>'เลขบัตรประชาชน',
			'การศึกษา',
			'อาชีพ',
			'ศาสนา',
			'center 2'=>'ความเกี่ยวข้องกับหัวหน้าครัวเรือน',
			'icons -hover-parent' => $isEdit ? '<a class="sg-action" href="'.url('imed/poorhome/view/'.$poorid,array('act'=>'addmember')).'" data-rel="box" title="เพิ่มสมาชิกในครัวเรือนที่อาศัยอยู่จริงในปีปัจจุบัน"><i class="icon -material">add</i></a>':'',
		];
		if ($dbs->_empty) $tables->rows[]=array('<td colspan="9">** ยังไม่มีข้อมูลสมาชิกในครัวเรือน **'.($isEdit?' เพิ่มสมาชิกในครัวเรือนโดยคลิกบน <a class="sg-action" href="'.url('imed/poorhome/view/'.$poorid,array('act'=>'addmember')).'" data-rel="box" title="เพิ่มสมาชิกในครัวเรือนที่อาศัยอยู่จริงในปีปัจจุบัน"><i class="icon -material">add</i>เพิ่มสมาชิกในครัวเรือน</a>':'').'</td>');
		foreach ($dbs->items as $item) {
			$tables->rows[]=array(
				++$no,
				imed_model::qt('name',array_replace_recursive($qt,array('name'=>array('tr'=>$item->psnid))),$item->name.' '.$item->lname,$isAdmin || ($isEdit && $item->uid==i()->uid)),
				view::inlineedit(array('group'=>'person','fld'=>'birth','tr'=>$item->psnid,'ret'=>'date:ว ดด ปปปป','value'=>$item->birth),$item->birth?sg_date($item->birth,'ว ดดด ปปปป'):null,$isEdit,'datepicker'),
				$item->birth?(date('Y')-sg_date($item->birth,'Y')):'',
				imed_model::qt('cid',array_replace_recursive($qt,array('cid'=>array('tr'=>$item->psnid,'options'=>'{maxlength:13}'))),$item,$isEdit),

				imed_model::qt('educate',array_replace_recursive($qt,array('educate'=>array('tr'=>$item->psnid,'value'=>$item->educate))),$item->edu_desc,$isEdit),

				imed_model::qt('occupa',array_replace_recursive($qt,array('occupa'=>array('tr'=>$item->psnid))),$item->occu_desc,$isEdit),
				imed_model::qt('religion',array_replace_recursive($qt,array('religion'=>array('tr'=>$item->psnid))),$item->reli_desc,$isEdit),
				imed_model::qt('reltohouseholder',array_replace_recursive($qt,array('reltohouseholder'=>array('tr'=>$item->psnid))),$item->reltohouseholder,$isEdit),
				$isEdit?'<nav class="nav -hover"><a class="sg-action hover--menu" href="'.url('imed/poorhome/view/'.$poorid,array('act'=>'removemember','id'=>$item->psnid)).'" data-confirm="ต้องการลบชื่อสมาชิกนี้ออกจากครัวเรือน กรุณายืนยัน?" data-rel="this" data-removeparent="tr"><i class="icon -material">cancel</i></a>':''
			);
			//$tables->rows[]=array('<td colspan="10">'.print_o($qt,'$qt').print_o($item,'$item').'</td>');
		}

		$ret.=$tables->build();
	}

	//$ret.=print_o(array_replace_recursive($qt,array('cid'=>array('tr'=>$item->psnid))),'$qt');
	$ret.='<h4>2. สภาพที่อยู่อาศัย</h4>';
	$ret.='<label>สภาพที่อยู่อาศัย :</label>'.imed_model::qt('สภาพที่อยู่อาศัย',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>สถานะที่อยู่อาศัย :</label>'.imed_model::qt('สถานะที่อยู่อาศัย',$qt,$rs,$isEdit)
				.' ระบุ '.imed_model::qt('สถานะที่อยู่อาศัย-อื่นๆ',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>สภาพปัญหา</label>'.imed_model::qt('สภาพที่อยู่อาศัย-สภาพปัญหา',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>ความต้องการ</label>'.imed_model::qt('สภาพที่อยู่อาศัย-ความต้องการ',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>ต้องการเข้าร่วมโครงการบ้านมั่นคง :</label>'.imed_model::qt('ต้องการเข้าร่วมโครงการบ้านมั่นคง',$qt,$rs,$isEdit).'<br />'._NL;


	$ret.='<h4>3. เครื่องนุ่งห่ม/ของใช้ในครัวเรือน</h4>';
	$ret.='<label>สถานการณ์ :</label>'.imed_model::qt('เครื่องนุ่งห่ม-สถานการณ์',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>สภาพปัญหา</label>'.imed_model::qt('เครื่องนุ่งห่ม-สภาพปัญหา',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>ความต้องการ</label>'.imed_model::qt('เครื่องนุ่งห่ม-ความต้องการ',$qt,$rs,$isEdit).'<br />'._NL;

	$ret.='<h4>4. อาหาร</h4>';
	$ret.='<label>สถานการณ์ :</label>'.imed_model::qt('อาหาร-สถานการณ์',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>สภาพปัญหา</label>'.imed_model::qt('อาหาร-สภาพปัญหา',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>ความต้องการ</label>'.imed_model::qt('อาหาร-ความต้องการ',$qt,$rs,$isEdit).'<br />'._NL;

	$ret.='<h4>5. สุขภาพ</h4>';
	$ret.='<label>สถานการณ์ :</label>'.imed_model::qt('สุขภาพ-สถานการณ์',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>กรณีมีคนป่วย</label><br /><label>จำนวนคนป่วย </label>'.imed_model::qt('สุขภาพ-คนป่วย',$qt,$rs,$isEdit).'คน'.'<br />'
				.'<label>ประเภทของการป่วย : </label>'.imed_model::qt('สุขภาพ-โรคเรื้อรัง',$qt,$rs,$isEdit)
				.imed_model::qt('สุขภาพ-คนพิการ',$qt,$rs,$isEdit)
				.'<br />'._NL;
	$ret.='<label>สภาพปัญหา</label>'.imed_model::qt('สุขภาพ-สภาพปัญหา',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>ความต้องการ</label>'.imed_model::qt('สุขภาพ-ความต้องการ',$qt,$rs,$isEdit).'<br />'._NL;

	$ret.='<h4>6. การออม</h4>';
	$ret.='<label>สถานการณ์ :</label>'.imed_model::qt('การออม-สถานการณ์',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>กรณีมีการออม</label><br /><label>รูปแบบของการออม </label>​<br />'
				.imed_model::qt('การออม-เงินฝากธนาคาร',$qt,$rs,$isEdit).'<br />'
				.imed_model::qt('การออม-กลุ่มออมทรัพย์/สัจจะ',$qt,$rs,$isEdit).'<br />'
				.imed_model::qt('การออม-สหกรณ์',$qt,$rs,$isEdit).'<br />'
				.imed_model::qt('การออม-แชร์',$qt,$rs,$isEdit).'<br />'
				.imed_model::qt('การออม-อื่นๆ',$qt,$rs,$isEdit)
				.' <label>ระบุ : </label>'.imed_model::qt('การออม-ระบุ',$qt,$rs,$isEdit).'<br />'
				.'<br />'._NL;
	$ret.='<label>สภาพปัญหา</label>'.imed_model::qt('การออม-สภาพปัญหา',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>ความต้องการ</label>'.imed_model::qt('การออม-ความต้องการ',$qt,$rs,$isEdit).'<br />'._NL;



	$ret.='<h4>7. อาชีพและรายได้ของสมาชิกในครัวเรือน</h4>';
	$ret.='<label>ทุกคนในครัวเรือนมีรายได้รวม</label>'.imed_model::qt('อาชีพ-รายได้รวม',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>รายได้เฉลี่ย/คน/เดือน</label>'.imed_model::qt('อาชีพ-รายได้เฉลี่ย',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>สมาชิกที่ไม่มีรายได้</label>'.imed_model::qt('อาชีพ-ไม่มีรายได้',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>สภาพปัญหา</label>'.imed_model::qt('อาชีพ-สภาพปัญหา',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>ความต้องการ :</label><br />'
				.imed_model::qt('อาชีพ-อาชีพเสริม',$qt,$rs,$isEdit).' ระบุ : '.imed_model::qt('อาชีพ-อาชีพเสริม-ระบุ',$qt,$rs,$isEdit).'<br />'
				.imed_model::qt('อาชีพ-รับงานมาทำที่บ้าน',$qt,$rs,$isEdit).' ระบุ : '.imed_model::qt('อาชีพ-รับงานมาทำที่บ้าน-ระบุ',$qt,$rs,$isEdit).'<br />'
				.imed_model::qt('อาชีพ-ลดรายจ่ายในครัวเรือน',$qt,$rs,$isEdit).'<br />'
				.imed_model::qt('อาชีพ-อื่นๆ',$qt,$rs,$isEdit).' ระบุ : '.imed_model::qt('อาชีพ-อื่นๆ-ระบุ',$qt,$rs,$isEdit).'<br />'
				._NL;

	$ret.='<h4>8. ความรู้ความเข้าใจในปรัชญาเศรษฐกิจพอเพียง</h4>';
	$ret.='<label>สถานการณ์ :</label>'.imed_model::qt('เศรษฐกิจพอเพียง-สถานการณ์',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>กรณีไม่มีความรู้ :</label>'.imed_model::qt('เศรษฐกิจพอเพียง-ไม่มีความรู้',$qt,$rs,$isEdit).'<br />'._NL;
	$ret.='<label>ข้อเสนอแนะ</label>'.imed_model::qt('เศรษฐกิจพอเพียง-ข้อเสนอแนะ',$qt,$rs,$isEdit).'<br />'._NL;

	$ret.='<h4>9. การได้รับความช่วยเหลือจากหน่วยงานของรัฐหรือเอกชนในรอบปีที่ผ่านมา</h4>';
	$ret.=imed_model::qt('ความช่วยเหลือ',$qt,$rs,$isEdit).'<br />'._NL;

	$ret.='<h4>10. ข้อเสนอแนะ สภาพปัญหาอื่น ๆ หรือความต้องการช่วยเหลือนอกเหนือจากประเด็นที่ระบุในข้างต้น</h4>';
	$ret.=imed_model::qt('ข้อเสนอแนะ',$qt,$rs,$isEdit).'<br />'._NL;

	$ret.='<h4>11. ลำดับความสำคัญของปัญหาที่ต้องการให้แก้ไขเร่งด่วน</h4>';
	$ret.=imed_model::qt('ลำดับความสำคัญของปัญหา',$qt,$rs,$isEdit).'<br />'._NL;

	if ($isEdit || $isAccess) {
		$ret.='<h4>ภาพถ่าย</h4>';
		$ret.=__imed_poor_photo($poorid,$isEdit);

		$ret.='<br clear="all" /><p><form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('imed/poorhome/view/'.$poorid,array('act'=>'photo')).'" data-rel="#poor-gallery"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ส่งภาพประกอบ</span><input type="file" name="photo" class="inline-upload" /></span></form><br clear="all" /></p>';
	}

	//$ret.=print_o($rs,'$rs');

	$ret.='</div>';
	$ret.='<style type="text/css">
	label {min-width:7em; font-weight:bold; display:inline-block; margin-right: 10px;}
	h4 {margin:40px 0 0;}
	</style>';
	return $ret;
}

function __imed_poor_get($poorid) {
	$stmt='SELECT
				p.*
				, COUNT(m.`poorid`) `members`
				, GROUP_CONCAT(IF(m.`reltohouseholder`="เจ้าบ้าน",CONCAT(psn.`prename`," ",psn.`name`," ",psn.`lname`),"") SEPARATOR "") householderName
				, cosub.`subdistname` subdistname
				, codist.`distname` distname
				, copv.`provname` provname
				FROM %poor% p
					LEFT JOIN %poormember% m USING(`poorid`)
					LEFT JOIN %db_person% psn USING(`psnid`)
					LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
					LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
					LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
				WHERE `poorid`=:poorid
				GROUP BY `poorid`
				ORDER BY `poorid` ASC
				LIMIT 1';
	$rs=mydb::select($stmt,':poorid',$poorid);
	if (!$rs->_empty) $rs->address=SG\implode_address($rs);
	return $rs;
}

function __imed_poor_view_addmember($poorid) {
	$ret.='<header class="header">'._HEADER_BACK.'<h3>เพิ่มสมาชิกในครัวเรือนที่อาศัยอยู่จริงในปีปัจจุบัน</h3></header>';

	$post=(object)post('person');
	if ($post->fullname) {
		$post->uid=i()->id;
		list($post->name,$post->lname)=sg::explode_name(' ',$post->fullname);

		if (empty($post->psnid)) {
			$post->psnid=NULL;
			$psnRs=mydb::select('SELECT * FROM %poor% WHERE `poorid`=:poorid LIMIT 1',':poorid',$poorid);

			$post->house=$psnRs->house;
			$post->village=$psnRs->village;
			$post->tambon=$psnRs->tambon;
			$post->ampur=$psnRs->ampur;
			$post->changwat=$psnRs->changwat;
			$post->zip=$psnRs->zip;
			$post->created=date('U');

			$stmt='INSERT IGNORE INTO %db_person%
							(`psnid`, `uid`, `prename`, `name`, `lname`, `house`, `village`, `tambon`, `ampur`, `changwat`)
						VALUES
							(:psnid, :uid, :prename, :name, :lname, :house, :village, :tambon, :ampur, :changwat)';
			mydb::query($stmt,$post);
			$post->psnid=mydb()->insert_id;
			//$ret.=mydb()->_query.'<br />';
		}

		if ($poorid && $post->psnid) {
			$stmt='INSERT INTO %poormember% (`poorid`, `psnid`) VALUES (:poorid, :psnid)';
			mydb::query($stmt,':poorid',$poorid, ':psnid',$post->psnid);
			//$ret.=mydb()->_query.'<br />';
		}
		location('imed/poorhome/view/'.$poorid);
	}

	$form = new Form([
		'variable' => 'person',
		'action' => url('imed/poorhome/view/'.$poorid,array('act'=>'addmember')),
		'class' => 'sg-form',
		'data-location' => url('imed/poorhome/view/'.$poorid),
		'children' => [
			'psnid' => ['type'=>'hidden','value'=>$post->psnid],
			'prename' => [
				'type' => 'text',
				'label' => 'คำนำหน้านาม',
				'value' => htmlspecialchars($post->prename),
				'setfocus' => true,
			],
			'fullname' => [
				'type' => 'text',
				'label' => 'ชื่อ - นามสกุล',
				'class' => 'sg-autocomplete -fill',
				'require' => true,
				'attr' => 'data-altfld="edit-person-psnid" data-query="'.url('org/api/person').'"',
				'value' => htmlspecialchars($post->fullname),
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>เพิ่มสมาชิก</span>',
				'pretext' => '<a class="sg-action" href="javascript:void(0)" data-rel="close">ยกเลิก</a>',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	$ret .= $form->build();
	//$ret.=print_o($post,'$post');
	return $ret;
}

function __imed_poor_photo($poorid,$isEdit) {
	$gallery=mydb::select('SELECT `gallery` FROM %poor% WHERE `poorid`=:poorid LIMIT 1',':poorid',$poorid)->gallery;
	$ret.='<div id="poor-gallery" class="gallery -fixedheight">'._NL;
	$ret.='<ul>'._NL;
	if ($gallery) {
		$photos=mydb::select('SELECT f.`fid`, f.`type`, f.`file`, f.`title` FROM %topic_files% f WHERE f.`gallery`=:gallery', ':gallery',$gallery);
		foreach ($photos->items as $item) {
			if ($item->type!='photo') continue;
			$photo=model::get_photo_property($item->file);
			$photo_alt=$item->title;
			$ret.='<li>';
			$ret.='<a class="sg-action" data-group="photo'.$poorid.'" href="'.$photo->_src.'" data-rel="box" title="'.htmlspecialchars($photo_alt).'">';
			$ret.='<img class="photo -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" height="256" ';
			$ret.=' />';
			$ret.='</a>';
			$photomenu=array();
			$ui = new Ui('span');
			if ($isEdit) {
				$ui->add('<a class="sg-action" href="'.url('imed/poorhome/view/'.$poorid,array('act'=>'rotatephoto','id'=>$item->fid)).'" title="หมุนภาพนี้" data-rel="none"><i class="icon -rotate"></i></a>');
				$ui->add('<a class="sg-action" href="'.url('imed/poorhome/view/'.$poorid,array('act'=>'removephoto','id'=>$item->fid)).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="notify" data-done="remove:parent li"><i class="icon -material">delete</i></a>');
			}
			$ret.=$ui->build();
			$ret.=view::inlineedit(array('group'=>'photo','fld'=>'title','tr'=>$item->fid),$item->title,$isEdit,'text');
			$ret .= '</li>'._NL;
		}
	}
	$ret.='</ul>'._NL;
	$ret.='</div><!--photo-->'._NL;
	//$ret.=print_o($rs,'$rs');
	return $ret;
}

/**
 * Save upload photo
 *
 * @param Integer $poorid
 * @return String and die
 */
function __imed_poor_savephoto($poorid) {
	$is_new_gallery=false;
	$ret='';

	// Get old gallery , create new gallery is not exists
	$gallery=mydb::select('SELECT `gallery` FROM %poor% WHERE `poorid`=:poorid LIMIT 1',':poorid',$poorid)->gallery;
	if (empty($gallery)) {
		$gallery=mydb::select('SELECT MAX(gallery) lastgallery FROM %topic_files% LIMIT 1')->lastgallery+1;
		$is_new_gallery=true;
	}

	$photo=$_FILES['photo'];
	if (!is_uploaded_file($photo['tmp_name'])) return message('error',"Upload error : No upload file");

	$ext=strtolower(sg_file_extension($photo['name']));
	if (!in_array($ext,array('jpg','jpeg','png'))) return message('error','Invalid photo format');

	// Upload photo
	$upload=new classFile($photo,cfg('paper.upload.photo.folder'),cfg('photo.file_type'));
	if (!$upload->valid_format()) die("Upload error : Invalid photo format");
	if (!$upload->valid_size(cfg('photo.max_file_size')*1024)) {
		sg_photo_resize($upload->upload->tmp_name,cfg('photo.resize.width'),NULL,NULL,true,cfg('photo.resize.quality'));
	}
	if ($upload->duplicate()) $upload->generate_nextfile();
	$photo_upload=$upload->filename;
	$pics_desc['type']='photo';
	$pics_desc['title']='';
	$pics_desc['cid'] = NULL;
	$pics_desc['gallery'] = $gallery;
	$pics_desc['uid']=i()->ok?i()->uid:NULL;
	$pics_desc['file']=$photo_upload;
	$pics_desc['timestamp']='func.NOW()';
	$pics_desc['ip'] = ip2long(GetEnv('REMOTE_ADDR'));

	if ($upload->copy()) {
		$stmt='INSERT INTO %topic_files% (`type`, `cid`, `gallery`, `uid`, `file`,`title`, `timestamp`, `ip`) VALUES (:type, :cid, :gallery, :uid, :file, :title, :timestamp, :ip)';
		mydb::query($stmt,$pics_desc);
		//$ret.=mydb()->_query.'<br />';
		$fid=mydb()->insert_id;
		if ($is_new_gallery) {
			mydb::query('UPDATE %poor% SET gallery=:gallery WHERE `poorid`=:poorid LIMIT 1',':poorid',$poorid,':gallery',$gallery);
			//$ret.=mydb()->_query.'<br />';
		}
	} else {
		$ret.='Upload error : Cannot save upload file';
	}
	return $ret;
}

/**
 * Delete photo
 *
 * @param Integer $fid - file id
 * @return String
 */
function __imed_poor_delphoto($poorid,$fid) {
	$rs=mydb::select('SELECT f.*, tr.trid FROM %topic_files% f LEFT JOIN %project_tr% tr ON tr.gallery=f.gallery WHERE f.fid='.$fid.' LIMIT 1',':fid',$fid);
	//$ret.=print_o($rs,'$rs');
	if ($rs->file) {
		if ($rs->type=='photo') {
			mydb::query('DELETE FROM %topic_files% WHERE fid='.$fid.' AND `type`="photo" LIMIT 1',':fid',$fid);
			$remain=mydb::select('SELECT COUNT(*) remain FROM %topic_files% WHERE gallery=:gallery LIMIT 1',':gallery',$rs->gallery)->remain;
			if ($remain==0) {
				mydb::query('UPDATE %poor% SET gallery=NULL WHERE poorid=:poorid LIMIT 1',':poorid',$poorid);
			}
			$filename=cfg('folder.abs').cfg('upload_folder').'pics/'.$rs->file;
			if (file_exists($filename) and is_file($filename)) {
				$is_photo_inused = mydb::count_rows('%topic_files%','file="'.$rs->file.'" AND fid!='.$rs->fid);
				if (!$is_photo_inused) unlink($filename);
				$ret.=$is_photo_inused?'ภาพถูกใช้โดยคนอื่น':'ลบภาพเรียบร้อยแล้ว';
			}
			model::watch_log('poor','remove photo','Photo id '.$rs->fid.' - '.$rs->file.' of poor '.$poorid.' was removed by '.i()->name.'('.i()->uid.')');
		}
	}
	return $ret;
}

?>