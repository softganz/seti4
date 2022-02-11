<?php
/**
* iMed Need on web app
*
* @param Object $self
* @param Int $psnid
* @return String
*/
function imed_need_home($self) {
	$ret = R::View('imed.toolbox',$self,'iMed@ความต้องการ', 'need');


	$ret .= '<div class="imed-sidebar">'.R::View('imed.menu.main')->build().'</div>';


	$ret .= '<div id="imed-app" class="imed-app">'._NL;


	$headerUi = new Ui();
	//$headerUi->add('<a href=""><i class="icon -material">view_list</i><span class="-hidden">คงเหลือ</span></a>');

	$ret .= '<header class="header -imed-pocenter"><nav class="nav -back"><a class="" href="'.url('imed').'"><i class="icon -material">arrow_back</i></a></nav><h3>ความต้องการ</h3><nav class="nav">'.$headerUi->build().'</header>';



	$stmt = 'SELECT
			n.*
		, u.`username`, u.`name`, CONCAT(p.`name`," ",p.`lname`) `patient_name`
		, nt.`name` `needTypeName`
		FROM %imed_need% n
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %imed_stkcode% nt ON nt.`stkid` = n.`needtype`
		ORDER BY `needid` DESC';
	$dbs = mydb::select($stmt);


	$ui = new Ui('div','ui-card imed-my-note -need');
	$ui->addId('imed-my-note');

	foreach ($dbs->items as $rs) {
		$ui->add(R::View('imed.need.render',$rs), '{class: "-urgency-'.$rs->urgency.'", id: "noteUnit-'.$rs->seq.'"}');
	}
	$ret .= $ui->build(true).'<!-- imed-my-note -->';

	$ret .= '</div><!-- imed-app -->';

	return $ret;
}
?>