<?php
/**
* Project : Search
* Created 2021-02-19
* Modify  2021-02-19
*
* @param Object $self
* @return String
*
* @usage project/app/search
*/

$debug = true;

function project_app_search($self) {
	$toolbar = new Toolbar($self, 'ค้นหาโครงการ');
	$ret = '';

	$form = new Form(NULL,url('project/app/search'),NULL,'sg-form -app-project-search');
	$form->addConfig('method','get');
	$form->addData('checkvalid',true);
	//$form->addData('rel', '#patient-list');
	$form->addData('silent', true);
	$form->addField('pid',array('type' => 'hidden', 'id' => 'pid'));
	$form->addField(
		'pn',
		array(
			'type' => 'text',
			//'class' => 'sg-autocomplete -fill',
			'class' => '-fill',
			'value' => htmlspecialchars($getSearch),
			'autocomplete' => 'OFF',
			'attr' => array(
				'data-query' => url('project/api/follows', array('result' => 'autocomplete')),
				'data-altfld' => 'pid',
			),
			'placeholder' => 'ระบุชื่อโครงการ',
			'pretext' => '<div class="input-prepend">'
				. '<span><a class="btn" href="javascript:void(0)" onclick=\'$("#edit-pn").val("");\'><i class="icon -material -gray -sg-16">clear</i></a></span>'
				. '</div>',
			'posttext' => '<div class="input-append">'
				. '<span><a class="btn" href="javascript:void(0)"><i class="icon -material">search</i></a></span>'
				//. '<span><button type="submit" class="btn"><i class="icon -material">search</i></button></span>'
				. '</div>',
			'container' => '{class: "-group"}',
		)
	);

	$ret .= '<div id="project-chat-box" class="ui-card project-chat-box">'
		. '<div class="ui-item">'
		//. '<div><img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
		. $form->build()
		//. '</div>'
		. '</div>'
		. '</div>';

	$ret .= '<div id="patient-list" style="margin: 0; padding-top: 16px;">';
	$ret .= '<div class="ui-card -patient -sg-flex -co-2" style="margin: 0;"></div>';
	$ret .= '</div>';


	head('<style type="text/css">
		.toolbar.-main.-imed {border-bottom : 1px #eee solid; overflow: hidden;}
		.toolbar.-main.-imed .form {margin: 0; padding: 0; height: 47px; background-color: #fff;}
		.module-imed.-app .imed-search-patient .form-item.-edit-pn {padding: 5px 8px;}
		.form.-app-project-search {width: 100%;}
		/*
		.form-item.-group>.input-prepend {border-radius: 32px 0 0 32px;}
		.form.-app-project-search .btn.-addnew {margin: 0; padding: 8px;}
		.form.-app-project-search .form-item.-group>.input-append {border: none; background-color: transparent;}
		.form.-app-project-search .form-item.-group>.input-append>span>.btn {margin-top: 4px;}
		.form.-app-project-search .form-item.-group>.input-prepend {border: none; background-color: transparent;}
		.form.-app-project-search .form-item.-group>.input-prepend>span>a {margin-top: 3px; margin-left: 6px;}
		*/
	</style>');

	$headerScript = '<script type="text/javascript">
	var canQuery = true
	$(document).on("submit", "form.-app-project-search", function() {
		$("#patient-list").show()
		$("#partient-add-form").hide()
		window.scrollTo(0, 0)
	});

	$(document).on("keyup", "#edit-pn", function(event) {
		console.log($(this).val())
		if (event.keyCode == 13) {
			//console.log("ENTER")
			window.scrollTo(0, 0)
			$(this).closest("form").submit()
			return false
		}

		if (!canQuery) {
			console.log("NOT QUERY "+$(this).val())
			return false
		}

		var $this = $(this)
		var url = $this.data("query")
		para = {}
		para.type = "all"
		para.title = $this.val()
		para.items = 200

		//console.log(para)

		canQuery = false

		getProject(url, para)
	})

	$(document).on("click", ".ui-item.-get-more", function() {
		console.log("GET MORE")
		var $this = $("#edit-pn")
		var url = $this.closest("form").attr("action")
		para = {}
		para.title = $this.val()
		para.p = $(".-get-more").data("nextpage")
		console.log(para)
		$.post(url, para, function(html) {
			console.log(html)
			$(".-get-more").remove()
			$("#patient-list").append(html)
		})
	})

	function getProject(url, para) {
		var viewProjectUrl = "'.url('project/app/follow/').'"
		window.scrollTo(0, 0)
		$.post(url,para, function(data) {
			//console.log(data)
			$("#patient-list>.ui-card.-patient").empty()
			$.each(data, function(index,value){
				//console.log(value)
				var cardStr = ""
				if (value.value == "...") {
					cardStr = "<div class=\"ui-item -get-more\" data-nextpage=\""+value.nextpage+"\" style=\"text-align: center; padding:16px 0; flex:1 0 100%\"><a>... ยังมีอีก ...</a></div>"
				} else {
					var projectName = value.label
					cardStr = "<a class=\"ui-item sg-action\" href=\"" + viewProjectUrl + "/" + value.value + "\" data-webview=\"" + projectName + "\" target=\"_blank\"><div class=\"header\"><b>" + projectName + "</b></div><div class=\"detail\"><p>" + value.desc + "</p></div></a>"
				}
				$("#patient-list>.ui-card.-patient").append(cardStr)
			})
		}, "json")
		.done(function() {
			canQuery = true
		})
	}

	'._NL;

	$headerScript .= '</script>'._NL;

	head($headerScript);

	return $ret;
}
?>