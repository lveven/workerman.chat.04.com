<?php
/**
 * Created by IntelliJ IDEA.
 * User: li914
 * Date: 18-8-13
 * Time: 上午10:00
 */

namespace app\chat\controller;


use GatewayClient\Gateway;
use think\Controller;
use think\facade\Request;


class Service extends Controller
{
    protected $success,$fail;
    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->success=config('success');
        $this->fail=config('fail');
    }

    public function bindUid(){
        if (!Request::isAjax()){
            $this->fail['msg']='该请求方式不正确!';
            return $this->fail;
        }
        $data=Request::post();
        if ($data['type']!='bind'){
            $this->fail['msg']='该请求参数不正确!';
            return $this->fail;
        }
        $client_id=$data['client_id'];
        $client_name=nl2br(htmlspecialchars($data['client_name']));
        $room_id=$data['room_id'];
        Gateway::bindUid($client_id,$client_name);
        if (!Gateway::isUidOnline($client_name)){
            $this->fail['msg']='对不起,未绑定成功!';
            return $this->fail;
        }
        Gateway::joinGroup($client_id,$room_id);
        Gateway::sendToGroup($room_id,'{"type":"join","client_name":"'.$client_name.'","time":"'.date('H:i:s').'"}');
        Gateway::setSession($client_id,array('room_id'=>$room_id,'client_name'=>$client_name));
        $client_list=Gateway::getClientSessionsByGroup($room_id);
        $list=array('type'=>'client_list','time'=>date('H:i:s'),'list'=>$client_list);
        try{
            Gateway::sendToGroup($room_id,json_encode($list,true));
        }catch (\Exception $e){
            Gateway::sendToGroup($room_id,json_encode($list,true));
        }
        $this->success['msg']='绑定成功,并且加入房间';
        $this->success['result']=$client_list;
        return $this->success;
    }
    public function sendGroupMsg(){
        if (!Request::isAjax()){
            $this->fail['msg']='该请求方式不正确!';
            return $this->fail;
        }
        $data=Request::post();
        if ($data['type']!='say'){
            $this->fail['msg']='该请求参数不正确!';
            return $this->fail;
        }
        $to_client_id=$data['to_client_id'];
        $to_client_name=$data['to_client_name'];
        $form_client_id=$data['form_client_id'];
        $form_client_name=$data['form_client_name'];
        $content=$data['content'];
        $room_id=$data['room_id'];
        $form_client_header=$data['form_client_header'];
        $time=$data['time'];
        $msg='{"type":"say","form_client_id":"'.$form_client_id.'","form_client_name":"' .$form_client_name.'","form_client_header":"'.$form_client_header.
            '","content":"'.$content.'","to_client_id":"'.$to_client_id.'","to_client_name":"'.$to_client_name.'","time":"'.$time.'"}';
        try{
            Gateway::sendToGroup($room_id,$msg,array($form_client_id));
        }catch (\Exception $e){
            var_dump($e);
        }
        $this->success['result']=$data;
        return $this->success;
    }
    public function sendPrivateMsg(){
        if (!Request::isAjax()){
            $this->fail['msg']='该请求方式不正确!';
            return $this->fail;
        }
        $data=Request::post();
        if ($data['type']!='say'){
            $this->fail['msg']='该请求参数不正确!';
            return $this->fail;
        }
        $to_client_id=$data['to_client_id'];
        $to_client_name=$data['to_client_name'];
        $form_client_id=$data['form_client_id'];
        $form_client_name=$data['form_client_name'];
        $form_client_header=$data['form_client_header'];
        $content=$data['content'];
        $room_id=$data['room_id'];
        $time=$data['time'];
        $msg='{"type":"say","content":"'.$content.'","form_client_name":"'.$form_client_name.'","form_client_id":"' .$form_client_id.'","form_client_header":"'.$form_client_header.
            '","to_client_id":"'.$to_client_id.'","to_client_name":"'.$to_client_name.'","time":"'.$time.'"}';
        Gateway::sendToUid($to_client_name,$msg);
        $this->success['result']=$data;
        return $this->success;
    }
    public function leaveGroupMsg(){
        if (!Request::isAjax()){
            $this->fail['msg']='该请求方式不正确!';
            return $this->fail;
        }
        $data=Request::post();
        if ($data['type']!='leave'){
            $this->fail['msg']='该请求参数不正确!';
            return $this->fail;
        }
        $client_name=$data['client_name'];
        $time=$data['time'];
        $room_id=$data['room_id'];
        $msg='{"type":"leave","client_name":"'.$client_name.'","time":"'.$time.'"}';
        Gateway::sendToGroup($room_id,$msg);
        $this->success['result']=$data;
        return $this->success;
    }
}