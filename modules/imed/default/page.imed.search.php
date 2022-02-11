<?php
/**
* iMed : Patient Search
* Created 2020-08-01
* Modify  2021-05-28
*
* @param String $ref
* @return Widget
*
* @usage imed/search
*/

$debug = true;

class ImedSearch extends Page {
	var $ref;
	var $id = 'imed-search';

	function __construct($ref = NULL) {
		$this->ref = SG\getFirst($ref,'app');
	}

	function build() {
		head('<style type="text/css">
			.toolbar.-main.-imed {border-bottom : 1px #eee solid; overflow: hidden;}
			.toolbar.-main.-imed .form {margin: 0; padding: 0; height: 47px; background-color: #fff;}
			.module-imed.-app .imed-search-patient .form-item.-edit-pn {padding: 5px 8px;}
			/*
			.form-item.-group>.input-prepend {border-radius: 32px 0 0 32px;}
			.form.-app-person-search .btn.-addnew {margin: 0; padding: 8px;}
			.form.-app-person-search .form-item.-group>.input-append {border: none; background-color: transparent;}
			.form.-app-person-search .form-item.-group>.input-append>span>.btn {margin-top: 4px;}
			.form.-app-person-search .form-item.-group>.input-prepend {border: none; background-color: transparent;}
			.form.-app-person-search .form-item.-group>.input-prepend>span>a {margin-top: 3px; margin-left: 6px;}
			*/
		</style>');

		$headerScript = '<script type="text/javascript">
		var canQuery = true
		var queryUrl = $("#edit-pn").data("query")

		function onWebViewComplete() {
			// console.log("CALL onWebViewComplete FROM WEBVIEW")
			var options = {title: "ค้นหา", actionBar: true}
			menu = []
			menu.push({id: "person_add", label: "เพิ่มชื่อผู้ป่วย", title: "เพิ่มชื่อผู้ป่วย", call: "addPatient", options: {actionBar: false}})
			//menu.push({id: "person", label: "Account", link: "imed/app/my/profile/info", title: "ACCOUNT", options: {actionBar: false}})
			options.menu = menu
			return options
		}

		function addPatient() {
			var initName = $("#edit-pn").val()
			var url = "'.url('imed/app/patient/add').'?initname=" + initName
			console.log("url = "+url)
			androidData = "{webviewTitle: \"เพิ่มชื่อผู้ป่วยรายใหม่\", options: {}}"
			if (typeof Android == "object") {
				var location = document.location.origin + url
				console.log("URL = "+location)
				Android.showWebView(location, androidData)
			} else {
				$("#edit-fullname").val(initName)
				$("#patient-list").hide()
				$("#partient-add-form").show()
			}
			return true
		}

		$(document).ready(function() {
			queryUrl = $("#edit-pn").data("query")
			$("#edit-pn").focus()
			$("form.-app-person-search").removeAttr("action")

			$(document).on("focus", "#edit-pn", function() {
				$("#patient-list").show()
				$("#partient-add-form").hide()
			});

			// On form submit
			$(document).on("submit", "form.-app-person-search", function() {
				para = {}
				para.q = $("#edit-pn").val()
				getPerson(queryUrl, para)

				$("#patient-list").show()
				$("#partient-add-form").hide()
				return false
			});

			// On key up
			$(document).on("keyup", "#edit-pn", function(event) {
				if (event.keyCode == 13) {
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
				para.q = $this.val()

				//console.log("GET "+para.q)

				canQuery = false

				if ($this.val() != "") {
					$("#imed-my-note").hide()
					$("#patient-list>p").hide()
					$(".imed-my-note-more").hide()
				} else {
					$("#imed-my-note").show()
					$("#patient-list>p").show()
					$(".imed-my-note-more").show()
				}

				getPerson(url, para)
			})

			// Get more item
			$(document).on("click", ".ui-item.-get-more", function() {
				para = {}
				para.q = $("#edit-pn").val()
				para.p = $(".-get-more").data("nextpage")
				$.post(queryUrl, para, function(persons) {
					$(".-get-more").remove()
					showPersonList(persons)
				}, "json")
			})

			// Get person from database
			function getPerson(queryUrl, para) {
				window.scrollTo(0, 0)
				$.post(queryUrl,para, function(persons) {
					$("#patient-list>.ui-card.-patient").empty()
					showPersonList(persons)
				}, "json")
				.done(function() {
					canQuery = true
				})
			}

			// Add person in list into card
			function showPersonList(persons) {
				var patientUrl = "'.url('imed/'.$this->ref.'/').'"
				$.each(persons, function(index,person){
					var cardStr = ""
					if (person.value == "...") {
						cardStr = "<div class=\"btn ui-item -get-more\" data-nextpage=\""+person.nextpage+"\" style=\"text-align: center; padding:16px 0; flex:1 0 100% !important\"><a>... ยังมีอีก ...</a></div>"
					} else {
						var patientName = person.prename + " " + person.label
						cardStr = "<a href=\"" + patientUrl + "/" + person.value + "\" class=\"ui-item sg-action\" data-webview=\"true\" data-webview-title=\"" + patientName + "\"><div class=\"header\"><b>" + patientName + "</b></div><div class=\"detail\"><p>" + person.desc + "</p></div></a>"
					}
					$("#patient-list>.ui-card.-patient").append(cardStr)
				})
			}
		});
		'._NL;

		$headerScript .= '</script>'._NL;

		head($headerScript);

		return new Container([
			'children' => [
				new Container([
					'id' => 'imed-chat-box',
					'class' => 'chat-box -imed-app-home',
					'children' => [
						'<img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;',
						new Form([
							//'action' => url('imed/app/person/search'),
							'action' => 'javascript:void(0)',
							'class' => 'xsg-form -app-person-search',
							'checkValid' => true,
							'rel' => '#patient-list',
							'data-silent' => true,
							'children' => [
								'pid' => ['type' => 'hidden', 'id' => 'pid'],
								'pn' => [
									'type' => 'text',
									'class' => '-fill',
									'value' => htmlspecialchars($getSearch),
									'autocomplete' => 'OFF',
									'attr' => [
										'data-query' => url('imed/api/person'),
										'data-altfld' => 'pid',
									],
									'placeholder' => 'ระบุ ชื่อ นามสกุล หรือ เลข 13 หลัก ของผู้ป่วย',
									'pretext' => '<div class="input-prepend">'
										. '<span><a class="btn" href="javascript:void(0)" onclick=\'$("#edit-pn").val("");\'><i class="icon -material -gray -sg-16">clear</i></a></span>'
										. '</div>',
									'posttext' => '<div class="input-append">'
										. '<span><button type="submit" class="btn"><i class="icon -material">search</i></button></span>'
										. '<span><a class="btn -add-new" href="'.url('imed/app/patient/add').'" onClick="addPatient(); return false;" title="เพิ่มชื่อผู้ป่วยรายใหม่" style="border-radius: 50%;"><i class="icon -material">person_add</i></a></span>'
										. '</div>',
									'container' => '{class: "-group"}',
								],
							], // children
						]), // Form
					], // children
				]), // Container

				'<div style="padding: 0px 8px 8px; text-align: center; font-size: 0.9em;">ค้นหา ชื่อ นามสกุล โดยไม่ต้องป้อนคำนำหน้านาม</div>'._NL,

				'<div id="patient-list" style="margin: 0; padding-top: 16px;">'._NL
				. '<div class="ui-card -patient -sg-flex -co-2" style="margin: 0;"></div>'._NL
				. '</div><!-- patient-list -->'._NL,

				'<div id="partient-add-form" class="-hidden">'.R::Page('imed.app.patient.add',NULL).'</div>'._NL,
			],
		]);
	}
}
?>