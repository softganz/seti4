<?php
/**
* Org Room :: Room Home Page
* Created 2021-09-23
* Modify  2021-09-23
*
* @return Widget
*
* @usage org/room
*/

$debug = true;
import('package:org/room/models/model.room.php');
class OrgRoomAnyedit extends Page {
    var $roomId;
    var $phone;
    var $items;
    var $tmp;
    function __construct($resvid,$phone) {
        $this->resvid = $resvid;
        $this->phone = $phone;
        $this->items = RoomModel::selectAnyEdit($this->resvid,$this->phone);
        $tmp = str_split($this->items->from_time,5);
        $this->items->from_time = $tmp[0];
        $tmp = str_split($this->items->to_time,5);
        $this->items->to_time = $tmp[0];
    }

 function build() {
     // If post name, save
    // if (post('resvName')) return $this->save();

    return new Scaffold([
    'appBar' => new AppBar([
    'title' => 'Room Booking',
    ]),
    'body' => new Widget([
    'children' => [
        new column([
            'style' => '',
            'children' => [
                $this->resvid.' '.$this->phone.' : '.$this->items->phone,
                new Form([
                    //'action' => url('org/room/booking/create'),
                    'action' => url('org/room/booking/api/'.$this->resvid.'/anyedit'),
                    'class' => 'sg-form',
                    'rel' => 'notify',
                    'done' => 'close | load:#main',
                    //'rel' => '.load_content',
                    //'done' => 'close | load:'.url('org/room/'.date_format($this->checkin,'m')),
                    'checkValid' => true,
                    'children' => [
                        '' =>[
                            'type' => 'hidden',
                            'label' => 'bkid',
                            'value' => $this->items->resvid,
                        ],
                        'title' => [
                            'type' => 'text',
                            'label' => 'ชื่อเรื่องการใช้ห้องประชุม',
                            'require' => true,
                            'value' => $this->items->title,
                        ],
                        'resvName' => [
                            'type' => 'text',
                            'label' => 'ชื่อ-นามสกุล ผู้ขอ',
                            'require' => true,
                            'value' => $this->items->resv_by
                        ],
                        'phone' => [
                            'type' => 'text',
                            'label' => 'โทรศัพท์',
                            'require' => true,
                            'value' => $this->items->phone,
                        ],
                        'org_name' => [
                            'type' => 'text',
                            'label' => 'ชื่อหน่วยงาน',
                            'require' => true,
                            'value' => $this->items->org_name,
                        ],
        
                        'org_type' => [
                            'type' => 'radio',
                            'label' => 'ประเภทหน่วยงาน',
                            'options'=>array('ppi'=>'โครงการบริการวิชาการสถาบันนโยบายสาธารณะ',
                                             'psu'=>'หน่วยงานภายใน',
                                             'out'=>'หน่วยงานภายนอก'),
                            'value'=>$this->items->org_type,
                            'require' => false,
                        ],
                        'checkin' => [
                            'type' => 'text',
                            'class' => 'sg-datepicker',
                            'label' => 'วันที่เริ่มใช้ห้อง',
                            'value' => date_format(date_create( $this->items->checkin),'d/m/Y'),
                            'require' => false,
                        ],
                        'from_time' => [
                            'type' => 'time',
                            'label' => 'เวลาที่เริ่มใช้ห้อง',
                            'require' => false,
                            'value' => $this->items->from_time,
                        ],
                        'checkout' => [
                            'type' => 'text',
                            'class' => 'sg-datepicker',
                            'label' => 'วันที่สิ้นสุดการใช้ห้อง',
                            //'value' => date('d/m/Y'),
                            'value' => date_format(date_create( $this->items->checkout),'d/m/Y'),
                            'require' => false,
                        ],
                        'to_time' => [
                            'type' => 'time',
                            'label' => 'เวลาที่สิ้นสุดการใช้ห้อง',
                            'require' => false,
                            'value' => $this->items->to_time,
                        ],
        
                        'roomid' => [
                            //'type' => 'checkbox',
                            'type'  => 'radio',
                            'label' => 'ห้องที่จอง',
                            'options'=>array('1401'=>'1401',
                                             '1402'=>'1402',
                                             '1403'=>'1403',
                                             '1405'=>'1405'),
                            'value'=> $this->items->roomid,
                            'require' => false,
                            //'multiple' => true
                        ],
        
                        'food' => [
                            'type' => 'radio',
                            'label' => 'ต้องการให้มีการจัดอาหารหรืออาหารว่างหรือไม่<br>(ติดต่อ คุณ พัฒนี โทร. 083-6246951)',
                            'options' => array('มี' => 'ต้องการ',
                                               'ไม่มี' => 'ไม่ต้องการ'),
                            'value' => $this->items->food,
                        ],
                        '.', 
                        'peoples' =>[
                            'label' => 'จำนวนคน',
                            'type'  => 'text',
                            'class' => 'number',
                            'value' => $this->items->peoples,
        
                        ],
                        'description' => [
                            'label' => 'รายละเอียดอื่นๆ',
                            'type'  => 'textarea',
                            'value' => $this->items->descript,
                            'rows' => '2',
                        ],
                        'save' => [
                            'type' => 'button',
                            'value' => '<i class="icon -material">done</i><span>บันทึก</span>',
                        ]
                    ],
                ]),//end form
            ]//end children column
            
        ]),//end column

    ],
    ]),
    ]);
    }

    // function save() {
    //     // ตรวจสอบสิทธิ์
    //     // ตรวจสอบความสมบูรณ์ของ field
    //     $error = false;
    //     // if (!post('resvName')) $error = 'ไม่มีชื่อผู้จอง';

    //     if (!$error) {
    //         // Save
    //         mydb::query(
    //             'INSERT INTO %calendar_room%
    //             (`org_name`, `phone`, `uid`, `created`)
    //             VALUES
    //             (:org_name, :phone, :uid, :created)
    //             ',
    //             [
    //                 ':org_name' => post('resvName'),
    //                 ':phone' => post('phone'),
    //                 ':uid' => i()->uid,
    //                 'created' => date('U'),
    //             ]
    //         );
    //         // debugMsg(mydb()->_query);
    //     }
    //     // debugMsg(post(),'post()');
    // }
}
?>