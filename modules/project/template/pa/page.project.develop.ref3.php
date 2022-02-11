<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_develop_ref3($self) {
	$ret = '';

	/*
<div id="comment" class="project-develop-commenting">
	<h2>ภาคผนวกที่ 3 ความเห็น</h2>

	<h3>1. ความเห็นของทีมสนับสนุนวิชาการ (พี่เลี้ยง) และ ผู้ทรงคุณวุฒิ</h3>
	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>1. การมีส่วนร่วม</h5>
	<p align="right">คะแนน
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-1" data-fld="rating-indicator-1" value="5" /> 5
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-1" data-fld="rating-indicator-1" value="4" /> 4
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-1" data-fld="rating-indicator-1" value="3" /> 3
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-1" data-fld="rating-indicator-1" value="2" /> 2
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-1" data-fld="rating-indicator-1" value="1" /> 1
	</p>
	<h5>2. ผู้นำ/แกนชุมชน</h5>
	<p align="right">คะแนน
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-2" data-fld="rating-indicator-2" value="5" /> 5
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-2" data-fld="rating-indicator-2" value="4" /> 4
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-2" data-fld="rating-indicator-2" value="3" /> 3
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-2" data-fld="rating-indicator-2" value="2" /> 2
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-2" data-fld="rating-indicator-2" value="1" /> 1
	</p>
	<h4>การพัฒนาโครงการ</h4>
	<h5>มีผู้รับผิดชอบโครงการ และแกนนำในชุมชน</h5>
	<ul>
	<li><a href="#ownername">ผู้รับผิดชอบโครงการ</a></li>
	<li><a href="#teamname">รายชื่อผู้เข้าร่วมทำโครงการ/คณะทำงาน</a></li>
	<li><a href="#leadername">รายชื่อแกนนำในชุมชน พร้อมประสบการณ์ในการทำงาน</a></li>
	</ul>
	<h4>การติดตามประเมินผล</h4>
	<ul>
	<li>เกิดกลไกขับเคลื่อนในพื้นที่ เช่น สภาผู้นำ/กลุ่ม/เครือข่าย</li>
	</ul>
	<h4>ข้อเสนอแนะเพิ่มเติม</h4>
	<div class="widget request" widget-request="project/develop/comment/{$tpid}/comment-1"></div>
	</div>

	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>3. โครงสร้างองค์กร</h5>
	<p align="right">คะแนน
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-3" data-fld="rating-indicator-3" value="5" /> 5
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-3" data-fld="rating-indicator-3" value="4" /> 4
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-3" data-fld="rating-indicator-3" value="3" /> 3
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-3" data-fld="rating-indicator-3" value="2" /> 2
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-3" data-fld="rating-indicator-3" value="1" /> 1
	</p>
	<h4>การพัฒนาโครงการ</h4>
	<h5>โครงสร้างชุมชน ทุนของชุมชน</h5>
	<ul>
	<li><a href="#ownername">ผู้รับผิดชอบโครงการ</a></li>
	<li><a href="#teamname">รายชื่อผู้เข้าร่วมทำโครงการ/คณะทำงาน</a></li>
	</ul>
	<h5>การวิเคราะห์และอธิบายทุนที่มีอยู่ในชุมชน</h5>
	<ul>
	<li><a href="#link-3-1">ผู้นำและแกนนำ</a></li>
	<li><a href="#link-3-2">กลุ่ม องค์กร หน่วยงานและเครือข่าย</a></li>
	<li><a href="#link-3-3">วิถีชีวิต ประเพณี วัฒนธรรม</a></li>
	<li><a href="#link-3-4">ภูมิปัญญา</a></li>
	<li><a href="#link-3-5">ศูนย์เรียนรู้</a></li>
	<li><a href="#link-3-6">กระบวนการมีส่วนร่วมของชุมชน</a></li>
	<li><a href="#link-3-7">เครือข่ายเศรษฐกิจชุมชน</a></li>
	</ul>
	<h4>การติดตามประเมินผล</h4>
	<ul><li>เกิดกลไกขับเคลื่อนในพื้นที่ เป็นส่วนสำคัญในโครงสร้างชุมชน เช่น กรรมการหมู่บ้าน กรรมการชุมชน เป็นต้น</li></ul>
	<h4>ข้อเสนอแนะเพิ่มเติม</h4>
	<div class="widget request" widget-request="project/develop/comment/{$tpid}/comment-3"></div>
	</div>

	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>4. การประเมินปัญหา</h5>
	<p align="right">คะแนน
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-4" data-fld="rating-indicator-4" value="5" /> 5
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-4" data-fld="rating-indicator-4" value="4" /> 4
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-4" data-fld="rating-indicator-4" value="3" /> 3
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-4" data-fld="rating-indicator-4" value="2" /> 2
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-4" data-fld="rating-indicator-4" value="1" /> 1
	</p>
	<h5>5. การถามว่าทำไม</h5>
	<p align="right">คะแนน
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-5" data-fld="rating-indicator-5" value="5" /> 5
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-5" data-fld="rating-indicator-5" value="4" /> 4
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-5" data-fld="rating-indicator-5" value="3" /> 3
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-5" data-fld="rating-indicator-5" value="2" /> 2
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-5" data-fld="rating-indicator-5" value="1" /> 1
	</p>
	<h4>การพัฒนาโครงการ</h4>
	<h5>การวิเคราะห์สภาพปัญหาในชุมชน/เลือกปัญหา</h5>
	<ul>
	<li><a href="#link-4-1">ปัญหาในชุมชนและจัดลำดับความสำคัญ</a></li>
	</ul>
	<h5>การวิเคราะห์ปัจจัยที่มีผลต่อการจัดการปัญหา (คน สภาพแวดล้อมกลไก)</h5>
	<ul>
	<li><a href="#link-4-3">ปัจจัยสาเหตุและปัจจัยเอื้อต่อการแก้ปัญหา</a></li>
	</ul>
	<h5>การวิเคราะห์และจัดทำแนวทางการจัดการปัญหา</h5>
	<ul>
	<li><a href="#link-4-4">แนวทางสำคัญ วิธีการสำคัญเพื่อแก้ไขปัญหา</a></li>
	</ul>
	<h5>การมีแผนชุมชน</h5>
	<ul>
	<li><a href="#ref1">ภาคผนวก 1 ข้อมูลชุมชน</a></li>
	<li><a href="#ref2">ภาคผนวก 2 ข้อมูลแผนชุมชน </a></li>
	</ul>
	<h4>การติดตามประเมินผล</h4>
	<ul><li>มีฐานข้อมูลชุมชน (ปัญหาของชุมชน , ปัญหาเฉพาะประเด็น)</li></ul>
	<h4>ข้อเสนอแนะเพิ่มเติม</h4>
	<div class="widget request" widget-request="project/develop/comment/{$tpid}/comment-4"></div>
	</div>

	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>6. การระดมทรัพยากร</h5>
	<p align="right">คะแนน
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-6" data-fld="rating-indicator-6" value="5" /> 5
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-6" data-fld="rating-indicator-6" value="4" /> 4
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-6" data-fld="rating-indicator-6" value="3" /> 3
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-6" data-fld="rating-indicator-6" value="2" /> 2
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-6" data-fld="rating-indicator-6" value="1" /> 1
	</p>
	<h5>7. การเชื่อมโยงภายนอก</h5>
	<p align="right">คะแนน
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-7" data-fld="rating-indicator-7" value="5" /> 5
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-7" data-fld="rating-indicator-7" value="4" /> 4
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-7" data-fld="rating-indicator-7" value="3" /> 3
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-7" data-fld="rating-indicator-7" value="2" /> 2
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-7" data-fld="rating-indicator-7" value="1" /> 1
	</p>
	<h4>การพัฒนาโครงการ</h4>
	<h5>แผนการดำเนินการมีภาคีร่วมสนับสนุน อะไร อย่างไร งบประมาณที่ร่วมสนับสนุน</h5>
	<ul><li><a href="#link-9">แผนการดำเนินงาน ภาคีร่วมสนับสนุน</a></li></ul>
	<h4>การติดตามประเมินผล</h4>
	<ul><li>การระดมทรัพยากรและการเชื่อมโยงภายนอก มีการบรรจุอยู่ใน แผนชุมชน แผน อบต./เทศบาล แผนของหน่วยงาน</li></ul>
	<h4>ข้อเสนอแนะเพิ่มเติม</h4>
	<div class="widget request" widget-request="project/develop/comment/{$tpid}/comment-6"></div>
	</div>

	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>8. บทบาทตัวแทน</h5>
	<p align="right">คะแนน
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-8" data-fld="rating-indicator-8" value="5" /> 5
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-8" data-fld="rating-indicator-8" value="4" /> 4
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-8" data-fld="rating-indicator-8" value="3" /> 3
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-8" data-fld="rating-indicator-8" value="2" /> 2
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-8" data-fld="rating-indicator-8" value="1" /> 1
	</p>
	<h5>ตัวแทนภายใน</h5>
	<ul>
	<li>ผู้รับผิดชอบโครงการเป็นตัวแทนชุมชน มีกระบวนการชี้แจง ประชุมชุมชนก่อนเริ่มโครงการ</li>
	<li>มีกระบวนการติดตามประเมินผลโครงการโดยชุมชน</li>
	<li>มีการประเมินผลระหว่างโครงการ</li>
	<li>มีการประเมินผลหลังการทำโครงการ</li>
	</ul>
	<h5>ตัวแทนภายนอก</h5>
	<ul>
	<li>มีระบบพี่เลี้ยง หนุนเสริม เชื่อมประสานกับหน่วยงานภาคีที่เกี่ยวข้องทั้งภายใน และภายนอกชุมชน</li>
	</ul>
	<h4>การพัฒนาโครงการ</h4>
	<h5>ตัวแทนภายใน และตัวแทนภายนอก</h5>
	<ul>
	<li><a href="#link-9">แผนการดำเนินงาน กิจกรรม</a></li>
	</ul>
	<h4>การติดตามประเมินผล</h4>
	<ul>
	<li>ผู้รับผิดชอบโครงการเข้าไปมีส่วนร่วมในกลุ่ม / เครือข่าย หรือ หน่วยงานทั้งภายในและภายนอกชุมชน</li>
	<li>ผู้รับผิดชอบโครงการยกระดับเป็นพี่เลี้ยง</li>
	</ul>
	<h4>ข้อเสนอแนะเพิ่มเติม</h4>
	<div class="widget request" widget-request="project/develop/comment/{$tpid}/comment-8"></div>
	</div>

	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>9. การบริหารจัดการ</h5>
	<p align="right">คะแนน
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-9" data-fld="rating-indicator-9" value="5" /> 5
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-9" data-fld="rating-indicator-9" value="4" /> 4
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-9" data-fld="rating-indicator-9" value="3" /> 3
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-9" data-fld="rating-indicator-9" value="2" /> 2
	<input type="radio" data-type="radio" class="{$datainput}" name="rating-indicator-9" data-fld="rating-indicator-9" value="1" /> 1
	</p>
	<h4>การพัฒนาโครงการ</h4>
	<ul>
	<li>การใช้ระบบพัฒนาโครงการบนเว็บไซต์</li>
	</ul>
	<h4>การติดตามประเมินผล</h4>
	<ul>
	<li>การใช้ระบบติดตามประเมินผลบนเว็บไซต์ (รายงาน, การเงิน)</li>
	</ul>
	<h4>ข้อเสนอแนะเพิ่มเติม</h4>
	<div class="widget request" widget-request="project/develop/comment/{$tpid}/comment-9"></div>
	</div>

	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>10. คุณค่าที่เกิดขึ้น</h5>
	<h5>10.1 เกิดความรู้ หรือ นวัตกรรมชุมชน</h5>
	<ul>
	<li>ความรู้ใหม่/องค์ความรู้ใหม่</li>
	<li>สิ่งประดิษฐ์/ผลผลิตใหม่</li>
	<li>กระบวนการใหม่</li>
	<li>วิธีการทำงาน/การจัดการใหม่</li>
	<li>การเกิดกลุ่ม/โครงสร้างในชุมชนใหม่</li>
	<li>แหล่งเรียนรู้ใหม่</li>
	</ul>
	<h4>การติดตามประเมินผล</h4>
	<ul><li>การเกิดความรู้ หรือ นวัตกรรมชุมชน</li></ul>
	<div class="x-widget request" widget-request="project/develop/comment/{$tpid}/comment-10-1"></div>
	</div>

	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>10. คุณค่าที่เกิดขึ้น</h5>
	<h5>10.2 เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ</h5>
	<ul>
	<li>การดูแลสุขอนามัยส่วนบุคคล</li>
	<li>การบริโภค</li>
	<li>การออกกำลังกาย</li>
	<li>การลด ละ เลิก อบายมุข</li>
	<li>การลดพฤติกรรมเสี่ยง</li>
	<li>การจัดการอารมณ์ / ความเครียด</li>
	<li>การดำรงชีวิต / วิถีชีวิต</li>
	<li>พฤติกรรมการจัดการตนเองครอบครัว</li>
	</ul>
	<h4>การติดตามประเมินผล</h4>
	<ul>
	<li>การเกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ</li>
	</ul>
	<div class="x-widget request" widget-request="project/develop/comment/{$tpid}/comment-10-2"></div>
	</div>

	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>10. คุณค่าที่เกิดขึ้น</h5>
	<h5>10.3 การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)</h5>
	<ul>
	<li>กายภาพ เช่น มีการจัดการขยะ ป่า น้ำ</li>
	<li>สังคม เช่น มีความปลอดภัยในชีวิตและทรัพย์สิน มีการใช้ศาสนา/วัฒนธรรมเป็นฐานการพัฒนา</li>
	<li>เศรษฐกิจสร้างสรรค์สังคม /สร้างอาชีพ</li>
	<li>มีการบริการสุขภาพทางเลือก และมีช่องทางการเข้าถึงระบบบริการสุขภาพ</li>
	</ul>
	<h4>การติดตามประเมินผล</h4>
	<ul>
	<li>การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)</li>
	</ul>
	<div class="x-widget request" widget-request="project/develop/comment/{$tpid}/comment-10-3"></div>
	</div>

	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>10. คุณค่าที่เกิดขึ้น</h5>
	<h5>10.4 การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ</h5>
	<ul>
	<li>มีกฎ / กติกา ของกลุ่ม ชุมชน</li>
	<li>มีมาตรการทางสังคมของกลุ่ม ชุมชน</li>
	<li>มีธรรมนูญของชุมชน</li>
	<li>อื่นๆ เช่น ออกเป็นข้อบัญญัติท้องถิ่น</li>
	</ul>
	<h4>การติดตามประเมินผล</h4>
	<ul>
	<li>การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ</li>
	</ul>
	<div class="x-widget request" widget-request="project/develop/comment/{$tpid}/comment-10-4"></div>
	</div>

	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>10. คุณค่าที่เกิดขึ้น</h5>
	<h5>10.5 เกิดกระบวนการชุมชน</h5>
	<ul>
	<li>เกิดการเชื่อมโยงประสานงานระหว่างกลุ่ม / เครือข่าย</li>
	<li>การเรียนรู้การแก้ปัญหาชุมชน (การประเมินปัญหา การวางแผน การปฏิบัติการ และการประเมิน)</li>
	<li>การใช้ประโยชน์จากทุนในชุมชน เช่น การระดมทุน</li>
	<li>มีการขับเคลื่อนการดำเนินงานของกลุ่มและชุมชนที่เกิดจากโครงการอย่างต่อเนื่อง</li>
	<li>เกิดกระบวนการจัดการความรู้ในชุมชน</li>
	<li>เกิดทักษะในการจัดการโครงการ เช่น การใช้ข้อมูลในการตัดสินใจ</li>
	</ul>
	<h4>การติดตามประเมินผล</h4>
	<ul>
	<li>การเกิดกระบวนการชุมชน</li>
	</ul>
	<div class="x-widget request" widget-request="project/develop/comment/{$tpid}/comment-10-5"></div>
	</div>

	<div class="box">
	<h4>ตัวชี้วัดการประเมิน</h4>
	<h5>10. คุณค่าที่เกิดขึ้น</h5>
	<h5>10.6 มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ</h5>
	<ul>
	<li>ความรู้สึกภาคภูมิใจในตัวเอง / กลุ่ม / ชุมชน</li>
	<li>การเห็นประโยชน์ส่วนรวมและส่วนตนอย่างสมดุล</li>
	<li>การใช้ชีวิตอย่างเรียบง่าย และพอเพียง</li>
	<li>ชุมชนมีความเอื้ออาทร</li>
	<li>มีการตัดสินใจโดยใช้ฐานปัญญา</li>
	</ul>
	<h4>การติดตามประเมินผล</h4>
	<ul>
	<li>มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ</li>
	</ul>
	<div class="x-widget request" widget-request="project/develop/comment/{$tpid}/comment-10-6"></div>
	</div>

	<div class="box project-develop-ratingindicator">
	<h4>คะแนนตัวชี้วัดการประเมิน</h4>
	<p style="font-size:1.2em;font-weight:bold;">คะแนนตัวชี้วัด = {$ratingIndicator}/45 = {$ratingPercent}%</p>
	<p><em>กรุณาคลิกรีเฟรชเพื่อคำนวนคะแนนตัวชี้วัดใหม่</em></p>
	</div>

	<h3>2. สรุปภาพรวมข้อเสนอโครงการ</h3>
	<div class="box">
	<div class="widget request" widget-request="project/develop/comment/{$tpid}/comment-summary"></div>
	</div>

	<h3>3. ความเห็นภาพรวมของผู้ทรงคุณวุฒิ</h3>
	<div class="box">
	<div class="widget request" widget-request="project/develop/comment/{$tpid}/comment-commentator"></div>
	</div>


	<div class="x-widget request" widget-request="project/develop/comment/{$tpid}"></div>

	<div class="" data-x-load="project/develop/comment/{$tpid}"></div>

</div><!--tab : comment -->

<a class="button--expand -no-print" href="#">&lt;</a>
	return $ret;
	/*
}
?>