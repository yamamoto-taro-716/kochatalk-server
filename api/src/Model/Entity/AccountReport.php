<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AccountReport Entity
 *
 * @property int $id
 * @property int $account_action_id
 * @property int $account_receive_id
 * @property int $status
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Account $account_action
 * @property \App\Model\Entity\Account $account_receife
 */
class AccountReport extends Entity
{
    const STATUS_NORMAL = 0;
    const STATUS_REPORTED = 1;
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
        'account_action_id' => true,
        'account_receive_id' => true,
        'status' => true,
        'modified' => true,
        'created' => true,
        'account_action' => true,
        'account_receife' => true
    ];
}
