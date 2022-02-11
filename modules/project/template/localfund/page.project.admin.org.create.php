<?php
function project_admin_org_create($self, $orgType = NULL) {
	R::View('project.toolbar',$self, 'เพิ่มหน่วยงานใหม่');
	$self->theme->sidebar = R::View('project.admin.menu');


	/*
	if (empty($orgType)) {
		$ui = new Ui();
		$ui->add('<a href="'.url('project/admin/org/create/fund').'"><i class="icon -addbig"></i><span>สร้างกองทุนสุขภาพตำบล</a>');
		$ui->add('<a href="'.url('project/admin/org/create/muni').'"><i class="icon -addbig"></i><span>สร้างเทศบาล/อบต.</a>');
		$ui->add('<a href="'.url('project/admin/org/create/gen').'"><i class="icon -addbig"></i><span>สร้างองค์กร/หน่วยงานทั่วไป</a>');
		$ret .= $ui->build();
		return $ret;
	}
	*/

	$post = (object)post('org');

	// Create new organization
	if ($post->name) {
		$isDupShortname = false;
		$post->shortname = strtoupper(trim($post->shortname));
		if (empty($post->shortname))
			$post->shortname=NULL;

		if ($post->shortname) {
			$stmt = 'SELECT * FROM %db_org% WHERE `shortname` = :shortname LIMIT 1';
			$isDupShortname = mydb::select($stmt,$post)->orgid;
			if ($isDupShortname) {
				$result = R::Model('org.get',$isDupShortname);
				//$ret .= print_o($isDupShortname,'$isDupShortname');
			} else {
				$result = R::Model('org.create',$post);
			}
		} else {
			$result = R::Model('org.create',$post);
		}

		if ($result->_error) {
			$ret .= message('error',$result->_error);
		} else {
			if ($post->sector == 9) {
				$post->orgid = $result->orgid;
				$post->fundid = $post->shortname;

				$post->fundnamearea = mydb::select('SELECT `areaname` FROM %project_area% WHERE `areaid` = :areaid LIMIT 1', $post)->areaname;
				$post->fundnamechangwat = mydb::select('SELECT `provname` FROM %co_province% WHERE `provid` = :changwat LIMIT 1', $post)->provname;
				$post->fundnameampur = mydb::select('SELECT `distname` FROM %co_district% WHERE `distid` = :ampur LIMIT 1', ':ampur', $post->changwat.$post->ampur)->distname;
				$post->fundnamefull = mydb::select('SELECT `subdistname` FROM %co_subdistrict% WHERE `subdistid` = :tambon LIMIT 1', ':tambon', $post->changwat.$post->ampur.$post->tambon)->subdistname;

				if (empty($post->fundnamefull))
					$post->fundnamefull = $post->name;

				$stmt = 'INSERT INTO %project_fund%
								(
								`orgid`, `fundid`, `areaid`
								, `changwat`, `ampur`, `tambon`
								, `namearea`, `namechangwat`, `nameampur`
								, `fundname`
								)
								VALUES
								(
								  :orgid, :fundid, :areaid
								, :changwat, :ampur, :tambon
								, :fundnamearea, :fundnamechangwat, :fundnameampur
								, :fundnamefull
								)
								ON DUPLICATE KEY UPDATE
								  `areaid`=:areaid
								, `changwat`=:changwat
								, `ampur`=:ampur
								, `tambon`=:tambon
								, `namearea`=:fundnamearea
								, `namechangwat`=:fundnamechangwat
								, `nameampur`=:fundnameampur
								, `fundname`=:fundnamefull';
				mydb::query($stmt,$post);
				//$ret .= mydb()->_query;
			}
			$stmt = 'UPDATE %db_org% SET `tambon` = :tambon, `ampur` = :ampur, `changwat` = :changwat WHERE `orgid` = :orgid LIMIT 1';
			mydb::query($stmt, $post);
			//$ret .= mydb()->_query;
			location('project/admin/org/'.$result->orgid.'/edit');
		}
		//$ret .= print_o($post,'$post');
		//$ret .= print_o($result,'$result');
	}



	$form = new Form('type',url('project/admin/org/create'),'org-add-type','sg-form');
	$form->addData('checkValid',true);
	$form->addConfig('title','เพิ่มองค์กร/หน่วงาน/เทศบาล/อบต./กองทุนสุขภาพตำบล');

	$form->addField(
						'sector',
						array(
							'type' => 'select',
							'label' => 'เลือกประเภทองค์กรที่ต้องการ:',
							'class' => '-fill',
							'options' => array('' => '== เลือกประเภท ==') + project_base::$orgTypeList,
							'value' => $post->sector,
						)
					);

	$ret .= $form->build();



	// Form of localfund
	$form=new Form('org',url('project/admin/org/create'),'org-add-fund','sg-form box -hidden');
	$form->addData('checkValid',true);
	$form->addConfig('title','ข้อมูลกองทุนสุขภาพตำบล');
	$form->addField('sector', array('type' => 'hidden', 'value' => 9));
	$form->addField('parent',array('type'=>'hidden','value'=>$post->parent));

	$form->addField(
						'name',
						array(
							'type'=>'text',
							'label'=>'ชื่อกองทุนสุขภาพตำบล',
							'class'=>'sg-autocomplete -fill',
							'require'=>true,
							'value'=>htmlspecialchars($post->name),
							'attr'=>'data-query="'.url('org/api/org').'" data-select=\'{"edit-org-name":"label", "edit-org-shortname":"shortname"}\'',
						)
					);

	$form->addField(
						'shortname',
						array(
							'type'=>'text',
							'label'=>'รหัสกองทุนสุขภาพตำบล',
							'class'=>'-fill',
							'maxlength'=>5,
							//'require'=>true,
							'value'=>htmlspecialchars($post->shortname),
							'attr'=>array('style'=>'text-transform:uppercase'),
						)
					);

	$form->addField(
						'parentname',
						array(
							'type'=>'text',
							'label'=>'ชื่อหน่วยงานต้นสังกัด',
							'class'=>'sg-autocomplete -fill',
							'value'=>htmlspecialchars($post->parentname),
							'description'=>'กรุณาป้อนชื่อหน่วยงานต้นสังกัดและเลือกจากรายการที่แสดง',
							'attr'=>array(
												'data-altfld'=>'org-add-fund #edit-org-parent',
												'data-query'=>url('org/api/org','sectorX=other'),
											),
						)
					);

	$form->addField('h1','<h3>ข้อมูลกองทุน</h3>');

	$options=array(''=>'== เลือกเขต ==');
	foreach (mydb::select('SELECT * FROM %project_area% WHERE `areatype`="nhso" ORDER BY `areaid`+0')->items as $rs) {
		$options[$rs->areaid]='เขต '.$rs->areaid.' '.$rs->areaname;
	}
	$form->addField(
						'areaid',
						array(
							'type'=>'select',
							'label'=>'เขต:',
							'class'=>'-fill',
							'options'=>$options,
							'value'=>$post->areaid,
						)
					);


	foreach (mydb::select('SELECT `provid`,`provname` FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC; -- {key:"provid"}')->items AS $item) $provOptions[$item->provid]=$item->provname;

	$form->addField(
						'province',
						array(
							'label'=>'จังหวัด :',
							'type'=>'select',
							'options'=>$provOptions,
							)
						);

	$provStr='<div class="form-item"><label>เลือกจังหวัด/อำเภอ/ตำบล/หมู่บ้าน จากช่องเลือก</label><select name="org[changwat]" id="changwat" class="form-select sg-changwat">'._NL;
	$provStr.='<option value="">==เลือกจังหวัด==</option>'._NL;
	foreach ($provOptions as $k=>$v) {
		$provStr.='<option value="'.$k.'">'.$v.'</option>'._NL;
	}
	$provStr.='</select>'._NL;
	$provStr.='<select name="org[ampur]" id="ampur" class="form-select sg-ampur -hidden"><option value="">==เลือกอำเภอ==</option></select>'._NL;
	$provStr.='<select name="org[tambon]" id="tambon" class="form-select sg-tambon -hidden" data-altfld="#edit-areacode"><option value="">==เลือกตำบล==</option></select>'._NL;
	$provStr.='</div>'._NL;

	$form->addField('province',$provStr);

	$form->addField(
						'areacode',
						array(
							'type'=>'hidden',
							'label'=>'เลือกตำบลในที่อยู่',
							'value'=>$post->areacode,
							//'require'=>true
							)
						);

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>บันทึกชื่อกองทุนสภาพตำบลใหม่</span>',
						'containerclass' => '-sg-text-right',
						)
					);

	$form->addField('footer','หมายเหตุ : กรณีสร้างกองทุนตำบล ให้ระบุรหัสหน่วยงาน/กองทุน , เขต , จังหวัด , อำเภอ และ ตำบล ให้ครบถ้วน');
	$ret.=$form->build();





	// Form of muni org
	$form = new Form('org',url('project/admin/org/create'),'org-add-muni','sg-form box -hidden');
	$form->addData('checkValid',true);
	$form->addConfig('title','ข้อมูลเทศบาล/อบต.');
	$form->addField('sector', array('type' => 'hidden', 'value' => 5));
	$form->addField('parent',array('type'=>'hidden','value'=>$post->parent));

	$form->addField(
						'name',
						array(
							'type'=>'text',
							'label'=>'ชื่อเทศบาล/อบต.',
							'class'=>'sg-autocomplete -fill',
							'require'=>true,
							'value'=>htmlspecialchars($post->name),
							'attr'=>'data-query="'.url('org/api/org').'" data-select=\'{"edit-org-name":"label", "edit-org-shortname":"shortname"}\'',
						)
					);

	$form->addField(
						'shortname',
						array(
							'type'=>'text',
							'label'=>'รหัส อปท.',
							'class'=>'-fill',
							'maxlength'=>8,
							'value'=>htmlspecialchars($post->shortname),
							'attr'=>array('style'=>'text-transform:uppercase'),
							'placeholder' => 'F0000000',
						)
					);

	$form->addField(
						'parentname',
						array(
							'type'=>'text',
							'label'=>'ชื่อหน่วยงานต้นสังกัด',
							'class'=>'sg-autocomplete -fill',
							'value'=>htmlspecialchars($post->parentname),
							'description'=>'กรุณาป้อนชื่อหน่วยงานต้นสังกัดและเลือกจากรายการที่แสดง',
							'attr'=>array(
												'data-altfld'=>'org-add-muni #edit-org-parent',
												'data-query'=>url('org/api/org','sectorX=other'),
											),
						)
					);

	$form->addField(
						'province',
						array(
							'label'=>'จังหวัด :',
							'type'=>'select',
							'options'=>$provOptions,
							)
						);

	$provStr='<div class="form-item"><label>เลือกจังหวัด/อำเภอ/ตำบล/หมู่บ้าน จากช่องเลือก</label><select name="org[changwat]" id="changwat" class="form-select sg-changwat">'._NL;
	$provStr.='<option value="">==เลือกจังหวัด==</option>'._NL;
	foreach ($provOptions as $k=>$v) {
		$provStr.='<option value="'.$k.'">'.$v.'</option>'._NL;
	}
	$provStr.='</select>'._NL;
	$provStr.='<select name="org[ampur]" id="ampur" class="form-select sg-ampur -hidden"><option value="">==เลือกอำเภอ==</option></select>'._NL;
	$provStr.='<select name="org[tambon]" id="tambon" class="form-select sg-tambon -hidden" data-altfld="#edit-areacode"><option value="">==เลือกตำบล==</option></select>'._NL;
	$provStr.='</div>'._NL;

	$form->addField('province',$provStr);

	$form->addField(
						'areacode',
						array(
							'type'=>'hidden',
							'label'=>'เลือกตำบลในที่อยู่',
							'value'=>$post->areacode,
							//'require'=>true
							)
						);

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>บันทึกชื่อเทศบาล/อบต.ใหม่</span>',
						'containerclass' => '-sg-text-right',
						)
					);

	$ret.=$form->build();





	// Form of other org
	$form = new Form('org',url('project/admin/org/create'),'org-add-other','sg-form box -hidden');
	$form->addData('checkValid',true);
	$form->addConfig('title','ข้อมูลองค์กร/หน่วยงาน');
	$form->addField('sector', array('type' => 'hidden', 'value' => ''));
	$form->addField('parent',array('type'=>'hidden','value'=>$post->parent));

	$form->addField(
						'name',
						array(
							'type'=>'text',
							'label'=>'ชื่อองค์กร/หน่วยงาน',
							'class'=>'sg-autocomplete -fill',
							'require'=>true,
							'value'=>htmlspecialchars($post->name),
							'attr'=>'data-query="'.url('org/api/org').'" data-select=\'{"edit-org-name":"label", "edit-org-shortname":"shortname"}\'',
						)
					);

	$form->addField(
						'parentname',
						array(
							'type'=>'text',
							'label'=>'ชื่อหน่วยงานต้นสังกัด',
							'class'=>'sg-autocomplete -fill',
							'value'=>htmlspecialchars($post->parentname),
							'description'=>'กรุณาป้อนชื่อหน่วยงานต้นสังกัดและเลือกจากรายการที่แสดง',
							'attr'=>array(
												'data-altfld'=>'org-add-other #edit-org-parent',
												'data-query'=>url('org/api/org','sectorX=other'),
											),
						)
					);

	$form->addField(
						'province',
						array(
							'label'=>'จังหวัด :',
							'type'=>'select',
							'options'=>$provOptions,
							)
						);

	$provStr='<div class="form-item"><label>เลือกจังหวัด/อำเภอ/ตำบล/หมู่บ้าน จากช่องเลือก</label><select name="org[changwat]" id="changwat" class="form-select sg-changwat">'._NL;
	$provStr.='<option value="">==เลือกจังหวัด==</option>'._NL;
	foreach ($provOptions as $k=>$v) {
		$provStr.='<option value="'.$k.'">'.$v.'</option>'._NL;
	}
	$provStr.='</select>'._NL;
	$provStr.='<select name="org[ampur]" id="ampur" class="form-select sg-ampur -hidden"><option value="">==เลือกอำเภอ==</option></select>'._NL;
	$provStr.='<select name="org[tambon]" id="tambon" class="form-select sg-tambon -hidden" data-altfld="#edit-areacode"><option value="">==เลือกตำบล==</option></select>'._NL;
	$provStr.='</div>'._NL;

	$form->addField('province',$provStr);

	$form->addField(
						'areacode',
						array(
							'type'=>'hidden',
							'label'=>'เลือกตำบลในที่อยู่',
							'value'=>$post->areacode,
							//'require'=>true
							)
						);

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>บันทึกชื่อองค์กร/หน่วยงานใหม่</span>',
						'containerclass' => '-sg-text-right',
						)
					);

	$ret.=$form->build();
	//$ret.=print_o(post(),'post()');

	$ret .= '<script type="text/javascript">
	//$("#org-add-fund, #org-add-muni, #org-add-other").hide()
	$("#edit-type-sector").change(function() {
		console.log($(this).val())
		$("#org-add-fund, #org-add-muni, #org-add-other").hide()
		if ($(this).val() == 5)
			$("#org-add-muni").show()
		else if ($(this).val() == 9)
			$("#org-add-fund").show()
		else {
			$("#org-add-other").show()
			$("#org-add-other #edit-org-sector").val($(this).val())
			console.log($("#edit-org-sector").val())
		}
	})
	</script>';
	return $ret;
}
?>