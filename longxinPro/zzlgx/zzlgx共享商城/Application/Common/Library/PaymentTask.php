<?php

/**
 * 每天晚上23:55分，任务计划扫描未结算订单给予分配共享币
 * 
 * @param  [type]  $url       [description]
 * @param  string  $post_data [description]
 * @param  integer $timeout   [description]
 * @return [type]             [description]
 */

function curl_post($url, $post_data = '', $timeout = 5){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, 1);

    if ($post_data != '') {

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    curl_setopt($ch, CURLOPT_HEADER, false);

    $file_contents = curl_exec($ch);

    curl_close($ch);

    return $file_contents;
}

echo curl_post("http://gx.zzlhi.com/Admin/Allow/payment_task");