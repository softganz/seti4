<?php
function qt_group_course_experience($self,$action=NULL,$qtgrid=NULL) {
	R::View('toolbar',$self,'ผลการนำความรู้ไปใช้ประโยชน์ในงานเครือข่าย','qt.course');

	switch ($action) {
		case 'post':
			if (post('detail')) {
				$data->keyname='qt.course';
				$data->keyid=i()->uid;
				$data->fldname='experience';
				$data->flddata=trim(post('detail'));
				$data->created=date('U');
				$data->ucreated=i()->uid;
				$stmt='INSERT INTO %bigdata% (`keyname`,`keyid`,`fldname`,`flddata`,`created`,`ucreated`) VALUES (:keyname,:keyid,:fldname,:flddata,:created,:ucreated)';
				mydb::query($stmt,$data);
				//$ret.=mydb()->_query;
			}
			$ret.=__qt_group_course_experience_form();
			$ret.='<h3>รายการผลการนำความรู้ไปใช้ประโยชน์ในงานเครือข่าย</h3>';
			$ret.=__qt_group_course_experience_show();
			//$ret.=print_o(post(),'post()');
			break;

		default:
			$ret.=__qt_group_course_experience_form();
			$ret.='<h3>รายการผลการนำความรู้ไปใช้ประโยชน์ในงานเครือข่าย</h3>';
			$ret.=__qt_group_course_experience_show();
			break;
	}

	return $ret;
}

function __qt_group_course_experience_form() {
	$form=new Form('data',url('qt/group/course/experience/post'),NULL,'sg-form');
	$form->addData('rel','#main');

	$form->addField(
					'detail',
					array(
						'type'=>'textarea',
						'name'=>'detail',
						'label'=>'วิธีการ/ผลการนำความรู้ไปใช้ประโยชน์ในงานเครือข่าย',
						'class'=>'-fill',
						'require'=>true,
						)
					);

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'name'=>'save',
						'containerclass'=>'-sg-text-right',
						'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>'
						)
					);

	$ret.=$form->build();
	return $ret;
}

function __qt_group_course_experience_show() {
	$stmt='SELECT
					*
					, `flddata` `detail`
					, `created` `timedata`
					, u.`username`
					, u.`name` `posterName`
					FROM %bigdata% b
						LEFT JOIN %users% u ON u.`uid`=b.`ucreated`
					WHERE `keyname`="qt.course" AND `fldname`="experience"
					ORDER BY `bigid` DESC';
	$dbs=mydb::select($stmt);
	foreach ($dbs->items as $rs) {
		$ret.=__qt_group_course_experience_render($rs);
	}
	//$ret.=print_o($dbs);
	return $ret;
}

function __qt_group_course_experience_render($rs,$showEdit=true) {

	return $ret;
}
?>