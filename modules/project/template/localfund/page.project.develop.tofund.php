<?php
/**
* Set project develop to local fund
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function project_develop_tofund($self, $tpid = NULL) {
	$devInfo = R::Model('project.develop.get', $tpid);

	$isEdit = user_access('administer projects') || (i()->ok && $devInfo->info->uid == i()->uid);

	R::View('project.toolbar',$self,'ส่งพัฒนาโครงการให้กองทุน','develop', $devInfo);

	if (!$isEdit) return message('error', 'access denied');

	$ret = '';

	$toOrgId = SG\getFirst(post('oldorg'), post('neworg'));
	if ($toOrgId) {
		$stmt = 'UPDATE %project_dev% SET `toorg` = :toorg WHERE `tpid` = :tpid LIMIT 1';
		mydb::query($stmt, ':tpid', $tpid, ':toorg', $toOrgId);
		
		$orgInfo = R::Model('org.get', $toOrgId);
		$ret .= message('status', 'ส่งพัฒนาโครงการให้กองทุน '.$orgInfo->name.' เรียบร้อย');
		return $ret;
	}


	$form = new Form(NULL, url('project/develop/tofund/'.$tpid));
	$form->addConfig('title', $devInfo->info->title);

	$stmt = 'SELECT DISTINCT t.`orgid`, o.`name`
					FROM %topic_user% tu
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %db_org% o USING(`orgid`)
					WHERE tu.`uid` = :uid
					HAVING `orgid` IS NOT NULL';
	$dbs = mydb::select($stmt, ':uid', i()->uid);

	if ($dbs->_num_rows) {
		foreach ($dbs->items as $rs) {
			$oldOrgOptions[$rs->orgid] = $rs->name;
		}
		$form->addField('oldorg',
						array(
							'type' => 'radio',
							'label' => 'เลือกกองทุนที่เคยเสนอโครงการ',
							'options' => $oldOrgOptions,
							'value' => $devInfo->info->toorg,
						)
					);
	}

	$newOrgOptions = array('' => '==เลือกกองทุนในจังหวัด==');
	$stmt = 'SELECT o.`orgid`, o.`ampur`, o.`changwat`, o.`name`, cod.`distname` `ampurName`
					FROM %project_fund% f
						LEFT JOIN %db_org% o USING(`orgid`)
						LEFT JOIN %co_district% cod ON cod.`distid` = CONCAT(o.`changwat`,o.`ampur`)
					WHERE o.`changwat` = :changwat
					ORDER BY CONVERT(`ampurName` USING tis620) ASC, CONVERT(`name` USING tis620) ASC';
	$dbs = mydb::select($stmt, ':changwat', $devInfo->info->changwat);
	foreach ($dbs->items as $rs) $newOrgOptions['อำเภอ'.$rs->ampurName][$rs->orgid] = $rs->name;
	$form->addField('neworg',
						array(
							'type' => 'select',
							'label' => 'เลือกกองทุนในจังหวัด',
							'class' => '-fill',
							'options' => $newOrgOptions,
							'value' => $devInfo->info->toorg,
						)
					);

	$form->addField('save',
						array(
							'type' => 'button',
							'value' => '<i class="icon -save -white"></i><span>บันทึก</span>',
						)
					);
	$ret .= $form->build();

	//$ret .= print_o(post(),'post()');
	return $ret;
}
?>