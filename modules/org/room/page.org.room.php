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
//import('model:org.room.php'); >> org/models/model.org.room.php
class OrgRoom extends Page {
    var $roomId;
    var $month;
    var $year;
    var $tmp;
 function __construct($month = NULL, $year = NULL) {
    $this->roomId = post('roomId');
    $this->month = $month;
    if(!$year)  {$this->year = date('Y');} 
    else        {$this->year= $year;};
    if($month == NULL) {$this->month = date('m');} 
    else        {  //debugMsg($month.' : '.$year); 
                    $this->month = $month;
                    if($this->month > 12 ) {$this->year +=1; $this->month -=  12;}

                    else if($this->month <= 0 ) {
                    $this->year -= 1; $this->month = 12;}
        
                };

  }

 function build() {
    $month = date('m');
    $year = date('Y');
    date_default_timezone_set("Thailand/Bangkok");
    $order = RoomModel::getOrderNum();
    if(in_array('officer',i()->roles) || in_array('teacher',i()->roles) ||  in_array('admin',i()->roles)) 
    {   return new Scaffold([

        'body' => new Widget([
            'children' => [
                '<script>
                var approve = ["รออนุมัติ","อนุมัติ","ไม่อนุมัติ","ยกเลิก"];
                function edit_approve(resvid,approve)
                {
                    $.get( "org/room/booking/api/"+resvid+"/editApprove/"+approve, function( data ) {
                        //$( ".result" ).html( data );
                        console.log( "staus = "+data );
                      });
                }
                </script>'
                ,
                $order,
                new Row([
                    'style' => 'padding: 8px;display: flex;align-items: center;',
                    'children' =>[
                        '<a class="sg-action" href="'.url('org/room/booking').'" data-rel="box" data-width="80%" data-height="80%"><i class="icon -material">add</i>เพิ่มรายการ</a>',
                        '<a href="{url:org/room/'.($this->month - 1).'/'.$this->year.'}" class="sg-action " data-rel=".load_content"><i class="icon -back"></i></a>',
                        '<a href="#" style="cursor:default;">'.sg_date(date('d').'/'.$this->month.'/'.$this->year,'ดดด').'</a>',
                        '<a href="{url:org/room/'.($this->month + 1).'/'.$this->year.'}" class="sg-action" data-rel=".load_content"><i class="icon -forward"></i></a>',
                        '<a href="{url:org/room/1/'.($this->year - 1).'}"  class="sg-action" data-rel=".load_content" style="margin-left:8px;"><i class="icon -back"></i></a>',
                        '<a href="#" style="cursor:default;">'.sg_date(date('d').'/'.$this->month.'/'.$this->year,'ปปปป').'</a>',
                        '<a href="{url:org/room/1/'.($this->year + 1).'}"  class="sg-action" data-rel=".load_content"><i class="icon -forward"></i></a>',
                    ],

                ]),

                '<span style="color:red;">* ใช้เบอร์โทรศัพท์ในการแก้ไข</span>',
                new Table([
                    'thead' => [
                                'ลำดับ',
                                'ผู้ขอใช้',
                                'หัวข้อการใช้ห้อง',
                                'ช่วงวันเวลา',
                                'สถานะ',
                                'การชำระ',
                                'แก้ไข'
                            ],
                    'children' => (function(){
                        $rows = []; $i = 0;
                        foreach (RoomModel::selectResvRoom($this->month,$this->year) as $item){
 
                            $rows[] = [
                                $item->order_num,

                                $item->resv_by.'<br>จองเมื่อ : '.sg_date($item->created,'ว ดด ปปปป h:i:s').'<br>'.$item->phone,
                                $item->title.'<br>'.$item->roomid.'<br>'.$item->org_name
                                ,
                                sg_date($item->checkin,'ว ดดด ปปปป').'<br>'.$item->from_time.
                                '<hr>'.
                                sg_date($item->checkout,'ว ดดด ปปปป').'<br>'.$item->to_time
                                ,
                                '<input type="radio" id="approve_'.$item->resvid.'_'.$i.'" name="approve_'.$item->resvid.'" value="อนุมัติ" onchange="edit_approve('.$item->resvid.',approve[1])" '.checkApprove($item->approve,'อนุมัติ').'>
                                 <label for="approve_'.$item->resvid.'_'.$i.'">อนุมัติ</label><br>
                                 <input type="radio" id="napprove_'.$item->resvid.'_'.$i.'" name="approve_'.$item->resvid.'" value="ไม่อนุมัติ" onchange="edit_approve('.$item->resvid.',approve[2])" '.checkApprove($item->approve,'ไม่อนุมัติ').'>
                                 <label for="napprove_'.$item->resvid.'_'.$i.'">ไม่อนุมัติ</label><br> 
                                 <input type="radio" id="napprove_'.$item->resvid.'_'.$i.'" name="approve_'.$item->resvid.'" value="ยกเลิก" onchange="edit_approve('.$item->resvid.',approve[3])" '.checkApprove($item->approve,'ยกเลิก').'>
                                 <label for="napprove_'.$item->resvid.'_'.$i.'">ยกเลิก</label><br> 
                                ',
                                '<span style="width:53px;display:inline-block;">ชำระเมื่อ</span>: '.sg_date($item->paid_date,'ว ดด ปป').'<br>
                                <span style="width:53px;display:inline-block;">ลงวันที่</span>: '.sg_date($item->paid_date_record,'ว ดด ปป').'<br>
                                <span style="width:53px;display:inline-block;">ใบเสร็จ</span>: '.$item->paid_method,
                                '<a class="sg-action" href="'.url('org/room/adminedit/'.$item->resvid).'" data-title="แก้ไขการจอง" data-rel="box" data-width="80%" data-height="80%"><i class="icon -material">edit</i></a>',

                            ]; 
                            $i++;
                        }

                        return $rows;
                    })(),
                ]),

 


            ],
        ]),
        ]);// end admin Scaffold
    }
    else
    {
        return new Scaffold([
        'appBar' => new AppBar([
            'title' => 'Room Resv',
            'style' => 'padding:8px; display:flex;align-items: center;',
            'navigator' => new Row([
                'style' => "padding:8px; display:flex;align-items: center;",
                'children' => [
                    '<a class="sg-action" href="'.url('org/room/booking').'" data-rel="box" data-width="80%" data-height="80%" data-title="เพิ่มห้องประชุม"><i class="icon -material">add</i>เพิ่มรายการ</a>',
                    '<a href="{url:org/room/'.($this->month - 1).'/'.$this->year.'}"><i class="icon -back"></i></a>',
                    '<a href="#" style="cursor:default;">'.sg_date(date('d').'/'.$this->month.'/'.$this->year,'ดดด').'</a>',
                    '<a href="{url:org/room/'.($this->month + 1).'/'.$this->year.'}"><i class="icon -forward"></i></a>',
                    
                    '<a href="{url:org/room/'.($this->month).'/'.($this->year - 1).'}" style="margin-left:8px;"><i class="icon -back"></i></a>',
                    '<a href="#" style="cursor:default;">'.sg_date(date('d').'/'.$this->month.'/'.$this->year,'ปปปป').'</a>',
                    '<a href="{url:org/room/'.($this->month).'/'.($this->year + 1).'}"><i class="icon -forward"></i></a>',
                ]
            ]),
        ]),
        'body' => new Widget([
            'children' => [
                new Row([
                    'style' => 'padding: 8px;display: flex;align-items: center;',
                    'children' =>[
                        '<a class="sg-action" href="'.url('org/room/booking').'" data-rel="box" data-width="80%" data-height="80%"><i class="icon -material">add</i>เพิ่มรายการ</a>',
                        '<a href="{url:org/room/'.($this->month - 1).'/'.$this->year.'}" class="sg-action " data-rel=".load_content"><i class="icon -back"></i></a>',
                        '<a href="#" style="cursor:default;">'.sg_date(date('d').'/'.$this->month.'/'.$this->year,'ดดด').'</a>',
                        '<a href="{url:org/room/'.($this->month + 1).'/'.$this->year.'}" class="sg-action" data-rel=".load_content"><i class="icon -forward"></i></a>',
                        '<a href="{url:org/room/'.($this->month).'/'.($this->year - 1).'}" style="margin-left:8px;"><i class="icon -back"></i></a>',
                        '<a href="#" style="cursor:default;">'.sg_date(date('d').'/'.$this->month.'/'.$this->year,'ปปปป').'</a>',
                        '<a href="{url:org/room/'.($this->month).'/'.($this->year + 1).'}"><i class="icon -forward"></i></a>',
                    ],

                ]),

                '<span style="color:red;">* ใช้เบอร์โทรศัพท์ในการแก้ไข</span>',
                new Table([
                    'thead' => [
                                'ผู้ขอใช้',
                                'หัวข้อการใช้ห้อง',
                                'ห้อง','หน่วยงาน',
                                'ตั้งแต่วันที่',
                                'ถึงวันที่',
                                'สถานะ',
                                'แก้ไข'
                            ],
                    'children' => (function(){
                        $rows = [];
                        //$dbs = mydb::select('SELECT * FROM %calendar_room% where YEAR(`checkin`) = '.$this->year.' AND MONTH(`checkin`) = '.$this->month);
                        //foreach ($dbs->items as $item) {
                        foreach (RoomModel::selectResvRoom($this->month,$this->year) as $item){
                            $rows[] = [
                                $item->resv_by.'<br>จองเมื่อ : '.sg_date($item->created,'ว ดด ปปปป').'<br>'.$item->phone,
                                $item->title,
                                $item->roomid,
                                $item->org_name,
                                sg_date($item->checkin,'ว ดดด ปปปป').'<br>'.$item->from_time,
                                sg_date($item->checkout,'ว ดดด ปปปป').'<br>'.$item->to_time,
                                $item->approve,
                                '<a class="sg-action" href="'.url('org/room/checkanyedit/'.$item->resvid.'/'.$item->phone.'/'.$item->approve).'" data-title="แก้ไขการจอง" data-rel="box" data-width="80%" data-height="80%"><i class="icon -material">edit</i></a>',

                            ];
                        }
                        //Class::method()->attribute
                        //Class::method() >> return count,items
                        
                        // foreach (RoomModel::items()->items as $item) {
                        //     $rows[] = [
                        //        sg_date($item->created,'ว ดด ปปปป'),
                        //         '',
                        //         $item->org_name,
                        //     ];
                        // }
                        return $rows;
                    })(),
                ]),
                // new Table([
                //     'thead' => ['วันที่','ห้อง','หน่วยงาน'],
                //     'children' => [
                //         ['26 ก.ย. 64','1401','สนส.'],
                //         ['26 ก.ย. 64','1405','สนส.'],
                //         ['28 ก.ย. 64','1401','สนส.'],
                //     ],
                // ]),
            ],
        ]),
        ]);// end anonymous Scaffold
    }//else if else
  
 }
 function check_any_edit()
 {
    return new Scaffold([
        'appBar' => new AppBar([
         'title' => 'Room Resv',
         'body' => new Widget([
            'children' => [
                '<input type="text" placeholder="please insert contact number">'
            ]
            ])
        ])
    ]);

 }//end check any edit
}
function checkApprove($text,$case)
{
    if($text == $case)
    { return 'checked'; }
    else return '';
}
?>