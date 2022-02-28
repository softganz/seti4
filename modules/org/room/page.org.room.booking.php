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

class OrgRoomBooking extends Page {
    var $roomId;
    function __construct() {
        $this->roomId = post('roomId');
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
            new Form([
                //'action' => url('org/room/booking/create'),
                'action' => url('org/room/booking/create'),
                'class' => 'sg-form',
                'rel' => 'notify',
                'done' => 'close | load:#main',
                'checkValid' => true,
                'children' => [
                    'title' => [
                        'type' => 'text',
                        'label' => 'ชื่อเรื่องการใช้ห้องประชุม',
                        'require' => true,
                    ],
                    'resvName' => [
                        'type' => 'text',
                        'label' => 'ชื่อ-นามสกุล ผู้ขอ',
                        'require' => true,
                    ],
                    'phone' => [
                        'type' => 'text',
                        'label' => 'โทรศัพท์',
                        'require' => true,
                    ],
                    'org_name' => [
                        'type' => 'text',
                        'label' => 'ชื่อหน่วยงาน',
                        'require' => true,
                    ],

                    'org_type' => [
                        'type' => 'radio',
                        'label' => 'ประเภทหน่วยงาน',
                        'options'=>array('ppi'=>'โครงการบริการวิชาการสถาบันนโยบายสาธารณะ',
                                        'psu'=>'หน่วยงานภายใน',
                                        'out'=>'หน่วยงานภายนอก'),
                        'value'=>'psu',
                        'require' => false,
                    ],
                    'checkin' => [
                        'type' => 'text',
                        'class' => 'sg-datepicker',
                        'label' => 'วันที่เริ่มใช้ห้อง',
                        'value' => date('d/m/Y'),
                        'require' => false,
                    ],
                    'from_time' => [
                        'type' => 'time',
                        'label' => 'เวลาที่เริ่มใช้ห้อง',
                        'require' => false,
                        'value' => '09:00',
                    ],
                    'checkout' => [
                        'type' => 'text',
                        'class' => 'sg-datepicker',
                        'label' => 'วันที่สิ้นสุดการใช้ห้อง',
                        'value' => date('d/m/Y'),
                        'require' => false,
                    ],
                    'to_time' => [
                        'type' => 'time',
                        'label' => 'เวลาที่สิ้นสุดการใช้ห้อง',
                        'require' => false,
                        'value' => '09:00',
                    ],

                    'roomid' => [
                        //'type' => 'checkbox',
                        'type'  => 'radio',
                        'label' => 'ห้องที่จอง',
                        'options'=>array('1401'=>'1401',
                                        '1402'=>'1402',
                                        '1403'=>'1403',
                                        '1405'=>'1405'),
                        'value'=>'1401',
                        'require' => false,
                        //'multiple' => true
                    ],

                    'food' => [
                        'type' => 'radio',
                        'label' => 'ต้องการให้มีการจัดอาหารหรืออาหารว่างหรือไม่<br>(ติดต่อ คุณ พัฒนี โทร. 083-6246951)',
                        'options' => array('มี' => 'ต้องการ',
                                        'ไม่มี' => 'ไม่ต้องการ'),

                    ],
                    '.', 
                    'peoples' =>[
                        'label' => 'จำนวนคน',
                        'type'  => 'text',
                        'class' => 'number',

                    ],
                    'description' => [
                        'label' => 'รายละเอียดอื่นๆ',
                        'type'  => 'textarea'
                    ],
                    'save' => [
                        'type' => 'button',
                        'value' => '<i class="icon -material">done</i><span>บันทึก</span>',
                    ]
                ],//end form children
            ]),
            ]//end column children
        ])//end column
    ],//end Widget children
    ]),//end Widget
    ]);//end scaffold
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