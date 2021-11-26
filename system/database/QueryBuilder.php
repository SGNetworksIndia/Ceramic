<?php

class QueryBuilder {
	public const ORDER_ASCENDING = 'ASC', ORDER_DESCENDING = 'DESC';
	private const QUERY_TYPE_SELECT = 'SELECT', QUERY_TYPE_UPDATE = 'UPDATE', QUERY_TYPE_DELETE = 'DELETE', QUERY_TYPE_INSERT = 'INSERT INTO', QUERY_TYPE_FROM = 'FROM';
	public WhereClauseBuilder $where;
	protected string $op = '', $sql = '', $sort = 'ASC', $whereSQL;
	protected array $condLOP = array(), $condValues = array(), $condFields = array(), $condOP = array(), $cond = array();
	private array $fields = array(), $tables = array(), $orderby = array(), $updates = array(), $inserts = array();
	private int $offset, $limit;

	/**
	 * QueryBuilder constructor.
	 * @param int $offset
	 * @param int $limit
	 */
	public function __construct(int $offset = -1, int $limit = -1) {
		$this->offset = $offset;
		$this->limit = $limit;
		if(empty($this->where))
			$this->where = new WhereClauseBuilder($this);
	}

	public function select(string $fields): static {
		$this->op = self::QUERY_TYPE_SELECT;
		$this->string2array($fields, $this->fields);
		$this->generateSQL($this->op, $this->fields);
		return $this;
	}

	private function string2array(string $str, array &$array) {
		$str = trim($str, ',');
		$strs = explode(',', $str);
		foreach($strs as $str) {
			$array[] = trim($str);
		}
	}

	private function generateSQL(string $type, array $values, bool $appendType = true): string {
		$sql = '';
		if($type == self::QUERY_TYPE_SELECT || $type == self::QUERY_TYPE_FROM) {
			$values = implode(', ', $values);
			$sql = ($appendType) ? "{$type} {$values}" : $values;
			if(!empty($this->sql))
				$this->sql = " ";
			$this->sql .= $sql;
		} elseif($type == self::QUERY_TYPE_UPDATE) {
			foreach($values as $k=>$v) {
				$v = (empty($v)) ? 'NULL' : "'$v'";
				$sql .= "{$k} = $v, ";
			}
			$sql = rtrim($sql, ', ');
		} elseif($type == self::QUERY_TYPE_INSERT) {
			$fields = array();
			$vals = array();
			foreach($values as $k=>$v) {
				$fields[] = $k;
				$v = (empty($v)) ? 'NULL' : "'$v'";
				$vals[] = $v;
			}
			$sql = "(" . implode(', ',$fields) . ") VALUES (" . implode(', ', $vals) . ")";
			$sql = rtrim($sql, ', ');
		}
		return $sql;
	}

	public function update(string $table): static {
		$this->op = self::QUERY_TYPE_UPDATE;
		$this->from($table);
		return $this;
	}

	public function from(string $tables): static {
		$this->string2array($tables, $this->tables);
		$this->generateSQL(self::QUERY_TYPE_FROM, $this->tables);
		return $this;
	}

	public function insert(string $table): static {
		$this->op = self::QUERY_TYPE_INSERT;
		$this->from($table);
		return $this;
	}

	public function delete(string $table): static {
		$this->op = self::QUERY_TYPE_DELETE;
		//$this->deleteTable = $table;
		$this->from($table);
		return $this;
	}

	public function orderby(string $fields, string $order = self::ORDER_ASCENDING): static {
		$this->string2array($fields, $this->orderby);
		$this->sort = $order;
		return $this;
	}

	/**
	 * @param int $offset
	 * @return QueryBuilder
	 */
	public function offset(int $offset): static {
		$this->offset = $offset;
		return $this;
	}

	/**
	 * @param int $limit
	 * @return QueryBuilder
	 */
	public function limit(int $limit): static {
		$this->limit = $limit;
		return $this;
	}

	public function set(string $field, string $value): static {
		$this->updates[$field] = $value;
		return $this;
	}

	public function bind(string $field, string $value): static {
		$this->inserts[$field] = $value;
		return $this;
	}

	public function bind_array(array $pairs): static {
		$this->inserts = $pairs;
		return $this;
	}

	public function bindFunctionArguments(string $function, $object = null): static {
		$this->inserts = $this->get_func_args($object, $function);
		return $this;
	}

	private function get_func_args($object, $funcName): array {
		$result = $params = array();
		$args = debug_backtrace()[2]['args'];
		try {
			$f = new ReflectionMethod($object, $funcName);
			foreach ($f->getParameters() as $k=>$param) {
				$params[] = $param->name;
				$result[$param->name] = $args[$k];
			}
		} catch(ReflectionException $e) {}
		return $result;
	}

	public function where(string $condition): static {
		$lpattern = "/(AND)|(OR)|(NOT)|(IN)|(ALL)|(ANY)|(LIKE)|(IS NULL)|(UNIQUE)|(EXISTS)|(BETWEEN)/i";
		preg_match_all($lpattern, $condition, $lmatch);
		$conditions = preg_split('/(AND)|(OR)|(NOT)|(IN)|(ALL)|(ANY)|(LIKE)|(IS NULL)|(UNIQUE)|(EXISTS)|(BETWEEN)/i', $condition); //logical
		foreach($conditions as $condition) {
			$condition = trim($condition);
			$pattern = "/^(\w+)((\s+)?((=)|(!=)|(<)|(>)|(<=)|(>=)|(!<)|(!>)|(<>))(\s+)?)(('(\w+)')|(\"(\w+)\")|(\w+))$/"; //conditional
			preg_match($pattern, $condition, $match);
			$f = $match[1];
			$o = $match[4];
			$v = $match[19];
			$v = (empty($v)) ? 'NULL' : $v;
			$this->condFields[] = $f;
			$this->condOP[] = $o;
			$this->condValues[] = $v;
		}
		$this->condLOP = $lmatch[0];
		//$pairs = $this->generateKeyValueSQL($this->condFields, $this->condValues);
		$s = "";
		if(!empty($this->condLOP) && count($this->condLOP) == (count($this->condFields) - 1)) {
			foreach($this->condFields as $k => $f) {
				if(($k + 1) <= count($this->condLOP))
					$s .= "{$f}{$this->condOP[$k]}'{$this->condValues[$k]}' {$this->condLOP[$k]} ";
				else
					$s .= "{$f}{$this->condOP[$k]}'{$this->condValues[$k]}'";
			}
		}
		return $this;
	}

	public function build(): string {
		$s = "";
		if($this->op == self::QUERY_TYPE_SELECT) {
			$s = $this->generateSQL($this->op, $this->fields);
			$s .= " " . $this->generateSQL(self::QUERY_TYPE_FROM, $this->tables);
			if(!empty($this->condFields))
				$s .= " WHERE " . $this->generateWhereSQL($this->condFields, $this->condOP, $this->condValues, $this->condLOP);
			if(!empty($this->orderby))
				$s .= " ORDER BY " . implode(',', $this->orderby) . " " . $this->sort;
			if($this->offset >= 0 && $this->limit > $this->offset)
				$s .= " OFFSET {$this->offset}";
			if($this->limit >= 0 && $this->limit > $this->offset)
				$s .= " LIMIT {$this->limit}";
 		} elseif($this->op == self::QUERY_TYPE_UPDATE) {
			$s = "UPDATE ";
			$s .= $this->generateSQL(self::QUERY_TYPE_FROM, $this->tables, false);
			$s .= " SET ";
			$s .= $this->generateSQL($this->op, $this->updates);
			if(!empty($this->condFields))
				$s .= " WHERE " . $this->generateWhereSQL($this->condFields, $this->condOP, $this->condValues, $this->condLOP);
		} elseif($this->op == self::QUERY_TYPE_INSERT) {
			$s = "INSERT INTO ";
			$s .= $this->generateSQL(self::QUERY_TYPE_FROM, $this->tables, false);
			$s .= $this->generateSQL($this->op, $this->inserts);
		} elseif($this->op == self::QUERY_TYPE_DELETE) {
			$s = "DELETE ";
			$s .= $this->generateSQL(self::QUERY_TYPE_FROM, $this->tables);
			if(!empty($this->condFields))
				$s .= " WHERE " . $this->generateWhereSQL($this->condFields, $this->condOP, $this->condValues, $this->condLOP);
		}
		$this->sql = $s . ';';
		$sql = $this->sql;
		$this->fields = $this->tables = $this->orderby = $this->updates = $this->inserts = $this->condLOP = $this->condFields = $this->condOP = $this->condValues = $this->cond = array();
		$this->offset = $this->limit = -1;
		$this->op = $this->sql = $this->sort = $this->whereSQL = "";
		return $sql;
	}

	private function generateWhereSQL(array $fields, array $operators, array $values, $separators = array()): string {
		//$pairs = array_combine($keys, $values);
		$s = "";
		if(!empty($operators) && count($operators) == (count($fields) - 1)) {
			foreach($fields as $k => $f) {
				if(($k + 1) <= count($operators))
					$s .= "{$f}$operators[$k]'{$values[$k]}' ${separators[$k]} ";
				else
					$s .= "{$f}$operators[$k]'{$values[$k]}'";
			}
		} else {
			foreach($fields as $k => $f) {
				$s .= "{$f}='{$values[$k]}'";
			}
		}
		return $s;
	}

	public function parseCondition(string $condition): string {
		$lpattern = "/(AND)|(OR)|(NOT)|(IN)|(ALL)|(ANY)|(LIKE)|(IS NULL)|(UNIQUE)|(EXISTS)|(BETWEEN)/i";
		preg_match_all($lpattern, $condition, $lmatch);
		$conditions = preg_split('/(AND)|(OR)|(NOT)|(IN)|(ALL)|(ANY)|(LIKE)|(IS NULL)|(UNIQUE)|(EXISTS)|(BETWEEN)/i', $condition); //logical
		foreach($conditions as $condition) {
			$condition = trim($condition);
			$pattern = "/^(\w+)((\s+)?((=)|(!=)|(<)|(>)|(<=)|(>=)|(!<)|(!>)|(<>))(\s+)?)(('(\w+)')|(\"(\w+)\")|(\w+))$/"; //conditional
			preg_match($pattern, $condition, $match);
			$f = $match[1];
			$o = $match[4];
			$v = $match[19];
			$this->condFields[] = $f;
			$this->condOP[] = $o;
			$this->condValues[] = $v;
		}
		$this->condLOP = $lmatch[0];
		//$pairs = $this->generateKeyValueSQL($this->condFields, $this->condValues);
		$s = "";
		if(!empty($this->condLOP) && count($this->condLOP) == (count($this->condFields) - 1)) {
			foreach($this->condFields as $k => $f) {
				if(($k + 1) <= count($this->condLOP))
					$s .= "{$f}{$this->condOP[$k]}'{$this->condValues[$k]}' {$this->condLOP[$k]} ";
				else
					$s .= "{$f}{$this->condOP[$k]}'{$this->condValues[$k]}'";
			}
		}
		return $s;
	}
}
class WhereClauseBuilder extends QueryBuilder {
	private QueryBuilder $builder;

	/**
	 * WhereClauseBuilder constructor.
	 * @param QueryBuilder $builder
	 */
	public function __construct(QueryBuilder $builder) { $this->builder = $builder; }

	public function equals(string $field, string $value): QueryBuilder {
		$this->builder->condFields[] = $field;
		$this->builder->condOP[] = '=';
		$this->builder->condValues[] = $value;
		return $this->builder;
	}
	public function not_equals(string $field, string $value): QueryBuilder {
		$this->builder->condFields[] = $field;
		$this->builder->condOP[] = '!=';
		$this->builder->condValues[] = $value;
		return $this->builder;
	}
	public function greater_than(string $field, string $value): QueryBuilder {
		$this->builder->condFields[] = $field;
		$this->builder->condOP[] = '>';
		$this->builder->condValues[] = $value;
		return $this->builder;
	}
	public function less_than(string $field, string $value): QueryBuilder {
		$this->builder->condFields[] = $field;
		$this->builder->condOP[] = '<';
		$this->builder->condValues[] = $value;
		return $this->builder;
	}
	public function greater_than_equals(string $field, string $value): QueryBuilder {
		$this->builder->condFields[] = $field;
		$this->builder->condOP[] = '>=';
		$this->builder->condValues[] = $value;
		return $this->builder;
	}
	public function less_than_equals(string $field, string $value): QueryBuilder {
		$this->builder->condFields[] = $field;
		$this->builder->condOP[] = '<=';
		$this->builder->condValues[] = $value;
		return $this->builder;
	}
	public function not_greater_than(string $field, string $value): QueryBuilder {
		$this->builder->condFields[] = $field;
		$this->builder->condOP[] = '!>';
		$this->builder->condValues[] = $value;
		return $this->builder;
	}
	public function not_less_than(string $field, string $value): QueryBuilder {
		$this->builder->condFields[] = $field;
		$this->builder->condOP[] = '!<';
		$this->builder->condValues[] = $value;
		return $this->builder;
	}
	public function less_greater_than(string $field, string $value): QueryBuilder {
		$this->builder->condFields[] = $field;
		$this->builder->condOP[] = '<>';
		$this->builder->condValues[] = $value;
		return $this->builder;
	}
}