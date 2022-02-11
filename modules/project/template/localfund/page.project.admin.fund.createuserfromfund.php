<?php

/*
-- IMPORT TO project_fund

INSERT IGNORE INTO `sgz_project_fund` (`orgid`,`fundid`,`areaid`,`namearea`,`namechangwat`,`nameampur`,`fundname`, `import_lot`)
SELECT `orgid`,`fundid`,`areaid`,`namearea`,`namechangwat`,`nameampur`,`fundname`,2
FROM `fund` ORDER BY `orgid` ASC;



-- IMPORT TO db_org

INSERT INTO `sgz_db_org` (
`orgid`, `parent`, `sector`, `name`, `shortname`, `created`
)
SELECT
`orgid`, 5, 9, CONCAT("กองทุนสุขภาพตำบล ",`fundname`), `fundid`, UNIX_TIMESTAMP()
FROM `fund` ORDER BY `orgid` ASC
ON DUPLICATE KEY UPDATE
`name` = CONCAT("กองทุนสุขภาพตำบล ",`fundname`)
;


1119

userid : 3938

DELETE FROM `sgz_org_officer` WHERE `uid` > 3938;
DELETE FROM `sgz_users` WHERE `uid` > 3938;
*/


function project_admin_fund_createuserfromfund($self, $orgType = NULL) {
	R::View('project.toolbar',$self, 'เพิ่มสมาชิกจากฐานข้อมูลกองทุน');
	$self->theme->sidebar = R::View('project.admin.menu');

	$getLotNo = post('lotno');

	// Create new organization
	if ($getLotNo > 0) {
		$stmt = 'SELECT `orgid`, `fundid`, `fundname`, `username`
			FROM %project_fund% f
				LEFT JOIN %users% u ON u.`username` = f.`fundid`
			WHERE `import_lot` = :import_lot AND `username` IS NULL';

		$dbs = mydb::select($stmt, ':import_lot', $getLotNo);

		foreach ($dbs->items as $rs) {
			$fundId = trim($rs->fundid);
			$data->orgid = $rs->orgid;
			$data->addusername = $fundId;
			$data->addpassword = $fundId.'.'.substr($fundId,-2);
			$data->name = 'จนท.กองทุน '.trim($rs->fundname);
			$data->encpassword = sg_encrypt($data->addpassword,cfg('encrypt_key'));
			$data->datein = date('Y-m-d H:i:s');
			$data->status = 'enable';
			$data->admin_remark = 'Add by Admin Impoty';
			$data->membership = 'ADMIN';

			$stmt = 'INSERT INTO %users%
				(`username`, `password`, `name`, `status`, `datein`, `admin_remark`)
				VALUES
				(:addusername, :encpassword, :name, :status, :datein, :admin_remark)';
			mydb::query($stmt,$data);
			$data->uid = mydb()->insert_id;

			$ret .= mydb()->_query.'<br />';

			$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, :membership)';
			mydb::query($stmt,$data);
			$ret .= mydb()->_query.'<br />';

		}
	}



	$form = new Form(NULL,url('project/admin/fund/createuserfromfund'),NULL,'sg-form');
	$form->addData('checkValid',true);

	$form->addField(
						'lotno',
						array(
							'type' => 'select',
							'label' => 'รอบการนำเข้า:',
							'class' => '-fill',
							'require' => true,
							'options' => array('' => '== เลือกรอบการนำเข้า ==', '2' => 'รอบที่  2') ,
							'value' => $getLotNo,
						)
					);





	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>สร้างสมาชิกใหม่</span>',
						'containerclass' => '-sg-text-right',
						)
					);

	$ret .= $form->build();

	//$ret .= print_o($dbs, '$dbs');

	return $ret;
}
?>