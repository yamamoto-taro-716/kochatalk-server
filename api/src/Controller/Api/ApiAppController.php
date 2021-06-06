<?php
namespace App\Controller\Api;

use App\Model\Entity\Account;
use App\Model\Table\AccountsTable;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\NotFoundException;
use Cake\Utility\Security;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;

/**
 * ApiApp Controller
 * @property AccountsTable $Accounts
 * @property Account $authUser
 */
class ApiAppController extends Controller
{
    public $autoRender = false;

    protected $_apiConfig;
    protected $checkAuthAllow = false;
    protected $authUser;
    protected $clientDevice;
    private $cakeParams;

    public function initialize()
    {
        parent::initialize();
        $this->_apiConfig = Configure::read("API");
        $this->cakeParams = $this->request->getAttribute('params');
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $headers = $this->request->getHeaders();
        if (!isset($headers['Uuid']) ||
            !isset($headers['User-Agent']) ||
            !in_array($headers['User-Agent'][0], ['ios', 'android']) ||
            !isset($headers['Timestamp']) || strlen($headers['Timestamp'][0]) < 10 ||
            !isset($headers['Version'])
        ) {
            return $this->responseData(['error_code' => 101, 'ext_msg' => "Header"]);
        }
        $this->clientDevice = [
            'uuid' => $headers['Uuid'][0],
            'user-agent' => $headers['User-Agent'][0],
            'version' => $headers['Version'][0]
        ];
        $checksum = Security::hash($this->_apiConfig['salt_key'] . $headers['Uuid'][0] . $headers['User-Agent'][0] . $headers['Timestamp'][0], 'sha256');
        if (!isset($headers['Checksum']) || $headers['Checksum'][0] != $checksum) {
            return $this->responseData(['error_code' => 203]);
        }
        if (!$this->checkAuthAllow) {
            if (isset($headers['Authorization'])) {
                try {
                    $JWTDecoded = JWT::decode($headers['Authorization'][0], $this->_apiConfig['jwt_key'], ['HS256']);
                    $this->loadModel("Accounts");
                    $this->authUser = $this->Accounts->find()->where(["Accounts.id" => $JWTDecoded->profile->id])->first();
                    if (!$this->authUser) {
                        return $this->responseData(["error_code" => 404, "ext_msg" => "Account"]);
                    }
                    if ($this->authUser->status != Account::STATUS_NORMAL) {
                        return $this->responseData(["error_code" => 205]);
                    }
                    if ($this->authUser->in_group == Account::STATUS_EXIT) {
                        return $this->responseData(["error_code" => 207]);
                    }
                } catch (ExpiredException $exp) {
                    return $this->responseData(['error_code' => 202]);
                } catch (\Exception $ex) {
                    return $this->responseData(['error_code' => 201]);
                }
            } else {
                return $this->responseData(['error_code' => 201]);
            }
        }

    }

    public function index()
    {
        throw new NotFoundException();
    }

    /**
     * Function: Response data structure with JSON format
     * @param array $json
     * @return \Cake\Http\Response|null|static
     */
    protected function responseData($json = [])
    {
        $jsonData = $json + $this->_apiConfig['template_response'];
        $jsonData['error_message'] = isset($this->_apiConfig['error_code'][$jsonData['error_code']]) ? $this->_apiConfig['error_code'][$jsonData['error_code']] : '';
        if (isset($jsonData['ext_msg'])) {
            $jsonData['error_message'] .= ': ' . $json["ext_msg"];
            unset($jsonData["ext_msg"]);
        }
        $this->response = $this->response->withType('application/json')->withStringBody(json_encode($jsonData));
        return $this->response;
    }

    /**
     * Function: Allow API do not need Authorization
     * @param $action
     */
    protected function authAllow($action)
    {
        if (is_array($action)) {
            $this->checkAuthAllow = in_array($this->cakeParams['action'], $action);
        } else {
            $this->checkAuthAllow = $this->cakeParams['action'] == $action;
        }
    }

    protected function syncAccountToMongo($data)
    {
        $conn = new Client(env("MONGODB_URL", "mongodb://127.0.0.1:27017"));
        $accountsMongo = $conn->selectDatabase(Configure::read("API.mongo_db"))->selectCollection('accounts');

        $account = $accountsMongo->findOne(['account_id' => $data['id']]);
        if (!$account) {
            $accountsMongo->insertOne([
                'user_agent' => $data['user_agent'],
                "push_token" => $data['push_token'],
                "account_id" => $data['id'],
                "nickname" => $data['nickname'],
                "nationality" => $data['nationality'],
                "gender" => $data['gender'],
                'avatar' => $data['avatar'],
                "revision" => $data['revision'] ? $data['revision'] : 1,
                "status" => $data['status'],
                'created' => new UTCDateTime()
            ]);
        }
    }
}
