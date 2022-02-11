<?php
/**
* Project :: Proposal Sharing
* Created 2021-11-08
* Modify  2021-11-18
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/proposal/{id}/info.share
*/

$debug = true;

import('model:org.php');
import('widget:node.share.php');

class ProjectProposalInfoShare extends Page {
	var $projectId;
	var $proposalInfo;

	function __construct($proposalInfo = NULL) {
		$this->projectId = $proposalInfo->projectId;
		$this->proposalInfo = $proposalInfo;
	}

	function build() {
		if (!$this->projectId) {
			return  message(['responseCode' => _HTTP_OK_NO_CONTENT, 'text' => 'ไม่มีข้อมูลข้อเสนอโครงการที่ระบุ']);
		}

		$topicMember = i()->ok ? R::Model('paper.membership.get',$this->projectId,i()->uid) : NULL;
		$orgMember = $this->proposalInfo->orgId && i()->ok ? OrgModel::officerType($this->proposalInfo->orgId,i()->uid) : NULL;

		$isAdmin = i()->admin;

		$this->isEditable = user_access('administer projects')
			|| in_array($orgMember, array('MANAGER','ADMIN','OWNER','TRAINER'))
			|| in_array($topicMember, array('MANAGER','ADMIN','OWNER','TRAINER'));

		$memberDb = mydb::select(
			'SELECT
				t.`uid` `topicUid`, u.`uid`, u.`username`, u.`name`, u.`email`, UPPER(tu.`membership`) `membership`
			FROM %topic% t
				LEFT JOIN %topic_user% tu USING(`tpid`)
				LEFT JOIN %users% u ON u.`uid` = tu.`uid`
			WHERE `tpid` = :projectId',
			[':projectId' => $this->projectId]
		);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แบ่งปัน',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Container([
				'id' => 'proposal-info-share',
				'attribute' => ['data-url' => url('project/proposal/'.$this->projectId.'/info.share')],
				'children' => [
					new NodeShareWidget([
						'copyLink' => _DOMAIN.url('project/proposal/'.$this->projectId),
						'shareMember' => $this->isEditable ? [
							'action' => url('project/'.$this->projectId.'*/info/member.add'),
							'done' => 'load:box',
							'query' => url('api/user'),
						] : NULL,
						'members' => new Table([
							'id' => 'project-develop-member-list',
							'class' => 'project-develop-member-list',
							'showHeader' => false,
							// 'colgroup' => ['','width="100%"','class="-hover-parent" '],
							'thead' => ['','','-nowrap -hover-parent' => ''],
							'children' => (function($memberDb){
								$widgets = [];
								foreach ($memberDb->items as $rs) {
									$ui = new Ui();
									if ($this->isEditable) {
										$ui->add('<a class="sg-action" href="'.url('profile/'.$rs->uid).'" data-rel="box" data-width="640"><i class="icon -material">find_in_page</i></a>');
									}
									if ($this->isEditable && $rs->topicUid != $rs->uid) {
										$ui->add('<a class="sg-action" href="'.url('project/info/api/'.$this->projectId.'*/member.remove/'.$rs->uid.'/').'" data-rel="notify" data-removeparent="tr"  data-title="ลบชื่อออกจากการมีส่วนร่วมในโครงการ" data-confirm="ต้องการลบชื่อออกจากการมีส่วนร่วมในโครงการ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>');
									}
									$menu = $ui->count() ? '<nav class="nav -icons -hover">'.$ui->build().'</nav>' : '';

									$widgets[] = [
										'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username).'" width="29" height="29" alt="'.htmlspecialchars($rs->name).'" title="'.htmlspecialchars($rs->name).'" /></a>',
										$rs->name
										.($rs->uid == i()->uid ? ' (is you)' : '')
										.($this->isEditable ? '<br /><span class="email">'.$rs->email.' ('.$rs->username.')</span>' : '')
										.'</a>',
										($rs->topicUid == $rs->uid ? 'Is ' : '').$rs->membership.
										$menu,
										'config' => $this->isEditable ? ['class' => 'sg-action', 'href' => url('profile/'.$rs->uid), 'data-rel' => 'box'] : '',
									];
								}
								return $widgets;
							})($memberDb), // children
						]), // Table
					]),

				],
			]),
		]);
	}
}
?>