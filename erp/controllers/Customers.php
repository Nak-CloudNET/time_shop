<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller
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
        $this->lang->load('customers', $this->Settings->language);
        $this->load->library('form_validation');
        $this->load->model('companies_model');
		$this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '4096';
    }

    function index($action = NULL)
    {
        $this->erp->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('customers')));
        $meta = array('page_title' => lang('customers'), 'bc' => $bc);
        $this->page_construct('customers/index', $meta, $this->data);
    }

    function getCustomers()
    {
        $this->erp->checkPermissions('index');
        $this->load->library('datatables');
        $this->datatables
            ->select("companies.id, companies.id AS cus_no,  companies.customer_group_name, companies.name, companies.gender, companies.phone, COALESCE((select sum(grand_total) from erp_sales where erp_sales.customer_id = erp_companies.id),0), companies.deposit_amount, companies.attachment")
            ->from("companies")
            ->where('group_name', 'customer')
            ->add_column("Actions", "<div class=\"text-center\"> <a class=\"tip\" title='" . lang("list_deposits") . "' href='" . site_url('customers/deposits/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-money\"></i></a> <a class=\"tip\" title='" . lang("add_deposit") . "' href='" . site_url('customers/add_deposit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-plus\"></i></a> <a class=\"tip\" title='" . lang("edit_customer") . "' href='" . site_url('customers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_customer") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "companies.id");
        //->unset_column('id');
        echo $this->datatables->generate();
    }

    function view($id = NULL)
    {
        $this->erp->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer'] = $this->companies_model->getCompanyByIDs($id);
        $this->load->view($this->theme.'customers/view',$this->data);
    }
	
	function add()
    {
        $this->erp->checkPermissions(false, true);

        $this->form_validation->set_rules('name', lang("name"), 'trim');
		$this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');

        if ($this->form_validation->run('companies/add') == true) {
            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
			$dob = $this->input->post('date_of_birth').'/'.date('Y');
            $name = $this->input->post('name');
            if($this->companies_model->getCompanyByName($name)){
                $this->session->set_flashdata('error', lang("check_customer_name") . " (" . $name . "). " );
                    redirect("customers");
            }
            $data = array(
				'name' 					=> $this->input->post('name'),
                'email' 				=> $this->input->post('email'),
                'group_id' 				=> '3',
                'group_name' 			=> 'customer',
                'customer_group_id' 	=> $this->input->post('customer_group'),
                'customer_group_name' 	=> $cg->name,
                'company' 				=> $this->input->post('company'),
                'address' 				=> $this->input->post('address'),
                'vat_no' 				=> $this->input->post('vat_no'),
                'city' 					=> $this->input->post('city'),
                'state' 				=> $this->input->post('state'),
                'postal_code' 			=> $this->input->post('postal_code'),
                'country' 				=> $this->input->post('country'),
                'phone' 				=> $this->input->post('phone'),
                'cf1' 					=> $this->input->post('cf1'),
                'gender' 				=> $this->input->post('gender'),
				'status' 				=> $this->input->post('status'),
                'date_of_birth' 		=> $this->erp->fld($dob),
                'status' 				=> $this->input->post('status'),
                'end_date' 				=> $this->erp->fld(trim($this->input->post('end_date'))),
				'start_date' 			=> $this->erp->fld(trim($this->input->post('start_date'))),
                'award_points' 			=> $this->input->post('award_points'),
                'created_at' 			=> $this->erp->fld(trim($this->input->post('creation_date'))),
				'created_by' 			=> $this->input->post('created_by'),
				'comment_note' 			=> $this->input->post('comment_note')
            );
			//$this->erp->print_arrays($data);
            
			$this->load->library('upload');
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
                        redirect($_SERVER["HTTP_REFERER"]);
                    } else {
                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = TRUE;
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
				$image = implode(',', $photos);
				$data['attachment'] = $image;
                $config = NULL;
            } else {
                $photos = NULL;
            }
			//$this->erp->print_arrays($data);
		
        } elseif ($this->input->post('add_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $cid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', lang("customer_added"));
            $ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : NULL;
            redirect($ref[0] . '?customer=' . $cid);
        } else {
			$this->data['agencies'] = $this->site->getAllUsers();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->load->view($this->theme . 'customers/add', $this->data);
        }
    }
	
    function edit($id = NULL)
    {
        $this->erp->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$this->form_validation->set_rules('company', lang("company"), 'trim');
        $company_details = $this->companies_model->getCompanyByIDs($id);
        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('email', lang("email_address"), 'is_unique[companies.email]');
        }

        if ($this->form_validation->run('companies/edit') == true) {
            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
			$dob = $this->input->post('date_of_birth').'/'.date('Y');
            $data = array(
				'name' 					=> $this->input->post('name'),
                'email' 				=> $this->input->post('email'),
                'group_id' 				=> '3',
                'group_name' 			=> 'customer',
                'customer_group_id' 	=> $this->input->post('customer_group'),
                'customer_group_name' 	=> $cg->name,
                'company' 				=> $this->input->post('company'),
                'address' 				=> $this->input->post('address'),	
                'vat_no' 				=> $this->input->post('vat_no'),
                'city' 					=> $this->input->post('city'),
                'state' 				=> $this->input->post('state'),
                'postal_code' 			=> $this->input->post('postal_code'),
                'country' 				=> $this->input->post('country'),
                'phone' 				=> $this->input->post('phone'),
                'cf1' 					=> $this->input->post('cf1'),
                'gender' 				=> $this->input->post('gender'),
				'status' 				=> $this->input->post('status'),
                'date_of_birth' 		=> $this->erp->fld($dob),
                'status' 				=> $this->input->post('status'),
                'award_points' 			=> $this->input->post('award_points'),
				'end_date' 				=> $this->erp->fld(trim($this->input->post('end_date'))),
				'start_date' 			=> $this->erp->fld(trim($this->input->post('start_date'))),
				'created_at' 			=> $this->erp->fld(trim($this->input->post('creation_date'))),
				'created_by' 			=> $this->input->post('created_by'),
				'comment_note' 			=> $this->input->post('comment_note')
            );
            // attachment
			$this->load->library('upload');
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
                        redirect($_SERVER["HTTP_REFERER"]);
                    } else {
                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = TRUE;
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
				$image = implode(',', $photos);
				$data['attachment'] = $image;
                $config = NULL;
            } else {
                $photos = NULL;
            }

		//$this->erp->print_arrays($data);
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', lang("customer_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$this->data['agencies'] = $this->site->getAllUsers();
            $this->data['customer'] = $company_details;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->load->view($this->theme . 'customers/edit', $this->data);
        }
    }

    function users($company_id = NULL)
    {
        $this->erp->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }


        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->data['users'] = $this->companies_model->getCompanyUsers($company_id);
        $this->load->view($this->theme . 'customers/users', $this->data);

    }

    function add_user($company_id = NULL)
    {
        $this->erp->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', lang("email_address"), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');

        if ($this->form_validation->run('companies/add_user') == true) {
            $active = $this->input->post('status');
            $notify = $this->input->post('notify');
            list($username, $domain) = explode("@", $this->input->post('email'));
            $email = strtolower($this->input->post('email'));
            $password = $this->input->post('password');
            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'phone' => $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
                'company_id' => $company->id,
                'company' => $company->company,
                'group_id' => 3
            );
            $this->load->library('ion_auth');
        } elseif ($this->input->post('add_user')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', lang("user_added"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'customers/add_user', $this->data);
        }
    }

    function import_csv()
    {
        $this->erp->checkPermissions('import', TRUE, 'customers');
		
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (DEMO) {
                $this->session->set_flashdata('warning', lang("disabled_in_demo"));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if (isset($_FILES["excel_file"])){
                $this->load->library('excel');
				$configUpload['upload_path'] 	= './assets/uploads/excel/';
				$configUpload['allowed_types'] 	= 'xls|xlsx|csv';
				$configUpload['max_size'] 	   	= '5000';
				$this->load->library('upload', $configUpload);
				$this->upload->do_upload('excel_file');	
				$upload_data = $this->upload->data();
				$file_name   = $upload_data['file_name']; 
				$extension   = $upload_data['file_ext']; 

				$objReader   = PHPExcel_IOFactory::createReader('Excel2007');
				$objReader->setReadDataOnly(true); 	

				$objPHPExcel = $objReader->load('./assets/uploads/excel/'.$file_name);	
				
				$totalrows=$objPHPExcel->setActiveSheetIndex(0)->getHighestRow();     	 
				$objWorksheet=$objPHPExcel->setActiveSheetIndex(0);
				
				for($i=2;$i<=$totalrows;$i++)
				{
                    $cs_group = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
					$cs_name  = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
					$cs_email = $objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
					
					if ($cs_name == "") {
						$this->session->set_flashdata('error', lang("customer_name_required"));
						redirect("customers");
					}
					if($this->companies_model->getCompanyByName($cs_name)){
						$this->session->set_flashdata('error', lang("check_customer_name") . " (" . $cs_name . "). " );
							redirect("customers");
					}
					if($cs_email){
						if ($this->companies_model->getCompanyByEmail($cs_email)) {
							$this->session->set_flashdata('error', lang("check_customer_email") . " (" . $cs_email . "). ");
							redirect("customers");
						}
					}
                    if($cs_group == ""){
                        $this->session->set_flashdata('error', lang("customer_group_required"));
                        redirect("customers");
                    }
                    if($cs_group){
                        if(!$this->companies_model->getCustomerGroups($cs_group)){
                            $this->session->set_flashdata('error', lang("check_customer_group") . " (" . $cs_group . "). ");
                            redirect("customers");
                        }
                    }
                    if($cs_name != ""){
                        $array[] = trim($cs_name);
                    }
				}

                $count_values = array();
                foreach ($array as $a) {

                     @$count_values[$a]++;

                }
                foreach($count_values as $key => $value){
                    if($value > 1){
                        $this->session->set_flashdata('error', lang("customer_name")." (".$key.") ".lang(" is_duplicate"));
                        redirect("customers");
                    }
                }
				
				$final = array();
				for($i=2;$i<=$totalrows;$i++)
				{
                    $cs_group = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
                    $customer = $this->companies_model->getCustomerGroups($cs_group);
					$dob = $objWorksheet->getCellByColumnAndRow(6,$i)->getValue();
					$final[] = array(
						'company'				=> $objWorksheet->getCellByColumnAndRow(1,$i)->getValue(),
						'name'					=> $objWorksheet->getCellByColumnAndRow(2,$i)->getValue(),
						'country'				=> $objWorksheet->getCellByColumnAndRow(3,$i)->getValue(),
						'email'					=> $objWorksheet->getCellByColumnAndRow(4,$i)->getValue(),
						'address'				=> $objWorksheet->getCellByColumnAndRow(5,$i)->getValue(),
						'date_of_birth'			=> date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($dob)),
						'phone'					=> $objWorksheet->getCellByColumnAndRow(7,$i)->getValue(),
						'gender'				=> $objWorksheet->getCellByColumnAndRow(8,$i)->getValue(),
						'group_id'				=> 3,
						'group_name'			=> 'customer',
						'customer_group_id' 	=> $cs_group,
						'customer_group_name' 	=> $customer->name,
						'created_at'			=> date('Y-m-d H:i'),
						'created_by'			=> $this->session->userdata('user_id')
					);
				}
            }

        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }
		
        if ($this->form_validation->run() == true) {
			//$this->erp->print_arrays($final);
            if ($this->companies_model->addCompanies($final)) {
                $this->session->set_flashdata('message', lang("customers_added"));
                redirect('customers');
            }
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/import', $this->data);
        }
    }

    function delete($id = NULL)
    {
        $this->erp->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->input->get('id') == 1) {
            $this->session->set_flashdata('error', lang('customer_x_deleted'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }

        if ($this->companies_model->deleteCustomer($id)) {
            echo lang("customer_deleted");
        } else {
            $this->session->set_flashdata('warning', lang('customer_x_deleted_have_sales'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    function suggestions($term = NULL, $limit = NULL)
    {
        // $this->erp->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        if (strlen($term) < 1) {
            return FALSE;
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->companies_model->getCustomerSuggestions($term, $limit);
        echo json_encode($rows);
    }
	
	function balance_suggest($term = NULL, $limit = NULL)
    {
        // $this->erp->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        if (strlen($term) < 1) {
            return FALSE;
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['result'] = $this->companies_model->getBalanceSuggestions($term, $limit);
        echo json_encode($rows);
    }

    function getCustomer($id = NULL)
    {
        $row = $this->companies_model->getCompanyByID($id);
        echo json_encode(array(array('id' => $row->id, 'text' => ($row->name != '-' ? $row->name .'('. $row->company .')' : $row->company))));
    }

    function get_award_points($id = NULL)
    {
        $this->erp->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        echo json_encode(array('ca_points' => $row->award_points));
    }

    function customer_actions()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->companies_model->deleteCustomer($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('customers_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', lang("customers_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('No'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('customer_group'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('gender'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount_spent'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('store_credit')); 

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);  
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->id);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->customer_group_name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $customer->gender);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->phone);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->erp->formatMoney($customer->grand_total));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->erp->formatMoney($customer->deposit_amount)); 
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'customers_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("no_customer_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function deposits($company_id = NULL)
    {
        $this->erp->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }

        $this->data['error'] 	= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] 	= $this->companies_model->getCompanyByID($company_id);
        $this->load->view($this->theme . 'customers/deposits', $this->data);

    }

    function get_deposits($id)
    {
		
        $this->erp->checkPermissions('deposits');
        $this->load->library('datatables');
        $this->datatables
            ->select("deposits.id as id, date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", false)
            ->from("deposits")
            ->join('users', 'users.id=deposits.created_by', 'left')
			->where('deposits.company_id', $id)
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("deposit_note") . "' href='" . site_url('customers/deposit_note/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-file-text-o\"></i></a> <a class=\"tip\" title='" . lang("edit_deposit") . "' href='" . site_url('customers/edit_deposit/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_deposit") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete_deposit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
        ->unset_column('id');
        echo $this->datatables->generate();
    }

    function add_deposit($company_id = NULL)
    {
        $this->erp->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
		
        $company = $this->companies_model->getCompanyByID($company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
		
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        
        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			
            $data = array(
                'date' 		 => $date,
                'amount' 	 => $this->input->post('amount'),
                'paid_by' 	 => $this->input->post('paid_by'),
                'note' 		 => $this->input->post('note') ? $this->input->post('note') : $company->name,
                'company_id' => $company->id,
                'created_by' => $this->session->userdata('user_id'),
				'biller_id'  => $this->input->post('biller')
            );
			
			$payment = array(
				'date' 			=> $date,
				'sale_id' 		=> $sale_id,
				'reference_no' 	=> $this->site->getReference('sp'),
				'amount' 		=> $this->input->post('amount'),
				'paid_by' 		=> $this->input->post('paid_by'),
				'cheque_no' 	=> $this->input->post('cheque_no'),
				'cc_no' 		=> $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
				'cc_holder' 	=> $this->input->post('pcc_holder'),
				'cc_month' 		=> $this->input->post('pcc_month'),
				'cc_year' 		=> $this->input->post('pcc_year'),
				'cc_type' 		=> $this->input->post('pcc_type'),
				'note' 			=> $this->input->post('note') ? $this->input->post('note') : $company->name,
				'created_by' 	=> $company->id,
				'type' 			=> 'received',
				'biller_id'		=> $this->input->post('biller')
			);
			
        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addDeposit($data, $payment)) {
            $this->session->set_flashdata('message', lang("deposit_added"));
            redirect("customers");
        } else {
            $this->data['error'] 	= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] 	= $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] 	= $company;
            $this->load->view($this->theme . 'customers/add_deposit', $this->data);
        }
    }

    function edit_deposit($id = NULL)
    {
        $this->erp->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit  = $this->companies_model->getDepositByID($id);
		$payments = $this->companies_model->getPaymentByDepositsID($id);
        $company  = $this->companies_model->getCompanyByID($deposit->company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        
        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = $deposit->date;
            }
            $data = array(
                'date' 			=> $date,
                'amount' 		=> $this->input->post('amount'),
                'paid_by' 		=> $this->input->post('paid_by'),
                'note' 			=> $this->input->post('note'),
                'company_id' 	=> $deposit->company_id,
                'updated_by' 	=> $this->session->userdata('user_id'),
                'updated_at' 	=> $date = date('Y-m-d H:i:s'),
				'biller_id' 	=> $this->input->post('biller')
            );
			
			$payment = array(
				'date' 			=> $date,
				'deposit_id' 	=> $id,
				'reference_no' 	=> $this->site->getReference('sp'),
				'amount' 		=> $this->input->post('amount'),
				'paid_by' 		=> $this->input->post('paid_by'),
				'cheque_no' 	=> $this->input->post('cheque_no'),
				'cc_no' 		=> $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
				'cc_holder' 	=> $this->input->post('pcc_holder'),
				'cc_month' 		=> $this->input->post('pcc_month'),
				'cc_year' 		=> $this->input->post('pcc_year'),
				'cc_type' 		=> $this->input->post('pcc_type'),
				'note' 			=> $this->input->post('note') ? $this->input->post('note') : $company->name,
				'type' 			=> 'received',
				'biller_id'		=> $this->input->post('biller')
			);

        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateDeposit($id, $data, $payment)) {
            $this->session->set_flashdata('message', lang("deposit_updated"));
            redirect("customers");
        } else {
            $this->data['error'] 	= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] 	= $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] 	= $company;
			$this->data['payment']	= $payments;
            $this->data['deposit'] 	= $deposit;
            $this->load->view($this->theme . 'customers/edit_deposit', $this->data);
        }
    }

    public function delete_deposit($id)
    {
        $this->erp->checkPermissions(NULL, TRUE);

        if ($this->companies_model->deleteDeposit($id)) {
            echo lang("deposit_deleted");
        }
    }

    public function deposit_note($id = null)
    {
        $this->erp->checkPermissions('deposits', true);
        $deposit = $this->companies_model->getDepositByID($id);
        $this->data['customer'] = $this->companies_model->getCompanyByID($deposit->company_id);
        $this->data['deposit'] = $deposit;
        $this->data['page_title'] = $this->lang->line("deposit_note");
        $this->load->view($this->theme . 'customers/deposit_note', $this->data);
    }
	
	public function return_deposit($id){
		$this->erp->checkPermissions('deposits', true);
		if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->companies_model->getDepositByID($id);
        $company = $this->companies_model->getCompanyByID($deposit->company_id);
		if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
		if($this->form_validation->run() == true){
			if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld(trim($this->input->post('date')));
            } else {
                $date = $deposit->date;
            }
            $data = array(
                'amount' => ($deposit->amount - $this->input->post('amount')),
                'note' => $this->input->post('note'),
                'company_id' => $deposit->company_id,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => $date = date('Y-m-d H:i:s'),
				'biller_id' => $this->input->post('biller')
            );
			
			$payment = array(
				'date' => $date,
				'deposit_id' => $id,
				'reference_no' => $this->site->getReference('pp'),
				'amount' => $this->input->post('amount'),
				'paid_by' => 'cash',
				'note' => $this->input->post('note') ? $this->input->post('note') : $company->name,
				'type' => 'received',
				'biller_id'	=> $this->input->post('biller')
			);

            $cdata = array(
                'deposit_amount' => (($deposit->amount - $this->input->post('amount')))
            );
		} elseif ($this->input->post('return_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('account/deposits');
        }
		if ($this->form_validation->run() == true && $this->companies_model->ReturnDeposit($id, $data, $cdata, $payment)) {
            $this->session->set_flashdata('message', lang("deposit_returned"));
            redirect("account/deposits");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->data['deposit'] = $deposit;
            $this->load->view($this->theme . 'customers/return_deposit', $this->data);
        }
	}
	
	function customer_view($company_id=null){
		$this->erp->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }


        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
		$this->load->library('datatables');
		$this->data['customer_info']=$this->db
			->select('id,group_name,name,company,address,city,state,country,phone,email,gender,DATE_FORMAT(date_of_birth,"%d/%b/%Y") AS dob,status')
			->from('companies')
			->where('group_name','customer')
			->Where('id',$company_id)
			->get();
        //$this->data['users'] = $this->companies_model->getCompanyUsers($company_id);
		$this->load->view($this->theme.'customers/customer_views',$this->data);
	}

}
