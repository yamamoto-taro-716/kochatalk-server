<?php

namespace App\Controller;

use App\Utility\AppUtil;
use Cake\Core\Configure;
use Firebase\JWT\JWT;

use App\Model\Table\AccountsTable;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use MongoDB\Collection;

/**
 * Contact Controller
 *
 * @property AccountsTable $Accounts
 */
class ContactController extends AppController
{
    /* @var Collection $Contacts */
    private $Contacts;
    /* @var Collection $ContactMessages */
    private $ContactMessages;

    public function initialize()
    {
        parent::initialize();
        $conn = new Client(env("MONGODB_URL", "mongodb://127.0.0.1:27017"));
        $this->Contacts = $conn->selectDatabase(Configure::read("API.mongo_db"))->selectCollection("contacts");
        $this->ContactMessages = $conn->selectDatabase(Configure::read("API.mongo_db"))->selectCollection("contact_messages");
    }

    public function index()
    {
        $contacts = $this->Contacts->find(
            [
                'is_reply' => ['$gt' => -1]
            ],
            [
                'sort' => ['is_reply' => 1, 'created' => -1]
            ]
        )->toArray();
        $this->set(compact("contacts"));
    }

    public function conversation($account_id = null)
    {
        $this->loadModel("Accounts");
        $account = $this->Accounts->find()
            ->contain(["Devices"])
            ->where(["Accounts.id" => $account_id])->first();
        if (!$account) {
            $this->Flash->error(__("Not found account"));
            return $this->redirect(["action" => "index"]);
        }

        $payload = [
            "sub" => 0,
            "profile" => [
                "id" => 0,
                "nickname" => "Admin",
                "user_agent" => "web",
                "push_token" => ""
            ],
        ];
        $jwt_token = JWT::encode($payload, Configure::read("API.jwt_key"));
        $this->set(compact("jwt_token", "account"));
    }

    public function getContactHistory()
    {
        if (!$this->request->is('AJAX')) {
            return $this->redirect(["action" => "index"]);
        }
        $limit = $this->request->getQuery("limit") ? intval($this->request->getQuery("limit")) : 10;
        $account_id = intval($this->request->getQuery('account_id'));
        $conditions = [
            'account_id' => $account_id
        ];
        if ($this->request->getQuery('last_id')) {
            $conditions['_id'] = [
                '$lt' => new ObjectID($this->request->getQuery('last_id'))
            ];
        }
        $messages = $this->ContactMessages->find($conditions, [
            'projection' => [
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
}
