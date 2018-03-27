<?php defined('BASEPATH') or exit('No direct script access allowed');

class Quotes extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            redirect('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('quotations', $this->Settings->language);
        $this->load->library('form_validation');
        $this->load->model('quotes_model');
		$this->load->model('documents_model');
		$this->load->model('products_model');
        $this->digital_upload_path = 'files/';
		$this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

    public function index($warehouse_id = null)
    {

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

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('quotes')));
        $meta = array('page_title' => lang('quotes'), 'bc' => $bc);
        $this->page_construct('quotes/index', $meta, $this->data);

    }

    public function getQuotes($warehouse_id = null)
    {
        $this->erp->checkPermissions('index', true);

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('quotes/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('quote_details'));
        $email_link = anchor('quotes/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_quote'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('quotes/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_quote'));
        $add_sale_link = anchor('sales/add/$1', '<i class="fa fa-heart"></i> ' . lang('create_sale'));
        $add_order_link = anchor('purchases_order/add/$1', '<i class="fa fa-star"></i> ' . lang('create_purchases_order'));
        $pdf_link = anchor('quotes/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_quote") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('quotes/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_quote') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $detail_link . '</li>';
		if($this->Owner || $this->Admin || $this->GP['quotes-edit'] ){
			$action .= '<li>' . $edit_link . '</li>';
		}				
        if($this->Owner || $this->Admin || $this->GP['sales-add'] ){
			$action .= '<li>' . $add_sale_link . '</li>';
		} 
		if($this->Owner || $this->Admin || $this->GP['purchases-order-add'] ){
			$action .= '<li>' . $add_order_link . '</li>';
		}
		if($this->Owner || $this->Admin || $this->GP['quotes-delete'] ){
			$action .= '<li>' . $delete_link . '</li>';
		}
        $action .= '</ul></div></div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("quotes.id, quotes.date, quotes.updated_at, quotes.reference_no, quotes.biller, quotes.customer, (erp_quotes.grand_total - erp_quotes.product_discount) as grand_total, (SELECT SUM(amount) FROM erp_payments WHERE deposit_quote_id = erp_quotes.id) AS deposit_amount, ((erp_quotes.grand_total - erp_quotes.product_discount) - COALESCE((SELECT SUM(amount) FROM erp_payments WHERE deposit_quote_id = erp_quotes.id), 0)) AS balance , quotes.status")
                ->from('quotes')
                ->where('quotes.warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("quotes.id, quotes.date, quotes.updated_at, quotes.reference_no, quotes.biller, quotes.customer, (erp_quotes.grand_total - erp_quotes.product_discount) as grand_total, (SELECT SUM(amount) FROM erp_payments WHERE deposit_quote_id = erp_quotes.id) AS deposit_amount, ((erp_quotes.grand_total - erp_quotes.product_discount) - COALESCE((SELECT SUM(amount) FROM erp_payments WHERE deposit_quote_id = erp_quotes.id), 0)) AS balance , quotes.status")
                ->from('quotes');
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "quotes.id");
        echo $this->datatables->generate();
    }

    public function modal_view($quote_id = null)
    {
        $this->erp->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->quotes_model->getQuoteByID($quote_id);
        if (!$this->session->userdata('view_right')) {
            $this->erp->view_rights($inv->created_by, true);
        }
        $this->data['rows'] 		= $this->quotes_model->getAllQuoteItems($quote_id);
        $this->data['customer'] 	= $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] 		= $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] 	= $this->site->getUser($inv->created_by);
        $this->data['updated_by'] 	= $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] 	= $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] 			= $inv;
		$this->data['deposits'] 	= $this->quotes_model->getPaymentForQuote($quote_id);

        $this->load->view($this->theme . 'quotes/modal_view', $this->data);

    }

    public function view($quote_id = null)
    {
        $this->erp->checkPermissions('index');

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->quotes_model->getQuoteByID($quote_id);
        if (!$this->session->userdata('view_right')) {
            $this->erp->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->quotes_model->getAllQuoteItems($quote_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
		
		$this->data['deposits'] = $this->quotes_model->getPaymentForQuote($quote_id);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('quotes'), 'page' => lang('quotes')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_quote_details'), 'bc' => $bc);
        $this->page_construct('quotes/view', $meta, $this->data);

    }

    public function pdf($quote_id = null, $view = null, $save_bufffer = null)
    {
        $this->erp->checkPermissions();

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->quotes_model->getQuoteByID($quote_id);
        if (!$this->session->userdata('view_right')) {
            $this->erp->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->quotes_model->getAllQuoteItems($quote_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $name = $this->lang->line("quote") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'quotes/pdf', $this->data, true);
        if ($view) {
            $this->load->view($this->theme . 'quotes/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->erp->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->erp->generate_pdf($html, $name);
        }
    }

    public function combine_pdf($quotes_id)
    {
        $this->erp->checkPermissions('pdf');

        foreach ($quotes_id as $quote_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->quotes_model->getQuoteByID($quote_id);
            if (!$this->session->userdata('view_right')) {
                $this->erp->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->quotes_model->getAllQuoteItems($quote_id);
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;

            $html[] = array(
                'content' => $this->load->view($this->theme . 'quotes/pdf', $this->data, true),
                'footer' => '',
            );
        }

        $name = lang("quotes") . ".pdf";
        $this->erp->generate_pdf($html, $name);

    }

    public function email($quote_id = null)
    {
        $this->erp->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $inv = $this->quotes_model->getQuoteByID($quote_id);
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
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $customer->name,
                'company' => $customer->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($quote_id, null, 'S'); //delete_files($attachment);
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->erp->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->db->update('quotes', array('status' => 'sent'), array('id' => $quote_id));
            $this->session->set_flashdata('message', $this->lang->line("email_sent"));
            redirect("quotes");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/quote.html')) {
                $quote_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/quote.html');
            } else {
                $quote_temp = file_get_contents('./themes/default/views/email_templates/quote.html');
            }

            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('quote').' (' . $inv->reference_no . ') '.lang('from').' '.$this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $quote_temp),
            );
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);

            $this->data['id'] = $quote_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'quotes/email', $this->data);

        }
    }

    public function add($catalog_id = NULL)
    {
        $this->erp->checkPermissions();

        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');

        if ($this->form_validation->run() == true) {
            $quantity 	= "quantity";
            $product 	= "product";
            $unit_cost 	= "unit_cost";
            $tax_rate 	= "tax_rate";
            $reference 	= $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qu');
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id 		= $this->input->post('warehouse')?$this->input->post('warehouse'):$this->Settings->default_warehouse;
            $customer_id 		= $this->input->post('customer');
            $biller_id 			= $this->input->post('biller');
            $status 			= $this->input->post('status');
			$service 			= $this->input->post('services');
            $shipping 			= $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details 	= $this->site->getCompanyByID($customer_id);
            $customer 			= $customer_details->name ? $customer_details->name : $customer_details->company;
            $biller_details 	= $this->site->getCompanyByID($biller_id);
            $biller 			= $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note 				= $this->erp->clear_tags($this->input->post('note'));

            $total 				= 0;
            $product_tax 		= 0;
            $order_tax 			= 0;
            $product_discount 	= 0;
            $order_discount 	= 0;
            $percentage 		= '%';
            $i 					= isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id 		= $_POST['product_id'][$r];
                $item_type 		= $_POST['product_type'][$r];
                $item_code 		= $_POST['product_code'][$r];
                $item_name 		= $_POST['product_name'][$r];
                $item_option 	= isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                //$option_details = $this->quotes_model->getProductOptionByID($item_option);
                $real_unit_price 	= $this->erp->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price 		= $this->erp->formatDecimal($_POST['unit_price'][$r]);
                $item_quantity 		= $_POST['quantity'][$r];
                $item_tax_rate 		= isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount 		= isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->quotes_model->getProductByCode($item_code) : null;
                    $unit_price = $real_unit_price;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = (($this->erp->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100;
                        } else {
                            $pr_discount = $this->erp->formatDecimal($discount);
                        }
                    }

                    $final_price 		= $this->erp->formatDecimal($unit_price - $pr_discount);
					$unit_price 		= $this->erp->formatDecimal($unit_price);
                    $item_net_price 	= $unit_price;
                    $pr_item_discount 	= $this->erp->formatDecimal($pr_discount * $item_quantity);
                    $product_discount 	+= $pr_item_discount;
                    $pr_tax 			= 0;
                    $pr_item_tax 		= 0;
                    $item_tax 			= 0;
                    $tax 				= "";

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
                                $item_net_price = $unit_price - $item_tax;
                            }

                        } elseif ($tax_details->type == 2) {

                            if ($product_details && $product_details->tax_method == 1) {
                                $pbt = (($unit_price) / (1 + ($tax_details->rate / 100)));
                                $item_tax = $this->erp->formatDecimal(($unit_price) - $pbt);

                                $tax = $tax_details->rate . "%";
                            } else {
                                //$item_tax = $this->erp->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate));
                                $pbt = (($unit_price) / (1 + ($tax_details->rate / 100)));
                                $item_tax = $this->erp->formatDecimal(($unit_price) - $pbt);

                                $tax = $tax_details->rate . "%";
                                $item_net_price = $unit_price - $item_tax;
                            }

                            $item_tax = $this->erp->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->erp->formatDecimal($item_tax * $item_quantity);

                    }

                    $product_tax 	+= $pr_item_tax;
                    $subtotal 		= ($item_net_price * $item_quantity);
					$finaltotal 	= ($final_price * $item_quantity);

                    $products[] = array(
                        'product_id' 		=> $item_id,
                        'product_code' 		=> $item_code,
                        'product_name' 		=> $item_name,
                        'product_type' 		=> $item_type,
                        'option_id' 		=> $item_option,
                        'net_unit_price' 	=> $item_net_price,
                        'unit_price' 		=> $this->erp->formatDecimal($unit_price),
                        'quantity' 			=> $item_quantity,
                        'warehouse_id' 		=> $warehouse_id,
                        'item_tax' 			=> $pr_item_tax,
                        'tax_rate_id' 		=> $pr_tax,
                        'tax' 				=> $tax,
                        'discount' 			=> $item_discount,
                        'item_discount' 	=> $pr_item_discount,
                        'subtotal' 			=> $this->erp->formatDecimal($subtotal),
						'final_total' 		=> $finaltotal,
                        'real_unit_price' 	=> $real_unit_price,
                    );

                    $total += $real_unit_price * $item_quantity;
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
                    $ods 			= explode("%", $order_discount_id);
                    $order_discount = (($total + $product_tax) * (Float) ($ods[0])) / 100;

                } else {
                    $order_discount = $order_discount_id;
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax 		= $product_tax + $order_tax;
            $grand_total 	= $this->erp->formatDecimal($total + $order_tax + $shipping - $order_discount);
			
			$final_total 	= $this->erp->formatDecimal($grand_total - $product_discount);
			
            $data = array(
            	'date' 				=> $date,
                'reference_no' 		=> $reference,
                'customer_id' 		=> $customer_id,
                'customer' 			=> $customer,
                'biller_id' 		=> $biller_id,
                'biller' 			=> $biller,
                'warehouse_id' 		=> $warehouse_id,
                'note' 				=> $note,
                'total' 			=> $total,
                'product_discount' 	=> $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' 	=> $order_discount,
                'total_discount' 	=> $total_discount,
                'product_tax' 		=> $product_tax,
                'order_tax_id' 		=> $order_tax_id,
                'order_tax' 		=> $order_tax,
                'total_tax' 		=> $total_tax,
                'shipping' 			=> $shipping,
                'grand_total' 		=> $grand_total,
				'final_total' 		=> $final_total,
                'status' 			=> $status,
                'created_by' 		=> $this->session->userdata('user_id'),
				'service_id' 		=> $service
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
                    $error = $this->upload->display_errors();
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
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment1'] = $photo;
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
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment2'] = $photo;
            }
			$payment = array();
			
			if($this->input->post('type_payment') == 'partial') {
				
				/* Deposit 1 */
				if ($this->input->post('deposit1') == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance = $gc->balance - $amount_paying;
					
					$payment[] = array(
						'date' 				=> $date,
						'reference_no' 		=> $this->site->getReference('sp'),
						'amount' 			=> $this->erp->formatDecimal($amount_paying),
						'pos_paid' 			=> $this->erp->formatDecimal($amount_paying),
						'paid_by' 			=> $this->input->post('paid_by_by_1_1'),
						'cheque_no' 		=> $this->input->post('cheque_no_1_1'),
						'bank_transfer_no' 	=> $this->input->post('bt_no1'),
						'cc_no' 			=> $this->input->post('gift_card_no_1_1'),
						'cc_holder' 		=> $this->input->post('pcc_holder_1_1'),
						'cc_month' 			=> $this->input->post('pcc_month_1_1'),
						'cc_year' 			=> $this->input->post('pcc_year_1_1'),
						'cc_type' 			=> $this->input->post('pcc_type_1_1'),
						'created_by' 		=> $this->session->userdata('user_id'),
						'note' 				=> 'Deposit1',
						'type' 				=> 'received',
						'gc_balance' 		=> $gc_balance,
						'biller_id' 		=> $biller_id
					);
                } else {
					if($this->input->post('amount-paid_1_1') == "" ){
						$amount = 0;
					}else{
						$amount = $this->input->post('amount-paid_1_1');
					}
					if($amount > 0){
						$payment[] = array(
							'date' 				=> $date,
							'reference_no' 		=> $this->site->getReference('sp'),
							'amount' 			=> $this->erp->formatDecimal($amount),
							'pos_paid' 			=> $this->erp->formatDecimal($amount),
							'paid_by' 			=> $this->input->post('deposit1'),
							'cheque_no' 		=> $this->input->post('cheque_no_1_1'),
							'bank_transfer_no' 	=> $this->input->post('bt_no1'),
							'cc_no' 			=> $this->input->post('pcc_no_1_1'),
							'cc_holder' 		=> $this->input->post('pcc_holder_1_1'),
							'cc_month' 			=> $this->input->post('pcc_month_1_1'),
							'cc_year' 			=> $this->input->post('pcc_year_1_1'),
							'cc_type' 			=> $this->input->post('pcc_type_1_1'),
							'created_by' 		=> $this->session->userdata('user_id'),
							'note' 				=> 'Deposit1',
							'type' 				=> 'received',
							'biller_id' 		=> $biller_id
						);
					}
                }
				if($_POST['deposit1'] == 'depreciation') {
					$no = sizeof($_POST['no']);
					$period = 1;
					for($m = 0; $m < $no; $m++){
						$dateline = date('Y-m-d', strtotime($_POST['dateline_1_1'][$m]));
						$loans[] = array(
							'period' 	=> $period,
							'sale_id' 	=> '',
							'interest' 	=> $_POST['interest'][$m],
							'principle' => $_POST['principle'][$m],
							'payment' 	=> $_POST['payment_amt'][$m],
							'balance' 	=> $_POST['balance'][$m],
							'type' 		=> $_POST['depreciation_type'],
							'rated' 	=> $_POST['depreciation_rate1'],
							'note' 		=> $_POST['note_1'][$m],
							'dateline' 	=> $dateline,
							'biller_id' => $biller_id
						);
						$period++;
					}
				}
				
				/* Deposit 2 */
				if ($this->input->post('deposit2') == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance = $gc->balance - $amount_paying;
					
					$payment[] = array(
						'date' 				=> $date,
						'reference_no' 		=> $this->site->getReference('sp'),
						'amount' 			=> $this->erp->formatDecimal($amount_paying),
						'pos_paid' 			=> $this->erp->formatDecimal($amount_paying),
						'paid_by' 			=> $this->input->post('paid_by_by_1_2'),
						'cheque_no' 		=> $this->input->post('cheque_no_1_2'),
						'bank_transfer_no' 	=> $this->input->post('bt_no2'),
						'cc_no' 			=> $this->input->post('gift_card_no_1_2'),
						'cc_holder' 		=> $this->input->post('pcc_holder_1_2'),
						'cc_month' 			=> $this->input->post('pcc_month_1_2'),
						'cc_year' 			=> $this->input->post('pcc_year_1_2'),
						'cc_type' 			=> $this->input->post('pcc_type_1_2'),
						'created_by' 		=> $this->session->userdata('user_id'),
						'note' 				=> 'Deposit2',
						'type' 				=> 'received',
						'gc_balance' 		=> $gc_balance,
						'biller_id' 		=> $biller_id
					);
                } else {
					if($this->input->post('amount-paid_1_2') == "" ){
						$amount2 = 0;
					}else{
						$amount2 = $this->input->post('amount-paid_1_2');
					}
					if($amount2 > 0){
						$payment[] = array(
							'date' 				=> $date,
							'reference_no' 		=> $this->site->getReference('sp'),
							'amount' 			=> $this->erp->formatDecimal($amount2),
							'pos_paid' 			=> $this->erp->formatDecimal($amount2),
							'paid_by' 			=> $this->input->post('deposit2'),
							'cheque_no' 		=> $this->input->post('cheque_no_1_2'),
							'bank_transfer_no' 	=> $this->input->post('bt_no2'),
							'cc_no' 			=> $this->input->post('pcc_no_1_2'),
							'cc_holder' 		=> $this->input->post('pcc_holder_1_2'),
							'cc_month' 			=> $this->input->post('pcc_month_1_2'),
							'cc_year' 			=> $this->input->post('pcc_year_1_2'),
							'cc_type' 			=> $this->input->post('pcc_type_1_2'),
							'created_by' 		=> $this->session->userdata('user_id'),
							'note' 				=> 'Deposit2',
							'type' 				=> 'received',
							'biller_id' 		=> $biller_id
						);
					}
					
                }
				if($_POST['deposit2'] == 'depreciation') {
					$no = sizeof($_POST['no']);
					$period = 1;
					for($m = 0; $m < $no; $m++){
						$dateline = date('Y-m-d', strtotime($_POST['dateline'][$m]));
						$loans[] = array(
							'period' 	=> $period,
							'sale_id' 	=> '',
							'interest' 	=> $_POST['interest'][$m],
							'principle' => $_POST['principle'][$m],
							'payment' 	=> $_POST['payment_amt'][$m],
							'balance' 	=> $_POST['balance'][$m],
							'type' 		=> $_POST['depreciation_type'],
							'rated' 	=> $_POST['depreciation_rate1'],
							'note' 		=> $_POST['note_1'][$m],
							'dateline' 	=> $dateline,
							'biller_id' => $biller_id
						);
						$period++;
					}
				}
				
				/* Deposit 3 */
				if ($this->input->post('deposit3') == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance = $gc->balance - $amount_paying;
					
					$payment[] = array(
						'date' 				=> $date,
						'reference_no' 		=> $this->site->getReference('sp'),
						'amount' 			=> $this->erp->formatDecimal($amount_paying),
						'pos_paid' 			=> $this->erp->formatDecimal($amount_paying),
						'paid_by' 			=> $this->input->post('paid_by_by_1_3'),
						'cheque_no' 		=> $this->input->post('cheque_no_1_3'),
						'bank_transfer_no' 	=> $this->input->post('bt_no3'),
						'cc_no' 			=> $this->input->post('gift_card_no_1_3'),
						'cc_holder' 		=> $this->input->post('pcc_holder_1_3'),
						'cc_month' 			=> $this->input->post('pcc_month_1_3'),
						'cc_year' 			=> $this->input->post('pcc_year_1_3'),
						'cc_type' 			=> $this->input->post('pcc_type_1_3'),
						'created_by' 		=> $this->session->userdata('user_id'),
						'note' 				=> 'Deposit3',
						'type' 				=> 'received',
						'gc_balance' 		=> $gc_balance,
						'biller_id' 		=> $biller_id
					);
                } else {
					if($this->input->post('amount-paid_1_3') == "" ){
						$amount3 = 0;
					}else{
						$amount3 = $this->input->post('amount-paid_1_3');
					}
					if($amount3 > 0){
						$payment[] = array(
							'date' 				=> $date,
							'reference_no' 		=> $this->site->getReference('sp'),
							'amount' 			=> $this->erp->formatDecimal($amount3),
							'pos_paid' 			=> $this->erp->formatDecimal($amount3),
							'paid_by' 			=> $this->input->post('deposit3'),
							'cheque_no' 		=> $this->input->post('cheque_no_1_3'),
							'bank_transfer_no' 	=> $this->input->post('bt_no3'),
							'cc_no' 			=> $this->input->post('pcc_no_1_3'),
							'cc_holder' 		=> $this->input->post('pcc_holder_1_3'),
							'cc_month' 			=> $this->input->post('pcc_month_1_3'),
							'cc_year' 			=> $this->input->post('pcc_year_1_3'),
							'cc_type' 			=> $this->input->post('pcc_type_1_3'),
							'created_by' 		=> $this->session->userdata('user_id'),
							'note' 				=> 'Deposit3',
							'type' 				=> 'received',
							'biller_id' 		=> $biller_id
						);
					}
					
                }
				if($_POST['deposit3'] == 'depreciation') {
					$no = sizeof($_POST['no']);
					$period = 1;
					for($m = 0; $m < $no; $m++){
						$dateline = date('Y-m-d', strtotime($_POST['dateline'][$m]));
						$loans[] = array(
							'period' 	=> $period,
							'sale_id' 	=> '',
							'interest' 	=> $_POST['interest'][$m],
							'principle' => $_POST['principle'][$m],
							'payment' 	=> $_POST['payment_amt'][$m],
							'balance' 	=> $_POST['balance'][$m],
							'type' 		=> $_POST['depreciation_type'],
							'rated' 	=> $_POST['depreciation_rate1'],
							'note' 		=> $_POST['note_1'][$m],
							'dateline' 	=> $dateline,
							'biller_id' => $biller_id
						);
						$period++;
					}
				}
			}
			
			if($this->input->post('type_payment') == 'full_payment') {
				if ($this->input->post('paid_by_by') == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance = $gc->balance - $amount_paying;
					
					$payment[] = array(
						'date' 				=> $date,
						'reference_no' 		=> $this->site->getReference('sp'),
						'amount' 			=> $this->erp->formatDecimal($amount_paying),
						'pos_paid' 			=> $this->erp->formatDecimal($amount_paying),
						'paid_by' 			=> $this->input->post('paid_by_by'),
						'cheque_no' 		=> $this->input->post('cheque_no'),
						'bank_transfer_no' 	=> $this->input->post('bt_no'),
						'cc_no' 			=> $this->input->post('gift_card_no'),
						'cc_holder' 		=> $this->input->post('pcc_holder'),
						'cc_month' 			=> $this->input->post('pcc_month'),
						'cc_year' 			=> $this->input->post('pcc_year'),
						'cc_type' 			=> $this->input->post('pcc_type'),
						'created_by' 		=> $this->session->userdata('user_id'),
						'note' 				=> 'Full Payment',
						'type' 				=> 'received',
						'gc_balance' 		=> $gc_balance,
						'biller_id' 		=> $biller_id
					);
                } else {
					if($this->input->post('amount-paid') > 0){
						$payment[] = array(
							'date' 				=> $date,
							'reference_no' 		=> $this->site->getReference('sp'),
							'amount' 			=> $this->erp->formatDecimal($this->input->post('amount-paid')),
							'pos_paid' 			=> $this->erp->formatDecimal($this->input->post('amount-paid')),
							'paid_by' 			=> $this->input->post('paid_by_by'),
							'cheque_no' 		=> $this->input->post('cheque_no'),
							'bank_transfer_no' 	=> $this->input->post('bt_no'),
							'cc_no' 			=> $this->input->post('pcc_no'),
							'cc_holder' 		=> $this->input->post('pcc_holder'),
							'cc_month' 			=> $this->input->post('pcc_month'),
							'cc_year' 			=> $this->input->post('pcc_year'),
							'cc_type' 			=> $this->input->post('pcc_type'),
							'created_by' 		=> $this->session->userdata('user_id'),
							'note' 				=> 'Full Payment',
							'type' 				=> 'received',
							'biller_id' 		=> $biller_id
						);
					}
					
                }
				if($_POST['paid_by_by'] == 'depreciation') {
					$no = sizeof($_POST['no']);
					$period = 1;
					for($m = 0; $m < $no; $m++){
						$dateline = date('Y-m-d', strtotime($_POST['dateline_1_2'][$m]));
						$loans[] = array(
							'period' 	=> $period,
							'sale_id' 	=> '',
							'interest' 	=> $_POST['interest'][$m],
							'principle' => $_POST['principle'][$m],
							'payment' 	=> $_POST['payment_amt'][$m],
							'balance' 	=> $_POST['balance'][$m],
							'type' 		=> $_POST['depreciation_type'],
							'rated' 	=> $_POST['depreciation_rate1'],
							'note' 		=> $_POST['note_1'][$m],
							'dateline' 	=> $dateline,
							'biller_id' => $biller_id
						);
						$period++;
					}
				}else{
					$loans = array();
				}
			}
			
		    //$this->erp->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && $this->quotes_model->addQuote($data, $products, $payment)) {
            $this->session->set_userdata('remove_quls', 1);
            $this->session->set_flashdata('message', $this->lang->line("quote_added"));
            redirect('quotes');
        } else {
			
			if($catalog_id){
				$items = $this->documents_model->getDocumentByID($catalog_id);
				$photo = $this->documents_model->getDocumentPhoto($catalog_id); 
				$pdata = array(
					'code' 				=> $items->product_code,
					'barcode_symbology' => 'code128',
					'name' 				=> $items->product_name,
					'type' 				=> 'standard',
					'category_id' 		=> $items->category_id,
					'subcategory_id' 	=> $items->subcategory_id,
					'cost' 				=> $items->cost,
					'price' 			=> $items->price,
					'unit' 				=> $items->unit,
					'image' 			=> $items->image,
					'tax_method' 		=> '0',
					'track_quantity' 	=> '0',
					'product_details' 	=> $items->description,
					'supplier1' 		=> 0,          
					'currentcy_code'    => 'USD',
					'inactived' 		=> 0,
					'brand_id' 			=> $items->brand_id,
					'is_serial' 		=> $items->serial
				);
				
				foreach($photo as $img){
					$image[] = $img->photo;
				}
				$check = $this->documents_model->checkProduct($items->product_code);
				if($check){
					$row = $this->documents_model->getProductByCode($items->product_code);
					
					$option 				= false;
					$row->quantity 			= 0;
					$row->item_tax_method 	= $row->tax_method;
					$row->qty 				= 1;
					$row->discount 			= '0';
					$options 				= $this->quotes_model->getProductOptions($row->id, $warehouse_id);
					
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
					$pis = $this->quotes_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
					
					if ($pis) {
						foreach ($pis as $pi) {
							$row->quantity += $pi->quantity_balance;
						}
					}
					if ($options) {
						$option_quantity = 0;
						foreach ($options as $option) {
							$pis = $this->quotes_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
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
							$combo_items = $this->quotes_model->getProductComboItems($row->id, $warehouse_id);
						}
						$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'options' => $options);
					} else {
						$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => false, 'options' => $options);
					}
					$this->data['catalog_items'] = json_encode($pr);
				}else{
					$this->products_model->addProduct($pdata, null, null, null, $image, null);
					$row = $this->documents_model->getProductByCode($items->product_code);
					
					$option 		= false;
					$row->quantity 	= 0;
					$row->item_tax_method = $row->tax_method;
					$row->qty 		= 1;
					$row->discount 	= '0';
					$options 		= $this->quotes_model->getProductOptions($row->id, $warehouse_id);
					
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
					$pis = $this->quotes_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
					
					if ($pis) {
						foreach ($pis as $pi) {
							$row->quantity += $pi->quantity_balance;
						}
					}
					if ($options) {
						$option_quantity = 0;
						foreach ($options as $option) {
							$pis = $this->quotes_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
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
							$combo_items = $this->quotes_model->getProductComboItems($row->id, $warehouse_id);
						}
						$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'options' => $options);
					} else {
						$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => false, 'options' => $options);
					}
					$this->data['catalog_items'] = json_encode($pr);
				}
			}
            $this->data['error'] 		= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['catalog_id'] 	= $catalog_id;
            $this->data['customer'] 	= $this->site->getAllCompanies('customer');
            $this->data['billers'] 		= ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->data['customers'] 	= $this->site->getCustomers();
            //$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->data['service'] 		= $this->quotes_model->getAllServices();
            $this->data['tax_rates'] 	= $this->site->getAllTaxRates();
            $this->data['warehouses'] 	= ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllWarehouses() : null;
            $this->data['qunumber'] 	= ''; //$this->site->getReference('qu');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('quotes'), 'page' => lang('quotes')), array('link' => '#', 'page' => lang('add_quote')));
            $meta = array('page_title' => lang('add_quote'), 'bc' => $bc);
            $this->page_construct('quotes/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->erp->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->quotes_model->getQuoteByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->erp->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('reference_no', $this->lang->line("reference_no"), 'required');
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        //$this->form_validation->set_rules('note', $this->lang->line("note"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $quantity 	= "quantity";
            $product 	= "product";
            $unit_cost 	= "unit_cost";
            $tax_rate 	= "tax_rate";
            $reference 	= $this->input->post('reference_no');
            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id 		= $this->input->post('warehouse')?$this->input->post('warehouse'):$this->Settings->default_warehouse;
            $customer_id 		= $this->input->post('customer');
            $biller_id 			= $this->input->post('biller');
            $status 			= $this->input->post('status');
			$service 			= $this->input->post('services');
            $shipping 			= $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details 	= $this->site->getCompanyByID($customer_id);
            $customer 			= $customer_details->name ? $customer_details->name : $customer_details->company;
            $biller_details 	= $this->site->getCompanyByID($biller_id);
            $biller 			= $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note 				= $this->erp->clear_tags($this->input->post('note'));

            $total 				= 0;
            $product_tax 		= 0;
            $order_tax 			= 0;
            $product_discount 	= 0;
            $order_discount 	= 0;
            $percentage 		= '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id 		= $_POST['product_id'][$r];
                $item_type 		= $_POST['product_type'][$r];
                $item_code 		= $_POST['product_code'][$r];
                $item_name 		= $_POST['product_name'][$r];
                $item_option 	= isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                //$option_details = $this->quotes_model->getProductOptionByID($item_option);
                $real_unit_price = $this->erp->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price 	= $this->erp->formatDecimal($_POST['unit_price'][$r]);
                $item_quantity 	= $_POST['quantity'][$r];
                $item_tax_rate 	= isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount 	= isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->quotes_model->getProductByCode($item_code) : null;
                    $unit_price = $real_unit_price;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = (($this->erp->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100;
                        } else {
                            $pr_discount = $this->erp->formatDecimal($discount);
                        }
                    }

                    $final_price 		= $this->erp->formatDecimal($unit_price - $pr_discount);
					$unit_price 		= $this->erp->formatDecimal($unit_price);
                    $item_net_price 	= $unit_price;
                    $pr_item_discount 	= $this->erp->formatDecimal($pr_discount * $item_quantity);
                    $product_discount 	+= $pr_item_discount;
                    $pr_tax 			= 0;
                    $pr_item_tax 		= 0;
                    $item_tax 			= 0;
                    $tax 				= "";

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
                                $item_net_price = $unit_price - $item_tax;
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
                                $item_net_price = $unit_price - $item_tax;
                            }

                            $item_tax = $this->erp->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->erp->formatDecimal($item_tax * $item_quantity);

                    }

                    $product_tax 	+= $pr_item_tax;
                    $subtotal 		= ($unit_price * $item_quantity);
                    $ftotal 		= ($final_price * $item_quantity);

                    $products[] = array(
                        'product_id' 		=> $item_id,
                        'product_code' 		=> $item_code,
                        'product_name' 		=> $item_name,
                        'product_type' 		=> $item_type,
                        'option_id' 		=> $item_option,
                        'net_unit_price' 	=> $item_net_price,
                        'unit_price' 		=> $this->erp->formatDecimal($unit_price),
                        'quantity' 			=> $item_quantity,
                        'warehouse_id' 		=> $warehouse_id,
                        'item_tax' 			=> $pr_item_tax,
                        'tax_rate_id' 		=> $pr_tax,
                        'tax' 				=> $tax,
                        'discount' 			=> $item_discount,
                        'item_discount' 	=> $pr_item_discount,
                        'subtotal' 			=> $this->erp->formatDecimal($subtotal),
						'final_total' 		=> $ftotal,
                        'real_unit_price' 	=> $real_unit_price,
                    );

                    $total += $unit_price * $item_quantity;
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
                    $order_discount = (($total + $product_tax) * (Float) ($ods[0])) / 100;

                } else {
                    $order_discount = $order_discount_id;
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $product_tax + $order_tax;
            $grand_total = $item_net_price = $this->erp->formatDecimal($total + $order_tax + $shipping - $order_discount);
			
			$final_total = $this->erp->formatDecimal($grand_total - $product_discount);
			
            $data = array(
				'date' 				=> $date,
                'reference_no' 		=> $reference,
                'customer_id' 		=> $customer_id,
                'customer' 			=> $customer,
                'biller_id' 		=> $biller_id,
                'biller' 			=> $biller,
                'warehouse_id' 		=> $warehouse_id,
                'note' 				=> $note,
                'total' 			=> $total,
                'product_discount' 	=> $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' 	=> $order_discount,
                'total_discount' 	=> $total_discount,
                'product_tax' 		=> $product_tax,
                'order_tax_id' 		=> $order_tax_id,
                'order_tax' 		=> $order_tax,
                'total_tax' 		=> $total_tax,
                'shipping' 			=> $shipping,
                'grand_total' 		=> $grand_total,
				'final_total' 		=> $final_total,
                'status' 			=> $status,
                'updated_by' 		=> $this->session->userdata('user_id'),
                'updated_at' 		=> date('Y-m-d H:i:s'),
				'service_id' 		=> $service
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
                    $error = $this->upload->display_errors();
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
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment1'] = $photo;
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
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment2'] = $photo;
            }
			
			$payment = array();

			if($this->input->post('type_payment') == 'partial') {
				
				/* Deposit 1 */
				if ($this->input->post('deposit1') == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance = $gc->balance - $amount_paying;
					
					$payment[] = array(
						'date' 				=> $date,
						'reference_no' 		=> $this->input->post('payment_reference1')?$this->input->post('payment_reference1'):$this->site->getReference('sp'),
						'amount' 			=> $this->erp->formatDecimal($amount_paying),
						'paid_by' 			=> $this->input->post('paid_by_by_1_1'),
						'cheque_no' 		=> $this->input->post('cheque_no_1_1'),
						'bank_transfer_no' 	=> $this->input->post('bt_no1'),
						'cc_no' 			=> $this->input->post('gift_card_no_1_1'),
						'cc_holder' 		=> $this->input->post('pcc_holder_1_1'),
						'cc_month' 			=> $this->input->post('pcc_month_1_1'),
						'cc_year' 			=> $this->input->post('pcc_year_1_1'),
						'cc_type' 			=> $this->input->post('pcc_type_1_1'),
						'created_by' 		=> $this->session->userdata('user_id'),
						'note' 				=> 'Deposit1',
						'type' 				=> 'received',
						'gc_balance' 		=> $gc_balance,
						'biller_id' 		=> $biller_id
					);
                } else {
					if($this->input->post('amount-paid_1_1') > 0){
						$payment[] = array(
							'date' 				=> $date,
							'reference_no'		=> $this->input->post('payment_reference1')?$this->input->post('payment_reference1'):$this->site->getReference('sp'),
							'amount' 			=> $this->erp->formatDecimal($this->input->post('amount-paid_1_1')),
							'paid_by' 			=> $this->input->post('deposit1'),
							'cheque_no' 		=> $this->input->post('cheque_no_1_1'),
							'bank_transfer_no' 	=> $this->input->post('bt_no1'),
							'cc_no' 			=> $this->input->post('pcc_no_1_1'),
							'cc_holder'			=> $this->input->post('pcc_holder_1_1'),
							'cc_month' 			=> $this->input->post('pcc_month_1_1'),
							'cc_year' 			=> $this->input->post('pcc_year_1_1'),
							'cc_type' 			=> $this->input->post('pcc_type_1_1'),
							'created_by' 		=> $this->session->userdata('user_id'),
							'note' 				=> 'Deposit1',
							'type' 				=> 'received',
							'biller_id' 		=> $biller_id
						);
					}
                }
				if($_POST['deposit1'] == 'depreciation') {
					$no = sizeof($_POST['no']);
					$period = 1;
					for($m = 0; $m < $no; $m++){
						$dateline = date('Y-m-d', strtotime($_POST['dateline_1_1'][$m]));
						$loans[] = array(
							'period' 	=> $period,
							'sale_id' 	=> '',
							'interest' 	=> $_POST['interest'][$m],
							'principle' => $_POST['principle'][$m],
							'payment' 	=> $_POST['payment_amt'][$m],
							'balance' 	=> $_POST['balance'][$m],
							'type' 		=> $_POST['depreciation_type'],
							'rated' 	=> $_POST['depreciation_rate1'],
							'note' 		=> $_POST['note_1'][$m],
							'dateline' 	=> $dateline,
							'biller_id' => $biller_id
						);
						$period++;
					}
				}
				
				/* Deposit 2 */
				if ($this->input->post('deposit2') == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance = $gc->balance - $amount_paying;
					
					$payment[] = array(
						'date' 				=> $date,
						'reference_no' 		=> $this->input->post('payment_reference2')?$this->input->post('payment_reference2'):$this->site->getReference('sp'),
						'amount' 			=> $this->erp->formatDecimal($amount_paying),
						'paid_by' 			=> $this->input->post('paid_by_by_1_2'),
						'cheque_no' 		=> $this->input->post('cheque_no_1_2'),
						'bank_transfer_no' 	=> $this->input->post('bt_no2'),
						'cc_no' 			=> $this->input->post('gift_card_no_1_2'),
						'cc_holder' 		=> $this->input->post('pcc_holder_1_2'),
						'cc_month' 			=> $this->input->post('pcc_month_1_2'),
						'cc_year' 			=> $this->input->post('pcc_year_1_2'),
						'cc_type' 			=> $this->input->post('pcc_type_1_2'),
						'created_by' 		=> $this->session->userdata('user_id'),
						'note' 				=> 'Deposit2',
						'type' 				=> 'received',
						'gc_balance' 		=> $gc_balance,
						'biller_id' 		=> $biller_id
					);
                } else {
					if($this->input->post('amount-paid_1_2') > 0){
						$payment[] = array(
							'date' 				=> $date,
							'reference_no' 		=> $this->input->post('payment_reference2')?$this->input->post('payment_reference2'):$this->site->getReference('sp'),
							'amount' 			=> $this->erp->formatDecimal($this->input->post('amount-paid_1_2')),
							'paid_by' 			=> $this->input->post('deposit2'),
							'cheque_no' 		=> $this->input->post('cheque_no_1_2'),
							'bank_transfer_no'	=> $this->input->post('bt_no2'),
							'cc_no' 			=> $this->input->post('pcc_no_1_2'),
							'cc_holder' 		=> $this->input->post('pcc_holder_1_2'),
							'cc_month' 			=> $this->input->post('pcc_month_1_2'),
							'cc_year' 			=> $this->input->post('pcc_year_1_2'),
							'cc_type' 			=> $this->input->post('pcc_type_1_2'),
							'created_by' 		=> $this->session->userdata('user_id_1_2'),
							'note' 				=> 'Deposit2',
							'type' 				=> 'received',
							'biller_id' 		=> $biller_id
						);
					}
                }
				if($_POST['deposit2'] == 'depreciation') {
					$no = sizeof($_POST['no']);
					$period = 1;
					for($m = 0; $m < $no; $m++){
						$dateline = date('Y-m-d', strtotime($_POST['dateline'][$m]));
						$loans[] = array(
							'period' 	=> $period,
							'sale_id' 	=> '',
							'interest' 	=> $_POST['interest'][$m],
							'principle' => $_POST['principle'][$m],
							'payment' 	=> $_POST['payment_amt'][$m],
							'balance' 	=> $_POST['balance'][$m],
							'type' 		=> $_POST['depreciation_type'],
							'rated' 	=> $_POST['depreciation_rate1'],
							'note' 		=> $_POST['note_1'][$m],
							'dateline' 	=> $dateline,
							'biller_id' => $biller_id
						);
						$period++;
					}
				}
				
				/* Deposit 3 */
				if ($this->input->post('deposit3') == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance = $gc->balance - $amount_paying;
					
					$payment[] = array(
						'date' 				=> $date,
						'reference_no' 		=> $this->input->post('payment_reference3')?$this->input->post('payment_reference3'):$this->site->getReference('sp'),
						'amount' 			=> $this->erp->formatDecimal($amount_paying),
						'paid_by' 			=> $this->input->post('paid_by_by_1_3'),
						'cheque_no' 		=> $this->input->post('cheque_no_1_3'),
						'bank_transfer_no' 	=> $this->input->post('bt_no3'),
						'cc_no' 			=> $this->input->post('gift_card_no_1_3'),
						'cc_holder' 		=> $this->input->post('pcc_holder_1_3'),
						'cc_month' 			=> $this->input->post('pcc_month_1_3'),
						'cc_year' 			=> $this->input->post('pcc_year_1_3'),
						'cc_type' 			=> $this->input->post('pcc_type_1_3'),
						'created_by' 		=> $this->session->userdata('user_id'),
						'note' 				=> 'Deposit3',
						'type' 				=> 'received',
						'gc_balance' 		=> $gc_balance,
						'biller_id' 		=> $biller_id
					);
                } else {
					IF($this->input->post('amount-paid_1_3') > 0){
						$payment[] = array(
							'date' 				=> $date,
							'reference_no' 		=> $this->input->post('payment_reference3')?$this->input->post('payment_reference3'):$this->site->getReference('sp'),
							'amount' 			=> $this->erp->formatDecimal($this->input->post('amount-paid_1_3')),
							'paid_by' 			=> $this->input->post('deposit3'),
							'cheque_no' 		=> $this->input->post('cheque_no_1_3'),
							'bank_transfer_no' 	=> $this->input->post('bt_no3'),
							'cc_no' 			=> $this->input->post('pcc_no_1_3'),
							'cc_holder' 		=> $this->input->post('pcc_holder_1_3'),
							'cc_month' 			=> $this->input->post('pcc_month_1_3'),
							'cc_year' 			=> $this->input->post('pcc_year_1_3'),
							'cc_type' 			=> $this->input->post('pcc_type_1_3'),
							'created_by' 		=> $this->session->userdata('user_id_1_3'),
							'note' 				=> 'Deposit3',
							'type' 				=> 'received',
							'biller_id' 		=> $biller_id
						);
					}
                }
				if($_POST['deposit3'] == 'depreciation') {
					$no = sizeof($_POST['no']);
					$period = 1;
					for($m = 0; $m < $no; $m++){
						$dateline = date('Y-m-d', strtotime($_POST['dateline'][$m]));
						$loans[] = array(
							'period' 	=> $period,
							'sale_id' 	=> '',
							'interest' 	=> $_POST['interest'][$m],
							'principle' => $_POST['principle'][$m],
							'payment' 	=> $_POST['payment_amt'][$m],
							'balance' 	=> $_POST['balance'][$m],
							'type' 		=> $_POST['depreciation_type'],
							'rated' 	=> $_POST['depreciation_rate1'],
							'note' 		=> $_POST['note_1'][$m],
							'dateline' 	=> $dateline,
							'biller_id' => $biller_id
						);
						$period++;
					}
				}
			}
			
			if($this->input->post('type_payment') == 'full_payment') {
				if ($this->input->post('paid_by_by') == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance = $gc->balance - $amount_paying;
					
					$payment[] = array(
						'date' 				=> $date,
						'reference_no' 		=> $this->input->post('payment_reference')?$this->input->post('payment_reference'):$this->site->getReference('sp'),
						'amount' 			=> $this->erp->formatDecimal($amount_paying),
						'paid_by' 			=> $this->input->post('paid_by_by'),
						'cheque_no' 		=> $this->input->post('cheque_no'),
						'bank_transfer_no' 	=> $this->input->post('bt_no'),
						'cc_no' 			=> $this->input->post('gift_card_no'),
						'cc_holder' 		=> $this->input->post('pcc_holder'),
						'cc_month' 			=> $this->input->post('pcc_month'),
						'cc_year' 			=> $this->input->post('pcc_year'),
						'cc_type' 			=> $this->input->post('pcc_type'),
						'created_by' 		=> $this->session->userdata('user_id'),
						'note' 				=> 'Full Payment',
						'type' 				=> 'received',
						'gc_balance' 		=> $gc_balance,
						'biller_id' 		=> $biller_id
					);
                } else {
					if($this->input->post('amount-paid') > 0 ){
						$payment[] = array(
							'date' 				=> $date,
							'reference_no' 		=> $this->input->post('payment_reference')?$this->input->post('payment_reference'):$this->site->getReference('sp'),
							'amount' 			=> $this->erp->formatDecimal($this->input->post('amount-paid')),
							//'paid_by_by' => $this->input->post('paid_by_by'),
							'paid_by' 			=> $this->input->post('paid_by_by'),
							'cheque_no' 		=> $this->input->post('cheque_no'),
							'bank_transfer_no' 	=> $this->input->post('bt_no'),
							'cc_no' 			=> $this->input->post('pcc_no'),
							'cc_holder' 		=> $this->input->post('pcc_holder'),
							'cc_month' 			=> $this->input->post('pcc_month'),
							'cc_year' 			=> $this->input->post('pcc_year'),
							'cc_type' 			=> $this->input->post('pcc_type'),
							'created_by' 		=> $this->session->userdata('user_id'),
							'note' 				=> 'Full Payment',
							'type' 				=> 'received',
							'biller_id' 		=> $biller_id
						);
					}
                }
				if($_POST['paid_by_by'] == 'depreciation') {
					$no = sizeof($_POST['no']);
					$period = 1;
					for($m = 0; $m < $no; $m++){
						$dateline = date('Y-m-d', strtotime($_POST['dateline_1_2'][$m]));
						$loans[] = array(
							'period' 	=> $period,
							'sale_id' 	=> '',
							'interest' 	=> $_POST['interest'][$m],
							'principle' => $_POST['principle'][$m],
							'payment' 	=> $_POST['payment_amt'][$m],
							'balance' 	=> $_POST['balance'][$m],
							'type' 		=> $_POST['depreciation_type'],
							'rated' 	=> $_POST['depreciation_rate1'],
							'note' 		=> $_POST['note_1'][$m],
							'dateline' 	=> $dateline,
							'biller_id' => $biller_id
						);
						$period++;
					}
				}else{
					$loans = array();
				}
			}
			//$this->erp->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && $this->quotes_model->updateQuote($id, $data, $products, $payment)) {
            $this->session->set_userdata('remove_quls', 1);
            $this->session->set_flashdata('message', $this->lang->line("quote_added"));
            redirect('quotes');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $this->quotes_model->getQuoteByID($id);
            $inv_items = $this->quotes_model->getAllQuoteItems($id);
			$this->data['payment'] = $this->quotes_model->getPaymentByQuoteID($id);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                } else {
                    unset($row->details, $row->product_details, $row->cost, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
                }
                $row->quantity = 0;
                $pis = $this->quotes_model->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->qty = $item->quantity;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $this->erp->formatDecimal($item->net_unit_price + $this->erp->formatDecimal($item->item_discount / $item->quantity));
                $row->unit_price = $row->tax_method ? $item->unit_price + $this->erp->formatDecimal($item->item_discount / $item->quantity) + $this->erp->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate = $item->tax_rate_id;
                $row->option = $item->option_id;
                $options = $this->quotes_model->getProductOptions($row->id, $item->warehouse_id);

                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->quotes_model->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
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

                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->quotes_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                $ri = $this->Settings->item_addition ? $row->id : $c;
                if ($row->tax_rate) {
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'options' => $options);
                } else {
                    $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => false, 'options' => $options);
                }
                $c++;
            }
            $this->data['inv_items'] 		= json_encode($pr);
            $this->data['id'] 				= $id;
			$this->data['payment_deposit'] 	= $this->quotes_model->getPaymentForQuote($id);
			$this->data['payment_full'] 	= $this->quotes_model->getFullPaymentByQuoteID($id);
            //$this->data['currencies'] 	= $this->site->getAllCurrencies();
			$this->data['service'] 			= $this->quotes_model->getAllServices();
            $this->data['billers'] 			= ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->data['customers'] 		= $this->site->getCustomers();
            $this->data['tax_rates'] 		= $this->site->getAllTaxRates();
            $this->data['warehouses'] 		= ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllWarehouses() : null;

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('quotes'), 'page' => lang('quotes')), array('link' => '#', 'page' => lang('edit_quote')));
            $meta = array('page_title' => lang('edit_quote'), 'bc' => $bc);
            $this->page_construct('quotes/edit', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->erp->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->quotes_model->deleteQuote($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("quote_deleted");die();
            }
            $this->session->set_flashdata('message', lang('quote_deleted'));
            redirect('welcome');
        }
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);

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
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->quotes_model->getProductNames($sr, $warehouse_id);
        if ($rows) {
            foreach ($rows as $row) {
                $option = false;
                $row->quantity = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty = 1;
                $row->discount = '0';
                $options = $this->quotes_model->getProductOptions($row->id, $warehouse_id);
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
                $pis = $this->quotes_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->quotes_model->getPurchasedItems($row->id, $warehouse_id, $row->option);
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
                        $combo_items = $this->quotes_model->getProductComboItems($row->id, $warehouse_id);
                    }
                    $pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'options' => $options);
                } else {
                    $pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => false, 'options' => $options);
                }
            }
			//$this->erp->print_arrays($pr);
            echo json_encode($pr);
        } else {
            echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function quote_actions()
    {
        
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    foreach ($_POST['val'] as $id) {
                        $this->quotes_model->deleteQuote($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("quotes_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('quotes'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $qu = $this->quotes_model->getQuoteByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($qu->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $qu->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $qu->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $qu->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $qu->total);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $qu->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'quotations_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	function add_deposit()
    {
        $this->erp->checkPermissions('deposits', true);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
		$this->form_validation->set_rules('date', lang("date"), 'required');
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        
        if ($this->form_validation->run() == true) {
			$company_id = $this->input->post('customer');
			$company = $this->site->getCompanyByID($company_id);

            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = array(
                'date' => $date,
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note') ? $this->input->post('note') : $company->name,
                'company_id' => $company->id,
                'created_by' => $this->session->userdata('user_id'),
				'biller_id' => $this->input->post('biller')
            );
			$payment = array(
				'date' => $date,
				'reference_no' => $this->site->getReference('sp'),
				'amount' => $this->input->post('amount'),
				'paid_by' => $this->input->post('paid_by'),
				'cheque_no' => $this->input->post('cheque_no'),
				'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
				'cc_holder' => $this->input->post('pcc_holder'),
				'cc_month' => $this->input->post('pcc_month'),
				'cc_year' => $this->input->post('pcc_year'),
				'cc_type' => $this->input->post('pcc_type'),
				'note' => $this->input->post('note') ? $this->input->post('note') : $company->name,
				'created_by' => $company->id,
				'type' => 'received',
				'biller_id'	=> $this->input->post('biller')
			);
            $cdata = array(
                'deposit_amount' => ($company->deposit_amount+$this->input->post('amount'))
            );
        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->quotes_model->addDeposit($data, $cdata, $payment)) {
            $this->session->set_flashdata('message', lang("deposit_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['customers'] = $this->site->getCustomers();
            $this->load->view($this->theme . 'quotes/add_deposit', $this->data);
        }
    }

	function services($id = NULL)
    {
		$this->data['services'] = $this->quotes_model->getAllService($id);
        $this->load->view($this->theme.'quotes/services', $this->data);
    }
	
	function getServicesInfo(){
		$id = $this->input->get('id', TRUE);
		$result = $this->quotes_model->getServiceByAjax($id);
		//$this->erp->print_arrays($result);
		if ($result) {
			$pr[] = array('id' => $result->id, 'name' => $result->name);
			echo json_encode($pr);
        } else {
            echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
	}
}
