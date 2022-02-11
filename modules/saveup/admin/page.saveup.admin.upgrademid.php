<?php
/**
 * Upgrade memer ID
 *
 * @return String
 */
function saveup_admin_upgrademid() {
	$dbs=mydb::select('SELECT mid,contact_id FROM %saveup_member%');
	foreach ($dbs->items as $rs) {
		if (strlen($rs->mid)<=3) {
			$oldmid=$rs->mid;
			$newmid='01-'.sprintf('%03d',$rs->mid);
			$stmt='UPDATE %saveup_member% SET mid="'.$newmid.'" WHERE mid="'.$oldmid.'" LIMIT 1;';
			mydb::query($stmt);
			$ret.=mydb()->_query.'<br />';
			$stmt='UPDATE %saveup_treat% SET mid="'.$newmid.'" WHERE mid="'.$oldmid.'"';
			mydb::query($stmt);
			$ret.=mydb()->_query.'<br />';
			$stmt='UPDATE %saveup_saving% SET mid="'.$newmid.'" WHERE mid="'.$oldmid.'"';
			mydb::query($stmt);
			$ret.=mydb()->_query.'<br />';
		}
		if ($rs->contact_id && strlen($rs->contact_id)<=3) {
			$newmid='01-'.sprintf('%03d',$rs->contact_id);
			$stmt='UPDATE %saveup_member% SET contact_id="'.$newmid.'" WHERE mid="'.$rs->mid.'" LIMIT 1;';
			mydb::query($stmt);
			$ret.=mydb()->_query.'<br />';
		}
	}
	$dbs=mydb::select('SELECT * FROM %saveup_line%');
	foreach ($dbs->items as $rs) {
		if (strlen($rs->mid)<=3) {
			$oldmid=$rs->mid;
			$newmid='01-'.sprintf('%03d',$rs->mid);
			$newlid='01-'.sprintf('%03d',$rs->lid);
			$newparent=$rs->parent==0?$rs->parent:'01-'.sprintf('%03d',$rs->parent);
			$stmt='UPDATE %saveup_line% SET mid="'.$newmid.'", lid="'.$newlid.'", parent="'.$newparent.'" WHERE mid="'.$oldmid.'";';
			mydb::query($stmt);
			$ret.=mydb()->_query.'<br />';
		}
	}
	return $ret;
}
?>