<?php

namespace App\Controller\Api;

use App\Model\Entity\Account;
use App\Model\Entity\AccountBlock;
use App\Model\Entity\AccountFriend;
use App\Model\Entity\AccountReport;
use App\Model\Entity\RandomConfig;
use App\Utility\AppUtil;
use Cake\Core\Configure;
use Cake\I18n\Time;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;

/**
 * Talk Controller
 *
 * @property \App\Model\Table\AccountBlocksTable $AccountBlocks
 * @property \App\Model\Table\AccountReportsTable $AccountReports
 * @property \App\Model\Table\AccountFriendsTable $AccountFriends
 */
class TalkController extends ApiAppController {

	/* @var Collection $Messages */
	private $Messages;
	/* @var Collection $ContactMessages */
	private $ContactMessages;

    private $AccountsMongo;

	public function initialize() {
		parent::initialize();
		$conn                  = new Client( env( "MONGODB_URL", "mongodb://127.0.0.1:27017" ) );
		$this->Messages        = $conn->selectDatabase( Configure::read("API.mongo_db") )->selectCollection( 'messages' );
		$this->ContactMessages = $conn->selectDatabase( Configure::read("API.mongo_db") )->selectCollection( 'contact_messages' );
	}

	public function joinChat() {
		if ( ! $this->request->is( "GET" ) ) {
			return $this->responseData( [ "error_code" => 100 ] );
		}
		$dataGet = $this->request->getQuery();
		if ( ! isset( $dataGet["id"] ) || $dataGet["id"] == $this->authUser->id ) {
			return $this->responseData( [ "error_code" => 101 ] );
		}

		$this->loadModel( "Accounts" );
		/* @var Account $account */
		$account = $this->Accounts->find()
		                          ->select( [
			                          "Accounts.id",
			                          "Accounts.nickname",
			                          "Accounts.avatar",
			                          "Accounts.gender",
			                          "Accounts.nationality",
			                          "Accounts.in_group"
		                          ] )
		                          ->where( [ "Accounts.id" => $dataGet["id"] ] )
		                          ->first();
		if ( ! $account ) {
			return $this->responseData( [ "error_code" => 404, "ext_msg" => "Account" ] );
		}

		$this->Messages->updateMany(
			[
				'room_id'    => AppUtil::genChatRoomId( $this->authUser->id, $account->id ),
				'receive_id' => $this->authUser->id
			],
			[ '$set' => [ 'is_read' => 1 ] ]
		);

		$this->loadModel( "AccountBlocks" );
		$this->loadModel( "AccountReports" );
		$this->loadModel( "AccountFriends" );
		$is_blocked         = $this->AccountBlocks->find()
		                                          ->select( [ "AccountBlocks.id" ] )
		                                          ->where( [
			                                          "AccountBlocks.account_action_id"  => $this->authUser->id,
			                                          "AccountBlocks.account_receive_id" => $dataGet["id"],
			                                          "AccountBlocks.status"             => AccountBlock::STATUS_BLOCKED
		                                          ] )->first();
		$is_friend_blocked  = $this->AccountBlocks->find()
		                                          ->select( [ "AccountBlocks.id" ] )
		                                          ->where( [
			                                          "AccountBlocks.account_action_id"  => $dataGet["id"],
			                                          "AccountBlocks.account_receive_id" => $this->authUser->id,
			                                          "AccountBlocks.status"             => AccountBlock::STATUS_BLOCKED
		                                          ] )->first();
		$is_reported        = $this->AccountReports->find()
		                                           ->select( [ "AccountReports.id" ] )
		                                           ->where( [
			                                           "AccountReports.account_action_id"  => $this->authUser->id,
			                                           "AccountReports.account_receive_id" => $dataGet["id"],
			                                           "AccountReports.status"             => AccountReport::STATUS_REPORTED
		                                           ] )->first();
		$is_friend_reported = $this->AccountReports->find()
		                                           ->select( [ "AccountReports.id" ] )
		                                           ->where( [
			                                           "AccountReports.account_action_id"  => $dataGet["id"],
			                                           "AccountReports.account_receive_id" => $this->authUser->id,
			                                           "AccountReports.status"             => AccountReport::STATUS_REPORTED
		                                           ] )->first();
		$conds              = $this->authUser->id > $dataGet["id"] ? [
			"AccountFriends.account_action_id"  => $dataGet["id"],
			"AccountFriends.account_receive_id" => $this->authUser->id
		] : [
			"AccountFriends.account_receive_id" => $dataGet["id"],
			"AccountFriends.account_action_id"  => $this->authUser->id
		];
		$is_friend          = $this->AccountFriends->find()
		                                           ->select( [ "AccountFriends.id" ] )
		                                           ->where( [
			                                           $conds,
			                                           "AccountFriends.status" => AccountFriend::STATUS_FRIEND
		                                           ] )->first();

		$dataReturn = [
			"id"          => $account->id,
			"avatar"      => AppUtil::handleStringNull( $account->avatar ),
			"nickname"    => AppUtil::handleStringNull( $account->nickname ),
			"gender"      => AppUtil::handleNumberNull( $account->gender, "int" ),
			"nationality" => AppUtil::handleStringNull( $account->nationality ),
			"in_group"    => AppUtil::handleNumberNull( $account->in_group, "int" ),
			"state"       => [
				"is_blocked"         => $is_blocked ? AccountBlock::STATUS_BLOCKED : AccountBlock::STATUS_NORMAL,
				"is_friend_blocked"  => $is_friend_blocked ? AccountBlock::STATUS_BLOCKED : AccountBlock::STATUS_NORMAL,
				"is_reported"        => $is_reported ? AccountReport::STATUS_REPORTED : AccountReport::STATUS_NORMAL,
				"is_friend_reported" => $is_friend_reported ? AccountReport::STATUS_REPORTED : AccountReport::STATUS_NORMAL,
				"is_friend"          => $is_friend ? AccountFriend::STATUS_FRIEND : AccountFriend::STATUS_NORMAL,
			]
		];

		return $this->responseData( [ "status" => true, "data" => $dataReturn ] );
	}

	public function getMessagesHistory() {
		if ( ! $this->request->is( "GET" ) ) {
			return $this->responseData( [ "error_code" => 100 ] );
		}
		$dataGet = $this->request->getQuery();
		$limit   = isset( $dataGet["limit"] ) ? intval( $dataGet["limit"] ) : 10;

		if ( ! isset( $dataGet["friend_id"] ) ) {
			return $this->responseData( [ "error_code" => 101 ] );
		}

		$friend_id = intval( $dataGet["friend_id"] );

		$roomId     = "#" . ( $this->authUser->id > $friend_id ? $friend_id . "-" . $this->authUser->id : $this->authUser->id . "-" . $friend_id );
		$conditions = [
			'room_id' => $roomId,
		];
		if ( isset( $dataGet["last_id"] ) ) {
			$conditions['_id'] = [
				'$lt' => new ObjectID( $dataGet["last_id"] )
			];
		}
		$messages = $this->Messages->find( $conditions, [
			'projection' => [
				'message'    => 1,
				'type'       => 1,
				'send_id'    => 1,
				'receive_id' => 1,
				'created'    => 1
			],
			'limit'      => $limit,
			'sort'       => [
				'_id' => - 1,
			]
		] )->toArray();

		$dataReturn = [
			"messages"  => [],
			"next_page" => ""
		];
		$i          = 0;
		foreach ( $messages as $k => $message ) {
			$dataReturn["messages"][] = [
				"id"         => strval( $message["_id"] ),
				"message"    => AppUtil::handleStringNull( $message["message"] ),
				"send_id"    => $message["send_id"],
				"receive_id" => $message["receive_id"],
				"type"       => AppUtil::handleNumberNull( $message["type"], "int" ),
				"created"    => $message["created"]->toDateTime()->format( "Y-m-d H:i:s" )
			];
			if ( ( count( $messages ) - 1 ) == $i && count( $messages ) >= $limit ) {
				$dataReturn["next_page"] = "last_id=" . strval( $message["_id"] );
			}
			$i ++;
		}

		return $this->responseData( [ "status" => true, "data" => $dataReturn ] );
	}

	public function getContactHistory() {
		if ( ! $this->request->is( "GET" ) ) {
			return $this->responseData( [ "error_code" => 100 ] );
		}
		$dataGet = $this->request->getQuery();
		$limit   = isset( $dataGet["limit"] ) ? intval( $dataGet["limit"] ) : 10;

		$conditions = [
			'account_id' => $this->authUser->id,
		];
		if ( isset( $dataGet["last_id"] ) ) {
			$conditions['_id'] = [
				'$lt' => new ObjectID( $dataGet["last_id"] )
			];
		}
		$messages = $this->ContactMessages->find( $conditions, [
			'projection' => [
				'message'    => 1,
				'send_id'    => 1,
				'receive_id' => 1,
				'created'    => 1
			],
			'limit'      => $limit,
			'sort'       => [
				'_id' => - 1,
			]
		] )->toArray();

		$dataReturn = [
			"messages"  => [],
			"next_page" => ""
		];
		$i          = 0;
		foreach ( $messages as $k => $message ) {
			$dataReturn["messages"][] = [
				"id"         => strval( $message["_id"] ),
				"message"    => AppUtil::handleStringNull( $message["message"] ),
				"send_id"    => $message["send_id"],
				"receive_id" => $message["receive_id"],
				"created"    => $message["created"]->toDateTime()->format( "Y-m-d H:i:s" )
			];
			if ( ( count( $messages ) - 1 ) == $i && count( $messages ) >= $limit ) {
				$dataReturn["next_page"] = "last_id=" . strval( $message["_id"] );
			}
			$i ++;
		}

		return $this->responseData( [ "status" => true, "data" => $dataReturn ] );
	}

	public function sendRandom() {
		if ( ! $this->request->is( "POST" ) ) {
			return $this->responseData( [ "error_code" => 100 ] );
		}
		$dataPost = $this->request->getData();

		if ( ! isset( $dataPost["message"] ) || ! isset( $dataPost["gender"] ) || ! isset( $dataPost["nationality"] ) ) {
			return $this->responseData( [ "error_code" => 101 ] );
		}
		if ($dataPost["nationality"] != '00') {
			$conditions = [ "Accounts.nationality" => $dataPost["nationality"] ];
		}

		if ( intval( $dataPost["gender"] ) > 0 ) {
			$conditions["Accounts.gender"] = $dataPost["gender"];
		}

		$this->loadModel( "AccountFriends" );
		$tmp_friends = $this->AccountFriends
			->find()
			->select( [
				"AccountFriends.account_receive_id",
				"AccountFriends.account_action_id"
			] )
			->where( [
				"AccountFriends.account_action_id" => $this->authUser->id,
				"AccountFriends.status"            => AccountFriend::STATUS_FRIEND
			] )->orWhere( [
				"AccountFriends.account_receive_id" => $this->authUser->id,
				"AccountFriends.status"             => AccountFriend::STATUS_FRIEND
			] );
		$friends     = [ $this->authUser->id ];
		foreach ( $tmp_friends as $tmp_friend ) {
			$friends[] = $tmp_friend->account_action_id == $this->authUser->id ? $tmp_friend->account_receive_id : $tmp_friend->account_action_id;
		}

//		$this->loadModel( "AccountBlocks" );
//		$tmp_blocks = $this->AccountBlocks
//			->find()
//			->select( [
//				"AccountBlocks.account_receive_id",
//				"AccountBlocks.account_action_id"
//			] )
//			->where( [
//				"AccountBlocks.account_action_id" => $this->authUser->id,
//				"AccountBlocks.status"            => AccountBlock::STATUS_BLOCKED
//			] )->orWhere( [
//				"AccountBlocks.account_receive_id" => $this->authUser->id,
//				"AccountBlocks.status"             => AccountBlock::STATUS_BLOCKED
//			] );
//		$blocks     = [];
//		foreach ( $tmp_blocks as $tmp_block ) {
//			$blocks[] = $tmp_block->account_action_id == $this->authUser->id ? $tmp_block->account_receive_id : $tmp_block->account_action_id;
//		}
//		$this->loadModel( "AccountReports" );
//		$tmp_reports = $this->AccountReports
//			->find()
//			->select( [
//				"AccountReports.account_receive_id",
//				"AccountReports.account_action_id"
//			] )
//			->where( [
//				"AccountReports.account_action_id" => $this->authUser->id,
//				"AccountReports.status"            => AccountReport::STATUS_REPORTED
//			] )->orWhere( [
//				"AccountReports.account_receive_id" => $this->authUser->id,
//				"AccountReports.status"             => AccountReport::STATUS_REPORTED
//			] );
//		$reports     = [];
//		foreach ( $tmp_reports as $tmp_report ) {
//			$reports[] = $tmp_report->account_action_id == $this->authUser->id ? $tmp_report->account_receive_id : $tmp_report->account_action_id;
//		}
        $blocks = $reports = [];
		$arr_ids = array_values( array_unique( array_merge( $friends, $blocks, $reports ), SORT_NUMERIC ) );

		if ( $arr_ids ) {
			$conditions["Accounts.id NOT IN"] = $arr_ids;
		}

		$conditions["LENGTH(Devices.push_token) >"] = 10;
		$conditions["Accounts.flg_push"]            = true;
		$conditions["Accounts.status"] = Account::STATUS_NORMAL;
		$conditions["Accounts.in_group"] = 1;

		$this->loadModel("RandomConfigs");
		$friendGender = intval( $dataPost["gender"] ) > 0 ? intval( $dataPost["gender"] ) : -1;
		$randomId = RandomConfig::getConfigId($this->authUser->gender, $friendGender);

		$randomConfig = $this->RandomConfigs->find()->where(["RandomConfigs.id" => $randomId])->first();
		if (!$randomConfig) {
			return $this->responseData(["error_code" => 404, "ext_msg" => "RandomConfig"]);
		}
		if ($randomConfig->created_type != RandomConfig::TYPE_ALL) {
			if ($randomConfig->created_type == RandomConfig::TYPE_SINCE) {
				$conditions["DATE(Accounts.created) <"] = $randomConfig->created_value;
			} else {
				$dt = Time::now()->modify("-" . intval($randomConfig->created_value))->format("Y-m-d");
				$conditions["DATE(Accounts.created) <"] = $dt;
			}
		}
		if ($randomConfig->access_type != RandomConfig::TYPE_ALL) {
			if ($randomConfig->access_type == RandomConfig::TYPE_SINCE) {
				$conditions["DATE(Devices.last_access) >="] = $randomConfig->access_value;
			} else {
				$dt = Time::now()->modify("-" . intval($randomConfig->access_value))->format("Y-m-d");
				$conditions["DATE(Devices.last_access) >="] = $dt;
			}
		}


		$accounts = $this->Accounts
			->find()
			->select( [
				"Accounts.id",
				"Devices.push_token",
                "Devices.user_agent"
			] )
			->contain( [ "Devices" ] )
			->where( $conditions )
			->limit( intval($randomConfig->random_limit) )
			->order( 'rand()' );

		$notification = [
			"title" => $this->authUser->nickname,
			"body"  => "New request friend"
		];

		$data = [
			"type"    => "friend",
			"message" => $dataPost["message"],
			"created" => Time::now()->format( "Y-m-d H:i:s" )
		];

        $dataAndroid = [
            'content' => array_merge($data, [
                "title" => $this->authUser->nickname,
                "message" => $dataPost["message"],
            ])
        ];

		$conn = $this->Accounts->getConnection();
		$conn->begin();
		try {
			$targetAndroid = [];
			$targetIos =[];
			$logMessage = [];
			foreach ( $accounts as $account ) {
                if ($account->device) {
                    if ($account->device->user_agent == 'android') {
                        $targetAndroid[] = $account->device->push_token;
                    } else if ($account->device->user_agent == 'ios') {
                        $targetIos[] = $account->device->push_token;
                    }
                }


				$conds       = $this->authUser->id > $account->id ? [
					"AccountFriends.account_action_id"  => $account->id,
					"AccountFriends.account_receive_id" => $this->authUser->id
				] : [
					"AccountFriends.account_receive_id" => $account->id,
					"AccountFriends.account_action_id"  => $this->authUser->id
				];
				$entity      = $this->AccountFriends->find()
				                                    ->where( $conds )->first();
				if ( $entity ) {
					$entity->message   = $dataPost["message"];
					$entity->status    = AccountFriend::STATUS_PENDING;
					$entity->action_id = $this->authUser->id;
				} else {
					$entity = $this->AccountFriends->newEntity( [
						"account_action_id"  => $this->authUser->id > $account->id ? $account->id : $this->authUser->id,
						"account_receive_id" => $account->id > $this->authUser->id ? $account->id : $this->authUser->id,
						"action_id"          => $this->authUser->id,
						"message"            => $dataPost["message"],
						"status"             => AccountFriend::STATUS_PENDING
					] );
				}
				$this->AccountFriends->save( $entity, [ "atomic" => false ] );
				$messageResult = $this->Messages->insertOne([
					'message' => $dataPost["message"],
					"is_read" => 0,
					"user_small_id_deleted" => 0,
					"user_big_id_deleted" => 0,
					"room_id" => "#" . ($this->authUser->id > $account->id ? $account->id . "-" . $this->authUser->id : $this->authUser->id . "-" . $account->id),
					"send_id" => $this->authUser->id,
					"receive_id" => $account->id,
					"type" => 1,
					'created' => new UTCDateTime()
				]);
                $logMessage[] = [
                    'room_id' => "#" . ($this->authUser->id > $account->id ? $account->id . "-" . $this->authUser->id : $this->authUser->id . "-" . $account->id),
                    'receive_id' => $account->id,
                    'status' => $messageResult->getInsertedId()
                ];

			}
            $resultIos = $resultAndroid = null;
            if (!empty($targetIos)) {
                $resultIos = AppUtil::sendFCMMessage($notification, $data, $targetIos);
            }
            if (!empty($targetAndroid)) {
                $resultAndroid = AppUtil::sendFCMMessage([], $dataAndroid, $targetAndroid, 'android');
            }

            $conn->commit();
            return $this->responseData(["status" => true, 'log' => $logMessage]);
		} catch ( \Exception $ex ) {
			$conn->rollback();
			return $this->responseData( [ "error_code" => 600 ] );
		}
	}

	public function sendChatImage() {
		if ( ! $this->request->is( 'post' ) ) {
			return $this->responseData( [ "error_code" => 100 ] );
		}
		$dataPost = $this->request->getData();
		if ( ! isset( $dataPost["image"] ) || ! isset( $dataPost["friend_id"] ) ) {
			return $this->responseData( [ "error_code" => 101 ] );
		}

		$room_id = $this->authUser->id > $dataPost["friend_id"] ? $dataPost["friend_id"] . "-" . $this->authUser->id : $this->authUser->id . "-" . $dataPost["friend_id"];

		$image = AppUtil::uploadImageChat( $dataPost["image"], $room_id );
		if ( $image ) {
			$path = Configure::read( "App.fullBaseUrl" ) . "/" . Configure::read("App.subDomain") . "/webroot/upload/messages/" . $room_id . "/" . $image;
            $message = $this->Messages->insertOne([
                'message' => $path,
                "is_read" => 0,
                "user_small_id_deleted" => 0,
                "user_big_id_deleted" => 0,
                "room_id" => "#" . $room_id,
                "send_id" => $this->authUser->id,
                "receive_id" => $dataPost["friend_id"],
                "type" => 2,
                'created' => new UTCDateTime()
            ]);
            $conditions = [
                '_id' => $message->getInsertedId(),
            ];
            $result = $this->Messages->find($conditions, [
                'projection' => [
                    '_id'        => 1,
                    'message'    => 1,
                    'room_id'    => 1,
                    'type'       => 1,
                    'send_id'    => 1,
                    'receive_id' => 1,
                    'is_read'    => 1,
                    'created'    => 1
                ],
                'limit'  => 1,
                'sort'  => [
                    '_id' => - 1,
                ]
            ])->toArray();
            if (!empty($result)) {
                $dataReturn = [
                    "id" => strval($result[0]["_id"]),
                    "type" => $result[0]["type"],
                    "is_read" => $result[0]["is_read"],
                    "message" => AppUtil::handleStringNull($result[0]["message"]),
                    "room_id" => $result[0]["room_id"],
                    "send_id" => $result[0]["send_id"],
                    "receive_id" => $result[0]["receive_id"],
                    "created" => $result[0]["created"]->toDateTime()->format("Y-m-d H:i:s")
                ];
            }
                      
			return $this->responseData( [ "status" => true, "data" => $dataReturn ] );
		} else {
			return $this->responseData( [ "error_code" => 803 ] );
		}
	}
}