<?php
/**
 * Client Controller
 * 
 * Handles client management with full CRUD operations
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Models\Client;

class ClientController extends Controller
{
    private $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->clientModel = new Client();
    }

    /**
     * Display clients list
     */
    public function index()
    {
        $this->requireAuth();
        
        $page = (int)($this->input('page') ?: 1);
        $search = $this->input('search', '');
        $type = $this->input('type', '');
        $perPage = 20;

        // Build query
        $sql = "SELECT * FROM clients WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (code LIKE :search1 OR company_name LIKE :search2 OR contact_person LIKE :search3 OR first_name LIKE :search4 OR last_name LIKE :search5 OR email LIKE :search6)";
            $searchParam = '%' . $search . '%';
            $params['search1'] = $searchParam;
            $params['search2'] = $searchParam;
            $params['search3'] = $searchParam;
            $params['search4'] = $searchParam;
            $params['search5'] = $searchParam;
            $params['search6'] = $searchParam;
        }

        if ($type) {
            $sql .= " AND type = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY created_at DESC";

        // Get paginated results
        $result = $this->clientModel->db->paginate($sql, $params, $page, $perPage);

        // Process client names for display
        foreach ($result['data'] as &$client) {
            $client['display_name'] = $this->clientModel->getDisplayName($client);
        }

        $this->setTitle(__('clients.title'));
        
        return $this->view('clients/index', [
            'clients' => $result['data'],
            'pagination' => $result['pagination'],
            'search' => $search,
            'type' => $type,
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Show create client form
     */
    public function create()
    {
        $this->requireAuth();
        
        $this->setTitle(__('clients.add_client'));
        
        return $this->view('clients/create', [
            'client_code' => $this->clientModel->generateClientCode(),
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Store new client
     */
    public function store()
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/clients/create');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/clients/create');
        }

        $data = [
            'code' => $this->input('code'),
            'type' => $this->input('type'),
            'company_name' => $this->input('company_name'),
            'contact_person' => $this->input('contact_person'),
            'first_name' => $this->input('first_name'),
            'last_name' => $this->input('last_name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'mobile' => $this->input('mobile'),
            'fax' => $this->input('fax'),
            'address_en' => $this->input('address_en'),
            'address_ar' => $this->input('address_ar'),
            'city' => $this->input('city'),
            'country' => $this->input('country'),
            'tax_number' => $this->input('tax_number'),
            'credit_limit' => $this->input('credit_limit') ?: 0,
            'payment_terms' => $this->input('payment_terms') ?: 30,
            'discount_percentage' => $this->input('discount_percentage') ?: 0,
            'is_active' => $this->input('is_active') ? 1 : 0
        ];

        $result = $this->clientModel->createClient($data);

        if ($result['success']) {
            $this->flash('success', __('clients.client_created'));
            return $this->redirect('/clients/' . $result['id']);
        } else {
            $this->flash('error', 'Validation errors occurred');
            return $this->view('clients/create', [
                'client' => $data,
                'errors' => $result['errors'],
                'client_code' => $data['code'],
                'flash_messages' => $this->getFlashMessages()
            ]);
        }
    }

    /**
     * Show client details
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $client = $this->clientModel->getClientWithStats($id);
        
        if (!$client) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/clients');
        }

        // Get recent transactions
        $quotes = $this->clientModel->getClientQuotes($id, 5);
        $orders = $this->clientModel->getClientOrders($id, 5);
        $invoices = $this->clientModel->getClientInvoices($id, 5);
        $payments = $this->clientModel->getClientPayments($id, 5);

        $client['display_name'] = $this->clientModel->getDisplayName($client);

        $this->setTitle(__('clients.client_details') . ' - ' . $client['display_name']);
        
        return $this->view('clients/show', [
            'client' => $client,
            'quotes' => $quotes,
            'orders' => $orders,
            'invoices' => $invoices,
            'payments' => $payments,
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Show edit client form
     */
    public function edit($id)
    {
        $this->requireAuth();
        
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/clients');
        }

        $client['display_name'] = $this->clientModel->getDisplayName($client);

        $this->setTitle(__('clients.edit_client') . ' - ' . $client['display_name']);
        
        return $this->view('clients/edit', [
            'client' => $client,
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Update client
     */
    public function update($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/clients/' . $id . '/edit');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/clients/' . $id . '/edit');
        }

        $client = $this->clientModel->find($id);
        if (!$client) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/clients');
        }

        $data = [
            'code' => $this->input('code'),
            'type' => $this->input('type'),
            'company_name' => $this->input('company_name'),
            'contact_person' => $this->input('contact_person'),
            'first_name' => $this->input('first_name'),
            'last_name' => $this->input('last_name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'mobile' => $this->input('mobile'),
            'fax' => $this->input('fax'),
            'address_en' => $this->input('address_en'),
            'address_ar' => $this->input('address_ar'),
            'city' => $this->input('city'),
            'country' => $this->input('country'),
            'tax_number' => $this->input('tax_number'),
            'credit_limit' => $this->input('credit_limit') ?: 0,
            'payment_terms' => $this->input('payment_terms') ?: 30,
            'discount_percentage' => $this->input('discount_percentage') ?: 0,
            'is_active' => $this->input('is_active') ? 1 : 0
        ];

        $result = $this->clientModel->updateClient($id, $data);

        if ($result['success']) {
            $this->flash('success', __('clients.client_updated'));
            return $this->redirect('/clients/' . $id);
        } else {
            $this->flash('error', 'Validation errors occurred');
            return $this->view('clients/edit', [
                'client' => array_merge($client, $data),
                'errors' => $result['errors'],
                'flash_messages' => $this->getFlashMessages()
            ]);
        }
    }

    /**
     * Delete client
     */
    public function delete($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/clients');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/clients');
        }

        $client = $this->clientModel->find($id);
        if (!$client) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/clients');
        }

        // Check if client has transactions
        $stats = $this->clientModel->getClientStatistics($id);
        if ($stats['quotes_count'] > 0 || $stats['orders_count'] > 0 || $stats['invoices_count'] > 0) {
            $this->flash('error', 'Cannot delete client with existing transactions. Deactivate instead.');
            return $this->redirect('/clients/' . $id);
        }

        if ($this->clientModel->delete($id)) {
            $this->flash('success', __('clients.client_deleted'));
        } else {
            $this->flash('error', 'Failed to delete client');
        }

        return $this->redirect('/clients');
    }

    /**
     * Toggle client status
     */
    public function toggleStatus($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/clients');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                return $this->error(__('error.csrf_error'));
            }
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/clients');
        }

        $client = $this->clientModel->find($id);
        if (!$client) {
            if ($this->isAjax()) {
                return $this->error(__('error.not_found'));
            }
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/clients');
        }

        $newStatus = !$client['is_active'];
        $success = $this->clientModel->update($id, ['is_active' => $newStatus]);

        if ($this->isAjax()) {
            if ($success) {
                return $this->success([
                    'status' => $newStatus,
                    'message' => $newStatus ? 'Client activated' : 'Client deactivated'
                ]);
            } else {
                return $this->error('Failed to update client status');
            }
        }

        if ($success) {
            $this->flash('success', $newStatus ? 'Client activated' : 'Client deactivated');
        } else {
            $this->flash('error', 'Failed to update client status');
        }

        return $this->redirect('/clients/' . $id);
    }

    /**
     * Search clients (AJAX)
     */
    public function search()
    {
        $this->requireAuth();
        
        if (!$this->isAjax()) {
            return $this->redirect('/clients');
        }

        $query = $this->input('q', '');
        $limit = (int)($this->input('limit') ?: 10);

        if (strlen($query) < 2) {
            return $this->success([]);
        }

        $clients = $this->clientModel->searchClients($query, $limit);

        // Format for select2 or similar
        $results = [];
        foreach ($clients as $client) {
            $results[] = [
                'id' => $client['id'],
                'text' => $client['code'] . ' - ' . $this->clientModel->getDisplayName($client),
                'data' => $client
            ];
        }

        return $this->success($results);
    }

    /**
     * Export clients to CSV
     */
    public function export()
    {
        $this->requireAuth();
        
        $search = $this->input('search', '');
        $type = $this->input('type', '');

        // Build query
        $sql = "SELECT * FROM clients WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (code LIKE :search1 OR company_name LIKE :search2 OR contact_person LIKE :search3 OR first_name LIKE :search4 OR last_name LIKE :search5 OR email LIKE :search6)";
            $searchParam = '%' . $search . '%';
            $params['search1'] = $searchParam;
            $params['search2'] = $searchParam;
            $params['search3'] = $searchParam;
            $params['search4'] = $searchParam;
            $params['search5'] = $searchParam;
            $params['search6'] = $searchParam;
        }

        if ($type) {
            $sql .= " AND type = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY created_at DESC";

        $clients = $this->clientModel->db->select($sql, $params);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="clients_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'Code', 'Type', 'Company Name', 'Contact Person', 'First Name', 'Last Name',
            'Email', 'Phone', 'Mobile', 'City', 'Country', 'Credit Limit', 'Payment Terms',
            'Discount %', 'Status', 'Created Date'
        ]);

        // CSV data
        foreach ($clients as $client) {
            fputcsv($output, [
                $client['code'],
                ucfirst($client['type']),
                $client['company_name'],
                $client['contact_person'],
                $client['first_name'],
                $client['last_name'],
                $client['email'],
                $client['phone'],
                $client['mobile'],
                $client['city'],
                $client['country'],
                $client['credit_limit'],
                $client['payment_terms'],
                $client['discount_percentage'],
                $client['is_active'] ? 'Active' : 'Inactive',
                $client['created_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
