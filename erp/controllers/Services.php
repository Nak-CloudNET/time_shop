<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Services extends MY_Controller
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
        $this->lang->load('document', $this->Settings->language);
        $this->load->library('form_validation');
		$this->load->model('service_model');
		$this->load->model('site');
		
		$this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'erp_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
		
		 
		if(!$this->Owner && !$this->Admin) {
            $gp = $this->site->checkPermissions();
            $this->permission = $gp[0];
            $this->permission[] = $gp[0];
        } else {
            $this->permission[] = NULL;
        }
    }

    function index($action = NULL)
    {
        //$this->erp->checkPermissions('index', true, 'services');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('list_services')));
        $meta = array('page_title' => lang('list_services'), 'bc' => $bc);
        $this->page_construct('services/index', $meta, $this->data);
    }
	
	
	function getServices()
	{
		//$this->erp->checkPermissions('index', true, 'services');

		$this->load->library('datatables');
		$this->datatables
		->select("services.id as idd, date_of_reception, services.service_number, (CASE WHEN erp_services.name = 0 THEN erp_services.name ELSE erp_companies.name END) as name, (CASE WHEN	erp_services.brand IS NULL OR erp_services.brand = 0 THEN erp_services.tbrand ELSE erp_brands.name END) as brand, services.type, (CASE WHEN	erp_services.modal IS NULL OR erp_services.modal = 0  THEN erp_services.tmodal ELSE erp_products.code END) as model, services.detail2 as description")
		->from("services")
		->join('products', 'services.modal=products.id', 'left')
		->join('brands', 'services.brand=brands.id', 'left')
		->join('companies', 'companies.id = services.name', 'left')
		->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_service") . "' href='" . site_url('services/edit_service/$1') . "'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_service") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('services/delete_service/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "idd");
		echo $this->datatables->generate();
	}
	
	function add_service()
	{
		$this->erp->checkPermissions('add', true, 'services');
		$this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
		if ($this->form_validation->run('services/add_service') == true) {			
			$num = $this->service_model->getRef();			
			if($num->num == ""){				
				$n = 000000;			
			}else{				
				$n = $this->service_model->separatestring($num->num);			
			}
			$n2 = str_pad($n + 1, 6, 0, STR_PAD_LEFT);
			$brand = $this->input->post('brand');
			$modal = $this->input->post('model');
			$data = array(
				'service_number' 	=> 'TPSF'.$n2,
				'name' 				=> $this->input->post('name'),
				'ic_passport_no' 	=> $this->input->post('ic_no'),
				'date_of_reception' => $this->erp->fld($this->input->post('recept_date')),
				'address' 			=> $this->input->post('address'),
				'hpline' 			=> $this->input->post('HPline'),
				'hline' 			=> $this->input->post('Hline'),
				'oline' 			=> $this->input->post('Oline'),
				'email' 			=> $this->input->post('email'),
				'is_jewelry' 		=> $this->input->post('is_jewelry'),
				'type' 				=> $this->input->post('category'),
				'serial_no' 		=> $this->input->post('serial_no'),
				'is_warranty' 		=> $this->input->post('is_warranty'),
				//'wto_date' 		=> $this->erp->fld($this->input->post('to_date')),
				'rinning' 			=> $this->input->post('rinning'),
				'stopped' 			=> $this->input->post('stopped'),
				'manual_winding' 	=> $this->input->post('manual_winding'),
				'self_winding' 		=> $this->input->post('self_winding'),
				'quartz' 			=> $this->input->post('quartz'),
				'gents' 			=> $this->input->post('gents'),
				'ladies' 			=> $this->input->post('ladies'),
				'platinum' 			=> $this->input->post('platinum'),
				'white_gold' 		=> $this->input->post('white_gold'),
				'rose_gold' 		=> $this->input->post('rose_gold'),
				'yellow_gold' 		=> $this->input->post('yellow_gold'),
				'titanium' 			=> $this->input->post('titanium'),
				'steel' 			=> $this->input->post('steel'),
				'bi_color' 			=> $this->input->post('bi_color'),
				'leather' 			=> $this->input->post('leather'),
				'calf' 				=> $this->input->post('calf'),
				'rubber_canvas' 	=> $this->input->post('rubber_canvas'),
				'normal_scratched' 	=> $this->input->post('normal_scratched'),
				'case_scratched' 	=> $this->input->post('case_scratched'),
				'lugs_scratched' 	=> $this->input->post('lugs_scratched'),
				'bezel_scratched' 	=> $this->input->post('bezel_scratched'),
				'crown_missing' 	=> $this->input->post('crown_missing'),
				'dial_scratched' 	=> $this->input->post('dial_scratched'),
				'hand_oxidized' 	=> $this->input->post('hand_oxidized'),
				'caseback_scratched'=> $this->input->post('caseback_scratched'),
				'strap_warm' 		=> $this->input->post('strap_warm'),
				'bracelet_scratched'=> $this->input->post('bracelet_scratched'),
				'buckle_scratched' 	=> $this->input->post('buckle_scratched'),
				'heavy_scratches' 	=> $this->input->post('heavy_scratches'),
				'case_dent' 		=> $this->input->post('case_dent'),
				'lugs_dent' 		=> $this->input->post('lugs_dent'),
				'bezel_dent' 		=> $this->input->post('bezel_dent'),
				'crown_demaged' 	=> $this->input->post('crown_demaged'),
				'dial_stained' 		=> $this->input->post('dial_stained'),
				'hand_jammed' 		=> $this->input->post('hand_jammed'),
				'caseback_dent' 	=> $this->input->post('caseback_dent'),
				'strap_damagged' 	=> $this->input->post('strap_damagged'),
				'bracelet_oxidized' => $this->input->post('bracelet_oxidized'),
				'buckle_demagged' 	=> $this->input->post('buckle_demagged'),
				'severely_scratches'=> $this->input->post('severely_scratches'),
				'case_deep_dent' 	=> $this->input->post('case_deep_dent'),
				'lugs_deep_dent' 	=> $this->input->post('lugs_deep_dent'),
				'bezel_deep_dent' 	=> $this->input->post('bezel_deep_dent'),
				'crown_pusher_missing' => $this->input->post('crown_pusher_missing'),
				'dial_oxidized' 	=> $this->input->post('dial_oxidized'),
				'hands_disloged' 	=> $this->input->post('hands_disloged'),
				'caseback_chipped' 	=> $this->input->post('caseback_chipped'),
				'strap_other' 		=> $this->input->post('strap_other'),
				'bracelet_damagged' => $this->input->post('bracelet_damagged'),
				'case_impacted' 	=> $this->input->post('case_impacted'),
				'lugs_impacted' 	=> $this->input->post('lugs_impacted'),
				'bezel_craked' 		=> $this->input->post('bezel_craked'),
				'crown_pusser_damaged' => $this->input->post('crown_pusser_damaged'),
				'dial_damaged' 		=> $this->input->post('dial_damaged'),
				'caseback_craked' 	=> $this->input->post('caseback_craked'),
				'strap_no_strap' 	=> $this->input->post('strap_no_strap'),
				'case_out_of_strap' => $this->input->post('case_out_of_strap'),
				'lugs_out_of_strap' => $this->input->post('lugs_out_of_strap'),
				'detail1' 			=> $this->input->post('detail1'),
				'staff_name' 		=> $this->input->post('staff_name'),
				'staff_signature' 	=> $this->input->post('staff_signature'),
				'staff_date' 		=> $this->erp->fld($this->input->post('staff_date')),
				'client_name' 		=> $this->input->post('client_name'),
				'client_signature' 	=> $this->input->post('client_signature'),
				'client_date' 		=> $this->erp->fld($this->input->post('client_date')),
				'staff_name1' 		=> $this->input->post('staff_name1'),
				'staff_signature1' 	=> $this->input->post('staff_signature1'),
				'staff_date1' 		=> $this->erp->fld($this->input->post('staff_date1')),
				'client_name1' 		=> $this->input->post('client_name1'),
				'client_signature1' => $this->input->post('client_signature1'),
				'client_date1' 		=> $this->erp->fld($this->input->post('client_date1')),
				'detail2' 			=> $this->input->post('description'),
				'type_customer' 	=> $this->input->post('type_cust')
			);
			if($brand != 0){
				$data['brand'] = $this->input->post('brand');
				
			}else{
				$data['tbrand'] = $this->input->post('brand');
			}
			if($modal != 0){
				$data['modal'] = $modal;
			}else{
				$data['tmodal'] = $modal;
			}
				
			//$this->erp->print_arrays($data);
		}
		
		if($this->form_validation->run() && $this->service_model->addServices($data)){
			$this->session->set_flashdata('message', $this->lang->line("service_added"));
			redirect('services');
		}else{
			$this->data['brands'] = $this->site->getAllBrands();
			$this->data['products'] = $this->site->getAllStandardProducts();
			$this->data['customer'] = $this->service_model->getAllCustomers();
			$this->data['type'] = $this->site->getAllCategories();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('add_service')));
			$meta = array('page_title' => lang('add_service'), 'bc' => $bc);
			$this->page_construct('services/add_service', $meta, $this->data);
		}
	}

	function edit_service($id)
	{
		$this->erp->checkPermissions('edit_service', true, 'services');
		$this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
		if ($this->form_validation->run('services/add_service') == true) {	
			$senum = $this->service_model->service_num($id);
			$brand = $this->input->post('brand');
			$modal = $this->input->post('model');
			$data = array(				
				'service_number' 	=> $senum->service_number,
				'name' 				=> $this->input->post('name'),
				'ic_passport_no' 	=> $this->input->post('ic_no'),
				'date_of_reception' => $this->erp->fld($this->input->post('recept_date')),
				'address' 			=> $this->input->post('address'),
				'hpline' 			=> $this->input->post('HPline'),
				'hline' 			=> $this->input->post('Hline'),
				'oline' 			=> $this->input->post('Oline'),
				'email' 			=> $this->input->post('email'),
				'is_jewelry' 		=> $this->input->post('is_jewelry'),
				'type' 				=> $this->input->post('category'),
				'serial_no' 		=> $this->input->post('serial_no'),
				'is_warranty' 		=> $this->input->post('is_warranty'),
				//'wto_date' 		=> $this->erp->fld($this->input->post('to_date')),
				'rinning' 			=> $this->input->post('rinning'),
				'stopped' 			=> $this->input->post('stopped'),
				'manual_winding' 	=> $this->input->post('manual_winding'),
				'self_winding' 		=> $this->input->post('self_winding'),
				'quartz' 			=> $this->input->post('quartz'),
				'gents' 			=> $this->input->post('gents'),
				'ladies' 			=> $this->input->post('ladies'),
				'platinum' 			=> $this->input->post('platinum'),
				'white_gold' 		=> $this->input->post('white_gold'),
				'rose_gold' 		=> $this->input->post('rose_gold'),
				'yellow_gold' 		=> $this->input->post('yellow_gold'),
				'titanium' 			=> $this->input->post('titanium'),
				'steel' 			=> $this->input->post('steel'),
				'bi_color' 			=> $this->input->post('bi_color'),
				'leather' 			=> $this->input->post('leather'),
				'calf' 				=> $this->input->post('calf'),
				'rubber_canvas' 	=> $this->input->post('rubber_canvas'),
				'normal_scratched' 	=> $this->input->post('normal_scratched'),
				'case_scratched' 	=> $this->input->post('case_scratched'),
				'lugs_scratched' 	=> $this->input->post('lugs_scratched'),
				'bezel_scratched' 	=> $this->input->post('bezel_scratched'),
				'crown_missing' 	=> $this->input->post('crown_missing'),
				'dial_scratched' 	=> $this->input->post('dial_scratched'),
				'hand_oxidized' 	=> $this->input->post('hand_oxidized'),
				'caseback_scratched'=> $this->input->post('caseback_scratched'),
				'strap_warm' 		=> $this->input->post('strap_warm'),
				'bracelet_scratched'=> $this->input->post('bracelet_scratched'),
				'buckle_scratched' 	=> $this->input->post('buckle_scratched'),
				'heavy_scratches' 	=> $this->input->post('heavy_scratches'),
				'case_dent' 		=> $this->input->post('case_dent'),
				'lugs_dent' 		=> $this->input->post('lugs_dent'),
				'bezel_dent' 		=> $this->input->post('bezel_dent'),
				'crown_demaged' 	=> $this->input->post('crown_demaged'),
				'dial_stained' 		=> $this->input->post('dial_stained'),
				'hand_jammed' 		=> $this->input->post('hand_jammed'),
				'caseback_dent' 	=> $this->input->post('caseback_dent'),
				'strap_damagged' 	=> $this->input->post('strap_damagged'),
				'bracelet_oxidized' => $this->input->post('bracelet_oxidized'),
				'buckle_demagged' 	=> $this->input->post('buckle_demagged'),
				'severely_scratches'=> $this->input->post('severely_scratches'),
				'case_deep_dent' 	=> $this->input->post('case_deep_dent'),
				'lugs_deep_dent' 	=> $this->input->post('lugs_deep_dent'),
				'bezel_deep_dent' 	=> $this->input->post('bezel_deep_dent'),
				'crown_pusher_missing' => $this->input->post('crown_pusher_missing'),
				'dial_oxidized' 	=> $this->input->post('dial_oxidized'),
				'hands_disloged' 	=> $this->input->post('hands_disloged'),
				'caseback_chipped' 	=> $this->input->post('caseback_chipped'),
				'strap_other' 		=> $this->input->post('strap_other'),
				'bracelet_damagged' => $this->input->post('bracelet_damagged'),
				'case_impacted' 	=> $this->input->post('case_impacted'),
				'lugs_impacted' 	=> $this->input->post('lugs_impacted'),
				'bezel_craked' 		=> $this->input->post('bezel_craked'),
				'crown_pusser_damaged' => $this->input->post('crown_pusser_damaged'),
				'dial_damaged' 		=> $this->input->post('dial_damaged'),
				'caseback_craked' 	=> $this->input->post('caseback_craked'),
				'strap_no_strap' 	=> $this->input->post('strap_no_strap'),
				'case_out_of_strap' => $this->input->post('case_out_of_strap'),
				'lugs_out_of_strap' => $this->input->post('lugs_out_of_strap'),
				'detail1' 			=> $this->input->post('detail1'),
				'staff_name' 		=> $this->input->post('staff_name'),
				'staff_signature' 	=> $this->input->post('staff_signature'),
				'staff_date' 		=> $this->erp->fld($this->input->post('staff_date')),
				'client_name' 		=> $this->input->post('client_name'),
				'client_signature' 	=> $this->input->post('client_signature'),
				'client_date' 		=> $this->erp->fld($this->input->post('client_date')),
				'staff_name1' 		=> $this->input->post('staff_name1'),
				'staff_signature1' 	=> $this->input->post('staff_signature1'),
				'staff_date1' 		=> $this->erp->fld($this->input->post('staff_date1')),
				'client_name1' 		=> $this->input->post('client_name1'),
				'client_signature1' => $this->input->post('client_signature1'),
				'client_date1' 		=> $this->erp->fld($this->input->post('client_date1')),
				'detail2' 			=> $this->input->post('description'),
				'type_customer' 	=> $this->input->post('type_cust')
			);
			if($brand != 0){
				$data['brand'] = $this->input->post('brand');
				$data['tbrand'] = '';
			}else{
				$data['tbrand'] = $this->input->post('brand');
				$data['brand'] = '';
			}
			if($modal != 0){
				$data['modal'] = $modal;
				$data['tmodal'] = '';
			}else{
				$data['tmodal'] = $modal;
				$data['modal'] = '';
			}
			//$this->erp->print_arrays($data);
		}
		
		if($this->form_validation->run() && $this->service_model->updateServices($id, $data)){
			$this->session->set_flashdata('message', $this->lang->line("service_updated"));
			redirect('services');
		}else{
			$this->data['id'] 		= $id;
			$this->data['services'] = $this->service_model->getServiceById($id);
			$this->data['brands'] 	= $this->site->getAllBrands();
			$this->data['customer'] = $this->service_model->getAllCustomers();
			$this->data['products'] = $this->site->getAllProducts();
			$this->data['type'] 	= $this->site->getAllCategories();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('edit_service')));
			$meta = array('page_title' => lang('edit_service'), 'bc' => $bc);
			$this->page_construct('services/edit_service', $meta, $this->data);
		}
	}
	
	public function delete_service($id)
	{
		$this->erp->checkPermissions('delete_service', TRUE, 'services');
		
		if($this->service_model->deleteServices($id)){
			$this->session->set_flashdata('message', $this->lang->line("service_deleted"));
			redirect('services');
		}else{
			$this->session->set_flashdata('warning', lang('service_cannot_deleted'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
	}
	
	function modal_view($id = NULL)
    {
		$this->data['services'] = $this->service_model->getAllService($id);
		//$this->erp->print_arrays($this->data['services']);
        $this->load->view($this->theme.'services/modal_view', $this->data);
    }
}

?>