<?php
/**
* Project :: Admin View Organization
* Created 2021-09-26
* Modify  2021-09-26
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class ProjectAdminOrgView extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo = NULL) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		if (!$this->orgId) return message('error', 'PROCESS ERROR');

		$sectorList = project_base::$orgTypeList;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->orgInfo->name,
				'crossAxisAlignment' => 'center',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
				'trailing' => new Row([
					'children' => ['<a class="sg-action btn -link" href="'.url('org/'.$this->orgId.'/info.view/edit').'" data-rel="box"><i class="icon -material">edit</i></a>',],
				]), // Row
			]),
			'body' => new Widget([
				'children' => [
					new Table([
						'colgroup' => ['width="20%"','width="80%"'],
						'children' => [
							['ID',$this->orgInfo->orgid],
							['ชื่อหน่วยงาน','<strong>'.$this->orgInfo->name.'</strong>'],
							['ชื่อย่อ','<strong>'.$this->orgInfo->info->shortname.' / '.$this->orgInfo->info->enshortname.'</strong>'],
							['หน่วยงานต้นสังกัด', ($this->orgInfo->info->parentName ? '<a class="sg-action" href="'.url('project/admin/org/'.$this->orgInfo->info->parent).'" data-rel="box">'.$this->orgInfo->info->parentName.'</a>':'ไม่มี')
								. '<a class="sg-action btn -link -sg-24" href="'.url('project/admin/org/'.$this->orgInfo->orgid.'/parent').'" data-rel="parent:td" style="position: absolute; right: 8px;"><i class="icon -material">add_circle_outline</i></a>'],
							['ประเภทองค์กร',project_base::$orgTypeList[$this->orgInfo->info->sector]],
							['ที่อยู่', $this->orgInfo->info->address],
							['รหัสพื้นที่ (AreaCode)', $this->orgInfo->info->areacode],
							['E-Mail',$this->orgInfo->info->email],
							['Phone',$this->orgInfo->info->phone],
							['วันที่สร้างข้อมูล',sg_date($this->orgInfo->info->created,'ว ดด ปปปป')],
						], // children
					]), // Table
					// new DebugMsg($this->orgInfo,'$this->orgInfo'),
					new Table([
						'thead' => ['no' => '', 'ชื่อหน่วยงาน', 'ประเภทองค์กร'],
						'caption' => 'หน่วยงานในสังกัด',
						'children' => (function() {
							$rows = [];
							$orgChild = mydb::select('SELECT o.`orgid`,o.`name`, o.`sector` FROM %db_org% o WHERE o.`parent` = :orgid', ':orgid', $this->orgId);
							$no = 0;
							foreach ($orgChild->items as $rs) {
								$rows[] = [
									++$no,
									'<a class="sg-action" href="'.url('project/admin/org/'.$rs->orgid).'" data-rel="box">'.$rs->name.'</a>',
									project_base::$orgTypeList[$rs->sector].'('.$rs->sector.')',
								];
							}
							return $rows;
						})(),
					]), // Table
					new Form([
						'class' => 'sg-form',
						'action' => url('org/info/api/'.$this->orgId.'/officer.add'),
						'rel' => 'notify',
						'done' => 'load:.box-page',
						'children' => [
							'uid' => ['type' => 'hidden'],
							new Table([
								'caption' => 'เจ้าหน้าที่องค์กร',
								'thead' => ['ชื่อ','อีเมล์','โทรศัพท์','กลุ่มสมาชิก','date -hover-parent' => 'วันที่เริ่มเป็นสมาชิก'],
								'children' => (function() {
									$rows = [];
									$stmt = 'SELECT
										o.`orgid`,o.`uid`, o.`membership`
										, u.`username`, u.`name`, u.`roles`, u.`email`, u.`phone`, u.`datein`
										FROM %org_officer% o
											LEFT JOIN %users% u USING(`uid`)
										WHERE o.`orgid` = :orgid
										ORDER BY CONVERT(`name` USING tis620) ASC';
									$dbs = mydb::select($stmt,':orgid',$this->orgId);

									foreach ($dbs->items as $rs) {
										$rows[] = [
											'<a class="sg-action" href="'.url('project/admin/user/'.$rs->uid).'" data-rel="box">'.$rs->name.' ('.$rs->username.')</a>',
											$rs->email,
											$rs->phone,
											$rs->membership,
											sg_date($rs->datein,'ว ดด ปปปป H:i')
											. '<nav class="nav iconset -hover"><a href="'.url('org/info/api/'.$this->orgId.'/officer.remove/'.$rs->uid).'" class="sg-action" data-rel="this" data-done="remove:parent tr" data-title="ลบเจ้าหน้าที่องค์กร" data-confirm="ต้องการลบเจ้าหน้าที่ออกจากองค์กร กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>',
										];
									}

									$rows[] = [
										'<input type="text" name="orgname" class="sg-autocomplete form-text -fill" data-query="'.url('admin/get/username',array('r'=>'id')).'" data-altfld="edit-uid" size="40" placeholder="ป้อนชื่อสมาชิก" data-select="label" />',
										'',
										'',
										'<select name="membership" class="form-select -fill"><option value="ADMIN">Admin</option><option value="OFFICER" selected="selected">Officer</option><option value="TRAINER">Trainer</option><option value="MEMBER">Regular Member</option></select>',
										'<button class="btn"><i class="icon -add"></i><span>เพิ่มเจ้าหน้าที่</span></button>'
									];

									return $rows;
								})(),
							]), // Table
						], // children
					]), // Form
				],
			]),
		]);
	}
}
?>