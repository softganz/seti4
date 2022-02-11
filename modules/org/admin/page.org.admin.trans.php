<?php
/**
* Org Upgrade Method
*
* @param Object $self
* @return String
*/

class OrgAdminTrans extends Page {
	var $orgId;

	function __construct($orgId = NULL) {
		$this->orgId = SG\getFirst($orgId, post('orgid'));
	}

	function build() {
		head('<style type="text/css">
			h4 {color: red; background-color: #ddd; padding: 8px;}
		</style>');

		$orgInfo = mydb::select('SELECT * FROM %db_org% WHERE `orgid` = :orgId LIMIT 1', ':orgId', $this->orgId);

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
				'<h4>db_org</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %db_org% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>org_board</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %org_board% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>org_docs</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %org_docs% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>org_doings</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %org_doings% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>org_dopaid</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %org_dopaid% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>org_dopaidtr</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %org_dopaidtr% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>org_dos</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %org_dos% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>org_mjoin</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %org_mjoin% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>org_morg</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %org_morg% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>org_officer</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %org_officer% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>org_ojoin</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %org_ojoin% WHERE `orgid` = :orgId OR `jorgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>org_subject</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %org_subject% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

				'<h4>topic</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %topic% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>project_fund</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %project_fund% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>project_gl</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %project_gl% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>project_tr</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %project_tr% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				'<h4>qtmast</h4>',
				mydb::printtable(mydb::select('SELECT * FROM %qtmast% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				// '<h4>table</h4>',
				// mydb::printtable(mydb::select('SELECT * FROM %table% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				// '<h4>table</h4>',
				// mydb::printtable(mydb::select('SELECT * FROM %table% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				// '<h4>table</h4>',
				// mydb::printtable(mydb::select('SELECT * FROM %table% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				// '<h4>table</h4>',
				// mydb::printtable(mydb::select('SELECT * FROM %table% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				// '<h4>table</h4>',
				// mydb::printtable(mydb::select('SELECT * FROM %table% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),
				// '<h4>table</h4>',
				// mydb::printtable(mydb::select('SELECT * FROM %table% WHERE `orgid` = :orgId', ':orgId', $this->orgId)),

			],
		]);
	}
}
?>