<?php

namespace App\Controller;

use App\Model\Table\SettingsTable;


/**
 * Settings Controller
 *
 * @property SettingsTable $Settings
 */
class SettingsController extends AppController
{
    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        $setting = $this->Settings->find()->where(["Settings.id" => 1])->first();
        if (!$setting) {
            $setting = $this->Settings->newEntity([
                "id" => 1
            ]);
            $this->Settings->save($setting);
        }
        if ($this->request->is(['POST', 'PUT'])) {
            $setting = $this->Settings->patchEntity($setting, $this->request->getData());
            if ($this->Settings->save($setting)) {
                $this->Flash->success(__("Success"));
            } else {
                $this->Flash->error(__("Error"));
            }
            $this->redirect(["action" => "index"]);
        }
        $this->set(compact("setting"));
    }
}
