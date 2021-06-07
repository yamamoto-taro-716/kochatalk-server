<?php

namespace App\Controller;


/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
    }

    public function profile()
    {
        $user = $this->Users->find()->where(["id" => $this->Auth->user('id')])->first();
        if ($this->request->is(["POST", "PUT"])) {
            $dataPost = $this->request->getData();
            $user = $this->Users->patchEntity($user, $dataPost);
            if ($this->Users->save($user)) {
                $this->Flash->success("Success");
            } else {
                $this->Flash->error("Error");
            }
            $this->redirect(["action" => "profile"]);
        }
        $this->set(compact("user"));
    }

    public function login()
    {
        $this->viewBuilder()->setLayout("login");
        if ($this->request->is("POST")) {
            $dataPost = $this->request->getData();
            $user = $this->Users->find()->where([
                "Users.username" => $dataPost["username"],
                "Users.password" => md5($dataPost["password"])
            ])->first();
            if ($user) {
                $this->Auth->setUser($user->toArray());
                return $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Flash->error(__("Invalid user !"));
            }
        }
    }

    public function logout()
    {
        $this->Auth->logout();
        $this->request->getSession()->destroy();
        return $this->redirect(["action" => "login"]);
    }
}
