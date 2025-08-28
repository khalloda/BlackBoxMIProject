<?php
/**
 * Product Model
 * 
 * Handles product management with full CRUD operations
 */

namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'code',
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'classification_id',
        'brand_id',
        'color_id',
        'car_make_id',
        'car_model_id',
        'part_number',
        'barcode',
        'unit_of_measure',
        'cost_price',
        'selling_price',
        'min_stock_level',
        'max_stock_level',
        'reorder_level',
        'weight',
        'dimensions',
        'image_url',
        'is_active'
    ];
    
    protected $casts = [
        'cost_price' => 'float',
        'selling_price' => 'float',
        'min_stock_level' => 'integer',
        'max_stock_level' => 'integer',
        'reorder_level' => 'integer',
        'weight' => 'float',
        'is_active' => 'boolean'
    ];
    
    protected $rules = [
        'code' => 'required|unique:products',
        'name_en' => 'required',
        'name_ar' => 'required',
        'cost_price' => 'numeric',
        'selling_price' => 'numeric'
    ];

    /**
     * Get all active products
     */
    public function getActiveProducts()
    {
        return $this->where('is_active', true)
                   ->orderBy('name_en')
                   ->get();
    }

    /**
     * Search products
     */
    public function searchProducts($query, $limit = 20)
    {
        // Ensure limit is a positive integer to prevent SQL injection
        $limit = max(1, min(1000, (int)$limit));
        
        $sql = "SELECT p.*, c.name_en as classification_name, b.name as brand_name, 
                       col.name_en as color_name, cm.name as car_make_name, cmod.name as car_model_name
                FROM {$this->table} p
                LEFT JOIN classifications c ON p.classification_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN colors col ON p.color_id = col.id
                LEFT JOIN car_makes cm ON p.car_make_id = cm.id
                LEFT JOIN car_models cmod ON p.car_model_id = cmod.id
                WHERE (p.code LIKE :search1 OR p.name_en LIKE :search2 OR p.name_ar LIKE :search3 
                       OR p.part_number LIKE :search4 OR p.barcode LIKE :search5)
                AND p.is_active = 1
                ORDER BY p.name_en
                LIMIT {$limit}";
        
        $searchParam = '%' . $query . '%';
        return $this->db->select($sql, [
            'search1' => $searchParam,
            'search2' => $searchParam,
            'search3' => $searchParam,
            'search4' => $searchParam,
            'search5' => $searchParam
        ]);
    }

    /**
     * Get product with stock information
     */
    public function getProductWithStock($id)
    {
        $sql = "SELECT p.*, c.name_en as classification_name, b.name as brand_name,
                       col.name_en as color_name, cm.name as car_make_name, cmod.name as car_model_name,
                       COALESCE(SUM(s.quantity), 0) as total_stock,
                       COALESCE(SUM(s.reserved_quantity), 0) as reserved_stock,
                       COALESCE(SUM(s.available_quantity), 0) as available_stock
                FROM {$this->table} p
                LEFT JOIN classifications c ON p.classification_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN colors col ON p.color_id = col.id
                LEFT JOIN car_makes cm ON p.car_make_id = cm.id
                LEFT JOIN car_models cmod ON p.car_model_id = cmod.id
                LEFT JOIN stock s ON p.id = s.product_id
                WHERE p.id = :id
                GROUP BY p.id";
        
        return $this->db->selectOne($sql, ['id' => $id]);
    }

    /**
     * Generate next product code
     */
    public function generateProductCode($classificationCode = 'GEN')
    {
        $sequenceName = 'product_code_' . $classificationCode;
        return $this->db->getNextSequence($sequenceName);
    }

    /**
     * Check if product code exists
     */
    public function codeExists($code, $excludeId = null)
    {
        $sql = "SELECT id FROM {$this->table} WHERE code = :code";
        $params = ['code' => $code];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        return $this->db->selectOne($sql, $params) !== null;
    }

    /**
     * Check if barcode exists
     */
    public function barcodeExists($barcode, $excludeId = null)
    {
        if (empty($barcode)) return false;
        
        $sql = "SELECT id FROM {$this->table} WHERE barcode = :barcode";
        $params = ['barcode' => $barcode];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        return $this->db->selectOne($sql, $params) !== null;
    }

    /**
     * Validate product data
     */
    public function validateProduct($data, $id = null)
    {
        $errors = [];

        // Code validation
        if (empty($data['code'])) {
            $errors['code'] = 'Product code is required';
        } elseif ($this->codeExists($data['code'], $id)) {
            $errors['code'] = 'Product code already exists';
        }

        // Name validation
        if (empty($data['name_en'])) {
            $errors['name_en'] = 'English name is required';
        }
        
        if (empty($data['name_ar'])) {
            $errors['name_ar'] = 'Arabic name is required';
        }

        // Price validation
        if (!empty($data['cost_price']) && !is_numeric($data['cost_price'])) {
            $errors['cost_price'] = 'Cost price must be a number';
        }
        
        if (!empty($data['selling_price']) && !is_numeric($data['selling_price'])) {
            $errors['selling_price'] = 'Selling price must be a number';
        }

        // Stock level validation
        if (!empty($data['min_stock_level']) && (!is_numeric($data['min_stock_level']) || $data['min_stock_level'] < 0)) {
            $errors['min_stock_level'] = 'Minimum stock level must be a positive number';
        }

        // Barcode validation
        if (!empty($data['barcode']) && $this->barcodeExists($data['barcode'], $id)) {
            $errors['barcode'] = 'Barcode already exists';
        }

        return $errors;
    }

    /**
     * Create product with validation
     */
    public function createProduct($data)
    {
        // Validate data
        $errors = $this->validateProduct($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Set defaults
        $data['is_active'] = $data['is_active'] ?? true;
        $data['unit_of_measure'] = $data['unit_of_measure'] ?? 'PCS';
        $data['cost_price'] = $data['cost_price'] ?? 0;
        $data['selling_price'] = $data['selling_price'] ?? 0;
        $data['min_stock_level'] = $data['min_stock_level'] ?? 0;
        $data['max_stock_level'] = $data['max_stock_level'] ?? 0;
        $data['reorder_level'] = $data['reorder_level'] ?? 0;

        try {
            $id = $this->create($data);
            return ['success' => true, 'id' => $id];
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to create product']];
        }
    }

    /**
     * Update product with validation
     */
    public function updateProduct($id, $data)
    {
        // Validate data
        $errors = $this->validateProduct($data, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $success = $this->update($id, $data);
            return ['success' => $success];
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to update product']];
        }
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts($limit = 50)
    {
        // Ensure limit is a positive integer to prevent SQL injection
        $limit = max(1, min(1000, (int)$limit));
        
        $sql = "SELECT p.*, c.name_en as classification_name,
                       COALESCE(SUM(s.quantity), 0) as current_stock
                FROM {$this->table} p
                LEFT JOIN classifications c ON p.classification_id = c.id
                LEFT JOIN stock s ON p.id = s.product_id
                WHERE p.is_active = 1
                GROUP BY p.id
                HAVING current_stock <= p.min_stock_level
                ORDER BY (current_stock / NULLIF(p.min_stock_level, 0)) ASC
                LIMIT {$limit}";
        
        return $this->db->select($sql);
    }

    /**
     * Get product stock movements
     */
    public function getProductStockMovements($productId, $limit = 20)
    {
        // Ensure limit is a positive integer to prevent SQL injection
        $limit = max(1, min(100, (int)$limit));
        
        $sql = "SELECT sm.*, wl.name_en as location_name, w.name_en as warehouse_name,
                       u.full_name as created_by_name
                FROM stock_movements sm
                LEFT JOIN warehouse_locations wl ON sm.warehouse_location_id = wl.id
                LEFT JOIN warehouses w ON wl.warehouse_id = w.id
                LEFT JOIN users u ON sm.created_by = u.id
                WHERE sm.product_id = :product_id
                ORDER BY sm.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->select($sql, ['product_id' => $productId]);
    }

    /**
     * Get products by classification
     */
    public function getProductsByClassification($classificationId)
    {
        return $this->where('classification_id', $classificationId)
                   ->where('is_active', true)
                   ->orderBy('name_en')
                   ->get();
    }

    /**
     * Get products by car make and model
     */
    public function getProductsByCarMakeModel($carMakeId, $carModelId = null)
    {
        $query = $this->where('car_make_id', $carMakeId)
                     ->where('is_active', true);
        
        if ($carModelId) {
            $query = $query->where('car_model_id', $carModelId);
        }
        
        return $query->orderBy('name_en')->get();
    }
}
