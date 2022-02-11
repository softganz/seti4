<?php
/**
* iMed :: Group Visit
* Created 2021-08-17
* Modify  2021-08-17
*
* @param Object $orgInfo
* @return Widget
*
* @usage imed/group/{id}/visit
*/

$debug = true;

import('model:imed.visit');
import('widget:imed.visits');


class ImedGroupVisit extends Page {
	var $refApp;
	var $orgId;
	var $orgInfo;
	var $urlView = 'imed/group/';
	var $urlPatientView;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->getStart = SG\getFirst(post('start'), 0);
		parent::__construct();
	}

	function build() {
		$defaults = '{debug:false, showEdit: true, page: "web"}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;

		$orgInfo = $this->orgInfo;
		$orgId = $orgInfo->orgid;

		if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

		$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
		$isMember = $isAdmin || $orgInfo->is->socialtype;
		$isRemovePatient = $isAdmin || in_array($orgInfo->is->socialtype,array('MODERATOR','CM'));
		$this->isCareManager = $isAdmin || in_array($isMember,array('CM','MODERATOR','PHYSIOTHERAPIST'));

		if (!($isAdmin || $isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');

		if ($this->getStart) return $this->_showVisits();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '@'.$this->orgInfo->name,
			]), // AppBar
			'body' => new Container([
				'tagName' => 'section',
				'id' => 'imed-group-visit',
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'title' => '@Visit of Group',
								'leading' => '<i class="icon -material">medical_services</i>',
							]), //
						], // children
					]), // Card
					$this->_showVisits(),
				], // children
			]), // Controller
		]);
	}

	function _showVisits() {
		$showItems = 10;
		$visits = ImedVisitModel::items(
			['orgId' => $this->orgId],
			[
				'start' => $this->getStart,
				'item' => $showItems,
				'debug' => false,
			]
		);
		return new Widget([
			'child' => new ImedVisitsWidget([
				'children' => $visits->items,
				'refApp' => $this->refApp,
				'urlMore' => $visits->items && $showItems == count($visits->items) ? url('imed/group/'.$this->orgId.'/visit',['start' => $this->start+count($visits->items)]) : NULL,
			]),
		]);
	}
}
?>