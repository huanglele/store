<?php
/**
 * Author: huanglele
 * Date: 2016/1/28
 * Time: 17:49
 * Description:
 */

namespace Admin\Controller;
use Admin\Controller;

class UserController extends CommonController
{

    public function _initialize(){
        parent::_initialize();
        $this->checkRole(1);
    }

    /**
     * 列出所有的微信用户
     */
    public function index(){
        $map = array();
        $uid = I('get.uid');
        if($uid){
            $map['uid'] = $uid;
        }
        $this->assign('uid',$uid);

        $name = I('get.name');
        if($name){
            $map['nickname'] = array('like','%'.$name.'%');
        }
        $this->assign('name',$name);

        $subscribe = I('get.subscribe',-1,'number_int');
        if($subscribe>=0){
            $map['subscribe'] = $subscribe;
        }
        $this->assign('subscribe',$subscribe);

        $M = M('user');
        $order = 'uid desc';
        $this->getData($M,$map,$order);
        $this->assign('Status',C('Subscribe'));

        $this->display('index');
    }

    /**
     * 用户的详细信息
     */
    public function userInfo(){
        $uid = I('get.uid',0,'number_int');
        $info = M('user')->find($uid);
        if(!$info){$this->error('用户不存在',U('index'));}
        $this->assign('info',$info);
        $this->assign('Status',C('Subscribe'));
        $this->display('userInfo');
    }

    /**
     * 更新用户的信息
     */
    public function updateUser(){
        if(isset($_POST['submit'])){
            $uid = I('post.uid');
            $rate = I('post.rate',20,'number_int');
            $map['uid'] = $uid;
            $map['rate'] = $rate;
            if(M('user')->save($map)){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }

    /**
     * 添加推手自定义财务
     */
    public function umoney(){
        $status = I('post.status',0,'number_int');
        $amount = I('post.amount',0,'number_int');
        $uid = I('post.uid',0,'number_int');
        $note = I('post.note','');
        if(!in_array($status,array(4,5)) || $amount==0 )  $this->error('参数错误');

        //扣钱 添加财务记录
        $User = M('User');
        $User->startTrans();

        if($status==5){     //扣除
            $r1 = $User->where('uid='.$uid)->setDec('money',$amount);
        }else if($status==4){     //添加
            $r1 = $User->where('uid='.$uid)->setInc('money',$amount);
        }

        $da2['time'] = time();
        $da2['uid'] = $uid;
        $da2['note'] = $note;
        $da2['money'] = $amount;
        $da2['type'] = $status;
        $r2 = M("usermoney")->add($da2);

        if($r1 && $r2){
            $User->commit();
            $this->success('添加成功');
        }else{
            $User->rollback();
            $this->error('添加失败');
        }
    }

    /**
     * 列出所有的商家用户
     */
    public function shang(){

        $map = array();
        $map['role'] = 2;
        $aid = I('get.aid');
        $this->assign('aid',$aid);
        if($aid){
            $map['aid'] = $aid;
        }

        $storename = I('get.storename');
        if($storename){
            $map['storename'] = array('like','%'.$storename.'%');
        }
        $this->assign('storename',$storename);

        $username = I('get.username');
        if($username){
            $map['username'] = array('like','%'.$username.'%');
        }
        $this->assign('username',$username);

        $status = I('get.status',-1,'number_int');
        if($status>=0){
            $map['status'] = $status;
        }
        $this->assign('status',$status);

        $M = M('admin');
        $order = 'aid desc';
        $this->getData($M,$map,$order);
        $this->assign('Status',C('AdminStatus'));

        $this->display('shang');
    }

    public function shangInfo(){
        $id = I('get.id');
        $info = M('admin')->find($id);
        if(!$info) $this->error('用户不存在',U('shang'));
        $this->assign('AdminStatus',C('AdminStatus'));
        $this->assign('info',$info);
        $this->display('shangInfo');
    }

    /**
     * 更新商家状态
     */
    public function update(){
        if(isset($_POST['submit'])){
            $M = M('admin');
            $rate = I('post.rate',0,'number_float');
            if($rate<=0 || $rate>100) $this->error('分成超出了限制');
            $aid = I('post.aid');
            $data['status'] = I('post.status');
            $data['aid'] = $aid;
            $data['rate'] = $rate;
            if($M->save($data)){
                $this->success('更新成功');
            }else{
                $this->error('更新失败');
            }
        }else{
            $this->error('参数错误');
        }

    }


    /**
     * 重置用户密码
     */
    public function pwd(){
        $id = I('get.id','','number_int');
        $map['aid'] = $id;
        $M = M('admin');
        $password = $M->where($map)->getField('password');
        if(!$password) $this->error('用户不存在');
        $newPwd = md5(readConf('adminDefaultPwd'));
        if(($newPwd)==$password){$this->success('密码已经重置');die;}
        if($M->where($map)->setField('password',$newPwd)){
            $this->success('更新成功');
        }else{
            $this->error('修改失败');
        }
    }

    /**
     * 添加推手自定义财务
     */
    public function smoney(){
        $status = I('post.status',0,'number_int');
        $amount = I('post.amount',0,'float');
        $aid = I('post.aid',0,'number_int');
        $note = I('post.note','');
        if(!in_array($status,array(4,3)) || $amount==0 || $aid==0)  $this->error('参数错误');

        //扣钱 添加财务记录
        $User = M('Admin');
        $User->startTrans();

        if($status==3){     //扣除
            $r1 = $User->where('aid='.$aid)->setDec('money',$amount);
        }else if($status==4){     //添加
            $r1 = $User->where('aid='.$aid)->setInc('money',$amount);
        }

        $da2['time'] = time();
        $da2['aid'] = $aid;
        $da2['note'] = $note;
        $da2['money'] = $amount;
        $da2['type'] = $status;
        $r2 = M("adminmoney")->add($da2);

        if($r1 && $r2){
            $User->commit();
            $this->success('添加成功');
        }else{
            $User->rollback();
            $this->error('添加失败');
        }
    }

    //设置受益人ID
    public function setIncomeUser(){
        $uid = I('get.uid',0,'number_int');
        $to = I('get.to');
        $map['uid'] = $uid;
        if($to=='self'){
            $map['income_uid'] = $uid;
        }elseif($to=='up'){
            $map['income_uid'] = array('exp','`invite_uid`');
        }else{
            $this->error('参数错误');
        }
        if(M('user')->save($map)){
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }
    }

}