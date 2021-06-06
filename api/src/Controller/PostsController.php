<?php
namespace App\Controller;

use App\Utility\AppUtil;
use Cake\Core\Configure;
use Cake\I18n\Time;

/**
 * Posts Controller
 *
 * @property \App\Model\Table\PostsTable $Posts
 *
 * @method \App\Model\Entity\Post[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class PostsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {

    }

    public function getTimeline() {
        if (!$this->request->is("ajax")) {
            return $this->redirect(["action" => "index"]);
        }
        $dataGet = $this->request->getQuery();
        $limit = isset($dataGet["limit"]) ? intval($dataGet["limit"]) : 10;
        $conditions = [];
        if (isset($dataGet["last_id"])) {
            $conditions["Posts.id <"] = $dataGet["last_id"];
        }

        if  (isset($dataGet["id"]) && !empty($dataGet["id"])) {
            $conditions["Posts.account_id"] = $dataGet["id"];
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


        $posts = $this->Posts->find()
            ->contain([
                "Accounts" => function ($q) {
                    return $q->contain(["Devices"])->select(["Accounts.id", "Accounts.nickname", "Accounts.nationality"])->contain(["Devices"]);
                }
            ])
            ->where($conditions)->order(["Posts.id" => "DESC"])->limit($limit)->all();
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
                "account_id" => $item->account_id,
                "nickname" => $item->account->nickname,
                "nationality" => $item->account->nationality,
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

    /**
     * Delete method
     *
     * @param string|null $id Post id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $post = $this->Posts->get($id);
        if ($this->Posts->delete($post)) {
            $this->Flash->success(__('The post has been deleted.'));
        } else {
            $this->Flash->error(__('The post could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
