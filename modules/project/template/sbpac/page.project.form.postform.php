<?php
/**
* Project owner
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_postform($self,$topic,$para,$form) {

		unset($form->body);
		unset($form->email,$form->website);
		unset($form->sticky);
		unset($form->tabs);

		$myOrg=mydb::select('SELECT * FROM %org_officer% oc LEFT JOIN %db_org% o USING(`orgid`) WHERE oc.`uid`=:uid ',':uid',i()->uid);

		if ($myOrg->_empty) {
			$form->error='<p class="notify">ขออภัย ท่านยังไม่ได้กำหนด <strong>สำนัก/กรม/กอง/หน่วยงาน</strong><br />กรุณาแจ้ง <strong>username และ ชื่อหน่วยงานของท่าน</strong> ต่อผู้ดูแลเว็บไซท์เพื่อดำเนินการกำหนด สำนัก/กรม/กอง/หน่วยงาน และให้สิทธ์ในการจัดการโครงการต่อไป<p>';

			unset($form->title,$form->submit);
			$form->poster->type='hidden';
			return $form;
		}

		$myProjectCount=mydb::select('SELECT COUNT(*) total FROM %topic% WHERE `type`="project" AND `uid`=:uid LIMIT 1',':uid',i()->uid)->total;
		if ($myProjectCount) $form->top='<p class="notify"><strong>คำเตือน !!!! คุณมีโครงการในความรับผิดชอบอยู่จำนวน '.$myProjectCount.' โครงการ<br />คุณแน่ใจหรือไม่ว่ากำลังจะสร้างโครงการติดตามใหม่? หรือเพียงแค่ต้องการรายงานกิจกรรมของโครงการเดิม<br />หากต้องการสร้างโครงการใหม่ ให้ป้อนข้อมูลในแบบฟอร์มด้านล่าง แล้วคลิก "บันทึกโครงการใหม่"<br />หากเป็นการรายงานกิจกรรมของโครงการเดิม ให้คลิกที่รายชื่อโครงการด้านล่าง แล้วทำการสร้างกิจกรรมในปฏิทินโครงการ และบันทึกกิจกรรมในรายงานผู้รับผิดชอบ</strong> </p>';

		$self->theme->title='เพิ่มโครงการใหม่';

		$property=property('project');

		$form->title->label='ชื่อโครงการ';

		$form->pryear->type='radio';
		$form->pryear->label='ประจำปีงบประมาณ';
		for ($year=date('Y')-1; $year<=date('Y')+1; $year++) {
			$form->pryear->options[$year]=$year+543;
		}
		$form->pryear->display='inline';
		$form->pryear->value=SG\getFirst($post->pryear,date('Y'));

		if (user_access('administer projects')) {
			$form->orgid->type='hidden';
			$form->orgname->label='หน่วยงานเจ้าของโครงการ';
			$form->orgname->type='text';
			$form->orgname->class='sg-autocomplete w-9';
			$form->orgname->attr=array('data-query'=>url('org/api/org'), 'data-altfld'=>'edit-topic-orgid');
			$form->orgname->size=40;
			$form->orgname->placeholder='ป้อนชื่อหน่วยงาน';
		} else if ($myOrg->_num_rows==1) {
			$form->orgid->type='hidden';
			$form->orgid->value=$myOrg->items[0]->orgid;
			$self->theme->title.='ของหน่วยงาน "'.$myOrg->items[0]->name.'"';
			$form->title->label.='ของหน่วยงาน "'.$myOrg->items[0]->name.'"';
		} 	else {
			$form->orgid->type='select';
			$form->orgid->label='หน่วยงานเจ้าของโครงการ';
			foreach ($myOrg->items as $item) {
				$form->orgid->options[$item->orgid]=$item->name;
			}
		}

		if (user_access('administer projects')) {
			$form->prtype->type='radio';
			$form->prtype->label='ประเภทโครงการ';
			$form->prtype->options=array('1'=>'โครงการ','แผนงาน','ชุดโครงการ');
			$form->prtype->value=1;
			$form->prtype->display='inline';
		} else {
			$form->prtype->type='hidden';
			$form->prtype->label='ประเภทโครงการ';
			$form->prtype->value=1;
		}

		$form->submit->items->save='บันทึกโครงการใหม่';
		$form->poster->type='hidden';

		$form->bottom='<h3>รายชื่อโครงการในความรับผิดชอบ</h3>'._NL.'<div data-load="project/list?u='.i()->uid.'"></div>';

		head('<script type="text/javascript">
		$(document).ready(function() {
			$("#edit-topic").submit(function() {
				if ($("#edit-topic-title").val()=="") {
					notify("กรุณาป้อนชื่อโครงการ");
					$("#edit-topic-title").focus();
					return false;
				}
			});
		});
		</script>');

		unset($form->submit->items->preview,$form->submit->items->cancel,$form->submit->items->text,$form->submit->items->draft,$form->submit->description,$form->video);

		property_reorder($form,'pryear','before title');
		property_reorder($form,'prtype','after pryear');
		property_reorder($form,'orgid','after pryear');
		property_reorder($form,'orgname','after pryear');
		property_reorder($form,'top','before pryear');

		$form->input_format->type='hidden';
	return $form;
}
?>