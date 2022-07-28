<?php
/**
* SOFTGANZ :: Page admin/ftp
*
* Copyright (c) 2000-2006 The SoftGanz Group By Panumas Nontapan
* Authors : Panumas Nontapan <webmaster@softganz.com>
*             : http://www.softganz.com/
* ============================================
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*/
/**
--- Created 2007-02-21
--- Modify   2017-07-31
*/

/**
๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏
๏  Page  :: admin/ftp
๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏๏
*/
function admin_ftp($self) {

	$self->theme->title='Transfer File Between Host Version 1.10';
	// set up basic connection
	$rServer=$_POST['rs'];
	$rName=$_POST['rn'];
	$rPass=$_POST['rp'];
	$rFolder = SG\getFirst($_POST['rf'],'.');
	$rcd = $_POST['rcd'];
	$remoteSelectFolder=$_POST['rsfolder'];
	$remoteSelectFile=$_POST['rsfile'];
	$rPort=SG\getFirst($_POST['rport'],21);

	$lServer=$_POST['ls'];
	$lName=$_POST['ln'];
	$lPass=$_POST['lp'];
	$lFolder = SG\getFirst($_POST['lf'],'.');
	$lcd = $_POST['lcd'];
	$localSelectFolder=$_POST['lsfolder'];
	$localSelectFile=$_POST['lsfile'];
	$lPort=SG\getFirst($_POST['lport'],21);

	$simulate=$_POST['simulate'] ? true:false;

	$error=array();

	if ($lServer && $lName && $lPass) {
		$lConn = $lPort==22? ftp_ssl_connect($lServer,$lPort) : ftp_connect($lServer,$lPort);
		if ($lConn) {
			// login with username and password
			if ($lLogin=@ftp_login($lConn, $lName, $lPass)) {
				if ($lFolder) {
					// change directory to public_html
					ftp_chdir($lConn, $lFolder);
					if ($lcd) ftp_chdir($lConn, $lcd);
					$currentLocalFolder=ftp_pwd($lConn);
				}
			} else {
				$error['local'][]='Login with username/password error';
			}
		} else {
			$error['local'][]='FTP connection has failed while attempted to connect to '.$lServer;
		}
	}

	if ($rServer && $rName && $rPass) {
		$rConn = $rPort==22? ftp_ssl_connect($rServer,$rPort) :  ftp_connect($rServer,$rPort);
		if ($rConn) {
			// login with username and password
			if($rLogin = @ftp_login($rConn, $rName, $rPass)) {
				if ($rFolder) {
					// change directory to public_html
					ftp_chdir($rConn, $rFolder);
					if ($rcd) ftp_chdir($rConn, $rcd);
					$currentRemoteFolder=ftp_pwd($rConn);
				}
			} else {
				$error['remote'][]='Login with username/password error';
			}
		} else {
			$error['remote'][]='FTP connection has failed while attempted to connect to '.$rServer;
		}
	}


	if ($_POST['copytolocal'] && $remoteSelectFile && $rConn && $rLogin && $lConn && $lLogin) {
		$result.='Connected to '.$rName.'@'.$rServer.$currentRemoteFolder.'<br />'._NL;
		$result.=__admin_ftp_transfer($rConn,$currentRemoteFolder,$lConn,$currentLocalFolder,$remoteSelectFile,$simulate);
	} else if ($_POST['copytoremote'] && $localSelectFile && $rConn && $rLogin && $lConn && $lLogin) {
		$result.='Connected to '.$lName.'@'.$lServer.$currentLocalFolder.'<br />'._NL;
		$result.=__admin_ftp_transfer($lConn,$currentLocalFolder,$rConn,$currentRemoteFolder,$localSelectFile,$simulate);
	} else if ($_POST['mkfolder'] && $_POST['makefolder'] && $lConn && $lLogin) {
		ftp_mkdir($lConn,$_POST['mkfolder']);
		if ($_POST['mkfolder_perm']) {
			$result.='Change folder permission to '.$_POST['mkfolder_perm'].' ('.octdec($_POST['mkfolder_perm']).')';
			ftp_chmod($lConn, octdec($_POST['mkfolder_perm']), $_POST['mkfolder']);
		}
	} else if ($_POST['delete']) {
		if ($localSelectFile) {
			$result.='Delete file '.implode(',', $localSelectFile);
			foreach ($localSelectFile as $file) {
			 	ftp_delete($lConn,$file);
			 }
			}
	}




	$ret.= '<form id="ftp" class="admin-ftp-form" method="post" action="'.url('admin/ftp').'">
		<table class="admin-ftp-result" width="100%" border="0">
		<thead><tr><th width="45%">Local Server</th><td width="10%"></td><th width="45%">Remote Server</th></tr>
		<tr valign="top">
		<td>
		<div class="form-item"><label>Ftp server</label><input class="form-text -fill" type="text" name="ls" size="30" value="'.$_POST['ls'].'"></div>
		<div class="form-item"><label>Port</label><input class="form-text -fill" type="text" name="lport" size="30" value="'.$_POST['lport'].'"></div>
		<div class="form-item"><label>Username</label><input class="form-text -fill" type="text" name="ln" size="30" value="'.$_POST['ln'].'"></div>
		<div class="form-item"><label>Password</label><input class="form-text -fill" type="password" name="lp" size="30" value="'.$_POST['lp'].'"></div>
		</td>
		<td></td>
		<td>
		<div class="form-item"><label>Ftp server</label><input class="form-text -fill" type="text" name="rs" size="30" value="'.$_POST['rs'].'"></div>
		<div class="form-item"><label>Port</label><input class="form-text -fill" type="text" name="rport" size="30" value="'.$_POST['rport'].'"></div>
		<div class="form-item"><label>Username</label><input class="form-text -fill" type="text" name="rn" size="30" value="'.$_POST['rn'].'"></div>
		<div class="form-item"><label>Password</label><input class="form-text -fill" type="password" name="rp" size="30" value="'.$_POST['rp'].'"></div>
		</td>
		</tr>
		</thead>
		<tbody>
		';

	$ret.='<tr>';
	$ret.='<td><div class="form-item"><label>Folder</label><input class="form-text -fill" type="text" name="lf" size="30" value="'.$currentLocalFolder.'"></div><input type="hidden" name="lcd" id="lcd" value="" /></td>';
	$ret.='<td></td>';
	$ret.='<td><div class="form-item"><label>Folder</label><input class="form-text -fill" type="text" name="rf" size="30" value="'.$currentRemoteFolder.'"></div><input type="hidden" name="rcd" id="rcd" value="" /></td>';
	$ret.='</tr>';



	$ret.='<tr><td colspan="3"><div id="result">'.$result.'</div></td></tr>
		<tr valign="top">
		<td>';
	if ($error['local']) $ret.='<ul class="error"><li>'.implode('</li><li>',$error['local']).'</li></ul>';


	// Local File & Folder
	$tables = new Table();
	$tables->id='local';
	$tables->thead=array('','Name','Size','Date','Permissions');
	if ($lConn && $lLogin) {
		list($folders,$links,$files)=__admin_ftp_get_files($lConn, $currentLocalFolder);
		//			$ret.=print_o($folders,'$folders').print_o($links,'$links').print_o($files,'$files');
		$tables->rows[]=array('','<a class="folder" href="#up">..</a>','','','');
		foreach ($folders as $folder) {
			$tables->rows[]=array(
												'<input type="checkbox" name="lsfolder[]" '.(in_array($folder['name'],$localSelectFolder)?'checked="checked"':'').' value="'.$folder['name'].'" />',
												'<a class="folder" href="#'.$folder['name'].'">'.$folder['name'].'</a>',
												'',
												$folder['month'].' '.$folder['day'].','.$folder['time'],
												$folder['perms']
												);
		}
		foreach ($files as $file) {
			$tables->rows[]=array(
												'<input type="checkbox" name="lsfile[]" '.(in_array($file['name'],$localSelectFile)?'checked="checked"':'').' value="'.$file['name'].'" />',
												'<a class="file" href="'.$file['name'].'">'.$file['name'].'</a>',
												number_format($file[size]),
												$file['month'].' '.$file['day'].','.$file['time'],
												$file['perms']
												);
		}
	}
	$ret.= $tables->build();
	//$ret.=print_o($_POST,'$_POST');
	$ret.='<div class="form-item"><button class="btn -danger" type="submit" name="delete" value="Delete file"><i class="icon -material">delete</i><span>Delete Select File</span></button></div>';
	$ret.='<div class="form-item" style="margin-top:64px;"><label>Create Folder name</label><input type="text" name="mkfolder" placeholder="Enter folder name" /></div><div class="form-item"><label>Folder Permission</label><input type="text" name="mkfolder_perm" placeholder="Enter permission" /></div><div class="form-item"><button class="btn" type="submit" name="makefolder" value="Make folder"><i class="icon -material">add</i><span>Make Folder</span></button></div>
	</td>
	<td align="center">
		<div class="form-item"><button id="refresh" class="btn -primary -nowrap -fill" type="submit" name="refresh" value="refresh"><i class="icon -material">refresh</i><span>Refresh</span></button></div>
		<div class="form-item"><button class="btn -nowrap" type="submit" name="copytoremote" value=" >> "><span>Copy to Right</span><i class="icon -material">navigate_next</i></button></div>
		<div class="form-item"><button class="btn -nowrap" type="submit" name="copytolocal" value=" << "><i class="icon -material">navigate_before</i><span>Copy to Left</span></button></div>
		<div class="form-item"><input type="checkbox" name="simulate" '.($_POST['simulate']?'checked':'').'> Simulate</div>
	</td>
	<td>';


	// Remote File & Folder
	if ($error['remote']) $ret.='<ul class="error"><li>'.implode('</li><li>',$error['remote']).'</li></ul>';

	$tables = new Table();
	$tables->id='remote';
	$tables->thead=array('','Name','Size','Date','Permissions');
	if ($rConn && $rLogin) {
		list($folders,$links,$files)=__admin_ftp_get_files($rConn, $currentRemoteFolder);
			//echo print_o($folders,'$folders').print_o($links,'$links').print_o($files,'$files');
		$tables->rows[]=array('','<a class="folder" href="#up">..</a>','','','');
		foreach ($folders as $folder) {
			$tables->rows[]=array(
												'<input type="checkbox" name="rsfolder[]" '.(in_array($folder['name'],$remoteSelectFolder)?'checked="checked"':'').' value="'.$folder['name'].'" />',
												'<a class="folder" href="#'.$folder['name'].'">'.$folder['name'].'</a>',
												'',
												$folder['month'].' '.$folder['day'].','.$folder['time'],
												$folder['perms']
												);
		}
		foreach ($files as $file) {
			$tables->rows[]=array(
												'<input type="checkbox" name="rsfile[]" '.(in_array($file['name'],$remoteSelectFile)?'checked="checked"':'').' value="'.$file['name'].'" />',
												'<a class="file" href="#'.$file['name'].'">'.$file['name'].'</a>',
												number_format($file[size]),
												$file['month'].' '.$file['day'].','.$file['time'],
												$file['perms']
												);
		}
	}
	$ret.= $tables->build();
	$ret.= '</td></tr>';


	$ret.='</tbody></table>'._NL;
	$ret.= '</form>';
	flush();

	$ret.= _NL;
	head('<style type="text/css">
			.admin.-ftp #content-wrapper.page.-content {max-width:none;}
			.admin-ftp-result thead th {background:#ccc;padding:8px;}
		</style>');
	head('<script type="text/javascript">
		$(document).ready(function() {
			/*
			//TODO : Bug cannot submit
			$(document).on("submit","form#ftp",function(html) {
				var $this=$(this);
				console.log("Form Submit "+$this.attr("action"))
				$.post($this.attr("action"),$this.serialize(),function(html) {
					//console.log(html);
					$this.replaceWith(html);
					//$("#result>tbody").html(html);
				});
				return false;
			});
			*/

			$(document).on("click","a.folder",function() {
				//console.log("Folder click");
				var $this=$(this);
				var ftpSide=$this.closest("table").attr("id");
				if (ftpSide=="local") $("#lcd").val($this.attr("href").slice(1));
				else if (ftpSide=="remote") $("#rcd").val($this.attr("href").slice(1));
				//console.log("Click "+$this.attr("href").slice(1));
				$("form#ftp #refresh").trigger("click");
				return false;
			});
		});
		</script>');
	return $ret;
	}

function __admin_ftp_parse_rawlist( $array ) {
	foreach($array as $curraw) {
		$struc = array();
		$current = preg_split("/[\s]+/",$curraw,9);

		$struc['perms']  = $current[0];
		$struc['number'] = $current[1];
		$struc['owner']  = $current[2];
		$struc['group']  = $current[3];
		$struc['size']  = $current[4];
		$struc['month']  = $current[5];
		$struc['day']    = $current[6];
		$struc['time']  = $current[7];
		$struc['year']  = $current[8];
		$struc['raw']  = $curraw;
		$structure[$struc['name']] = $struc;
	}
	return $structure;

	}

function __admin_ftp_get_files($conection,$path = '.') {
	$array = ftp_rawlist($conection, $path);
	if (is_array($array))
	foreach ($array as $folder) {
		$struc = array();
		$current = preg_split("/[\s]+/",$folder,9);

		$struc['perms']	= $current[0];
		$struc['permsn']	= __admin_ftp_chmodnum($current[0]);
		$struc['number']	= $current[1];
		$struc['owner']		= $current[2];
		$struc['group']		= $current[3];
		$struc['size']		= intval($current[4]);
		$struc['month']	= $current[5];
		$struc['day']		= $current[6];
		$struc['time']		= $current[7];
		$struc['name']		= str_replace('//','',$current[8]);
		$struc['raw']		= $folder;

		if ($struc['name'] == '.') continue;
		if ($struc['name'] == '..') continue;
		else if (__admin_ftp_get_type($struc['perms']) == "folder") $folders[] = $struc;
		elseif (__admin_ftp_get_type($struc['perms']) == "link") $links[] = $struc;
		else $files[] = $struc;
	}
	return array($folders,$links,$files);
}

function __admin_ftp_chmodnum($mode) {
     $realmode = "";
     $legal =  array("","w","r","x","-");
     $attarray = preg_split("//",$mode);
     for($i=0;$i<count($attarray);$i++){
         if($key = array_search($attarray[$i],$legal)){
             $realmode .= $legal[$key];
         }
     }
     $mode = str_pad($realmode,9,'-');
     $trans = array('-'=>'0','r'=>'4','w'=>'2','x'=>'1');
     $mode = strtr($mode,$trans);
     $newmode = '';
     $newmode .= $mode[0]+$mode[1]+$mode[2];
     $newmode .= $mode[3]+$mode[4]+$mode[5];
     $newmode .= $mode[6]+$mode[7]+$mode[8];
     return $newmode;
 }

function __admin_ftp_get_type($perms) {
	if (substr($perms, 0, 1) == "d") {
		return 'folder';
	} elseif (substr($perms, 0, 1) == "l") {
		return 'link';
	} else {
		return 'file';
	}
}

function __admin_ftp_transfer($rConn,$currentRemoteFolder,$lConn,$currentLocalFolder,$selectFile=array(),$simulate=false) {
	$tmpFolder='/tmp/';
	// get file & folder content
	//		list($folders,$links,$files)=__admin_ftp_get_files($rConn, $rFolder);

	//		$ret.='<p><font color=#005C8F>transfer <b>'.count($files).'</b> files from folder '.$rFolder.' => '.$local_folder.'</font><br />'._NL;

		// process each file
	//$ret.=print_o($selectFile,'$selectFile');
	if (!$selectFile) return $ret;
	$no=0;
	foreach ($selectFile as $file) {
		$filename=$file;
		$remoteFile=$currentRemoteFolder.'/'.$filename;
		$localFile=$tmpFolder.$filename;
		$ret.=++$no.'. '.($simulate?'Will ':'').'Copy '.$remoteFile.' => '.$localFile;

		// start transfer file
		if (!$simulate) {
			if (ftp_get($rConn, $localFile, $remoteFile, FTP_BINARY)) { //Ftp get function which will download file
				$ret.=' (successfull)';
				$ret.=' => Transfer to '.$currentLocalFolder.'/'.$file;
				if ($upload = ftp_put($lConn, $currentLocalFolder . "/" . $file, $localFile, FTP_BINARY)) {
					$ret.=' (successfull)';
				} else $et.=' (error)';
				unlink($localFile);
			} else $ret.=' (error)';
			$ret.='.<br />';
			continue;

			//				echo 'copy '.$server_file.' => '.$local_file.'<br />';

			/*
			// check local folder
			if (!file_exists($local_folder) || !is_dir($local_folder)) {
				echo ' <font color="red">error because local folder not exists.</font>';
				continue;
			}
			*/
			//check same file size
			if (file_exists($local_file) && ($local_file_size=filesize($local_file)) && $file['size']===$local_file_size) {
				//  get & change the last modified time
				$last_modify_time = ftp_mdtm($rConn, $server_file);
				if ($last_modify_time != -1) touch($local_file,$last_modify_time);

				$permission=cfg('upload.file.chmod')?cfg('upload.file.chmod'):octdec($file['permsn']);
				echo ' [perm '.$permission.']';
				chmod($local_file,$permission);

				echo ' <span style="color:#f60;">file exists</span><br />';
				continue;
			}
			flush();
			// try to download $server_file and save to $local_file
			if (ftp_get($rConn, $local_file, $server_file, FTP_BINARY)) {
				$permission=cfg('upload.file.chmod')?cfg('upload.file.chmod'):octdec($file['permsn']);
				echo ' [perm '.$permission.']';
				chmod($local_file,$permission);
				//  get & change the last modified time
				$last_modify_time = ftp_mdtm($rConn, $server_file);
				if ($last_modify_time != -1) touch($local_file,$last_modify_time);

				echo ' <font color="green">complete.</font>';
			} else {
				echo ' <font color="red">error.</font>';
			}

		}

		$ret.='<br />'._NL;
		flush();
	}
	$ret.='</p>'._NL;

	/*
	// process each sub folder
	if ($folders) {
		foreach ($folders as $folder) {
			$folder_name=$folder['name'];
			$copy_to_folder=$local_folder.'/'.$folder_name;
			$permission=cfg('upload.folder.chmod')?cfg('upload.folder.chmod'):octdec($folder['permsn']);
			if (!$simulate && !file_exists($copy_to_folder)) {
				if (mkdir($copy_to_folder)) {
					echo '<p>Create folder <em>'.$copy_to_folder.' [perm '.$permission.']</em></p>';
				} else {
					echo '<font color=red>cannot create folder <b>'.$copy_to_folder.'</b></font>';
				}
			}
			chmod($copy_to_folder,$permission);
			__admin_ftp_transfer($rConn,$rFolder.'/'.$folder_name,$copy_to_folder,$simulate);
		}
	}
	*/
	return $ret;
}
?>
