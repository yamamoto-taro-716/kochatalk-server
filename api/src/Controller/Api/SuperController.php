<?php

namespace App\Controller\Api;

use App\Model\Entity\AccountFriend;
use App\Model\Table\DevicesTable;
use App\Utility\AppUtil;
use Cake\I18n\Time;
use Firebase\JWT\JWT;

/**
 * Super Controller
 *
 * @property DevicesTable $Devices
 */
class SuperController extends ApiAppController {
	public function initialize() {
		parent::initialize();
		$this->authAllow( [ 'startUp', 'syncPushToken' ] );
		$this->loadModel( "Devices" );
	}

	public function startUp() {
		//Only accept method GET
		if ( ! $this->request->is( 'get' ) ) {
			return $this->responseData( [ 'error_code' => 100 ] );
		}

		//Check device exist
		$device    = $this->Devices->find()
		                           ->where( [
			                           'uuid'       => $this->clientDevice['uuid'],
			                           'user_agent' => $this->clientDevice['user-agent'],
		                           ] )->first();
		$jwt_token = '';
		if ( $device ) {
			$account = $this->Devices->Accounts->find()->select( [
				"Accounts.id",
				"Accounts.nickname",
				"Accounts.nationality",
				"Accounts.gender",
				"Accounts.revision",
				"Accounts.avatar",
				"Accounts.status"
			] )->where( [ "Accounts.device_id" => $device->id ] )->first();
			if ( $account ) {
				$payload   = [
					"sub"     => $account->id,
					"profile" => [
						"id"          => $account->id,
						"nickname"    => $account->nickname,
						"nationality" => AppUtil::handleStringNull( $account->nationality ),
						"gender"      => $account->gender,
						"avatar"      => AppUtil::handleStringNull( $account->avatar ),
						"revision"    => $account->revision,
						"status"      => $account->status,
						"user_agent"  => $this->clientDevice['user-agent'],
						"push_token"  => $device->push_token
					],
				];
				$jwt_token = JWT::encode( $payload, $this->_apiConfig["jwt_key"] );
			}
		} else {
			$device = $this->Devices->newEntity();
		}
		$device->uuid        = $this->clientDevice['uuid'];
		$device->user_agent  = $this->clientDevice['user-agent'];
		$device->version     = $this->clientDevice['version'];
		$device->last_access = Time::now();

		$this->loadModel( "Settings" );
		$setting = $this->Settings->find()
		                          ->select( [ "Settings.title_ads", "Settings.title_ads_en", "Settings.show_notify", "Settings.count_ads" ] )
		                          ->first();

		$dataReturn = [
			"Authorization" => $jwt_token,
			"title_notify"  => $setting->title_ads,
			"title_notify_en"  => $setting->title_ads_en,
			"show_notify"   => $setting->show_notify,
			"count_ads"     => $setting->count_ads,
		];

		if ( $this->Devices->save( $device ) ) {
			return $this->responseData( [ 'status' => true, 'data' => $dataReturn ] );
		} else {
			return $this->responseData( [ 'error_code' => 600 ] );
		}
	}

	public function syncPushToken() {
		//Only accept method POST
		if ( ! $this->request->is( 'post' ) ) {
			return $this->responseData( [ 'error_code' => 100 ] );
		}
		$dataPost = $this->request->getData();
		if ( ! isset( $dataPost["push_token"] ) || strlen( $dataPost["push_token"] ) < 16 ) {
			return $this->responseData( [ "error_code" => 101 ] );
		}
		$device = $this->Devices->find()
		                        ->where( [
			                        'uuid'       => $this->clientDevice['uuid'],
			                        'user_agent' => $this->clientDevice['user-agent'],
		                        ] )->first();
		if ( ! $device ) {
			return $this->responseData( [ "error_code" => 404, "ext_msg" => "Device" ] );
		}
		$device->push_token = $dataPost["push_token"];
		if ( $this->Devices->save( $device ) ) {
			$account   = $this->Devices->Accounts->find()->select( [
				"Accounts.id",
				"Accounts.nickname",
				"Accounts.nationality",
				"Accounts.gender",
				"Accounts.revision",
				"Accounts.avatar",
				"Accounts.status"
			] )->where( [ "Accounts.device_id" => $device->id ] )->first();
			$jwt_token = '';
			if ( $account ) {
				$payload   = [
					"sub"     => $account->id,
					"profile" => [
						"id"          => $account->id,
						"nickname"    => $account->nickname,
						"nationality" => AppUtil::handleStringNull( $account->nationality ),
						"gender"      => $account->gender,
						"avatar"      => AppUtil::handleStringNull( $account->avatar ),
						"revision"    => $account->revision,
						"status"      => $account->status,
						"user_agent"  => $this->clientDevice['user-agent'],
						"push_token"  => $device->push_token
					],
				];
				$jwt_token = JWT::encode( $payload, $this->_apiConfig["jwt_key"] );

                $this->syncAccountToMongo([
                    "id" => $account->id,
                    "account_id" => $account->id,
                    "nickname" => $account->nickname,
                    "nationality" => AppUtil::handleStringNull($account->nationality),
                    "gender" => $account->gender,
                    "avatar" => AppUtil::handleStringNull($account->avatar),
                    "revision" => $account->revision,
                    "status" => $account->status,
                    "user_agent" => $this->clientDevice['user-agent'],
                    "push_token" => $device->push_token
                ]);
			}

			return $this->responseData( [ 'status' => true, 'data' => [ "Authorization" => $jwt_token ] ] );
		} else {
			return $this->responseData( [ 'error_code' => 600 ] );
		}
	}

	public function getFriendPendingCount() {
		if ( ! $this->request->is( "GET" ) ) {
			return $this->responseData( [ "error_code" => 100 ] );
		}
		$this->loadModel( "AccountFriends" );
		$count_friend = $this->AccountFriends
			->find()
			->select( [
				"AccountFriends.account_receive_id",
				"AccountFriends.account_action_id"
			] )
			->where( [
				"AccountFriends.account_action_id" => $this->authUser->id,
				"AccountFriends.status"            => AccountFriend::STATUS_PENDING,
				"AccountFriends.action_id !=" => $this->authUser->id
			] )->orWhere( [
				"AccountFriends.account_receive_id" => $this->authUser->id,
				"AccountFriends.status"             => AccountFriend::STATUS_PENDING,
				"AccountFriends.action_id !=" => $this->authUser->id
			] )->count();
		return $this->responseData(["status" => true, "data" => ["count" => $count_friend]]);
	}
}
