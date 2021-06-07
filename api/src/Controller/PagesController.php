<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use Cake\Controller\Controller;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link https://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends Controller
{
    public function initialize()
    {
        parent::initialize();
        $this->viewBuilder()->setLayout("empty");
    }

    public function index()
    {
        return $this->redirect(["controller" => "Accounts"]);
    }

    public function announcement($lang = "")
    {
        $this->loadModel("Settings");
        $setting = $this->Settings->find()->select(["Settings.content_ads", "Settings.content_ads_en"])->first()->toArray();
        $announcement = isset($setting["content_ads"]) ? $setting["content_ads"] : "";
        if ($lang == "en") {
	        $announcement = isset($setting["content_ads_en"]) ? $setting["content_ads_en"] : "";
        }
        $this->set(compact("announcement"));
    }

    public function term($lang = "ja")
    {
        $this->loadModel("Settings");
        $setting = $this->Settings->find()->select(["Settings.term_ja", "Settings.term_en"])->first()->toArray();
        $term = isset($setting["term_" . $lang]) ? $setting["term_" . $lang] : "";
        $this->set(compact("term"));
    }

    public function policy($lang = "ja")
    {
        $this->loadModel("Settings");
        $setting = $this->Settings->find()->select(["Settings.policy_ja", "Settings.policy_en"])->first()->toArray();
        $policy = isset($setting["policy_" . $lang]) ? $setting["policy_" . $lang] : "";
        $this->set(compact("policy"));
    }
}
