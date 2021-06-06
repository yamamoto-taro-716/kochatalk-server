<?php

namespace App\Controller;

use App\Model\Entity\RandomConfig;

/**
 * RandomConfigs Controller
 *
 * @property \App\Model\Table\RandomConfigsTable $RandomConfigs
 */
class RandomConfigsController extends AppController {
	public function initialize() {
		parent::initialize();
	}

	public function index() {
		$randomConfigs = $this->RandomConfigs->find()->all();
		$this->set(compact("randomConfigs"));
	}

	public function edit($id = null) {
		$randomConfig = $this->RandomConfigs->find()->where(["id" => $id])->first();
		if (!$randomConfig) {
			$this->Flash->error("Not found");
			return $this->redirect(["action" => "index"]);
		}
		if ($this->request->is(["put", "post"])) {
			$dataPost = $this->request->getData();
			if ($dataPost["created_type"] == RandomConfig::TYPE_INTERVAL) {
				$dataPost["created_value"] = intval($dataPost["created_value"]);
			}
			if ($dataPost["access_type"] == RandomConfig::TYPE_INTERVAL) {
				$dataPost["access_value"] = intval($dataPost["access_value"]);
			}
			$entity = $this->RandomConfigs->patchEntity($randomConfig, $dataPost);
			if ($this->RandomConfigs->save($entity)) {
				$this->Flash->success("Successful");
				return $this->redirect(["action" =>"index"]);
			} else {
				$this->Flash->error("Error");
			}
		}
		$this->set(compact("randomConfig"));
	}
}
