<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Setting Entity
 *
 * @property int $id
 * @property int $day_message
 * @property int $count_ads
 * @property string $title_ads
 * @property string $title_ads_en
 * @property int $show_notify
 * @property string $content_ads
 * @property string $content_ads_en
 * @property string $term_ja
 * @property string $term_en
 * @property string $policy_ja
 * @property string $policy_en
 */
class Setting extends Entity
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
        '*' => true
    ];
}
