<?php

namespace App\Controller\Api;

use App\Model\Entity\Account;
use App\Model\Entity\AccountBlock;
use App\Model\Entity\AccountFriend;
use App\Model\Entity\AccountReport;
use App\Utility\AppUtil;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Firebase\JWT\JWT;

/**
 * Accounts Controller
 *
 * @property \App\Model\Table\AccountsTable $Accounts
 * @property \App\Model\Table\AccountBlocksTable $AccountBlocks
 * @property \App\Model\Table\AccountReportsTable $AccountReports
 * @property \App\Model\Table\AccountFriendsTable $AccountFriends
 *
 */
class AccountsController extends ApiAppController
{
    public function initialize()
    {
        parent::initialize();
        $this->authAllow(["register", "enterGroup"]);
    }

    public function get()
    {
        if (!$this->request->is('get')) {
            return $this->responseData(["error_code" => 100]);
        }
        $dataReturn = [
            "id" => $this->authUser->id,
            "avatar" => AppUtil::handleStringNull($this->authUser->avatar),
            "nickname" => AppUtil::handleStringNull($this->authUser->nickname),
            "gender" => AppUtil::handleNumberNull($this->authUser->gender, "int"),
            "intro" => AppUtil::handleStringNull($this->authUser->intro),
            "nationality" => AppUtil::handleStringNull($this->authUser->nationality),
            "age" => AppUtil::handleNumberNull($this->authUser->age, "int"),
            "prefecture" => AppUtil::handleStringNull($this->authUser->prefecture),
            "avatar_status" => AppUtil::handleNumberNull($this->authUser->avatar_status, "int"),
            "marital_status" => AppUtil::handleNumberNull($this->authUser->marital_status, "int"),
        ];
        return $this->responseData(["status" => true, "data" => $dataReturn]);
    }
    
    /**
     * search method
     *
     * @return \Cake\Http\Response|void
     */
    public function search()
    {        
        if (!$this->request->is("GET")) {
            return $this->responseData(["error_code" => 100]);
        }       
        $dataGet = $this->request->getQuery();
        $limit = isset($dataGet["limit"]) ? intval($dataGet["limit"]) : 10;
        $conditions = [
            "Accounts.id !=" => $this->authUser->id,
            "Accounts.in_group"=> 1,
            "Accounts.status"=> Account::STATUS_NORMAL
        ];
        
        $this->loadModel("AccountBlocks");
        $tmp_blocks = $this->AccountBlocks
            ->find()
            ->select(["AccountBlocks.account_receive_id"])
            ->where(
                [
                    "AccountBlocks.account_action_id" => $this->authUser->id, 
                    "AccountBlocks.status" => AccountBlock::STATUS_BLOCKED               
                ]);
        $blocks = [];
        foreach ($tmp_blocks as $tmp_block) {
            $blocks[] = $tmp_block->account_receive_id;
        }
        $this->loadModel("AccountReports");
        $tmp_reports = $this->AccountReports
            ->find()
            ->select(["AccountReports.account_receive_id"])
            ->where(
                [
                    "AccountReports.account_action_id" => $this->authUser->id, 
                    "AccountReports.status" => AccountReport::STATUS_REPORTED                
                ]);
        $reports = [];
        foreach ($tmp_reports as $tmp_report) {
            $reports[] = $tmp_report->account_receive_id;
        }
        $accountList = array_values(array_unique(array_merge($blocks, $reports), SORT_NUMERIC));
        if (count($accountList) > 0) {
            $conditions["Accounts.id NOT IN"] = $accountList;
        }
             
        if (isset($dataGet["gender"]) && $dataGet["gender"]) {
            $value = 0;
            switch ($dataGet["gender"]) {
                case '男性':
                    $value = 1;
                    break;
                case '女性':
                    $value = 2;
                    break;
                default:
                    $value = 0;
            }
            if ($value > 0) {
                $conditions["Accounts.gender"] = $value;
            }
        }
        
        if (isset($dataGet["last_id"])) {
            $conditions["Accounts.id <"] = $dataGet["last_id"];
        }
        
        if (isset($dataGet["has_avatar"])) {
            if ($dataGet["has_avatar"] == 1) {
                $conditions[] = "Accounts.avatar IS NOT NULL";
            } else {
                $conditions[] = "Accounts.avatar IS NULL";
            }
        }
        
        if (isset($dataGet["prefecture"]) && $dataGet["prefecture"]) {
            $conditions["Accounts.prefecture"] = $dataGet["prefecture"];
        }
        
        if (isset($dataGet["age_from"]) && $dataGet["age_from"]) {
            $conditions["Accounts.age >="] = $dataGet["age_from"];
        }
        
        if (isset($dataGet["age_to"]) && $dataGet["age_to"]) {
            $conditions["Accounts.age <="] = $dataGet["age_to"];
        }
        
        if (isset($dataGet["nickname"])) {
            $conditions["Accounts.nickname LIKE"] = "%" . $dataGet["nickname"] . "%";
        }
        
        if (isset($dataGet["marital_status"]) && $dataGet["marital_status"]) {
            $conditions["Accounts.marital_status"] = $dataGet["marital_status"];
        }
        
        /* @var Account[] $accounts */
        $accounts = $this->Accounts->find()
            ->contain([
                "Devices" => function ($q) {
                    return $q;
                }
            ])    
            ->where($conditions)
            ->order(["Accounts.id" => "DESC"])
            ->limit($limit)->all();
            
        $dataReturn = [
            "accounts" => [],
            "next_page" => ""
        ];
        foreach ($accounts as $k => $account) {
            $dataReturn["accounts"][] = [
                "id" => $account->id,
                "avatar" => AppUtil::handleStringNull($account->avatar),
                "nickname" => AppUtil::handleStringNull($account->nickname),
                "gender" => AppUtil::handleNumberNull($account->gender, "int"),
                "intro" => AppUtil::handleStringNull($account->intro),
                "nationality" => AppUtil::handleStringNull($account->nationality),
                "age" => AppUtil::handleNumberNull($account->age, "int"),
                "prefecture" => AppUtil::handleStringNull($account->prefecture),
                "marital_status" => AppUtil::handleNumberNull($account->marital_status, "int"),
                "avatar_status" => AppUtil::handleNumberNull($account->avatar_status, "int"),
                "login_time" => $account->device->last_access->timezone(Configure::read("COMMON.timezone"))->format("Y-m-d H:i:s")
            ];
            if ((count($accounts) - 1) == $k && count($accounts) >= $limit) {
                $dataReturn["next_page"] = "last_id=$account->id&last_date=" . $account->modified->timestamp;
            }
        }
        
        return $this->responseData(["status" => true, "data" => $dataReturn]);
    }

    public function register()
    {
        if (!$this->request->is("POST")) {
            return $this->responseData(["error_code" => 100]);
        }

        $device = $this->Accounts->Devices->find()
            ->select(["Devices.id", "Devices.push_token"])
            ->where([
                'uuid' => $this->clientDevice['uuid'],
                'user_agent' => $this->clientDevice['user-agent'],
            ])->first();

        if (!$device) {
            return $this->responseData(["error_code" => 404, "ext_msg" => "Device"]);
        }

        $tmp_account = $this->Accounts->find()->select(["Accounts.id"])->where(["Accounts.device_id" => $device->id])->first();
        if ($tmp_account) {
            return $this->responseData(["error_code" => 206]);
        }

        $dataPost = $this->request->getData();

        if (!isset($dataPost["nickname"]) ||
            !isset($dataPost["gender"]) ||
            !isset($dataPost["nationality"])
        ) {
            return $this->responseData(["error_code" => 101]);
        }

        $account = $this->Accounts->newEntity($dataPost);
        $account->device_id = $device->id;
        $account->in_group = Account::STATUS_NORMAL;
        $account->status = Account::STATUS_NORMAL;
        $account->revision = 1;

        if ($this->Accounts->save($account)) {
        	//TODO: For Apple Review
//	        $this->loadModel('AccountFriends');
//	        $tmp_friends = $this->Accounts->find()->select(["Accounts.id"])->where(["Accounts.id !=" => $account->id])->limit(2)->order('rand()');
//	        foreach ($tmp_friends as $k => $friend) {
//		        $entity = $this->AccountFriends->newEntity( [
//			        "account_action_id"  => $friend->id > $account->id ? $account->id : $friend->id,
//			        "account_receive_id" => $account->id > $friend->id ? $account->id : $friend->id,
//			        "action_id"          => $friend->id,
//			        "message"            => "Make friend ?",
//			        "status"             => AccountFriend::STATUS_PENDING
//		        ] );
//		        $this->AccountFriends->save( $entity, [ "atomic" => false ] );
//	        }
        	//TODO: For Apple Review

	        if (isset($dataPost["avatar"])) {
		        if (!isset($dataPost["avatar"]["size"])) {
			        return $this->responseData(["error_code" => 101]);
		        }
		        $avatar = AppUtil::uploadImageProfile($dataPost["avatar"], $account->id . "_" . 1);
		        if (empty($avatar)) {
			        return $this->responseData(["error_code" => 803]);
		        }
		        $path = Configure::read("App.fullBaseUrl") . "/" . Configure::read("App.subDomain") . "/webroot/upload/profiles/" . $avatar;
		        if ($path) {
			        $account->avatar = $path;
			        $account->avatar_status = Account::AVATAR_STATUS_UNCONFIRM;
			        $this->Accounts->save($account);
		        }
	        }
            $payload = [
                "sub" => $account->id,
                "profile" => [
                    "id" => $account->id,
                    "nickname" => $account->nickname,
                    "nationality" => AppUtil::handleStringNull($account->nationality),
                    "gender" => $account->gender,
                    "avatar" => AppUtil::handleStringNull($account->avatar),
                    "revision" => $account->revision,
                    "status" => $account->status,
                    "intro" => $account->intro,
                    "user_agent" => $this->clientDevice['user-agent'],
                    "push_token" => $device->push_token,
                    "age" => AppUtil::handleNumberNull($account->age, "int"),                
                    "prefecture" => AppUtil::handleStringNull($account->prefecture),
                    "avatar_status" => AppUtil::handleNumberNull($account->avatar_status, "int"),
                ],
            ];
            $jwt_token = JWT::encode($payload, $this->_apiConfig["jwt_key"]);

            $this->syncAccountToMongo([
                "id" => $account->id,
                "account_id" => $account->id,
                "nickname" => $account->nickname,
                "nationality" => AppUtil::handleStringNull($account->nationality),
                "gender" => $account->gender,
                "avatar" => AppUtil::handleStringNull($account->avatar),
                "intro" => AppUtil::handleStringNull($account->intro),
                "revision" => $account->revision,
                "status" => $account->status,
                "user_agent" => $this->clientDevice['user-agent'],
                "push_token" => $device->push_token,
                "age" => AppUtil::handleNumberNull($account->age, "int"),
                "prefecture" => AppUtil::handleStringNull($account->prefecture),
                "avatar_status" => AppUtil::handleNumberNull($account->avatar_status, "int"),
            ]);

            return $this->responseData(['status' => true, 'data' => ["Authorization" => $jwt_token, "payload"=>$payload]]);
        } else {
            return $this->responseData(["error_code" => 600]);
        }
    }

    public function update()
    {
        if (!$this->request->is('post')) {
            return $this->responseData(["error_code" => 100]);
        }
        $dataPost = $this->request->getData();
        if (empty($dataPost)) {
            return $this->responseData(["error_code" => 101]);
        }
        $revision = $this->authUser->revision + 1;
        if (isset($dataPost["nickname"])) {
            $this->authUser->nickname = trim($dataPost["nickname"]);
        }
        if (isset($dataPost["intro"])) {
            $this->authUser->intro = trim($dataPost["intro"]);
        }

        if (isset($dataPost["avatar"])) {
            if (!isset($dataPost["avatar"]["size"])) {
                return $this->responseData(["error_code" => 101]);
            }
            $avatar = AppUtil::uploadImageProfile($dataPost["avatar"], $this->authUser->id . "_" . $revision);
            if (empty($avatar)) {
                return $this->responseData(["error_code" => 803]);
            }
            $path = Configure::read("App.fullBaseUrl") . "/" . Configure::read("App.subDomain") . "/webroot/upload/profiles/" . $avatar;
            $this->authUser->avatar = $path;
        }
        if (isset($dataPost["gender"]) && in_array($dataPost["gender"], array_keys(Account::getGenders()))) {
            $this->authUser->gender = $dataPost["gender"];
        }

        if (isset($dataPost["nationality"])) {
            $this->authUser->nationality = $dataPost["nationality"];
        }
        
        if (isset($dataPost["age"])) {
            $this->authUser->age = $dataPost["age"];
        }
        
        if (isset($dataPost["prefecture"])) {
            $this->authUser->prefecture = $dataPost["prefecture"];
        }

        $this->authUser->revision = $revision;

        if ($this->Accounts->save($this->authUser)) {
            $dataReturn = [
                "id" => $this->authUser->id,
                "avatar" => AppUtil::handleStringNull($this->authUser->avatar),
                "nickname" => AppUtil::handleStringNull($this->authUser->nickname),
                "gender" => AppUtil::handleNumberNull($this->authUser->gender, "int"),
                "intro" => AppUtil::handleStringNull($this->authUser->intro),
                "objective" => AppUtil::handleNumberNull($this->authUser->objective, "int"),
                "nationality" => AppUtil::handleStringNull($this->authUser->nationality),
                "revision" => $this->authUser->revision,
                "intro" => $this->authUser->intro,
                "age" => AppUtil::handleNumberNull($this->authUser->age, "int"),
                "prefecture" => AppUtil::handleStringNull($this->authUser->prefecture),
                "avatar_status" => AppUtil::handleNumberNull($this->authUser->avatar_status, "int"),
            ];
            $auth = $this->request->getHeader("Authorization");
            $JWTDecoded = JWT::decode($auth[0], $this->_apiConfig['jwt_key'], ['HS256']);
            $payload = [
                "sub" => $this->authUser->id,
                "profile" => [
                    "id" => $this->authUser->id,
                    "nickname" => $dataReturn["nickname"],
                    "nationality" => $dataReturn["nationality"],
                    "gender" => $dataReturn["gender"],
                    "avatar" => $dataReturn["avatar"],
                    "age" => $dataReturn["age"],
                    "intro" => $dataReturn["intro"],
                    "prefecture" => $dataReturn["prefecture"],
                    "revision" => $dataReturn["revision"],
                    "status" => $this->authUser->status,
                    "avatar_status" => $dataReturn["avatar_status"],
                    "user_agent" => $this->clientDevice['user-agent'],
                    "push_token" => $JWTDecoded->profile->push_token
                ],
            ];
            $jwt_token = JWT::encode($payload, $this->_apiConfig["jwt_key"]);
            $dataReturn["Authorization"] = $jwt_token;
            $dataReturn["payload"] = $payload;
            return $this->responseData(["status" => true, "data" => $dataReturn]);
        } else {
            return $this->responseData(["error_code" => 600]);
        }
    }

    public function detail()
    {
        if (!$this->request->is('get')) {
            return $this->responseData(["error_code" => 100]);
        }
        $dataGet = $this->request->getQuery();
        if (!isset($dataGet["id"]) || $dataGet["id"] == $this->authUser->id) {
            return $this->responseData(["error_code" => 101]);
        }

        /* @var Account $account */
        $account = $this->Accounts->find()
            ->where(["Accounts.id" => $dataGet["id"]])
            ->first();
        if (!$account) {
            return $this->responseData(["error_code" => 404, "ext_msg" => "Account"]);
        }
        $this->loadModel("AccountBlocks");
        $this->loadModel("AccountReports");
        $this->loadModel("AccountFriends");
        $is_blocked = $this->AccountBlocks->find()
            ->select(["AccountBlocks.id"])
            ->where([
                "AccountBlocks.account_action_id" => $this->authUser->id,
                "AccountBlocks.account_receive_id" => $dataGet["id"],
                "AccountBlocks.status" => AccountBlock::STATUS_BLOCKED
            ])->first();
        $is_friend_blocked = $this->AccountBlocks->find()
            ->select(["AccountBlocks.id"])
            ->where([
                "AccountBlocks.account_action_id" => $dataGet["id"],
                "AccountBlocks.account_receive_id" => $this->authUser->id,
                "AccountBlocks.status" => AccountBlock::STATUS_BLOCKED
            ])->first();
        $is_reported = $this->AccountReports->find()
            ->select(["AccountReports.id"])
            ->where([
                "AccountReports.account_action_id" => $this->authUser->id,
                "AccountReports.account_receive_id" => $dataGet["id"],
                "AccountReports.status" => AccountReport::STATUS_REPORTED
            ])->first();
        $is_friend_reported = $this->AccountReports->find()
            ->select(["AccountReports.id"])
            ->where([
                "AccountReports.account_action_id" => $dataGet["id"],
                "AccountReports.account_receive_id" => $this->authUser->id,
                "AccountReports.status" => AccountReport::STATUS_REPORTED
            ])->first();
	    $conds              = $this->authUser->id > $dataGet["id"] ? [
		    "AccountFriends.account_action_id"  => $dataGet["id"],
		    "AccountFriends.account_receive_id" => $this->authUser->id
	    ] : [
		    "AccountFriends.account_receive_id" => $dataGet["id"],
		    "AccountFriends.account_action_id"  => $this->authUser->id
	    ];
        $is_friend = $this->AccountFriends->find()
            ->select(["AccountFriends.id"])
            ->where([
                $conds,
                "AccountFriends.status" => AccountFriend::STATUS_FRIEND
            ])->first();

        $dataReturn = [
            "id" => $account->id,
            "avatar" => AppUtil::handleStringNull($account->avatar),
            "nickname" => AppUtil::handleStringNull($account->nickname),
            "age" => AppUtil::handleNumberNull($account->age, "int"),
            "gender" => AppUtil::handleNumberNull($account->gender, "int"),
            "intro" => AppUtil::handleStringNull($account->intro),
            "nationality" => AppUtil::handleStringNull($account->nationality),
            "prefecture" => AppUtil::handleStringNull($account->prefecture),
            "avatar_status" => AppUtil::handleNumberNull($account->avatar_status, "int"),
//            "in_group" => AppUtil::handleNumberNull($account->in_group, "int"),
            "state" => [
                "is_blocked" => $is_blocked ? AccountBlock::STATUS_BLOCKED : AccountBlock::STATUS_NORMAL,
                "is_friend_blocked" => $is_friend_blocked ? AccountBlock::STATUS_BLOCKED : AccountBlock::STATUS_NORMAL,
                "is_reported" => $is_reported ? AccountReport::STATUS_REPORTED : AccountReport::STATUS_NORMAL,
                "is_friend_reported" => $is_friend_reported ? AccountReport::STATUS_REPORTED : AccountReport::STATUS_NORMAL,
                "is_friend" => $is_friend ? AccountFriend::STATUS_FRIEND : AccountFriend::STATUS_NORMAL,
            ]
        ];
        return $this->responseData(["status" => true, "data" => $dataReturn]);
    }

    public function addFriend()
    {
        if (!$this->request->is('get')) {
            return $this->responseData(["error_code" => 100]);
        }
        $dataGet = $this->request->getQuery();
        if (!isset($dataGet["id"]) || $dataGet["id"] == $this->authUser->id) {
            return $this->responseData(["error_code" => 101]);
        }
        $this->loadModel("AccountFriends");
	    $conds       = $this->authUser->id > $dataGet["id"] ? [
		    "AccountFriends.account_action_id"  => $dataGet["id"],
		    "AccountFriends.account_receive_id" => $this->authUser->id
	    ] : [
		    "AccountFriends.account_receive_id" => $dataGet["id"],
		    "AccountFriends.account_action_id"  => $this->authUser->id
	    ];
        $tmp_friend = $this->AccountFriends->find()->where($conds)->first();
        if ($tmp_friend) {
            $tmp_friend->status = AccountFriend::STATUS_FRIEND;
	        if ($this->AccountFriends->save($tmp_friend)) {
		        return $this->responseData(["status" => true]);
	        } else {
		        return $this->responseData(["error_code" => 600]);
	        }
        }
	    return $this->responseData(["error_code" => 404]);
    }

    public function unFriend()
    {
        if (!$this->request->is('get')) {
            return $this->responseData(["error_code" => 100]);
        }
        $dataGet = $this->request->getQuery();
        if (!isset($dataGet["id"]) || $dataGet["id"] == $this->authUser->id) {
            return $this->responseData(["error_code" => 101]);
        }
        $this->loadModel("AccountFriends");
	    $conds       = $this->authUser->id > $dataGet["id"] ? [
		    "AccountFriends.account_action_id"  => $dataGet["id"],
		    "AccountFriends.account_receive_id" => $this->authUser->id
	    ] : [
		    "AccountFriends.account_receive_id" => $dataGet["id"],
		    "AccountFriends.account_action_id"  => $this->authUser->id
	    ];
        $tmp_friend = $this->AccountFriends->find()->where($conds)->first();
        if ($tmp_friend) {
            $tmp_friend->status = AccountFriend::STATUS_NORMAL;
            if ($this->AccountFriends->save($tmp_friend)) {
                return $this->responseData(["status" => true]);
            } else {
                return $this->responseData(["error_code" => 600]);
            }
        }
	    return $this->responseData(["error_code" => 404]);
    }

    public function block()
    {
        if (!$this->request->is('get')) {
            return $this->responseData(["error_code" => 100]);
        }
        $dataGet = $this->request->getQuery();
        if (!isset($dataGet["id"]) || $dataGet["id"] == $this->authUser->id) {
            return $this->responseData(["error_code" => 101]);
        }
        $this->loadModel("AccountBlocks");
        $tmp_block = $this->AccountBlocks->find()->where(["AccountBlocks.account_action_id" => $this->authUser->id, "AccountBlocks.account_receive_id" => $dataGet["id"]])->first();
        if ($tmp_block) {
            $tmp_block->status = AccountBlock::STATUS_BLOCKED;
        } else {
            $tmp_block = $this->AccountBlocks->newEntity([
                "account_action_id" => $this->authUser->id,
                "account_receive_id" => $dataGet["id"],
                "status" => AccountBlock::STATUS_BLOCKED
            ]);
        }
        if ($this->AccountBlocks->save($tmp_block)) {
            return $this->responseData(["status" => true]);
        } else {
            return $this->responseData(["error_code" => 600]);
        }
    }

    public function unBlock()
    {
        if (!$this->request->is('get')) {
            return $this->responseData(["error_code" => 100]);
        }
        $dataGet = $this->request->getQuery();
        if (!isset($dataGet["id"]) || $dataGet["id"] == $this->authUser->id) {
            return $this->responseData(["error_code" => 101]);
        }
        $this->loadModel("AccountBlocks");
        $tmp_block = $this->AccountBlocks->find()->where(["AccountBlocks.account_action_id" => $this->authUser->id, "AccountBlocks.account_receive_id" => $dataGet["id"]])->first();
        if ($tmp_block) {
            $tmp_block->status = AccountBlock::STATUS_NORMAL;
            if ($this->AccountBlocks->save($tmp_block)) {
                return $this->responseData(["status" => true]);
            } else {
                return $this->responseData(["error_code" => 600]);
            }
        }
        return $this->responseData(["status" => true]);
    }

    public function report()
    {
        if (!$this->request->is('get')) {
            return $this->responseData(["error_code" => 100]);
        }
        $dataGet = $this->request->getQuery();
        if (!isset($dataGet["id"]) || $dataGet["id"] == $this->authUser->id) {
            return $this->responseData(["error_code" => 101]);
        }
        $this->loadModel("AccountReports");
        $tmp_report = $this->AccountReports->find()->where(["AccountReports.account_action_id" => $this->authUser->id, "AccountReports.account_receive_id" => $dataGet["id"]])->first();
        if ($tmp_report) {
            $tmp_report->status = AccountReport::STATUS_REPORTED;
        } else {
            $tmp_report = $this->AccountReports->newEntity([
                "account_action_id" => $this->authUser->id,
                "account_receive_id" => $dataGet["id"],
                "status" => AccountReport::STATUS_REPORTED
            ]);
        }
        $this->loadModel("AccountFriends");
	    $conds  = $this->authUser->id > $dataGet["id"] ? [
		    "AccountFriends.account_action_id"  => $dataGet["id"],
		    "AccountFriends.account_receive_id" => $this->authUser->id
	    ] : [
		    "AccountFriends.account_receive_id" => $dataGet["id"],
		    "AccountFriends.account_action_id"  => $this->authUser->id
	    ];
        $tmp_friend = $this->AccountFriends
	        ->find()
	        ->where([$conds, "AccountFriends.status" => AccountFriend::STATUS_FRIEND])->first();
        if ($tmp_friend) {
            $tmp_friend->status = AccountFriend::STATUS_NORMAL;
            $conn = $this->AccountReports->getConnection();
            $conn->begin();
            try {
                $this->AccountFriends->save($tmp_friend, ["atomic" => false]);
                $this->AccountReports->save($tmp_report, ["atomic" => false]);
                $conn->commit();
                return $this->responseData(["status" => true]);
            } catch (\Exception $ex) {
                $conn->rollback();
                return $this->responseData(["error_code" => 600]);
            }
        } else {
            if ($this->AccountReports->save($tmp_report)) {
                return $this->responseData(["status" => true]);
            } else {
                return $this->responseData(["error_code" => 600]);
            }
        }
    }

    public function exitGroup()
    {
        if (!$this->request->is('get')) {
            return $this->responseData(["error_code" => 100]);
        }
        $this->authUser->in_group = Account::STATUS_EXIT;
        if ($this->Accounts->save($this->authUser)) {
            return $this->responseData(["status" => true]);
        } else {
            return $this->responseData(["error_code" => 600]);
        }
    }
    
    public function disableUser()
    {
        if (!$this->request->is('get')) {
            return $this->responseData(["error_code" => 100]);
        }
        $this->authUser->status = Account::STATUS_DISABLE;
        if ($this->Accounts->save($this->authUser)) {
            return $this->responseData(["status" => true]);
        } else {
            return $this->responseData(["error_code" => 600]);
        }
    }
    
    public function enterGroup()
    {
        if (!$this->request->is('get')) {
            return $this->responseData(["error_code" => 100]);
        }
        $auth = $this->request->getHeader("Authorization");
        if (!empty($auth)) {
            try {
                $JWTDecoded = JWT::decode($auth[0], $this->_apiConfig['jwt_key'], ['HS256']);
                $this->authUser = $this->Accounts->find()->where(["Accounts.id" => $JWTDecoded->profile->id])->first();
                if (!$this->authUser) {
                    return $this->responseData(["error_code" => 404, "ext_msg" => "Account"]);
                }
                $this->authUser->in_group = Account::STATUS_NORMAL;
                if ($this->Accounts->save($this->authUser)) {
                    return $this->responseData(["status" => true]);
                } else {
                    return $this->responseData(["error_code" => 600]);
                }
            } catch (\Exception $ex) {
                return $this->responseData(['error_code' => 201]);
            }
        } else {
            return $this->responseData(['error_code' => 201]);
        }
    }

    public function friendList()
    {
        if (!$this->request->is('get')) {
            return $this->responseData(["error_code" => 100]);
        }
        $this->loadModel("AccountFriends");
        /* @var  AccountFriend[] $friends */
        $friends = $this->AccountFriends->find()
            ->contain([
                "AccountActions" => function ($q) {
                    return $q->select([
                        "AccountActions.id",
                        "AccountActions.nickname",
                        "AccountActions.avatar",
                        "AccountActions.modified",
                        "AccountActions.gender",
                        "AccountActions.nationality",
                        "AccountActions.revision",
                    ]);
                },
                "AccountReceives" => function ($q) {
	                return $q->select([
		                "AccountReceives.id",
		                "AccountReceives.nickname",
		                "AccountReceives.avatar",
		                "AccountReceives.modified",
		                "AccountReceives.gender",
		                "AccountReceives.nationality",
		                "AccountReceives.revision",
	                ]);
                }
            ])
            ->where([
                'OR' => [
                    [
                        "AccountFriends.account_action_id" => $this->authUser->id,
                        "AccountFriends.status !="            => AccountFriend::STATUS_NORMAL
                    ],
                    [
                        "AccountFriends.account_receive_id" => $this->authUser->id,
                        "AccountFriends.status !="             => AccountFriend::STATUS_NORMAL
                    ]
                ]
            ])
	        /* ->where( [
		        "AccountFriends.account_action_id" => $this->authUser->id,
		        "AccountFriends.status !="            => AccountFriend::STATUS_NORMAL
	        ] )->orWhere( [
		        "AccountFriends.account_receive_id" => $this->authUser->id,
		        "AccountFriends.status !="             => AccountFriend::STATUS_NORMAL
	        ] ) */
            ->order(["AccountFriends.modified" => "DESC"]);
        $dataReturn = [
        	"pending" => [],
	        "friend" => []
        ];
        foreach ($friends as $k => $friend) {
        	$tmp_friend = [
		        "id" => $this->authUser->id == $friend->account_action_id ? $friend->account_receife->id : $friend->account_action->id,
		        "nickname" => $this->authUser->id == $friend->account_action_id ? $friend->account_receife->nickname : $friend->account_action->nickname,
		        "avatar" => $this->authUser->id == $friend->account_action_id ? AppUtil::handleStringNull($friend->account_receife->avatar) : AppUtil::handleStringNull($friend->account_action->avatar),
		        "gender" => $this->authUser->id == $friend->account_action_id ? AppUtil::handleNumberNull($friend->account_receife->gender, "int") : AppUtil::handleNumberNull($friend->account_action->gender, "int"),
		        "nationality" => $this->authUser->id == $friend->account_action_id ? AppUtil::handleStringNull($friend->account_receife->nationality) : AppUtil::handleStringNull($friend->account_action->nationality),
		        "revision" => $this->authUser->id == $friend->account_action_id ? $friend->account_receife->revision : $friend->account_action->revision,
		        "created" => $this->authUser->id == $friend->account_action_id ? $friend->account_receife->modified->format("Y-m-d H:i:s") : $friend->account_action->modified->format("Y-m-d H:i:s"),
		        "message" => $friend->message
	        ];
        	if ($friend->status == AccountFriend::STATUS_FRIEND) {
		        $dataReturn["friend"][] = $tmp_friend;
	        } else if ($friend->status == AccountFriend::STATUS_PENDING && $friend->action_id != $this->authUser->id) {

		        $dataReturn["pending"][] = $tmp_friend;
	        }
        }
        return $this->responseData(["status" => true, "data" => $dataReturn]);
    }

    public function searchFriend()
    {
        if (!$this->request->is("GET")) {
            return $this->responseData(["error_code" => 100]);
        }

        $dataGet = $this->request->getQuery();
        $limit = isset($dataGet["limit"]) ? intval($dataGet["limit"]) : 9;

        $conditions = [
            "Accounts.id !=" => $this->authUser->id,
            "Accounts.in_group"=> 1
        ];

        $this->loadModel("AccountReports");
        $tmp_reports = $this->AccountReports->find()->select(["AccountReports.account_receive_id"])->where(["AccountReports.account_action_id" => $this->authUser->id, "AccountReports.status" => AccountReport::STATUS_REPORTED]);
        $reports = [];
        foreach ($tmp_reports as $tmp_report) {
            $reports[] = $tmp_report->account_receive_id;
        }
        if (count($reports) > 0) {
            $conditions["Accounts.id NOT IN"] = $reports;
        }

        if (isset($dataGet["last_id"])) {
            $conditions["Accounts.id <"] = $dataGet["last_id"];
        }

        if (isset($dataGet["nickname"])) {
            $conditions["Accounts.nickname LIKE"] = "%" . $dataGet["nickname"] . "%";
        }

        if (isset($dataGet["nationality"]) && isset(AppUtil::getCountries()[$dataGet["nationality"]])) {
            $conditions["Accounts.nationality"] = $dataGet["nationality"];
        }

        if (isset($dataGet["gender"])) {
            $conditions["Accounts.gender"] = $dataGet["gender"];
        }

        if (isset($dataGet["age_from"]) && isset($dataGet["age_to"])) {
            $conditions["Accounts.age >="] = $dataGet["age_from"];
            $conditions["Accounts.age <="] = $dataGet["age_to"];
        }

        if (isset($dataGet["marital_status"]) && $dataGet["marital_status"]) {
            $conditions["Accounts.marital_status"] = $dataGet["marital_status"];
        }
        if (isset($dataGet["objective"]) && $dataGet["objective"]) {
            $conditions["Accounts.objective"] = $dataGet["objective"];
        }

        if (isset($dataGet["is_image"])) {
            if ($dataGet["is_image"]) {
                $conditions[] = "Accounts.avatar IS NOT NULL";
            } else {
                $conditions[] = "Accounts.avatar IS NULL";
            }
        }

        /* @var Account[] $accounts */
        $accounts = $this->Accounts->find()
            ->contain([
                "Devices" => function ($q) {
                    return $q;
                }
            ])
            ->select(["Accounts.id", "Accounts.nickname", "Accounts.avatar", "Accounts.gender", "Accounts.age", "Accounts.nationality", "Accounts.device_id", "Devices.last_access"])
            ->where($conditions)
            ->limit($limit)
            ->order(["Accounts.id" => "DESC"])
            ->all();
        $dataReturn = [
            "accounts" => [],
            "next_page" => ""
        ];
        foreach ($accounts as $k => $account) {
            $dataReturn["accounts"][] = [
                "id" => $account->id,
                "nickname" => $account->nickname,
                "avatar" => AppUtil::handleStringNull($account->avatar),
                "age" => $account->age,
                "gender" => $account->gender,
                "nationality" => AppUtil::handleStringNull($account->nationality),
            ];
            if ((count($accounts) - 1) == $k && count($accounts) >= $limit) {
                $dataReturn["next_page"] = "last_id=$account->id&last_date=" . $account->device->last_access->timestamp;
            }
        }
        return $this->responseData(["status" => true, "data" => $dataReturn]);
    }

    public function setting() {
    	if (!$this->request->is("POST")) {
    		return $this->responseData(["error_code" => 100]);
	    }

	    $dataPost = $this->request->getData();

	    if (isset($dataPost["flg_push"])) {
	    	$this->authUser->flg_push = $dataPost["flg_push"] ? true : false;
	    }

	    if ($dataPost) {
	    	if ($this->Accounts->save($this->authUser)) {
	    		return $this->responseData(["status" => true]);
		    } else {
			    return $this->responseData(["error_code" => 600]);
		    }
	    } else {
		    return $this->responseData(["error_code" => 101]);
	    }
    }
}
