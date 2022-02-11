SELECT
p.`name`, p.`lname`, p.`birth`, p.`cid`
, p.`house`, p.`village`, p.`tambon`, p.`ampur`,p.`changwat`
, p.`zip`,p.`phone`,p.`email`
, (SELECT GROUP_CONCAT(`detail1`) FROM `sgz_person_tr` `sk` WHERE sk.`uid`=g.`uid` AND sk.`tagname`="skill") `skills`
, (SELECT CONCAT('[',GROUP_CONCAT(CONCAT('{"ปี"="',`date1`,'"',',"โครงการ"="',`detail1`,'"','"แหล่งทุน"="',`detail2`,'"','}')),']')
                 FROM `sgz_person_tr` trp WHERE trp.`uid`=g.`uid` AND trp.`tagname` = "project") `projects`
FROM `sgz_person_group` g
LEFT JOIN `sgz_db_person` p USING(`psnid`)
WHERE g.`status`>0



SET @@group_concat_max_len = 4096;
SELECT
p.`name` `ชื่อ`, p.`lname` `นามสกุล`
, p.`house` `บ้านเลขที่`
, p.`village` `หมู่`
, cos.`subdistname` `ตำบล`
, cod.`distname` `อำเภอ`
, cop.`provname` `จังหวัด`
, p.`zip` `รหัสไปรษณีย์`,p.`phone` `เบอร์โทร`,p.`email` `อีเมล์`
, (SELECT GROUP_CONCAT(`detail1`) FROM `sgz_person_tr` `sk` WHERE sk.`uid`=g.`uid` AND sk.`tagname`="skill") `ความเชี่ยวชาญ`
, (SELECT CONCAT(`detail1`,' ',`detail2`) FROM `sgz_person_tr` WHERE `uid` = g.`uid` AND `tagname` = "job" ORDER BY `date1` DESC LIMIT 1) `อาชีพ`
, (SELECT CONCAT(`detail1`,' ',`detail2`) FROM `sgz_person_tr` WHERE `uid` = g.`uid` AND `tagname` = "education" ORDER BY `date1` DESC LIMIT 1) `การศึกษา`
, YEAR(pj.`date1`) `ประสบการณ์ ปี`
, pj.`detail1` `ชื่อโครงการ`
, pj.`detail2` `แหล่งทุน`
FROM `sgz_person_group` g
LEFT JOIN `sgz_db_person` p USING(`psnid`)
LEFT JOIN `sgz_co_province` cop ON cop.`provid`=p.`changwat`
LEFT JOIN `sgz_co_district` cod ON cod.`distid`=CONCAT(p.`changwat`,p.`ampur`)
LEFT JOIN `sgz_co_subdistrict` cos ON cos.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
LEFT JOIN `sgz_person_tr` pj ON pj.`psnid`=g.`psnid` AND pj.`tagname`="project"
WHERE g.`status`>0
ORDER BY CONVERT(p.`name` USING tis620) ASC, CONVERT(p.`lname` USING tis620) ASC