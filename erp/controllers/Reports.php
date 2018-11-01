<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            redirect('login');
        }

        $this->lang->load('reports', $this->Settings->language);
		$this->lang->load('accounts', $this->Settings->language);
        $this->load->library('form_validation');
        $this->load->model('reports_model');
		$this->load->model('sales_model');
		$this->load->model('companies_model');
		$this->load->model('accounts_model');
		$this->load->model('site');
        
        if(!$this->Owner && !$this->Admin) {
            $gp = $this->site->checkPermissions();
            $this->permission = $gp[0];
            $this->permission[] = $gp[0];
        } else {
            $this->permission[] = NULL;
        }
    }

    function index()
    {
        $this->erp->checkPermissions('products', true);
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['monthly_sales'] = $this->reports_model->getChartData();
        $this->data['stock'] = $this->reports_model->getStockValue();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('reports'), 'bc' => $bc);
        $this->page_construct('reports/index', $meta, $this->data);

    }
	
	function profit_chart()
    {
        $this->erp->checkPermissions('account');
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
       
        $this->data['stock'] = $this->reports_model->getStockValue();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('reports'), 'bc' => $bc);
		
		if($this->input->post('biller')){
			$biller_id = $this->input->post('biller');
		}else{
			$biller_id = null;
		}
		if($this->input->post('year')){
			$year = $this->input->post('year');
		}else{
			$year = null;
		}
			
		
		$this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['monthly_incomes'] = $this->reports_model->getChartDataProfit($biller_id, $year);
			$this->page_construct('reports/profit_chart', $meta, $this->data);
		
		
    }
    
    function warehouse_stock($warehouse = NULL)
    {
        $this->erp->checkPermissions('products', TRUE);
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        }

        $this->data['stock'] = $warehouse ? $this->reports_model->getWarehouseStockValue($warehouse) : $this->reports_model->getStockValue();
        $this->data['warehouses'] = $this->reports_model->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse;
        $this->data['warehouse'] = $warehouse ? $this->site->getWarehouseByID($warehouse) : NULL;
        $this->data['totals'] = $this->reports_model->getWarehouseTotals($warehouse);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('reports'), 'bc' => $bc);
        $this->page_construct('reports/warehouse_stock', $meta, $this->data);

    }
	
	function category_stock($warehouse = NULL)
    {
        $this->erp->checkPermissions('products', TRUE);
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        }
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['customers'] = $this->site->getCustomers();
		
		//$this->form_validation->set_rules('biller', lang("biller"), 'required');
		//$this->form_validation->set_rules('customer', lang("customer"), 'required');
		//$this->form_validation->set_rules('start_date', lang("start_date"), 'required');
		//$this->form_validation->set_rules('end_date', lang("end_date"), 'required');
		$this->form_validation->set_rules('user_post', lang("user_post"), 'required');
		
        if ($this->form_validation->run() == true) {
			$biller = $this->input->post('biller');
			$customer = $this->input->post('customer');
			$start_date = $this->input->post('start_date');
			$end_date = $this->input->post('end_date');
			
			//$end_date = $start_date ? $this->erp->fld($end_date) : date('Y-m-d');
			
			
			$this->data['stocks'] = $warehouse ? $this->reports_model->getCategoryStockValueById($warehouse,$biller,$customer,$start_date,$end_date) : $this->reports_model->getCategoryStockValue($biller,$customer,$start_date,$end_date);
		}else{
			$this->data['stocks'] = $warehouse ? $this->reports_model->getCategoryStockValueById($warehouse) : $this->reports_model->getCategoryStockValue();
		}
		
        $this->data['warehouses'] = $this->reports_model->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse;
        $this->data['warehouse'] = $warehouse ? $this->site->getWarehouseByID($warehouse) : NULL;
        $this->data['totals'] = $this->reports_model->getWarehouseTotals($warehouse);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('reports'), 'bc' => $bc);
        $this->page_construct('reports/category_stock', $meta, $this->data);

    }
	
	function cash_chart($accountcode = NULL)
    {
        //$this->erp->checkPermissions('index', TRUE);
		$this->erp->checkPermissions('account', TRUE);
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('accountcode')) {
            $accountcode = $this->input->get('accountcode');
        }

        $this->data['charts'] = $accountcode ? $this->reports_model->getChartValueById($accountcode) : $this->reports_model->getChartValue();
		
        $this->data['warehouses'] = $this->reports_model->getAllWarehouses();
        $this->data['warehouse_id'] = $accountcode;
        $this->data['chart'] = $accountcode ? $this->site->getChartByID($accountcode) : NULL;
        //$this->data['totals'] = $this->reports_model->getWarehouseTotals($accountcode);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('reports'), 'bc' => $bc);
        $this->page_construct('reports/cash_chart', $meta, $this->data);

    }
    
	function expiry_alerts($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('expiry_alerts');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if($this->Owner || $this->Admin){
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        }else{
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : NULL;
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('product_expiry_alerts')));
        $meta = array('page_title' => lang('product_expiry_alerts'), 'bc' => $bc);
        $this->page_construct('reports/expiry_alerts', $meta, $this->data);
    }
   
    function getExpiryAlerts($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('expiry_alerts', TRUE);
        $date = date('Y-m-d', strtotime('+3 months'));

        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("image, product_code, product_name, quantity_balance, warehouses.name, expiry")
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
                ->where('warehouse_id', $warehouse_id)->where('expiry <', $date);
        } else {
            $this->datatables
                ->select("image, product_code, product_name, quantity_balance, warehouses.name, expiry")
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
                ->where('expiry <', $date);
        }
        echo $this->datatables->generate();
    }

    function quantity_alerts($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('products', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : NULL;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('product_quantity_alerts')));
        $meta = array('page_title' => lang('product_quantity_alerts'), 'bc' => $bc);
        $this->page_construct('reports/quantity_alerts', $meta, $this->data);
    }

    function getQuantityAlerts($warehouse_id = NULL, $pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('products', TRUE);
        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        if ($pdf || $xls) {

            if ($warehouse_id) {
                $this->db
                    ->select('products.image as image, products.code, products.name, warehouses_products.quantity, alert_quantity')
                    ->from('products')->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
                    ->where('alert_quantity > warehouses_products.quantity', NULL)
                    ->where('warehouse_id', $warehouse_id)
                    ->where('track_quantity', 1)
                    ->order_by('products.code desc');
            } else {
                $this->db
                    ->select('image, code, name, quantity, alert_quantity')
                    ->from('products')
                    ->where('alert_quantity > quantity', NULL)
                    ->where('track_quantity', 1)
                    ->order_by('code desc');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('product_quantity_alerts'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('quantity'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('alert_quantity'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->quantity);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->alert_quantity);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);

                $filename = 'product_quantity_alerts';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            if ($warehouse_id) {
                $this->datatables
                    ->select('erp_products.id, image, code, name, wp.quantity, alert_quantity')
                    ->from('products')
                    ->join("( SELECT * from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left')
                    ->where('alert_quantity > wp.quantity', NULL)
                    ->or_where('wp.quantity', NULL)
                    ->where('track_quantity', 1)
                    ->group_by('products.id');
            } else {
                $this->datatables
                    ->select('erp_products.id, image, code, name, quantity, alert_quantity')
                    ->from('products')
                    ->where('alert_quantity > quantity', NULL)
                    ->where('track_quantity', 1);
            }

            echo $this->datatables->generate();

        }
    }
	
	function getPurchasePaymentAlerts($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('expiry_alerts', TRUE);
        $date = date('Y-m-d', strtotime('+3 months'));

        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("products.image, purchase_items.product_code, purchase_items.product_name, purchase_items.quantity, warehouses.name, purchases.date")
                ->from('purchases')
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
				->join('products', 'products.code=purchase_items.product_code', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
				->where('purchases.payment_term !=', 0)
                ->where('warehouse_id', $warehouse_id)->where('purchases.date <', $date);
        } else {
            $this->datatables
                ->select("products.image, purchase_items.product_code, purchase_items.product_name, purchase_items.quantity, warehouses.name, purchases.date")
                ->from('purchases')
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
				->join('products', 'products.code=purchase_items.product_code', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
				->where('purchases.payment_term !=', 0)
                ->where('purchases.date <', $date);
        }
        echo $this->datatables->generate();
    }
	
	function getCustomerPaymentAlerts($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('expiry_alerts', TRUE);
        $date = date('Y-m-d', strtotime('+3 months'));

        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        $this->load->library('datatables');
            if ($warehouse_id) {
                $this->datatables
                    ->select('sales.date, sales.reference_no, sales.biller, sales.customer, sales.grand_total, sales.paid, (erp_sales.grand_total - erp_sales.paid) AS balance')
                    ->from('sales')
                    ->join('sale_items', 'sale_items.sale_id = sales.id', 'inner')
					->join('warehouses', 'warehouses.warehouse_id = sales.warehouse_id', 'inner')
					->join('companies', 'companies.id = sales.customer_id', 'inner')
					->join('customer_groups', 'customer_groups.id = companies.customer_group_id')
					->where('sales.payment_term !=', 0)
					->or_where('sales.payment_term !=', '')
					->where('sales.date <', $date);
            } else {
                $this->datatables
                    ->select('sales.date, sales.reference_no, sales.biller, sales.customer, sales.grand_total, sales.paid, (erp_sales.grand_total - erp_sales.paid) AS balance')
                    ->from('sales')
                    ->join('sale_items', 'sale_items.sale_id = sales.id', 'inner')
					->join('warehouses', 'warehouses.id = sales.warehouse_id', 'inner')
					->join('companies', 'companies.id = sales.customer_id', 'inner')
					->join('customer_groups', 'customer_groups.id = companies.customer_group_id')
					->where('sales.payment_term !=', 0)
					->or_where('sales.payment_term !=', '')
					->where('sales.date <', $date);
            }
        echo $this->datatables->generate();
    }
	
    function suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1) {
            die();
        }

        $rows = $this->reports_model->getProductNames($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")");

            }
            echo json_encode($pr);
        } else {
            echo FALSE;
        }
    }
	
	function products($biller_id = NULL)
    {
        $this->erp->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
		$this->data['suppliers'] = $this->site->getAllSuppliers();
		$user = $this->site->getUser();
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			if($user->biller_id){
				$this->data['biller_id'] = $user->biller_id;
			}else{
				$this->data['biller_id'] = "";
			}
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}

        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
		if ($this->Owner || $this->Admin) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
			if($this->session->userdata('warehouse_id')){
				$this->data['warehouse_id'] = $warehouse_id;
				$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
			}else{
				$this->data['warehouse_id']=null;
				$this->data['warehouse'] = null;
			}
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('products_report')));
        $meta = array('page_title' => lang('products_report'), 'bc' => $bc);
        $this->page_construct('reports/products', $meta, $this->data);
    }
    /*function products()
    {
        $this->erp->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
		
		if ($this->Owner || $this->Admin) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
			if($this->session->userdata('warehouse_id')){
				$this->data['warehouse_id'] = $warehouse_id;
				$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
			}else{
				$this->data['warehouse_id']=null;
				$this->data['warehouse'] = null;
			}
            
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('products_report')));
        $meta = array('page_title' => lang('products_report'), 'bc' => $bc);
		
        $this->page_construct('reports/products', $meta, $this->data);
    }*/
	
    /* Products Daily In Out */
	function product_daily_in_out($warehouse_id=null){
		
		$this->load->library("pagination");
		
		$suffix_uri = "";
		$month = "";
		$year = "";
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
			$suffix_uri .= "&product=".$product ;	
        }else{
			 $product = 0;
        } 
        if ($this->input->get('month')) {
            $month =  $this->input->get('month');
				$suffix_uri .= "&month=".$month ;
        }else{
				$month = date('m');
        }
        if ($this->input->get('year')) {
            $year = $this->input->get('year');
			$suffix_uri .= "&year=".$year ;
        }else{
				$year = date('Y');
        }
		$wid = $this->reports_model->getWareByUserID();
		
		if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
			$suffix_uri .= "&warehouse=".$warehouse ;
        }else{
			$warehouse = 0;
        } 
		if ($this->input->get('category')) {
            $category = $this->input->get('category');
			$suffix_uri .= "&category=".$category ;
        }else{
				$category = 0;
        } 
		if ($this->input->get('in_out')) {
            $in_out = $this->input->get('in_out');
			$suffix_uri .= "&in_out=".$in_out ;
        }else{
				$in_out = 'all';
        } 
		
		$row_nums = $this->reports_model->getStockINOUTNUM($product,$category,$in_out,$year,$month,$warehouse,$wid);
		
		$config = array();
		$config['suffix'] = "?v=1".$suffix_uri;
		$uri = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $config["base_url"] = site_url("reports/product_dailyinout/") ;
		$config["total_rows"] = $row_nums;
		$config["ob_set"] = $uri;
        $config["per_page"] =20; 
		$config["uri_segment"] = 3;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm">';
		$config['full_tag_close'] = '</ul>';
		$config['next_tag_open'] = '<li class="next">';
		$config['next_tag_close'] = '</li>';
		$config['prev_tag_open'] = '<li class="prev">';
		$config['prev_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="active"><a>';
		$config['cur_tag_close'] = '</a></li>';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		 $this->data['categories'] = $this->site->getAllCategories();

		$d=cal_days_in_month(CAL_GREGORIAN,$month,$year);
		
		$this->data['days'] = $d;
		$this->pagination->initialize($config);
		$this->data["pagination"] = $this->pagination->create_links();  
		
		$this->data['stocks']    = $this->reports_model->getStockINOUT($product,$category,$warehouse,$wid,$in_out,$year,$month,$config['per_page'],$config["ob_set"]);
		$this->data['warefull'] = $this->reports_model->getWareFullByUSER($wid);
		$this->data['category2']    = $category;
		$this->data['year2']    = $year;
		$this->data['month2'] 	  	= $month;
		$this->data['warehouse2'] 	  	= $warehouse;
		$this->data['wid2'] 	  	= $wid;
		$this->data['in_out2'] 	  	= $in_out;
		$this->data['product2'] 	  	= $product;
		$this->data['products']     = $this->reports_model->getAllProducts();
		//Convert value pass to excel export
        if($product == null){
            $this->data['product1'] = 0;
        }else{
            $this->data['product1'] = $product;
        }
        if($warehouse == null){
            $this->data['warehouse1'] = 0;
        }else{
            $this->data['warehouse1'] = $warehouse;
        }
        if($category == null){
            $this->data['category1'] = 0;
        }else{
            $this->data['category1'] = $category;
        }
		if($wid == null){
			$this->data['wid1'] = 0;
		}else{
			$this->data['wid1'] = $wid;
		}
		$this->data['biller_idd'] = $this->reports_model->getBiilerByUserID();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('daily_products'), 'bc' => $bc);
        $this->page_construct('reports/product_dailyinout', $meta, $this->data);
		
	}
    
	function product_daily_in_outs($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('products', true);
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['suppliers'] = $this->site->getAllSuppliers();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        
        if ($this->Owner || $this->Admin) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            if(isset($warehouse_id)){
                $this->data['warehouse_id'] = $warehouse_id;
                $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
            }else{
                $this->data['warehouse_id']=null;
                $this->data['warehouse'] = null;
            }
            
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_products')));
        $meta = array('page_title' => lang('daily_products'), 'bc' => $bc);
        
        $this->page_construct('reports/product_daily_in_out', $meta, $this->data);
    }

	/* Products Monthly In Out */
	function product_monthly_in_out($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('products', true);
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
		$this->data['suppliers'] = $this->site->getAllSuppliers();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
		
		if ($this->Owner || $this->Admin) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
			if(isset($warehouse_id)){
				$this->data['warehouse_id'] = $warehouse_id;
				$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
			}else{
				$this->data['warehouse_id']=null;
				$this->data['warehouse'] = null;
			}
            
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('monthly_products')));
        $meta = array('page_title' => lang('monthly_products'), 'bc' => $bc);
		
        $this->page_construct('reports/product_monthly_in_out', $meta, $this->data);
    }
	
	
	function product_in_out($biller_id = NULL)
    {
        $this->erp->checkPermissions('products', true);
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
		$this->data['suppliers'] = $this->site->getAllSuppliers();
		$user = $this->site->getUser();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			if($user->biller_id){
				$this->data['biller_id'] = $user->biller_id;
			}else{
				$this->data['biller_id'] = "";
			}
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
		
		if ($this->Owner || $this->Admin) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
			if($this->session->userdata('warehouse_id')){
				$this->data['warehouse_id'] = $warehouse_id;
				$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
			}else{
				$this->data['warehouse_id']=null;
				$this->data['warehouse'] = null;
			}
            
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('products_in/out')));
        $meta = array('page_title' => lang('products_report'), 'bc' => $bc);
		
        $this->page_construct('reports/product_in_out', $meta, $this->data);
    }
	
	function product_in_out1()
    {
        $this->erp->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
		$this->data['suppliers'] = $this->site->getAllSuppliers();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
		
		if ($this->Owner || $this->Admin) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
			if($this->session->userdata('warehouse_id')){
				$this->data['warehouse_id'] = $warehouse_id;
				$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
			}else{
				$this->data['warehouse_id']=null;
				$this->data['warehouse'] = null;
			}
            
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('products_in/out')));
        $meta = array('page_title' => lang('products_report'), 'bc' => $bc);
		
        $this->page_construct('reports/product_in_out', $meta, $this->data);
    }

    function getProductsReport($pdf = NULL, $xls = NULL, $biller_id = NULL)
    {
        $this->erp->checkPermissions('products', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
		
		if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }

        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
		if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
		if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
			$where_sale='where si.warehouse_id='.$warehouse;
			$where_purchase="where {$this->db->dbprefix('purchase_items')}.warehouse_id=".$warehouse;
        } else {
            $warehouse = NULL;
			$where_purchase = '';
			$where_sale='';
        }
		
		if($biller_id){
			$where_p_biller = "AND p.biller_id = {$biller_id} ";
			$where_s_biller = "AND s.biller_id = {$biller_id} ";
		}else{
			$where_p_biller = 'AND 1=1 ';
			$where_s_biller = 'AND 1=1 ';
		}
		
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
			//echo $start_date; die();
            $end_date = $end_date ? $this->erp->fld($end_date) : date('Y-m-d');
			
			
			
			$pp = "( SELECT 
				pi.date as date, pi.product_id, 
				pi.purchase_id, 
				COALESCE(SUM( CASE WHEN pi.purchase_id <> 0 THEN (pi.quantity*(CASE WHEN ppv.qty_unit <> 0 THEN ppv.qty_unit ELSE 1 END)) ELSE 0 END),0) as purchasedQty, 
				SUM(pi.quantity_balance) as balacneQty, 
				SUM((CASE WHEN pi.option_id <> 0 THEN ppv.cost ELSE pi.net_unit_cost END) * pi.quantity_balance ) balacneValue, 
				SUM( pi.unit_cost * (CASE WHEN pi.purchase_id <> 0 THEN pi.quantity ELSE 0 END) ) totalPurchase 
				FROM {$this->db->dbprefix('purchase_items')} pi 
				LEFT JOIN {$this->db->dbprefix('purchases')} p 
				on p.id = pi.purchase_id 
				LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
				ON ppv.id=pi.option_id 
				WHERE p.date >= '{$start_date}' and p.date < '{$end_date}' 
				AND pi.status <> 'ordered'
				". $where_p_biller ."
				GROUP BY pi.product_id ) PCosts";
				
			$sp = "( SELECT si.product_id, 
				SUM( si.quantity*(CASE WHEN pv.qty_unit <> 0 THEN pv.qty_unit ELSE 1 END)) soldQty, 
				SUM( si.subtotal ) totalSale, 
				s.date as sdate 
				FROM " . $this->db->dbprefix('sales') . " s 
				INNER JOIN " . $this->db->dbprefix('sale_items') . " si 
				ON s.id = si.sale_id 
				LEFT JOIN " . $this->db->dbprefix('product_variants') . " pv 
				ON pv.id=si.option_id 
				WHERE s.date >= '{$start_date}' 
				AND s.date < '{$end_date}' 
				". $where_s_biller ."
				GROUP BY si.product_id ) PSales";
			
        } else {
            $pp = "( SELECT 
						pi.date as date, 
						pi.product_id, 
						pi.purchase_id, 
						COALESCE(SUM(CASE WHEN pi.purchase_id <> 0 THEN (pi.quantity*(CASE WHEN ppv.qty_unit <> 0 THEN ppv.qty_unit ELSE 1 END)) ELSE 0 END),0) as purchasedQty, 
						SUM(pi.quantity_balance) as balacneQty, 
						SUM((CASE WHEN pi.option_id <> 0 THEN ppv.cost ELSE pi.net_unit_cost END) * pi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * (CASE WHEN pi.purchase_id <> 0 THEN pi.quantity ELSE 0 END) ) totalPurchase
						FROM {$this->db->dbprefix('purchase_items')} pi 
						LEFT JOIN {$this->db->dbprefix('purchases')} p 
						ON p.id = pi.purchase_id
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
						ON ppv.id=pi.option_id ".$where_purchase." 
						WHERE pi.status <> 'ordered' 
						". $where_p_biller ."
						GROUP BY pi.product_id ) PCosts";
			
            $sp = "( SELECT 
						si.product_id, 
						SUM( si.quantity*(CASE WHEN pv.qty_unit <> 0 THEN pv.qty_unit ELSE 1 END)) soldQty, 
						SUM( si.subtotal ) totalSale, 
						s.date as sdate FROM " . $this->db->dbprefix('sales') . " s 
						INNER JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " pv 
						ON pv.id=si.option_id ".$where_sale." 
						". $where_s_biller ."
						GROUP BY si.product_id ) PSales";
        }
        
		if ($pdf || $xls) {
            $this->db->query('SET SQL_BIG_SELECTS=1');
            $this->db
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
				COALESCE( PCosts.purchasedQty, 0 ) as PurchasedQty,
				COALESCE( PSales.soldQty, 0 )  + COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
                                erp_combo_items ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    ) as SoldQty,
				COALESCE( PCosts.balacneQty, 0 ) as BalacneQty,
				COALESCE( PCosts.totalPurchase, 0 ) as TotalPurchase,
				COALESCE( PCosts.balacneValue, 0 ) as TotalBalance,
				COALESCE( PSales.totalSale, 0 ) as TotalSales,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')				
				->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				->join('categories', 'products.category_id=categories.id', 'left')
				->group_by("products.id");

            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			
			if ($supplier) {
                $this->db->where("products.supplier1 = '".$supplier."' or products.supplier2 = '".$supplier."' or products.supplier3 = '".$supplier."' or products.supplier4 = '".$supplier."' or products.supplier5 = '".$supplier."'");
            }
			
			if ($warehouse) {
                $this->datatables->where('wp.warehouse_id', $warehouse);
                $this->datatables->where('wp.quantity !=', 0);
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('profit_loss'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('stock_in_hand'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $bQty = 0;
                $bAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->BalacneQty);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->Profit);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->TotalBalance);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $bQty += $data_row->BalacneQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $bAmt += $data_row->TotalBalance;
                    $pl += $data_row->Profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $bQty);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $bAmt);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);

                $filename = 'products_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {$detail_sale = anchor('reports/view_sale_detail/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Sale_detail'), 'data-toggle="modal" data-target="#myModal"');
			$detail_purchase = anchor('reports/view_purchase_detail/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Purchase_detail'), 'data-toggle="modal" data-target="#myModal"');
			
				
			$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_purchase . '</li>
				<li>' . $detail_sale . '</li>
			<ul>
			</div></div>';

            $this->load->library('datatables');
            $this->db->query('SET SQL_BIG_SELECTS=1');
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as idd, " . $this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
				CONCAT(COALESCE( PCosts.purchasedQty, 0 ), '__', COALESCE( PCosts.totalPurchase, 0 )) as purchased,
				CONCAT(
                    COALESCE (PSales.soldQty, 0) + COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
                                erp_combo_items ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    ),
                    '__',
                    COALESCE (PSales.totalSale, 0)
                ) AS sold,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit,
				CONCAT(COALESCE( PCosts.balacneQty, 0 ), '__', COALESCE( PCosts.balacneValue, 0 )) as balance", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')				
				->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				->join('categories', 'products.category_id=categories.id', 'left')
				->group_by("products.id")
				->add_column("Action", '<div class="text-center"><div class="btn-group text-left">'. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'. lang('actions') . ' <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu"><li>' . $detail_purchase . '</li><li>' . $detail_sale . '</li><ul></div></div>', $this->db->dbprefix('products') . ".code");

            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			if ($supplier) {
                $this->datatables->where("products.supplier1 = '".$supplier."' or products.supplier2 = '".$supplier."' or products.supplier3 = '".$supplier."' or products.supplier4 = '".$supplier."' or products.supplier5 = '".$supplier."'");
            }
			if ($warehouse) {
                $this->datatables->where('wp.warehouse_id', $warehouse);
                $this->datatables->where('wp.quantity !=', 0);
            }
            echo $this->datatables->generate();

        }

    }
	
	function getPurchasedReport($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('products', TRUE);
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
       
        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
       
		if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
      
		if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
		
		if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
		
		if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
			$where_sale='where si.warehouse_id='.$warehouse;
			$where_purchase="where {$this->db->dbprefix('purchase_items')}.warehouse_id=".$warehouse;
        } else {
            $warehouse = NULL;
			$where_purchase = '';
			$where_sale='';
        }
        
		if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $end_date ? $this->erp->fld($end_date) : date('Y-m-d');

            $pp = "( SELECT 
						pi.date as date, pi.product_id, 
						pi.purchase_id, 
						COALESCE(SUM( CASE WHEN pi.purchase_id <> 0 THEN (pi.quantity*(CASE WHEN ppv.qty_unit <> 0 THEN ppv.qty_unit ELSE 1 END)) ELSE 0 END),0) as purchasedQtypurchasedQtypurchasedQty, 
						SUM(pi.quantity_balance) as balacneQty, 
						SUM((CASE WHEN pi.option_id <> 0 THEN ppv.cost ELSE pi.net_unit_cost END) * pi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * (CASE WHEN pi.purchase_id <> 0 THEN pi.quantity ELSE 0 END) ) totalPurchase 
						FROM {$this->db->dbprefix('purchase_items')} pi 
						LEFT JOIN {$this->db->dbprefix('purchases')} p 
						on p.id = pi.purchase_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
						ON ppv.id=pi.option_id 
						WHERE p.date >= '{$start_date}' and p.date < '{$end_date}' 
						GROUP BY pi.product_id ) PCosts";
            $sp = "( SELECT si.product_id, 
						SUM( si.quantity*(CASE WHEN pv.qty_unit <> 0 THEN pv.qty_unit ELSE 1 END)) soldQty, 
						SUM( si.subtotal ) totalSale, 
						s.date as sdate 
						FROM " . $this->db->dbprefix('sales') . " s 
						INNER JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " pv 
						ON pv.id=si.option_id 
						WHERE s.date >= '{$start_date}' 
						AND s.date < '{$end_date}' 
						GROUP BY si.product_id ) PSales";
						
        } else {
            $pp = "( SELECT 
						pi.date as date, 
						pi.product_id, 
						pi.purchase_id, 
						COALESCE(SUM(CASE WHEN pi.purchase_id <> 0 THEN (pi.quantity*(CASE WHEN ppv.qty_unit <> 0 THEN ppv.qty_unit ELSE 1 END)) ELSE 0 END),0) as purchasedQty, 
						SUM(pi.quantity_balance) as balacneQty, 
						SUM((CASE WHEN pi.option_id <> 0 THEN ppv.cost ELSE pi.net_unit_cost END) * pi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * (CASE WHEN pi.purchase_id <> 0 THEN pi.quantity ELSE 0 END) ) totalPurchase 
						FROM {$this->db->dbprefix('purchase_items')} pi 
						LEFT JOIN {$this->db->dbprefix('purchases')} p 
						ON p.id = pi.purchase_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
						ON ppv.id=pi.option_id ".$where_purchase." 
						GROUP BY pi.product_id ) PCosts";
            $sp = "( SELECT 
						si.product_id, 
						SUM( si.quantity*(CASE WHEN pv.qty_unit <> 0 THEN pv.qty_unit ELSE 1 END)) soldQty, 
						SUM( si.subtotal ) totalSale, 
						s.date as sdate FROM " . $this->db->dbprefix('sales') . " s 
						INNER JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " pv 
						ON pv.id=si.option_id ".$where_sale." 
						GROUP BY si.product_id ) PSales";
        }
        if ($pdf || $xls) {
            $this->db->query('SET SQL_BIG_SELECTS=1');
            $this->db
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
				COALESCE( PCosts.purchasedQty, 0 ) as PurchasedQty,
				COALESCE( PSales.soldQty, 0 ) as SoldQty,
				COALESCE( PCosts.balacneQty, 0 ) as BalacneQty,
				COALESCE( PCosts.totalPurchase, 0 ) as TotalPurchase,
				COALESCE( PCosts.balacneValue, 0 ) as TotalBalance,
				COALESCE( PSales.totalSale, 0 ) as TotalSales,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')				
				->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				->join('categories', 'products.category_id=categories.id', 'left')
				->group_by("products.id");

            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			if ($warehouse) {
                $this->datatables->where('wp.warehouse_id', $warehouse);
                $this->datatables->where('wp.quantity !=', 0);
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('profit_loss'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('stock_in_hand'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $bQty = 0;
                $bAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->BalacneQty);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->Profit);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->TotalBalance);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $bQty += $data_row->BalacneQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $bAmt += $data_row->TotalBalance;
                    $pl += $data_row->Profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $bQty);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $bAmt);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);

                $filename = 'products_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {$detail_sale = anchor('reports/view_sale_detail/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Sale_detail'), 'data-toggle="modal" data-target="#myModal"');
			$detail_purchase = anchor('reports/view_purchase_detail/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Purchase_detail'), 'data-toggle="modal" data-target="#myModal"');
			
				
			$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_purchase . '</li>
				<li>' . $detail_sale . '</li>
			<ul>
			</div></div>';

            $this->load->library('datatables');
            $this->db->query('SET SQL_BIG_SELECTS=1');
            $this->datatables
                ->select($this->db->dbprefix('purchase_items') . ".product_code, " . $this->db->dbprefix('purchase_items') . ".product_name,
				CONCAT(COALESCE( PCosts.purchasedQty, 0 ), '__', COALESCE( PCosts.totalPurchase, 0 )) as purchased,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit,
				CONCAT(COALESCE( PCosts.balacneQty, 0 ), '__', COALESCE( PCosts.balacneValue, 0 )) as balance", FALSE)
                ->from('purchase_items')
                ->join($sp, 'purchase_items.product_id = PSales.product_id', 'left')
                ->join($pp, 'purchase_items.product_id = PCosts.product_id', 'left')
				->join('purchases', 'purchases.id = purchase_items.purchase_id', 'left')
				//->group_by("purchase_items.product_id")
				->add_column("Action", '<div class="text-center"><div class="btn-group text-left">'. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'. lang('actions') . ' <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu"><li>' . $detail_purchase . '</li><li>' . $detail_sale . '</li><ul></div></div>', $this->db->dbprefix('purchase_items') . ".product_code");

            if ($supplier) {
                $this->datatables->where($this->db->dbprefix('purchases') . ".supplier_id", $supplier);
            }
			
			if($biller_id){
				$this->datatables->where($this->db->dbprefix('purchases') . ".biller_id", $biller_id);
			}
            
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			if ($warehouse) {
                $this->datatables->where('wp.warehouse_id', $warehouse);
                $this->datatables->where('wp.quantity !=', 0);
            }
            echo $this->datatables->generate();

        }

    }
    
    function getPurchasedSupplierItemsReport($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('products', TRUE);
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
		
		if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
       
        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
		if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
       
		if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
      
		if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
		
		if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
			$where_sale='where si.warehouse_id='.$warehouse;
			$where_purchase="where {$this->db->dbprefix('purchase_items')}.warehouse_id=".$warehouse;
        } else {
            $warehouse = NULL;
			$where_purchase = '';
			$where_sale='';
        }
        
		if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $end_date ? $this->erp->fld($end_date) : date('Y-m-d');

			$pp = "	erp_purchases.supplier_id,
						erp_companies.NAME AS suppName,
						erp_purchase_items.product_code,
						erp_purchase_items.product_name,
						COALESCE ((
									SELECT
											SUM(pi.subtotal) AS purchase_amount
										FROM
											erp_purchase_items pi
										WHERE
											erp_purchase_items.supplier_id = pi.supplier_id
										AND erp_purchase_items.product_code = pi.product_code
										GROUP BY
											pi.supplier_id

					), 0) AS purchase_amount,
						COALESCE ((
									SELECT
											COALESCE (
												SUM(
													CASE
													WHEN pi.purchase_id <> 0 THEN
														(
															pi.quantity * (
																CASE
																WHEN ppv.qty_unit <> 0 THEN
																	ppv.qty_unit
																ELSE
																	1
																END
															)
														)
													ELSE
														0
													END
												),
												0
											) AS purchaseQty
										FROM
											erp_purchase_items pi
											LEFT JOIN erp_product_variants ppv ON ppv.id = pi.option_id
											LEFT JOIN erp_purchases p ON p.id = pi.purchase_id
										WHERE
											erp_purchase_items.supplier_id = pi.supplier_id
										AND erp_purchase_items.product_code = pi.product_code
										AND erp_purchases.supplier_id = p.supplier_id
										AND p.date >= '$start_date' and p.date < '$end_date' 
										GROUP BY
											pi.product_code

								), 0) AS purchase_quantity,
						COALESCE ((
									SELECT
											SUM(pi.quantity_balance) AS balacneQty
										FROM
											erp_purchase_items pi
										LEFT JOIN erp_purchases p ON p.id = pi.purchase_id
										WHERE
											erp_purchase_items.supplier_id = pi.supplier_id
											AND erp_purchase_items.product_code = pi.product_code
											AND p.date >= '$start_date' and p.date < '$end_date' 
										GROUP BY
											pi.supplier_id

					), 0) AS balance";
        } else {
            $pp = "	erp_purchases.supplier_id,
						erp_companies.NAME AS suppName,
						erp_purchase_items.product_code,
						erp_purchase_items.product_name,
						COALESCE ((
									SELECT
											SUM(pi.subtotal) AS purchase_amount
										FROM
											erp_purchase_items pi
										WHERE
											erp_purchase_items.supplier_id = pi.supplier_id
										AND erp_purchase_items.product_code = pi.product_code
										GROUP BY
											pi.supplier_id

					), 0) AS purchase_amount,
						COALESCE ((
									SELECT
											COALESCE (
												SUM(
													CASE
													WHEN pi.purchase_id <> 0 THEN
														(
															pi.quantity * (
																CASE
																WHEN ppv.qty_unit <> 0 THEN
																	ppv.qty_unit
																ELSE
																	1
																END
															)
														)
													ELSE
														0
													END
												),
												0
											) AS purchaseQty
										FROM
											erp_purchase_items pi
											INNER JOIN erp_purchases p ON p.id = pi.purchase_id
											LEFT JOIN erp_product_variants ppv ON ppv.id = pi.option_id
										WHERE
											erp_purchase_items.supplier_id = pi.supplier_id
										AND erp_purchase_items.product_code = pi.product_code
										AND erp_purchases.supplier_id = p.supplier_id
										GROUP BY
											pi.product_code

								), 0) AS purchase_quantity,
						COALESCE ((
									SELECT
											SUM(pi.quantity_balance) AS balacneQty
										FROM
											erp_purchase_items pi
										WHERE
											erp_purchase_items.supplier_id = pi.supplier_id
										AND erp_purchase_items.product_code = pi.product_code
										GROUP BY
											pi.supplier_id

					), 0) AS balance";
        }
        if ($pdf || $xls){
            $this->db->query('SET SQL_BIG_SELECTS=1');
			
			/* 
			COALESCE (PO.purchaseQty, 0) AS purchase_quantity,
				(
					COALESCE (PSales.totalSale, 0) - COALESCE (PO.purchase_amount, 0)
				) AS Profit,
			*/
			
            $this->db
                ->select($pp, FALSE)
                ->from('purchase_items')
                ->join('purchases', 'purchases.id = purchase_items.purchase_id', 'inner')
				->join('products', 'products.id = purchase_items.product_id', 'inner')
				->join('companies', 'companies.id = purchase_items.supplier_id', 'inner')
				->group_by('purchase_items.supplier_id');
				//->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				//->join('categories', 'products.category_id=categories.id', 'left');
           if ($supplier){
                $this->datatables->where($this->db->dbprefix('purchases') . ".supplier_id", $supplier);
            }
			
			if ($biller_id) {
                $this->datatables->where($this->db->dbprefix('purchases') . ".biller_id", $biller_id);
            }
			
			if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			if ($warehouse) {
                $this->datatables->where('wp.warehouse_id', $warehouse);
                $this->datatables->where('wp.quantity !=', 0);
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('supplier_products'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('purchased_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('stock_in_hand'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $bQty = 0;
                $bAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->suppName);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->product_code);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->product_name);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->purchase_amount);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->purchase_quantity);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->balance);
                    $pQty += $data_row->purchase_amount;
                    $sQty += $data_row->purchase_quantity;
                    $bQty += $data_row->balance;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("D" . $row . ":F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $bQty);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);

                $filename = 'products_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {$detail_sale = anchor('reports/view_sale_detail/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Sale_detail'), 'data-toggle="modal" data-target="#myModal"');
			$detail_purchase = anchor('reports/view_purchase_detail/$1/$2', '<i class="fa fa-file-text-o"></i> ' . lang('Purchase_detail'), 'data-toggle="modal" data-target="#myModal"');
			
			$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_purchase . '</li>
			<ul>
			</div></div>';

            $this->load->library('datatables');
            $this->db->query('SET SQL_BIG_SELECTS=1');
			$this->datatables
                ->select($pp)
				->from('purchase_items')
				->join('purchases', 'purchases.id = purchase_items.purchase_id', 'left')
				->join('products', 'products.id = purchase_items.product_id', 'left')
				->join('companies', 'companies.id = purchase_items.supplier_id', 'inner')
				//->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				//->join('categories', 'products.category_id=categories.id', 'left')
				->group_by('purchase_items.supplier_id')
				->group_by('purchase_items.product_code')
				->add_column("Action", '<div class="text-center"><div class="btn-group text-left">'. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'. lang('actions') . ' <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu"><li>' . $detail_purchase . '</li><ul></div></div>', $this->db->dbprefix('purchase_items') . ".product_code, erp_purchases.supplier_id");

            if ($supplier) {
                $this->datatables->where($this->db->dbprefix('purchases') . ".supplier_id", $supplier);
            }
			
			if ($biller_id) {
                $this->datatables->where($this->db->dbprefix('purchases') . ".biller_id", $biller_id);
            }
			
			if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			if ($warehouse) {
                $this->datatables->where('wp.warehouse_id', $warehouse);
                $this->datatables->where('wp.quantity !=', 0);
            }
            echo $this->datatables->generate();
        }

    }
	
	function getProductsBiller($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('products', TRUE);
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
       
        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
       
		if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
      
		if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
		
		if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
			$where_sale='where si.warehouse_id='.$warehouse;
			$where_purchase="where {$this->db->dbprefix('purchase_items')}.warehouse_id=".$warehouse;
        } else {
            $warehouse = NULL;
			$where_purchase = '';
			$where_sale='';
        }
        
		if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $end_date ? $this->erp->fld($end_date) : date('Y-m-d');

            $pp = "( SELECT 
						pi.date as date, pi.product_id, 
						pi.purchase_id, 
						COALESCE(SUM( CASE WHEN pi.purchase_id <> 0 THEN (pi.quantity*(CASE WHEN ppv.qty_unit <> 0 THEN ppv.qty_unit ELSE 1 END)) ELSE 0 END),0) as purchasedQtypurchasedQtypurchasedQty, 
						SUM(pi.quantity_balance) as balacneQty, 
						SUM((CASE WHEN pi.option_id <> 0 THEN ppv.cost ELSE pi.net_unit_cost END) * pi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * (CASE WHEN pi.purchase_id <> 0 THEN pi.quantity ELSE 0 END) ) totalPurchase 
						FROM {$this->db->dbprefix('purchase_items')} pi 
						LEFT JOIN {$this->db->dbprefix('purchases')} p 
						on p.id = pi.purchase_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
						ON ppv.id=pi.option_id 
						WHERE p.date >= '{$start_date}' and p.date < '{$end_date}' 
						GROUP BY pi.product_id ) PCosts";
            $sp = "( SELECT si.product_id, 
						SUM( si.quantity*(CASE WHEN pv.qty_unit <> 0 THEN pv.qty_unit ELSE 1 END)) soldQty, 
						SUM( si.subtotal ) totalSale, 
						s.date as sdate 
						FROM " . $this->db->dbprefix('sales') . " s 
						INNER JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " pv 
						ON pv.id=si.option_id 
						WHERE s.date >= '{$start_date}' 
						AND s.date < '{$end_date}' 
						GROUP BY si.product_id ) PSales";
						
        } else {
            $pp = "( SELECT 
						pi.date as date, 
						pi.product_id, 
						pi.purchase_id, 
						COALESCE(SUM(CASE WHEN pi.purchase_id <> 0 THEN (pi.quantity*(CASE WHEN ppv.qty_unit <> 0 THEN ppv.qty_unit ELSE 1 END)) ELSE 0 END),0) as purchasedQty, 
						SUM(pi.quantity_balance) as balacneQty, 
						SUM((CASE WHEN pi.option_id <> 0 THEN ppv.cost ELSE pi.net_unit_cost END) * pi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * (CASE WHEN pi.purchase_id <> 0 THEN pi.quantity ELSE 0 END) ) totalPurchase 
						FROM {$this->db->dbprefix('purchase_items')} pi 
						LEFT JOIN {$this->db->dbprefix('purchases')} p 
						ON p.id = pi.purchase_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
						ON ppv.id=pi.option_id ".$where_purchase." 
						GROUP BY pi.product_id ) PCosts";
            $sp = "( SELECT 
						si.product_id, 
						SUM( si.quantity*(CASE WHEN pv.qty_unit <> 0 THEN pv.qty_unit ELSE 1 END)) soldQty, 
						SUM( si.subtotal ) totalSale, 
						s.date as sdate FROM " . $this->db->dbprefix('sales') . " s 
						INNER JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " pv 
						ON pv.id=si.option_id ".$where_sale." 
						GROUP BY si.product_id ) PSales";
        }
        if ($pdf || $xls) {
            $this->db->query('SET SQL_BIG_SELECTS=1');
            $this->db
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
				COALESCE( PCosts.purchasedQty, 0 ) as PurchasedQty,
				COALESCE( PSales.soldQty, 0 ) as SoldQty,
				COALESCE( PCosts.balacneQty, 0 ) as BalacneQty,
				COALESCE( PCosts.totalPurchase, 0 ) as TotalPurchase,
				COALESCE( PCosts.balacneValue, 0 ) as TotalBalance,
				COALESCE( PSales.totalSale, 0 ) as TotalSales,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')				
				->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				->join('categories', 'products.category_id=categories.id', 'left')
				->group_by("products.id");

            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			if ($warehouse) {
                $this->datatables->where('wp.warehouse_id', $warehouse);
                $this->datatables->where('wp.quantity !=', 0);
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('profit_loss'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('stock_in_hand'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $bQty = 0;
                $bAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->BalacneQty);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->Profit);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->TotalBalance);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $bQty += $data_row->BalacneQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $bAmt += $data_row->TotalBalance;
                    $pl += $data_row->Profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $bQty);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $bAmt);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);

                $filename = 'products_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {$detail_sale = anchor('reports/view_sale_detail/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Sale_detail'), 'data-toggle="modal" data-target="#myModal"');
			$detail_purchase = anchor('reports/view_purchase_detail/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Purchase_detail'), 'data-toggle="modal" data-target="#myModal"');
				
			$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_purchase . '</li>
				<li>' . $detail_sale . '</li>
			<ul>
			</div></div>';

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('purchase_items') . ".product_code, " . $this->db->dbprefix('purchase_items') . ".product_name,
				CONCAT(COALESCE( PCosts.purchasedQty, 0 ), '__', COALESCE( PCosts.totalPurchase, 0 )) as purchased,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit,
				CONCAT(COALESCE( PCosts.balacneQty, 0 ), '__', COALESCE( PCosts.balacneValue, 0 )) as balance", FALSE)
                ->from('purchase_items')
                ->join($sp, 'purchase_items.product_id = PSales.product_id', 'left')
                ->join($pp, 'purchase_items.product_id = PCosts.product_id', 'left')
				->join('purchases', 'purchases.id = purchase_items.purchase_id', 'left')
				//->group_by("purchase_items.product_id")
				->add_column("Action", '<div class="text-center"><div class="btn-group text-left">'. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'. lang('actions') . ' <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu"><li>' . $detail_purchase . '</li><li>' . $detail_sale . '</li><ul></div></div>', $this->db->dbprefix('purchase_items') . ".product_code");

            if ($biller) {
                $this->datatables->where($this->db->dbprefix('purchases') . ".biller_id", $biller);
            }
            
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			if ($warehouse) {
                $this->datatables->where('wp.warehouse_id', $warehouse);
                $this->datatables->where('wp.quantity !=', 0);
            }
            echo $this->datatables->generate();

        }

    }
	
	function getProductsReportInOut($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('products', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
		
		if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
        
        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
        if ($this->input->get('in_out')) {
            $in_out = $this->input->get('in_out');
        } else {
            $in_out = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
		if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
		if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
			$where_sale='where si.warehouse_id='.$warehouse;
			$where_purchase="where {$this->db->dbprefix('purchase_items')}.warehouse_id=".$warehouse . "AND {$this->db->dbprefix('purchase_items')}.status <> 'ordered'";
        } else {
            $warehouse = NULL;
			$where_purchase = "where 1=1 AND {$this->db->dbprefix('purchase_items')}.status <> 'ordered' AND {$this->db->dbprefix('purchase_items')}.purchase_id != ''";
			//$where_purchase = "where 1=1 AND {$this->db->dbprefix('purchase_items')}.status <> 'ordered'";
			$where_sale='where 1=1';
        }
		
		if($biller_id){
			$where_p_biller = "AND pp.biller_id = {$biller_id} ";
			$where_s_biller = "AND s.biller_id = {$biller_id} ";
		}else{
			$where_p_biller = 'AND 1=1 ';
			$where_s_biller = 'AND 1=1 ';
		}
		
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $end_date ? $this->erp->fld($end_date) : date('Y-m-d');

            $pp = "( SELECT pi.product_id, 
						SUM( pi.quantity * (CASE WHEN pi.option_id <> 0 THEN pi.vqty_unit ELSE 1 END) ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM((CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
                        SUM(pi.unit_cost) AS totalCost,
						SUM(pi.quantity) AS Pquantity,
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity 
									FROM erp_purchase_items 
									INNER JOIN " . $this->db->dbprefix('purchases') . " pp 
									ON pp.id = {$this->db->dbprefix('purchase_items')}.purchase_id  
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									WHERE {$this->db->dbprefix('purchase_items')}.date >= '{$start_date}' AND {$this->db->dbprefix('purchase_items')}.date < '{$end_date}' 
									".$where_p_biller."
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id 
						GROUP BY pi.product_id ) PCosts";

			$sp = "( SELECT si.product_id, 
						SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)) soldQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						WHERE s.date >= '{$start_date}' AND s.date < '{$end_date}' 
						".$where_s_biller."
						GROUP BY si.product_id ) PSales";

			$ppb = "( SELECT pi.product_id, 
						SUM( pi.quantity ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity 
									FROM erp_purchase_items 
									LEFT JOIN " . $this->db->dbprefix('purchases') . " pp 
									ON pp.id={$this->db->dbprefix('purchase_items')}.purchase_id  
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									WHERE {$this->db->dbprefix('purchase_items')}.date < '{$start_date}'
									".$where_p_biller."
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCostsBegin";
            
			$spb = "( SELECT si.product_id, 
						SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)) saleQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						WHERE s.date < '{$start_date}' 
						".$where_s_biller."
						GROUP BY si.product_id ) PSalesBegin";
        } else {
			$current_date = date('Y-m-d');
			$prevouse_date = date('Y').'-'.date('m').'-'.'01';
            //$pp = "( SELECT pi.product_id, SUM( pi.quantity ) purchasedQty, SUM( tpi.quantity_balance ) balacneQty, SUM( pi.unit_cost * tpi.quantity_balance ) balacneValue, SUM( pi.unit_cost * pi.quantity ) totalPurchase, pi.date as pdate from ( SELECT p.date as date, product_id, purchase_id, SUM(quantity) as quantity, unit_cost from erp_purchase_items JOIN {$this->db->dbprefix('purchases')} p on p.id = {$this->db->dbprefix('purchase_items')}.purchase_id GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi LEFT JOIN ( SELECT product_id, SUM(quantity_balance) as quantity_balance from {$this->db->dbprefix('purchase_items')} GROUP BY product_id ) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCosts";
            //$sp = "( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale, s.date as sdate from " . $this->db->dbprefix('sales') . " s JOIN " . $this->db->dbprefix('sale_items') . " si on s.id = si.sale_id GROUP BY si.product_id ) PSales";
			$pp = "( SELECT pi.product_id, 
						SUM( pi.quantity * (CASE WHEN pi.option_id <> 0 THEN pi.vqty_unit ELSE 1 END) ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
                        SUM(pi.unit_cost) AS totalCost,
						SUM(pi.quantity) AS Pquantity,
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost ,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity
									FROM {$this->db->dbprefix('purchase_items')} 
									LEFT JOIN " . $this->db->dbprefix('purchases') . " pp 
									ON pp.id = {$this->db->dbprefix('purchase_items')}.purchase_id  
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									".$where_purchase." 
									".$where_p_biller."
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 			
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} GROUP BY product_id 
									) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCosts";

			$sp = "( SELECT si.product_id, 
						COALESCE(SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)),0) soldQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						".$where_sale."
						".$where_s_biller."
						GROUP BY si.product_id ) PSales";

			
			$ppb = "( SELECT pi.product_id, 
						SUM(pi.quantity) AS purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) * tpi.quantity_balance ) balacneValue, 
						SUM(pi.unit_cost * pi.quantity) totalPurchase, 
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost ,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity
									FROM {$this->db->dbprefix('purchase_items')} 
									LEFT JOIN " . $this->db->dbprefix('purchases') . " pp 
									ON pp.id={$this->db->dbprefix('purchase_items')}.purchase_id  
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									".$where_purchase." 
									".$where_p_biller."
									AND {$this->db->dbprefix('purchase_items')}.date < '{$prevouse_date}' 
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 			
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCostsBegin";
			
            $spb = "( SELECT si.product_id, 
						COALESCE(SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)),0) saleQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						".$where_sale."
						".$where_s_biller."
						AND s.date < '{$prevouse_date}'
						GROUP BY si.product_id ) PSalesBegin";
        }
		
        if ($pdf || $xls) {
            $this->db->query('SET SQL_BIG_SELECTS=1');
            $this->db
                ->select($this->db->dbprefix('products') . ".id as product_id, 
				" . $this->db->dbprefix('products') . ".code as product_code,
				" . $this->db->dbprefix('products') . ".name,
				COALESCE ((
					SELECT 
						SUM(
							" . $this->db->dbprefix('purchase_items') . ".quantity_balance
						) AS quantity
					FROM
						". $this->db->dbprefix('purchase_items') ."
					JOIN " . $this->db->dbprefix('products') . "  p ON p.id = " . $this->db->dbprefix('purchase_items') . ".product_id
					LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv ON ppv.id =" . $this->db->dbprefix('purchase_items') . ".option_id 
					WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$LYMD."'
					AND " . $this->db->dbprefix('products') . ".id = (p.id)
					AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
					GROUP BY
						DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m'),
						erp_products.id
				), 0 ) as BeginPS,
				CONCAT(
                    (COALESCE (" . $this->db->dbprefix('products') . ".quantity, 0) - COALESCE (PCosts.Pquantity, 0) + COALESCE( PSales.Squantity, 0 )
					+ COALESCE (PCosts.Pquantity, 0)),
                    '__',
                    COALESCE (
                        PCosts.totalCost,
                        0
                    )) AS purchased,
				COALESCE( PSales.Squantity, 0 ) + COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
                                ".$this->db->dbprefix('combo_items') . " ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    ) as sold,
					COALESCE((
							COALESCE (erp_products.quantity, 0) - COALESCE (PCosts.Pquantity, 0) + COALESCE (PSales.Squantity, 0) + COALESCE (PCosts.Pquantity, 0)
						) - COALESCE (PSales.Squantity, 0) + COALESCE (
						(
							SELECT
								SUM(si.quantity * ci.quantity)
							FROM
								erp_combo_items ci
							INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
							WHERE
								ci.item_code = erp_products. CODE
						),
						0
						), 0)
					AS balance", 
				FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
				->join($spb, 'products.id = PSalesBegin.product_id', 'left')
                ->join($ppb, 'products.id = PCostsBegin.product_id', 'left')
                // ->group_by('products.id');
				->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				->join('categories', 'products.category_id=categories.id', 'left')
				->group_by("products.id");
            
			if ($supplier) {
				$this->db->where("products.supplier1 = '".$supplier."' or products.supplier2 = '".$supplier."' or products.supplier3 = '".$supplier."' or products.supplier4 = '".$supplier."' or products.supplier5 = '".$supplier."'");
            }else{
				//$this->db->where("COALESCE( PCosts.purchasedQty, 0 ) > 0 OR COALESCE( PSales.soldQty, 0 ) > 0");
			}
			
            if($in_out){
                if($in_out == 'in'){
                    $this->db->order_by('PCosts.purchasedQty', 'DESC');
                }else if($in_out == 'out'){
                    $this->db->order_by('PSales.soldQty', 'DESC');
                }
            }
            
            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			
			if ($warehouse) {
                $this->db->where('wp.warehouse_id', $warehouse);
                $this->db->where('wp.quantity !=', 0);
            }
            
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('balance'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $bQty = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->BalacneQty);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $bQty += $data_row->BalacneQty;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":G" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $bQty);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);

                $filename = 'products_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {
			$year = date('Y');
			$month = date('m');
			$YMD = $this->site->months($year, $month);
			if($YMD->date == ""){
				$LYMD = '0000-00-00';
			}else{
				$LYMD = $YMD->date;
			}
			$detail_sale = anchor('reports/view_sale_detail_in_out/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Sale_detail'), 'data-toggle="modal" data-target="#myModal"');
			$detail_purchase = anchor('reports/view_purchase_detail_in_out/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Purchase_detail'), 'data-toggle="modal" data-target="#myModal"');
					
			$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_purchase . '</li>
				<li>' . $detail_sale . '</li>					
			<ul>
			</div></div>';
			// COALESCE (" . $this->db->dbprefix('products') . ".quantity, 0) - COALESCE (PCosts.Pquantity, 0) as BeginPS,
            $this->load->library('datatables');
            $this->db->query('SET SQL_BIG_SELECTS=1');
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as product_id, 
				" . $this->db->dbprefix('products') . ".code as product_code,
				" . $this->db->dbprefix('products') . ".name,
				COALESCE ((
					SELECT 
						SUM(
							" . $this->db->dbprefix('purchase_items') . ".quantity_balance
						) AS quantity
					FROM
						". $this->db->dbprefix('purchase_items') ."
					JOIN " . $this->db->dbprefix('products') . "  p ON p.id = " . $this->db->dbprefix('purchase_items') . ".product_id
					LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv ON ppv.id =" . $this->db->dbprefix('purchase_items') . ".option_id 
					WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$LYMD."'
					AND " . $this->db->dbprefix('products') . ".id = (p.id)
					AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
					GROUP BY
						DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m'),
						erp_products.id
				), 0 ) as BeginPS,
				CONCAT(
                    (COALESCE (" . $this->db->dbprefix('products') . ".quantity, 0) - COALESCE (PCosts.Pquantity, 0) + COALESCE( PSales.Squantity, 0 )
					+ COALESCE (PCosts.Pquantity, 0)),
                    '__',
                    COALESCE (
                        PCosts.totalCost,
                        0
                    )) AS purchased,
				COALESCE( PSales.Squantity, 0 ) + COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
                                ".$this->db->dbprefix('combo_items') . " ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    ) as sold,
					COALESCE((
						COALESCE (erp_products.quantity, 0) - COALESCE (PCosts.Pquantity, 0) + COALESCE (PSales.Squantity, 0) + COALESCE (PCosts.Pquantity, 0)
					) - COALESCE (PSales.Squantity, 0) + COALESCE (
					(
						SELECT
							SUM(si.quantity * ci.quantity)
						FROM
							erp_combo_items ci
						INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
						WHERE
							ci.item_code = erp_products. CODE
					),
					0
					), 0)
					AS balance", 
				FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
				->join($spb, 'products.id = PSalesBegin.product_id', 'left')
                ->join($ppb, 'products.id = PCostsBegin.product_id', 'left')
                // ->group_by('products.id');
				->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				->join('categories', 'products.category_id=categories.id', 'left')
				->group_by("products.id");
            
			if ($supplier) {
				$this->datatables->where("products.supplier1 = '".$supplier."' or products.supplier2 = '".$supplier."' or products.supplier3 = '".$supplier."' or products.supplier4 = '".$supplier."' or products.supplier5 = '".$supplier."'");
            }else{
				//$this->datatables->where("COALESCE( PCosts.purchasedQty, 0 ) > 0 OR COALESCE( PSales.soldQty, 0 ) > 0");
			}
			
            if($in_out){
                if($in_out == 'in'){
                    $this->datatables->order_by('PCosts.purchasedQty', 'DESC');
                }else if($in_out == 'out'){
                    $this->datatables->order_by('PSales.soldQty', 'DESC');
                }
            }
            
            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			
			if ($warehouse) {
                $this->datatables->where('wp.warehouse_id', $warehouse);
                $this->datatables->where('wp.quantity !=', 0);
            }


			$this->datatables->add_column("Actions", $action, "product_code");
            echo $this->datatables->generate();

        }
	}
	
    function getProductsDaily($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('products', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }

        if ($this->input->get('year')) {
            $year = $this->input->get('year');
        } else {
            $year = NULL;
        }

        if ($this->input->get('month')) {
            $month = $this->input->get('month');
        } else {
            $month = 'NULL';
        }
		
        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
        if ($this->input->get('in_out')) {
            $in_out = $this->input->get('in_out');
        } else {
            $in_out = 'all';
        }
        
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
         
		$YMD = $this->site->months($year,$month);
		if($YMD->date == ""){
			$LYMD = "0000-00-00";
		}else{
			$LYMD = $YMD->date;
		}
		 
		if($in_out == 'in'){
			
		}
	   $part1 = "COALESCE(
					CASE WHEN 'in' = '" . $in_out . "' or 'all' = '" . $in_out . "' THEN
					(
						(
							SELECT
								SUM(
									" . $this->db->dbprefix('purchase_items') . ".quantity_balance
								) AS quantity
							FROM
								" . $this->db->dbprefix('purchase_items') . "
							JOIN " . $this->db->dbprefix('products') . "  p ON p.id = " . $this->db->dbprefix('purchase_items') . ".product_id
							LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv ON ppv.id =" . $this->db->dbprefix('purchase_items') . ".option_id ";
                            
		$part2 = " 		GROUP BY
								DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m'),
								erp_products.id
						) + 
						(
							CASE WHEN 
								(SELECT 
									SUM(
										" . $this->db->dbprefix('purchase_items') . ".quantity_balance
									) AS quantity
								FROM
									". $this->db->dbprefix('purchase_items') ."
								JOIN " . $this->db->dbprefix('products') . "  p ON p.id = " . $this->db->dbprefix('purchase_items') . ".product_id
								LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv ON ppv.id =" . $this->db->dbprefix('purchase_items') . ".option_id 
								WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$LYMD."'
								AND " . $this->db->dbprefix('products') . ".id = (p.id)
								AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
								GROUP BY
									DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m'),
									erp_products.id
								) IS NULL THEN 0 
							ELSE
								(SELECT 
									SUM(
										" . $this->db->dbprefix('purchase_items') . ".quantity_balance
									) AS quantity
								FROM
									". $this->db->dbprefix('purchase_items') ."
								JOIN " . $this->db->dbprefix('products') . "  p ON p.id = " . $this->db->dbprefix('purchase_items') . ".product_id
								LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv ON ppv.id =" . $this->db->dbprefix('purchase_items') . ".option_id 
								WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$LYMD."'
								AND " . $this->db->dbprefix('products') . ".id = (p.id)
								AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
								GROUP BY
									DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m'),
									erp_products.id
								)
							END
						)
					) ELSE 0 END, 
				0)";

            $list=array();
            for($d=1; $d<=31; $d++)
            {
                $time=mktime(12, 0, 0, $month, $d, $year);          
                if (date('m', $time)==$month)       
                    $list[]= date('Y-m-d', $time);
            }
            $d  = 1;
            $D1 = ""; $D2 = ""; $D3 = ""; $D4 = ""; $D5 = ""; $D6 = ""; $D7 = ""; $D8 = "";$D9 = ""; $D10 = ""; $D11 = ""; $D12 = ""; $D13 = ""; $D14 = ""; $D15 = ""; $D16 = ""; $D17 = ""; $Degithty = ""; $D19 = ""; $D20 = ""; $D21 = ""; $D22 = ""; $D23 = ""; $D24 = "";

            foreach ($list as $rws) {
                $dnumber    = $d;

                if(1 == $d):
                    $D1 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                            AS " . "DayNumber".$d . " ";
                endif;

                if(2 == $d):
                    $D2 = " ". $part1 ." 
							WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
						    AS " . "DayNumber".$d . " ";
                endif;
                if(3 == $d):
                    $D3 = " ". $part1 ." 
							WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(4 == $d):
                    $D4 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(5 == $d):
                    $D5 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(6 == $d):
                    $D6 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(7 == $d):
                    $D7 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(8 == $d):
                    $D8 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(9 == $d):
                    $D9 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(10 == $d):
                    $D10 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(11 == $d):
                    $D11 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(12 == $d):
                    $D12 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(13 == $d):
                    $D13 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(14 == $d):
                    $D14 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(15 == $d):
                    $D15 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(16 == $d):
                    $D16 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(17 == $d):
                    $D17 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(18 == $d):
                    $D18 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(19 == $d):
                    $D19 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(20 == $d):
                    $D20 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(21 == $d):
                    $D21 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(22 == $d):
                    $D22 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(23 == $d):
                    $D23 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(24 == $d):
                    $D24 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(25 == $d):
                    $D25 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(26 == $d):
                    $D26 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(27 == $d):
                    $D27 = " ". $part1 ." 
                            WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(28 == $d):
                    $D28 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(29 == $d):
                    $D29 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(30 == $d):
                    $D30 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;
                if(31 == $d):
                    $D31 = " ". $part1 ." 
                           WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '$rws'
                            AND " . $this->db->dbprefix('products') . ".id = (p.id)
                            AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
                            ". $part2 ." 
                             AS " . "DayNumber".$d . " ";
                endif;

                $d ++;
            }
            $detail_sale = anchor('reports/view_sale_detail_in_out/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Sale_detail'), 'data-toggle="modal" data-target="#myModal"');
            $detail_purchase = anchor('reports/view_purchase_detail_in_out/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Purchase_detail'), 'data-toggle="modal" data-target="#myModal"');
                    
            $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_purchase . '</li>
                <li>' . $detail_sale . '</li>                   
            <ul>
            </div></div>';

            $this->load->library('datatables');
            $this->db->query('SET SQL_BIG_SELECTS=1');
            $this->datatables
                ->select($this->db->dbprefix('products') . ".code as product_code, 
                " . $this->db->dbprefix('products') . ".name ,
                ".  $D1 ." , 
                ".  $D2 ." , 
                ".  $D3 ." , 
                ".  $D4 ." , 
                ".  $D5 ." , 
                ".  $D6 ." , 
                ".  $D7 ." , 
                ".  $D8 ." , 
                ".  $D9 ." , 
                ".  $D10 ." , 
                ".  $D11 ." , 
                ".  $D12 ." , 
                ".  $D13 ." , 
                ".  $D14 ." , 
                ".  $D15 ." , 
                ".  $D16 ." , 
                ".  $D17 ." , 
                ".  $D18 ." , 
                ".  $D19 ." , 
                ".  $D20 ." , 
                ".  $D21 ." , 
                ".  $D22 ." , 
                ".  $D23 ." , 
                ".  $D24 ." , 
                ".  $D25 ." , 
                ".  $D26 ." 
                ".  ($D27 != "" ? ", " . $D27 : "") ."
                ".  ($D28 != "" ? ", " . $D28 : "") ." 
                ".  ($D29 != "" ? ", " . $D29 : "") ." 
                ".  ($D30 != "" ? ", " . $D30 : "") ." 
                ".  ($D31 != "" ? ", " . $D31 : "") ." ", 
                FALSE)
                ->from('products')
                ->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->group_by("products.id");
            
            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            
            if ($warehouse) {
                $this->datatables->where('wp.warehouse_id', $warehouse);
                $this->datatables->where('wp.quantity !=', 0);
            }


            $this->datatables->add_column("Actions", "product_code");
            echo $this->datatables->generate();
    }
	
	function getProductsMonthly($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('products', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }

        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
        if ($this->input->get('in_out')) {
            $in_out = $this->input->get('in_out');
        } else {
            $in_out = 'all';
        }
		
		if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
			
		if($in_out == 'in'){
			
		}

		$part1 = "COALESCE(
						CASE WHEN 'in' = '" . $in_out . "' or 'all' = '" . $in_out . "' THEN
						(
							(
								SELECT
									SUM(
										" . $this->db->dbprefix('purchase_items') . ".quantity_balance
									) AS quantity
								FROM
									" . $this->db->dbprefix('purchase_items') . "
								JOIN " . $this->db->dbprefix('products') . "  p ON p.id = " . $this->db->dbprefix('purchase_items') . ".product_id
								LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv ON ppv.id =" . $this->db->dbprefix('purchase_items') . ".option_id"; 
		$part2 = "
									AND " . $this->db->dbprefix('products') . ".id = (p.id)
									AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
									GROUP BY
									DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m'),
									erp_products.id
							) + 
							(
								CASE WHEN 
									(
										SELECT 
											SUM(
												" . $this->db->dbprefix('purchase_items') . ".quantity_balance
											) AS quantity
										FROM
											". $this->db->dbprefix('purchase_items') ."
										JOIN " . $this->db->dbprefix('products') . "  p ON p.id = " . $this->db->dbprefix('purchase_items') . ".product_id
										LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv ON ppv.id =" . $this->db->dbprefix('purchase_items') . ".option_id ";
										
		$part3 = "					AND " . $this->db->dbprefix('products') . ".id = (p.id)
										AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
										GROUP BY
											DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m'),
											erp_products.id
									) IS NULL THEN 0 
								ELSE
									(
										SELECT 
											SUM(
												" . $this->db->dbprefix('purchase_items') . ".quantity_balance
											) AS quantity
										FROM
											". $this->db->dbprefix('purchase_items') ."
										JOIN " . $this->db->dbprefix('products') . "  p ON p.id = " . $this->db->dbprefix('purchase_items') . ".product_id
										LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv ON ppv.id =" . $this->db->dbprefix('purchase_items') . ".option_id ";
									
		$part4 = "					AND " . $this->db->dbprefix('products') . ".id = (p.id)
										AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
										GROUP BY
											DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m'),
											erp_products.id
									)
								END
							)
						) ELSE 0 END, 
					0)";
			
		$detail_sale = anchor('reports/view_sale_detail_in_out/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Sale_detail'), 'data-toggle="modal" data-target="#myModal"');
		$detail_purchase = anchor('reports/view_purchase_detail_in_out/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Purchase_detail'), 'data-toggle="modal" data-target="#myModal"');
				
		$action = '<div class="text-center"><div class="btn-group text-left">'
		. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
		. lang('actions') . ' <span class="caret"></span></button>
		<ul class="dropdown-menu pull-right" role="menu">
			<li>' . $detail_purchase . '</li>
			<li>' . $detail_sale . '</li>					
		<ul>
		</div></div>';
		$this->load->library('datatables');
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$this->datatables
			->select($this->db->dbprefix('products') . ".code as product_code, 
			" . $this->db->dbprefix('products') . ".name ,
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-01')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('01', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('01', 'product_code')."'
				". $part4 ."
			)
			AS january,
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-02')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('0-1', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('0-1', 'product_code')."'
				". $part4 ."
			)
			AS february,
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-03')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('02', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('02', 'product_code')."'
				". $part4 ."
			)
			AS march,
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-04')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('03', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('03', 'product_code')."'
				". $part4 ."
			)
			AS april,
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-05')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('04', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('04', 'product_code')."'
				". $part4 ."
			)
			AS may,
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-06')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('05', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('05', 'product_code')."'
				". $part4 ."
			)
			AS june,
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-07')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('06', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('06', 'product_code')."'
				". $part4 ."
			)
			AS july,
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-08')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('07', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('07', 'product_code')."'
				". $part4 ."
			)
			AS august, 
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-09')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('08', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('08', 'product_code')."'
				". $part4 ."
			)
			AS september, 
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-10')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('09', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('09', 'product_code')."'
				". $part4 ."
			)
			AS october, 
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-11')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('10', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('10', 'product_code')."'
				". $part4 ."
			)
			AS november, 
			(
				". $part1 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m') = CONCAT(YEAR(CURDATE()),'-12')
				". $part2 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('11', 'product_code')."'
				". $part3 ."
						WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$this->site->month('11', 'product_code')."'
				". $part4 ."
			)
			AS december",  
			FALSE)
			->from('products')
			->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
			->join('categories', 'products.category_id=categories.id', 'left')
			->group_by("products.id");
		
		if ($product) {
			$this->datatables->where($this->db->dbprefix('products') . ".id", $product);
		}
		if ($category) {
			$this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
		}
		
		if ($warehouse) {
			$this->datatables->where('wp.warehouse_id', $warehouse);
			$this->datatables->where('wp.quantity !=', 0);
		}


		$this->datatables->add_column("Actions", "product_code");
		echo $this->datatables->generate();
    }

    function getCategoriesReport($pdf = NULL, $xls = NULL)
    {
        //$this->erp->checkPermissions('categories', TRUE);
		$this->erp->checkPermissions('products', TRUE);
        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
		
		if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
		
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
		
		if($biller_id){
			$where_p_biller = "AND p.biller_id = {$biller_id} ";
			$where_s_biller = "AND s.biller_id = {$biller_id} ";
			$where_p_biller_where = "WHERE p.biller_id = {$biller_id} ";
			$where_s_biller_where = "WHERE s.biller_id = {$biller_id} ";
		}else{
			$where_p_biller = 'AND 1=1 ';
			$where_s_biller = 'AND 1=1 ';
			$where_p_biller_where = "WHERE 1=1 ";
			$where_s_biller_where = "WHERE 1=1 ";
		}
		
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $end_date ? $this->erp->fld($end_date) : date('Y-m-d');

            $pp = "( SELECT pp.category_id as category, pi.product_id, SUM( pi.quantity ) purchasedQty, SUM( pi.net_unit_cost * pi.quantity ) totalPurchase, p.date as pdate from " . $this->db->dbprefix('products') . " pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi on pp.id = pi.product_id
                left join " . $this->db->dbprefix('purchases') . " p ON p.id = pi.purchase_id
				where p.date >= '{$start_date}' and p.date < '{$end_date}' group by pp.category_id 
                ) PCosts";
            $sp = "( SELECT sp.category_id as category, si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale, s.date as sdate from " . $this->db->dbprefix('products') . " sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si on sp.id = si.product_id
                left join " . $this->db->dbprefix('sales') . " s ON s.id = si.sale_id
                where s.date >= '{$start_date}' and s.date < '{$end_date}' group by sp.category_id 
                ) PSales";
        } else {
            $pp = "( SELECT pp.category_id as category, pi.product_id, SUM( pi.quantity ) purchasedQty, SUM( pi.net_unit_cost * pi.quantity ) totalPurchase from " . $this->db->dbprefix('products') . " pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi on pp.id = pi.product_id 
                group by pp.category_id
                ) PCosts";
            $sp = "( SELECT sp.category_id as category, si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from " . $this->db->dbprefix('products') . " sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si on sp.id = si.product_id 
                group by sp.category_id 
                ) PSales";
        }
        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('categories') . ".id as cidd, " .$this->db->dbprefix('categories') . ".code, " . $this->db->dbprefix('categories') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left')
            ->group_by('categories.id');

            if ($category) {
                $this->db->where($this->db->dbprefix('categories') . ".id", $category);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('categories_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('category_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('category_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('profit_loss'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $profit);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl += $profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":G" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);

                $filename = 'categories_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {


            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('categories') . ".id as cidd, " .$this->db->dbprefix('categories') . ".code, " . $this->db->dbprefix('categories') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left')
            ->group_by('categories.id');

            if ($category) {
                $this->datatables->where($this->db->dbprefix('categories') . ".id", $category);
            }
            $this->datatables->unset_column('cid');
            echo $this->datatables->generate();

        }

    }
	
	function getCategoriesValueReport($pdf = NULL, $xls = NULL)
    {
        //$this->erp->checkPermissions('categories', TRUE);
		$this->erp->checkPermissions('products', TRUE);
        if ($pdf || $xls) {

            $this->db
                ->select('categories.id AS cid, categories.code, categories.name, COALESCE(SUM(quantity), 0) AS current_stock, COALESCE(SUM(cost), 0) AS total_cost, COALESCE(SUM(price), 0) AS total_price')
				->from('categories')
				->join('products', 'products.category_id = categories.id', 'left')
				->group_by('categories.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('categories_value_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('category_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('category_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('category_stock'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('costs'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('price'));

                $row = 2;
                $sQty = 0;
                $cQty = 0;
                $pAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->current_stock);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->total_cost);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->total_price);
                    $sQty += $data_row->current_stock;
                    $cQty += $data_row->total_cost;
                    $pAmt += $data_row->total_price;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":E" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $cQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pAmt);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);

                $filename = 'categories_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select('categories.id AS cid, categories.code, categories.name, COALESCE(SUM(quantity), 0) AS current_stock, COALESCE(SUM(cost), 0) AS total_cost, COALESCE(SUM(price), 0) AS total_price')
				->from('categories')
				->join('products', 'products.category_id = categories.id', 'left')
				->group_by('categories.id');

			$this->datatables->unset_column('cid');
            echo $this->datatables->generate();
        }
    }
	
	function shops()
    {
        $this->erp->checkPermissions('sales', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('shops_report')));
        $meta = array('page_title' => lang('billers_report'), 'bc' => $bc);
        $this->page_construct('reports/shops', $meta, $this->data);
    }
	
	function getShops($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('customers', TRUE);

        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
                ->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('customers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_sales'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->erp->formatNumber($data_row->total));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->erp->formatMoney($data_row->total_amount));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->erp->formatMoney($data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->erp->formatMoney($data_row->balance));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'customers_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
                ->group_by('companies.id')
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/customer_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
                ->unset_column('id');
            echo $this->datatables->generate();

        }

    }
	
	function getBillers($pdf = NULL, $xls = NULL)
    {
        //$this->erp->checkPermissions('biller', TRUE);
		$this->erp->checkPermissions('sales', TRUE);
        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(" . $this->db->dbprefix('sales') . ".grand_total), 0) as total_amount, (COALESCE(sum(" . $this->db->dbprefix('sales') . ".grand_total), 0) * (" . $this->db->dbprefix('companies') . ".cf6/100)) as total_earned, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.biller_id=companies.id')
                ->where('companies.group_name', 'biller')
                ->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('billers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_sales'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('total_earned'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->erp->formatNumber($data_row->total));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->erp->formatMoney($data_row->total_amount));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->erp->formatMoney($data_row->total_earned));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->erp->formatMoney($data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->erp->formatMoney($data_row->balance));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $filename = 'billers_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as idd, company, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(" . $this->db->dbprefix('sales') . ".grand_total), 0) as total_amount, (COALESCE(sum(" . $this->db->dbprefix('sales') . ".grand_total), 0) * (" . $this->db->dbprefix('companies') . ".cf6/100)) as total_earned, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.biller_id=companies.id')
                ->where('companies.group_name', 'biller')
                ->group_by('companies.id')
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/biller_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "idd")
                ->unset_column('id');
            echo $this->datatables->generate();

        }

    }
	
	function shop_report($user_id = NULL)
    {
        $this->erp->checkPermissions('customers', TRUE);
        if (!$user_id && $_GET['d'] == null) {
            $this->session->set_flashdata('error', lang("no_customer_selected"));
            redirect('reports/shops');
        }

        $this->data['sales'] = $this->reports_model->getSalesTotals($user_id);
        $this->data['total_sales'] = $this->reports_model->getCustomerSales($user_id);
        $this->data['total_quotes'] = $this->reports_model->getCustomerQuotes($user_id);
        $this->data['total_returns'] = $this->reports_model->getCustomerReturns($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
			
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
		
		$this->data['date'] = $date;
		
        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('billers_report')));
        $meta = array('page_title' => lang('billers_report'), 'bc' => $bc);
        $this->page_construct('reports/shop_report', $meta, $this->data);
    }

    function profit($date = NULL)
    {
        if ( ! $this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->erp->md();
        }
        if ( ! $date) { $date = date('Y-m-d'); }
        $this->data['revenues'] 		= $this->reports_model->getDailySaleRevenues($date);
        $this->data['costing'] 			= $this->reports_model->getCosting($date);
		$this->data['costing_return']   = $this->reports_model->getReturnTotalCost($date);
        $this->data['expenses'] 		= $this->reports_model->getExpenses($date);
		$this->data['discount_date'] 	= $this->reports_model->getTotalDiscountDate($date);
        $refund 						=  $this->reports_model->getSalesReturnDate($date);
		$this->data['refunds'] 			= $refund;
        //$this->data['discount'] 		= $refund->order_discount;
		$this->data['discount'] 		= $this->reports_model->getTotalDiscountDate($date);
		$this->data['count_dis'] 		= $this->reports_model->Count_Sale_discount($date);
		$this->data['date'] 			= $date;
        $this->load->view($this->theme . 'reports/profit', $this->data);
    }
	
	function profits($date = NULL)
    { 
        $this->erp->checkPermissions('sales', true, 'reports');
        
        if ( ! $date) { $date = date('Y-m-d'); }
        $this->data['sales'] = $this->reports_model->getSaleDailies($date);		
        $this->data['date'] = $date;
        $this->load->view($this->theme . 'reports/profits', $this->data); 
    }
	
	function profitPurchase($date = NULL)
    {
        if ( ! $this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->erp->md();
        }
        if ( ! $date) { $date = date('Y-m-d'); }
        $this->data['purchase'] = $this->reports_model->getPurchaseing($date);		
        $this->data['date'] = $date;
        $this->load->view($this->theme . 'reports/purchasesreport', $this->data);
    }
	
	function daily_sales($year = NULL, $month = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->erp->checkPermissions('sales', true);
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $config = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => site_url('reports/daily_sales'),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable">{/table_open}
		{heading_row_start}<tr>{/heading_row_start}
		{heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
		{heading_title_cell}<th colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
		{heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
		{heading_row_end}</tr>{/heading_row_end}
		{week_row_start}<tr>{/week_row_start}
		{week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
		{week_row_end}</tr>{/week_row_end}
		{cal_row_start}<tr class="days">{/cal_row_start}
		{cal_cell_start}<td class="day">{/cal_cell_start}
		{cal_cell_content}
		<div class="day_num">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content}
		{cal_cell_content_today}
		<div class="day_num highlight">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content_today}
		{cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
		{cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
		{cal_cell_blank}&nbsp;{/cal_cell_blank}
		{cal_cell_end}</td>{/cal_cell_end}
		{cal_row_end}</tr>{/cal_row_end}
		{table_close}</table>{/table_close}';

        $this->load->library('calendar', $config);
		$sales = $user_id ? $this->reports_model->getStaffDailySales($user_id, $year, $month) : $this->reports_model->getDailySales($year, $month);
		
        if (!empty($sales)) {
            foreach ($sales as $sale) {
				$d = date('Y-m-d', strtotime($year . '-' . $month . '-' . $sale->date));
				$refund 		= $this->reports_model->getSalesReturnDate($d);
				$costing_return = $this->reports_model->getReturnTotalCost($d); 
				$expenses 		= $this->reports_model->getExpenses($d); 
				$quantity  		= $this->reports_model->getTotalQty($d);

                $daily_sale[$sale->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("Total quantity") . "</td><td>" . $this->erp->formatQuantity($quantity->total_qty) . "</td></tr><tr><td>" . lang("Sales' Revenue") . "</td><td>" . $this->erp->formatMoney($sale->no_total) . "</td></tr><tr><td>" . lang("order_discount") . "</td><td>" . $this->erp->formatMoney($sale->order_discount) . "</td></tr><tr><td>" . lang("sales_refund") . "</td><td>" . $this->erp->formatMoney($refund->paid) . "</td></tr><tr><td>" . lang("Products' Cost") . "</td><td>" . $this->erp->formatMoney($sale->total_cost-$costing_return->total_cost) . "</td></tr>
				<tr><td>" . lang("expenses") . "</td><td>" . $this->erp->formatMoney($expenses->total) . "</td></tr>
				<tr><td>" . lang("Profit") . "</td><td>" . $this->erp->formatMoney($sale->no_total-$sale->order_discount-$refund->paid-($sale->total_cost-$costing_return->total_cost)-$expenses->total) . "</td></tr></table>";
            }
        } else {
            $daily_sale = array();
        }
		
        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/daily', $this->data, true);
            $name = lang("daily_sales") . "_" . $year . "_" . $month . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->erp->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_sales_report')));
        $meta = array('page_title' => lang('daily_sales_report'), 'bc' => $bc);
        $this->page_construct('reports/daily', $meta, $this->data);

    }
    
	function daily_sales_1($year = NULL, $month = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->erp->checkPermissions('daily_sales');
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $config = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => site_url('reports/daily_sales'),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable">{/table_open}
		{heading_row_start}<tr>{/heading_row_start}
		{heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
		{heading_title_cell}<th colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
		{heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
		{heading_row_end}</tr>{/heading_row_end}
		{week_row_start}<tr>{/week_row_start}
		{week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
		{week_row_end}</tr>{/week_row_end}
		{cal_row_start}<tr class="days">{/cal_row_start}
		{cal_cell_start}<td class="day">{/cal_cell_start}
		{cal_cell_content}
		<div class="day_num">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content}
		{cal_cell_content_today}
		<div class="day_num highlight">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content_today}
		{cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
		{cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
		{cal_cell_blank}&nbsp;{/cal_cell_blank}
		{cal_cell_end}</td>{/cal_cell_end}
		{cal_row_end}</tr>{/cal_row_end}
		{table_close}</table>{/table_close}';

        $this->load->library('calendar', $config);
        $sales = $user_id ? $sales = $this->reports_model->getStaffDailySales($user_id, $year, $month) : $this->reports_model->getDailySales($year, $month);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $daily_sale[$sale->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->erp->formatMoney($sale->discount) . "</td></tr><tr><td>" . lang("shipping") . "</td><td>" . $this->erp->formatMoney($sale->shipping) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->erp->formatMoney($sale->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->erp->formatMoney($sale->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->erp->formatMoney($sale->total) . "</td></tr><tr><td>" . lang("award_points") . "</td><td>" . intval($sale->total / $this->Settings->each_sale) . "</td></tr></table>";
            }
        } else {
            $daily_sale = array();
        }

        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/daily', $this->data, true);
            $name = lang("daily_sales") . "_" . $year . "_" . $month . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->erp->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_sales_report')));
        $meta = array('page_title' => lang('daily_sales_report'), 'bc' => $bc);
        $this->page_construct('reports/daily', $meta, $this->data);

    }

    function monthly_sales($year = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->erp->checkPermissions('sales',true);
        if (!$year) {
            $year = date('Y');
            $year = date('Y');
        } 
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->load->language('calendar');
        $this->data['error'] 		= (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['year'] 		= $year;
        $this->data['sales'] 		= $user_id ? $this->reports_model->getStaffMonthlySaleman($user_id, $year):$this->reports_model->getMonthlySales($year);
		$this->data['total_qty' ] 	= $this->reports_model->getTotalMonthlyQty($year);
		$this->data['total_return'] = $this->reports_model->getReturnMonthlySales($year); 
		$this->data['Exespence'] 	= $this->reports_model->getExespens($year);
		
		if ($pdf){
            $html = $this->load->view($this->theme . 'reports/monthly', $this->data, true);
            $name = lang("monthly_sales") . "_" . $year . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->erp->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
		
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('monthly_sales_report')));
        $meta = array('page_title' => lang('monthly_sales_report'), 'bc' => $bc);
        $this->page_construct('reports/monthly', $meta, $this->data);
    }
	
	function monthly_profit($year, $month, $warehouse_id = NULL)
    {
        $this->erp->checkPermissions('sales', false, 'reports');
        
        $this->data['costing'] = $this->reports_model->getMonthSales(NULL, $warehouse_id, $year, $month);
        $this->data['date'] = date('F Y', strtotime($year.'-'.$month.'-'.'01'));
        $this->load->view($this->theme . 'reports/monthly_profit', $this->data);
    }
	
	function monthly_profits($year, $month, $warehouse_id = NULL)
    {
        if ( ! $this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->erp->md();
        }
        
        $this->data['costing'] = $this->reports_model->getMonthCosting(NULL, $warehouse_id, $year, $month);
		//$this->erp->print_arrays($this->data['costing']);
        $this->data['discount'] = $this->reports_model->getOrderDiscount(NULL, $warehouse_id, $year, $month);
        $this->data['expenses'] = $this->reports_model->getExpense(NULL, $warehouse_id, $year, $month);
        $this->data['returns'] = $this->reports_model->getReturns(NULL, $warehouse_id, $year, $month);
        $this->data['date'] = date('F Y', strtotime($year.'-'.$month.'-'.'01'));
        $this->load->view($this->theme . 'reports/monthly_profit', $this->data);
    }

	function purchase_monthly($year, $month, $warehouse_id = NULL)
    {
        if ( ! $this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->erp->md();
        }
        
        $this->data['costing'] = $this->reports_model->getMonthPurchaseing(NULL, $warehouse_id, $year, $month);
        $this->data['date'] = date('F Y', strtotime($year.'-'.$month.'-'.'01'));
        $this->load->view($this->theme . 'reports/purchase_monthly', $this->data);
    }

	
    function sales_profit($biller_id = NULL)
    {
        $this->erp->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        
		$user = $this->site->getUser();
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			if($user->biller_id){
				$this->data['biller_id'] = $user->biller_id;
			}else{
				$this->data['biller_id'] = "";
			}
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
		
		$this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('sales_profit_report')));
        $meta = array('page_title' => lang('sales_profit_report'), 'bc' => $bc);
        $this->page_construct('reports/sales_profit', $meta, $this->data);
    }

    function getSalesProfitReport($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('sales', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
		
		if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
		
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
		if($this->input->get("customer_group")){
		   $customer_group = $this->input->get("customer_group");
        }else {
		   $customer_group = NULL;
        }        
	
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($this->input->get('serial')) {
            $serial = $this->input->get('serial');
        } else {
            $serial = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
		
		$p_cost = "COALESCE (
						(
							SELECT
								CASE
							WHEN type <> 'combo' THEN
								(
									SELECT
										SUM(
											cost * erp_sale_items.quantity
										)
									FROM
										erp_products pp
									WHERE
										pp.id = erp_sale_items.product_id
								)
							ELSE
								(
									SELECT
										SUM(
											erp_products.cost * erp_sale_items.quantity
										) AS cost
									FROM
										erp_combo_items
									INNER JOIN erp_products ON erp_products.`code` = erp_combo_items.item_code
									WHERE
										erp_combo_items.product_id = erp_sale_items.product_id
								)
							END
							FROM
								erp_products
							WHERE
								erp_products.id = erp_sale_items.product_id
						),
						0
					)";

        if ($pdf || $xls) {

            $this->db
					->select("date, reference_no, biller, customer, grand_total, paid, (grand_total-paid) as balance,
                    " . $p_cost . " AS total_cost,
                    COALESCE (
                        COALESCE (
                            (
                                grand_total
                            ),
                            0
                        ) - COALESCE (
                            (
                                SELECT
                                    SUM(cost * " . $this->db->dbprefix('sale_items') . ".quantity)
                                FROM
                                    " . $this->db->dbprefix('sale_items') . "
                                INNER JOIN " . $this->db->dbprefix('products') . " ON " . $this->db->dbprefix('products') . ".id = " . $this->db->dbprefix('sale_items') . ".product_id
                                WHERE
                                    " . $this->db->dbprefix('sale_items') . ".sale_id = " . $this->db->dbprefix('sales') . ".id
                            ),
                            0
                        )
                    ) AS profit, payment_status", FALSE)
					->from('sales')
					->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
					->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
					->join('companies', 'companies.id=sales.customer_id','left')                
					->join('customer_groups','customer_groups.id=companies.customer_group_id','left')
					->group_by('sales.id');
            
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('sales.created_by', $user);
				}
			}
			if ($biller_id) {
                $this->db->where('sales.biller_id', $biller_id);
            }
            if ($product) {
                $this->db->like('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
			if($customer_group){
			   $this->db->where('companies.customer_group_id', $customer_group);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('cost_'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('profit'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
				$total_cost_amount = 0;
				$total_profit = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->grand_total);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->total_cost);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->profit);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->payment_status);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
					$total_cost_amount += $data_row->total_cost;
					$total_profit += $data_row->profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("E" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $balance);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $total_cost_amount);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $total_profit);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'sales_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
					$this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->applyFromArray(
					 array(
						 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
					 ));
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
					$this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->applyFromArray(
					 array(
						 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
						 'wrap'       => true
					 ));
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');

				$this->datatables
					->select("erp_sales.id, date, reference_no,suspend_note ,biller, customer, grand_total, paid, (grand_total-paid) as balance,
                    COALESCE(erp_sales.total_cost,0),
                    COALESCE (
                        COALESCE (
                            (
                                grand_total
                            ),
                            0
                        ) -   COALESCE(erp_sales.total_cost,0)
                    ) AS profit, payment_status", FALSE)
					->from('sales')
					->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
					->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
					->join('companies', 'companies.id=sales.customer_id','left')                
					->join('customer_groups','customer_groups.id=companies.customer_group_id','left')
					->group_by('sales.id');
            
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('sales.created_by', $user);
				}
			}
			if ($biller_id) {
                $this->datatables->where('sales.biller_id', $biller_id);
            }
            if ($product) {
                $this->datatables->like('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->datatables->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if($customer_group){
			   $this->datatables->where('companies.customer_group_id', $customer_group);                
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            echo $this->datatables->generate();
        }

    }

    function sales($biller_id = NULL)
    {
        $this->erp->checkPermissions('sales');
		
		$user = $this->site->getUser();
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			if($user->biller_id){
				$this->data['biller_id'] = $user->biller_id;
			}else{
				$this->data['biller_id'] = "";
			}
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
		$this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
		$this->data['customers'] = $this->site->getCustomers();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('sales_report')));
        $meta = array('page_title' => lang('sales_report'), 'bc' => $bc);
        $this->page_construct('reports/sales', $meta, $this->data);
    }
    
	function sale_report_action($id = null){
	    if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf'){
            if (!empty($_POST['val'])) {
					 
					 	
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('using_stock'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang("product_code"));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang("product_name"));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang("brands"));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang("serial_no"));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang("quantity"));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang("unit_price"));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang("discount"));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang("subtotal"));
                $this->excel->getActiveSheet()->getStyle('A1'. $row.':G1'.$row)->getFont()->setBold(true);
				
                $config["ob_set"] = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
                $config["per_page"] = 50; 
				$row=2;
			    foreach($_POST['val'] as $id){ 
				   $sale =(
						"SELECT 
						erp_sales.id,
						1 as type,
						erp_sales.date,
						erp_sales.return_id,
						erp_sales.reference_no,
						erp_sales.customer,
						erp_sales.customer_id,
						erp_warehouses.name as warehouse,
						erp_sales.warehouse_id,
						erp_sales.order_discount,
						erp_sales.created_by,
						erp_sales.pos
						From erp_sales
						LEFT JOIN erp_warehouses ON erp_warehouses.id=erp_sales.warehouse_id 
					"); 
					
					$return =("Select
						 erp_return_sales.id,
						 2 as type,		 
						 erp_return_sales.date,
						 erp_return_sales.sale_id,	 
						 erp_return_sales.reference_no,
						 erp_return_sales.customer,
						 erp_return_sales.customer_id,
						 erp_warehouses.name as warehouse,
						 erp_return_sales.warehouse_id,
						 erp_return_sales.order_discount,
						 erp_return_sales.created_by,
						 0 as pos
						 From erp_return_sales
						 LEFT JOIN erp_warehouses ON erp_warehouses.id=erp_return_sales.warehouse_id 
					");
				   $sales=$this->db->query("Select *from ({$sale} UNION {$return}) as temp Where 1=1 AND id = {$id} ORDER BY id DESC ")->result(); 
				   foreach($sales as $sale){ 
					  $this->excel->getActiveSheet()->SetCellValue('a'. $row, $sale->date ." >> ". $sale->reference_no ." >> ".$sale->customer ." >> ".$sale->warehouse);	
					  $this->excel->getActiveSheet()->mergeCells("a".($row).":h".($row));	
					  $this->excel->getActiveSheet()->getStyle("a".($row).":h".($row))->getFont()->setBold(true); 
					  $row++;
      	    	  }
			
				}
				
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);

                //set font bold,font color,font size,font name and background color to excel  by dara
                $styleArray = array(
                    'font'  => array(
                        'bold'  => true,
                        'color' => array('rgb' => 'FFFFFF'),
                        'size'  => 11,
                        'name'  => 'Verdana'
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '428BCA')
                    )
                );
            
                $this->excel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($styleArray);
                $row = 2;
                foreach ($_POST['val'] as $id){

                     
                }
                 
                $filename = lang('Report List Using Stock'). date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($this->input->post('form_action') == 'export_pdf') {
                    $styleArray = array(
                        'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                        PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                 
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
        } else {
            $this->session->set_flashdata('error', $this->lang->line("no_selected. Please select at least one"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
      }
	}
    
	function getSalesReport($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('sales', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
		
		if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
		
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
		if($this->input->get("customer_group")){
		   $customer_group = $this->input->get("customer_group");
        }else {
		   $customer_group = NULL;
        }        
	
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($this->input->get('serial')) {
            $serial = $this->input->get('serial');
        } else {
            $serial = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db
					->select("date, reference_no, biller, customer,GROUP_CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name SEPARATOR '\n') as iname, GROUP_CONCAT(ROUND(". $this->db->dbprefix('sale_items') . ".quantity) SEPARATOR '\n') as iqty, grand_total, paid, (grand_total-paid) as balance, payment_status", FALSE)
					->from('sales')
					->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
					->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
					->join('companies', 'companies.id=sales.customer_id','left')                
					->join('customer_groupss','customer_groups.id=companies.customer_group_id','left')
					->group_by('sales.id');
           if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('sales.created_by', $user);
				}
		   }
			if ($biller_id) {
                $this->db->where('sales.biller_id', $biller_id);
            }
            if ($product) {
                $this->db->like('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
			if($customer_group){
			   $this->db->where('companies.customer_group_id', $customer_group);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            } 
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row){
                    $data[] = $row;
                }
            }else{
                $data = NULL;
            }

            if (!empty($data)){

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('quantity'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));
				
                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->iqty);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->payment_status);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("G" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'sales_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
					$this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->applyFromArray(
					 array(
						 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
					 ));
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
					$this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->applyFromArray(
					 array(
						 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
						 'wrap'       => true
					 ));
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');

				$this->datatables
					->select("erp_sales.id, date, reference_no, biller, customer,CONCAT(GROUP_CONCAT(".$this->db->dbprefix('sale_items').".product_code ,'[',ROUND(". $this->db->dbprefix('sale_items') . ".quantity),'] ___',".$this->db->dbprefix('sale_items').".serial_no  SEPARATOR '<br>')) as iname,grand_total, paid, (grand_total-paid) as balance, paid as cost, paid as profit_lost, payment_status", FALSE)
					->from('sales')
					->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
					->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
					->join('companies', 'companies.id=sales.customer_id','left')                
					->join('customer_groups','customer_groups.id=companies.customer_group_id','left')
					->group_by('sales.id');
            
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('sales.created_by', $user);
				}
			}
            
			if ($biller_id) {
                $this->datatables->where('sales.biller_id', $biller_id);
            }
            if ($product) {
                $this->datatables->like('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->datatables->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if($customer_group){
			   $this->datatables->where('companies.customer_group_id', $customer_group);                
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            echo $this->datatables->generate();
        }
    }
	
	function getsale_brand_report(){
		
		$data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		
		$this->data['brands'] =$this->reports_model->getAllbrand();
		
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('brand_sales_daily')));
        $meta = array('page_title' => lang('brand_sales_daily'), 'bc' => $bc);
        $this->page_construct('reports/brand_sales_daily', $meta, $this->data);
	}
	
	function brand_actions(){
          
            if (!empty($_POST['val'])){
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf'){

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Brand_Sales_Daily'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date')); 
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('brands')); 
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('quantity')); 
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));

                    $row = 2;
					$total_qty=0; 
					if($this->input->post('starts')){
						$start_date =$this->erp->fld($this->input->post('starts')); 
					}else{ 
						$start_date = date('Y-m-d 00:00:00');
					}
					if($this->input->post('ends')){
						$end_date  = $this->erp->fld($this->input->post('ends'));
					}else{
						$end_date =date('Y-m-d 23:55:00');   
		            }
                    foreach ($_POST['val'] as $id) {
						
                        $sc = $this->reports_model->getBrandExport($id,$start_date,$end_date);
                        if(count($sc)>0){ 
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->date);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->brand);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->qty);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->gt); 
						}
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'purchases_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            } 
	}
	
	function getbrands(){
		   
		if($this->input->get('brand')){
			$brand=$this->input->get('brand');
		}else{
			$brand =null;
		} 
	    if($this->input->get('start_date')){
			$start_date =$this->erp->fld($this->input->get('start_date'));
		}else{ 
			$start_date = date('Y-m-d 00:00:00');
		}
		if($this->input->get('end_date')){
			$end_date  = $this->erp->fld($this->input->get('end_date'));
		}else{
			$end_date =date('Y-m-d 23:55:00');   
		}
		$this->load->library('datatables');  
        $this->datatables
            ->select("erp_brands.id, erp_sales.date, erp_brands.name, SUM(erp_sale_items.quantity) as siq, SUM(erp_sale_items.subtotal)")
            ->from('sales')
            ->join('sale_items','sale_items.sale_id=sales.id', 'left')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('brands', 'brands.id=products.brand_id','left') 
            ->where("erp_sales.date BETWEEN  '$start_date' AND '$end_date'")	
            ->order_by('siq','DESC')	
            ->group_by('erp_brands.id');
	    
		 if($brand){
			 $this->datatables->where('erp_brands.id',$brand);
		 }
		 if($start){
			$this->datatables->where("erp_sales.date BETWEEN '$start' AND '$end'");
		 }
		 echo $this->datatables->generate();
	}
	function getProductSaleReport($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('sales', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
		if ($this->input->get('product_type')) {
            $product_type = $this->input->get('product_type');
        } else {
            $product_type = NULL;
        }
		
		if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
		
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
		if($this->input->get("customer_group")){
		   $customer_group = $this->input->get("customer_group");
        }else {
		   $customer_group = NULL;
        }        
	
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($this->input->get('serial')) {
            $serial = $this->input->get('serial');
        } else {
            $serial = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db
					->select("date, reference_no, biller, customer,GROUP_CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name SEPARATOR '\n') as iname, GROUP_CONCAT(ROUND(". $this->db->dbprefix('sale_items') . ".quantity) SEPARATOR '\n') as iqty, grand_total, paid, (grand_total-paid) as balance, payment_status", FALSE)
					->from('sales')
					->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
					->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
					->join('companies', 'companies.id=sales.customer_id','left')                
					->join('customer_groupss','customer_groups.id=companies.customer_group_id','left')
					->group_by('sales.id');
           if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('sales.created_by', $user);
				}
		   }
			if ($biller_id) {
                $this->db->where('sales.biller_id', $biller_id);
            }
            if ($product) {
                $this->db->like('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
			if($customer_group){
			   $this->db->where('companies.customer_group_id', $customer_group);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            } 
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row){
                    $data[] = $row;
                }
            }else{
                $data = NULL;
            }

            if (!empty($data)){

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('quantity'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));
				
                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->iqty);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->payment_status);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("G" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'sales_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
					$this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->applyFromArray(
					 array(
						 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
					 ));
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
					$this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->applyFromArray(
					 array(
						 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
						 'wrap'       => true
					 ));
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');
			    if($product_type == 'combo' || $product_type == 'service'){
					$this->datatables
						->select("sales.date, 
								  sales.reference_no, 
								  sales.biller, 
								  sales.customer,
						          GROUP_CONCAT((".$this->db->dbprefix('sale_items') . ".quantity) SEPARATOR '___') as iqty, 
								  sales.grand_total, 
								  sales.paid, 
								  (grand_total-paid) as balance,
								  sales.payment_status", FALSE)
						->from('sales')
						->join('sale_items', 'sale_items.sale_id = sales.id', 'left')
						->join('purchase_items', 'purchase_items.sale_id = sales.id', 'left')
						->join('warehouses', 'warehouses.id = sales.warehouse_id', 'left')
						->join('companies', 'companies.id = sales.customer_id','left')                
						->join('customer_groups','customer_groups.id = companies.customer_group_id','left')
						->where('sale_items.product_id', $product)
						->group_by('sales.id');
				}else{
					$this->datatables
						->select("sales.date, 
						          sales.reference_no, 
								  sales.biller, 
								  sales.customer,GROUP_CONCAT(abs(".$this->db->dbprefix('purchase_items') . ".quantity_balance) SEPARATOR '___') as iqty, 
								  sales.grand_total, 
								  sales.paid, 
								  (grand_total-paid) as balance,
								  sales.payment_status", FALSE)
						->from('sales')
						->join('sale_items', 'sale_items.sale_id = sales.id', 'left')
						->join('purchase_items', 'purchase_items.sale_id = sales.id', 'left')
						->join('warehouses', 'warehouses.id = sales.warehouse_id', 'left')
						->join('companies', 'companies.id = sales.customer_id','left')                
						->join('customer_groups','customer_groups.id = companies.customer_group_id','left')
						->where(array('purchase_items.product_id' => $product, 'purchase_items.transaction_type' => 'sale'))
						->group_by('sales.id');
				}
            
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('sales.created_by', $user);
				}
			}
			if ($biller_id) {
                $this->datatables->where('sales.biller_id', $biller_id);
            }
            if ($serial) {
                $this->datatables->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if($customer_group){
			   $this->datatables->where('companies.customer_group_id', $customer_group);                
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            echo $this->datatables->generate();
        }

    }
	
	function getSalemanReport($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('sales', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
		if($this->input->get("customer_group")){
		   $customer_group = $this->input->get("customer_group");
        }else {
		   $customer_group = NULL;
        }        
	
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($this->input->get('serial')) {
            $serial = $this->input->get('serial');
        } else {
            $serial = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db
					->select("date, reference_no, biller, customer,GROUP_CONCAT(CONCAT( '[', " . $this->db->dbprefix('sale_items') . ".product_code, ']', ' - ', CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name, '__', " . $this->db->dbprefix('sale_items') . ".quantity)) SEPARATOR '___') as iname, grand_total, paid, (grand_total-paid) as balance,
                    payment_status", FALSE)
					->from('sales')
					->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
					->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
					->join('companies', 'companies.id=sales.customer_id','left')                
					->join('customer_groups','customer_groups.id=companies.customer_group_id','left')
					->group_by('sales.id');
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where("(CASE WHEN saleman_by <> '' THEN saleman_by ELSE created_by END) = {$user} ");
				}
			}
            if ($product) {
                $this->db->like('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
			if($customer_group){
			   $this->db->where('companies.customer_group_id', $customer_group);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->payment_status);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row . ":H" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $filename = 'sales_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');

				$this->datatables
					->select("date, reference_no, biller, customer,GROUP_CONCAT(CONCAT( '[', " . $this->db->dbprefix('sale_items') . ".product_code, ']', ' - ', CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name, '__', " . $this->db->dbprefix('sale_items') . ".quantity)) SEPARATOR '___') as iname, grand_total, paid, (grand_total-paid) as balance,
                    payment_status", FALSE)
					->from('sales')
					->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
					->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
					->join('companies', 'companies.id=sales.customer_id','left')                
					->join('customer_groups','customer_groups.id=companies.customer_group_id','left')
					->group_by('sales.id');
            
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('sales.created_by', $user);
				}
			}
            if ($product) {
                $this->datatables->like('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->datatables->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if($customer_group){
			   $this->datatables->where('companies.customer_group_id', $customer_group);                
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            echo $this->datatables->generate();
        }

    }

	function getSellReport($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('sales', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
		if($this->input->get("customer_group")){
		   $customer_group = $this->input->get("customer_group");
        }else {
		   $customer_group = NULL;
        }        
	
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($this->input->get('serial')) {
            $serial = $this->input->get('serial');
        } else {
            $serial = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db
                ->select("date, reference_no, biller, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name, ' (', " . $this->db->dbprefix('sale_items') . ".quantity, ')') SEPARATOR '\n') as iname, grand_total, paid, payment_status", FALSE)
                ->from('sales')
                ->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->join('companies', 'sales.biller_id=companies.id','left')
                ->group_by('sales.id')
                ->order_by('sales.date desc');
            
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('sales.created_by', $user);
				}
			}
            if ($product) {
                $this->db->like('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
			if($customer_group){
			   $this->db->where('companies.customer_group_id', $customer_group);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->payment_status);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row . ":H" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $filename = 'sales_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');

				$this->datatables
					->select("date, reference_no, biller, customer,GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name, '__', " . $this->db->dbprefix('sale_items') . ".quantity) SEPARATOR '___') as iname, grand_total, paid, (grand_total-paid) as balance, payment_status", FALSE)
					->from('sales')
					->join('sale_items', 'sale_items.sale_id=sales.id')
					->join('warehouses', 'warehouses.id=sales.warehouse_id')
					->join('companies', 'companies.id=sales.biller_id')
					->where('sales.biller_id', $biller)
					//->join('customer_groups','customer_groups.id=companies.customer_group_id','inner')
					->group_by('sales.id');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('sales.created_by', $user);
				}
			}
            if ($product) {
                $this->datatables->like('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->datatables->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if($customer_group){
			   $this->datatables->where('companies.customer_group_id', $customer_group);                
            }            
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }
	
	function deliveries() {
		$this->erp->checkPermissions('sales', true);
		
		if (!$start_date) {
            //$start = $this->db->escape(date('Y-m') . '-1');
           // $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            //$end = $this->db->escape(date('Y-m-d H:i'));
            //$end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }

        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		
		$this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);
		
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('sale_by_delivery_person')));
        $meta = array('page_title' => lang('deliveries'), 'bc' => $bc);
        $this->page_construct('reports/deliveries', $meta, $this->data);
	}
	
	function getDeliveries($start = NULL, $end = NULL)
    {
        $this->erp->checkPermissions('sales', true);

		$print_cabon_link = anchor('sales/view_delivery_cabon/$1', '<i class="fa fa-file-text-o"></i> ' . lang('print_cabon'), 'data-toggle="modal" data-target="#myModal"');
        $detail_link = anchor('sales/view_delivery/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_details'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email_delivery/$1', '<i class="fa fa-envelope"></i> ' . lang('email_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit_delivery/$1', '<i class="fa fa-edit"></i> ' . lang('edit_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $pdf_link = anchor('sales/pdf_delivery/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_delivery") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_delivery/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_delivery') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
    <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $print_cabon_link . '</li>
		<li>' . $detail_link . '</li>
        <li>' . $edit_link . '</li>
        <li>' . $pdf_link . '</li>
        <li>' . $delete_link . '</li>
    </ul>
</div></div>';

        $this->load->library('datatables');
        //GROUP_CONCAT(CONCAT('Name: ', sale_items.product_name, ' Qty: ', sale_items.quantity ) SEPARATOR '<br>')
		
		$this->datatables
            ->select("deliveries.id as id, deliveries.date, username, do_reference_no, sale_reference_no, total, paid, (total-paid) AS grand_total, delivery_status")
            ->from('deliveries')
            ->join('sales', 'sales.id=deliveries.sale_id', 'left')
			->join('users', 'deliveries.delivery_by=users.id', 'left')
            ->group_by('deliveries.id');
		
		if($start && $end){
			$this->datatables->where('date BETWEEN "' . $start . '" AND "' . $end . '"');
		}
		
        $this->datatables->add_column("Actions", $action, "id");

        echo $this->datatables->generate();
    }
	function getQuotesProduct($pdf = NULL, $xls = NULL)
    {

        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
		if ($this->input->get('product_type')) {
            $product_type = $this->input->get('product_type');
        } else {
            $product_type = NULL;
        }
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if ($pdf || $xls) {

            $this->db
                ->select("date, reference_no, biller, customer, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('quote_items') . ".product_name, ' (', " . $this->db->dbprefix('quote_items') . ".quantity, ')') SEPARATOR '<br>') as iname, grand_total, status", FALSE)
                ->from('quotes')
                ->join('quote_items', 'quote_items.quote_id=quotes.id', 'left')
                ->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
                ->group_by('quotes.id');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('quotes.created_by', $user);
				}
			}
            if ($product) {
                $this->db->like('quote_items.product_id', $product);
            }
            if ($biller) {
                $this->db->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('quotes').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('quotes_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'quotes_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');
			if($product_type == 'combo' || $product_type == 'service'){
				$this->datatables
					->select("date, 
							reference_no, 
							biller, 
							customer, 
							quote_items.quantity, 
							grand_total, status", FALSE)
					->from('quotes')
					->join('quote_items', 'quote_items.quote_id=quotes.id', 'left')
					->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
					->where('quote_items.product_id', $product)
					->group_by('quotes.id');
			}else{
				$this->datatables
					->select("date, 
							reference_no, 
							biller, 
							customer, 
							combo_items.quantity, 
							grand_total, status", FALSE)
					->from('quotes')
					->join('quote_items', 'quote_items.quote_id=quotes.id', 'left')
					->join('combo_items', 'combo_items.product_id=quote_items.product_id', 'left')
					->join('products', 'products.code=combo_items.item_code', 'left')
					->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
					->where('products.id', $product)
					->group_by('quotes.id');
			}
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('quotes.created_by', $user);
				}
			}
            if ($biller) {
                $this->datatables->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('quotes').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }
	
    function getQuotesReport($pdf = NULL, $xls = NULL)
    {

        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if ($pdf || $xls) {

            $this->db
                ->select("date, reference_no, biller, customer, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('quote_items') . ".product_name, ' (', " . $this->db->dbprefix('quote_items') . ".quantity, ')') SEPARATOR '<br>') as iname, grand_total, status", FALSE)
                ->from('quotes')
                ->join('quote_items', 'quote_items.quote_id=quotes.id', 'left')
                ->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
                ->group_by('quotes.id');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('quotes.created_by', $user);
				}
			}
            if ($product) {
                $this->db->like('quote_items.product_id', $product);
            }
            if ($biller) {
                $this->db->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('quotes').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('quotes_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'quotes_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("date, reference_no, biller, customer, quote_items.quantity, grand_total, status", FALSE)
                ->from('quotes')
                ->join('quote_items', 'quote_items.quote_id=quotes.id', 'left')
                ->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
                ->group_by('quotes.id');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('quotes.created_by', $user);
				}
			}
            if ($product) {
                $this->datatables->like('quote_items.product_id', $product);
            }
            if ($biller) {
                $this->datatables->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('quotes').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }

    function getTransfersReport($pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }

        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('transfers') . ".date, transfer_no, (CASE WHEN " . $this->db->dbprefix('transfers') . ".status = 'completed' THEN  GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".quantity, ')') SEPARATOR '<br>') ELSE GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('transfer_items') . ".product_name, ' (', " . $this->db->dbprefix('transfer_items') . ".quantity, ')') SEPARATOR '<br>') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, " . $this->db->dbprefix('transfers') . ".status")
                ->from('transfers')
                ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id')->order_by('transfers.date desc');
            if ($product) {
                $this->db->where($this->db->dbprefix('purchase_items') . ".product_id", $product);
                $this->db->or_where($this->db->dbprefix('transfer_items') . ".product_id", $product);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('transfers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('transfer_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse') . ' (' . lang('from') . ')');
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('warehouse') . ' (' . lang('to') . ')');
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->transfer_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->fname . ' (' . $data_row->fcode . ')');
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->tname . ' (' . $data_row->tcode . ')');
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'transfers_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('transfers') . ".date, transfer_no, (CASE WHEN " . $this->db->dbprefix('transfers') . ".status = 'completed' THEN  GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, '__', " . $this->db->dbprefix('purchase_items') . ".quantity) SEPARATOR '___') ELSE GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('transfer_items') . ".product_name, '__', " . $this->db->dbprefix('transfer_items') . ".quantity) SEPARATOR '___') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, " . $this->db->dbprefix('transfers') . ".status", FALSE)
                ->from('transfers')
                ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id');
            if ($product) {
                $this->datatables->where(" (({$this->db->dbprefix('purchase_items')}.product_id = {$product}) OR ({$this->db->dbprefix('transfer_items')}.product_id = {$product})) ", NULL, FALSE);
            }
            $this->datatables->edit_column("fname", "$1 ($2)", "fname, fcode")
                ->edit_column("tname", "$1 ($2)", "tname, tcode")
                ->unset_column('fcode')
                ->unset_column('tcode');
            echo $this->datatables->generate();
        }
    }
	
	function getTransfersProduct($pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }

        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('transfers') . ".date, transfer_no, (CASE WHEN " . $this->db->dbprefix('transfers') . ".status = 'completed' THEN  GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".quantity, ')') SEPARATOR '<br>') ELSE GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('transfer_items') . ".product_name, ' (', " . $this->db->dbprefix('transfer_items') . ".quantity, ')') SEPARATOR '<br>') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, " . $this->db->dbprefix('transfers') . ".status")
                ->from('transfers')
                ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id')->order_by('transfers.date desc');
            if ($product) {
                $this->db->where($this->db->dbprefix('purchase_items') . ".product_id", $product);
                $this->db->or_where($this->db->dbprefix('transfer_items') . ".product_id", $product);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('transfers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('transfer_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse') . ' (' . lang('from') . ')');
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('warehouse') . ' (' . lang('to') . ')');
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->transfer_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->fname . ' (' . $data_row->fcode . ')');
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->tname . ' (' . $data_row->tcode . ')');
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'transfers_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('transfers') . ".date,
						transfer_no, 
						(CASE WHEN " . $this->db->dbprefix('transfers') . ".status = 'completed' THEN  erp_purchase_items.quantity ELSE erp_transfer_items.quantity END) as iname, 
						from_warehouse_name as fname, 
						from_warehouse_code as fcode, 
						to_warehouse_name as tname,
						to_warehouse_code as tcode, 
						grand_total, 
						" . $this->db->dbprefix('transfers') . ".status", FALSE)
                ->from('transfers')
                ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id');
            if ($product) {
                $this->datatables->where(" (({$this->db->dbprefix('purchase_items')}.product_id = {$product}) OR ({$this->db->dbprefix('transfer_items')}.product_id = {$product})) ", NULL, FALSE);
            }
            $this->datatables->edit_column("fname", "$1 ($2)", "fname, fcode")
                ->edit_column("tname", "$1 ($2)", "tname, tcode")
                ->unset_column('fcode')
                ->unset_column('tcode');
            echo $this->datatables->generate();
        }
    }

    function getReturnsReport($pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }

        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('return_sales') . ".date as date, " . $this->db->dbprefix('return_sales') . ".reference_no as ref, " . $this->db->dbprefix('sales') . ".reference_no as sal_ref, " . $this->db->dbprefix('return_sales') . ".biller, " . $this->db->dbprefix('return_sales') . ".customer, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('return_items') . ".product_name, ' (', " . $this->db->dbprefix('return_items') . ".quantity, ')') SEPARATOR '<br>') as iname, " . $this->db->dbprefix('return_sales') . ".surcharge, " . $this->db->dbprefix('return_sales') . ".grand_total, " . $this->db->dbprefix('return_sales') . ".id as id", FALSE)
                ->join('sales', 'sales.id=return_sales.sale_id', 'left')
                ->from('return_sales')
                ->join('return_items', 'return_items.return_id=return_sales.id', 'left')
                ->group_by('return_sales.id')->order_by('return_sales.date desc');
            if ($product) {
                $this->db->like($this->db->dbprefix('return_items') . ".product_id", $product);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_return_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_ref'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sal_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->surcharge);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->grand_total);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $filename = 'sales_return_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('return_sales') . ".date as date, " . $this->db->dbprefix('return_sales') . ".reference_no as ref, " . $this->db->dbprefix('sales') . ".reference_no as sal_ref, " . $this->db->dbprefix('return_sales') . ".biller, " . $this->db->dbprefix('return_sales') . ".customer, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('return_items') . ".product_name, '__', " . $this->db->dbprefix('return_items') . ".quantity) SEPARATOR '___') as iname, " . $this->db->dbprefix('return_sales') . ".surcharge, " . $this->db->dbprefix('return_sales') . ".grand_total, " . $this->db->dbprefix('return_sales') . ".id as id", FALSE)
                ->join('sales', 'sales.id=return_sales.sale_id', 'left')
                ->from('return_sales')
                ->join('return_items', 'return_items.return_id=return_sales.id', 'left')
                ->group_by('return_sales.id')
				->where('sales.biller_id');
            if ($product) {
                $this->datatables->like($this->db->dbprefix('return_items') . ".product_id", $product);
            }
            echo $this->datatables->generate();
        }
    }
	
	function getReturnsProduct($pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
		if ($this->input->get('product_type')) {
            $product_type = $this->input->get('product_type');
        } else {
            $product_type = NULL;
        }
        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('return_sales') . ".date as date, " . $this->db->dbprefix('return_sales') . ".reference_no as ref, " . $this->db->dbprefix('sales') . ".reference_no as sal_ref, " . $this->db->dbprefix('return_sales') . ".biller, " . $this->db->dbprefix('return_sales') . ".customer, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('return_items') . ".product_name, ' (', " . $this->db->dbprefix('return_items') . ".quantity, ')') SEPARATOR '<br>') as iname, " . $this->db->dbprefix('return_sales') . ".surcharge, " . $this->db->dbprefix('return_sales') . ".grand_total, " . $this->db->dbprefix('return_sales') . ".id as id", FALSE)
                ->join('sales', 'sales.id=return_sales.sale_id', 'left')
                ->from('return_sales')
                ->join('return_items', 'return_items.return_id=return_sales.id', 'left')
                ->group_by('return_sales.id')->order_by('return_sales.date desc');
            if ($product) {
                $this->db->like($this->db->dbprefix('return_items') . ".product_id", $product);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_return_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_ref'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sal_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->surcharge);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->grand_total);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $filename = 'sales_return_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
			
			if($product_type == 'combo' || $product_type == 'service'){
				$this->datatables
                ->select($this->db->dbprefix('return_sales') . ".date as date, 
						" . $this->db->dbprefix('return_sales') . ".reference_no as ref, 
						" . $this->db->dbprefix('sales') . ".reference_no as sal_ref, 
						" . $this->db->dbprefix('return_sales') . ".biller, 
						" . $this->db->dbprefix('return_sales') . ".customer, 
						return_items.quantity,
						" . $this->db->dbprefix('return_sales') . ".surcharge, 
						" . $this->db->dbprefix('return_sales') . ".grand_total, 
						" . $this->db->dbprefix('return_sales') . ".id as id", FALSE)
                ->from('return_sales')
                ->join('sales', 'sales.id=return_sales.sale_id', 'left')
                ->join('return_items', 'return_items.return_id=return_sales.id', 'left')
                ->where('return_items.product_id', $product)
                ->group_by('return_sales.id');
			}else{
				$this->datatables
                ->select($this->db->dbprefix('return_sales') . ".date as date, 
						" . $this->db->dbprefix('return_sales') . ".reference_no as ref, 
						" . $this->db->dbprefix('sales') . ".reference_no as sal_ref, 
						" . $this->db->dbprefix('return_sales') . ".biller, 
						" . $this->db->dbprefix('return_sales') . ".customer, 
						purchase_items.quantity_balance,
						" . $this->db->dbprefix('return_sales') . ".surcharge, 
						" . $this->db->dbprefix('return_sales') . ".grand_total, 
						" . $this->db->dbprefix('return_sales') . ".id as id", FALSE)
                ->from('return_sales')
                ->join('sales', 'sales.id=return_sales.sale_id', 'left')
                ->join('purchase_items', 'purchase_items.sale_id = return_sales.sale_id', 'left')
                ->join('return_items', 'return_items.return_id=return_sales.id', 'left')
				->where(array('purchase_items.product_id' => $product, 'purchase_items.transaction_type' => 'sale_return'))
                ->group_by('return_sales.id');
			}
            echo $this->datatables->generate();
        }
    }
	
	function getDepositsReport($pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }

        if ($pdf || $xls) {
            $this->db
                ->select("deposits.id as id, date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", FALSE)
                ->from('deposits')
                ->join('users', 'users.id=deposits.created_by', 'inner')
				->where('deposits.company_id', $customer);
            if ($customer) {
                $this->db->like($this->db->dbprefix('deposits') . ".company_id", $customer);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_return_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_ref'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sal_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->surcharge);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->grand_total);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $filename = 'sales_return_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {
			$this->load->library('datatables');
			$this->datatables
				->select("deposits.id as id, date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", false)
				->from("deposits")
				->join('users', 'users.id=deposits.created_by', 'inner')
				->where('deposits.company_id', $customer)
				->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("deposit_note") . "' href='" . site_url('customers/deposit_note/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-file-text-o\"></i></a> <a class=\"tip\" title='" . lang("edit_deposit") . "' href='" . site_url('customers/edit_deposit/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_deposit") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete_deposit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
			->unset_column('id');

            echo $this->datatables->generate();
        }
    }
	
	function getProductsReports()
	{
		if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
		if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
		$this->load->library('datatables');
		$this->datatables
			->select("sales.id as id, products.image, products.code, products.name as pname, categories.name as cname, (erp_sale_items.quantity * erp_products.cost) as total_cost, (erp_sale_items.quantity * erp_products.price) as total_price, sale_items.quantity", false)
			->from("sales")
			->join('sale_items', 'sales.id=sale_items.sale_id', 'inner')
			->join('products', 'products.id=sale_items.product_id')
			->join('categories', 'products.category_id=categories.id')
			->where('sales.customer_id', $customer);

		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}
		echo $this->datatables->generate();
		
	}

    function purchases($biller_id = NULL)
    {
        $this->erp->checkPermissions('purchases');
		
		$user = $this->site->getUser();
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			if($user->biller_id){
				$this->data['biller_id'] = $user->biller_id;
			}else{
				$this->data['biller_id'] = "";
			}
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
		
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('purchases_report')));
        $meta = array('page_title' => lang('purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/purchases', $meta, $this->data);
    }

    function getPurchasesReport($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('purchases', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
		
		if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
		
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db
                ->select("" . $this->db->dbprefix('purchases') . ".id, date, reference_no, " . $this->db->dbprefix('warehouses') . ".name as wname, supplier,purchase_items.name AS supplier_name , (SELECT GROUP_CONCAT(pi.product_name SEPARATOR '\n') FROM " . $this->db->dbprefix('purchase_items') . " pi WHERE pi.purchase_id = " . $this->db->dbprefix('purchase_items') . ".purchase_id) AS iname,  GROUP_CONCAT(ROUND(" . $this->db->dbprefix('purchase_items') . ".quantity) SEPARATOR '\n') as iqty, grand_total, paid, " . $this->db->dbprefix('purchases') . ".status", FALSE)
                ->from('purchases')
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
				->join('companies', 'companies.id = purchase_items.supplier_id', 'left')
                ->group_by('purchases.id')
                ->order_by('purchases.date desc');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('purchases.created_by', $user);
				}
			}
			if ($biller_id) {
                $this->db->where('purchases.biller_id', $biller_id);
            }
            if ($product) {
                $this->db->like('purchase_items.product_id', $product);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->db->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('purchase_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('qty'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->wname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->supplier);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->iqty);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->status);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("G" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'purchase_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
					$this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->applyFromArray(
					 array(
						 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
					 ));
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
					$this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->applyFromArray(
					 array(
						 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
						 'wrap'       => true
					 ));
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        }else{
			
            $this->load->library('datatables'); 
			$this->datatables
			->select($this->db->dbprefix('purchases') . ".id, " . $this->db->dbprefix('purchases') . ".date, reference_no, " . $this->db->dbprefix('warehouses') . ".name as wname, supplier,CONCAT(GROUP_CONCAT(".$this->db->dbprefix('purchase_items').".product_code ,'[',ROUND(". $this->db->dbprefix('purchase_items') . ".quantity),'] ___',".$this->db->dbprefix('purchase_items').".serial_no  SEPARATOR '<br>')) as iname, grand_total, paid, (grand_total-paid) as balance, " . $this->db->dbprefix('purchases') . ".status", FALSE)
			->from('purchases')
			->join('purchase_items', 'purchase_items.purchase_id=purchases.id','left')
			->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
			->join('companies', 'companies.id = purchase_items.supplier_id', 'left')
			->group_by('purchases.id');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('purchases.created_by', $user);
				}
			}
			if ($biller_id) {
                $this->datatables->where('purchases.biller_id', $biller_id);
            }
            if ($product) {
                $this->datatables->like('purchase_items.product_id', $product);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->datatables->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }
	
    function getProductPurchasesReport(){
        $this->erp->checkPermissions('purchases', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        
        if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
        
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        $this->load->library('datatables');

        $this->datatables
        ->select($this->db->dbprefix('purchases') . ".id, " . $this->db->dbprefix('purchases') . ".date, reference_no, " . $this->db->dbprefix('warehouses') . ".name as wname, supplier, GROUP_CONCAT(ROUND(" . $this->db->dbprefix('purchase_items') . ".quantity) SEPARATOR '___') as iqty, grand_total, paid, (grand_total-paid) as balance, " . $this->db->dbprefix('purchases') . ".status", FALSE)
        ->from('purchases')
        ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
        ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
        ->join('companies', 'companies.id = purchase_items.supplier_id', 'left')
        ->group_by('purchases.id');
        
        if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
            if ($user) {
                $this->datatables->where('purchases.created_by', $user);
            }
        }
        if ($biller_id) {
            $this->datatables->where('purchases.biller_id', $biller_id);
        }
        if ($product) {
            $this->datatables->where('purchase_items.product_id', $product);
        }
        if ($supplier) {
            $this->datatables->where('purchases.supplier_id', $supplier);
        }
        if ($warehouse) {
            $this->datatables->where('purchases.warehouse_id', $warehouse);
        }
        if ($reference_no) {
            $this->datatables->like('purchases.reference_no', $reference_no, 'both');
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }

        echo $this->datatables->generate();
    }

	function getSalesDiscountReport($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('sales', TRUE);
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('payment_ref')) {
            $payment_ref = $this->input->get('payment_ref');
        } else {
            $payment_ref = NULL;
        }
        if ($this->input->get('sale_ref')) {
            $sale_ref = $this->input->get('sale_ref');
        } else {
            $sale_ref = NULL;
        }

        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('payments') . ".id as idd, ". $this->db->dbprefix('sales') . ".suspend_note as noted," . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, " . $this->db->dbprefix('sales') . ".reference_no as sale_ref, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, " . $this->db->dbprefix('payments') . ".note, paid_by, amount, type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id')
                ->order_by('payments.date desc');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('payments.created_by', $user);
				}
			}
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($sale_ref) {
                $this->db->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($start_date) {
				$this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
			}

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('payments_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('payment_reference'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchase_reference'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->purchase_ref);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'payments_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables->select("sales.date, sale_items.product_code, sale_items.product_name, sales.customer, products.cost, sale_items.unit_price ,sale_items.quantity, sale_items.discount,
											(CASE WHEN erp_sale_items.discount = '100%' THEN 
												'free'
											ELSE
												erp_sales.payment_status
											END
											) AS s_status ")
							->from('sale_items')
							->join('sales', 'sales.id = sale_items.sale_id', 'left')
							->join('products', 'products.id = sale_items.product_id', 'left')
							->where('sale_items.discount <>', 0);
			
			
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->datatables->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($sale_ref) {
                $this->datatables->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }
	
	function sales_discount()
    {
        $this->erp->checkPermissions('sales', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('sales_discount_report')));
        $meta = array('page_title' => lang('sales_discount_report'), 'bc' => $bc);
        $this->page_construct('reports/sales_discount', $meta, $this->data);
    }

    function payments($biller_id = NULL)
    {
        
		$user = $this->site->getUser();
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			$this->data['biller_id'] = "";
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
		
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('payments_report')));
        $meta = array('page_title' => lang('payments_report'), 'bc' => $bc);
        $this->page_construct('reports/payments', $meta, $this->data);
    }

    function getPaymentsReport($pdf = NULL, $xls = NULL)
    {
        
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
		if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
		if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
        if ($this->input->get('payment_ref')) {
            $payment_ref = $this->input->get('payment_ref');
        } else {
            $payment_ref = NULL;
        }
        if ($this->input->get('sale_ref')) {
            $sale_ref = $this->input->get('sale_ref');
        } else {
            $sale_ref = NULL;
        }
        if ($this->input->get('purchase_ref')) {
            $purchase_ref = $this->input->get('purchase_ref');
        } else {
            $purchase_ref = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('payments') . ".id as idd, ". $this->db->dbprefix('sales') . ".suspend_note as noted," . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, " . $this->db->dbprefix('sales') . ".reference_no as sale_ref, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, " . $this->db->dbprefix('payments') . ".note, paid_by, amount, type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id')
                ->order_by('payments.date desc');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('payments.created_by', $user);
				}
			}
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
			if ($biller_id) {
                $this->db->where('payments.biller_id', $biller_id);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($biller_id) {
                $this->db->where('payments.biller_id', $biller_id);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($sale_ref) {
                $this->db->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->db->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
				$this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
			}

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('payments_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('payment_reference'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchase_reference'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->purchase_ref);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'payments_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('payments') . ".id as idd, ". $this->db->dbprefix('payments'). ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, " . $this->db->dbprefix('sales') . ".reference_no as sale_ref, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, 
				(
					CASE 
						WHEN " . $this->db->dbprefix('payments') . ".note = ' ' THEN 
								".$this->db->dbprefix('sales') . ".suspend_note 
						WHEN " . $this->db->dbprefix('sales') . ".suspend_note != ''  THEN 
								CONCAT(".$this->db->dbprefix('sales') . ".suspend_note, ' - ',  " . $this->db->dbprefix('payments') . ".note) 
					ELSE " . $this->db->dbprefix('payments') . ".note END
				) as note,
				,paid_by,amount, type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('payments.created_by', $user);
				}
			}
			if ($biller_id) {
                $this->datatables->where('payments.biller_id', $biller_id);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($biller_id) {
                $this->datatables->where('payments.biller_id', $biller_id);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->datatables->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($sale_ref) {
                $this->datatables->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->datatables->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }
	
	function getPaymentsReportStaff($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('payments', TRUE);
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('payment_ref')) {
            $payment_ref = $this->input->get('payment_ref');
        } else {
            $payment_ref = NULL;
        }
        if ($this->input->get('sale_ref')) {
            $sale_ref = $this->input->get('sale_ref');
        } else {
            $sale_ref = NULL;
        }
        if ($this->input->get('purchase_ref')) {
            $purchase_ref = $this->input->get('purchase_ref');
        } else {
            $purchase_ref = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {

            $this->db
                ->select("" . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, " . $this->db->dbprefix('sales') . ".reference_no as sale_ref, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, " . $this->db->dbprefix('payments') . ".note, paid_by, amount, type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id')
                ->order_by('payments.date desc');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('payments.created_by', $user);
				}
			}
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($sale_ref) {
                $this->db->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->db->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
				$this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
			}

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('payments_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('payment_reference'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchase_reference'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->purchase_ref);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'payments_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, " . $this->db->dbprefix('sales') . ".reference_no as sale_ref, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, " . $this->db->dbprefix('payments') . ".note,paid_by,amount, type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('payments.created_by', $user);
				}
			}
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->datatables->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($sale_ref) {
                $this->datatables->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->datatables->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }
	
    function customers()
    {
        $this->erp->checkPermissions('sales', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('customers_report')));
        $meta = array('page_title' => lang('customers_report'), 'bc' => $bc);
        $this->page_construct('reports/customers', $meta, $this->data);
    }

    function getCustomers($pdf = NULL, $xls = NULL)
    {
        //$this->erp->checkPermissions('customers', TRUE);
		$this->erp->checkPermissions('sales', TRUE);
        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('companies') . ".id as idd, company, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
                ->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('customers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_sales'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->erp->formatNumber($data_row->total));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->erp->formatMoney($data_row->total_amount));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->erp->formatMoney($data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->erp->formatMoney($data_row->balance));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'customers_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as idd, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
                ->group_by('companies.id')
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/customer_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "idd")
                ->unset_column('id');
            echo $this->datatables->generate();

        }

    }
	
	function getSellers($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('customers', TRUE);

        if ($pdf || $xls) {
            $this->db
               ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
				->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('customers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_sales'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->erp->formatNumber($data_row->total));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->erp->formatMoney($data_row->total_amount));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->erp->formatMoney($data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->erp->formatMoney($data_row->balance));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'customers_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
                ->group_by('companies.id')
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/customer_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
                ->unset_column('id');
            echo $this->datatables->generate();

        }

    }

    function customer_report($user_id = NULL)
    {
        $this->erp->checkPermissions('sales', TRUE, 'reports');
        if (!$user_id && $_GET['d'] == null) {
            $this->session->set_flashdata('error', lang("no_customer_selected"));
            redirect('reports/customers');
        }

        $this->data['sales'] = $this->reports_model->getSalesTotals($user_id);
        $this->data['total_sales'] = $this->reports_model->getCustomerSales($user_id);
        $this->data['total_quotes'] = $this->reports_model->getCustomerQuotes($user_id);
        $this->data['total_returns'] = $this->reports_model->getCustomerReturns($user_id);
		$this->data['total_deposits'] = $this->reports_model->getCustomerDeposits($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
			
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
		
		$this->data['date'] = $date;
		
        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('customers_report')));
        $meta = array('page_title' => lang('customers_report'), 'bc' => $bc);
        $this->page_construct('reports/customer_report', $meta, $this->data);

    }
	
	function biller_report($user_id = NULL)
    {
        $this->erp->checkPermissions('customers', TRUE);
        if (!$user_id && $_GET['d'] == null) {
            $this->session->set_flashdata('error', lang("no_customer_selected"));
            redirect('reports/customers');
        }

        $this->data['sales'] = $this->reports_model->getSalesTotals($user_id);
        $this->data['total_sales'] = $this->reports_model->getCustomerSales($user_id);
        $this->data['total_quotes'] = $this->reports_model->getCustomerQuotes($user_id);
        $this->data['total_returns'] = $this->reports_model->getCustomerReturns($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
			
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
		
		$this->data['date'] = $date;
		
        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('customers_report')));
        $meta = array('page_title' => lang('customers_report'), 'bc' => $bc);
        $this->page_construct('reports/biller_report', $meta, $this->data);

    }

    function suppliers()
    {
        $this->erp->checkPermissions('purchases', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('suppliers_report')));
        $meta = array('page_title' => lang('suppliers_report'), 'bc' => $bc);
        $this->page_construct('reports/suppliers', $meta, $this->data);
    }

    function getSuppliers($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('purchases', TRUE);
		//$this->erp->checkPermissions('suppliers');
        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('companies') . ".id as id, company, name,email, count(erp_purchases.id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('purchases', 'purchases.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('suppliers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_purchases'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->erp->formatNumber($data_row->total));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->erp->formatMoney($data_row->total_amount));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->erp->formatMoney($data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->erp->formatMoney($data_row->balance));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'suppliers_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as idd, company, name,email, count(" . $this->db->dbprefix('purchases') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('purchases', 'purchases.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->group_by('companies.id')
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/supplier_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "idd")
                ->unset_column('id');
            echo $this->datatables->generate();

        }

    }

    function supplier_report($user_id = NULL, $biller_id = NULL)
    {
        $this->erp->checkPermissions('suppliers', TRUE);

        if (!$user_id && $_GET['d'] == null) {
            $this->session->set_flashdata('error', lang("no_supplier_selected"));
            redirect('reports/suppliers');
        }
		
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			$this->data['biller_id'] = "";
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}

        $this->data['purchases'] = $this->reports_model->getPurchasesTotals($user_id);
        $this->data['total_purchases'] = $this->reports_model->getSupplierPurchases($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('suppliers_report')));
        $meta = array('page_title' => lang('suppliers_report'), 'bc' => $bc);
        $this->page_construct('reports/supplier_report', $meta, $this->data);

    }
    
    function getSupplierByItems($pdf = NULL, $xls = NULL)
    {
        //$this->erp->checkPermissions('suppliers', TRUE);
		$this->erp->checkPermissions('products', TRUE);
        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count(purchases.id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('purchases', 'purchases.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('suppliers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_purchases'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->erp->formatNumber($data_row->total));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->erp->formatMoney($data_row->total_amount));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->erp->formatMoney($data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->erp->formatMoney($data_row->balance));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'suppliers_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as idd, company, name, phone, email, count(" . $this->db->dbprefix('purchases') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('purchases', 'purchases.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->group_by('companies.id')
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/supplier_by_items_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "idd")
                ->unset_column('id');
            echo $this->datatables->generate();

        }

    }

    function supplier_by_items_report($user_id = NULL, $biller_id = NULL)
    {
        $this->erp->checkPermissions('suppliers', TRUE);

        if (!$user_id && $_GET['d'] == null) {
            $this->session->set_flashdata('error', lang("no_supplier_selected"));
            redirect('reports/suppliers');
        }
		
		$user = $this->site->getUser();
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			if($user->biller_id){
				$this->data['biller_id'] = $user->biller_id;
			}else{
				$this->data['biller_id'] = "";
			}
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
		
        $this->data['purchases'] = $this->reports_model->getPurchasesTotals($user_id);
        $this->data['total_purchases'] = $this->reports_model->getSupplierPurchases($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('supplier_by_items_report')));
        $meta = array('page_title' => lang('suppliers_by_items_report'), 'bc' => $bc);
        $this->page_construct('reports/supplier_by_items_report', $meta, $this->data);

    }
    
    function supplier_by_items()
    {
        $this->erp->checkPermissions('products', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('supplier_by_items_report')));
        $meta = array('page_title' => lang('supplier_by_items_report'), 'bc' => $bc);
        $this->page_construct('reports/supplier_by_items', $meta, $this->data);
    }

    function users()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('staff_report')));
        $meta = array('page_title' => lang('staff_report'), 'bc' => $bc);
        $this->page_construct('reports/users', $meta, $this->data);
    }

    function getUsers()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users').".id as id, first_name, last_name, email, company, ".$this->db->dbprefix('groups').".name, active")
            ->from("users")
            ->join('groups', 'users.group_id=groups.id', 'left')
            ->group_by('users.id')
            ->where('company_id', NULL);
        if (!$this->Owner) {
            $this->datatables->where('group_id !=', 1);
        }
        $this->datatables
            ->edit_column('active', '$1__$2', 'active, id')
            ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/staff_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
            ->unset_column('id');
        echo $this->datatables->generate();
    }

    function staff_report($user_id = NULL, $year = NULL, $month = NULL, $pdf = NULL, $cal = 0)
    {
        if (!$user_id) {
            $this->session->set_flashdata('error', lang("no_user_selected"));
            redirect('reports/users');
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['purchases'] = $this->reports_model->getStaffPurchases($user_id);
        $this->data['sales'] = $this->reports_model->getStaffSaleman($user_id);
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        if (!$year) {
            $year = date('Y');
        }
        if (!$month || $month == '#monthly-con') {
            $month = date('m');
        }
        if ($pdf) {
            if ($cal) {
                $this->monthly_sales($year, $pdf, $user_id);
            } else {
                $this->daily_sales($year, $month, $pdf, $user_id);
            }
        }
        $config = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => site_url('reports/staff_report/'.$user_id),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable">{/table_open}
		{heading_row_start}<tr>{/heading_row_start}
		{heading_previous_cell}<th class="text-center"><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
		{heading_title_cell}<th class="text-center" colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
		{heading_next_cell}<th class="text-center"><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
		{heading_row_end}</tr>{/heading_row_end}
		{week_row_start}<tr>{/week_row_start}
		{week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
		{week_row_end}</tr>{/week_row_end}
		{cal_row_start}<tr class="days">{/cal_row_start}
		{cal_cell_start}<td class="day">{/cal_cell_start}
		{cal_cell_content}
		<div class="day_num">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content}
		{cal_cell_content_today}
		<div class="day_num highlight">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content_today}
		{cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
		{cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
		{cal_cell_blank}&nbsp;{/cal_cell_blank}
		{cal_cell_end}</td>{/cal_cell_end}
		{cal_row_end}</tr>{/cal_row_end}
		{table_close}</table>{/table_close}';

        $this->load->library('calendar', $config);
        $sales = $this->reports_model->getStaffDailySaleman($user_id, $year, $month);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $daily_sale[$sale->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->erp->formatMoney($sale->discount) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->erp->formatMoney($sale->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->erp->formatMoney($sale->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->erp->formatMoney($sale->total) . "</td></tr><tr><td>" . lang("award_points") . "</td><td>" . intval($sale->total / $this->Settings->each_sale) . "</td></tr></table>";
            }
        } else {
            $daily_sale = array();
        }
        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        if ($this->input->get('pdf')) {

        }
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        $this->data['msales'] = $this->reports_model->getStaffMonthlySaleman($user_id, $year);
        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('staff_report')));
        $meta = array('page_title' => lang('staff_report'), 'bc' => $bc);
        $this->page_construct('reports/staff_report', $meta, $this->data);

    }

    function getUserLogins($id = NULL, $pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('login_start_date')) {
            $login_start_date = $this->input->get('login_start_date');
        } else {
            $login_start_date = NULL;
        }
        if ($this->input->get('login_end_date')) {
            $login_end_date = $this->input->get('login_end_date');
        } else {
            $login_end_date = NULL;
        }
        if ($login_start_date) {
            $login_start_date = $this->erp->fld($login_start_date);
            $login_end_date = $login_end_date ? $this->erp->fld($login_end_date) : date('Y-m-d H:i:s');
        }
        if ($pdf || $xls) {

            $this->db
                ->select("login, ip_address, time")
                ->from("user_logins")
                ->where('user_id', $id)
                ->order_by('time desc');
            if ($login_start_date) {
                $this->datatables->where('time BETWEEN "' . $login_start_date . '" and "' . $login_end_date . '"', FALSE);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('staff_login_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('ip_address'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('time'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->login);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->ip_address);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->erp->hrld($data_row->time));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(35);

                $filename = 'staff_login_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("login, ip_address, time")
                ->from("user_logins")
                ->where('user_id', $id);
            if ($login_start_date) {
                $this->datatables->where('time BETWEEN "' . $login_start_date . '" and "' . $login_end_date . '"', FALSE);
            }
            echo $this->datatables->generate();

        }

    }
	
	function email_receipt($sale_id = NULL)
    {
        $this->erp->checkPermissions('index');
        if ($this->input->post('id')) {
            $sale_id = $this->input->post('id');
        } else {
            die();
        }
        if ($this->input->post('email')) {
            $to = $this->input->post('email');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');

        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);

        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['barcode'] = $this->barcode($inv->reference_no, 'code39', 30);
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['page_title'] = $this->lang->line("invoice");

        if (!$to) {
            $to = $this->data['customer']->email;
        }
        if (!$to) {
            echo json_encode(array('msg' => $this->lang->line("no_meil_provided")));
        }
        $receipt = $this->load->view($this->theme . 'pos/email_receipt', $this->data, TRUE);

        if ($this->erp->send_email($to, 'Receipt from ' . $this->data['biller']->company, $receipt)) {
            echo json_encode(array('msg' => $this->lang->line("email_sent")));
        } else {
            echo json_encode(array('msg' => $this->lang->line("email_failed")));
        }

    }
	
    function getCustomerLogins($id = NULL)
    {
        if ($this->input->get('login_start_date')) {
            $login_start_date = $this->input->get('login_start_date');
        } else {
            $login_start_date = NULL;
        }
        if ($this->input->get('login_end_date')) {
            $login_end_date = $this->input->get('login_end_date');
        } else {
            $login_end_date = NULL;
        }
        if ($login_start_date) {
            $login_start_date = $this->erp->fld($login_start_date);
            $login_end_date = $login_end_date ? $this->erp->fld($login_end_date) : date('Y-m-d H:i:s');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("login, ip_address, time")
            ->from("user_logins")
            ->where('customer_id', $id);
        if ($login_start_date) {
            $this->datatables->where('time BETWEEN "' . $login_start_date . '" and "' . $login_end_date . '"');
        }
        echo $this->datatables->generate();
    }

    function profit_loss($start_date = NULL, $end_date = NULL, $biller_id = NULL)
    {
        
        if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }
		if($biller_id != NULL && biller_id != ""){
			$this->data['biller_id'] = $biller_id;
		}else{
			$this->data['biller_id'] = "";
		}
		$this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->data['total_purchases'] = $this->reports_model->getTotalPurchases($start, $end, $biller_id);
        $this->data['total_sales'] = $this->reports_model->getTotalSales($start, $end, $biller_id);
        $this->data['total_expenses'] = $this->reports_model->getTotalExpenses($start, $end, $biller_id);
        $this->data['total_paid'] = $this->reports_model->getTotalPaidAmount($start, $end, $biller_id);
        $this->data['total_received'] = $this->reports_model->getTotalReceivedAmount($start, $end, $biller_id);
        $this->data['total_received_cash'] = $this->reports_model->getTotalReceivedCashAmount($start, $end, $biller_id);
        $this->data['total_received_cc'] = $this->reports_model->getTotalReceivedCCAmount($start, $end, $biller_id);
        $this->data['total_received_cheque'] = $this->reports_model->getTotalReceivedChequeAmount($start, $end, $biller_id);
        $this->data['total_received_ppp'] = $this->reports_model->getTotalReceivedPPPAmount($start, $end, $biller_id);
        $this->data['total_received_stripe'] = $this->reports_model->getTotalReceivedStripeAmount($start, $end, $biller_id);
        $this->data['total_returned'] = $this->reports_model->getTotalReturnedAmount($start, $end, $biller_id);
		$this->data['total_costs'] = $this->reports_model->getTotalCosts($start, $end);
        $this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('profit_loss')));
        $meta = array('page_title' => lang('profit_loss'), 'bc' => $bc);
        $this->page_construct('reports/profit_loss', $meta, $this->data);
    }

    function profit_loss_pdf($start_date = NULL, $end_date = NULL)
    {
        $this->erp->checkPermissions('profit_loss');
        if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }

        $this->data['total_purchases'] = $this->reports_model->getTotalPurchases($start, $end);
        $this->data['total_sales'] = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_expenses'] = $this->reports_model->getTotalExpenses($start, $end);
        $this->data['total_paid'] = $this->reports_model->getTotalPaidAmount($start, $end);
        $this->data['total_received'] = $this->reports_model->getTotalReceivedAmount($start, $end);
        $this->data['total_received_cash'] = $this->reports_model->getTotalReceivedCashAmount($start, $end);
        $this->data['total_received_cc'] = $this->reports_model->getTotalReceivedCCAmount($start, $end);
        $this->data['total_received_cheque'] = $this->reports_model->getTotalReceivedChequeAmount($start, $end);
        $this->data['total_received_ppp'] = $this->reports_model->getTotalReceivedPPPAmount($start, $end);
        $this->data['total_received_stripe'] = $this->reports_model->getTotalReceivedStripeAmount($start, $end);
        $this->data['total_returned'] = $this->reports_model->getTotalReturnedAmount($start, $end);
		$this->data['total_costs'] = $this->reports_model->getTotalCosts($start, $end);
        $this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);

        $html = $this->load->view($this->theme . 'reports/profit_loss_pdf', $this->data, true);
        $name = lang("profit_loss") . "-" . str_replace(array('-', ' ', ':'), '_', $this->data['start']) . "-" . str_replace(array('-', ' ', ':'), '_', $this->data['end']) . ".pdf";
        $this->erp->generate_pdf($html, $name, false, false, false, false, false, 'L');
    }

    function register()
    {
        //$this->erp->checkPermissions('products', TRUE);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('register_report')));
        $meta = array('page_title' => lang('register_report'), 'bc' => $bc);
        $this->page_construct('reports/register', $meta, $this->data);
    }

    function getRrgisterlogs($pdf = NULL, $xls = NULL)
    {
        //$this->erp->checkPermissions('products', TRUE);
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }

        if ($pdf || $xls) {

            $this->db
                ->select("date, closed_at, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, ' (', users.email, ')') as user, cash_in_hand, total_cc_slips, total_cheques, total_cash, total_cc_slips_submitted, total_cheques_submitted,total_cash_submitted, note", FALSE)
                ->from("pos_register")
                ->join('users', 'users.id=pos_register.user_id', 'left')
                ->order_by('date desc');
            //->where('status', 'close');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('pos_register.user_id', $user);
				}
			}
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('register_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('open_time'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('close_time'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('user'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('cash_in_hand'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('cc_slips'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('cheques'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('total_cash'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('cc_slips_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('cheques_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('total_cash_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('note'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->closed_at);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->user);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->cash_in_hand);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->total_cc_slips);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->total_cheques);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->total_cash);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->total_cc_slips_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->total_cheques_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->total_cash_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->note);
                    if($data_row->total_cash_submitted < $data_row->total_cash || $data_row->total_cheques_submitted < $data_row->total_cheques || $data_row->total_cc_slips_submitted < $data_row->total_cc_slips) {
                        $this->excel->getActiveSheet()->getStyle('A'.$row.':K'.$row)->applyFromArray(
                                array( 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'F2DEDE')) )
                                );
                    }
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(35);
                $filename = 'register_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    //$this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("erp_pos_register.id as idd, date, closed_at, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, '<br>', " . $this->db->dbprefix('users') . ".email) as user, cash_in_hand, CONCAT(total_cc_slips, ' (', total_cc_slips_submitted, ')'), CONCAT(total_cheques, ' (', total_cheques_submitted, ')'), CONCAT(total_cash, ' (', total_cash_submitted, ')'), note", FALSE)
                ->from("pos_register")
                ->join('users', 'users.id=pos_register.user_id', 'left');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('pos_register.user_id', $user);
				}
			}
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }
	
	public function view_sale_detail($product_code = NULL)
    {		
        $this->erp->checkPermissions(false, true);
        $this->data['sale_details'] = $this->reports_model->getSaleDetail($product_code);	
		
        $this->load->view($this->theme . 'reports/view_sale_detail', $this->data);
		 //$this->page_construct('reports/index', $meta, $this->data);
    }
	
	public function view_purchase_detail($product_code = NULL, $supplier_id = NULL)
    {		
        $this->erp->checkPermissions(false, true);
		if($supplier_id){
			$this->data['purchase_details'] = $this->reports_model->getPurchaseDetailSupplier($product_code, $supplier_id);	
		}else{
			$this->data['purchase_details'] = $this->reports_model->getPurchaseDetail($product_code);	
		}

        $this->load->view($this->theme . 'reports/view_purchase_detail', $this->data);
		 //$this->page_construct('reports/index', $meta, $this->data);
    }
	
	public function view_sale_detail_in_out($product_code = NULL)
    {		
        $this->erp->checkPermissions(false, true);
        $this->data['sale_details'] = $this->reports_model->getSaleDetail($product_code);	
		
        $this->load->view($this->theme . 'reports/view_sale_detail_in_out', $this->data);
		 //$this->page_construct('reports/index', $meta, $this->data);
    }
	
	public function view_purchase_detail_in_out($product_code = NULL)
    {		
        $this->erp->checkPermissions(false, true);
        $this->data['purchase_details'] = $this->reports_model->getPurchaseDetail($product_code);	
		
        $this->load->view($this->theme . 'reports/view_purchase_detail_in_out', $this->data);
		 //$this->page_construct('reports/index', $meta, $this->data);
    }
	
	// function update 2016-01-16
	function view_profit_lost_purchase($start_date=null, $end_date=null)
    {
       $end_date_time=explode('_',$end_date);
	   $end_date=$end_date_time[0]." ".str_replace('-',':',$end_date_time[1]);
       $this->erp->checkPermissions(false, true);
	   
       $this->data['purchase_info']= $this->db
                ->select("id, date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status")
                ->from('purchases')
				->where('status', 'received')
				->where('date >=',$start_date)
				->where('date <=',$end_date)
				->get();
         $this->load->view($this->theme . 'reports/modal_profit_lost_purchase', $this->data);
    }
	
	public function view_profit_lost_sale($start_date=null,$end_date=null){
		$end_date_time=explode('_',$end_date);
		$end_date=$end_date_time[0]." ".str_replace('-',':',$end_date_time[1]);
		$this->erp->checkPermissions(false,true);
		$this->data['sale_info']=$this->db
				->select("id,date,reference_no,biller,customer,sale_status,grand_total,paid,(grand_total-paid) as balance,payment_status")
				->from('sales')
				->where('date >=',$start_date)
				->where('date <=',$end_date)
				->get();
		$this->load->view($this->theme .'reports/modal_profit_lost_sale',$this->data);
	}
	
	function view_expense($start_date=null,$end_date=null){
		$end_date_time=explode('_',$end_date);
		$end_date=$end_date_time[0]." ".str_replace('-',':',$end_date_time[1]);
		$this->erp->checkPermissions(false,true);
		$this->data['expense_info']=$this->db
				->select("id,date,reference,amount,note,created_by")
				->from("expenses")
				->where('date>=',$start_date)
				->where("date<=",$end_date)
				->get();
		$this->load->view($this->theme.'reports/modal_view_expense',$this->data);
	}
	
	public function view_profit_payment($start_date=null,$end_date=null){
		$end_date_time=explode('_',$end_date);
		$end_date=$end_date_time[0]." ".str_replace('-',':',$end_date_time[1]);
		$this->erp->checkPermissions(false,true);
		
		$this->data['payment_info']=$this->db	
				->select("payments.id as id,payments.date as date1,payments.reference_no as ref_no1,sales.reference_no as sale_ref1,purchases.reference_no as pur_ref1,payments.paid_by as paid_by1,payments.amount as amount1,payments.type as type1")
				->from('payments')
				->join('sales','sales.id=payments.id','left')
				->join('purchases','purchases.id=payments.id','left')
				->where('payments.date >= ',$start_date)
				->where('payments.date <=',$end_date)
				->get();
				
		$this->load->view($this->theme.'reports/modal_view_payment',$this->data);
	}
	
	public function view_profit_payments_received($start_date=null,$end_date=null){
		$end_date_time=explode('_',$end_date);
		$end_date=$end_date_time[0]." ".str_replace('-',':',$end_date_time[1]);
		$this->erp->checkPermissions(false,true);
		$this->data['payment_received_info']=$this->db	
				->select("payments.id as id,payments.date as date,payments.reference_no as ref_no,sales.reference_no as sale_ref,purchases.reference_no as pur_ref,payments.paid_by as paid_by,payments.amount as amount,payments.type as type")
				->from('payments')
				->join('sales','sales.id=payments.id','left')
				->join('purchases','purchases.id=payments.id','left')
				->where('payments.date >= ',$start_date)
				->where('payments.date <=',$end_date)
				->where('payments.type=','received')
				->get();
		$this->load->view($this->theme.'reports/modal_view_payment_received',$this->data);
	}
	
	public function view_profit_payment_sent($start_date=null,$end_date=null){
		$end_date_time=explode('_',$end_date);
		$end_date=$end_date_time[0]." ".str_replace('-',':',$end_date_time[1]);
		$this->erp->checkPermissions(false,true);
		$this->data['payment_sent_info']=$this->db
				->select("payments.id as id,payments.date as date,payments.reference_no as ref_no,sales.reference_no as sale_ref,purchases.reference_no as pur_ref,payments.paid_by as paid_by,payments.amount as amount,payments.type as type")
				->from('payments')
				->join('sales','sales.id=payments.id','left')
				->join('purchases','purchases.id=payments.id','left')
				->where('payments.date >=',$start_date)
				->where('payments.date <=',$end_date)
				->where('payments.type=','sent')
				->get();
		$this->load->view($this->theme.'reports/modal_view_payment_sent',$this->data);
	}
	
	function view_profit_payment_return($start_date=null,$end_date=null){
		$end_date_time=explode('_',$end_date);
		$end_date=$end_date_time[0]." ".str_replace('-',':',$end_date_time[1]);
		$this->erp->checkPermissions(false,true);
		$this->data['payment_returned_info']=$this->db
				->select("payments.id as id,payments.date as date,payments.reference_no as ref_no,sales.reference_no as sale_ref,purchases.reference_no as pur_ref,payments.paid_by as paid_by,payments.amount as amount,payments.type as type")
				->from("payments")
				->join('sales','sales.id=payments.id','left')
				->join('purchases','purchases.id=payments.id','left')
				->where('payments.date >=',$start_date)
				->where('payments.date <=',$end_date)
				->where('payments.type=','returned')
				->get();
		$this->load->view($this->theme.'reports/modal_view_payment_return',$this->data);
	}
	
	function billReceipt()
    {
        $this->erp->checkPermissions('payments');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('payments_report')));
        $meta = array('page_title' => lang('payments_report'), 'bc' => $bc);
        $this->page_construct('reports/bill_reciept', $meta, $this->data);
    }
	
	function getBillReciept($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('payments', TRUE);
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('payment_ref')) {
            $payment_ref = $this->input->get('payment_ref');
        } else {
            $payment_ref = NULL;
        }
        if ($this->input->get('sale_ref')) {
            $sale_ref = $this->input->get('sale_ref');
        } else {
            $sale_ref = NULL;
        }
        if ($this->input->get('purchase_ref')) {
            $purchase_ref = $this->input->get('purchase_ref');
        } else {
            $purchase_ref = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fsd($start_date);
            $end_date = $this->erp->fsd($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {

            $this->db
                ->select("" . $this->db->dbprefix('payments') . ".date, 
				" . $this->db->dbprefix('payments') . ".reference_no as payment_ref, 
				" . $this->db->dbprefix('sales') . ".reference_no as sale_ref,customer,paid_by, amount, type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id')
                ->order_by('payments.date asc');
				$this->db->where('payments.type != "sent"');
			//	$this->db->where('sales.customer !=""');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('payments.created_by', $user);
				}
			}
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($sale_ref) {
                $this->db->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->db->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('payments_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('payment_reference'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'payments_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('payments') . ".id,
				" . $this->db->dbprefix('sales') . ".suspend_note AS noted,
				" . $this->db->dbprefix('payments') . ".date AS date,
				" . $this->db->dbprefix('payments') . ".reference_no as payment_ref, 
				" . $this->db->dbprefix('sales') . ".reference_no as sale_ref, customer,paid_by, amount, type", $this->db->dbprefix('payments') . ".id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id')
				->order_by('payments.date desc');
				$this->db->where('payments.type != "sent"');
				$this->db->where('sales.customer !=""');
            
            if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
                if (isset($user)) {
                    $this->datatables->where('payments.created_by', $user);
                }
            }
            if (isset($customer)) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if (isset($supplier)) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if (isset($biller)) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if (isset($customer)) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if (isset($payment_ref)) {
                $this->datatables->like('payments.reference_no', $payment_ref, 'both');
            }
            if (isset($sale_ref)) {
                $this->datatables->like('sales.reference_no', $sale_ref, 'both');
            }
			if (isset($customers)){
				$this->datatables->like('sales.customers',$customers,'both');
			}
            if (isset($purchase_ref)) {
                $this->datatables->like('payments.paid_bys', $purchase_ref, 'both');
            }
			if (isset($grand_total)) {
                $this->datatables->like('sales.grand_total', $grand_total, 'both');
            }
            if (isset($start_date)) {
                $this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
           

            echo $this->datatables->generate();

        }

    }
	
	function billPayable()
    {
        $this->erp->checkPermissions('payments');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Bill Payable Report')));
        $meta = array('page_title' => lang('payments_report'), 'bc' => $bc);
        $this->page_construct('reports/bill_payable', $meta, $this->data);
    }
	
	function getBillPaymentReport($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('payments', TRUE);
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('payment_ref')) {
            $payment_ref = $this->input->get('payment_ref');
        } else {
            $payment_ref = NULL;
        }
        if ($this->input->get('sale_ref')) {
            $sale_ref = $this->input->get('sale_ref');
        } else {
            $sale_ref = NULL;
        }
        if ($this->input->get('purchase_ref')) {
            $purchase_ref = $this->input->get('purchase_ref');
        } else {
            $purchase_ref = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fsd($start_date);
            $end_date = $this->erp->fsd($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('purchases') . ".date, 
				" . $this->db->dbprefix('purchases') . ".reference_no,
				" . $this->db->dbprefix('purchases') . ".supplier as purchase_ref,
				" . $this->db->dbprefix('payments') . ".paid_by,
				" . $this->db->dbprefix('purchases') . ".paid,
				" . $this->db->dbprefix('purchases') . ".grand_total,
				" . $this->db->dbprefix('purchases') . ".payment_status")
                ->from('purchases')
				->JOIN('payments','purchases.id=payments.purchase_id','left')
                ->group_by('purchases.id');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->db->where('payments.created_by', $user);
				}
			}
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($sale_ref) {
                $this->db->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->db->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('bill_payable'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('paid_by'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));

                $row = 2;
                $total = 0;
				$paid=0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->supplier);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, lang($data_row->paid_by));
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
					$paid+=$data_row->paid;
					$total+=$data_row->grand_total;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
				$this->excel->getActiveSheet()->setCellValue('E'.$row,$paid);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'payments_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('purchases') . ".date, 
				" . $this->db->dbprefix('purchases') . ".reference_no as payment_ref,
				" . $this->db->dbprefix('purchases') . ".supplier as purchase_ref,
				" . $this->db->dbprefix('payments') . ".paid_by,
				" . $this->db->dbprefix('purchases') . ".paid,
				" . $this->db->dbprefix('purchases') . ".grand_total,
				" . $this->db->dbprefix('purchases') . ".payment_status")
                ->from('purchases')
				->JOIN('payments','purchases.id=payments.purchase_id','left')
                ->group_by('purchases.id');
			
			if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
				if ($user) {
					$this->datatables->where('payments.created_by', $user);
				}
			}
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->datatables->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($sale_ref) {
                $this->datatables->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->datatables->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }
	
	function income_statement($start_date = NULL, $end_date = NULL, $pdf = NULL, $xls = NULL, $biller_id = NULL)
    {
        $this->erp->checkPermissions('account', true, 'reports');
		if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }
		$user = $this->site->getUser();
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			if($user->biller_id){
				$this->data['biller_id'] = $user->biller_id;
				$biller_id = $user->biller_id;
			}else{
				$this->data['biller_id'] = "";
			}
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
		
		$this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);
		
        $totalBeforeAyear = date('Y', strtotime($this->data['start'])) - 1;
		
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports/income_statement')));
        $meta = array('page_title' => lang('income_statement'), 'bc' => $bc);
		
		
		$from_date = date('Y-m-d H:m',strtotime(urldecode($start_date)));//'2014-08-01';
		$to_date = date('Y-m-d H:m',strtotime(urldecode($end_date. ' +1 day')));//'2015-09-01';
		
        $this->data['totalBeforeAyear'] = $totalBeforeAyear;
		$dataIncome = $this->accounts_model->getStatementByDate('40,70',$from_date,$to_date,$biller_id);
		$this->data['dataIncome'] = $dataIncome;
		
		$IncomeData = $this->accounts_model->getStatementByDate('40,70',$from_date,$to_date,$biller_id);
		$dataCost = $this->accounts_model->getStatementByDate('50',$from_date,$to_date,$biller_id);
		$this->data['dataCost'] = $dataCost;
		
		$dataExpense = $this->accounts_model->getStatementByDate('60,80,90',$from_date,$to_date,$biller_id);
		$this->data['dataExpense'] = $dataExpense;
		
		if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/income_statement', $this->data, true);
            $name = lang("income_statement") . "_" . date('Y_m_d_H_i_s') . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_income_text") . '</p>', '', $html);
            $this->erp->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
		
		if($xls){
			$styleArray = array(
				'font'  => array(
					'bold'  => true,
					'color' => array('rgb' => '000000'),
					'size'  => 10,
					'name'  => 'Verdana'
				)
			);
			$bold = array(
				'font' => array(
					'bold' => true
				)
			);
			$this->load->library('excel');
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($styleArray);
			$this->excel->getActiveSheet()->setTitle(lang('Income Statement'));
			$this->excel->getActiveSheet()->SetCellValue('A1', lang('account_name'));
			$this->excel->getActiveSheet()->SetCellValue('B1', lang('amount'));
			$this->excel->getActiveSheet()->SetCellValue('C1', lang('total'));
			//$this->excel->getActiveSheet()->SetCellValue('D1', lang("total") . ' ('.$totalBeforeAyear.')');
			
			$this->excel->getActiveSheet()->getStyle('A2:B2')->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A2:B2')->setCellValue('A2' , lang('income'));
			$this->excel->getActiveSheet()->mergeCells('C2:D2');
			$total_income = 0;
			$totalBeforeAyear_income = 0;
			$income = 3;
			foreach($dataIncome->result() as $row){
				$total_income += $row->amount;

				$query = $this->db->query("SELECT
					sum(erp_gl_trans.amount) AS amount
				FROM
					erp_gl_trans
				WHERE
					DATE(tran_date) = '$totalBeforeAyear' AND account_code = '" . $row->account_code . "';");
				$totalBeforeAyearRows = $query->row();
				$totalBeforeAyear_income += $totalBeforeAyearRows->amount;
				$this->excel->getActiveSheet()->SetCellValue('A' . $income, $row->account_code.' - '.$row->accountname);
				$this->excel->getActiveSheet()->SetCellValue('B' . $income, number_format(abs($row->amount),2));
				$this->excel->getActiveSheet()->SetCellValue('C' . $income, '');
				//$this->excel->getActiveSheet()->SetCellValue('D' . $income, number_format(abs($totalBeforeAyearRows->amount),2));
				$income++;
			}
			
			$this->excel->getActiveSheet()->getStyle('A3:A'.($income-1))->getAlignment()->setIndent(2);	
			$this->excel->getActiveSheet()->mergeCells('A'.$income.':B'.$income)->setCellValue('A'.$income , lang('total_income'));
			$this->excel->getActiveSheet()->SetCellValue('C' . $income, number_format((-1)*($total_income),2));
			//$this->excel->getActiveSheet()->SetCellValue('D' . $income, number_format((-1)*($totalBeforeAyear_income),2));
			
			$this->excel->getActiveSheet()->getStyle('A'.($income + 1).':B'.($income +1))->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.($income + 1).':B'.($income +1))->setCellValue('A'. ($income + 1) , lang('cost'));
			//$this->excel->getActiveSheet()->mergeCells('C'.($income + 1).':D'.($income +1));
			
			$total_cost = 0;
			$totalBeforeAyear_cost = 0;
			$cost = $income + 2;
			foreach($dataCost->result() as $rowcost){
				$total_cost += $rowcost->amount;

				$query = $this->db->query("SELECT
					sum(erp_gl_trans.amount) AS amount
				FROM
					erp_gl_trans
				WHERE
					DATE(tran_date) = '$totalBeforeAyear' AND account_code = '" . $rowcost->account_code . "';");
				$totalBeforeAyearRows = $query->row();
				$totalBeforeAyear_cost += $totalBeforeAyearRows->amount;
				$this->excel->getActiveSheet()->SetCellValue('A' . $cost, $rowcost->account_code.' - '.$rowcost->accountname);
				$this->excel->getActiveSheet()->SetCellValue('B' . $cost, number_format(abs($rowcost->amount),2));
				$this->excel->getActiveSheet()->SetCellValue('C' . $cost, '');
				//$this->excel->getActiveSheet()->SetCellValue('D' . $cost, number_format(abs($totalBeforeAyearRows->amount),2));
				$cost++;
			}

			$this->excel->getActiveSheet()->getStyle('A'.($income+2).':A'.($cost-1))->getAlignment()->setIndent(2);	
			$this->excel->getActiveSheet()->mergeCells('A'.$cost.':B'.$cost)->setCellValue('A'.$cost , lang('total_cost'));
			$this->excel->getActiveSheet()->SetCellValue('C' . $cost, number_format((-1)*$total_cost,2));
			//$this->excel->getActiveSheet()->SetCellValue('D' . $cost, number_format((-1)*$totalBeforeAyear_cost,2));

			$this->excel->getActiveSheet()->getStyle('C'.($cost + 1).':D'.($cost + 1))->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.($cost + 1).':B'.($cost + 1))->setCellValue('A'.($cost + 1) , lang('gross_margin'));
			$this->excel->getActiveSheet()->SetCellValue('C' . ($cost +1), number_format((-1)*($total_cost+$total_income),2));
			//$this->excel->getActiveSheet()->SetCellValue('D' . ($cost +1), number_format((-1)*($totalBeforeAyear_income+$totalBeforeAyear_cost),2));
			
			$this->excel->getActiveSheet()->getStyle('A'.($cost + 2).':B'.($cost + 2))->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.($cost + 2).':B'.($cost + 2))->setCellValue('A'. ($cost + 2) , lang('operating_expense'));
			//$this->excel->getActiveSheet()->mergeCells('C'.($cost + 2).':D'.($cost + 2));
			
			$total_expense = 0;
			$totalBeforeAyear_expense = 0;
			$expene = $cost + 3;
			foreach($dataExpense->result() as $row){
				$total_expense += $row->amount;

				$query = $this->db->query("SELECT
					sum(erp_gl_trans.amount) AS amount
				FROM
					erp_gl_trans
				WHERE
					DATE(tran_date) = '$totalBeforeAyear' AND account_code = '" . $row->account_code . "';");
				$totalBeforeAyearRows = $query->row();
				$totalBeforeAyear_expense += $totalBeforeAyearRows->amount;
				$this->excel->getActiveSheet()->SetCellValue('A' . $expene, $row->account_code.' - '.$row->accountname);
				$this->excel->getActiveSheet()->SetCellValue('B' . $expene, number_format(abs($row->amount),2));
				$this->excel->getActiveSheet()->SetCellValue('C' . $expene, '');
				//$this->excel->getActiveSheet()->SetCellValue('D' . $expene, number_format(abs($totalBeforeAyearRows->amount),2));
			}
			$this->excel->getActiveSheet()->mergeCells('A'.$expene.':B'.$expene)->setCellValue('A'.$expene , lang('total_expense'));
			$this->excel->getActiveSheet()->SetCellValue('C' . $expene, number_format((-1)*$total_expense,2));
			//$this->excel->getActiveSheet()->SetCellValue('D' . $expene, number_format((-1)*$totalBeforeAyear_expense,2));
			
			$this->excel->getActiveSheet()->getStyle('A'.($expene + 1).':C'.($expene + 1))->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.($expene + 1).':B'.($expene + 1))->setCellValue('A'. ($expene + 1) , lang('profits'));
			$this->excel->getActiveSheet()->SetCellValue('C' . ($expene + 1), number_format((-1)*$total_income-($total_cost+$total_expense),2));
			//$this->excel->getActiveSheet()->SetCellValue('D' . ($expene + 1), number_format((-1)*$totalBeforeAyear_income-($totalBeforeAyear_cost+$totalBeforeAyear_expense),2));
			
			$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			//$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$filename = 'Income_Statement' . date('Y_m_d_H_i_s');
			if ($xls) {
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
				return $objWriter->save('php://output');
			}

			redirect($_SERVER["HTTP_REFERER"]);	
		}
		
        $this->page_construct('reports/income_statement', $meta, $this->data);
	}
	
	function balance_sheet($start_date = NULL, $end_date = NULL, $pdf = NULL, $xls = NULL, $biller_id = NULL)
    {
        $this->erp->checkPermissions('account', true, 'reports');
		$user = $this->site->getUser();
		if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
			$gl_ = $this->accounts_model->getGLYearMonth();
			if($gl_){
				$gl_full = $gl_->min_year . '-' . $gl_->min_month;
				$start_date = date('Y-m', strtotime($gl_full)) . '-1';
			}else{
				$start_date = date('Y-m') . '-1';
			}
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }
		
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			if($user->biller_id){
				$this->data['biller_id'] = $user->biller_id;
				$biller_id = $user->biller_id;
			}else{
				$this->data['biller_id'] = "";
			}
			
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
		
		$this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);
		
		
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports/balance_sheet')));
        $meta = array('page_title' => lang('balance_sheet'), 'bc' => $bc);
		$from_date = date('Y-m-d',strtotime(urldecode($start_date)));//'2014-08-01';
		//$to_date = date('Y-m-d',strtotime(urldecode($end_date)));//'2015-09-01'; before, it use in select query.
		
		$rep_space_end=str_replace(' ','_',urldecode($end_date));
		$end_dates=str_replace(':','-',$rep_space_end);//replace  $to_date.
		
		$totalBeforeAyear = date('Y', strtotime($this->data['start'])) - 1;

        $this->data['totalBeforeAyear'] = $totalBeforeAyear;
		$dataAsset = $this->accounts_model->getStatementByDate('10,11',$from_date,$end_dates,$biller_id);
		$this->data['dataAsset'] = $dataAsset;
		
		$dataLiability = $this->accounts_model->getStatementByDate('20,21',$from_date,$end_dates,$biller_id);
		$this->data['dataLiability'] = $dataLiability;
		
		$dataEquity = $this->accounts_model->getStatementByDate('30',$from_date,$end_dates,$biller_id);
		$this->data['dataEquity'] = $dataEquity;
		
		$dataIncome = $this->accounts_model->getStatementByDate('40,70',$from_date,$end_dates,$biller_id);
		$this->data['dataIncome'] = $dataIncome;
		
		$dataExpense = $this->accounts_model->getStatementByDate('50,60,80,90',$from_date,$end_dates,$biller_id);
		$this->data['dataExpense'] = $dataExpense;
		
		if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/balance_sheet', $this->data, true);
            $name = lang("balance_sheet") . "_" . date('Y_m_d_H_i_s') . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_balance_text") . '</p>', '', $html);
            $this->erp->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
		
		
		if($xls){
			$styleArray = array(
				'font'  => array(
					'bold'  => true,
					'color' => array('rgb' => '000000'),
					'size'  => 10,
					'name'  => 'Verdana'
				)
			);
			$bold = array(
				'font' => array(
					'bold' => true
				)
			);
			$this->load->library('excel');
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($styleArray);
			$this->excel->getActiveSheet()->setTitle(lang('Balance Sheet'));
			$this->excel->getActiveSheet()->SetCellValue('A1', lang('account_name'));
			$this->excel->getActiveSheet()->SetCellValue('B1', lang('debit'));
			$this->excel->getActiveSheet()->SetCellValue('C1', lang('credit'));
			$this->excel->getActiveSheet()->SetCellValue('D1', lang("debit") . ' (' . $totalBeforeAyear . ')');
			$this->excel->getActiveSheet()->SetCellValue('E1', lang("credit") . ' (' . $totalBeforeAyear . ')');
			
			$this->excel->getActiveSheet()->getStyle('A2:B2')->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A2:B2')->setCellValue('A2' , lang('asset'));
			$this->excel->getActiveSheet()->mergeCells('C2:E2');
			$total_asset = 0;
			$totalBeforeAyear_asset = 0;
			$Asset = 3;
			foreach($dataAsset->result() as $row){
				$total_asset += $row->amount;
				$query = $this->db->query("SELECT
				SUM(CASE WHEN erp_gl_trans.amount < 0 THEN erp_gl_trans.amount ELSE 0 END) as NegativeTotal,
				SUM(CASE WHEN erp_gl_trans.amount >= 0 THEN erp_gl_trans.amount ELSE 0 END) as PostiveTotal
				FROM
					erp_gl_trans
				WHERE
					DATE(tran_date) = '$totalBeforeAyear' AND account_code = '" . $row->account_code . "';");
				$totalBeforeAyearRows = $query->row();
				$totalBeforeAyear_asset += ($totalBeforeAyearRows->NegativeTotal + $totalBeforeAyearRows->PostiveTotal);
				
				if ($row->amount>0){
					$this->excel->getActiveSheet()->SetCellValue('A' . $Asset, $row->account_code.' - '.$row->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $Asset, number_format(abs($row->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('C' . $Asset, '');
					$this->excel->getActiveSheet()->SetCellValue('D' . $Asset, number_format(abs($totalBeforeAyearRows->PostiveTotal),2));
					$this->excel->getActiveSheet()->SetCellValue('E' . $Asset, '');
				}else{
					$this->excel->getActiveSheet()->SetCellValue('A' . $Asset, $row->account_code.' - '.$row->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $Asset, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $Asset, number_format(abs($row->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('D' . $Asset, '');
					$this->excel->getActiveSheet()->SetCellValue('E' . $Asset, number_format(abs($totalBeforeAyearRows->NegativeTotal),2));
				}
				$Asset++;
			}
			
			$this->excel->getActiveSheet()->getStyle('A3:A'.($Asset-1))->getAlignment()->setIndent(2);
			
			$this->excel->getActiveSheet()->getStyle('B'.$Asset.':E'.$Asset)->applyFromArray($bold);
			$this->excel->getActiveSheet()->SetCellValue('A' . $Asset, lang('total_asset'));
			$this->excel->getActiveSheet()->SetCellValue('B' . $Asset, number_format(abs($total_asset),2));
			$this->excel->getActiveSheet()->SetCellValue('C' . $Asset, '');
			$this->excel->getActiveSheet()->SetCellValue('D' . $Asset,  number_format(abs($totalBeforeAyear_asset),2));
			$this->excel->getActiveSheet()->SetCellValue('E' . $Asset, '');
			
			$eq = $Asset + 1;
			$this->excel->getActiveSheet()->getStyle('A'.$eq.':B'.$eq)->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.$eq.':B'.$eq)->setCellValue('A' . $eq , lang('liabilities'));
			$this->excel->getActiveSheet()->mergeCells('C'.$eq.':E'.$eq);
			$total_liability = 0;
			$totalBeforeAyear_liability = 0;
			$Liability = $Asset + 2;
			foreach($dataLiability->result() as $rowlia){
				$total_liability += $rowlia->amount;

				$query = $this->db->query("SELECT
					SUM(CASE WHEN erp_gl_trans.amount < 0 THEN erp_gl_trans.amount ELSE 0 END) as NegativeTotal,
					SUM(CASE WHEN erp_gl_trans.amount >= 0 THEN erp_gl_trans.amount ELSE 0 END) as PostiveTotal
					FROM
						erp_gl_trans
					WHERE
						DATE(tran_date) = '$totalBeforeAyear' AND account_code = '" . $rowlia->account_code . "';");
				$totalBeforeAyearRows = $query->row();
				$totalBeforeAyear_liability += ($totalBeforeAyearRows->NegativeTotal + $totalBeforeAyearRows->PostiveTotal);
				if ($rowlia->amount>0){
					$this->excel->getActiveSheet()->SetCellValue('A' . $Liability, $rowlia->account_code.' - '.$rowlia->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $Liability, number_format(abs($rowlia->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('C' . $Liability, '');
					$this->excel->getActiveSheet()->SetCellValue('D' . $Liability, number_format(abs($totalBeforeAyearRows->PostiveTotal),2));
					$this->excel->getActiveSheet()->SetCellValue('E' . $Liability, '');
				}else{
					$this->excel->getActiveSheet()->SetCellValue('A' . $Liability, $rowlia->account_code.' - '.$rowlia->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $Liability, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $Liability, number_format(abs($rowlia->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('D' . $Liability, '');
					$this->excel->getActiveSheet()->SetCellValue('E' . $Liability, number_format(abs($totalBeforeAyearRows->NegativeTotal),2));
				}
				$Liability++;
			}
			
			$this->excel->getActiveSheet()->getStyle('A'.($Asset+2).':A'.($Liability-1))->getAlignment()->setIndent(2);
			$this->excel->getActiveSheet()->getStyle('B'.$Liability.':E'.$Liability)->applyFromArray($bold);
			$this->excel->getActiveSheet()->SetCellValue('A' . $Liability, lang('total_liabilities'));
			$this->excel->getActiveSheet()->SetCellValue('B' . $Liability, '');
			$this->excel->getActiveSheet()->SetCellValue('C' . $Liability, number_format(abs($total_liability),2));
			$this->excel->getActiveSheet()->SetCellValue('D' . $Liability, '');
			$this->excel->getActiveSheet()->SetCellValue('E' . $Liability, number_format(abs($totalBeforeAyear_liability),2));
			
			$equ = $Liability + 1;
			$this->excel->getActiveSheet()->getStyle('A'.$equ.':B'.$equ)->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.$equ.':B'.$equ)->setCellValue('A' . $equ , lang('equities'));
			$this->excel->getActiveSheet()->mergeCells('C'.$equ.':E'.$equ);
			$total_income = 0;
			$total_expense = 0;
			$total_returned = 0;
			$equities = $Liability + 2;
			$total_income_beforeAyear = 0;
			$total_expense_beforeAyear = 0;
			$total_returned_beforeAyear = 0;
			$queryIncom = $this->db->query("SELECT sum(erp_gl_trans.amount) AS amount FROM
										erp_gl_trans
									INNER JOIN erp_gl_charts ON erp_gl_charts.accountcode = erp_gl_trans.account_code
									WHERE DATE(tran_date) = '$totalBeforeAyear' AND	erp_gl_trans.sectionid IN ('40,70') GROUP BY erp_gl_trans.account_code;");
			$total_income_beforeAyear = $queryIncom->amount;

			$queryExpense = $this->db->query("SELECT sum(erp_gl_trans.amount) AS amount FROM
										erp_gl_trans
									INNER JOIN erp_gl_charts ON erp_gl_charts.accountcode = erp_gl_trans.account_code
									WHERE DATE(tran_date) = '$totalBeforeAyear' AND	erp_gl_trans.sectionid IN ('50,60,80,90') GROUP BY erp_gl_trans.account_code;");
			$total_expense_beforeAyear = $queryExpense->amount;

			$total_returned_beforeAyear = abs($total_income_beforeAyear)-abs($total_expense_beforeAyear);

			foreach($dataIncome->result() as $rowincome){
				$total_income += $rowincome->amount;
			}
			foreach($dataExpense->result() as $rowexpense){
				$total_expense += $rowexpense->amount;
			}
			$total_returned = abs($total_income)-abs($total_expense);
			$this->excel->getActiveSheet()->SetCellValue('A' . $equities, '300000 - Retained Earnings');
			if($total_returned<0) {
				$this->excel->getActiveSheet()->SetCellValue('B' . $equities, number_format(abs($total_returned),2));
				$this->excel->getActiveSheet()->SetCellValue('C' . $equities, '');
				$this->excel->getActiveSheet()->SetCellValue('D' . $equities, number_format($total_returned_beforeAyear,2));
				$this->excel->getActiveSheet()->SetCellValue('E' . $equities, '');
			}else{
				$this->excel->getActiveSheet()->SetCellValue('B' . $equities, '');
				$this->excel->getActiveSheet()->SetCellValue('C' . $equities, number_format(abs($total_returned),2));
				$this->excel->getActiveSheet()->SetCellValue('D' . $equities, '');
				$this->excel->getActiveSheet()->SetCellValue('E' . $equities, number_format($total_returned_beforeAyear,2));
			}
			
			$total_equity = 0;
			$totalBeforeAyear_equity = 0;
			$equity = $equities + 1;
			foreach($dataEquity->result() as $rowequity){
				$total_equity += $rowequity->amount;

				$query = $this->db->query("SELECT
					sum(erp_gl_trans.amount) AS amount
				FROM
					erp_gl_trans
				WHERE
					DATE(tran_date) = '$totalBeforeAyear' AND account_code = '" . $rowequity->account_code . "';");
				$totalBeforeAyearRows = $query->row();
				$totalBeforeAyear_equity += $totalBeforeAyearRows->amount;
				if($rowequity->amount<0) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $equity, $rowequity->account_code.' - '.$rowequity->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $equity, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $equity, number_format(abs($rowequity->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('D' . $equity, number_format(abs($totalBeforeAyearRows->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('E' . $equity, '');
				}else{
					$this->excel->getActiveSheet()->SetCellValue('A' . $equity, $rowequity->account_code.' - '.$rowequity->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $equity, number_format(abs($rowequity->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('C' . $equity, '');
					$this->excel->getActiveSheet()->SetCellValue('D' . $equity, number_format(abs($totalBeforeAyearRows->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('E' . $equity, '');
				}
				$equity++;
			}
			
			$this->excel->getActiveSheet()->getStyle('A'.($Liability+2).':A'.($equity-1))->getAlignment()->setIndent(2);
			
			$this->excel->getActiveSheet()->getStyle('B'.$equity.':E'.$equity)->applyFromArray($bold);
			$this->excel->getActiveSheet()->SetCellValue('A' . $equity, lang('total_equities'));
			$this->excel->getActiveSheet()->SetCellValue('B' . $equity, '');
			$this->excel->getActiveSheet()->SetCellValue('C' . $equity, number_format(abs($total_equity-$total_returned),2));
			$this->excel->getActiveSheet()->SetCellValue('D' . $equity,  '');
			$this->excel->getActiveSheet()->SetCellValue('E' . $equity, number_format(abs($totalBeforeAyear_equity-$total_returned_beforeAyear),2));
			
			$totalL = $equity + 1;
			$this->excel->getActiveSheet()->getStyle('B'.$totalL.':E'.$totalL)->applyFromArray($bold);
			$this->excel->getActiveSheet()->SetCellValue('A' . $totalL, lang('total_liabilities_equities'));
			$this->excel->getActiveSheet()->SetCellValue('B' . $totalL, '');
			$this->excel->getActiveSheet()->SetCellValue('C' . $totalL, number_format(abs($total_equity+$total_liability-$total_returned),2));
			$this->excel->getActiveSheet()->SetCellValue('D' . $totalL,  '');
			$this->excel->getActiveSheet()->SetCellValue('E' . $totalL, number_format(abs($totalBeforeAyear_equity+$totalBeforeAyear_liability-$total_returned_beforeAyear),2));
			
			$totalA = $totalL + 1;
			$this->excel->getActiveSheet()->getStyle('A'.$totalA.':E'.$totalA)->applyFromArray($bold);
			$this->excel->getActiveSheet()->SetCellValue('A' . $totalA, lang('Total ASSET = LIABILITIES + EQUITY'));
			$this->excel->getActiveSheet()->SetCellValue('B' . $totalA, '');
			$this->excel->getActiveSheet()->SetCellValue('C' . $totalA, number_format(abs($total_equity+$total_liability-$total_returned)-abs($total_asset),2));
			$this->excel->getActiveSheet()->SetCellValue('D' . $totalA,  '');
			$this->excel->getActiveSheet()->SetCellValue('E' . $totalA, number_format(abs($totalBeforeAyear_equity+$totalBeforeAyear_liability+$total_returned_beforeAyear)-abs($totalBeforeAyear_asset),2));
			
			
			$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
			$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$filename = 'Balance_Sheet' . date('Y_m_d_H_i_s');
			if ($xls) {
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
				return $objWriter->save('php://output');
			}

			redirect($_SERVER["HTTP_REFERER"]);	
		}
        $this->page_construct('reports/balance_sheet', $meta, $this->data);
	}
	
	function trial_balance($start_date = NULL, $end_date = NULL, $pdf= NULL, $xls = NULL, $biller_id = NULL)
    {
        $this->erp->checkPermissions('account', true,'reports');
		
		if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }
		$user = $this->site->getUser();
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			$this->data['biller_id'] = "";
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
		$this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);
		
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports/trial_balance')));
        $meta = array('page_title' => lang('trial_balance'), 'bc' => $bc);
		$from_date = date('Y-m-d H:m',strtotime(urldecode($start_date)));//'2014-08-01';
		$to_date = date('Y-m-d H:m',strtotime(urldecode($end_date)));//'2015-09-01';
		
		$data10 = $this->accounts_model->getStatementByDate('10',$from_date,$to_date,$biller_id);
		$this->data['data10'] = $data10;
		
		$data11 = $this->accounts_model->getStatementByDate('11',$from_date,$to_date,$biller_id);
		$this->data['data11'] = $data11;
		
		$data20 = $this->accounts_model->getStatementByDate('20',$from_date,$to_date,$biller_id);
		$this->data['data20'] = $data20;
		
		$data21 = $this->accounts_model->getStatementByDate('21',$from_date,$to_date,$biller_id);
		$this->data['data21'] = $data21;
		
		$data30 = $this->accounts_model->getStatementByDate('30',$from_date,$to_date,$biller_id);
		$this->data['data30'] = $data30;
		
		$data40 = $this->accounts_model->getStatementByDate('40',$from_date,$to_date,$biller_id);
		$this->data['data40'] = $data40;
		
		$data50 = $this->accounts_model->getStatementByDate('50',$from_date,$to_date,$biller_id);
		$this->data['data50'] = $data50;
		
		$data60 = $this->accounts_model->getStatementByDate('60',$from_date,$to_date,$biller_id);
		$this->data['data60'] = $data60;
		
		$data70 = $this->accounts_model->getStatementByDate('70',$from_date,$to_date,$biller_id);
		$this->data['data70'] = $data70;
		
		$data80 = $this->accounts_model->getStatementByDate('80',$from_date,$to_date,$biller_id);
		$this->data['data80'] = $data80;		
		
		if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/trial_balance', $this->data, true);
            $name = lang("trial_balance") . "_" . date('Y_m_d_H_i_s') . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_trial_text") . '</p>', '', $html);
            $this->erp->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
		
		if($xls){
			$styleArray = array(
				'font'  => array(
					'bold'  => true,
					'color' => array('rgb' => '000000'),
					'size'  => 10,
					'name'  => 'Verdana'
				),
			);
			$bold = array(
				'font' => array(
					'bold' => true
				)
			);
			$this->load->library('excel');
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getStyle('A1:C1')->applyFromArray($styleArray);
			$this->excel->getActiveSheet()->setTitle(lang('Trial Balance'));
			$this->excel->getActiveSheet()->SetCellValue('A1', lang('account_name'));
			$this->excel->getActiveSheet()->SetCellValue('B1', lang('debit'));
			$this->excel->getActiveSheet()->SetCellValue('C1', lang('credit'));
			$this->excel->getActiveSheet()->getStyle('A2:B2')->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A2:B2')->setCellValue('A2' , lang('current_assets'));
			$total_10 = 0;
			$total_C = 0;
			$total_D = 0;
			$r10 = 3;
			foreach($data10->result() as $row10){
				if ($row10->amount>0){
					$this->excel->getActiveSheet()->mergeCells('C2:C'. $r10);
					$total_C += $row10->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r10, $row10->account_code.' - '.$row10->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r10, number_format(abs($row10->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('C' . $r10, '');
				}else{
					$total_D += $row10->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r10, $row10->account_code.' - '.$row10->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r10, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r10, number_format(abs($row10->amount),2));
				}
				$r10++;
			}
			
			$this->excel->getActiveSheet()->getStyle('A'.$r10.':B'.$r10)->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.$r10.':B'.$r10)->setCellValue('A' . $r10 , lang('fixed_assets'));
			$total_11 = 0;
			$r11 = $r10 + 1;
			foreach($data11->result() as $row11){
				if ($row11->amount>0){
					$this->excel->getActiveSheet()->mergeCells('C'.$r10.':C'. $r11);
					$total_C += $row11->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r11, $row11->account_code.' - '.$row11->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r11, number_format(abs($row11->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('C' . $r11, '');
				}else{
					$total_D += $row11->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r11, $row11->account_code.' - '.$row11->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r11, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r11, number_format(abs($row11->amount),2));
				}
				$r11++;
			}
			
			$this->excel->getActiveSheet()->getStyle('A'.$r11.':B'.$r11)->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.$r11.':B'.$r11)->setCellValue('A' . $r11 , lang('current_liabilities'));
			$total_20 = 0;
			$r20 = $r11 + 1;
			foreach($data20->result() as $row20){
				if ($row20->amount>0){
					//$this->excel->getActiveSheet()->mergeCells('C'.$r11.':C'. $r20);
					$total_C += $row20->amount;
					if($row20->account_code == 201100){
						$this->excel->getActiveSheet()->SetCellValue('A' . $r20, $row20->account_code.' - '.$row20->accountname);
						$this->excel->getActiveSheet()->SetCellValue('B' . $r20, '');
						$this->excel->getActiveSheet()->SetCellValue('C' . $r20, number_format(abs($row20->amount),2));
					}else{
						$this->excel->getActiveSheet()->SetCellValue('A' . $r20, $row20->account_code.' - '.$row20->accountname);
						$this->excel->getActiveSheet()->SetCellValue('B' . $r20, number_format(abs($row20->amount),2));
						$this->excel->getActiveSheet()->SetCellValue('C' . $r20, '');
					}
				}else{
					$total_D += $row20->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r20, $row20->account_code.' - '.$row20->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r20, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r20, number_format(abs($row20->amount),2));
				}
				$r20++;
			}			
			
			$this->excel->getActiveSheet()->getStyle('A'.$r20.':B'.$r20)->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.$r20.':B'.$r20)->setCellValue('A' . $r20 , lang('non_liabilities'));
			$total_21 = 0;
			$r21 = $r20 + 1;
			foreach($data21->result() as $row21){
				if ($row21->amount>0){
					//$this->excel->getActiveSheet()->mergeCells('C'.$r11.':C'. $r20);
					$total_C += $row21->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r21, $row21->account_code.' - '.$row21->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r21, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r21, number_format(abs($row21->amount),2));
				}else{
					$total_D += $row21->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r21, $row21->account_code.' - '.$row21->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r21, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r21, number_format(abs($row21->amount),2));
				}
				$r21++;
			}	

			$this->excel->getActiveSheet()->getStyle('A'.$r21.':B'.$r21)->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.$r21.':B'.$r21)->setCellValue('A' . $r21 , lang('equity_retained_erning'));
			$total_30 = 0;
			$r30 = $r21 + 1;
			foreach($data30->result() as $row30){
				if ($row30->amount>0){
					//$this->excel->getActiveSheet()->mergeCells('C'.$r11.':C'. $r20);
					$total_C += $row30->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r30, $row30->account_code.' - '.$row30->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r30, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r30, number_format(abs($row30->amount),2));
				}else{
					$total_D += $row30->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r30, $row30->account_code.' - '.$row30->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r30, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r30, number_format(abs($row30->amount),2));
				}
				$r30++;
			}

			$this->excel->getActiveSheet()->getStyle('A'.$r30.':B'.$r30)->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.$r30.':B'.$r30)->setCellValue('A' . $r30 , lang('income'));
			$total_40 = 0;
			$r40 = $r30 + 1;
			foreach($data40->result() as $row40){
				if ($row40->amount>0){
					//$this->excel->getActiveSheet()->mergeCells('C'.$r11.':C'. $r20);
					$total_C += $row40->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r40, $row40->account_code.' - '.$row40->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r40, number_format(abs($row40->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('C' . $r40, '');
				}else{
					$total_D += $row40->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r40, $row40->account_code.' - '.$row40->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r40, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r40, number_format(abs($row40->amount),2));
				}
				$r40++;
			}
			
			$this->excel->getActiveSheet()->getStyle('A'.$r40.':B'.$r40)->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.$r40.':B'.$r40)->setCellValue('A' . $r40 , lang('cost'));
			$total_50 = 0;
			$r50 = $r40 + 1;
			foreach($data50->result() as $row50){
				if ($row50->amount>0){
					//$this->excel->getActiveSheet()->mergeCells('C'.$r11.':C'. $r20);
					$total_C += $row50->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r50, $row50->account_code.' - '.$row50->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r50, number_format(abs($row50->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('C' . $r50, '');
				}else{
					$total_D += $row50->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r50, $row50->account_code.' - '.$row50->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r50, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r50, number_format(abs($row50->amount),2));
				}
				$r50++;
			}
			
			$this->excel->getActiveSheet()->getStyle('A'.$r50.':B'.$r50)->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.$r50.':B'.$r50)->setCellValue('A' . $r50 , lang('operating_expense'));
			$total_60 = 0;
			$r60 = $r50 + 1;
			foreach($data60->result() as $row60){
				if ($row60->amount>0){
					//$this->excel->getActiveSheet()->mergeCells('C'.$r11.':C'. $r20);
					$total_C += $row60->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r60, $row60->account_code.' - '.$row60->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r60, number_format(abs($row60->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('C' . $r60, '');
				}else{
					$total_D += $row60->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r60, $row60->account_code.' - '.$row60->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r60, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r60, number_format(abs($row60->amount),2));
				}
				$r60++;
			}
			
			$this->excel->getActiveSheet()->getStyle('A'.$r60.':B'.$r60)->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.$r60.':B'.$r60)->setCellValue('A' . $r60 , lang('other_income'));
			$total_70 = 0;
			$r70 = $r60 + 1;
			foreach($data70->result() as $row70){
				if ($row70->amount>0){
					//$this->excel->getActiveSheet()->mergeCells('C'.$r11.':C'. $r20);
					$total_C += $row70->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r70, $row70->account_code.' - '.$row70->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r70, number_format(abs($row70->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('C' . $r70, '');
				}else{
					$total_D += $row70->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r70, $row70->account_code.' - '.$row70->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r70, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r70, number_format(abs($row70->amount),2));
				}
				$r70++;
			}
			
			$this->excel->getActiveSheet()->getStyle('A'.$r70.':B'.$r70)->applyFromArray($bold);
			$this->excel->getActiveSheet()->mergeCells('A'.$r70.':B'.$r70)->setCellValue('A' . $r70 , lang('other_expense'));
			$total_80 = 0;
			$r80 = $r70 + 1;
			foreach($data80->result() as $row80){
				if ($row80->amount>0){
					//$this->excel->getActiveSheet()->mergeCells('C'.$r11.':C'. $r20);
					$total_C += $row80->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r80, $row80->account_code.' - '.$row80->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r80, number_format(abs($row80->amount),2));
					$this->excel->getActiveSheet()->SetCellValue('C' . $r80, '');
				}else{
					$total_D += $row80->amount;
					$this->excel->getActiveSheet()->SetCellValue('A' . $r80, $row80->account_code.' - '.$row80->accountname);
					$this->excel->getActiveSheet()->SetCellValue('B' . $r80, '');
					$this->excel->getActiveSheet()->SetCellValue('C' . $r80, number_format(abs($row80->amount),2));
				}
				$r80++;
			}
			
			$this->excel->getActiveSheet()->getStyle('A'.$r80.':C'.$r80)->applyFromArray($bold);
			$this->excel->getActiveSheet()->SetCellValue('A' . $r80, lang('total'));
			$this->excel->getActiveSheet()->SetCellValue('B' . $r80, number_format(abs($total_D),2));
			$this->excel->getActiveSheet()->SetCellValue('C' . $r80, number_format(abs($total_C),2));
			
			$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$filename = 'Trial_Balance' . date('Y_m_d_H_i_s');
			if ($xls) {
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
				return $objWriter->save('php://output');
			}

			redirect($_SERVER["HTTP_REFERER"]);	
		}
		
        $this->page_construct('reports/trial_balance', $meta, $this->data);
	}
	
	function categories($biller_id = NULL)
    {
        $this->erp->checkPermissions('products', true, 'reports');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
		
		$user = $this->site->getUser();
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			if($user->biller_id){
				$this->data['biller_id'] = $user->biller_id;
			}else{
				$this->data['biller_id'] = "";
			}
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
		
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('categories_report')));
        $meta = array('page_title' => lang('categories_report'), 'bc' => $bc);
        $this->page_construct('reports/categories', $meta, $this->data);
    }
	
	function categories_value()
    {
        $this->erp->checkPermissions('products', true);
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('categories_value_report')));
        $meta = array('page_title' => lang('categories_value_report'), 'bc' => $bc);
        $this->page_construct('reports/categories_value', $meta, $this->data);
    }

    function ledger($pdf = NULL, $xls = null, $biller_id = NULL)
    {
        $this->erp->checkPermissions('account', true, 'reports');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
		
		$user = $this->site->getUser();
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			if($user->biller_id){
				$this->data['biller_id'] = $user->biller_id;
				$biller_id = $user->biller_id;
			}else{
				$this->data['biller_id'] = "";
			}
			
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
		
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
		
		if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/ledger', $this->data, true);
            $name = lang("ledger") . "_" . date('Y_m_d_H_i_s') . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_ledger_text") . '</p>', '', $html);
            $this->erp->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
		
		if($xls){
			$styleArray = array(
				'font'  => array(
					'bold'  => true,
					'color' => array('rgb' => '2E9AFE'),
					'size'  => 10,
					'name'  => 'Verdana'
				),
			);
			$this->load->library('excel');
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($styleArray);
			$this->excel->getActiveSheet()->setTitle(lang('ledger'));
			$this->excel->getActiveSheet()->SetCellValue('A1', lang('batch'));
			$this->excel->getActiveSheet()->SetCellValue('B1', lang('ref'));
			$this->excel->getActiveSheet()->SetCellValue('C1', lang('Seq'));
			$this->excel->getActiveSheet()->SetCellValue('D1', lang('description'));
			$this->excel->getActiveSheet()->SetCellValue('E1', lang('date'));
			$this->excel->getActiveSheet()->SetCellValue('F1', lang('type'));
			$this->excel->getActiveSheet()->SetCellValue('G1', lang('debit_amount'));
			$this->excel->getActiveSheet()->SetCellValue('H1', lang('credit_amount'));
			
			$accounntCode = $this->db;
			$accounntCode->select('*')->from('gl_charts');
			if ($this->input->post('account') ) {
				$accounntCode->where('accountcode', $this->input->post('account'));
			}
			$acc = $accounntCode->get()->result();
			$row = 2;
			$rows = 3;
			foreach($acc as $val){
				$gl_tranStart = $this->db->select('sum(amount) as startAmount')->from('gl_trans');
				$gl_tranStart->where(array('tran_date < '=> $this->erp->fld($this->input->post('start_date')), 'account_code'=> $val->accountcode));
				$startAmount = $gl_tranStart->get()->row();
				
				$endAccountBalance = 0;
				$getListGLTran = $this->db->select("*")->from('gl_trans')->where('account_code =', $val->accountcode);
				if ($this->input->post('start_date')) {
					$getListGLTran->where('tran_date >=', $this->erp->fld($this->input->post('start_date')) );
				}
				if ($this->input->post('end_date')) {
					$getListGLTran->where('tran_date <=', $this->erp->fld($this->input->post('end_date')) );
				}
				if (!$this->input->post('end_date') && !$this->input->post('end_date'))
				{
					$current_month = date('m');
					$getListGLTran->where('MONTH(tran_date)', $current_month);
				}
				if($biller_id != ""){
					 $getListGLTran->where('biller_id' ,$biller_id);
				}
				$gltran_list = $getListGLTran->get()->result();
				if($gltran_list) {
					$this->excel->getActiveSheet()->mergeCells('A'.$row.':D'.$row)->setCellValue('A'. $row , 'Account: '.$val->accountcode . ' ' .$val->accountname);
					$this->excel->getActiveSheet()->mergeCells('E'.$row.':F'.$row)->setCellValue('E'. $row , 'Begining Account Balance: ');
					$this->excel->getActiveSheet()->mergeCells('G'.$row.':H'.$row)->setCellValue('G'. $row , abs($this->erp->formatMoney($startAmount->startAmount)));
					foreach($gltran_list as $rw)
					{
						$endAccountBalance += $rw->amount;
						$this->excel->getActiveSheet()->SetCellValue('A' . $rows, $rw->tran_id);
						$this->excel->getActiveSheet()->SetCellValue('B' . $rows, $rw->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $rows, $rw->tran_no);
						$this->excel->getActiveSheet()->SetCellValue('D' . $rows, $rw->narrative);
						$this->excel->getActiveSheet()->SetCellValue('E' . $rows, $rw->tran_date);
						$this->excel->getActiveSheet()->SetCellValue('F' . $rows, $rw->tran_type);
						$this->excel->getActiveSheet()->SetCellValue('G' . $rows, ($rw->amount > 0 ? $this->erp->formatMoney($rw->amount) : '0.00'));
						$this->excel->getActiveSheet()->SetCellValue('H' . $rows, ($rw->amount < 1 ? $this->erp->formatMoney(abs($rw->amount)) : '0.00'));
						$rows++;	
					}
					$test = $rows;
					$this->excel->getActiveSheet()->mergeCells('A'.$test.':D'.$test);
					$this->excel->getActiveSheet()->mergeCells('E'.$test.':F'.$test)->setCellValue('E'. $test , 'Ending Account Balance: ');
					$this->excel->getActiveSheet()->mergeCells('G'.$test.':H'.$test)->setCellValue('G'. $test , $this->erp->formatMoney(abs($endAccountBalance)));
					$row = $rows;
					$rows = $rows + 2 ;
					$row++;
				}		
				
			}
			$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(5);
			$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
			$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
			$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
			$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$filename = 'ledger_' . date('Y_m_d_H_i_s');
			if ($xls) {
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
				return $objWriter->save('php://output');
			}

			redirect($_SERVER["HTTP_REFERER"]);	
		}

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Ledger_report')));
        $meta = array('page_title' => lang('Ledger_report'), 'bc' => $bc);
        $this->page_construct('reports/ledger', $meta, $this->data);
    }

    function getLedger()
    {
        $this->erp->checkPermissions('register', TRUE);
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
    }

    function cash_books($pdf = NULL,$biller_id = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('account', true, 'reports');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
		$user = $this->site->getUser();
		if($biller_id != NULL){
			$this->data['biller_id'] = $biller_id;
		}else{
			if($user->biller_id){
				$this->data['biller_id'] = $user->biller_id;
				$biller_id = $user->biller_id;
			}else{
				$this->data['biller_id'] = $user->biller_id;
			}
		}
		if(!$this->Owner && !$this->Admin) {
			if($user->biller_id){
				$this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
			}else{
				$this->data['billers'] = $this->site->getAllCompanies('biller');
			}
		}else{
			$this->data['billers'] = $this->site->getAllCompanies('biller');
		}
		
		if ($pdf != NULL && $biller_id == NULL) {
            $html = $this->load->view($this->theme . 'reports/cash_books', $this->data, true);
            $name = lang("cash_books") . "_" . date('Y_m_d_H_i_s') . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_cash_books_text") . '</p>', '', $html);
            $this->erp->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
		
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Cash_Books_Report')));
        $meta = array('page_title' => lang('Cash_Books_Report'), 'bc' => $bc);
        $this->page_construct('reports/cash_books', $meta, $this->data);
    
		if($xls){
			
			$styleArray = array(
				'font'  => array(
					'bold'  => true,
					'color' => array('rgb' => '000000'),
					'size'  => 10,
					'name'  => 'Verdana'
				)
			);
			$bold = array(
				'font' => array(
					'bold' => true
				)
			);
			
			$this->load->library('excel');
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($styleArray);
			$this->excel->getActiveSheet()->setTitle(lang('Cash Book Statement'));
			$this->excel->getActiveSheet()->SetCellValue('A1', lang('Batch'));
			$this->excel->getActiveSheet()->SetCellValue('B1', lang('Reference'));
			$this->excel->getActiveSheet()->SetCellValue('C1', lang('Seq'));
			$this->excel->getActiveSheet()->SetCellValue('D1', lang('Description'));
			$this->excel->getActiveSheet()->SetCellValue('E1', lang('Date'));
			$this->excel->getActiveSheet()->SetCellValue('F1', lang('Type'));
			$this->excel->getActiveSheet()->SetCellValue('G1', lang('Debit_Amount'));
			$this->excel->getActiveSheet()->SetCellValue('H1', lang('Credit_Amount'));
			
			$this->excel->getActiveSheet()->getStyle('E2:F2')->applyFromArray($bold);
			$this->excel->getActiveSheet()->getStyle('G2:H2')->applyFromArray($bold);
			
			if ($this->input->post('start_date') || $this->input->post('end_date') || (!$this->input->post('end_date') && !$this->input->post('end_date'))) {
					
				$accounntCode = $this->db;
				$accounntCode->select('*')->from('gl_charts')->where('bank', 1);
				if ($this->input->post('account') ) {
					$accounntCode->where('accountcode', $this->input->post('account'));
				}
				
				$acc = $accounntCode->get()->result();
				
				foreach($acc as $val){
					
					$gl_tranStart = $this->db->select('sum(amount) as startAmount')->from('gl_trans');
					$gl_tranStart->where(array('tran_date < '=> $this->erp->fld($this->input->post('start_date')), 'account_code'=> $val->accountcode));
					$startAmount = $gl_tranStart->get()->row();
					
					$endAccountBalance = 0;
					$getListGLTran = $this->db->select("*")->from('gl_trans')->where('account_code =', $val->accountcode);
					if ($this->input->post('start_date')) {
						$getListGLTran->where('tran_date >=', $this->erp->fld($this->input->post('start_date')) );
					}

					if ($this->input->post('end_date')) {
						$getListGLTran->where('tran_date <=', $this->erp->fld($this->input->post('end_date')) );
					}
					if (!$this->input->post('end_date') && !$this->input->post('end_date'))
					{
						$current_month = date('m');
						$getListGLTran->where('MONTH(tran_date)', $current_month);
					}
					if($biller_id != "" && $biller_id != NULL && $biller_id != 0){
						$getListGLTran->where('biller_id', $biller_id);
					}
					$gltran_list = $getListGLTran->get()->result();
					
					$acc_name = "";
					$start_amount = 0;
					
					if($gltran_list) {
						$acc_name = $val->accountcode . ' ' .$val->accountname;
						$start_amount = $this->erp->formatMoney($startAmount->startAmount);
						
						$this->excel->getActiveSheet()->mergeCells('A2:B2:C2:D2')->setCellValue('A2' , "Account ".$acc_name);
						$this->excel->getActiveSheet()->mergeCells('E2:F2')->setCellValue('E2' , lang('Begining Balance: '));
						$this->excel->getActiveSheet()->mergeCells('G2:H2')->setCellValue('G2' , $start_amount);
					}
					
					
					$row = 3;
					$endAccountBalance = 0;
					foreach($gltran_list as $rw){
						$endAccountBalance += $rw->amount; 
						
						$this->excel->getActiveSheet()->SetCellValue('A'.$row,$rw->tran_id);
						$this->excel->getActiveSheet()->SetCellValue('B'.$row,$rw->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C'.$row,$rw->tran_no);
						$this->excel->getActiveSheet()->SetCellValue('D'.$row,$rw->narrative);
						$this->excel->getActiveSheet()->SetCellValue('E'.$row,$rw->tran_date);
						$this->excel->getActiveSheet()->SetCellValue('F'.$row, $rw->tran_type);
						$this->excel->getActiveSheet()->SetCellValue('G'.$row,($rw->amount > 0 ? $this->erp->formatMoney($rw->amount) : '0.00'));
						$this->excel->getActiveSheet()->SetCellValue('H'.$row,($rw->amount < 1 ? $this->erp->formatMoney(abs($rw->amount)) : '0.00'));
						$row++;
					}
					$this->excel->getActiveSheet()->mergeCells('A'.($row+1).':B'.($row+1).':C'.($row+1).':D'.($row+1));
					$this->excel->getActiveSheet()->mergeCells('E'.($row+1).':F'.($row+1))->setCellValue('E'.($row+1) , lang('Ending Balance: '));
					$this->excel->getActiveSheet()->mergeCells('G'.($row+1).':H'.($row+1))->setCellValue('G'.($row+1) , $endAccountBalance);
						
				}
				
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
				
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$filename = 'Cash_Books_Report' . date('Y_m_d_H_i_s');
				if ($xls) {
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
					header('Cache-Control: max-age=0');

					$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
					return $objWriter->save('php://output');
				}

				redirect($_SERVER["HTTP_REFERER"]);
				
			}
			
		}
	}
    /**********suspend**********/
    function suspends($warehouse_id = NULL){ 
        $this->load->model('reports_model');
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('report'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Room_Reports')));
        $meta = array('page_title' => lang('sale_suspend'), 'bc' => $bc);
        $this->page_construct('reports/room_report', $meta, $this->data);
    }

    function getRoom()
    {
        $this->erp->checkPermissions('sales', true);
		//$this->erp->checkPermissions('index');
        $this->load->library('datatables');
        $this->datatables
            ->select("id,floor,name,ppl_number,description, CASE WHEN status = 0 THEN 'Active' ELSE 'Close' END AS status")
            ->from("erp_suspended")
            ->add_column("Actions", "<center><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/view_room_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></center>", "id");
        echo $this->datatables->generate();
    }

    function view_room_report($room_id = NULL, $year = NULL, $month = NULL, $pdf = NULL, $cal = 0)
    {

        if (!$room_id) {
            $this->session->set_flashdata('error', lang("no_room_selected"));
            redirect('reports/suspends');
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['purchases'] = $this->reports_model->getRoomPurchases($room_id);
        $this->data['sales'] = $this->reports_model->getRoomSales($room_id);
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        if (!$year) {
            $year = date('Y');
        }
        if (!$month || $month == '#monthly-con') {
            $month = date('m');
        }
        if ($pdf) {
            if ($cal) {
                $this->monthly_sales($year, $pdf, $room_id);
            } else {
                $this->daily_sales($year, $month, $pdf, $room_id);
            }
        }
        $config = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => site_url('reports/view_room_report/'.$room_id),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable">{/table_open}
        {heading_row_start}<tr>{/heading_row_start}
        {heading_previous_cell}<th class="text-center"><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
        {heading_title_cell}<th class="text-center" colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
        {heading_next_cell}<th class="text-center"><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
        {heading_row_end}</tr>{/heading_row_end}
        {week_row_start}<tr>{/week_row_start}
        {week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
        {week_row_end}</tr>{/week_row_end}
        {cal_row_start}<tr class="days">{/cal_row_start}
        {cal_cell_start}<td class="day">{/cal_cell_start}
        {cal_cell_content}
        <div class="day_num">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content}
        {cal_cell_content_today}
        <div class="day_num highlight">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content_today}
        {cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
        {cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
        {cal_cell_blank}&nbsp;{/cal_cell_blank}
        {cal_cell_end}</td>{/cal_cell_end}
        {cal_row_end}</tr>{/cal_row_end}
        {table_close}</table>{/table_close}';

        $this->load->library('calendar', $config);
        $sales = $this->reports_model->getRoomDailySales($room_id, $year, $month);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $daily_sale[$sale->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->erp->formatMoney($sale->discount) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->erp->formatMoney($sale->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->erp->formatMoney($sale->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->erp->formatMoney($sale->total) . "</td></tr></table>";
            }
        } else {
            $daily_sale = array();
        }
        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        if ($this->input->get('pdf')) {

        }
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        $this->data['msales'] = $this->reports_model->getRoomMonthlySales($room_id, $year);
        $this->data['user_id'] = $room_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('View_Room_report')));
        $meta = array('page_title' => lang('View_Room_report'), 'bc' => $bc);
        $this->page_construct('reports/view_room_report', $meta, $this->data);

    }
	
    function daily_purchases($warehouse_id = NULL, $year = NULL, $month = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->erp->checkPermissions('purchases', true);
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $config = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => site_url('reports/daily_purchases/'.($warehouse_id ? $warehouse_id : 0)),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable">{/table_open}
        {heading_row_start}<tr>{/heading_row_start}
        {heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
        {heading_title_cell}<th colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
        {heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
        {heading_row_end}</tr>{/heading_row_end}
        {week_row_start}<tr>{/week_row_start}
        {week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
        {week_row_end}</tr>{/week_row_end}
        {cal_row_start}<tr class="days">{/cal_row_start}
        {cal_cell_start}<td class="day">{/cal_cell_start}
        {cal_cell_content}
        <div class="day_num">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content}
        {cal_cell_content_today}
        <div class="day_num highlight">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content_today}
        {cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
        {cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
        {cal_cell_blank}&nbsp;{/cal_cell_blank}
        {cal_cell_end}</td>{/cal_cell_end}
        {cal_row_end}</tr>{/cal_row_end}
        {table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $purchases = $user_id ? $this->reports_model->getStaffDailyPurchases($user_id, $year, $month, $warehouse_id) : $this->reports_model->getDailyPurchases($year, $month, $warehouse_id);

        if (!empty($purchases)) {
            foreach ($purchases as $purchase) {
                $daily_purchase[$purchase->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->erp->formatMoney($purchase->discount) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->erp->formatMoney($purchase->tax1) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->erp->formatMoney($purchase->total) . "</td></tr></table>";
            }
        } else {
            $daily_purchase = array();
        }

        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_purchase);
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/daily', $this->data, true);
            $name = lang("daily_purchases") . "_" . $year . "_" . $month . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->erp->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_purchases_report')));
        $meta = array('page_title' => lang('daily_purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/daily_purchases', $meta, $this->data);
    }


    function monthly_purchases($warehouse_id = NULL, $year = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->erp->checkPermissions('purchases', true);
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->load->language('calendar');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['year'] = $year;
        $this->data['purchases'] = $user_id ? $this->reports_model->getStaffMonthlyPurchases($user_id, $year, $warehouse_id) : $this->reports_model->getMonthlyPurchases($year, $warehouse_id);
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('monthly_purchases_report')));
		if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/monthly_purchases', $this->data, true);
            $name = lang("monthly_purchases") . "_" . $year . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_monthly_text") . '</p>', '', $html);
            $this->erp->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $meta = array('page_title' => lang('monthly_purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/monthly_purchases', $meta, $this->data);

    }

    function saleman()
    {
        $this->erp->checkPermissions('sales', true);
        $this->load->model('reports_model');
         
        if(isset($_GET['d']) != ""){
            $date = $_GET['d'];
            $this->data['date'] = $date;
        }

        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => 'reports', 'page' => lang('reports')));
        $meta = array('page_title' => lang('Saleman'), 'bc' => $bc);
        $this->page_construct('reports/saleman', $meta, $this->data);
    }

    function getSalemans($warehouse_id = NULL)
    {
        
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }

        if ((! $this->Owner || ! $this->Admin) && ! $warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $cabon_print = anchor('sales/cabon_print/$1', '<i class="fa fa-print"></i> ' . lang('print_cabon'), 'target="_blank"');
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $cabon_print . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        $where = "";
        if ($start_date) {
            $where = ' AND ' . $this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"';
        }
        
        $this->datatables
            ->select("username, username, phone, (SELECT sum(total) FROM sales s WHERE s.saleman_by = u.id $where) as sale_amount, (SELECT sum(paid) FROM sales s WHERE s.saleman_by = u.id ) as sale_amount, ((SELECT sum(total) - sum(paid)) FROM sales s WHERE s.saleman_by = u.id $where) as balance")
            ->from('users u');

        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	function warehouse_reports_action(){
		 if(!empty($_POST['val'])){ 
                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf'){

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Brand_Sales_Daily'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code')); 
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name')); 
					 
					 $rowNumberH = 1;
					 $colH = 'C';
					 $warefull = $this->reports_model->getWareFull();
					 $total = count($warefull); 
					 foreach($warefull as $h){
							$this->excel->getActiveSheet()->setCellValue($colH.$rowNumberH,$h->name);
							$colH++;    
					 } 
					 
					$this->excel->getActiveSheet()->SetCellValue(($colH).'1', lang('total'));
                    $row = 2;
					$total_qty=0;  
                    foreach($_POST['val'] as $id){ 
					    $str = "";
						 
						if($this->input->get('category')){
							$category = $this->input->get('category');
							$str .="&category=".$category;
						}else{
							$category = 0;
						} 
					  
					 $colH = 'C';
                     $products_details = $this->reports_model->getAllProductsDetails($id,$category);
                      foreach($products_details as $pro){ 
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pro->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pro->name);
						 foreach($warefull as $w){
							$qty = $this->reports_model->getQtyByWare($pro->id,$w->id,$id,$category);
						    $tt_qty[$w->id] += $qty->wqty;
							$tt_qty_p[$pro->id] += $qty->wqty;
							$this->excel->getActiveSheet()->setCellValue($colH.$row,$qty->wqty);
							$colH++;
						 }  
                        $this->excel->getActiveSheet()->SetCellValue($colH.$row, $tt_qty_p[$pro->id]);$row++; 	
                        $total_q+=$tt_qty_p[$pro->id];						
					  } 
					  
					  $this->excel->getActiveSheet()->mergeCells("A".$row.":B".$row);
					  $this->excel->getActiveSheet()->SetCellValue("A" .$row, lang("total")); 
					  $colH1="C";
					  foreach($warefull as $w){
							$this->excel->getActiveSheet()->setCellValue($colH1.$row,$tt_qty[$w->id]);
							$colH1++;    
					  } 
					  $this->excel->getActiveSheet()->setCellValue($colH1.$row,$total_q);
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'purchases_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            } 
	}
	
	function warehouse_reports()
    {
		$this->load->library("pagination");
		$str = "";
        if($this->input->get('product')){
            $product = $this->input->get('product');
			$str .="&product=".$product;
        }else{
			$product = 0;
        }    
		if($this->input->get('category')){
            $category = $this->input->get('category');
			$str .="&category=".$category;
        }else{
			$category = 0;
        } 

		$row_nums = $this->reports_model->getAllProductsDetailsNUM($product,$category);
		
		$config = array();
		$config['suffix'] = "?v=1".$str;
		$uri = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $config["base_url"] = site_url("reports/warehouse_reports/") ;
		$config["total_rows"] = $row_nums;
		$config["ob_set"] = $uri;
        $config["per_page"] =5; 
		$config["uri_segment"] = 3;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm">';
		$config['full_tag_close'] = '</ul>';
		$config['next_tag_open'] = '<li class="next">';
		$config['next_tag_close'] = '</li>';
		$config['prev_tag_open'] = '<li class="prev">';
		$config['prev_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="active"><a>';
		$config['cur_tag_close'] = '</a></li>';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$this->pagination->initialize($config);
		$this->data["pagination"] = $this->pagination->create_links();  
		$this->data['products_details']     = $this->reports_model->getAllProductsDetails($product,$category,$config['per_page'],$config["ob_set"]);
		$this->data['warefull'] = $this->reports_model->getWareFull();
		$this->data['category2']    = $category;
		$this->data['product2'] 	  	= $product;
		$this->data['from_date2']    = trim($from_date);
		$this->data['to_date2'] 	  	= trim($to_date);
		$this->data['warehouse2'] 	  	= $warehouse;
		$this->data['categories'] = $this->site->getAllCategories();
		$this->data['products']     = $this->reports_model->getAllProducts();
		
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('warehouse_products'), 'bc' => $bc);
        $this->page_construct('reports/warehouse_products', $meta, $this->data);
    }
	
	function warehouse_reportssss(){
		$this->erp->checkPermissions('products', true);
        if ($this->input->post('product')) {
            $product = $this->input->post('product');
        } else {
            $product = NULL;
        }
        
        if ($this->input->post('category')) {
            $category = $this->input->post('category');
        } else {
            $category = NULL;
        }
		
        if ($this->input->post('start_date')) {
            $start_date = $this->input->post('start_date');
        } else {
            $start_date = NULL;
        }
		
        if ($this->input->post('end_date')) {
            $end_date = $this->input->post('end_date');
        } else {
            $end_date = NULL;
        }
		
		if ($this->input->post('supplier')) {
            $supplier = $this->input->post('supplier');
        } else {
            $supplier = NULL;
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
		$this->data['suppliers'] = $this->site->getAllSuppliers();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $this->data['warehouse'] = $this->reports_model->getAllWarehouses();
		$this->data['wreport'] = $this->reports_model->getReportW($product, $category, $supplier, $start_date, $end_date); 
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('warehouse_products')));
        $meta = array('page_title' => lang('warehouse_products'), 'bc' => $bc);
		
        $this->page_construct('reports/warehouse_products', $meta, $this->data);
	}
	
	function getwarehousereports($pdf = NULL, $xls = NULL){
		
        $this->erp->checkPermissions('products', TRUE);
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        
        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
        if ($this->input->get('in_out')) {
            $in_out = $this->input->get('in_out');
        } else {
            $in_out = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
		if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
		if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
			$where_sale='where si.warehouse_id='.$warehouse;
			$where_purchase="where {$this->db->dbprefix('purchase_items')}.warehouse_id=".$warehouse . "AND {$this->db->dbprefix('purchase_items')}.status <> 'ordered'";
        } else {
            $warehouse = NULL;
			$where_purchase = "where 1=1 AND {$this->db->dbprefix('purchase_items')}.status <> 'ordered' AND {$this->db->dbprefix('purchase_items')}.purchase_id != ''";
			$where_sale='where 1=1';
        }
        $ware = $this->reports_model->getAllWarehouses();
		if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $end_date ? $this->erp->fld($end_date) : date('Y-m-d');

            $pp = "( SELECT pi.product_id, 
						SUM( pi.quantity * (CASE WHEN pi.option_id <> 0 THEN pi.vqty_unit ELSE 1 END) ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM((CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
                        SUM(pi.unit_cost) AS totalCost,
						SUM(pi.quantity) AS Pquantity,
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity 
									FROM erp_purchase_items 
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									WHERE {$this->db->dbprefix('purchase_items')}.date >= '{$start_date}' AND {$this->db->dbprefix('purchase_items')}.date < '{$end_date}' 
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id 
						GROUP BY pi.product_id ) PCosts";

			$sp = "( SELECT si.product_id, 
						SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)) soldQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						WHERE s.date >= '{$start_date}' AND s.date < '{$end_date}' 
						GROUP BY si.product_id ) PSales";

			$ppb = "( SELECT pi.product_id, 
						SUM( pi.quantity ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity 
									FROM erp_purchase_items 
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									WHERE {$this->db->dbprefix('purchase_items')}.date < '{$start_date}'
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCostsBegin";
            
			$spb = "( SELECT si.product_id, 
						SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)) saleQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						WHERE s.date < '{$start_date}'
						GROUP BY si.product_id ) PSalesBegin";
        } 
		else {
			$current_date = date('Y-m-d');
			$prevouse_date = date('Y').'-'.date('m').'-'.'01';
			$pp = "( SELECT pi.product_id, 
						SUM( pi.quantity * (CASE WHEN pi.option_id <> 0 THEN pi.vqty_unit ELSE 1 END) ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
                        SUM(pi.unit_cost) AS totalCost,
						SUM(pi.quantity) AS Pquantity,
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost ,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity
									FROM {$this->db->dbprefix('purchase_items')} 
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									".$where_purchase." 
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 			
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} GROUP BY product_id 
									) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCosts";

			$sp = "( SELECT si.product_id, 
						COALESCE(SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)),0) soldQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						".$where_sale."
						GROUP BY si.product_id ) PSales";

			
			$ppb = "( SELECT pi.product_id, 
						SUM(pi.quantity) AS purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) * tpi.quantity_balance ) balacneValue, 
						SUM(pi.unit_cost * pi.quantity) totalPurchase, 
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost ,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity
									FROM {$this->db->dbprefix('purchase_items')} 
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									".$where_purchase." 
									AND {$this->db->dbprefix('purchase_items')}.date < '{$prevouse_date}' 
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 			
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCostsBegin";
			
            $spb = "( SELECT si.product_id, 
						COALESCE(SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)),0) saleQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						".$where_sale."
						AND s.date < '{$prevouse_date}'
						GROUP BY si.product_id ) PSalesBegin";
        }
		
        if ($pdf || $xls) {
            $this->db->query('SET SQL_BIG_SELECTS=1');
            $this->db
                ->select($this->db->dbprefix('products') . ".code, supplier1, " . $this->db->dbprefix('products') . ".name,
				COALESCE( PCosts.purchasedQty, 0 ) as PurchasedQty,
				COALESCE( PSales.soldQty, 0 ) + COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
                                erp_combo_items ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    ) as SoldQty,
				PWarehouse.quantity_balance,
				COALESCE (COALESCE (
						PCostsBegin.totalPurchase - PSalesBegin.totalSale,
						0
					)+COALESCE (PCosts.purchasedQty, 0)-COALESCE (PSales.soldQty, 0)- COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
                                erp_combo_items ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    )) as BalacneQty", FALSE)
                ->from('products')
				->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
				->join($spb, 'products.id = PSalesBegin.product_id', 'left')
                ->join($ppb, 'products.id = PCostsBegin.product_id', 'left')
				->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				->join('categories', 'products.category_id=categories.id', 'left')
				->group_by("products.id");

			if ($supplier) {
                $this->datatables->where("products.supplier1 = '".$supplier."' or products.supplier2 = '".$supplier."' or products.supplier3 = '".$supplier."' or products.supplier4 = '".$supplier."' or products.supplier5 = '".$supplier."'");
            }else{
				$this->db->where("COALESCE( PCosts.purchasedQty, 0 ) > 0 OR COALESCE( PSales.soldQty, 0 ) > 0");
			}
			
            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			
			if ($warehouse) {
                $this->db->where('wp.warehouse_id', $warehouse);
                $this->db->where('wp.quantity !=', 0);
            }
            
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('balance'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $bQty = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->BalacneQty);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $bQty += $data_row->BalacneQty;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":I" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $bQty);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);

                $filename = 'products_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } 
		else {
			$detail_sale = anchor('reports/view_sale_detail_in_out/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Sale_detail'), 'data-toggle="modal" data-target="#myModal"');
			$detail_purchase = anchor('reports/view_purchase_detail_in_out/$1', '<i class="fa fa-file-text-o"></i> ' . lang('Purchase_detail'), 'data-toggle="modal" data-target="#myModal"');
					
			$action = '<div class="text-center"><div class="btn-group text-left">'
			. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
			. lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_purchase . '</li>
				<li>' . $detail_sale . '</li>					
			<ul>
			</div></div>';
            $this->load->library('datatables');
            $this->db->query('SET SQL_BIG_SELECTS=1');
            $this->datatables
                ->select($this->db->dbprefix('products') . ".code as product_code, 
				" . $this->db->dbprefix('products') . ".name,
				CONCAT(COALESCE( PCostsBegin.purchasedQty-PSalesBegin.saleQty, 0 ), '__', COALESCE( PCostsBegin.totalPurchase-PSalesBegin.totalSale, 0 )) as BeginPS,
				CONCAT(
                    COALESCE (PCosts.Pquantity, 0),
                    '__',
                    COALESCE (
                        PCosts.totalCost,
                        0
                    )) AS purchased,
				COALESCE( PSales.Squantity, 0 ) + COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
                                ".$this->db->dbprefix('combo_items') . " ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    ) as sold,
				COALESCE (COALESCE (
						PCostsBegin.purchasedQty-PSalesBegin.saleQty,
						0
					)+COALESCE (PCosts.Pquantity, 0) - COALESCE( PSales.Squantity , 0 ) -  COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
								".$this->db->dbprefix('combo_items') . " ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    ) ) AS balance", 
				FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
				->join($spb, 'products.id = PSalesBegin.product_id', 'left')
                ->join($ppb, 'products.id = PCostsBegin.product_id', 'left')
				->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				->join('categories', 'products.category_id=categories.id', 'left')
				->group_by("products.id");
            
			if ($supplier) {
				$this->datatables->where("products.supplier1 = '".$supplier."' or products.supplier2 = '".$supplier."' or products.supplier3 = '".$supplier."' or products.supplier4 = '".$supplier."' or products.supplier5 = '".$supplier."'");
            }else{
				//$this->datatables->where("COALESCE( PCosts.purchasedQty, 0 ) > 0 OR COALESCE( PSales.soldQty, 0 ) > 0");
			}
			
            if($in_out){
                if($in_out == 'in'){
                    $this->datatables->order_by('PCosts.purchasedQty', 'DESC');
                }else if($in_out == 'out'){
                    $this->datatables->order_by('PSales.soldQty', 'DESC');
                }
            }
            
            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
			
			if ($warehouse) {
                $this->datatables->where('wp.warehouse_id', $warehouse);
                $this->datatables->where('wp.quantity !=', 0);
            }


			$this->datatables->add_column("Actions", $action, "product_code");
            echo $this->datatables->generate();

        }
    
	}
	
	function in_out_actions(){
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('product_in_out'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('begin'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('in'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('out'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('balance'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getInOutByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->product_code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->BeginPS);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->purchased);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->sold);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->balance);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'product_in_out_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function room_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('room_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('floor'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('room_number'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('people'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('description'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getRoomByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->floor);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->ppl_number);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->description);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'room_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function saleman_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('saleman_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('sale_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('saleman_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('amount'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('balance'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						
                        $sc = $this->reports_model->getSalemanByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->username);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->username);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->phone);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->sale_amount);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->sale_paid);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->balance);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'saleman_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function purchases_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('purchases_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('supplier'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('product'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('quantity'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('balacne'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						
                        $sc = $this->reports_model->getPurchasesByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->date);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->wname);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->supplier);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->iname);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->iqty);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $sc->grand_total);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $sc->paid);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $sc->balance);
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $sc->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'purchases_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function payments_actions($id){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('payments_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('suspend'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('payment_ref'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('sale_ref'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('purchases_ref'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid_by'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('amount'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('type'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getPaymentsByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->noted);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->date);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->payment_ref);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->sale_ref);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->purchase_ref);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->note);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $sc->paid_by);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $sc->amount);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $sc->type);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'payments_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function profit_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sale_profit_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('suspend'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('shop'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('cost'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('profits'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('payment_status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getProfitByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->date);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->suspend_note);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->biller);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->customer);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $sc->paid);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $sc->balance);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $sc->total_cost);
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $sc->profit);
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, $sc->payment_status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sale_profit_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function shops_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('shops_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('email_address'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('total_sales'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('total_earned'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getShopsByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->phone);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->total);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->total_amount);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $sc->total_earned);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $sc->paid);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $sc->balance);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'shops_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function suppliers_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('suppliers_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('email_address'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('total_purchases'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getSupplierByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->phone);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->total);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->total_amount);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sc->paid);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $sc->balance);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'suppliers_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function customers_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customers_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('email_address'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('total_purchases'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getCustomersByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->phone);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->total);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->total_amount);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sc->paid);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $sc->balance);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'customers_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function sales_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('shop'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('product'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('quantity'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));

                    $row = 2;
					$total_qty = 0;
					$total = 0;
					$paid = 0;
					$balance = 0;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getSalesExportByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->date);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->biller);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->iname);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->iqty);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sc->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $sc->paid);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $sc->balance);
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $sc->payment_status);
						$total_qty += $sc->total_qty;
						$total += $sc->grand_total;
						$paid += $sc->paid;
						$balance += ($sc->grand_total - $sc->paid);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getStyle("F" . $row . ":I" . $row)->getBorders()
						->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $total_qty);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $total);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $paid);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $balance);

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				
				
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sales_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
						$styleArray = array(
							'borders' => array(
								'allborders' => array(
									'style' => PHPExcel_Style_Border::BORDER_THIN
								)
							)
						);
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->applyFromArray(
						 array(
							 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
						 ));
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
						$this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
						$this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->applyFromArray(
						 array(
							 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
							 'wrap'       => true
						 ));
						ob_clean();
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function categories_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('categories_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('category_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('category_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('purchase_amount'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('sold_amount'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('profit_loss'));

                    $row = 2;
					$sQty = 0;
					$pQty = 0;
					$sAmt = 0;
					$pAmt = 0;
					$pl = 0;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getCategoryByID($id);
						//$this->erp->print_arrays($sc);
						$profit = $sc->TotalSales - $sc->TotalPurchase;
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->PurchasedQty);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->SoldQty);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->TotalPurchase);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->TotalSales);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sc->Profit);
						$pQty += $sc->PurchasedQty;
						$sQty += $sc->SoldQty;
						$pAmt += $sc->TotalPurchase;
						$sAmt += $sc->TotalSales;
						$pl += $profit;
					
                        $row++;
                    }
					
					$this->excel->getActiveSheet()->getStyle("C" . $row . ":G" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $pAmt);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $sAmt);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $pl);

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'categories_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function item_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('supplier_by_items_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('email_address'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('total_purchases'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getSupplierByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->phone);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->total);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->total_amount);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sc->paid);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $sc->balance);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'supplier_by_items_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function warehouse_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('warehouse_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('WH1'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('WH2'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('total'));
					
                    $row = 2;
					$ware = $this->reports_model->getAllWarehouses();
					$alphabet = array('C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getWarehouseByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
						$total_wh_amount = 0;
						$i = 0;
						foreach($ware as $warehouse){
							$this->db->select('SUM(quantity_balance) as qb');
							$this->db->from('purchase_items');
							$this->db->where(array('warehouse_id'=>$warehouse->id, 'product_id'=>$sc->id));
							$q = $this->db->get();
							if ($q->num_rows() > 0) {
								foreach ($q->result() as $rows) {
									$this->excel->getActiveSheet()->SetCellValue($alphabet[$i] . $row, number_format($rows->qb,2));
									$total_wh_amount += $row->qb;
								}
							}
							$i++;
						}
                        $this->excel->getActiveSheet()->SetCellValue($alphabet[$i] . $row, $sc->quantity);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'warehouse_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function products_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('purchase_loss'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('stock_in_hand'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getProductByID($id);
						//$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, '('.number_format($sc->qpurchase,0).') '. number_format($sc->ppurchased,2));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, '('.number_format($sc->qsale,0).') '. number_format($sc->psold,2));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->Profit);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, '('.number_format($sc->qbalance,0).') '. number_format($sc->pbalance,2));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'products_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
    function quantity_actions(){
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('quantity_alerts_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('alert_quantity'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getQuantityByID($id);
                        //$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->quantity);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->alert_quantity);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'quantity_alerts_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function register_actions(){
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('register_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('open_time'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('close_time'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('users'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('cash_in_hand'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('cc_slips'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('cheques'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('total_cash'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('note'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getRegisterByID($id);
                        //$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->date);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->closed_at);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->user);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->cash_in_hand);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->c_slips);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->cheques);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sc->cash);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $sc->note);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'register_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	function income_actions(){
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('quantity_alerts_report'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('alert_quantity'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->reports_model->getQuantityByID($id);
                        //$this->erp->print_arrays($sc);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->quantity);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->alert_quantity);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'quantity_alerts_report_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	function brands()
	{
		$this->erp->checkPermissions('purchases', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		/*$search_term = array(
		'cate' => $this->input->post('category'),
		'd' => $this->input->post('start_date'),
		'e' => $this->input->post('end_date')
		);*/

		
		$this->data['brand'] =$this->reports_model->getbrand();
		//$this->data['brands'] =$this->reports_model->getAllbrand($search_term);
		$this->data['categories'] = $this->site->getAllCategories();
		$this->data['Allbrand'] =$this->reports_model->searchbrand();
		$this->data['subcategories'] = $this->reports_model->getsubcategoires();
		$this->data['products'] =$this->reports_model->getProduct();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('brands_report')));
        $meta = array('page_title' => lang('brands_report'), 'bc' => $bc);
        $this->page_construct('reports/brands', $meta, $this->data);
	}
	function getSalesReportDetail($start_date = NULL, $end_date = NULL, $biller_id = NULL){		
		
		$this->load->library("pagination");
		if($this->input->get('reference_no')){
			 $reference_no = $this->input->get('reference_no');
			 $str.="&reference_no=".$reference_no;
			 $this->data['reference_no'] =$reference_no;
		}else{
			 $reference_no =null;
		} 
		if($this->input->get('customer')){
			 $customer = $this->input->get('customer');
			 $str .="&customer=".$customer;
			 $this->data['customer'] =$customer;
		}else{
			 $customer =null;
		} 
		if($this->input->get('warehouse')){
			 $warehouse = $this->input->get('warehouse');
			 $str .="&warehouse=".$warehouse;
			 $this->data['warehouse'] =$warehouse;
		}else{
			 $biller =null;
		} 
        if($this->input->get('biller')){
			 $biller = $this->input->get('biller');
			 $str .="&biller=".$customer;
			 $this->data['biller'] =$customer;
		}else{
			 $biller =null;
		}		
		if($this->input->get('type')){
			 $type = $this->input->get('type');
			 $str .="&type=".$type;
			 $this->data['type'] =$type;
		}else{
			 $type =null;
		}
		if($this->input->get('types')){
			 $types = $this->input->get('types');
			 $str .="&types=".$types;
			 $this->data['types'] =$types;
		}else{
			 $types =null;
		}
		if($this->input->get('user')){
			 $user = $this->input->get('user');
			 $str .="&user=".$user;
			 $this->data['user'] =$user;
		}else{
			 $user =null; 
		}
		if($this->input->get('start_date')){
			 $start_date = $this->input->get('start_date');
			 $str .="&start_date=".$start_date;
			 $this->data['start_date'] =$start_date;
		}else{
			 $start_date =null; 
		}
		if($this->input->get('end_date')){
			 $end_date = $this->input->get('end_date');
			 $str .="&end_date=".$end_date;
			 $this->data['end_date'] =$end_date;
		}else{
			 $end_date =null; 
		}
		$this->db->select('erp_sales.*'); 
		if(isset($reference_no) !=''){
			$this->db->where("erp_sales.reference_no",$reference_no);
		}
		if(isset($customer)!=''){
			$this->db->where("erp_sales.customer_id",$customer);
		}
		if(isset($warehouse)!=''){
			$this->db->where("erp_sales.warehouse_id",$warehouse);
		}
		if(isset($biller)!=''){
			$this->db->where("erp_sales.biller_id",$biller);
		}
		if(isset($user)!=''){
			$this->db->where("erp_sales.created_by",$user);
		}
		if(isset($start_date)!=''){
			$this->db->where("erp_sales.date BETWEEN '$start_date' AND '$end_date'");
		}
		$this->db->group_by('reference_no');
		$sales_nums = $this->db->get('sales')->num_rows(); 
		
		$config = array();
		$config['suffix'] = "?v=1".$str;
        $config["base_url"] = base_url() . "reports/getSalesReportDetail/";
		$config["total_rows"] = $sales_nums;
		$config["ob_set"] = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $config["per_page"] = 40; 
		$config["uri_segment"] = 3;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm">';
		$config['full_tag_close'] = '</ul>';
		$config['next_tag_open'] = '<li class="next">';
		$config['next_tag_close'] = '<li>';
		$config['prev_tag_open'] = '<li class="prev">';
		$config['prev_tag_close'] = '<li>';
		$config['cur_tag_open'] = '<li class="active"><a href="#">';
		$config['cur_tag_close'] = '</a><li>';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '<li>';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '<li>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		
		$sale =(
		"SELECT 
		erp_sales.id,
		1 as type,
		erp_sales.date,
		erp_sales.return_id,
		erp_sales.reference_no,
		erp_sales.customer,
		erp_sales.customer_id,
		erp_warehouses.name as warehouse,
		erp_sales.warehouse_id,
		erp_sales.order_discount,
		erp_sales.created_by,
		erp_sales.pos
		From erp_sales
		LEFT JOIN erp_warehouses ON erp_warehouses.id=erp_sales.warehouse_id 
		"); 
		
		$return =("Select
         erp_return_sales.id,
         2 as type,		 
		 erp_return_sales.date,
         erp_return_sales.sale_id,	 
		 erp_return_sales.reference_no,
		 erp_return_sales.customer,
		 erp_return_sales.customer_id,
		 erp_warehouses.name as warehouse,
		 erp_return_sales.warehouse_id,
		 erp_return_sales.order_discount,
		 erp_return_sales.created_by,
		 0 as pos
		 From erp_return_sales
		 LEFT JOIN erp_warehouses ON erp_warehouses.id=erp_return_sales.warehouse_id 
		");
		$sql="";
		if($this->data['reference_no']){
			$sql .="AND reference_no = '{$this->data['reference_no']}'";
		}
		if($this->data['customer']){
			$sql .="AND customer_id = '{$this->data['customer']}'";
		}
		if($this->data['biller']){
			$sql .="AND biller_id ='{$this->data['biller']}'";
		}
		if($this->data['user']){
			$sql .="AND created_by = '{$this->data['user']}'";
		}
		if($this->data['type'] !=''){
			$sql .= " AND type = {$this->data['type']}";
		} 
		 
		if($this->data['types'] !=''){
			$sql .= " AND pos = {$this->data['types']}";
		}
	    $sales=$this->db->query("Select *from ({$sale} UNION {$return}) as temp Where 1=1 {$sql} ORDER BY id DESC
									LIMIT {$config['ob_set']},{$config['per_page']}")->result();
		
		$this->pagination->initialize($config);
		$this->data["pagination"] = $this->pagination->create_links();
		$data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['sales'] = $sales;
		$this->data['categories'] = $this->reports_model->getCategoryName($category_name,$product_name,$start,$end,$biller_id);	
		$this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);	
		 $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
		$this->data['products'] = $this->reports_model->getProductName(); 
		$this->data['warehouse'] = $this->reports_model->getAllWarehouses();
		$this->data['saleItemsWarehouse'] = $this->reports_model->getAllSaleIemsWarehouses();
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['biller_id'] = $biller_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('sales_report_detail')));
        $meta = array('page_title' => lang('sales_report_detail'), 'bc' => $bc);
        $this->page_construct('reports/sales_report_detail', $meta, $this->data);
		
	}
	
	function getSalesReportDetails($start_date = NULL, $end_date = NULL, $biller_id = NULL) 
	{
		
		$this->erp->checkPermissions('detail',NULL,'sale_report');
		$start = "";
		$end = "";
		//$this->erp->print_arrays($start_date, $end_date, $biller_id)
		if (!$start_date) {
            //$start = $this->db->escape(date('Y-m') . '-1');
           // $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            //$end = $this->db->escape(date('Y-m-d H:i'));
            //$end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        } 
		
		if ($this->input->post('category_name')) {
            $category_name = $this->input->post('category_name');
			
        } else {
            $category_name = NULL;
        }
		if ($this->input->post('product_name')) {
            $product_name = $this->input->post('product_name');			
        } else {
            $product_name = NULL;
        }
		if ($this->input->post('supplier')) {
            $supplier_name = $this->input->post('supplier');
        } else {
            $supplier_name = NULL;
        }
		
		
		$data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['cate'] = $this->reports_model->getCategory();
		$this->data['categories'] = $this->reports_model->getCategoryName($category_name,$product_name,$start,$end,$biller_id);	
		$this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);	
		$this->data['products'] = $this->reports_model->getProductName(); 
		$this->data['warehouse'] = $this->reports_model->getAllWarehouses();
		$this->data['saleItemsWarehouse'] = $this->reports_model->getAllSaleIemsWarehouses();
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['biller_id'] = $biller_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('sales_report_detail')));
        $meta = array('page_title' => lang('sales_report_detail'), 'bc' => $bc);
        $this->page_construct('reports/sales_report_detail', $meta, $this->data);
		
	}
	
	function stock_movement()
	{
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['brand'] =$this->reports_model->getbrand();
		
		if($this->input->post('submit_report')){
			$warehouse=$this->input->post('warehouses');
			$item=$this->input->post('item');
			$this->data['search_term'] =$this->reports_model->search($term,$item);		 
		}
		  
		$this->data['warehouses'] =$this->reports_model->getwarehouseName();
		
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('brands_report')));
        $meta = array('page_title' => lang('brands_report'), 'bc' => $bc);
        $this->page_construct('reports/stock_movement', $meta, $this->data);
	}
	
	function getbrandReport(){
		
	   $this->erp->checkPermissions('products', TRUE);
	    if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
		
		if ($this->input->get('brand')) {
            $brand = $this->input->get('brand');
        } else {
            $brand = NULL;
        }
		
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
		if ($this->input->get('subcategories')) {
            $subcategories = $this->input->get('subcategories');
        } else {
            $subcategories = NULL;
        }
		
		if ($this->input->get('product_id')) {
            $product_id = $this->input->get('product_id');
        } else {
            $product_id = NULL;
        }
		if ($start_date) {
           // $start_date = $this->erp->fld($start_date);
            //$end_date = $end_date ? $this->erp->fld($end_date) : date('Y-m-d');
           
			$this->load->library('datatables');
			$this->datatables
				 ->select("
				 sales.date,
				 categories.name as d,
				 subcategories.name as c,
				 products.name as e,
				 serial.serial_number,
				 sale_items.quantity,
				 sale_items.unit_price,
				 sale_items.subtotal")
				 ->from('erp_sale_items')
				 ->join('erp_products','erp_sale_items.product_id=erp_products.id','LEFT')
				 ->join('erp_categories','erp_categories.id=erp_products.category_id','LEFT')
				 ->join('erp_subcategories','erp_subcategories.id = erp_products.subcategory_id','LEFT')
				 ->join('erp_brands','erp_brands.id=erp_products.brand_id','LEFT')
				 ->join('erp_serial','erp_serial.id = erp_sale_items.serial_no','LEFT')
				 ->join('erp_sales','erp_sales.id=erp_sale_items.sale_id','LEFT')
				 ->where('date BETWEEN "'. $start_date. '" and "'.$end_date.'"');
		}else{
			$this->load->library('datatables');
			$this->datatables
				 ->select("
				 sales.date,
				 categories.name as d,
				 subcategories.name as c,
				 products.name as e,
				 serial.serial_number,
				 sale_items.quantity,
				 sale_items.unit_price,
				 sale_items.subtotal")
				 ->from('erp_sale_items')
				 ->join('erp_products','erp_sale_items.product_id=erp_products.id','LEFT')
				 ->join('erp_categories','erp_categories.id=erp_products.category_id','LEFT')
				 ->join('erp_subcategories','erp_subcategories.id = erp_products.subcategory_id','LEFT')
				 ->join('erp_brands','erp_brands.id=erp_products.brand_id','LEFT')
				 ->join('erp_serial','erp_serial.id = erp_sale_items.serial_no','LEFT')
				 ->join('erp_sales','erp_sales.id=erp_sale_items.sale_id','LEFT');
			
		    }
			if ($category) {
                $this->db->where($this->db->dbprefix('categories') . ".id", $category);
            }
			if ($subcategories) {
                $this->db->where($this->db->dbprefix('subcategories') . ".id", $subcategories);
            }
			if($brand){
			    $this->db->where($this->db->dbprefix('brands') . ".id",$brand);
			}
			if($product_id){
				$this->db->where($this->db->dbprefix('products') . ".id",$product_id);
				
			}
			  echo $this->datatables->generate();
	}
	
	function brand_reports()
	{
        $this->load->library("pagination");
        $this->data['brands'] = $this->reports_model->getbrands();

		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('brand_reports'), 'bc' => $bc);
        $this->page_construct('reports/brand_reports', $meta, $this->data);
	}

    function getbrandReport2($warehouse_id = NULL){

        $this->erp->checkPermissions('products', TRUE);
        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }

        if ($this->input->get('brand')) {
            $brand = $this->input->get('brand');
        } else {
            $brand = NULL;
        }

        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($this->input->get('subcategories')) {
            $subcategories = $this->input->get('subcategories');
        } else {
            $subcategories = NULL;
        }

        if ($this->input->get('product_id')) {
            $product_id = $this->input->get('product_id');
        } else {
            $product_id = NULL;
        }
        if ($start_date) {
            // $start_date = $this->erp->fld($start_date);
            //$end_date = $end_date ? $this->erp->fld($end_date) : date('Y-m-d');

            $this->erp->checkPermissions('index');

            if ((! $this->Owner || ! $this->Admin) && ! $warehouse_id) {
                $user = $this->site->getUser();
                $warehouse_id = $user->warehouse_id;
            }

            $detail_link = anchor('sales/invoice_landscap_a5/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));

            $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                . lang('delete_sale') . "</a>";
            $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'. lang('actions') . ' <span class="caret"></span></button> <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>';

            if($this->Owner || $this->Admin || $this->GP['sales-delete']){
                $action .= '<li>' . $delete_link . '</li>';
            }
            $action .= '</ul></div></div>';


            $this->load->library('datatables');
            $this->datatables
                ->select("categories.id as id,
				 brands.name as brand_name,
				 categories.name as d,
				 sale_items.quantity,'' as action")
                ->from('erp_sale_items')
                ->join('erp_products','erp_sale_items.product_id=erp_products.id','LEFT')
                ->join('erp_categories','erp_categories.id=erp_products.category_id','LEFT')
                ->join('erp_subcategories','erp_subcategories.id = erp_products.subcategory_id','LEFT')
                ->join('erp_brands','erp_brands.id=erp_products.brand_id','LEFT')
                ->join('erp_serial','erp_serial.id = erp_sale_items.serial_no','LEFT')
                ->join('erp_sales','erp_sales.id=erp_sale_items.sale_id','LEFT')
                ->where('date BETWEEN "'. $start_date. '" and "'.$end_date.'"');
        }else{
            $this->load->library('datatables');
            $this->datatables
                ->select("categories.id as id,
				  brands.name as brand_name,
				 categories.name as d,
				 erp_sale_items.quantity,'' as action")
                ->from('erp_sale_items')
                ->join('erp_products','erp_sale_items.product_id=erp_products.id','LEFT')
                ->join('erp_categories','erp_categories.id=erp_products.category_id','LEFT')
                ->join('erp_subcategories','erp_subcategories.id = erp_products.subcategory_id','LEFT')
                ->join('erp_brands','erp_brands.id=erp_products.brand_id','LEFT')
                ->join('erp_serial','erp_serial.id = erp_sale_items.serial_no','LEFT')
                ->join('erp_sales','erp_sales.id=erp_sale_items.sale_id','LEFT');

        }
        if ($category) {
            $this->db->where($this->db->dbprefix('categories') . ".id", $category);
        }
        if ($subcategories) {
            $this->db->where($this->db->dbprefix('subcategories') . ".id", $subcategories);
        }
        if($brand){
            $this->db->where($this->db->dbprefix('brands') . ".id",$brand);
        }
        if($product_id){
            $this->db->where($this->db->dbprefix('products') . ".id",$product_id);

        }

        $this->datatables->add_column("Actions", $action, "id, psuspend")->unset_column('psuspend');
        echo $this->datatables->generate();
    }
}
    