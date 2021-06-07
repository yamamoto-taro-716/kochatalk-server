<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AccountFriend Entity
 *
 * @property int $id
 * @property int $account_action_id
 * @property int $account_receive_id
 * @property int $action_id
 * @property string $message
 * @property int $status
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Account $account_action
 * @property \App\Model\Entity\Account $account_receife
 */
class AccountFriend extends Entity
{
    const STATUS_NORMAL = -1;
    const STATUS_PENDING = 0;
    const STATUS_FRIEND = 1;
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
