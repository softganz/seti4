<?php
/**
* Make Paper as Poll
* Created 2019-06-02
* Modify  2019-06-02
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_makepoll($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;

	$ret = '<header class="header -box"><nav class="nav -back"><a class="" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material">arrow_back</i></a></nav><h3>MAKE POLL</h3></header>';

	$ret .= '<h3>สร้างแบบสำรวจความคิดเห็น</h3>';

	$stmt = 'SELECT * FROM %poll_choice% WHERE `tpid` = :tpid ORDER BY `choice` ASC; -- {key: "choice"}';

	$dbs = mydb::select($stmt,':tpid',$tpid);


	$form = new Form('poll', url('paper/info/api/'.$tpid.'/poll.update'), 'edit-topic');

	for ($i = 1; $i <= 10; $i++) {
		$choice_name=$i;
		$form->addField(
				$i,
				array(
					'type' => 'text',
					'label' => 'ตัวเลือกที่ '.$i,
					'class' => '-fill',
					'value' => $dbs->items[$i]->detail,
				)
			);
	}

	$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			)
		);

	$ret .= $form->build();


	return $ret;
}
?>