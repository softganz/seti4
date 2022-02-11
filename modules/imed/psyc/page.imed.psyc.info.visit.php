<?php
/**
* iMed :: Patient Home Visit
* Created 2021-05-27
* Modify  2021-08-29
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/psyc/{id}/info.visit
*/

$debug = true;

import('widget:imed.qt.button');

class ImedPsycInfoVisit {
	var $psnId;
	var $patientInfo;
	var $isAccess;

	function __construct($patientInfo = NULL) {
		$this->psnId = $patientInfo->psnId;
		$this->patientInfo = $patientInfo;
	}

	function build() {
		$psnInfo = $this->patientInfo;
		$uid = i()->uid;
		$this->isAccess = $psnInfo->RIGHT & _IS_ACCESS;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $psnInfo->info->fullname,
				'removeOnApp' => true,
			]), // AppBar
			'body' => new Container([
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'title' => 'เขียนบันทึกการเยี่ยมบ้าน',
								'leading' => '<i class="icon -material">medical_services</i>',
							]),
							new ScrollView([
								'children' => [
									new Row([
										'id' => 'pre-visit',
										'class' => '-sg-paddingmore',
										'mainAxisAlignment' => 'spacearound',
										'children' => [
											'<a id="btn-qt-list" class="sg-action btn -primary" href="#qt-list" data-rel="box" data-width="full"><i class="icon -material">fact_check</i><span>ประเมินก่อนเยี่ยมบ้าน</span></a>',
											'<a id="btn-visit-form" class="sg-action btn -primary" href="#visit-form" data-rel="box" data-width="full"><i class="icon -material">post_add</i><span>เขียนบันทึกเยี่ยมบ้าน</span></a>',
										], // children
									]), // Row
								],
							]),
						], // children
					]), // Card
					// new ImedQtButtonWidget(['qtKey' => 'ADL', 'psnId' => $this->psnId]),
					$this->_qtList(),

					new Card([
						'id' => 'visit-form',
						'class' => '-hidden',
						'child' => $this->_visitForm(),
					]), // Card

					new ListTile([
						'title' => '<h4>ประวัติการเยี่ยมบ้าน</h4>',
						'leading' => '<i class="icon -material">medical_services</i>',
					]), // ListTile

					'<div class="sg-load" data-url="'.url('imed/visits', ['pid' => $this->psnId, 'ref' => 'psyc']).'" data-replace="true">'._NL
					. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 48px auto; display: block;"></div>'
					. '</div><!-- imed-my-note -->',

					$this->_script(),
				], // children
			]), // Container
		]); // Scaffold
	}

	function _qtList() {
		return new Card([
			'id' => 'qt-list',
			'class' => 'qt-list -hidden',
			'children' => [
				'<header class="header">'._HEADER_BACK.'<h3>แบบบันทึกข้อมูล</h3></header>',
				new ImedQtButtonWidget([
					'psnId' => $this->psnId,
					'seqId' => -1,
					'refApp' => 'psyc',
					'formDone' => 'qtDoneCallback',
					'firebaseUpdate' => false,
				]), // ImedVisitQt
			], // children
		]);
	}

	function _visitForm() {
		return new Form([
			'action' => url('imed/psyc/api/visit.create'),
			'id' => 'visit-form',
			'class' => 'sg-form visit-form -imed-visit',
			'rel' => 'none',
			'checkValid' => true,
			'done' => 'back | callback:imedInfoVisitDone',
			'children' => [
			'<header class="header -box">'._HEADER_BACK.'<h3>บันทึกการเยี่ยมบ้าน</h3></header>',
				'service' => ['type' => 'hidden', 'value' => 'Home Visit'],
				'psnId' => ['type' => 'hidden', 'value' => $this->psnId],
				'seqId' => ['type' => 'hidden'],
				'msg' => [
					'type' => 'textarea',
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
					'value' => date('d/m/Y'),
					'container' => '{class: "-label-in"}',
				],

				// *****
				'<section>',
				'<h4>1. วิเคราะห์ปัญหาของผู้รับบริการ</h4>',
				'<div>',
				'problem' => [
					'type' => 'checkbox',
					'multiple' => true,
					'options' => [
						'eatdrug' => 'รับประทานยาไม่ต่อเนื่อง/ขาดยา',
						'exacerbation' => 'อาการทางจิตกำเริบ',
						'nocaretaker' => 'ไม่มีผู้ดูแล',
						'usedrug' => 'การใช้สารเสพติด',
						'economy' => 'ปัญหาด้านเศรษฐกิจ',
						'other' => 'อื่น ๆ'
					],
				],
				'problem-detail' => [
					'type' => 'textarea',
					'label' => 'รายละเอียดปัญหา',
					'class' => '-fill',
					'rows' => 2,
					'placeholder' => 'เขียนบันทึกรายละเอียดปัญหา',
					'container' => '{class: "-label-in"}',
				],
				'</div>',
				'</section>',

				// *****
				'<section>',
				'<h4>2. แนวทางและวิธีการแก้ไขปัญหา/ช่วยเหลือ</h4>',
				'<div>',
					// *****
					'<div id="guide-for-eatdrug" class="guide-for-eatdrug -hidden">',
					'<h5>2.1 รับประทานยาไม่ต่อเนื่อง/ขาดยา</h5>',
					'guide-eatdrug' => [
						'type' => 'checkbox',
						'multiple' => true,
						'options' => [
							'1' => 'ประสานงานแพทย์เพื่อรับยา',
							'2' => 'ตรวจสอบสิทธิ์การรักษา',
							'3' => 'ประสานงานการรับยา หรือ อสม.',
							'other' => 'อื่น ๆ'
						],
					],
					'guide-detail-eatdrug' => [
						'type' => 'textarea',
						'label' => 'แนวทางและวิธีการแก้ไขปัญหา/ช่วยเหลือ',
						'class' => '-fill',
						'rows' => 2,
						'placeholder' => 'เขียนบันทึกแนวทางและวิธีการแก้ไขปัญหา/ช่วยเหลือ',
						'container' => '{class: "-label-in"}',
					],
					'</div>',
					// *****
					'<div id="guide-for-exacerbation" class="guide-for-exacerbation -hidden">',
					'<h5>2.2 อาการทางจิตกำเริบ/ระดับเล็กน้อย/ปานกลาง/รุนแรง</h5>',
					'guide-exacerbation-green' => [
						'type' => 'radio',
						'name' => 'guide-exacerbation',
						'options' => ['green' => 'ระดับเล็กน้อย',],
					],
					'<div class="-hidden">',
						'guide-exacerbation-item-green' => [
							'type' => 'checkbox',
							'multiple' => true,
							'options' => [
								'1' => 'ให้ผู้ดูแลคอยสังเกตอาการ',
								'2' => 'อสม.ติดตามอาการต่อเนื่อง',
								'other' => 'อื่น ๆ'
							],
						],
					'</div>',
					'guide-exacerbation-yellow' => [
						'type' => 'radio',
						'name' => 'guide-exacerbation',
						'options' => ['yellow' => 'ระดับปานกลาง',],
					],
					'<div class="-hidden">',
						'guide-exacerbation-item-yellow' => [
							'type' => 'checkbox',
							'multiple' => true,
							'options' => [
								'1' => 'ประเมินความรุนแรงอาการทางจิต',
								'2' => 'ผู้ดูแลเฝ้าสังเกตอาการอย่างใกล้ชิด',
								'3' => 'หากอาการรุนแรงเตรียมประสานงานกับเครือข่ายฉุกเฉิน',
								'other' => 'อื่น ๆ'
							],
						],
					'</div>',
					'guide-exacerbation-red' => [
						'type' => 'radio',
						'name' => 'guide-exacerbation',
						'options' => ['red' => 'ระดับรุนแรง',],
					],
					'<div class="-hidden">',
						'guide-exacerbation-item-red' => [
							'type' => 'checkbox',
							'multiple' => true,
							'options' => [
								'1' => 'ประเมินความรุนแรงอาการทางจิต',
								'2' => 'จำกัดพฤติกรรม',
								'3' => 'ขอความช่วยเหลือจากแหล่งสนับสนุน เช่น ตำรวจ ทหาร',
								'other' => 'อื่น ๆ'
							],
						],
					'</div>',
					'guide-detail-exacerbation' => [
						'type' => 'textarea',
						'label' => 'แนวทางและวิธีการแก้ไขปัญหา/ช่วยเหลือ',
						'class' => '-fill',
						'rows' => 2,
						'placeholder' => 'เขียนบันทึกแนวทางและวิธีการแก้ไขปัญหา/ช่วยเหลือ อาการทางจิตกำเริบ',
						'container' => '{class: "-label-in"}',
					],
					'</div>',
					// *****
					'<div id="guide-for-nocaretaker" class="guide-for-nocaretaker -hidden">',
					'<h5>2.3 ไม่มีผู้ดูแล</h5>',
					'guide-nocaretaker' => [
						'type' => 'checkbox',
						'multiple' => true,
						'options' => [
							'1' => 'ประสานงานกับ อปท.',
							'2' => 'ประสานงาน พมจ.',
							'other' => 'อื่น ๆ'
						],
					],
					'guide-detail-nocaretaker' => [
						'type' => 'textarea',
						'label' => 'แนวทางและวิธีการแก้ไขปัญหา/ช่วยเหลือ',
						'class' => '-fill',
						'rows' => 2,
						'placeholder' => 'เขียนบันทึกแนวทางและวิธีการแก้ไขปัญหา/ช่วยเหลือ ไม่มีผู้ดูแล',
						'container' => '{class: "-label-in"}',
					],
					'</div>',
					// *****
					'<div id="guide-for-usedrug" class="guide-for-usedrug -hidden">',
					'<h5>2.4 การใช้สารเสพติด</h5>',
					'guide-usedrug' => [
						'type' => 'checkbox',
						'multiple' => true,
						'options' => [
							'1' => 'ประเมินความรุนแรง/อาการการใช้สารเสพติด',
							'2' => 'ประสานงานกับกำนัน ผู้ใหญ่บ้านในพื้นที่',
							'3' => 'ประสานงานแหล่งสนับสนุน/ส่งต่อ',
							'other' => 'อื่น ๆ'
						],
					],
					'guide-detail-usedrug' => [
						'type' => 'textarea',
						'label' => 'แนวทางและวิธีการแก้ไขปัญหา/ช่วยเหลือ',
						'class' => '-fill',
						'rows' => 2,
						'placeholder' => 'เขียนบันทึกแนวทางและวิธีการแก้ไขปัญหา/ช่วยเหลือ การใช้สารเสพติด',
						'container' => '{class: "-label-in"}',
					],
					'</div>',
					// *****
					'<div id="guide-for-economy" class="guide-for-economy -hidden">',
					'<h5>2.5 ปัญหาด้านเศรษฐกิจ</h5>',
					'guide-economy' => [
						'type' => 'checkbox',
						'multiple' => true,
						'options' => [
							'1' => 'ประสานงานกับ อปท.',
							'2' => 'ประสานงาน พมจ.',
							'other' => 'อื่น ๆ'
						],
					],
					'guide-detail-economy' => [
						'type' => 'textarea',
						'label' => 'แนวทางและวิธีการแก้ไขปัญหา/ช่วยเหลือ',
						'class' => '-fill',
						'rows' => 2,
						'placeholder' => 'เขียนบันทึกแนวทางและวิธีการแก้ไขปัญหา/ช่วยเหลือ ปัญหาด้านเศรษฐกิจ',
						'container' => '{class: "-label-in"}',
					],
					'</div>',
				'</div>',
				'</section>',

				// *****
				'<section>',
				'<h4>3. การติดตามและประเมินผล</h4>',
				'follow' => [
					'type' => 'textarea',
					'label' => 'การติดตามและประเมินผล',
					'class' => '-fill',
					'rows' => 2,
					'placeholder' => 'เขียนบันทึกการติดตามและประเมินผล',
					'container' => '{class: "-label-in"}',
				],
				'</section>',

				// *****
				'<section>',
				'<h4>4. วางแผนเยี่ยมบ้านครั้งต่อไป (วันเวลา)</h4>',
				'nextvisit' => [
					'type' => 'textarea',
					'label' => 'วางแผนเยี่ยมบ้านครั้งต่อไป (วันเวลา)',
					'class' => '-fill',
					'rows' => 2,
					'placeholder' => 'เขียนบันทึกวางแผนเยี่ยมบ้านครั้งต่อไป',
					'container' => '{class: "-label-in"}',
				],
				'</section>',

				'go' => [
					'type' => 'button',
					'value' => '<i class="icon -save -white"></i><span>โพสท์เยี่ยมบ้าน</span>',
					'container' => '{class: "-sg-text-right"}',
				],
				'<p class="remark">** บันทึกข้อความในการเยี่ยมบ้าน ภาพถ่ายและข้อมูลประกอบการเยี่ยมบ้าน จะแสดงให้เห็นเฉพาะสมาชิกของกลุ่มและผู้ที่ได้รับสิทธิ์ในการดูแลผู้ป่วยในตำบล อำเภอ จังหวัด ของผู้ป่วยเท่านั้น กรุณาใช้ข้อความที่สุภาพ รักษาสิทธิ์และความเป็นส่วนตัวของผู้ป่วยตามแนวทางในการรักษาข้อมูลส่วนบุคคลของผู้ป่วย **</p>',
			], // children
		]); // Form
	}

	function _script() {
		head(
		'<style type="text/css">
		.-imed-visit section {padding: 8px;}
		.-imed-visit section>h4 {background-color: #eee; padding: 8px;}
		.form-item.-edit-guide-exacerbation {padding: 0 8px;}
		.form-item.-edit-guide-exacerbation+div>* {padding: 0 0 0 32px}
		</style>
		<script type="text/javascript">
		function qtDoneCallback($this, data) {
			console.log(data)
			if (data.seqId) {
				let $form = $(".sg-form.-imed-visit")
				let formAction = $form.attr("action")+"/"+data.seqId
				// $form.attr("action", formAction)
				$(".form-item.-edit-seqid>input").val(data.seqId)
				$(".imed-visit-qt-menu a").each(function(index) {
					let $link = $(this)
					if (!$link.data("defaultHref")) $link.data("defaultHref", $link.attr("href"))
					let href = $link.attr("href").replace("/-1", "/"+data.seqId)
					$link.attr("href", href)
					// console.log($link.attr("href"),href)
				})
				// console.log("formAction ",formAction)
			}
		}

		function imedInfoVisitDone($this, data) {
			$(".sg-form.-imed-visit").trigger("reset")
			$("#edit-msg").val("")
			$("#edit-visittype").val("")
			$(".form-item.-edit-seqid>input").val("")
			$(".imed-visit-qt-menu a").each(function(index) {
				let $link = $(this)
				if ($link.data("defaultHref")) {
					$link.attr("href", $link.data("defaultHref"))
				}
			})

			// Add visit top top of visit list
			if (data.seqId) {
				let url = "'.url('imed/visit/'.$this->psnId.'/item/').'" + "/" + data.seqId
				let para = []
				para.ref = "psyc"
				$.get(url, para, function(html) {
					$("#imed-visits").prepend(html)
				})

				$.get("'.url('imed/api/firebase/visitAdd').'", data, function(html) {})
			}
		}

		$(document).on("change", ".form-item.-edit-problem input", function() {
			let changeInput = $(this).val()
			let $target = $(this).closest("form").find(".guide-for-"+changeInput)
			$target.toggle()
		});

		$(document).on("click", ".form-item.-edit-guide-exacerbation .form-radio", function() {
			$(".form-item.-edit-guide-exacerbation+div").hide()
			$(".form-item.-edit-guide-exacerbation+div").find(".form-checkbox").prop("checked", 0)
			$(this).closest(".form-item.-edit-guide-exacerbation").next().show()
		});
		</script>
		');

		$ret = '';
		// Show update visit card when data change
		if (cfg('firebase') && (cfg('imed.visit.realtime.change.member') == 'all' || (cfg('imed.visit.realtime.change.member') == 'admin' && is_admin('imed')))) {
			$ret .= '<script type="text/javascript">
			$(document).ready(function() {
				if (!firebaseConfig) return

				let isAccess = '.$this->isAccess.'
				let psnId = '.$this->patientInfo->psnId.'
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
		return $ret;
	}
}
?>