<?php

namespace Packlink\Middleware\Model\Repository;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Logeecom\Infrastructure\ORM\Entity;
use Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Logeecom\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Logeecom\Infrastructure\ORM\QueryFilter\Operators;
use Logeecom\Infrastructure\ORM\QueryFilter\QueryCondition;
use Logeecom\Infrastructure\ORM\QueryFilter\QueryFilter;
use Logeecom\Infrastructure\ORM\Utility\IndexHelper;
use Packlink\BusinessLogic\ORM\Contracts\ConditionallyDeletes;

/**
 * Class BaseRepository
 *
 * @package Packlink\Middleware\Model\Repository
 */
class BaseRepository implements RepositoryInterface, ConditionallyDeletes
{
    /**
     * @var string
     */
    protected $entityClass;
    /**
     * Name of the base entity table in database.
     */
    protected const TABLE_NAME = 'packlink_entity';

    /**
     * Returns full class name.
     *
     * @return string Full class name.
     */
    public static function getClassName(): string
    {
        return static::class;
    }

    /**
     * Sets repository entity.
     *
     * @param string $entityClass Repository entity class.
     */
    public function setEntityClass($entityClass): void
    {
        $this->entityClass = $entityClass;
    }

    /**
     * Saves a new entity or updates an existing one.
     *
     * @param Entity $entity Entity to be saved.
     */
    public function saveOrUpdate(Entity $entity): void
    {
        if ($entity->getId() === null) {
            $id = $this->save($entity);
            $entity->setId($id);
        } else {
            $this->update($entity);
        }
    }

    /**
     * Executes insert query and returns ID of created entity. Entity will be updated with new ID.
     *
     * @param Entity $entity Entity to be saved.
     *
     * @return int Identifier of saved entity.
     */
    public function save(Entity $entity): int
    {
        $data = $this->prepareDataForInsertOrUpdate($entity);

        $data['type'] = $entity->getConfig()->getType();

        $id = DB::table($this->getTableName())->insertGetId($data);

        $entity->setId($id);

        return $id;
    }

    /**
     * Executes mass insert query for all provided entities
     *
     * @param Entity[] $entities
     */
    public function massInsert(array $entities): void
    {
        $data = [];
        foreach ($entities as $entity) {
            $entityData = $this->prepareDataForInsertOrUpdate($entity);
            $entityData['type'] = $entity->getConfig()->getType();
            $data[] = $entityData;
        }

        DB::table($this->getTableName())->insert($data);
    }

    /**
     * Executes update query and returns success flag.
     *
     * @param Entity $entity Entity to be updated.
     *
     * @return bool TRUE if operation succeeded; otherwise, FALSE.
     */
    public function update(Entity $entity): bool
    {
        $data = $this->prepareDataForInsertOrUpdate($entity);

        $rows = DB::table($this->getTableName())->where('id', $entity->getId())->update($data);

        return $rows === 1;
    }

    /**
     * Executes delete query and returns success flag.
     *
     * @param Entity $entity Entity to be deleted.
     *
     * @return bool TRUE if operation succeeded; otherwise, FALSE.
     */
    public function delete(Entity $entity): bool
    {
        $rows = DB::table($this->getTableName())->where('id', $entity->getId())->delete();

        return $rows === 1;
    }

    /**
     * Counts records that match filter criteria.
     *
     * @param QueryFilter $filter Filter for query.
     *
     * @return int Number of records that match filter criteria.
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function count(QueryFilter $filter = null): int
    {
        return \count($this->select($filter));
    }

    /**
     * Executes select query and returns first result.
     *
     * @param QueryFilter $filter Filter for query.
     *
     * @return Entity|null First found entity or NULL.
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function selectOne(QueryFilter $filter = null): ?Entity
    {
        if ($filter === null) {
            $filter = new QueryFilter();
        }

        $filter->setLimit(1);
        $results = $this->select($filter);

        return empty($results) ? null : $results[0];
    }

    /**
     * Selects all Packlink entities in the system and encodes the result.
     *
     * @return string Encoded entities.
     */
    public function encodeAllEntities(): string
    {
        $queryBuilder = DB::table($this->getTableName())->select();

        $records = $queryBuilder->get()->toArray();

        return !empty($records) ? json_encode($records) : '';
    }

    /**
     * Executes select query.
     *
     * @param QueryFilter $filter Filter for query.
     *
     * @return Entity[] A list of found entities ot empty array.
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function select(QueryFilter $filter = null): array
    {
        $queryBuilder = $this->getBaseQueryBuilder($filter);

        $baseEntities = $queryBuilder->get()->toArray();

        return $this->transformEntities($baseEntities);
    }

    /**
     * @inheritDoc
     */
    public function deleteWhere(QueryFilter $filter = null)
    {
        $query = $this->getBaseQueryBuilder($filter);
        $query->delete();
    }

    /**
     * Returns the name of database table that this repository should query.
     *
     * @return string
     */
    protected function getTableName(): string
    {
        return static::TABLE_NAME;
    }

    /**
     * Prepares data for inserting a new record or updating an existing one.
     *
     * @param Entity $entity Packlink entity object.
     *
     * @return array Prepared entity array.
     */
    protected function prepareDataForInsertOrUpdate(Entity $entity): array
    {
        $preparedEntity = [];
        $preparedEntity['data'] = $this->serializeData($entity);
        $indexes = IndexHelper::transformFieldsToIndexes($entity);

        foreach ($indexes as $index => $value) {
            $indexField = 'index_' . $index;
            $preparedEntity[$indexField] = $value;
        }

        return $preparedEntity;
    }

    /**
     * Transforms an array of records to an array of Packlink entities.
     *
     * @param array $records Array of records.
     *
     * @return Entity[] Array of Packlink entities.
     */
    protected function transformEntities(array $records): array
    {
        $entities = [];
        foreach ($records as $record) {
            $entities[] = $this->transformEntity($record);
        }

        return $entities;
    }

    /**
     * Returns index mapped to given property.
     *
     * @param string $property Property name.
     *
     * @return string|null Index column in Packlink entity table, or null if it doesn't exist.
     */
    protected function getIndexMapping($property): ?string
    {
        $indexMapping = IndexHelper::mapFieldsToIndexes(new $this->entityClass);

        if (array_key_exists($property, $indexMapping)) {
            return 'index_' . $indexMapping[$property];
        }

        return null;
    }

    /**
     * Serializes Packlink entity to string.
     *
     * @param Entity $entity Packlink entity object to be serialized
     *
     * @return string Serialized entity
     */
    private function serializeData(Entity $entity): string
    {
        return json_encode($entity->toArray());
    }

    /**
     * Builds WHERE condition part of SELECT query.
     *
     * @param Builder $queryBuilder Eloquent query builder.
     * @param QueryFilter $filter Packlink query filter.
     * @param array $indexMap Array of field index mappings.
     *
     * @return Builder Updated eloquent query builder.
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    private function buildWhereCondition(Builder $queryBuilder, QueryFilter $filter, array $indexMap): Builder
    {
        foreach ($filter->getConditions() as $condition) {
            if ($condition->getColumn() === 'id') {
                $queryBuilder->where('id', $condition->getValue());
                continue;
            }

            if (!array_key_exists($condition->getColumn(), $indexMap)) {
                throw new QueryFilterInvalidParamException(
                    "Field {$condition->getColumn()} is not indexed!"
                );
            }

            $queryBuilder = $this->addCondition($queryBuilder, $condition, $indexMap);
        }

        return $queryBuilder;
    }

    /**
     * Adds a single AND condition to SELECT query.
     *
     * @param Builder $queryBuilder Eloquent query builder.
     * @param QueryCondition $condition Packlink query condition.
     * @param array $indexMap Array of field index mappings.
     *
     * @return Builder Updated eloquent query builder.
     */
    private function addCondition(Builder $queryBuilder, QueryCondition $condition, array $indexMap): Builder
    {
        $isChainOrOperator = $condition->getChainOperator() === 'OR';
        $column = $condition->getColumn();
        $columnName = 'index_' . $indexMap[$column];
        $conditionValue = IndexHelper::castFieldValue($condition->getValue(), $condition->getValueType());
        switch ($condition->getOperator()) {
            case Operators::NULL:
                return $isChainOrOperator ? $queryBuilder->orWhereNull($columnName)
                    : $queryBuilder->whereNull($columnName);
            case Operators::NOT_NULL:
                return $isChainOrOperator ? $queryBuilder->orWhereNotNull($columnName)
                    : $queryBuilder->whereNotNull($columnName);
            case Operators::IN:
                return $isChainOrOperator ? $queryBuilder->orWhereIn($columnName, $conditionValue)
                    : $queryBuilder->whereIn($columnName, $conditionValue);
            case Operators::NOT_IN:
                return $isChainOrOperator ? $queryBuilder->orWhereNotIn($columnName, $conditionValue)
                    : $queryBuilder->whereNotIn($columnName, $conditionValue);
            default:
                return $isChainOrOperator ? $queryBuilder->orWhere(
                    $columnName,
                    $condition->getOperator(),
                    $conditionValue
                )
                    : $queryBuilder->where($columnName, $condition->getOperator(), $conditionValue);
        }
    }

    /**
     * Builds ORDER BY part of SELECT query.
     *
     * @param Builder $queryBuilder Eloquent query builder.
     * @param QueryFilter $filter Packlink query filter.
     * @param array $indexMap Array of field index mappings.
     *
     * @return Builder Updated eloquent query builder.
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    private function buildOrderBy(Builder $queryBuilder, QueryFilter $filter, array $indexMap): Builder
    {
        $orderByColumn = $filter->getOrderByColumn();

        if ($orderByColumn) {
            $indexedColumn = null;
            if ($orderByColumn === 'id') {
                $indexedColumn = 'id';
            } elseif (array_key_exists($orderByColumn, $indexMap)) {
                $indexedColumn = 'index_' . $indexMap[$orderByColumn];
            }

            if ($indexedColumn === null) {
                throw new QueryFilterInvalidParamException(
                    "Unknown or not indexed OrderBy column $orderByColumn"
                );
            }

            $queryBuilder->orderBy($indexedColumn, $filter->getOrderDirection());
        }

        return $queryBuilder;
    }

    /**
     * Transforms record to Packlink entity.
     *
     * @param \stdClass $record Database record.
     *
     * @return Entity Packlink entity.
     */
    protected function transformEntity(\stdClass $record): Entity
    {
        $jsonEntity = json_decode($record->data, true);
        if (array_key_exists('class_name', $jsonEntity)) {
            $entity = new $jsonEntity['class_name'];
        } else {
            $entity = new $this->entityClass;
        }

        /** @var Entity $entity */
        $entity->inflate($jsonEntity);
        if (!empty($record->id)) {
            $entity->setId($record->id);
        }

        return $entity;
    }

    /**
     * Retrieves base query builder.
     *
     * @param \Logeecom\Infrastructure\ORM\QueryFilter\QueryFilter|null $filter
     *
     * @return \Illuminate\Database\Query\Builder
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected function getBaseQueryBuilder(?QueryFilter $filter): Builder
    {
        /** @var Entity $entity */
        $entity = new $this->entityClass;

        $queryBuilder = DB::table($this->getTableName())
            ->where('type', $entity->getConfig()->getType());

        if ($filter !== null) {
            $indexMap = IndexHelper::mapFieldsToIndexes($entity);
            $queryBuilder = $this->buildWhereCondition($queryBuilder, $filter, $indexMap);

            if ($filter->getLimit()) {
                $queryBuilder->offset($filter->getOffset());
                $queryBuilder->take($filter->getLimit());
            }

            $this->buildOrderBy($queryBuilder, $filter, $indexMap);
        }

        return $queryBuilder;
    }
}
