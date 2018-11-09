<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pos extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            redirect('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->load->model('pos_model');
        $this->load->helper('text');
        $this->pos_settings = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : NULL;
        $this->data['pos_settings'] = $this->pos_settings;
        $this->session->set_userdata('last_activity', now());
        $this->lang->load('pos', $this->Settings->language);
        $this->load->library('form_validation');
        
        if(!$this->Owner && !$this->Admin) {
            $gp = $this->site->checkPermissions();
            $this->permission = $gp[0];
            $this->permission[] = $gp[0];
        } else {
            $this->permission[] = NULL;
        }
    }

    function sales($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('index');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('pos_sales')));
        $meta = array('page_title' => lang('pos_sales'), 'bc' => $bc);
        $this->page_construct('pos/sales', $meta, $this->data);
    }

	function pos_list($warehouse_id = NULL){
		$this->erp->checkPermissions('index');
		$this->data['table'] = $warehouse_id;
		$this->data['warehouse_id'] = $warehouse_id;
		$this->load->view('default/views/pos/view_list', $this->data);
	}
	
    function getSales($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('index');

        if ((! $this->Owner || ! $this->Admin) && ! $warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
		
        $detail_link = anchor('sales/invoice_landscap_a5/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $payments_link = anchor('pos/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('pos/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('#', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'class="email_receipt" data-id="$1" data-email-address="$2"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'. lang('actions') . ' <span class="caret"></span></button> <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>';
			if($this->Owner || $this->Admin || $this->GP['sales-payments']){
				$action .= '<li>' . $payments_link . '</li><li>' . $add_payment_link . '</li>';
			}
            if($this->Owner || $this->Admin || $this->GP['sales-edit']){
				$action .= '<li>' . $edit_link . '</li>';
			}
            if($this->Owner || $this->Admin || $this->GP['sales-return_sales']){
				$action .= '<li>' . $return_link . '</li>';
			}
			if($this->Owner || $this->Admin || $this->GP['sales-delete']){
				$action .= '<li>' . $delete_link . '</li>';
			}
        $action .= '</ul></div></div>';

        $this->load->library('datatables');
		
		$exchange_rate = $this->pos_model->getExchange_rate();
		
        if ($warehouse_id) {
			$this->datatables
                ->select($this->db->dbprefix('sales').".id as id, 
				".$this->db->dbprefix('sales').".date,
				".$this->db->dbprefix('payments').".date as pdate,
				".$this->db->dbprefix('sales').".reference_no, biller, customer, sale_status , grand_total, paid, (grand_total - paid) AS balance, payment_status")
                ->from('sales')
				->join('payments', 'payments.sale_id=sales.id', 'left')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
				->order_by($this->db->dbprefix('sales').".date", "DESC")
                ->where('warehouse_id', $warehouse_id)
                ->group_by('sales.id');
        } else {
            $this->datatables
                ->select($this->db->dbprefix('sales').".id as id, 
				".$this->db->dbprefix('sales').".date,
				".$this->db->dbprefix('payments').".date as pdate,
				".$this->db->dbprefix('sales').".reference_no, biller, customer, sale_status , grand_total, paid, (grand_total - paid) AS balance, payment_status")
                ->from('sales')
				->join('payments', 'payments.sale_id=sales.id', 'left')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
				->order_by($this->db->dbprefix('sales').".date", "DESC")
                ->group_by('sales.id');
        }
        $this->datatables->where('pos', 1);
        if(!$this->Owner && !$this->Admin && $this->session->userdata('view_right') == 0){
			$this->datatables->where('sales.created_by', $this->session->userdata('user_id'));
		} elseif ($this->Customer) {
			$this->datatables->where('customer_id', $this->session->userdata('user_id'));
		}
        $this->datatables->add_column("Actions", $action, "id, psuspend")->unset_column('psuspend');
        echo $this->datatables->generate();
    }

	function getPos($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('index');

        if ((! $this->Owner || ! $this->Admin) && ! $warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('pos/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('#', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'class="email_receipt" data-id="$1" data-email-address="$2"');
        $edit_link = anchor('pos/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
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
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
		
		$exchange_rate = $this->pos_model->getExchange_rate();
		
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('sales').".id as id, date, reference_no, biller, customer, grand_total, paid, (grand_total - paid) AS balance, payment_status, companies.email as cemail")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->where('warehouse_id', $warehouse_id)
                ->group_by('sales.id');
        } else {
            $this->datatables
                ->select($this->db->dbprefix('sales').".id as id, date, reference_no, biller, customer, grand_total, paid, (grand_total - paid) AS balance, payment_status, companies.email as cemail")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->group_by('sales.id');
        }
        $this->datatables->where('pos', 1);
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id, cemail")->unset_column('cemail');
        echo $this->datatables->generate();
    }

    function index($sid = null)
    {
		$this->erp->checkPermissions('index');

        if (!$this->pos_settings->default_biller || !$this->pos_settings->default_customer || !$this->pos_settings->default_category) {
            $this->session->set_flashdata('warning', lang('please_update_settings'));
            redirect('pos/settings');
        }
        
		if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))){
            $register_data = array('register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date);
            $this->session->set_userdata($register_data);
        } else {
            $this->session->set_flashdata('error', lang('register_not_open'));
            redirect('pos/open_register');
        }

        $this->data['sid'] 	= $this->input->get('suspend_id') ? $this->input->get('suspend_id') : $sid;
        $did 				= $this->input->post('delete_id') ? $this->input->post('delete_id') : NULL;
        $suspend 			= $this->input->post('suspend') ? TRUE : FALSE;
        $count 				= $this->input->post('count') ? $this->input->post('count') : NULL;

        //validate form input
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'trim|required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
		$this->form_validation->set_rules('date', $this->lang->line("date"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');

        if ($this->form_validation->run() == true) {
            $quantity 		    = "quantity";
            $product 		    = "product";
            $unit_cost 		    = "unit_cost";
            $tax_rate 		    = "tax_rate";

            $date 			    = $this->input->post('date');
            $warehouse_id 	    = $this->input->post('warehouse');
            $customer_id 	    = $this->input->post('customer');
            $biller_id 		    = $this->input->post('biller');
			$saleman_id 	    = $this->input->post('saleman_1');
            $delivery_by 	    = $this->input->post('delivery_by_1');

            $total_items 	    = $this->input->post('total_items');
            $sale_status 	    = $this->input->post('sale_status_1');
            
            $payment_status     = 'pending';
            $payment_term 	    = 0;
            $due_date 		    = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
            $shipping 		    = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details   = $this->site->getCompanyByID($customer_id);
            $customer 		    = $customer_details->name ? $customer_details->name : $customer_details->company;
            $biller_details     = $this->site->getCompanyByID($biller_id);
            $biller 		    = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note 			    = $this->erp->clear_tags($this->input->post('pos_note'));
            
			$suspend_room 	    = $this->input->post('suspend_room');
            $reference 		    = $this->site->getReference('pos');

            $total 				= 0;
            $fsubtotal 			= 0;
            $product_tax 		= 0;
            $order_tax 			= 0;
            $product_discount 	= 0;
            $order_discount 	= 0;
            $percentage 		= '%';
            $g_total_txt1 		= 0;

            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
			
            for ($r = 0; $r < $i; $r++) {
                $item_id   			= $_POST['product_id'][$r];
                $item_type 			= $_POST['product_type'][$r];
                $item_code 			= $_POST['product_code'][$r];
				$item_note 			= $_POST['product_note'][$r];
                $item_name 			= $_POST['product_name'][$r];
                $item_option 		= isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : NULL;
                $real_unit_price 	= $this->erp->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price 		= $this->erp->formatDecimal($_POST['unit_price'][$r]);
                $item_quantity 		= $_POST['quantity'][$r];
                $item_serial 		= isset($_POST['serial'][$item_id]) ? $_POST['serial'][$item_id] : '';
                $item_tax_rate 		= isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                $item_discount 		= isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : NULL;
                $g_total_txt 		= $_POST['grand_total'][$r];
				
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : NULL;
                    $unit_price 	= $real_unit_price;
                    $pr_discount 	= 0;

                    if (isset($item_discount)) {
                        $discount 	= $item_discount;
                        $dpos 		= strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = (($unit_price) * (Float)($pds[0])) / 100;
                        } else {
                            $pr_discount = ($discount);
                        }
                    }
                    $pr_discount 		= $pr_discount;
                    $unit_price 		= $this->erp->formatDecimal($unit_price);
                    $item_net_price 	= $unit_price;
                    $pr_item_discount 	= ($pr_discount * $item_quantity);
                    $product_discount 	+= ($pr_item_discount);
                    $pr_tax 			= 0; $pr_item_tax = 0; $item_tax = 0; $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {
                            if ($product_details && $product_details->tax_method == 1) {
                                $pbt = (($unit_price) / (1 + ($tax_details->rate / 100)));
                                $item_tax = $this->erp->formatDecimal(($unit_price) - $pbt);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $pbt = (($unit_price) / (1 + ($tax_details->rate / 100)));
                                $item_tax = $this->erp->formatDecimal(($unit_price) - $pbt);
                                $tax = $tax_details->rate . "%";
                            }
                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $pbt = (($unit_price) / (1 + ($tax_details->rate / 100)));
                                $item_tax = $this->erp->formatDecimal(($unit_price) - $pbt);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $pbt = (($unit_price) / (1 + ($tax_details->rate / 100)));
                                $item_tax = $this->erp->formatDecimal(($unit_price) - $pbt);
                                $tax = $tax_details->rate . "%";
                            }
                            $item_tax = $this->erp->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;
                        }
                        $pr_item_tax = $this->erp->formatDecimal($item_tax * $item_quantity, 4);
                    }

                    $product_tax += $this->erp->formatDecimal($pr_item_tax);
					
                    $subtotal = ($item_net_price * $item_quantity - $pr_discount);
                    $fsubtotal += $this->erp->formatDecimal($subtotal);
                    
                    $products[] = array(
                        'product_id' 		=> $item_id,
                        'product_code' 		=> $item_code,
                        'product_name' 		=> $item_name,
                        'product_type' 		=> $item_type,
                        'option_id' 		=> $item_option,
                        'net_unit_price' 	=> $item_net_price,
                        'unit_price' 		=> $this->erp->formatDecimal($item_net_price),
                        'quantity' 			=> $item_quantity,
                        'warehouse_id' 		=> $warehouse_id,
                        'item_tax' 			=> $pr_item_tax,
                        'tax_rate_id' 		=> $pr_tax,
                        'tax' 				=> $tax,
                        'discount' 			=> $item_discount,
                        'item_discount' 	=> $pr_item_discount,
                        'subtotal' 			=> $subtotal,
                        'serial_no' 		=> $item_serial,
                        'real_unit_price' 	=> $real_unit_price,
						'product_noted' 	=> $item_note,
						'transaction_type'	=> 'sale',
						'status'			=> ($sale_status == 'completed'? 'received':$sale_status)
					);
					
                    $total 			+= $this->erp->formatDecimal($subtotal);
					$g_total_txt1 	+= $this->erp->formatDecimal($subtotal);
                }
            }
            
			if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id 	= $this->input->post('discount');
                $opos 				= strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods 	= explode("%", $order_discount_id);
                    $order_discount = (($fsubtotal) * (Float)($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            } else {
                $order_discount_id = NULL;
            }
			
            $total_discount = $this->erp->formatDecimal($order_discount + $product_discount);

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
                $order_tax_id = NULL;
            }

            $total_tax 		= $this->erp->formatDecimal($product_tax + $order_tax);
			$grand_total 	= $total + $order_tax + $this->erp->formatDecimal($shipping) - $this->erp->formatDecimal($order_discount);
			$cur_rate 		= $this->pos_model->getExchange_rate();
            $other_cur_paid = 0;
            $paidd          = 0;

            if($sale_status != 'ordered'){
                $other_cur_paid = $this->input->post('other_cur_paid');
                $paidd          = $this->input->post('amount')[0];

                if($cur_rate){
                    $paidd      = $paidd + ($other_cur_paid / $cur_rate->rate);
                }
                
                if($paidd >= $grand_total) {
                    $paidd = $grand_total;
                }
                
                if(!$_POST['amount'][0]){
                    $paidd = 0;
                }
            }
			
            if ($grand_total == 0) {
                $payment_status = 'paid';
            }

			$suppend_name = $this->pos_model->get_suppendName($did);
            $data = array(
				'date' 				=> $date,
                'reference_no' 		=> $reference,
                'customer_id' 		=> $customer_id,
                'customer' 			=> $customer,
                'biller_id' 		=> $biller_id,
                'biller' 			=> $biller,
                'warehouse_id' 		=> $warehouse_id,
                'note' 				=> $note,
                'total' 			=> $this->erp->formatDecimal($total),
                'product_discount' 	=> $this->erp->formatDecimal($product_discount),
                'order_discount_id' => $order_discount_id,
                'order_discount' 	=> $this->erp->formatDecimal($order_discount),
                'total_discount'	=> $total_discount,
                'product_tax' 		=> $this->erp->formatDecimal($product_tax),
                'order_tax_id' 		=> $order_tax_id,
                'order_tax' 		=> $order_tax,
                'total_tax' 		=> $total_tax,
                'shipping' 			=> $this->erp->formatDecimal($shipping),
                'grand_total' 		=> $grand_total,
                'total_items' 		=> $total_items,
                'total_cost' 		=> 0,
                'sale_status' 		=> $sale_status?$sale_status:'completed',
                'payment_status' 	=> $payment_status,
                'payment_term' 		=> $payment_term,
                'pos' 				=> 1,
                'other_cur_paid' 	=> $other_cur_paid ? $other_cur_paid:0,
                'paid' 				=> $paidd ? $paidd:0,
                'created_by' 		=> $this->session->userdata('user_id'),
				'suspend_note' 		=> isset($suppend_name->suspend_name)?$suppend_name->suspend_name:$suspend_room,
				'other_cur_paid_rate' => $cur_rate->rate,
				'saleman_by' 		=> $saleman_id
            );

			if($_POST['paid_by'][0] == 'depreciation'){
				$no = sizeof($_POST['no']);
				$period = 1;
				for($m = 0; $m < $no; $m++){
					$dateline = date('Y-m-d', strtotime($_POST['dateline'][$m]));
					$loans[] = array(
						'period' 		=> $period,
						'sale_id' 		=> '',
						'interest' 		=> $_POST['interest'][$m],
						'principle' 	=> $_POST['principle'][$m],
						'payment' 		=> $_POST['payment_amt'][$m],
						'balance' 		=> $_POST['balance'][$m],
						'type' 			=> $_POST['loan_type'],
						'rated' 		=> $_POST['loan_rate'],
						'note' 			=> $_POST['note1'][$m],
						'dateline' 		=> $dateline
					);
					$period++;
				}
				//$this->erp->print_arrays($loans);
			}else{
				$loans = array();
			}
			$amount = 0;
            $amounts= 0;
            
			if (!$suspend) {
                $p 		= isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
				$p_cur 	= isset($_POST['other_paid']) ? sizeof($_POST['other_paid']) : 0;
				
				$balance_amount = 0;
                for ($r = 0; $r < $p; $r++) {
					if (isset($_POST['amount'][$r]) && !empty($_POST['amount'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
						$amt = $_POST['amount'][$r];
						if($_POST['other_paid'][$r]){
							$amt = $_POST['amount'][$r] + ($_POST['other_paid'][$r]/$cur_rate->rate);
						}
						$amounts += $amt;
						if ($amounts > $grand_total) {
							$amount = $amt - ($amounts - $grand_total);
						} else {
							$amount = $amt;
						}
											
						if(strpos($_POST['amount'][$r], '-') !== false){
							$payment[] = array(
								'biller_id'	     => $biller_id,
								'date'           => $date,
								'reference_no'   => $this->site->getReference('sp'),
								'amount'         => $amount,
								'paid_by'        => $_POST['paid_by'][$r],
								'cheque_no'      => $_POST['cheque_no'][$r],
								'cc_no'          => ($_POST['paid_by'][$r] == 'gift_card' ? $_POST['paying_gift_card_no'][$r] : $_POST['cc_no'][$r]),
								'cc_holder'      => $_POST['cc_holder'][$r],
								'cc_month'       => $_POST['cc_month'][$r],
								'cc_year'        => $_POST['cc_year'][$r],
								'cc_type'        => $_POST['cc_type'][$r],
								'created_by'     => $this->session->userdata('user_id'),
								'type'           => 'returned',
								'note'           => $_POST['payment_note'][$r],
								'pos_paid'       => $_POST['amount'][$r],
								'pos_paid_other' => $_POST['other_paid'][$r]
							);
						} else {
							$payment[] = array(
								'biller_id'		 => $biller_id,
								'date'           => $date,
								'reference_no'   => $this->site->getReference('sp'),
								'amount'         => $amount,
								'paid_by'        => $_POST['paid_by'][$r],
								'cheque_no'      => $_POST['cheque_no'][$r],
								'cc_no'          => ($_POST['paid_by'][$r] == 'gift_card' ? $_POST['paying_gift_card_no'][$r] : $_POST['cc_no'][$r]),
								'cc_holder'      => $_POST['cc_holder'][$r],
								'cc_month'       => $_POST['cc_month'][$r],
								'cc_year'        => $_POST['cc_year'][$r],
								'cc_type'        => $_POST['cc_type'][$r],
								'cc_cvv2'        => $_POST['cc_cvv2'][$r],
								'created_by'     => $this->session->userdata('user_id'),
								'type'           => 'received',
								'note'           => $_POST['payment_note'][$r],
								'pos_paid'       => $_POST['amount'][$r],
								'pos_paid_other' => $_POST['other_paid'][$r]
							);
						}
						
                        $pp[] = $amount;
                        $this->site->updateReference('sp');
                    }
                }
				
				for ($j = 0; $j < $p_cur; $j++) {
					if(isset($_POST['other_paid'][$j]) && !empty($_POST['other_paid'][$j]) && empty($_POST['amount'][$j]) ){
						
						$p_amount = $_POST['other_cur_paid'] / $cur_rate->rate;
						if($p_amount >= $grand_total){
							$p_amount = $grand_total;
						}else{
							$p_amount = $this->erp->formatDecimal($_POST['other_cur_paid'] / $cur_rate->rate);
						}
						
						$payment[] = array(
							'biller_id'	     => $biller_id,
							'date'           => $date,
							'reference_no'   => $this->site->getReference('sp'),
							'amount'         => $p_amount,
							'paid_by'        => $_POST['paid_by'][$j],
							'cheque_no'      => $_POST['cheque_no'][$j],
							'cc_no'          => ($_POST['paid_by'][$j] == 'gift_card' ? $_POST['paying_gift_card_no'][$j] : $_POST['cc_no'][$j]),
							'cc_holder'      => $_POST['cc_holder'][$j],
							'cc_month'       => $_POST['cc_month'][$j],
							'cc_year'        => $_POST['cc_year'][$j],
							'cc_type'        => $_POST['cc_type'][$j],
							'cc_cvv2'        => $_POST['cc_cvv2'][$j],
							'created_by'     => $this->session->userdata('user_id'),
							'type'           => 'received',
							'note'           => $_POST['payment_note'][$j],
							'pos_paid'       => $_POST['amount'][$j],
							'pos_paid_other' => $_POST['other_cur_paid']
						);
						
						$pp[] = $amount;
						$this->site->updateReference('sp');
					}
				}
                if (!empty($pp)) {
                    $paid = array_sum($pp);
					$paid = $paid + ($this->input->post('other_cur_paid')/$cur_rate->rate);
                } else {
                    $paid = 0;
                }
				
            }
            
			if (!isset($payment) || empty($payment)) {
                $payment = array();
            }
            //$this->erp->print_arrays($data, $products);
		}

        if ($this->form_validation->run() == true && !empty($products) && !empty($data)){
			$cur_rate = $this->pos_model->getExchange_rate();
            if ($suspend) {
                $data['suspend_id'] 		= $this->input->post('suspend_id');
				$data['suspend_name'] 		= $this->input->post('suspend_name');
                $data['other_cur_paid'] 	= $this->input->post('other_cur_paid');
                if ($this->pos_model->suspendSale($data, $products, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $this->session->set_flashdata('message', $this->lang->line("sale_suspended"));
                    redirect("pos");
                }
            } else {
                $data['other_cur_paid'] = $this->input->post('other_cur_paid');
				$data['payment_status'] = $payment_status;
				
                if ($sale = $this->pos_model->addSale($data, $products, $payment, $did, $loans)) {
					$suspended_sale 	= $this->pos_model->getOpenBillByID($did);
					$inactive 			= $this->pos_model->updateSuspendactive($suspended_sale->suspend_id);
                    $this->session->set_userdata('remove_posls', 1);
                    $msg 				= $this->lang->line("sale_added");
                    if (!empty($sale['message'])) {
                        foreach ($sale['message'] as $m) {
                            $msg .= '<br>' . $m;
                        }
                    }
                    $this->session->set_flashdata('message', $msg);
					
					$sale_id = $this->sales_model->getInvoiceByID($sale['sale_id']);
					$address = $customer_details->address . " " . $customer_details->city . " " . $customer_details->state . " " . $customer_details->postal_code . " " . $customer_details->country . "<br>Tel: " . $customer_details->phone . " Email: " . $customer_details->email;
					$dlDetails = array(
						'date' 				=> $date,
						'sale_id' 			=> $sale['sale_id'],
						'do_reference_no' 	=> $this->site->getReference('do'),
						'sale_reference_no' => $sale_id->reference_no,
						'customer' 			=> $customer_details->name,
						'address' 			=> $address,
						//'note' => ' ',
						'created_by' 		=> $this->session->userdata('user_id'),
						'delivery_status' 	=> 'pending',
						'delivery_by' 		=> $delivery_by
					);
					//$this->erp->print_arrays($dlDetails);
					$pos = $this->sales_model->getSetting();
					if($pos->auto_delivery == 1){
						$this->sales_model->addDelivery($dlDetails);
					}
					$invoice_view=$this->Settings->invoice_view;
					if($invoice_view==0){
						redirect("sales/print_/".$sale['sale_id']);
					}
					else if($invoice_view==1){
						redirect("sales/invoice/".$sale['sale_id']);
					}
					else if($invoice_view==2){
						redirect("sales/tax_invoice/".$sale['sale_id']);
					}
					else if($invoice_view==3){
						redirect("sales/print_/".$sale['sale_id']);
					}
					else if($invoice_view==4){
						redirect("sales/invoice_landscap_a5/".$sale['sale_id']);
					}
                    redirect("pos/view/" . $sale['sale_id']);
                }
            }
        }
		else {
			//it may be run.
            $this->data['suspend_sale'] 	= NULL;
            if ($sid) {
                $suspended_sale = $this->pos_model->getOpenBillByID($sid);
				$suspended = $this->pos_model->getSuspended($suspended_sale->suspend_id);
                $inv_items = $this->pos_model->getSuspendedSaleItems($sid);
                $c = rand(100000, 9999999);
                foreach ($inv_items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row = json_decode('{}');
                        $row->tax_method = 0;
                        $row->quantity = 0;
                    } else {
                        unset($row->details, $row->product_details, $row->cost, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
                    }
                    $pis = $this->pos_model->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    if($pis){
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
                    }
                    $row->id = $item->product_id;
                    $row->code = $item->product_code;
                    $row->name = $item->product_name;
                    $row->type = $item->product_type;
                    $row->qty = $item->quantity;
                    $row->quantity += $item->quantity;
                    $row->discount = $item->discount ? $item->discount : '0';
                    $row->price = $this->erp->formatDecimal($item->net_unit_price+$this->erp->formatDecimal($item->item_discount/$item->quantity));
                    $row->unit_price = $row->tax_method ? $item->unit_price+$this->erp->formatDecimal($item->item_discount/$item->quantity)+$this->erp->formatDecimal($item->item_tax/$item->quantity) : $item->unit_price+($item->item_discount/$item->quantity);
                    $row->real_unit_price = $item->real_unit_price;
                    $row->tax_rate = $item->tax_rate_id;
                    $row->serial = $item->serial_no;
                    $row->option = $item->option_id;
                    $options = $this->pos_model->getProductOptions($row->id, $item->warehouse_id);

                    if ($options) {
                        $option_quantity = 0;
                        foreach ($options as $option) {
                            $pis = $this->pos_model->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                            if($pis){
                                foreach ($pis as $pi) {
                                    $option_quantity += $pi->quantity_balance;
                                }
                            }
                            if($option->quantity > $option_quantity) {
                                $option->quantity = $option_quantity;
                            }
                        }
                    }

                    $ri = $this->Settings->item_addition ? $row->id : $c;

                    if ($row->tax_rate) {
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => $tax_rate, 'image' => $row->image, 'options' => $options, 'makeup_cost' => 0);
                    } else {
                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'tax_rate' => false, 'image' => $row->image, 'options' => $options, 'makeup_cost' => 0);
                    }
                    $c++;
                }

				$this->data['items'] = json_encode($pr);
                $this->data['sid'] = $sid;
                $this->data['suspend_sale'] = $suspended_sale;
				$this->data['cus_suspend'] = $suspended;
                $this->data['message'] = lang('suspended_sale_loaded');
                $this->data['customer'] = $this->pos_model->getCompanyByID($suspended_sale->customer_id);
               // $this->data['reference_note'] = $suspended_sale->suspend_note;
            } else {
                $this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
                $this->data['reference_note'] = NULL;
            }

            $this->data['error'] 			= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['message'] 			= isset($this->data['message']) ? $this->data['message'] : $this->session->flashdata('message');

            $this->data['biller'] 			= $this->site->getCompanyByID($this->pos_settings->default_biller);
            $this->data['billers'] 			= $this->site->getAllCompanies('biller');
            $this->data['warehouses'] 		= $this->site->getAllWarehouses();
            $this->data['tax_rates'] 		= $this->site->getAllTaxRates();
			$this->data['owner_password'] 	= $this->site->getUserSetting(1);
			
			$this->data['agencies'] 		= $this->site->getAllUsers();
            $this->data['user'] 			= $this->site->getUser();
            $this->data["tcp"] 				= $this->pos_model->products_count($this->pos_settings->default_category);
            $this->data['products'] 		= $this->ajaxproducts($this->pos_settings->default_category);
			$this->data['room'] 			= $this->site->suspend_room();
			$this->data['user_settings'] 	= $this->site->getUserSetting($this->session->userdata('user_id'));
            $this->data['categories'] 		= $this->site->getAllCategories();
            $this->data['subcategories'] 	= $this->pos_model->getSubCategoriesByCategoryID($this->pos_settings->default_category);
            $this->data['pos_settings'] 	= $this->pos_settings;
			$this->data['exchange_rate'] 	= $this->pos_model->getExchange_rate('KHM');
			$this->data['user_layout'] 		= $this->pos_model->getPosLayout($this->session->userdata('user_id'));
			//$this->data['$order_discount_percent'] = $this->input->post('discount');

            $this->load->view($this->theme . 'pos/add', $this->data);
        }
    }

    function view_bill()
    {
        $this->erp->checkPermissions('index');
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $this->load->view($this->theme . 'pos/view_bill', $this->data);
    }
	
	function view_kitchen()
    {
        $this->erp->checkPermissions('index');

        //$this->table->set_heading('Id', 'The Title', 'The Content');
        
        $this->data['data'] = $this->pos_model->getDelivers();
        $this->load->view($this->theme . 'pos/view_kitchen', $this->data);
    }

    function stripe_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->model('stripe_payments');
        return $this->stripe_payments->get_balance();
    }

    function paypal_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->model('paypal_payments');
        return $this->paypal_payments->get_balance();
    }

    function registers()
    {
        $this->erp->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['registers'] = $this->pos_model->getOpenRegisters();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('open_registers')));
        $meta = array('page_title' => lang('open_registers'), 'bc' => $bc);
        $this->page_construct('pos/registers', $meta, $this->data);
    }

    function open_register()
    {
		$this->load->model('auth_model');
        $this->erp->checkPermissions('index');
        $this->form_validation->set_rules('cash_in_hand', lang("cash_in_hand"), 'trim|required|numeric');
        /*
		if(!$this->Owner && !$this->Admin){
    		$this->form_validation->set_rules('user_password', lang("user_password"), 'required');
        }
		*/
 
        if ($this->form_validation->run() == true) {
            $data = array(
                'date'         => date('Y-m-d H:i:s'),
                'cash_in_hand' => $this->input->post('cash_in_hand'),
                'user_id'      => $this->session->userdata('user_id'),
                'status'       => 'open',
            );
        }
		
        if ($this->form_validation->run() == true) {
			if($this->pos_model->openRegister($data)){
				$this->session->set_flashdata('message', lang("welcome_to_pos"));
				redirect("pos");
			}
			/*
			$pwd = $this->input->post('user_password');
            if($this->Owner || $this->Admin){
                if($this->pos_model->openRegister($data)){
                    $this->session->set_flashdata('message', lang("welcome_to_pos"));
                    redirect("pos");
                }
            }else{
                if($this->auth_model->check_password_db($pwd)){
                    if($this->pos_model->openRegister($data)){
                        $this->session->set_flashdata('message', lang("welcome_to_pos"));
                        redirect("pos");
                    }
                } else {
                    $this->session->set_flashdata('error', lang("Sorry, your password is incorrect."));
                    redirect("pos/open_register");
                }
            }
			*/
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('open_register')));
            $meta = array('page_title' => lang('open_register'), 'bc' => $bc);
            $this->page_construct('pos/open_register', $meta, $this->data);
        }
    }

    function close_register($user_id = NULL)
    {
        $this->erp->checkPermissions('index');
        if (!$this->Owner && !$this->Admin) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->form_validation->set_rules('total_cash', lang("total_cash"), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cheques', lang("total_cheques"), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cc_slips', lang("total_cc_slips"), 'trim|required|numeric');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
                $rid = $user_register ? $user_register->id : $this->session->userdata('register_id');
                $user_id = $user_register ? $user_register->user_id : $this->session->userdata('user_id');
            } else {
                $rid = $this->session->userdata('register_id');
                $user_id = $this->session->userdata('user_id');
            }
            $data = array('closed_at' => date('Y-m-d H:i:s'),
                'total_cash' => $this->input->post('total_cash'),
                'total_cheques' => $this->input->post('total_cheques'),
                'total_cc_slips' => $this->input->post('total_cc_slips'),
                'total_cash_submitted' => $this->input->post('total_cash_submitted'),
                'total_cheques_submitted' => $this->input->post('total_cheques_submitted'),
                'total_cc_slips_submitted' => $this->input->post('total_cc_slips_submitted'),
                'note' => $this->input->post('note'),
                'status' => 'close',
                'transfer_opened_bills' => $this->input->post('transfer_opened_bills'),
                'closed_by' => $this->session->userdata('user_id'),
            );
        } elseif ($this->input->post('close_register')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            redirect("pos");
        }

        if ($this->form_validation->run() == true && $this->pos_model->closeRegister($rid, $user_id, $data)) {
            $this->session->set_flashdata('message', lang("register_closed"));
            redirect("welcome");
        } else {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
                $register_open_time = $user_register ? $user_register->date : $this->session->userdata('register_open_time');
                $this->data['cash_in_hand'] = $user_register ? $user_register->cash_in_hand : NULL;
                $this->data['register_open_time'] = $user_register ? $register_open_time : NULL;
            } else {
                $register_open_time = $this->session->userdata('register_open_time');
                $this->data['cash_in_hand'] = NULL;
                $this->data['register_open_time'] = NULL;
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time, $user_id);
            $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time, $user_id);
            $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time, $user_id);
            $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time, $user_id);
            $this->data['stripesales'] = $this->pos_model->getRegisterStripeSales($register_open_time, $user_id);
            $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time, $user_id);
            $this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time);
            $this->data['cashrefunds'] = $this->pos_model->getRegisterCashRefunds($register_open_time);
            $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time);
            $this->data['users'] = $this->pos_model->getUsers($user_id);
            $this->data['suspended_bills'] = $this->pos_model->getSuspendedsales($user_id);
            $this->data['user_id'] = $user_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/close_register', $this->data);
        }
    }
    
	function updateQty($suspend_id = null, $item_code = null, $quantity = null, $ruprice = null)
	{
        $suspend_id = $this->input->get('suspend_id', TRUE);
        $item_code = $this->input->get('item_code', TRUE);
        $quantity = $this->input->get('quantity', TRUE);
        $ruprice = $this->input->get('ruprice', TRUE);

        $this->db->update('suspended_items',
                            array('quantity' => $quantity),
                            array('suspend_id' => $suspend_id, 'product_code' => $item_code));

        /* Select total price */
        $this->db->select('sum(unit_price * quantity) as tprice');
        $this->db->where('suspend_id', $suspend_id);
        $q = $this->db->get('suspended_items')->row();

        $pr = array('sub_total' => $q->tprice);
        echo json_encode($pr);
    }

    function clearPosItem($suspend_id = null)
    {
        $suspend_id = $this->input->get('suspend_id', TRUE);
        $this->db->delete('suspended_bills', array('id' => $suspend_id));
        $this->db->delete('suspended_items', array('suspend_id' => $suspend_id));
        exit('success');
    }

    function removeItemCount($suspend_id = null, $item_rows = null, $item_code = null, $quantity = null)
    {
        $suspend_id = $this->input->get('suspend_id');
        $item_rows = $this->input->get('item_rows');
        $item_code = $this->input->get('item_code');
        $quantity = $this->input->get('quantity');

        /* DELETE item */
        $this->db->delete('suspended_items', array('suspend_id' => $suspend_id, 'product_code' => $item_code));

        /* Select total price */
        $this->db->select('sum(unit_price * quantity) as tprice');
        $this->db->where('suspend_id', $suspend_id);
        $q = $this->db->get('suspended_items')->row();

        $this->db->update('suspended_bills', array('count' => $item_rows - 1, 'total' => $q->unit_price), array('id' => $suspend_id ));
        $pr = array('sub_total' => $q->tprice);
        echo json_encode($pr);
    }
    
	function saveItemList()
	{
        $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
        $products = array ();
        $quantity = "quantity";
        $product = "product";
        $unit_cost = "unit_cost";
        $tax_rate = "tax_rate";

        $date = date('Y-m-d H:i:s');
        $warehouse_id = $this->input->post('warehouse');
        $customer_id = $this->input->post('customer');
        $biller_id = $this->input->post('biller');
        $total_items = $this->input->post('total_items');
        $suspend_ = $this->input->post('suspend_');
        //$sale_status = $this->input->post('sale_status');
        $sale_status = 'completed';
        $payment_status = 'due';
        $payment_term = 0;
        $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
        $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
        $customer_details = $this->site->getCompanyByID($customer_id);
        $customer = $customer_details->company ? $customer_details->company : $customer_details->name;
        $biller_details = $this->site->getCompanyByID($biller_id);
        $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
        $note = $this->erp->clear_tags($this->input->post('pos_note'));
        $staff_note = $this->erp->clear_tags($this->input->post('staff_note'));
        $reference = $this->site->getReference('pos');

        $item_image = "";
        $total = 0;
        $product_tax = 0;
        $order_tax = 0;
        $product_discount = 0;
        $order_discount = 0;
        $percentage = '%';

        $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
        for ($r = 0; $r < $i; $r++) {
            $item_id = $_POST['product_id'][$r];
            $item_type = $_POST['product_type'][$r];
            $item_code = $_POST['product_code'][$r];
            $item_name = $_POST['product_name'][$r];
            $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : NULL;
            //$option_details = $this->pos_model->getProductOptionByID($item_option);
            $real_unit_price = $this->erp->formatDecimal($_POST['real_unit_price'][$r]);
            $unit_price = $this->erp->formatDecimal($_POST['unit_price'][$r]);
            $item_quantity = $_POST['quantity'][$r];
            $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
            $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
            $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : NULL;

            if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                $product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : NULL;
                $unit_price = $real_unit_price;
                $pr_discount = 0;

                if (isset($item_discount)) {
                    $discount = $item_discount;
                    $dpos = strpos($discount, $percentage);
                    if ($dpos !== false) {
                        $pds = explode("%", $discount);
                        $pr_discount = (($this->erp->formatDecimal($unit_price)) * (Float)($pds[0])) / 100;
                    } else {
                        $pr_discount = $this->erp->formatDecimal($discount);
                    }
                }

                $unit_price = $this->erp->formatDecimal($unit_price - $pr_discount);
                $item_net_price = $unit_price;
                $pr_item_discount = $this->erp->formatDecimal($pr_discount * $item_quantity);
                $product_discount += $pr_item_discount;
                $pr_tax = 0; $pr_item_tax = 0; $item_tax = 0; $tax = "";

                if (isset($item_tax_rate) && $item_tax_rate != 0) {
                    $pr_tax = $item_tax_rate;
                    $tax_details = $this->site->getTaxRateByID($pr_tax);
                    if ($tax_details->type == 1 && $tax_details->rate != 0) {

                        if ($product_details && $product_details->tax_method == 1) {
                            $item_tax = $this->erp->formatDecimal((($unit_price) * $tax_details->rate) / 100);
                            $tax = $tax_details->rate . "%";
                        } else {
                            $item_tax = $this->erp->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate));
                            $tax = $tax_details->rate . "%";
                            $item_net_price = $unit_price - $item_tax;
                        }

                    } elseif ($tax_details->type == 2) {

                        if ($product_details && $product_details->tax_method == 1) {
                            $item_tax = $this->erp->formatDecimal((($unit_price) * $tax_details->rate) / 100);
                            $tax = $tax_details->rate . "%";
                        } else {
                            $item_tax = $this->erp->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate));
                            $tax = $tax_details->rate . "%";
                            $item_net_price = $unit_price - $item_tax;
                        }

                        $item_tax = $this->erp->formatDecimal($tax_details->rate);
                        $tax = $tax_details->rate;

                    }
                    $pr_item_tax = $this->erp->formatDecimal($item_tax * $item_quantity);

                }

                $product_tax += $pr_item_tax;
                $subtotal = (($item_net_price * $item_quantity) + $pr_item_tax);
                $row = $this->pos_model->getWHProduct($item_code, $warehouse_id);
                /*$item_image .= '<button id="' . $item_code . '" type="button" value="'  . $item_code . '" title="" class="btn-prni btn-default product pos-tip" data-container="body" data-original-title="' . $item_name . '"><img src="'.site_url().'assets/uploads/thumbs/'. $row->image .'" alt="' . $item_name . '" style="width: 60px; height: 60px;" class="img-rounded"/></button>';*/

                $products[] = array(
                    'product_id' => $item_id,
                    'product_code' => $item_code,
                    'product_name' => $item_name,
                    'product_type' => $item_type,
                    'option_id' => $item_option,
                    'net_unit_price' => $item_net_price,
                    'unit_price' => $this->erp->formatDecimal($item_net_price + $item_tax),
                    'quantity' => $item_quantity,
                    'warehouse_id' => $warehouse_id,
                    'item_tax' => $pr_item_tax,
                    'tax_rate_id' => $pr_tax,
                    'tax' => $tax,
                    'discount' => $item_discount,
                    'item_discount' => $pr_item_discount,
                    'subtotal' => $this->erp->formatDecimal($subtotal),
                    'serial_no' => $item_serial,
                    'real_unit_price' => $real_unit_price
                );

                $total += $item_net_price * $item_quantity;
            }
        }
        $susp_bill_arr = array(
                        'count' => $i,
                        'total' => $total
                        );

        if($this->pos_model->suspendItem_($susp_bill_arr, $suspend_, $products)){
            $pr = array('total_items' => $i, 'sub_total' => $total, 'image_' => $item_image);
            exit(json_encode($pr));
        }
        exit('fail');
    }
	
	function getCustomerInfo(){
		$cus_id = $this->input->get('customer_id');
		$customer_info = $this->pos_model->getCustomerByID($cus_id);
        exit(json_encode($customer_info));
	}

    function getProductDataByCode($code = NULL, $warehouse_id = NULL, $cust_id = null, $suspend_id = null, $item_rows = null, $sub_total = null)
    {
        $suspend_id 		= $this->input->get('suspend_id');
        $item_rows 			= $this->input->get('item_rows');
        $sub_total 			= $this->input->get('sub_total');

        $this->erp->checkPermissions('index');
        if ($this->input->get('code')) {
            $code 			= $this->input->get('code', TRUE);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id 	= $this->input->get('warehouse_id', TRUE);
        }
        if ($this->input->get('customer_id')) {
            $customer_id 	= $this->input->get('customer_id', TRUE);
        }
        if (!$code) {
            echo NULL;
            die();
        }

        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row = $this->pos_model->getWHProduct($code, $warehouse_id);
        $option = '';
        if ($row) {
            $combo_items = FALSE;
            $row->item_tax_method 	= $row->tax_method;
            $row->qty 				= 1;
            $row->discount 			= '0';
            $row->serial 			= '';
            $options 				= $this->pos_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option 		= $opt->id;
                }
            } else {
                $opt = json_decode('{}');
                $opt->price 		= 0;
            }
            $row->option = $option;
            if ($opt->price != 0) {
                $row->price 		= $opt->price + (($opt->price * $customer_group->percent) / 100);
            } else {
                $row->price 		= $row->price + (($row->price * $customer_group->percent) / 100);
            }
            $row->quantity 			= 0;
            $pis = $this->pos_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
            if($pis){
                foreach ($pis as $pi) {
                    $row->quantity += $pi->quantity_balance;
                }
            }
            if ($options) {
                $option_quantity = 0;
                foreach ($options as $option) {
                    $pis 			= $this->pos_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
                    if($pis){
                        foreach ($pis as $pi) {
                            $option_quantity += $pi->quantity_balance;
                        }
                    }
                    if($option->quantity > $option_quantity) {
                        $option->quantity = $option_quantity;
                    }
                }
            }
            $row->real_unit_price = $row->price;
			
            $combo_items = FALSE;
            if ($row->tax_rate) {
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                if ($row->type == 'combo') {
                    $combo_items 	= $this->pos_model->getProductComboItems($row->id, $warehouse_id);
                    $row->sep 		= $this->pos_model->getComboSerial($row->id, $warehouse_id);
                }
                $pr = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",'image' => $row->image, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'options' => $options, 'item_price' => $row->price);
            } else {
                $pr = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",'image' => $row->image, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => false, 'options' => $options, 'item_price' => $row->price);
            }
			
            echo json_encode($pr);
			
        } else {
			
            echo NULL;
			
        }
    }

	function getProductSearchByCode($code = NULL, $warehouse_id = NULL, $cust_id = null, $suspend_id = null, $item_rows = null, $sub_total = null)
    {
		$data = '';
        $suspend_id = $this->input->get('suspend_id');
        $item_rows = $this->input->get('item_rows');
        $sub_total = $this->input->get('sub_total');

        $this->erp->checkPermissions('index');
        if ($this->input->get('code')) {
            $code = $this->input->get('code', TRUE);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', TRUE);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', TRUE);
        }
        if (!$code) {
            echo NULL;
            die();
        }

        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->pos_model->getSESuspend($code, $warehouse_id);
		foreach($rows as $id){
			$array[] = $id['product_id'];
			
		}
		$data_=implode(',',$array);
		$result = $this->pos_model->getSEProduct($data_, $warehouse_id);
		
		//$this->erp->print_arrays($result);
        $option = '';
        $errors = array_filter($result);
        if (!empty($errors)) {
			foreach($result as $row){
				$combo_items = FALSE;
				$row->item_tax_method = $row->tax_method;
				$row->qty = 1;
				$row->discount = '0';
				$row->serial = '';
				$options = $this->pos_model->getProductOptions($row->id, $warehouse_id);
				if ($options) {
					$opt = current($options);
					if (!$option) {
						$option = $opt->id;
					}
				} else {
					$opt = json_decode('{}');
					$opt->price = 0;
				}
				$row->option = $option;
				if ($opt->price != 0) {
					$row->price = $opt->price + (($opt->price * $customer_group->percent) / 100);
				} else {
					$row->price = $row->price + (($row->price * $customer_group->percent) / 100);
				}
				$row->quantity = 0;
				$pis = $this->pos_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
				if($pis){
					foreach ($pis as $pi) {
						$row->quantity += $pi->quantity_balance;
					}
				}
				if ($options) {
					$option_quantity = 0;
					foreach ($options as $option) {
						$pis = $this->pos_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
						if($pis){
							foreach ($pis as $pi) {
								$option_quantity += $pi->quantity_balance;
							}
						}
						if($option->quantity > $option_quantity) {
							$option->quantity = $option_quantity;
						}
					}
				}
				$row->real_unit_price = $row->price;
				
				$combo_items = FALSE;
				if ($row->tax_rate) {
					$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
					if ($row->type == 'combo') {
						$combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
					}
					$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",'image' => $row->image, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'options' => $options, 'item_price' => $row->price);
				} else {
					$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",'image' => $row->image, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => false, 'options' => $options, 'item_price' => $row->price);
				}

			}
			echo json_encode($pr);
        } else {
			
            echo NULL;
			
        }
    }

    function ajaxproducts()
    {
        $this->erp->checkPermissions('index');
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }
        if ($this->input->get('subcategory_id')) {
            $subcategory_id = $this->input->get('subcategory_id');
        } else {
            $subcategory_id = NULL;
        }
        if ($this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }

        $this->load->library("pagination");

        $config = array();
        $config["base_url"] = base_url() . "pos/ajaxproducts";
        $config["total_rows"] = $subcategory_id ? $this->pos_model->products_count($category_id, $subcategory_id) : $this->pos_model->products_count($category_id);
        $config["per_page"] = $this->pos_settings->pro_limit;
        $config['prev_link'] = FALSE;
        $config['next_link'] = FALSE;
        $config['display_pages'] = FALSE;
        $config['first_link'] = FALSE;
        $config['last_link'] = FALSE;

        $this->pagination->initialize($config);
        $user_setting = $this->site->getUserSetting($this->session->userdata('user_id'));
        $products = $subcategory_id ? $this->pos_model->fetch_products($category_id, $config["per_page"], $page, $user_setting->sales_standard, $user_setting->sales_combo, $user_setting->sales_digital, $user_setting->sales_service, $user_setting->sales_category, $subcategory_id) : $this->pos_model->fetch_products($category_id, $config["per_page"], $page, $user_setting->sales_standard, $user_setting->sales_combo, $user_setting->sales_digital, $user_setting->sales_service, $user_setting->sales_category);
        $pro = 1;
		$i=1;
        $prods = '<div  id=box-item>';
        if ( ! empty($products)) {
            foreach ($products as $product) {
                $count = $product->id;
                if ($count < 10) {
                    $count = "0" . ($count / 100) * 100;
                }
                if ($category_id < 10) {
                    $category_id = "0" . ($category_id / 100) * 100;
                }

                $prods .= "<button id=\"product-" . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->name . "\" class=\"btn-prni btn-" . $this->pos_settings->product_button_color . " product pos-tip\" data-container=\"body\"><img src=\"" . base_url() . "assets/uploads/thumbs/" . $product->image . "\" alt=\"" . $product->name . "\" style='width:" . $this->Settings->twidth . "px;height:" . $this->Settings->theight . "px;' class='img-rounded' /><span>" . character_limiter($product->name, 40) . "</span></button>";
				
					
					
                $pro++;
            }
        }
        $prods .= "</div>";

        if ($this->input->get('per_page')) {
            echo $prods;
        } else {
            return $prods;
        }
    }

    function ajaxcategorydata($category_id = NULL)
    {
        $this->erp->checkPermissions('index');
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }

        $subcategories = $this->pos_model->getSubCategoriesByCategoryID($category_id);
        $scats = '';
        if($subcategories) {
            foreach ($subcategories as $category) {
                $scats .= "<button id=\"subcategory-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" style='width:" . $this->Settings->twidth . "px;height:" . $this->Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";			
			}
        }

        $products = $this->ajaxproducts($category_id);

        if (!($tcp = $this->pos_model->products_count($category_id))) {
            $tcp = 0;
        }

        echo json_encode(array('products' => $products, 'subcategories' => $scats, 'tcp' => $tcp));
    }

    function view($sale_id = NULL, $modal = NULL)
    {
        $this->erp->checkPermissions('index');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
		
        $this->load->helper('text');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments'] = $this->pos_model->getInvoicePaymentsPOS($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['barcode'] = $this->barcode($inv->reference_no, 'code39', 30);
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
		$this->data['exchange_rate'] = $this->pos_model->getExchange_rate();
		$this->data['outexchange_rate'] = $this->pos_model->getExchange_rate('KHM_o');
		$this->data['exchange_rate_th'] = $this->pos_model->getExchange_rate('THA');
		$this->data['exchange_rate_kh_c'] = $this->pos_model->getExchange_rate('KHM');
        $this->data['modal'] = $modal;
        $this->data['page_title'] = $this->lang->line("invoice");
        $this->load->view($this->theme . 'pos/view', $this->data);
    }
	
	function cabon_print($sale_id = NULL, $modal = NULL)
    {
        $this->erp->checkPermissions('index');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('text');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments'] = $this->pos_model->getInvoicePaymentsPOS($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['barcode'] = $this->barcode($inv->reference_no, 'code39', 30);
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
		$this->data['exchange_rate'] = $this->pos_model->getExchange_rate();
		$this->data['exchange_rate_th'] = $this->pos_model->getExchange_rate('THA');
		$this->data['exchange_rate_kh_c'] = $this->pos_model->getExchange_rate('KHM');
        $this->data['modal'] = $modal;
        $this->data['page_title'] = $this->lang->line("invoice");
        $this->load->view($this->theme . 'pos/cabon_print', $this->data);
    }

    function register_details()
    {
        $this->erp->checkPermissions('index');
        $register_open_time = $this->session->userdata('register_open_time');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time);
        $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time);
        $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time);
        $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time);
        $this->data['stripesales'] = $this->pos_model->getRegisterStripeSales($register_open_time);
        $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time);
        $this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time);
        $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time);
        $this->load->view($this->theme . 'pos/register_details', $this->data);
    }

    function today_sale()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->erp->md();
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales'] = $this->pos_model->getTodayCCSales();
        $this->data['cashsales'] = $this->pos_model->getTodayCashSales();
        $this->data['chsales'] = $this->pos_model->getTodayChSales();
        $this->data['pppsales'] = $this->pos_model->getTodayPPPSales();
        $this->data['stripesales'] = $this->pos_model->getTodayStripeSales();
        $this->data['totalsales'] = $this->pos_model->getTodaySales();
        $this->data['refunds'] = $this->pos_model->getTodayRefunds();
        $this->data['expenses'] = $this->pos_model->getTodayExpenses();
        $this->load->view($this->theme . 'pos/today_sale', $this->data);
    }

    function check_pin()
    {
        $pin = $this->input->post('pw', true);
        if ($pin == $this->pos_pin) {
            echo json_encode(array('res' => 1));
        }
        echo json_encode(array('res' => 0));
    }

    function barcode($text = NULL, $bcs = 'code39', $height = 50)
    {
        return site_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }

    function settings()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('pro_limit', $this->lang->line('pro_limit'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('pin_code', $this->lang->line('delete_code'), 'numeric');
        $this->form_validation->set_rules('category', $this->lang->line('default_category'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('customer', $this->lang->line('default_customer'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('biller', $this->lang->line('default_biller'), 'required|is_natural_no_zero');

        if ($this->form_validation->run() == true) {

            $data = array(
                'pro_limit' => $this->input->post('pro_limit'),
                'pin_code' => $this->input->post('pin_code') ? $this->input->post('pin_code') : NULL,
                'default_category' => $this->input->post('category'),
                'default_customer' => $this->input->post('customer'),
                'default_biller' => $this->input->post('biller'),
                'display_time' => $this->input->post('display_time'),
                'receipt_printer' => $this->input->post('receipt_printer'),
                'cash_drawer_codes' => $this->input->post('cash_drawer_codes'),
                'cf_title1' => $this->input->post('cf_title1'),
                'cf_title2' => $this->input->post('cf_title2'),
                'cf_value1' => $this->input->post('cf_value1'),
                'cf_value2' => $this->input->post('cf_value2'),
                'focus_add_item' => $this->input->post('focus_add_item'),
                'add_manual_product' => $this->input->post('add_manual_product'),
                'customer_selection' => $this->input->post('customer_selection'),
                'add_customer' => $this->input->post('add_customer'),
                'toggle_category_slider' => $this->input->post('toggle_category_slider'),
                'toggle_subcategory_slider' => $this->input->post('toggle_subcategory_slider'),
                'cancel_sale' => $this->input->post('cancel_sale'),
                'suspend_sale' => $this->input->post('suspend_sale'),
                'print_items_list' => $this->input->post('print_items_list'),
                'finalize_sale' => $this->input->post('finalize_sale'),
				'product_unit' => $this->input->post('product_unit'),
				'show_search_item' => $this->input->post('show_search_item'),
                'today_sale' => $this->input->post('today_sale'),
                'open_hold_bills' => $this->input->post('open_hold_bills'),
                'close_register' => $this->input->post('close_register'),
                'tooltips' => $this->input->post('tooltips'),
                'keyboard' => $this->input->post('keyboard'),
                'pos_printers' => $this->input->post('pos_printers'),
                'java_applet' => $this->input->post('enable_java_applet'),
                'product_button_color' => $this->input->post('product_button_color'),
                'paypal_pro' => $this->input->post('paypal_pro'),
                'stripe' => $this->input->post('stripe'),
                'rounding' => $this->input->post('rounding'),
                'show_item_img' => $this->input->post('show_item_img'),
				'pos_layout' => $this->input->post('pos_layout'),
				'show_payment_noted' => $this->input->post('show_payment_noted'),
				'payment_balance' => $this->input->post('payment_balance'),
				'display_qrcode' => $this->input->post('display_qrcode'),
				'show_suspend_bar' => $this->input->post('show_suspend_bar'),
				'payment_balance' => $this->input->post('payment_balance'),
				'show_product_code' => $this->input->post('show_product_code'),
				'auto_delivery' => $this->input->post('auto_delivery'),
				'in_out_rate' => $this->input->post('in_out_rate')
            );
			//$this->erp->print_arrays($data);
            $payment_config = array(
                'APIUsername' => $this->input->post('APIUsername'),
                'APIPassword' => $this->input->post('APIPassword'),
                'APISignature' => $this->input->post('APISignature'),
                'stripe_secret_key' => $this->input->post('stripe_secret_key'),
                'stripe_publishable_key' => $this->input->post('stripe_publishable_key'),
            );
        } elseif ($this->input->post('update_settings')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("pos/settings");
        }

        if ($this->form_validation->run() == true && $this->pos_model->updateSetting($data)) {
            if ($this->write_payments_config($payment_config)) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                redirect("pos/settings");
            } else {
                $this->session->set_flashdata('error', $this->lang->line('pos_setting_updated_payment_failed'));
                redirect("pos/settings");
            }
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['pos'] = $this->pos_model->getSetting();
            $this->data['categories'] = $this->site->getAllCategories();
            //$this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
            $this->data['billers'] = $this->pos_model->getAllBillerCompanies();
            $this->config->load('payment_gateways');
            $this->data['stripe_secret_key'] = $this->config->item('stripe_secret_key');
            $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
            $this->data['APIUsername'] = $this->config->item('APIUsername');
            $this->data['APIPassword'] = $this->config->item('APIPassword');
            $this->data['APISignature'] = $this->config->item('APISignature');
            $this->data['paypal_balance'] = $this->pos_settings->paypal_pro ? $this->paypal_balance() : NULL;
            $this->data['stripe_balance'] = $this->pos_settings->stripe ? $this->stripe_balance() : NULL;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('pos_settings')));
            $meta = array('page_title' => lang('pos_settings'), 'bc' => $bc);
            $this->page_construct('pos/settings', $meta, $this->data);
        }
    }

    public function write_payments_config($config)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $file_contents = file_get_contents('./assets/config_dumps/payment_gateways.php');
        $output_path = APPPATH . 'config/payment_gateways.php';
        $this->load->library('parser');
        $parse_data = array(
            'APIUsername' => $config['APIUsername'],
            'APIPassword' => $config['APIPassword'],
            'APISignature' => $config['APISignature'],
            'stripe_secret_key' => $config['stripe_secret_key'],
            'stripe_publishable_key' => $config['stripe_publishable_key'],
        );
        $new_config = $this->parser->parse_string($file_contents, $parse_data);

        $handle = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new_config)) {
                @chmod($output_path, 0644);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function opened_bills($per_page = 0)
    {
        $this->load->library('pagination');

        //$this->table->set_heading('Id', 'The Title', 'The Content');
        if ($this->input->get('per_page')) {
            $per_page = $this->input->get('per_page');
        }

        $config['base_url'] = site_url('pos/opened_bills');
        $config['total_rows'] = $this->pos_model->bills_count();
        $config['per_page'] = 6;
        $config['num_links'] = 3;

        $config['full_tag_open'] = '<ul class="pagination pagination-sm">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';

        $this->pagination->initialize($config);
        $data['r'] = TRUE;
        $bills = $this->pos_model->fetch_bills($config['per_page'], $per_page);
        if (!empty($bills)) {
            $html = "";
            $html .= '<ul class="ob">';
            foreach ($bills as $bill) {
                $html .= '<li><button type="button" class="btn btn-info sus_sale" id="' . $bill->id . '"><p>' . $bill->suspend_note . '</p><strong>' . $bill->customer . '</strong><br>Date: ' . $bill->date . '<br>Items: ' . $bill->count . '<br>Total: ' . $this->erp->formatMoney($bill->total) . '</button></li>';
            }
            $html .= '</ul>';
        } else {
            $html = "<h3>" . lang('no_opeded_bill') . "</h3><p>&nbsp;</p>";
            $data['r'] = FALSE;
        }

        $data['html'] = $html;

        $data['page'] = $this->pagination->create_links();
        echo $this->load->view($this->theme . 'pos/opened', $data, TRUE);

    }

    function delete($id = NULL)
    {

        $this->erp->checkPermissions('index');

        if ($this->pos_model->deleteBill($id)) {
            echo lang("suspended_sale_deleted");
        }
    }
	
	function delete_suspend($id = NULL)
    {

        $this->erp->checkPermissions('index');

        if ($this->pos_model->deleteBill($id)) {
			echo '<div class="alert alert-success gcerror-con" style="display: block;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span>Suspend Table clear!</span>
                </div>';
            redirect('pos');
        }
    }
	
	function delete_room($id = NULL)
    {

        $this->erp->checkPermissions('index');

        if ($this->pos_model->deleteBillRoom($id)) {
			echo '<div class="alert alert-success gcerror-con" style="display: block;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span>Suspend Table clear!</span>
                </div>';
            redirect('pos');
        }
    }

	function complete_kitchen($id = NULL){
		$data = array(
			'status' => 1
		);
		//$this->erp->print_arrays($data);
		if($this->pos_model->kitchen_complete($id, $data)){
			redirect('pos/view_kitchen');
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

    public function active()
    {
        $this->session->set_userdata('last_activity', now());
        if ((now() - $this->session->userdata('last_activity')) <= 20) {
            die('Successfully updated the last activity.');
        } else {
            die('Failed to update last activity.');
        }
    }
	
	function payments($id = NULL)
    {
        $this->erp->checkPermissions('payments', true, 'sales');
		$inv 				= $this->pos_model->getInvoiceByID($id);
		$payments 			= $this->pos_model->getCurrentBalance($inv->id);
		$current_balance 	= $inv->grand_total;
		foreach($payments as $curr_pay) {
			$current_balance -= $curr_pay->amount;
		}
		$this->data['curr_balance'] = $current_balance;
        $this->data['payments'] 	= $this->pos_model->getInvoicePayments($id);
        $this->load->view($this->theme . 'pos/payments', $this->data);
    }

    function add_payment($id = NULL)
    {
        $this->erp->checkPermissions('payments', true, 'sales');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			$amount  = $this->input->post('amount-paid');
			$get_amt = $this->input->post('amounts');
			$inp_amt = $this->input->post('amount-paid');
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $company 				= $this->pos_model->getInvoiceByID($id);
			if ($get_amt < $inp_amt) {
				$amount = $get_amt;
			}
			$payment = array(
                'date' 				=> $date,
                'sale_id' 			=> $this->input->post('sale_id'),
                'reference_no' 		=> $this->input->post('reference_no'),
                'amount' 			=> $amount,
                'pos_paid'          => $this->input->post('amount-paid'),
                'paid_by' 			=> $this->input->post('paid_by'),
                'cheque_no' 		=> $this->input->post('cheque_no'),
				'bank_transfer_no' 	=> $this->input->post('bank_transfer'),
                'cc_no' 			=> $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder' 		=> $this->input->post('pcc_holder'),
                'cc_month' 			=> $this->input->post('pcc_month'),
                'cc_year' 			=> $this->input->post('pcc_year'),
                'cc_type' 			=> $this->input->post('pcc_type'),
                'cc_cvv2' 			=> $this->input->post('pcc_ccv'),
                'note' 				=> $this->input->post('note'),
                'created_by' 		=> $this->session->userdata('user_id'),
                'type' 				=> 'received'
            );
			
            if ($this->input->post('paid_by') == 'deposit') {
                $deposit = array(
                    'date'          => $date,
                    'amount'        => (-1) * $this->input->post('amount-paid'),
                    'paid_by'       => $this->input->post('paid_by'),
                    'company_id'    => $company->customer_id,
                    'created_by'    => $this->session->userdata('user_id')
                );
            }

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->erp->print_arrays($payment, $deposit);

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }


        if ($this->form_validation->run() == true && $msg = $this->pos_model->addPayment($payment, $deposit)) {
            if ($msg) {
                if ($msg['status'] == 0) {
                    $error = '';
                    foreach ($msg as $m) {
                        $error .= '<br>' . (is_array($m) ? print_r($m, true) : $m);
                    }
                    $this->session->set_flashdata('error', '<pre>' . $error . '</pre>');
                } else {
                    $this->session->set_flashdata('message', lang("payment_added"));
                }
            } else {
                $this->session->set_flashdata('error', lang("payment_failed"));
            }
            redirect("pos/sales");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $sale 						= $this->pos_model->getInvoiceByID($id);
            $this->data['inv'] 			= $sale;
            $this->data['payment_ref'] 	= $this->site->getReference('sp');
            $this->data['modal_js'] 	= $this->site->modal_js();
			$this->data['customers'] 	= $this->site->getCustomers();

            $this->load->view($this->theme . 'pos/add_payment', $this->data);
        }
    }
	
	function edit_payment($id = NULL)
    {
        $this->erp->checkPermissions('payments', true, 'sales');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $amount  = $this->input->post('amount-paid');
            $get_amt = $this->input->post('amounts');
            $inp_amt = $this->input->post('amount-paid');
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $pay 	  = $this->pos_model->getPaymentByID($id);
            $company  = $this->pos_model->getInvoiceByID($pay->sale_id);
            if ($get_amt < $inp_amt) {
                $amount = $get_amt;
            }
            $payment = array(
                'date' 				=> $date,
                'sale_id' 			=> $this->input->post('sale_id'),
                'reference_no' 		=> $this->input->post('reference_no'),
                'amount' 			=> $amount,
                'pos_paid'          => $this->input->post('amount-paid'),
                'paid_by' 			=> $this->input->post('paid_by'),
                'cheque_no' 		=> $this->input->post('cheque_no'),
                'bank_transfer_no' 	=> $this->input->post('bank_transfer'),
                'cc_no' 			=> $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder' 		=> $this->input->post('pcc_holder'),
                'cc_month' 			=> $this->input->post('pcc_month'),
                'cc_year' 			=> $this->input->post('pcc_year'),
                'cc_type' 			=> $this->input->post('pcc_type'),
                'cc_cvv2' 			=> $this->input->post('pcc_ccv'),
                'note' 				=> $this->input->post('note'),
                'created_by' 		=> $this->session->userdata('user_id'),
                'type' 				=> 'received'
            );
			
			$deposit = array(
                'date' 			=> $date,
                'amount' 		=> (-1) * $this->input->post('amount-paid'),
                'paid_by' 		=> $this->input->post('paid_by'),
                'company_id' 	=> $company->customer_id,
                'created_by' 	=> $this->session->userdata('user_id')
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] 		= $this->upload_path;
                $config['allowed_types'] 	= $this->digital_file_types;
                $config['max_size'] 		= $this->allowed_file_size;
                $config['overwrite'] 		= FALSE;
                $config['encrypt_name'] 	= TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->erp->print_arrays($payment, $deposit);

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->pos_model->updatePayment($id, $payment, $deposit)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
            redirect("pos/sales");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			
            $payment 			 = $this->pos_model->getPaymentByID($id);
			$this->data['payment']  = $payment;
			$this->data['inv'] 	 = $this->pos_model->getInvoiceByID($payment->sale_id);
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'pos/edit_payment', $this->data);
        }
    }

    function updates()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->form_validation->set_rules('purchase_code', lang("purchase_code"), 'required');
        $this->form_validation->set_rules('envato_username', lang("envato_username"), 'required');
        if ($this->form_validation->run() == true) {
            $this->db->update('pos_settings', array('purchase_code' => $this->input->post('purchase_code', TRUE), 'envato_username' => $this->input->post('envato_username', TRUE)), array('pos_id' => 1));
            redirect('pos/updates');
        } else {
            $fields = array('version' => $this->pos_settings->version, 'code' => $this->pos_settings->purchase_code, 'username' => $this->pos_settings->envato_username, 'site' => base_url());
            $this->load->helper('update');
            $protocol = is_https() ? 'https://' : 'http://';
            $updates = get_remote_contents($protocol.'cloudnet.com.kh/api/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('updates')));
            $meta = array('page_title' => lang('updates'), 'bc' => $bc);
            $this->page_construct('pos/updates', $meta, $this->data);
        }
    }

    function install_update($file, $m_version, $version)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->load->helper('update');
        save_remote_file($file . '.zip');
        $this->erp->unzip('./files/updates/' . $file . '.zip');
        if ($m_version) {
            $this->load->library('migration');
            if (!$this->migration->latest()) {
                $this->session->set_flashdata('error', $this->migration->error_string());
                redirect("pos/updates");
            }
        }
        $this->db->update('pos_settings', array('version' => $version, 'update' => 0), array('pos_id' => 1));
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        redirect("pos/updates");
    }
	//----------- Export to excel and pdf POS Sales ---------------------
		function getPosSales($pdf = NULL, $excel = NULL)
    {
        $this->erp->checkPermissions('Sales');

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
			$this->db->where('sales.reference_no LIKE "SALE/POS/%"');
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
                $this->excel->getActiveSheet()->setTitle(lang('POS Sales List'));
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
	//-------------- End export POS Sales --------------------------------
	
	//--------------------Kitchen Area------------------
	function view_modal($id)
    {
        $this->erp->checkPermissions('index');
        
        $this->data['data'] = $this->pos_model->getKitchen($id);
        $this->load->view($this->theme . 'pos/view_modal', $this->data);
    }
	
	function view_complete(){
		$this->data['data'] = $this->pos_model->getComplete();
		$this->load->view($this->theme . 'pos/view_complete', $this->data);
	}
	
	function delete_item($id){
		$delete = $this->pos_model->clear_item($id);
		if($delete){
			redirect('pos/view_complete');
		}
	}
	
	public function suggestions()
    {
        $term   = $this->input->get('term', true);
        $sus_id = $this->input->get('pros', true);

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

        $rows = $this->pos_model->getProductNames($term);

        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $pr[] = array('id' => $row->id, 'code' => $row->code, 'label' => $row->name, 'qty' => $row->quantity);
                $r++;
            }
            echo json_encode($pr);
        } else {
            echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	function add_item(){
		$pid = $this->input->post('pro_id',true);
		$sid = $this->input->post('sus_id',true);
		$wid = $this->input->post('ware_id',true);
		$exp = explode(',', $pid);
		foreach($exp as $id){
			$result = $this->pos_model->selectProByID($id);
			$data = array(
				'suspend_id' => $sid,
				'product_id' => $result->id,
				'product_code' => $result->code,
				'product_name' => $result->name,
				'net_unit_price' => $result->price,
				'unit_price' => $result->price,
				'warehouse_id' => $wid,
				'real_unit_price' => $result->price
			);
			$res = $this->db->insert('suspended_items',$data);
		}
		redirect('pos/view_complete');
		/*
		foreach($results as $result){
			$data = array(
				'suspend_id' => $sid,
				'product_id' => $result->id,
				'product_code' => $result->code,
				'product_name' => $result->name,
				'net_unit_price' => $result->price,
				'unit_price' => $result->price,
				'warehouse_id' => $wid,
				'real_unit_price' => $result->price
			);
		}
		*/
		//$this->erp->print_arrays($data);
		//$this->db->insert('suspended_items',array('post_id'=>$product_id,'category_id'=>default_cate($user_id)));
	}
	
	function login1(){
		$this->form_validation->set_rules('user_password', lang("user_password"), 'required');
		 if ($this->form_validation->run() == true) {
		   //$pwd = do_hash($this->input->post('user_password'));

		   //if($this->pos_model->getpassword($pwd)){
				//redirect('pos'); 
		   //}else{
			  redirect('pos'); 
		   //}
		 }
	}
	
	function register_modal($pwd = NULL)
    {
        $this->erp->checkPermissions('index');
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'pos/register_modal', $this->data);   
    }
}
