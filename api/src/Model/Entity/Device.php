<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Device Entity
 *
 * @property int $id
 * @property string $uuid
 * @property string $user_agent
 * @property string $push_token
 * @property string $version
 * @property \Cake\I18n\FrozenTime $last_access
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Account[] $accounts
 */
class Device extends Entity
{

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
        'uuid' => true,
        'user_agent' => true,
        'push_token' => true,
        'version' => true,
        'last_access' => true,
        'created' => true,
        'accounts' => true
    ];
}
