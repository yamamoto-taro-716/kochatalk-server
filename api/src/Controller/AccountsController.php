<?php

namespace App\Controller;

use App\Model\Entity\Account;
use App\Utility\AppUtil;
use Cake\Core\Configure;
use Cake\I18n\Time;
use MongoDB\Client;
use MongoDB\Collection;

/**
 * Accounts Controller
 *
 * @property \App\Model\Table\AccountsTable $Accounts
 *
 * @method \App\Model\Entity\Account[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AccountsController extends AppController
{

    /* @var Collection $Acc */
    private $Acc;

    public function initialize()
    {
        parent::initialize();
        $conn = new Client(env("MONGODB_URL", "mongodb://127.0.0.1:27017"));
        $this->Acc = $conn->selectDatabase(Configure::read("API.mongo_db"))->selectCollection('accounts');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $conditions = [];
        $dataGet = $this->request->getQuery();

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
        
        if (isset($dataGet["avatar_status"]) && count($dataGet["avatar_status"]) > 0) {
            foreach ($dataGet["avatar_status"] as $value) {
                $conditions["OR"][] = [
                    "Accounts.avatar_status" => $value
                ];
            }
        }
        
        if (isset($dataGet["has_avatar"]) && count($dataGet["has_avatar"]) > 0) {
            if (count($dataGet["has_avatar"]) < 2) {
                foreach ($dataGet["has_avatar"] as $value) {
                    if ($value) {
                        $conditions[] = "Accounts.avatar IS NOT NULL";
                    } else {
                        $conditions[] = "Accounts.avatar IS NULL";
                    }
                }
            }
        }

        if (isset($dataGet["created_from"]) && $dataGet["created_from"]) {
            $conditions["Accounts.created >="] = Time::createFromFormat("Y-m-d H:i", $dataGet["created_from"])->modify("-9 hours")->format("Y-m-d H:i:s");
        }

        if (isset($dataGet["created_to"]) && $dataGet["created_to"]) {
            $conditions["Accounts.created <="] = Time::createFromFormat("Y-m-d H:i", $dataGet["created_to"])->modify("-9 hours")->format("Y-m-d H:i:s");
        }

        if (isset($dataGet["login_from"]) && $dataGet["login_from"]) {
            $conditions["Devices.last_access >="] = Time::createFromFormat("Y-m-d H:i", $dataGet["login_from"])->modify("-9 hours")->format("Y-m-d H:i:s");
        }

        if (isset($dataGet["login_to"]) && $dataGet["login_to"]) {
            $conditions["Devices.last_access <="] = Time::createFromFormat("Y-m-d H:i", $dataGet["login_to"])->modify("-9 hours")->format("Y-m-d H:i:s");
        }

        $this->paginate = [
            'contain' => [
                'Devices'
            ],
            'conditions' => $conditions,
            'order' => [
                'Accounts.created' => "DESC"
            ],
            'limit' => 50
        ];
        $accounts = $this->paginate($this->Accounts);

        $this->set(compact('accounts'));
    }

    /**
     * View method
     *
     * @param string|null $id Account id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $account = $this->Accounts->get($id, [
            'contain' => ['Devices']
        ]);
        if ($this->request->is(["POST", "PUT"])) {
            $account = $this->Accounts->patchEntity($account, $this->request->getData());
            if ($this->Accounts->save($account)) {
                $this->Acc->updateOne(
                    [
                        'account_id' => $account->id
                    ],
                    ['$set' => ['status' => $account->status]]
                );
                $this->Flash->success("Success");
            }
            return $this->redirect(["action" => "view", $account->id]);
        }

        $this->set('account', $account);
    }

    public function getTimeline()
    {
        if (!$this->request->is("ajax")) {
            return $this->redirect(["action" => "index"]);
        }
        $dataGet = $this->request->getQuery();
        $id = isset($dataGet["id"]) ? $dataGet["id"] : null;
        $limit = isset($dataGet["limit"]) ? intval($dataGet["limit"]) : 10;
        $conditions["Posts.account_id"] = $id;
        if (isset($dataGet["last_id"])) {
            $conditions["Posts.id <"] = $dataGet["last_id"];
        }
        $posts = $this->Accounts->Posts->find()->where($conditions)->order(["Posts.id" => "DESC"])->limit($limit)->all();
        $dataReturn = [
            "posts" => [],
            "next_page" => ""
        ];
        foreach ($posts as $k => $item) {
            $images = [];
            if (!empty($item->images)) {
                $images = explode("|", $item->images);
            }
            $dataReturn["posts"][] = [
                "id" => $item->id,
                "content" => AppUtil::handleStringNull($item->content),
                "images" => $images,
                "date" => $item->modified->timezone(Configure::read("COMMON.timezone"))->format("Y-m-d H:i:s"),
            ];
            if ((count($posts) - 1) == $k && count($posts) >= $limit) {
                $dataReturn["next_page"] = "last_id=$item->id&last_date=" . $item->modified->timestamp;
            }
        }
        $this->response = $this->response->withType("application/json")->withStringBody(json_encode($dataReturn));
        return $this->response;
    }

    public function delPost()
    {
        if (!$this->request->is("ajax")) {
            return $this->redirect(["action" => "index"]);
        }
        $dataGet = $this->request->getQuery();
        $id = isset($dataGet["id"]) ? $dataGet["id"] : null;
        $post = $this->Accounts->Posts->find()->where(["Posts.id" => $id])->first();
        $dataReturn["status"] = false;
        if ($post) {
            if ($this->Accounts->Posts->delete($post)) {
                $dataReturn["status"] = true;
            }
        }
        $this->response = $this->response->withType("application/json")->withStringBody(json_encode($dataReturn));
        return $this->response;
    }

	public function delete($id) {
		$account = $this->Accounts->get($id);
		if ($this->Accounts->delete($account)) {
			$this->Flash->success('Account was deleted');
		} else {
			$this->Flash->error('Cant delete account !');
		}
		return $this->redirect(['action' => 'index']);
	}
	
	public function confirmAvatar($id) {
	    $account = $this->Accounts->get($id);
	    $account->avatar_status = Account::AVATAR_STATUS_CONFIRM;
	    if ($this->Accounts->save($account)) {
	        $this->Flash->success('Confirm avatar successfull');
	    } else {
	        $this->Flash->error('Can\'t confirm avatar !');
	    }
	    return $this->redirect(['action' => 'index']);
	}
	
	public function removeAvatar($id) {
	    $account = $this->Accounts->get($id);
	    $account->avatar = NULL;
	    $account->avatar_status = NULL;
	    if ($this->Accounts->save($account)) {
	        $this->Flash->success('Remove avatar successfull');
	    } else {
	        $this->Flash->error('Can\'t remove avatar !');
	    }
	    return $this->redirect(['action' => 'index']);
	}
    
    /**
     * View method
     *
     * @param string|null $id Account id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $account = $this->Accounts->get($id, [
            'contain' => ['Devices']
        ]);
        if ($this->request->is(["POST", "PUT"])) {
            $account = $this->Accounts->patchEntity($account, $this->request->getData());
            if ($this->Accounts->save($account)) {
                $this->Acc->updateOne(
                    [
                        'account_id' => $account->id
                    ],
                    ['$set' => ['status' => $account->status]]
                );
                $this->Flash->success("Success");
            }
            return $this->redirect(["action" => "view", $account->id]);
        }

        $this->set('account', $account);
    }
}
