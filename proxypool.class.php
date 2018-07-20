<?php   
$curl_timeout=10;
$fn=basename($_SERVER["SCRIPT_NAME"]);
if($fn<>"proxypool.class.php"){
	/*
	require_once("./lib/func.general.php");
	require_once("./lib/Snoopy.class.php");
	require_once("./lib/sql.lib.php");
	require_once("./lib/sqlite.lib.php");
	$dbdir="../sqlite/sqlite/";
	*/
	require_once("./lib/uatool.class.php");
	require_once("./lib/process.class.php");
	//echo "pppppppppppppppppppppppppp";exit;
	$dbname_proxypool=$dbdir."sys_proxypool.db3";
	$DB_proxypool=new SQLite($dbname_proxypool); 
	tbl_create_proxypool();
}else{
	date_default_timezone_set("Asia/Shanghai");
	if(!(PHP_OS=="Linux" &&  isset($_SERVER["ANDROID_ROOT"]))){
		ignore_user_abort(true);
		set_time_limit(0);
	}
	define("_DEFAULT_SRC_DATE_",date('Y-m-d H:i:s'));
	ini_set("max_execution_time",60*60*60); 
	ini_set("extension","php_pdo.dll"); 
	//ini_set("error_reporting","E_ALL ~E_NOTICE"); 
	ini_set("extension","php_pdo_sqlite.dll"); 
	ini_set("extension","php_sqlite.dll"); 
	require_once("../lib/func.general.php");
	require_once("../lib/Snoopy.class.php");
	require_once("../lib/sql.lib.php");
	require_once("../lib/sqlite.lib.php");
	require_once("../lib/uatool.class.php");
	require_once("../lib/process.class.php");
	$dbdir="../../sqlite/sqlite/";
	$dbname_proxypool=$dbdir."sys_proxypool.db3";
	$process= new process;
	ob_implicit_flush();
	ob_end_flush();
	$DB_proxypool=new SQLite($dbname_proxypool); 
	tbl_create_proxypool();
	$actfunc=$_GET["actfunc"];
	if($_SERVER["HTTP_HOST"]<>""){?>
<A HREF="?">proxy</A>
<A HREF="?actfunc=actfunc_recheck">actfunc_recheck okcheck badcheck fetch</A>
<A HREF="?actfunc=proxy_loopfetch89ip">proxy_loopfetch89ip</A>
<A HREF="?actfunc=proxy_fetch89ip">proxy_fetch89ip</A>
<A HREF="?actfunc=actfunc_geturlok">proxy_geturlok</A>
<A HREF="process.class.php">process</A>

<pre>
	<?
	}
	if(!empty($actfunc)){
		if(function_exists($actfunc)){
			$actfunc();
		}
		exit;
	}


//echo 
//proxy_import();
//proxy_fetch89ip(300);
//exit;
//proxy_changestatus("0","fetch");
$A_all=proxy_count();
list_2array($A_all);
exit;
/*
$n=proxy_count("ok");
echo "ok: $n\n";
$n=proxy_count("bad");
echo "bad: $n\n";
$n=proxy_count("0");
echo "0: $n\n";
$n=proxy_count("okcheck");
echo "okrecheck: $n\n";
$n=proxy_count("badcheck");
echo "badrecheck: $n\n";
$n=proxy_count("fetch");
echo "fetch: $n\n";
exit;
*/
/*
proxy_changestatus("ok","okcheck");

proxy_fetch89ip(300);
//proxy_loopfetch89ip();
//echo 
//$ua=ua_get();
//proxy_changestatus("bad","badcheck");

proxy_recheck("badcheck");//all recheck
exit;

//proxy_fetch89ip(330);
proxy_recheck();//all recheck
exit;

*/
//echo "\n\n\n";
//$str=proxy_geturlok();
//proxy_changestatus("bad","badcheck");
$str=proxy_geturlok(false);
//$str=proxy_geturlok(false);
$str1=msubstr($str,0,1500); 

//echo $str;
echo "\n\n\n";
$A_all=proxy_count();
list_2array($A_all);
exit;

}
//==========================
function proxy_import(){
	global $DB_proxypool,$dbdir;
	$dbname_import=$dbdir."sys_proxypool.bak.db3";
	$DB_dbimport=new SQLite($dbname_import); 
	$sql="select * from proxypool where status not like '%import%' limit 0,100000";
	$A_all=$DB_dbimport->queryall($sql);
	//print_r($A_all);
	//exit;
	foreach ($A_all as $A_row){
		$host=$A_row["host"];
		$sql="select proxyid from proxypool where  host='$host'";
		//	echo $sql."\n";
		$proxyid=$DB_proxypool->queryitem($sql);
		if (empty($proxyid)){
			$sql=make_sql_additem($A_row);
			$sql="insert into \"proxypool\"  ".$sql;
			//echo $sql."\n";
			$DB_proxypool->query($sql);
			$i_new++;
			echo $host." ok\n";
			//$Aproxy[]=$A_row;
		}else{
			//$sql="update proxypool set timex='$timex' ,updatetime='$time_current' where  proxyid='$proxyid'";
			echo $host." old\n";
			//echo $sql."\n";
			//$DB_proxypool->query($sql);
		}
		$status=$A_row["status"]."_import";
		$status="ok_import";
		//echo 
		$sql="update proxypool set status='$status' where  host='$host'";
		$DB_dbimport->query($sql);
	}
	echo $i_new;
}


function actfunc_geturlok(){
	$str=proxy_geturlok(false);
	//$str=proxy_geturlok(false);
	$str1=msubstr($str,0,1500); 
	echo 
	$str1=htmlentities($str1);
}
function actfunc_recheck(){
	while(true){
		proxy_recheck("fetch");//all recheck
		$n=proxy_count("badcheck");
		if($n<2)proxy_changestatus("bad","badcheck");
		proxy_recheck("badcheck");//all recheck
		proxy_changestatus("okbad","okcheck");
		proxy_recheck("okcheck");//all recheck
	}
}
function actfunc_proxy_loopfetch89ip(){
	proxy_loopfetch89ip();
}
//==========================
function proxy_geturlok($A_check=false){
	/*
	okjob    ok job list to work
	okcur    ok current running 
	ok       ok result ok
	okbad    ok result bad
	*/
	if(!$A_check)$A_check=array("url"=>"http://xueshu.baidu.com/s?wd=宋茜&tn=SE_baiduxueshu_c1gjeupa&cl=3&ie=utf-8&bs=宋茜&f=8&rsv_bp=1&rsv_sug2=0&sc_f_para=sc_tasktype%3D%7BfirstSimpleSearch%7D&rsv_spt=3","str_check"=>"宋茜");
	if(!is_array($A_check)){
		$A_check=array("url"=>$A_check,"str_check"=>"200");
	}
	$process= new process;
	$process->start();
	$i=1;
	while(true){
		$A_proxy=proxy_get("okjob");
		$host=$A_proxy["host"];
		proxy_set($host,"okcur");
		//print_r($A_proxy);
		//$timex=proxy_check($A_proxy,$A_check,"timex");
		//echo 
		if(!$A_proxy===false){
			//$str=proxy_check($A_proxy,$A_check,"str");
			$str=proxy_check($A_proxy,$A_check,"str200");
			if($str === 0){
				//echo "\nbaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad\n";
				proxy_set($host,"okbad");
				$i++;
				if($i<30){
					$process->check2stop($i."okbad",false);
				}else{
					$process->stop(false);
					return $str;
				}
			}else{
				//echo "\nooooooooooooooooooooooooooooook\n";
				proxy_set($host,"ok");
				$process->stop(false);
				return $str;
			}
		}else{
			$process->stop();
			echo "no ok proxy to use~!";
			exit;
		}
		//exit;
	}
}
function proxy_changestatus($status_fr,$status_to){
	global $DB_proxypool;
	$sql="update proxypool set status='$status_to' where status='$status_fr'";
	//echo $sql."\n";
	$DB_proxypool->query($sql);
}
function proxy_count($status=false){
	global $DB_proxypool;
	if($status===false){
		$sql="select status ,count(*) as total from proxypool group by status order by status";
		//echo $sql."\n";
		$A_all=$DB_proxypool->queryall($sql);
		$sql="select 'total',count(*) as total from proxypool ";
		//echo $sql."\n";
		$A_row=$DB_proxypool->queryrow($sql);
		$A_all[]=$A_row;
		return $A_all;
	}else{
		$sql="select count(*) from proxypool  where status='$status'";
		//echo $sql."\n";
		$n=$DB_proxypool->queryitem($sql);
		return $n;
	}
}

function proxy_recheck($status=0){
	global $process;
	$process->start();
	$i=1;
	while (true) {
		$A_proxy=proxy_get($status);
		if (!is_array($A_proxy)){
			$process->stop(false);
			return "over";
			//exit;
		}else{
			//print_r($A_proxy);
			$timex=proxy_check($A_proxy);
			if($timex==0){
				//echo "baaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaad<br>\n";
				proxy_set($A_proxy["host"],"bad",$timex);
			}else{
				//echo "ooooooooooooooooooooooooooooook<br>\n";
				proxy_set($A_proxy["host"],"ok",$timex);
			}
		}
		$i++;
		$process->check2stop($i);
		//if($i>14)exit;
	}
}

function proxy_loopfetch89ip(){
	$i=0;
	$n1=2000;
	global $process;
	$process->start();
	//echo "ssssssssssss";
	//exit;
	while ($i<$n1) {
		$i++;
		$res=proxy_fetch89ip(300);
		//$res=true;
		echo "\n$i =====\n";
		if (!$res){
			//echo "=======";
			$process->stop(false);
			//exit;
		}else{
			/*
			$n=proxy_count("ok");
			$time=date('Y-m-d H:i:s');
			echo $time."\n";
			echo "ok: $n\n";
			$n=proxy_count("bad");
			echo "bad: $n\n";
			$n=proxy_count("0");
			echo "0: $n\n";
			*/
			$process->check2stop($i);
		}
		//if($i>6)exit;
		//sleep(1);
		sleep(60*12);
	}
	//exit;
}

function proxy_fetch89ip($num=300,$status_default="fetch"){
	global $DB_proxypool;
	$proxysrc="http://www.89ip.cn/tqdl.html?api=1&num=$num&port=&address=&isp=";
	//         http://www.89ip.cn/tqdl.html?api=1&num=300&port=&address=&isp=
	//echo 
	$str=fetch2url($proxysrc);
	//$str=proxy_geturlok($proxysrc);
	$rule="});*</script>[name]<br>高效高匿";
	//echo
	$str=getpregmsg($str, $rule);
	$A_name=explode("<br>",$str);
	//print_r($A_name);
	//exit;
	//$A_name=pregmessage($strproxy, $rule, "name",0);
//$strtmp=$A_name[0];
//var_dump($A_name);
	if(empty($str)){
		echo "no proxy list fectched~!";
		return false;
	}else{
		$Aproxy=array();
		$fengefu=":";
	//echo "22222";
		$i_new=0;
		foreach ($A_name as $strtmp){
			$Atmp=explode($fengefu,$strtmp);
			//unset($Atmp[4]);
			//unset($Atmp[3]);
			$A_row["host"]=$host=$Atmp[0];
			$A_row["port"]=$Atmp[1];
			$timex=0;
			$A_row["timex"]=$timex;
			$A_row["status"]=$status_default;
			$time_current=date('Y-m-d H:i:s');
			$A_row["createtime"]=$time_current;
			$A_row["updatetime"]=$time_current;
			//print_r($A_row);
			//echo $host;
			if(!empty($host)){
				$sql="select proxyid from proxypool where  host='$host'";
					//echo $sql."\n";
				$proxyid=$DB_proxypool->queryitem($sql);
				if (empty($proxyid)){
					$sql=make_sql_additem($A_row);
					$sql="insert into \"proxypool\"  ".$sql;
					//echo $sql."\n";
					$DB_proxypool->query($sql);
					$i_new++;
					//echo $host." ok\n";
					//$Aproxy[]=$A_row;
				}else{
					$i_old++;
					//$sql="update proxypool set timex='$timex' ,updatetime='$time_current' where  proxyid='$proxyid'";
					//echo $host." old\n";
					//echo $sql."\n";
					//$DB_proxypool->query($sql);
				}
			}else{
			}
			//break;
		}
		echo "\nfetch new:  $i_new\n old : $i_old\n";
		return true;
		//var_dump($Aproxy);
	}
}


function proxy_get($status=0){
	global $DB_proxypool;
	//echo 
	$sql="select host,port from proxypool where  status='$status' order by random()";
	$arr=$DB_proxypool->queryrow($sql);
	if(empty($arr) && $status==="okjob"){
		proxy_changestatus("ok","okjob");
		proxy_changestatus("okcur","okjob");
		$n=proxy_count("okjob");
		if($n<20)proxy_fetch89ip(300,"okjob");//万不得已的情况下，直接下载proxy
		$sql="select host,port from proxypool where  status='okjob' order by random()";
		$arr=$DB_proxypool->queryrow($sql);
	}
	if(empty($arr)){
		return false;
	}else{
		return $arr;
	}
}
function proxy_set($host,$status,$timex=0){
	/*
	status
	0 current to use
	ok 
	bad 
	*/
	global $DB_proxypool;
	$time_current=date('Y-m-d H:i:s');
	$sql="update proxypool set status='$status',timex='$timex' ,updatetime='$time_current' where  host='$host'";
	//echo $sql."\n";
	$DB_proxypool->query($sql);

}

function tbl_create_proxypool(){
	global $DB_proxypool;
	$sql="
CREATE TABLE proxypool
(
[proxyid] integer primary key autoincrement,
[host] char(30),
[port] char(8),
[timex] smallint(10),
[createtime] datetime,
[updatetime] datetime,
[status] char(20) default 0
);
	";
	$DB_proxypool->query($sql);
	$sql="CREATE UNIQUE INDEX [host] On [proxypool] ([host]);";
	$DB_proxypool->query($sql);
	$sql="CREATE  INDEX [createtime] On [proxypool] ([createtime]);";
	$DB_proxypool->query($sql);
	$sql="CREATE  INDEX [updatetime] On [proxypool] ([updatetime]);";
	$DB_proxypool->query($sql);
	$sql="CREATE  INDEX [timex] On [proxypool] ([timex]);";
	$DB_proxypool->query($sql);

}

function proxy_check($A_proxy_para=false,$A_check=false,$mode="timex"){
	global $A_proxy;
	if(!$A_proxy_para){
		$A_proxy=array("host"=>0,"port"=>0);
	}else{
		$A_proxy=$A_proxy_para;
	}
	if(!$A_check)$A_check=array("url"=>"http://xueshu.baidu.com/s?wd=%E5%8C%BA%E5%9D%97%E9%93%BE&tn=SE_baiduxueshu_c1gjeupa&cl=3&ie=utf-8&bs=%E5%8C%BA%E5%9D%97%E8%BF%9E&f=8&rsv_bp=1&rsv_sug2=0&sc_f_para=sc_tasktype%3D%7BfirstSimpleSearch%7D&rsv_spt=3","str_check"=>"区块链");
	//print_r($arr);
	global $ua;
	$ua=ua_get();
	$host=$A_proxy["host"];
	$port=$A_proxy["port"];
	//$url="http://www.so.com/s?q=site%3Ajkwz.applinzi.com&src=360portal&_re=0";
	//$cookie="WZWS4=74c3bbf68ca26fac0d5c22d210d483d9";
	//$str_check='jkwz.applinzi.com';

	//$url="http://www.chinaso.com/";
	//$url="http://t.cn/RgP2N5y"; 
	//$url="http://news.sogou.com/news?ie=utf8&p=40230447&interV=kKIOkrELjbkRmLkElbkTkKIMkrELjboImLkEk74TkKIRmLkEk78TkKILkY==_-115092183&query=site%3Ajkwz.applinzi.com&"; 
	$url=$A_check["url"];
	$str_check=$A_check["str_check"];
	$time1 = time();
	//echo 
	$A_res=fetch2url($url,"info");
	$str=$A_res["str"];
	$A_info=$A_res["A_info"];
	$http_code=$A_info["http_code"];
	$time2 = time();
	$timex=$time2-$time1;
	//echo $timex;
	//echo "\n";
	//$str1=msubstr($str,0,1500); 
	//$str_check=mb_convert_encoding($str_check, "GBK", "UTF-8");  
	if($http_code==200){
		if($mode=="str200"){
			return $str;
		}else{
			$pos=strpos($str,$str_check);
			if($pos>2){
				//echo "oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo";
				if($mode=="timex"){
					return $timex;
				}else{
					return $str;
				}
			}else{
				//echo $str1;
				return 0;
			}
		}
	}else{
		//echo "$http_code \n";
		return 0;
	}
}

?>
