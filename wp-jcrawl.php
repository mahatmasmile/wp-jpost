<meta http-equiv="content-type" content="text/html;charset=utf-8">
<?php
if ( ! defined( 'ABSPATH' ) ) exit; 

if( !class_exists('Snoopy')){
    require_once ABSPATH . WPINC ."/class-snoopy.php";
}
if( !function_exists('str_get_html') ){
    require_once  plugin_dir_path( __FILE__ )."simple_html_dom.php";
}

$j_single_page_elmt = str_replace(
                        array('”','“','‘','’'),
                        array('"','"',"'","'"),
                        $j_single_page_elmt
                    );

//  用户定义变量
$category = $j_target_category;//分类ID
$tags_input = $j_target_tags;//分类标签
$siteUrl = $j_target_url;

//  获取总页数
$maxPage = $j_target_list_max_page;

//  准备循环数据
$url = '';
$url = $j_target_list_url.$j_target_list_to_single;
$cPage = $jpage >= 0 ? (int)$jpage : $maxPage;

if( $j_random ) {
    $jumpUrl = home_url('/jpost/').'?r='.wp_jpost_getRandChar().(isset($jtask) ? '&jtask='.$jtask : '').'&jpage={page}';
}else{
    $jumpUrl = home_url('/jpost/').'?'.(isset($jtask) ? 'jtask='.$jtask : '').'&jpage={page}';
}
$jumpUrl .= $debug ? '&debug=true' : '';
date_default_timezone_set('PRC');
$post_category = $category;
$url = str_replace('{page}', $cPage, $url);

if( $debug ){
    wp_cache_flush();
    echo wp_jpost_output("All caches have been flushed 所有缓存已清除.");
}

$cacheID = $the_c."_page_".$cPage;
$tt = wp_cache_get($cacheID,'',true);
if(  false === $tt ){
    $tsnoopy = new Snoopy();
    if( $j_curl ){  $tsnoopy->curl_path = $j_curl;  }
    $tsnoopy->fetch($url);        //获取所有内容
    if( $tsnoopy->results == false){
        echo wp_jpost_output("Get html failed 获取网页内容失败. <a target='_blank' href='{$url}'>".$url."</a>");
        exit;
    }
    if( $cPage <= 0 ){
		//$Loop=true;//计算下次执行时间
		$nextTimeout=6*3600*1000;//默认为6小时
		$curTime=time();
		if(isset($j_timer_interval) && $j_timer_interval ){
			$nextTimeout=$j_timer_interval;
		}else if(isset($j_timer_exp) && $j_timer_exp ){
			list($nexthour,$nextminute)=str_split($j_timer_exp,' ');
			if($nexthour && $nexthour!='*'){
				$nextHourArr=str_split($j_timer_exp,',');
				$nextTimeArr=array();
				foreach( $nextHourArr as $item){
					$nextTimeArr[]=strtotime(date("Y-m-d $item:i:s"));
				}
				$nextTimeArr[]=$nextTimeArr[0]+3600*24;
				foreach( $nextTimeArr as $item){
					if($item>$curTime+10){
						$nextTimeout=$item-$curTime*1000;
						break;
					}
				}
				
			}else{
				$nextMinArr=str_split($j_timer_exp,',');
				$nextTimeArr=array();
				foreach( $nextMinArr as $item){
					$nextTimeArr[]=strtotime(date("Y-m-d H:$item:s"));
				}
				$nextTimeArr[]=$nextTimeArr[0]+3600;
				foreach( $nextTimeArr as $item){
					if($item>$curTime+10){
						$nextTimeout=$item-$curTime*1000;
						break;
					}
				}
				
			}
		}
		$nextTime=date('Y-m-d`H:i',time()+$nextTimeout/1000);
        if( $Loop ){
            echo 'List Finished. '.date('Y-m-d H:i:s',time()).$enter ;
            wp_jpost_g( $_SERVER['SCRIPT_NAME']."?page=1","$nextTimeout" );
            die('List Finished 采集结束.Ready to Begin next Loop.. 下次启动时间：$nextTime ');
        }else{  
			wp_cache_flush();  
			wp_jpost_g( $_SERVER['SCRIPT_NAME']."?page=1","$nextTimeout" );
			die( "List Finished 采集结束. 下次启动时间：$nextTime ");
		}
    }
    // 抓取单页链接
    $jSingleList = '';
    $tt = '';
    $jSingleList = @str_get_html($tsnoopy->results);
    $tt = $jSingleList->find($j_target_list_elmt);
    wp_cache_add($cacheID, $tt);

    echo wp_jpost_output('List cache setted 已设置列表缓存 '.$cacheID);

}else{
    echo wp_jpost_output('Using list cache 正在使用列表缓存 '.$cacheID);
}

//  抓取不到文章链接
if( !is_array( $tt )) {   echo "URL ERROR 网址错误 : ".$url.$enter;}

if( $i == '')   //控制页面元素
    $i= count($tt)-1;
elseif($i > 0) 
    $i = (int)$i;
elseif( $i == 0 ){ 
    wp_cache_delete($cacheID);
    echo wp_jpost_output("List cache deleted 列表缓存已删除 ".$cacheID);
}

//  文章页面内容
$value = '';
$value = $tt[$i]->href;

if( !$value ){
    echo wp_jpost_h1('URL 网址');
    echo wp_jpost_output( $url );
    echo wp_jpost_h1('RETURN HTML 返回的HTML');
    var_dump( wp_jpost_char2UTF8($tsnoopy->results) );
    echo wp_jpost_output("Get list href Error 获取网址列表a链接错误. <a target='_blank' href='{$value}'>{$value}</a>");
    exit;
}
if ( strpos( $value,"http://" ) === false && strpos($value , 'https://') === false ) {
    $value = $j_target_url.$value;
}
$value = str_replace('../','',$value);
if( $debug  == true ){  
    echo wp_jpost_h1('CACHED URL LIST 已缓存的列表网址');
    foreach( $tt as $ttt){
        echo wp_jpost_output($ttt->href);
    }
    echo wp_jpost_h1('Crawled URL 抓取网址');
    echo wp_jpost_output('<a target="_blank" href="'.$value.'">'.$value.'</a>');
}


//$outputUrl = str_replace($siteUrl, '', $value);
$outputUrl = $value;
$singleSnoopy = '';
$singleSnoopy = new Snoopy;
if( $j_curl ){  
    $singleSnoopy->curl_path = $j_curl;  
    echo wp_jpost_output('Using 正在使用 curl');
}

if(strpos($value, 'https://') === true )
        $singleSnoopy -> port = 443;


// 模拟用户登录
$submit_url = $j_loginurl;
if( $submit_url && $j_params ){
    $submit_vars = array();
    if( $j_params ){
        parse_str($j_params,$submit_vars);
    }

    $singleSnoopy->referer = $j_target_url;
    //$singleSnoopy->user = $submit_vars['UserName'];
    //$singleSnoopy->pass = $submit_vars['PassWord'];
    $singleSnoopy->submit($submit_url, $submit_vars); 
    //var_dump(wp_jpost_char2UTF8($singleSnoopy->results));
}
$singleSnoopy->fetch($value);
$result = '';
$result = wp_jpost_char2UTF8($singleSnoopy->results);
$html = @str_get_html($result);

// 获取标题
$post_title = '';
$post_title = $html->find($j_single_page_title);
$post_title = wp_jpost_char2UTF8($post_title[0]->innertext);

// 获取时间
$post_date = '';
$post_date = $html->find($j_single_page_date);
$post_date = wp_jpost_char2UTF8($post_date[0]->innertext);
$post_date = trim($post_date);

// 删除指定元素并获取正文
$p_content = '';
if( $j_replace_tags ):
    $j_replace_tags = explode(',', $j_replace_tags);
    foreach( $j_replace_tags as $tags_ele):
        $tags_n = $html->find($tags_ele);
        if( count( $tags_n ) >= 1 ){
            for($tags_i = 0 ; $tags_i < count( $tags_n ); $tags_i++  ):
                $html->find($tags_ele,$tags_i)->outertext = '';
            endfor;
        }
    endforeach;
endif;
$p_content = $html->find($j_single_page_elmt);

$post_content = '';
if( count(  $p_content ) >= 1){
    for( $iii=0; $iii<=count($p_content);$iii++ ):
        $post_content .= '<p>'.wp_jpost_char2UTF8($p_content[$iii]->innertext).'</p>';
    endfor;
}

// 正文部分额外抓取
if( $j_single_page_other_fetch && $j_single_page_other_elmt ){
    $po_content = $html->find($j_single_page_other_fetch);
    $ourl = $po_content[0]->src;
    if ( strpos( $ourl,$j_target_url ) === false ) {
        $value = $j_target_url.$ourl;
        $value = str_replace('../','',$value);
    }
    $singleSnoopy->fetch($value);
    $ohtml = @str_get_html($singleSnoopy->results);
    $po_content = $ohtml->find( $j_single_page_other_elmt );
    $post_content .= wp_jpost_char2UTF8($po_content[0]->innertext);
    $ohtml->clear();
}

$html->clear();



if( is_array($j_search_array)){//替换内容
    $post_title = str_replace($j_search_array,$j_replace_array,$post_title);
    $post_content = str_replace($j_search_array,$j_replace_array,$post_content);
	$post_date = str_replace($j_search_array,$j_replace_array,$post_date);
}
//匹配日期
function post_date_matchparse($post_date){
	$post_date = trim($post_date);
	$patten = "/^\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])(\s+(0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9])\:(0?[0-9]|[1-5][0-9]))?$/";
	if ($post_date && preg_match($patten, $post_date)){
		
	}else if($post_date && strlen($post_date)>20){
		$patten = "/\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])(\s+(0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9])\:(0?[0-9]|[1-5][0-9]))?/";
		if(preg_match($patten, $post_date)){
			preg_match_all($patten,$post_date, $t_date);
			$post_date=$t_date[0][0];
		}else{
			$patten = "/(\d{4})(年|\\-|\\/)/";
			preg_match_all($patten,$post_date, $t_year);
			$t_year=$t_year[1][0];
			$patten = "/(\d{1,2})(月|\\-|\\/)/";
			preg_match_all($patten,$post_date, $t_month);
			$t_month=$t_month[1][0];
			$patten = "/(月|\\-|\\/)(\d{1,2})(日?)/";
			preg_match_all($patten,$post_date, $t_day);
			$t_day=$t_day[2][0];
			
			$patten = "/\d{1,2}\\:\d{1,2}(\\:\d{1,2})?/";
			preg_match_all($patten,$post_date, $t_time);
			$t_time=$t_time[0][0];
			if($t_time){
				$t_time=" $t_time";
			}else{
				$t_time="";
			}
			$post_date="$t_year-$t_month-$t_day$t_time";
		}
		//die(json_encode($t_year[1][0],true));
	}

	if($post_date){
		$post_date=date("Y-m-d H:i:s",strtotime($post_date));
	}
	if(!$post_date){
		$post_date=date("Y-m-d H:i:s",time());
	}
	return $post_date;
}
$post_date=post_date_matchparse($post_date);

if( $j_random_tags ){   //随机插入关键词
    $post_content = randomInsert($post_content , explode(',', $j_random_tags));
}



if( $post_title && $post_content ){//检查内容
        $the_query = '';
        $post_ID = '';
        $the_query = $wpdb->get_row("SELECT `ID`,post_content FROM `wp_posts` WHERE `post_title`='#".$post_title."#' and `post_status`='publish' AND `post_type` = 'post' order by ID desc" );  
        $post_ID = $the_query->ID;

        if( $post_ID ){// 修改文章
                $status = 'Modified 已修改 ';
                $my_post = array(
                    'ID'            =>  $post_ID,
                    'post_content'  =>  $post_content,
                    "Auto_Remove_Link"  => "1",

                );
                if(function_exists(Auto_Save_Image_savepost )):
                    try{
                        $my_post['post_content'] = Auto_Save_Image_savepost($my_post);
                    }  catch (Exception $e){
                         echo wp_jpost_output('Exception Message : ' .$e->getMessage());
                    }
                endif;
                unset( $e ); 
                kses_remove_filters(); //停止过滤字符
                wp_update_post( $my_post );
                kses_init_filters();

        } else {// 插入wordpress 库
                $status = 'Inserted 已插入 ';

                $my_post = array(
                        "temp_ID2"      => $insert_id,
                        "Auto_Remove_Link"  => "1",
                        "post_title"    =>  "#".$post_title."#",
                        "post_content"  =>  $post_content,
                        'post_status'   =>  'publish',
                        'post_author'   =>  '1',
                        'post_category' =>  $post_category,
                        'tags_input'    =>  $tags_input,
                        "post_date"     =>  $post_date,//date("Y-m-d H:i:s",time()),   
                        'guid'          =>  '',
                );
                if(function_exists(Auto_Save_Image_savepost )):
                    try{
                        $my_post['post_content'] = Auto_Save_Image_savepost($my_post);
                    }  catch (Exception $e){
                         echo wp_jpost_output('Exception Message 异常错误 : ' .$e->getMessage());
                    }
                endif;
                unset( $e );   
                kses_remove_filters(); //停止过滤字符
                $post_ID = wp_insert_post( $my_post);
				//die("id $post_ID".json_encode($my_post,true));
                kses_init_filters();
        }
        if( $debug ){
            $my_post = get_post($post_ID);
            echo wp_jpost_h1('TITLE 标题 ');
            echo $my_post->post_title.$enter;
            echo wp_jpost_h1('CONTENT 内容 ');
            echo $my_post->post_content.$enter;
            wp_cache_flush();
            echo wp_jpost_output("All caches have been flushed 所有缓存已清除.");
            wp_delete_post($post_ID);
            echo wp_jpost_output("The Article has deleted 文章已删除.");             
        }else{
            $insertUrl = get_permalink($post_ID);
            echo wp_jpost_output("{$outputUrl} => {$status} <a href='{$insertUrl}' target='_blank'>{$insertUrl}</a>");
            echo wp_jpost_output("This page will automatically jump to next URL 本网址将自动跳转到下一地址 :)");
        }
        unset( $downloads );
        unset( $the_query );
        unset( $singleSnoopy );
        echo $enter;
        unset( $ppplay );
}else{
    echo wp_jpost_h1('TITLE 标题 ');
    var_dump( $post_title );
    echo wp_jpost_h1('CONTENT 内容 ');
    var_dump( $post_content );
    echo wp_jpost_output("<span style='color:red'>Something NULL 标题/正文为空, Skipped 忽略本内容.</span>");
}

if( $i <= 0 ){//当前页面最后一个元素，页面ID--
        $cPage--;
        $jumpUrl = str_replace('{page}', $cPage, $jumpUrl);
        echo wp_jpost_output("Next URL 下一个地址 : <a href='{$jumpUrl}'>".$jumpUrl."</a>");
        wp_jpost_g($jumpUrl);
}else{
        $jumpUrl .= '&i={i}';
        $i--;
        $jumpUrl = str_replace(array('{page}','{i}'), array($cPage,$i), $jumpUrl);
        echo wp_jpost_output("Next URL 下一个地址 : <a href='{$jumpUrl}'>".$jumpUrl."</a>");
        wp_jpost_g($jumpUrl);
}

