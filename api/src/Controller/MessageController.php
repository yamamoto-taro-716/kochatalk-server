<?php

namespace App\Controller;

use App\Utility\AppUtil;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Collection;
use Cake\Core\Configure;

/**
 * Message Controller
 */
class MessageController extends AppController
{
    /* @var Collection $Messages */
    private $Messages;

    public function initialize()
    {
        parent::initialize();
        $conn = new Client(env("MONGODB_URL", "mongodb://127.0.0.1:27017"));
        $this->Messages = $conn->selectDatabase(Configure::read("API.mongo_db"))->selectCollection('messages');
    }

    public function index()
    {

    }

    public function getMessages()
    {
        if (!$this->request->is("ajax")) {
            return $this->redirect(["action" => "index"]);
        }
        $dataGet = $this->request->getQuery();
        
        $limit = isset($dataGet["limit"]) ? intval($dataGet["limit"]) : 10;
        $conditions = ['send_id' => ['$gt' => 0]];
        if (isset($dataGet["last_id"])) {
            $conditions["_id"] = [
                '$lt' => new ObjectID($dataGet["last_id"])
            ];
        }
        
        if (isset($dataGet["id"]) && !empty($dataGet["id"])) {
            $conditions["send_id"] = intval($dataGet["id"]);
        }
        
        if (isset($dataGet["nickname"]) && trim($dataGet["nickname"]) != "") {
            $regex = new Regex("/^" . $dataGet['nickname'] . "/", "i");
            $conditions["Account.nickname"] = $regex;
        }
        
        if (isset($dataGet["gender"]) && count($dataGet["gender"]) > 0) {
            if (count($dataGet["gender"]) < 2) {
                foreach ($dataGet["gender"] as $value) {
                    $conditions["Account.gender"] = $value;
                }
            }
        }
        
        if (isset($dataGet["os"]) && count($dataGet["os"]) > 0) {
            if (count($dataGet["os"]) < 2) {
                foreach ($dataGet["os"] as $value) {
                    $conditions["Account.user_agent"] = $value;
                }
            }
        }
        
        if (isset($dataGet["status"]) && count($dataGet["status"]) > 0) {
            if (count($dataGet["status"]) < 4) {
                $status = [];
                foreach ($dataGet["status"] as $value) {
                    $status[] = intval($value);
                }
                $conditions['Account.status'] = [
                    '$in' => $status
                ];
            }
        }
        
        
        $messages = $this->Messages->aggregate([
            [
                '$lookup' => [
                    'from' => 'accounts',
                    'localField' => 'send_id',
                    'foreignField' => 'account_id',
                    'as' => 'Account'
                ],
            ],
            ['$unwind' => '$Account']
            ,
            [
                '$lookup' => [
                    'from' => 'accounts',
                    'localField' => 'receive_id',
                    'foreignField' => 'account_id',
                    'as' => 'AccountReceive'
                ],
            ],
            ['$unwind' => '$AccountReceive']
            ,
            [
                '$project' => [
                    'room_id' => 1,
                    'message' => 1,
                    'send_id' => 1,
                    'receive_id' => 1,
                    'type' => 1,
                    'created' => 1,
                    'Account' => [
                        'nickname' => 1,
                        'gender' => 1,
                        'nationality' => 1,
                        'user_agent' => 1,
                        'status' => 1
                    ],
                    'AccountReceive' => [
                        'nickname' => 1,
                        'nationality' => 1
                    ]
                ]
            ],
            [
                '$match' => $conditions
            ],
            ['$sort' => ['_id' => -1]],
            ['$limit' => $limit]
        ])->toArray();
        
        $dataReturn = [
            "messages" => [],
            "next_page" => ""
        ];
        $i = 0;
        foreach ($messages as $k => $message) {
            $dataReturn["messages"][] = [
                "id" => strval($message["_id"]),
                "room_id" => $message["room_id"],
                "account_id" => $message["send_id"],
                "receive_id" => $message["receive_id"],
                "nickname" => $message["Account"]["nickname"],
                "receiver" => $message["AccountReceive"]["nickname"],
                "nationality" => isset($message["Account"]["nationality"]) ? $message["Account"]["nationality"] : null,
                "nationality_receive" => isset($message["AccountReceive"]["nationality"]) ? $message["AccountReceive"]["nationality"] : null,
                "message" => $message["message"],
                "type" => $message["type"],
                "created" => $message["created"]->toDateTime()->format("Y-m-d H:i:s")
            ];
            if ((count($messages) - 1) == $i && count($messages) >= $limit) {
                $dataReturn["next_page"] = "last_id=" . strval($message["_id"]);
            }
            $i++;
        }
        $this->response = $this->response->withType("application/json")->withStringBody(json_encode($dataReturn));
        return $this->response;
    }
    
    public function getSentReceive() {
        if (!$this->request->is("ajax")) {
            return $this->redirect(["action" => "index"]);
        }
        $dataGet = $this->request->getQuery();     
        $messages = $this->Messages->aggregate([
            [
                '$addFields' => [
                    'fmt_created' => [
                        '$dateToString' => [
                            'format' => '%Y%m%d%H%M%S',
                            'date' => '$created'
                        ]
                    ]
                ],
            ],
            [
                '$lookup' => [
                    'from' => 'accounts',
                    'localField' => 'send_id',
                    'foreignField' => 'account_id',
                    'as' => 'Account'
                ],
            ],
            ['$unwind' => '$Account']
            ,
            [
                '$lookup' => [
                    'from' => 'accounts',
                    'localField' => 'receive_id',
                    'foreignField' => 'account_id',
                    'as' => 'AccountReceive'
                ],
            ],
            ['$unwind' => '$AccountReceive']
            ,
            [
                '$project' => [
                    'message' => 1,
                    'send_id' => 1,
                    'type' => 1,
                    'created' => 1,
                    'fmt_created' => 1,
                    'Account' => [
                        'nickname' => 1
                    ],
                    'AccountReceive' => [
                        'nickname' => 1
                    ]
                ]
            ],
            [
                '$match' => [
                    'send_id' => intval($dataGet["send_id"]),
                    'fmt_created' => $dataGet["created"],
                ]            
            ]
        ])->toArray();
        $dataReturn = [
            "messages" => []
        ];
        $i = 0;
        foreach ($messages as $message) {
            $dataReturn["messages"][] = [
                "message" => $message["message"],
                "nickname" => $message["Account"]["nickname"],
                "receiver" => $message["AccountReceive"]["nickname"],
                "type" => $message["type"],
                "created" => $message["created"]
            ];
            $i++;
        }
        
        $this->response = $this->response->withType("application/json")->withStringBody(json_encode($dataReturn));
        return $this->response;
    }
    public function getMessagesHistory() {
        if (!$this->request->is("ajax")) {
            return $this->redirect(["action" => "index"]);
        }
        $dataGet = $this->request->getQuery();

        $limit = isset($dataGet["limit"]) ? intval($dataGet["limit"]) : 10;

        $room_id = $dataGet["room_id"];
        $conditions = [
            'room_id' => $room_id
        ];
        if (isset($dataGet["last_id"]) && $dataGet["last_id"]) {
            $conditions['_id'] = [
                '$lt' => new ObjectID($dataGet["last_id"])
            ];
        }
        $messages = $this->Messages->find($conditions, [
            'projection' => [
                '_id' => 1,
                'type' => 1,
                'message' => 1,
                'send_id' => 1,
                'receive_id' => 1,
                'created' => 1
            ],
            'limit' => $limit,
            'sort' => [
                '_id' => -1,
            ]
        ])->toArray();

        $dataReturn = [
            "messages" => [],
            "next_page" => ""
        ];
        $i = 0;
        foreach ($messages as $k => $message) {
            $dataReturn["messages"][] = [
                "id" => strval($message["_id"]),
                "type" => $message["type"],
                "message" => AppUtil::handleStringNull($message["message"]),
                "send_id" => $message["send_id"],
                "receive_id" => $message["receive_id"],
                "created" => $message["created"]->toDateTime()->format("Y-m-d H:i:s")
            ];
            if ((count($messages) - 1) == $i && count($messages) >= $limit) {
                $dataReturn["next_page"] = "last_id=" . strval($message["_id"]);
            }
            $i++;
        }
        $this->response = $this->response->withType("application/json")->withStringBody(json_encode($dataReturn));
        return $this->response;
    }

    public function delete()
    {
        if (!$this->request->is("ajax")) {
            return $this->redirect(["action" => "index"]);
        }
        $dataGet = $this->request->getQuery();
        $del = $this->Messages->deleteOne(['_id' => new ObjectID($dataGet['id'])]);
        $dataReturn["status"] = false;
        if ($del) {
            $dataReturn["status"] = true;
        }
        $this->response = $this->response->withType("application/json")->withStringBody(json_encode($dataReturn));
        return $this->response;
    }
}
