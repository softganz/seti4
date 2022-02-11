<?php
/**
 * Add user to be an owner
 *
 * @param Integer $tpid
 * @param String $_REQUEST[q]
 * @param Integer $_REQUEST[uid]
 * @return String or Array
 */

import('model:org.php');

function project_edit_addowner($self, $tpid = NULL) {
	$uid = post('uid');
	$q = trim(post('q'));
	$name = post('name');

	$topicMember = $tpid && i()->ok ? R::Model('paper.membership.get',$tpid,i()->uid) : NULL;
	$orgId = mydb::select('SELECT `orgid` FROM %topic% WHERE `tpid` = :tpid LIMIT 1',':tpid',$tpid)->orgid;
	$orgMember = $orgId && i()->ok ? OrgModel::officerType($orgId,i()->uid) : NULL;

	$isEditable = user_access('administer projects')
						|| in_array($orgMember, array('MANAGER','ADMIN','OWNER','TRAINER'))
						|| in_array($topicMember, array('MANAGER','ADMIN','OWNER','TRAINER'));



	//$ret .= 'orgmember = '.$orgMember.' ,topicMember = '.$topicMember;
	if (!($isEditable)) return $ret.'Access denied';

	//if (empty($tpid) || mydb::select('SELECT `tpid` FROM %topic% WHERE `tpid`=:tpid AND `type`="project" LIMIT 1',':tpid',$tpid)->_empty) return 'No project';

	if ($q) {
		$stmt='SELECT uid, username, name
						FROM %users% u
						WHERE u.`username` LIKE :q OR u.`name` LIKE :q  OR u.`email` LIKE :q
						ORDER BY u.`name` ASC
						LIMIT 10';
		$dbs=mydb::select($stmt,':q','%'.$q.'%');

		$result=array();
		foreach ($dbs->items as $rs) {
			$result[] = array(
										'value'=>$rs->uid,
										'label'=>htmlspecialchars($rs->name),
										//'desc'=>'<img src="'.model::user_photo($rs->username).'" width="24" height="24" />'
									);
		}
		if (debug('api')) {
			$result[]=array('value'=>'query','label'=>$dbs->_query);
			$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
		}
		return $result;
	}



	if ($uid) {
		if (mydb::select('SELECT `uid` FROM %topic_user% WHERE `tpid`=:tpid AND `uid`=:uid LIMIT 1',':tpid',$tpid, ':uid',$uid)->uid) {
			// do nothing
		} else {
			$stmt='INSERT INTO %topic_user% (`tpid`, `uid`, `membership`) VALUES (:tpid, :uid, :membership)
							ON DUPLICATE KEY UPDATE `membership`=:membership;';
			mydb::query($stmt,':tpid',$tpid, ':uid', $uid, ':membership', 'Owner');
			$user=mydb::select('SELECT uid,username,name FROM %users% WHERE uid=:uid LIMIT 1',':uid',$uid);
			if (i()->uid!=$uid) {
				$ret.='<img src="'.model::user_photo($user->username).'" width="32" height="32" alt="'.htmlspecialchars($user->name).'" title="'.htmlspecialchars($user->name).'" /><span><a class="sg-action" href="'.url('profile/'.$uid).'" data-rel="box">'.$user->name.' (Owner)</a></span> ';
				$ret.='<a class="sg-action" href="'.url('project/edit/removeowner/'.$tpid.'/'.$user->uid).'" data-rel="none" data-removeparent="li" data-confirm="ลบชื่อออกจากการเป็นผู้ดำเนินการติดตามสนับสนุนโครงการ"><i class="icon -cancel -gray"></i></a> ';
			}
			model::watch_log('project','add owner',$user->name.'('.$user->uid.') was added to be an owner of project '.$tpid.' by '.i()->name.'('.i()->uid.')');
		}
		return $ret;
	}


	$form = new Form(NULL, url('project/edit/addowner/'.$tpid),'add-owner', 'sg-form');
	$form->addData('rel','notify');
	$form->addData('complete','closebox');
	$form->addClass('project-addowner -inlineitem');

	$form->addField('uid',array('type'=>'hidden','name'=>'uid'));

	$form->addField(
					'name',
					array(
						'type'=>'text',
						'label'=>'ป้อน username หรือ ชื่อ',
						'class'=>'sg-autocomplete',
						'require'=>true,
						'value'=>htmlspecialchars($name),
						'placeholder'=>'ป้อน username หรือ ชื่อ',
						'attr'=>array(
											'data-query'=>url('project/edit/addowner/'.$tpid),
											'data-callback'=>'projectAddOwnerSubmit',
										),
					)
				);

	$form->addField('save',array('type'=>'button','value'=>'<i class="icon -addbig -white -circle"></i>'));

	$ret .= $form->build();

	$ret .= '
	<script type="text/javascript">
	var $form=$("form#add-owner");
	$form.find(".button,label").hide();

	function projectAddOwnerSubmit($this,ui) {
		var $form=$("form#add-owner");
		$.post($form.attr("action"),{uid: ui.item.value}, function(data) {
			console.log(data)
			$form.closest("td").children("ul").append("<li>"+data+"</li>");
			$form.parent().html("");
		});
	}
	</script>';
	return $ret;
}
?>