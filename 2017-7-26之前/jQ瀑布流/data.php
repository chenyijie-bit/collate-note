<?php

// 实际应用中 data 一般从数据库读取
$data = array();

$data[] = (object)array('url'=>'http://www.bcty365.com/', 'image'=>'images/1.jpg',  'title'=>'可爱与性感集于一身 来自韩国的荷叶边复古连衣裙');
$data[] = (object)array('url'=>'http://www.bcty365.com/', 'image'=>'images/2.jpg',  'title'=>'关爱自己body的同时，千万不要忘记一些小细节，要想让别人爱你，首先先要爱自己。@日本LC品爱 一直是为女生专用护理设计出的牌子，迦沐弹力泡沫是我之前推荐过的迦沐草本皂的升级版，美白效果更明显，泡沫更丰富，能更全面的呵护身体。当然所谓的body也有指“私处”哦。');
$data[] = (object)array('url'=>'http://www.bcty365.com/', 'image'=>'images/3.jpg',  'title'=>'只为那一抹清新的绿');
$data[] = (object)array('url'=>'http://www.bcty365.com/', 'image'=>'images/4.jpg',  'title'=>'穿好人人都变小细腿儿');
$data[] = (object)array('url'=>'http://www.bcty365.com/', 'image'=>'images/5.jpg',  'title'=>'性感蕾丝');
$data[] = (object)array('url'=>'http://www.bcty365.com/', 'image'=>'images/6.jpg',  'title'=>'贴身舒适，超级有型。');
$data[] = (object)array('url'=>'http://www.bcty365.com/', 'image'=>'images/7.jpg',  'title'=>'我和益若翼~~买过她家假睫毛的请举手');
$data[] = (object)array('url'=>'http://www.bcty365.com/', 'image'=>'images/8.jpg',  'title'=>'头发留那么长实属不易，“养”了那么长时间也有感情，但又会对杂志上的QUEEN B QUEEN S的发型馋涎欲滴，我还没有潇洒到减去一头长发，但也会尝试改变下自己，在买的2款假发，一个是发片让头发秘密增多的好武器，一个是梨花头带好立刻变身乖乖女。@花部屋旗舰店');
$data[] = (object)array('url'=>'http://www.bcty365.com/', 'image'=>'images/9.jpg',  'title'=>'大头不适合带帽子,我去ZARA/HM买帽子都要带男士的L号的算囊意思, 鞋子是@STACCATO 的 今年就收了她家2双鞋子一双是E嫂设计的靴子一双是毛毛高跟，春天快到了，我要HOT PINK!!!!');


// 随机抽取9条记录以模拟实际情况
$keys = array_rand($data, 6);

$json = array();
foreach($keys as $key)
{
	$json[] = $data[$key];
}

echo json_encode( $json );

?>