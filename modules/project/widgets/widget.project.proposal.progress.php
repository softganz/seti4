<?php
/**
* Module :: Description
* Created 2021-11-06
* Modify 	2021-11-06
*
* @param Array $args
* @return Widget
*
* @usage new ProjectProposalProgressWidget([])
*/

$debug = true;

class ProjectProposalProgressWidget extends Widget {
	var $projectId;
	var $proposalInfo;

	function __construct($proposalInfo = []) {
		$this->projectId = $proposalInfo->projectId;
		$this->proposalInfo = $proposalInfo;
	}

	function build() {
		$state2Active = in_array($this->proposalInfo->info->status,[2,3,5]) || $this->proposalInfo->followId;
		$state3Active = in_array($this->proposalInfo->info->status,[3]);
		$state4Active = in_array($this->proposalInfo->info->status,[5]) || $this->proposalInfo->followId;
		$state5Active = $this->proposalInfo->followId;
		$notPass = in_array($this->proposalInfo->info->status,[8,9]);

		return new Nav([
			'class' => 'progress-bar -no-print',
			'children' => [
				'<a class="status -s1 -active" href="javascript:void(0)" data-tooltip="ข้อเสนอ"><i class="icon -material">description</i></a>',
				'<a class="status -s2'.($state2Active ? ' -active' : '').'" data-tooltip="พิจารณา"><i class="icon -material">grading</i></a>',
				'<a class="status -s3'.($state3Active ? ' -active' : '').'" data-tooltip="ปรับแก้"><i class="icon -material">edit</i></a>',
				'<a class="status -s4'.($state4Active ? ' -active' : '').($notPass ? ' -cancel' : '').'" data-tooltip="'.($notPass ? 'ไม่' : '').'ผ่านการพิจารณา"><i class="icon -material">verified</i></a>',
				'<a class="status -s5'.($state5Active ? ' -active' : '').'" '.($state5Active ? 'href="'.url('project/'.$this->projectId).'"' : '').' data-tooltip="ติดตาม"><i class="icon -material">directions_run</i></a>',
			], // children
		]);
	}
}
?>