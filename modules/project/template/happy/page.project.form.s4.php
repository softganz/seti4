<?php
/**
 * แบบรายงานสรุปเมื่อสิ้นสุดระยะเวลาโครงการ (ส.4)
 *
 * @param Object $topic
 * @param Object $para
 * @param String $report
 */
function project_form_s4($self,$topic,$para,$report) {
  $tpid=$topic->tpid;
  $formid='ส.4';
  $download=post('download');
  $action=post('act');
  $order=SG\getFirst(post('order'),'mainact');

  $isEdit=!$download && $topic->project->project_statuscode==1 && (user_access('administer projects') || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid));

  $info=project_model::get_tr($tpid,'info');
  $report=project_model::get_tr($tpid,$formid);

  switch ($action) {
    case 'addtr' :
      list($formid,$part)=explode(':',post('group'));
      //$ret.='FormId='.$formid.' Part='.$part;
      mydb::query('INSERT INTO %project_tr% (`tpid`, `formid`, `part`, `period`, `created`, `uid`) VALUES (:tpid, :formid, :part, :period, :created, :uid)',':tpid',$tpid, ':formid',$formid, ':part',$part, ':period',$period, ':created', date('U'), ':uid',i()->uid);
      //$ret.=mydb()->_query;
      break;

    case 'addleader' :
      $ret.=__project_form_s3_addleader($tpid);
      return $ret;
      break;

    case 'removeleader' :
      if ($isEdit && ($psnid = post('id')) && SG\confirm()) {
        $ret.='Remove';
        mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `parent`=:psnid AND `formid`="leader"', ':tpid',$tpid, ':psnid',$psnid);
        $ret.=mydb()->_query;
      }
      return $ret;
      break;

    case 'addinno' :
      $ret.=__project_form_s3_addinno($tpid);
      return $ret;
      break;

  }

  $ret.='<a name="top"></a>';

  $url='paper/'.$tpid.'/owner/s4';
  // Show form toolbar
  $ui=new ui();
  $ui->add('<a href="'.url($url).'">แบบรายงานสรุปเมื่อสิ้นสุดระยะเวลาโครงการ (ส.4)</a>');
  $ui->add('<a href="'.url($url,array('download'=>'word')).'" >ดาวน์โหลด</a>');
  $ui->add('<a href="javascript:window.print()">พิมพ์</a>');
  if (!post('download')) $ret.='<div class="reportbar -no-print">'.$ui->build('ul').'</div>';


  if ($isEdit) {
    $inlineAttr['data-update-url']=url('project/edit/tr');
    $inlineAttr['data-period']=$period;
    if (post('debug')) $inlineAttr['data-debug']='yes';
  }
 
  $ret.='<div id="project-report-s4" class="inline-edit report project__report -s4" '.sg_implode_attr($inlineAttr).'>'._NL;

  $ret.='<h3 class="noprint">แบบรายงานสรุปเมื่อสิ้นสุดระยะเวลาโครงการ (ส.4)</h3>'._NL;

  $ret.='<div class="remark"><p><strong>คำชี้แจง</strong><br />
  แบบรายงานฉบับนี้มีวัตถุประสงค์เพื่อรวบรวมผลการดำเนินงานของ สสส. ว่า ได้มีการสนับสนุนโครงการสร้างเสริมสุขภาพลักษณะใด ในเรื่องอะไร สอดคล้องกับวัตถุประสงค์กองทุน สสส. หรือไม่ รายงานฉบับนี้ไม่ได้เป็นการประเมินผลความสำเร็จรายโครงการ แต่เป็นการเก็บข้อมูลเพื่อการประมวลผลในภาพรวม และเป็นข้อเสนอแนะต่อ สสส. เพื่อใช้ในการพัฒนางานต่อไป โดยจัดทำรายงานนี้เพียงครั้งเดียว <u>เมื่อสิ้นสุดระยะเวลาโครงการ</u></p>
  <p>(ข้อมูลเหล่านี้จะเป็นประโยชน์ต่อตัวท่านเองในการนำเสนอโครงการในครั้งต่อไป โปรดกรอกรายละเอียดให้ครบ)</p></div>';

  $section='title';
  $irs=end($rs->items[$section]);

  // section :: ชื่อโครงการ
  $ret.='<div class="box">';
  $ret.='<p>รหัสโครงการ '.$topic->project->prid.' เลขที่ข้อตกลง '.$topic->project->agrno.'</p>';
  $ret.='<p><strong>ชื่อโครงการ '.$topic->project->title.'</strong></p>';
  $ret.='<p></p>';
  $ret.='<p>ชุมชน '.$topic->project->area.' จังหวัด '.$topic->project->provname.'</p>';
  $ret.='<p></p>';
  $ret.='<p>หัวหน้าโครงการ '.$topic->project->prowner.'</p>'._NL;
  if($topic->project->prteam) $ret.='<p>คณะทำงาน '.$topic->project->prteam.'</p>'._NL;
  if($topic->project->org) $ret.='<p>ชื่อหน่วยงานที่ได้รับทุน : '.$topic->project->org.'</p>'._NL;
  $ret.='<p></p>';
  $ret.='<p>ระยะเวลาดำเนินงาน ตั้งแต่ '.($topic->project->date_from?sg_date($topic->project->date_from,'ว ดดด ปปปป'):'').' ถึง '.($topic->project->date_end?sg_date($topic->project->date_end,'ว ดดด ปปปป'):'').'</p>';
  $ret.='</div>';


  $ret.='<h3>ผู้ดำเนินโครงการ (Organizer)</h3>'._NL;
  $ret.='<h4>1. ผู้มีส่วนร่วมในการดำเนินการโครงการ</h4>'._NL;
  $ret.='<ul>
  <li>ทีมงานในองค์กรที่มีส่วนร่วมในโครงการ '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num1','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num1),$isEdit).' คน</li>
  <li>บุคคลภายนอก (เช่น วิทยากร ผู้ทรงคุณวุฒิ) '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num2','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num2),$isEdit).' คน</li>
  </ul>'._NL;

  $ret.='<h3>กลุ่มเป้าหมาย (Target groups)</h3>'._NL;
  $ret.='<h4>2. กลุ่มเป้าหมายที่เข้าร่วมโครงการ</h4>'._NL;
  $ret.='<p>2.1 แบ่งตาม<u>กลุ่มอายุ</u>ของกลุ่มเป้าหมายที่เข้าร่วมโครงการ<br />โปรดระบุกลุ่มเป้าหมายที่โครงการกำหนด และ กลุ่มเป้าหมายที่เข้าร่วมโครงการจริง</p>';

  $tables = new Table();
  $tables->addClass('-target');
  $tables->thead='<tr><th rowspan="2">กลุ่มอายุ</th><th colspan="3">กลุ่มเป้าหมายที่โครงการกำหนด</th><th colspan="3">กลุ่มเป้าหมายที่เข้าร่วมโครงการจริง</th></tr><tr><th>ชาย(คน)</th><th>หญิง(คน)</th><th>รวม(คน)</th><th>ชาย(คน)</th><th>หญิง(คน)</th><th>รวม(คน)</th></tr>';

  $section='target_1';
  $irs=end($report->items[$section]);
  $tables->rows[]=array(
                  'เด็กเล็ก (ต่ำกว่า 6 ปี)',
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num1','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num1),$isEdit),
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num2','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num2),$isEdit),
                  $irs->num1+$irs->num2,
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num3','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num3),$isEdit),
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num4','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num4),$isEdit),
                  $irs->num3+$irs->num4,
                  );

  $section='target_2';
  $irs=end($report->items[$section]);
  $tables->rows[]=array(
                  'เด็ก (6-14 ปี)',
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num1','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num1),$isEdit),
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num2','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num2),$isEdit),
                  $irs->num1+$irs->num2,
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num3','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num3),$isEdit),
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num4','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num4),$isEdit),
                  $irs->num3+$irs->num4,
                  );

  $section='target_3';
  $irs=end($report->items[$section]);
  $tables->rows[]=array(
                  'วัยรุ่น (15-24 ปี)',
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num1','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num1),$isEdit),
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num2','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num2),$isEdit),
                  $irs->num1+$irs->num2,
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num3','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num3),$isEdit),
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num4','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num4),$isEdit),
                  $irs->num3+$irs->num4,
                  );

  $section='target_4';
  $irs=end($report->items[$section]);
  $tables->rows[]=array(
                  'ผู้ใหญ่ (25-59 ปี)',
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num1','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num1),$isEdit),
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num2','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num2),$isEdit),
                  $irs->num1+$irs->num2,
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num3','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num3),$isEdit),
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num4','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num4),$isEdit),
                  $irs->num3+$irs->num4,
                  );

  $section='target_5';
  $irs=end($report->items[$section]);
  $tables->rows[]=array(
                  'ผู้สูงอายุ (60 ปีขึ้นไป)',
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num1','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num1),$isEdit),
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num2','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num2),$isEdit),
                  $irs->num1+$irs->num2,
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num3','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num3),$isEdit),
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num4','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num4),$isEdit),
                  $irs->num3+$irs->num4,
                  );

  $ret.=$tables->build();


  $ret.='<p>2.2 แบ่งตามกลุ่มเป้าหมายเฉพาะ</p>';
  $tables = new Table();
  $tables->addClass('-target');
  $tables->thead=array('กลุ่ม','amt'=>'จำนวน (คน)');

  $section='target_sp';
  $irs=end($report->items[$section]);
  $tables->rows[]=array(
                  'ประชาชนทั่วไป',
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num1','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num1),$isEdit)
                  );
  $tables->rows[]=array(
                  'ผู้กำหนดนโยบาย(ผู้มีอำนาจในการกำหนดนโยบายระดับองค์กร ท้องถิ่น ประเทศ)',
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num2','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num2),$isEdit)
                  );
  $tables->rows[]=array(
                  'นักวิชาการ (เช่น นักวิจัย อาจารย์มหาวิทยาลัย)',
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num3','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num3),$isEdit)
                  );
  $tables->rows[]=array(
                  'ผู้ปฏิบัติงานในองค์กรต่าง ๆ',
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num4','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num4),$isEdit)
                  );
  $tables->rows[]=array(
                  'สื่อมวลชน',
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num5','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num5),$isEdit)
                  );
  $tables->rows[]=array(
                  'ผู้พิการ',
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num6','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num6),$isEdit)
                  );
  $tables->rows[]=array(
                  'กลุ่มอื่น ๆ ระบุ '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid, 'class'=>'w-5'),$irs->detail1,$isEdit),
                  view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'num7','tr'=>$irs->trid,'ret'=>'numeric'),number_format($irs->num7),$isEdit)
                  );
  $ret.=$tables->build();


  $section='area';
  $irs=end($report->items[$section]);
  $ret.='<h3>พื้นที่ดำเนินการ (Target Area)</h3>'._NL;
  $ret.='<h4>3. พื้นที่ดำเนินโครงการ (แบ่งตามเขตการปกครอง)</h4>';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid, 'class'=>'w-5', 'value'=>$irs->detail1),'เขต/เทศบาลเมือง/เทศบาลนคร',$isEdit,'radio').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid, 'class'=>'w-5', 'value'=>$irs->detail1),'เทศบาลตำบล',$isEdit,'radio').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid, 'class'=>'w-5', 'value'=>$irs->detail1),'นอกเขตเทศบาล',$isEdit,'radio').'<br />';
  $ret.='<p>ระบุพื้นที่<br />';
  $ret.='ตำบล '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid, 'class'=>'w-5'),$irs->text1,$isEdit).'<br />';
  $ret.='อำเภอ '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid, 'class'=>'w-5'),$irs->text2,$isEdit).'<br />';
  $ret.='จังหวัด '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid, 'class'=>'w-5'),$irs->text3,$isEdit).'<br />';
  $ret.='รหัสไปรษณีย์ '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text4','tr'=>$irs->trid, 'class'=>'w-5'),$irs->text4,$isEdit).'<br />';
  $ret.='</p>'._NL;


  $section='goal';
  $irs=end($report->items[$section]);
  $ret.='<h3>เป้าประสงค์</h3>';
  $ret.='<h4>4. โครงการมุ่งตอบเป้าประสงค์ดังนี้</h4>(เลือกได้มากกว่า 1 ข้อ)<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid, 'value'=>$irs->text1),'การสร้างนโยบายสาธารณะที่เอื้อต่อสุขภาพโดยเฉพาะในระดับท้องถิ่น',$isEdit,'checkbox').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid, 'value'=>$irs->text2),'การสร้างสรรค์สิ่งแวดล้อมที่เอื้อต่อการสร้างเสริมสุขภาพชุมชน',$isEdit,'checkbox').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid, 'value'=>$irs->text3),'การเสริมสร้างชุมชนสุขภาพดีและเข้มแข็ง',$isEdit,'checkbox').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text4','tr'=>$irs->trid, 'value'=>$irs->text4),'การพัฒนาทักษะส่วนบุคคลที่จำเป็นเพื่อการมีสุขภาพดี',$isEdit,'checkbox').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text5','tr'=>$irs->trid, 'value'=>$irs->text5),'ปรับเปลี่ยนระบบบริการสุขภาพ ที่เน้นการร่วมคิดร่วมทำของชุมชน องค์กรท้องถิ่น และหน่วยบริการสุขภาพในระดับชุมชน',$isEdit,'checkbox').'<br />';

  $section='strategic';
  $irs=end($report->items[$section]);
  $ret.='<h3>ยุทธศาสตร์ สสส.</h3>';
  $ret.='<h4>5. โครงการมุ่งตอบยุทธศาสตร์ สสส. ดังนี้</h4>(เลือกได้มากกว่า 1 ข้อ)<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid, 'value'=>$irs->text1),'พลังปัญญา (ขยายพื้นที่ทางปัญญา โดยการดำเนินงานด้วยความรู้ ตลอดจนอาศัยกระบวนการเรียนรู้ และการมีส่วนร่วม)',$isEdit,'checkbox').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid, 'value'=>$irs->text2),'พลังนโยบาย (ขยายพื้นที่การมีส่วนร่วมในกระบวนการนโยบายสาธารณะ)',$isEdit,'checkbox').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid, 'value'=>$irs->text3),'พลังสังคม (ขยายพื้นที่ทางสังคม เกิดเครือข่ายทางสังคมในการเฝ้าระวัง การรณรงค์ การดำเนินงาน สร้างเสริมสุขภาพอย่างต่อเนื่อง ตลอดจนการพัฒนาทักษะการร่วมงานกันในลักษณะเครือข่าย)',$isEdit,'checkbox').'<br />';



  $section='objective';
  $irs=end($report->items[$section]);
  $ret.='<h3>วัตถุประสงค์ สสส.</h3>';
  $ret.='<h4>6. โครงการมุ่งตอบวัตถุประสงค์ สสส. ดังนี้</h4>(เลือกได้มากกว่า 1 ข้อ)<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid, 'value'=>$irs->text1),'ส่งเสริมและสนับสนุนการสร้างเสริมสุขภาพในประชากรทุกวัยตามนโยบายสุขภาพแห่งชาติ',$isEdit,'checkbox').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid, 'value'=>$irs->text2),'สร้างความตระหนักเรื่องพฤติกรรมการเสี่ยงจากการบริโภคสุรา ยาสูบ หรือสาร หรือสิ่งอื่นที่ทำลายสุขภาพและสร้างความเชื่อในการสร้างเสริมสุขภาพแก่ประชาชนทุกระดับ',$isEdit,'checkbox').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid, 'value'=>$irs->text3),'สนับสนุนการรณรงค์ให้ลดบริโภคสุรา ยาสูบ หรือสาร หรือสิ่งอื่นที่ทำลายสุขภาพ ตลอดจนให้ประชาชนได้รับรู้ข้อกฎหมายที่เกี่ยวข้อง',$isEdit,'checkbox').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text4','tr'=>$irs->trid, 'value'=>$irs->text4),'ศึกษาวิจัยหรือสนับสนุนให้มีการศึกษาวิจัย ฝึกอบรม หรือดำเนินการให้มีการประชุมเกี่ยวกับการสนับสนุนการสร้างเสริมสุขภาพ',$isEdit,'checkbox').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text5','tr'=>$irs->trid, 'value'=>$irs->text5),'พัฒนาความสามารถของชุมชนในการสร้างเสริมสุขภาพโดยชุมชนหรือองค์กรเอกชน องค์กรสาธารณประโยชน์ ส่วนราชการ รัฐวิสาหกิจ หรือหน่วยงานอื่นของรัฐ',$isEdit,'checkbox').'<br />';
  $ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text6','tr'=>$irs->trid, 'value'=>$irs->text6),'สนับสนุนการรณรงค์สร้างเสริมสุขภาพผ่านกิจกรรมต่างๆ ในลักษณะที่เป็นสื่อเพื่อให้ประชาชนสร้างเสริมสุขภาพให้แข็งแรง ใช้เวลาว่างให้เป็นประโยชน์ และลดบริโภคสุรา ยาสูบ หรือสารหรือสิ่งอื่นที่ทำลายสุขภาพ',$isEdit,'checkbox').'<br />';


  $section='strategy';
  $irs=end($report->items[$section]);
  $ret.='<h3>กลยุทธ์การดำเนินงาน (Strategies)</h3>';
  $ret.='<h4>7. โครงการของท่านใช้กลยุทธ์ใดในการดำเนินงาน</h4>(เลือกได้มากกว่า 1 ข้อ)<br />';
  $tables = new Table();
  $strategyList=array(
                  1=>'การสร้างกระแสสังคมเพื่อให้ตระหนักต่อการสร้างเสริมสุขภาพ (Social Mobilization)',
                  2=>'การสร้างกระแสสังคมเพื่อให้ผู้มีอำนาจตัดสินใจกำหนดนโยบาย (Advocacy)',
                  3=>'การตลาดเพื่อสังคม (Social Marketing)',
                  4=>'การจัดกระบวนการเรียนรู้ (Educational Processes)',
                  5=>'พัฒนานโยบายสาธารณะที่เกี่ยวข้องกับสุขภาพ (Healthy Public Policy Development)',
                  6=>'พัฒนาปัจจัยแวดล้อม (Supportive Environment)',
                  7=>'พัฒนาองค์ความรู้ (Knowledge Development)',
                  8=>'พัฒนาศักยภาพและการมีส่วนร่วมของชุมชน (Community Development)',
                  9=>'พัฒนาศักยภาพและนโยบายองค์กร (Organization Development)',
                  10=>'พัฒนาศักยภาพบุคคล (Personal Skill Development)',
                  11=>'สร้างและพัฒนาเครือข่าย (Networking)',
                  12=>'การประสานงานกับส่วนภายนอกระบบสุขภาพ (Inter-Sectoral Coordination)',
                  13=>'การปรับระบบบริการสุขภาพ (Reorient Health Service)',
                  99=>'อื่น ๆ',
                  );
  $i=0;
  foreach ($strategyList as $key => $item) {
    $section='strategy-'.$key;
    $irs=end($report->items[$section]);
    $tables->rows[]=array(
                      ++$i.')',
                      $item.($key==99?' ระบุ '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid, 'value'=>$irs->text1, 'class'=>'w-5'),$irs->text1,$isEdit):''),
                      view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid, 'value'=>$irs->detail1, 'removeempty'=>'yes'),$key.':',$isEdit,'checkbox')
                      );
  }
  $ret.=$tables->build();
  $ret.='<p>กลยุทธ์ในการดำเนินโครงการที่สำคัญมากที่สุด (เลือกเพียงข้อเดียว)</p>';
  $ret.='<p>(ถ้ามี) กลยุทธ์ในการดำเนินโครงการที่สำคัญมากเป็นอันดับ 2 คือ (เลือกเพียงข้อเดียว)</p>';



  $section='activity';
  $irs=end($report->items[$section]);
  $ret.='<h3>กิจกรรมหลัก (Activity)</h3>';
  $ret.='<h4>8. โครงการมีกิจกรรมหลักอะไรบ้าง</h4>(เลือกได้มากกว่า 1 ข้อ)<br />';
  $tables = new Table();
  $strategyList=array(
                  1=>'การประชุม/อบรม/สัมนาทางวิชาการ',
                  2=>'การศึกษาวิจัย',
                  3=>'การพัฒนาสื่อ/เครื่องมือ การสื่อสาร เพื่อการสร้างเสริมสุขภาพ',
                  4=>'การจัดการเรียนการสอนหรือกระบวนการเรียนรู้ต่อกลุ่มเป้าหมาย',
                  5=>'นิทรรศการ การแข่งขัน หรือการประกวด',
                  6=>'การรณรงค์',
                  7=>'การจัดกิจกรรมอื่นกับกลุ่มเป้าหมาย',
                  8=>'การปรับระบบงานและจัดสภาพแวดล้อมที่สร้างเสริมสุขภาพ',
                  9=>'การเผยแพร่ข้อมูล',
                  99=>'อื่น ๆ',
                  );
  $i=0;
  foreach ($strategyList as $key => $item) {
    $section='activity-'.$key;
    $irs=end($report->items[$section]);
    $tables->rows[]=array(
                      ++$i.')',
                      $item.($key==99?' ระบุ '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid, 'value'=>$irs->text1, 'class'=>'w-5'),$irs->text1,$isEdit):''),
                      view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid, 'value'=>$irs->detail1, 'removeempty'=>'yes'),$key.': ',$isEdit,'checkbox')
                      );
  }
  $ret.=$tables->build();
  $ret.='<p>กิจกรรมหลักที่สำคัญมากที่สุด (เลือกเพียงข้อเดียว)</p>';
  $ret.='<p>(ถ้ามี) กิจกรรมหลักที่สำคัญมากเป็นอันดับ 2 คือ (เลือกเพียงข้อเดียว)</p>';




  $ret.='</div><!-- project-report-s4 -->'._NL;

  $ret.='<a href="#top" class="noprint">ไปบนสุด</a>'._NL;

  $style='<style type="text/css">
  .item.-target td:nth-child(n+2) {text-align:center;}
  </style>';
  if ($download) {
    sendheader('application/octet-stream');
    mb_internal_encoding("UTF-8");
    header("Content-type: application/vnd.ms-word");
    header('Content-Disposition: attachment; filename="'.mb_substr($topic->title,0,50).'-ส3-'.date('Y-m-d').'.doc"');
    // move style tag to head section
    $body=$ret;
    if (preg_match_all('/<style.*?>.*?<\/style>/si',$body,$out)) {
      foreach ($out[0] as $style) $styles.=$style._NL;
      $body=preg_replace('/(<style.*?>.*?<\/style>)/si','',$body);
    }
    $ret='<HTML>
    <HEAD>
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
    <TITLE>'.$topic->title.'</TITLE>
    '.$styles.'
    </HEAD>
    <BODY>
    '.$body.'
    </BODY>
    </HTML>';
    die($ret);
  } else {
    $ret.=$style;
  }
  return $ret;
}
/*




9. กิจกรรมของท่านเกิดขึ้นในพื้นที่ใด/อยู่ในองค์กรประเภทใด (เลือก              ได้มากกว่า 1 ข้อ)

1)  สถานศึกษา/โรงเรียน

2)  สถานพยาบาล/โรงพยาบาล

3)  สถานประกอบการ (สถานที่ทำงานลักษณะต่างๆ เช่น โรงงาน, สำนักงาน, ร้านค้า เป็นต้น)

4)  ชุมชน ( เช่น วัด หมู่บ้าน เป็นต้น)

5)  สื่อมวลชน

6)  องค์กรเครือข่าย

7)  อื่น ๆ (ระบุ)  ………………………………………






10. โครงการฯ เกี่ยวข้องกับการสร้างเสริมสุขภาพในประเด็นอะไรเป็นหลัก (เลือก            ได้มากกว่า  1 ข้อ)

1) สุขภาพจิต

7)   อุบัติเหตุและเสริมสร้างความปลอดภัย

2) ออกกำลังกาย

8)   เพศสัมพันธ์/พฤติกรรมทางเพศ

3) การบริโภคยาสูบ

9)   สิ่งแวดล้อม

4) การบริโภคอาหาร

10)  การคุ้มครองผู้บริโภค

5) การบริโภคแอลกอฮอล์

11)   สุขภาพองค์รวม

6) สารเสพติด

12)   อื่น ๆ (ระบุ)  ………………………



11. ผลงานโครงการของท่านได้มีการเผยแพร่สู่สาธารณะผ่านช่องทางเหล่านี้หรือไม่  (โปรดระบุ                ชื่อเรื่อง/ประเด็น ที่ใช้ในการสื่อสาร  เลือก            ได้มากกว่า  1 ข้อ)
ช่องทางสื่อ
ชื่อเรื่อง/ประเด็น
หนังสือพิมพ์


โทรทัศน์


วิทยุ


อินเตอร์เนท


เอกสาร/คู่มือ/ชุดความรู้


อื่นๆ ระบุ ………….……….......





12.  มีนวัตกรรมที่เกิดจากโครงการหรือไม่ (ถ้ามี นวัตกรรมที่เกิดจากโครงการเป็นนวัตกรรมแบบใด)
ไม่มี             
มี           การพัฒนาความรู้ใหม่จาการวิจัยและพัฒนา (R&D)
การนำสิ่งที่มีอยู่ในชุมชนอื่นมาพัฒนาหรือปรับใช้ในชุมชนของตนเอง
การนำสิ่งที่ทำอยู่มาปรับกระบวนทัศน์ใหม่หรือทำด้วยวิธีใหม่แล้วได้ผล



13. โครงการนี้มีการร่วมมือกับองค์กร (หน่วยงานอื่น) หรือไม่

ไม่มี
มี
ถ้ามี (โปรดระบุชื่อองค์กร)


1…………………………………………………………………………..
2…………………………………………………………………………..
3…………………………………………………………………………..


14. โครงการได้รับการสนับสนุนทุนในการรวมกลุ่มกิจกรรมได้ด้วยตนเอง หรืออาศัยทุนในชุมชน หรือหน่วยงานในชุมชนหรือไม่

ไม่มี
มี
ถ้ามี (โปรดระบุชื่อหน่วยงาน)


1…………………………………………………………………………......................
2…………………………………………………………………………......................
3…………………………………………………………………………......................


15. สิ่งที่ทำให้โครงการของท่านเกิดความต่อเนื่องยั่งยืน   ถ้ามีโปรดระบุ  
มีแผนการทำงานร่วมกับหน่วยงานในท้องถิ่นต่อเนื่อง/ การบรรจุเข้าไปในแผนท้องถิ่น 
เรื่อง ...................................................................................................................................
เกิดมาตรการร่วมที่จะนำไปสู่การปรับเปลี่ยนพฤติกรรมและสภาพแวดล้อมในชุมชน (เช่น มาตรการลดละเลิกเหล้า/บุหรี่ในชุมชน  การจัดการขยะ  การลดใช้สารเคมีทางการเกษตร การใช้พลังงานทางเลือกฯ)  โปรดระบุ .....................................................................................................
อื่น ๆ โปรดระบุ...................................................................................................................  
……………………………………………………………………………………………………            


16.การจัดพื้นที่ปลอดบุหรี่
จัดให้มีพื้นที่ปลอดบุหรี่ก่อนที่จะเริ่มโครงการนี้

จัดให้มีพื้นที่ปลอดบุหรี่เฉพาะในช่วงดำเนินโครงการนี้ (ระยะสั้น)

จัดให้มีพื้นที่ปลอดบุหรี่เฉพาะในช่วงมีกิจกรรม

จัดให้มีพื้นที่ปลอดบุหรี่โดยถาวร

ไม่สามารถดำเนินการได้




.........................................................................................................................................................................
.........................................................................................................................................................................
.........................................................................................................................................................................
.........................................................................................................................................................................
.........................................................................................................................................................................
*/


