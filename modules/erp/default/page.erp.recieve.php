<?php
/**
* Module :: Description
* Created 2021-12-02
* Modify  2021-12-02
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class ErpRecieve extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Recieve',
			]),
			'body' => new Widget([
				'children' => [
					new Table([
						'thead' => [
							'เลขที่',
							'rcvdate -date' => 'วันที่',
							'name' => 'ลูกค้า',
							'total -money -hover-parent' => 'จำนวนเงิน',
						],
						'children' => array_map(
							function($item){
								$nav = new Nav([
									'class' => '-icons -hover',
									'children' => [
										'<a href="'.url('erp/'.$this->orgId.'/recieve.info/'.$item->rcvId).'"><i class="icon -material">find_in_page</i></a>'
									],
								]);
								return [
									$item->rcvNo,
									sg_date($item->rcvDate, 'ว ดด ปปปป'),
									$item->customerName,
									number_format($item->total,2)
									. $nav->build(),
								];
							},
							mydb::select(
								'SELECT * FROM %erp_rcv% WHERE `orgId` = :orgId ORDER BY `rcvId` DESC',
								[':orgId' => $this->orgId]
							)->items
						), // children
					]), // Table
					// new DebugMsg(mydb()->_query),
				], // children
			]), // Widget
		]);
	}
}
?>