<?php
/**
 * ibuy class for shop on web
 *
 * @package ibuy
 * @version 0.11
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2009-06-22
 * @modify 2012-06-20
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class ibuy extends module {
var $items=20;

	/**
	 * class constructor
	 */
	public function __construct() {
		parent::__construct();
		$q1=q(1);
		R::View('ibuy.'.($q1 && !is_numeric($q1)?$q1.'.':'').'menu.global');
	}

	/**
	 * Method extension when get_topic_by_id was complete
	 *
	 * @param Object $self
	 * @param Object $rs
	 * @param Object $para
	 * @return void
	 */
	function __get_topic_by_id($self,$rs,$para) {
	 // Add product detail from ibuy_product table into $rs
		$rs->_relate_url='ibuy';
		$product=mydb::select('SELECT * FROM %ibuy_product% WHERE `tpid`='.$rs->tpid.' LIMIT 1');
		foreach ($product as $key=>$value) if (substr($key,0,1)!='_') $rs->{$key}=$value;
	}

	/**
	 * Method extension when create paper post form
	 *
	 * @param Object $self
	 * @param Object $topic
	 * @param Object $para
	 * @param Object $form
	 */
	function __post_form($self,$topic,$para,$form) {
		$self->theme->title=$topic->type->name;
		unset($self->theme->header);

		$form->section_01_s = '<fieldset><legend>Product information</legend>';

		$form->listprice->type='text';
		$form->listprice->label='ราคาหน้าร้าน (บาท)';
		$form->listprice->size=10;
		$form->listprice->maxlength=10;
		$form->listprice->value=$topic->post->listprice;

		$form->price1->type='text';
		$form->price1->label='ราคาลูกค้าทั่วไป (บาท)';
		$form->price1->size=10;
		$form->price1->maxlength=10;
		$form->price1->value=$topic->post->price1;

		$form->price2->type='text';
		$form->price2->label='ราคา V.I.P. (บาท)';
		$form->price2->size=10;
		$form->price2->maxlength=10;
		$form->price2->value=$topic->post->price2;

		$form->price3->type='text';
		$form->price3->label='ราคา Gold (บาท)';
		$form->price3->size=10;
		$form->price3->maxlength=10;
		$form->price3->value=$topic->post->price3;

		$form->price4->type='text';
		$form->price4->label='ราคา 004 (บาท)';
		$form->price4->size=10;
		$form->price4->maxlength=10;
		$form->price4->value=$topic->post->price4;

		$form->price5->type='text';
		$form->price5->label='ราคา 005 (บาท)';
		$form->price5->size=10;
		$form->price5->maxlength=10;
		$form->price5->value=$topic->post->price5;

		$form->resalerprice->type='text';
		$form->resalerprice->label='ราคาตัวแทนจำหน่าย (บาท)';
		$form->resalerprice->size=10;
		$form->resalerprice->maxlength=10;
		$form->resalerprice->value=$topic->post->resalerprice;

		$form->retailprice->type='text';
		$form->retailprice->label='ราคาเฟรนส์ไชน์ (บาท)';
		$form->retailprice->size=10;
		$form->retailprice->maxlength=10;
		$form->retailprice->value=$topic->post->retailprice;

		$form->cost->type='text';
		$form->cost->label='ราคาทุน (บาท)';
		$form->cost->size=10;
		$form->cost->maxlength=10;
		$form->cost->value=$topic->post->cost;

		if (cfg('ibuy.stock.use')) {
			$form->balance->type='text';
			$form->balance->label='ยอดคงเหลือ (ชิ้น)';
			$form->balance->size=10;
			$form->balance->maxlength=7;
			$form->balance->value=number_format($topic->post->balance);
		}

		if (cfg('ibuy.resaler.discount')>0) {
			$form->isdiscount->type='radio';
			$form->isdiscount->label='การคำนวณส่วนลด :';
			$form->isdiscount->options[0]='ไม่ ไม่นำมาคำนวณส่วนลดเมื่อมีการสั่งซื้อสินค้า';
			$form->isdiscount->options[1]='ไช่ นำมาคำนวณส่วนลดเมื่อมีการสั่งซื้อสินค้า';
			$form->isdiscount->value=$topic->post->isdiscount;
		}

		if (cfg('ibuy.franchise.marketvalue')>0) {
			$form->ismarket->type='radio';
			$form->ismarket->label='คำนวณค่าการตลาด :';
			$form->ismarket->options[0]='ไม่ ไม่นำมาคำนวณค่าการตลาดเมื่อมีการสั่งซื้อสินค้า';
			$form->ismarket->options[1]='ไช่ นำมาคำนวณค่าการตลาดเมื่อมีการสั่งซื้อสินค้า';
			$form->ismarket->value=$topic->post->ismarket;
		}

		if (cfg('ibuy.franchise.franchisor')>0) {
			$form->isfranchisor->type='radio';
			$form->isfranchisor->label='คำนวณยอดสำหรับเจ้าของเฟรนส์ไชน์ :';
			$form->isfranchisor->options[0]='ไม่ ไม่นำมาคำนวณยอดสำหรับเจ้าของเฟรนส์ไชน์เมื่อมีการสั่งซื้อสินค้า';
			$form->isfranchisor->options[1]='ไช่ นำมาคำนวณยอดสำหรับเจ้าของเฟรนส์ไชน์เมื่อมีการสั่งซื้อสินค้า';
			$form->isfranchisor->value=SG\getFirst($topic->post->isfranchisor,1);
		}

		$form->isnew->type='radio';
		$form->isnew->label='แสดงในรายการสินค้ามาใหม่ :';
		$form->isnew->options[1]='แสดง';
		$form->isnew->options[0]='ไม่แสดง';
		$form->isnew->value=SG\getFirst($topic->post->isnew,0);

		$form->section_01_e = '</fieldset>';

		$form->product_detail_s = '<fieldset><legend>Product information</legend>';

		$form->body=$form->body;
		unset($form->body->label);

		$form->product_detail_e = '</fieldset>';

		//property_reorder($form,'product_info','before body');
		//property_reorder($form,'product_detail','after product_info');

		unset($form->email,$form->website,$form->body);
		unset($form->sticky);

		$form->input_format->type='hidden';
	}

	/**
	 * Method extension when save paper to database complete
	 *
	 * @param Object $self
	 * @param Object $topic
	 * @param Object $para
	 * @param Object $form Data post form
	 * @return void
	 */
	function __post_save($self,$topic,$para,$form) {
		$ibuy['tpid']=$topic->tpid;
		$ibuy['listprice']=$topic->post->listprice;
	
		$ibuy['price1']=$topic->post->price1;
		$ibuy['price2']=$topic->post->price2;
		$ibuy['price3']=$topic->post->price3;
		$ibuy['price4']=$topic->post->price4;
		$ibuy['price5']=$topic->post->price5;
		
		$ibuy['retailprice']=$topic->post->retailprice;
		$ibuy['resalerprice']=$topic->post->resalerprice;
		$ibuy['cost']=$topic->post->cost;

		$ibuy['balance']=	cfg('ibuy.stock.use')?$topic->post->balance:0;
		$ibuy['isdiscount']=$topic->post->isdiscount;
		$ibuy['ismarket']=$topic->post->ismarket;
		$ibuy['isfranchisor']=$topic->post->isfranchisor;
		$ibuy['isnew']=$topic->post->isnew;
		mydb::query(db_create_insert_cmd('%ibuy_product%',$ibuy));
	}

	/**
	 * Method extension when save paper content was complete
	 *
	 * @param Object $self
	 * @param Object $topic
	 * @return Relocatio to vuew detail
	 */
	function __post_complete($self,$topic) {
		location('ibuy/'.$topic->tpid);
		die;
	}

	/**
	 * Method extension when paper edit detail complete
	 *
	 * @param Object $self
	 * @param Object $topic
	 * @param Object $para
	 * @param Object $data
	 * @return void Relocation to ibuy product detail
	 */
	function __edit_complete($self,$topic,$para,$data) {
		location('ibuy/'.$topic->tpid);
	}

	/**
	 * Method extension when before delete content from database was process
	 *
	 * @param Object $self
	 * @param Object $topic
	 * @param Object $para
	 * @param Object $result
	 * @return void
	 */
	function __delete($self,$topic,$para,$result) {
		$rs=mydb::select('SELECT tpid FROM %ibuy_ordertr% WHERE `tpid`=:tpid ORDER BY tpid ASC LIMIT 1',':tpid',$topic->tpid);
		if ($rs->_num_rows) {
			$result->error='ไม่สามารถลบสินค้ารายการนี้ได้ : สินค้านี้มีการใช้งานอยู่หรือมีรายการสั่งซื้อที่บันทึกไปแล้ว';
			return false;
		}

		$simulate = debug('simulate');
		$result->process[]='Delete ibuy topic'.($simulate?' was simulate':' process');
		mydb::query('DELETE FROM %ibuy_product% WHERE `tpid`='.$topic->tpid.' LIMIT 1',$simulate);
		$result->process[]=db_query_cmd();
		return true;
	}

	/**
	 * Method extension when paper delete was complete
	 *
	 * @param Object $self
	 * @param Object $topic
	 * @param Object $para
	 * @param Object $result
	 * @return void
	 */
	function __delete_complete($self,$topic,$para,$result) {
		location('ibuy');
	}

	function __view_load($self,$topic,$para,$result) {
		if (i()->am=='' && cfg('ibuy.showfor.public')=='PUBLIC' && $topic->showfor!='PUBLIC') {
			$topic->status=_BLOCK;
		}
	}

} // end of class ibuy
?>