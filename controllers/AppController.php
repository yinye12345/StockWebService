<?php

namespace app\controllers;

use app\models\TidyGreenType;
use app\models\TidyLineStation;
use app\models\TidyPlantCategoryStock;
use app\models\TidyPlantCategoryStockDetail;
use app\models\TidyPlantDamage;
use app\models\TidyPlantDamageDetail;
use app\models\TidyPlantDeliverFormDetail;
use app\models\TidyPlantHandoverForm;
use app\models\TidyPlantHandoverFormDetail;
use app\models\TidyUser;
use yii\base\Exception;
use yii\web\Controller;
use Yii;

class AppController extends ApiBaseController
{

    public $apiNameList=[
        "index"=>"index",
        "CheckAccess"=>"CheckAccess",
        "ModifyPwd"=>"ModifyPwd",
        "CheckUpdateApp" => "CheckUpdateApp",
    ];

    /**
     *******************************
     *                             *
     *                             *
     *                             *
     * 绿植服务接口 文档 v1.0.0    *
     *                             *
     *                             *
     *                             *
     *******************************
     */
    public function actionIndex(){
        exit("Hello World!");
    }

    /**
     * @param $username 用户名
     * @param $passwd 密码
     * @param string $version 版本号
     */
    public function actionCheckAccess()
    {
        $userName = Yii::$app->request->post("username",'');
        $password = Yii::$app->request->post("passwd",'');
        $flagCLogin = TidyUser::checkLogin($userName,$password);
        if($flagCLogin==false){
            $this->error("用户名密码不正确！");
        }else{
            $this->success($flagCLogin);
        }
    }

    /**
     * @param $originPwd
     * @param $newPwd
     * @param $userId
     * @return 返回执行结果
     */
    public function actionModifyPwd(){
        $originPwd = Yii::$app->request->post("originPwd",'');
        $newPwd = Yii::$app->request->post("newPwd",'');
        $userId = Yii::$app->request->post("userId",'');
        $checkRes=TidyUser::pwdCheck($newPwd);

        if($checkRes!==true){
            $this->error($checkRes);
        }
        $res=TidyUser::modifyPwd($userId,$newPwd,$originPwd);
        if($res===false){
            $this->error("密码修改失败！");
        }else if($res===true){
            $this->success("密码修改成功！");
        }else{
            $this->error($res);
        }
    }

    /**
     * 检测当前App是否需要更新
     * @param $cCVersion 当前客户端的版本号
     * @return 如果需要更新则返回一个下载链接
     */
    public function actionCheckUpdateApp(){
        $cVersion = Yii::$app->params["updateAppVersion"];
        $cCVersion = Yii::$app->request->post("cCVersion",$cVersion);
        if($cCVersion!=$cVersion){
            $updateAppUrl = Yii::$app->params["updateAppUrl"];
            $this->success(["url" => $updateAppUrl]);
        }else{
            $this->success("当前为最新版本！");
        }
    }

    /**
     * 获取库存接口
     * @param $cityId 城市编号
     * @param $userId 用户编号
     * @param $date 日期
     */
    public function actionGetPlantStock(){
        $cityId = Yii::$app->request->post("cityId");
        $userId = Yii::$app->request->post("userId");
        $date = Yii::$app->request->post("date");

        if (empty($date)){
            $date = date("Y-m-d");
        }

        $sqlStock = "SELECT * FROM tidy_plant_category_stock WHERE city_id = :city_id AND record_userid = :record_userid AND DATE_FORMAT(create_datetime, '%Y-%m-%d') = :date ORDER BY create_datetime DESC LIMIT 1";
        $stock = Yii::$app->db->createCommand($sqlStock)->bindValues(array(":city_id"=>$cityId, ":record_userid"=>$userId, ":date"=>$date))->queryOne();
        if (empty($stock)){
            $this->error("暂无数据!");
        }

        $sqlStockDetail = "SELECT * FROM tidy_plant_category_stock_detail WHERE stock_id = :stock_id";
        $stockDetail = Yii::$app->db->createCommand($sqlStockDetail)->bindValues(array(":stock_id"=>$stock['id']))->queryAll();
        $stockInfo = array();
        $tempCategoryArray = array();
        //先筛选一级品类
        foreach ($stockDetail as $item){
            if (!in_array($item['pid'], $tempCategoryArray)){
                $tempArray = array();
                $tempArray['pid'] = $item['pid'];
                $tempArray['pname'] = $item['pname'];
                $stockInfo[] = $tempArray;
                $tempCategoryArray[] = $item['pid'];
            }
        }
        foreach ($stockInfo as &$value) {
            foreach ($stockDetail as $vl) {
                if ($value['pid'] == $vl['pid']) {
                    $tempArray = array();
                    $prickArray = array();//扎数
                    $branchArray = array();//枝数
                    $bindArray = array();//束数
                    $tempArray['cid'] = $vl['cid'];
                    $tempArray['cname'] = $vl['cname'];
                    $prickArray['unit'] = "扎数";
                    $prickArray['num'] = $vl['prick_num'];
                    $branchArray['unit'] = "枝数";
                    $branchArray['num'] = $vl['branch_num'];
                    $bindArray['unit'] = "束数";
                    $bindArray['num'] = $vl['bind_num'];
                    $tempArray['num'][] = $prickArray;
                    $tempArray['num'][] = $branchArray;
                    $tempArray['num'][] = $bindArray;
                    $value['detail'][] = $tempArray;
                }
            }
        }
        $stock['detail'] = $stockInfo;
        $this->success($stock);
    }

    /**
     * 生成批次编号
     * @param $date 需求日期
     */
    public function actionGenerateBatchId(){
        $date = $this->getParam("date");
        $cityId = $this->getParam("cityId");
        $this->checkParamters(array($date, $cityId));
        $sqlBatchInfo = "SELECT batch_id, start_time, end_time FROM tidy_plant_time_batch WHERE city_id = :city_id AND DATE_FORMAT(date, '%Y-%m-%d') = :date ORDER BY create_datetime DESC";
        $batchInfo = Yii::$app->db->createCommand($sqlBatchInfo)->bindValues(array(":city_id"=>$cityId, ":date"=>$date))->queryOne();
        $num = 20;
        $dateArray = array();
        $batchId = strtotime($date);
        if (empty($batchInfo)){
            $sql = "SELECT end_time FROM tidy_plant_time_batch WHERE city_id = :city_id ORDER BY create_datetime DESC LIMIT 1";
            $endTime = Yii::$app->db->createCommand($sql)->bindValues(array(":city_id"=>$cityId))->queryScalar();
            if (empty($endTime)){
                $startTime = $date;
            } else {
                $startTime = date("Y-m-d", strtotime($endTime) + 3600 * 24);
            }

        } else {
            $startTime = date("Y-m-d", strtotime($batchInfo['end_time']) + 3600 * 24);
        }
        for ($i = 0; $i < $num; $i++){
            $dateArray[] = date("Y-m-d", (strtotime($startTime)) + 3600 * 24 * $i);
        }
        $this->success(array("batchId"=>$batchId, "dateArray"=>$dateArray));
    }

    /**
     * 创建收货单
     * @param $cityId 城市编号
     * @param $userId 用户编号
     * @param $requireDate 需求日期
     * @param $batchId 批次编号
     * @param $startDate 开始日期
     * @param $endDate 结束日期
     */
    public function actionCreateDeliverForm(){
        $cityId = $this->getParam("cityId");
        $userId = $this->getParam("userId");
        $requireDate = $this->getParam("requireDate");
        $batchId = $this->getParam("batchId");
        $receiveNum = $this->getParam("receiveNum");
        $startDate = $this->getParam("startDate");
        $endDate = $this->getParam("endDate");

        $this->checkParamters(array($cityId, $userId, $requireDate, $batchId, $receiveNum, $startDate, $endDate));

        $createDatetime = date("Y-m-d H:i:s");
        $tsc = Yii::$app->db->beginTransaction();
        try{
            $sqlInsert = "INSERT INTO tidy_plant_time_batch (date, batch_id, city_id, start_time, end_time, create_datetime) VALUES (:date, :batch_id, :city_id, :start_time, :end_time, :create_datetime)";
            Yii::$app->db->createCommand($sqlInsert)->bindValues(array(":date"=>$requireDate, ":batch_id"=>$batchId, ":city_id"=>$cityId, ":start_time"=>$startDate, ":end_time"=>$endDate, ":create_datetime"=>$createDatetime))->execute();
            $sqlSelect = "SELECT batch_id, batch_suffix_id FROM tidy_plant_deliver_form WHERE batch_id = :batch_id AND city_id = :city_id ORDER BY create_datetime DESC LIMIT 1";
            $select = Yii::$app->db->createCommand($sqlSelect)->bindValues(array(":batch_id"=>$batchId, ":city_id"=>$cityId))->queryOne();
            $suffixId = 0;
            if (!empty($select)){
                $suffixId = $select['batch_suffix_id'] + 1;
            }

            $sqlInsertPickup = "INSERT INTO tidy_plant_deliver_form (batch_id, batch_suffix_id, city_id, receive_userid, receive_num, create_datetime) VALUES (:batch_id, :batch_suffix_id, :city_id, :receive_userid, :receive_num, :create_datetime)";
            Yii::$app->db->createCommand($sqlInsertPickup)->bindValues(array(":batch_id"=>$batchId, ":batch_suffix_id"=>$suffixId, ":city_id"=>$cityId, ":receive_userid"=>$userId, ":receive_num"=>$receiveNum, ":create_datetime"=>$createDatetime))->execute();

            $tsc->commit();
            $this->success("创建收货单成功!");
        } catch (Exception $e){
            $tsc->rollBack();
            $this->error("创建收货单失败!");
        }
    }

    /**
     * 检测是否需要录入剩余
     */
    public function actionCheckRestStatus(){
        $userId = $this->getParam("userId");

    }

    /**
     * 获取收货单
     */
    public function actionGetDeliverForm(){
        $userId = $this->getParam("userId");
        $status = $this->getParam("status");

        $this->checkParamters(array($userId));

        if (empty($status)){
            $status = 0;
        }

        $sqlFormInfo = "SELECT id, batch_id, batch_suffix_id, deliver_datetime, deliver_num, deliver_userid, deliver_phone, rest_flag FROM tidy_plant_deliver_form WHERE receive_userid = :receive_userid AND status = :status";
        $formInfo = Yii::$app->db->createCommand($sqlFormInfo)->bindValues(array(":receive_userid"=>$userId, ":status"=>$status))->queryAll();

        if (empty($formInfo)){
            $this->error("暂无收货单!");
        }
        $this->success($formInfo);
    }

    /**
     * 获取绿植品类
     * @param $type 类型 0、获取所有品类,1、获取单品,2、获取混搭按束为单位的花
     */
    public function actionGetPlantCategory(){
        $type = $this->getParam("type");

        if (empty($type)){
            $type = 0;
        }

        if ($type == 0){
            $plantCategory = TidyGreenType::find()->asArray()->all();
        } else {
            $plantCategory = TidyGreenType::find()->where(["goods_type"=>$type])->asArray()->all();
        }

        if (empty($plantCategory)){
            $this->error("暂无数据!");
        }
        //先筛选一级品类
        $category = array();
        foreach ($plantCategory as $item){
            if ($item['pid'] == 0){
                $category[] = $item;
            }
        }
        //筛选耳机品类
        foreach ($category as &$value){
            foreach ($plantCategory as $vl){
                if($value['id'] == $vl['pid']){
                    $vl['pname'] = $value['name'];
                    $value['detail'][] = $vl;
                }
            }
        }
        $this->success($category);
    }

    /**
     * 录入收货单详情
     * @param $userId 用户编号
     * @param $cityId 城市编号
     * @param $batchId 批次编号
     * @param $suffixId 批次后缀
     * @param $formId 收货单编号
     * @param $formDetail 收货单详细信息
     */
    public function actionEnteringDeliverFormInfo(){
        $userId = $this->getParam("userId");
        $cityId = $this->getParam("cityId");
        $batchId = $this->getParam("batchId");
        $suffixId = $this->getParam("suffixId");
        $formId = $this->getParam("formId");
        $formDetail = $this->getParam("formDetail");

        $this->checkParamters(array($userId, $cityId, $batchId, $formId, $formDetail));

        if (empty($suffixId)){
            $suffixId = 0;
        }

        $dateTime = date("Y-m-d H:i:s");

        $formDetail = json_decode($formDetail, true);

        $tsc = Yii::$app->db->beginTransaction();
        try {
            $sqlUpdate = "UPDATE tidy_plant_deliver_form SET receive_datetime = :receive_datetime, status = :status WHERE id = :id";
            Yii::$app->db->createCommand($sqlUpdate)->bindValues(array(":receive_datetime"=>$dateTime, ":status"=>1, ":id"=>$formId))->execute();
            $sql = "INSERT INTO tidy_plant_deliver_form_detail (deliver_id, goods_pid, goods_pname, goods_cid, goods_cname, receive_prick_num, receive_branch_num, receive_bind_num, mark, create_datetime) VALUES (:deliver_id, :goods_pid, :goods_pname, :goods_cid, :goods_cname, :receive_prick_num, :receive_branch_num, :receive_bind_num, :mark, :create_datetime)";
            foreach ($formDetail as $item){
                Yii::$app->db->createCommand($sql)->bindValues(array(":deliver_id"=>$formId, ":goods_pid"=>$item['pid'], ":goods_pname"=>$item['pname'], ":goods_cid"=>$item['cid'], ":goods_cname"=>$item['cname'], ":receive_prick_num"=>$item['prickNum'], ":receive_branch_num"=>$item['branchNum'], ":receive_bind_num"=>$item['bindNum'], ":mark"=>$item['mark'], ":create_datetime"=>$dateTime))->execute();
            }

            //检测是否已经创建过可分配单,若已创建过就不再重复创建
            $sqlCheck = "SELECT COUNT(0) FROM tidy_plant_distribute_form WHERE batch_id = :batch_id AND city_id = :city_id";
            $check = Yii::$app->db->createCommand($sqlCheck)->bindValues(array(":batch_id"=>$batchId, ":city_id"=>$cityId))->queryScalar();
            if (empty($check)){
                $sqlInsert = "INSERT INTO tidy_plant_distribute_form (batch_id, city_id, user_id, take_datetime, status) VALUES (:batch_id, :city_id, :user_id, :take_datetime, :status)";
                Yii::$app->db->createCommand($sqlInsert)->bindValues(array(":batch_id"=>$batchId, ":city_id"=>$cityId, ":user_id"=>$userId, ":take_datetime"=>$dateTime, ":status"=>0))->execute();
            }
            $tsc->commit();
            $this->success("录入成功!");
        } catch (Exception $e){
            $tsc->rollBack();
            $this->error("录入失败!");
        }
    }

    /**
     * 获取收货单信息
     * @param $formId 收货单编号
     */
    public function actionGetDeliverFormInfo(){
        $formId = $this->getParam("formId");
        $this->checkParamters(array($formId));

        $sqlFormInfo = "SELECT * FROM tidy_plant_deliver_form_detail WHERE deliver_id = :deliver_id";
        $formInfo = Yii::$app->db->createCommand($sqlFormInfo)->bindValues(array(":deliver_id"=>$formId))->queryAll();

        if (empty($formInfo)){
            $this->error("暂无详情!");
        }

        $returnInfo = array();
        $tempPidInfo = array();
        //先筛选一级品类
        foreach ($formInfo as $item){
            if (!in_array($item['goods_pid'], $tempPidInfo)){
                $tempArray = array();
                $tempArray['pid'] = $item['goods_pid'];
                $tempArray['pname'] = $item['goods_pname'];
                $returnInfo[] = $tempArray;
                $tempPidInfo[] = $item['goods_pid'];
            }
        }

        foreach ($returnInfo as &$value){
            foreach ($formInfo as $vl){
                if ($value['pid'] == $vl['goods_pid']){
                    $tempArray = array();
                    $tempArray['cid'] = $vl['goods_cid'];
                    $tempArray['cname'] = $vl['goods_cname'];
                    $tempArray['prickNum'] = $vl['receive_prick_num'];
                    $tempArray['branchNum'] = $vl['receive_branch_num'];
                    $tempArray['bindNum'] = $vl['receive_bind_num'];
                    $tempArray['mark'] = $vl['mark'];
                    $value['detail'][] = $tempArray;
                }
            }
        }

        $this->success($returnInfo);
    }

    /**
     * 获取分配单
     * @param $userId 用户编号
     * @param $cityId 城市编号
     * @param $status 状态
     */
    public function actionGetDistributeForm(){
        $userId = $this->getParam("userId");
        $cityId = $this->getParam("cityId");
        $status = $this->getParam("status");

        $this->checkParamters(array($userId, $cityId));

        if (empty($status)){
            $status = 0;
        }

        $sqlFormList = "SELECT * FROM tidy_plant_distribute_form WHERE city_id = :city_id AND user_id = :user_id AND status = :status";
        $formList = Yii::$app->db->createCommand($sqlFormList)->bindValues(array(":city_id"=>$cityId, ":user_id"=>$userId, ":status"=>$status))->queryAll();
        if (empty($formList)){
            $this->error("暂无数据!");
        } else {
            $this->success($formList);
        }
    }

    /**
     * 获取交接单批次
     * @param $type 类型:order、florist、market
     * @param $userId 用户编号
     * @param $cityId 城市编号
     * @param $batchId 批次编号
     */
    public function actionGetHandoverForm(){
        $type = $this->getParam("type");
        $userId = $this->getParam("userId");
        $cityId = $this->getParam("cityId");
        $batchId = $this->getParam("batchId");
        $this->checkParamters(array($type, $userId, $cityId, $batchId));

        if ($type == 'order'){

            //获取干线以及对应网点
            $sql = "SELECT t.trans_id, t.trans_name, COUNT(s.station_id) AS station_count, l.user_id, u.nickname FROM tidy_transfers AS t LEFT JOIN tidy_line_station AS s ON (s.tran_id = t.trans_id) LEFT JOIN tidy_line_user AS l ON (l.tran_id = t.trans_id AND l.status = 1) LEFT JOIN tidy_user AS u ON (u.user_id = l.user_id) WHERE t.cancel = 0 AND s.status = 1 AND t.city_id = :city_id GROUP BY t.trans_id";
            $tranStationInfo = Yii::$app->db->createCommand($sql)->bindValues(array(":city_id"=>$cityId))->queryAll();

            $sql = "SELECT * FROM tidy_plant_handover_form WHERE batch_id = :batch_id AND city_id = :city_id AND type = 1";
            $handoverList = Yii::$app->db->createCommand($sql)->bindValues(array(":batch_id"=>$batchId, ":city_id"=>$cityId))->queryAll();
            if (empty($handoverList)){
                foreach ($tranStationInfo as &$item){
                    $item['start_count'] = 0;
                    $item['accept_count'] = 0;
                }
            } else {
                foreach ($tranStationInfo as &$item){
                    $startCount = 0;
                    $acceptCount = 0;
                    foreach ($handoverList as $value){
                        if ($item['trans_id'] == $value['tran_id']){
                            if ($value['status'] > 0) {
                                $startCount++;
                                if ($value['status'] == 4) {
                                    $acceptCount++;
                                }
                            }
                        }
                    }
                    $item['start_count'] = $startCount;
                    $item['accept_count'] = $acceptCount;
                }
            }
            $this->success($tranStationInfo);

        } elseif ($type == 'florist'){
            //获取分配给花艺师的交接单
            $sqlInfo = "SELECT * FROM tidy_plant_handover_form WHERE batch_id = :batch_id AND deliver_id = :deliver_id AND type = 2";
            $info = Yii::$app->db->createCommand($sqlInfo)->bindValues(array(":batch_id"=>$batchId, ":deliver_id"=>$userId))->queryAll();
            if (empty($info)){
                $this->error("暂无数据!");
            } else {
                $this->success($info);
            }
        } elseif ($type == 'market'){
            //获取分配给市场的交接单
            $sqlInfo = "SELECT * FROM tidy_plant_handover_form WHERE batch_id = :batch_id AND deliver_id = :deliver_id AND type = 3";
            $info = Yii::$app->db->createCommand($sqlInfo)->bindValues(array(":batch_id"=>$batchId, ":deliver_id"=>$userId))->queryAll();
            if (empty($info)){
                $this->error("暂无数据!");
            } else {
                $this->success($info);
            }
        }
    }

    /**
     * 获取网店分配单列表
     * @param $tranId 干线编号
     * @param $batchId 批次编号
     */
    public function actionGetStationDistributeForm(){
        $tranId = $this->getParam("tranId");
        $batchId = $this->getParam("batchId");

        $this->checkParamters(array($tranId, $batchId));

        $sqlHandoverForm = "SELECT * FROM tidy_plant_handover_form WHERE tran_userid = :tran_userid AND batch_id = :batch_id";
        $handoverForm = Yii::$app->db->createCommand($sqlHandoverForm)->bindValues(array(":tran_userid"=>$tranId, ":batch_id"=>$batchId))->queryAll();

        if (empty($handoverForm)){
            $this->error("暂无数据!");
        } else {
            $this->success($handoverForm);
        }

    }

    /**
     * 获取分配单详情
     * @param $formId 交接单编号
     */
    public function actionGetDistributeFormDetail(){
        $formId = $this->getParam("formId");

        $this->checkParamters(array($formId));

        $sqlFormDetail = "SELECT * FROM tidy_plant_handover_form_detail WHERE form_id = :form_id";
        $formDetail = Yii::$app->db->createCommand($sqlFormDetail)->bindValues(array(":form_id"=>$formId))->queryAll();

        if (empty($formDetail)){
            $this->error("暂无数据!");
        } else {
            $this->success($formDetail);
        }

    }

    /**
     * 获取干线对应的网店交接单列表
     * @param $batchId 批次编号
     * @param $tranId 干线编号
     */
    public function actionGetLineStationHandoverForm(){
        $batchId = $this->getParam("batchId");
        $tranId = $this->getParam("tranId");

        $this->checkParamters(array($batchId, $tranId));

        //获取某条干线下的网店列表
        $sql = "SELECT s.station_id, i.station_name, e.user_id, u.nickname FROM tidy_line_station AS s LEFT JOIN tidy_service_info AS i ON (i.id = s.station_id) LEFT JOIN tidy_station_emp AS e ON (i.id = e.station_id AND e.is_glder = 1) LEFT JOIN tidy_user AS u ON (u.user_id = e.user_id) WHERE tran_id = :tran_id AND s.status = 1";
        $stationList = Yii::$app->db->createCommand($sql)->bindValues(array(":tran_id"=>$tranId))->queryAll();

        //获取某个批次某条干线下的交接单信息
        $sql = "SELECT id, station_id, station_name, receive_id, receive_name, status, create_datetime, receive_datetime FROM tidy_plant_handover_form WHERE batch_id = :batch_id AND tran_id = :tran_id";
        $handoverList = Yii::$app->db->createCommand($sql)->bindValues(array(":batch_id"=>$batchId, ":tran_id"=>$tranId))->queryAll();

        $returnArray = array();
        if (empty($handoverList)){
            foreach ($stationList as $item){
                $tempArray = array();
                $tempArray['id'] = 0;
                $tempArray['station_id'] = $item['station_id'];
                $tempArray['station_name'] = $item['station_name'];
                $tempArray['receive_id'] = $item['user_id'];
                $tempArray['receive_name'] = $item['nickname'];
                $tempArray['status'] = 0;
                $tempArray['create_datetime'] = '0000-00-00 00:00:00';
                $tempArray['receive_datetime'] = '0000-00-00 00:00:00';
                $returnArray[] = $tempArray;
            }
            $this->success($returnArray);
        } else {
            foreach ($stationList as $item){
                $existFlag = false;
                foreach ($handoverList as $value){
                    if ($item['station_id'] == $value['station_id']){
                        $existFlag = true;
                        break;
                    }
                }
                if (!$existFlag){
                    $tempArray = array();
                    $tempArray['id'] = 0;
                    $tempArray['station_id'] = $item['station_id'];
                    $tempArray['station_name'] = $item['station_name'];
                    $tempArray['receive_id'] = $item['user_id'];
                    $tempArray['receive_name'] = $item['nickname'];
                    $tempArray['status'] = 0;
                    $tempArray['create_datetime'] = '0000-00-00 00:00:00';
                    $tempArray['receive_datetime'] = '0000-00-00 00:00:00';
                    $handoverList[] = $tempArray;
                }
            }
            $this->success($handoverList);
        }
    }

//    /**
//     * 添加交接单接口
//     * @param $distributeId 分配单编号
//     * @param $batchId 批次编号
//     * @param $cityId 城市编号
//     * @param $type 分配交接单的类型
//     * @param $deliverName 分配人名称
//     * @param $deliverId 分配人编号
//     * @param $tranId 干线编号
//     * @param $tranUserId 干线人员编号
//     * @param $tranName 干线人员名称
//     * @param $stationId 网点编号
//     * @param $stationName 网点名称
//     * @param $receiveId 接收人编号
//     * @param $receiveName 接收人名称
//     * @param $deliverDetail 分配详情
//     */
//    public function actionAddHandoverForm(){
//        $distributeId = $this->getParam("distributeId");
//        $batchId = $this->getParam("batchId");
//        $cityId = $this->getParam("cityId");
//        $type = $this->getParam("type");
//        $deliverName = $this->getParam("deliverName");
//        $deliverId = $this->getParam("deliverId");
//        $tranId = $this->getParam("tranId");
//        $tranUserId = $this->getParam("tranUserId");
//        $tranName = $this->getParam("tranName");
//        $stationId = $this->getParam("stationId");
//        $stationName = $this->getParam("stationName");
//        $receiveId = $this->getParam("receiveId");
//        $receiveName = $this->getParam("receiveName");
//        $deliverDetail = $this->getParam("deliverDetail");
//
//        $this->checkParamters(array($distributeId, $batchId, $cityId, $type, $deliverName, $deliverId, $tranId, $tranUserId, $tranName, $stationId, $stationName, $receiveId, $receiveName, $deliverDetail));
//
//        $dateTime = date("Y-m-d H:i:s");
//        $deliverDetail = json_decode($deliverDetail, true);
//
//        $tsc = Yii::$app->db->beginTransaction();
//        try{
//            $tidyPlantHandoverForm = new TidyPlantHandoverForm();
//            $attributes = array(
//                "distributeId"=>$distributeId,
//                "batch_id"=>$batchId,
//                "city_id"=>$cityId,
//                "type"=>$type,
//                "deliver_name"=>$deliverName,
//                "deliver_id"=>$deliverId,
//                "tran_id"=>$tranId,
//                "tran_userid"=>$tranUserId,
//                "tran_name"=>$tranName,
//                "station_id"=>$stationId,
//                "station_name"=>$stationName,
//                "receive_id"=>$receiveId,
//                "receive_name"=>$receiveName,
//                "status"=>1,
//                "create_datetime"=>$dateTime
//            );
//            $tidyPlantHandoverForm->setAttributes($attributes);
//            $tidyPlantHandoverForm->save();
//            $lastInsertId = $tidyPlantHandoverForm->attributes['id'];
//
//            foreach ($deliverDetail as $item){
//                $fitNum = $item['fitNum'];
//                foreach ($item['detail'] as $value){
//                    $tidyPlantHandoverFormDetail = new TidyPlantDeliverFormDetail();
//                    $itemAttributes = array();
//                    $itemAttributes['handover_id'] = $lastInsertId;
//                    $itemAttributes['pid'] = $value['pid'];
//                    $itemAttributes['pname'] = $value['pname'];
//                    $itemAttributes['cid'] = $value['cid'];
//                    $itemAttributes['cname'] = $value['cname'];
//                    $itemAttributes['deliver_prick_num'] = $value['prickNum'];
//                    $itemAttributes['deliver_branch_num'] = $value['branchNum'];
//                    $itemAttributes['deliver_bind_num'] = $value['bindNum'];
//                    $itemAttributes['fit_order_num'] = $fitNum;
//                    $itemAttributes['deliver_mark'] = $value['mark'];
//                    $tidyPlantHandoverFormDetail->setAttributes($itemAttributes);
//                    $tidyPlantHandoverFormDetail->save();
//                }
//            }
//            $tsc->commit();
//            $this->success("分配成功!");
//        } catch (Exception $e){
//            $tsc->rollBack();
//            $this->error("分配失败!");
//        }
//    }

    /**
     * 获取某条干线下的网店列表
     */
    public function actionGetStationList(){
        $tranId = $this->getParam("tranId");
        $this->checkParamters(array($tranId));

        $sql = "SELECT s.station_id AS value, i.station_name AS text, e.user_id, u.nickname FROM tidy_line_station AS s LEFT JOIN tidy_service_info AS i ON (i.id = s.station_id) LEFT JOIN tidy_station_emp AS e ON (e.station_id = s.station_id AND e.is_glder = 1) LEFT JOIN tidy_user AS u ON (e.user_id = u.user_id) WHERE s.status = 1 AND s.tran_id = :tran_id";
        $stationList = Yii::$app->db->createCommand($sql)->bindValues(array(":tran_id"=>$tranId))->queryAll();
        if (empty($stationList)){
            $this->error("暂无网点!");
        } else {
            $this->success($stationList);
        }
    }

    /**
     * 获取花艺师人员列表
     * @param $cityId 城市编号
     * @param $position 职位
     */
    public function actionGetUserList(){
        $cityId = $this->getParam("cityId");
        $position = $this->getParam("position");

        $this->checkParamters(array($cityId, $position));

        if (!in_array($position, array("花艺师", "市场"))){
            $this->error("职位错误!");
        }

        if ($cityId == 35){
            $cityId = 0;
        }

        $userList = TidyUser::getCityPositionUserList($cityId, $position);
        if (empty($userList)){
            $this->error("暂无可选人员!");
        } else {
            $returnUserList = array();
            foreach ($userList as $item){
                $tempArray = array();
                $tempArray['value'] = $item['id'];
                $tempArray['text'] = $item['name'];
                $returnUserList[] = $tempArray;
            }
            $this->success($returnUserList);
        }

    }

    /**
     * 创建交接单接口
     * @param $distributeId 分配单编号
     * @param $batchId 批次编号
     * @param $cityId 城市编号
     * @param $type 分配交接单的类型
     * @param $deliverName 分配人名称
     * @param $deliverId 分配人编号
     * @param $tranId 干线编号
     * @param $tranUserId 干线人员编号
     * @param $tranName 干线人员名称
     * @param $stationId 网点编号
     * @param $stationName 网点名称
     * @param $receiveId 接收人编号
     * @param $receiveName 接收人名称
     */
    public function actionCreateHandoverForm(){
        $distributeId = $this->getParam("distributeId");
        $batchId = $this->getParam("batchId");
        $cityId = $this->getParam("cityId");
        $type = $this->getParam("type");
        $deliverName = $this->getParam("deliverName");
        $deliverId = $this->getParam("deliverId");
        $tranId = $this->getParam("tranId");
        $tranUserId = $this->getParam("tranUserId");
        $tranName = $this->getParam("tranName");
        $stationId = $this->getParam("stationId");
        $stationName = $this->getParam("stationName");
        $receiveId = $this->getParam("receiveId");
        $receiveName = $this->getParam("receiveName");

        $this->checkParamters(array($distributeId, $batchId, $cityId, $type, $deliverName, $deliverId, $receiveId, $receiveName));

        if ($type == 1){
            $this->checkParamters(array($tranId, $tranUserId, $tranName, $stationId, $stationName));
        }

        $dateTime = date("Y-m-d H:i:s");

        switch ($type){
            case 1://创建网点交接单
                $attributes = array(
                    "distribute_id" => $distributeId,
                    "batch_id" => $batchId,
                    "city_id" => $cityId,
                    "type" => $type,
                    "deliver_name" => $deliverName,
                    "deliver_id" => $deliverId,
                    "tran_id" => $tranId,
                    "tran_userid" => $tranUserId,
                    "tran_name" => $tranName,
                    "station_id" => $stationId,
                    "station_name" => $stationName,
                    "receive_id" => $receiveId,
                    "receive_name" => $receiveName,
                    "status" => 0,
                    "create_datetime" => $dateTime
                );
                break;
            case 2://创建花艺师交接单
                $attributes = array(
                    "distributeId" => $distributeId,
                    "batch_id" => $batchId,
                    "city_id" => $cityId,
                    "type" => $type,
                    "deliver_name" => $deliverName,
                    "deliver_id" => $deliverId,
                    "receive_id" => $receiveId,
                    "receive_name" => $receiveName,
                    "status" => 0,
                    "create_datetime" => $dateTime
                );
                break;
            case 3://创建市场交接单
                $attributes = array(
                    "distributeId" => $distributeId,
                    "batch_id" => $batchId,
                    "city_id" => $cityId,
                    "type" => $type,
                    "deliver_name" => $deliverName,
                    "deliver_id" => $deliverId,
                    "receive_id" => $receiveId,
                    "receive_name" => $receiveName,
                    "status" => 0,
                    "create_datetime" => $dateTime
                );
                break;
            default:
                $this->error("交接类型错误!");
                break;
        }

        $tsc = Yii::$app->db->beginTransaction();
        try {
            $tidyPlantHandoverForm = new TidyPlantHandoverForm();
            $tidyPlantHandoverForm->setAttributes($attributes);
            $tidyPlantHandoverForm->save();
            $lastInsertId = $tidyPlantHandoverForm->attributes['id'];
            $tsc->commit();
            $this->success(array("formId"=>$lastInsertId));
        } catch (Exception $e){
            $tsc->rollBack();
            $this->error("创建交接单失败!");
        }
    }

    /**
     * 录入交接单详情
     * @param $handoverId 交接单编号
     * @param $cityId 城市编号
     * @param $handoverDetail 交接单详情
     */
    public function actionEnteringHandoverFormDetail(){
        $handoverId = $this->getParam("handoverId");
        $cityId = $this->getParam("cityId");
        $handoverDetail = $this->getParam("handoverDetail");

        $this->checkParamters(array($handoverId, $handoverDetail));

        $handoverDetail = json_decode($handoverDetail, true);

        if (empty($handoverDetail)){
            $this->error("详情错误!");
        }

        $tsc = Yii::$app->db->beginTransaction();
        try {
            foreach ($handoverDetail as $item) {
                $fitNum = $item['fitNum'];
                foreach ($item['detail'] as $value) {
                    $tidyPlantHandoverFormDetail = new TidyPlantHandoverFormDetail();
                    $itemAttributes = array();
                    $itemAttributes['handover_id'] = $handoverId;
                    $itemAttributes['pid'] = $value['pid'];
                    $itemAttributes['pname'] = $value['pname'];
                    $itemAttributes['cid'] = $value['cid'];
                    $itemAttributes['cname'] = $value['cname'];
                    $itemAttributes['goods_type'] = $value['goodsType'];
                    $itemAttributes['deliver_prick_num'] = $value['prickNum'];
                    $itemAttributes['deliver_branch_num'] = $value['branchNum'];
                    $itemAttributes['deliver_bind_num'] = $value['bindNum'];
                    $itemAttributes['fit_order_num'] = $fitNum;
                    $itemAttributes['deliver_mark'] = $value['mark'];
                    $tidyPlantHandoverFormDetail->setAttributes($itemAttributes);
                    $tidyPlantHandoverFormDetail->save();
                }
            }

            $sqlUpdate = "UPDATE tidy_plant_handover_form SET status = 1 WHERE id = :id";
            Yii::$app->db->createCommand($sqlUpdate)->bindValues(array(":id"=>$handoverId))->execute();

            //判断是否需要更新distributeForm状态
            $sql = "SELECT s.* FROM tidy_transfers AS t LEFT JOIN tidy_line_station AS s ON (s.tran_id = t.trans_id) WHERE t.city_id = :city_id AND s.status = 1";
            $stationList = Yii::$app->db->createCommand($sql)->bindValues(array(":city_id"=>$cityId))->queryAll();
            $sql = "SELECT distribute_id, batch_id FROM tidy_plant_handover_form WHERE id = :id";
            $idArray = Yii::$app->db->createCommand($sql)->bindValues(array(":id"=>$handoverId))->queryOne();
            $sqlHandoverList = "SELECT * FROM tidy_plant_handover_form WHERE batch_id = :batch_id AND city_id = :city_id";
            $handoverList = Yii::$app->db->createCommand($sqlHandoverList)->bindValues(array(":batch_id"=>$idArray['batch_id'], ":city_id"=>$cityId))->queryAll();
            $changeFlag = true;
            $existStation = array();
            foreach ($handoverList as $item){
                if ($item['status'] == 0){
                    $changeFlag = false;
                    break;
                }
                $existStation[] = $item['station_id'];
            }
            if ($changeFlag){
                foreach ($stationList as $value){
                    if (!in_array($value['station_id'], $existStation)){
                        $changeFlag = false;
                    }
                }
            }
            if ($changeFlag){
                $sql = "UPDATE tidy_plant_distribute_form SET status = 1 WHERE id = :id";
                Yii::$app->db->createCommand($sql)->bindValues(array(":id"=>$idArray['distribute_id']))->execute();
            }

            $tsc->commit();
            $this->success("分配成功a!");
        } catch (Exception $e){
            $tsc->rollBack();
            $this->error("分配失败!");
        }
    }

    /**
     * 获取交接单详情
     * @param $formId 交接单编号
     */
    public function actionGetHandoverFormDetail(){
        $formId = $this->getParam("formId");

        $this->checkParamters(array($formId));

        $sql = "SELECT * FROM tidy_plant_handover_form_detail WHERE handover_id = :handover_id";
        $detail = Yii::$app->db->createCommand($sql)->bindValues(array(":handover_id"=>$formId))->queryAll();
        if (empty($detail)){
            $this->error("暂无详情!");
        }

        $returnInfo = array();
        $tempPnameArray = array();
        //先筛选一级品类
        foreach ($detail as $item){
            if (!in_array($item['pname'], $tempPnameArray)){
                $tempArray = array();
                $tempArray['pname'] = $item['pname'];
                $tempArray['goodsType'] = $item['goods_type'];
                $tempArray['fitNum'] = $item['fit_order_num'];
                $returnInfo[] = $tempArray;
                $tempPnameArray[] = $item['pname'];
            }
        }
        //筛选二级品类
        foreach ($returnInfo as &$value){
            foreach ($detail as $vl){
                if ($value['pname'] == $vl['pname']){
                    $value["detail"][] = $vl;
                }
            }
        }
        $this->success($returnInfo);
    }

    /**
     * 录入折损接口
     * @param $relativeId 关联编号
     * @param $damageType 折损类型
     * @param $batchId 批次编号
     * @param $userId 录入人编号
     * @param $damageImg 折损照片
     * @param $damageDetail 折损明细
     */
    public function actionEnteringDamage(){
        $relativeId = $this->getParam("relativeId");
        $damageType = $this->getParam("damageType");
        $batchId = $this->getParam("batchId");
        $userId = $this->getParam("userId");
        $damageImg = $this->getParam("damageImg");
        $damageDetail = $this->getParam("damageDetail");

        $this->checkParamters(array($relativeId, $damageType, $batchId, $userId, $damageDetail));

        $damageDetail = json_decode($damageDetail, true);
        if (empty($damageDetail)){
            $this->error("详情错误!");
        }

        $dateTime = date("Y-m-d H:i:s");

        $tsc = Yii::$app->db->beginTransaction();
        try{
            $tidyPlantDamage = new TidyPlantDamage();
            $attributes = array(
                "relative_id"=>$relativeId,
                "damage_type"=>$damageType,
                "batch_id"=>$batchId,
                "user_id"=>$userId,
                "create_datetime"=>$dateTime,
                "status"=>1,
            );
            $tidyPlantDamage->setAttributes($attributes);
            $tidyPlantDamage->save();
            $lastInsertId = $tidyPlantDamage->attributes("id");

            foreach ($damageDetail as $item){
                $tidyPlantDamageDetail = new TidyPlantDamageDetail();
                $tempAttributes = array();
                $tempAttributes['damage_id'] = $lastInsertId;
                $tempAttributes['pid'] = $item['pid'];
                $tempAttributes['pname'] = $item['pname'];
                $tempAttributes['cid'] = $item['cid'];
                $tempAttributes['cname'] = $item['cname'];
                $tempAttributes['prick_num'] = $item['prickNum'];
                $tempAttributes['branch_num'] = $item['branchNum'];
                $tempAttributes['bind_num'] = $item['bindNum'];
                $tidyPlantDamageDetail->setAttributes($tempAttributes);
                $tidyPlantDamageDetail->save();
            }
            switch ($damageType){
                //包装折损
                case "bzzs":
                    $sql = "UPDATE tidy_plant_distribute_form SET damage_flag = 1 WHERE batch_id = :batch_id";
                    Yii::$app->db->createCommand($sql)->bindValues(array(":batch_id"=>$batchId))->execute();
                    break;
                //采购折损
                case "cgzs":
                    $sql = "UPDATE tidy_plant_deliver_form SET damage_flag = 1 WHERE batch_id = :batch_id";
                    Yii::$app->db->createCommand($sql)->bindValues(array(":batch_id"))->execute();
                    break;
                default:
                    $tsc->rollBack();
                    $this->error("折损类型错误!");
                    break;
            }
            $tsc->commit();
            $this->success("录入成功!");
        } catch (Exception $e){
            $tsc->rollBack();
            $this->error("录入失败!");
        }
    }

    /**
     * 录入库存接口,针对批次
     * @param $batchId 批次编号
     * @param $cityId 城市编号
     * @param $recordUserId 记录人编号
     * @param $recordDetail 记录明细
     */
    public function actionEnteringStock(){
        $batchId = $this->getParam("batchId");
        $cityId = $this->getParam("cityId");
        $recordUserId = $this->getParam("recordUserId");
        $recordDetail = $this->getParam("recordDetail");

        $this->checkParamters(array($batchId, $cityId, $recordUserId, $recordDetail));

        $recordDetail = json_decode($recordDetail, true);
        if (empty($recordDetail)){
            $this->error("记录明细错误!");
        }

        $dateTime = date("Y-m-d H:i:s");

        $tsc = Yii::$app->db->beginTransaction();
        try{
            $tidyPlantCategoryStock = new TidyPlantCategoryStock();
            $attributes = array(
                "batch_id"=>$batchId,
                "city_id"=>$cityId,
                "record_userid"=>$recordUserId,
                "create_datetime"=>$dateTime
            );
            $tidyPlantCategoryStock->setAttributes($attributes);
            $tidyPlantCategoryStock->save();
            $lastInsertId = $tidyPlantCategoryStock->attributes['id'];

            foreach ($recordDetail as $item){
                $tidyPlantCategoryStockDetail = new TidyPlantCategoryStockDetail();
                $tempAttributes = array();
                $tempAttributes['stock_id'] = $lastInsertId;
                $tempAttributes['pid'] = $item['pid'];
                $tempAttributes['pname'] = $item['pname'];
                $tempAttributes['cid'] = $item['cid'];
                $tempAttributes['cname'] = $item['cname'];
                $tempAttributes['prick_num'] = $item['prickNum'];
                $tempAttributes['branch_num'] = $item['branchNum'];
                $tempAttributes['bind_num'] = $item['bindNum'];
                $tempAttributes['create_datetime'] = $dateTime;
                $tidyPlantCategoryStockDetail->setAttributes($tempAttributes);
                $tidyPlantCategoryStockDetail->save();
            }

            //更新录入剩余标记

            $tsc->commit();
            $this->success("录入成功!");
        } catch (Exception $e){
            $tsc->rollBack();
            $this->error("录入失败!");
        }
    }

}
