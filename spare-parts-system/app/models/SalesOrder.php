<?php
/**
 * Sales Order Model
 * 
 * Handles sales order management with full CRUD operations
 */

namespace App\Models;

use App\Core\Model;

class SalesOrder extends Model
{
    protected $table = 'sales_orders';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'order_number',
        'quote_id',
        'client_id',
        'order_date',
        'delivery_date',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'shipping_address',
        'notes',
        'created_by'
    ];
    
    protected $casts = [
        'subtotal' => 'float',
        'discount_value' => 'float',
        'discount_amount' => 'float',
        'tax_percentage' => 'float',
        'tax_amount' => 'float',
        'total_amount' => 'float',
        'order_date' => 'date',
        'delivery_date' => 'date'
    ];
    
    protected $rules = [
        'order_number' => 'required|unique:sales_orders',
        'client_id' => 'required',
        'order_date' => 'required',
        'status' => 'required'
    ];

    /**
     * Get sales order with client and items
     */
    public function getSalesOrderWithDetails($id)
    {
        $sql = "SELECT so.*, c.company_name, c.contact_person, c.first_name, c.last_name,
                       c.email, c.phone, c.address_en, c.address_ar, c.city, c.country,
                       u.full_name as created_by_name, q.quote_number
                FROM {$this->table} so
                LEFT JOIN clients c ON so.client_id = c.id
                LEFT JOIN users u ON so.created_by = u.id
                LEFT JOIN quotes q ON so.quote_id = q.id
                WHERE so.id = :id";
        
        $order = $this->db->selectOne($sql, ['id' => $id]);
        
        if ($order) {
            // Get order items
            $order['items'] = $this->getSalesOrderItems($id);
            
            // Format client name
            $order['client_name'] = $order['company_name'] ?: 
                                   trim($order['first_name'] . ' ' . $order['last_name']);
        }
        
        return $order;
    }

    /**
     * Get sales order items
     */
    public function getSalesOrderItems($orderId)
    {
        $sql = "SELECT soi.*, p.code as product_code, p.name_en as product_name_en, 
                       p.name_ar as product_name_ar, p.unit_of_measure
                FROM sales_order_items soi
                LEFT JOIN products p ON soi.product_id = p.id
                WHERE soi.sales_order_id = :order_id
                ORDER BY soi.id";
        
        return $this->db->select($sql, ['order_id' => $orderId]);
    }

    /**
     * Generate next order number
     */
    public function generateOrderNumber()
    {
        return $this->db->getNextSequence('order_number');
    }

    /**
     * Check if order number exists
     */
    public function orderNumberExists($orderNumber, $excludeId = null)
    {
        $sql = "SELECT id FROM {$this->table} WHERE order_number = :order_number";
        $params = ['order_number' => $orderNumber];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        return $this->db->selectOne($sql, $params) !== null;
    }

    /**
     * Create sales order with items
     */
    public function createSalesOrderWithItems($orderData, $items)
    {
        try {
            $this->db->beginTransaction();
            
            // Create sales order
            $orderId = $this->create($orderData);
            
            // Create order items and reserve stock
            foreach ($items as $item) {
                $item['sales_order_id'] = $orderId;
                $this->createSalesOrderItem($item);
                
                // Reserve stock for this item
                $this->reserveStock($item['product_id'], $item['quantity']);
            }
            
            // Update order totals
            $this->updateSalesOrderTotals($orderId);
            
            $this->db->commit();
            return ['success' => true, 'id' => $orderId];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update sales order with items
     */
    public function updateSalesOrderWithItems($orderId, $orderData, $items)
    {
        try {
            $this->db->beginTransaction();
            
            // Get existing items to unreserve stock
            $existingItems = $this->getSalesOrderItems($orderId);
            foreach ($existingItems as $item) {
                $this->unreserveStock($item['product_id'], $item['quantity']);
            }
            
            // Update order
            $this->update($orderId, $orderData);
            
            // Delete existing items
            $this->db->execute("DELETE FROM sales_order_items WHERE sales_order_id = :order_id", ['order_id' => $orderId]);
            
            // Create new items and reserve stock
            foreach ($items as $item) {
                $item['sales_order_id'] = $orderId;
                $this->createSalesOrderItem($item);
                
                // Reserve stock for this item
                $this->reserveStock($item['product_id'], $item['quantity']);
            }
            
            // Update order totals
            $this->updateSalesOrderTotals($orderId);
            
            $this->db->commit();
            return ['success' => true];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create sales order item
     */
    private function createSalesOrderItem($itemData)
    {
        // Calculate line total
        $quantity = $itemData['quantity'];
        $unitPrice = $itemData['unit_price'];
        $discountAmount = $itemData['discount_amount'] ?? 0;
        $taxAmount = $itemData['tax_amount'] ?? 0;
        
        $lineTotal = ($quantity * $unitPrice) - $discountAmount + $taxAmount;
        $itemData['line_total'] = $lineTotal;
        
        $this->db->insert('sales_order_items', $itemData);
    }

    /**
     * Update sales order totals
     */
    private function updateSalesOrderTotals($orderId)
    {
        $sql = "SELECT SUM(line_total) as subtotal FROM sales_order_items WHERE sales_order_id = :order_id";
        $result = $this->db->selectOne($sql, ['order_id' => $orderId]);
        $subtotal = $result ? $result['subtotal'] : 0;
        
        // Get order for discount and tax calculation
        $order = $this->find($orderId);
        
        // Calculate discount
        $discountAmount = 0;
        if ($order['discount_type'] === 'percentage') {
            $discountAmount = $subtotal * ($order['discount_value'] / 100);
        } else {
            $discountAmount = $order['discount_value'];
        }
        
        // Calculate tax on subtotal after discount
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $taxableAmount * ($order['tax_percentage'] / 100);
        
        // Calculate total
        $totalAmount = $subtotal - $discountAmount + $taxAmount;
        
        // Update order
        $this->update($orderId, [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount
        ]);
    }

    /**
     * Reserve stock for order item
     */
    private function reserveStock($productId, $quantity)
    {
        // Get available stock locations for this product
        $sql = "SELECT id, quantity, reserved_quantity 
                FROM stock 
                WHERE product_id = :product_id AND quantity > reserved_quantity
                ORDER BY quantity DESC";
        
        $stockLocations = $this->db->select($sql, ['product_id' => $productId]);
        
        $remainingToReserve = $quantity;
        
        foreach ($stockLocations as $location) {
            if ($remainingToReserve <= 0) break;
            
            $availableQty = $location['quantity'] - $location['reserved_quantity'];
            $reserveQty = min($remainingToReserve, $availableQty);
            
            if ($reserveQty > 0) {
                // Update reserved quantity
                $this->db->execute(
                    "UPDATE stock SET reserved_quantity = reserved_quantity + :reserve_qty WHERE id = :id",
                    ['reserve_qty' => $reserveQty, 'id' => $location['id']]
                );
                
                $remainingToReserve -= $reserveQty;
            }
        }
        
        if ($remainingToReserve > 0) {
            throw new \Exception("Insufficient stock for product ID: {$productId}. Need {$remainingToReserve} more units.");
        }
    }

    /**
     * Unreserve stock for order item
     */
    private function unreserveStock($productId, $quantity)
    {
        // Get stock locations with reserved quantity for this product
        $sql = "SELECT id, reserved_quantity 
                FROM stock 
                WHERE product_id = :product_id AND reserved_quantity > 0
                ORDER BY reserved_quantity DESC";
        
        $stockLocations = $this->db->select($sql, ['product_id' => $productId]);
        
        $remainingToUnreserve = $quantity;
        
        foreach ($stockLocations as $location) {
            if ($remainingToUnreserve <= 0) break;
            
            $unreserveQty = min($remainingToUnreserve, $location['reserved_quantity']);
            
            if ($unreserveQty > 0) {
                // Update reserved quantity
                $this->db->execute(
                    "UPDATE stock SET reserved_quantity = reserved_quantity - :unreserve_qty WHERE id = :id",
                    ['unreserve_qty' => $unreserveQty, 'id' => $location['id']]
                );
                
                $remainingToUnreserve -= $unreserveQty;
            }
        }
    }

    /**
     * Ship sales order
     */
    public function shipOrder($orderId, $shippingDetails = [])
    {
        $order = $this->find($orderId);
        if (!$order || $order['status'] !== 'open') {
            return ['success' => false, 'error' => 'Order must be open to ship'];
        }

        $updateData = [
            'status' => 'shipped',
            'delivery_date' => $shippingDetails['delivery_date'] ?? null
        ];

        if (isset($shippingDetails['shipping_address'])) {
            $updateData['shipping_address'] = $shippingDetails['shipping_address'];
        }

        $success = $this->update($orderId, $updateData);
        
        return ['success' => $success];
    }

    /**
     * Deliver sales order
     */
    public function deliverOrder($orderId)
    {
        $order = $this->find($orderId);
        if (!$order || $order['status'] !== 'shipped') {
            return ['success' => false, 'error' => 'Order must be shipped to deliver'];
        }

        try {
            $this->db->beginTransaction();
            
            // Update order status
            $this->update($orderId, [
                'status' => 'delivered',
                'delivery_date' => date('Y-m-d')
            ]);
            
            // Deduct stock and unreserve
            $items = $this->getSalesOrderItems($orderId);
            foreach ($items as $item) {
                $this->deductStock($item['product_id'], $item['quantity']);
                $this->unreserveStock($item['product_id'], $item['quantity']);
                
                // Record stock movement
                $this->recordStockMovement($item['product_id'], $item['quantity'], 'out', 'sales_order', $orderId);
            }
            
            $this->db->commit();
            return ['success' => true];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cancel sales order
     */
    public function cancelOrder($orderId, $reason = '')
    {
        $order = $this->find($orderId);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found'];
        }

        if (in_array($order['status'], ['delivered', 'cancelled'])) {
            return ['success' => false, 'error' => 'Cannot cancel delivered or already cancelled order'];
        }

        try {
            $this->db->beginTransaction();
            
            // Update order status
            $this->update($orderId, [
                'status' => 'cancelled',
                'notes' => ($order['notes'] ? $order['notes'] . "\n" : '') . "Cancelled: " . $reason
            ]);
            
            // Unreserve stock
            $items = $this->getSalesOrderItems($orderId);
            foreach ($items as $item) {
                $this->unreserveStock($item['product_id'], $item['quantity']);
            }
            
            $this->db->commit();
            return ['success' => true];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Deduct stock from inventory
     */
    private function deductStock($productId, $quantity)
    {
        // Get stock locations with available quantity
        $sql = "SELECT id, quantity 
                FROM stock 
                WHERE product_id = :product_id AND quantity > 0
                ORDER BY quantity ASC"; // FIFO - First In, First Out
        
        $stockLocations = $this->db->select($sql, ['product_id' => $productId]);
        
        $remainingToDeduct = $quantity;
        
        foreach ($stockLocations as $location) {
            if ($remainingToDeduct <= 0) break;
            
            $deductQty = min($remainingToDeduct, $location['quantity']);
            
            if ($deductQty > 0) {
                // Update quantity
                $this->db->execute(
                    "UPDATE stock SET quantity = quantity - :deduct_qty WHERE id = :id",
                    ['deduct_qty' => $deductQty, 'id' => $location['id']]
                );
                
                $remainingToDeduct -= $deductQty;
            }
        }
    }

    /**
     * Record stock movement
     */
    private function recordStockMovement($productId, $quantity, $type, $referenceType, $referenceId)
    {
        // Get the first warehouse location for this product (simplified)
        $sql = "SELECT warehouse_location_id FROM stock WHERE product_id = :product_id LIMIT 1";
        $result = $this->db->selectOne($sql, ['product_id' => $productId]);
        
        if ($result) {
            $this->db->insert('stock_movements', [
                'product_id' => $productId,
                'warehouse_location_id' => $result['warehouse_location_id'],
                'movement_type' => $type,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => 1, // System user
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Convert to invoice
     */
    public function convertToInvoice($orderId)
    {
        $order = $this->getSalesOrderWithDetails($orderId);
        if (!$order || !in_array($order['status'], ['shipped', 'delivered'])) {
            return ['success' => false, 'error' => 'Order must be shipped or delivered to create invoice'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Create invoice
            $invoiceModel = new Invoice();
            $invoiceData = [
                'invoice_number' => $invoiceModel->generateInvoiceNumber(),
                'sales_order_id' => $orderId,
                'client_id' => $order['client_id'],
                'invoice_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d', strtotime('+30 days')), // Default 30 days
                'status' => 'draft',
                'subtotal' => $order['subtotal'],
                'discount_type' => $order['discount_type'],
                'discount_value' => $order['discount_value'],
                'discount_amount' => $order['discount_amount'],
                'tax_percentage' => $order['tax_percentage'],
                'tax_amount' => $order['tax_amount'],
                'total_amount' => $order['total_amount'],
                'notes' => $order['notes'],
                'created_by' => $order['created_by']
            ];
            
            $invoiceId = $invoiceModel->create($invoiceData);
            
            // Create invoice items
            foreach ($order['items'] as $item) {
                $invoiceItemData = [
                    'invoice_id' => $invoiceId,
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
                
                $this->db->insert('invoice_items', $invoiceItemData);
            }
            
            $this->db->commit();
            return ['success' => true, 'invoice_id' => $invoiceId];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get orders by status
     */
    public function getOrdersByStatus($status, $limit = 20)
    {
        // Ensure limit is a positive integer to prevent SQL injection
        $limit = max(1, min(100, (int)$limit));
        
        $sql = "SELECT so.*, c.company_name, c.contact_person, c.first_name, c.last_name,
                       u.full_name as created_by_name
                FROM {$this->table} so
                LEFT JOIN clients c ON so.client_id = c.id
                LEFT JOIN users u ON so.created_by = u.id
                WHERE so.status = :status
                ORDER BY so.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->select($sql, ['status' => $status]);
    }
}
