<?php
/**
 * Sales Order Controller
 * 
 * Handles sales order management with full CRUD operations
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Models\SalesOrder;
use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;

class SalesOrderController extends Controller
{
    private $salesOrderModel;
    private $clientModel;
    private $productModel;
    private $quoteModel;

    public function __construct()
    {
        parent::__construct();
        $this->salesOrderModel = new SalesOrder();
        $this->clientModel = new Client();
        $this->productModel = new Product();
        $this->quoteModel = new Quote();
    }

    /**
     * Display sales orders list
     */
    public function index()
    {
        $this->requireAuth();
        
        $page = (int)($this->input('page') ?: 1);
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $client = $this->input('client', '');
        $perPage = 20;

        // Build query
        $sql = "SELECT so.*, c.company_name, c.contact_person, c.first_name, c.last_name,
                       u.full_name as created_by_name, q.quote_number
                FROM sales_orders so
                LEFT JOIN clients c ON so.client_id = c.id
                LEFT JOIN users u ON so.created_by = u.id
                LEFT JOIN quotes q ON so.quote_id = q.id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (so.order_number LIKE :search1 OR c.company_name LIKE :search2 OR c.contact_person LIKE :search3 OR c.first_name LIKE :search4 OR c.last_name LIKE :search5)";
            $searchParam = '%' . $search . '%';
            $params['search1'] = $searchParam;
            $params['search2'] = $searchParam;
            $params['search3'] = $searchParam;
            $params['search4'] = $searchParam;
            $params['search5'] = $searchParam;
        }

        if ($status) {
            $sql .= " AND so.status = :status";
            $params['status'] = $status;
        }

        if ($client) {
            $sql .= " AND so.client_id = :client";
            $params['client'] = $client;
        }

        $sql .= " ORDER BY so.created_at DESC";

        // Get paginated results
        $result = $this->salesOrderModel->db->paginate($sql, $params, $page, $perPage);

        // Process client names for display
        foreach ($result['data'] as &$order) {
            $order['client_name'] = $order['company_name'] ?: 
                                   trim($order['first_name'] . ' ' . $order['last_name']);
        }

        $this->setTitle(__('sales_orders.title'));
        
        return $this->view('sales-orders/index', [
            'orders' => $result['data'],
            'pagination' => $result['pagination'],
            'search' => $search,
            'status' => $status,
            'client' => $client,
            'clients' => $this->getActiveClients(),
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Show create sales order form
     */
    public function create()
    {
        $this->requireAuth();
        
        $quoteId = $this->input('quote_id');
        $quote = null;
        
        if ($quoteId) {
            $quote = $this->quoteModel->getQuoteWithDetails($quoteId);
            if (!$quote || $quote['status'] !== 'approved') {
                $this->flash('error', 'Quote must be approved to create sales order');
                return $this->redirect('/quotes');
            }
        }
        
        $this->setTitle(__('sales_orders.add_order'));
        
        return $this->view('sales-orders/create', [
            'order_number' => $this->salesOrderModel->generateOrderNumber(),
            'clients' => $this->getActiveClients(),
            'quote' => $quote,
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Store new sales order
     */
    public function store()
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/sales-orders/create');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/sales-orders/create');
        }

        // Get order data
        $orderData = [
            'order_number' => $this->input('order_number'),
            'quote_id' => $this->input('quote_id') ?: null,
            'client_id' => $this->input('client_id'),
            'order_date' => $this->input('order_date'),
            'delivery_date' => $this->input('delivery_date') ?: null,
            'status' => 'open',
            'discount_type' => $this->input('discount_type') ?: 'percentage',
            'discount_value' => $this->input('discount_value') ?: 0,
            'tax_percentage' => $this->input('tax_percentage') ?: 0,
            'shipping_address' => $this->input('shipping_address'),
            'notes' => $this->input('notes'),
            'created_by' => Auth::id()
        ];

        // Get order items
        $items = [];
        $productIds = $this->input('product_id', []);
        $quantities = $this->input('quantity', []);
        $unitPrices = $this->input('unit_price', []);
        $itemNotes = $this->input('item_notes', []);

        for ($i = 0; $i < count($productIds); $i++) {
            if (!empty($productIds[$i]) && !empty($quantities[$i]) && !empty($unitPrices[$i])) {
                $items[] = [
                    'product_id' => $productIds[$i],
                    'quantity' => $quantities[$i],
                    'unit_price' => $unitPrices[$i],
                    'discount_type' => 'percentage',
                    'discount_value' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => $orderData['tax_percentage'],
                    'tax_amount' => 0,
                    'notes' => $itemNotes[$i] ?? ''
                ];
            }
        }

        if (empty($items)) {
            $this->flash('error', 'At least one order item is required');
            return $this->view('sales-orders/create', [
                'order' => $orderData,
                'order_number' => $orderData['order_number'],
                'clients' => $this->getActiveClients(),
                'flash_messages' => $this->getFlashMessages()
            ]);
        }

        $result = $this->salesOrderModel->createSalesOrderWithItems($orderData, $items);

        if ($result['success']) {
            $this->flash('success', __('sales_orders.order_created'));
            return $this->redirect('/sales-orders/' . $result['id']);
        } else {
            $this->flash('error', 'Failed to create sales order: ' . ($result['error'] ?? 'Unknown error'));
            return $this->view('sales-orders/create', [
                'order' => $orderData,
                'order_number' => $orderData['order_number'],
                'clients' => $this->getActiveClients(),
                'flash_messages' => $this->getFlashMessages()
            ]);
        }
    }

    /**
     * Show sales order details
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $order = $this->salesOrderModel->getSalesOrderWithDetails($id);
        
        if (!$order) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/sales-orders');
        }

        $this->setTitle(__('sales_orders.order_details') . ' - ' . $order['order_number']);
        
        return $this->view('sales-orders/show', [
            'order' => $order,
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Show edit sales order form
     */
    public function edit($id)
    {
        $this->requireAuth();
        
        $order = $this->salesOrderModel->getSalesOrderWithDetails($id);
        
        if (!$order) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/sales-orders');
        }

        // Check if order can be edited
        if (in_array($order['status'], ['shipped', 'delivered', 'cancelled'])) {
            $this->flash('error', 'Cannot edit order with status: ' . $order['status']);
            return $this->redirect('/sales-orders/' . $id);
        }

        $this->setTitle(__('sales_orders.edit_order') . ' - ' . $order['order_number']);
        
        return $this->view('sales-orders/edit', [
            'order' => $order,
            'clients' => $this->getActiveClients(),
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Update sales order
     */
    public function update($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/sales-orders/' . $id . '/edit');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/sales-orders/' . $id . '/edit');
        }

        $order = $this->salesOrderModel->find($id);
        if (!$order) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/sales-orders');
        }

        // Check if order can be edited
        if (in_array($order['status'], ['shipped', 'delivered', 'cancelled'])) {
            $this->flash('error', 'Cannot edit order with status: ' . $order['status']);
            return $this->redirect('/sales-orders/' . $id);
        }

        // Get order data
        $orderData = [
            'order_number' => $this->input('order_number'),
            'client_id' => $this->input('client_id'),
            'order_date' => $this->input('order_date'),
            'delivery_date' => $this->input('delivery_date') ?: null,
            'discount_type' => $this->input('discount_type') ?: 'percentage',
            'discount_value' => $this->input('discount_value') ?: 0,
            'tax_percentage' => $this->input('tax_percentage') ?: 0,
            'shipping_address' => $this->input('shipping_address'),
            'notes' => $this->input('notes')
        ];

        // Get order items
        $items = [];
        $productIds = $this->input('product_id', []);
        $quantities = $this->input('quantity', []);
        $unitPrices = $this->input('unit_price', []);
        $itemNotes = $this->input('item_notes', []);

        for ($i = 0; $i < count($productIds); $i++) {
            if (!empty($productIds[$i]) && !empty($quantities[$i]) && !empty($unitPrices[$i])) {
                $items[] = [
                    'product_id' => $productIds[$i],
                    'quantity' => $quantities[$i],
                    'unit_price' => $unitPrices[$i],
                    'discount_type' => 'percentage',
                    'discount_value' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => $orderData['tax_percentage'],
                    'tax_amount' => 0,
                    'notes' => $itemNotes[$i] ?? ''
                ];
            }
        }

        if (empty($items)) {
            $this->flash('error', 'At least one order item is required');
            return $this->redirect('/sales-orders/' . $id . '/edit');
        }

        $result = $this->salesOrderModel->updateSalesOrderWithItems($id, $orderData, $items);

        if ($result['success']) {
            $this->flash('success', __('sales_orders.order_updated'));
            return $this->redirect('/sales-orders/' . $id);
        } else {
            $this->flash('error', 'Failed to update sales order: ' . ($result['error'] ?? 'Unknown error'));
            return $this->redirect('/sales-orders/' . $id . '/edit');
        }
    }

    /**
     * Delete sales order
     */
    public function delete($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/sales-orders');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/sales-orders');
        }

        $order = $this->salesOrderModel->find($id);
        if (!$order) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/sales-orders');
        }

        // Check if order can be deleted
        if (in_array($order['status'], ['shipped', 'delivered'])) {
            $this->flash('error', 'Cannot delete shipped or delivered order');
            return $this->redirect('/sales-orders/' . $id);
        }

        // Cancel the order first to unreserve stock
        $result = $this->salesOrderModel->cancelOrder($id, 'Order deleted');
        
        if ($result['success'] && $this->salesOrderModel->delete($id)) {
            $this->flash('success', __('sales_orders.order_deleted'));
        } else {
            $this->flash('error', 'Failed to delete sales order');
        }

        return $this->redirect('/sales-orders');
    }

    /**
     * Ship sales order
     */
    public function ship($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/sales-orders/' . $id);
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/sales-orders/' . $id);
        }

        $shippingDetails = [
            'delivery_date' => $this->input('delivery_date'),
            'shipping_address' => $this->input('shipping_address')
        ];

        $result = $this->salesOrderModel->shipOrder($id, $shippingDetails);

        if ($result['success']) {
            $this->flash('success', 'Sales order shipped successfully');
        } else {
            $this->flash('error', 'Failed to ship order: ' . $result['error']);
        }

        return $this->redirect('/sales-orders/' . $id);
    }

    /**
     * Deliver sales order
     */
    public function deliver($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/sales-orders/' . $id);
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/sales-orders/' . $id);
        }

        $result = $this->salesOrderModel->deliverOrder($id);

        if ($result['success']) {
            $this->flash('success', 'Sales order delivered successfully');
        } else {
            $this->flash('error', 'Failed to deliver order: ' . $result['error']);
        }

        return $this->redirect('/sales-orders/' . $id);
    }

    /**
     * Cancel sales order
     */
    public function cancel($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/sales-orders/' . $id);
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/sales-orders/' . $id);
        }

        $reason = $this->input('cancel_reason', 'Cancelled by user');
        $result = $this->salesOrderModel->cancelOrder($id, $reason);

        if ($result['success']) {
            $this->flash('success', 'Sales order cancelled successfully');
        } else {
            $this->flash('error', 'Failed to cancel order: ' . $result['error']);
        }

        return $this->redirect('/sales-orders/' . $id);
    }

    /**
     * Create invoice from sales order
     */
    public function createInvoice($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/sales-orders/' . $id);
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/sales-orders/' . $id);
        }

        $result = $this->salesOrderModel->convertToInvoice($id);

        if ($result['success']) {
            $this->flash('success', 'Invoice created successfully');
            return $this->redirect('/invoices/' . $result['invoice_id']);
        } else {
            $this->flash('error', 'Failed to create invoice: ' . $result['error']);
            return $this->redirect('/sales-orders/' . $id);
        }
    }

    /**
     * Generate PDF
     */
    public function pdf($id)
    {
        $this->requireAuth();
        
        $order = $this->salesOrderModel->getSalesOrderWithDetails($id);
        
        if (!$order) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/sales-orders');
        }

        // TODO: Generate PDF using FPDF or similar library
        $this->flash('info', 'PDF generation not implemented yet');
        return $this->redirect('/sales-orders/' . $id);
    }

    /**
     * Export sales orders to CSV
     */
    public function export()
    {
        $this->requireAuth();
        
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $client = $this->input('client', '');

        // Build query
        $sql = "SELECT so.*, c.company_name, c.contact_person, c.first_name, c.last_name,
                       u.full_name as created_by_name, q.quote_number
                FROM sales_orders so
                LEFT JOIN clients c ON so.client_id = c.id
                LEFT JOIN users u ON so.created_by = u.id
                LEFT JOIN quotes q ON so.quote_id = q.id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (so.order_number LIKE :search1 OR c.company_name LIKE :search2 OR c.contact_person LIKE :search3 OR c.first_name LIKE :search4 OR c.last_name LIKE :search5)";
            $searchParam = '%' . $search . '%';
            $params['search1'] = $searchParam;
            $params['search2'] = $searchParam;
            $params['search3'] = $searchParam;
            $params['search4'] = $searchParam;
            $params['search5'] = $searchParam;
        }

        if ($status) {
            $sql .= " AND so.status = :status";
            $params['status'] = $status;
        }

        if ($client) {
            $sql .= " AND so.client_id = :client";
            $params['client'] = $client;
        }

        $sql .= " ORDER BY so.created_at DESC";

        $orders = $this->salesOrderModel->db->select($sql, $params);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sales_orders_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'Order Number', 'Quote Number', 'Client', 'Order Date', 'Delivery Date', 
            'Status', 'Total Amount', 'Created By', 'Created Date'
        ]);

        // CSV data
        foreach ($orders as $order) {
            $clientName = $order['company_name'] ?: 
                         trim($order['first_name'] . ' ' . $order['last_name']);
            
            fputcsv($output, [
                $order['order_number'],
                $order['quote_number'],
                $clientName,
                $order['order_date'],
                $order['delivery_date'],
                ucfirst($order['status']),
                $order['total_amount'],
                $order['created_by_name'],
                $order['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Get active clients for dropdowns
     */
    private function getActiveClients()
    {
        return $this->clientModel->getActiveClients();
    }
}
