<?php
/**
* Show note of symbol
*
* @param String $symbol
* @param String $_REQUEST['msg'] Message to save
* @return String
*/
function set_note($self, $symbol = NULL) {
	if ($symbol && $_REQUEST['msg']) {
		$post = (object)post();
		$post->created = date('U');

		$stmt = 'INSERT INTO %setnote% SET `uid`=:uid, `symbol`=:symbol, `msg`=:msg, `created`=:created';

		mydb::query($stmt,':symbol',$symbol, ':uid',i()->uid,$post);

		$stmt = 'SELECT * FROM %setnote% WHERE `symbol`=:symbol AND `uid`=:uid ORDER BY `nid` DESC';

		$dbs = mydb::select($stmt,':symbol',$symbol, ':uid',i()->uid);

		foreach ($dbs->items as $key => $rs) {
			$ret.='<div><span class="timestamp postdate">@'.sg_date($rs->created,'d/m/Y H:i').'</span> '.sg_text2html(($symbol=='SET'?'<strong>'.$rs->symbol.'</strong>'.' ':'').$rs->msg).'</div>';
		}
		return $ret;
	}
	//$ret.='<h3>'.$symbol.' : Take notes</h3>';
	$ret.='<form id="set-note-post" method="post" action="'.url('set/note/'.$symbol).'"><div class="form-item"><label>บันทึก</label><textarea id="set-note-msg" name="msg" class="form-textarea" rows="3" cols="40" placeholder="เขียนบันทึก"></textarea></div><div class="form-item"><button id="set-note-submit" class="btn -primary">'.tr('Post').'</button></div></form>'._NL;
	$ret.='<div id="set-note-items" class="set-note-items">';

	mydb::where('`uid` = :uid ',':uid',i()->uid);
	if ($symbol == 'SET') {
		; // do nothing
	} else if ($symbol) {
		mydb::where('`symbol` = :symbol ',':symbol',$symbol);
	}
	$stmt = 'SELECT * FROM %setnote%
					%WHERE%
					ORDER BY `nid` DESC';

	$dbs = mydb::select($stmt);

	foreach ($dbs->items as $key => $rs) {
		$ret.='<div><span class="timestamp postdate">@'.sg_date($rs->created,'d/m/Y H:i').'</span> '.sg_text2html(($symbol=='SET'?'<strong>'.$rs->symbol.'</strong>'.' ':'').$rs->msg).'</div>';
	}
	$ret.='</div>';
	return $ret;
}
?>