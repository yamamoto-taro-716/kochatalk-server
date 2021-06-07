<?php
use Migrations\AbstractSeed;

/**
 * RandomConfigs seed.
 */
class RandomConfigsSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $data = [
	        [
		        "id" => \App\Model\Entity\RandomConfig::MALE_MALE,
		        "title" => "男性 -> 男性",
		        "created_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "created_value" => null,
		        "access_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "access_value" => null,
		        "random_limit" => 10
	        ],
	        [
		        "id" => \App\Model\Entity\RandomConfig::FEMALE_FEMALE,
		        "title" => "女性 -> 女性",
		        "created_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "created_value" => null,
		        "access_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "access_value" => null,
		        "random_limit" => 10
	        ],
	        [
		        "id" => \App\Model\Entity\RandomConfig::MALE_FEMALE,
		        "title" => "男性 -> 女性",
		        "created_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "created_value" => null,
		        "access_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "access_value" => null,
		        "random_limit" => 10
	        ],
	        [
		        "id" => \App\Model\Entity\RandomConfig::FEMALE_MALE,
		        "title" => "女性 -> 男性",
		        "created_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "created_value" => null,
		        "access_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "access_value" => null,
		        "random_limit" => 10
	        ],
	        [
		        "id" => \App\Model\Entity\RandomConfig::MALE_ALL,
		        "title" => "男性 -> 女性, 男性",
		        "created_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "created_value" => null,
		        "access_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "access_value" => null,
		        "random_limit" => 10
	        ],
	        [
		        "id" => \App\Model\Entity\RandomConfig::FEMALE_ALL,
		        "title" => "女性 -> 女性, 男性",
		        "created_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "created_value" => null,
		        "access_type" => \App\Model\Entity\RandomConfig::TYPE_ALL,
		        "access_value" => null,
		        "random_limit" => 10
	        ]
        ];

        $table = $this->table('random_configs');
        $table->insert($data)->save();
    }
}
