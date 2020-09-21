<?php
set_time_limit(0);
/*
	Настройки
*/
// https://vkhost.github.io/
$access_token = "nevaliddad5f4cf6a4nevalid2d4e298c7dfba28eb057nevalid76nevalid4a11c07bf040c9db5nevalid"; // Ключ доступа

// https://vk.com/im?sel=XXXXX
$peer_id = "445340"; // Ид диалога



/*
	Код
*/
$per_req=100;
$offset=0;

$messages = [];
$profiles = [];


/*
	Парсинг
*/
do{
	$code = array(
		"vars"=>[],
		"items"=>[],
		"profiles"=>[],
		"groups"=>[]
	);

	for($i=0;$i<1;$i++){
		$params = array(
			"peer_id"=>$peer_id,
			"offset"=>$offset,
			"count"=>$per_req,
			"extended"=>1,
			"rev"=>1,
		);

		$vname = 'part' . count($code["vars"]);
		$code["vars"][]= 'var '.$vname.' = API.messages.getHistory('.json_encode($params).')';
		$code["items"][] = $vname.'.items';
		$code["profiles"][] = $vname.'.profiles';
		$code["groups"][] = $vname.'.groups';
		$offset += $per_req;
	}

	$code = implode("; ",$code["vars"]).';'.
	'return {'.
		'items:'.implode("+",$code["items"]).','.
		'profiles:['.implode(",",$code["profiles"]).'],'.
		'groups:['.implode(",",$code["groups"]).']'.
	'};';


	$get = api("execute",true,array(
		"code"=>$code,
	));
	$messages = array_merge($messages,$get["response"]["items"]);
	for($i=0;$i<10;$i++){
	
		for($x=0;$x<count($get["response"]["profiles"]);$x++){
			if($get["response"]["profiles"][$x] != ""){
				for($y=0;$y<count($get["response"]["profiles"][$x]);$y++){
					if(!$profiles[$get["response"]["profiles"][$x][$y]["id"]]){
						$profiles[$get["response"]["profiles"][$x][$y]["id"]] = $get["response"]["profiles"][$x][$y];
					}
				}
			}
		}
		for($x=0;$x<count($get["response"]["groups"]);$x++){
			if($get["response"]["groups"][$x] != ""){
				for($y=0;$y<count($get["response"]["groups"][$x]);$y++){
					if(!$profiles["-".$get["response"]["groups"][$x][$y]["id"]]){
						$profiles["-".$get["response"]["groups"][$x][$y]["id"]] = $get["response"]["groups"][$x][$y];
					}
				}
			}
		}
	}
	


}while(count($get["response"]["items"])>0);


/*
	Экспорт в html
*/
$html = "";
$html .= "<h4> Даты сообщений: с ".date("d.m.Y H:i:s",$messages["0"]["date"])." по ".date("d.m.Y H:i:s",$messages[count($messages) - 1]["date"])."</h4>";
$html .= "<h4> Всего сообщений: ".count($messages)." </h4>";
$html .= "<hr>";

for($i=0;$i<count($messages);$i++){
	$from_id = $messages[$i]["from_id"]?$messages[$i]["from_id"] : $messages[$i]["user_id"];
	$u = $profiles[$from_id] ? $profiles[$from_id] : array(
		"id"=>$from_id,
		"first_name"=>"DELETED",
		"last_name"=>"",
		"photo_100"=>"http://vk.com/images/deactivated_100.png"
	);
	$html .= '<div id="msg'.$messages[$i]["id"].'" class="msg_item">';
	$html .= '<div class="upic"><img src="'.$u["photo_100"].'" alt="[photo_100]"></div>';
	$html .= '<div class="from"> <b>'.$u["first_name"].' '.$u["last_name"].'</b> <a href="'.make_ulink($from_id).'" target="_blank">@'.$u["screen_name"].'</a> <a href="#msg'.$messages[$i]["id"].'">'.date("d.m.Y H:i:s",$messages[$i]["date"]).'</a></div>';

	print_r($messages[$i]);
	if($messages[$i]["text"] != ""){
		$html .= '<div class="msg_body">'.t2m($messages[$i]["text"]).'</div>';
	}
	if($messages[$i]["action"]){
		$html .= chatAction($messages[$i]["action"]);
	}
	if($messages[$i]["attachments"]){
		$html .= '<div class="attacments"> <b>Материалы:</b> </div>';
		for($k=0;$k<count($messages[$i]["attachments"]);$k++){
		   $html .= make_attach($messages[$i]["attachments"][$k]);
		}
	}
	if($messages[$i]["geo"]){
		$html .= make_geo($messages[$i]);
	}
	if($messages[$i]["fwd_messages"]){
		initfwd($messages[$i]["fwd_messages"]);
	}
	if($messages[$i]["reply_message"]){
		initfwd(array($messages[$i]["reply_message"]),true);
	}
	$html .= '</div>';
}
$html .= "<hr>";

$html = '<!DOCTYPE html>
         <html>
            <head>
               <meta charset="utf-8"/>
               <link rel="shortcut icon" href="http://vk.com/images/fav_chat.ico"/>
               <!--<link rel="stylesheet" type="text/css" href="http://vk.com/css/al/common.css" />-->
               <title>VK Messages</title>
               <style>
                  .emoji,.emoji_css {width: 16px;height: 16px;border: none;vertical-align: -3px;margin: 0 1px;display: inline-block}
                  .emoji_css {background: url(https://vk.com/images/im_emoji.png?9) no-repeat}
                  @media (-webkit-min-device-pixel-ratio: 2), (-o-min-device-pixel-ratio: 2/1), (min-resolution: 192dpi) {
                      .emoji_css {background-image:url(https://vk.com/images/im_emoji_2x.png?9);background-size: 16px 544px}
                  }
                  h4{font-family: inherit;font-weight: 500;line-height: 1.1;color: inherit;margin-top: 10px;margin-bottom: 10px;font-size: 18px;}
                  body{font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;font-size: 14px;line-height: 1.42857143;color: #333;background-color: #fff;margin:0;}
                  hr{height: 0;margin-top: 20px;margin-bottom: 20px;border: 0;border-top: 1px solid #eee;}
                  .messages{width:1170px;margin:0 auto;text-align:left;}
                  .msg_item {overflow:hidden}
                  .from,.msg_body,.att_head,.attacments,.attacment,.fwd{margin-left:60px;min-height: 1px;padding-right: 15px;padding-left: 15px;}
                  .msg_item{margin-top:5px;}
                  .upic{float:left}
                  .upic img{vertical-align:top;padding:5px;width: 50px;height: 50px;}
                  .round_upic .upic img{border-radius: 50%;}
                  a {color: #337ab7;text-decoration: none;}
                  a:active, a:hover {outline: 0;}
                  a:focus, a:hover {color: #23527c;text-decoration: underline;}
                  .att_head{color:#777;}
                  .att_ico{float:left;width:11px;height:11px;margin: 3px 3px 2px; background-image:url(http://vk.com/images/icons/mono_iconset.gif);}
                  .att_photo{background-position: 0 -30px;}
                  .att_audio{background-position: 0 -222px;}
                  .att_video{background-position: 0 -75px;}
                  .att_doc{background-position: 0 -280px;}
                  .att_wall,.att_fwd{background-position: 0 -194px;}
                  .att_gift{background-position: 0 -105px;}
                  .att_sticker{background-position: 0 -362px; width: 12px; height: 12px;}
                  .att_link{background-position: 0 -237px;}
                  .attb_link a span{color:#777777 !important;}
                  .att_geo{background-position: 0 -165px;}
                  .fwd{border:2px solid #C3D1E0;border-width: 0 0 0 2px;margin-left:85px;}
				  .msg_item:target{background: #E7E4D0;}
               </style>
            </head>
            <body><div class="messages round_upic">'.$html.'</div></body>
         </html>';
file_put_contents(dirname(__FILE__)."/Messages ".$peer_id.".html",$html);


function initfwd($msgfwd,$reply=false){
	global $html,$profiles;
	if(!$msgfwd) return;
	
	$html .= '<div class="att_head"> <div class="att_ico att_fwd"></div> Пересланные сообщения: </div>';
	$html .= '<div class="fwd">';
	
	
	for($i=0;$i<count($msgfwd);$i++){
		$u = $profiles[$msgfwd[$i]["from_id"]] ? $profiles[$msgfwd[$i]["from_id"]] : array(
			"id"=>$msgfwd[$i]["from_id"],
			"first_name"=>"DELETED",
			"last_name"=>"",
			"photo_100"=>"http://vk.com/images/deactivated_100.png"
		);
		$html .= '<div class="msg_item">';
		$html .= '<div class="upic"><img src="'.$u["photo_100"].'" alt="[photo_100]"></div>';
		$html .= '<div class="from"> <b>'.$u["first_name"].' '.$u["last_name"].'</b> <a href="'.make_ulink($msgfwd[$i]["from_id"]).'" target="_blank">@'.$u["screen_name"].'</a> ';
		$html .= $reply ? '<a href="#msg'.$msgfwd[$i]["id"].'">'.date("d.m.Y H:i:s",$msgfwd[$i]["date"]).'</a>':'';
		$html .= '</div>';

		$html .= '<div class="msg_body">'.t2m($msgfwd[$i]["text"]).'</div>';
		
		if($msgfwd[$i]["attachments"]){
			$html .= '<div class="attacments"> <b>Материалы:</b> </div>';
			for($k=0;$k<count($msgfwd[$i]["attachments"]);$k++){
			   $html .= make_attach($msgfwd[$i]["attachments"][$k]);
			}
		}
		if($msgfwd[$i]["geo"]){
			$html .= make_geo($msgfwd[$i]);
		}
		if($msgfwd[$i]["fwd_messages"]){
			initfwd($msgfwd[$i]["fwd_messages"]);
		}
		$html .= '</div>';
	}
	$html .= '</div>';
}

function make_geo($map){
	$html = '';
	$html .= '<div class="attacment"> <div class="att_ico att_geo"></div> <a href="https://www.google.ru/maps/@'.$map["geo"]["coordinates"]["latitude"].','.$map["geo"]["coordinates"]["longitude"].',17z" target="_blank">Место: '.($map["geo"]["place"] ? $map["geo"]["place"]["title"] : '---').'</a></div>';
	return $html;
}
function make_attachments($attachments){
	$html = '';
	if($attachments){
		$html .= '<div class="attacments"> <b>Материалы:</b> </div>';
		for($i=0;$i<count($attachments);$i++){
			$html .= make_attach($attachments[$i]);
		}
	}
	return $html;
}
function make_attach($attach){
	$html='';
	switch ($attach["type"]){
		case 'photo':
			$photolink = photosGet($attach["photo"]["sizes"]);
			print_r($photolink);
			$photo_size = $attach["photo"]["width"] ? ' ('.$attach["photo"]["width"].'x'.$attach["photo"]["height"].')' : '';
			
			$html .= '<div class="attacment"> <div class="att_ico att_photo"></div> <a target="_blank" href="'.$photolink.'">[photo'.$attach["photo"]["owner_id"].'_'.$attach["photo"]["id"].']'.$photo_size.'</a> </div>';
		break;
		case 'audio':
			$url = $attach["audio"]["url"];
			if(!$url){
				$url = 'http://vk.com/audio?q='.urlencode($attach["audio"]["artist"].' - '.$attach["audio"]["title"]);
			}
			$html .= '<div class="attacment"> <div class="att_ico att_audio"></div> <a target="_blank" href="'.$url.'">[audio'.$attach["audio"]["owner_id"].'_'.$attach["audio"]["id"].'] '.doc2text($attach["audio"]["artist"]).' - '.doc2text($attach["audio"]["title"]).' ('.a2t($attach["audio"]["duration"]).')</a></div>';
		break;
		case 'video':
			$html .= '<div class="attacment"> <div class="att_ico att_video"></div> <a href="http://vk.com/video'.$attach["video"]["owner_id"].'_'.$attach["video"]["id"].'" target="_blank">[video'.$attach["video"]["owner_id"].'_'.$attach["video"]["id"].'] '.doc2text($attach["video"]["title"]).' ('.a2t($attach["video"]["duration"]).')</a></div>';
		break;
		case 'audio_message':
			$html .= '<div class="attacment"> <div class="att_ico att_doc"></div> <a target="_blank" href="'.doc2text($attach["audio_message"]["link_ogg"]?$attach["audio_message"]["link_ogg"]:$attach["audio_message"]["link_mp3"]).'">audio_message</a></div>';
		break;
		case 'doc':
			$html .= '<div class="attacment"> <div class="att_ico att_doc"></div> <a target="_blank" href="'.doc2text($attach["doc"]["url"]).'">'.doc2text($attach["doc"]["title"]).'</a></div>';
		break;
		case 'wall':
			$html .= '<div class="attacment"> <div class="att_ico att_wall"></div> <a target="_blank" href="http://vk.com/wall'.$attach["wall"]["to_id"].'_'.$attach["wall"]["id"].'">[wall'.$attach["wall"]["to_id"].'_'.$attach["wall"]["id"].']</a><div class="att_wall_text">'.$attach["wall"]["text"].'</div>'.make_attachments($attach["wall"]["attachments"]).'</div>';
			break;
		case 'wall_reply':
			$html .= '<div class="attacment"> <div class="att_ico att_wall"></div> <a target="_blank" href="http://vk.com/wall'.$attach["wall_reply"]["owner_id"].'_'.$attach["wall_reply"]["post_id"].'">[wall'.$attach["wall_reply"]["owner_id"].'_'.$attach["wall_reply"]["post_id"].']</a> <div class="att_wall_text">'.$attach["wall_reply"]["text"].'</div>'.make_attachments($attach["wall_reply"]["attachments"]).'</div>';
			break;
		case 'link':
			$html .= '<div class="attacment attb_link"> <div class="att_ico att_link"></div> <a href="'.$attach["link"]["url"].'" target="_blank"><span>Ссылка</span> '.doc2text($attach["link"]["title"]).'</a></div>';
			break;
		case 'gift':
			$html .= '<div class="attacment"> <div class="att_ico att_gift"></div> <a target="_blank" href="'.$attach["gift"]["thumb_256"].'">Подарок #'.$attach["gift"]["id"].'</a></div>';
			break;
		case 'sticker':
			$html .= '<div class="attacment"> <div class="att_ico att_sticker"></div> <a target="_blank" href="'.$attach["sticker"]["images"][count($attach["sticker"]["images"])-1]["url"].'">Стикер #'.$attach["sticker"]["sticker_id"].'</a></div>';
			break;
		default:
			$html .= '<div class="attacment"><pre>'.json_encode($attach).'</pre></div>';
		}
	return $html;
}
function photosGet($res){
	$return = array("w","z","y","x","m","o");

	for($z=0;$z<count($return);$z++){
		for($i=0;$i<count($res);$i++){
			if($res[$i]["type"] == $return[$z]){
				$photo = $res[$i]["url"];
				break 2;
			}
		}
	}
	return $photo;
}
function chatAction($action_name){
	switch($action_name){
		case 'chat_photo_update':
			$html = '<div style="color:#888888;">обновил(а) фотографию беседы:</div>';
		break;
		case 'chat_photo_remove':
			$html = '<div style="color:#888888;">удалил(а) фотографию беседы</div>';
		break;
		default:
			$html = '<div>action "<b>'.$action_name.'</b>" is unknown</div>';
	}
	return $html;
}
function a2t($sec){
	return floor($sec/60).':'.substr('0'.($sec%60),-2);
}
function doc2text($text){
	$text = str_replace("&",'&amp;',$text);
	$text = str_replace("<",'&lt;',$text);
	$text = str_replace(">",'&gt;',$text);
	$text = str_replace("\"",'&quot;',$text);
	return $text;
}
function t2m($text){
	$text = doc2text($text);
	
	//URLs starting with http://, https://, or ftp://
	$text = preg_replace("/(\b(https?|ftp):\/\/[-A-Z0-9+&@#\\/%?=~_|!:,.;\u0410-\u042f\u0430-\u044f\u0401\u0451]*[-A-Z0-9+&@#\/%=~_|\u0410-\u042f\u0430-\u044f\u0401\u0451])/im",'<a href="$1" target="_blank">$1</a>',$text);
	
	//URLs starting with "www." (without // before it, or it'd re-link the ones done above).
	$text = preg_replace("/(^|[^\/])(www\.[\S]+(\b|$))/im",'$1<a href="http://$2" target="_blank">$2</a>',$text);

	return $text;
}
function make_ulink($id){
	return 'http://vk.com/'.($id > 0 ? 'id' : 'club').abs($id);
}
function api($method, $token, $post = null) {
	global $access_token;
	if(!$post["v"]) $post["v"] = "5.111";
	if($token) $method .= "?access_token=".$access_token;
	$url = "https://api.vk.com/method/".$method;
	do{
		$while = false;
		$return = json_decode(curl($url, $post), true);
		if($return["error"]["error_code"] == 6){
			sleep(1);
			$while = true;
		}
		elseif($return["error"]["error_code"] == 5){
			exit("Проверь токен | Check access_token");
		}
	}while($while);
	return $return;
}
function curl($url, $post = null){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if($post){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}
// Ня ^_^
?>