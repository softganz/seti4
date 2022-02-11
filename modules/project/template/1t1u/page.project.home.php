<?php
/**
* Project :: Home Page
* Created 2022-01-28
* Modify  2022-01-28
*
* @return Widget
*
* @usage module/{id}/method
*/

class ProjectHome extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ระบบบริหารโครงการยกระดับเศรษฐกิจ สังคม รายตำบลแบบบูรณาการ',
				'leading' => '<i class="icon -material">school</i>',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'title' => 'Project Management',
								'leading' => '<i class="icon -material">manage_accounts</i>'
							]),
							new Row([
								'class' => 'nav -app-menu',
								'children' => [
									'<a href="'.url('project/network').'"><i class="icon -material">device_hub</i><span>ระดับเครือข่าย</span></a>',
									'<a href="'.url('project/university').'"><i class="icon -material">school</i><span>ระดับมหาวิทยาลัย</span></a>',
									'<a href="'.url('project/tambon').'"><i class="icon -material">people</i><span>ระดับตำบล</span></a>',
									'<a href="'.url('project/employee').'"><i class="icon -material">groups</i><span>ระดับผู้รับจ้าง</span></a>',
								], // children
							]), // Row
						], // children
					]), // Card

					new Card([
						'children' => [
							new ListTile([
								'title' => 'กิจกรรมการจ้างงาน',
								'leading' => '<i class="icon -material">groups</i>'
							]),
							new Row([
								'class' => 'nav -app-menu',
								'children' => [
									'<a href="'.url('project/job/university').'"><i class="icon -material">people</i><span>กิจกรรมมหาวิทยาลัย</span></a>',
									'<a href="'.url('project/job/tambon').'"><i class="icon -material">people</i><span>กิจกรรมตำบล</span></a>',
									'<a href="'.url('project/job/employee').'"><i class="icon -material">groups</i><span>กิจกรรมผู้รับจ้าง</span></a>',
								], // children
							]), // Row
						], // children
					]), // Card

					new Card([
						'children' => [
							new ListTile([
								'title' => 'Report',
								'leading' => '<i class="icon -material">assessment</i>'
							]),
							new Row([
								'class' => 'nav -app-menu',
								'children' => [
									'<a href="'.url('project/report').'"><i class="icon -material">assessment</i><span>รายงาน</span></a>',
									'<a href="'.url('project/analyze').'"><i class="icon -material">insights</i><span>วิเคราะห์</span></a>',
								], // children
							]), // Row
						], // children
					]), // Card

					new Card([
						'children' => [
							new ListTile([
								'title' => 'Application',
								'leading' => '<i class="icon -material">apps</i>'
							]),
							new Row([
								'class' => 'nav -app-menu',
								'children' => [
									'<a href="'.url('project/app').'"><i class="icon -material">public</i><span>Web App</span></a>',
									'<a href="https://play.google.com/store/apps/details?id=com.softganz.otou" target="_blank"><i class="icon -material">phone_android</i><span>Android Mobile Phone App</span></a>',
								], // children
							]), // Row
						], // children
					]), // Card

				], // children
			]), // Widget
		]);
	}
}
?>