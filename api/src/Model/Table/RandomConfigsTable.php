<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RandomConfigs Model
 *
 * @method \App\Model\Entity\RandomConfig get($primaryKey, $options = [])
 * @method \App\Model\Entity\RandomConfig newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\RandomConfig[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RandomConfig|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RandomConfig patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RandomConfig[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\RandomConfig findOrCreate($search, callable $callback = null, $options = [])
 */
class RandomConfigsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('random_configs');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmpty('title');

        $validator
            ->integer('created_type')
            ->requirePresence('created_type', 'create')
            ->notEmpty('created_type');

        $validator
            ->scalar('created_value')
            ->maxLength('created_value', 255)
            ->allowEmpty('created_value');

        $validator
            ->integer('access_type')
            ->requirePresence('access_type', 'create')
            ->notEmpty('access_type');

        $validator
            ->scalar('access_value')
            ->maxLength('access_value', 255)
            ->allowEmpty('access_value');

	    $validator
		    ->integer('random_limit')
		    ->requirePresence('random_limit', 'create')
		    ->notEmpty('random_limit');

        return $validator;
    }
}
