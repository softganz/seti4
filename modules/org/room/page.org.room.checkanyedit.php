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
import('package:org/room/models/model.room.php');
$debug = true;

class OrgRoomCheckanyedit extends Page {
    var $bookingId;
    var $contact_num;
    var $approve;
    var $item;
    function __construct($bookingId = NULL,$contact_num = NULL,$approve = NULL) {
        $this->bookingId = $bookingId;
        $this->contact_num = $contact_num;
        $this->approve = $approve;
        $this->item = RoomModel::selectEditRoom($this->bookingId);
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
                    new Column([
                        'style' => 'justify-content: center;align-items: center;display: flex;
                        flex-direction: column;',
                        'children' => [
                            //$this->roomId.' '.$this->contact_num.' '.$this->approve,'<br>',
                            '<span>กรอกเลขหมายติดต่อที่ใช้ในการจอง</span>',
                            new Row([
                                'style' => 'justify-content: center;align-items: center;',
                                'children' => [
                                    '<input type="text" id="checkanyedit" style="margin-right:8px;">',
                                    '<a href="#" class="btn" onclick="check()" >ตรวจสอบ</a>',
                                    '<a class="sg-action" style="display:none;" id="anyedit" href="#" data-rel="box" data-width="80%" data-title="แก้ไขการจอง" data-height="80%">ตรวจสอบ</a>',

                                ]
                            ])
                            ,'<span style="color:red;display:none;" id="anyedit-warn">หมายเลขการติดต่อไม่ถูกต้อง</span>'
                            
                        ]
                    ]),
                    '<script>
                            function check()
                            {
                                $.get("/org/room/booking/api/'.$this->bookingId.'/checkAnyEdit/"+$("#checkanyedit").val(), 
                                function (data, textStatus, jqXHR) {  
                                    if(data == 1)
                                    {
                                       $("#anyedit").attr("href","{url:org/room/anyedit/'.$this->bookingId.'/"+$("#checkanyedit").val()+"}");
                                       $("#anyedit").trigger("click");
                                    }
                                    else { $("#anyedit-warn").show(); }
                                });


                            }
                    </script>'

                    //'text','text'
                ],
            ]),
            ]);
    }
}