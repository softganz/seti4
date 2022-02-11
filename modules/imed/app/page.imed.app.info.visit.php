<?php
/**
* iMed :: App Patient Visit Form and List
* Created 2019-03-12
* Modify  2021-09-07
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/app/{id}/info.visit
*/

$debug = true;

class ImedAppInfoVisit extends Page {
	var $psnId;
	var $patientInfo;
	function __construct($patientInfo = NULL) {
		$this->psnId = $patientInfo->psnId;
		$this->patientInfo = $patientInfo;
	}

	function build() {
		if (!$this->psnId) return message(['responseCode' => _HTTP_OK_NO_CONTENT, 'text' => 'ไม่มีข้อมูลผู้ป่วย']);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->patientInfo->info->realname,
				'removeOnApp' => true,
			]), // AppBar
			'children' => [
				new Form([
					'action' => url('imed/api/visit/create'),
					'class' => 'sg-form -imed-visit',
					'rel' => 'none',
					'checkValid' => true,
					'done' => 'callback:imedInfoVisitDone',
					'children' => [
						'service' => ['type' => 'hidden', 'value' => 'Home Visit',],
						'psnId' => ['type' => 'hidden', 'value' => $this->psnId],
						'msg' => [
							'type'=>'textarea',
							'label' => 'ข้อความเยี่ยมบ้าน',
							'class' => '-fill',
							'require' => true,
							'rows' => 4,
							'placeholder' => 'เขียนบันทึกข้อความในการเยี่ยมบ้าน',
							'container' => '{class: "-label-in"}',
						],
						'timedata' => [
							'label' => 'วันที่เยี่ยมบ้าน',
							'type' => 'text',
							'class' => 'sg-datepicker -fill',
							'require' => true,
							'readonly' => true,
							'value' => sg_date(SG\getFirst($data->timedata,date('U')),'d/m/Y'),
							'container' => '{class: "-label-in"}',
						],
						'go' => [
							'type' => 'button',
							'name' => NULL,
							'value' => '<i class="icon -save -white"></i><span>โพสท์เยี่ยมบ้าน</span>',
							'container' => '{class: "-sg-text-right"}',
						],
						'<p class="remark">** บันทึกข้อความในการเยี่ยมบ้าน ภาพถ่ายและข้อมูลประกอบการเยี่ยมบ้าน จะแสดงให้เห็นเฉพาะสมาชิกของกลุ่มและผู้ที่ได้รับสิทธิ์ในการดูแลผู้ป่วยในตำบล อำเภอ จังหวัด ของผู้ป่วยเท่านั้น กรุณาใช้ข้อความที่สุภาพ รักษาสิทธิ์และความเป็นส่วนตัวของผู้ป่วยตามแนวทางในการรักษาข้อมูลส่วนบุคคลของผู้ป่วย **</p>',
					],
				]), // Form

				new ListTile([
					'title' => '<h4>ประวัติการเยี่ยมบ้าน</h4>',
					'leading' => '<i class="icon -material">medical_services</i>',
				]), // ListTile

				'<div id="imed-my-note" class="sg-load" data-url="'.url('imed/visits', ['pid' => $this->psnId, 'ref' => 'app']).'" data-replace="true">'._NL
					. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 48px auto; display: block;"></div>'
					. '</div><!-- imed-my-note -->',

				$this->_script(),
			],
		]);
	}

	function _script() {
		head(
		'<style style="text/css">
		.form.-imed-visit {}
		/*
		.form-textarea {height: 24px;}
		.form.-imed-visit .form-item.-edit-timedata {display: none;}
		.form.-imed-visit .form-item.-edit-go {display: none;}
		.form.-imed-visit .remark {display: none;}
		.form.-imed-visit:focus {background-color: red;}
		.form.-imed-visit:focus-within .form-textarea {height: 86px;}
		.form.-imed-visit:focus-within>.form-item:not(.-hidden),.form.-imed-visit:focus-within>.remark {display: block;}
		*/
		</style>
		<script type="text/javascript">
		function imedInfoVisitDone($this, data) {
			// console.log(data)
			$(".sg-form.-imed-visit").trigger("reset")
			$("#edit-msg").val("")
			$("#edit-visittype").val("")
			// $(":focus").blur()

			// Add visit top top of visit list
			if (data.seqId) {
				let url = "'.url('imed/visit/'.$this->psnId.'/item/').'" + "/" + data.seqId
				let para = []
				para.ref = "app"
				$.get(url, para, function(html) {
					$("#imed-visits").prepend(html)
				})
				$.get("'.url('imed/api/firebase/visitAdd').'", data, function(html) {
				})
			}
		}
		</script>
		');

		$script = '';
		// Show update visit card when data change
		if (cfg('firebase') && (cfg('imed.visit.realtime.change.member') == 'all' || (cfg('imed.visit.realtime.change.member') == 'admin' && is_admin('imed')))) {

			$script .= '<script type="text/javascript">
			$(document).ready(function() {
				if (!firebaseConfig) return

				let psnId = '.$this->psnId.'
				let uid = '.i()->uid.'
				let drawUrl = "'.url('imed/visit/'.$this->psnId.'/item').'"
				let database = firebase.database()
				let ref = database.ref(firebaseConfig.visit)
				var i = 0

				var getCurrentTimestamp = (function() {
					var OFFSET = 0
					database.ref("/.info/serverTimeOffset").on("value", function(ss) {
						OFFSET = ss.val() || 0
					});
					return function() { return Date.now() + OFFSET }
				})();

				var now = getCurrentTimestamp()


				// Show new visit card on member create new visit

				let newItems = false

				// console.log("Monitor ", firebaseConfig.visit, now)

				ref
				.orderByChild("psnid")
				.equalTo(psnId)
				.on("child_added", snap => {
					// console.log("newItem ", newItem)
					if (!newItems) return
					$.post(drawUrl + "/" + snap.key, {ref: "app"}, function(html) {
						$("#imed-visits").prepend(html)
					})
					// console.log(++i + " : NEW VISIT " + snap.key, snap.val())
				});

				ref.once("value", () => { newItems = true });

				ref
				.orderByChild("psnid")
				.equalTo(psnId)
				.on("child_changed",function(snap){
					$.post(drawUrl + "/" + snap.key, {ref: "app"}, function(html) {
						$("#imed-visit-"+snap.key).replaceWith(html)
					});
					// console.log(++i + " : CHANGE VISIT " + snap.key, snap.val())
				});
			})
			</script>';
		}
		return $script;
	}
}
?>