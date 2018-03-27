<?php defined('BASEPATH') or exit('No direct script access allowed');

class Purchases extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            redirect('login');
        }
        if ($this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('purchases', $this->Settings->language);
        $this->load->library('form_validation');
        $this->load->model('purchases_model');
		$this->load->model('documents_model');
        $this->load->model('products_model');		
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
		
		if(!$this->Owner && !$this->Admin) {
            $gp = $this->site->checkPermissions();
            $this->permission = $gp[0];
            $this->permission[] = $gp[0];
        } else {
            $this->permission[] = NULL;
        }
        $this->default_biller_id = $this->site->default_biller_id();
    }

    /* ------------------------------------------------------------------------- */

    public function index($warehouse_id = null)
    {
        $this->erp->checkPermissions();
		
		$this->load->model('reports_model');
		
		if(isset($_GET['d']) != ""){
			$date = $_GET['d'];
			$this->data['date'] = $date;
		}
		
		$this->data['users'] = $this->reports_model->getStaff();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('purchases')));
        $meta = array('page_title' => lang('purchases'), 'bc' => $bc);
        $this->page_construct('purchases/index', $meta, $this->data);
    }

    public function getPurchases($warehouse_id = null)
    {
        $this->erp->checkPermissions('index');
		if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        if ($this->input->get('user')) {
            $user_query = $this->input->get('user');
        } else {
            $user_query = NULL;
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
		if ($this->input->get('note')) {
            $note = $this->input->get('note');
        } else {
            $note = NULL;
        }
		
        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link = anchor('purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        //$print_barcode = anchor('products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
		$purchase_return = anchor('purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_purchase") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchases/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_purchase') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
			<li>' . $purchase_return . '<li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                 ->select("id, date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status")
                 ->from('purchases')
				 ->where('warehouse_id', $warehouse_id);
        } else {
			$this->datatables
                 ->select("id, date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status")
                 ->from('purchases');
			if(isset($_REQUEST['d'])){
				$date_c = date('Y-m-d', strtotime('+3 months'));
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));
				
				$this->datatables
				->where("date >=", $date)
				->where('DATE_SUB(date, INTERVAL 1 DAY) <= CURDATE()')
				->where('purchases.payment_term <>', 0);
			}
        }
		$this->datatables->order_by('purchases.id', 'desc');
		if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
            $this->datatables->where('purchases.created_by', $this->session->userdata('user_id'));
        }
		
		// search options
		
		if ($user_query) {
			$this->datatables->where('purchases.created_by', $user_query);
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
		if ($note) {
			$this->datatables->like('purchases.note', $note, 'both');
		}
		
        /*if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }*/
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
		
	public function return_purchase($id = null)
    {
        $this->erp->checkPermissions('return', null, 'purchases');
		
		$serial_items = $this->purchases_model->getSerialByPurchaseId($id);
		foreach($serial_items as $serial_item){
			if ($serial_item->serial_status == 0) {
				$this->session->set_flashdata('error', lang('can_not_return_pu'));
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        // $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');

        if ($this->form_validation->run() == true) {
            $purchase = $this->purchases_model->getPurchaseByID($id);
            $quantity = "quantity";
            $product = "product";
            $unit_cost = "unit_cost";
            $tax_rate = "tax_rate";
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep');
            
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $note = $this->erp->clear_tags($this->input->post('note'));
			
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $total_pay = $this->input->post('total_pay');
            $i = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
                $serial_number = $_POST['serial_number'][$r];
                $purchase_item_id = $_POST['purchase_item_id'][$r];
                $purchase_id = $_POST['purchase_id'][$r];
                $item_option = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                //$option_details = $this->purchases_model->getProductOptionByID($item_option);
                $real_unit_cost = ($_POST['real_unit_cost'][$r]);
                $unit_cost = ($_POST['unit_cost'][$r]);
                $item_quantity = $_POST['quantity'][$r];
                $item_expiry = isset($_POST['expiry'][$r]) ? $_POST['expiry'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
				
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);

                    $item_type = $product_details->type;
                    $item_name = $product_details->name;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = (($unit_cost) * (Float) ($pds[0])) / 100;
                        } else {
                            $pr_discount = ($discount);
                        }
                    } else {
                        $pr_discount = 0;
                    }
                    // $unit_cost = $this->erp->formatDecimal($unit_cost - $pr_discount);
                    $pr_item_discount = ($pr_discount * $item_quantity);
                    $product_discount += $this->erp->formatDecimal($pr_item_discount);

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if (!$product_details->tax_method) {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate));
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / 100);
                                $tax = $tax_details->rate . "%";
                            }

                        } elseif ($tax_details->type == 2) {

                            $item_tax = ($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = ($item_tax * $item_quantity);

                    } else {
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $tax = "";
                    }
                    $item_net_cost = $product_details->tax_method ? ($unit_cost - $pr_discount) : ($unit_cost - $item_tax - $pr_discount);
                    
					$product_tax += $this->erp->formatDecimal($pr_item_tax);
					$subtotal = (($item_net_cost * $item_quantity) + $pr_item_tax);
                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'quantity' => $item_quantity,
                        'quantity_balance' => $item_quantity * -1,
                        'warehouse_id' => $purchase->warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount?$pr_item_discount:0,
                        'subtotal' => $subtotal,
                        'real_unit_cost' => $real_unit_cost,
                        'purchase_item_id' => $purchase_item_id,
                        'purchase_id' => $purchase_id,
                        'serial_no' => $serial_number,
                        'transaction_type' => 'purchase_return'
                    );
					$total += $this->erp->formatDecimal($subtotal);                   				
                }
            }
			
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('rediscount')) {
                $order_discount_id = $this->input->post('rediscount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = ((($total) * (Float) ($ods[0])) / 100);
                } else {
                    $order_discount = ($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
			
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = ($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = ((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = ($product_tax + $order_tax);
            $grand_total = (($total) + $order_tax - $this->erp->formatDecimal($return_surcharge) - $order_discount);
            $data = array('date' => $date,
                'purchase_id' => $id,
                'reference_no' => $reference,
                'supplier_id' => $purchase->supplier_id,
                'supplier' => $purchase->supplier,
                'warehouse_id' => $purchase->warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id ? $order_discount_id : 0,
                'order_discount' => $order_discount ? $order_discount:0,
                'total_discount' => $total_discount ? $total_discount:0,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id?$order_tax_id:0,
                'order_tax' => $order_tax?$order_tax:0,
                'total_tax' => $total_tax?$total_tax:0,
                'surcharge' => $return_surcharge,
                'grand_total' => $grand_total ? $grand_total:0,
                'created_by' => $this->session->userdata('user_id'),
            );
			
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            } 
        }

        if ($this->form_validation->run() == true && $this->purchases_model->returnPurchase($data, $products)) {
            $this->session->set_flashdata('message', lang("return_purchase_added"));
            redirect("purchases/return_purchases");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
            if ($this->data['inv']->status != 'received' && $this->data['inv']->status != 'partial') {
                $this->session->set_flashdata('error', lang("purchase_return_has_been_created"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            
            if ($this->data['inv']->date <= date('Y-m-d', strtotime('-3 months'))) {
                $this->session->set_flashdata('error', lang("purchase_x_edited_older_than_3_months"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->erp->fsd($item->expiry) : '');
                $row->qty = $item->quantity;
                $row->oqty = $item->quantity;
                $row->purchase_item_id = $item->id;
                $row->purchase_id = $item->purchase_id;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = !empty($item->option_id) ? $item->option_id : '';
                $row->real_unit_cost = $item->real_unit_cost;
                //$row->cost = $this->erp->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
				$row->cost = $item->unit_cost ? $item->unit_cost : 0 ;
                $row->tax_rate = $item->tax_rate_id;
				$row->net_cost = $item->net_unit_cost;
				$row->serial_item = $item->serial_no;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                if ($row->tax_rate) {
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => $tax_rate, 'options' => $options);
                }else{
                    $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => false, 'options' => $options);
                }
                $c++;
            }
			
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            //$this->data['reference'] = '';
            $this->data['reference'] = $this->site->getReference('rep');			
			$this->data['referenceno'] 	= $this->purchases_model->getReferenceno($id);
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('return_purchase')));
            $meta = array('page_title' => lang('return_purchase'), 'bc' => $bc);
            $this->page_construct('purchases/return_purchase', $meta, $this->data);
        }
    }
	
	public function return_purchases($warehouse_id = null)
    {
        $this->erp->checkPermissions('list-return', null, 'purchases');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('return_purchases')));
        $meta = array('page_title' => lang('return_purchases'), 'bc' => $bc);
        $this->page_construct('purchases/return_purchases', $meta, $this->data);
    }
	
	public function return_purchase_actions($pdf = NULL, $excel = NULL)
    {
        $this->erp->checkPermissions('list-return', null, 'purchases');

        $returnp = $this->input->get('sales') ? $this->input->get('sales') : NULL;

        if ($pdf || $excel) {

            $this->db
                ->select($this->db->dbprefix('return_purchases') . ".date as date, " . $this->db->dbprefix('return_purchases') . ".reference_no as ref, " . $this->db->dbprefix('purchases') . ".reference_no as sal_ref, " . $this->db->dbprefix('return_purchases') . ".supplier, " . $this->db->dbprefix('return_purchases') . ".surcharge, " . $this->db->dbprefix('return_purchases') . ".grand_total, " . $this->db->dbprefix('return_purchases') . ".id as id")
                ->join('purchases', 'purchases.id=return_purchases.purchase_id', 'left')
                ->from('return_purchases')
                ->group_by('return_purchases.id');
            if ($returnp) {
                $this->db->where('sales.id', $sales);
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
                $this->excel->getActiveSheet()->setTitle(lang('list purchase return'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchase_reference'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('surcharge'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));

                $row = 2;
				
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->date);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sal_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->supplier);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->surcharge);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($data_row->grand_total));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $filename = lang('list purchase return');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
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
                if ($excel) { 
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }

            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        }
    }
    
    function checkReturn($id){
        if($id){
            $isReturn = $this->purchases_model->getReturnPurchaseByPurchaseID($id);
            if($isReturn){
                echo true;
            }else{
                echo false;
            }
        }
    }

    public function getReturns($warehouse_id = null)
    {
       $this->erp->checkPermissions('list-return', null, 'purchases');

        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('return_purchases') . ".date as date, " . $this->db->dbprefix('return_purchases') . ".reference_no as ref, " . $this->db->dbprefix('purchases') . ".reference_no as sal_ref, " . $this->db->dbprefix('return_purchases') . ".supplier, " . $this->db->dbprefix('return_purchases') . ".surcharge, " . $this->db->dbprefix('return_purchases') . ".grand_total, " . $this->db->dbprefix('return_purchases') . ".id as id")
                ->join('purchases', 'purchases.id=return_purchases.purchase_id', 'left')
                ->from('return_purchases')
                ->group_by('return_purchases.id')
                ->where('return_purchases.warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select($this->db->dbprefix('return_purchases') . ".date as date, " . $this->db->dbprefix('return_purchases') . ".reference_no as ref, " . $this->db->dbprefix('purchases') . ".reference_no as sal_ref, " . $this->db->dbprefix('return_purchases') . ".supplier, " . $this->db->dbprefix('return_purchases') . ".surcharge, " . $this->db->dbprefix('return_purchases') . ".grand_total, " . $this->db->dbprefix('return_purchases') . ".id as id")
                ->join('purchases', 'purchases.id=return_purchases.purchase_id', 'left')
                ->from('return_purchases')
                ->group_by('return_purchases.id');
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin) {
            $this->datatables->where('return_purchases.created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('return_purchases.supplier_id', $this->session->userdata('supplier_id'));
        }

        echo $this->datatables->generate();
    }
	
	public function view_return($id = null)
    {
        $this->erp->checkPermissions('list-return', null, 'purchases');	
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		
        $inv = $this->purchases_model->getReturnByID($id);
		
        if (!$this->session->userdata('view_right')) {
            $this->erp->view_rights($inv->created_by);
        }
		
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
		
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->purchases_model->getAllPurchaseReturnItems($id);
        $this->data['purchase'] = $this->purchases_model->getPurchaseByID($inv->purchase_id);
        $this->load->view($this->theme.'purchases/modal_view_return', $this->data);
    }
	
	//-------------pending-------------
	public function getpending_Purchases($warehouse_id = null, $dt = null)
    {
        $this->erp->checkPermissions('index', true,'accounts');
		if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        if ($this->input->get('user')) {
            $user_query = $this->input->get('user');
        } else {
            $user_query = NULL;
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

        if ($this->input->get('search_id')) {
            $search_id = $this->input->get('search_id');
        } else {
            $search_id = NULL;
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
		if ($this->input->get('note')) {
            $note = $this->input->get('note');
        } else {
            $note = NULL;
        }
		
		if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
			$user = $this->site->getUser();
			$warehouse_id = $user->warehouse_id;
		}

        $detail_link = anchor('purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link = anchor('purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_purchase") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchases/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_purchase') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $print_barcode . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
			
            $this->datatables
                ->select("id, date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status")
                ->from('purchases')
				->where('payment_status !=','paid')
                ->where('warehouse_id', $warehouse_id);
        } else {
			$this->datatables
                ->select("id, date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status")
                ->from('purchases')
				->where('payment_status !=','paid');
			if(isset($_REQUEST['d'])){
				$date_c = date('Y-m-d', strtotime('+3 months'));
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));
				
				$this->datatables
				->where("date >=", $date)
				->where('payment_status !=','paid')
				->where('DATE_SUB(date, INTERVAL 1 DAY) <= CURDATE()')
				->where('purchases.payment_term <>', 0);
				
			}
            
        }
		
		// search options
		
        if($search_id) {
            $this->datatables->where('purchases.id', $search_id);
        }
			if ($user_query) {
				$this->datatables->where('purchases.created_by', $user_query);
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
		if ($note) {
			$this->datatables->like('purchases.note', $note, 'both');
		}
		
		if($dt == 30){
			$this->datatables->where('date('. $this->db->dbprefix('purchases')  .'.date) > CURDATE() AND date('. $this->db->dbprefix('purchases')  .'.date) <= DATE_ADD(now(), INTERVAL + 30 DAY)');
		}elseif($dt == 60){
			$this->datatables->where('date('. $this->db->dbprefix('purchases')  .'.date) > DATE_ADD(now(), INTERVAL + 30 DAY) AND date('. $this->db->dbprefix('purchases')  .'.date) <= DATE_ADD(now(), INTERVAL + 60 DAY)');
		}elseif($dt == 90){
			$this->datatables->where('date('. $this->db->dbprefix('purchases')  .'.date) > DATE_ADD(now(), INTERVAL + 60 DAY) AND date('. $this->db->dbprefix('purchases')  .'.date) <= DATE_ADD(now(), INTERVAL + 90 DAY)');
		}elseif($dt == 91){
			$this->datatables->where('date(purchases.date) >= DATE_ADD(now(), INTERVAL + 90 DAY)');
		}
		
        /*if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }*/
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    /* ----------------------------------------------------------------------------- */
    public function modal_view_ap_aging($purchase_id = null, $type = NULL)
    {
        $this->erp->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->erp->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['supplier_id'] = $inv->supplier_id;
        $this->data['type_view'] = $type;

        $this->load->view($this->theme . 'purchases/modal_view_ap_aging', $this->data);

    }

    public function modal_view($purchase_id = null)
    {
        $this->erp->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->erp->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;

        $this->load->view($this->theme . 'purchases/modal_view', $this->data);
	}

    public function view($purchase_id = null)
    {
        $this->erp->checkPermissions('index');

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->erp->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_purchase_details'), 'bc' => $bc);
        $this->page_construct('purchases/view', $meta, $this->data);

    }

    /* ----------------------------------------------------------------------------- */

	//generate pdf and force to download

    public function pdf($purchase_id = null, $view = null, $save_bufffer = null)
    {
        $this->erp->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->erp->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['inv'] = $inv;
        $name = $this->lang->line("purchase") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
        if ($view) {
            $this->load->view($this->theme . 'purchases/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->erp->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->erp->generate_pdf($html, $name);
        }

    }

    public function combine_pdf($purchases_id)
    {
        $this->erp->checkPermissions('pdf');

        foreach ($purchases_id as $purchase_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->purchases_model->getPurchaseByID($purchase_id);
            if (!$this->session->userdata('view_right')) {
                $this->erp->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['created_by'] = $this->site->getUser($inv->created_by);
            $this->data['inv'] = $inv;

            $html[] = array(
                'content' => $this->load->view($this->theme . 'purchases/pdf', $this->data, true),
                'footer' => '',
            );
        }

        $name = lang("purchases") . ".pdf";
        $this->erp->generate_pdf($html, $name);

    }

    public function email($purchase_id = null)
    {
        $this->erp->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        $this->form_validation->set_rules('to', $this->lang->line("to") . " " . $this->lang->line("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', $this->lang->line("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', $this->lang->line("cc"), 'trim');
        $this->form_validation->set_rules('bcc', $this->lang->line("bcc"), 'trim');
        $this->form_validation->set_rules('note', $this->lang->line("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->erp->view_rights($inv->created_by);
            }
            $to = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $supplier = $this->site->getCompanyByID($inv->supplier_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $supplier->name,
                'company' => $supplier->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($purchase_id, null, 'S');
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->erp->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->db->update('purchases', array('status' => 'ordered'), array('id' => $purchase_id));
            $this->session->set_flashdata('message', $this->lang->line("email_sent"));
            redirect("purchases");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/purchase.html')) {
                $purchase_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/purchase.html');
            } else {
                $purchase_temp = file_get_contents('./themes/default/views/email_templates/purchase.html');
            }
            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('purchase_order').' (' . $inv->reference_no . ') '.lang('from').' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $purchase_temp),
            );
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);

            $this->data['id'] = $purchase_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/email', $this->data);

        }
    }

    /* -------------------------------------------------------------------------------------------------------------------------------- */

    public function add($quote_id = null, $catelog_id = null, $purchase_order_id = null)
    {
        $this->erp->checkPermissions();		
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
		$this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
			$quantity  = "quantity";
            $product   = "product";
            $unit_cost = "unit_cost";
            $tax_rate  = "tax_rate";
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
			$payment_term = $this->input->post('payment_term');
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			if ($this->Owner || $this->Admin) {
                $expected_date = $this->erp->fld(trim($this->input->post('expected_date')));
            } else {
                $expected_date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
			$rsupplier_id = $this->input->post('rsupplier_id');
			$purchase_order_id = $this->input->post('po_id');
            $status = 'received';
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            
			$supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            
            $note = $this->erp->clear_tags($this->input->post('note'));
			$variant_id = $this->input->post('variant_id');

			//$variant_id = array_filter($variant_id);
			
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                $item_code 			= $_POST['product'][$r];
                $item_net_cost 		= ($_POST['net_cost'][$r]);
                $unit_cost 			= ($_POST['unit_cost'][$r]);
				$unit_cost_real 	= $unit_cost;
                $real_unit_cost 	= ($_POST['real_unit_cost'][$r]);
                $item_quantity 		= $_POST['quantity'][$r];
                $quantity_balance   = $_POST['received'][$r];				
				$serial_no 			= $_POST['serial'][$r]?$_POST['serial'][$r]:0;
                $p_supplier 		= $_POST['rsupplier_id'][$r];				
                $p_type 			= $_POST['type'][$r];
                $item_option 		= isset($_POST['product_option'][$r]) ? $_POST['product_option'][$r] : NULL;
				
				if($item_option == 'undefined'){
					$item_option 	= NULL;
				}
				if($quantity_balance){
					$item_quantity = $quantity_balance;
				}
                $item_tax_rate 		= isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount 		= isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry 		= (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->erp->fsd($_POST['expiry'][$r]) : null;

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
					$product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    $unit_cost = $real_unit_cost;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = ((($unit_cost)) * (Float) ($pds[0])) / 100;
                        } else {
                            $pr_discount = ($discount);
                        }
                    }

                    $unit_cost 		= ($unit_cost - $pr_discount);
					
                    $item_net_cost 	= $unit_cost_real;
                    $pr_item_discount = ($pr_discount * $item_quantity);
                    $product_discount += $this->erp->formatDecimal($pr_item_discount);
                    $pr_tax 		= 0;
                    $pr_item_tax 	= 0;
                    $item_tax 		= 0;
                    $tax 			= "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / 100);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = ((($unit_cost_real) * $tax_details->rate) / (100 + $tax_details->rate));
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / 100);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = ((($unit_cost_real) * $tax_details->rate) / (100 + $tax_details->rate));
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                            $item_tax = ($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
						
                        $pr_item_tax = ($item_tax * $item_quantity);
                    }					
					
                    $product_tax 	+= $this->erp->formatDecimal($pr_item_tax);
                    $subtotal 		= $this->erp->formatDecimal(($item_net_cost * $item_quantity) + $pr_item_tax);
					
					$setting = $this->site->get_setting();
					$products[] = array(
						'product_id' 		=> $product_details->id,
						'product_code' 		=> $item_code,
						'product_name' 		=> $product_details->name,
						'option_id' 		=> $item_option,
						'net_unit_cost' 	=> $item_net_cost, 
						'unit_cost' 		=> $unit_cost_real,
						'quantity' 			=> $item_quantity,
						'quantity_balance' 	=> $quantity_balance ? $quantity_balance : $item_quantity,
						'warehouse_id' 		=> $warehouse_id,
						'item_tax' 			=> $pr_item_tax,
						'tax_rate_id' 		=> $pr_tax,
						'tax' 				=> $tax,
						'discount' 			=> $item_discount,
						'item_discount' 	=> $pr_item_discount,
						'subtotal' 			=> $subtotal,
						'expiry' 			=> $item_expiry,
						'real_unit_cost' 	=> $real_unit_cost,
						'date' 				=> date('Y-m-d', strtotime($date)),
						'status' 			=> $status,
						'supplier_id' 		=> $p_supplier,
						'type' 				=> $p_type,
						'serial_no' 		=> $serial_no,
						'cb_avg'			=> $product_details->cost,
						'cb_qty'			=> $product_details->quantity,
						'transaction_type'	=> 'purchase'
					);
					
					if($serial_no != 'undefined' or $serial_no != null){
						$serial[] = array(
							'product_id'    => $product_details->id,
							'serial_number' => $serial_no,
							'warehouse'     => $warehouse_id,
							'biller_id'     => $this->site->default_biller_id(),
							'serial_status' => 1
							
						);
					}
					
                    $total += $subtotal;
					//============== Variants For AVG ===============//
					$qty_variant = 0;
					$cos_variant = 0;
					$variant = $this->site->getUnitQuantity($item_option, $product_details->id);
					$qty_variant = $item_quantity;
					$cos_variant = $item_net_cost;
					if($variant){
						$qty_variant = $item_quantity * $variant->qty_unit;
						$cos_variant = $item_net_cost / $variant->qty_unit;
					}
					
					$avg_cost[] = array(
						'product_id' 		=> $product_details->id,
						'quantity'   		=> $qty_variant,
						'unit_cost'  		=> $cos_variant,
						'price'      		=> $p_price,
						'option_id'  		=> $item_option,
					);
					//==================== End =====================//
                }
            }
			$out = array();
			foreach ($avg_cost as $key => $value){
				if (array_key_exists($value['product_id'], $out)){
					$out[$value['product_id']]['product_id'] = $value['product_id'];
					$out[$value['product_id']]['quantity'] 	+= $value['quantity'];
					$out[$value['product_id']]['unit_cost'] += $value['unit_cost'];
					$out[$value['product_id']]['price'] 	+= $value['price'];
					$out[$value['product_id']]['option_id']  = $value['option_id'];
				} else {
					$out[$value['product_id']] = array(
						'product_id' => $value['product_id'], 
						'quantity'   => $value['quantity'],
						'unit_cost'  => $value['unit_cost'],
						'price'      => $value['price'],
						'option_id'  => $value['option_id'],
					);
				}
			}
			$array_c 	= array_values($out);
			
			if($setting->accounting_method == 1 || $shipping){
				$c = count($array_c);
				$t_po_item_amount = 0;
				$total_price = 0;
				$a = 0;
				foreach($array_c as $p){
					$total_price += $p['quantity'] * $p['unit_cost'];
				}
				$avg  = array();
				$ship = array();
				for($i = 0; $i < $c; $i++){
					$item_option = $_POST['product_option'][$i];
					$unitCost = $this->erp->formatPurDecimal($_POST['unit_cost'][$i]);
					$costunit = $this->site->calculateAVGCost2017($array_c[$i]['product_id'], $shipping, $array_c[$i]['quantity'], $array_c[$i]['price'], $total_price, $array_c[$i]['unit_cost'], $item_discount  , $this->input->post('discount'), $array_c[$i]['option_id']);
					$avg[$array_c[$i]['product_id']]  = $costunit['avgcost'];
					$ship[$array_c[$i]['product_id']] = $costunit['shipping_cost'];
				}
				$i = 0;
				foreach($products as $p){
					$products[$i]['real_unit_cost'] = $avg[ $p['product_id'] ];
					$i++;
				}
			}
			
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
			
            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = ((($total) * (Float) ($ods[0])) / 100);
                } else {
                    $order_discount = ($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = ($order_discount + $product_discount);
			
            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = ($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = ((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100);
                    }
                }
            } else {
                $order_tax_id = null;
            }
			if($purchase_order_id){
				$order_id 		= $this->purchases_model->getPurchaseOrderByID($purchase_order_id);
				$po_id 			= $order_id->id;
				$po_reference 	= $order_id->reference_no;
			}
            $total_tax = $product_tax + $order_tax;
            $grand_total = (($total) + $order_tax + ($shipping) - $order_discount);
            $data = array(
				'biller_id' 				=> $this->site->default_biller_id(),
				'reference_no' 				=> $reference,
				'purchase_order_id' 		=> $po_id ? $po_id : "",
				'purchase_order_reference' 	=> $po_reference ? $po_reference : "",
				'payment_term' 				=> $payment_term,
                'date' 						=> $date,
                'supplier_id' 				=> $supplier_id,
                'supplier' 					=> $supplier,
                'warehouse_id' 				=> $warehouse_id,
                'note' 						=> $note,
                'total' 					=> $total,
                'product_discount' 			=> $product_discount,
                'order_discount_id' 		=> $order_discount_id,
                'order_discount' 			=> $order_discount,
                'total_discount' 			=> $total_discount,
                'product_tax' 				=> $product_tax,
                'order_tax_id' 				=> $order_tax_id,
                'order_tax' 				=> $order_tax,
                'total_tax' 				=> $total_tax,
                'shipping' 					=> $shipping,
                'grand_total' 				=> $grand_total,
                'status' 					=> $status,
                'created_by' 				=> $this->session->userdata('user_id')
				//'expected_date' 			=> $expected_date
            );
			
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] 		= $this->digital_upload_path;
                $config['allowed_types'] 	= $this->digital_file_types;
                $config['max_size'] 		= $this->allowed_file_size;
                $config['overwrite'] 		= false;
                $config['encrypt_name'] 	= true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error 					= $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			if ($_FILES['document1']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] 		= $this->digital_upload_path;
                $config['allowed_types'] 	= $this->digital_file_types;
                $config['max_size'] 		= $this->allowed_file_size;
                $config['overwrite'] 		= false;
                $config['encrypt_name'] 	= true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document1')) {
                    $error 					= $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo 						= $this->upload->file_name;
                $data['attachment1'] 		= $photo;
            }
			if ($_FILES['document2']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] 		= $this->digital_upload_path;
                $config['allowed_types'] 	= $this->digital_file_types;
                $config['max_size'] 		= $this->allowed_file_size;
                $config['overwrite'] 		= false;
                $config['encrypt_name'] 	= true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document2')) {
                    $error 					= $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo 						= $this->upload->file_name;
                $data['attachment2'] 		= $photo;
            }
			//$this->erp->print_arrays($data, $products);
		}
		
        if ($this->form_validation->run() == true && $purchase_id = $this->purchases_model->addPurchase($data, $products, $purchase_order_id)) {
			if($this->Settings->purchase_serial){
				if($status == 'ordered'){

				}elseif($status == 'pending'){

				}else{
					$this->purchases_model->addSerial($serial, $purchase_id);
				}
			}
			$this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            redirect('purchases');
        } else {
            if ($quote_id) {
                $this->data['quote'] = $this->purchases_model->getQuoteByID($quote_id);
                $items = $this->purchases_model->getAllQuoteItems($quote_id);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
					
                    if ($row->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($row->id, $warehouse_id);
                        foreach ($combo_items as $citem) {
                            $crow = $this->site->getProductByID($citem->product_id);
                            if (!$crow) {
                                $crow = json_decode('{}');
                                $crow->quantity = 0;
                            } else {
                                unset($crow->details, $crow->product_details);
                            }
                            $crow->discount = $item->discount ? $item->discount : '0';
                            $crow->cost = $crow->cost ? $crow->cost : 0;
                            $crow->tax_rate = $item->tax_rate_id;
                            $crow->real_unit_cost = $crow->cost ? $crow->cost : 0;
                            $crow->expiry = '';
                            $options = $this->purchases_model->getProductOptions($crow->id);

                            $ri = $this->Settings->item_addition ? $crow->id : $c;
                            if ($crow->tax_rate) {
                                $tax_rate = $this->site->getTaxRateByID($crow->tax_rate);
                                $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'tax_rate' => $tax_rate, 'options' => $options, 'suppliers' => '' ,'supplier_id' => '');
                            } else {
                                $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'tax_rate' => false, 'options' => $options, 'suppliers' => '' ,'supplier_id' => '');
                            }
                            $c++;
                        }
                    } elseif ($row->type == 'standard') {
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->quantity = 0;
                        } else {
                            unset($row->details, $row->product_details);
                        }

                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->qty = $item->quantity;
                        $row->option = $item->option_id;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $row->cost = $row->cost ? $row->cost : 0;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->expiry = '';
                        $row->real_unit_cost = $row->cost ? $row->cost : 0;

                        $options = $this->purchases_model->getProductOptions($row->id);

                        $ri = $this->Settings->item_addition ? $row->id : $c;
                        if ($row->tax_rate) {
                            $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                            $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => $tax_rate, 'options' => $options, 'suppliers' => '' ,'supplier_id' => '');
                        } else {
                            $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => false, 'options' => $options, 'suppliers' => '' ,'supplier_id' => '');
                        }
                        $c++;
                    }
					if(!$row){
						if (!$row) {
                            $row = json_decode('{}');
                            $row->quantity = 0;
                        } else {
                            unset($row->details, $row->product_details);
                        }

                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->qty = $item->quantity;
                        $row->option = $item->option_id;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $row->cost = $row->cost ? $row->cost : 0;
						$row->price = $item->unit_price;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->exist = 1;
                        $row->expiry = '';
						$options = json_decode('{}');
						$ri = $this->Settings->item_addition ? $item->product_id : $c;
						$pr[$ri] = array('id' => $c, 'item_id' => $item->product_id, 'label' => $item->product_name . " (" . $item->product_code . ")", 'row' => $row, 'tax_rate' => false, 'options' => $options, 'suppliers' => '' ,'supplier_id' => '');
						$c++;
					}
                }               
				$this->data['quote_items'] = json_encode($pr);
            }
			if($catelog_id){
				$items = $this->documents_model->getDocumentByID($catelog_id);
				$photo = $this->documents_model->getDocumentPhoto($catelog_id); 
				$pdata = array(
					'code' => $items->product_code,
					'barcode_symbology' => 'code128',
					'name' => $items->product_name,
					'type' => 'standard',
					'category_id' => $items->category_id,
					'subcategory_id' => $items->subcategory_id,
					'cost' => $items->cost,
					'price' => $items->price,
					'unit' => $items->unit,
					'image' => $items->image,
					'tax_method' => '0',
					'track_quantity' => '0',
					'product_details' => $items->description,
					'supplier1' => 0,          
					'currentcy_code'    => 'USD',
					'inactived' => 0,
					'brand_id' => $items->brand_id,
					'is_serial' => $items->serial
				);
				foreach($photo as $img){
					$image[] = $img->photo;
				}
				$check = $this->documents_model->checkProduct($items->product_code);
				if($check){
					$row = $this->documents_model->getProductByCode($items->product_code);
					$option = false;
					$row->quantity = 0;
					$row->item_tax_method = $row->tax_method;
					$row->qty = 1;
					$row->discount = '0';
					$options = $this->purchases_model->getProductOptions1($row->id, $warehouse_id);
					if ($options) {
						$opt = $options[0];
						if (!$option) {
							$option = $opt->id;
						}
					} else {
						$opt = json_decode('{}');
						$opt->price = 0;
					}
					$row->option = $option;
					$pis = $this->purchases_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
					
					if ($pis) {
						foreach ($pis as $pi) {
							$row->quantity += $pi->quantity_balance;
						}
					}
					if ($options) {
						$option_quantity = 0;
						foreach ($options as $option) {
							$pis = $this->purchases_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
							if ($pis) {
								foreach ($pis as $pi) {
									$option_quantity += $pi->quantity_balance;
								}
							}
							if ($option->quantity > $option_quantity) {
								$option->quantity = $option_quantity;
							}
						}
					}
					if ($opt->price != 0) {
						$row->price = $opt->price + (($opt->price * $customer_group->percent) / 100);
					} else {
						$row->price = $row->price + (($row->price * $customer_group->percent) / 100);
					}
					$row->real_unit_price = $row->price;
					$combo_items = false;
					if ($row->tax_rate) {
						$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
						if ($row->type == 'combo') {
							$combo_items = $this->purchases_model->getProductComboItems($row->id, $warehouse_id);
						}
						$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'options' => $options);
					} else {
						$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => false, 'options' => $options);
					}
					$this->data['catelog_items'] = json_encode($pr);
				}else{
					$this->products_model->addProduct($pdata, null, null, null, $image, null);
					$row = $this->documents_model->getProductByCode($items->product_code);
					$option = false;
					$row->quantity = 0;
					$row->item_tax_method = $row->tax_method;
					$row->qty = 1;
					$row->discount = '0';
					$options = $this->purchases_model->getProductOptions1($row->id, $warehouse_id);
					if ($options) {
						$opt = $options[0];
						if (!$option) {
							$option = $opt->id;
						}
					} else {
						$opt = json_decode('{}');
						$opt->price = 0;
					}
					$row->option = $option;
					$pis = $this->purchases_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
					
					if ($pis) {
						foreach ($pis as $pi) {
							$row->quantity += $pi->quantity_balance;
						}
					}
					if ($options) {
						$option_quantity = 0;
						foreach ($options as $option) {
							$pis = $this->purchases_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
							if ($pis) {
								foreach ($pis as $pi) {
									$option_quantity += $pi->quantity_balance;
								}
							}
							if ($option->quantity > $option_quantity) {
								$option->quantity = $option_quantity;
							}
						}
					}
					if ($opt->price != 0) {
						$row->price = $opt->price + (($opt->price * $customer_group->percent) / 100);
					} else {
						$row->price = $row->price + (($row->price * $customer_group->percent) / 100);
					}
					$row->real_unit_price = $row->price;
					$combo_items = false;
					if ($row->tax_rate) {
						$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
						if ($row->type == 'combo') {
							$combo_items = $this->purchases_model->getProductComboItems($row->id, $warehouse_id);
						}
						$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'options' => $options);
					} else {
						$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => false, 'options' => $options);
					}
					$this->data['catelog_items'] = json_encode($pr);
				}
			}
			if ($purchase_order_id) {
				$this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
				$order_id               = $this->purchases_model->getPurchaseOrderByID($purchase_order_id);
				$this->data['inv']      = $order_id;
				$ref                    = $this->site->getReference('pp');
				$pref                   = $this->purchases_model->getPaymentByPurchaseID($purchase_order_id);
				$pur                    = $this->purchases_model->getPurchaseOrderByID($purchase_order_id);
				$inv_items              = $this->purchases_model->getAllPurchaseOrderItems_order($purchase_order_id);
				
				$c = rand(100000, 9999999);
				foreach ($inv_items as $item) {
					$row = $this->site->getProductByID($item->product_id);
					$row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->erp->fsd($item->expiry) : '');
					$row->qty = $item->quantity;
					
					$row->received = ((($item->quantity - $item->quantity_received) > 0)? ($item->quantity - $item->quantity_received) : 0);
					$row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
					
					$row->discount = $item->discount ? $item->discount : '0';
					$options = $this->purchases_model->getProductOptions($row->id);
					$row->option = $item->option_id;
					$row->real_unit_cost = $item->unit_cost;
					$row->cost = $item->unit_cost;
					$row->tax_rate = $item->tax_rate_id;
					$row->net_cost = $item->unit_cost;
					$row->price = $item->price;
					$row->net_unit_cost = $item->net_unit_cost;
					$pii = $this->purchases_model->getPurcahseItemByPurchaseID($purchase_order_id);
					
					unset($row->details, $row->product_details, $row->file, $row->product_group_id);
					$ri = $this->Settings->item_addition ? $row->id : $c;
					if ($row->tax_rate) {
						$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
						$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => $tax_rate, 'options' => $options, 'suppliers' => '' , 'supplier_id' => $item->supplier_id, 'create_id'=>$item->id);
					} else {
						
						$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => false, 'options' => $options, 'suppliers' => '' , 'supplier_id' => $item->supplier_id, 'create_id'=>$item->id);
					}
					$c++;
				}				
				$this->data['inv_items'] = json_encode($pr);
				$this->data['id'] = $purchase_order_id;
				$this->data['suppliers'] = $this->site->getAllCompanies('supplier');
				$this->data['purchase'] = $order_id;
            }
			
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id'] = $quote_id;
			$this->data['catelog_id']=$catelog_id;
            $this->data['categories'] = $this->site->getAllCategories();
			$this->data['products'] = $this->site->getAllProducts();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
			$this->data['brands'] = $this->site->getAllBrands();
			$this->data['variants'] = $this->products_model->getAllVariants();
			$this->data['case'] = $this->products_model->getCases();
			$this->data['diameters'] = $this->products_model->getDiameters();
			$this->data['shops'] = $this->products_model->getProjects();
			$this->data['dial'] = $this->products_model->getDials();
			$this->data['strap'] = $this->products_model->getStraps();
			$this->data['water'] = $this->products_model->getWater();
			$this->data['winding'] = $this->products_model->getWinding();
			$this->data['powerreserve'] = $this->products_model->getPowerReserve();
			$this->data['buckle'] = $this->products_model->getBuckle();
			$this->data['complication'] = $this->products_model->getComplication();
			$this->data['unit']  = $this->products_model->getUnits();
            $this->data['ponumber'] = ''; //$this->site->getReference('po');
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('add_purchase')));
            $meta = array('page_title' => lang('add_purchase'), 'bc' => $bc);
            $this->page_construct('purchases/add', $meta, $this->data);
        }
    }
	
    /* ------------------------------------------------------------------------------------- */
		
	public function getSearchSupplier(){
        //$code = $this->input->get('code', TRUE);
		$code = $this->input->get('limit', TRUE);
		$supplier = $this->site->getProductSupplier($code);
		$suppliers = array($supplier->supplier1, $supplier->supplier2, $supplier->supplier3,$supplier->supplier4,$supplier->supplier4);
		$rows['results'] = $this->site->getSupplierByArray($suppliers);
		//$rows['results'] = $this->site->getAllCompanies('supplier');
		//$test['results'] = 'fuck';
        //$limit['results'] = $this->input->get('limit', TRUE);
        //$rows['results'] = $this->purchases_model->getSupplierSuggestions($term, $limit);
		echo json_encode($rows);
	}
    
    public function getSupplierProduct(){
		$code = $this->input->get('code');
		$supplier = $this->site->getProductSupplier($code);
		$suppliers = array($supplier->supplier1, $supplier->supplier2, $supplier->supplier3,$supplier->supplier4,$supplier->supplier4);
		$rows['results'] = $this->site->getSupplierByArray($suppliers);
		echo json_encode($rows);
	}
	function getProductSerialAjax($serial = NULL){        
        $row = $this->purchases_model->getSerialProducts($serial);
        if ($row) {
            echo 1;
        } else {
            echo 0;
        }
	}
    public function edit($id = null)
    {
        $this->erp->checkPermissions('edit', null, 'purchases');
		$serial_items = $this->purchases_model->getSerialByPurchaseId($id);
		foreach($serial_items as $serial_item){
			if ($serial_item->serial_status == 0) {
				$this->session->set_flashdata('error', lang('can_not_edit_pu'));
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
		$setting = $this->site->get_setting();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->erp->view_rights($inv->created_by);
        }		
		
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $quantity 		= "quantity";
            $product 		= "product";
            $unit_cost 		= "unit_cost";
            $tax_rate 		= "tax_rate";
            $reference 		= $this->input->post('reference_no');
			$payment_term 	= $this->input->post('payment_term');
			
			$due_date 		= $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days')) : NULL;
			
            if ($this->Owner || $this->Admin) {
                $date 		= $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date 		= null;
            }
			if ($this->Owner || $this->Admin) {
                $expected_date = $this->erp->fld(trim($this->input->post('expected_date')));
            } else {
                $expected_date = date('Y-m-d H:i:s');
            }
            $warehouse_id 	= $this->input->post('warehouse');
            $supplier_id 	= $this->input->post('supplier');
            $status 		= 'received';

            $shipping 		= $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier 		= $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note 			= $this->erp->clear_tags($this->input->post('note'));

            $total 			= 0;
            $product_tax 	= 0;
            $order_tax 		= 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage 	= '%';
            $partial 		= false;
            $i = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                $item_code 		= $_POST['product'][$r];
                $item_net_cost 	= ($_POST['net_cost'][$r]);
				
                $unit_cost 		= ($_POST['unit_cost'][$r]);
				$unit_cost_real = $unit_cost;
				
				// Price
				$p_price 		= $_POST['price'][$r];
                $p_type 		= $_POST['type'][$r];
				$serial_no 		= $_POST['serial'][$r]?$_POST['serial'][$r]:0;
				
                // Supplier
				$p_supplier 	= $_POST['rsupplier_id'][$r];
				
                $real_unit_cost = ($_POST['real_unit_cost'][$r]);
				
				$received_hidden = $_POST['received_hidden'][$r];
				$current_stock 	= $_POST['rstock'][$r];
				
                $item_quantity	= $_POST['quantity'][$r];
                $quantity_received = $_POST['received'][$r];
                $item_option 	= isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate 	= isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount 	= isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry 	= isset($_POST['expiry'][$r]) ? $this->erp->fsd($_POST['expiry'][$r]) : null;

                $quantity_balance = $_POST['quantity_balance'][$r];
                $ordered_quantity = $_POST['ordered_quantity'][$r];
				
				$balance_qty = $item_quantity;
                $quantity_received = $item_quantity;
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);

                    $unit_cost 		= $real_unit_cost;
                    $pr_discount 	= 0;

                    if (isset($item_discount)) {
                        $discount 	= $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = (($unit_cost) * (Float) ($pds[0])) / 100;
                        } else {
                            $pr_discount = ($discount);
                        }
                    }

                    $unit_cost 			= ($unit_cost - $pr_discount);
                    $item_net_cost 		= $unit_cost_real;
					
                    $pr_item_discount 	= ($pr_discount * $item_quantity);
                    $product_discount 	+= $this->erp->formatDecimal($pr_item_discount);
                    $pr_tax 			= 0;
                    $pr_item_tax 		= 0;
                    $item_tax 			= 0;
                    $tax 				= "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / 100);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = ((($unit_cost_real) * $tax_details->rate) / (100 + $tax_details->rate));
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                        } elseif ($tax_details->type == 2) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $item_tax = ((($unit_cost) * $tax_details->rate) / 100);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = ((($unit_cost_real) * $tax_details->rate) / (100 + $tax_details->rate));
                                $tax = $tax_details->rate . "%";
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                            $item_tax = ($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
						
                        $pr_item_tax = ($item_tax * $item_quantity);
                    }
					
					$quantity_balance = 0;
					$option_cost = 0;
					if($item_option != 0) {
						$row 				= $this->purchases_model->getVariantQtyById($item_option);
						$quantity_balance 	= $item_quantity * $row->qty_unit;
						$option_cost 		= $row->cost * $row->qty_unit;
					}else{
						$quantity_balance 	= $item_quantity;
					}

                    $product_tax 			+= $this->erp->formatDecimal($pr_item_tax);					
					
					$q = $this->purchases_model->getPurcahseItemByPurchaseID($id);
					
					$subtotal 			= (($item_net_cost * ($quantity_received!=0?$quantity_received:$item_quantity)) + $pr_item_tax);
					$real_unit_costs 	= $real_unit_cost;
					
                    $items[] = array(
                        'product_id' 		=> $product_details->id,
                        'product_code' 		=> $item_code,
                        'product_name' 		=> $product_details->name,
                        'option_id' 		=> $item_option,
                        'net_unit_cost' 	=> $item_net_cost, 
						'unit_cost' 		=> $unit_cost_real,
                        'quantity' 			=> $item_quantity,
                        'quantity_balance'  => $balance_qty,
                        'quantity_received' => $quantity_received,
                        'warehouse_id' 		=> $warehouse_id,
                        'item_tax' 			=> $pr_item_tax,
                        'tax_rate_id' 		=> $pr_tax,
                        'tax' 				=> $tax,
                        'discount' 			=> $item_discount,
                        'item_discount' 	=> $pr_item_discount,
                        'subtotal' 			=> $this->erp->formatPurDecimal($subtotal),
                        'expiry' 			=> $item_expiry,
                        'real_unit_cost' 	=> $real_unit_costs,
                        'date' 				=> date('Y-m-d H:i:s', strtotime($date)),
                        'status' 			=> $status,
						'price' 			=> $p_price,
                        'supplier_id' 		=> $p_supplier,
						'serial_no'		 	=> $serial_no,
						'type'		 		=> $p_type,
						'transaction_type'	=> 'purchase'
                    );
					
					if($serial_no != 'undefined' or $serial_no != null){
						$serial[] = array(
							'product_id'    => $product_details->id,
							'serial_number' => $serial_no,
							'warehouse'     => $warehouse_id,
							'biller_id'     => $this->site->default_biller_id(),
							'serial_status' => 1
						);
					}
					//============== Variants For AVG ===============//
					$qty_variant = 0;
					$cos_variant = 0;
					$variant 	 = $this->site->getUnitQuantity($item_option, $product_details->id);
					$qty_variant = $item_quantity;
					$cos_variant = $item_net_cost;
					if($variant){
						$qty_variant = $item_quantity * $variant->qty_unit;
						$cos_variant = $item_net_cost / $variant->qty_unit;
					}
					
					$avg_cost[] = array(
						'product_id' 		=> $product_details->id,
						'quantity'   		=> $qty_variant,
						'unit_cost'  		=> $cos_variant,
						'price'      		=> $p_price,
						'option_id'  		=> $item_option,
					);
					//==================== End =====================//
					
					if($item_option != 0) {
						if($item_net_cost == $option_cost){
							$total += $item_net_cost * ($quantity_received!=0?$quantity_received*$option_cost:$item_quantity*$option_cost) + $pr_discount;
						}else{
							$total += $this->erp->formatDecimal($subtotal);
						}
					}else{
						$total += $this->erp->formatDecimal($subtotal);
					}
                }
            }
			$out = array();
			foreach ($avg_cost as $key => $value){
				if (array_key_exists($value['product_id'], $out)){
					$out[$value['product_id']]['product_id'] = $value['product_id'];
					$out[$value['product_id']]['quantity'] 	+= $value['quantity'];
					$out[$value['product_id']]['unit_cost'] += $value['unit_cost'];
					$out[$value['product_id']]['price'] 	+= $value['price'];
					$out[$value['product_id']]['option_id']  = $value['option_id'];
				} else {
					$out[$value['product_id']] = array(
						'product_id' => $value['product_id'], 
						'quantity'   => $value['quantity'],
						'unit_cost'  => $value['unit_cost'],
						'price'      => $value['price'],
						'option_id'  => $value['option_id'],
					);
				}
			}
			$array_c 			= array_values($out);
			if($setting->accounting_method == 2 || $shipping){
				$c = count($array_c);
				$t_po_item_amount = 0;
				$total_price = 0;
				$a = 0;
				foreach($array_c as $p){
					$total_price += $p['quantity'] * $p['unit_cost'];
				}
				$avg  = array();
				$ship = array();
				for($i = 0; $i < $c; $i++){
					$item_option = $_POST['product_option'][$i];
					$unitCost = ($_POST['unit_cost'][$i]);
					$costunit = $this->site->editCalculateAVGCost2017($array_c[$i]['product_id'], $shipping, $array_c[$i]['quantity'], $array_c[$i]['price'], $total_price, $array_c[$i]['unit_cost'], $item_discount  , $this->input->post('discount'), $array_c[$i]['option_id']);
					$avg[$array_c[$i]['product_id']]  = $costunit['avgcost'];
					$ship[$array_c[$i]['product_id']] = $costunit['shipping_cost'];
				}
				$i = 0;
				foreach($products as $p){
					$products[$i]['real_unit_cost'] = $avg[ $p['product_id'] ];
					$i++;
				}
			}
			
            $status = $partial ? $partial : $status;
            if (empty($items)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                foreach ($items as $item) {
                    $item["status"] = $status;
                    $products[] = $item;
                }
                krsort($products);
            }
			
            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = ((($total) * (Float) ($ods[0])) / 100);
                } else {
                    $order_discount = ($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = ($order_discount + $product_discount);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = ($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = ((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = ($product_tax + $order_tax);
            $grand_total = ($total + $order_tax + $this->erp->formatPurDecimal($shipping) - $order_discount);
			
            $data = array(
				'biller_id'         => $this->site->default_biller_id(),
				'reference_no'      => $reference,
				'payment_term'      => $payment_term,
                'supplier_id'       => $supplier_id,
                'supplier'          => $supplier,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $order_tax_id,
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $shipping,
                'grand_total'       => $grand_total,
                'status'            => $status,
                'updated_by'        => $this->session->userdata('user_id'),
                'updated_at'        => date('Y-m-d H:i:s')
				//'expected_date'     => $expected_date
            );
			
            if ($date) {
                $data['date'] = $date;
            }
			
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			if ($_FILES['document1']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document1')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment1'] = $photo;
            }
			if ($_FILES['document2']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document2')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment2'] = $photo;
            }
			//$this->erp->print_arrays($id, $data, $products);
        }
		
        if ($this->form_validation->run() == true && $this->purchases_model->updatePurchase($id, $data, $products)) {
			if($this->Settings->purchase_serial){
				$this->purchases_model->addSerial($serial, $id);
			}
			
			$this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_edited"));
            redirect('purchases');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);

            if ($this->data['inv']->date <= date('Y-m-d', strtotime('-3 months'))) {
                $this->session->set_flashdata('error', lang("purchase_x_edited_older_than_3_months"));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
			
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->erp->fsd($item->expiry) : '');
                $row->qty = $item->quantity;

                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = $item->option_id;
                $row->real_unit_cost = $item->real_unit_cost;
                //$row->cost = $this->erp->formatPurDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
				$row->cost = $item->unit_cost;
                $row->tax_rate = $item->tax_rate_id;
				$row->net_cost = $item->net_unit_cost;
				$row->serial_item = $item->serial_no;
				
				$pii = $this->purchases_model->getPurcahseItemByPurchaseID($id);
				
                unset($row->details, $row->product_details, $row->file, $row->product_group_id);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                if ($row->tax_rate) {
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => $tax_rate, 'options' => $options, 'suppliers' => '' ,'supplier_id' => $item->supplier_id);
                } else {
                    $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => false, 'options' => $options, 'suppliers' => '' ,'supplier_id' => $item->supplier_id);
                }
                $c++;
            }
			
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['purchase'] = $this->purchases_model->getPurchaseByID($id);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('edit_purchase')));
            $meta = array('page_title' => lang('edit_purchase'), 'bc' => $bc);
            $this->page_construct('purchases/edit', $meta, $this->data);
        }
    }
	
	//------------------- Purchases export as Excel and pdf -----------------------
	function getPurchasesAll($pdf = NULL, $excel = NULL)
    {
        $this->erp->checkPermissions('Purchases');

        $sales = $this->input->get('sales') ? $this->input->get('sales') : NULL;

        if ($pdf || $excel) {

            $this->db
                ->select($this->db->dbprefix('sales') . ".date as dates, " . $this->db->dbprefix('sales') . ".reference_no as reference_nos,". $this->db->dbprefix('sales') .".biller as billers,
				" . $this->db->dbprefix('sales') . ".customer as customers, " . $this->db->dbprefix('sales') . ".sale_status as sale_statuses, 
				" . $this->db->dbprefix('sales') . ".grand_total as grand_totals, " . $this->db->dbprefix('sales') . ".paid as paids,
				(" . $this->db->dbprefix('sales') . ". grand_total - paid) as balances,
				" . $this->db->dbprefix('sales') . ".payment_status as payment_statuses");
				//" . $this->db->dbprefix('warehouses') . ".name as wname");
            $this->db->from('sales');
            //$this->db->join('categories', 'categories.id=products.category_id', 'left');
            //$this->db->join('warehouses', 'warehouses.id=products.warehouse', 'left');
            $this->db->group_by("sales.id")->order_by('sales.date desc');
			$this->db->where('sales.reference_no NOT LIKE "SALE/POS%"');
            if ($sales) {
                $this->db->where('sales.id', $sales);
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
                $this->excel->getActiveSheet()->setTitle(lang('Sales List'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('sale_status'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));

                $row = 2;
				
                foreach ($data as $data_row) {
                    //$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->id));
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->dates);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_nos);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->billers);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customers);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->sale_statuses);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($data_row->grand_totals));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($data_row->paids));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($data_row->balances));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($data_row->payment_statuses));
                    //$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->wh);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                $filename = lang('Sales List');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
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
                if ($excel) {
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
    }

    /* ----------------------------------------------------------------------------------------------------------- */

    public function purchase_by_csv()
    {
        $this->erp->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('userfile', $this->lang->line("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $quantity 			= "quantity";
            $product 			= "product";
            $unit_cost 			= "unit_cost";
            $tax_rate 			= "tax_rate";

            $total 				= 0;
            $product_tax 		= 0;
            $order_tax 			= 0;
            $product_discount 	= 0;
            $order_discount 	= 0;
            $percentage 		= '%';

            if (isset($_FILES["userfile"])) {

                $this->load->library('excel');
				$configUpload['upload_path'] = './assets/uploads/excel/';
				$configUpload['allowed_types'] = 'xls|xlsx|csv';
				$configUpload['max_size'] = '5000';
				$this->load->library('upload', $configUpload);
				$this->upload->do_upload('userfile');	
				$upload_data 	= $this->upload->data();
				$file_name 		= $upload_data['file_name']; 
				$extension		= $upload_data['file_ext']; 

				$objReader 		= PHPExcel_IOFactory::createReader('Excel2007');
				$objReader->setReadDataOnly(true); 	
				$objPHPExcel	= $objReader->load('./assets/uploads/excel/'.$file_name);		 
				$totalrows 		= $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();     	 
				$objWorksheet 	=$objPHPExcel->setActiveSheetIndex(0);
				
				$final = array();
				
				for($i = 2; $i <= $totalrows; $i++)
				{
					$p_code = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
					if($p_code){
						$p_reference = $this->site->getReference('po');
						
						if(!$this->site->getProductByCode($p_code)){
							$this->session->set_flashdata('error',lang("pr_not_found") . " ( " . $p_code . " ) " . lang('in_inventory') );
							redirect($_SERVER["HTTP_REFERER"]);
						}
					}
					$supplier = $objWorksheet->getCellByColumnAndRow(5,$i)->getValue();
					if($supplier){
						if(!$this->site->getCompanyByID($supplier)){
							$this->session->set_flashdata('error',lang("supplier_no") . " ( " . $supplier . " ) " . lang('not_found.') );
							redirect($_SERVER["HTTP_REFERER"]);
						}
					}
					$serial = $objWorksheet->getCellByColumnAndRow(3,$i)->getValue();
					if($serial){
						if($this->purchases_model->getSerialProducts($serial)){
							$this->session->set_flashdata('error',lang("this_serial_number") . " ( " . $serial . " ) " . lang('alreay_have.') );
							redirect($_SERVER["HTTP_REFERER"]);
						}
					}
					
					
					$final[] = array(
						'code'				=> $objWorksheet->getCellByColumnAndRow(0,$i)->getValue(),
						'net_unit_cost' 	=> $objWorksheet->getCellByColumnAndRow(1,$i)->getValue(),
						'quantity'			=> $objWorksheet->getCellByColumnAndRow(2,$i)->getValue(),
						'serial_no'			=> $objWorksheet->getCellByColumnAndRow(3,$i)->getValue(),
						'warehouse_code'	=> $objWorksheet->getCellByColumnAndRow(4,$i)->getValue(),
						'supplier_id'		=> $objWorksheet->getCellByColumnAndRow(5,$i)->getValue(),
						'reference_no'		=> $p_reference, 
						'date'				=> $objWorksheet->getCellByColumnAndRow(6,$i)->getValue(),
						'status'			=> $objWorksheet->getCellByColumnAndRow(7,$i)->getValue(),
						'payment_status'	=> $objWorksheet->getCellByColumnAndRow(8,$i)->getValue(),
						'discount'			=> $objWorksheet->getCellByColumnAndRow(9,$i)->getValue()
					);
				}
				
                $rw 						= 2;
                $date 						= '';
                $reference 					= '';
                $supplier_id 				= '';
				$purchase_item_supplier_id 	= '';
                $biller_id 					= '';
                $status 					= '';
                $payment_term 				= '';
                $payment_status 			= '';
                $shipping 					= '';
                $order_discount 			= '';
                $order_tax 					= '';
                $supplier 					= '';
                $biller 					= '';
                $products 					= array();
                $pr_tax 					= '';
				$bak_ref 					= '';
                $old_reference 				= '';
				$warehouse_code 			= '';
				
				foreach($final as $items){
					$supplier_details = $this->site->getSupplierNameByID($items['supplier_id']);
					$supplier = $supplier_details->company ? $supplier_details->company : $supplier_details->name;
					
					//================ Discount =================
					
					if (isset($items['discount']) && $this->Settings->product_discount) {
						$discount = $items['discount'];
						$dpos = strpos($discount, $percentage);
						if ($dpos !== false) {
							$pds = explode("%", $discount);
							$pr_discount = (($this->erp->formatDecimal($items['net_unit_cost'])) * (Float) ($pds[0])) / 100;
						} else {
							$pr_discount = $this->erp->formatDecimal($discount);
						}
					} else {
						$pr_discount = 0;
					}
					
					$pr_item_discount = $this->erp->formatDecimal($pr_discount * $items['quantity']);
					
					//===================== End =================
					
					$product_details = $this->products_model->getProductByCode(trim($items['code']));
					
					//================ Tax ===================
					
					$pr_tax = $product_details->tax_rate;
                    $tax_details = $this->site->getTaxRateByID($pr_tax);
					
					if ($tax_details->type == 1 && $tax_details->rate != 0) {
						if ($product_details && $product_details->tax_method == 1) {
							$pbt = (($items['net_unit_cost']) / (1 + ($tax_details->rate / 100)));
							$item_tax = $this->erp->formatDecimal(($items['net_unit_cost']) - $pbt);
							$tax = $tax_details->rate . "%";
						} else {
							$pbt = (($items['net_unit_cost']) / (1 + ($tax_details->rate / 100)));
							$item_tax = $this->erp->formatDecimal(($items['net_unit_cost']) - $pbt);
							$tax = $tax_details->rate . "%";
						}
					} elseif ($tax_details->type == 2) {

						if ($product_details && $product_details->tax_method == 1) {
							$pbt = (($items['net_unit_cost']) / (1 + ($tax_details->rate / 100)));
							$item_tax = $this->erp->formatDecimal(($items['net_unit_cost']) - $pbt);
							$tax = $tax_details->rate . "%";
						} else {
							$pbt = (($items['net_unit_cost']) / (1 + ($tax_details->rate / 100)));
							$item_tax = $this->erp->formatDecimal(($items['net_unit_cost']) - $pbt);
							$tax = $tax_details->rate . "%";
						}
						$item_tax = $this->erp->formatDecimal($tax_details->rate);
						$tax = $tax_details->rate;
					}
					$pr_item_tax = $this->erp->formatDecimal($item_tax * $items['quantity']);
					
					//================ End ===================
					
					$warehouse_id = $this->purchases_model->getWarehouseIDByCode(trim($items['warehouse_code']));
					
					$subtotal = $this->erp->formatDecimal(($items['net_unit_cost'] * $items['quantity']) - $pr_item_discount);
					
					$products[] = array(
						'product_id' 		=> $product_details->id,
						'product_code' 		=> $items['code'],
						'product_name' 		=> $product_details->name,
						'net_unit_cost' 	=> $items['net_unit_cost'] -$item_tax ,
						'quantity'	 		=> $items['quantity'],
						'quantity_balance' 	=> $items['quantity'],
						'warehouse_id' 		=> $warehouse_id,
						'item_tax' 			=> $pr_item_tax,
						'tax_rate_id' 		=> $pr_tax,
						'tax' 				=> $tax,
						'discount' 			=> $items['discount'],
						'item_discount' 	=> $pr_item_discount,
						'subtotal' 			=> $subtotal,
						'date' 				=> date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($items['date'])),
						'status' 			=> $items['status'],
						'unit_cost' 		=> $this->erp->formatDecimal($items['net_unit_cost']),
						'real_unit_cost' 	=> $this->erp->formatDecimal($items['net_unit_cost'] - $pr_item_discount),
						'serial_no'			=> $items['serial_no'],
						'type'				=> $product_details->type,
						'transaction_type'	=> 'purchase',
						'reference_no'		=> $items['reference_no']
					);
					
					//============= Add to Purchase ==============//
					$data[] = array(
						'reference_no' 		=> $items['reference_no'],
						'date' 				=> date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($items['date'])),
						'expected_date' 	=> date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($items['date'])),
						'supplier_id' 		=> $items['supplier_id'],
						'supplier' 			=> $supplier,
						'total' 			=> $this->erp->formatDecimal($subtotal),
						'product_discount' 	=> $this->erp->formatDecimal($pr_item_discount),
						'total_discount' 	=> $pr_item_discount,
						'product_tax' 		=> $this->erp->formatDecimal($pr_item_tax),
						'total_tax' 		=> $pr_item_tax,
						'grand_total' 		=> $subtotal,
						'status' 			=> $items['status'],
						'payment_status' 	=> $items['payment_status'],
						'created_by' 		=> $this->session->userdata('user_id'),
						'warehouse_id' 		=> $warehouse_id
					);
					
					$out  = array();
					foreach ($data as $key => $value){
						if (array_key_exists($value['reference_no'], $out)){
							$out[$value['reference_no']]['reference_no'] = $value['reference_no'];
							
							$out[$value['reference_no']]['date'] = $value['date'];
							
							$out[$value['reference_no']]['expected_date'] = $value['expected_date'];
							
							$out[$value['reference_no']]['supplier_id'] = $value['supplier_id'];
							
							$out[$value['reference_no']]['supplier'] = $value['supplier'];
							
							$out[$value['reference_no']]['total'] += $value['total'];
							
							$out[$value['reference_no']]['product_discount'] += $value['product_discount'];
							
							$out[$value['reference_no']]['warehouse_id'] = $value['warehouse_id'];
							
							$out[$value['reference_no']]['total_discount'] += $value['total_discount'];
							
							$out[$value['reference_no']]['product_tax'] += $value['product_tax'];
							
							$out[$value['reference_no']]['total_tax'] += $value['total_tax'];
							
							$out[$value['reference_no']]['grand_total'] += $value['grand_total'];
							
							$out[$value['reference_no']]['status'] = $value['status'];
							
							$out[$value['reference_no']]['payment_status'] = $value['payment_status'];
							
							$out[$value['reference_no']]['created_by'] = $value['created_by'];
							
						} else {
							$out[$value['reference_no']] = array(
								'reference_no' 	=> $value['reference_no'],
								'date' 			=> $value['date'],
								'expected_date' => $value['expected_date'],
								'supplier_id' 	=> $value['supplier_id'],
								'supplier' 		=> $value['supplier'],
								'total' 		=> $value['total'],
								'product_discount' => $value['product_discount'],
								'warehouse_id' 	=> $value['warehouse_id'],
								'total_discount'=> $value['total_discount'],
								'product_tax' 	=> $value['product_tax'],
								'total_tax' 	=> $value['total_tax'],
								'grand_total' 	=> $value['grand_total'],
								'status' 		=> $value['status'],
								'payment_status' => $value['payment_status'],
								'created_by' 	=> $value['created_by']
							);
						}
					}
					$invoice = array_values($out);
				}
				
				//$this->erp->print_arrays($products, $invoice);
				
            }
        }

        if ($this->form_validation->run() == true && $this->purchases_model->import_csv($invoice, $products)) {
            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            redirect("purchases");
        } else {

            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['ponumber'] = $this->site->getReference('po');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('import_purchase')));
            $meta = array('page_title' => lang('import_purchase'), 'bc' => $bc);
            $this->page_construct('purchases/purchase_by_csv', $meta, $this->data);
        }
    }

    /* --------------------------------------------------------------------------- */

    public function delete($id = null)
    {
        $this->erp->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deletePurchase($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("purchase_deleted");die();
            }
            $this->session->set_flashdata('message', lang('purchase_deleted'));
            redirect("purchases");
        }
    }

    /* --------------------------------------------------------------------------- */

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
		
        $spos = strpos($term, '%');
        if ($spos !== false) {
            $st = explode("%", $term);
            $sr = trim($st[0]);
            $option = trim($st[1]);
        } else {
            $sr = $term;
            $option = '';
        }
		$user_setting = $this->site->getUserSetting($this->session->userdata('user_id'));
        $rows = $this->purchases_model->getProductNames($term, $user_setting->purchase_standard, $user_setting->purchase_combo, $user_setting->purchase_digital, $user_setting->purchase_service, $user_setting->purchase_category);

        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option = false;
                $row->item_tax_method = $row->tax_method;
                $options = $this->purchases_model->getProductOptions($row->id);
                if ($options) {

                    $opt = current($options);
                    if (!$option) {
                        $option = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->cost = 0;
                }
                $row->option = $option;
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                } else {
                    $row->cost = $row->cost;
                    if ($supplier_id == $row->supplier1 && (!empty($row->supplier1price)) && $row->supplier1price != 0) {


                        $row->cost = $row->supplier1price;

                    } elseif ($supplier_id == $row->supplier2 && (!empty($row->supplier2price)) && $row->supplier2price != 0) {


                        $row->cost = $row->supplier2price;

                    } elseif ($supplier_id == $row->supplier3 && (!empty($row->supplier3price)) && $row->supplier3price != 0) {


                        $row->cost = $row->supplier3price;

                    } elseif ($supplier_id == $row->supplier4 && (!empty($row->supplier4price)) && $row->supplier4price != 0) {


                        $row->cost = $row->supplier4price;

                    } elseif ($supplier_id == $row->supplier5 && (!empty($row->supplier5price)) && $row->supplier5price != 0) {


                        $row->cost = $row->supplier5price;

                    }
                }
                $row->real_unit_cost = $row->cost;
				
                $row->expiry = '';
                $row->qty = 1;
                $row->quantity_balance = '';
                $row->discount = '0';
                unset($row->details, $row->product_details, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
                if ($row->tax_rate) {
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => $tax_rate, 'options' => $options);
                } else {
                    $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => false, 'options' => $options);
                }
                $r++;
            }
            echo json_encode($pr);
        } else {
            echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	public function suggests()
    {
        $term = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id', true);
		if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
		
        $spos = strpos($term, '%');
        if ($spos !== false) {
            $st = explode("%", $term);
            $sr = trim($st[0]);
            $option = trim($st[1]);
        } else {
            $sr = $term;
            $option = '';
        }
		$user_setting = $this->site->getUserSetting($this->session->userdata('user_id'));
        $rows = $this->purchases_model->getProductNumber($term, $user_setting->purchase_standard, $user_setting->purchase_combo, $user_setting->purchase_digital, $user_setting->purchase_service, $user_setting->purchase_category);

        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option = false;
                $row->item_tax_method = $row->tax_method;
                $options = $this->purchases_model->getProductOptions($row->id);
                if ($options) {

                    $opt = current($options);
                    if (!$option) {
                        $option = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->cost = 0;
                }
                $row->option = $option;
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                } else {
                    $row->cost = $row->cost;
                    if ($supplier_id == $row->supplier1 && (!empty($row->supplier1price)) && $row->supplier1price != 0) {


                        $row->cost = $row->supplier1price;

                    } elseif ($supplier_id == $row->supplier2 && (!empty($row->supplier2price)) && $row->supplier2price != 0) {


                        $row->cost = $row->supplier2price;

                    } elseif ($supplier_id == $row->supplier3 && (!empty($row->supplier3price)) && $row->supplier3price != 0) {


                        $row->cost = $row->supplier3price;

                    } elseif ($supplier_id == $row->supplier4 && (!empty($row->supplier4price)) && $row->supplier4price != 0) {


                        $row->cost = $row->supplier4price;

                    } elseif ($supplier_id == $row->supplier5 && (!empty($row->supplier5price)) && $row->supplier5price != 0) {


                        $row->cost = $row->supplier5price;

                    }
                }
                $row->real_unit_cost = $row->cost;
				
                $row->expiry = '';
                $row->qty = 1;
                $row->quantity_balance = '';
                $row->discount = '0';
                unset($row->details, $row->product_details, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
                if ($row->tax_rate) {
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => $tax_rate, 'options' => $options);
                } else {
                    $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => false, 'options' => $options);
                }
                $r++;
            }
			echo json_encode($pr);
        } else {
            echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	/* --------------------------------------------------------------------------------------------- */

	function getReferences($term = NULL, $limit = NULL)
    {
        // $this->erp->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        if (strlen($term) < 1) {
            return FALSE;
        }
        $limit = $this->input->get('limit', TRUE);
		
        $rows['results'] = $this->purchases_model->getPurchasesReferences($term, $limit);
        echo json_encode($rows);
    }
	
    public function purchase_actions()
    {
		/*
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
		*/
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    foreach ($_POST['val'] as $id) {
						$this->erp->checkPermissions('delete');
                        $this->purchases_model->deletePurchase($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("purchases_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'purchase_tax'){
					
					$ids = $_POST['val'];
					
					$this->data['modal_js'] = $this->site->modal_js();
					$this->data['ids'] = $ids;

				} elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
					$this->erp->actionPermissions('pdf', 'purchases');
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('purchases'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchase_status'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('balance'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('payment_status')); 

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $purchase = $this->purchases_model->getPurchaseByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($purchase->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $purchase->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $purchase->supplier);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, lang($purchase->status));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->erp->formatMoneyPurchase($purchase->grand_total));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->erp->formatMoneyPurchase($purchase->paid));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->erp->formatMoneyPurchase($purchase->grand_total - $purchase->paid));
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $purchase->payment_status); 
                        $row++;
                    } 
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'purchases_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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
                $this->session->set_flashdata('error', $this->lang->line("no_purchase_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function payments($id = null)
    {
        $this->erp->checkPermissions(false, true);

        $this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
        $this->load->view($this->theme . 'purchases/payments', $this->data);
    }

    public function payment_note($id = null)
    {

        $payment = $this->purchases_model->getPaymentByID($id);
        $inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line("payment_note");

        $this->load->view($this->theme . 'purchases/payment_note', $this->data);
    }
	
	function cash_receipt($id = NULL)
    {
		$this->erp->checkPermissions('index');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		
		$this->load->model('pos_model');
		$this->data['pos'] = $this->pos_model->getSetting();
		
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
		$payment = $this->purchases_model->getPaymentByID($id);
        $inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);	
		$payments = $this->purchases_model->getCurrentBalance($id, $inv->id);
		
		$current_balance = $inv->grand_total;
		foreach($payments as $curr_pay) {
			if ($curr_pay->id < $id) {
				$current_balance -= $curr_pay->amount;
			}
		}
		
		$this->data['curr_balance'] = $current_balance;
		$this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line("payment_note");

		//$this->erp->print_arrays($payment);
		
        $this->load->view($this->theme . 'sales/cash_receipt', $this->data);
    }
	
    public function add_payment($id = null)
    {
        $this->erp->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $rows = $this->purchases_model->getStatus($id);
		if($rows->status=="ordered"){
			$this->session->set_flashdata('error', lang("purchase_status_ordered_can_not_add_payment"));
            die('<script>window.location.replace("'.$_SERVER["HTTP_REFERER"].'");</script>');
		}
        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pp'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => strip_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'sent',
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->erp->print_arrays($payment);

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPayment($payment)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $purchase = $this->purchases_model->getPurchaseByID($id);
            $this->data['inv'] = $purchase;
            $this->data['payment_ref'] = ''; //$this->site->getReference('pp');
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['suppliers'] = $this->purchases_model->getSuppliersByID($id);

            $this->load->view($this->theme . 'purchases/add_payment', $this->data);
        }
    }
	
	public function combine_payment($id = null)
    {
        $this->erp->checkPermissions('payments', true);
        $this->load->helper('security');
        $arr = array();
        if ($this->input->get('data'))
        {
            $arr = explode(',', $this->input->get('data'));
        }

        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                //$payment['attachment'] = $photo;
            }
			$purchase_id_arr = $this->input->post('purchase_id');
			$amount_paid_arr = $this->input->post('amount_paid_line');
			$i = 0;
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pp');
			foreach($purchase_id_arr as $purchase_id){
				$payment = array(
					'date' => $date,
					'purchase_id' => $purchase_id,
					'reference_no' => $reference_no,
					'amount' => $amount_paid_arr[$i],
					'paid_by' => $this->input->post('paid_by'),
					'cheque_no' => $this->input->post('cheque_no'),
					'cc_no' => $this->input->post('pcc_no'),
					'cc_holder' => $this->input->post('pcc_holder'),
					'cc_month' => $this->input->post('pcc_month'),
					'cc_year' => $this->input->post('pcc_year'),
					'cc_type' => $this->input->post('pcc_type'),
					'note' => $this->erp->clear_tags($this->input->post('note')),
					'created_by' => $this->session->userdata('user_id'),
					'type' => 'sent',
					'attachment' => $photo
				);
				$this->purchases_model->addPayment($payment);
				$i++;
			}
            

            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);

            //$this->erp->print_arrays($payment);

        }else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $purchase = $this->purchases_model->getCombinePaymentById($arr);
            $this->data['combine_purchases'] = $purchase;
            $this->data['payment_ref'] = ''; //$this->site->getReference('pp');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/combine_payment', $this->data);
        } 
    }
	
    public function edit_payment($id = null)
    {
        $this->erp->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->erp->clear_tags($this->input->post('note')),
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->erp->print_arrays($payment);

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updatePayment($id, $payment)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
            redirect("purchases");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['suppliers'] = $this->purchases_model->getSuppliersByID($id);
            $this->data['payment'] = $this->purchases_model->getPaymentByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            
            $this->load->view($this->theme . 'purchases/edit_payment', $this->data);
        }
    }

    public function delete_payment($id = null)
    {
        $this->erp->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deletePayment($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function expenses($id = null)
    {
        $this->erp->checkPermissions('expense', null, 'purchases');
		$this->data['users'] = $this->purchases_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('expenses')));
        $meta = array('page_title' => lang('expenses'), 'bc' => $bc);
        $this->page_construct('purchases/expenses', $meta, $this->data);
    }

    public function getExpenses($id = null)
    {
        $this->erp->checkPermissions('expense', null, 'purchases');
		if ($this->input->get('user')) {
            $user_query = $this->input->get('user');
        } else {
            $user_query = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
		if ($this->input->get('note')) {
            $note = $this->input->get('note');
        } else {
            $note = NULL;
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
		
        $detail_link = anchor('purchases/expense_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('expense_note'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link = anchor('purchases/edit_expense/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense'), 'data-toggle="modal" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_expense") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchases/delete_expense/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_expense') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">' . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $detail_link . '</li>
				<li>' . $edit_link . '</li>
				<li>' . $delete_link . '</li>
			</ul>
		</div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select($this->db->dbprefix('expenses') . ".id as id, date, reference, erp_expense_categories.name , expenses.amount ,note, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as user, attachment", false)
            ->from('expenses')
            ->join('users', 'users.id=expenses.created_by', 'left')
			->join('gl_trans', 'gl_trans.account_code = expenses.account_code', 'left')
			->join('erp_expense_categories','erp_expense_categories.id=erp_expenses.account_code','LEFT')
            ->group_by('expenses.id');

        if (!$this->Owner && !$this->Admin) {
            $this->datatables->where('expenses.created_by', $this->session->userdata('user_id'));
        }
		if ($user_query) {
			$this->datatables->where('expenses.created_by', $user_query);
		}
		if ($reference_no) {
			$this->datatables->where('expenses.reference', $reference_no);
		}
		if ($biller) {
			$this->datatables->where('expenses.biller_id', $biller);
		}
		if ($note) {
			$this->datatables->where("expenses.note LIKE '%" . $note . "%'");
		}
		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('expenses').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function expense_note($id = null)
    {
        $expense = $this->purchases_model->getExpenseByID($id);
        $this->data['user'] = $this->site->getUser($expense->created_by);
        $this->data['expense'] = $expense;
        $this->data['page_title'] = $this->lang->line("expense_note");
        $this->load->view($this->theme . 'purchases/expense_note', $this->data);
    }
	
	function expense_reference_check( $reference_no )
	{
		$check_expense_reference = $this->purchases_model->check_expense_reference($reference_no);
	 
		if ($check_expense_reference == TRUE)
		{
			$this->form_validation->set_message('reference_no', lang('reference_no_expense_exist'));
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

    public function add_expense()
    {
        
		$this->erp->checkPermissions('add-expenses', true);
		
        //$this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
		if($this->input->post('reference')){
			$this->form_validation->set_rules('reference', lang("reference"), 'trim|callback_expense_reference_check');
		}

        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			
            $data_payment = array(
                            'date'          => $date,
							'reference_no'	=> $this->site->getReference('pay'),
                            //'amount'        => abs($this->input->post('amount')),
							'amount'        => abs($this->input->post('amount')),
                            'paid_by'       => 'cash',
                            'created_by'    => $this->session->userdata('user_id'),
                            'note'          => $this->input->post('note', true),
							'type'			=> 'sent'
                        );
            $data = array(
                'date' => $date,
                'reference' => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
				//'amount'  => $this->input->post('amount'),
                'amount' 	=> $this->input->post('amount'),
                'created_by'	=> $this->session->userdata('user_id'),
                'note' 		=> $this->input->post('note', true),
				'account_code' 	=> $this->input->post('account_section'),
				'biller_id'	=> $this->input->post('biller'),
				'bank_code' => $this->input->post('paid_by')
            );
			
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
           
        } elseif ($this->input->post('add_expense')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('purchases/expenses');
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addExpense($data, $data_payment)) {
            $this->session->set_flashdata('message', lang("expense_added"));
            redirect('purchases/expenses');
        } else {
			$this->load->model('accounts_model');
             $this->load->model('pos_model');
            $this->pos_settings = $this->pos_model->getSetting();
            $this->data['pos_settings'] = $this->pos_settings;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber'] = ''; //$this->site->getReference('ex');
			//$this->data['chart_accounts'] = $this->accounts_model->getAllChartAccountIn('30,50,60,80');
			$this->data['chart_accounts'] =$this->accounts_model->getAllAccount();
			$this->data['paid_by'] = $this->accounts_model->getAllChartAccountBank();
			$this->data['currency'] = $this->site->getCurrency();
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/add_expense', $this->data);
        }
    }

    public function edit_expense($id = null)
    {
        $this->erp->checkPermissions('edit-expense', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data_payment = array(
				'date'          => $date,
				'reference_no'	=> $this->site->getReference('pay'),
				'amount'        => abs($this->input->post('amount')),
				'paid_by'       => 'cash',
				'created_by'    => $this->session->userdata('user_id'),
				'note'          => $this->input->post('note', true),
				'type'			=> 'sent'
			);
            $data = array(
                'date' => $date,
                'reference' => $this->input->post('reference'),
                'amount' => $this->input->post('amount'),
                'note' => $this->input->post('note', true),
				'account_code' => $this->input->post('account_code'),
				'biller_id'	=> $this->input->post('biller'),
				'bank_code' => $this->input->post('paid_by'),
				'updated_by' => $this->session->userdata('user_id')
            );
			
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            //$this->erp->print_arrays($data);

        } elseif ($this->input->post('edit_expense')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updateExpense($id, $data, $data_payment)) {
            $this->session->set_flashdata('message', lang("expense_updated"));
            redirect("purchases/expenses");
        } else {
			$this->load->model('accounts_model');
            $this->load->model('pos_model');
            $this->pos_settings 		= $this->pos_model->getSetting();
            $this->data['pos_settings'] = $this->pos_settings;
            $this->data['error'] 		= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['KHM'] 			= $this->purchases_model->getKHM();
            $this->data['expense'] 		= $this->purchases_model->getExpenseByID($id);
            $this->data['modal_js'] 	= $this->site->modal_js();
			$this->data['currency'] 	= $this->site->getCurrency();
			//$this->data['chart_accounts'] = $this->accounts_model->getAllChartAccountIn('30,50,60,80');
			$this->data['chart_accounts'] =$this->accounts_model->getAllAccount();
			$this->data['paid_by'] 		= $this->accounts_model->getAllChartAccountBank();
			$this->data['billers'] 		= $this->site->getAllCompanies('biller');
            $this->load->view($this->theme . 'purchases/edit_expense', $this->data);
        }
    }

    public function delete_expense($id = null)
    {
        $this->erp->checkPermissions('delete-expense', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $expense = $this->purchases_model->getExpenseByID($id);
        if ($this->purchases_model->deleteExpense($id)) {
            if ($expense->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
            echo lang("expense_deleted");
        }
    }

    public function expense_actions()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }		
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {					
                    foreach ($_POST['val'] as $id) {
						//$this->erp->checkPermissions('delete-expense', null, 'purchases');	
                        $this->purchases_model->deleteExpense($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("expenses_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('expenses'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('category_expense'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('amount'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('created_by'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $expense = $this->purchases_model->getExpenses($id);
						//$this->erp->print_arrays($expense);
                        $user = $this->site->getUser($expense->created_by);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($expense->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $expense->reference);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $expense->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $expense->amount);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $expense->note);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $expense->user);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'expenses_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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
                $this->session->set_flashdata('error', $this->lang->line("no_expense_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	/* Purchase Return */
	public function add_purchase_return($id = null)
    {
        $this->erp->checkPermissions();

        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        //$this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

        $this->session->unset_userdata('csrf_token');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        // $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');

        if ($this->form_validation->run() == true) {
            $purchase = $this->purchases_model->getPurchaseByID($id);
			
			$warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
			$biller_id = $this->input->post('biller');
			
			$supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
			
            $quantity = "quantity";
            $product = "product";
            $unit_cost = "unit_cost";
            $tax_rate = "tax_rate";
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			
			$reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep');

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $note = $this->erp->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
                //$purchase_item_id = $_POST['purchase_item_id'][$r];
				$purchase_ref = $_POST['purchase_reference'][$r];
				
                $item_option = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                //$option_details = $this->purchases_model->getProductOptionByID($item_option);
                $real_unit_cost = $this->erp->formatDecimal($_POST['real_unit_cost'][$r]);
                $unit_cost = $this->erp->formatDecimal($_POST['unit_cost'][$r]);
                $item_quantity = $_POST['quantity'][$r];
                $item_expiry = isset($_POST['expiry'][$r]) ? $_POST['expiry'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
				
				$purchase_item_id = $this->purchases_model->getPurchaseIDByRef($purchase_ref);
				//$purchase_item_id = $purchase->id;

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);

                    $item_type = $product_details->type;
                    $item_name = $product_details->name;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = (($this->erp->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100;
                        } else {
                            $pr_discount = $this->erp->formatDecimal($discount);
                        }
                    } else {
                        $pr_discount = 0;
                    }
                    // $unit_cost = $this->erp->formatDecimal($unit_cost - $pr_discount);
                    $pr_item_discount = $this->erp->formatDecimal($pr_discount * $item_quantity);
                    $product_discount += $pr_item_discount;

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if (!$product_details->tax_method) {
                                $item_tax = $this->erp->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate));
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->erp->formatDecimal((($unit_cost) * $tax_details->rate) / 100);
                                $tax = $tax_details->rate . "%";
                            }

                        } elseif ($tax_details->type == 2) {

                            $item_tax = $this->erp->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->erp->formatDecimal($item_tax * $item_quantity);

                    } else {
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $tax = "";
                    }

                    $item_net_cost = $product_details->tax_method ? $this->erp->formatDecimal($unit_cost - $pr_discount) : $this->erp->formatDecimal($unit_cost - $pr_discount);
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_quantity) + $pr_item_tax);

                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        // 'unit_cost' => $this->erp->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->erp->formatDecimal($subtotal),
                        'real_unit_cost' => $real_unit_cost,
                        'purchase_item_id' => $purchase_item_id,
                    );

                    $total += $item_net_cost * $item_quantity;
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('order_discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->erp->formatDecimal((($total + $product_tax) * (Float) ($ods[0])) / 100);
                } else {
                    $order_discount = $this->erp->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->erp->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->erp->formatDecimal((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->erp->formatDecimal($product_tax + $order_tax);
            $grand_total = $this->erp->formatDecimal($this->erp->formatDecimal($total) + $total_tax - $this->erp->formatDecimal($return_surcharge) - $order_discount);
            $data = array('date' => $date,
                'purchase_id' => $id,
                'reference_no' => $reference,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $this->erp->formatDecimal($total),
                'product_discount' => $this->erp->formatDecimal($product_discount),
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $this->erp->formatDecimal($product_tax),
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'surcharge' => $this->erp->formatDecimal($return_surcharge),
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
            );
			/*
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			*/

            // $this->erp->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->returnPurchases($data, $products)) {
            $this->session->set_flashdata('message', lang("return_purchase_added"));
            redirect("purchases/return_purchases");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
            
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            $c = rand(100000, 9999999);
            if(isset($inv_items)){
                foreach ($inv_items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->erp->fsd($item->expiry) : '');
                    $row->qty = $item->quantity;
                    $row->oqty = $item->quantity;
                    $row->purchase_item_id = $item->id;
                    $row->supplier_part_no = $item->supplier_part_no;
                    $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                    $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                    $row->discount = $item->discount ? $item->discount : '0';
                    $options = $this->purchases_model->getProductOptions($row->id);
                    $row->option = !empty($item->option_id) ? $item->option_id : '';
                    $row->real_unit_cost = $item->real_unit_cost;
                    $row->cost = $this->erp->formatDecimal($item->net_unit_cost + ($item->item_discount / ($item->quantity ?$item->quantity:0)));
                    $row->tax_rate = $item->tax_rate_id;
                    unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                    $ri = $this->Settings->item_addition ? $row->id : $c;
                    if ($row->tax_rate) {
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => $tax_rate, 'options' => $options, 'purchase_ref' => '', 'quantity_received' => 0);
                    } else {
                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => false, 'options' => $options, 'purchase_ref' => '', 'quantity_received' => 0);
                    }
                    $c++;
                }
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['ponumber'] = ''; //$this->site->getReference('po');
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('add_purchase_return')));
            $meta = array('page_title' => lang('add_purchase_return'), 'bc' => $bc);
            $this->page_construct('purchases/add_purchase_return', $meta, $this->data);
        }
    }

	public function expense_by_csv(){
		
		$this->erp->checkPermissions();
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
			if (isset($_FILES["userfile"])) {
                $this->load->library('excel');
				$configUpload['upload_path'] = './assets/uploads/excel/';
				$configUpload['allowed_types'] = 'xls|xlsx|csv';
				$configUpload['max_size'] = '5000';
				$this->load->library('upload', $configUpload);
				$this->upload->do_upload('userfile');	
				$upload_data 	= $this->upload->data();
				$file_name 		= $upload_data['file_name']; 
				$extension		= $upload_data['file_ext']; 

				$objReader 		= PHPExcel_IOFactory::createReader('Excel2007');
				$objReader->setReadDataOnly(true); 	
				$objPHPExcel	= $objReader->load('./assets/uploads/excel/'.$file_name);		 
				$totalrows 		= $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();     	 
				$objWorksheet 	= $objPHPExcel->setActiveSheetIndex(0);	
				
				for($i=2; $i<= $totalrows ; $i++)
				{
					$p_expense	= 	$objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
					if(!$this->purchases_model->getProductExpense($p_expense)){
						$this->session->set_flashdata('error', lang("product_expense") .' ('. $p_expense .') '. lang('is_invalid') );
						redirect('purchases/expense_by_csv');
					}
					/*
					$paid_by	= 	$objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
					if(!$this->accounts_model->getSectionIdByCode($paid_by)){
						$this->session->set_flashdata('error', lang("product_expense") .' ('. $p_expense .') '. lang('is_invalid') );
						redirect('purchases/expense_by_csv');
					}
					*/
				}
				
				for($i=2; $i<= $totalrows ; $i++)
				{
					
					$expense_no = $this->site->getReference('ex');
					
					$point_of_sale  = 	$objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
					$date	 		=	$objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
					$p_expense 		= 	$objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
					$paid_by	 	= 	$objWorksheet->getCellByColumnAndRow(3,$i)->getValue();
					$amount			=	$objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
					$note	 		= 	$objWorksheet->getCellByColumnAndRow(5,$i)->getValue();
					
					$exp_date[] 		= date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($date)) ;
					$exp_reference[] 	= trim($expense_no);
					$exp_amount[] 		= trim($amount);
					$exp_note[] 		= trim($note);
					$exp_created_by[] 	= trim($this->session->userdata('user_id'));
					$exp_expense[] 		= trim($p_expense);
					$exp_paid_by[] 		= trim($paid_by);
					$exp_biller_id[] 	= trim($point_of_sale);
					
				}

                $keys = array('date', 'reference', 'amount', 'note', 'created_by', 'account_code', 'bank_code', 'biller_id');
				
				$final = array();
				foreach (array_map(null, $exp_date, $exp_reference, $exp_amount, $exp_note, $exp_created_by, $exp_expense, $exp_paid_by, $exp_biller_id) as $items => $value) {
					$final[] = array_combine($keys, $value);
				}
				
                foreach ($final as $csv_pr) {
                    $data[] = array(
						'date' 			=> $csv_pr['date'],
						'reference' 	=> $csv_pr['reference'],
						'amount' 		=> $csv_pr['amount'],
						'note' 			=> $csv_pr['note'],
						'created_by' 	=> $csv_pr['created_by'],
						'account_code' 	=> $csv_pr['account_code'],
						'bank_code' 	=> $csv_pr['bank_code'],
						'note' 			=> $csv_pr['note'],
						'biller_id' 	=> $csv_pr['biller_id']
					);
                }
				//$this->erp->print_arrays($data);
            }
        }
		
        if ($this->form_validation->run() == true && !empty($final)) {
            $this->purchases_model->addExpenses($data);
            $this->session->set_flashdata('message', lang("expense_added"));
            redirect('purchases/expenses');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('import_expense')));
            $meta = array('page_title' => lang('import_expense'), 'bc' => $bc);
            $this->page_construct('purchases/import_expense', $meta, $this->data);
        }
	}
    
    public function getPurchaseReturnQuantity() {
        if ($this->input->get('purchase_ref')) {
            $purchase_ref = $this->input->get('purchase_ref', TRUE);
        }
        if ($this->input->get('product_id')) {
            $product_id = $this->input->get('product_id', TRUE);
        }
		
        $quantity = $this->purchases_model->getPurchaseItemByRefPID($purchase_ref, $product_id);
        echo json_encode($quantity);
    }
	
	function add_product()
	{
		$this->data['purchase'] = $this->purchases_model->getPurchaseByID($id);
		$this->data['categories'] = $this->site->getAllCategories();
		$this->data['tax_rates'] = $this->site->getAllTaxRates();
		$this->data['warehouses'] = $this->site->getAllWarehouses();    
      	
		$this->data['brands'] = $this->site->getAllBrands();
		$this->data['modal_js'] = $this->site->modal_js();
		
		$this->load->view($this->theme . 'products/add_product', $this->data);
	}

    function addProducts()
    {   
	
	    $this->erp->checkPermissions();
        $this->load->helper('security');
		
		$this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]');
		
		$warehouse_qty = array();
        if ($this->form_validation->run() == true) {
            $tax_rate 	= $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : NULL;
			$p_price 	= $this->erp->formatDecimal($this->input->post('price'));
			$vat_per 	= $tax_rate->rate / 100;
			$multiplier = $vat_per + 1;
			
			$gdc_price 	= $p_price + ($p_price * $vat_per);
			
            if($this->input->post('inactive')) {
				$inactived = $this->input->post('inactive');
			} else {
				$inactived = 0;
			}
			$data = array(
                'code' 				=> $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' 				=> $this->input->post('name'),
                'type' 				=> $this->input->post('type'),
                'category_id' 		=> $this->input->post('category'),
                'subcategory_id' 	=> $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
                'cost' 				=> $this->erp->formatDecimal($this->input->post('cost')),
                'price' 			=> $this->erp->formatDecimal($this->input->post('price')),
                'unit' 				=> $this->input->post('unit'),
                'tax_rate' 			=> $this->input->post('tax_rate'),
                'tax_method' 		=> $this->input->post('tax_method'),
                'alert_quantity' 	=> $this->input->post('alert_quantity'),
                'track_quantity' 	=> $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' 			=> $this->input->post('details'),
                'product_details' 	=> $this->input->post('product_details'),
                'supplier1' 		=> $this->input->post('supplier'),
                'supplier1price' 	=> $this->erp->formatDecimal($this->input->post('supplier_price')),
                'supplier2' 		=> $this->input->post('supplier_2'),
                'supplier2price' 	=> $this->erp->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' 		=> $this->input->post('supplier_3'),
                'supplier3price' 	=> $this->erp->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' 		=> $this->input->post('supplier_4'),
                'supplier4price' 	=> $this->erp->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' 		=> $this->input->post('supplier_5'),
                'supplier5price' 	=> $this->erp->formatDecimal($this->input->post('supplier_5_price')),
                'cf1' 				=> $this->input->post('cf1'),
                'cf2' 				=> $this->input->post('cf2'),
                'cf3' 				=> $this->input->post('cf3'),
                'cf4' 				=> $this->input->post('cf4'),
                'cf5' 				=> $this->input->post('cf5'),
                'cf6' 				=> $this->input->post('cf6'),
                'cf7' 				=> $this->input->post('cf7'),
                'cf8' 				=> $this->input->post('cf8'),
                'cf9' 				=> $this->input->post('cf9'),
				'promotion' 		=> $this->input->post('promotion'),
                'promo_price' 		=> $this->erp->formatDecimal($this->input->post('promo_price')),
                'start_date' 		=> $this->erp->fsd($this->input->post('start_date')),
                'end_date' 			=> $this->erp->fsd($this->input->post('end_date')),
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),           
				'currentcy_code'    => $this->input->post('currency'),
				'inactived' 		=> $inactived,
				'brand_id' 			=> $this->input->post('brand'),
				'is_serial' 		=> $this->input->post('is_serial')
			);
			
			$related_straps = $this->input->post('related_strap');
			for($i=0; $i<sizeof($related_straps); $i++) {
				$product_name = $this->site->getProductByCode($related_straps[$i]);
				$related_products[] = array(
					'product_code' => $this->input->post('code'),
					'related_product_code' => $related_straps[$i],
					'product_name' => $product_name->name,
				);
			}
			$this->erp->print_arrays($data);
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                $wh_total_quantity = 0;
                $pv_total_quantity = 0;
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    if ($this->input->post('wh_qty_' . $warehouse->id)) {
                        $warehouse_qty[] = array(
                            'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                            'quantity' => $this->input->post('wh_qty_' . $warehouse->id),
                            'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : NULL
                        );
                        $wh_total_quantity += $this->input->post('wh_qty_' . $warehouse->id);
                    }
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
							if(isset($_POST['attr_warehouse'][$r]) == NULL){
								$_POST['attr_warehouse'][$r] = '';
							}
							if(isset($_POST['attr_quantity_unit'][$r]) == NULL){
								$_POST['attr_quantity_unit'][$r] = '';
							}
							if(isset($_POST['attr_quantity'][$r]) == NULL){
								$_POST['attr_quantity'][$r] = '';
							}
							if(isset($_POST['attr_cost'][$r]) == NULL){
								$_POST['attr_cost'][$r] = '';
							}
							if(isset($_POST['attr_price'][$r]) == NULL){
								$_POST['attr_price'][$r] = '';
							}
                            $product_attributes[] = array(
                                'name' => $_POST['attr_name'][$r],
                                'warehouse_id' => $_POST['attr_warehouse'][$r],
								'qty_unit' => $_POST['attr_quantity_unit'][$r],
                                'quantity' => $_POST['attr_quantity'][$r],
                                'cost' => $_POST['attr_cost'][$r],
                                'price' => $_POST['attr_price'][$r],
                            );
                            $pv_total_quantity += $_POST['attr_quantity'][$r];
                        }
                    }
                } else {
                    $product_attributes = NULL;
                }
				
            } else {
                $warehouse_qty = NULL;
                $product_attributes = NULL;
            }

            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
                $total_price = 0;
                $c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity_unit'][$r]) && isset($_POST['combo_item_price'][$r])) {
                        $items[] = array(
                            'item_code' => $_POST['combo_item_code'][$r],
							//'qty_unit' => $_POST['combo_item_quantity_unit'][$r],
							'quantity' => $_POST['combo_item_quantity_unit'][$r],
                            'unit_price' => $_POST['combo_item_price'][$r],
                        );
                    }
                    $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity_unit'][$r];
                }
				
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {
                if ($_FILES['digital_file']['size'] > 0) {
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = FALSE;
                    $config['encrypt_name'] = TRUE;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('digital_file')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect("products/add");
                    }
                    $file = $this->upload->file_name;
                    $data['file'] = $file;
                } else {
                    $this->form_validation->set_rules('digital_file', lang("digital_file"), 'required');
                }
                $config = NULL;
                $data['track_quantity'] = 0;
            }
            if (!isset($items)) {
                $items = NULL;
            }
            if ($_FILES['product_image']['size'] > 0) {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("products/add");
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;

                $config['maintain_ratio'] = TRUE;
				$config['width'] = $this->Settings->iwidth;
                $config['height'] = $this->Settings->iheight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
				copy($config['new_image'] , $config['source_image']);
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'right';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }

            if ($_FILES['userfile']['name'][0] != "") {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {

                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect("products/add");
                    } else {
                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = TRUE;
                        //$config['width'] = $this->Settings->twidth;
                        //$config['height'] = $this->Settings->theight;
						$config['width'] = $this->Settings->iwidth;
						$config['height'] = $this->Settings->iheight;

                        $this->image_lib->initialize($config);
						copy($config['new_image'] , $config['source_image']);
                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'right';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }
                        $this->image_lib->clear();
                    }
                }
                $config = NULL;
            } else {
                $photos = NULL;
            }
            $data['quantity'] = isset($wh_total_quantity) ? $wh_total_quantity : 0;
            //$this->erp->print_arrays($data, $warehouse_qty, $product_attributes);
        }
		 if ($this->form_validation->run() == true && $this->purchases_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $related_products)) {
            $this->session->set_flashdata('message', lang("product_added"));
			$this->session->set_flashdata('from_ls_pr', '1');
			if (strpos($_SERVER['HTTP_REFERER'], 'purchases/add') !== false) {
				redirect('purchases');
			}else{
				redirect('products/add');
			}
        }else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		
			$this->data['purchase'] 			= $this->purchases_model->getPurchaseByID($id);
			$this->data['units']				= $this->purchases_model-> getUnits();
			$this->data['currencies']			= $this->purchases_model->getAllCurrencies();
			$this->data['categories'] 			= $this->site->getAllCategories();
			$this->data['tax_rates'] 			= $this->site->getAllTaxRates();
			$this->data['warehouses'] 			= $this->site->getAllWarehouses();  
			$this->data['case'] 				= $this->purchases_model->getCases();	
			$this->data['diameters'] 			= $this->purchases_model->getDiameters();
			$this->data['dial'] 				= $this->products_model->getDials();
			$this->data['strap'] 				= $this->products_model->getStraps();
			$this->data['water'] 				= $this->products_model->getWater();
			$this->data['winding'] 				= $this->products_model->getWinding();
			$this->data['powerreserve'] 		= $this->products_model->getPowerReserve();
			$this->data['buckle'] 				= $this->products_model->getBuckle();
			$this->data['complication'] 		= $this->products_model->getComplication();
			$this->data['products'] 			= $this->site->getAllProducts();
			$this->data['warehouses_products'] 	= $this->site->getAllWarehouses($id);  		
			$this->data['brands'] 				= $this->site->getAllBrands();
			$this->data['modal_js'] 			= $this->site->modal_js();
			
			$this->load->view($this->theme . 'products/add_product', $this->data);
		}
    }
}
