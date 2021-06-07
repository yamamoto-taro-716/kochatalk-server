<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RandomConfig Entity
 *
 * @property int $id
 * @property string $title
 * @property int $created_type
 * @property string $created_value
 * @property int $access_type
 * @property string $access_value
 * @property int $random_limit
 */
class RandomConfig extends Entity {
	const MALE_MALE = 1;
	const FEMALE_FEMALE = 2;
	const MALE_FEMALE = 3;
	const FEMALE_MALE = 4;
	const MALE_ALL = 5;
	const FEMALE_ALL = 6;

	static function getConfigId( $user_gender, $friend_gender ) {
		if ( $user_gender == Account::GENDER_MALE && $friend_gender == Account::GENDER_MALE ) {
			return self::MALE_MALE;
		} else if ( $user_gender == Account::GENDER_FEMALE && $friend_gender == Account::GENDER_FEMALE ) {
			return self::FEMALE_FEMALE;
		} else if ( $user_gender == Account::GENDER_MALE && $friend_gender == Account::GENDER_FEMALE ) {
			return self::MALE_FEMALE;
		} else if ( $user_gender == Account::GENDER_FEMALE && $friend_gender == Account::GENDER_MALE ) {
			return self::FEMALE_MALE;
		} else if ( $user_gender == Account::GENDER_MALE && $friend_gender == - 1 ) {
			return self::MALE_ALL;
		} else if ( $user_gender == Account::GENDER_FEMALE && $friend_gender == - 1 ) {
			return self::FEMALE_ALL;
		}

		return 0;
	}

	const TYPE_ALL = 1;
	const TYPE_SINCE = 2;
	const TYPE_INTERVAL = 3;

	static function getTypeArray() {
		return [
			self::TYPE_ALL      => '全ユーザー',
			self::TYPE_SINCE    => '日付',
			self::TYPE_INTERVAL => '日'
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
		'*' => true,
	];
}
