<?php
namespace app\index\controller;
use think\Db;
require_once('simple_html_dom.class.php');
class Index
{

    public function index()
    {

        //获取数据
    	$html= $this->curlFunction('https://cucc.tazzfdc.com/reisPub/pub/averageDailyStatist?statisttype=1&designUsages=1');
        //解析dom
        $htmlDOM = new \simple_html_dom();
        $nowTime = time();

        $dir = dirname(__FILE__);
        // $_SERVER['DOCUMENT_ROOT']
        file_put_contents($dir.'/html/backHtml'.date('Y-d-m',$nowTime).'.html', $html);
        $htmlDOM->load($html);
        $list =array();
        foreach($htmlDOM->find('.content tr') as $tr) {
            $listTmp=array();
            $i=1;
            foreach ($tr->find('td') as $td) {
                if ($i==1) {
                   $listTmp['quyu'] = $td->plaintext;
                }elseif ($i==2) {
                   $listTmp['xiaoshoulouhao'] = $td->plaintext;
                }elseif ($i==3) {
                   $listTmp['chengjiaotaoshu'] = $td->plaintext;
                }elseif ($i==4) {
                   $listTmp['chengjiaomianji'] = $td->plaintext;
                }elseif ($i==5) {
                   $listTmp['chengjiaojunjia'] = $td->plaintext;
                }
                $listTmp['time']=$nowTime;
                $i++;
            }
            if (!empty($listTmp)) {
                array_push($list, $listTmp);
            }
        }
        $res = Db::name("source")->insertAll($list);
        file_put_contents($dir.'/input.log', $res.date('Y-d-m',$nowTime).PHP_EOL,FILE_APPEND);
    }


    //发送请求
    private function curlFunction($url,$method="get",$parms='',$cookie=''){
    	
		// $cookie_file = dirname(__FILE__).'/cookie.txt';
    	//初始化
    	$ch = curl_init();
    	//设置变量
    	//https模式
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查  
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在 
		$head[] = 'Upgrade-Insecure-Requests: 1';

		curl_setopt($ch, CURLOPT_HTTPHEADER, $head);

		//保存上次cookie
		// if ($cookie) {
		// 	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
		// }

		//继续添加cookie
		$cookie = 'pubDistrict=370900';
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);


		//设置时间限制
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时限制，防止死循环

		//伪装游览器
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36');

    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_HEADER, 0);

    	if($method=="post"){
    		curl_setopt($ch, CURLOPT_POST, $parms);
    	}

    	//执行结果
    	$out = curl_exec($ch);
    	//释放句柄
    	curl_close($ch);

    	return $out;
    }
}
