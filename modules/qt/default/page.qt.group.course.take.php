<?php
/**
 * Assessor Take Course
 *
 * @param Integer $psnid
 * @param String $action
 * @param Integer $trid
 * @return String
 */
function qt_group_course_take($self,$tpid=NULL,$action=NULL,$trid=NULL) {
	$step=post('step');
	R::View('toolbar',$self,'บันทึกแบบประเมินผลหลักสูตร','qt.course');

	// Check is login
	if (!i()->ok) {
		R::View('toolbar',$self,'ระบบสมาชิก','qt.course');
		return '<p class="notify">สำหรับผู้ที่เป็นสมาชิกเว็บอยู่แล้ว กรุณาเข้าสู่ระบบสมาชิกก่อนลงทะเบียน<br />หากยังไม่ได้เป็นสมาชิก กรุณา<a href="'.url('user/register',array('ret'=>'qt/group/course/take')).'">สมัครสมาชิกเว็บ</a>ให้เรียบร้อยก่อนลงทะเบียน</p>'.R::View('signform');
	}


	// Check is assessor register
	$stmt='SELECT * FROM %person_group% WHERE `groupname`="assessor" AND `uid`=:uid LIMIT 1';
	$assessorInfo=mydb::select($stmt,':uid',i()->uid);
	if ($assessorInfo->_empty) location('qt/group/course/register');
	//$ret.=print_o($assessorInfo);

	//if (!is_numeric($psnid)) {$action=$psnid;unset($psnid);}

	//if ($psnid) $psnInfo=R::Model('person.get',$psnid);

	if (empty($tpid)) {
		// Step 1 : Select Course
		$dbs=mydb::select('SELECT * FROM %topic% WHERE `type`="course" ORDER BY `tpid` ASC');
		//$ret.=print_o($dbs,'$dbs');
		$ret.='<h3>เลือกหลักสูตร</h3>';
		$no=0;
		$ui = new Ui('div', 'ui-card');
		foreach ($dbs->items as $rs) {
			$ui->add('<a class="btn -fill -course-select" href="javascript:void(0)" data-cid="'.$rs->tpid.'"><span style="margin: 0 auto; padding: 0; display: block; width: 3em; height: 3em; line-height: 3em; border-radius: 50%; background-color: #999; color: #fff;">'.(++$no).'</span><span>'.$rs->title.'</span></a>');
		}
		$ret .= $ui->build();
		//$ret.='<div class="ui-item col -md-5"><a class="btn -course-select" href="">2.<br /> การบริหารโครงการสุขภาวะ<br />(Project and Program Management in Health Promotion)</a></div>';
		//$ret.='<div class="container -sg-text-center"><div class="ui-card row">';
		//$ret.='</div></div>';
		$ret .= '<nav class="nav -page -sg-text-right" style="padding: 32px;"><a id="course-next" class="btn -disabled" href="'.url('qt/group/course/take').'"><i class="icon -forward -white"></i><span>ถัดไป</span></a></nav>';
	} else {
		$courseTitle=mydb::select('SELECT `title` FROM %topic% WHERE `type`="course" AND `tpid`=:tpid LIMIT 1',':tpid',$tpid)->title;
		R::View('toolbar',$self,'บันทึกแบบประเมินผลหลักสูตร - '.$courseTitle,'qt.course');


		// Get or Create quotation on empty
		$stmt='SELECT * FROM %qtmast% WHERE `qtform`="102" AND `tpid`=:tpid AND `uid`=:uid LIMIT 1';
		$qtMast=mydb::select($stmt,':tpid',$tpid, ':uid',i()->uid);
		if ($qtMast->_empty) {
			$stmt='INSERT INTO %qtmast% (`qtgroup`, `qtform`, `tpid`, `psnid`, `qtdate`, `qtstatus`, `uid`, `created`) VALUES ("10", "102", :tpid, NULL, :qtdate, 1, :uid, :created)';
			mydb::query($stmt,':tpid',$tpid, ':uid',i()->uid, ':qtdate',date('Y-m-d'), ':created',date('U'));
			$qtref=mydb()->insert_id;
		} else {
			$qtref=$qtMast->qtref;
		}
		//$ret.=print_o($qtMast,'$qtMast');

		// Save Data
		$data=(object)post('data');
		$data->tpid=$tpid;
		$data->qtref=$qtref;
		R::Model('qt.save.course',$data);

		if (post('datab')) {
			$datab=(object)post('datab');
			$datab->tpid=$tpid;
			$datab->qtref=$qtref;
			R::Model('qt.save.course',$datab);
		}

		if (post('use')) {
			$use=(object)post('use');
			$use->tpid=$tpid;
			$use->qtref=$qtref;
			R::Model('qt.save.course',$use);
		}

		if (empty($step)) $step=1;
		$form=new Form('data',url('qt/group/course/take/'.$tpid),NULL,'sg-form');
		$form->addData('checkValid',true);

		$lists=array(
			1=>'เนื้อหา',
			'เอกสาร/คู่มือ',
			'วิทยากร',
			'การบรรยาย',
			'กิจกรรมกลุ่ม',
			'ความรู้/ทักษะ/ความสามารถ',
			'การนำความรู้ไปใช้ประโยชน์ในงานของเครือข่าย',
		);

		// Setp 3 : ความพึงพอใจในกิจกรรม
		if ($step>=1 && $step<=7) {
			$qt=$step;
			$ret.='<h3>ส่วนที่ '.($step).' '.$lists[$qt].'</h3>';

			$qtTran=mydb::select('SELECT * FROM %qttran% WHERE `qtref`=:qtref AND `part`=:part LIMIT 1',':qtref',$qtref, ':part','RATE.'.$qt);

			$form->addField('qtid',array('type'=>'hidden','value'=>$qtTran->qtid));
			$form->addField('part',array('type'=>'hidden','value'=>'RATE.'.$qt));

			$tables = new Table();
			if ($qt==6) {
				$qtTranB=mydb::select('SELECT * FROM %qttran% WHERE `qtref`=:qtref AND `part`="AFTER.6" LIMIT 1',':qtref',$qtref, ':part','RATE.'.$qt);
				$form->addField('qtidb',array('type'=>'hidden','name'=>'datab[qtid]','value'=>$qtTranB->qtid));
				$form->addField('partb',array('type'=>'hidden','name'=>'datab[part]','value'=>'AFTER.6'));

				$tables->colgroup=array(
					'',
					'center -1'=>'',
					'center -2'=>'',
					'center -3'=>'',
					'center -4'=>'',
					'center -5'=>'',
					'center -6'=>'',
					'center -7'=>'',
					'center -8'=>'',
					'center -9'=>'',
					'center -10'=>'',
				);
				$tables->thead='<tr><th rowspan="3">หัวข้อที่ประเมิน</th><th colspan="5">ก่อนการอบรม</th><th colspan="5">หลังการอบรม</th></tr>'
					.'<tr><th>มากที่สุด</th><th>มาก</th><th>ปานกลาง</th><th>น้อย</th><th>น้อยที่สุด</th><th>มากที่สุด</th><th>มาก</th><th>ปานกลาง</th><th>น้อย</th><th>น้อยที่สุด</th></tr>'
				;

				$tables->rows[]=array(
					$lists[$step],
					'<input class="rate-check -require" type="radio" name="data[rate]" value="5" '.($qtTran->rate==5?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="data[rate]" value="4" '.($qtTran->rate==4?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="data[rate]" value="3" '.($qtTran->rate==3?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="data[rate]" value="2" '.($qtTran->rate==2?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="data[rate]" value="1" '.($qtTran->rate==1?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="datab[rate]" value="5" '.($qtTranB->rate==5?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="datab[rate]" value="4" '.($qtTranB->rate==4?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="datab[rate]" value="3" '.($qtTranB->rate==3?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="datab[rate]" value="2" '.($qtTranB->rate==2?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="datab[rate]" value="1" '.($qtTranB->rate==1?'checked="checked"':'').' />',
				);
				$form->addText($tables->build());
				//$ret.='<p class="-sg-text-right"><button class="btn -primary"><i class="icon -forward -white"></i><span>ถัดไป</span></button></p>';
			} else {
				$tables->thead=array(
						'หัวข้อที่ประเมิน',
						'center -c1'=>'มากที่สุด<br />(5)',
						'center -c2'=>'มาก<br />(4)',
						'center -c3'=>'ปานกลาง<br />(3)',
						'center -c4'=>'น้อย<br />(2)',
						'center -c5'=>'น้อยที่สุด<br />(1)'
					);


				$tables->rows[]=array(
					$lists[$qt],
					'<input class="rate-check -require" type="radio" name="data[rate]" value="5" '.($qtTran->rate==5?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="data[rate]" value="4" '.($qtTran->rate==4?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="data[rate]" value="3" '.($qtTran->rate==3?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="data[rate]" value="2" '.($qtTran->rate==2?'checked="checked"':'').' />',
					'<input class="rate-check -require" type="radio" name="data[rate]" value="1" '.($qtTran->rate==1?'checked="checked"':'').' />'
				);
				$form->addText($tables->build());
			}

			if ($step==7) {
				$useTran=mydb::select('SELECT * FROM %qttran% WHERE `qtref`=:qtref AND `part`=:part LIMIT 1',':qtref',$qtref, ':part','USE');
				$form->addField('useqtid',array('type'=>'hidden','name'=>'use[qtid]','value'=>$useTran->qtid));
				$form->addField('usepart',array('type'=>'hidden','name'=>'use[part]','value'=>'USE'));
				$form->addField(
					'value-use',
					array(
						'type'=>'textarea',
						'name'=>'use[value]',
						'label'=>'ตัวอย่างการนำความรู้ไปใช้ประโยชน์ในงานของเครือข่าย',
						'class'=>'-fill',
						'rows'=>10,
						'value'=>$useTran->value,
					)
				);
			}

			$form->addField(
				'value',
				array(
					'type'=>'textarea',
					'label'=>'ข้อเสนอแนะและความคิดเห็นเพิ่มเติม',
					'class'=>'-fill',
					'rows'=>10,
					'value'=>$qtTran->value,
				)
			);
			$form->addField('next','<p class="clear -sg-text-right">'.($step>1?'<button class="btn" name="step" value="'.($step-1).'"><i class="icon -back"></i><span>ก่อนหน้า</span></button> ':'').'<button class="btn -primary'.($qtTran->rate?'':' -disabled').'" name="step" value="'.($step+1).'"><i class="icon -forward -white"></i><span>ถัดไป</span></button></p>');
		} else if ($step==8) {
			// Step 5 : ข้อเสนอแนะและความคิดเห็นเพิ่มเติม
			$ret.='<h3>ข้อเสนอแนะและความคิดเห็นเพิ่มเติม</h3>';
			$qtTran=mydb::select('SELECT * FROM %qttran% WHERE `qtref`=:qtref AND `part`=:part LIMIT 1',':qtref',$qtref, ':part','COMMENT');
			$form->addField('qtid',array('type'=>'hidden','value'=>$qtTran->qtid));
			$form->addField('part',array('type'=>'hidden','value'=>'COMMENT'));
			$form->addField(
				'value',
				array(
					'type'=>'textarea',
					'label'=>'ข้อเสนอแนะและความคิดเห็นเพิ่มเติม',
					'class'=>'-fill',
					'value'=>$qtTran->value,
				)
			);
			$form->addField('next','<p class="clear -sg-text-right"><button class="btn" name="step" value="7"><i class="icon -back"></i><span>ก่อนหน้า</span></button> <button class="btn -primary" name="step" value="end"><i class="icon -save -white"></i><span>บันทึกแบบประเมินผลหลักสูตร</span></button></p>');
		} else if ($step=='end') {
			$ret.=message('notify','บันทึกข้อมูลเรียบร้อย');
			$ret.='<hr /><p><a class="btn" href="'.url('qt/group/course/take').'">บันทึกแบบประเมินผลหลักสูตรอื่น ๆ</a></p>';

		}

		$ret .= $form->build();
	}

	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($qtTran,'$qtTran');
	//$ret.=print_o($qtTranB,'$qtTranB');

	//$ret.=print_o($psnInfo,'$psnInfo');

	$ret.='<style type="text/css">
	.btn.-active {background:#f60;color:#fff;box-shadow:0 0 0 1px red inset;}
	h3 {padding:16px;background:#999;margin:32px 0; font-size:1.6em;color:#fff;}
	.btn.-primary.-disabled {background-color:#87b1d8; box-shadow: 0 0 0 1px #87b1d8 inset;}
	.item td:nth-child(n+2) {background:#f5f5f5;}
	.item td:nth-child(n+7) {background:#e0e0e0;}
	</style>';
	$ret.='<script type="text/javascript">
	var courseUrl=$("#course-next").attr("href")+"/";
	$(".btn.-course-select").click(function(){
		console.log("Click");
		var $this=$(this);
		$(".btn.-course-select").removeClass("-active");
		$this.toggleClass("-active");
		$("#course-next").removeClass("-disabled").addClass("-primary").attr("href",courseUrl+$this.data("cid"));
		return false;
	});

	$(".rate-check").click(function(){
		var $this=$(this);
		var $btnPrimary=$this.closest("form").find(".-primary");
		console.log("Click");
		$btnPrimary.removeClass("-disabled");
	})
	</script>';
	return $ret;
}


function __qt_group_course_take_saveperson($post) {
	if ($post->name && $post->lname) {
		$addrList=SG\explode_address($post->address,$post->areacode);
		$post->house=$addrList['house'];
		$post->village=$addrList['village'];
		$post->tambon=$addrList['tambonCode'];
		$post->ampur=$addrList['ampurCode'];
		$post->changwat=$addrList['changwatCode'];

		$post->rhouse=$addrList['house'];
		$post->rvillage=$addrList['village'];
		$post->rtambon=$addrList['tambonCode'];
		$post->rampur=$addrList['ampurCode'];
		$post->rchangwat=$addrList['changwatCode'];

		//$ret.=print_o($post,'$post');
		//$ret.=print_o($addrList,'$addrList');
		if (empty($post->cid)) $post->cid=NULL;

		$dupid=mydb::select('SELECT p.`psnid` FROM %db_person% p WHERE name=:name && lname=:lname AND `cid` IS :cid LIMIT 1',$post);
		$post->query[]=mydb()->_query;

		if (empty($post->name) || empty($post->lname)) {
			$error='กรุณาป้อน ชื่อ และ นามสกุล โดยเว้นวรรค 1 เคาะ';
		} else if ($post->name && $post->lname &&
			$dupid=mydb::select('SELECT p.`psnid` FROM %db_person% p WHERE name=:name AND lname=:lname LIMIT 1',$post)->psnid) {
			$error='ชื่อ <b>"'.$post->name.' '.$post->lname.'"</b> มีอยู่ในฐานข้อมูลแล้ว';
		}
		if ($error) {
			$post->error=$error;
		} else {
			//$ret.='<p>Prepare to save person</p>';
			$post->uid=i()->uid;
			$post->created=date('U');

			$stmt='INSERT INTO %db_person% (
					  `uid`, `cid`, `prename`, `name`, `lname`
					, `house`, `village`, `tambon`, `ampur`, `changwat`
					, `rhouse`, `rvillage`, `rtambon`, `rampur`, `rchangwat`
					, `phone`, `email`, `website`
					, `created`
				) VALUES (
					  :uid, :cid, :prename, :name, :lname
					, :house, :village, :tambon, :ampur, :changwat
					, :rhouse, :rvillage, :rtambon, :rampur, :rchangwat
					, :phone, :email, :website
					, :created
				)';
			mydb::query($stmt,$post);
			$post->query[]=mydb()->_query;

			if (!mydb()->_error) {
				$psnid=$post->psnid=mydb()->insert_id;
				$post->joindate=date('Y-m-d H:i:s');

				$stmt='INSERT INTO %person_group%
					(`groupname`,`psnid`,`uid`,`joindate`)
					VALUES
					("assessor",:psnid,:uid,:joindate)
					';
				mydb::query($stmt,$post);
				$post->query[]=mydb()->_query;
				//location('project/assessor/'.$psnid);
			}
		}
	}
	return $post;
}
?>