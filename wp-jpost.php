<?php
/*
Plugin Name: WP-JPost
Plugin URI: http://laoji.org
Description: Wordpress的开源采集插件
Version: 0.2.4
Author: LaoJi
Author URI: http://laoji.org/
License: GPL
*/
if ( ! defined( 'ABSPATH' ) ) exit;

require_once 'jpost-function.php'; 
add_action('admin_menu', 'register_wp_jpost');

function register_wp_jpost() {
    add_menu_page( 'wp_jpost', 'wp-jpost', 'administrator', 'wp-jpost', 'jpost_task_list', '', 100);
    add_submenu_page('wp-jpost', $titlename.'任务列表', '任务列表', 'administrator', 'wp-jpost', 'jpost_task_list');
    add_submenu_page('wp-jpost', $titlename.'新增/修改任务', '新增/修改任务', 'administrator', 'jtask-option', 'jpost_setopt');
}
 
function jpost_task_list() {
    global $titlename, $wp_jpost_options;
    $wp_task_list = json_decode(get_option('_wp_jpost_tasks'),true);
    $jtask_name = $_REQUEST['j_task_name'];
    
    if( $_REQUEST['del'] && $jtask_name && array_key_exists($jtask_name, $wp_task_list) ){
        unset( $wp_task_list[$jtask_name] );
        update_option( '_wp_jpost_tasks', json_encode($wp_task_list) ); 
        $joutput = '<div class="updated settings-error"><p>'.$titlename.' 任务修改完成</p></div>';
//        $wp_task_list = json_decode(get_option('_wp_jpost_tasks'),true);
    }
    
    if (!is_array($wp_task_list)):
        echo "<h2>没有任务</h2>";
    else:
        $crawl_url = home_url('/jpost/');
?>
<?php wp_jpost_jcopr();?>
<div class=' wrap d_wrap'>
    <?php echo $joutput;?>
    <table class="wp-list-table widefat fixed striped posts tasks">

        <thead>
        <tr>
            <td>任务名</td>
            <td>任务网址</td>
            <td>操作</td>
        </tr>
        </thead>
            <?php foreach( $wp_task_list as $task_name => $the_task ):?>
        <tr>
            <td><?php echo $task_name;?></td>
            <td><a href="<?php echo $the_task[$wp_jpost_options[0]];?>" target="_blank"><?php echo $the_task[$wp_jpost_options[0]];?></a></td>
            <td>
                <a href='admin.php?page=jtask-option&j_task_name=<?php echo $task_name;?>'>修改</a> | 
                <a href='admin.php?page=wp-jpost&j_task_name=<?php echo $task_name;?>&del=true'>删除</a> |||
                <a target="_blank" href='<?php echo $crawl_url.'?'.'jtask='.$task_name.'&debug=true&jpage='.$the_task['j_target_list_max_page'];;?>'>调试</a> | 
                <a target="_blank" href='<?php echo $crawl_url.'?'.'jtask='.$task_name.'&jpage='.$the_task['j_target_list_max_page'];?>'>采集</a>
            </td>
        </tr>
        <?php endforeach;?>
    </table>
</div>
<?php  
    endif;
}



function jpost_setopt() {
    global $titlename, $wp_jpost_options,$wp_jpost_jplugin_url;
    $wp_task_name = $_REQUEST['j_task_name'];

    if ( 'save' == $_REQUEST['action'] ) {
        $wp_jpost_tasks = array();
        $wp_jpost_tasks = json_decode(get_option('_wp_jpost_tasks'),true);
        if(is_array($wp_jpost_tasks) && array_key_exists($wp_task_name, $wp_jpost_tasks)){
            unset( $wp_jpost_tasks[$wp_task_name] );
        }
        foreach ($wp_jpost_options as $value) {
            $wp_jpost_tasks[$wp_task_name][$value] = trim($_REQUEST[ $value ],',');
        }
        update_option( '_wp_jpost_tasks', json_encode($wp_jpost_tasks) ,false); 
    }
    
    if ( $_REQUEST['saved'] ) :
        echo '<div class="updated settings-error"><p>'.$titlename.' 任务 [ '.$wp_task_name.' ] 已保存.';
        echo '        <a target="_blank" style="font-size:18px;margin-left:24px;margin-top:6px;" href="'.home_url('/jpost/').'?'.'jtask='.$wp_task_name.'&debug=true&jpage='.$_REQUEST['j_target_list_max_page'].'">[ 调试 ] </a>';
        echo '        <a target="_blank" style="font-size:18px;margin-left:24px;margin-top:6px;" href="'.home_url('/jpost/').'?'.'jtask='.$wp_task_name.'&jpage='.$_REQUEST['j_target_list_max_page'].'">[ 采集 ] </a>';
        echo '</p></div>';
    endif;
?>
<div class="wrap d_wrap">
   <style type="text/css">
        h2,h2.span{font-size:1.3em;}
        input.ipt-b{min-width:240px;width:100%;}
        .postbox .inside table{ width:60%;min-width: 240px;text-align: left;}
        .postbox .inside table tr { font-size:14px; line-height:3;}
        .postbox .inside table tr td.d_tit{ min-width:124px;width:20%; vertical-align: top;}
    </style> 
    <?php wp_jpost_jcopr();?>

    <form method="post" class="d_formwrap" action="admin.php?page=jtask-option&jtask=<?php echo $wp_task_name;?>&saved=true">
            <div class="postbox">
                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">基本配置</span>
                <span class="toggle-indicator" aria-hidden="true"></span></button>
                <h2 class="hndle ui-sortable-handle"><span>基本配置 *</span></h2>
                <div class="inside">
                    <table >
                            <tr>
                    <td class="d_tit">任务名</td>
                    <td>
                        <input class="ipt-b" type="text" id="j_task_name" name="j_task_name" value="<?php echo $wp_task_name; ?>" <?php if( $wp_task_name ) echo ' readonly';?>>
                    </td>
                </tr>
                <tr>
                    <td class="d_tit">网站地址[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                    <td>
                        <input class="ipt-b" type="text" id="j_target_url" name="j_target_url" value="<?php echo wp_jpost_jopt('j_target_url'); ?>">（网址以/结束）
                    </td>
                </tr>
                <tr>
                    <td class="d_tit">列表地址[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                    <td>
                        <input class="ipt-b" type="text" id="j_target_list_url" name="j_target_list_url" value="<?php echo wp_jpost_jopt('j_target_list_url'); ?>">
                    </td>
                </tr>
                <tr>
                    <td class="d_tit">列表a元素[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                    <td>
                        <input class="ipt-b" type="text" id="j_target_list_elmt" name="j_target_list_elmt" value="<?php echo wp_jpost_jopt('j_target_list_elmt',true); ?>">
                        类似jquery的CSS选择器
                    </td>
                </tr> 
                <tr>
                    <td class="d_tit">分页格式[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                    <td>
                        <input class="ipt-b" type="text" id="j_target_list_to_single" name="j_target_list_to_single" value="<?php echo wp_jpost_jopt('j_target_list_to_single'); ?>">
                        例如:{page}.html
                    </td>
                </tr> 
                 <tr>
                    <td class="d_tit">分页最大值[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                    <td>
                        <input class="ipt-b" type="text" id="j_target_list_max_page" name="j_target_list_max_page" value="<?php echo wp_jpost_jopt('j_target_list_max_page'); ?>">
                    </td>
                </tr>    

                    </table>

                </div>
            </div>

            <div class="postbox">
                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">单页设置</span>
                <span class="toggle-indicator" aria-hidden="true"></span></button>
                <h2 class="hndle ui-sortable-handle"><span>单页设置 *</span></h2>
                <div class="inside">
                    <table >   
                        <tr>
                           <td class="d_tit">文章标题元素[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                           <td>
                               <input class="ipt-b" type="text" id="j_single_page_title" name="j_single_page_title" value="<?php echo wp_jpost_jopt('j_single_page_title',true); ?>">
                           </td>
                       </tr> 
					   <tr>
                           <td class="d_tit">文章时间元素[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                           <td>
                               <input class="ipt-b" type="text" id="j_single_page_date" name="j_single_page_date" value="<?php echo wp_jpost_jopt('j_single_page_date',true); ?>">
                           </td>
                       </tr>     
                       <tr>
                           <td class="d_tit">文章正文元素[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                           <td>
                               <input class="ipt-b" type="text" id="j_single_page_elmt" name="j_single_page_elmt" value="<?php echo wp_jpost_jopt('j_single_page_elmt',true); ?>">
                           </td>
                       </tr>  
					   
                           <tr>
                            <td class="d_tit">入库分类ID[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input class="ipt-b" type="text" id="j_target_category" name="j_target_category" value="<?php echo wp_jpost_jopt('j_target_category'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td class="d_tit">文章标签[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input class="ipt-b" type="text" id="j_target_tags" name="j_target_tags" value="<?php echo wp_jpost_jopt('j_target_tags'); ?>">
                            </td>
                        </tr> 
                    </table>
                </div>
            </div>




            <div class="postbox">
                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">删除元素</span>
                <span class="toggle-indicator" aria-hidden="true"></span></button>
                <h2 class="hndle ui-sortable-handle"><span>删除元素</span></h2>
                <div class="inside">
                    <div class="tagsdiv" id="post_tag">
                            <div class="jaxtag">
                                    <div class="nojs-tags hide-if-js">
                                            <label for="tax-input-post_tag">删除元素</label>
                                            <p><textarea name="tax_input[post_tag]" rows="3" cols="20" class="the-tags" id="tax-input-post_tag" aria-describedby="new-tag-post_tag-desc"></textarea></p>
                                    </div>
                                            <div class="ajaxtag hide-if-no-js">
                                            <label class="screen-reader-text" for="new-tag-post_tag">添加</label>
                                            <p><input data-wp-taxonomy="post_tag" id="new-tag-post_tag" name="newtag[post_tag]" class="newtag form-input-tip ui-autocomplete-input" size="16" autocomplete="off" aria-describedby="new-tag-post_tag-desc" value="" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-owns="ui-id-1" type="text">
                                            <input class="button tagadd" value="添加" type="button"></p><p>元素的class、id、固定标签。如：script</p>
                                    </div>
                            </div>
                            
                            <div class="tagchecklist">
                                <?php $j_replace_tags =  wp_jpost_jopt('j_replace_tags',true);
                                    if( $j_replace_tags ){
                                        $j_replace_tags_ary = array();
                                        $j_replace_tags_ary = explode(',',$j_replace_tags);
                                        foreach( $j_replace_tags_ary as $k => $v ):
                                            echo '<span><button type="button" id="check-num-'.$k.'" class="ntdelbutton">
                                                        <span class="remove-tag-icon" aria-hidden="true"></span>
                                                        <span class="screen-reader-text">'.$v.'</span>
                                                        </button>&nbsp;'.$v.'</span>';
                                        endforeach;
                                    }
                                ?>
                            </div>
                    </div>
                    <input name="j_replace_tags" type="hidden" value="<?php echo wp_jpost_jopt('j_replace_tags',true);?>">
                </div>
            </div>
        
        
            <div class="postbox">
                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">删除元素</span>
                <span class="toggle-indicator" aria-hidden="true"></span></button>
                <h2 class="hndle ui-sortable-handle"><span>随机关键词</span></h2>
                <div class="inside">
                    <div class="tagsdiv" id="post_tag">
                            <div class="jaxtag">
                                    <div class="nojs-tags hide-if-js">
                                            <label for="tax-input-post_tag">删除元素</label>
                                            <p><textarea name="tax_input[post_tag]" rows="3" cols="20" class="the-tags" id="tax-input-post_tag" aria-describedby="new-tag-post_tag-desc"></textarea></p>
                                    </div>
                                            <div class="ajaxtag hide-if-no-js">
                                            <label class="screen-reader-text" for="new-tag-post_tag">添加</label>
                                            <p><input data-wp-taxonomy="post_tag" id="new-tag-post_tag" name="newtag[post_tag]" class="newtag form-input-tip ui-autocomplete-input" size="16" autocomplete="off" aria-describedby="new-tag-post_tag-desc" value="" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-owns="ui-id-1" type="text">
                                            <input class="button tagadd" value="添加" type="button"></p>
                                    </div>
                            </div>
                            
                            <div class="tagchecklist">
                                <?php $j_random_tags =  wp_jpost_jopt('j_random_tags',true);
                                    if( $j_random_tags ){
                                        $j_random_tags_ary = array();
                                        $j_random_tags_ary = explode(',',$j_random_tags);
                                        foreach( $j_random_tags_ary as $k => $v ):
                                            echo '<span><button type="button" id="check-num-'.$k.'" class="ntdelbutton">
                                                        <span class="remove-tag-icon" aria-hidden="true"></span>
                                                        <span class="screen-reader-text">'.$v.'</span>
                                                        </button>&nbsp;'.$v.'</span>';
                                        endforeach;
                                    }
                                ?>
                            </div>
                    </div>
                    <input name="j_random_tags" type="hidden" value="<?php echo wp_jpost_jopt('j_random_tags',true);?>">
                </div>
            </div>        
        
        
        

            <div class="postbox">
                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">搜索替换</span>
                <span class="toggle-indicator" aria-hidden="true"></span></button>
                <h2 class="hndle ui-sortable-handle"><span>搜索替换</span></h2>   
                <div class="inside">
                    <table>    
                        <tr>
                            <td class="d_tit">搜索的文字/代码[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input class="ipt-b" type="text" id="j_search" name="j_search" value="<?php echo wp_jpost_jopt('j_search',true); ?>">
                            </td>
                        </tr>     
                        <tr>
                            <td class="d_tit">替换成文字/代码[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input class="ipt-b" type="text" id="j_replace" name="j_replace" value="<?php echo wp_jpost_jopt('j_replace',true); ?>">
                            </td>
                        </tr>  
                    </table>
                </div>
            </div>
			
            <div class="postbox">
                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">定时</span>
                <span class="toggle-indicator" aria-hidden="true"></span></button>
                <h2 class="hndle ui-sortable-handle"><span>定时</span></h2>   
                <div class="inside">
                    <table>    
                        <tr>
                            <td class="d_tit">间隔(秒)[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input class="ipt-b" type="text" id="j_timer_interval" name="j_timer_interval" value="<?php echo wp_jpost_jopt('j_timer_interval',true); ?>">
                            </td>
                        </tr>     
                        <tr>
                            <td class="d_tit">每天运行时间
							<br/>(* 1,2,3,4 #每小时 1,2,3,4分钟)
							<br/>(1:01,1:02,1:03,1:04 #每天 1点1,2,3,4分钟)[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input class="ipt-b" type="text" id="j_timer_exp" name="j_timer_exp" value="<?php echo wp_jpost_jopt('j_timer_exp',true); ?>">
                            </td>
                        </tr>  
                    </table>
                </div>
            </div>

            <div class="postbox">
                <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">其他设置</span>
                <span class="toggle-indicator" aria-hidden="true"></span></button>
                <h2 class="hndle ui-sortable-handle"><span>其他设置</span></h2>
                <div class="inside">
                    <table >           

                        <tr><td><p></p></td><td><p>如不需要登陆/内容页IFRAME，以下内容可留空</p></td></tr>
                        <tr>
                            <td class="d_tit">Curl 路径[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input class="ipt-b" type="text" id="j_curl" name="j_curl" value="<?php echo wp_jpost_jopt('j_curl'); ?>"> Windows主机或没有安装curl，请留空
                            </td>
                        </tr> 
                        <tr>
                            <td class="d_tit">模拟登录地址[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input class="ipt-b" type="text" id="j_loginurl" name="j_loginurl" value="<?php echo wp_jpost_jopt('j_loginurl'); ?>">
                            </td>
                        </tr>      
                        <tr>
                            <td class="d_tit">POST参数[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input class="ipt-b" type="text" id="j_params" name="j_params" value="<?php echo wp_jpost_jopt('j_params'); ?>"> 如：&username=laoji&passwd=laoji&type=person&url=laoji.org
                            </td>
                        </tr>  
                        <tr>
                            <td class="d_tit">内容页IFRAME抓取[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input class="ipt-b" type="text" id="j_single_page_other_fetch" name="j_single_page_other_fetch" value="<?php echo wp_jpost_jopt('j_single_page_other_fetch'); ?>"> 例：额外iframe等
                            </td>
                        </tr>      
                        <tr>
                            <td class="d_tit">IFRAME页内元素[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input class="ipt-b" type="text" id="j_single_page_other_elmt" name="j_single_page_other_elmt" value="<?php echo wp_jpost_jopt('j_single_page_other_elmt'); ?>"> 
                            </td>
                        </tr>  

                        <tr>
                            <td class="d_tit">其他[<a href="<?php echo $wp_jpost_jplugin_url;?>#readme">?</a>]</td>
                            <td>
                                <input type="checkbox" id="j_login" name="j_login" <?php if(wp_jpost_jopt('j_login')) echo 'checked="checked"' ?>> 需要登陆才能打开采集页面
                            </td>
                        </tr>   
                    </table>
                </div>
            </div>        
            <table style="width:100%;text-align: center">
                    <tr>
                <td class="d_tit"></td>
                <td>
                    <div class="d_desc">
                        <input class="button-primary" name="save" type="submit" value="保存设置">
                    </div>
                    <input type="hidden" name="action" value="save">
                </td>
            </tr>
            </table>
        
        
        
</form>
</div>
        
<style type="text/css">
    .postbox h2.hndle {font-size: 16px;padding: 8px 12px;margin: 0;line-height: 1.4;}
</style>

<script type="text/javascript">
jQuery(document).ready(function($){   

    $(".postbox h2.hndle").click(function(){
        $(this).parent().find(".inside").toggle();
    })
    
    $("input.button.tagadd").click(function(){
        var span = $(this).parent().find('input.newtag');
        if( span.val() == '' )  return;
        var the_inside = $(this).parent().parent().parent().parent().parent();
        var tag_nums = the_inside.find('.tagchecklist').children('span').length;
        the_inside.find('.tagchecklist').append('<span><button type="button" id="check-num-'+ tag_nums +'" class="ntdelbutton" >' +
						'<span class="remove-tag-icon" aria-hidden="true"></span>' +
						'<span class="screen-reader-text">' + span.val() + '</span>' +
						'</button>&nbsp;' + span.val() +'</span>');
        var h = '';
        h = the_inside.find('input[type=hidden]');
        h.val(h.val()+','+span.val());
        span.val('');
        console.log(the_inside.find('input[type=hidden]').val());
    });
    
    $('button.ntdelbutton').click(function(){
        var index_num = $(this).attr('id').replace('check-num-','');
        var span = $(this).parent();
        var the_inside = $(this).parent().parent().parent().parent();
        var tagchecklist = the_inside.find('.tagchecklist');
        var tag_nums = the_inside.find('.tagchecklist').children('span').length;
        var the_hidden = the_inside.find('input[type=hidden]');
        span.remove();
        the_hidden.val(delStr(the_hidden.val(),span.val()));
    });

    function delStr(strings , del){
        if( strings == del ){   
            return '';
        }
        var arr = strings.split(',');
        arr.splice($.inArray(del,arr),1);
        return arr.join(',');        
    }

});
</script>




<?php
}





//add rewrite rules in case another plugin flushes rules
add_action('init', 'jpost_plugin_rules');
//add plugin query vars (product_id) to wordpress
add_filter('query_vars', 'jpost_plugin_query_vars');
//register plugin custom pages display
add_filter('template_redirect', 'jpost_plugin_display');


 
function jpost_plugin_rules() {
    add_rewrite_rule('^jpost/?([^/]*)', 'index.php?jpost=jpost&$matches[1]', 'top');
    add_rewrite_endpoint( 'jpost', EP_PERMALINK );
// 重置规则请删除一下2行的注释    
//    global $wp_rewrite;     
//    $wp_rewrite->flush_rules(); 
}

function jpost_plugin_query_vars($query_vars) {
    array_push($query_vars,'jpost');
    array_push($query_vars,'jtask');
    array_push($query_vars,'i');
    array_push($query_vars,'jpage');
    array_push($query_vars,'debug');
    return $query_vars;
}

function jpost_plugin_display(  $templates = '' ) {
    global $wp_query,$wpdb,$wp_jpost_options;
    $jtask = get_query_var('jtask');
    $template = $wp_query->query_vars;
    if ( array_key_exists( 'jpost', $template ) && 'jpost' == $template['jpost'] ):
        $_REQUEST['j_task_name'] = $jtask;
        $jpage = get_query_var('jpage');
        $debug = get_query_var('debug');
        $i = get_query_var('i');
        
        $the_c = $jtask;
        $the_config = wp_jpost_jopt();
        if( !$the_config ) {
            die('Parameter C Error.');
        }

        foreach( $wp_jpost_options as $jkey => $jval ){
            if( wp_cache_get($jtask.'_'.$jval,'',true) ){
                $$jval = wp_cache_get($jtask.'_'.$jval,'',true);
            }else{
                $$jval = $the_config[$jval];
                wp_cache_add($jtask.'_'.$jval, $$jval);
            }
        }

        //$j_target_url = $the_config['j_target_url'];
        //$j_target_list_url = $the_config['j_target_list_url'];
        $j_target_category = explode(',',$j_target_category);
        //$j_target_tags = $the_config['j_target_tags'];
        $j_target_list_elmt = stripslashes($j_target_list_elmt);
        //$j_target_list_to_single = $the_config['j_target_list_to_single'];
        //$j_target_list_max_page = $the_config['j_target_list_max_page'];
        //$j_single_page_title = $the_config['j_single_page_title'];
        $j_single_page_elmt = stripslashes($j_single_page_elmt);
        $j_search = stripslashes($j_search);
        $j_replace = stripslashes($j_replace);
        $j_search_array = explode(',',$j_search);
        $j_replace_array = explode(',',$j_replace);
        //$j_curl = $the_config['j_curl'];
        $debug = isset($debug) ?  $debug : FALSE;

        if ( $j_login && !is_user_logged_in() ) { 
            die( wp_jpost_output('Access denied.') );
        }
        require_once  plugin_dir_path( __FILE__ )."wp-jcrawl.php";
        exit;
    endif;
}
