<?php
//Init theme
add_action('load-themes.php','Init_theme');
function Init_theme(){
  global $pagenow;
  if('themes.php'==$pagenow&&isset($_GET['activated'])){
    wp_redirect(admin_url('themes.php?page=kratos'));
    exit;
  }
}
//The admin control module
if(!function_exists('optionsframework_init')){
    define('OPTIONS_FRAMEWORK_DIRECTORY',get_template_directory_uri().'/inc/theme-options/');
    require_once (get_template_directory().'/inc/theme-options/options-framework.php');
    $optionsfile = locate_template('options.php');
    load_template($optionsfile);
}
function kratos_options_menu_filter($menu){
  $menu['mode'] = 'menu';
  $menu['page_title'] = '主题设置';
  $menu['menu_title'] = '主题设置';
  $menu['menu_slug'] = 'kratos';
  return $menu;
}
add_filter('optionsframework_menu','kratos_options_menu_filter');
//The menu navigation registration
function kratos_register_nav_menu(){register_nav_menus(array('header_menu'=>'顶部菜单'));}
add_action('after_setup_theme','kratos_register_nav_menu');
//Highlighting the active menu
function kratos_active_menu_class($classes){
    if(in_array('current-menu-item',$classes) OR in_array('current-menu-ancestor',$classes)) $classes[] = 'active';
    return $classes;
}
add_filter('nav_menu_css_class','kratos_active_menu_class');
//Disable automatic formatting
remove_filter('the_content','wpautop');
remove_filter('the_content','wptexturize');
//Support chinese tags
add_action('parse_request','kratos_chinese_tag_names_parse_request');
add_filter('get_pagenum_link','kratos_chinese_tag_names_get_pagenum_link');
function kratos_chinese_convencoding($str,$to='UTF-8',$from='GBK'){
    if(function_exists('mb_convert_encoding')){
        $str = mb_convert_encoding($str,$to,$from);
    }else if(function_exists('iconv')){
        $str = iconv($from,$to."//IGNORE",$str);
    }
    return $str;
}
function kratos_chinese_tag_names_parse_request($obj){
    if($obj->did_permalink==false) return;
    if(isset($obj->request)) $obj->request = kratos_chinese_convencoding($obj->request,get_option('blog_charset'));
    if(isset($obj->query_vars)) foreach ($obj->query_vars as $key => &$value){
        if($key=='s') continue;
        $value = kratos_chinese_convencoding($value,get_option('blog_charset'));
    }
}
function kratos_chinese_tag_names_get_pagenum_link($result){
    $result = kratos_chinese_convencoding($result,get_option('blog_charset'));
    return $result;
}
//Disable google fonts
function kratos_disable_open_sans($translations,$text,$context,$domain){
    if('Open Sans font: on or off'==$context&&'on'==$text) $translations = 'off';
    return $translations;
}
add_filter('gettext_with_context','kratos_disable_open_sans',888,4);
//Support webp upload
add_filter('upload_mimes','kratos_upload_webp');
function kratos_upload_webp ($existing_mimes=array()){
  $existing_mimes['webp']='image/webp';
  return $existing_mimes;
}
//The length and suffix
function kratos_excerpt_length($length){return 170;}
add_filter('excerpt_length','kratos_excerpt_length');
function kratos_excerpt_more($more){return '……';}
add_filter('excerpt_more','kratos_excerpt_more');
//Load scripts
function kratos_theme_scripts(){
    $url1 = 'https://cdn.jsdelivr.net/gh/xb2016/kratos-pjax@'.KRATOS_VERSION;
    $url2 = get_bloginfo('template_directory');
    if(kratos_option('js_out')) $jsdir = $url1; else $jsdir = $url2;
    if(kratos_option('owo_out')) $owodir = $url1; else $owodir = $url2;
    if(kratos_option('fa_url')) $fadir = kratos_option('fa_url'); else $fadir = $url2.'/css/font-awesome.min.css';
    if(kratos_option('jq_url')) $jqdir = kratos_option('jq_url'); else $jqdir = $url2.'/js/jquery.min.js';
    if(kratos_option('bs_url')) $bsdir = kratos_option('bs_url'); else $bsdir = $url2.'/js/bootstrap.min.js';
    if(!is_admin()){
        wp_enqueue_style('fontawe',$fadir,array(),'4.7.0');
        wp_enqueue_style('kratos',$jsdir.'/css/kratos.min.css',array(),KRATOS_VERSION);
        wp_enqueue_script('theme-jq',$jqdir,array(),'2.1.4');
        wp_enqueue_script('bootstrap',$bsdir,array(),'3.3');
        wp_enqueue_script('layer',$jsdir.'/js/layer.min.js',array(),'3.1.0');
        wp_enqueue_script('OwO',$jsdir.'/js/OwO.min.js',array(),'1.0.1');
        if(kratos_option('ap_footer')) wp_enqueue_script('aplayer',$jsdir.'/js/aplayer.min.js',array(),'1.10.1');
        if(kratos_option('page_pjax')) wp_enqueue_script('pjax',$jsdir.'/js/pjax.min.js',array(),'0.0.7');
        wp_enqueue_script('kratos',$jsdir.'/js/kratos.js',array(),KRATOS_VERSION);
    }
    if(kratos_option('site_girl')=='l2d'&&!wp_is_mobile()){
        wp_enqueue_script('live2d',$jsdir.'/js/live2d.js',array(),'l2d');
        wp_enqueue_script('waifu',$jsdir.'/js/waifu-tips.js',array(),'1.3');
    }
    if(kratos_option('site_sa')&&!wp_is_mobile()){if(kratos_option('head_mode')=='pic') $site_sa_h = 61; else $site_sa_h = 103;}
    $d2kratos = array(
         'thome'=> get_stylesheet_directory_uri(),
         'ctime'=> kratos_option('createtime'),
        'donate'=> kratos_option('paytext_head'),
          'scan'=> kratos_option('paytext'),
        'alipay'=> kratos_option('alipayqr_url'),
        'wechat'=> kratos_option('wechatpayqr_url'),
           'owo'=> $owodir,
       'site_sh'=> $site_sa_h
    );
    wp_localize_script('kratos','xb',$d2kratos);
}
add_action('wp_enqueue_scripts','kratos_theme_scripts');
//Remove code
remove_action('wp_head','wp_print_head_scripts',9);
remove_action('wp_head','wp_generator');
remove_action('wp_head','rsd_link');
remove_action('wp_head','wlwmanifest_link');
remove_action('wp_head','index_rel_link');
remove_action('wp_head','parent_post_rel_link',10,0);
remove_action('wp_head','start_post_rel_link',10,0);
remove_action('wp_head','adjacent_posts_rel_link_wp_head',10,0);
remove_action('wp_head','rel_canonical');
remove_action('wp_head','feed_links',2);
remove_action('wp_head','feed_links_extra',3);
remove_action('wp_head','rest_output_link_wp_head',10);
remove_action('wp_head','wp_oembed_add_discovery_links',10);
remove_action('admin_print_scripts','print_emoji_detection_script');
remove_action('admin_print_styles','print_emoji_styles');
remove_action('wp_head','print_emoji_detection_script',7);
remove_action('wp_print_styles','print_emoji_styles');
remove_action('embed_head','print_emoji_detection_script');
remove_filter('the_content','wptexturize'); 
remove_filter('the_content_feed','wp_staticize_emoji');
remove_filter('comment_text_rss','wp_staticize_emoji');
remove_filter('wp_mail','wp_staticize_emoji_for_email');
add_filter('emoji_svg_url','__return_false');
add_filter('show_admin_bar','__return_false');
add_action('wp_enqueue_scripts','mt_enqueue_scripts',1);
add_filter('rest_enabled','_return_false');
add_filter('rest_jsonp_enabled','_return_false');
function mt_enqueue_scripts(){wp_deregister_script('jquery');}
function disable_embeds_init(){
    global $wp;
    $wp->public_query_vars = array_diff($wp->public_query_vars,array('embed'));
    remove_action('rest_api_init','wp_oembed_register_route');
    add_filter('embed_oembed_discover','__return_false');
    remove_filter('oembed_dataparse','wp_filter_oembed_result',10);
    remove_action('wp_head','wp_oembed_add_discovery_links');
    remove_action('wp_head','wp_oembed_add_host_js');
    add_filter('tiny_mce_plugins','disable_embeds_tiny_mce_plugin');
    add_filter('rewrite_rules_array','disable_embeds_rewrites');
}
add_action('init','disable_embeds_init',9999);
function disable_embeds_tiny_mce_plugin($plugins){return array_diff($plugins,array('wpembed'));}
function disable_embeds_rewrites($rules){
    foreach ($rules as $rule => $rewrite){
        if(false !== strpos($rewrite,'embed=true')) unset($rules[$rule]);
    }
    return $rules;
}
function disable_embeds_remove_rewrite_rules(){
    add_filter('rewrite_rules_array','disable_embeds_rewrites');
    flush_rewrite_rules();
}
register_activation_hook(__FILE__,'disable_embeds_remove_rewrite_rules');
function disable_embeds_flush_rewrite_rules(){
    remove_filter('rewrite_rules_array','disable_embeds_rewrites');
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__,'disable_embeds_flush_rewrite_rules');
//Prohibit character escaping
$qmr_work_tags = array('the_title','the_excerpt','single_post_title','comment_author','comment_text','link_description','bloginfo','wp_title','term_description','category_description','widget_title','widget_text');
foreach($qmr_work_tags as $qmr_work_tag){remove_filter ($qmr_work_tag,'wptexturize');}
remove_filter('the_content','wptexturize');
//Add the page html
add_action('init','html_page_permalink',-1);
function html_page_permalink(){
    if(kratos_option('page_html')){
        global $wp_rewrite;
        if(!strpos($wp_rewrite->get_page_permastruct(),'.html')) $wp_rewrite->page_structure = $wp_rewrite->page_structure.'.html';
    }
}
//Remove the revision
remove_action('post_updated','wp_save_post_revision');
//Auto add <p>
remove_filter('the_content','wpautop');
add_filter('the_content','wpautop',12);
//Link manager(Link page pre)
add_filter('pre_option_link_manager_enabled','__return_true');
//Remove the excess CSS selectors
add_filter('nav_menu_css_class','my_css_attributes_filter',100,1);
add_filter('nav_menu_item_id','my_css_attributes_filter',100,1);
add_filter('page_css_class','my_css_attributes_filter',100,1);
function my_css_attributes_filter($var){return is_array($var)?array_intersect($var,array('current-menu-item','current-post-ancestor','current-menu-ancestor','current-menu-parent')):'';}
//Add article type
add_theme_support('post-formats',array('gallery','video'));
//Keywords Description set
function kratos_keywords(){
    if(is_home()||is_front_page()){echo kratos_option('site_keywords');}
    elseif(is_category()){single_cat_title();}
    elseif(is_single()){
        echo trim(wp_title('',FALSE)).',';
        if(has_tag()){foreach((get_the_tags()) as $tag){echo $tag->name.',';}}
        foreach((get_the_category()) as $category){echo $category->cat_name.',';} 
    }
    elseif(is_search()){the_search_query();}
    else{echo trim(wp_title('',FALSE));}
}
function kratos_description(){
    if(is_home()||is_front_page()){echo trim(kratos_option('site_description'));}
    elseif(is_category()){$description = strip_tags(category_description());echo trim($description);}
    elseif(is_single()){ 
        if(get_the_excerpt()){echo get_the_excerpt();}
        else{global $post;$description = trim(str_replace(array("\r\n","\r","\n","　"," ")," ",str_replace("\"","'",strip_tags(do_shortcode($post->post_content)))));echo mb_substr($description,0,220,'utf-8');}
    }
    elseif(is_search()){echo '“';the_search_query();echo '”为您找到结果 ';global $wp_query;echo $wp_query->found_posts;echo ' 个';}
    elseif(is_tag()){$description = strip_tags(tag_description());echo trim($description);}
    else{$description = strip_tags(term_description());echo trim($description);}
}
//Article outside chain optimization
function imgnofollow($content){
    $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>";
    if(preg_match_all("/$regexp/siU",$content,$matches,PREG_SET_ORDER)){
        if(!empty($matches)){
            $srcUrl = get_option('siteurl');
            for ($i=0;$i<count($matches);$i++){
                $tag = $matches[$i][0];
                $tag2 = $matches[$i][0];
                $url = $matches[$i][0];
                $noFollow = '';
                $pattern = '/target\s*=\s*"\s*_blank\s*"/';
                preg_match($pattern,$tag2,$match,PREG_OFFSET_CAPTURE);
                if(count($match)<1) $noFollow .= ' target="_blank" ';
                $pattern = '/rel\s*=\s*"\s*[n|d]ofollow\s*"/';
                preg_match($pattern, $tag2, $match, PREG_OFFSET_CAPTURE);
                if(count($match)<1) $noFollow .= ' rel="nofollow" ';
                $pos = strpos($url,$srcUrl);
                if($pos===false){
                    $tag = rtrim ($tag,'>');
                    $tag .= $noFollow.'>';
                    $content = str_replace($tag2,$tag,$content);
                }
            }
        }
    }
    $content = str_replace(']]>',']]>',$content);
    return $content;
}
add_filter('the_content','imgnofollow');
//The title set
function kratos_wp_title($title,$sep){
    global $paged,$page;
    if(is_feed()) return $title;
    $title .= get_bloginfo('name');
    $site_description = get_bloginfo('description','display');
    if($site_description&&(is_home()||is_front_page())) $title = "$title $sep $site_description";
    if($paged>=2||$page>=2) $title = "$title $sep " . sprintf('Page %s',max($paged,$page));
    return $title;
}
add_filter('wp_title','kratos_wp_title',10,2);
//Banner
function kratos_banner(){
    if(!$output = get_option('kratos_banners')){
        $output = '';
        $kratos_banner_on = kratos_option("kratos_banner")?kratos_option("kratos_banner"):0;
        if($kratos_banner_on){
            for($i=1; $i<6; $i++){
                $kratos_banner{$i} = kratos_option("kratos_banner{$i}")?kratos_option("kratos_banner{$i}"):"";
                $kratos_banner_url{$i} = kratos_option("kratos_banner_url{$i}")?kratos_option("kratos_banner_url{$i}"):"";
                if($kratos_banner{$i}){
                    $banners[] = $kratos_banner{$i};
                    $banners_url[] = $kratos_banner_url{$i};
                }
            }
            $count = count($banners);
            $output .= '<div id="slide" class="carousel slide" data-ride="carousel">';
            $output .= '<ol class="carousel-indicators">';
            for($i=0;$i<$count;$i++){
                $output .= '<li data-target="#slide" data-slide-to="'.$i.'"';
                if($i==0) $output .= 'class="active"';
                $output .= '></li>';
            };
            $output .='</ol>';
            $output .= '<div class="carousel-inner" role="listbox">';
            for($i=0;$i<$count;$i++){
                $output .= '<div class="item';
                if($i==0) $output .= ' active';
                $output .= '">';
                if(!empty($banners_url[$i])){
                    $output .= '<a href="'.$banners_url[$i].'"><img src="'.$banners[$i].'"/></a>';
                }else{
                    $output .= '<img src="'.$banners[$i].'"/>';
                }
                $output .= "</div>";
            };
            $output .= '</div>';
            $output .= '<span class="left carousel-control" href="#slide" role="button" data-slide="prev">';
            $output .= '<span class="fa fa-chevron-left glyphicon glyphicon-chevron-left"></span></span>';
            $output .= '<span class="right carousel-control" href="#slide" role="button" data-slide="next">';
            $output .= '<span class="fa fa-chevron-right glyphicon glyphicon-chevron-right"></span></span></div>';
            update_option('kratos_banners',$output);
        }
    }
    echo $output;
}
function clear_banner(){update_option('kratos_banners','');}
add_action('optionsframework_after_validate','clear_banner');
//More users' info
function get_client_ip(){
    if(getenv("HTTP_CLIENT_IP")&&strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown")) $ip = getenv("HTTP_CLIENT_IP");
    elseif(getenv("HTTP_X_FORWARDED_FOR")&&strcasecmp(getenv("HTTP_X_FORWARDED_FOR"),"unknown")) $ip = getenv("HTTP_X_FORWARDED_FOR");
    elseif(getenv("REMOTE_ADDR")&&strcasecmp(getenv("REMOTE_ADDR"),"unknown")) $ip = getenv("REMOTE_ADDR");
    elseif(isset($_SERVER['REMOTE_ADDR'])&&$_SERVER['REMOTE_ADDR']&&strcasecmp($_SERVER['REMOTE_ADDR'],"unknown")) $ip = $_SERVER['REMOTE_ADDR'];
    else $ip = "unknown";
    return ($ip);
}
add_action('wp_login','insert_last_login');
function insert_last_login($login){
    global $user_id;
    $user = get_userdatabylogin($login);
    update_user_meta($user->ID,'last_login',current_time('mysql'));
    $last_login_ip = get_client_ip();
    update_user_meta($user->ID,'last_login_ip',$last_login_ip);
}
add_filter('manage_users_columns','add_user_additional_column');
function add_user_additional_column($columns){
    $columns['user_nickname'] = '昵称';
    $columns['user_url'] = '网站';
    $columns['reg_time'] = '注册时间';
    $columns['last_login'] = '上次登录';
    $columns['last_login_ip'] = '登录IP';
    unset($columns['name']);
    return $columns;
}
add_action('manage_users_custom_column','show_user_additional_column_content',10,3);
function show_user_additional_column_content($value,$column_name,$user_id){
    $user = get_userdata($user_id);
    if('user_nickname'==$column_name) return $user->nickname;
    if('user_url'==$column_name) return '<a href="'.$user->user_url.'" target="_blank">'.$user->user_url.'</a>';
    if('reg_time'==$column_name) return get_date_from_gmt($user->user_registered);
    if('last_login'==$column_name&&$user->last_login) return get_user_meta($user->ID,'last_login',ture);
    if('last_login_ip'==$column_name) return get_user_meta($user->ID,'last_login_ip',ture);
    return $value;
}
add_filter("manage_users_sortable_columns",'cmhello_users_sortable_columns');
function cmhello_users_sortable_columns($sortable_columns){
    $sortable_columns['reg_time'] = 'reg_time';
    return $sortable_columns;
}
add_action( 'pre_user_query','cmhello_users_search_order');
function cmhello_users_search_order($obj){
    if(!isset($_REQUEST['orderby'])||$_REQUEST['orderby']=='reg_time'){
        if(!in_array($_REQUEST['order'],array('asc','desc'))) $_REQUEST['order'] = 'desc';
        $obj->query_orderby = "ORDER BY user_registered ".$_REQUEST['order']."";
    }
}
//Enable comments <img>
function sig_allowed_html_tags_in_comments(){
   define('CUSTOM_TAGS',true);
   global $allowedtags;
   $allowedtags = array(
      'img'=> array(
         'alt' => true,
         'class' => true,
         'height'=> true,
         'src' => true,
         'width' => true,
      ),
   );
}
add_action('init','sig_allowed_html_tags_in_comments',10);
//Hex2rgb
function hex2rgb($hexColor){
    $color=str_replace('#','',$hexColor);
    if(strlen($color)>3){
        $rgb=hexdec(substr($color,0,2)).','.hexdec(substr($color,2,2)).','.hexdec(substr($color,4,2));
    }else{
        $color=str_replace('#','',$hexColor);
        $r=substr($color,0,1).substr($color,0,1);
        $g=substr($color,1,1).substr($color,1,1);
        $b=substr($color,2,1).substr($color,2,1);
        $rgb=hexdec($r).','.hexdec($g).','.hexdec($b);
    }
    return $rgb;
}
//Sitemap - https://mkblog.cn
function kratos_get_xml_sitemap(){
    ob_start();
    echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.baidu.com/schemas/sitemap-mobile/1/">
    <!-- generated-on=<?php echo get_lastpostdate('blog'); ?> -->
    <url>
        <loc><?php echo get_home_url(); ?></loc>
        <lastmod><?php echo gmdate('Y-m-d\TH:i:s+00:00',strtotime(get_lastpostmodified(GMT))); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url><?php
$posts = get_posts('numberposts=-1&orderby=post_date&order=DESC');
foreach($posts as $post): ?>
    <url>
        <loc><?php echo get_permalink($post->ID); ?></loc>
        <lastmod><?php echo str_replace(" ","T",get_post($post->ID)->post_modified); ?>+00:00</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url><?php 
endforeach;
$pages = get_pages('numberposts=-1&orderby=post_date&order=DESC');
foreach($pages as $page): ?>
    <url>
        <loc><?php echo get_page_link($page->ID); ?></loc>
        <lastmod><?php echo str_replace(" ","T",get_page($page->ID)->post_modified); ?>+00:00</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url><?php 
endforeach;
$categorys = get_terms('category','orderby=name&hide_empty=0');
foreach($categorys as $category): ?>
    <url>
        <loc><?php echo get_term_link($category,$category->slug); ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url><?php 
endforeach;
$tags = get_terms('post_tag','orderby=name&hide_empty=0');
foreach ($tags as $tag) : 
?>
    <url>
        <loc><?php echo get_term_link($tag, $tag->slug); ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>
<?php endforeach; ?>
</urlset><?php
    $sitemap = ob_get_contents();
    ob_clean();
    return $sitemap;
}
function kratos_get_html_sitemap(){
    ob_start(); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    <meta name="author" content="mengkun">
    <meta name="generator" content="KodCloud">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp">
    <meta name="description" content="<?php bloginfo('name'); ?>站点地图">
    <meta name="keywords" content="<?php bloginfo('name'); ?>,站点地图,sitemap">
    <title><?php bloginfo('name'); ?> | 站点地图</title>
    <link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
    <link rel="alternate" type="application/atom+xml" title="<?php bloginfo('name'); ?> Atom Feed" href="<?php bloginfo('atom_url'); ?>" />
    <style>
    *{margin:0;padding:0;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:Microsoft Yahei,"微软雅黑","Helvetica Neue",Helvetica,Hiragino Sans GB,WenQuanYi Micro Hei,sans-serif}
    html,body{width:100%;height:100%}
    a{text-decoration:none;color:#333;-webkit-transition:.3s ease all;-moz-transition:.3s ease all;-o-transition:.3s ease all;transition:.3s ease all}
    a:focus{outline:none}
    .sitemap-lists a{padding:8px 5px;border-radius:5px}
    .sitemap-lists a:hover{background:#eee}
    img{border:none}
    li{list-style:none}
    .clear-fix{zoom:1}
    .clear-fix:before,.clear-fix:after{display:table;line-height:0;content:""}
    .clear-fix:after{clear:both}
    .hidden{display:none}
    .container{max-width:900px;margin:0 auto;position:relative;padding:5px}
    .page-title{font-weight:600;font-size:30px;text-align:center;padding:40px;position:relative}
    .page-title:after{content:"";border-bottom:3px #bdbdbd solid;position:absolute;left:50%;top:50%;padding-top:60px;transform:translate(-50%,-50%);width:60px;z-index:-1}
    .section-title{font-weight:500;font-size:16px;position:relative;margin:15px 0 10px;color:#fff;background:#565555;display:inline-block;padding:5px 8px;border-radius:5px}
    .post-lists li{padding:4px 0}
    .post-lists li>a{display:block}
    .category-lists li>a,.tag-lists li>a{display:inline-block;float:left;margin-right:4px;margin-bottom:4px}
    .page-footer{text-align:center;padding:10px;font-size:14px;color:#c7c7c7}
    </style>
</head>
<body>
<div class="container">
    <h1 class="page-title"><a href="<?php bloginfo('url'); ?>" target="_blank">SiteMap</a></h1><?php
    $posts = get_posts('numberposts=-1&orderby=post_date&order=DESC');
    if(count($posts)): ?>
    <h2 class="section-title">文章 / Article</h2>
    <ul class="sitemap-lists post-lists clear-fix">
        <?php foreach($posts as $post): ?>
        <li><a href="<?php echo get_permalink($post->ID); ?>" title="<?php echo $post->post_title; ?>" target="_blank"><?php echo $post->post_title; ?></a></li>
        <?php endforeach; ?>
    </ul><?php
    endif;
    $pages = get_pages('numberposts=-1&orderby=post_date&order=DESC');
    if(count($pages)): ?>
    <h2 class="section-title">页面 / Page</h2>
    <ul class="sitemap-lists post-lists clear-fix">
        <?php foreach($pages as $page): ?>
        <li><a href="<?php echo get_page_link($page->ID); ?>" title="<?php echo $page->post_title; ?>" target="_blank"><?php echo $page->post_title; ?></a></li>
        <?php endforeach; ?>
    </ul><?php
    endif;
    $categorys = get_terms('category','orderby=name&hide_empty=0');
    if(count($categorys)): ?>
    <h2 class="section-title">分类 / Category</h2>
    <ul class="sitemap-lists category-lists clear-fix">
        <?php foreach ($categorys as $category) : ?>
        <li><a href="<?php echo get_term_link($category,$category->slug); ?>" title="<?php echo $category->name; ?>" target="_blank"><?php echo $category->name; ?></a></li>
        <?php endforeach; ?>
    </ul><?php
    endif;
    $tags = get_terms('post_tag','orderby=name&hide_empty=0');
    if(count($tags)): ?>
    <h2 class="section-title">标签 / Tag</h2>
    <ul class="sitemap-lists tag-lists clear-fix">
        <?php foreach($tags as $tag): ?>
        <li><a href="<?php echo get_term_link($tag,$tag->slug); ?>" title="<?php echo $tag->name; ?>" target="_blank"><?php echo $tag->name; ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div><!-- .container -->
<footer class="page-footer">
    最后更新于 <?php echo get_lastpostdate('blog'); ?>
    <!-- 本页基于 mk-sitemap 插件 - https://mkblog.cn/ -->
</footer>
</body>
</html><?php
    $sitemap = ob_get_contents();
    ob_clean();
    return $sitemap;
}
function kratos_sitemap_refresh(){
    $sitemap_xml = kratos_get_xml_sitemap();
    $sitemap_html = kratos_get_html_sitemap();
    file_put_contents(ABSPATH.'sitemap.xml',$sitemap_xml);
    file_put_contents(ABSPATH.'sitemap.html',$sitemap_html);
}

if(kratos_option('sitemap')&&defined('ABSPATH')){
    add_action('publish_post','kratos_sitemap_refresh');
    add_action('save_post','kratos_sitemap_refresh');
    add_action('edit_post','kratos_sitemap_refresh');
    add_action('delete_post','kratos_sitemap_refresh');
}
//New window-comment author link
function comment_author_link_window(){
    global $comment;
    $url = get_comment_author_url();
    $author = get_comment_author();
    if(empty($url)||'http://'==$url||'https://'==$url)
        $return = $author;
    else
        $return = "<a href='".$url."' target='_blank'>".$author."</a>"; 
    return $return;
}
add_filter('get_comment_author_link','comment_author_link_window');
//Notice
function kratos_admin_notice(){
    $noticeinfo = wp_remote_retrieve_body(wp_remote_get('https://www.fczbl.vip/api/kratos_notice.txt'));
    if(!is_wp_error($noticeinfo)&&$noticeinfo){ ?>
    <style type="text/css">.about-description a{text-decoration:none}</style>
    <div class="notice notice-info">
        <p class="about-description"><?php echo $noticeinfo; ?></p>
    </div><?php
    }
}
add_action('welcome_panel','kratos_admin_notice');