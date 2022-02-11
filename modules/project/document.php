<?php return; ?>

ข้อมูลทั่วไปโครงการ
======================================
	formid = info
	part = basic
	text1 = หลักการและเหตุผล
	text2 = วิธีการดำเนินกิจกรรม
	text3 = รายละเอียดกลุ่มเป้าหมาย
	text4 = ตัวชี้วัดกิจกรรม
	text5 = ผลการดำเนินงานที่คาดว่าจะได้รับ
	text6 = กรอบแนวคิด
	detail1 = ตำแหน่ง (เจ้าหน้าที่รับผิดชอบ)
	detail2 = เบอร์โทร (เจ้าหน้าที่รับผิดชอบ)
	detail3 = ชื่อ - สกุล (ผู้เสนอโครงการ)
	detail4 = ตำแหน่ง (ผู้เสนอโครงการ)
	detail5 = เบอร์โทร (ผู้เสนอโครงการ)

	formid = "info"
	part = "basic"
	refid = ความสอดคล้องกับแผนงาน/ประเด็น
	detail1 = ภายใต้แผนงาน (ระบุชื่อแผนงาน)


วัตถุประสงค์โครงการ
======================================
	flag = lock for editable, 0 = editable, 1-10 = internal lock 11-?? = external lock
	trid = Objective ID
	tpid = Project ID
	parent = objectiveType => table=tag taggroup=project:objtype USING catid => objectiveTypeName
	formid = "info"
	part = "objective"
	tagname link to tag.taggroup
	refid link to tag.catid
	tag.name = objectiveTypeName
	flag =
	uid =
	text1 = title => Objective Title
	text2 = indicatorDetail => Indicator Detail
	num1, problem.num1 = problemsize
	num2 = targetsize
	num3 = outputSize
	text4 = outputDetail
	text5 = outcomeDetail
	text6 = impactDetail
	text3 = noticeDetail
	JOIN to problem formid = info, part = problem on same tagname and same refid
	JOIN to tag as problem referer



กิจรรมหลัก - Main activity
======================================
	trid =
	tpid =
	parent = objectId : Object ID : link to project_tr trid on formid="info" and part="objective"
	sorder =
	formid = "info"
	part = "mainact"
	flag = lock for editable, 0 = editable, 1-10 = internal lock 11-?? = external lock
	uid =
	detail1 = title : ชื่อกิจกรรมหลัก
	detail2 = timeprocess : ระยะเวลาดำเนินงาน (Ver 1)
	date1 = fromdate : เริ่มการดำเนินงาน
	date2 = todate : สิ้นสุดการดำเนินงาน
	text1 = desc : รายละเอียดกิจกรรม
	text2 = indicator : ตัวชี้วัด
	text3 = output : ผลผลิต (Output)
	text4 = copartner : ภาคีร่วมสนับสนุน
	text5 = budgetdetail : รายละเอียดงบประมาณ
	text6 = outcome : ผลลัพธ์ (Outcome)
	detail3 = targetOtherDesc : ชื่อกลุ่มเป้าหมายอื่น ๆ
	num1 = : รวมงบประมาณ
	num2 = : จำนวนกลุ่มเป้าหมาย
	num3 = : เด็กเล็ก
	num4 = : เด็กวัยเรียน
	num5 = : วัยทำงาน
	num6 = : ผู้สูงอายุ
	num7 = : คนพิการ
	num8 = : ผู้หญิง
	num9 = : มุสลิม
	num10 = : แรงงาน
	num11 = : อื่น ๆ





topic
======================================
status = editable status of project 1-5 = editable, 11 = project detail not editable


วัตถุประสงค์ของกิจกรรมหลัก
======================================
	formid = info
	part = actobj
	tpid = project ID
	trid = primary key
	parent = objective ID
	gallery = main activity ID


ค่าใช้จ่ายกิจกรรมหลัก (พัฒนาโครงการ)
=======================================
	trid = expense id
	tpid = project id
	parent = main activity id
	gallery = expense code
	formid = "develop"
	part = "exptr"
	num1 = amt
	num2 = unit price
	num3 = times
	num4 = total expense
	detail1 = unit name
	text1 = expense detail
	uid = creater
	created = create time


Activity field of follow project
=======================================
form = info
part = activity
	activity.`trid` `activityId`
	activity.`parent`
	calendar.`id` `calid`
	calendar.*
	DATE_FORMAT(c.`from_time`, "%H:%i") `from_time`
	DATE_FORMAT(c.`to_time`, "%H:%i") `to_time`
	IFNULL(c.`title`,ac.`detail1`) `title`
	IFNULL(c.`detail`, ac.`text1`) `detail`
	project_activity.`mainact`
	project_activity.`budget`
	project_activity.`targetpreset`
	project_activity.`target`
	action.`trid` `actionId`
	action.`flag`
	action.`num7` `exp_total`
	COUNT(action.`trid`) `trtotal`
	IF(action.`trid` IS NULL, DATEDIFF(:curdate, c.`to_date`), NULL) `late`
	(SELECT COUNT(*) FROM %project_tr% WHERE `tpid` = c.`tpid` AND `formid` = "info" AND `part` = "activity" AND `parent` = ac.`trid`) `childs`
	(SELECT SUM(`num1`) FROM %project_tr% WHERE `tpid` = c.`tpid` AND `formid` = "info" AND `part` = "activity" AND `parent` = ac.`trid`) `childBudget`

	Join Table:
	- project_activity => JOIN %project_activity% a ON a.`calid` = c.`id`
	- activity => JOIN %project_tr% ac ON ac.`formid` = "info" AND ac.`part` = "activity" AND ac.`calid` = c.`id`
	- action => JOIN %project_tr% action ON action.`formid` = "activity" AND action.`calid` = c.`id`

Activity field of follow & proposal
=======================================
	project_tr.`tpid`
	project_tr.`trid`
	project_tr.`refid`
	project_tr.`refcode` 	`serieNo`							: รุ่นนักศึกษา
	project_tr.`parent`													: Parent activity
	project_tr.`calid`
	project_tr.`gallery`
	project_tr.`period`
	project_tr.`flag`
	project_tr.`uid`,
	project_tr.`rate1` rate,
	project_tr.`date1` action_date,
	project_tr.`detail1` action_time,
	project_tr.`detail2` followername,
	project_tr.`detail3` objective,
	project_tr.`text1` goal_do,
	project_tr.`text2` real_do,
	project_tr.`text3` targetPresetDetail,
	project_tr.`text4` real_work,
	project_tr.`text5` problem,
	project_tr.`text6` recommendation,
	project_tr.`text7` support,
	project_tr.`text8` followerrecommendation,
	project_tr.`text9` targetjoindetail,
	project_tr.`num1` exp_meed,
	project_tr.`num2` exp_wage,
	project_tr.`num3` exp_supply,
	project_tr.`num4` exp_material,
	project_tr.`num5` exp_utilities,
	project_tr.`num6` exp_other,
	project_tr.`num7` exp_total,
	project_tr.`num8` targetjoin,

	calendar.`title`,
	calendar.`detail` goal_dox,
	topic.`title` projectTitle,
	activity.`budget`,
	activity.`mainact`,
	mainact.`detail1` mainact_detail,
	activity.`targetpreset`,
	activity.`target` target,
	mainact.`text3` `presetOutputOutcome`,
	user.`username`
	user.`name` ownerName,
	GROUP_CONCAT(DISTINCT p.`fid`, "|" , p.`file`) photos

	Join Table:
	- mainact => JOIN %project_tr% m ON m.`trid`=a.`mainact`
	- activity => JOIN %project_activity% a ON a.`calid`=tr.`calid`





Action field of follow project
=======================================
	form = activity
	part = owner

	project_tr.tpid 				= tpid : project topic id
	project_tr.trid 				= actionId : transation id
	project_tr.orgid 				= orgid : transaction of orgid
	project_tr.parent 			= parent : main activity id
	  - link to project_tr.trid on formid="info" and part="mainact"
	  - *** Not use on new version but ref to project_activity.mainact using calid ***
	project_tr.calid 				= calid : calendar id
	project_tr.refid				= activityId
	planact.refcode					= serieNo
	project_tr.gallery 			= gallery : photo gallery id (link to table topic_file)
	project_tr.formid 			= "activity"
	project_tr.part 				= "owner"
	calendar.title 					= title : activityname
	topic.title 						= projectTitle : Project title
	project_tr.rate1 				= rate : ประเมินผล คุณภาพกิจกรรม
	project_tr.date1 				= action_date : วันที่ปฎิบัติจริง
	project_tr.detail1 			= from_time :
	project_activity.budget	= budget : งบประมาณที่ตั้งไว้
	project_activity.mainact = mainact : Main activity id
	project_tr(part = mainact).detail1 mainact_detail : Main activity title
	project_activity.targetpreset = targetpreset : จำนวนกลุ่มเป้าหมายที่ตั้งไว้
	project_activity.target 	= target : รายละเอียดกลุ่มเป้าหมายที่ตั้งไว้
	project_tr.text3 				= targetPresetDetail : รายละเอียดกลุ่มเป้าหมายที่ตั้งไว้
	project_tr.num8 				= targetjoin : จำนวนกลุ่มเป้าหมายที่เข้าร่วม
	project_tr.text9 				= targetjoindetail : รายละเอียดกลุ่มเป้าหมายที่เข้าร่วม
	project_tr.detail3 			= objective : วัตถุประสงค์
	project_tr.text1 				= actionPreset (goal_do) : รายละเอียดกิจกรรมตามแผน
	project_tr.text2 				= actionReal (real_do) : รายละเอียดขั้นตอน กระบวนการ กิจกรรมปฎิบัติจริง
	project_tr.text10 			= outputOutcomePreset (goal_work) : ผลผลิต (Output) / ผลลัพธ์ (Outcome) ที่ตามแผน
	project_tr.text4 				= outputOutcomeReal (real_work) : ผลสรุปที่สำคัญของกิจกรรม (ที่เกิดขึ้นจริง)
	project_tr.text5 				= problem : ปัญหา/แนวทางแก้ไข
	project_tr.text6 				= recommendation : ข้อเสนอแนะต่อ ...
	project_tr.text7 				= support : ความต้องการสนับสนุนจากพี่เลี้ยง
	project_tr.text8 				= followerRecommendation : คำแนะนำจากเจ้าหน้าที่ติดตามในพื้นที่
	project_tr.detail2 			= followerName : ชื่อผู้ติดตามในพื้นที่
	project_tr.num1 				= exp_meed : ค่าตอบแทน
	project_tr.num2 				= exp_wage : ค่าจ้าง
	project_tr.num3 				= exp_supply : ค่าใช้สอย
	project_tr.num4 				= exp_material : ค่าวัสดุ
	project_tr.num5 				= exp_utilities : ค่าสาธารณูปโภค
	project_tr.num6 				= exp_other : ค่าอื่น ๆ
	project_tr.num7 				= exp_total : รวมรายจ่าย
	calendar.detail 				= goal_dox : ยกเลิก ให้ไปใช้ฟิล์ด real
	users.username 				= username :
	users.name 						= ownerName :
	project_tr.detail4 			= Not used
	project_tr.text9 				= Not used
	project_tr.text10 			= Not used
	project_tr.num9 				= Not used
	project_tr.num10 				= Not used
	project_tr.num11 				= Not used

	Join Table:
	planact => JOIN %project_tr% planact ON planact.`trid`=ac.`refid` : Activity from project_tr/formid=info/part=activity

Action field
=======================================
	form = activity
	part = owner

	calendar.detail = detail (รายละเอียดกิจกรรม)
	calendar.date_from = date_from (ระยะเวลา ตามแผน)


	project_tr.text1 = goal_plan (เป้าหมาย/วิธีการ ตามแผน)
	project_tr.text2 = goal_do (เป้าหมาย/วิธีการ ปฏิบัติจริง)
	project_tr.text3 = result_plan (ผลการดำเนินงาน ตามแผน)
	project_tr.text4 = result_do (ผลการดำเนินงาน ปฏิบัติจริง)
	project_tr.text5 = problem (ปัญหา/แนวทางแก้ไข)

	project_tr.rate1 = rate ประเมินผล คุณภาพกิจกรรม

	project_tr.text6 = suggestion2sss ข้อเสนอแนะต่อ สสส.
	project_tr.text7 = suggestion2trainer ความต้องการสนับสนุนจากพี่เลี้ยงและ สจรส.ม.อ.

	project_tr.text8 = suggestionfromtrainer คำแนะนำจากเจ้าหน้าที่ติดตามในพื้นที่
	project_tr.detail1 = follower ชื่อผู้ติดตามในพื้นที่ของ สสส.

	ค่าตอบแทน = project_tr.num1
	ค่าจ้าง = project_tr.num2
	ค่าใช้สอย = project_tr.num3
	ค่าวัสดุ = project_tr.num4
	ค่าสาธารณูปโภค = project_tr.num5
	อื่น ๆ = project_tr.num6
	รวมรายจ่าย = project_tr.num7

SELECT
	ac.`tpid`
	, ac.`trid` `actionId`
	, p.`project_status` `projectStatus`
	, p.`project_status`+0 `projectStatusCode`
	, ac.`parent` `activityParent`
	, ac.`refid` `activityId`
	, ac.`calid`
	, ac.`gallery`
	, ac.`formid`
	, ac.`period`
	, ac.`part`
	, ac.`flag`
	, ac.`uid`
	, c.`title` `title`
	, t.`title` `projectTitle`
	, ac.`rate1` `rate1`
	, ac.`rate2` `rate2`
	, ac.`date1` `actionDate`
	, ac.`detail1` `actionTime`
	, a.`budget` `budgetPreset`
	, a.`mainact` `mainactId`
	, m.`detail1` `mainactDetail`
	, a.`targetpreset` `targetPresetAmt`
	, a.`target` `targetPresetDetail`
	--	, ac.`text3` `targetPresetDetail`
	, ac.`num8` `targetJoinAmt`
	, ac.`text9` `targetJoinDetail`
	, ac.`detail3` `objectiveDetail`
	, ac.`text1` `actionPreset`
	, ac.`text2` `actionReal`
	, m.`text3` `outputOutcomePreset`
	, ac.`text4` `outputOutcomeReal`
	, ac.`text5` `problem`
	, ac.`text6` `recommendation`
	, ac.`text7` `support`
	, ac.`text8` `followerRecommendation`
	, ac.`detail2` `followerName`
	, ac.`num1` `exp_meed`
	, ac.`num2` `exp_wage`
	, ac.`num3` `exp_supply`
	, ac.`num4` `exp_material`
	, ac.`num5` `exp_utilities`
	, ac.`num6` `exp_other`
	, ac.`num7` `exp_total`
	, c.`detail` `goal_dox`
	, u.`username`, u.`name` `ownerName`
	, mu.`name` `modifybyname`
	, FROM_UNIXTIME(ac.`created`,"%Y-%m-%d %H:%i:%s") `created`
	, FROM_UNIXTIME(ac.`modified`,"%Y-%m-%d %H:%i:%s") `modified`
	, (SELECT GROUP_CONCAT(`fid`,"|",ph.`file`,"|",IFNULL(ph.`tagname`,"")) FROM %topic_files% ph WHERE ph.`gallery`=ac.`gallery` AND ph.`type`="photo" ) `photos`
	FROM
		(
		SELECT *
			FROM %project_tr% tr
			%WHERE%
			ORDER BY $order
			$limit
		) ac
		LEFT JOIN %topic% t ON t.`tpid`=ac.`tpid`
		LEFT JOIN %project% p ON p.`tpid`=ac.`tpid`
		LEFT JOIN %users% u ON u.`uid`=ac.`uid`
		LEFT JOIN %project_tr% planact ON planact.`trid`=ac.`refid`
		LEFT JOIN %calendar% c ON c.`id`=ac.`calid`
		LEFT JOIN %project_activity% a ON a.`calid`=ac.`calid`
		LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
		LEFT JOIN %users% mu ON mu.`uid`=ac.`modifyby`



=====================================
งวดการเงิน Period
=====================================
	formid="info"
	part="period"
	period = period no
	flag = สถานนะรายงาน ง.1
		0 : เริ่มทำรายงาน : _PROJECT_DRAFTREPORT
		1 : ส่งรายงานจากพื้นที่ : _PROJECT  _COMPLETEPORT
		2 : ผ่านการตรวจสอบของ พี่เลี้ยง : _PROJECT_LOCKREPORT
		6 : ผ่านการตรวจสอบของ ผู้ตรวจสอบ : _PROJECT_PASS_HSMI
		9 : ผ่านการตรวจสอบของ สุดท้าย : _PROJECT_PASS_SSS
	date1 = from_date
	date2 = to_date
	num1 = budget
	detail1 = report_from_date
	detail2 = report_to_date
	text1 = note_owner
	text2 = note_complete
	text3 = note_trainer
	text4 = note_hsmi
	text5 = note_sss


รายงานการเงินประจำงวด
=====================================
	formid="ง.1"
	part="summary"
	flag=ขอเบิกเงินสนับสนุนโครงการงวดต่อไป
	num10=เป็นจำนวนเงินขอเบิกงวดต่อไป
	detail1=วันที่ผู้รับผิดชอบโครงการเซ็นต์ชื่อ
	detail4=ชื่อเจ้าหน้าที่การเงินโครงการ
	detail2=วันที่เจ้าหน้าที่การเงินโครงการเซ็นต์ชื่อ



=====================================
ส.1
=====================================
formid = ส.1
part = title
----------
	rate1=แผนงาน/กิจกรรม ที่จะดำเนินการในงวดต่อไป
		1:ตามแผนงานเดิมที่ระบุไว้ในข้อเสนอโครงการ
		2:มีการปรับเปลี่ยนจากข้อเสนอโครงการ
	rate2=ประเมินสถานการณ์โดยภาพรวมของโครงการ
		1:สามารถดำเนินกิจกรรมให้เป็นไปตามแผนได้
		2:ล่าช้ากว่าแผน
	date1=
	date2=
	detail1=วันที่ส่งรายงาน
	text1=ประเด็นปัญหา/อุปสรรค
	text2=กิจกรรม/รายละเอียดที่จะปรับเปลี่ยน และระยะเวลาที่จะปรับเปลี่ยน
	text3=แนวทางแก้ไขปรับปรุง
	text4=ข้อคิดเห็นอื่น
	text5=สาเหตุเพราะ
	text6=แนวทางการแก้ไขของผู้รับทุน





ส.3 / finalreport
=====================================
formid = ส.3
part = title
----------
	text1=การเปลี่ยนแปลงที่เกิดขึ้นนอกเหนือวัตถุประสงค์
	text2=Abstract
	text3=ปัญหาและอุปสรรค
	text4=แนวทางแก้ไขของผู้รับทุน
	text5=สาเหตุเพราะ
	text6=พฤติกรรมที่ส่งเสริมสุขภาพทางตรง
	text7=พฤติกรรมที่ส่งเสริมสุขภาพทางอ้อม
	text8=บทนำ
	text10=เอกสารอ้างอิง
	detail1=วันที่ส่งรายงาน
	detail2=คำสำคัญ
	num1=จำนวนผู้ได้รับประโยชน์ทางตรง
	num2=จำนวนผู้ได้รับประโยชน์ทางอ้อม

part:outcome
------------
text1=เกิดกฏ กติกา ระเบียบ หรือมาตรการชุมชน
text2=เกิดกลไก ระบบ หรือโครงสร้างชุมชนที่พัฒนาขึ้นใหม่
text3=เกิดต้นแบบ พื้นที่เรียนรู้ หรือแหล่งเรียนรู้ในระดับชุมชน

part:innovation
------------
detail1=ชื่อนวัตกรรม
detail2=ชนิดนวัตกรรม
	1=การพัฒนาความรู้ใหม่จากการวิจัยและพัฒนา
	2=การนำสิ่งที่มีอยู่ในชุมชนอื่นมาปรับใช้ในชุมชนตนเอง
	3=การนำสิ่งที่มีอยู่มาปรับกระบวนทัศน์ใหม่หรือทำด้วยวิธีใหม่
	4=การรื้อฟื้นสิ่งดีๆ ที่เคยมีในชุมชนมาปรับให้สอดคล้องกับสถานการณ์ปัจจุบัน
text1=การนำนวัตกรรมไปใช้ประโยชน์





Admin Report
======================================
	formid=admin
	part=comment
	date1=When
	text1=Message
	ติดตามจาก => เจ้าหน้าที่ สสส. / พี่เลี้ยง / ผู้รับผิดชอบโครงการ






แผนภาพ
======================================
	สถานการณ์สุขภาวะ=SITUATION=project-problem

	ปัจจัยที่เป็นสาเหตุที่เกี่ยวข้องกับ
	คน=PEOPLE=factor-human
	สภาพแวดล้อม=ENVIRONMENT=factor-environment
	กลไก=MECHANISM=factor-mechanism
	จุดหมาย/วัตถุประสงค์/เป้าหมาย=OBJECTIVE=project_tr:info:objective
	ปัจจัยสำคัญที่เอื้อต่อความสำเร็จ/ตัวชี้วัด=INDICATOR=project_tr:info:objective
	วิธีการสำคัญ=METHOD=กลวิธีที่เกี่ยวข้องกับคน กลุ่มคน:strategies-human / กลวิธีที่เกี่ยวข้องกับการปรับสภาพแวดล้อม:strategies-environment / กลวิธีที่เกี่ยวข้องกับการสร้างและปรับปรุงกลไก:strategies-mechanism

	ปัจจัยนำเข้า
	ทุนของชุมชน=CAPTITAL=คน:commune-leader / กลุ่ม องค์กร หน่วยงานและเครือข่าย:commune-org / วัฒนธรรม:commune-tradition / วิถีชีวิต ภูมิปัญญาและเศรษฐกิจชุมชน:commune-knowledge
	งบประมาณ=BUDGET=
	บุคลากร=PERSONNEL=owner-prename+owner-name+owner-lastname / coowner-1-prename+coowner-1-name+coowner-1-lastname ... name-mainstay
	ทรัพยากรอื่น=OTHERRESOURCE=commune-learningcenter / commune-participation / commune-cconomic

	ขั้นตอนทำงาน=PROCESS=project_tr:mainact:title

	ผลผลิต=OUTPUT=project_tr:mainact:
	ผลลัพธ์=OUTCOME=project_tr:mainact:
	ผลกระทบ=IMPACT=conversion-human / conversion-environment / conversion-mechanism

	กลไกและวิธีการติดตามของชุมชน=TRACKING=project-evaluation
	กลไกและวิธีการประเมินผลของชุมชน=EVALUATION=project-evaluation





สถานการณ์ภาวะโภชนาการนักเรียน - ดัชนีมวลกาย
======================================
	trid = หมายเลขสถานการณ์
	tpid = โครงการ
	uid = ผู้สร้างข้อมูล
	formid = weight
	part = title
	sorder = order : ลำดับการเก็บข้อมูล
	detail1 = year : ปีการศึกษา
	detail2 = term : ภาคการศึกษา
	period = period : ช่วงเวลา
	detail4 = postby : ผู้ประเมิน
	date1 = dateinput : วันที่ประเมิน
	created = วันที่บันทึกข้อมูล

Transaction
======================================
	trid = หมายเลขข้อมูล
	tpid = โครงการ
	parent = หมายเลขสถานการณ์ = formid:weight,part:title => trid
	uid = ผู้สร้างข้อมูล
	sorder = หมายเลขแบบสอบถาม
	formid = weight
	part = weight
	num1 = total
	num2 = getweight
	num5 = thin
	num6 = ratherthin
	num7 = willowy
	num8 = plump
	num9 = gettingfat
	num10 = fat
	created = วันที่บันทึกข้อมูล


Transaction
======================================
	trid = หมายเลขข้อมูล
	tpid = โครงการ
	parent = หมายเลขสถานการณ์ = formid:weight,part:title => trid
	uid = ผู้สร้างข้อมูล
	sorder = หมายเลขแบบสอบถาม
	formid = height
	part = height
	num1 = total
	num2 = getheight
	num5 = short
	num6 = rathershort
	num7 = standard
	num8 = ratherheight
	num9 = veryheight
	created = วันที่บันทึกข้อมูล



หนังสือแต่งตั้งกรรมการ
======================================
	refcode = new/add/change = ประเภทหนังสือ
	formid = "fund"
	part = "boardletter"
	refid = orgid
	detail1 = orgName
	detail2 = nayokName
	detail3 = positionName
	detail4 = docNo
	text1 = docDate
	flag = 1:แจ้งส่งแล้ว
	date1 = วันที่แจ้งส่ง





======================================
=========     nxtgenhedb     =========
======================================

Student Plan Target
======================================
formid = develop
part = studentPlan
num1 = studentPerLot
num2 = lotPerYear
num3 = year
num4 = hourAll
num5 = hourType1
num6 = hourType2
num2 * num3 = totalSerie
detail1 = yearStart
detail2 = learnType

Student Serie Target
======================================
