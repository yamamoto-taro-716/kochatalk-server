<?php

namespace App\Controller;

use App\Model\Table\AccountReportsTable;


/**
 * Report Controller
 *
 * @property AccountReportsTable $AccountReports
 */
class ReportController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadModel("AccountReports");
    }

    public function index()
    {
        $this->paginate = [
            "contain" => [
                "AccountActions" => function ($q){
                    return $q->select(["AccountActions.id", "AccountActions.nickname"]);
                },
                "AccountReceives" => function ($q){
                    return $q->select(["AccountReceives.id", "AccountReceives.nickname"]);
                }
            ],
            "order" => ['AccountReports.modified' => 'DESC'],
            "limit" => 50
        ];
        $account_reports = $this->paginate($this->AccountReports);
        $this->set(compact("account_reports"));
    }
}
