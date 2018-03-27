<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Suppliers extends MY_Controller
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
        $this->lang->load('suppliers', $this->Settings->language);
        $this->load->library('form_validation');
        $this->load->model('companies_model');
    }

    function index($action = NULL)
    {
        $this->erp->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('suppliers')));
        $meta = array('page_title' => lang('suppliers'), 'bc' => $bc);
        $this->page_construct('suppliers/index', $meta, $this->data);
    }

    function getSuppliers()
    {
        $this->erp->checkPermissions('index');

        $this->load->library('datatables');
        $this->datatables
            ->select("id,id as sup_no, company, name, email,city, country, vat_no")
            ->from("companies")
            ->where('group_name', 'supplier')
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_supplier") . "' href='" . site_url('suppliers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a class=\"tip\" title='" . $this->lang->line("list_users") . "' href='" . site_url('suppliers/users/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-users\"></i></a> <a class=\"tip\" title='" . $this->lang->line("add_user") . "' href='" . site_url('suppliers/add_user/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-plus-circle\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_supplier") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('suppliers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');
        echo $this->datatables->generate();
    }

    function view($id = NULL)
    {
        $this->erp->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['supplier'] = $this->companies_model->getCompanyByID($id);
        $this->load->view($this->theme.'suppliers/view',$this->data);
    }

    function add()
    {
        $this->erp->checkPermissions(false, true);

        $this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[companies.email]');

        if ($this->form_validation->run('companies/add') == true) {

            $data = array(
				'name' 			=> $this->input->post('name'),
                'email' 		=> $this->input->post('email'),
                'group_id' 		=> '4',
                'group_name' 	=> 'supplier',
                'company' 		=> $this->input->post('company'),
                'address' 		=> $this->input->post('address'),
                'vat_no' 		=> $this->input->post('vat_no'),
                'city' 			=> $this->input->post('city'),
                'state' 		=> $this->input->post('state'),
                'postal_code' 	=> $this->input->post('postal_code'),
                'country' 		=> $this->input->post('country'),
                'phone' 		=> $this->input->post('phone'),
                'cf1' 			=> $this->input->post('cf1'),
                'cf2' 			=> $this->input->post('cf2'),
                'cf3' 			=> $this->input->post('cf3'),
                'cf4' 			=> $this->input->post('cf4'),
                'cf5' 			=> $this->input->post('cf5'),
                'cf6' 			=> $this->input->post('cf6'),
            );
        } elseif ($this->input->post('add_supplier')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $sid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', $this->lang->line("supplier_added"));
            $ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : NULL;
            redirect($ref[0] . '?supplier=' . $sid);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/add', $this->data);
        }
    }

    function edit($id = NULL)
    {
        $this->erp->checkPermissions('edit', true, 'suppliers');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        $this->form_validation->set_rules('code', lang("email_address"), 'is_unique[companies.email]');

        if ($this->form_validation->run('companies/add') == true) {
            $data = array(
				'name' 			=> $this->input->post('name'),
                'email' 		=> $this->input->post('email'),
                'group_id' 		=> '4',
                'group_name' 	=> 'supplier',
                'company' 		=> $this->input->post('company'),
                'address' 		=> $this->input->post('address'),
                'vat_no' 		=> $this->input->post('vat_no'),
                'city' 			=> $this->input->post('city'),
                'state' 		=> $this->input->post('state'),
                'postal_code' 	=> $this->input->post('postal_code'),
                'country' 		=> $this->input->post('country'),
                'phone' 		=> $this->input->post('phone'),
                'cf1' 			=> $this->input->post('cf1'),
                'cf2' 			=> $this->input->post('cf2'),
                'cf3' 			=> $this->input->post('cf3'),
                'cf4' 			=> $this->input->post('cf4'),
                'cf5' 			=> $this->input->post('cf5'),
                'cf6' 			=> $this->input->post('cf6'),
            );
        } elseif ($this->input->post('edit_supplier')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line("supplier_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['supplier'] = $company_details;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/edit', $this->data);
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
        $this->load->view($this->theme . 'suppliers/users', $this->data);

    }

    function add_user($company_id = NULL)
    {
        $this->erp->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('confirm_password'), 'required');

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
            redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', $this->lang->line("user_added"));
            redirect("suppliers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'suppliers/add_user', $this->data);
        }
    }

    function import_csv()
    {
        $this->erp->checkPermissions('import', null, 'suppliers');
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', lang("upload_file"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
			
            if (DEMO) {
                $this->session->set_flashdata('warning', $this->lang->line("disabled_in_demo"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
			
            if (isset($_FILES["excel_file"])){
                $this->load->library('excel');
				$configUpload['upload_path'] = './assets/uploads/excel/';
				$configUpload['allowed_types'] = 'xls|xlsx|csv';
				$configUpload['max_size'] 	   = '5000';
				$this->load->library('upload', $configUpload);
				$this->upload->do_upload('excel_file');	
				$upload_data = $this->upload->data();
				$file_name   = $upload_data['file_name']; 
				$extension   = $upload_data['file_ext']; 

				$objReader   = PHPExcel_IOFactory::createReader('Excel2007');
				$objReader->setReadDataOnly(true); 	

				$objPHPExcel = $objReader->load('./assets/uploads/excel/'.$file_name);	
				
				$totalrows   = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();     	 
				$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
				
				for($i=2;$i<=$totalrows;$i++)
				{
					$cs_name  = $objWorksheet->getCellByColumnAndRow(1, $i)->getValue();
					$cs_email = $objWorksheet->getCellByColumnAndRow(2, $i)->getValue();
					
					if ($cs_name == "") {
						$this->session->set_flashdata('error', lang("supplier_name_required"));
						redirect("suppliers");
					}
					
					if ($this->companies_model->getCompanyByEmail($cs_email)) {
                        $this->session->set_flashdata('error', lang("check_supplier_email") . " (" . $cs_email . "). " . lang("supplier_already_exist"));
                        redirect("suppliers");
                    }
				}

                $final = array();
				for($i=2;$i<=$totalrows;$i++)
				{
					
					$final[] = array(
						'company'				=> $objWorksheet->getCellByColumnAndRow(0,$i)->getValue(),
						'name'					=> $objWorksheet->getCellByColumnAndRow(1,$i)->getValue(),
						'email'					=> $objWorksheet->getCellByColumnAndRow(2,$i)->getValue(),
						'phone'					=> $objWorksheet->getCellByColumnAndRow(3,$i)->getValue(),
						'address'				=> $objWorksheet->getCellByColumnAndRow(4,$i)->getValue(),						
						'city'					=> $objWorksheet->getCellByColumnAndRow(5,$i)->getValue(),						
						'state'					=> $objWorksheet->getCellByColumnAndRow(6,$i)->getValue(),						
						'postal_code'			=> $objWorksheet->getCellByColumnAndRow(7,$i)->getValue(),						
						'country'				=> $objWorksheet->getCellByColumnAndRow(8,$i)->getValue(),
						'vat_no'				=> $objWorksheet->getCellByColumnAndRow(9,$i)->getValue(),
						'group_id'				=> 4,
						'group_name'			=> 'supplier',
						'created_at'			=> date('Y-m-d H:i'),
						'created_by'			=> $this->session->userdata('user_id')
					);
				}				
            }

        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('suppliers');
        }

        if ($this->form_validation->run() == true && !empty($final)) {
            if ($this->companies_model->addCompanies($final)) {
                $this->session->set_flashdata('message', $this->lang->line("suppliers_added"));
                redirect('suppliers');
            }
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/import', $this->data);
        }
    }

    function delete($id = NULL)
    {
        $this->erp->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->companies_model->deleteSupplier($id)) {
            echo $this->lang->line("supplier_deleted");
        } else {
            $this->session->set_flashdata('warning', lang('supplier_x_deleted_have_purchases'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }

    function suggestions($term = NULL, $limit = NULL)
    {
        // $this->erp->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->companies_model->getSupplierSuggestions($term, $limit);
        echo json_encode($rows);
    }

    function getSupplier($id = NULL)
    {
        // $this->erp->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        echo json_encode(array(array('id' => $row->id, 'text' => $row->company)));
    }

    function supplier_actions()
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
                        if (!$this->companies_model->deleteSupplier($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("suppliers_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('city'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('country'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('vat_no'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->id);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->company);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $customer->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->phone);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $customer->city);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $customer->country);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $customer->vat_no);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'suppliers_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

}
