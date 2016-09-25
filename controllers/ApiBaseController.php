<?php
namespace app\controllers;

use yii;
use yii\web\Controller;

class ApiBaseController extends Controller{

    /**
     * 增加token验证，IP控制
     * @param yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        return true;
        $ip=$_SERVER["REMOTE_ADDR"];
        if(!($ip=='112.64.233.130'||strpos($ip,'192.168')===0||$ip=='127.0.0.1'||$ip=='::1'||$ip=='localhost')){
            @header("http/1.1 404 not found");
            @header("status: 404 not found");
            exit("404 page not found！");
        }
        $token = Yii::$app->request->post('token');
//        if (!parent::beforeAction($action)) {
//            return false;
//        }
        if($token != yii::$app->params['tidy_token']){
            $this->error("缺少参数，请重新发起请求");
            //   return false;
        }
        return true;
    }

    /**
     * 成功提示
     * @param string $msg
     */
    protected function success($msg="操作成功！"){
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode(array("status"=>"true","info"=>$msg)));
    }

    /**
     * 失败提示
     * @param string $msg
     */
    protected function error($msg="操作失败！"){
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode(array("status"=>"false","info"=>$msg)));
    }

    /**
     * 获取请求来的参数
     * @param $param
     * @return array|mixed
     */
    protected function getParam($param){
        $param_post = Yii::$app->request->post($param);
        if(!empty($param_post)){
            return $param_post;
        }else{
            $param_get = Yii::$app->request->get($param);
            return $param_get;
        }
    }

    protected function checkParamters($params){
        foreach ($params as $item){
            if($item==null||$item==""){
                $this->error($item."参数不能为空");
                exit();
            }
        }
        return true;
    }
}
