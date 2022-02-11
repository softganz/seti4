<?php
/**
* Select Oganization
* Created 2019-10-20
* Modify  2019-10-20
*
* @param Object $self
* @param Object $vaprojectInfor
* @return String
*/

$debug = true;

function project_proposal_info_selectorg($self, $proposalInfo) {
	if (!$proposalInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $proposalInfo->tpid;

	$ret = '';

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>เลือกองค์กรหลัก</h3></header>';


	$form = new Form(NULL, url(), NULL, 'search-box');
	$form->addField(
		'orgname',
		array(
			'type' => 'text',
			'class' => 'sg-autocomplete -fill',
			'placeholder' => 'ค้นชื่อองค์กร',
			'attr' => array(
				'data-altfld'=>'edit-org-parent',
				'data-query'=>url('api/org','sector=10'),
				'data-updateurl' => url('project/edit/tr', array('tpid' => $tpid)),
				'data-tpid' => $tpid,
				'data-minLength' => 1,
				'data-render-item' => 'projectOrgRender',
				'data-render-complete' => 'projectOrgRenderComplete'
			),
		)
	);
	/*
	$form->addField(
		'go',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">search</i>',
		)
	);
	*/

	$ret .= '<nav class="nav -page">'.$form->build().'</nav>';

	$ret .= '<p class="-sg-text-center" style="color:red;">*** กรณีไม่พบชื่อสถาบันการศึกษาในรายการด้านล่าง ให้ติดต่อ 081-818-2543 ***</p>';
	$ret .= '<style type="text/css">
	.ui-menu.-orglist .icon.-material {color: transparent;}
	.ui-menu.-orglist a:hover>.icon.-material {color: #333;}
	</style>';

	$ret .= '<script type="text/javascript">
	$("#edit-orgname").keyup(function() {
		$("#orglist").empty()
	})

	function projectOrgRenderComplete($this) {
		$("#orglist a").click(function() {
			var $thisOrg = $(this)
			console.log("Click "+$thisOrg.data("orgid")+$thisOrg.html())
			var updateUrl = $("#edit-orgname").data("updateurl")
			var para = {}
			para.group = "topic"
			para.fld = "orgid"
			para.value = $thisOrg.data("orgid")
			para.action = "save"
			console.log(updateUrl)
			$.post(updateUrl, para, function(data) {
				console.log(data)
				$("#project-info-org>span>span").html($thisOrg.children("span").text())
				$.colorbox.close()
			},"json")
		})
	}

	function projectOrgRender($this, ul, item) {
		var target = document.getElementById("orglist")
		console.log(item)
		return $("<li class=\"ui-item\"></li>")
		.append(\'<a class="btn -link" href="javascript:void(0)" data-orgid="\'+item.value+\'"><i class="icon -material">done</i><span>\'+item.label+\'</span></a>\')
		.appendTo( target );
	}

	projectOrgRenderComplete()

	</script>';

	/*
	$ret .= '<form id="search" class="search-box" method="get" action="'.url('project/search').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ค้นชื่อโครงการหรือเลขที่ข้อตกลง" data-query="'.url('project/get/title').'" data-callback="'.url('project/').'" data-altfld="sid"><button type="submit"><i class="icon -search"></i></button></form>'._NL;

	$form = new Form(NULL,url('imed/app/person/search','webview'),NULL,'sg-form imed-search-patient');
	$form->addConfig('method', 'GET');
	$form->addData('checkValid',true);
	$form->addData('rel', '#patient-list');
	$form->addField('pid',array('type' => 'hidden', 'id' => 'pid'));
	$form->addField(
						'pn',
						array(
							'label' => 'ชื่อผู้ป่วยที่ต้องการเยี่ยมบ้าน',
							'type' => 'text',
							//'class' => 'sg-autocomplete -fill',
							'class' => '-fill',
							'require' => true,
							'value' => htmlspecialchars($getSearch),
							'autocomplete' => 'OFF',
							'attr' => array(
												'data-query' => url('imed/api/person'),
												'data-altfld' => 'pid',
												),
							'placeholder' => 'ระบุ ชื่อ นามสกุล หรือ เลข 13 หลัก ของผู้ป่วย',
							)
						);
	$form->addField(
					'go',
					array(
						'type'=>'button',
						'name'=>NULL,
						'value' => '<i class="icon -material">search</i><span>ค้นหา</span>',
						'posttext' => '<a class="sg-action btn -primary -addnew" href="'.url('imed/app/patient/add').'" data-rel="#patient-list" title="เพิ่มชื่อผู้ป่วยรายใหม่"><i class="icon -material">person_add</i></a>',
						)
					);
	$ret .= $form->build();
	*/



	$stmt = 'SELECT
		*
		FROM %db_org% o
		WHERE o.`sector` IN (10)
		ORDER BY CONVERT(`name` USING tis620) ASC';

	$dbs = mydb::select($stmt);

	$ui = new Ui(NULL, 'ui-menu -orglist');
	$ui->addClass('-orglist');
	$ui->addId('orglist');

	foreach ($dbs->items as $rs) {
		$cardStr = '<a class="btn -link" href="javascript:void(0)" data-orgid="'.$rs->orgid.'"><i class="icon -material">done</i><span>'.$rs->name.'</span></a>';
		$ui->add($cardStr);
	}

	$ret .= $ui->build();

	$ret .= '<style type="text/css">
	#ui-id-1 {display: none; border: none; padding: 0;}
	.ui-menu.-orglist .ui-item a {display: block; text-align: left;}
	</style>';

	return $ret;
}
?>