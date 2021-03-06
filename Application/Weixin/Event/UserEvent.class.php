<?php
/**
 * Created by GreenStudio GCS Dev Team.
 * File: UserEvent.class.php
 * User: Timothy Zhang
 * Date: 14-2-20
 * Time: 下午10:33
 */

namespace Weixin\Event;


use Weixin\Controller\WeixinCoreController;
use Weixin\Util\UserManagemant;

/**
 * Class UserEvent
 * @package Weixin\Event
 */
class UserEvent extends WeixinCoreController
{

    /**
     *
     */
    public function renew()
    {
        $UserManagemant=new UserManagemant();

        $res =$UserManagemant->getFansList();

       // $res = $this->getUserList();

        foreach ($res["data"]["openid"] as $openid) {
            $ifuser = D('Weixinuser')->where(array('openid' => $openid))->find();

            if ($ifuser) {
                $data = $this->getUserDetail($openid);
                unset($data['openid']);
                D('Weixinuser')->where(array('openid' => $openid))->data($data)->save();
            }
        }

    }

    /**
     *
     */
    public function update()
    {

        $UserManagemant=new UserManagemant();

        $res =$UserManagemant->getFansList();

        foreach ($res["data"]["openid"] as $openid) {
            $ifuser = D('Weixinuser')->where(array('openid' => $openid))->find();

            if (!$ifuser) {
                $data =$UserManagemant->getUserInfo($openid);
                unset($data['remark']);
                D('Weixinuser')->data($data)->add();
            }
        }
    }

    /**
     * @return array
     */
    public function getUserList()
    {
        $user_list = $this->getFirstList();

        static $user_ids = array();
        if ($user_list['count'] == $user_list['total']) {
            //不足10000人
            $user_ids = $user_list['data']['openid'];
        } else {
            $user_id_2 = $this->getNextUser($user_list['next_openid']);
            $user_ids = array_merge($user_ids, $user_id_2['data']['openid']);
        }


        return $user_ids;
    }

    /**
     * @param $next_openid
     * @return array
     */
    public function getNextUser($next_openid)
    {
        $ACCESS_TOKEN = $this->getAccess();
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=$ACCESS_TOKEN&next_openid=$next_openid";

        $user_list = file_get_contents($url);
        $user_list = json_decode($user_list, true);


        static $user_ids = array();
        if ($user_list['count'] != $user_list['total']) {
            //不足10000人，最后一组
            $user_ids = $user_list['data']['openid'];
        } else {
            $user_id_2 = $this->getNextUser($user_list['next_openid']);
            $user_ids = array_merge($user_ids, $user_id_2['data']['openid']);
        }

        return $user_ids;
    }

    //todo 处理关注量10000以上
    /**
     * @return bool|mixed
     */
    public function getFirstList()
    {
        $ACCESS_TOKEN = $this->getAccess();


        if($ACCESS_TOKEN==false ) return false;

        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=$ACCESS_TOKEN";

        $user_list = file_get_contents($url);
        $user_list = json_decode($user_list, true);
        return $user_list;
    }

    /**
     * @param $openid
     * @return mixed
     */
    public function getUserDetail($openid)
    {
        $ACCESS_TOKEN = $this->getAccess();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$ACCESS_TOKEN&openid=$openid&lang=zh_CN";
        $json = file_get_contents($url);
        $res = json_decode($json, true);

        return $res;
    }

    /**
     * {
    "touser":"OPENID",
    "msgtype":"text",
    "text":
    {
    "content":"Hello World"
    }
    }
     */
    public function sendMessage($openid, $content = '', $msgtype = 'text')
    {
        $ACCESS_TOKEN = $this->getAccess();

        if ($msgtype == 'text') {
            $data['touser'] = $openid;
            $data['msgtype'] = "text";
            $data['text']["content"] = urlencode($content);

            $json = urldecode(json_encode($data));

            $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $ACCESS_TOKEN;
            $res = json_decode(simple_post($url, $json), true);

        }


        $Weixinsend = D('Weixinsend');

        $send['openid'] = $openid;
        $send['type'] = $msgtype;
        $send['content'] = $content;
        //echo date('Y-m-d h:i:s a') ;die();
        $send['CreateTime'] = current_timestamp();

        $Weixinsend->data($send)->add();


        return $res;


    }

}