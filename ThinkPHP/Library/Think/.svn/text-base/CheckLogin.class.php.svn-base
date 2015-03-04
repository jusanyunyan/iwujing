<?php
// +----------------------------------------------------------------------
// | Baojao [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.baojia.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 纪道星 <xiaoxing_yy@126.com> <http://www.baojia.com>
// +----------------------------------------------------------------------
namespace Think;

class CheckLogin extends Controller{	
    public function _initialize(){
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置
        // 下面这个判断是为了解决 uploadify 在 Firfox 中上传图片时没有session信息的问题
        // 此处手动把session信息传过来，在判断前，否则可能存在页面被转发而使图片上传失败
        // 根本原因： uploadify 利用 flash 上传，而falsh是无法记录 session 信息的
        if( I("post.sessionid")!='' ){
            session('[pause]');
            session_id(I("post.sessionid"));   
            session('[start]');
        }
        if ( !is_login() ) {
            header('location:/account/login/?referral='.get_current_url());
        }
    }
}
?>
