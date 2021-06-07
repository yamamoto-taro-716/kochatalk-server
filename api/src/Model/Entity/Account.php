<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Account Entity
 *
 * @property int $id
 * @property string $avatar
 * @property string $nickname
 * @property string $intro
 * @property int $age
 * @property int $gender
 * @property string $nationality
 * @property int $objective
 * @property int $marital_status
 * @property string $language_translate
 * @property string $memo
 * @property int $revision
 * @property int $device_id
 * @property int $in_group
 * @property int $status
 * @property int $avatar_status
 * @property string $prefecture
 * @property boolean $flg_push
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Device $device
 * @property \App\Model\Entity\Post[] $posts
 */
class Account extends Entity
{
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    static function getGenders()
    {
        return [
            self::GENDER_MALE => __("Male"),
            self::GENDER_FEMALE => __("Female"),
        ];
    }

    const OBJECTIVE_0 = 0;
    const OBJECTIVE_10 = 10;
    const OBJECTIVE_20 = 20;
    const OBJECTIVE_30 = 30;
    const OBJECTIVE_40 = 40;
    const OBJECTIVE_50 = 50;
    static function getObjectives(){
        return [
            self::OBJECTIVE_0 => __("指定なし"),
            self::OBJECTIVE_10 => __("友達探し"),
            self::OBJECTIVE_20 => __("恋人探し"),
            self::OBJECTIVE_30 => __("趣味友探し"),
            self::OBJECTIVE_40 => __("暇つぶし"),
            self::OBJECTIVE_50 => __("ヒミツ"),
        ];
    }

    const STATUS_NORMAL = 1;
    const STATUS_DISABLE = 0;
    const STATUS_EXIT = 10;
    const STATUS_BLOCKED = 20;
    const STATUS_BLOCKED_REPORT = 30;
    const AVATAR_STATUS_CONFIRM = 1;
    const AVATAR_STATUS_UNCONFIRM = 0;
    const HAS_AVATAR_STATUS = 1;
    const NO_AVATAR_STATUS = 0;
    
    static function getStatus() {
        return [
            self::STATUS_NORMAL => __("Normal"),
            self::STATUS_BLOCKED => __("Blocked"),
            self::STATUS_BLOCKED_REPORT => __("Blocked by report"),
        ];
    }
    
    static function getAvatarStatus() {
        return [
            self::AVATAR_STATUS_CONFIRM => __("確認済み"),
            self::AVATAR_STATUS_UNCONFIRM => __("未確認"),
        ];
    }
    
    static function getHasAvatarStatus() {
        return [
            self::HAS_AVATAR_STATUS => __("画像有り"),
            self::NO_AVATAR_STATUS => __("画像無し"),
        ];
    }

	/**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true
    ];
}
