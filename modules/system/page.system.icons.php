<?php
/**
* System  :: Icons
* Created :: 2025-06-21
* Modify  :: 2025-06-21
* Version :: 2
*
* @param String $args
* @return Widget
*
* @usage system/icons
*/

class SystemIcons extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Icons',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new ListTile(['title' => 'Icons', 'leading' => new Icon('push_pin')]),
					new ListTile(['title' => 'Button', 'leading' => new Icon('push_pin')]),
					new Container([
						'style' => 'display: flex; gap: 16px; padding: 16px 0; flex-wrap: wrap;',
						'children' => array_map(
							function ($type) {
								return new Button([
									'class' => '-no-wrap',
									'type' => $type,
									'icon' => new Icon('add_circle'),
									'text' => $type,
									// 'description' => 'กลับไปยังหน้ากองทุน',
								]);
							},
							['normal', 'primary', 'secondary', 'link', 'cancel', 'info', 'success', 'warning', 'danger', 'floating']
						),
					]), // Container

					// $icons = explode(',','edit,dropbox,person,person-add,people,people-add,delete,sort,icon,sortdesc,add,addbig,refresh,upload,lock,unlock,signin,cancel,closecircle,adddoc,view,viewdoc,search,image,camera,report,save,list,module,dashboard,download,print,home,calendar,setting,diagram,back,forward,up,down,visible,invisible,assignment,money,car,svg,done,redo,undo,walk,pin-drop,pin,close,notification,star,goods,nature-people,disabled-people,elder,rehabilitation,heart,doctor,share,chat,shopping-cart,description,favorite,thumbup,thumbdown,gps-fixed,help,clear,remove');

					// asort($icons);

					// foreach ($icons as $icon) {
					// 	$ret.='<div class="icons">';
					// 	$ret.='<div><a class="black" href="javascript:void(0)"><i class="icon -'.$icon.'""></i><span>icon -'.$icon.'</span></a></div>';
					// 	$ret.='<div><a class="gray" href="javascript:void(0)"><i class="icon -'.$icon.' -gray""></i><span>icon -'.$icon.' -gray</span></a></div>';
					// 	$ret.='<div><a class="white" href="javascript:void(0)"><i class="icon -'.$icon.' -white""></i><span>icon -'.$icon.' -white</span></a></div>';
					// 	$ret.='</div>'._NL;
					// }

					message(NULL, 'MESSAGE : Message detail'),
					message('error', 'ERROR : Error detail (Code : 404)'),
					message('notify', 'NOTIFY : Notify detail'),
					message('success', 'SUCCESS : Success detail'),

				], // children
			]), // Widget
		]);
	}
}
function xicons() {
	$ret='ICONs';


	$ret.='<a class="btn -primary" href="">Primary</a> <a class="btn -secondary" href="">Secondary</a> <a class="btn -success" href="">Success</a> <a class="btn -info" href="">Info</a> <a class="btn -warning" href="">Warning</a> <a class="btn -danger" href="">Danger</a> <a class="btn -floating" href="">Floating</a>';
	

	$ret.='<style type="text/css">
	.icons {display: flex;}
	.icons>div {margin-bottom:8px; width: 30%;}
	.icons>div>a>span {padding-left:8px;}
	.icons .black>i.icon {background-color:#ccc;}
	.icons .gray>i.icon {background-color:#e0e0e0;}
	.icons .white>i.icon {background-color:#1477D5;}
	.icon {border-radius:50%;}
	</style>';
	return $ret;
}
?>