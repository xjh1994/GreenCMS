<?php
/**
 * Created by Green Studio.
 * File: RuleController.class.php
 * User: TianShuo
 * Date: 14-2-20
 * Time: 下午8:34
 */

namespace Weixin\Controller;


class RuleController extends WeixinBaseController
{


    public function index()
    {
        $weixinaction = D('Weixinaction')->select();

        $this->assign('weixinaction', $weixinaction);

        $this->display();

    }

    public function add()
    {

        $this->assign('form_action', U('Weixin/Rule/addHandle'));
        $this->assign('action_name', '添加');

        $this->display();
    }

    public function edit($id)
    {

        $this->assign('action', '编辑规则');
        $this->assign('action_url', U('Weixin/Rule/edit', array('id' => $id)));

        $item = D('Weixinaction')->where(array('wx_action_id' => $id))->find();
        $this->assign('item', $item);
        $this->assign('form_action', U('Weixin/Rule/editHandle', array('id' => $id)));

        $this->assign('action_name', '编辑');
        $this->display('Rule/add');
    }

    public function editHandle($id)
    {
        $data = I('post.');

         $res = D('Weixinaction')->where(array('wx_action_id' => $id))->data($data)->save();

        if ($res) {
            $this->success('编辑成功');
        } else {
            $this->error('编辑失败或者是没有改变');
        }

    }

    public function addHandle()
    {
        $data = I('post.');

        $res = D('Weixinaction')->data($data)->add();

        if ($res) {
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }

    }


    public function del($id)
    {

        $res = D('Weixinaction')->where(array('wx_action_id' => $id))->delete();

        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }


    }

}