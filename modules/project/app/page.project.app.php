<?php
/**
* Project :: App Home Pahe
* Created 2019-10-27
* Modify  2021-09-08
*
* @param String $arg1
* @return Widget
*
* @usage project/app
*/

$debug = true;

class ProjectApp extends Page {
	var $userId;
	function __construct() {
		$this->userId = post('u');
	}

	function build() {
		$isDevVersion = true; //in_array(i()->username, explode(',',cfg('green.useDevVersion')));
		$isShowAll = true;

		if ($this->userId) $isShowAll = false;

		return new Scaffold([
			'body' => new Widget([
				'children' => [
					// Activity Post Button
					new Card([
						'id' => 'project-chat-box',
						'class' => 'ui-card project-chat-box',
						'children' => [
							'<div class="ui-item">'
							. '<div><img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
							. '<a class="sg-action form-text" href="'.url('project/app/action/form').'" placeholder="เขียนบันทึกการทำกิจกรรม" data-rel="box" data-width="full" data-height="100%" data-webview="บันทึกการทำกิจกรรม">เขียนบันทึกการทำกิจกรรม</a>&nbsp;'
							. '<a class="sg-action btn -link" href="'.url('project/app/action/form').'" data-rel="box" data-width="full" data-height="100%" data-webview="บันทึกการทำกิจกรรม"><i class="icon -camera"></i><span>Photo</span></a></div>'
							. '</div>',
						],
					]), // Card

					// Show project activity
					'<section id="project-activity-card" class="sg-load" data-url="'.url('project/app/activity', ['u' => $this->userId]).'">'._NL
					. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 32px auto; display: block;"></div>'
					. '</section><!-- project-app-activity -->',
				], // children
			]), // Widget
		]);
	}
}
?>