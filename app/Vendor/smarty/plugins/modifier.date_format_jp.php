<?php
//Smartyで曜日を日本語で表示する修飾子（プラグイン）
function smarty_modifier_date_format_jp($value,$format){
    $weekDayList = array("日","月","火","水","木","金","土");
    echo $weekDay = $weekDayList[date("w",$value)];

    //パターン，置換後文字列，置換対象
    $format = preg_replace("/([^%])(([%]{2})*)%a/","$1$2{$weekDay}",$format);
    $format = preg_replace("/([^%])(([%]{2})*)%A/","$1$2{$weekDay}曜日",$format);

    return strftime($format,$value);
}
