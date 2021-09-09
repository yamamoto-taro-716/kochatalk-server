<?php

namespace App\Controller;

use App\Model\Table\AccountsTable;
use App\Utility\AppUtil;
use Cake\I18n\Time;

/**
 * PushNotification Controller
 *
 * @property AccountsTable $Accounts
 *
 */
class PushNotificationController extends AppController
{
    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        $this->loadModel("Devices");
        $device = $this->Devices->find()
            ->where(["LENGTH(Devices.push_token) > " => 16])
            ->count();
        $this->set(compact("device"));
    }

    public function send()
    {
        $registration_ids = ['csgnNRHOdtg:APA91bH6n4tvd_CSVsbMTjnuhBRtkewvmxQwdb8-j4JnHpuLimaLjWBUAWS8CgnJJFn9H_41h65uXuJjCCnsYKXQz-qp7JjRTVhpsrPvSqCJ9gMQifVpG288Ppqzp9-G71mElMgaurdz'];
        $data = [
            "sound" => "default",
            "body" => "Hello",
            "created" => Time::now()->format("Y-m-d H:i:s"),
            "title" => "System Admin",
            "account_id" => 0,
            "nationality" => "",
            "gender" => 0,
            "avatar" => "",
            "revision" => 0,
            "type" => "admin"
        ];
        $result = AppUtil::sendFCMMessage($data, $registration_ids);
        pr($result);
    }

    public function sendPush()
    {
        if (!$this->request->is("ajax")) {
            return $this->redirect(["action" => "index"]);
        }
        $dataGet = $this->request->getQuery();
        $conditions = [
            "LENGTH(Devices.push_token) >" => 16
        ];

        if (isset($dataGet["id"]) && !empty($dataGet["id"])) {
            $conditions["Accounts.id"] = $dataGet["id"];
        }

        if (isset($dataGet["nickname"]) && trim($dataGet["nickname"]) != "") {
            $conditions["Accounts.nickname LIKE"] = "%" . $dataGet["nickname"] . "%";
        }

        if (isset($dataGet["gender"]) && count($dataGet["gender"]) > 0) {
            if (count($dataGet["gender"]) < 2) {
                foreach ($dataGet["gender"] as $value) {
                    $conditions["Accounts.gender"] = $value;
                }
            }
        }

        if (isset($dataGet["os"]) && count($dataGet["os"]) > 0) {
            if (count($dataGet["os"]) < 2) {
                foreach ($dataGet["os"] as $value) {
                    $conditions["Devices.user_agent"] = $value;
                }
            }
        }

        if (isset($dataGet["status"]) && count($dataGet["status"]) > 0) {
            if (count($dataGet["status"]) < 4) {
                foreach ($dataGet["status"] as $value) {
                    $conditions["OR"][] = [
                        "Accounts.status" => $value
                    ];
                }
            }
        }

        $this->loadModel("Accounts");
        $account = $this->Accounts->find()
            ->contain(["Devices"])
            ->select(["Devices.push_token","Devices.user_agent"])
            ->where($conditions);
        $dataReturn = [];
        if ($dataGet["action"] == "search") {
            $dataReturn["action"] = "search";
            $dataReturn["number"] = $account->count();
        } else {

            $notification = [
		        "title" =>$dataGet["title"],
		        "body"  => $dataGet["content"]
	        ];

	        $data = [
		        "type"    => "admin",
		        "message" => "",
		        "created" => Time::now()->format( "Y-m-d H:i:s" )
            ];
            $dataAndroid = [
                'content' => array_merge($data, [
                    "title" => $dataGet["title"],
                    "message" => $dataGet["content"]
                ])
            ];
            
            $page = $account->count() / 1000;
            if ($page > intval($page)) {
                $page = intval($page + 1);
            }
            $offset = 0;
            // for ($i = 0; $i < 1; $i++) {
            $iOSSuccess = 0;
            $androidSuccess = 0;
            $iOSFailure = 0;
            $androidFailure = 0;
            for ($i = 0; $i < $page; $i++) {
                $arrTokenAndroid = [];
                $arrTokenIos = [];
                $tmpAccount = $account->limit(1000)->offset($offset);
                $offset = $offset + 1000;
                foreach ($tmpAccount as $key => $value) {
                    if ($value->device) {
                        if ($value->device->user_agent == 'android') {
                            $arrTokenAndroid[] = $value->device->push_token;
                        } else if ($value->device->user_agent == 'ios') {
                            $arrTokenIos[] = $value->device->push_token;
                        }
                    }
                }
                $resultIos = [];
                if (!empty($arrTokenIos)) {
                    $resultIos = AppUtil::sendFCMMessage($notification, $data, $arrTokenIos);
                }
                $resultAndroid = [];
                if (!empty($arrTokenAndroid)) {
                    $resultAndroid = AppUtil::sendFCMMessage([], $dataAndroid, $arrTokenAndroid, 'android');
                }
                if (isset($resultIos['success'])) $iOSSuccess += $resultIos['success'];
                if (isset($resultIos['failure'])) $iOSFailure += $resultIos['failure'];
                if (isset($resultAndroid['success'])) $androidSuccess += $resultAndroid['success'];
                if (isset($resultAndroid['failure'])) $androidFailure += $resultAndroid['failure'];
            }
	        
            
            
            if ($androidSuccess > 0 || $iOSSuccess > 0) {
                $dataReturn["status"] = true;
            } else {
                $dataReturn["status"] = false;
            }
            $dataReturn["iosSuccess"] = $iOSSuccess;
            $dataReturn["iOSFailure"] = $iOSFailure;
            $dataReturn["androidSuccess"] = $androidSuccess;
            $dataReturn["androidFailure"] = $androidFailure;
            $dataReturn["arrTokenAndroid"] = count($arrTokenAndroid);
            $dataReturn["arrTokenIos"] = count($arrTokenIos);
            
        }
        $this->response = $this->response->withType("application/json")->withStringBody(json_encode($dataReturn));
        return $this->response;
    }
}
