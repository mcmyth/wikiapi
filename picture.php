<?php
header('Access-Control-Allow-Origin: *');
require_once 'zh.php';
$zh=new ZhConvert();
date_default_timezone_set('Asia/Shanghai');
$year = isset($_GET["year"]) ? $_GET["year"] : date("Y");
$month = isset($_GET["month"]) ? $_GET["month"] : date("n");
$day = isset($_GET['day']) ? $_GET['day'] : null;
$date = urlencode( $year . "年".$month."月");
$url = 'https://zh.wikipedia.org/wiki/Wikipedia:%E6%AF%8F%E6%97%A5%E5%9B%BE%E7%89%87/'.$date;
//if($year."-".$month > date("Y-n")) die();


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
//curl_setopt($ch, CURLOPT_PROXY, 'socks5://127.0.0.1:1088');
//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
$curl_scraped_page = curl_exec($ch);
curl_close($ch);

$html = $curl_scraped_page;

$json=null;


$source = getDivByClassName($html,"thumb",false,true);
$i=0;
foreach($source as $src){
$dom = new DomDocument();
@$dom->loadHtml($html);
	preg_match_all("/\<img.*?src\=\"(.*?)\"[^>]*>/i", $src, $match);
	$link = "https:".$match[1][0];
	
	$json[$i]["small"] =  $link;
	$json[$i]["large"] = str_replace("/thumb", "",dirname($link)) ;
	$filename = basename($link);
	$json[$i]["filename"] =substr($filename,strripos($filename,"-")+1); ;
	//$json[$i]["parent"] =  var_dump($dom);
	
	
	$i++;
}

$source = getDivByClassName($html,"gallerytext");
$i=0;
$j=0;
$desc=null;
foreach($source as $src){

	if($i % 2 == 0 ){
		$str = chop( str_replace(array("\r\n", "\r", "\n"), "",  strip_tags($src)));
		if($str != null)
		{
	
		$desc[$i]=$str;
		$json[$j]["desc"] =$zh->zh( $str,"zh-hans","zh-hant");
		
		
		
		$j++;
		}
	}
	 $i++;
}

$source = getDivByClassName($html,"mw-headline");
$i=0;
foreach($source as $src){

	if($i-1 != -1){

        $str = chop( str_replace(array("\r\n", "\r", "\n"), "",  strip_tags($src)));
	$json[$i-1]["date"] =str_replace("[编辑]", "",$str) ;
	}
	$i++;
}


if($day != null && $json !=null) {
    $json2 = [];
    foreach ($json as $value){
        if($value["date"] == $year . "年" . $month . "月" . $day . "日"){

            if(@$value["small"]!=null){
            $json2["small"] =  $value["small"];
            $json2["large"] = $value["large"];
            $json2["desc"] = $value ["desc"];
            $json2["date"] = $value["date"];
                $json2["status"]="ok";
            }else
            {
                $json2["status"]="null";
            }

            if($json2 !==null)
            echo json_encode($json2);
            die();
        }
    }

    echo $year . "年" . $month . "月" . $day . "日";
    echo str_replace("[编辑]", "", $str);
    return;
}


if($json ==null){
    $json["status"]="null";
}else{
    $json["status"]="ok";
}
echo json_encode($json);

function getDivByClassName($html,$classname,$includeParent=false,$test=false){
$array = array();
$dom = new DomDocument();
@$dom->loadHtml($html);
$finder = new DomXPath($dom);
$nodes = $finder->query("//*[contains(@class, '$classname')]");
$i=0;
foreach ($nodes as $rowNode) {
	if($includeParent){
			$array[] = html_entity_decode($rowNode->ownerDocument->saveXML($rowNode));
			
	}else{
		$array[] = html_entity_decode(get_inner_html($rowNode)) ;
	}

	$i++;
}
return $array;
}
function get_inner_html($node) { 
    $innerHTML= ''; 
    $children = $node->childNodes; 
    foreach ($children as $child) { 
        $innerHTML .= $child->ownerDocument->saveXML( $child ); 
    } 
    return $innerHTML;  
} 


