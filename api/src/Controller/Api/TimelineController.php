<?php

namespace App\Controller\Api;

use App\Model\Entity\AccountBlock;
use App\Model\Entity\AccountReport;
use App\Model\Entity\Post;
use App\Model\Table\AccountBlocksTable;
use App\Model\Table\PostsTable;
use App\Utility\AppUtil;
use Cake\I18n\Time;


/**
 * Timeline Controller
 *
 * @property PostsTable $Posts
 * @property AccountBlocksTable $AccountBlocks
 * @property AccountReport $AccountReports
 */
class TimelineController extends ApiAppController
{
    public function initialize()
    {
        parent::initialize();
        $this->authAllow(["getListByAccount"]);

        $this->loadModel("Posts");
    }

    public function getList()
    {
        if (!$this->request->is("GET")) {
            return $this->responseData(["error_code" => 100]);
        }
        $dataGet = $this->request->getQuery();
        $limit = isset($dataGet["limit"]) ? intval($dataGet["limit"]) : 10;

        $conditions = [
            "Accounts.in_group" => 1
        ];
        $this->loadModel("AccountBlocks");
        $tmp_blocks = $this->AccountBlocks->find()->select(["AccountBlocks.account_receive_id"])->where(["AccountBlocks.account_action_id" => $this->authUser->id, "AccountBlocks.status" => AccountBlock::STATUS_BLOCKED]);
        $blocks = [];
        foreach ($tmp_blocks as $tmp_block) {
            $blocks[] = $tmp_block->account_receive_id;
        }
        $this->loadModel("AccountReports");
        $tmp_reports = $this->AccountReports->find()->select(["AccountReports.account_receive_id"])->where(["AccountReports.account_action_id" => $this->authUser->id, "AccountReports.status" => AccountReport::STATUS_REPORTED]);
        $reports = [];
        foreach ($tmp_reports as $tmp_report) {
            $reports[] = $tmp_report->account_receive_id;
        }
        $arr_ids = array_values(array_unique(array_merge($blocks, $reports), SORT_NUMERIC));
        if (count($arr_ids) > 0) {
            $conditions["Posts.account_id NOT IN"] = $arr_ids;
        }
        if (isset($dataGet["last_id"])) {
            $conditions["Posts.id <"] = $dataGet["last_id"];
        }

        if (isset($dataGet["nickname"])) {
            $conditions["Accounts.nickname LIKE"] = "%" . $dataGet["nickname"] . "%";
        }

        if (isset($dataGet["nationality"])) {
            $conditions["Accounts.nationality"] = $dataGet["nationality"];
        }

        if (isset($dataGet["gender"])) {
            $conditions["Accounts.gender"] = $dataGet["gender"];
        }

        if (isset($dataGet["age_from"]) && isset($dataGet["age_to"])) {
            $conditions["Accounts.age >="] = $dataGet["age_from"];
            $conditions["Accounts.age <="] = $dataGet["age_to"];
        }

        if (isset($dataGet["marital_status"])) {
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

        /* @var Post[] $posts */
        $posts = $this->Posts->find()
            ->contain([
                "Accounts" => function ($q) {
                    return $q->select(["Accounts.id", "Accounts.nickname", "Accounts.gender", "Accounts.avatar", "Accounts.nationality"]);
                }
            ])
            ->where($conditions)
            ->order(["Posts.id" => "DESC"])
            ->limit($limit)->all();

        $dataReturn = [
            "posts" => [],
            "next_page" => ""
        ];
        foreach ($posts as $k => $post) {
            $images = [];
            if (!empty($post->images)) {
                $images = explode("|", $post->images);
            }
            $dataReturn["posts"][] = [
                "id" => $post->id,
                "content" => AppUtil::handleStringNull($post->content),
                "images" => $images,
                "revision" => AppUtil::handleNumberNull($post->revision, "int"),
                "date" => $post->modified->format("Y-m-d H:i:s"),
                "account" => [
                    "id" => $post->account_id,
                    "nickname" => AppUtil::handleStringNull($post->account->nickname),
                    "gender" => AppUtil::handleNumberNull($post->account->gender, "int"),
                    "avatar" => AppUtil::handleStringNull($post->account->avatar),
                    "nationality" => AppUtil::handleStringNull($post->account->nationality)
                ]
            ];
            if ((count($posts) - 1) == $k && count($posts) >= $limit) {
                $dataReturn["next_page"] = "last_id=$post->id&last_date=" . $post->modified->timestamp;
            }
        }
        return $this->responseData(["status" => true, "data" => $dataReturn]);
    }

    public function getListByAccount()
    {
        if (!$this->request->is("GET")) {
            return $this->responseData(["error_code" => 100]);
        }
        $dataGet = $this->request->getQuery();
        if (!isset($dataGet["id"])) {
            return $this->responseData(["error_code" => 101]);
        }
        $limit = isset($dataGet["limit"]) ? intval($dataGet["limit"]) : 10;

        $conditions["Posts.account_id"] = intval($dataGet["id"]);

        if (isset($dataGet["last_id"])) {
            $conditions["Posts.id <"] = $dataGet["last_id"];
        }

        /* @var Post[] $posts */
        $posts = $this->Posts->find()
            ->where($conditions)
            ->order(["Posts.id" => "DESC"])
            ->limit($limit)->all();

        $dataReturn = [
            "posts" => [],
            "next_page" => ""
        ];
        foreach ($posts as $k => $post) {
            $images = [];
            if (!empty($post->images)) {
                $images = explode("|", $post->images);
            }
            $dataReturn["posts"][] = [
                "id" => $post->id,
                "content" => AppUtil::handleStringNull($post->content),
                "images" => $images,
                "revision" => AppUtil::handleNumberNull($post->revision, "int"),
                "date" => $post->modified->format("Y-m-d H:i:s"),
            ];
            if ((count($posts) - 1) == $k && count($posts) >= $limit) {
                $dataReturn["next_page"] = "last_id=$post->id&last_date=" . $post->modified->timestamp;
            }
        }
        return $this->responseData(["status" => true, "data" => $dataReturn]);
    }

    public function delete()
    {
        if (!$this->request->is("GET")) {
            return $this->responseData(["error_code" => 100]);
        }
        $dataGet = $this->request->getQuery();
        if (!isset($dataGet["id"])) {
            return $this->responseData(["error_code" => 101]);
        }
        $post = $this->Posts->find()->where(["Posts.id" => $dataGet["id"], "Posts.account_id" => $this->authUser->id])->first();
        if (!$post) {
            return $this->responseData(["error_code" => 404, "ext_msg" => "Post"]);
        }
        if ($this->Posts->delete($post)) {
            return $this->responseData(["status" => true]);
        } else {
            return $this->responseData(["error_code" => 600]);
        }
    }

    public function post()
    {
        if (!$this->request->is("POST")) {
            return $this->responseData(["error_code" => 100]);
        }

        $dataPost = $this->request->getData();

        if (!isset($dataPost["content"])) {
            return $this->responseData(["error_code" => 101]);
        }

        $post = $this->Posts->newEntity([
            "account_id" => $this->authUser->id,
            "content" => $dataPost["content"],
            "images" => isset($dataPost["images"]) ? $dataPost["images"] : "",
            "revision" => 1,
            "status" => 1
        ]);

        if ($this->Posts->save($post)) {
            $images = [];
            if (!empty($post->images)) {
                $images = explode("|", $post->images);
            }
            $dataReturn = [
                "id" => $post->id,
                "content" => $dataPost["content"],
                "images" => $images,
                "revision" => 1,
                "date" => $post->modified->format("Y-m-d H:i:s")
            ];
            return $this->responseData(["status" => true, "data" => $dataReturn]);
        } else {
            return $this->responseData(["error_code" => 600]);
        }
    }
}
