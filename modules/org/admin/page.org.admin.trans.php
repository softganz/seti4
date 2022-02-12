<?php
/**
* Org Upgrade Method
*
* @param Object $self
* @return String
*/

import('model:org.php');

class OrgAdminTrans extends Page {
	var $orgId;

	function __construct($orgId = NULL) {
		$this->orgId = SG\getFirst($orgId, post('orgid'));
	}

	function build() {
		if (!$this->orgId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'กรุณาระบุองค์กร']);

		$orgInfo = OrgModel::get($this->orgId);

		head('<style type="text/css">
			h4 {color: red; background-color: #ddd; padding: 8px;}
		</style>');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Transaction ของ องค์กร '.$orgInfo->name,
				'navigator' => [
					'<a class="" href="'.url('org/'.$this->orgId).'" target="_blank"><i class="icon -material">deck</i><span>องค์กร</span></a>',
					'<a class="" href="'.url('fund/'.$this->orgId).'" target="_blank"><i class="icon -material">deck</i><span>กองทุน</span></a>',
					'<a class="" href="'.url('project/org/'.$this->orgId).'" target="_blank"><i class="icon -material">deck</i><span>โครงการ</span></a>',
				],
				'trailing' => new Row([
					'children' => [
						new Form([
							'action' => url('org/admin/trans'),
							'children' => [
								'orgid' => [
									'type' => 'text',
									'value' => $this->orgId,
									'placeholder' => 'orgId',
								],
							], // children
						]), // Form
						new Dropbox([
							'children' => [
								'<a href=""><i class="icon -material">home</i><span>Test</span></a>',
							], // children
						]), // Dropbox
					], // children
				]), // Row
			]),
			'body' => new Container([
				'children' => [
					$this->orgId ? $this->_orgTrans() : NULL,
				],
			]),
		]);
	}

	function _orgTrans() {
		return new Container([
			'children' => [
				new ListTile([
					'title' => 'db_org',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %db_org% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'org_board',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %org_board% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'org_docs',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %org_docs% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'org_doings',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %org_doings% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'org_dopaid',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %org_dopaid% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'org_dopaidtr',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %org_dopaidtr% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'org_dos',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %org_dos% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'org_mjoin',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %org_mjoin% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'org_morg',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %org_morg% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'org_officer',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %org_officer% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'org_ojoin',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %org_ojoin% WHERE `orgid` = :orgId OR `jorgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'org_subject',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %org_subject% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'topic',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %topic% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'project_fund',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %project_fund% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				new ListTile([
					'title' => 'project_gl',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %project_gl% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				new ListTile([
					'title' => 'project_tr',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %project_tr% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				new ListTile([
					'title' => 'qtmast',
					'leading' => '<i class="icon -material">stars</i>',
				]),
				mydb::printtable(mydb::select('SELECT * FROM %qtmast% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
			],
		]);
	}
}
?>