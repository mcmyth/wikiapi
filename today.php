<?php
require_once 'zh.php';
$tool=new ZhConvert();
/*
 //允许跨域
header("Access-Control-Allow-Origin:*");
ini_set('date.timezone','Asia/Shanghai');
$month=date( 'm',time() );
$day=date( 'd',time() );
//当前年月日
$today = date('Y年m月d日');


$htmlFile = file_get_contents("https://zh.wikipedia.org/w/api.php?action=parse&page=2%E6%9C%884%E6%97%A5&format=json");
$htmlFile  = json_decode($htmlFile,true)["parse"]["text"]["*"];
$dom = new DOMDocument;
$dom->loadXML($htmlFile);
$item = $dom->getElementsByTagName('h2');
echo $htmlFile;
//var_dump($item[1]->nextSibling); ;


 *
 */

/*
//允许跨域
header("Access-Control-Allow-Origin:*");
ini_set('date.timezone','Asia/Shanghai');
$month=date( 'm',time() );
$day=date( 'd',time() );
//当前年月日
$today = date('Y年m月d日');


$htmlFile = file_get_contents("https://zh.wikipedia.org/w/api.php?action=parse&page=2%E6%9C%884%E6%97%A5&prop=wikitext&utf8&format=json");

$dom = new DOMDocument;
@$dom->loadXML($htmlFile);
$item = $dom->getElementsByTagName('h2');

//echo $htmlFile;

echo(json_decode($htmlFile,true)["parse"]["wikitext"]["*"]);

*/



header("Access-Control-Allow-Origin:*");
ini_set('date.timezone','Asia/Shanghai');

$month = isset($_GET["month"]) ? $_GET["month"] : date("n");
$day = isset($_GET['day']) ? $_GET['day'] : date( 'j',time() );

//当前年月日
$today = date('Y年m月d日');
$date = urlencode($month . "月".$day."日");


$url ="https://zh.wikipedia.org/w/api.php?action=parse&page=$date&format=json";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//curl_setopt($ch, CURLOPT_PROXY, 'socks5://127.0.0.1:1088');
//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$curl_scraped_page = curl_exec($ch);
curl_close($ch);

$htmlFile = $curl_scraped_page;

//echo $htmlFile;
//echo "https://zh.wikipedia.org/w/api.php?action=parse&page=$date&format=json";
$htmlFile  = json_decode($htmlFile,true)["parse"]["text"]["*"];
$text = $tool->zh(strip_tags($htmlFile),"zh-hans","zh-hant");


$dom = new DOMDocument;
$dom->loadXML($htmlFile);
$item = $dom->getElementsByTagName('ul');
$content = $tool->zh(strip_tags($item[0]->ownerDocument->saveXML( $item[0] )),"zh-hans","zh-hant");
$dir = preg_replace('/[\r\n]+/', "\n", trim($content));
$dirArray  =  explode("\n", $dir);
$dirArray2 = [];
$i =0;
foreach ($dirArray as $value){
    if(!strpos($value,"世纪") > 0){
        $dirArray2[$i] = substr($value,strripos($value," ")+1);
        $i++;
    }


}
//var_dump($dirArray2);
$text  = str_replace($content,"",$text);
$json=[];

for($i=0;$i <= count($dirArray2) - 2;$i++)
{

    $json [$dirArray2[$i]]  = preg_replace("/&#?[a-z0-9]{2,8};/i","",substr(substr(str_replace("[编辑]" ,"",getSubstr($text, $dirArray2[$i]."[编辑]",$dirArray2[$i+1]."[编辑]")),1),0, -1));
}


function getSubstr($str, $leftStr, $rightStr)
{
$left = strpos($str, $leftStr);
//echo '左边:'.$left;
$right = strpos($str, $rightStr,$left);
//echo '<br>右边:'.$right;
if($left < 0 or $right < $left) return '';
return substr($str, $left + strlen($leftStr), $right-$left-strlen($leftStr));
}
if(@$json["大事记"]!=""){
   $json["大事记"] =  preg_replace("/[0-9]+世纪\n/","",$json["大事记"]);
}


echo json_encode($json);