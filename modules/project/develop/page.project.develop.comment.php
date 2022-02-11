<?php
/**
* Module Method
*
* @param Object $self
* @param Int $tpid
* @param String $part
* @return String
*/

$debug = true;

function project_develop_comment($self, $tpid, $part = NULL) {
	$devInfo = is_object($tpid) ? $tpid : R::Model('project.develop.get',$tpid);
	$tpid = $devInfo->tpid;

	$isAdmin = $devInfo->RIGHT & _IS_ADMIN;
	$isOwner = $devInfo->RIGHT & _IS_OWNER;
	$isTrainer = $devInfo->RIGHT & _IS_TRAINER;
	$isEditable = $devInfo->RIGHT & _IS_EDITABLE;


	$isEdit = user_access('administer projects');
	$isAdd = user_access('administer projects');

	$ownerId = mydb::select('SELECT `uid` FROM %topic% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid)->uid;
	$is_comment_sss = user_access('comment project');
	$is_comment_hsmi = user_access('administer projects') || project_model::is_trainer_of($tpid);
	$is_comment_owner = user_access('administer projects','comment own project',$ownerId);

	//$ret.='part='.$part.' tpid='.$tpid.' owner='.$ownerId.' i='.i()->uid;

	$post = (object) post();

	if ($post->act == 'delete' && $post->id) {
		$ownerId=mydb::select('SELECT `ucreated` FROM %bigdata% WHERE `bigid`=:id LIMIT 1',':id',$post->id)->ucreated;
		if (user_access('administer projects','edit own project content',$ownerId)) {
			$stmt='DELETE FROM %bigdata% WHERE `bigid`=:id LIMIT 1';
			mydb::query($stmt,':id',$post->id);
			return 'ลบทิ้งเรียบร้อย';
		} else {
			return;
		}
	} else if ($post->part) {
		if ($post->msg) {
			$post->keyname='project.develop';
			$post->fldname=$part;
			$post->keyid=$tpid;
			$post->flddata=$post->msg;
			$post->created=date('U');
			$post->ucreated=i()->uid;
			$stmt='INSERT INTO %bigdata% (`keyname`, `keyid`, `fldname`, `flddata`, `created`, `ucreated`) VALUES (:keyname, :keyid, :fldname, :flddata, :created, :ucreated)';
			mydb::query($stmt,$post);
			//$ret.=mydb()->_query;

			// Update change time
			$isCommentator=mydb::select('SELECT `uid` FROM %users% WHERE `uid`=:uid AND `roles`="commentator" LIMIT 1',':uid',i()->uid)->uid;
			if ($isCommentator) {
				mydb::query('UPDATE %topic% SET `commentsssdate`=:changed WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid, ':changed', date('Y-m-d H:i:s'));
			} else {
				mydb::query('UPDATE %topic% SET `commenthsmidate`=:changed WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid, ':changed', date('Y-m-d H:i:s'));
			}
		}
		$ret.=__project_develop_comment_draw($tpid,$post->part);
		//if ($is_comment_hsmi || $is_comment_sss || $is_comment_owner) $ret.=__project_develop_comment_form($tpid,$post->part);
		if ($part == 'comment-commentator' && $isAdmin) {
			$ret .= __project_develop_comment_form($tpid,$post->part);
		} else if ($part == 'comment-summary' && ($isAdmin || $isTrainer)) {
			$ret .= __project_develop_comment_form($tpid,$post->part);
		} else if ($isAdmin || $isTrainer || $is_comment_hsmi || $is_comment_sss || $is_comment_owner) {
			$ret .= __project_develop_comment_form($tpid,$post->part);
		}
		return $ret;
	}



	// Show comment
	$ret .= '<div id="project-'.$part.'">'._NL;
	//$ret .= $isAdmin ? 'ADMIN' : 'NOT ADMIN';
	if ($part == 'comment-commentator') {
		if ($isOwner || $isAdmin || $isTrainer) {
			$ret .= __project_develop_comment_draw($tpid,$part,$is_comment_hsmi);
		}
		if ($isAdmin) {
			$ret .= __project_develop_comment_form($tpid,$part);
		}
	} else if ($part == 'comment-summary') {
		if ($isOwner || $isAdmin || $isTrainer) {
			$ret .= __project_develop_comment_draw($tpid,$part,$is_comment_hsmi);
		}
		if ($isAdmin || $isTrainer) {
			$ret .= __project_develop_comment_form($tpid,$part);
		}
	} else if ($isAdmin || $isTrainer || $is_comment_hsmi || $is_comment_sss || $is_comment_owner) {
		$ret .= __project_develop_comment_draw($tpid,$part,$is_comment_hsmi);
		$ret .= __project_develop_comment_form($tpid,$part);
	}
	$ret.='</div>'._NL;

	//$ret.=print_o($dbs,'$dbs');
	//$ret.=print_o($post,'$post').print_o($_FILES,'$_FILES');
	//$ret.=print_o($devInfo,'$devInfo');
	return $ret;
}

function __project_develop_comment_draw($tpid,$part) {
	$stmt = 'SELECT c.*, u.`username`, u.`name` `posterName`, u.`roles`
					FROM %bigdata% c
					LEFT JOIN %users% u ON `uid` = c.`ucreated`
					WHERE `keyid` = :tpid AND `keyname` = "project.develop" AND `fldname` = :part
					ORDER BY `bigid` ASC';
	$dbs = mydb::select($stmt,':tpid',$tpid,':part',$part);
	$ret .= '<ul class="project-report-items">'._NL;
	foreach ($dbs->items as $rs) {
		$isEdit = user_access('administer projects','edit own project content',$rs->ucreated);
		$inlineAttr = array('class' => 'project-report-item -hover-parent');
		if ($isEdit) {
			$inlineAttr['class'] .= ' sg-inline-edit';
			$inlineAttr['data-tpid'] = $tpid;
			$inlineAttr['data-update-url'] = url('project/develop/update/'.$tpid);
			if (debug('inline')) $inlineAttr['data-debug']='inline';
		}

		$ret .= '<li '.sg_implode_attr($inlineAttr).'>'._NL;
		if ($isEdit) $ret .= '<nav class="nav -icons -hover -top-right"><a href="'.url('project/develop/comment/'.$tpid,array('act'=>'delete','id'=>$rs->bigid)).'" class="sg-action" data-confirm="ต้องการลบข้อความนี้ กรุณายืนยัน" data-removeparent="li" data-rel="this" style="position:absolute;right:4px;"><i class="icon -material -gray">cancel</i></a></nav>'._NL;
		$ret .= '<div class="poster'.($rs->roles=='commentator'?' bycommentator':'').'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="24" height="24" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" />'.$rs->posterName.' @'.sg_date($rs->created,'ว ดด ปปปป H:i:s').($rs->roles=='commentator'?'<p>ความเห็นผู้ทรงคุณวุฒิ</p>':'').'</div>'._NL;
		$ret .= '<div class="summary">'.view::inlineedit(array('group'=>'bigdata', 'fld'=>$part, 'tr'=>$rs->bigid, 'ret'=>'html'), $rs->flddata, $isEdit, 'textarea').'</div>'._NL;
		//$ret .= print_o($rs,'$rs');
		$ret .= '</li>'._NL;
	}
	$ret .= '</ul>'._NL;
	return $ret;
}

function __project_develop_comment_form($tpid,$part) {
	$ret.='<form class="sg-form -no-print" method="post" action="'.url('project/develop/comment/'.$tpid.'/'.$part).'" data-rel="#project-'.$part.'">'._NL;
	$ret.='<input type="hidden" name="part" value="'.$part.'" />';
	$ret.='<div id="form-project-adminreport-msg" class="form-item"><textarea id="project-adminreport-msg" name="msg" class="form-textarea -fill" rows="3" cols="20" placeholder="รายละเอียดความคิดเห็น"></textarea></div>'._NL;
	$ret.='<div class="form-item"><button id="project-adminreport-submit" class="btn -primary">'.tr('Post').'</button></div>'._NL;
	$ret.='</form>'._NL;
	return $ret;
}
?>