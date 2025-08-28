<?php
/**
 * Quote Model
 * 
 * Handles quote management with full CRUD operations
 */

namespace App\Models;

use App\Core\Model;

class Quote extends Model
{
    protected $table = 'quotes';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'quote_number',
        'client_id',
        'quote_date',
        'valid_until',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'notes',
        'terms_conditions',
        'created_by',
        'approved_by',
        'approved_at'
    ];
    
    protected $casts = [
        'subtotal' => 'float',
        'discount_value' => 'float',
        'discount_amount' => 'float',
        'tax_percentage' => 'float',
        'tax_amount' => 'float',
        'total_amount' => 'float',
        'quote_date' => 'date',
        'valid_until' => 'date',
        'approved_at' => 'datetime'
    ];
    
    protected $rules = [
        'quote_number' => 'required|unique:quotes',
        'client_id' => 'required',
        'quote_date' => 'required',
        'status' => 'required'
    ];

    /**
     * Get quote with client and items
     */
    public function getQuoteWithDetails($id)
    {
        $sql = "SELECT q.*, c.company_name, c.contact_person, c.first_name, c.last_name,
                       c.email, c.phone, c.address_en, c.address_ar, c.city, c.country,
                       u.full_name as created_by_name, a.full_name as approved_by_name
                FROM {$this->table} q
                LEFT JOIN clients c ON q.client_id = c.id
                LEFT JOIN users u ON q.created_by = u.id
                LEFT JOIN users a ON q.approved_by = a.id
                WHERE q.id = :id";
        
        $quote = $this->db->selectOne($sql, ['id' => $id]);
        
        if ($quote) {
            // Get quote items
            $quote['items'] = $this->getQuoteItems($id);
            
            // Format client name
            $quote['client_name'] = $quote['company_name'] ?: 
                                   trim($quote['first_name'] . ' ' . $quote['last_name']);
        }
        
        return $quote;
    }

    /**
     * Get quote items
     */
    public function getQuoteItems($quoteId)
    {
        $sql = "SELECT qi.*, p.code as product_code, p.name_en as product_name_en, 
                       p.name_ar as product_name_ar, p.unit_of_measure
                FROM quote_items qi
                LEFT JOIN products p ON qi.product_id = p.id
                WHERE qi.quote_id = :quote_id
                ORDER BY qi.id";
        
        return $this->db->select($sql, ['quote_id' => $quoteId]);
    }

    /**
     * Generate next quote number
     */
    public function generateQuoteNumber()
    {
        return $this->db->getNextSequence('quote_number');
    }

    /**
     * Check if quote number exists
     */
    public function quoteNumberExists($quoteNumber, $excludeId = null)
    {
        $sql = "SELECT id FROM {$this->table} WHERE quote_number = :quote_number";
        $params = ['quote_number' => $quoteNumber];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        return $this->db->selectOne($sql, $params) !== null;
    }

    /**
     * Create quote with items
     */
    public function createQuoteWithItems($quoteData, $items)
    {
        try {
            $this->db->beginTransaction();
            
            // Create quote
            $quoteId = $this->create($quoteData);
            
            // Create quote items
            foreach ($items as $item) {
                $item['quote_id'] = $quoteId;
                $this->createQuoteItem($item);
            }
            
            // Update quote totals
            $this->updateQuoteTotals($quoteId);
            
            $this->db->commit();
            return ['success' => true, 'id' => $quoteId];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update quote with items
     */
    public function updateQuoteWithItems($quoteId, $quoteData, $items)
    {
        try {
            $this->db->beginTransaction();
            
            // Update quote
            $this->update($quoteId, $quoteData);
            
            // Delete existing items
            $this->db->execute("DELETE FROM quote_items WHERE quote_id = :quote_id", ['quote_id' => $quoteId]);
            
            // Create new items
            foreach ($items as $item) {
                $item['quote_id'] = $quoteId;
                $this->createQuoteItem($item);
            }
            
            // Update quote totals
            $this->updateQuoteTotals($quoteId);
            
            $this->db->commit();
            return ['success' => true];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create quote item
     */
    private function createQuoteItem($itemData)
    {
        // Calculate line total
        $quantity = $itemData['quantity'];
        $unitPrice = $itemData['unit_price'];
        $discountAmount = $itemData['discount_amount'] ?? 0;
        $taxAmount = $itemData['tax_amount'] ?? 0;
        
        $lineTotal = ($quantity * $unitPrice) - $discountAmount + $taxAmount;
        $itemData['line_total'] = $lineTotal;
        
        $this->db->insert('quote_items', $itemData);
    }

    /**
     * Update quote totals
     */
    private function updateQuoteTotals($quoteId)
    {
        $sql = "SELECT SUM(line_total) as subtotal FROM quote_items WHERE quote_id = :quote_id";
        $result = $this->db->selectOne($sql, ['quote_id' => $quoteId]);
        $subtotal = $result ? $result['subtotal'] : 0;
        
        // Get quote for discount and tax calculation
        $quote = $this->find($quoteId);
        
        // Calculate discount
        $discountAmount = 0;
        if ($quote['discount_type'] === 'percentage') {
            $discountAmount = $subtotal * ($quote['discount_value'] / 100);
        } else {
            $discountAmount = $quote['discount_value'];
        }
        
        // Calculate tax on subtotal after discount
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $taxableAmount * ($quote['tax_percentage'] / 100);
        
        // Calculate total
        $totalAmount = $subtotal - $discountAmount + $taxAmount;
        
        // Update quote
        $this->update($quoteId, [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount
        ]);
    }

    /**
     * Approve quote
     */
    public function approveQuote($quoteId, $approvedBy)
    {
        return $this->update($quoteId, [
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Reject quote
     */
    public function rejectQuote($quoteId)
    {
        return $this->update($quoteId, [
            'status' => 'rejected'
        ]);
    }

    /**
     * Convert quote to sales order
     */
    public function convertToSalesOrder($quoteId)
    {
        $quote = $this->getQuoteWithDetails($quoteId);
        if (!$quote || $quote['status'] !== 'approved') {
            return ['success' => false, 'error' => 'Quote must be approved before conversion'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Create sales order
            $salesOrderModel = new SalesOrder();
            $orderData = [
                'order_number' => $salesOrderModel->generateOrderNumber(),
                'quote_id' => $quoteId,
                'client_id' => $quote['client_id'],
                'order_date' => date('Y-m-d'),
                'status' => 'open',
                'subtotal' => $quote['subtotal'],
                'discount_type' => $quote['discount_type'],
                'discount_value' => $quote['discount_value'],
                'discount_amount' => $quote['discount_amount'],
                'tax_percentage' => $quote['tax_percentage'],
                'tax_amount' => $quote['tax_amount'],
                'total_amount' => $quote['total_amount'],
                'notes' => $quote['notes'],
                'created_by' => $quote['created_by']
            ];
            
            $orderId = $salesOrderModel->create($orderData);
            
            // Create sales order items
            foreach ($quote['items'] as $item) {
                $orderItemData = [
                    'sales_order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_type' => $item['discount_type'],
                    'discount_value' => $item['discount_value'],
                    'discount_amount' => $item['discount_amount'],
                    'tax_percentage' => $item['tax_percentage'],
                    'tax_amount' => $item['tax_amount'],
                    'line_total' => $item['line_total'],
                    'notes' => $item['notes']
                ];
                
                $this->db->insert('sales_order_items', $orderItemData);
            }
            
            $this->db->commit();
            return ['success' => true, 'order_id' => $orderId];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get quotes by status
     */
    public function getQuotesByStatus($status, $limit = 20)
    {
        // Ensure limit is a positive integer to prevent SQL injection
        $limit = max(1, min(100, (int)$limit));
        
        $sql = "SELECT q.*, c.company_name, c.contact_person, c.first_name, c.last_name,
                       u.full_name as created_by_name
                FROM {$this->table} q
                LEFT JOIN clients c ON q.client_id = c.id
                LEFT JOIN users u ON q.created_by = u.id
                WHERE q.status = :status
                ORDER BY q.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->select($sql, ['status' => $status]);
    }

    /**
     * Get expired quotes
     */
    public function getExpiredQuotes()
    {
        $sql = "SELECT q.*, c.company_name, c.contact_person, c.first_name, c.last_name
                FROM {$this->table} q
                LEFT JOIN clients c ON q.client_id = c.id
                WHERE q.status = 'sent' AND q.valid_until < CURDATE()
                ORDER BY q.valid_until ASC";
        
        return $this->db->select($sql);
    }

    /**
     * Mark expired quotes
     */
    public function markExpiredQuotes()
    {
        return $this->db->execute(
            "UPDATE {$this->table} SET status = 'expired' WHERE status = 'sent' AND valid_until < CURDATE()"
        );
    }
}
