<?php
/**
* iMed :: List All Visits
* Created 2022-02-21
* Modify  2022-02-21
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/visit/{id}/all
*/

import('model:imed.khonsongkhla.php');

class ImedVisitAll extends Page {
	var $psnId;
	var $action;
	var	$tranId;
	var $right;
	var $patientInfo;

	function __construct($patientInfo = NULL, $action = NULL, $tranId = NULL) {
		$this->psnId = $patientInfo->psnId;
		$this->patientInfo = $patientInfo;
		$this->action = post('action');
		$this->tranId = post('id');
		$this->right = (Object) [
			'edit' => is_admin('imed'),
		];
	}

	function build() {
		$khonSongkhlaModel = new ImedKhonsongkhlaModel();
		$refreshToken = $khonSongkhlaModel->refreshToken();
		// debugMsg($refreshToken, '$refreshToken');
		// if ($refreshToken->code) $khonSongkhlaModel->login();

		if ($this->action) {
			switch ($this->action) {
				case 'delete.service':
					if ($this->right->edit) {
						$khonSongkhlaModel->deletePublicService(['cid' => $this->patientInfo->info->cid, 'id' => $this->tranId]);
					}
					break;

				case 'delete.aid':
					if ($this->right->edit) {
						$khonSongkhlaModel->deleteAidService(['cid' => $this->patientInfo->info->cid, 'id' => $this->tranId]);
					}
					break;

			}
			return $result;
		}

		// debugMsg($khonSongkhlaModel, '$khonSongkhlaModel');
		// debugMsg($this,'$this');


		// debugMsg($khonSongkhlaModel->getPublicServiceList($this->patientInfo->info->cid), '$aid');

		$serviceList = $khonSongkhlaModel->getPublicServiceList($this->patientInfo->info->cid);
		if ($serviceList->code) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => $serviceList->message]);

		$aidList = $khonSongkhlaModel->getAidServiceList($this->patientInfo->info->cid);
		if ($aidList->code) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => $aidList->message]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รายการบริการ',
				'boxHeader' => true,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					// Show Service Card
					new ListTile([
						'title' => 'การรับบริการ',
						'leading' => '<i class="icon -material">stars</i>',
					]),
					new Container([
						'class' => '-sg-paddingnorm',
						'children' => array_map(
							function($visit) {
								return new Card([
									'style' => 'flex: 1 0 100%;',
									'children' => [
										new ListTile([
											'title' => '@'.$visit->date.' from '.$visit->source.' by '.$visit->serviceUnit,
											'trailing' => new DropBox([
												'children' => [
													$visit->source === 'scf' && $this->right->edit ? '<a class="sg-action" href="'.url('imed/visit/'.$this->psnId.'/all', ['action' => 'delete.service', 'id' => $visit->id]).'" data-rel="notify" data-done="remove:parent .widget-card" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">delete</i><span>DELETE!!!</a>' : NULL,
												]
											]),
										]),
										new Container([
											'class' => '-sg-paddingnorm',
											'children' => [
												$visit->description
											]
										]), // Container
										// new DebugMsg($visit, '$visit'),
									], // children
								]);
							},
							$serviceList
						), // children
					]), // Container

					// Show Aid Card
					new ListTile([
						'title' => 'การต้องการความช่วยเหลือ',
						'leading' => '<i class="icon -material">stars</i>',
					]),
					new Container([
						'class' => '-sg-paddingnorm',
						'children' => array_map(
							function($aid) {
								return new Card([
									'style' => 'flex: 1 0 100%;',
									'children' => [
										new ListTile([
											'title' => '@'.$aid->date.' from '.$aid->source.' by '.$aid->serviceUnit,
											'trailing' => new DropBox([
												'children' => [
													$aid->source === 'scf' && $this->right->edit ? '<a class="sg-action" href="'.url('imed/visit/'.$this->psnId.'/all', ['action' => 'delete.aid', 'id' => $aid->id]).'" data-rel="notify" data-done="remove:parent .widget-card" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">delete</i><span>DELETE!!!</a>' : NULL,
												]
											]),
										]),
										new Container([
											'class' => '-sg-paddingnorm',
											'children' => [
												$aid->description
											]
										]), // Container
										// new DebugMsg($aid, '$aid'),
									], // children
								]);
							},
							$aidList
						), // children
					]), // Container

				], // children
			]), // Widget
		]);
	}
}
?>