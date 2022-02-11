<?php
/**
* project :: Manage Category Code
* Created 2020-12-22
* Modify  2020-12-22
*
* @param Object $self
* @return String
*
* @usage project/admin/category[/{id}/{action}]
*/

$debug = true;

function project_admin_category($self, $tagId = NULL, $action = NULL) {
	// Data Model
	$taggroup = post('g');
	$stmt = 'SELECT `taggroup`,COUNT(*) `total` FROM %tag% WHERE `taggroup` LIKE "project:%" GROUP BY `taggroup`';
	$dbs = mydb::select($stmt);

	switch ($action) {
		case 'form':
			$ret .= __project_admin_category_form($tagId);
			break;

		case 'change':
			if ($newId = post('to')) {
				switch (post('taggroup')) {
					case 'project:expcode':
						$ret .= __project_admin_category_change_to($tagId, $newId);
						break;
					
					default:
						$ret .= 'ไม่สามารถเปลี่ยนรหัสได้';
						break;
				}
			}
			break;
		
		default:
			R::View('project.toolbar',$self,'Project Administrator','admin');

			$navBar = new Ui(NULL, 'ui-menu');
			$navBar->addConfig('container', '{tag: "nav"}');
			foreach ($dbs->items as $rs) {
				$navBar->add('<a href="'.url('admin/category',array('g'=>$rs->taggroup)).'">'.$rs->taggroup.' ('.$rs->total.')'.'</a>');
			}

			$self->theme->sidebar = $navBar->build();

			//$ret .= R::Page('admin.category', NULL);
			break;
	}

	return $ret;
}

function __project_admin_category_change_to($tagId, $newId) {
	$tagInfo = mydb::select('SELECT * FROM %tag% WHERE `tid` = :tagId LIMIT 1', ':tagId', $tagId);
	$catId = $tagInfo->catid;

	$isDup = mydb::select('SELECT * FROM %tag% WHERE `taggroup` = :taggroup AND `catid` = :catid LIMIT 1', ':taggroup', $tagInfo->taggroup, ':catid', $newId)->catid;
	//$ret .= '$isDup = '.$isDup;
	if ($isDup) return 'ERROR: Id '.$newId.' is duplicate.';


	$ret .= 'Change id '.$catId.' to '.$newId.' completed.';

	$stmt = 'UPDATE %project_tr% SET `refid` = :newId, `gallery` = :newId WHERE `formid` = "develop" AND `part` = "exptr" AND `gallery` = :oldId';
	mydb::query($stmt, ':oldId', $catId, ':newId', $newId);
	$ret .= '<br />'.mydb()->_query.'<br />';

	$stmt = 'UPDATE %project_tr% SET `refid` = :newId, `gallery` = :newId WHERE `formid` = "expense" AND `part` = "exptr" AND (`gallery` = :oldId OR `refid` = :oldId )';
	mydb::query($stmt, ':oldId', $catId, ':newId', $newId);
	$ret .= mydb()->_query.'<br />';

	$stmt = 'UPDATE %org_dopaidtr% SET `catid` = :newId WHERE `catid` = :oldId';
	mydb::query($stmt, ':oldId', $catId, ':newId', $newId);
	$ret .= mydb()->_query.'<br />';

	$stmt = 'UPDATE %tag% SET `catid` = :newId WHERE `taggroup` = :taggroup AND `catid` = :oldId';
	mydb::query($stmt, ':oldId', $catId, ':newId', $newId, ':taggroup', $tagInfo->taggroup);
	$ret .= mydb()->_query.'<br />';

	return $ret;
}
?>