<?php
App::uses('ModelBehavior', 'Model');

class JoinableBehavior extends ModelBehavior {

/**
 * Array of join rules created
 *
 * @var array
 * @access private
 */
	private $__joins = array();

/**
 * Array of model objects
 *
 * @var array
 * @access private
 */
	private $__models = array();

/**
 * Executed before a model find
 *
 * @param Model $Model
 * @param array $query
 * @return array
 * @access public
 */
	public function beforeFind(Model $Model, $query) {
		if (!empty($query['join'])) {
			$query['recursive'] = -1;
			$this->__joins[$Model->alias] = array();

			foreach ($query['join'] as $k => $v) {
				if (!is_array($v)) {
					$this->addJoins($Model, $v);
				} else {
					$this->addJoins($Model, $k, $Model->alias, $v);
				}
			}

			if (!empty($query['joins'])) {
				$query['joins'] = array_merge($this->__joins[$Model->alias], $query['joins']);
			} else {
				$query['joins'] = $this->__joins[$Model->alias];
			}
		}

		return $query;
	}

/**
 * Adds the join rule
 *
 * @param Model $Model
 * @param string $join
 * @param mixed $parent
 * @param array $children
 */
	public function addJoins(Model &$Model, $join, $parent = false, $children = array()) {
		if (!is_string($parent)) {
			$parent = $Model->alias;
		}

		if (!isset($this->__models[$Model->alias][$Model->alias])) {
			$this->__models[$Model->alias][$Model->alias] =& $Model;
		}

		// Determine the model name and alias for our join
		$alias = $join;
		if (!empty($children['model'])) {
			$join = $children['model'];
			unset($children['model']);
		}

		// Load model which we're joining
		if (!isset($this->__models[$Model->alias][$join])) {
			$this->__models[$Model->alias][$join] = ClassRegistry::init($join);
		}

		// Determine table name for our join
		if (!empty($children['table'])) {
			$table = $children['table'];
			unset($children['table']);
		} else {
			$table = $this->__models[$Model->alias][$join]->getDataSource()->fullTableName($this->__models[$Model->alias][$join]);
		}

		// Determine the join type
		$type = 'left';
		if (isset($children['type'])) {
			$type = $children['type'];
			unset($children['type']);
		}

		$parentModel = $parent;
		$parentForeignKey = $this->__models[$Model->alias][$parentModel]->primaryKey;

		// Determine the foreign key for our join
		if (isset($children['foreignKey'])) {
			$joinForeignKey = $children['foreignKey'];
			unset($children['foreignKey']);
		} else {
			$joinForeignKey = Inflector::underscore($parent) . '_id';
		}

		if (array_key_exists($join, $this->__models[$Model->alias][$parentModel]->hasAndBelongsToMany)) {
			$association = $this->__models[$Model->alias][$parentModel]->hasAndBelongsToMany[$join];
			$this->addJoins($Model, $association['with'], $parentModel, array(
				'type' => $type,
				'foreignKey' => $association['foreignKey']
			));
			$parentModel = $association['with'];
			$joinForeignKey = $this->__models[$Model->alias][$association['with']]->primaryKey;
			$parentForeignKey = $association['associationForeignKey'];
		}

		if (array_key_exists($join, $this->__models[$Model->alias][$parentModel]->belongsTo)) {
			$association = $this->__models[$Model->alias][$parentModel]->belongsTo[$join];
			$parentForeignKey = $association['foreignKey'];
			$joinForeignKey = $this->__models[$Model->alias][$join]->primaryKey;
		}

		// Determine our join conditions
		if (!empty($children['conditions'])) {
			$conditions = $children['conditions'];
			unset($children['conditions']);
		} else {
			$conditions = array(
				"$join.$joinForeignKey = $parentModel.$parentForeignKey"
			);
		}

		$this->__joins[$Model->alias][] = array(
			'table' => $table,
			'alias' => $alias,
			'type' => strtoupper($type),
			'conditions' => $conditions
		);

		// Recursively add child joins
		if (!empty($children)) {
			foreach ($children as $k => $v) {
				if (!is_array($v)) {
					$this->addJoins($Model, $v, $join);
				} else {
					$this->addJoins($Model, $k, $join, $v);
				}
			}
		}
	}
}