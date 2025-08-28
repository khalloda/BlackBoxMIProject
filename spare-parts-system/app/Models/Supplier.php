<?php
/**
 * Supplier Model
 * 
 * Handles supplier management with full CRUD operations
 */

namespace App\Models;

use App\Core\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'code',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'fax',
        'address_en',
        'address_ar',
        'city',
        'country',
        'tax_number',
        'payment_terms',
        'is_active'
    ];
    
    protected $casts = [
        'payment_terms' => 'integer',
        'is_active' => 'boolean'
    ];
    
    protected $rules = [
        'code' => 'required|unique:suppliers',
        'company_name' => 'required',
        'email' => 'email',
        'payment_terms' => 'integer'
    ];

    /**
     * Get all active suppliers
     */
    public function getActiveSuppliers()
    {
        return $this->where('is_active', true)
                   ->orderBy('company_name')
                   ->get();
    }

    /**
     * Search suppliers
     */
    public function searchSuppliers($query, $limit = 20)
    {
        // Ensure limit is a positive integer to prevent SQL injection
        $limit = max(1, min(1000, (int)$limit));
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE (code LIKE :search1 OR company_name LIKE :search2 OR contact_person LIKE :search3 OR email LIKE :search4 OR phone LIKE :search5 OR mobile LIKE :search6)
                AND is_active = 1
                ORDER BY company_name
                LIMIT {$limit}";
        
        $searchParam = '%' . $query . '%';
        return $this->db->select($sql, [
            'search1' => $searchParam,
            'search2' => $searchParam,
            'search3' => $searchParam,
            'search4' => $searchParam,
            'search5' => $searchParam,
            'search6' => $searchParam
        ]);
    }

    /**
     * Get supplier with statistics
     */
    public function getSupplierWithStats($id)
    {
        $supplier = $this->find($id);
        if (!$supplier) return null;

        // Get supplier statistics
        $stats = $this->getSupplierStatistics($id);
        $supplier['stats'] = $stats;

        return $supplier;
    }

    /**
     * Get supplier statistics
     */
    public function getSupplierStatistics($supplierId)
    {
        $stats = [];

        try {
            // Total purchase orders
            $poResult = $this->db->selectOne(
                "SELECT COUNT(*) as count, SUM(total_amount) as total FROM purchase_orders WHERE supplier_id = :id",
                ['id' => $supplierId]
            );
            $stats['purchase_orders_count'] = $poResult ? (int)$poResult['count'] : 0;
            $stats['purchase_orders_total'] = $poResult ? (float)$poResult['total'] : 0;

            // Total payments made
            $paymentsResult = $this->db->selectOne(
                "SELECT COUNT(*) as count, SUM(amount) as total FROM supplier_payments WHERE supplier_id = :id",
                ['id' => $supplierId]
            );
            $stats['payments_count'] = $paymentsResult ? (int)$paymentsResult['count'] : 0;
            $stats['payments_total'] = $paymentsResult ? (float)$paymentsResult['total'] : 0;

            // Outstanding balance
            $outstandingResult = $this->db->selectOne(
                "SELECT SUM(total_amount - paid_amount) as balance FROM purchase_orders WHERE supplier_id = :id AND status != 'cancelled'",
                ['id' => $supplierId]
            );
            $stats['outstanding_balance'] = $outstandingResult ? (float)$outstandingResult['balance'] : 0;

            // Last transaction date
            $lastTransactionResult = $this->db->selectOne(
                "SELECT MAX(created_at) as last_transaction FROM purchase_orders WHERE supplier_id = :id",
                ['id' => $supplierId]
            );
            $stats['last_transaction'] = $lastTransactionResult ? $lastTransactionResult['last_transaction'] : null;

        } catch (\Exception $e) {
            error_log("Supplier statistics error: " . $e->getMessage());
            $stats = [
                'purchase_orders_count' => 0,
                'purchase_orders_total' => 0,
                'payments_count' => 0,
                'payments_total' => 0,
                'outstanding_balance' => 0,
                'last_transaction' => null
            ];
        }

        return $stats;
    }

    /**
     * Generate next supplier code
     */
    public function generateSupplierCode()
    {
        return $this->db->getNextSequence('supplier_code');
    }

    /**
     * Check if supplier code exists
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
     * Validate supplier data
     */
    public function validateSupplier($data, $id = null)
    {
        $errors = [];

        // Code validation
        if (empty($data['code'])) {
            $errors['code'] = 'Supplier code is required';
        } elseif ($this->codeExists($data['code'], $id)) {
            $errors['code'] = 'Supplier code already exists';
        }

        // Company name validation
        if (empty($data['company_name'])) {
            $errors['company_name'] = 'Company name is required';
        }

        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        // Payment terms validation
        if (!empty($data['payment_terms']) && (!is_numeric($data['payment_terms']) || $data['payment_terms'] < 0)) {
            $errors['payment_terms'] = 'Payment terms must be a positive number';
        }

        return $errors;
    }

    /**
     * Create supplier with validation
     */
    public function createSupplier($data)
    {
        // Validate data
        $errors = $this->validateSupplier($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = $this->generateSupplierCode();
        }

        // Set defaults
        $data['is_active'] = $data['is_active'] ?? true;
        $data['payment_terms'] = $data['payment_terms'] ?? 30;

        try {
            $id = $this->create($data);
            return ['success' => true, 'id' => $id];
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to create supplier']];
        }
    }

    /**
     * Update supplier with validation
     */
    public function updateSupplier($id, $data)
    {
        // Validate data
        $errors = $this->validateSupplier($data, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $success = $this->update($id, $data);
            return ['success' => $success];
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to update supplier']];
        }
    }

    /**
     * Get supplier purchase orders
     */
    public function getSupplierPurchaseOrders($supplierId, $limit = 10)
    {
        // Ensure limit is a positive integer to prevent SQL injection
        $limit = max(1, min(100, (int)$limit));
        
        $sql = "SELECT po.*, u.full_name as created_by_name
                FROM purchase_orders po
                LEFT JOIN users u ON po.created_by = u.id
                WHERE po.supplier_id = :supplier_id
                ORDER BY po.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->select($sql, [
            'supplier_id' => $supplierId
        ]);
    }

    /**
     * Get supplier payments
     */
    public function getSupplierPayments($supplierId, $limit = 10)
    {
        // Ensure limit is a positive integer to prevent SQL injection
        $limit = max(1, min(100, (int)$limit));
        
        $sql = "SELECT sp.*, po.po_number, u.full_name as created_by_name
                FROM supplier_payments sp
                LEFT JOIN purchase_orders po ON sp.purchase_order_id = po.id
                LEFT JOIN users u ON sp.created_by = u.id
                WHERE sp.supplier_id = :supplier_id
                ORDER BY sp.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->select($sql, [
            'supplier_id' => $supplierId
        ]);
    }
}
