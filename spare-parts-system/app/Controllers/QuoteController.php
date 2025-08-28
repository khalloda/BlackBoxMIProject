<?php
/**
 * Quote Controller
 * 
 * Handles quote management with full CRUD operations
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Models\Quote;
use App\Models\Client;
use App\Models\Product;

class QuoteController extends Controller
{
    private $quoteModel;
    private $clientModel;
    private $productModel;

    public function __construct()
    {
        parent::__construct();
        $this->quoteModel = new Quote();
        $this->clientModel = new Client();
        $this->productModel = new Product();
    }

    /**
     * Display quotes list
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
        $sql = "SELECT q.*, c.company_name, c.contact_person, c.first_name, c.last_name,
                       u.full_name as created_by_name
                FROM quotes q
                LEFT JOIN clients c ON q.client_id = c.id
                LEFT JOIN users u ON q.created_by = u.id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (q.quote_number LIKE :search1 OR c.company_name LIKE :search2 OR c.contact_person LIKE :search3 OR c.first_name LIKE :search4 OR c.last_name LIKE :search5)";
            $searchParam = '%' . $search . '%';
            $params['search1'] = $searchParam;
            $params['search2'] = $searchParam;
            $params['search3'] = $searchParam;
            $params['search4'] = $searchParam;
            $params['search5'] = $searchParam;
        }

        if ($status) {
            $sql .= " AND q.status = :status";
            $params['status'] = $status;
        }

        if ($client) {
            $sql .= " AND q.client_id = :client";
            $params['client'] = $client;
        }

        $sql .= " ORDER BY q.created_at DESC";

        // Get paginated results
        $result = $this->quoteModel->db->paginate($sql, $params, $page, $perPage);

        // Process client names for display
        foreach ($result['data'] as &$quote) {
            $quote['client_name'] = $quote['company_name'] ?: 
                                   trim($quote['first_name'] . ' ' . $quote['last_name']);
        }

        $this->setTitle(__('quotes.title'));
        
        return $this->view('quotes/index', [
            'quotes' => $result['data'],
            'pagination' => $result['pagination'],
            'search' => $search,
            'status' => $status,
            'client' => $client,
            'clients' => $this->getActiveClients(),
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Show create quote form
     */
    public function create()
    {
        $this->requireAuth();
        
        $this->setTitle(__('quotes.add_quote'));
        
        return $this->view('quotes/create', [
            'quote_number' => $this->quoteModel->generateQuoteNumber(),
            'clients' => $this->getActiveClients(),
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Store new quote
     */
    public function store()
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/quotes/create');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/quotes/create');
        }

        // Get quote data
        $quoteData = [
            'quote_number' => $this->input('quote_number'),
            'client_id' => $this->input('client_id'),
            'quote_date' => $this->input('quote_date'),
            'valid_until' => $this->input('valid_until'),
            'status' => 'draft',
            'discount_type' => $this->input('discount_type') ?: 'percentage',
            'discount_value' => $this->input('discount_value') ?: 0,
            'tax_percentage' => $this->input('tax_percentage') ?: 0,
            'notes' => $this->input('notes'),
            'terms_conditions' => $this->input('terms_conditions'),
            'created_by' => Auth::id()
        ];

        // Get quote items
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
                    'tax_percentage' => $quoteData['tax_percentage'],
                    'tax_amount' => 0,
                    'notes' => $itemNotes[$i] ?? ''
                ];
            }
        }

        if (empty($items)) {
            $this->flash('error', 'At least one quote item is required');
            return $this->view('quotes/create', [
                'quote' => $quoteData,
                'quote_number' => $quoteData['quote_number'],
                'clients' => $this->getActiveClients(),
                'flash_messages' => $this->getFlashMessages()
            ]);
        }

        $result = $this->quoteModel->createQuoteWithItems($quoteData, $items);

        if ($result['success']) {
            $this->flash('success', __('quotes.quote_created'));
            return $this->redirect('/quotes/' . $result['id']);
        } else {
            $this->flash('error', 'Failed to create quote: ' . ($result['error'] ?? 'Unknown error'));
            return $this->view('quotes/create', [
                'quote' => $quoteData,
                'quote_number' => $quoteData['quote_number'],
                'clients' => $this->getActiveClients(),
                'flash_messages' => $this->getFlashMessages()
            ]);
        }
    }

    /**
     * Show quote details
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $quote = $this->quoteModel->getQuoteWithDetails($id);
        
        if (!$quote) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/quotes');
        }

        $this->setTitle(__('quotes.quote_details') . ' - ' . $quote['quote_number']);
        
        return $this->view('quotes/show', [
            'quote' => $quote,
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Show edit quote form
     */
    public function edit($id)
    {
        $this->requireAuth();
        
        $quote = $this->quoteModel->getQuoteWithDetails($id);
        
        if (!$quote) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/quotes');
        }

        // Check if quote can be edited
        if (in_array($quote['status'], ['approved', 'rejected', 'expired'])) {
            $this->flash('error', 'Cannot edit quote with status: ' . $quote['status']);
            return $this->redirect('/quotes/' . $id);
        }

        $this->setTitle(__('quotes.edit_quote') . ' - ' . $quote['quote_number']);
        
        return $this->view('quotes/edit', [
            'quote' => $quote,
            'clients' => $this->getActiveClients(),
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Update quote
     */
    public function update($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/quotes/' . $id . '/edit');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/quotes/' . $id . '/edit');
        }

        $quote = $this->quoteModel->find($id);
        if (!$quote) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/quotes');
        }

        // Check if quote can be edited
        if (in_array($quote['status'], ['approved', 'rejected', 'expired'])) {
            $this->flash('error', 'Cannot edit quote with status: ' . $quote['status']);
            return $this->redirect('/quotes/' . $id);
        }

        // Get quote data
        $quoteData = [
            'quote_number' => $this->input('quote_number'),
            'client_id' => $this->input('client_id'),
            'quote_date' => $this->input('quote_date'),
            'valid_until' => $this->input('valid_until'),
            'discount_type' => $this->input('discount_type') ?: 'percentage',
            'discount_value' => $this->input('discount_value') ?: 0,
            'tax_percentage' => $this->input('tax_percentage') ?: 0,
            'notes' => $this->input('notes'),
            'terms_conditions' => $this->input('terms_conditions')
        ];

        // Get quote items
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
                    'tax_percentage' => $quoteData['tax_percentage'],
                    'tax_amount' => 0,
                    'notes' => $itemNotes[$i] ?? ''
                ];
            }
        }

        if (empty($items)) {
            $this->flash('error', 'At least one quote item is required');
            return $this->redirect('/quotes/' . $id . '/edit');
        }

        $result = $this->quoteModel->updateQuoteWithItems($id, $quoteData, $items);

        if ($result['success']) {
            $this->flash('success', __('quotes.quote_updated'));
            return $this->redirect('/quotes/' . $id);
        } else {
            $this->flash('error', 'Failed to update quote: ' . ($result['error'] ?? 'Unknown error'));
            return $this->redirect('/quotes/' . $id . '/edit');
        }
    }

    /**
     * Delete quote
     */
    public function delete($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/quotes');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/quotes');
        }

        $quote = $this->quoteModel->find($id);
        if (!$quote) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/quotes');
        }

        // Check if quote can be deleted
        if ($quote['status'] === 'approved') {
            $this->flash('error', 'Cannot delete approved quote');
            return $this->redirect('/quotes/' . $id);
        }

        if ($this->quoteModel->delete($id)) {
            $this->flash('success', __('quotes.quote_deleted'));
        } else {
            $this->flash('error', 'Failed to delete quote');
        }

        return $this->redirect('/quotes');
    }

    /**
     * Send quote to client
     */
    public function send($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/quotes/' . $id);
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/quotes/' . $id);
        }

        $quote = $this->quoteModel->find($id);
        if (!$quote) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/quotes');
        }

        if ($quote['status'] !== 'draft') {
            $this->flash('error', 'Only draft quotes can be sent');
            return $this->redirect('/quotes/' . $id);
        }

        // Update quote status
        $success = $this->quoteModel->update($id, ['status' => 'sent']);

        if ($success) {
            $this->flash('success', 'Quote sent successfully');
            // TODO: Send email to client
        } else {
            $this->flash('error', 'Failed to send quote');
        }

        return $this->redirect('/quotes/' . $id);
    }

    /**
     * Approve quote
     */
    public function approve($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/quotes/' . $id);
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/quotes/' . $id);
        }

        $quote = $this->quoteModel->find($id);
        if (!$quote) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/quotes');
        }

        if ($quote['status'] !== 'sent') {
            $this->flash('error', 'Only sent quotes can be approved');
            return $this->redirect('/quotes/' . $id);
        }

        $success = $this->quoteModel->approveQuote($id, Auth::id());

        if ($success) {
            $this->flash('success', 'Quote approved successfully');
        } else {
            $this->flash('error', 'Failed to approve quote');
        }

        return $this->redirect('/quotes/' . $id);
    }

    /**
     * Reject quote
     */
    public function reject($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/quotes/' . $id);
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/quotes/' . $id);
        }

        $quote = $this->quoteModel->find($id);
        if (!$quote) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/quotes');
        }

        if ($quote['status'] !== 'sent') {
            $this->flash('error', 'Only sent quotes can be rejected');
            return $this->redirect('/quotes/' . $id);
        }

        $success = $this->quoteModel->rejectQuote($id);

        if ($success) {
            $this->flash('success', 'Quote rejected');
        } else {
            $this->flash('error', 'Failed to reject quote');
        }

        return $this->redirect('/quotes/' . $id);
    }

    /**
     * Convert quote to sales order
     */
    public function convert($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/quotes/' . $id);
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/quotes/' . $id);
        }

        $result = $this->quoteModel->convertToSalesOrder($id);

        if ($result['success']) {
            $this->flash('success', 'Quote converted to sales order successfully');
            return $this->redirect('/sales-orders/' . $result['order_id']);
        } else {
            $this->flash('error', 'Failed to convert quote: ' . $result['error']);
            return $this->redirect('/quotes/' . $id);
        }
    }

    /**
     * Generate PDF
     */
    public function pdf($id)
    {
        $this->requireAuth();
        
        $quote = $this->quoteModel->getQuoteWithDetails($id);
        
        if (!$quote) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/quotes');
        }

        // TODO: Generate PDF using FPDF or similar library
        $this->flash('info', 'PDF generation not implemented yet');
        return $this->redirect('/quotes/' . $id);
    }

    /**
     * Export quotes to CSV
     */
    public function export()
    {
        $this->requireAuth();
        
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $client = $this->input('client', '');

        // Build query
        $sql = "SELECT q.*, c.company_name, c.contact_person, c.first_name, c.last_name,
                       u.full_name as created_by_name
                FROM quotes q
                LEFT JOIN clients c ON q.client_id = c.id
                LEFT JOIN users u ON q.created_by = u.id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (q.quote_number LIKE :search1 OR c.company_name LIKE :search2 OR c.contact_person LIKE :search3 OR c.first_name LIKE :search4 OR c.last_name LIKE :search5)";
            $searchParam = '%' . $search . '%';
            $params['search1'] = $searchParam;
            $params['search2'] = $searchParam;
            $params['search3'] = $searchParam;
            $params['search4'] = $searchParam;
            $params['search5'] = $searchParam;
        }

        if ($status) {
            $sql .= " AND q.status = :status";
            $params['status'] = $status;
        }

        if ($client) {
            $sql .= " AND q.client_id = :client";
            $params['client'] = $client;
        }

        $sql .= " ORDER BY q.created_at DESC";

        $quotes = $this->quoteModel->db->select($sql, $params);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="quotes_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'Quote Number', 'Client', 'Quote Date', 'Valid Until', 'Status',
            'Total Amount', 'Created By', 'Created Date'
        ]);

        // CSV data
        foreach ($quotes as $quote) {
            $clientName = $quote['company_name'] ?: 
                         trim($quote['first_name'] . ' ' . $quote['last_name']);
            
            fputcsv($output, [
                $quote['quote_number'],
                $clientName,
                $quote['quote_date'],
                $quote['valid_until'],
                ucfirst($quote['status']),
                $quote['total_amount'],
                $quote['created_by_name'],
                $quote['created_at']
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
