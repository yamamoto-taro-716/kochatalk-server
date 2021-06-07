<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\I18n\Time;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use Cake\Core\Configure;

/**
 * Delete shell command.
 */
class DeleteShell extends Shell
{
    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        $this->loadModel("Settings");
        $this->loadModel("Posts");

        $setting = $this->Settings->find()->select(["day_message", "day_timeline"])->first();
        if ($setting) {
            $day_timeline = intval($setting->day_timeline);
            $sDate = Time::now()->modify("- $day_timeline days");
            $this->Posts->deleteAll([
                "created <=" => $sDate->format("Y-m-d H:i:s")
            ]);

            $day_message = intval($setting->day_message);
            $mDate =  Time::now()->modify("- $day_message days");
            $mDate = new UTCDateTime($mDate->timestamp * 1000);
            $conn = new Client(env("MONGODB_URL", "mongodb://127.0.0.1:27017"));
            $message = $conn->selectDatabase(Configure::read("API.mongo_db"))->selectCollection('messages');
            $message->deleteMany([
                "created" => [
                    '$lte' => $mDate
                ]
            ]);
            $this->out("Success");
        } else {
            $this->out("Not found Setting");
        }
    }
}
