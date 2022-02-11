<?php
/**
* Paper event when paper/$tpid (paper view) page call on module project :: event onView
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* Change property of $body will effect on page show
*/

import('model:org.php');

function on_project_paper_view($self,$topic,$para,$body) {
	//$body->onVeiw='onVeiw '.$topic->tpid;
	//print_o($body,'$body',1);
	//echo 'onView BBBBBBBAAAAAAAA<br />'.print_o($body,'$body');
	//$ret=$body->onVeiw;

		user_menu('edit:remove');
		user_menu('home:remove');
		user_menu('new:remove');
		user_menu('type:remove');
		user_menu('paper_id:remove');
		user_menu('member:remove');
		user_menu('tag:remove');
		user_menu('signin:remove');
		unset($self->theme->header);

		unset($topic->photo);

		$self->module='project';

		$topicUser=mydb::select('SELECT * FROM %topic_user% WHERE `tpid`=:tpid AND `uid`=:uid LIMIT 1',':tpid',$topic->tpid,':uid',i()->uid);
		//$ret.=print_o($topicUser,'$topicUser',1);
		if (user_access('access projects')) {
			; // do nothing
		} else if (i()->ok && user_access('access own projects') && $topicUser->uid==i()->uid) {
			; // do nothing
		} else {
			R::View('project.toolbar',$self,$topic->title,$topic);
			$self->theme->body='<p class="notify">ขออภัย :: สิทธิ์ในการเข้าดูรายละเอียดโครงการนี้ถูกปฎิเสธ กรุณาติดต่อผู้ดูแลระบบ</p>';
			unset($body->comment);
			return;
		}


		$tpid=$topic->tpid;
		head ('<script type="text/javascript">var tpid='.$topic->tpid.'</script>');
		$topic->RIGHT = NULL;
		$topic->RIGHTBIN = NULL;
		$topic->project = project_model::get_project($topic->tpid);
		$topic->membership = $topic->project->membership;
		if ($topic->orgid) {
			$topic->project->officerType = OrgModel::officerType($topic->orgid, i()->uid);
		}

		$topic->RIGHT = $topic->project->RIGHT;
		$topic->RIGHTBIN = decbin($topic->RIGHT);

		if ($topic->project->prtype=='แผนงาน') location('project/planning/'.$topic->tpid);
		else if ($topic->project->prtype=='ชุดโครงการ') location('project/set/'.$topic->tpid);

		if (debug('method')) $body->debug=print_o($para,'$para').print_o($topic,'$topic');

		if (isset($para->admin)) $body->project.='Administrator';

		$loadPageName = '';
		if (property_exists($para,'owner')) {
			$loadPageName=SG\getFirst($para->owner,'owner');
			unset($body->comment);
		} else if (property_exists($para,'trainer')) {
			$loadPageName=SG\getFirst($para->trainer,'trainer');
			unset($body->comment);
		} else if (property_exists($para,'info')) {
			$loadPageName=SG\getFirst($para->info,'info');
		} else if (property_exists($para,'situation')) {
			$loadPageName=SG\getFirst($para->situation,'situation');
			unset($para->situation);
		} else if ($para->member) {
			$loadPageName=$para->member;
			if ($para->post) $loadPageName=$para->post;
			unset($body->comment);
		} else if ($para->info) {
			$loadPageName=$para->info;
		} else {
			$loadPageName='detail';
		}

		/*
		if (empty($topic->project->template)) {
			$topic->project->template=mydb::select('SELECT * FROM %project% p WHERE `tpid`=:parent LIMIT 1',':parent',$topic->project->projectset)->template;
		}
		*/

		R::Module('project.template',$self,$tpid);


		//debugMsg($para,'$para');
		//debugMsg($topic,'$topic');

		R::View('project.toolbar',$self,$topic->title,NULL,$topic);
		if ($loadPageName) {
			$body->project.=R::Page('project.form.'.$loadPageName,$self,$topic,$para,$body);
		}

		property_reorder($body,'project','before detail');
		unset($body->relate,$body->detail, $body->photo,$body->video);
		unset($body->comment,$body->footer,$body->timestamp,$topic->property->option->package);

		if (debug('method')) debugMsg($body,'$body');
		//debugMsg($topic,'$topic');

		head('js.project.js','<script type="text/javascript" src="/project/js.project.js"></script>');
}