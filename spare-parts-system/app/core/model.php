<?php
/**
 * Base Model Class
 * 
 * Provides database access and common functionality for all models
 */

namespace App\Core;

abstract class Model
{
    protected static $database = null;
    protected $table = '';
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $casts = [];
    protected $rules = [];

    /**
     * Set the database instance
     */
    public static function setDatabase($database)
    {
        self::$database = $database;
    }

    /**
     * Get the database instance
     */
    protected static function db()
    {
        if (self::$database === null) {
            throw new \Exception('Database not initialized. Call Model::setDatabase() first.');
        }
        return self::$database;
    }

    /**
     * Get all records
     */
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        return self::db()->select($sql);
    }

    /**
     * Find a record by ID
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return self::db()->selectOne($sql, ['id' => $id]);
    }

    /**
     * Create a new record
     */
    public function create($data)
    {
        // Filter only fillable fields
        $filteredData = [];
        foreach ($this->fillable as $field) {
            if (isset($data[$field])) {
                $filteredData[$field] = $data[$field];
            }
        }

        // Add timestamps
        $filteredData['created_at'] = date('Y-m-d H:i:s');
        $filteredData['updated_at'] = date('Y-m-d H:i:s');

        return self::db()->insert($this->table, $filteredData);
    }

    /**
     * Update a record
     */
    public function update($id, $data)
    {
        // Filter only fillable fields
        $filteredData = [];
        foreach ($this->fillable as $field) {
            if (isset($data[$field])) {
                $filteredData[$field] = $data[$field];
            }
        }

        // Add timestamp
        $filteredData['updated_at'] = date('Y-m-d H:i:s');

        return self::db()->update($this->table, $filteredData, [$this->primaryKey => $id]);
    }

    /**
     * Delete a record
     */
    public function delete($id)
    {
        return self::db()->delete($this->table, [$this->primaryKey => $id]);
    }

    /**
     * Paginate records
     */
    public static function paginate($page = 1, $perPage = 20, $filters = [])
    {
        $instance = new static();
        $table = $instance->table;
        
        // Build WHERE clause
        $whereClause = '';
        $params = [];
        
        if (!empty($filters)) {
            $conditions = [];
            foreach ($filters as $field => $value) {
                if ($value !== null && $value !== '') {
                    $conditions[] = "{$field} LIKE :{$field}";
                    $params[$field] = '%' . $value . '%';
                }
            }
            if (!empty($conditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            }
        }

        // Count total records
        $countSql = "SELECT COUNT(*) as total FROM {$table} {$whereClause}";
        $totalResult = self::db()->selectOne($countSql, $params);
        $total = $totalResult['total'];

        // Calculate pagination
        $offset = ($page - 1) * $perPage;
        $totalPages = ceil($total / $perPage);

        // Get records
        $sql = "SELECT * FROM {$table} {$whereClause} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $stmt = self::db()->prepare($sql);
        foreach ($params as $key => $value) {
            if (in_array($key, ['limit', 'offset'])) {
                $stmt->bindValue(":{$key}", (int)$value, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":{$key}", $value);
            }
        }
        $stmt->execute();
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'data' => $records,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_previous' => $page > 1,
                'has_next' => $page < $totalPages,
                'previous_page' => $page > 1 ? $page - 1 : null,
                'next_page' => $page < $totalPages ? $page + 1 : null
            ]
        ];
    }

    /**
     * Where clause builder
     */
    public function where($field, $operator, $value = null)
    {
        // Simple implementation for basic queries
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $sql = "SELECT * FROM {$this->table} WHERE {$field} {$operator} :value";
        return self::db()->select($sql, ['value' => $value]);
    }

    /**
     * Order by clause
     */
    public function orderBy($field, $direction = 'ASC')
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$field} {$direction}";
        return self::db()->select($sql);
    }

    /**
     * Get records with limit
     */
    public function limit($count)
    {
        $sql = "SELECT * FROM {$this->table} LIMIT :limit";
        $stmt = self::db()->prepare($sql);
        $stmt->bindValue(':limit', (int)$count, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
