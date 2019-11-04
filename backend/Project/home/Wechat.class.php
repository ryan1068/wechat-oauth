<?php

namespace App\home;

use \Framework\Library\Process\Tool;
use EasyWeChat\Foundation\Application;


/**
 * 默认首页控制器
 * Class Wechat
 * @package App\Home
 */
class Wechat
{
    /**
     * @var Application
     */
    public $app;

    public function __construct()
    {
        $this->app = $this->getApp();
    }

    /**
     * 获取easywechat实例
     * @return Application
     */
    public function getApp()
    {
        $options = Config('Wechat');
        return new Application($options['wechat']);
    }

    /**
     * 获取鉴权地址
     * @return string
     */
    public function getOauthUrl()
    {
        return $this->app->oauth->redirect()->getTargetUrl();
    }

    /**
     * 获取jssdk
     * @return array|string
     */
    public function getJssdk()
    {
        $url = get("url","");
        $apiList = get("apiList",null);
        $debug = get("debug",false);
        return $this->app->js->setUrl($url)->config((array)$apiList, $debug, false, false);
    }

    /**
     * 根据前端提供的accesstoken返回用户信息
     * @return array|null
     */
    public function getUser()
    {
        $userInfo = null;
        $code = get("code","");
        if (!empty($code)) {
            $oauth = $this->app->oauth;
            //通过code换取access_token
            $token = $oauth->getAccessToken($code);
            //通过access_token拉取用户信息
            $user = $oauth->user($token);
            //获取用户基本信息，用户未关注的话将不返回用户的基本信息
            $userInfo = $user->getOriginal() ? : $this->app->user->get($user->getId())->toArray();
            //保存用户信息
            if ($user->getId()) {
                $wxuser = Db('wx_user_data')->select(['where' => ['openid' => $user->getId()]])->find();
                if (!$wxuser) {
                    Db('wx_user_data')->insert([
                        'openid' => $user->getId(),
                        'username' => $this->userTextEncode($user->getName()),
                        'icon' => $user->getAvatar(),
                        'icon_operate_count' => 0,
                        'created_at' => time(),
                        'updated_at' => time()
                    ]);
                }
            }
        }
        return $userInfo;
    }

    /**
     * 处理特殊字符
     * @param $str
     * @return mixed|string
     */
	public function userTextEncode($str){
		if (!is_string($str)) return $str;
		if (!$str || $str=='undefined') return '';

		$text = json_encode($str); //暴露出unicode
		$text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function($str) {
			return addslashes($str[0]);
		}, $text); //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。
		return json_decode($text);
	}

	/**
     * 通过openid获取用户信息
     * @return \EasyWeChat\Support\Collection
     */
    public function getUserByOpenId()
    {
        $openid = get("openid");
        return $this->app->user->get($openid);
    }
}