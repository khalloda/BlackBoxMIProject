<?php
/**
 * Client Model
 * 
 * Handles client management with full CRUD operations
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Client extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'code',
        'type',
        'company_name',
        'contact_person',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'fax',
        'address_en',
        'address_ar',
        'city',
        'country',
        'tax_number',
        'credit_limit',
        'payment_terms',
        'discount_percentage',
        'is_active'
    ];
    
    protected $casts = [
        'credit_limit' => 'float',
        'payment_terms' => 'integer',
        'discount_percentage' => 'float',
        'is_active' => 'boolean'
    ];
    
    protected $rules = [
        'code' => 'required|unique:clients',
        'type' => 'required',
        'email' => 'email',
        'credit_limit' => 'numeric',
        'payment_terms' => 'integer',
        'discount_percentage' => 'numeric'
    ];

    /**
     * Get all active clients
     */
    public function getActiveClients()
    {
        return $this->where('is_active', true)
                   ->orderBy('company_name')
                   ->orderBy('first_name')
                   ->get();
    }

    /**
     * Search clients
     */
    public function searchClients($query, $limit = 20)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (code LIKE :query 
                   OR company_name LIKE :query 
                   OR contact_person LIKE :query
                   OR first_name LIKE :query
                   OR last_name LIKE :query
                   OR email LIKE :query
                   OR phone LIKE :query
                   OR mobile LIKE :query)
                AND is_active = 1
                ORDER BY company_name, first_name
                LIMIT :limit";
        
        return $this->db->select($sql, [
            'query' => '%' . $query . '%',
            'limit' => $limit
        ]);
    }

    /**
     * Get client with statistics
     */
    public function getClientWithStats($id)
    {
        $client = $this->find($id);
        if (!$client) return null;

        // Get client statistics
        $stats = $this->getClientStatistics($id);
        $client['stats'] = $stats;

        return $client;
    }

    /**
     * Get client statistics
     */
    public function getClientStatistics($clientId)
    {
        $stats = [];

        try {
            // Total quotes
            $quotesResult = $this->db->selectOne(
                "SELECT COUNT(*) as count, SUM(total_amount) as total FROM quotes WHERE client_id = :id",
                ['id' => $clientId]
            );
            $stats['quotes_count'] = $quotesResult ? (int)$quotesResult['count'] : 0;
            $stats['quotes_total'] = $quotesResult ? (float)$quotesResult['total'] : 0;

            // Total orders
            $ordersResult = $this->db->selectOne(
                "SELECT COUNT(*) as count, SUM(total_amount) as total FROM sales_orders WHERE client_id = :id",
                ['id' => $clientId]
            );
            $stats['orders_count'] = $ordersResult ? (int)$ordersResult['count'] : 0;
            $stats['orders_total'] = $ordersResult ? (float)$ordersResult['total'] : 0;

            // Total invoices
            $invoicesResult = $this->db->selectOne(
                "SELECT COUNT(*) as count, SUM(total_amount) as total, SUM(balance_amount) as balance FROM invoices WHERE client_id = :id",
                ['id' => $clientId]
            );
            $stats['invoices_count'] = $invoicesResult ? (int)$invoicesResult['count'] : 0;
            $stats['invoices_total'] = $invoicesResult ? (float)$invoicesResult['total'] : 0;
            $stats['outstanding_balance'] = $invoicesResult ? (float)$invoicesResult['balance'] : 0;

            // Total payments
            $paymentsResult = $this->db->selectOne(
                "SELECT COUNT(*) as count, SUM(amount) as total FROM payments WHERE client_id = :id",
                ['id' => $clientId]
            );
            $stats['payments_count'] = $paymentsResult ? (int)$paymentsResult['count'] : 0;
            $stats['payments_total'] = $paymentsResult ? (float)$paymentsResult['total'] : 0;

            // Last transaction date
            $lastTransactionResult = $this->db->selectOne(
                "SELECT MAX(created_at) as last_transaction FROM (
                    SELECT created_at FROM quotes WHERE client_id = :id
                    UNION ALL
                    SELECT created_at FROM sales_orders WHERE client_id = :id
                    UNION ALL
                    SELECT created_at FROM invoices WHERE client_id = :id
                    UNION ALL
                    SELECT created_at FROM payments WHERE client_id = :id
                ) as transactions",
                ['id' => $clientId]
            );
            $stats['last_transaction'] = $lastTransactionResult ? $lastTransactionResult['last_transaction'] : null;

        } catch (\Exception $e) {
            error_log("Client statistics error: " . $e->getMessage());
            $stats = [
                'quotes_count' => 0,
                'quotes_total' => 0,
                'orders_count' => 0,
                'orders_total' => 0,
                'invoices_count' => 0,
                'invoices_total' => 0,
                'outstanding_balance' => 0,
                'payments_count' => 0,
                'payments_total' => 0,
                'last_transaction' => null
            ];
        }

        return $stats;
    }

    /**
     * Generate next client code
     */
    public function generateClientCode()
    {
        return $this->db->getNextSequence('client_code');
    }

    /**
     * Check if client code exists
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
     * Get clients by type
     */
    public function getClientsByType($type)
    {
        return $this->where('type', $type)
                   ->where('is_active', true)
                   ->orderBy('company_name')
                   ->orderBy('first_name')
                   ->get();
    }

    /**
     * Get client display name
     */
    public function getDisplayName($client)
    {
        if (is_array($client)) {
            if ($client['type'] === 'company') {
                return $client['company_name'];
            } else {
                return trim($client['first_name'] . ' ' . $client['last_name']);
            }
        }
        return '';
    }

    /**
     * Get clients with outstanding balances
     */
    public function getClientsWithOutstandingBalances()
    {
        $sql = "SELECT c.*, SUM(i.balance_amount) as outstanding_balance
                FROM {$this->table} c
                INNER JOIN invoices i ON c.id = i.client_id
                WHERE c.is_active = 1 AND i.balance_amount > 0
                GROUP BY c.id
                HAVING outstanding_balance > 0
                ORDER BY outstanding_balance DESC";
        
        return $this->db->select($sql);
    }

    /**
     * Get top clients by sales
     */
    public function getTopClientsBySales($limit = 10)
    {
        $sql = "SELECT c.*, SUM(i.total_amount) as total_sales
                FROM {$this->table} c
                INNER JOIN invoices i ON c.id = i.client_id
                WHERE c.is_active = 1 AND i.status != 'cancelled'
                GROUP BY c.id
                ORDER BY total_sales DESC
                LIMIT :limit";
        
        return $this->db->select($sql, ['limit' => $limit]);
    }

    /**
     * Validate client data
     */
    public function validateClient($data, $id = null)
    {
        $errors = [];

        // Code validation
        if (empty($data['code'])) {
            $errors['code'] = 'Client code is required';
        } elseif ($this->codeExists($data['code'], $id)) {
            $errors['code'] = 'Client code already exists';
        }

        // Type validation
        if (empty($data['type'])) {
            $errors['type'] = 'Client type is required';
        } elseif (!in_array($data['type'], ['company', 'individual'])) {
            $errors['type'] = 'Invalid client type';
        }

        // Company name validation for company type
        if ($data['type'] === 'company' && empty($data['company_name'])) {
            $errors['company_name'] = 'Company name is required for company clients';
        }

        // Individual name validation for individual type
        if ($data['type'] === 'individual') {
            if (empty($data['first_name'])) {
                $errors['first_name'] = 'First name is required for individual clients';
            }
            if (empty($data['last_name'])) {
                $errors['last_name'] = 'Last name is required for individual clients';
            }
        }

        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        // Credit limit validation
        if (!empty($data['credit_limit']) && !is_numeric($data['credit_limit'])) {
            $errors['credit_limit'] = 'Credit limit must be a number';
        }

        // Payment terms validation
        if (!empty($data['payment_terms']) && (!is_numeric($data['payment_terms']) || $data['payment_terms'] < 0)) {
            $errors['payment_terms'] = 'Payment terms must be a positive number';
        }

        // Discount percentage validation
        if (!empty($data['discount_percentage'])) {
            if (!is_numeric($data['discount_percentage'])) {
                $errors['discount_percentage'] = 'Discount percentage must be a number';
            } elseif ($data['discount_percentage'] < 0 || $data['discount_percentage'] > 100) {
                $errors['discount_percentage'] = 'Discount percentage must be between 0 and 100';
            }
        }

        return $errors;
    }

    /**
     * Create client with validation
     */
    public function createClient($data)
    {
        // Validate data
        $errors = $this->validateClient($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = $this->generateClientCode();
        }

        // Set defaults
        $data['is_active'] = $data['is_active'] ?? true;
        $data['credit_limit'] = $data['credit_limit'] ?? 0;
        $data['payment_terms'] = $data['payment_terms'] ?? 30;
        $data['discount_percentage'] = $data['discount_percentage'] ?? 0;

        try {
            $id = $this->create($data);
            return ['success' => true, 'id' => $id];
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to create client']];
        }
    }

    /**
     * Update client with validation
     */
    public function updateClient($id, $data)
    {
        // Validate data
        $errors = $this->validateClient($data, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $success = $this->update($id, $data);
            return ['success' => $success];
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to update client']];
        }
    }

    /**
     * Get client quotes
     */
    public function getClientQuotes($clientId, $limit = 10)
    {
        $sql = "SELECT q.*, u.full_name as created_by_name
                FROM quotes q
                LEFT JOIN users u ON q.created_by = u.id
                WHERE q.client_id = :client_id
                ORDER BY q.created_at DESC
                LIMIT :limit";
        
        return $this->db->select($sql, [
            'client_id' => $clientId,
            'limit' => $limit
        ]);
    }

    /**
     * Get client orders
     */
    public function getClientOrders($clientId, $limit = 10)
    {
        $sql = "SELECT so.*, u.full_name as created_by_name
                FROM sales_orders so
                LEFT JOIN users u ON so.created_by = u.id
                WHERE so.client_id = :client_id
                ORDER BY so.created_at DESC
                LIMIT :limit";
        
        return $this->db->select($sql, [
            'client_id' => $clientId,
            'limit' => $limit
        ]);
    }

    /**
     * Get client invoices
     */
    public function getClientInvoices($clientId, $limit = 10)
    {
        $sql = "SELECT i.*, u.full_name as created_by_name
                FROM invoices i
                LEFT JOIN users u ON i.created_by = u.id
                WHERE i.client_id = :client_id
                ORDER BY i.created_at DESC
                LIMIT :limit";
        
        return $this->db->select($sql, [
            'client_id' => $clientId,
            'limit' => $limit
        ]);
    }

    /**
     * Get client payments
     */
    public function getClientPayments($clientId, $limit = 10)
    {
        $sql = "SELECT p.*, i.invoice_number, u.full_name as created_by_name
                FROM payments p
                LEFT JOIN invoices i ON p.invoice_id = i.id
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.client_id = :client_id
                ORDER BY p.created_at DESC
                LIMIT :limit";
        
        return $this->db->select($sql, [
            'client_id' => $clientId,
            'limit' => $limit
        ]);
    }
}
