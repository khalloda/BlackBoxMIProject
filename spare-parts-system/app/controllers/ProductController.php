<?php
/**
 * Product Controller
 * 
 * Handles product management with full CRUD operations
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Models\Product;
use App\Models\Classification;
use App\Models\Brand;
use App\Models\Color;
use App\Models\CarMake;
use App\Models\CarModel;

class ProductController extends Controller
{
    private $productModel;

    public function __construct()
    {
        parent::__construct();
        $this->productModel = new Product();
    }

    /**
     * Display products list
     */
    public function index()
    {
        $this->requireAuth();
        
        $page = (int)($this->input('page') ?: 1);
        $search = $this->input('search', '');
        $classification = $this->input('classification', '');
        $brand = $this->input('brand', '');
        $perPage = 20;

        // Build query
        $sql = "SELECT p.*, c.name_en as classification_name, b.name as brand_name,
                       col.name_en as color_name, cm.name as car_make_name, cmod.name as car_model_name,
                       COALESCE(SUM(s.quantity), 0) as total_stock
                FROM products p
                LEFT JOIN classifications c ON p.classification_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN colors col ON p.color_id = col.id
                LEFT JOIN car_makes cm ON p.car_make_id = cm.id
                LEFT JOIN car_models cmod ON p.car_model_id = cmod.id
                LEFT JOIN stock s ON p.id = s.product_id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (p.code LIKE :search1 OR p.name_en LIKE :search2 OR p.name_ar LIKE :search3 OR p.part_number LIKE :search4 OR p.barcode LIKE :search5)";
            $searchParam = '%' . $search . '%';
            $params['search1'] = $searchParam;
            $params['search2'] = $searchParam;
            $params['search3'] = $searchParam;
            $params['search4'] = $searchParam;
            $params['search5'] = $searchParam;
        }

        if ($classification) {
            $sql .= " AND p.classification_id = :classification";
            $params['classification'] = $classification;
        }

        if ($brand) {
            $sql .= " AND p.brand_id = :brand";
            $params['brand'] = $brand;
        }

        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

        // Get paginated results
        $result = $this->productModel->db->paginate($sql, $params, $page, $perPage);

        // Get filter options
        $classifications = $this->getClassifications();
        $brands = $this->getBrands();

        $this->setTitle(__('products.title'));
        
        return $this->view('products/index', [
            'products' => $result['data'],
            'pagination' => $result['pagination'],
            'search' => $search,
            'classification' => $classification,
            'brand' => $brand,
            'classifications' => $classifications,
            'brands' => $brands,
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Show create product form
     */
    public function create()
    {
        $this->requireAuth();
        
        $this->setTitle(__('products.add_product'));
        
        return $this->view('products/create', [
            'classifications' => $this->getClassifications(),
            'brands' => $this->getBrands(),
            'colors' => $this->getColors(),
            'car_makes' => $this->getCarMakes(),
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Store new product
     */
    public function store()
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/products/create');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/products/create');
        }

        $data = [
            'code' => $this->input('code'),
            'name_en' => $this->input('name_en'),
            'name_ar' => $this->input('name_ar'),
            'description_en' => $this->input('description_en'),
            'description_ar' => $this->input('description_ar'),
            'classification_id' => $this->input('classification_id') ?: null,
            'brand_id' => $this->input('brand_id') ?: null,
            'color_id' => $this->input('color_id') ?: null,
            'car_make_id' => $this->input('car_make_id') ?: null,
            'car_model_id' => $this->input('car_model_id') ?: null,
            'part_number' => $this->input('part_number'),
            'barcode' => $this->input('barcode'),
            'unit_of_measure' => $this->input('unit_of_measure') ?: 'PCS',
            'cost_price' => $this->input('cost_price') ?: 0,
            'selling_price' => $this->input('selling_price') ?: 0,
            'min_stock_level' => $this->input('min_stock_level') ?: 0,
            'max_stock_level' => $this->input('max_stock_level') ?: 0,
            'reorder_level' => $this->input('reorder_level') ?: 0,
            'weight' => $this->input('weight') ?: null,
            'dimensions' => $this->input('dimensions'),
            'is_active' => $this->input('is_active') ? 1 : 0
        ];

        $result = $this->productModel->createProduct($data);

        if ($result['success']) {
            $this->flash('success', __('products.product_created'));
            return $this->redirect('/products/' . $result['id']);
        } else {
            $this->flash('error', 'Validation errors occurred');
            return $this->view('products/create', [
                'product' => $data,
                'errors' => $result['errors'],
                'classifications' => $this->getClassifications(),
                'brands' => $this->getBrands(),
                'colors' => $this->getColors(),
                'car_makes' => $this->getCarMakes(),
                'flash_messages' => $this->getFlashMessages()
            ]);
        }
    }

    /**
     * Show product details
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $product = $this->productModel->getProductWithStock($id);
        
        if (!$product) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/products');
        }

        // Get stock movements
        $stockMovements = $this->productModel->getProductStockMovements($id, 10);

        $this->setTitle(__('products.product_details') . ' - ' . $product['name_en']);
        
        return $this->view('products/show', [
            'product' => $product,
            'stock_movements' => $stockMovements,
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Show edit product form
     */
    public function edit($id)
    {
        $this->requireAuth();
        
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/products');
        }

        $this->setTitle(__('products.edit_product') . ' - ' . $product['name_en']);
        
        return $this->view('products/edit', [
            'product' => $product,
            'classifications' => $this->getClassifications(),
            'brands' => $this->getBrands(),
            'colors' => $this->getColors(),
            'car_makes' => $this->getCarMakes(),
            'car_models' => $this->getCarModels($product['car_make_id']),
            'flash_messages' => $this->getFlashMessages()
        ]);
    }

    /**
     * Update product
     */
    public function update($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/products/' . $id . '/edit');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/products/' . $id . '/edit');
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/products');
        }

        $data = [
            'code' => $this->input('code'),
            'name_en' => $this->input('name_en'),
            'name_ar' => $this->input('name_ar'),
            'description_en' => $this->input('description_en'),
            'description_ar' => $this->input('description_ar'),
            'classification_id' => $this->input('classification_id') ?: null,
            'brand_id' => $this->input('brand_id') ?: null,
            'color_id' => $this->input('color_id') ?: null,
            'car_make_id' => $this->input('car_make_id') ?: null,
            'car_model_id' => $this->input('car_model_id') ?: null,
            'part_number' => $this->input('part_number'),
            'barcode' => $this->input('barcode'),
            'unit_of_measure' => $this->input('unit_of_measure') ?: 'PCS',
            'cost_price' => $this->input('cost_price') ?: 0,
            'selling_price' => $this->input('selling_price') ?: 0,
            'min_stock_level' => $this->input('min_stock_level') ?: 0,
            'max_stock_level' => $this->input('max_stock_level') ?: 0,
            'reorder_level' => $this->input('reorder_level') ?: 0,
            'weight' => $this->input('weight') ?: null,
            'dimensions' => $this->input('dimensions'),
            'is_active' => $this->input('is_active') ? 1 : 0
        ];

        $result = $this->productModel->updateProduct($id, $data);

        if ($result['success']) {
            $this->flash('success', __('products.product_updated'));
            return $this->redirect('/products/' . $id);
        } else {
            $this->flash('error', 'Validation errors occurred');
            return $this->view('products/edit', [
                'product' => array_merge($product, $data),
                'errors' => $result['errors'],
                'classifications' => $this->getClassifications(),
                'brands' => $this->getBrands(),
                'colors' => $this->getColors(),
                'car_makes' => $this->getCarMakes(),
                'car_models' => $this->getCarModels($data['car_make_id']),
                'flash_messages' => $this->getFlashMessages()
            ]);
        }
    }

    /**
     * Delete product
     */
    public function delete($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/products');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/products');
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/products');
        }

        // Check if product has stock or transactions
        $productWithStock = $this->productModel->getProductWithStock($id);
        if ($productWithStock['total_stock'] > 0) {
            $this->flash('error', 'Cannot delete product with existing stock. Deactivate instead.');
            return $this->redirect('/products/' . $id);
        }

        if ($this->productModel->delete($id)) {
            $this->flash('success', __('products.product_deleted'));
        } else {
            $this->flash('error', 'Failed to delete product');
        }

        return $this->redirect('/products');
    }

    /**
     * Toggle product status
     */
    public function toggleStatus($id)
    {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            return $this->redirect('/products');
        }

        try {
            CSRF::verify();
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                return $this->error(__('error.csrf_error'));
            }
            $this->flash('error', __('error.csrf_error'));
            return $this->redirect('/products');
        }

        $product = $this->productModel->find($id);
        if (!$product) {
            if ($this->isAjax()) {
                return $this->error(__('error.not_found'));
            }
            $this->flash('error', __('error.not_found'));
            return $this->redirect('/products');
        }

        $newStatus = !$product['is_active'];
        $success = $this->productModel->update($id, ['is_active' => $newStatus]);

        if ($this->isAjax()) {
            if ($success) {
                return $this->success([
                    'status' => $newStatus,
                    'message' => $newStatus ? 'Product activated' : 'Product deactivated'
                ]);
            } else {
                return $this->error('Failed to update product status');
            }
        }

        if ($success) {
            $this->flash('success', $newStatus ? 'Product activated' : 'Product deactivated');
        } else {
            $this->flash('error', 'Failed to update product status');
        }

        return $this->redirect('/products/' . $id);
    }

    /**
     * Search products (AJAX)
     */
    public function search()
    {
        $this->requireAuth();
        
        if (!$this->isAjax()) {
            return $this->redirect('/products');
        }

        $query = $this->input('q', '');
        $limit = (int)($this->input('limit') ?: 10);

        if (strlen($query) < 2) {
            return $this->success([]);
        }

        $products = $this->productModel->searchProducts($query, $limit);

        // Format for select2 or similar
        $results = [];
        foreach ($products as $product) {
            $results[] = [
                'id' => $product['id'],
                'text' => $product['code'] . ' - ' . $product['name_en'],
                'data' => $product
            ];
        }

        return $this->success($results);
    }

    /**
     * Generate product code (AJAX)
     */
    public function generateCode()
    {
        $this->requireAuth();
        
        if (!$this->isAjax()) {
            return $this->redirect('/products');
        }

        $classificationId = $this->input('classification_id');
        
        if (!$classificationId) {
            return $this->error('Classification is required');
        }

        // Get classification code
        $classification = $this->productModel->db->selectOne(
            "SELECT code FROM classifications WHERE id = :id",
            ['id' => $classificationId]
        );

        if (!$classification) {
            return $this->error('Classification not found');
        }

        $code = $this->productModel->generateProductCode($classification['code']);
        
        return $this->success(['code' => $code]);
    }

    /**
     * Export products to CSV
     */
    public function export()
    {
        $this->requireAuth();
        
        $search = $this->input('search', '');
        $classification = $this->input('classification', '');
        $brand = $this->input('brand', '');

        // Build query
        $sql = "SELECT p.*, c.name_en as classification_name, b.name as brand_name,
                       col.name_en as color_name, cm.name as car_make_name, cmod.name as car_model_name,
                       COALESCE(SUM(s.quantity), 0) as total_stock
                FROM products p
                LEFT JOIN classifications c ON p.classification_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN colors col ON p.color_id = col.id
                LEFT JOIN car_makes cm ON p.car_make_id = cm.id
                LEFT JOIN car_models cmod ON p.car_model_id = cmod.id
                LEFT JOIN stock s ON p.id = s.product_id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (p.code LIKE :search1 OR p.name_en LIKE :search2 OR p.name_ar LIKE :search3 OR p.part_number LIKE :search4 OR p.barcode LIKE :search5)";
            $searchParam = '%' . $search . '%';
            $params['search1'] = $searchParam;
            $params['search2'] = $searchParam;
            $params['search3'] = $searchParam;
            $params['search4'] = $searchParam;
            $params['search5'] = $searchParam;
        }

        if ($classification) {
            $sql .= " AND p.classification_id = :classification";
            $params['classification'] = $classification;
        }

        if ($brand) {
            $sql .= " AND p.brand_id = :brand";
            $params['brand'] = $brand;
        }

        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

        $products = $this->productModel->db->select($sql, $params);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'Code', 'Name (EN)', 'Name (AR)', 'Classification', 'Brand', 'Part Number',
            'Barcode', 'Unit', 'Cost Price', 'Selling Price', 'Stock', 'Min Stock', 'Status'
        ]);

        // CSV data
        foreach ($products as $product) {
            fputcsv($output, [
                $product['code'],
                $product['name_en'],
                $product['name_ar'],
                $product['classification_name'],
                $product['brand_name'],
                $product['part_number'],
                $product['barcode'],
                $product['unit_of_measure'],
                $product['cost_price'],
                $product['selling_price'],
                $product['total_stock'],
                $product['min_stock_level'],
                $product['is_active'] ? 'Active' : 'Inactive'
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Get classifications for dropdowns
     */
    private function getClassifications()
    {
        return $this->productModel->db->select("SELECT id, code, name_en, name_ar FROM classifications WHERE is_active = 1 ORDER BY name_en");
    }

    /**
     * Get brands for dropdowns
     */
    private function getBrands()
    {
        return $this->productModel->db->select("SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name");
    }

    /**
     * Get colors for dropdowns
     */
    private function getColors()
    {
        return $this->productModel->db->select("SELECT id, name_en, name_ar, hex_code FROM colors WHERE is_active = 1 ORDER BY name_en");
    }

    /**
     * Get car makes for dropdowns
     */
    private function getCarMakes()
    {
        return $this->productModel->db->select("SELECT id, name FROM car_makes WHERE is_active = 1 ORDER BY name");
    }

    /**
     * Get car models for dropdowns
     */
    private function getCarModels($carMakeId = null)
    {
        if (!$carMakeId) return [];
        
        return $this->productModel->db->select(
            "SELECT id, name FROM car_models WHERE car_make_id = :car_make_id AND is_active = 1 ORDER BY name",
            ['car_make_id' => $carMakeId]
        );
    }
}
