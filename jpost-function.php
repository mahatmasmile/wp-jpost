<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
$titlename = 'WP-JPOST';
$wp_jpost_options = array(
    "j_target_url", 
    "j_target_list_url",
    "j_target_category",
    "j_target_tags",
    'j_target_list_elmt',
    'j_target_list_to_single',
    "j_target_list_max_page",
	"j_single_page_date",
    "j_single_page_title", 
    "j_single_page_elmt", 
    "j_replace_tags",
    "j_random_tags",
    "j_search", 
    "j_replace", 
    "j_curl",
    "j_login",
    "j_random",
    "j_loginurl",
    "j_params",
    "j_single_page_other_elmt",
    "j_single_page_other_fetch",
);
$wp_jpost_jplugin_url = 'https://www.jiloc.com/43412.html';


    
// 获取后台配置
function wp_jpost_jopt($e='',$html = false ){
    $jtask_name = $_REQUEST['j_task_name'];
    if( $jtask_name ){
        $wp_jpost_jopt = json_decode(get_option('_wp_jpost_tasks'),true);
        if(!$wp_jpost_jopt)  return 'Get Option Error!';
        if( !$e )   return $wp_jpost_jopt = $wp_jpost_jopt[$jtask_name];
        $wp_jpost_jopt = stripslashes($wp_jpost_jopt[$jtask_name][$e]);
        
        return $html ? str_replace('"', '&#34;', str_replace("'", '&#39;', stripslashes($wp_jpost_jopt))) : stripslashes($wp_jpost_jopt);
    }else{
        return $html ? str_replace('"', '&#34;', str_replace("'", '&#39;', stripslashes(get_option($e)))) : stripslashes(get_option($e));
    }
}



function wp_jpost_jcopr(){
    global $titlename, $wp_jpost_options,$wp_jpost_jplugin_url;
    echo '<h2>'.$titlename.'设置';
    echo '<span class="d_themedesc" style="margin-left:12px;">作者：<a href="http://laoji.org/" target="_blank">老季</a> &nbsp;&nbsp'; 
    echo '<a href="'.$wp_jpost_jplugin_url.'" target="_blank">访问'.$titlename.'主页</a></span></h2>';
}



//  生成32位随机字符，防止CDN缓存
function wp_jpost_getRandChar(){
    return md5( time() );
}



function wp_jpost_char2GBK($char){
    return iconv("UTF-8","GBK",$char);
}



function wp_jpost_char2UTF8($str){
    if( mb_detect_encoding($str,"UTF-8, ISO-8859-1, GBK")!="UTF-8" ) {//判断是否不是UTF-8编码，如果不是UTF-8编码，则转换为UTF-8编码
        return  iconv("gbk","utf-8",$str);
    } else {
        return $str;
    }
}



function wp_jpost_g($url,$time='8000'){
    global $debug;
    if ( $debug ){
        return ;
    }
    echo "<script>";
    echo "function reflesh(){";
    echo 'window.location="'.$url.'"';
    echo "}";
    echo 'setTimeout("reflesh()",'.$time.');';
    echo "</script>";
}


function wp_jpost_output($string = ''){
    return '<p>'.$string.'</p>';
}

function wp_jpost_h1($string = ''){
    return '<h1>'.$string.'</h1>';
}


//显示页面查询次数、加载时间和内存占用 From wpdaxue.com
function wp_jpost_performance( $visible = false ) {
    $stat = sprintf(  '%d queries in %.3f seconds, using %.2fMB memory',
        get_num_queries(),
        timer_stop( 0, 3 ),
        memory_get_peak_usage() / 1024 / 1024
    );
    echo $visible ? $stat : "<!-- {$stat} -->" ;
}


function randomInsert($txt,$insert){//txt 内容；insert要插入的关键字，可以是链接，数组
    //将内容拆分成数组，每个字符都是一个value，英文，中文，符号都算一个，只能在utf-8下中文才能拆分
    preg_match_all("/[\x01-\x7f]|[\xe0-\xef][\x80-\xbf]{2}/", $txt, $match);
 
    $delay=array();
    $add=0;
    //获取不能插入的位置索引号($delay 数组)，也就是< > 之间的位置
    foreach($match[0] as $k=>$v){
        if($v=='<') $add=1;
        if($add==1) $delay[]=$k;
        if($v=='>') $add=0;
    }
  
    $str_arr=$match[0];
    $len=count($str_arr);
  
    foreach($insert as $k=>$v){
        //获取随机插入的位置索引值
        $insertk=insertK($len-1,$delay);
        //循环将insert数据 拼接到 随机生成的索引
        $str_arr[$insertk].=$insert[$k];
    }
    //合并插入 关键词后的数据，拼接成一段内容
    return join('',$str_arr);
}
 
function insertK($count,$delay){//count 随机索引值范围，也就是内容拆分成数组后的总长度-1；delay 不允许的随机索引值，也就是不能在 < > 之间
    $insertk=rand(0,$count);
    if(in_array($insertk,$delay)){//索引值不能在 不允许的位置处（也就是< > 之内的索引值）
        $insertk=insertK($count,$delay);//递归调用，直到随机插入的索引值不在 < > 这个索引值数组中
    }
    return $insertk;
}