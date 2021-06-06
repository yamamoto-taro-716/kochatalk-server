<?php

namespace App\Utility;


use Cake\Core\Configure;

class AppUtil
{
    static function handleStringNull($string)
    {
        return strval($string);
    }

    static function handleNumberNull($number, $type)
    {
        switch ($type) {
            case 'int':
                return intval($number);
                break;
            case 'double':
                return doubleval($number);
                break;
            case 'float':
                return floatval($number);
                break;
            default:
                return 0;
        }
    }

    static function getCountries()
    {
        $countries = json_decode(Configure::read("COUNTRIES"), true);
        $new_array = [];
        foreach ($countries as $k => $item) {
            $new_array[$item["code"]] = $item["name"];
        }
        return $new_array;
    }

    static function uploadImageProfile($infoFile, $_id)
    {
        $ext = ["image/jpeg", "image/png", "image/jpg"];
        $filename = null;
        if ($infoFile['size'] != 0 || $infoFile['error'] != 0) {
            if (in_array($infoFile['type'], $ext)) {
                $uploadFolder = WWW_ROOT . "upload" . DS . "profiles";
                if (!file_exists($uploadFolder)) {
                    mkdir($uploadFolder);
                }
                $file_name = $_id . '.' . pathinfo(basename($infoFile['name']), PATHINFO_EXTENSION);
                $uploadPath = $uploadFolder . DS . $file_name;
                if (move_uploaded_file($infoFile['tmp_name'], $uploadPath)) {
                    $filename = $file_name;
                }
            }
        }
        return $filename;
    }

    static function genChatRoomId($account_id, $friend_id)
    {
        return '#' . ($account_id > $friend_id ? $friend_id . '-' . $account_id : $account_id . '-' . $friend_id);
    }

    static function sendFCMMessage($notification, $data = null, $target, $env = 'ios')
    {
        set_time_limit(0);
        //FCM API end-point
        $url = 'https://fcm.googleapis.com/fcm/send';
        //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        if ($env == 'android') {
            $server_key = Configure::read("API.fcm_server_key_android");
        } else {
            $server_key = Configure::read("API.fcm_server_key");
        }


        $fields = array();
        if (!empty($notification)) {
            $fields['notification'] = $notification;
        }

        if ($data) {
            $fields['data'] = $data;
        }

        $return = [];
        $fields['registration_ids'] = [];
        foreach ($target as $k => $value) {
            $fields['registration_ids'][] = $value;
        }

        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $server_key
        );
        //CURL request to route notification to FCM connection server (provided by Google)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);

        $return = ['success' => 0, 'failure' => 0, 'error' => curl_error($ch)];
        if ($result === FALSE) {
        } else {
            $tmp = json_decode($result, true);
            if ($tmp) {
                $return = $tmp;
            }
        }
        curl_close($ch);

        return $return;
    }

    static function uploadImageChat($infoFile, $room_id)
    {
        $ext = ["image/jpeg", "image/png", "image/jpg"];
        $filename = null;
        if ($infoFile['size'] != 0 || $infoFile['error'] != 0) {
            if (in_array($infoFile['type'], $ext)) {
                $uploadFolder = WWW_ROOT . "upload" . DS . "messages" . DS . $room_id;
                if (!file_exists($uploadFolder)) {
                    mkdir($uploadFolder);
                }
                $file_name = time() . '.' . pathinfo(basename($infoFile['name']), PATHINFO_EXTENSION);
                $uploadPath = $uploadFolder . DS . $file_name;
                if (move_uploaded_file($infoFile['tmp_name'], $uploadPath)) {
                    $filename = $file_name;
                }
            }
        }
        return $filename;
    }
}