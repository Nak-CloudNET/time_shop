<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            redirect('login');
        }
		$this->lang->load('settings', $this->Settings->language);
        $this->load->library('form_validation');
        $this->load->model('settings_model');
		
        $this->lang->load('products', $this->Settings->language);
        $this->load->library('form_validation');
        $this->load->model('products_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '4096';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'erp_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
		
		if(!$this->Owner && !$this->Admin) {
            $gp = $this->site->checkPermissions();
            $this->permission = $gp[0];
            $this->permission[] = $gp[0];
        } else {
            $this->permission[] = NULL;
        }
		$this->default_biller_id = $this->site->default_biller_id();
    } 
    function index($warehouse_id = NULL)
    {
        $this->erp->checkPermissions();
        
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $this->data['products'] = $this->site->getProducts();
        $this->data['categories'] = $this->site->getAllCategories();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('products')));
        $meta = array('page_title' => lang('products'), 'bc' => $bc);
        $this->page_construct('products/index', $meta, $this->data);
    }
	
	function add_procategory()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang("category_code"), 'trim|is_unique[categories.code]|required');
        $this->form_validation->set_rules('name', lang("name"), 'required|min_length[3]');
        $this->form_validation->set_rules('userfile', lang("category_image"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $name = $this->input->post('name');
            $code = $this->input->post('code');

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                //$config['max_width'] = $this->Settings->iwidth;
                //$config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                //$data['image'] = $photo;
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
            } else {
                $photo = NULL;
            }
        } elseif ($this->input->post('add_category')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("products/add");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCategory($name, $code, $photo)) {
            $this->session->set_flashdata('message', lang("category_added"));
        //  redirect("products/add");
			if (strpos($_SERVER['HTTP_REFERER'], 'products/add_procategory') !== false) {
				 redirect("products/add");
			}else{
				 redirect("products/add");
			}
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

            $this->data['name'] = array('name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'class' => 'form-control',
                'required' => 'required',
                'value' => $this->form_validation->set_value('name'),
            );
            $this->data['code'] = array('name' => 'code',
                'id' => 'code',
                'type' => 'text',
                'class' => 'form-control',
                'required' => 'required',
                'value' => $this->form_validation->set_value('code'),
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_category', $this->data);
        }
    }
	
	function add_subcategory($parent_id = NULL)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('category', lang("main_category"), 'required');
        $this->form_validation->set_rules('code', lang("subcategory_code"), 'trim|is_unique[categories.code]|is_unique[subcategories.code]|required');
        $this->form_validation->set_rules('name', lang("subcategory_name"), 'required|min_length[2]');
        $this->form_validation->set_rules('userfile', lang("category_image"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $name = $this->input->post('name');
            $code = $this->input->post('code');
            $category = $this->input->post('category');
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                //$data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
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
            } else {
                $photo = NULL;
            }
        } elseif ($this->input->post('add_subcategory')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("products/add");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addSubCategory($category, $name, $code, $photo)) {
            $this->session->set_flashdata('message', lang("subcategory_added"));
            redirect("products/add", 'refresh');
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

            $this->data['name'] = array('name' => 'name',
                'id' => 'name',
                'type' => 'text', 'class' => 'form-control',
                'class' => 'form-control',
                'required' => 'required',
                'value' => $this->form_validation->set_value('name'),
            );
            $this->data['code'] = array('name' => 'code',
                'id' => 'code',
                'type' => 'text',
                'class' => 'form-control',
                'required' => 'required',
                'value' => $this->form_validation->set_value('code'),
            );
            $this->data['parent_id'] = $parent_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['categories'] = $this->settings_model->getAllCategories();
            $this->load->view($this->theme . 'settings/add_subcategory', $this->data);
        }
    }

    function getProducts($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('index');
        
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
		if ($this->input->get('product_actions')) {
            $product_actions = $this->input->get('product_actions');
        } else {
            $product_actions = NULL;
        }
		if ($this->input->get('product_type')) {
            $product_type = $this->input->get('product_type');
        } else {
            $product_type = NULL;
        }

        if ((! $this->Owner || ! $this->Admin) && ! $warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $detail_link = anchor('products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
		
		$catalog_link = '<a data-target="#myModal" data-toggle="modal" href="' . site_url('products/add_catalog/$1') . '"><i class="fa fa-edit"></i> ' . lang('Add_Catalog') . '</a>';
		
		$edit_link = '<a href="' . site_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a>';
		
        $delete_link = "<a href='products/delete/$1' class='tip po' title='<b>" . $this->lang->line("delete_product") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' id='a__$1' href='" . site_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . "</a>";
        $single_barcode = anchor_popup('products/single_barcode/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_barcode'), $this->popup_attributes);
		
        $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'. lang('actions') . ' <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu"><li>' . $detail_link . '</li>';
		
		if($this->Owner || $this->Admin || $this->GP['documents-add']){
			$action .= '<li>'.$catalog_link.'</li>';
		}
		
		if($this->Owner || $this->Admin || $this->GP['products-edit']){
			$action .= '<li>'.$edit_link.'</li>';
		}
		
		if($this->Owner || $this->Admin || $this->GP['products-adjustments']){
			$action .= '<li><a href="' . site_url('products/add_adjustment/$1/' . ($warehouse_id ? $warehouse_id : '')) . '" data-toggle="modal" data-target="#myModal"><i class="fa fa-filter"></i> '
            . lang('adjust_quantity') . '</a></li>';
		}
        
        $action .= '<li><a href="' . site_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
			<li>' . $single_barcode . '</li>';
		
		if($this->Owner || $this->Admin || $this->GP['products-delete']){
			$action .= '<li class="divider"></li><li>' . $delete_link . '</li>';
		}
			
		$action .= '</ul></div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
			
			$this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, " . 
							$this->db->dbprefix('products') . ".image as image, " . 
							$this->db->dbprefix('brands') . ".name as brand_name, " . 
							$this->db->dbprefix('categories') . ".name as cname, " . 
							$this->db->dbprefix('products') . ".code as code, " . 
							$this->db->dbprefix('products') . ".name as name, 
							cost as cost,
							price as price, 
							COALESCE(".$this->db->dbprefix('products').".quantity, 0) as quantity, 
							'' as rack, " . 
							$this->db->dbprefix('case').".name as case_name, " . 
							$this->db->dbprefix('diameter') . ".name as diameter_name," . 
							$this->db->dbprefix('dials').".name as dials_name, " . 
							$this->db->dbprefix('strap') . ".name as strap_name, " . 
							$this->db->dbprefix('water_resistance').".name as wa_name," . 
							$this->db->dbprefix('winding').".name as wi_name," . 
							$this->db->dbprefix('power_reserve').".name as po_name," . 
							$this->db->dbprefix('buckle').".name as bu_name," . 
							$this->db->dbprefix('buckle').".name as bu_name," . 
							$this->db->dbprefix('products') . ".product_details as product_details,".
							$this->db->dbprefix('complication').".name as co_name", FALSE)
                ->from('products')
                ->join('brands', 'brands.id = products.brand_id', 'left')
				->join('case', 'products.cf1=case.id', 'left')
				->join('diameter', 'products.cf2=diameter.id', 'left')
				->join('dials', 'products.cf3=dials.id', 'left')
				->join('strap', 'products.cf4=strap.id', 'left')
				->join('water_resistance', 'products.cf5=water_resistance.id', 'left')
				->join('winding', 'products.cf6=winding.id', 'left')
				->join('power_reserve', 'products.cf7=power_reserve.id', 'left')
				->join('buckle', 'products.cf8=buckle.id', 'left')
				->join('complication', 'products.cf9=complication.id', 'left');
			$this->datatables->unset_column('cf1');
            $this->datatables->group_by("products.id");
            if ($this->Settings->display_all_products) {
                $this->datatables->join("( SELECT * from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left');
            } else {
                
                if($warehouse_id == "no"){
                    $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left outer')->where('wp.product_id IS NULL');
                }else{
                    $this->datatables->join('warehouses_products wp', 'products.id = wp.product_id', 'left')->where(array('wp.warehouse_id' => $warehouse_id));
                }
                
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
			->join('units', 'products.unit=units.id', 'left')
            ->group_by("products.id");
			
        } else {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, " . 
							$this->db->dbprefix('products') . ".image as image, " . 
							$this->db->dbprefix('brands') . ".name as brand_name, " . 
							$this->db->dbprefix('categories') . ".name as cname, " . 
							$this->db->dbprefix('products') . ".code as code, " . 
							$this->db->dbprefix('products') . ".name as name, 
							cost as cost,
							price as price, 
							COALESCE(quantity, 0) as quantity, 
							'' as rack, " . 
							$this->db->dbprefix('case').".name as case_name, " . 
							$this->db->dbprefix('diameter') . ".name as diameter_name," . 
							$this->db->dbprefix('dials').".name as dials_name, " . 
							$this->db->dbprefix('strap') . ".name as strap_name, " . 
							$this->db->dbprefix('water_resistance').".name as wa_name," . 
							$this->db->dbprefix('winding').".name as wi_name," . 
							$this->db->dbprefix('power_reserve').".name as po_name," . 
							$this->db->dbprefix('buckle').".name as bu_name," . 
							$this->db->dbprefix('buckle').".name as bu_name," . 
							$this->db->dbprefix('products') . ".product_details as product_details,".
							$this->db->dbprefix('complication').".name as co_name", FALSE)
                ->from('products')
                ->join('brands', 'brands.id = products.brand_id', 'left')
                ->join('categories', 'products.category_id=categories.id', 'left')
				->join('case', 'products.cf1=case.id', 'left')
				->join('diameter', 'products.cf2=diameter.id', 'left')
				->join('dials', 'products.cf3=dials.id', 'left')
				->join('strap', 'products.cf4=strap.id', 'left')
				->join('water_resistance', 'products.cf5=water_resistance.id', 'left')
				->join('winding', 'products.cf6=winding.id', 'left')
				->join('power_reserve', 'products.cf7=power_reserve.id', 'left')
				->join('buckle', 'products.cf8=buckle.id', 'left')
				->join('complication', 'products.cf9=complication.id', 'left')
				->join('units', 'products.unit=units.id', 'left');
			$this->datatables->unset_column('cf1');
            $this->datatables->group_by("products.id");
        }
        if (!$this->Owner && !$this->Admin) {
            if (!$this->GP['products-cost']) {
                $this->datatables->unset_column("cost");
            }
            if (!$this->GP['products-price']) {
                $this->datatables->unset_column("price");
            }
        }
        if ($product) {
            $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
        }
        if ($category) {
            $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
        }
		if ($product_type) {
            $this->datatables->where($this->db->dbprefix('products') . ".type", $product_type);
        }
		if ($product_actions) {
            $this->datatables->where($this->db->dbprefix('products') . ".inactived", $product_actions);
        }else{
			$this->datatables->where($this->db->dbprefix('products') . ".inactived !=", '1');
		}      
        $this->datatables->add_column("Actions", $action, "productid, image, code, name");
        echo $this->datatables->generate();
    }
    function add_catalog($id=null){
	    $type =$this->products_model->getProductStatus($id); 
		if($type->type=="combo"){  
			$this->session->set_flashdata('error', ucfirst(lang('product_type_combo_can_not_add_catalog')));
            die('<script>window.location.replace("'.$_SERVER["HTTP_REFERER"].'");</script>');
		}
		
		$this->data['product']=$this->products_model->getproduct($id);
		$this->data['gallary']=$this->products_model->getgallary_image($id);
		$this->data['modal_js'] = $this->site->modal_js();
		$this->load->view($this->theme .'products/add_catalog', $this->data);
	}

		
	function add_catalogs(){
		
		//$this->erp->checkPermissions('documents', true, 'add');

		$this->form_validation->set_rules('code', $this->lang->line("code"), 'required|is_unique[erp_documents.product_code]');
		if ($this->form_validation->run('products') == true) {
			$image = $this->input->post('product_image');
			$gallary = $this->input->post('img');
			$product_code = $this->input->post('code');
			$product_name = $this->input->post('product_name');
			$description = $this->input->post('description');
			$brand_id = $this->input->post('brand_id');
			$cate_id = $this->input->post('cate_id');
			$scate_id = $this->input->post('subcate_id');
			$cost = $this->input->post('cost');
			$unit = $this->input->post('unit_id');
			$price = $this->input->post('price');
			$serial = ($this->input->post('is_serial')? '1':'0');
			
			$document = array(
        					'product_code' => $product_code,
        					'product_name' => $product_name,
        					'description' => $description,
        					'brand_id' => $brand_id,
        					'category_id' => $cate_id,
        					'subcategory_id' => $scate_id,
        					'cost' => $cost,
        					'price' => $price,
        					'unit' => $unit,
        					'serial' => $serial
        				);
			
			$this->load->library('upload');
			if ($_FILES['product_image']['size'] > 0) {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                //$config['max_width'] = $this->Settings->iwidth;
                //$config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("documents");
                }
                $photo = $this->upload->file_name;
                $document['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;

                $config['maintain_ratio'] = TRUE;
                //$config['width'] = $this->Settings->twidth;
                //$config['height'] = $this->Settings->theight;
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
            }else{
				$document['image'] = $this->input->post('exsit_image');
			}
			
			if ($_FILES['userfile']['name'][0] != "") {
				
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                //$config['max_width'] = $this->Settings->iwidth;
                //$config['max_height'] = $this->Settings->iheight;
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
                        redirect("documents");
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
                $photos = $gallary;
            }
			
		}
       
		if ($this->form_validation->run() == true && $sid = $this->products_model->addDocument($document, $photos)) {
			$this->session->set_flashdata('message', $this->lang->line("document_added"));
			$ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : NULL;
			redirect("documents");
		} else {	
			$this->session->set_flashdata('error', $this->lang->line("catalog_aready_exist"));
			redirect("products");
		}

	}
    function set_rack($product_id = NULL, $warehouse_id = NULL)
    {
        $this->erp->checkPermissions('edit', true);

        $this->form_validation->set_rules('rack', lang("rack_location"), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = array('rack' => $this->input->post('rack'),
                'product_id' => $product_id,
                'warehouse_id' => $warehouse_id,
            );
        } elseif ($this->input->post('set_rack')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("products");
        }

        if ($this->form_validation->run() == true && $this->products_model->setRack($data)) {
            $this->session->set_flashdata('message', lang("rack_set"));
            redirect("products/" . $warehouse_id);
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['product'] = $this->site->getProductByID($product_id);
            $wh_pr = $this->products_model->getProductQuantity($product_id, $warehouse_id);
            $this->data['rack'] = $wh_pr['rack'];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'products/set_rack', $this->data);

        }
    }

    function product_barcode($product_code = NULL, $bcs = 'code128', $height = 60)
    {
        return "<img src='" . site_url('products/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height) . "' alt='{$product_code}' class='bcimg' />";
    }

    function barcode($product_code = NULL, $bcs = 'code128', $height = 60)
    {
        return site_url('products/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height);
    }

    function gen_barcode($product_code = NULL, $bcs = 'code128', $height = 60, $text = 2 )
    {
        
		$drawText = ($text != 1) ? FALSE : TRUE;
        $this->load->library('zend');
        $this->zend->load('Zend/Barcode');
        $barcodeOptions = array('text' => $product_code, 'barHeight' => $height, 'drawText' => $drawText, 'factor' => 1);
        $rendererOptions = array('imageType' => 'png', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
        $imageResource = Zend_Barcode::render($bcs, 'image', $barcodeOptions, $rendererOptions);
        return $imageResource;

    }

    function single_barcode($product_id = NULL, $warehouse_id = NULL)
    {
        $this->erp->checkPermissions('barcode', true);

        $product = $this->products_model->getProductByID($product_id);
        $currencies = $this->site->getAllCurrencies();

        $this->data['product'] = $product;
        $options = $this->products_model->getProductOptionsWithWH($product_id);
        if( ! $options) {
            $options = $this->products_model->getProductOptions($product_id);
        }
        $table = '';
        if (!empty($options)) {
            $r = 1;
            foreach ($options as $option) {
                $quantity = $option->wh_qty;
                $warehouse = $this->site->getWarehouseByID(($option->quantity <= 0) ? $this->Settings->default_warehouse :$option->warehouse_id);
                $table .= '<h3 class="'.($option->quantity ? '' : 'text-danger').'">'.$warehouse->name.' ('.$warehouse->code.') - '.$product->name.' - '.$option->name.' ('.lang('quantity').': '.$quantity.')</h3>';
                $table .= '<table class="table table-bordered barcodes"><tbody><tr>';
                for($i=0; $i < $quantity; $i++) {

                    $table .= '<td style="width: 20px;"><table class="table-barcode"><tbody><tr><td colspan="2" class="bold">' . $this->Settings->site_name . '</td></tr><tr><td colspan="2">' . $product->name . ' - '.$option->name.'</td></tr><tr><td colspan="2" class="text-center bc">' . $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', 60) . '</td></tr>';
                    foreach ($currencies as $currency) {
                        $table .= '<tr><td class="text-left">' . $currency->code . '</td><td class="text-right">' . $this->erp->formatMoney($product->price * $currency->rate) . '</td></tr>';
                    }
                    $table .= '</tbody></table>';
                    $table .= '</td>';
                    $table .= ((bool)($i & 1)) ? '</tr><tr>' : '';

                }
                $r++;
                $table .= '</tr></tbody></table><hr>';
            }
        } else {
            $table .= '<table class="table table-bordered barcodes"><tbody><tr>';
            $num = $product->quantity;
            for ($r = 1; $r <= $num; $r++) {
                if ($r != 1) {
                    $rw = (bool)($r & 1);
                    $table .= $rw ? '</tr><tr>' : '';
                }
                $table .= '<td style="width: 20px;"><table class="table-barcode"><tbody><tr><td colspan="2" class="bold">' . $this->Settings->site_name . '</td></tr><tr><td colspan="2">' . $product->name . '</td></tr><tr><td colspan="2" class="text-center bc">' . $this->product_barcode($product->code, $product->barcode_symbology, 60) . '</td></tr>';
                foreach ($currencies as $currency) {
                    $table .= '<tr><td class="text-left">' . $currency->code . '</td><td class="text-right">' . $this->erp->formatMoney($product->price * $currency->rate) . '</td></tr>';
                }
                $table .= '</tbody></table>';
                $table .= '</td>';
            }
            $table .= '</tr></tbody></table>';
        }

        $this->data['table'] = $table;

        $this->data['page_title'] = lang("print_barcodes");
        $this->load->view($this->theme . 'products/single_barcode', $this->data);
    }

    function single_label($product_id = NULL, $warehouse_id = NULL)
    {
        $this->erp->checkPermissions('barcode', true);

        $product = $this->products_model->getProductByID($product_id);
        $currencies = $this->site->getAllCurrencies();

        $this->data['product'] = $product;
        $options = $this->products_model->getProductOptionsWithWH($product_id);

        $table = '';
        if (!empty($options)) {
            $r = 1;
            foreach ($options as $option) {
                $quantity = $option->wh_qty;
                $warehouse = $this->site->getWarehouseByID($option->warehouse_id);
                $table .= '<h3 class="'.($option->quantity ? '' : 'text-danger').'">'.$warehouse->name.' ('.$warehouse->code.') - '.$product->name.' - '.$option->name.' ('.lang('quantity').': '.$quantity.')</h3>';
                $table .= '<table class="table table-bordered barcodes"><tbody><tr>';
                for($i=0; $i < $quantity; $i++) {
                    if ($i % 4 == 0 && $i > 3) {
                        $table .= '</tr><tr>';
                    }
                    $table .= '<td style="width: 20px;"><table class="table-barcode"><tbody><tr><td colspan="2" class="bold">' . $this->Settings->site_name . '</td></tr><tr><td colspan="2">' . $product->name . ' - '.$option->name.'</td></tr><tr><td colspan="2" class="text-center bc">' . $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', 30) . '</td></tr>';
                    foreach ($currencies as $currency) {
                        $table .= '<tr><td class="text-left">' . $currency->code . '</td><td class="text-right">' . $this->erp->formatMoney($product->price * $currency->rate) . '</td></tr>';
                    }
                    $table .= '</tbody></table>';
                    $table .= '</td>';
                }
                $r++;
                $table .= '</tr></tbody></table><hr>';
            }
        } else {
            $table .= '<table class="table table-bordered barcodes"><tbody><tr>';
            $num = $product->quantity;
            for ($r = 1; $r <= $num; $r++) {
                $table .= '<td style="width: 20px;"><table class="table-barcode"><tbody><tr><td colspan="2" class="bold">' . $this->Settings->site_name . '</td></tr><tr><td colspan="2">' . $product->name . '</td></tr><tr><td colspan="2" class="text-center bc">' . $this->product_barcode($product->code, $product->barcode_symbology, 30) . '</td></tr>';
                foreach ($currencies as $currency) {
                    $table .= '<tr><td class="text-left">' . $currency->code . '</td><td class="text-right">' . $this->erp->formatMoney($product->price * $currency->rate) . '</td></tr>';
                }
                $table .= '</tbody></table>';
                $table .= '</td>';
                if ($r % 4 == 0 && $r > 3) {
                    $table .= '</tr><tr>';
                }
            }
            $table .= '</tr></tbody></table>';
        }

        $this->data['table'] = $table;
        $this->data['page_title'] = lang("barcode_label");
        $this->load->view($this->theme . 'products/single_label', $this->data);
    }

    function single_label2($product_id = NULL, $warehouse_id = NULL)
    {
        $this->erp->checkPermissions('barcode', true);

        $pr = $this->products_model->getProductByID($product_id);
        $currencies = $this->site->getAllCurrencies();

        $this->data['product'] = $pr;
        $options = $this->products_model->getProductOptionsWithWH($product_id);
        $html = "";

        if (!empty($options)) {
            foreach ($options as $option) {
                for ($r = 1; $r <= $option->wh_qty; $r++) {
                    $html .= '<div class="labels"><strong>' . $pr->name . ' - '.$option->name.'</strong><br>' . $this->product_barcode($pr->code . $this->Settings->barcode_separator . $option->id, 'code128', 25) . '<br><span class="price">'.lang('price') .': ' .$this->Settings->default_currency. ' ' . $this->erp->formatMoney($pr->price) . '</span></div>';
                }
            }
        } else {
            for ($r = 1; $r <= $pr->quantity; $r++) {
                $html .= '<div class="labels"><strong>' . $pr->name . '</strong><br>' . $this->product_barcode($pr->code, $pr->barcode_symbology, 25) . '<br><span class="price">'.lang('price') .': ' .$this->Settings->default_currency. ' ' . $this->erp->formatMoney($pr->price) . '</span></div>';
            }
        }

        $this->data['html'] = $html;
        $this->data['page_title'] = lang("barcode_label");
        $this->load->view($this->theme . 'products/single_label2', $this->data);
    }

    function print_barcodes($product_id = NULL)
    {
        $this->erp->checkPermissions('barcode', true);

        $this->form_validation->set_rules('style', lang("style"), 'required');

        if ($this->form_validation->run() == true) {

            $style = $this->input->post('style');
            $bci_size = ($style == 10 || $style == 12 ? 50 : ($style == 14 || $style == 18 ? 30 : 20));
            $currencies = $this->site->getAllCurrencies();
            $s = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            if ($s < 1) {
                $this->session->set_flashdata('error', lang('no_product_selected'));
                redirect("products/print_barcodes");
            }
            for ($m = 0; $m < $s; $m++) {
                $pid = $_POST['product'][$m];
                $quantity = $_POST['quantity'][$m];
                $product = $this->products_model->getProductWithCategory($pid);
                
                if ($variants = $this->products_model->getProductOptions($pid)) {
                    foreach ($variants as $option) {
                        if ($this->input->post('vt_'.$product->id.'_'.$option->id)) {
                            $barcodes[] = array(
                                'site' 			=> $this->input->post('site_name') ? $this->Settings->site_name : FALSE,
                                'name' 			=> $this->input->post('product_name') ? $product->name.' - '.$option->name : FALSE,
                                'image' 		=> $this->input->post('product_image') ? $product->image : FALSE,
                                //'barcode' 	=> $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', $bci_size),
								'barcode' 		=> $this->product_barcode($product->code, $product->barcode_symbology, $bci_size),
                                'price' 		=> $this->input->post('price') ?  $this->erp->formatMoney($option->price != 0 ? $option->price : $product->price) : FALSE,
                                'unit' 			=> $this->input->post('unit') ? $product->unit : FALSE,
                                'category' 		=> $this->input->post('category') ? $product->category : FALSE,
                                'currencies' 	=> $this->input->post('currencies'),
                                'variants' 		=> $this->input->post('variants') ? $variants : FALSE,
                                'quantity' 		=> $quantity
                                );
                        }
                    }
                } else {
                    $barcodes[] = array(
                        'site' 			=> $this->input->post('site_name') ? $this->Settings->site_name : FALSE,
                        'name' 			=> $this->input->post('product_name') ? $product->name : FALSE,
                        'image' 		=> $this->input->post('product_image') ? $product->image : FALSE,
                        'barcode' 		=> $this->product_barcode($product->code, $product->barcode_symbology, $bci_size),
                        'price' 		=> $this->input->post('price') ?  $this->erp->formatMoney($product->price) : FALSE,
                        'unit' 			=> $this->input->post('unit') ? $product->unit : FALSE,
                        'category' 		=> $this->input->post('category') ? $product->category : FALSE,
                        'currencies' 	=> $this->input->post('currencies'),
                        'variants' 		=> FALSE,
                        'quantity' 		=> $quantity
                    );
                }
            }
            $this->data['barcodes'] = $barcodes;
            $this->data['currencies'] = $currencies;
            $this->data['style'] = $style;
            $this->data['items'] = false;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
            $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
            $this->page_construct('products/print_barcodes', $meta, $this->data);

        } else {

            if ($this->input->get('purchase') || $this->input->get('transfer')) {
                if ($this->input->get('purchase')) {
                    $purchase_id = $this->input->get('purchase', TRUE);
                    $items = $this->products_model->getPurchaseItems($purchase_id);
                } elseif ($this->input->get('transfer')) {
                    $transfer_id = $this->input->get('transfer', TRUE);
                    $items = $this->products_model->getTransferItems($transfer_id);
                }
                if ($items) {
                    foreach ($items as $item) {
                        if ($row = $this->products_model->getProductByID($item->product_id)) {
                            $selected_variants = false;
                            if ($variants = $this->products_model->getProductOptions($row->id)) {
                                foreach ($variants as $variant) {
                                    $selected_variants[$variant->id] = isset($pr[$row->id]['selected_variants'][$variant->id]) && !empty($pr[$row->id]['selected_variants'][$variant->id]) ? 1 : ($variant->id == $item->option_id ? 1 : 0);
                                }
                            }
                            $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $item->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                        }
                    }
                    $this->data['message'] = lang('products_added_to_list');
                }
            }

            if ($this->input->get('category')) {
                if ($products = $this->products_model->getCategoryProducts($this->input->get('category'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = array();
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }

            if ($this->input->get('subcategory')) {
                if ($products = $this->products_model->getSubCategoryProducts($this->input->get('subcategory'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = array();
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }

            $this->data['items'] = isset($pr) ? json_encode($pr) : false;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
            $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
            $this->page_construct('products/print_barcodes', $meta, $this->data);
        }
    }

    /* ------------------------------------------------------- */

    function add($id = NULL, $quote_id = NULL)
    {
        $this->erp->checkPermissions();
        $this->load->helper('security');
        $warehouses = $this->site->getAllWarehouses();
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
        }
        if ($this->input->post('barcode_symbology') == 'ean13') {
            $this->form_validation->set_rules('code', lang("product_code"), 'min_length[13]|max_length[13]');
        }
		$this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]');
        $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');
		$warehouse_qty = array();
		$related_products = array();
		$q_code = '';
        if ($this->form_validation->run() == true) {
            $tax_rate = $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : NULL;
			$p_price = $this->erp->formatDecimal($this->input->post('price'));
			$vat_per = $tax_rate->rate / 100;
			$multiplier = $vat_per + 1;

			//$gdc_price = ($p_price + $multiplier);

			$gdc_price = $p_price + ($p_price * $vat_per);
			
            if($this->input->post('inactive')) {
				$inactived = $this->input->post('inactive');
			} else {
				$inactived = 0;
			}
			$q_code = $this->input->post('q_code');
			$data = array(
                'code' => $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
                'cost' => $this->erp->formatDecimal($this->input->post('cost')),
                'price' => $this->erp->formatDecimal($this->input->post('price')),
                'unit' => $this->input->post('unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' => $this->input->post('details'),
                'product_details' => $this->input->post('product_details'),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->erp->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->erp->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->erp->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->erp->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->erp->formatDecimal($this->input->post('supplier_5_price')),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'cf7' => $this->input->post('cf7'),
                'cf8' => $this->input->post('cf8'),
                'cf9' => $this->input->post('cf9'),
				'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->erp->formatDecimal($this->input->post('promo_price')),
                'start_date' => $this->erp->fsd($this->input->post('start_date')),
                'end_date' => $this->erp->fsd($this->input->post('end_date')),
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),           
				'currentcy_code'    => $this->input->post('currency'),
				'inactived' => $inactived,
				'brand_id' => $this->input->post('brand'),
				'is_serial' => $this->input->post('is_serial')
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
			//$this->erp->print_arrays($data);
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
                            'item_code'   => $_POST['combo_item_code'][$r],
							//'qty_unit'  => $_POST['combo_item_quantity_unit'][$r],
							'quantity'    => $_POST['combo_item_quantity_unit'][$r],
                            'unit_price'  => $_POST['combo_item_price'][$r],
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
            //$this->erp->print_arrays($data);
        }

        if ($this->form_validation->run() == true && $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $related_products, $q_code)) {
            $this->session->set_flashdata('message', lang("product_added"));
			$this->session->set_flashdata('from_ls_pr', '1');
			if (strpos($_SERVER['HTTP_REFERER'], 'products/add') !== false) {
				redirect('products');
			}else{
				redirect('purchases/add');
			}
        } else {
            $this->data['error'] 		= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			
			$this->data['currencies'] 	= $this->products_model->getAllCurrencies();
            $this->data['categories'] 	= $this->site->getAllCategories();
			$this->data['brands'] 		= $this->site->getAllBrands();
            $this->data['tax_rates'] 	= $this->site->getAllTaxRates();
            $this->data['warehouses'] 	= $warehouses;
			
			if($id){
				$this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : NULL;
				$this->data['product'] 		= $id ? $this->products_model->getProductByID($id) : NULL;
				$this->data['combo_items']  = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : NULL;
				$this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : NULL;
			}
			
			if($quote_id){
				$this->data['product'] 		= $this->products_model->getProductByQuoteId($quote_id);
			}
			
			/** Project **/
			$this->data['shops'] 		= $this->products_model->getProjects();
			$this->data['case'] 		= $this->products_model->getCases();
			$this->data['diameters'] 	= $this->products_model->getDiameters();
			$this->data['dial'] 		= $this->products_model->getDials();
			$this->data['strap'] 		= $this->products_model->getStraps();
			$this->data['water'] 		= $this->products_model->getWater();
			$this->data['winding'] 		= $this->products_model->getWinding();
			$this->data['powerreserve'] = $this->products_model->getPowerReserve();
			$this->data['buckle'] 		= $this->products_model->getBuckle();
			$this->data['complication'] = $this->products_model->getComplication();
			$this->data['unit']  		= $this->products_model->getUnits();
			$this->data['products'] 	= $this->site->getAllProducts();
            $this->data['variants'] 	= $this->products_model->getAllVariants();
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_product')));
            $meta = array('page_title' => lang(''), 'bc' => $bc);
			$this->page_construct('products/add', $meta, $this->data);
        }
    }

    function suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getProductNames($term);
        if ($rows) {
            $uom = "";
            foreach ($rows as $row) {
                $this->db->select('product_variants.id, product_variants.name');
                $this->db->from('product_variants');
                $this->db->where('product_id', $row->id);
                $q = $this->db->get()->result();
                foreach ($q as $rw) {
                    $uom .= $rw->name . "#";
                }

                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")(". $row->uname .")", 'uom' => $uom, 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'cost' => $row->cost, 'qty' => 1, 'cqty' => 1, 'unit' => $row->unit);
				$uom = '';
            }
            echo json_encode($pr);
        } else {
			echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	public function suggests()
    {
        $term = $this->input->get('term', true);

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

        $rows = $this->products_model->getProductNumber($term);

        if ($rows) {
            $uom = "";
            foreach ($rows as $row) {
                $this->db->select('id, name');
                $this->db->from('product_variants');
                $this->db->where('product_id', $row->id);
                $q = $this->db->get()->result();
                foreach ($q as $rw) {
                    $uom .= $rw->name . "#";
                }

                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'uom' => $uom, 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1);
            }
            echo json_encode($pr);
        } else {
            echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	function check_product_available($term = NULL){
		$term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $row = $this->products_model->getProductCode($term);
        if ($row) {
            echo 1;
        } else {
            echo 0;
        }
	}
	
	function getProductSerialAjax($serial = NULL){
		$serial = $this->input->get('serial', TRUE);
        if (strlen($serial) < 1 || !$serial) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $row = $this->products_model->getSerialProducts($serial);
        if ($row) {
            echo 1;
        } else {
            echo 0;
        }
	}
	
	function getComboItemInfo($id = NULL) {
		$id = $this->input->get('combo_id', TRUE);
		$result = $this->products_model->getProductByID($id);
		if ($result) {
			$pr[] = array('id' => $result->id, 'cf1' => $result->cf1, 'cf2' => $result->cf2, 'cf3' => $result->cf3, 'cf4' => $result->cf4, 'cf5' => $result->cf5, 'cf6' => $result->cf6, 'cf7' => $result->cf7, 'cf8' => $result->cf8, 'cf9' => $result->cf9, 'brand' => $result->brand_id, 'image' => $result->image);
			echo json_encode($pr);
        } else {
            echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
	}

    function get_suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getProductsForPrinting($term);
        if ($rows) {
            foreach ($rows as $row) {
                $variants = $this->products_model->getProductOptions($row->id);
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1, 'variants' => $variants);
            }
            echo json_encode($pr);
        } else {
            echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    function addByAjax()
    {
        if (!$this->mPermissions('add')) {
            exit(json_encode(array('msg' => lang('access_denied'))));
        }
        if ($this->input->get('token') && $this->input->get('token') == $this->session->userdata('user_csrf') && $this->input->is_ajax_request()) {
            $product = $this->input->get('product');
			if(!isset($product['type']) || empty($prodcut['type'])){
				exit(json_encode(array('msg' => lang('product_type_is_required'))));
			}
            if (!isset($product['code']) || empty($product['code'])) {
                exit(json_encode(array('msg' => lang('product_code_is_required'))));
            }
            if (!isset($product['name']) || empty($product['name'])) {
                exit(json_encode(array('msg' => lang('product_name_is_required'))));
            }
			if (!isset($product['barcode_symbology']) || empty($product['barcode_symbology'])) {
                exit(json_encode(array('msg' => lang('barcode_symbology_is_required'))));
            }
            if (!isset($product['category_id']) || empty($product['category_id'])) {
                exit(json_encode(array('msg' => lang('product_category_is_required'))));
            }
            if (!isset($product['unit']) || empty($product['unit'])) {
                exit(json_encode(array('msg' => lang('product_unit_is_required'))));
            }
            if (!isset($product['price']) || empty($product['price'])) {
                exit(json_encode(array('msg' => lang('product_price_is_required'))));
            }
            if (!isset($product['cost']) || empty($product['cost'])) {
                exit(json_encode(array('msg' => lang('product_cost_is_required'))));
            }
            if ($this->products_model->getProductByCode($product['code'])) {
                exit(json_encode(array('msg' => lang('product_code_already_exist'))));
            }
            if ($row = $this->products_model->addAjaxProduct($product)) {
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $pr = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'qty' => 1, 'cost' => $row->cost, 'name' => $row->name, 'tax_method' => $row->tax_method, 'tax_rate' => $tax_rate, 'discount' => '0');
                echo json_encode(array('msg' => 'success', 'result' => $pr));
            } else {
                exit(json_encode(array('msg' => lang('failed_to_add_product'))));
            }
        } else {
            json_encode(array('msg' => 'Invalid token'));
        }

    }

    /* -------------------------------------------------------- */

    function edit($id = NULL)
    {
        $this->erp->checkPermissions();
        $this->load->helper('security');
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $warehouses = $this->site->getAllWarehouses();
        $warehouses_products = $this->products_model->getAllWarehousesWithPQ($id);
        $product = $this->site->getProductByID($id);
        if (!$id || !$product) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->input->post('type') == 'standard') {
            // $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
        }
        if ($this->input->post('code') !== $product->code) {
            $this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]');
        }
        if ($this->input->post('barcode_symbology') == 'ean13') {
            $this->form_validation->set_rules('code', lang("product_code"), 'min_length[13]|max_length[13]');
        }
        $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');

        if ($this->form_validation->run('products/add') == true) {
			if($this->input->post('inactive')) {
				$inactived = $this->input->post('inactive');
			} else {
				$inactived = 0;
			}
            $data = array(
				'code' => $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
                'cost' => $this->erp->formatDecimal($this->input->post('cost')),
                'price' => $this->erp->formatDecimal($this->input->post('price')),
                'unit' => $this->input->post('unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' => $this->input->post('details'),
                'product_details' => $this->input->post('product_details'),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->erp->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->erp->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->erp->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->erp->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->erp->formatDecimal($this->input->post('supplier_5_price')),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'cf7' => $this->input->post('cf7'),
                'cf8' => $this->input->post('cf8'),
                'cf9' => $this->input->post('cf9'),
				'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->erp->formatDecimal($this->input->post('promo_price')),
                'start_date' => $this->erp->fsd($this->input->post('start_date')),
                'end_date' => $this->erp->fsd($this->input->post('end_date')),
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'), 
				'currentcy_code'    => $this->input->post('currency'),
				'inactived' => $inactived,
				'brand_id'  => $this->input->post('brand'),
				'is_serial' => $this->input->post('is_serial')
			);
			
			$related_straps = $this->input->post('related_strap');
			if($this->site->deleteStrapByProductCode($this->input->post('code'))) {
				for($i=0; $i<sizeof($related_straps); $i++) {
					$product_name = $this->site->getProductByCode($related_straps[$i]);
					$related_products[] = array(
						'product_code' 			=> $this->input->post('code'),
						'related_product_code' 	=> $related_straps[$i],
						'product_name' 			=> $product_name->name,
					);
				}
			}
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                if ($product_variants = $this->products_model->getProductOptions($id)) {
                    foreach ($product_variants as $pv) {
                        $update_variants[] = array(
                            'id' 		=> $this->input->post('variant_id_'.$pv->id),
                            'name' 		=> $this->input->post('variant_name_'.$pv->id),
							'qty_unit' 	=> $this->input->post('variant_qty_unit_'.$pv->id),
                            'price' 	=> $this->input->post('variant_price_'.$pv->id),
                        );
                    }
                } else {
                    $update_variants = NULL;
                }
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    $warehouse_qty[] = array(
                        'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                        'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : NULL
                    );
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            if ($product_variatnt = $this->products_model->getPrductVariantByPIDandName($id, trim($_POST['attr_name'][$r]))) {
                                $this->form_validation->set_message('required', lang("product_already_has_variant").' ('.$_POST['attr_name'][$r].')');
                                $this->form_validation->set_rules('new_product_variant', lang("new_product_variant"), 'required');
                            } else {
                                $product_attributes[] = array(
                                    'name' 		=> $_POST['attr_name'][$r],
									'qty_unit' 	=> $_POST['attr_quantity_unit'][$r],
                                    'price' 	=> $_POST['attr_price'][$r],
                                );
                            }
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
                            'item_code' 	=> $_POST['combo_item_code'][$r],
							'quantity' 		=> $_POST['combo_item_quantity_unit'][$r],
                            'unit_price' 	=> $_POST['combo_item_price'][$r],
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
                //$config['max_width'] = $this->Settings->iwidth;
                //$config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("products/edit/" . $id);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                //$config['width'] = $this->Settings->twidth;
                //$config['height'] = $this->Settings->theight;
				$config['width'] = $this->Settings->iwidth;
                $config['height'] = $this->Settings->iheight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
				copy($config['new_image'] , $config['source_image']);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
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
                //$config['max_width'] = $this->Settings->iwidth;
                //$config['max_height'] = $this->Settings->iheight;
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
                        redirect("products/edit/" . $id);
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
            //$this->erp->print_arrays($data, $warehouse_qty, $update_variants, $product_attributes, $photos, $items);
        }

        if ($this->form_validation->run() == true && $this->products_model->updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants, $related_products)) {
            $this->session->set_flashdata('message', lang("product_updated"));
            redirect('products');
        } else {
            $this->data['error'] 		= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			
			$this->data['currencies'] 	= $this->products_model->getAllCurrencies();
            $this->data['categories'] 	= $this->site->getAllCategories();
			$this->data['brands'] 		= $this->site->getAllBrands();
            $this->data['tax_rates'] 	= $this->site->getAllTaxRates();
            $this->data['warehouses'] 	= $warehouses;
            $this->data['warehouses_products'] = $warehouses_products;
            $this->data['product'] 		= $product;
			$this->data['products'] 	= $this->site->getAllProducts();
			$this->data['straps'] 		= $this->products_model->getStrapByProductID($product->code);
            $this->data['variants'] 	= $this->products_model->getAllVariants();
			$this->data['case'] 		= $this->products_model->getCases();
			$this->data['diameters'] 	= $this->products_model->getDiameters();
			$this->data['dial'] 		= $this->products_model->getDials();
			$this->data['all_strap'] 	= $this->products_model->getStraps();
			$this->data['water'] 		= $this->products_model->getWater();
			$this->data['winding'] 		= $this->products_model->getWinding();
			
			$this->data['powerreserve'] = $this->products_model->getPowerReserve();
			$this->data['buckle'] = $this->products_model->getBuckle();
			$this->data['complication'] = $this->products_model->getComplication();
			
			$this->data['unit'] 		= $this->products_model->getUnits();
            $this->data['product_variants'] = $this->products_model->getProductOptions($id);
            $this->data['combo_items'] 	= $product->type == 'combo' ? $this->products_model->getProductComboItems($product->id) : NULL;
            $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : NULL;
			
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_product')));
            $meta = array('page_title' => lang('edit_product'), 'bc' => $bc);
            $this->page_construct('products/edit', $meta, $this->data);
        }
    }

    /* ----------------------------------------------------------------------------------------------------------------------------------------- */

    function import_csv()
    {
        $this->erp->checkPermissions('import', true, 'products');
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
				$upload_data = $this->upload->data();
				$file_name = $upload_data['file_name']; 
				$extension=$upload_data['file_ext']; 

				$objReader 		= PHPExcel_IOFactory::createReader('Excel2007');
				$objReader->setReadDataOnly(true); 	
				$objPHPExcel	= $objReader->load('./assets/uploads/excel/'.$file_name);		 
				$totalrows 		= $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();     	 
				$objWorksheet 	=$objPHPExcel->setActiveSheetIndex(0);
				
				//########### Prevent from duplicated code
				for($i = 2; $i <= $totalrows; $i++)
				{
					$p_reference = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
					if ($this->products_model->getProductByCode(trim($p_reference))) {
						$this->session->set_flashdata('error', lang("check_product_code") . " (" . $p_reference . "). " . lang("code_already_exist") . " " . lang("line_no") . " " . $rw);
						redirect("products/import_csv");
					}
                    if($p_reference != ""){
                        $array[] = trim($p_reference);
                    }
					
				}
				$count_values = array();
                foreach ($array as $a) {

                    @$count_values[$a]++;

                }
                foreach($count_values as $key => $value){
                    if($value > 1){
                        $this->session->set_flashdata('error', lang("prodcut_code")." (".$key.") ".lang(" is_duplicate"));
                        redirect("products/import_csv");
                    }
                }
               
				//########### End Prevent
				$brand = $category = $subcategory = array();
				for($i=2;$i<=$totalrows;$i++)
				{
					$p_type = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
					$rw = 2;
					if(isset($p_type)){
						$p_reference = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
						$p_name 	 = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
						$p_brand 	 = $objWorksheet->getCellByColumnAndRow(3,$i)->getValue();
						$p_category	 = $objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
						$p_units	 = $objWorksheet->getCellByColumnAndRow(5,$i)->getValue();
						$p_cost 	 = $objWorksheet->getCellByColumnAndRow(6,$i)->getValue();
						$p_price 	 = $objWorksheet->getCellByColumnAndRow(7,$i)->getValue();
						$p_alertQ 	 = $objWorksheet->getCellByColumnAndRow(8,$i)->getValue();
						$p_tax  	 = $objWorksheet->getCellByColumnAndRow(9,$i)->getValue();
						$p_method  	 = $objWorksheet->getCellByColumnAndRow(10,$i)->getValue();
						$p_line  	 = $objWorksheet->getCellByColumnAndRow(11,$i)->getValue();
						$p_variant 	 = $objWorksheet->getCellByColumnAndRow(12,$i)->getValue();
						$p_supplier1 = $objWorksheet->getCellByColumnAndRow(13,$i)->getValue();
						$p_supplier2 = $objWorksheet->getCellByColumnAndRow(14,$i)->getValue();
						$p_supplier3 = $objWorksheet->getCellByColumnAndRow(15,$i)->getValue();
						$p_supplier4 = $objWorksheet->getCellByColumnAndRow(16,$i)->getValue();
						$p_supplier5 = $objWorksheet->getCellByColumnAndRow(17,$i)->getValue();
						$p_serial	 = $objWorksheet->getCellByColumnAndRow(18,$i)->getValue();
						$p_case 	 = $objWorksheet->getCellByColumnAndRow(19,$i)->getValue();
						$p_dialmeter = $objWorksheet->getCellByColumnAndRow(20,$i)->getValue();
						$p_dial		 = $objWorksheet->getCellByColumnAndRow(21,$i)->getValue();
						$p_strap	 = $objWorksheet->getCellByColumnAndRow(22,$i)->getValue();
						$p_water	 = $objWorksheet->getCellByColumnAndRow(23,$i)->getValue();
						$p_winding	 = $objWorksheet->getCellByColumnAndRow(24,$i)->getValue();
						$p_power	 = $objWorksheet->getCellByColumnAndRow(25,$i)->getValue();
						$p_buckle	 = $objWorksheet->getCellByColumnAndRow(26,$i)->getValue();
						$p_compicate = $objWorksheet->getCellByColumnAndRow(27,$i)->getValue();
						$p_detail	 = $objWorksheet->getCellByColumnAndRow(28,$i)->getValue();
						$p_image	 = $objWorksheet->getCellByColumnAndRow(29,$i)->getValue();
						
						$p_comitem1	 = $objWorksheet->getCellByColumnAndRow(30,$i)->getValue();
						$p_comcode1  = $objWorksheet->getCellByColumnAndRow(31,$i)->getValue();
						$p_comprice1 = $objWorksheet->getCellByColumnAndRow(32,$i)->getValue();
						$p_comimage1 = $objWorksheet->getCellByColumnAndRow(33,$i)->getValue();
						$p_comcate1  = $objWorksheet->getCellByColumnAndRow(34,$i)->getValue();
						$p_comline1  = $objWorksheet->getCellByColumnAndRow(35,$i)->getValue();
						
						$p_comitem2	 = $objWorksheet->getCellByColumnAndRow(36,$i)->getValue();
						$p_comcode2  = $objWorksheet->getCellByColumnAndRow(37,$i)->getValue();
						$p_comprice2 = $objWorksheet->getCellByColumnAndRow(38,$i)->getValue();
						$p_comimage2 = $objWorksheet->getCellByColumnAndRow(39,$i)->getValue();
						$p_comcate2  = $objWorksheet->getCellByColumnAndRow(40,$i)->getValue();
						$p_comline2  = $objWorksheet->getCellByColumnAndRow(41,$i)->getValue();
						
						$p_comitem3	 = $objWorksheet->getCellByColumnAndRow(42,$i)->getValue();
						$p_comcode3  = $objWorksheet->getCellByColumnAndRow(43,$i)->getValue();
						$p_comprice3 = $objWorksheet->getCellByColumnAndRow(44,$i)->getValue();
						$p_comimage3 = $objWorksheet->getCellByColumnAndRow(45,$i)->getValue();
						$p_comcate3  = $objWorksheet->getCellByColumnAndRow(46,$i)->getValue();
						$p_comline3  = $objWorksheet->getCellByColumnAndRow(47,$i)->getValue();
						
						$p_comitem4	 = $objWorksheet->getCellByColumnAndRow(48,$i)->getValue();
						$p_comcode4  = $objWorksheet->getCellByColumnAndRow(49,$i)->getValue();
						$p_comprice4 = $objWorksheet->getCellByColumnAndRow(50,$i)->getValue();
						$p_comimage4 = $objWorksheet->getCellByColumnAndRow(51,$i)->getValue();
						$p_comcate4  = $objWorksheet->getCellByColumnAndRow(52,$i)->getValue();
						$p_comline4  = $objWorksheet->getCellByColumnAndRow(53,$i)->getValue();
						
						//Get Data of Brand/Category/Subcategory
						$brad = $this->products_model->getBrandByName(trim($p_brand));
						$catd = $this->products_model->getCategoryByName(trim($p_category));
						$subd = $this->products_model->getSubcategoryByName(trim($p_line));
						//Combo Item 1
						$catd1 = $this->products_model->getCategoryByName(trim($p_comcate1));
						$subd1 = $this->products_model->getSubcategoryByName(trim($p_comline1));
						//Combo Item 2
						$catd2 = $this->products_model->getCategoryByName(trim($p_comcate2));
						$subd2 = $this->products_model->getSubcategoryByName(trim($p_comline2));
						//Combo Item 3
						$catd3 = $this->products_model->getCategoryByName(trim($p_comcate3));
						$subd3 = $this->products_model->getSubcategoryByName(trim($p_comline3));
						//Combo Item 4
						$catd4 = $this->products_model->getCategoryByName(trim($p_comcate4));
						$subd4 = $this->products_model->getSubcategoryByName(trim($p_comline4));
						
						$unit = $this->products_model->getUnitByName(trim($p_units));
						$rate = $this->products_model->getRate(trim($p_tax));
						$case = $this->products_model->getCaseByName(trim($p_case));
						$diameter = $this->products_model->getDiameterByName(trim($p_dialmeter));
						$dial = $this->products_model->getDialByName(trim($p_dial));
						$strap = $this->products_model->getStrapByName(trim($p_strap));
						$water = $this->products_model->getWaterRByName(trim($p_water));
						$wind = $this->products_model->getWindingByName(trim($p_winding));
						$power = $this->products_model->getPowerReserveByName(trim($p_power));
						$buckle = $this->products_model->getBuckleByName(trim($p_buckle));
						$compli = $this->products_model->getComplicationByName(trim($p_compicate));
						
						if (empty($brad) and $p_brand != '' ){
							$brand[] = array('name'=>$p_brand);
						}
						if (empty($catd) and $p_category != '') {
							$category[] = array('brand_id'=> $p_brand,'name'=>$p_category);
							
						}
						if (empty($subd) and $p_line != '') {
							$subcategory[] = array('brand_id'=> $p_brand, 'category_id'=>$p_category, 'name'=>$p_line);
						}
						
						//Check Combo Item 1
						if (empty($catd1) and $p_comcate1 != '') {
							$category[] = array('brand_id'=> $p_brand,'name'=>$p_comcate1);
						}
						if (empty($subd1) and $p_comline1 != '') {
							$subcategory[] = array('brand_id'=> $p_brand, 'category_id'=>$p_comcate1, 'name'=>$p_comline1);
						}
						//Check Combo Item 2
						if (empty($catd2) and $p_comcate2 != '') {
							$category[] = array('brand_id'=> $p_brand,'name'=>$p_comcate2);
						}
						if (empty($subd2) and $p_comline2 != '') {
							$subcategory[] = array('brand_id'=> $p_brand, 'category_id'=>$p_comcate2, 'name'=>$p_comline2);
						}
						//Check Combo Item 3
						if (empty($catd3) and $p_comcate3 != '') {
							$category[] = array('brand_id'=> $p_brand,'name'=>$p_comcate3);
						}
						if (empty($subd3) and $p_comline3 != '') {
							$subcategory[] = array('brand_id'=> $p_brand, 'category_id'=>$p_comcate3, 'name'=>$p_comline3);
						}
						//Check Combo Item 3
						if (empty($catd4) and $p_comcate4 != '') {
							$category[] = array('brand_id'=> $p_brand,'name'=>$p_comcate4);
						}
						if (empty($subd4) and $p_comline4 != '') {
							$subcategory[] = array('brand_id'=> $p_brand, 'category_id'=>$p_comcate4, 'name'=>$p_comline4);
						}

						if (empty($unit) and $p_units != '') {
							$units[] = array('name'=>$p_units);
						}
						if (empty($case) and $p_case != '') {
							$cases[] = array('name'=>$p_case);
						}
						if (empty($diameter) and $p_dialmeter != '') {
							$diameters[] = array('name'=>$p_dialmeter);
						}
						if (empty($dial) and $p_dial != '') {
							$dials[] = array('name'=>$p_dial);
						}
						if (empty($strap) and $p_strap != '') {
							$straps[] = array('name'=>$p_strap);
						}
						if (empty($water) and $p_water != '') {
							$waters[] = array('name'=>$p_water);
						}
						if (empty($wind) and $p_winding != '') {
							$winds[] = array('name'=>$p_winding);
						}
						if (empty($power) and $p_power != '') {
							$powers[] = array('name'=>$p_power);
						}
						if (empty($buckle) and $p_buckle != '') {
							$buckles[] = array('name'=>$p_buckle);
						}
						if (empty($compli) and $p_compicate != '') {
							$complication[] = array('name'=>$p_compicate);
						}
						
						$pr_code[] 	= trim($p_reference);
						$pr_name[] 	= trim($p_name);
						$pr_codec[] = trim($p_reference).'-combo';
						$pr_namec[] = trim($p_name).'-combo';
						$pr_cat[] 	= trim($p_category);
						$pr_variants[] = trim($p_variant);
						$pr_unit[] 	= trim($p_units);
						$tax_method[]  = $p_method == 'exclusive' ? 1 : 0;
						$prsubcat = trim($p_line);
						$pr_subcat[] = trim($p_line);
						$pr_cost[] = trim($p_cost);
						$pr_price[] = trim($p_price);
						$pr_aq[] = trim($p_alertQ);
						$tax_details = $this->products_model->getTaxRateByName(trim($p_tax));
						$pr_tax[] = $rate ? $rate->id : NULL;
						
						$supplier1[] = trim($p_supplier1);
						$supplier2[] = trim($p_supplier2);
						$supplier3[] = trim($p_supplier3);
						$supplier4[] = trim($p_supplier4);
						$supplier5[] = trim($p_supplier5);
						
						$cf1[] = trim($p_case);
						$cf2[] = trim($p_dialmeter);
						$cf3[] = trim($p_dial);
						$cf4[] = trim($p_strap);
						$cf5[] = trim($p_water);
						$cf6[] = trim($p_winding);
						$cf7[] = trim($p_power);
						$cf8[] = trim($p_buckle);
						$cf9[] = trim($p_compicate);
						
						$image[] = $this->erp->convertImageSpecialChar($p_image);
						
						$description[] = trim($p_detail);
						
						$brand_id[] = trim($p_brand);
						
						$is_serial[] = trim($p_serial);
						
						if(trim($p_serial)){
							$serial_number[] = array('code' => trim($p_reference), 'serial_number'=>trim($p_serial), 'serial_status' => 0);
						}
						
						if(trim($p_type) == 'combo'){
							$type_com[] = 'combo';
						}
						if(trim($p_type) == 'standard'){
							$type[] = 'standard';
						}
						
						$combo_type1[]   = 'standard';
						$combo_item1[]   = trim($p_comitem1);
						$combo_code1[]   = trim($p_comcode1);
						$combo_unit1[]   = trim($p_units);
						$combo_cat1[] 	 = trim($p_comcate1);
						$combo_bra1[] 	 = trim($p_brand);
						$combo_subcat1[] = trim($p_comline1);
						$combo_price1[]  = trim($p_comprice1);
						$combo_image1[]  = trim($p_comimage1);
						
						$combo_type2[]   = 'standard';
						$combo_item2[]   = trim($p_comitem2);
						$combo_code2[]   = trim($p_comcode2);
						$combo_unit2[]   = trim($p_units);
						$combo_cat2[] 	 = trim($p_comcate2);
						$combo_bra2[] 	 = trim($p_brand);
						$combo_subcat2[] = trim($p_comline2);
						$combo_price2[]  = trim($p_comprice2);
						$combo_image2[]  = trim($p_comimage2);
						
						$combo_type3[]   = 'standard';
						$combo_item3[]   = trim($p_comitem3);
						$combo_code3[]   = trim($p_comcode3);
						$combo_unit3[]   = trim($p_units);
						$combo_cat3[] 	 = trim($p_comcate3);
						$combo_bra3[] 	 = trim($p_brand);
						$combo_subcat3[] = trim($p_comline3);
						$combo_price3[]  = trim($p_comprice3);
						$combo_image3[]  = trim($p_comimage3);
						
						$combo_type4[]   = 'standard';
						$combo_item4[]   = trim($p_comitem4);
						$combo_code4[]   = trim($p_comcode4);
						$combo_unit4[]   = trim($p_units);
						$combo_cat4[] 	 = trim($p_comcate4);
						$combo_bra4[] 	 = trim($p_brand);
						$combo_subcat4[] = trim($p_comline4);
						$combo_price4[]  = trim($p_comprice4);
						$combo_image4[]  = trim($p_comimage4);
						
						$combo_price[] 	 = trim($p_price) + trim($p_comprice1) + trim($p_comprice2) + trim($p_comprice3) + trim($p_comprice4);
							
						$combo_cost[]    = $p_cost;
						
						$rw++;
					}
					
				}
			}
			$ikeys = array('code', 'name', 'category_id', 'unit', 'cost', 'price', 'alert_quantity', 'tax_rate', 'tax_method', 'subcategory_id', 'variants', 'supplier1','supplier2','supplier3', 'supplier4', 'supplier5', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6', 'cf7', 'cf8', 'cf9', 'brand_id', 'is_serial', 'type', 'pcost', 'pprice', 'product_details', 'image', 'ccode1', 'cprice1', 'ccode2', 'cprice2', 'ccode3', 'cprice3', 'ccode4', 'cprice4');
			
			$icoms = array('type', 'name', 'code', 'unit', 'category_id', 'brand_id', 'subcategory_id', 'price', 'image', 'alert_quantity', 'tax_rate', 'tax_method', 'supplier1','supplier2','supplier3', 'supplier4', 'supplier5');

			$combo1 = array();
			foreach (array_map(null, $combo_type1, $combo_item1, $combo_code1, $combo_unit1, $combo_cat1, $combo_bra1, $combo_subcat1, $combo_price1, $combo_image1, $pr_aq, $pr_tax, $tax_method, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5) as $icom => $value) {
                $combo1[] = array_combine($icoms, $value);
            }
			
			$combo2 = array();
			foreach (array_map(null, $combo_type2, $combo_item2, $combo_code2, $combo_unit2, $combo_cat2, $combo_bra2, $combo_subcat2, $combo_price2, $combo_image2, $pr_aq, $pr_tax, $tax_method, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5) as $icom => $value) {
                $combo2[] = array_combine($icoms, $value);
            }
			
			$combo3 = array();
			foreach (array_map(null, $combo_type3, $combo_item3, $combo_code3, $combo_unit3, $combo_cat3, $combo_bra3, $combo_subcat3, $combo_price3, $combo_image3, $pr_aq, $pr_tax, $tax_method, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5) as $icom => $value) {
                $combo3[] = array_combine($icoms, $value);
            }
			
			$combo4 = array();
			foreach (array_map(null, $combo_type4, $combo_item4, $combo_code4, $combo_unit4, $combo_cat4, $combo_bra4, $combo_subcat4, $combo_price4, $combo_image4, $pr_aq, $pr_tax, $tax_method, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5) as $icom => $value) {
                $combo4[] = array_combine($icoms, $value);
            }
			
			$combo_item = array_merge_recursive($combo1, $combo2, $combo3, $combo4);
			
			$combo = array();
			
			if(!empty($type_com)){
				foreach (array_map(null, $pr_codec, $pr_namec, $pr_cat, $pr_unit, $combo_cost, $combo_price, $pr_aq, $pr_tax, $tax_method, $pr_subcat, $pr_variants, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5 ,$cf1, $cf2, $cf3, $cf4, $cf5, $cf6, $cf7, $cf8, $cf9, $brand_id, $is_serial, $type_com, $pr_cost, $pr_price, $description, $image, $combo_code1, $combo_price1, $combo_code2, $combo_price2, $combo_code3, $combo_price3, $combo_code4, $combo_price4) as $ikey => $value) {
					$combo[] = array_combine($ikeys, $value);
				}
			}
			$items = array();
			if(!empty($type)){
				foreach (array_map(null, $pr_code, $pr_name, $pr_cat, $pr_unit, $pr_cost, $pr_price, $pr_aq, $pr_tax, $tax_method, $pr_subcat, $pr_variants, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5 ,$cf1, $cf2, $cf3, $cf4, $cf5, $cf6, $cf7, $cf8, $cf9, $brand_id, $is_serial, $type, $pr_cost, $pr_price, $description, $image, $combo_code1, $combo_price1, $combo_code2, $combo_price2, $combo_code3, $combo_price3, $combo_code4, $combo_price4) as $ikey => $value) {
					$items[] = array_combine($ikeys, $value);
				}
			}
			
        }

        if ($this->form_validation->run() == true && $this->products_model->add_products($serial_number, $items, $combo, array_unique($combo_item, SORT_REGULAR), trim($p_type), array_unique($brand, SORT_REGULAR), array_unique($category, SORT_REGULAR),array_unique($subcategory, SORT_REGULAR), array_unique($units, SORT_REGULAR),array_unique($cases, SORT_REGULAR), array_unique($diameters, SORT_REGULAR), array_unique($dials, SORT_REGULAR), array_unique($straps, SORT_REGULAR), array_unique($waters, SORT_REGULAR), array_unique($winds, SORT_REGULAR), array_unique($powers, SORT_REGULAR), array_unique($buckles, SORT_REGULAR), array_unique($complication, SORT_REGULAR) )) {
            $this->session->set_flashdata('message', lang("products_added"));
            redirect('products');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('import_products')));
            $meta = array('page_title' => lang('import_products'), 'bc' => $bc);
            $this->page_construct('products/import_csv', $meta, $this->data);

        }
    }

    /* ---------------------------------------------------------------------------------------------- */
	function import_product_detail()
    {   		
		$this->erp->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {

                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("products/import_product_detail");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'product_details');

                $final = array();

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }				
				
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if($this->products_model->getProductByCode(trim($csv_pr['code']))) {
						$detail = array(
							'product_details' => trim($csv_pr['product_details'])
						);
						$this->products_model->updateProductDetail($detail, trim($csv_pr['code']));
						
					}
					else{
						$this->session->set_flashdata('error', lang("check_product_detail")."  ".lang("line_no") . " " . $rw);
                        redirect("products/import_product_detail");
					}
                    $rw++;
                }
            }            
        }
        if ($this->form_validation->run() == true){
            $this->session->set_flashdata('message', lang("products_added"));
			redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('import_product_detail')));
            $meta = array('page_title' => lang('import_product_detail'), 'bc' => $bc);
            $this->page_construct('products/import_product_detail', $meta, $this->data);

        }
    }
    
	function import_product_serial()
    {   		
		$this->erp->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {

                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("products/import_product_detail");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'product_serial');

                $final = array();

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }	
                $rw = 2;
				$pcode = array();
                foreach ($final as $csv_pr) {
                    $code = $this->products_model->getProductByCode(trim($csv_pr['code']));
					$pcode[] = $csv_pr['code'];
                    $rw++;
                }
				$vals = array_count_values($pcode);
				foreach ($final as $csv_pr) {
                    $product = $this->products_model->getProductByCode(trim($csv_pr['code']));
					$databaseQty = $product->quantity;
					$inputQty    = $vals[$csv_pr['code']];
					
					if($inputQty > $databaseQty){
						$this->session->set_flashdata('message', lang("quantity_of") . " (" . $csv_pr['code'] . ") " . lang("is_bigger") );
                        redirect("products/import_product_serial");
					}
					
					if($this->products_model->checkserial($product->id)){
						$this->session->set_flashdata('message', lang("this_code") . " (" . $csv_pr['code'] . ") " . lang("is_still") );
                        redirect("products/import_product_serial");
					}
					
					$data[] = array(
						'product_id'    => $product->id,
						'serial_number' => $csv_pr['product_serial'],
						'serial_status' => 0
					);
					
                }
            }            
        }
        if ($this->form_validation->run() == true  && $this->products_model->insertSerials($data)){
            $this->session->set_flashdata('message', lang("product_serial_added"));
			redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('import_product_serial')));
            $meta = array('page_title' => lang('import_product_serial'), 'bc' => $bc);
            $this->page_construct('products/import_product_serial', $meta, $this->data);

        }
    }
    
    function update_products()
    {
        $this->erp->checkPermissions('import', true, 'products');
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
                $upload_data = $this->upload->data();
                $file_name = $upload_data['file_name']; 
                $extension=$upload_data['file_ext']; 

                $objReader= PHPExcel_IOFactory::createReader('Excel2007');
                $objReader->setReadDataOnly(true);  
                $objPHPExcel=$objReader->load('./assets/uploads/excel/'.$file_name);         
                $totalrows=$objPHPExcel->setActiveSheetIndex(0)->getHighestRow();        
                $objWorksheet=$objPHPExcel->setActiveSheetIndex(0);
                
                $brand = $category = $subcategory = array();

				//########### Prevent from duplicated code
				for($i = 2; $i <= $totalrows; $i++)
				{
					$p_reference = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();

                    if($p_reference != ""){
    					if (!$this->products_model->getProductByCode(trim($p_reference))) {
    						$this->session->set_flashdata('error', lang("product_code") . " (" . $p_reference . "). " . lang("not_yet_inventory"));
    						redirect("products/update_products");
    					}
    					$p_comcode1 = $objWorksheet->getCellByColumnAndRow(31,$i)->getValue();
    					if($p_comcode1){
    						if (!$this->products_model->getProductByCode(trim($p_comcode1))) {
    							$this->session->set_flashdata('error', lang("combo_price1") . " (" . $p_comcode1 . "). " . lang("not_yet_inventory"));
    							redirect("products/update_products");
    						}
    					}
    					$p_comcode2 = $objWorksheet->getCellByColumnAndRow(37,$i)->getValue();
    					if($p_comcode2){
    						if (!$this->products_model->getProductByCode(trim($p_comcode2))) {
    							$this->session->set_flashdata('error', lang("combo_price2") . " (" . $p_comcode2 . "). " . lang("not_yet_inventory"));
    							redirect("products/update_products");
    						}
    					}
    					$p_comcode3 = $objWorksheet->getCellByColumnAndRow(43,$i)->getValue();
    					if($p_comcode3){
    						if (!$this->products_model->getProductByCode(trim($p_comcode3))) {
    							$this->session->set_flashdata('error', lang("combo_price3") . " (" . $p_comcode3 . "). " . lang("not_yet_inventory"));
    							redirect("products/update_products");
    						}
    					}
    					$p_comcode4 = $objWorksheet->getCellByColumnAndRow(49,$i)->getValue();
    					if($p_comcode4){
    						if (!$this->products_model->getProductByCode(trim($p_comcode4))) {
    							$this->session->set_flashdata('error', lang("combo_price4") . " (" . $p_comcode4 . "). " . lang("not_yet_inventory"));
    							redirect("products/update_products");
    						}
    					}
                        $array[] = trim($p_reference);
                    }

				}
				$count_values = array();
                foreach ($array as $a) {

                     @$count_values[$a]++;

                }
                foreach($count_values as $key => $value){
                    if($value > 1){
                        $this->session->set_flashdata('error', lang("prodcut_code")." (".$key.") ".lang(" is_duplicate"));
                        redirect("products/update_products");
                    }
                }
               
				//########### End Prevent
				
                for($i=2;$i<=$totalrows;$i++)
                {
                    $p_type = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
                    $rw = 2;
                    if(isset($p_type)){
                        $p_reference = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
                        $p_name      = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
                        $p_brand     = $objWorksheet->getCellByColumnAndRow(3,$i)->getValue();
                        $p_category  = $objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
                        $p_units     = $objWorksheet->getCellByColumnAndRow(5,$i)->getValue();
                        $p_cost      = $objWorksheet->getCellByColumnAndRow(6,$i)->getValue();
                        $p_price     = $objWorksheet->getCellByColumnAndRow(7,$i)->getValue();
                        $p_alertQ    = $objWorksheet->getCellByColumnAndRow(8,$i)->getValue();
                        $p_tax       = $objWorksheet->getCellByColumnAndRow(9,$i)->getValue();
                        $p_method    = $objWorksheet->getCellByColumnAndRow(10,$i)->getValue();
                        $p_line      = $objWorksheet->getCellByColumnAndRow(11,$i)->getValue();
                        $p_variant   = $objWorksheet->getCellByColumnAndRow(12,$i)->getValue();
                        $p_supplier1 = $objWorksheet->getCellByColumnAndRow(13,$i)->getValue();
                        $p_supplier2 = $objWorksheet->getCellByColumnAndRow(14,$i)->getValue();
                        $p_supplier3 = $objWorksheet->getCellByColumnAndRow(15,$i)->getValue();
                        $p_supplier4 = $objWorksheet->getCellByColumnAndRow(16,$i)->getValue();
                        $p_supplier5 = $objWorksheet->getCellByColumnAndRow(17,$i)->getValue();
                        $p_serial    = $objWorksheet->getCellByColumnAndRow(18,$i)->getValue();
                        $p_case      = $objWorksheet->getCellByColumnAndRow(19,$i)->getValue();
                        $p_dialmeter = $objWorksheet->getCellByColumnAndRow(20,$i)->getValue();
                        $p_dial      = $objWorksheet->getCellByColumnAndRow(21,$i)->getValue();
                        $p_strap     = $objWorksheet->getCellByColumnAndRow(22,$i)->getValue();
                        $p_water     = $objWorksheet->getCellByColumnAndRow(23,$i)->getValue();
                        $p_winding   = $objWorksheet->getCellByColumnAndRow(24,$i)->getValue();
                        $p_power     = $objWorksheet->getCellByColumnAndRow(25,$i)->getValue();
                        $p_buckle    = $objWorksheet->getCellByColumnAndRow(26,$i)->getValue();
                        $p_compicate = $objWorksheet->getCellByColumnAndRow(27,$i)->getValue();
                        $p_detail    = $objWorksheet->getCellByColumnAndRow(28,$i)->getValue();
                        $p_image     = $objWorksheet->getCellByColumnAndRow(29,$i)->getValue();
                        
                        $p_comitem1  = $objWorksheet->getCellByColumnAndRow(30,$i)->getValue();
                        $p_comcode1  = $objWorksheet->getCellByColumnAndRow(31,$i)->getValue();
                        $p_comprice1 = $objWorksheet->getCellByColumnAndRow(32,$i)->getValue();
                        $p_comimage1 = $objWorksheet->getCellByColumnAndRow(33,$i)->getValue();
                        $p_comcate1  = $objWorksheet->getCellByColumnAndRow(34,$i)->getValue();
                        $p_comline1  = $objWorksheet->getCellByColumnAndRow(35,$i)->getValue();
                        
                        $p_comitem2  = $objWorksheet->getCellByColumnAndRow(36,$i)->getValue();
                        $p_comcode2  = $objWorksheet->getCellByColumnAndRow(37,$i)->getValue();
                        $p_comprice2 = $objWorksheet->getCellByColumnAndRow(38,$i)->getValue();
                        $p_comimage2 = $objWorksheet->getCellByColumnAndRow(39,$i)->getValue();
                        $p_comcate2  = $objWorksheet->getCellByColumnAndRow(40,$i)->getValue();
                        $p_comline2  = $objWorksheet->getCellByColumnAndRow(41,$i)->getValue();
                        
                        $p_comitem3  = $objWorksheet->getCellByColumnAndRow(42,$i)->getValue();
                        $p_comcode3  = $objWorksheet->getCellByColumnAndRow(43,$i)->getValue();
                        $p_comprice3 = $objWorksheet->getCellByColumnAndRow(44,$i)->getValue();
                        $p_comimage3 = $objWorksheet->getCellByColumnAndRow(45,$i)->getValue();
                        $p_comcate3  = $objWorksheet->getCellByColumnAndRow(46,$i)->getValue();
                        $p_comline3  = $objWorksheet->getCellByColumnAndRow(47,$i)->getValue();
                        
                        $p_comitem4  = $objWorksheet->getCellByColumnAndRow(48,$i)->getValue();
                        $p_comcode4  = $objWorksheet->getCellByColumnAndRow(49,$i)->getValue();
                        $p_comprice4 = $objWorksheet->getCellByColumnAndRow(50,$i)->getValue();
                        $p_comimage4 = $objWorksheet->getCellByColumnAndRow(51,$i)->getValue();
                        $p_comcate4  = $objWorksheet->getCellByColumnAndRow(52,$i)->getValue();
                        $p_comline4  = $objWorksheet->getCellByColumnAndRow(53,$i)->getValue();
                        
                        //Get Data of Brand/Category/Subcategory
                        $brad 		= $this->products_model->getBrandByName(trim($p_brand));
                        $catd 		= $this->products_model->getCategoryByName(trim($p_category));
                        $subd 		= $this->products_model->getSubcategoryByName(trim($p_line));
                        //Combo Item 1
                        $catd1 		= $this->products_model->getCategoryByName(trim($p_comcate1));
                        $subd1 		= $this->products_model->getSubcategoryByName(trim($p_comline1));
                        //Combo Item 2
                        $catd2 		= $this->products_model->getCategoryByName(trim($p_comcate2));
                        $subd2 		= $this->products_model->getSubcategoryByName(trim($p_comline2));
                        //Combo Item 3
                        $catd3 		= $this->products_model->getCategoryByName(trim($p_comcate3));
                        $subd3 		= $this->products_model->getSubcategoryByName(trim($p_comline3));
                        //Combo Item 4
                        $catd4 		= $this->products_model->getCategoryByName(trim($p_comcate4));
                        $subd4 		= $this->products_model->getSubcategoryByName(trim($p_comline4));
                        
                        $unit 		= $this->products_model->getUnitByName(trim($p_units));
                        $rate 		= $this->products_model->getRate(trim($p_tax));
                        $case 		= $this->products_model->getCaseByName(trim($p_case));
                        $diameter 	= $this->products_model->getDiameterByName(trim($p_dialmeter));
                        $dial 		= $this->products_model->getDialByName(trim($p_dial));
                        $strap 		= $this->products_model->getStrapByName(trim($p_strap));
                        $water 		= $this->products_model->getWaterRByName(trim($p_water));
                        $wind 		= $this->products_model->getWindingByName(trim($p_winding));
                        $power 		= $this->products_model->getPowerReserveByName(trim($p_power));
                        $buckle 	= $this->products_model->getBuckleByName(trim($p_buckle));
                        $compli 	= $this->products_model->getComplicationByName(trim($p_compicate));
                        
                        if (empty($brad) and $p_brand != '' ){
                            $brand[] = array('name'=>$p_brand);
                        }
                        if (empty($catd) and $p_category != '') {
                            $category[] = array('brand_id'=> $p_brand,'name'=>$p_category);
                            
                        }
                        if (empty($subd) and $p_line != '') {
                            $subcategory[] = array('brand_id'=> $p_brand, 'category_id'=>$p_category, 'name'=>$p_line);
                        }
                        
                        //Check Combo Item 1
                        if (empty($catd1) and $p_comcate1 != '') {
                            $category[] = array('brand_id'=> $p_brand,'name'=>$p_comcate1);
                        }
                        if (empty($subd1) and $p_comline1 != '') {
                            $subcategory[] = array('brand_id'=> $p_brand, 'category_id'=>$p_comcate1, 'name'=>$p_comline1);
                        }
                        //Check Combo Item 2
                        if (empty($catd2) and $p_comcate2 != '') {
                            $category[] = array('brand_id'=> $p_brand,'name'=>$p_comcate2);
                        }
                        if (empty($subd2) and $p_comline2 != '') {
                            $subcategory[] = array('brand_id'=> $p_brand, 'category_id'=>$p_comcate2, 'name'=>$p_comline2);
                        }
                        //Check Combo Item 3
                        if (empty($catd3) and $p_comcate3 != '') {
                            $category[] = array('brand_id'=> $p_brand,'name'=>$p_comcate3);
                        }
                        if (empty($subd3) and $p_comline3 != '') {
                            $subcategory[] = array('brand_id'=> $p_brand, 'category_id'=>$p_comcate3, 'name'=>$p_comline3);
                        }
                        //Check Combo Item 3
                        if (empty($catd4) and $p_comcate4 != '') {
                            $category[] = array('brand_id'=> $p_brand,'name'=>$p_comcate4);
                        }
                        if (empty($subd4) and $p_comline4 != '') {
                            $subcategory[] = array('brand_id'=> $p_brand, 'category_id'=>$p_comcate4, 'name'=>$p_comline4);
                        }

                        if (empty($unit) and $p_units != '') {
                            $units[] = array('name'=>$p_units);
                        }
                        if (empty($case) and $p_case != '') {
                            $cases[] = array('name'=>$p_case);
                        }
                        if (empty($diameter) and $p_dialmeter != '') {
                            $diameters[] = array('name'=>$p_dialmeter);
                        }
                        if (empty($dial) and $p_dial != '') {
                            $dials[] = array('name'=>$p_dial);
                        }
                        if (empty($strap) and $p_strap != '') {
                            $straps[] = array('name'=>$p_strap);
                        }
                        if (empty($water) and $p_water != '') {
                            $waters[] = array('name'=>$p_water);
                        }
                        if (empty($wind) and $p_winding != '') {
                            $winds[] = array('name'=>$p_winding);
                        }
                        if (empty($power) and $p_power != '') {
                            $powers[] = array('name'=>$p_power);
                        }
                        if (empty($buckle) and $p_buckle != '') {
                            $buckles[] = array('name'=>$p_buckle);
                        }
                        if (empty($compli) and $p_compicate != '') {
                            $complication[] = array('name'=>$p_compicate);
                        }
                        
                        $pr_code[]  = trim($p_reference);
                        $pr_name[]  = trim($p_name);
                        $pr_codec[] = trim($p_reference).'-combo';
                        $pr_namec[] = trim($p_name).'-combo';
                        $pr_cat[]   = trim($p_category);
                        $pr_variants[] = trim($p_variant);
                        $pr_unit[]  = trim($p_units);
                        $tax_method[]  = $p_method == 'exclusive' ? 1 : 0;
                        $prsubcat = trim($p_line);
                        $pr_subcat[] = trim($p_line);
                        $pr_cost[] = trim($p_cost);
                        $pr_price[] = trim($p_price);
                        $pr_aq[] = trim($p_alertQ);
                        $tax_details = $this->products_model->getTaxRateByName(trim($p_tax));
                        $pr_tax[] = $rate ? $rate->id : NULL;
                        
                        $supplier1[] = trim($p_supplier1);
                        $supplier2[] = trim($p_supplier2);
                        $supplier3[] = trim($p_supplier3);
                        $supplier4[] = trim($p_supplier4);
                        $supplier5[] = trim($p_supplier5);
                        
                        $cf1[] = trim($p_case);
                        $cf2[] = trim($p_dialmeter);
                        $cf3[] = trim($p_dial);
                        $cf4[] = trim($p_strap);
                        $cf5[] = trim($p_water);
                        $cf6[] = trim($p_winding);
                        $cf7[] = trim($p_power);
                        $cf8[] = trim($p_buckle);
                        $cf9[] = trim($p_compicate);
                        
                        $image[] = $this->erp->convertImageSpecialChar($p_image);
                        
                        $description[] = trim($p_detail);
                        
                        $brand_id[] = trim($p_brand);
                        
                        $is_serial[] = trim($p_serial);
                        
                        if(trim($p_serial)){
                            $serial_number[] = array('code' => trim($p_reference), 'serial_number'=>trim($p_serial), 'serial_status' => 0);
                        }
                        
                        if(trim($p_type) == 'combo'){
                            $type_com[] = 'combo';
                        }
                        if(trim($p_type) == 'standard'){
                            $type[] = 'standard';
                        }
                        
                        $combo_type1[]   = 'standard';
                        $combo_item1[]   = trim($p_comitem1);
                        $combo_code1[]   = trim($p_comcode1);
                        $combo_unit1[]   = trim($p_units);
                        $combo_cat1[]    = trim($p_comcate1);
                        $combo_bra1[]    = trim($p_brand);
                        $combo_subcat1[] = trim($p_comline1);
                        $combo_price1[]  = trim($p_comprice1);
                        $combo_image1[]  = trim($p_comimage1);
                        
                        $combo_type2[]   = 'standard';
                        $combo_item2[]   = trim($p_comitem2);
                        $combo_code2[]   = trim($p_comcode2);
                        $combo_unit2[]   = trim($p_units);
                        $combo_cat2[]    = trim($p_comcate2);
                        $combo_bra2[]    = trim($p_brand);
                        $combo_subcat2[] = trim($p_comline2);
                        $combo_price2[]  = trim($p_comprice2);
                        $combo_image2[]  = trim($p_comimage2);
                        
                        $combo_type3[]   = 'standard';
                        $combo_item3[]   = trim($p_comitem3);
                        $combo_code3[]   = trim($p_comcode3);
                        $combo_unit3[]   = trim($p_units);
                        $combo_cat3[]    = trim($p_comcate3);
                        $combo_bra3[]    = trim($p_brand);
                        $combo_subcat3[] = trim($p_comline3);
                        $combo_price3[]  = trim($p_comprice3);
                        $combo_image3[]  = trim($p_comimage3);
                        
                        $combo_type4[]   = 'standard';
                        $combo_item4[]   = trim($p_comitem4);
                        $combo_code4[]   = trim($p_comcode4);
                        $combo_unit4[]   = trim($p_units);
                        $combo_cat4[]    = trim($p_comcate4);
                        $combo_bra4[]    = trim($p_brand);
                        $combo_subcat4[] = trim($p_comline4);
                        $combo_price4[]  = trim($p_comprice4);
                        $combo_image4[]  = trim($p_comimage4);
                        
                        $combo_price[]   = trim($p_price) + trim($p_comprice1) + trim($p_comprice2) + trim($p_comprice3) + trim($p_comprice4);
                            
                        $combo_cost[] 	 = $p_cost;
                        
                        $rw++;
                    }
                    
                }
            }
            $ikeys = array('code', 'name', 'category_id', 'unit', 'cost', 'price', 'alert_quantity', 'tax_rate', 'tax_method', 'subcategory_id', 'variants', 'supplier1','supplier2','supplier3', 'supplier4', 'supplier5', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6', 'cf7', 'cf8', 'cf9', 'brand_id', 'is_serial', 'type', 'pcost', 'pprice', 'product_details', 'image', 'ccode1', 'cprice1', 'ccode2', 'cprice2', 'ccode3', 'cprice3', 'ccode4', 'cprice4');
            
            $icoms = array('type', 'name', 'code', 'unit', 'category_id', 'brand_id', 'subcategory_id', 'price', 'image', 'alert_quantity', 'tax_rate', 'tax_method', 'supplier1','supplier2','supplier3', 'supplier4', 'supplier5');

            $combo1 = array();
            foreach (array_map(null, $combo_type1, $combo_item1, $combo_code1, $combo_unit1, $combo_cat1, $combo_bra1, $combo_subcat1, $combo_price1, $combo_image1, $pr_aq, $pr_tax, $tax_method, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5) as $icom => $value) {
                $combo1[] = array_combine($icoms, $value);
            }
            
            $combo2 = array();
            foreach (array_map(null, $combo_type2, $combo_item2, $combo_code2, $combo_unit2, $combo_cat2, $combo_bra2, $combo_subcat2, $combo_price2, $combo_image2, $pr_aq, $pr_tax, $tax_method, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5) as $icom => $value) {
                $combo2[] = array_combine($icoms, $value);
            }
            
            $combo3 = array();
            foreach (array_map(null, $combo_type3, $combo_item3, $combo_code3, $combo_unit3, $combo_cat3, $combo_bra3, $combo_subcat3, $combo_price3, $combo_image3, $pr_aq, $pr_tax, $tax_method, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5) as $icom => $value) {
                $combo3[] = array_combine($icoms, $value);
            }
            
            $combo4 = array();
            foreach (array_map(null, $combo_type4, $combo_item4, $combo_code4, $combo_unit4, $combo_cat4, $combo_bra4, $combo_subcat4, $combo_price4, $combo_image4, $pr_aq, $pr_tax, $tax_method, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5) as $icom => $value) {
                $combo4[] = array_combine($icoms, $value);
            }
            
            $combo_item = array_merge_recursive($combo1, $combo2, $combo3, $combo4);
            
            $combo = array();
            
            if(!empty($type_com)){
                foreach (array_map(null, $pr_codec, $pr_namec, $pr_cat, $pr_unit, $combo_cost, $combo_price, $pr_aq, $pr_tax, $tax_method, $pr_subcat, $pr_variants, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5 ,$cf1, $cf2, $cf3, $cf4, $cf5, $cf6, $cf7, $cf8, $cf9, $brand_id, $is_serial, $type_com, $pr_cost, $pr_price, $description, $image, $combo_code1, $combo_price1, $combo_code2, $combo_price2, $combo_code3, $combo_price3, $combo_code4, $combo_price4) as $ikey => $value) {
                    $combo[] = array_combine($ikeys, $value);
                }
            }
            $items = array();
            if(!empty($type)){
                foreach (array_map(null, $pr_code, $pr_name, $pr_cat, $pr_unit, $pr_cost, $pr_price, $pr_aq, $pr_tax, $tax_method, $pr_subcat, $pr_variants, $supplier1, $supplier2, $supplier3, $supplier4, $supplier5 ,$cf1, $cf2, $cf3, $cf4, $cf5, $cf6, $cf7, $cf8, $cf9, $brand_id, $is_serial, $type, $pr_cost, $pr_price, $description, $image, $combo_code1, $combo_price1, $combo_code2, $combo_price2, $combo_code3, $combo_price3, $combo_code4, $combo_price4) as $ikey => $value) {
                    $items[] = array_combine($ikeys, $value);
                }
            }
            //$this->erp->print_arrays($combo);
        }

        if ($this->form_validation->run() == true && $this->products_model->update_products($serial_number, $items, $combo, array_unique($combo_item, SORT_REGULAR), trim($p_type), array_unique($brand, SORT_REGULAR), array_unique($category, SORT_REGULAR),array_unique($subcategory, SORT_REGULAR), array_unique($units, SORT_REGULAR),array_unique($cases, SORT_REGULAR), array_unique($diameters, SORT_REGULAR), array_unique($dials, SORT_REGULAR), array_unique($straps, SORT_REGULAR), array_unique($waters, SORT_REGULAR), array_unique($winds, SORT_REGULAR), array_unique($powers, SORT_REGULAR), array_unique($buckles, SORT_REGULAR), array_unique($complication, SORT_REGULAR) )) {
            $this->session->set_flashdata('message', lang("products_added"));
            redirect('products');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('update_products')));
            $meta = array('page_title' => lang('update_products'), 'bc' => $bc);
            $this->page_construct('products/import_update_products', $meta, $this->data);

        }
    }
	
    function upload_image(){
		
		$this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
			$this->load->library('upload');
			if ($_FILES['userfile']['name'][0] != "") {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 500;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);

                for ($i = 0; $i < $cpt; $i++) {
                    $_FILES['userfile']['name']     = $this->erp->convertImageSpecialChar($files['userfile']['name'][$i]);
                    $_FILES['userfile']['type']     = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error']    = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size']     = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect("products/upload_image");
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
                $config = NULL;
            } else {
                $photos = NULL;
            }
		}

		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('upload_image')));
            $meta = array('page_title' => lang('upload_image'), 'bc' => $bc);
			
		$this->page_construct('products/upload_image', $meta, $this->data);
	}
	
	function update_price()
    {
        $this->erp->checkPermissions('import', true, 'products');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (DEMO) {
                $this->session->set_flashdata('message', lang("disabled_in_demo"));
                redirect('welcome');
            }

            if (isset($_FILES["userfile"])) {

                $this->load->library('excel');
                $configUpload['upload_path'] = './assets/uploads/excel/';
                $configUpload['allowed_types'] = 'xls|xlsx|csv';
                $configUpload['max_size'] = '5000';
                $this->load->library('upload', $configUpload);
                $this->upload->do_upload('userfile');   
                $upload_data    = $this->upload->data();
                $file_name      = $upload_data['file_name']; 
                $extension      = $upload_data['file_ext']; 

                $objReader      = PHPExcel_IOFactory::createReader('Excel2007');
                $objReader->setReadDataOnly(true);  
                $objPHPExcel    = $objReader->load('./assets/uploads/excel/'.$file_name);         
                $totalrows      = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();        
                $objWorksheet   = $objPHPExcel->setActiveSheetIndex(0);
				
				//########### Prevent from duplicated code
				for($i = 2; $i <= $totalrows; $i++)
				{
					$p_code  = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();

                        if (!$this->products_model->getProductByCode(trim($p_code))) {
                            $this->session->set_flashdata('error', lang("product_code") . " (" . $p_code . "). " . lang("not_yet_inventory"));
                            redirect("products/update_price");
                        }
                        if($p_code != ""){
                            $array[] = trim($p_code);
                        }
                    			
				}
				$count_values = array();
				foreach ($array as $a) {

					 @$count_values[$a]++;

				}
				foreach($count_values as $key => $value){
					if($value > 1){
						$this->session->set_flashdata('error', lang("prodcut_code")." (".$key.") ".lang(" is_duplicate"));
						redirect("products/update_price");
					}
				}
				for($i=2;$i<=$totalrows;$i++)
                {
					$p_code  = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
					$p_cost  = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
					$p_price = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
					
					$code[]  = trim($p_code);
					$cost[]  = trim($p_cost);
					$price[] = trim($p_price);
					$ccode[] = trim($p_code.'-combo');
					
					if (!$this->products_model->getProductByCode(trim($p_code))) {
                        $this->session->set_flashdata('error', lang("check_product_code") . " (" . $p_code . "). " . lang("code_x_exist"));
                        redirect("products/update_price");
                    }
				}

                $keys = array('code', 'cost', 'price');
				
				foreach (array_map(null, $code, $cost, $price) as $key => $value) {
                    $items[] = array_combine($keys, $value);
                }
				
				foreach (array_map(null, $ccode, $cost, $price) as $key => $value) {
                    $combo[] = array_combine($keys, $value);
                }
				
            }

        }

        if ($this->form_validation->run() == true && !empty($items)) {
            $this->products_model->updatePrice($items, $combo);
            $this->session->set_flashdata('message', lang("price_updated"));
            redirect('products');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('import_price_cost')));
            $meta = array('page_title' => lang('import_price_cost'), 'bc' => $bc);
            $this->page_construct('products/update_price', $meta, $this->data);

        }
    }
	
	function update_quantity()
    {
		$this->erp->checkPermissions('import', true, 'products');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

		if ($this->form_validation->run() == true) {
			
			if (DEMO) {
				$this->session->set_flashdata('message', lang("disabled_in_demo"));
				redirect('welcome');
			}

			if (isset($_FILES["userfile"])) {

                $this->load->library('excel');
                $configUpload['upload_path'] = './assets/uploads/excel/';
                $configUpload['allowed_types'] = 'xls|xlsx|csv';
                $configUpload['max_size'] = '5000';
                $this->load->library('upload', $configUpload);
                $this->upload->do_upload('userfile');   
                $upload_data = $this->upload->data();
                $file_name = $upload_data['file_name']; 
                $extension=$upload_data['file_ext']; 

                $objReader= PHPExcel_IOFactory::createReader('Excel2007');
                $objReader->setReadDataOnly(true);  
                $objPHPExcel=$objReader->load('./assets/uploads/excel/'.$file_name);         
                $totalrows=$objPHPExcel->setActiveSheetIndex(0)->getHighestRow();        
                $objWorksheet=$objPHPExcel->setActiveSheetIndex(0);

                $arrResult = array();
                for($i=2;$i<=$totalrows;$i++)
                {
                    $p_reference = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
                    //########### Check if reference is in stock or not
                    if($p_reference){
						$product = $this->products_model->getProductByCode(trim($p_reference));
                        if (!$this->products_model->getProductByCode(trim($p_reference))) {
                            $this->session->set_flashdata('error', lang("product_code").' ('.$p_reference.') '.lang("not_yet_inventory"));
                            redirect("products/update_quantity");
                        }
                    }
                    
                    //########## End
					
					//================= Check Warehouse =================//
					$p_warehouse = $objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
					if($p_warehouse){
                        if (!$this->site->getWarehouseByID(trim($p_warehouse))) {
                            $this->session->set_flashdata('error', lang("product_code").' ('.$p_reference.') '.lang("ware_id").' '.lang("is_invalid"));
                            redirect("products/update_quantity");
                        }
                    }
					//======================= End =======================//
					
					//================= Check Warehouse =================//
					$p_serial   = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
					if($p_serial){
                        if ($this->products_model->checkSerialProdduct(trim($product->id), $p_serial)) {
                            $this->session->set_flashdata('error', lang("product_code").' ('.$p_reference.') '.lang("already_have").' ('.$p_serial.')');
                            redirect("products/update_quantity");
                        }
						if ($this->products_model->getSerialProducts($p_serial)) {
                            $this->session->set_flashdata('error', lang("this_serial").' ('.$p_serial.') '.lang("already_use"));
                            redirect("products/update_quantity");
                        }
                    }
					//======================= End =======================//
					
                    $p_quantity = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
                    $p_serial   = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
                    $p_option   = $objWorksheet->getCellByColumnAndRow(3,$i)->getValue();
                    $p_warehou  = $objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
                    $p_cost     = $objWorksheet->getCellByColumnAndRow(5,$i)->getValue();
                    if($p_reference){
                        $arrResult[] = array(
                            '0' => $p_reference,
                            '1' => $p_quantity,
                            '2' => $p_serial,
                            '3' => $p_option,
                            '4' => $p_warehou,
                            '5' => $p_cost
                        ); 
                    }
                    
                }

                //$this->erp->print_arrays($arrResult);
				$keys 				= array('code', 'quantity', 'cost');
				$keys_warehouse 	= array('quantity', 'warehouse_id');
				$keys_var 			= array('quantity', 'option_id','warehouse_id');
				$keys_purchase 		= array('product_code', 'quantity_balance','option_id','warehouse_id');
				$keys_serial 		= array('serial_number', 'warehouse');
				
				$final 				= array();
				$final_ware_product = array();
				$final_var 			= array();
				$final_purchase_item = array();
				$final_serial 		= array();
				$code_pro 			= array();
				$code 				= 0;
				
				foreach ($arrResult as $key => $value) {
					if($value[0] != ''){
                        $temp_product 	= $value;
						$temp_warehouse = $value;
						$temp_var 		= $value;
						$temp_key 		= $value;

						unset($temp_product[2]);
						unset($temp_product[3]);
						unset($temp_product[4]);
						
						unset($temp_warehouse[0]);
						unset($temp_warehouse[2]);
						unset($temp_warehouse[3]);
						unset($temp_warehouse[5]);
						
						unset($temp_var[0]);
						unset($temp_var[2]);
						unset($temp_var[5]);
						
						unset($temp_key[0]);
						unset($temp_key[1]);
						unset($temp_key[3]);
						unset($temp_key[5]);
						
						unset($value[2]);
						unset($value[5]);
						
						$final[] 				= array_combine($keys, $temp_product);
						$final_ware_product[] 	= array_combine($keys_warehouse, $temp_warehouse);
						$final_var[] 			= array_combine($keys_var, $temp_var);
						$final_purchase_item[] 	= array_combine($keys_purchase, $value);
						$final_serial[] 		= array_combine($keys_serial, $temp_key);
						
					}
				}
				$rw = 2;
				$i =0;
				
				foreach ($final as $csv_pr) {	
					if(trim($csv_pr['code']) != ""){
						$query_product = $this->products_model->getProductByCode(trim($csv_pr['code']));
					}

					$final_purchase_item[$i]['product_id'] 	= $query_product->id;
					$final_ware_product[$i]['product_id']  	= $query_product->id;
					$final_var[$i]['product_id'] 			= $query_product->id;	
					$query_product_var 						= $this->products_model->getOptionId($query_product->id,$final_var[$i]['option_id']);
					
					if($query_product_var){
                        $final_var[$i]['option_id'] 			= $query_product_var->id;
                        $final_purchase_item[$i]['option_id'] 	= $query_product_var->id;  
                    }

                    $final_purchase_item[$i]['cost'] 			= $csv_pr['cost']; 

					if($csv_pr['cost'] == ""){
                        $final_purchase_item[$i]['cost'] = 0;
                    }
					
					
					$final_serial[$i]['product_id'] 	= $query_product->id;
					$final_serial[$i]['serial_status'] 	= 1;
					$final_serial[$i]['biller_id'] 		= $this->default_biller_id;
					
					if (!$query_product ) {
						$this->session->set_flashdata('message', lang("check_product_code") . " (" . $csv_pr['code'] . "). " . lang("code_x_exist") . " " . lang("line_no") . " " . $rw);
						redirect("products/update_quantity");
					}
					$rw++;
					$i++;
				} 
				
				$out = array();
				$wwp = array();
				$fiv = array();
				$pi  = array();
				foreach ($final as $key => $value){
					if (array_key_exists($value['code'], $out)){
						$out[$value['code']]['code'] 		= $value['code'];
						$out[$value['code']]['quantity']   += $value['quantity'];
						$out[$value['code']]['cost'] 		= $value['cost'];
						
					} else {
						$out[$value['code']] = array(
							'code' 		=> $value['code'], 
							'quantity' 	=> $value['quantity'], 
							'cost' 		=> $value['cost']
						);
					}
				}
				$final_p = array_values($out);
				
				foreach ($final_ware_product as $key => $value){
					if (array_key_exists($value['product_id'], $wwp)){
						$wwp[$value['product_id']]['product_id'] 	= $value['product_id'];
						$wwp[$value['product_id']]['quantity'] 	   += $value['quantity'];
						$wwp[$value['product_id']]['warehouse_id'] 	= $value['warehouse_id'];
						
					} else {
						$wwp[$value['product_id']] = array(
							'product_id' 	=> $value['product_id'], 
							'quantity' 		=> $value['quantity'], 
							'warehouse_id' 	=> $value['warehouse_id']
						);
					}
				}
				$final_w = array_values($wwp);
				
				foreach ($final_var as $key => $value){
					if (array_key_exists($value['product_id'], $fiv)){
						$fiv[$value['product_id']]['quantity'] 		+= $value['quantity'];
						$fiv[$value['product_id']]['option_id'] 	= $value['option_id'];
						$fiv[$value['product_id']]['warehouse_id'] 	= $value['warehouse_id'];
						$fiv[$value['product_id']]['product_id'] 	= $value['product_id'];
						
					} else {
						$fiv[$value['product_id']] = array( 
							'quantity' 		=> $value['quantity'], 
							'option_id' 	=> $value['option_id'], 
							'warehouse_id' 	=> $value['warehouse_id'],
							'product_id' 	=> $value['product_id']
						);
					}
				}
				$final_v = array_values($fiv);
				
				foreach ($final_purchase_item as $key => $value){
					if (array_key_exists($value['product_code'], $pi) AND array_key_exists($value['warehouse_id'], $pi)){
						$pi[$value['product_code']]['product_code'] 	= $value['product_code'];
						$pi[$value['product_code']]['quantity_balance'] += $value['quantity_balance'];
						$pi[$value['product_code']]['option_id'] 		= $value['option_id'];
						$pi[$value['product_code']]['warehouse_id'] 	= $value['warehouse_id'];
						$pi[$value['product_code']]['product_id'] 		= $value['product_id'];
						$pi[$value['product_code']]['cost'] 			= $value['cost'];
					} else {
						$pi[$value['product_code']] = array( 
							'product_code'     	=> $value['product_code'], 
							'quantity_balance' 	=> $value['quantity_balance'], 
							'option_id' 		=> $value['option_id'],
							'warehouse_id' 		=> $value['warehouse_id'],
							'product_id' 		=> $value['product_id'],
							'cost' 				=> $value['cost']
						);
					}
				}
				$final_pi = array_values($pi);
				//$this->erp->print_arrays($final_purchase_item, $final_serial);
			}
		}
		
        if ($this->form_validation->run() == true && !empty($final)) {
            //$this->products_model->updateQuantityExcel($final_p);
			//$this->products_model->updateQuantityExcelWarehouse($final_w);
			//$this->products_model->updateQuantityExcelVar($final_v);
			$this->products_model->updateQuantityExcelPurchase($final_purchase_item);
			$this->products_model->insertSerialkey($final_serial);
            $this->session->set_flashdata('message', lang("quantity_updated"));
            redirect('products');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('update_quantity')));
            $meta = array('page_title' => lang('update_quantity'), 'bc' => $bc);
            $this->page_construct('products/update_quantity', $meta, $this->data);

        }
    }
	
	//============================= Import Update Serial
	
	function import_update_serial()
    {
        $this->erp->checkPermissions();
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

		if ($this->form_validation->run() == true) {
			
			if (DEMO) {
				$this->session->set_flashdata('message', lang("disabled_in_demo"));
				redirect('welcome');
			}

			if (isset($_FILES["userfile"])) {

                $this->load->library('excel');
                $configUpload['upload_path'] = './assets/uploads/excel/';
                $configUpload['allowed_types'] = 'xls|xlsx|csv';
                $configUpload['max_size'] = '5000';
                $this->load->library('upload', $configUpload);
                $this->upload->do_upload('userfile');   
                $upload_data = $this->upload->data();
                $file_name = $upload_data['file_name']; 
                $extension=$upload_data['file_ext']; 

                $objReader= PHPExcel_IOFactory::createReader('Excel2007');
                $objReader->setReadDataOnly(true);  
                $objPHPExcel=$objReader->load('./assets/uploads/excel/'.$file_name);         
                $totalrows=$objPHPExcel->setActiveSheetIndex(0)->getHighestRow();        
                $objWorksheet=$objPHPExcel->setActiveSheetIndex(0);

                $arrResult = array();
                for($i=2;$i<=$totalrows;$i++)
                {
                    $p_reference = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
                    //########### Check if reference is in stock or not
                    if($p_reference){
                        if (!$this->products_model->getProductByCode(trim($p_reference))) {
                            $this->session->set_flashdata('error', lang("product_code").' ('.$p_reference.') '.lang("not_yet_inventory"));
                            redirect("products/update_quantity");
                        }
                    }
                    
                    //########## End
                    $p_quantity = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
                    $p_serial   = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
                    $p_option   = $objWorksheet->getCellByColumnAndRow(3,$i)->getValue();
                    $p_warehou  = $objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
                    $p_cost     = $objWorksheet->getCellByColumnAndRow(5,$i)->getValue();
                    if($p_reference){
                        $arrResult[] = array(
                            '0' => $p_reference,
                            '1' => $p_quantity,
                            '2' => $p_serial,
                            '3' => $p_option,
                            '4' => $p_warehou,
                            '5' => $p_cost
                        ); 
                    }
                    
                }

                //$this->erp->print_arrays($arrResult);
				$keys = array('code', 'quantity', 'cost');
				$keys_warehouse = array('quantity', 'warehouse_id');
				$keys_var = array('quantity', 'option_id','warehouse_id');
				$keys_purchase = array('product_code', 'quantity_balance','option_id','warehouse_id');
				$keys_serial = array('serial_number', 'warehouse');
				
				$final = array();
				$final_ware_product = array();
				$final_var = array();
				$final_purchase_item = array();
				$final_serial = array();
				$code_pro = array();
				$code = 0;
				
				foreach ($arrResult as $key => $value) {
					if($value[0] != ''){
                        $temp_product = $value;
						$temp_warehouse = $value;
						$temp_var = $value;
						$temp_key = $value;

						unset($temp_product[2]);
						unset($temp_product[3]);
						unset($temp_product[4]);
						
						unset($temp_warehouse[0]);
						unset($temp_warehouse[2]);
						unset($temp_warehouse[3]);
						unset($temp_warehouse[5]);
						
						unset($temp_var[0]);
						unset($temp_var[2]);
						unset($temp_var[5]);
						
						unset($temp_key[0]);
						unset($temp_key[1]);
						unset($temp_key[3]);
						unset($temp_key[5]);
						
						unset($value[2]);
						unset($value[5]);
						
						$final[] = array_combine($keys, $temp_product);
						$final_ware_product[] = array_combine($keys_warehouse, $temp_warehouse);
						$final_var[] = array_combine($keys_var, $temp_var);
						$final_purchase_item[] = array_combine($keys_purchase, $value);
						$final_serial[] = array_combine($keys_serial, $temp_key);
						
					}
				}
				$rw = 2;
				$i =0;
				
				foreach ($final as $csv_pr) {	
					if(trim($csv_pr['code']) != ""){
						$query_product = $this->products_model->getProductByCode(trim($csv_pr['code']));
					}

					$final_purchase_item[$i]['product_id'] = $query_product->id;
					$final_ware_product[$i]['product_id'] = $query_product->id;
					$final_var[$i]['product_id'] = $query_product->id;					
					$query_product_var = $this->products_model->getOptionId($query_product->id,$final_var[$i]['option_id']);
					if($query_product_var){
                        $final_var[$i]['option_id'] = $query_product_var->id;
                        $final_purchase_item[$i]['option_id'] = $query_product_var->id;  
                    }

                    $final_purchase_item[$i]['cost'] = $csv_pr['cost']; 

					if($csv_pr['cost'] == ""){
                        $final_purchase_item[$i]['cost'] = 0;
                    }
					
					
					$final_serial[$i]['product_id'] = $query_product->id;
					$final_serial[$i]['serial_status'] = 1;
					
					if (!$query_product ) {
						$this->session->set_flashdata('message', lang("check_product_code") . " (" . $csv_pr['code'] . "). " . lang("code_x_exist") . " " . lang("line_no") . " " . $rw);
						redirect("products/update_quantity");
					}
					$rw++;
					$i++;
				} 
				
				$out = array();
				$wwp = array();
				$fiv = array();
				$pi  = array();
				foreach ($final as $key => $value){
					if (array_key_exists($value['code'], $out)){
						$out[$value['code']]['code'] = $value['code'];
						$out[$value['code']]['quantity'] += $value['quantity'];
						$out[$value['code']]['cost'] = $value['cost'];
						
					} else {
						$out[$value['code']] = array(
							'code' => $value['code'], 
							'quantity' => $value['quantity'], 
							'cost' => $value['cost']
						);
					}
				}
				$final_p = array_values($out);
				
				foreach ($final_ware_product as $key => $value){
					if (array_key_exists($value['product_id'], $wwp)){
						$wwp[$value['product_id']]['product_id'] = $value['product_id'];
						$wwp[$value['product_id']]['quantity'] += $value['quantity'];
						$wwp[$value['product_id']]['warehouse_id'] = $value['warehouse_id'];
						
					} else {
						$wwp[$value['product_id']] = array(
							'product_id' => $value['product_id'], 
							'quantity' => $value['quantity'], 
							'warehouse_id' => $value['warehouse_id']
						);
					}
				}
				$final_w = array_values($wwp);
				
				foreach ($final_var as $key => $value){
					if (array_key_exists($value['product_id'], $fiv)){
						$fiv[$value['product_id']]['quantity'] += $value['quantity'];
						$fiv[$value['product_id']]['option_id'] = $value['option_id'];
						$fiv[$value['product_id']]['warehouse_id'] = $value['warehouse_id'];
						$fiv[$value['product_id']]['product_id'] = $value['product_id'];
						
					} else {
						$fiv[$value['product_id']] = array( 
							'quantity' => $value['quantity'], 
							'option_id' => $value['option_id'], 
							'warehouse_id' => $value['warehouse_id'],
							'product_id' => $value['product_id']
						);
					}
				}
				$final_v = array_values($fiv);
				
				foreach ($final_purchase_item as $key => $value){
					if (array_key_exists($value['product_code'], $pi)){
						$pi[$value['product_code']]['product_code'] = $value['product_code'];
						$pi[$value['product_code']]['quantity_balance'] += $value['quantity_balance'];
						$pi[$value['product_code']]['option_id'] = $value['option_id'];
						$pi[$value['product_code']]['warehouse_id'] = $value['warehouse_id'];
						$pi[$value['product_code']]['product_id'] = $value['product_id'];
						$pi[$value['product_code']]['cost'] = $value['cost'];
					} else {
						$pi[$value['product_code']] = array( 
							'product_code'     => $value['product_code'], 
							'quantity_balance' => $value['quantity_balance'], 
							'option_id' => $value['option_id'],
							'warehouse_id' => $value['warehouse_id'],
							'product_id' => $value['product_id'],
							'cost' => $value['cost']
						);
					}
				}
				$final_pi = array_values($pi);
				//$this->erp->print_arrays($final_serial);
			}
		}
		
        if ($this->form_validation->run() == true && !empty($final)) {
            //$this->products_model->updateQuantityExcel($final_p);
			//$this->products_model->updateQuantityExcelWarehouse($final_w);
			//$this->products_model->updateQuantityExcelVar($final_v);
			//$this->products_model->updateQuantityExcelPurchase($final_pi);
			$this->products_model->updateSerialkey($final_serial);
            $this->session->set_flashdata('message', lang("quantity_updated"));
            redirect('products/product_serial');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'products/import_update_serial', $this->data);

        }
    }
	
	//===================================== End
    /* ------------------------------------------------------------------------------- */
	
	/*------------------- */

    function delete($id = NULL)
    {
        $this->erp->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->products_model->deleteProduct($id)) {
            if($this->input->is_ajax_request()) {
                echo lang("product_deleted");
            }
            $this->session->set_flashdata('message', lang('product_deleted'));
            redirect('welcome');
        }

    }

    /* ----------------------------------------------------------------------------- */

    function quantity_adjustments()
    {
        $this->erp->checkPermissions('adjustments');

        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $data['warehouses'] = $this->site->getAllWarehouses();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('quantity_adjustments')));
        $meta = array('page_title' => lang('quantity_adjustments'), 'bc' => $bc);
        $this->page_construct('products/quantity_adjustments', $meta, $this->data);
    }

    function getadjustments($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('adjustments');
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;

        if ($pdf || $xls) { 
            $this->db
                ->select($this->db->dbprefix('adjustments') . ".id as did, " . $this->db->dbprefix('adjustments') . ".product_id as productid, " . $this->db->dbprefix('adjustments') . ".date as date, " . $this->db->dbprefix('products') . ".image as image, " . $this->db->dbprefix('products') . ".code as code, " . $this->db->dbprefix('products') . ".name as pname, GROUP_CONCAT(" . $this->db->dbprefix('adjustments') . ".serial_number SEPARATOR '<br>') as serial, " . $this->db->dbprefix('adjustments') . ".quantity as quantity, " .$this->db->dbprefix('adjustments') . ".type, " . $this->db->dbprefix('warehouses') . ".name as wh, ". $this->db->dbprefix('adjustments') . ".note");
            $this->db->from('adjustments');
            $this->db->join('products', 'products.id=adjustments.product_id', 'left');
            $this->db->join('product_variants', 'product_variants.id=adjustments.option_id', 'left');
            $this->db->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');
            $this->db->group_by("adjustments.id")->order_by('adjustments.date desc');
            if ($product) {
                $this->db->where('adjustments.product_id', $product);
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
                $this->excel->getActiveSheet()->setTitle(lang('quantity_adjustments'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('serial'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('quantity'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('type'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('warehouse'));

                $row = 2;
                foreach ($data as $data_row) { 
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->pname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->serial);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->quantity);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($data_row->type));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->wh);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $filename = lang('quantity_adjustments');
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

        } else {

            $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_adjustment") . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger' id='a__$1' href='" . site_url('products/delete_adjustment/$2') . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('adjustments') . ".id as did, " . 
				$this->db->dbprefix('adjustments') . ".product_id as productid, " . 
				$this->db->dbprefix('adjustments') . ".date as date, " . 
				$this->db->dbprefix('products') . ".image as image, " . 
				$this->db->dbprefix('products') . ".code as code, " . 
				$this->db->dbprefix('products') . ".name as pname, " .  
				$this->db->dbprefix('adjustments') . ".quantity as quantity, " .
				$this->db->dbprefix('adjustments') . ".serial_number, " .
				$this->db->dbprefix('adjustments') . ".type, " . 
				$this->db->dbprefix('warehouses') . ".name as wh, ".
				$this->db->dbprefix('adjustments') . ".note");
            $this->datatables->from('adjustments');
            $this->datatables->join('products', 'products.id=adjustments.product_id', 'left');
            $this->datatables->join('product_variants', 'product_variants.id=adjustments.option_id', 'left');
            $this->datatables->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');
            $this->datatables->group_by("adjustments.id");
            $this->datatables->add_column("Actions", "<div class='text-center'><a href='" . site_url('products/edit_adjustment/$1/$2') . "' class='tip' title='" . lang("edit_adjustment") . "' data-toggle='modal' data-target='#myModal'><i class='fa fa-edit'></i></a> " . $delete_link . "</div>", "productid, did");
            if ($product) {
                $this->datatables->where('adjustments.product_id', $product);
            }
            $this->datatables->unset_column('did');
            $this->datatables->unset_column('productid');
            $this->datatables->unset_column('image');

            echo $this->datatables->generate();

        }

    }
	
	function getAdjustmentProduct($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('adjustments');
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;

        if ($pdf || $xls) { 
            $this->db
                ->select($this->db->dbprefix('adjustments') . ".id as did, " . $this->db->dbprefix('adjustments') . ".product_id as productid, " . $this->db->dbprefix('adjustments') . ".date as date, " . $this->db->dbprefix('products') . ".image as image, " . $this->db->dbprefix('products') . ".code as code, " . $this->db->dbprefix('products') . ".name as pname, GROUP_CONCAT(" . $this->db->dbprefix('adjustments') . ".serial_number SEPARATOR '<br>') as serial, " . $this->db->dbprefix('adjustments') . ".quantity as quantity, " .$this->db->dbprefix('adjustments') . ".type, " . $this->db->dbprefix('warehouses') . ".name as wh, ". $this->db->dbprefix('adjustments') . ".note");
            $this->db->from('adjustments');
            $this->db->join('products', 'products.id=adjustments.product_id', 'left');
            $this->db->join('product_variants', 'product_variants.id=adjustments.option_id', 'left');
            $this->db->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');
            $this->db->group_by("adjustments.id")->order_by('adjustments.date desc');
            if ($product) {
                $this->db->where('adjustments.product_id', $product);
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
                $this->excel->getActiveSheet()->setTitle(lang('quantity_adjustments'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('serial'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('quantity'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('type'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('warehouse'));

                $row = 2;
                foreach ($data as $data_row) { 
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->pname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->serial);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->quantity);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($data_row->type));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->wh);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $filename = lang('quantity_adjustments');
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

        } else {

            $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_adjustment") . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger' id='a__$1' href='" . site_url('products/delete_adjustment/$2') . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('adjustments') . ".id as did, " . 
				$this->db->dbprefix('adjustments') . ".product_id as productid, " . 
				$this->db->dbprefix('adjustments') . ".date as date, " . 
				$this->db->dbprefix('products') . ".image as image, " . 
				$this->db->dbprefix('products') . ".code as code, " .  
				$this->db->dbprefix('adjustments') . ".quantity as quantity, " .
				$this->db->dbprefix('adjustments') . ".serial_number, " .
				$this->db->dbprefix('adjustments') . ".type, " . 
				$this->db->dbprefix('warehouses') . ".name as wh, ".
				$this->db->dbprefix('adjustments') . ".note");
            $this->datatables->from('adjustments');
            $this->datatables->join('products', 'products.id=adjustments.product_id', 'left');
            $this->datatables->join('product_variants', 'product_variants.id=adjustments.option_id', 'left');
            $this->datatables->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');
            $this->datatables->group_by("adjustments.id");
            $this->datatables->add_column("Actions", "<div class='text-center'><a href='" . site_url('products/edit_adjustment/$1/$2') . "' class='tip' title='" . lang("edit_adjustment") . "' data-toggle='modal' data-target='#myModal'><i class='fa fa-edit'></i></a> " . $delete_link . "</div>", "productid, did");
            if ($product) {
                $this->datatables->where('adjustments.product_id', $product);
            }
            $this->datatables->unset_column('did');
            $this->datatables->unset_column('productid');
            $this->datatables->unset_column('image');

            echo $this->datatables->generate();

        }

    }
	
	//-------------- Export to Excel and PDF product
	function getProductAll($pdf = NULL, $excel = NULL)
    {
        $this->erp->checkPermissions('products');

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;

        if ($pdf || $excel) {

            $this->db
                ->select($this->db->dbprefix('products') . ".code as codes, " . $this->db->dbprefix('products') . ".name as names,". $this->db->dbprefix('products') .".unit as units,
				" . $this->db->dbprefix('categories') . ".name as cname, " . $this->db->dbprefix('products') . ".cost as costes, 
				" . $this->db->dbprefix('products') . ".price as prices, " . $this->db->dbprefix('products') . ".quantity as quantities,
				" . $this->db->dbprefix('products') . ".alert_quantity as alert_quantities,
				" . $this->db->dbprefix('warehouses') . ".name as wname");
            $this->db->from('products');
            $this->db->join('categories', 'categories.id=products.category_id', 'left');
            $this->db->join('warehouses', 'warehouses.id=products.warehouse', 'left');
            $this->db->group_by("products.id")->order_by('products.id desc');
            if ($product) {
                $this->db->where('product.id', $product);
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
                $this->excel->getActiveSheet()->setTitle(lang('products'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('category'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('product_cost'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_price'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('quantity'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('product_unit'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('alert_quantity'));

                $row = 2;
                foreach ($data as $data_row) {
                    //$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->id));
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->codes);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->names);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->cname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->costes);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->prices);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($data_row->quantities));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($data_row->units));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($data_row->alert_quantities));
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
                $filename = lang('Product');
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
	//------------------- End export product

    function add_adjustment($product_id = NULL, $warehouse_id = NULL)
    {
        $this->erp->checkPermissions('adjustments', true);
		
		
        $this->form_validation->set_rules('type', lang("type"), 'required');
        $this->form_validation->set_rules('quantity', lang("quantity"), 'required');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
		
		$have_serial = $this->site->getProductByID($product_id);
		
        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:s:i');
            }
			
            //============== Add / Remove Serial ================

            $serial_array  = array();
            $serial_add    = 0;
            $serial_number = 0;

            if($this->input->post('type') == 'subtraction'){
                $serial_array  = $this->input->post('choose_serial');
                $serial_id     = implode(',', $serial_array);
				foreach($serial_array as $seri){
					$serial = $this->products_model->getSerialbyId($seri);
					$serial_num[] = $serial->serial_number;
				}
				$serial_number = implode(',', $serial_num);
			}else{
                $serial_add    = $this->input->post('add_serial');
				$serial_number = implode(',', $serial_add);
            }
            
            //====================== End ========================

            $data = array(
                'date'          => $date,
                'product_id'    => $product_id,
                'type'          => $this->input->post('type'),
                'quantity'      => $this->input->post('quantity'),
                'warehouse_id'  => $this->input->post('warehouse'),
                'option_id'     => $this->input->post('option') ? $this->input->post('option') : NULL,
                'note'          => $this->input->post('note'),
                'created_by'    => $this->session->userdata('user_id'),
				'biller_id'     => $this->default_biller_id?$this->default_biller_id:'',
                'serial_number' => $serial_number
            );
			
            if (!$this->Settings->overselling && $this->input->post('type') == 'subtraction') {
                if ($this->input->post('option')) {
                    if($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($this->input->post('option'), $this->input->post('warehouse'))) {
                        if ($op_wh_qty->quantity < $data['quantity']) {
                            $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                }
				
                if($wh_qty = $this->products_model->getProductQuantity($product_id, $this->input->post('warehouse'))){
                    if ($wh_qty['quantity'] < $data['quantity']) {
                        $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                } else {
                    $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }

        } elseif ($this->input->post('adjust_quantity')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('products');
        }
		
        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $serial_array)) {
            $this->session->set_flashdata('message', lang("quantity_adjusted"));
            redirect('products/quantity_adjustments');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $product = $this->site->getProductByID($product_id);
            if($product->type != 'standard') {
                $this->session->set_flashdata('error', lang('quantity_x_adjuste').' ('.lang('product_type').': '.lang($product->type).')');
                die('<script>window.location.replace("'.$_SERVER["HTTP_REFERER"].'");</script>');
            }
			
            $this->data['product'] 		= $product;
            $this->data['have_serial'] 	= $have_serial->is_serial;
            $this->data['warehouses'] 	= $this->site->getAllWarehouses();
            $this->data['modal_js'] 	= $this->site->modal_js();
            $this->data['options'] 		= $this->products_model->getProductOptions($product_id);
            $this->data['product_id'] 	= $product_id;
            $this->data['warehouse_id'] = $warehouse_id;
            $this->load->view($this->theme . 'products/add_adjustment', $this->data);

        }
    }

    //############ Get Serial By Warehouse
    public function getProductsSerialByWarehouse()
    {
        $warehouse_id 	= $_REQUEST['warehouse_id'];
        $product_id 	= $_REQUEST['product_id'];
        $rows 			= $this->products_model->getProductSerialByWarehouse($warehouse_id, $product_id);
        if($rows){
            foreach ($rows as $item) {
                $pr[] = array('id' => $item->id, 'serial_number' => $item->serial_number);
            }
        }else{
            $pr[] = array('id' => 0, 'serial_number' => 'This products have no serial and quantity in this warehouse');
        }
        
        echo json_encode($pr);
    }
	
	//############ Get Adjustment Serial
    public function getAdjustmentSerialAjax()
    {
        $warehouse_id = $_REQUEST['warehouse_id'];
        $product_id = $_REQUEST['product_id'];
        $rows = $this->products_model->getAdjustmentSerialByWarehouse($warehouse_id, $product_id);
        if($rows){
            foreach ($rows as $item) {
                $pr[] = array('serial_number' => $item->serial_number);
            }
        }else{
            $pr[] = array('serial_number' => 'no serial number');
        }
        
        echo json_encode($pr);
    }
	
    function edit_adjustment($product_id = NULL, $id = NULL)
    {
        $this->erp->checkPermissions('adjustments', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->input->get('product_id')) {
            $product_id = $this->input->get('product_id');
        }
        $this->form_validation->set_rules('type', lang("type"), 'required');
        $this->form_validation->set_rules('quantity', lang("quantity"), 'required');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
		
		$have_serial = $this->site->getProductByID($product_id);

        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin) {
                $date = $this->erp->fld($this->input->post('date'));
            } else {
                $date = NULL;
            }
			
			//============== Add / Remove Serial ================

            $serial_array  = array();
            $serial_add    = 0;
            $serial_number = 0;

            if($this->input->post('type') == 'subtraction'){
                $serial_array  = $this->input->post('choose_serial');
				$serial_number = implode(',', $serial_array);
			}else{
                $serial_add    = $this->input->post('add_serial');
				$serial_number = implode(',', $serial_add);
            }
            
            //====================== End ========================

            $data = array(
                'product_id' 	=> $product_id,
                'type' 			=> $this->input->post('type'),
                'quantity' 		=> $this->input->post('quantity'),
                'warehouse_id' 	=> $this->input->post('warehouse'),
                'option_id' 	=> $this->input->post('option') ? $this->input->post('option') : NULL,
                'note' 			=> $this->input->post('note'),
                'updated_by' 	=> $this->session->userdata('user_id'),
				'biller_id'     => $this->default_biller_id?$this->default_biller_id:'',
                'serial_number' => $serial_number
			);
			
            if ($date) {
                $data['date'] = $date;
            }

            if (!$this->Settings->overselling && $this->input->post('type') == 'subtraction') {
                $dp_details = $this->products_model->getAdjustmentByID($id);
                if ($this->input->post('option')) {
                    $op_wh_qty = $this->products_model->getProductWarehouseOptionQty($this->input->post('option'), $this->input->post('warehouse'));
                    $old_op_qty = $op_wh_qty->quantity + $dp_details->quantity;
                    if ($old_op_qty < $data['quantity']) {
                        $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                        redirect('products');
                    }
                }
                $wh_qty = $this->products_model->getProductQuantity($product_id, $this->input->post('warehouse'));
                $old_quantity = $wh_qty['quantity'] + $dp_details->quantity;
                if ($old_quantity < $data['quantity']) {
                    $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                    redirect('products/quantity_adjustments');
                }
            }

        } elseif ($this->input->post('edit_adjustment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('products/quantity_adjustments');
        }

        if ($this->form_validation->run() == true && $this->products_model->updateAdjustment($id, $data, $serial_array)) {
            $this->session->set_flashdata('message', lang("quantity_adjusted"));
            redirect('products/quantity_adjustments');
        } else {
            $this->data['error'] 		= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['have_serial'] 	= $have_serial->is_serial;
            $this->data['product'] 		= $this->site->getProductByID($product_id);
            $this->data['options'] 		= $this->products_model->getProductOptions($product_id);
            $this->data['damage'] 		= $this->products_model->getAdjustmentByID($id);
            $this->data['warehouses'] 	= $this->site->getAllWarehouses();
            $this->data['id'] 			= $id;
            $this->data['product_id'] 	= $product_id;
            $this->data['modal_js'] 	= $this->site->modal_js();
            $this->load->view($this->theme . 'products/edit_adjustment', $this->data);
        }
    }

    function delete_adjustment($id = NULL)
    {
        $this->erp->checkPermissions(NULL, TRUE);

        if ($this->products_model->deleteAdjustment($id)) {
			$this->session->set_flashdata('message', lang("adjustment_deleted"));
            redirect('products/quantity_adjustments');
        }
    }

    /* --------------------------------------------------------------------------------------------- */

    function modal_view($id = NULL)
    {
        $this->erp->checkPermissions('index', TRUE);
		$pr_details = $this->products_model->getProductByID($id);
		
		if($pr_details->cf1){
			$cf1d = $this->products_model->getCaseByID($pr_details->cf1);
			$this->data['cf1data'] = $cf1d;
		}
		if($pr_details->cf2){
			$cf2d = $this->products_model->getDiameterByID($pr_details->cf2);
			$this->data['cf2data'] = $cf2d;
		}
		
		if($pr_details->cf3){
			$cf3d = $this->products_model->getDialByID($pr_details->cf3);
			$this->data['cf3data'] = $cf3d;
		}
	
		if($pr_details->cf4){
			$cf4d = $this->products_model->getStrapByID($pr_details->cf4);
			$this->data['cf4data'] = $cf4d;
		}
		
		if($pr_details->cf5){
			$cf5d = $this->products_model->getWaterRByID($pr_details->cf5);
			$this->data['cf5data'] = $cf5d;
		}
        
		if($pr_details->cf6){
			$cf6d = $this->products_model->getWindingByID($pr_details->cf6);
			$this->data['cf6data'] = $cf6d;
		}
		
		if($pr_details->cf7){
			$cf7d = $this->products_model->getPowerReserveByID($pr_details->cf7);
			$this->data['cf7data'] = $cf7d;
		}
		
		if($pr_details->cf8){
			$cf8d = $this->products_model->getBuckleByID($pr_details->cf8);
			$this->data['cf8data'] = $cf8d;
		}
		
		if($pr_details->cf9){
			$cf9d = $this->products_model->getComplicationByID($pr_details->cf9);
			$this->data['cf9data'] = $cf9d;
		}
		
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
		
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
		if ($pr_details->is_serial) {
            $this->data['serial_number'] 	= $this->products_model->getProductserial($id);
            $this->data['serial_sale'] 		= $this->products_model->getSerialSale($id);
        }
        $this->db->select('*');
        $this->db->from('companies');
        $this->db->where('id',  $pr_details->supplier1);
        $q = $this->db->get()->result();

        $this->data['supplier'] 	= $q;
        $this->data['product'] 		= $pr_details;
        $this->data['images'] 		= $this->products_model->getProductPhotos($id);
        $this->data['category'] 	= $this->site->getCategoryByID($pr_details->category_id);
		$this->data['brand'] 		= $this->site->getBrandByID($pr_details->brand_id);
        $this->data['subcategory'] 	= $pr_details->subcategory_id ? $this->products_model->getSubCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] 	= $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['warehouses'] 	= $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] 		= $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] 	= $this->products_model->getProductOptions($id);

        $this->load->view($this->theme.'products/modal_view', $this->data);
    }

    function view($id = NULL)
    {
        $this->erp->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        if ($pr_details->is_serial) {
            $this->data['serial_number'] = $this->products_model->getProductserial($id);
            $this->data['serial_sale']   = $this->products_model->getSerialSale($id);
        }
        if($pr_details->cf1){
            $cf1d = $this->products_model->getCaseByID($pr_details->cf1);
            $this->data['cf1data'] = $cf1d;
        }
        if($pr_details->cf2){
            $cf2d = $this->products_model->getDiameterByID($pr_details->cf2);
            $this->data['cf2data'] = $cf2d;
        }
        
        if($pr_details->cf3){
            $cf3d = $this->products_model->getDialByID($pr_details->cf3);
            $this->data['cf3data'] = $cf3d;
        }
    
        if($pr_details->cf4){
            $cf4d = $this->products_model->getStrapByID($pr_details->cf4);
            $this->data['cf4data'] = $cf4d;
        }
        
        if($pr_details->cf5){
            $cf5d = $this->products_model->getWaterRByID($pr_details->cf5);
            $this->data['cf5data'] = $cf5d;
        }

        if($pr_details->cf6){
            $cf6d = $this->products_model->getWindingByID($pr_details->cf6);
            $this->data['cf6data'] = $cf6d;
        }
        
        if($pr_details->cf7){
            $cf7d = $this->products_model->getPowerReserveByID($pr_details->cf7);
            $this->data['cf7data'] = $cf7d;
        }
        if($pr_details->cf8){
            $cf8d = $this->products_model->getBuckleByID($pr_details->cf8);
            $this->data['cf8data'] = $cf8d;
        }
        if($pr_details->cf9){
            $cf9d = $this->products_model->getComplicationByID($pr_details->cf9);
            $this->data['cf9data'] = $cf9d;
        }
        $this->data['product'] = $pr_details;
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->products_model->getSubCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);
        $this->data['sold'] = $this->products_model->getSoldQty($id);
        $this->data['purchased'] = $this->products_model->getPurchasedQty($id);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => $pr_details->name));
        $meta = array('page_title' => $pr_details->name, 'bc' => $bc);
        $this->page_construct('products/view', $meta, $this->data);
    }

    function pdf($id = NULL, $view = NULL)
    {
        $this->erp->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->products_model->getSubCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);

        $name = $pr_details->code . '_' . str_replace('/', '_', $pr_details->name) . ".pdf";
        if ($view) {
            $this->load->view($this->theme . 'products/pdf', $this->data);
        } else {
            $html = $this->load->view($this->theme . 'products/pdf', $this->data, TRUE);
            $this->erp->generate_pdf($html, $name);
        }
    }
	
	function getCategories($band_id = NULL)
    {
        if ($rows = $this->products_model->getCategoriesForBrandID($band_id)) {
            $data = json_encode($rows);
        } else {
            $data = false;
        }
        echo $data;
    }

    function getSubCategories($category_id = NULL)
    {
        if ($rows = $this->products_model->getSubCategoriesForCategoryID($category_id)) {
            $data = json_encode($rows);
        } else {
            $data = false;
        }
        echo $data;
    }

    /*function product_actions($wh = NULL)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'sync_quantity') {
					
                    foreach ($_POST['val'] as $id) {
                        $this->site->syncQuantity(NULL, NULL, NULL, $id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_quantity_sync"));
                    redirect($_SERVER["HTTP_REFERER"]);
				
                }else if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteProduct($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
					
                }else if ($this->input->post('form_action') == 'labels') {
                    $currencies = $this->site->getAllCurrencies();
                    $r = 1;
                    $inputs = '';
                    $html = "";
                    $html .= '<table class="table table-bordered table-condensed bartable"><tbody><tr>';
                    foreach ($_POST['val'] as $id) {
                        $inputs .= form_hidden('val[]', $id);
                        $pr = $this->products_model->getProductByID($id);

                        $html .= '<td class="text-center"><h4>' . $this->Settings->site_name . '</h4>' . $pr->name . '<br>' . $this->product_barcode($pr->code, $pr->barcode_symbology, 30);
                        $html .= '<table class="table table-bordered">';
                        foreach ($currencies as $currency) {
                            $html .= '<tr><td class="text-left">' . $currency->code . '</td><td class="text-right">' . $this->erp->formatMoney($pr->price * $currency->rate) . '</td></tr>';
                        }
                        $html .= '</table>';
                        $html .= '</td>';

                        if ($r % 4 == 0) {
                            $html .= '</tr><tr>';
                        }
                        $r++;
                    }
                    if ($r < 4) {
                        for ($i = $r; $i <= 4; $i++) {
                            $html .= '<td></td>';
                        }
                    }
                    $html .= '</tr></tbody></table>';

                    $this->data['r'] = $r;
                    $this->data['html'] = $html;
                    $this->data['inputs'] = $inputs;
                    $this->data['page_title'] = lang("print_labels");
                    $this->data['categories'] = $this->site->getAllCategories();
                    $this->data['category_id'] = '';
                    //$this->load->view($this->theme . 'products/print_labels', $this->data);
                    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_labels')));
                    $meta = array('page_title' => lang('print_labels'), 'bc' => $bc);
                    $this->page_construct('products/print_labels', $meta, $this->data);
                }else if ($this->input->post('form_action') == 'barcodes') {
                    $currencies = $this->site->getAllCurrencies();
                    $r = 1;

                    $html = "";
                    $html .= '<table class="table table-bordered sheettable"><tbody><tr>';
                    foreach ($_POST['val'] as $id) {
                        $pr = $this->site->getProductByID($id);
                        if ($r != 1) {
                            $rw = (bool)($r & 1);
                            $html .= $rw ? '</tr><tr>' : '';
                        }
                        $html .= '<td colspan="2" class="text-center"><h3>' . $this->Settings->site_name . '</h3>' . $pr->name . '<br>' . $this->product_barcode($pr->code, $pr->barcode_symbology, 60);
                        $html .= '<table class="table table-bordered">';
                        foreach ($currencies as $currency) {
                            $html .= '<tr><td class="text-left">' . $currency->code . '</td><td class="text-right">' . $this->erp->formatMoney($pr->price * $currency->rate) . '</td></tr>';
                        }
                        $html .= '</table>';
                        $html .= '</td>';
                        $r++;
                    }
                    if (!(bool)($r & 1)) {
                        $html .= '<td></td>';
                    }
                    $html .= '</tr></tbody></table>';

                    $this->data['r'] = $r;
                    $this->data['html'] = $html;
                    $this->data['category_id'] = '';
                    $this->data['categories'] = $this->site->getAllCategories();
                    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
                    $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
                    $this->page_construct('products/print_barcodes', $meta, $this->data);
                    //$this->load->view($this->theme . 'products/print_barcodes', $this->data);
                }else if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('Products');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('category_code'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('unit'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('cost'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('alert_quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('tax_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('tax_method'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('subcategory_code'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('product_variants'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('pcf1'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('pcf2'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('pcf3'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('pcf4'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('pcf5'));
                    $this->excel->getActiveSheet()->SetCellValue('R1', lang('pcf6'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $product = $this->products_model->getProductDetail($id);
                        $variants = $this->products_model->getProductOptions($id);
                        $product_variants = '';
                        if ($variants) {
                            foreach ($variants as $variant) {
                                $product_variants .= trim($variant->name) . '|';
                            }
                        }
                        $quantity = $product->quantity;
                        if ($wh) {
                            if($wh_qty = $this->products_model->getProductQuantity($id, $wh)) {
                                $quantity = $wh_qty['quantity'];
                            } else {
                                $quantity = 0;
                            }
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $product->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $product->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $product->category_code);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $product->unit);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $product->cost);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $product->price);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $quantity);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $product->alert_quantity);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $product->tax_rate_code);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $product->tax_method ? lang('exclusive') : lang('inclusive'));
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $product->subcategory_code);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $product_variants);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $product->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $product->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $product->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, $product->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $product->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('R' . $row, $product->cf6);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'products_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', $this->lang->line("no_product_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }*/
    function product_serial_actions($id=null){
		 if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        } 
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
		if ($this->form_validation->run() == true) {
			if (!empty($_POST['val'])) {
			     if($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf'){
					 
				$this->load->library('excel');
                $this->excel->setActiveSheetIndex(0); 
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('brand'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_reference'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('point_of_sales'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('serial_number'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));
                $row=2;
				foreach($_POST['val'] as $id){
					$product_serial=$this->products_model->exportserail($id); 
					foreach($product_serial as $serial){
						
					$this->excel->getActiveSheet()->SetCellValue('A'.$row,$serial->brandname);
					$this->excel->getActiveSheet()->SetCellValue('B'.$row,$serial->pro_code);
					$this->excel->getActiveSheet()->SetCellValue('C'.$row,$serial->product_name);
					$this->excel->getActiveSheet()->SetCellValue('D'.$row,$serial->company);
					$this->excel->getActiveSheet()->SetCellValue('E'.$row,$serial->warehouse);
					$this->excel->getActiveSheet()->SetCellValue('F'.$row,$serial->number);
					$this->excel->getActiveSheet()->SetCellValue('G'.$row,lang($serial->serial_status));
					
					$row++;
				  }
				}
                 

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $filename = lang('quantity_adjustments');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
			   }
			   $filename = 'products_serial' . date('Y_m_d_H_i_s');
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
			   if($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
               }

			}else {
                $this->session->set_flashdata('error', $this->lang->line("no_product_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
		} else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	function product_actions($wh = NULL)
    {
        /*
        if (!$this->Owner ) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        */

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'sync_quantity') {
                    
                    foreach ($_POST['val'] as $id) {
                        $this->site->syncQuantity(NULL, NULL, NULL, $id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_quantity_sync"));
                    redirect($_SERVER["HTTP_REFERER"]);
                
                }else if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {

                        $this->products_model->deleteProduct($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                    
                }else if ($this->input->post('form_action') == 'labels') {
                    $currencies = $this->site->getAllCurrencies();
                    $r = 1;
                    $inputs = '';
                    $html = "";
                    $html .= '<table class="table table-bordered table-condensed bartable"><tbody><tr>';
                    foreach ($_POST['val'] as $id) {
                        $inputs .= form_hidden('val[]', $id);
                        $pr = $this->products_model->getProductByID($id);

                        $html .= '<td class="text-center"><h4>' . $this->Settings->site_name . '</h4>' . $pr->name . '<br>' . $this->product_barcode($pr->code, $pr->barcode_symbology, 30);
                        $html .= '<table class="table table-bordered">';
                        foreach ($currencies as $currency) {
                            $html .= '<tr><td class="text-left">' . $currency->code . '</td><td class="text-right">' . $this->erp->formatMoney($pr->price * $currency->rate) . '</td></tr>';
                        }
                        $html .= '</table>';
                        $html .= '</td>';

                        if ($r % 4 == 0) {
                            $html .= '</tr><tr>';
                        }
                        $r++;
                    }
                    if ($r < 4) {
                        for ($i = $r; $i <= 4; $i++) {
                            $html .= '<td></td>';
                        }
                    }
                    $html .= '</tr></tbody></table>';

                    $this->data['r'] = $r;
                    $this->data['html'] = $html;
                    $this->data['inputs'] = $inputs;
                    $this->data['page_title'] = lang("print_labels");
                    $this->data['categories'] = $this->site->getAllCategories();
                    $this->data['category_id'] = '';
                    //$this->load->view($this->theme . 'products/print_labels', $this->data);
                    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_labels')));
                    $meta = array('page_title' => lang('print_labels'), 'bc' => $bc);
                    $this->page_construct('products/print_labels', $meta, $this->data);
                }else if ($this->input->post('form_action') == 'barcodes') {
					foreach ($_POST['val'] as $id) {
                        $row = $this->products_model->getProductByID($id);
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }

                    $this->data['items'] = isset($pr) ? json_encode($pr) : false;
                    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('print_barcodes')));
                    $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
                    $this->page_construct('products/print_barcodes', $meta, $this->data);

                }else if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('Products');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_type'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_code'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('brand'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('category'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('product_unit'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('product_cost'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('product_price'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('alert_quantity'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('product_tax'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('tax_method'));
					$this->excel->getActiveSheet()->SetCellValue('L1', lang('product_line'));
					//============= Add Qty to Export ==============//
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('quantity'));
					//=================== End ======================//
					$this->excel->getActiveSheet()->SetCellValue('N1', lang('product_variant'));
					$this->excel->getActiveSheet()->SetCellValue('O1', lang('supplier1'));
					$this->excel->getActiveSheet()->SetCellValue('P1', lang('supplier2'));
					$this->excel->getActiveSheet()->SetCellValue('Q1', lang('supplier3'));
					$this->excel->getActiveSheet()->SetCellValue('R1', lang('supplier4'));
					$this->excel->getActiveSheet()->SetCellValue('S1', lang('supplier5'));
					$this->excel->getActiveSheet()->SetCellValue('T1', lang('serial_number'));
					$this->excel->getActiveSheet()->SetCellValue('U1', lang('case'));
					$this->excel->getActiveSheet()->SetCellValue('V1', lang('diameter'));
					$this->excel->getActiveSheet()->SetCellValue('W1', lang('dial'));
					$this->excel->getActiveSheet()->SetCellValue('X1', lang('strap'));
					$this->excel->getActiveSheet()->SetCellValue('Y1', lang('water'));
					$this->excel->getActiveSheet()->SetCellValue('Z1', lang('winding'));
                    $this->excel->getActiveSheet()->SetCellValue('AA1', lang('power'));
                    $this->excel->getActiveSheet()->SetCellValue('AB1', lang('buckle'));
					$this->excel->getActiveSheet()->SetCellValue('AC1', lang('complications'));
					$this->excel->getActiveSheet()->SetCellValue('AD1', lang('product_description'));
					$this->excel->getActiveSheet()->SetCellValue('AE1', lang('image'));
					$this->excel->getActiveSheet()->SetCellValue('AF1', lang('combo_item1'));
					$this->excel->getActiveSheet()->SetCellValue('AG1', lang('combo_code1'));
					$this->excel->getActiveSheet()->SetCellValue('AH1', lang('combo_price1'));
					$this->excel->getActiveSheet()->SetCellValue('AI1', lang('combo_image1'));
					$this->excel->getActiveSheet()->SetCellValue('AJ1', lang('combo_product_category1'));
					$this->excel->getActiveSheet()->SetCellValue('AK1', lang('combo_product_line1'));
					$this->excel->getActiveSheet()->SetCellValue('AL1', lang('combo_item2'));
					$this->excel->getActiveSheet()->SetCellValue('AM1', lang('combo_code2'));
					$this->excel->getActiveSheet()->SetCellValue('AN1', lang('combo_price2'));
					$this->excel->getActiveSheet()->SetCellValue('AO1', lang('combo_image2'));
					$this->excel->getActiveSheet()->SetCellValue('AP1', lang('combo_product_category2'));
					$this->excel->getActiveSheet()->SetCellValue('AQ1', lang('combo_product_line2'));
					$this->excel->getActiveSheet()->SetCellValue('AR1', lang('combo_item3'));
					$this->excel->getActiveSheet()->SetCellValue('AS1', lang('combo_code3'));
					$this->excel->getActiveSheet()->SetCellValue('AT1', lang('combo_price3'));
					$this->excel->getActiveSheet()->SetCellValue('AU1', lang('combo_image3'));
					$this->excel->getActiveSheet()->SetCellValue('AV1', lang('combo_product_category3'));
					$this->excel->getActiveSheet()->SetCellValue('AW1', lang('combo_product_line3'));
					$this->excel->getActiveSheet()->SetCellValue('AX1', lang('combo_item4'));
					$this->excel->getActiveSheet()->SetCellValue('AY1', lang('combo_code4'));
					$this->excel->getActiveSheet()->SetCellValue('AZ1', lang('combo_price4'));
					$this->excel->getActiveSheet()->SetCellValue('BA1', lang('combo_image4'));
					$this->excel->getActiveSheet()->SetCellValue('BB1', lang('combo_product_category4'));
					$this->excel->getActiveSheet()->SetCellValue('BC1', lang('combo_product_line4'));
					
                    $row = 2;
					$qty = 0;
					$code = 0;
					$name = 0;
                    foreach ($_POST['val'] as $id) {
                        $product = $this->products_model->getProductDetail($id);
                        $variants = $this->products_model->getProductOptions($id);
						$product_combo = array();
						$combo = $this->products_model->getProductCombos($id, $product->code);
                        $product_variants = '';
                        if ($variants) {
                            foreach ($variants as $variant) {
                                $product_variants .= trim($variant->name) . '|';
                            }
                        }
						
						if($product->type == 'combo'){
							$qty = $this->products_model->getProductByCode($product->code);
							$code = $this->erp->subLastStr($product->code);
							$name = $this->erp->subLastStr($product->name);
						}else{
							$qty = $this->products_model->getProductByCode($product->code);
							$code = $product->code;
							$name = $product->name;
						}
						
                        $quantity = $product->quantity;
						
                        if ($wh) {
                            if($wh_qty = $this->products_model->getProductQuantity($id, $wh)) {
                                $quantity = $wh_qty['quantity'];
                            } else {
                                $quantity = 0;
                            }
                        }
						
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $product->type);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $code);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $name);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $product->brand);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $product->category_name);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $product->unit_name);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $product->cost);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $product->price);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $product->alert_quantity);
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $product->tax_rate_code);
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, $product->tax_method);
						$this->excel->getActiveSheet()->SetCellValue('L' . $row, $product->subcategory_name);
						$this->excel->getActiveSheet()->SetCellValue('M' . $row, ($qty->quantity?$qty->quantity:0));
						$this->excel->getActiveSheet()->SetCellValue('N' . $row, $product_variants);
						$this->excel->getActiveSheet()->SetCellValue('O' . $row, $product->supplier1);
						$this->excel->getActiveSheet()->SetCellValue('P' . $row, $product->supplier2);
						$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $product->supplier3);
						$this->excel->getActiveSheet()->SetCellValue('R' . $row, $product->supplier4);
						$this->excel->getActiveSheet()->SetCellValue('S' . $row, $product->supplier5);
						$this->excel->getActiveSheet()->SetCellValue('T' . $row, $product->is_serial);
						$this->excel->getActiveSheet()->SetCellValue('U' . $row, $product->case_meterial);
						$this->excel->getActiveSheet()->SetCellValue('V' . $row, $product->diameter);
						$this->excel->getActiveSheet()->SetCellValue('W' . $row, $product->dial);
						$this->excel->getActiveSheet()->SetCellValue('X' . $row, $product->strap);
						$this->excel->getActiveSheet()->SetCellValue('Y' . $row, $product->water);
						$this->excel->getActiveSheet()->SetCellValue('Z' . $row, $product->winding);
						$this->excel->getActiveSheet()->SetCellValue('AA' . $row, $product->power);
						$this->excel->getActiveSheet()->SetCellValue('AB' . $row, $product->buckle);
						$this->excel->getActiveSheet()->SetCellValue('AC' . $row, $product->complications);
						$this->excel->getActiveSheet()->SetCellValue('AD' . $row, $product->product_details);
						$this->excel->getActiveSheet()->SetCellValue('AE' . $row, $product->image);
						
						$i = 0;
						if($combo){
							
						
							foreach($combo as $p_combo){
								$n = count($p_combo->code);
								
								if($i == 0){
									$this->excel->getActiveSheet()->SetCellValue('AF' . $row, $p_combo->name);
									$this->excel->getActiveSheet()->SetCellValue('AG' . $row, $p_combo->code);
									$this->excel->getActiveSheet()->SetCellValue('AH' . $row, $p_combo->price);
									$this->excel->getActiveSheet()->SetCellValue('AI' . $row, $p_combo->image);
									$this->excel->getActiveSheet()->SetCellValue('AJ' . $row, $p_combo->category_name);
									$this->excel->getActiveSheet()->SetCellValue('AK' . $row, $p_combo->subcategory_name);
								}elseif($i == 1){
									$this->excel->getActiveSheet()->SetCellValue('AL' . $row, $p_combo->name);
									$this->excel->getActiveSheet()->SetCellValue('AM' . $row, $p_combo->code);
									$this->excel->getActiveSheet()->SetCellValue('AN' . $row, $p_combo->price);
									$this->excel->getActiveSheet()->SetCellValue('AO' . $row, $p_combo->image);
									$this->excel->getActiveSheet()->SetCellValue('AP' . $row, $p_combo->category_name);
									$this->excel->getActiveSheet()->SetCellValue('AQ' . $row, $p_combo->subcategory_name);
								}elseif($i == 2){
									$this->excel->getActiveSheet()->SetCellValue('AR' . $row, $p_combo->name);
									$this->excel->getActiveSheet()->SetCellValue('AS' . $row, $p_combo->code);
									$this->excel->getActiveSheet()->SetCellValue('AT' . $row, $p_combo->price);
									$this->excel->getActiveSheet()->SetCellValue('AU' . $row, $p_combo->image);
									$this->excel->getActiveSheet()->SetCellValue('AV' . $row, $p_combo->category_name);
									$this->excel->getActiveSheet()->SetCellValue('AW' . $row, $p_combo->subcategory_name);
								}else{
									$this->excel->getActiveSheet()->SetCellValue('AX' . $row, $p_combo->name);
									$this->excel->getActiveSheet()->SetCellValue('AY' . $row, $p_combo->code);
									$this->excel->getActiveSheet()->SetCellValue('AZ' . $row, $p_combo->price);
									$this->excel->getActiveSheet()->SetCellValue('BA' . $row, $p_combo->image);
									$this->excel->getActiveSheet()->SetCellValue('BB' . $row, $p_combo->category_name);
									$this->excel->getActiveSheet()->SetCellValue('BC' . $row, $p_combo->subcategory_name);
								}
								
								$i++;
							}
						}
						
						$row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'products_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', $this->lang->line("no_product_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
		} else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete_image($id = NULL)
    {
        $this->erp->checkPermissions('edit', true);
        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            $id || die(json_encode(array('error' => 1, 'msg' => lang('no_image_selected'))));
            $this->db->delete('product_photos', array('id' => $id));
            die(json_encode(array('error' => 0, 'msg' => lang('image_deleted'))));
        }
        die(json_encode(array('error' => 1, 'msg' => lang('ajax_error'))));
    }
	
	public function list_convert(){
		
		$this->erp->checkPermissions('index', true, 'products');
		
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('products')));
		$meta = array('page_title' => lang('items_convert'), 'bc' => $bc);
		$this->page_construct('products/list_convert', $meta, $this->data);
	}
	
	public function delete_convert($id = null)
    {
        $this->erp->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->products_model->deleteConvert($id) && $this->products_model->deleteConvert_items($id)) {
            echo lang("convert_deleted");
        }
    }
	
	public function product_analysis($id = null)
    {
        //$convert = $this->products_model->getConvertByID($id);
		$deduct = $this->products_model->ConvertDeduct($id);
		$add = $this->products_model->ConvertAdd($id);
        //$this->data['user'] = $this->site->getUser($convert->created_by);
        $this->data['deduct'] = $deduct;
		$this->data['add'] = $add;
		$this->data['logo'] = true;
        $this->data['page_title'] = $this->lang->line("product_analysis");
        $this->load->view($this->theme . 'products/product_anlysis', $this->data);
    }
	
	public function getListConvert()
    {
        $this->erp->checkPermissions('index', true, 'products');
		$add_link = '<a href="' . site_url('products/items_convert') . '"><i class="fa fa-plus-circle"></i> ' . lang('add_convert') . '</a>';
        $analysis_link = anchor('products/product_analysis/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_analysis'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link = '<a href="' . site_url('products/edit_convert/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_convert') . '</a>';
        //$attachment_link = '<a href="'.base_url('assets/uploads/$1').'" target="_blank"><i class="fa fa-chain"></i></a>';
        /*$delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_expense") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('products/delete_convert/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_convert') . "</a>";*/
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
			<li>' . $add_link . '</li>
            <li>' . $edit_link . '</li>
			<li>' . $analysis_link . '</li>
        </ul>
    </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select($this->db->dbprefix('convert') . ".id as id,
					".$this->db->dbprefix('convert').".date AS Date, 
					".$this->db->dbprefix('convert').".reference_no AS Reference, 
					SUM(".$this->db->dbprefix('convert_items').".quantity) AS Quantity, 
					".$this->db->dbprefix('products').".cost AS Cost,
					".$this->db->dbprefix('convert').".noted AS Note,
					".$this->db->dbprefix('warehouses').".name as na,
					CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as user", false)
            ->from('convert')
            ->join('users', 'users.id=convert.created_by', 'left')
			->join('convert_items', 'convert_items.convert_id = convert.id')
			->join('products', 'convert_items.product_id = products.id')
			->join('warehouses', 'warehouses.id = convert.warehouse_id')
			->where('convert_items.status','add')
            ->group_by('convert.id');
			
        //$this->datatables->edit_column("attachment", $attachment_link, "attachment");
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function edit_convert($id = null)
    { 
        $this->erp->checkPermissions();
        $this->load->helper('security');
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
         $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
		$id_convert_item = 0;
        if ($this->form_validation->run() == true) {
			
			$convert_id = $_POST['convert_id'];
			
			$warehouse_id        = $_POST['warehouse'];
            // list convert item from
            $cIterm_from_id     = $_POST['convert_from_items_id'];
            $cIterm_from_code   = $_POST['convert_from_items_code'];
            $cIterm_from_name   = $_POST['convert_from_items_name'];
            $cIterm_from_uom    = $_POST['convert_from_items_uom'];
            $cIterm_from_qty    = $_POST['convert_from_items_qty'];
            // list convert item to
            $iterm_to_id        = $_POST['convert_to_items_id'];
            $iterm_to_code      = $_POST['convert_to_items_code'];
            $iterm_to_name      = $_POST['convert_to_items_name'];
            $iterm_to_uom      	= $_POST['convert_to_items_uom'];
            $iterm_to_qty       = $_POST['convert_to_items_qty'];
            $data               = array(
									'reference_no' => $_POST['reference_no'],
									'date' => date('Y-m-d h:m', strtotime($_POST['cdate'])),
									'warehouse_id' => $_POST['warehouse'],
									'updated_by' => $this->session->userdata('user_id'),
									'noted' => $_POST['note']
								);
								
            $idConvert          = $this->products_model->updateConvert($convert_id,$data);
			$id_convert_item = $idConvert;
			//$this->products_model->deleteConvert_items($convert_id);
            $items = array();
            $i = isset($_POST['convert_from_items_code']) ? sizeof($_POST['convert_from_items_code']) : 0;
			//echo $i; die();
            for ($r = 0; $r < $i; $r++) {
                $products   = $this->site->getProductByID($cIterm_from_id[$r]);
				$convert_from = $this->products_model->getConvertItemsByIDPID($convert_id, $cIterm_from_id[$r]);
				$this->products_model->deleteConvert_itemsByPID($convert_id, $cIterm_from_id[$r]);
                if(!empty($cIterm_from_uom[$r])){
                    $product_variant    	= $this->site->getProductVariantByID($cIterm_from_id[$r], $cIterm_from_uom[$r]);
                }else{
                    $product_variant        = $this->site->getProductVariantByID($cIterm_from_id[$r]);
                }
                $PurchaseItemsQtyBalance    =  $this->site->getPurchaseBalanceQuantity($cIterm_from_id[$r], $warehouse_id);
				
				$unit_qty = ( !empty($product_variant->qty_unit) && $product_variant->qty_unit > 0 ? $product_variant->qty_unit : 1 );
				//$this->erp->print_arrays($convert_from);
				
				//get qty from warehouse for update with one pro in two warehouses
                $qtyWarehouse = $this->products_model->getWarehouseQty($cIterm_from_id[$r], $warehouse_id);
                $EachWarehouseQty = ($qtyWarehouse->quantity + $convert_from->quantity) - ($unit_qty  * $cIterm_from_qty[$r]);
                //echo $EachWarehouseQty;exit;
				
                $PurchaseItemsQtyBalance    = ($PurchaseItemsQtyBalance + $convert_from->quantity) - ($unit_qty  * $cIterm_from_qty[$r]);
                $qtyBalace                  = $product_variant->quantity - $cIterm_from_qty[$r];
				
				$purchase_items_id = 0;
				$pis = $this->site->getPurchasedItems($cIterm_from_id[$r], $warehouse_id, $option_id = NULL);
				foreach ($pis as $pi) {
					$purchase_items_id = $pi->id;
					break;
				}

				$clause = array('purchase_id' => NULL, 'product_code' => $cIterm_from_code[$r], 'product_id' => $cIterm_from_id[$r], 'warehouse_id' => $warehouse_id);
				if ($pis) {
					$this->db->update('purchase_items', array('quantity_balance' => $PurchaseItemsQtyBalance), array('id' => $purchase_items_id, 'warehouse_id' => $warehouse_id));
				} else {
					$clause['quantity'] = 0;
					$clause['item_tax'] = 0;
					$clause['option_id'] = null;
					$clause['transfer_id'] = null;
					$clause['product_name'] = $cIterm_from_name[$r];
					$clause['quantity_balance'] = $PurchaseItemsQtyBalance;
					$this->db->insert('purchase_items', $clause);
				}
                // UPDATE PRODUCT QUANTITY
				
                if($this->db->update('products', array('quantity' => $PurchaseItemsQtyBalance), array('code' => $cIterm_from_code[$r])))
				{
					// UPDATE WAREHOUSE_PRODUCT QUANTITY
					if ($this->site->getWarehouseProducts( $cIterm_from_id[$r], $warehouse_id)) {
						$this->db->update('warehouses_products', array('quantity' => $EachWarehouseQty), array('product_id' => $cIterm_from_id[$r], 'warehouse_id' => $warehouse_id));
					} else {
						$this->db->insert('warehouses_products', array('quantity' => $EachWarehouseQty, 'product_id' => $cIterm_from_id[$r], 'warehouse_id' => $warehouse_id));
					}
					// UPDATE PRODUCT_VARIANT quantity
					if(!empty($cIterm_from_uom[$r])){
						$this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $cIterm_from_id[$r], 'name' => $cIterm_from_uom[$r]));
					}else{
						$this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $cIterm_from_id[$r]));
					}
				} else {
					exit('error - product');
				}

                $this->db->insert('erp_convert_items',  array(
                                                        'convert_id' => $convert_id,
                                                        'product_id' => $cIterm_from_id[$r],
                                                        'product_code' => $cIterm_from_code[$r],
                                                        'product_name' => $cIterm_from_name[$r],
                                                        'quantity' => $cIterm_from_qty[$r],
                                                        'status' => 'deduct'));
								
				//$this->site->syncQuantity(NULL, $purchase_items_id);
				//$this->site->syncQuantity(NULL, NULL, NULL, $cIterm_from_id[$r]);
            }
            $j = isset($_POST['convert_to_items_code']) ? sizeof($_POST['convert_to_items_code']) : 0;
           $qty_from = '';
            for ($r = 0; $r < $i; $r++) {
				$qty_from += $cIterm_from_qty[$r];
                $products   = $this->site->getProductByID($iterm_to_id[$r]);
				$convert_to = $this->products_model->getConvertItemsByIDPID($convert_id, $iterm_to_id[$r]);
				$this->products_model->deleteConvert_itemsByPID($convert_id, $iterm_to_id[$r]);
                if(!empty($cIterm_from_uom[$r])){
                    $product_variant        = $this->site->getProductVariantByID($iterm_to_id[$r], $iterm_to_uom[$r]);
                }else{
                    $product_variant        = $this->site->getProductVariantByID($iterm_to_id[$r]);
                }
				
                $PurchaseItemsQtyBalance    =  $this->site->getPurchaseBalanceQuantity($iterm_to_id[$r], $warehouse_id);
                $unit_qty = ( !empty($product_variant->qty_unit) && $product_variant->qty_unit > 0 ? $product_variant->qty_unit : 1 );
                $PurchaseItemsQtyBalance    = ($PurchaseItemsQtyBalance - $convert_to->quantity) + ($unit_qty  * $iterm_to_qty[$r]);
                $qtyBalace                  = $product_variant->quantity + $iterm_to_qty[$r];
				
				$qtyWarehouse = $this->products_model->getWarehouseQty($iterm_to_id[$r], $warehouse_id);
                $EachWarehouseQty = ($qtyWarehouse->quantity - $convert_to->quantity) + ($unit_qty  * $iterm_to_qty[$r]);
                //echo $EachWarehouseQty;exit;
				
                $purchase_items_id = 0;
				$pis = $this->site->getPurchasedItems($iterm_to_id[$r], $warehouse_id, $option_id = NULL);
				foreach ($pis as $pi) {
					$purchase_items_id = $pi->id;
					break;
				}
				$clause = array('purchase_id' => NULL, 'product_code' => $iterm_to_code[$r], 'product_id' => $iterm_to_id[$r], 'warehouse_id' => $warehouse_id);
				if ($pis) {
					$this->db->update('purchase_items', array('quantity_balance' => $PurchaseItemsQtyBalance), array('id' => $purchase_items_id));
				} else {
					$clause['quantity'] = 0;
					$clause['item_tax'] = 0;
					$clause['option_id'] = null;
					$clause['transfer_id'] = null;
					$clause['product_name'] = $iterm_to_name[$r];
					$clause['quantity_balance'] = $PurchaseItemsQtyBalance;
					$this->db->insert('purchase_items', $clause);
				}
                // UPDATE PRODUCT QUANTITY
				
                if($this->db->update('products', array('quantity' => $PurchaseItemsQtyBalance), array('code' => $iterm_to_code[$r])))
				{
					// UPDATE WAREHOUSE_PRODUCT QUANTITY
					if ($this->site->getWarehouseProducts($iterm_to_id[$r], $warehouse_id)) {
						$this->db->update('warehouses_products', array('quantity' => $EachWarehouseQty), array('product_id' => $iterm_to_id[$r], 'warehouse_id' => $warehouse_id));
					} else {
						$this->db->insert('warehouses_products', array('quantity' => $EachWarehouseQty, 'product_id' => $iterm_to_id[$r], 'warehouse_id' => $warehouse_id));
					}
					// UPDATE PRODUCT_VARIANT quantity
					if(!empty($cIterm_from_uom[$r])){
						$this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $iterm_to_id[$r], 'name' => $iterm_to_uom[$r]));
					}else{
						$this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $iterm_to_id[$r]));
					}
				} else {
					exit('error increase product ');
				}
				
                $this->db->insert('erp_convert_items', array(
                                                        'convert_id' => $convert_id,
                                                        'product_id' => $iterm_to_id[$r],
                                                        'product_code' => $iterm_to_code[$r],
                                                        'product_name' => $iterm_to_name[$r],
                                                        'quantity' => $iterm_to_qty[$r],
                                                        'status' => 'add'));
				
				//$this->site->syncQuantity(NULL, $purchase_items_id);
				//$this->site->syncQuantity(NULL, NULL, NULL, $cIterm_from_id[$r]);
            }
			
			$qty_to = implode(',',$iterm_to_qty);
			$qty_toex = explode(',',$qty_to);
			foreach($qty_toex as $QTo){
				if(count($qty_toex) > 1){
					$num = $qty_from - $QTo;
					$final_num = $qty_from - $num;
					$new_cost = $this->site->calculateCONAVCost($convert_id, $QTo, $final_num);
					$upPros = $this->site->updateCostPro(array('cost' => $new_cost), $iterm_to_id);
				}else{
					$new_cost = $this->site->calculateCONAVCost($convert_id, $QTo, $qty_from);
					$upPros = $this->site->updateCostPro(array('cost' => $new_cost), $iterm_to_id);
				}
			}
			
			if($id_convert_item != 0){
				$items = $this->products_model->getConvertItemsById($id_convert_item);
				$deduct = $this->products_model->getConvertItemsDeduct($id_convert_item);
				$adds = $this->products_model->getConvertItemsAdd($id_convert_item);
				$each_cost = 0;
				$total_item = count($adds);
				
				foreach($items as $item){
					if($item->status == 'deduct'){
						$this->db->update('convert_items', array('cost' => $item->tcost), array('product_id' => $item->product_id, 'convert_id' => $item->convert_id));
					}else{
						$each_cost = $deduct->tcost / $total_item;
						if($this->db->update('convert_items', array('cost' => $each_cost), array('product_id' => $item->product_id, 'convert_id' => $item->convert_id))){
							
							//foreach($adds as $add){
								$total_net_unit_cost = $each_cost / $item->c_quantity;
								//$total_quantity += $each_cost;
								//$total_unit_cost += ($pi->unit_cost ? ($pi->unit_cost *  $pi->quantity_balance) : ($pi->net_unit_cost + ($pi->item_tax / $pi->quantity) *  $pi->quantity_balance));
							//}
							//$avg_net_unit_cost = $total_net_unit_cost / $total_quantity;
							//$avg_unit_cost = $total_unit_cost / $total_quantity;

							//$cost2 = $each_cost * $item->p_cost;
							
							//$product_cost = ($total_net_unit_cost + $cost2) / $total_quantity;
							//$this->db->update('products', array('cost' => $total_net_unit_cost), array('id' => $item->product_id));
						}
					}
				}
			}
			
            $this->session->set_flashdata('message', lang("item_conitem_convert_success"));
            redirect('products/list_convert');
        }
		
        //$this->erp->print_arrays($this->products_model->getConvert_ItemByID($id));
		
		$this->data['warehouses'] = $this->site->getAllWarehouses();
		$this->data['convert'] = $this->products_model->getConvertByID($id);
		$this->data['convert_items'] = $this->products_model->getConvert_ItemByID($id);
		
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_product')));
		$meta = array('page_title' => lang('edit_convert'), 'bc' => $bc);
		
		$this->page_construct('products/edit_convert', $meta, $this->data);
    }
	
	public function convert_actions()
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
                        $this->products_model->deleteConvert($id);
						$this->products_model->deleteConvert_items($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("convert_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('convert'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('quantity_convert'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('cost'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('created_by'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $converts = $this->products_model->getConvertByID($id);
						//$this->erp->print_arrays($converts); 
                        $user = $this->site->getUser($converts->created_by);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($converts->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $converts->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->erp->formatMoneyPurchase($converts->Quantity));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->erp->formatMoneyPurchase($converts->cost));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $converts->noted);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $user->first_name . ' ' . $user->last_name);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'convert_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', $this->lang->line("no_convert_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	function items_convert()
    {
        $this->erp->checkPermissions('index', true, 'products');
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
		$id_convert_item = 0;
        if ($this->form_validation->run() == true) {
			$warehouse_id        = $_POST['warehouse'];
            // list convert item from
            $cIterm_from_id     = $_POST['convert_from_items_id'];
            $cIterm_from_code   = $_POST['convert_from_items_code'];
            $cIterm_from_name   = $_POST['convert_from_items_name'];
            $cIterm_from_uom    = $_POST['convert_from_items_uom'];
            $cIterm_from_qty    = $_POST['convert_from_items_qty'];
            // list convert item to
            $iterm_to_id        = $_POST['convert_to_items_id'];
            $iterm_to_code      = $_POST['convert_to_items_code'];
            $iterm_to_name      = $_POST['convert_to_items_name'];
            $iterm_to_uom       = $_POST['convert_to_items_uom'];
            $iterm_to_qty       = $_POST['convert_to_items_qty'];
            $data               = array(
                                        'reference_no' => $_POST['reference_no']?$_POST['reference_no']:$this->site->getReference('con'),
                                        'date' => $this->erp->fld($_POST['sldate']),
										'warehouse_id' => $_POST['warehouse'],
                                        'created_by' => $this->session->userdata('user_id'),
										'noted' => $_POST['note'],
										'bom_id' => $_POST['bom_id']
                                    );
			//$this->erp->print_arrays($iterm_to_qty);
			
            $idConvert          = $this->products_model->insertConvert($data);
			$id_convert_item = $idConvert;
				
            $items = array();
            $i = isset($_POST['convert_from_items_code']) ? sizeof($_POST['convert_from_items_code']) : 0;
			$qty_from = '';
            for ($r = 0; $r < $i; $r++) {
				$qty_from += $cIterm_from_qty[$r];
                $product_fr   = $this->site->getProductByID($cIterm_from_id[$r]);
                if(!empty($cIterm_from_uom[$r])){
                    $product_variant    	= $this->site->getProductVariantByID($cIterm_from_id[$r], $cIterm_from_uom[$r]);
					//$this->erp->print_arrays($product_variant);
                }else{
                    $product_variant        = $this->site->getProductVariantByID($cIterm_from_id[$r]);
                }
				
                $PurchaseItemsQtyBalance    =  $this->site->getProductQty($cIterm_from_id[$r]);
				
				$unit_qty = ( !empty($product_variant->qty_unit) && $product_variant->qty_unit > 0 ? $product_variant->qty_unit : 1 );
				//echo $unit_qty;exit;
				
				//get qty from warehouse for update with one pro in two warehouses
                $qtyWarehouse = $this->products_model->getWarehouseQty($cIterm_from_id[$r], $warehouse_id);
                $EachWarehouseQty = $qtyWarehouse->quantity - ($unit_qty  * $cIterm_from_qty[$r]);
                //echo $EachWarehouseQty;exit;
				
                $PurchaseItemsQtyBalance    = $PurchaseItemsQtyBalance - ($unit_qty  * $cIterm_from_qty[$r]);
				
                $qtyBalace                  = $product_variant->quantity - $cIterm_from_qty[$r];
				
				$purchase_items_id = 0;
				$pis = $this->site->getPurchasedItems($cIterm_from_id[$r], $warehouse_id, $option_id = NULL);
				
				foreach ($pis as $pi) {
					$purchase_items_id = $pi->id;
					break;
				}
				
				$clause = array('purchase_id' => NULL, 'product_code' => $cIterm_from_code[$r], 'product_id' => $cIterm_from_id[$r], 'warehouse_id' => $warehouse_id);
				
				if ($pis) {
					$this->db->update('purchase_items', array('quantity_balance' => $PurchaseItemsQtyBalance), array('id' => $purchase_items_id, 'warehouse_id' => $warehouse_id));
				} else {
					$clause['quantity'] = 0;
					$clause['item_tax'] = 0;
					$clause['option_id'] = null;
					$clause['transfer_id'] = null;
					$clause['product_name'] = $cIterm_from_name[$r];
					$clause['quantity_balance'] = $PurchaseItemsQtyBalance;
					$this->db->insert('purchase_items', $clause);
				}
				
                // UPDATE PRODUCT QUANTITY
				//echo $PurchaseItemsQtyBalance;exit;
                if($this->db->update('products', array('quantity' => $PurchaseItemsQtyBalance), array('code' => $cIterm_from_code[$r])))
				{
					// UPDATE WAREHOUSE_PRODUCT QUANTITY
					if ($this->site->getWarehouseProducts( $cIterm_from_id[$r], $warehouse_id)) {
						$this->db->update('warehouses_products', array('quantity' => $EachWarehouseQty), array('product_id' => $cIterm_from_id[$r], 'warehouse_id' => $warehouse_id));
					} else {
						$this->db->insert('warehouses_products', array('quantity' => $EachWarehouseQty, 'product_id' => $cIterm_from_id[$r], 'warehouse_id' => $warehouse_id));
					}
					// UPDATE PRODUCT_VARIANT quantity
					if(!empty($cIterm_from_uom[$r])){
						$this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $cIterm_from_id[$r], 'name' => $cIterm_from_uom[$r]));
					}else{
						$this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $cIterm_from_id[$r]));
					}
				} else {
					exit('error - product');
				}

                $this->db->insert('erp_convert_items',  array(
                                                        'convert_id' => $idConvert,
                                                        'product_id' => $cIterm_from_id[$r],
                                                        'product_code' => $cIterm_from_code[$r],
                                                        'product_name' => $cIterm_from_name[$r],
                                                        'quantity' => $cIterm_from_qty[$r],
                                                        'status' => 'deduct'));
							
				//$this->site->syncQuantity(NULL, $purchase_items_id);
				//$this->site->syncQuantity(NULL, NULL, NULL, $cIterm_from_id[$r]);
            }
            $j = isset($_POST['convert_to_items_code']) ? sizeof($_POST['convert_to_items_code']) : 0;
			$qty_to = '';
            for ($r = 0; $r < $j; $r++) {
				//$qty_to += $iterm_to_qty[$r]; 
                $products   = $this->site->getProductByID($iterm_to_id[$r]);
                if(!empty($cIterm_from_uom[$r])){
                    $product_variant        = $this->site->getProductVariantByID($iterm_to_id[$r], $iterm_to_uom[$r]);
                }else{
                    $product_variant        = $this->site->getProductVariantByID($iterm_to_id[$r]);
                }

                $PurchaseItemsQtyBalance    =  $this->site->getProductQty($iterm_to_id[$r]);

                $unit_qty = ( !empty($product_variant->qty_unit) && $product_variant->qty_unit > 0 ? $product_variant->qty_unit : 1 );
				
				$qtyWarehouse = $this->products_model->getWarehouseQty($iterm_to_id[$r], $warehouse_id);
                $EachWarehouseQty = $qtyWarehouse->quantity + ($unit_qty  * $iterm_to_qty[$r]);
                //echo $EachWarehouseQty;exit;
				
                $PurchaseItemsQtyBalance    = $PurchaseItemsQtyBalance + ($unit_qty  * $iterm_to_qty[$r]);
                $qtyBalace                  = $product_variant->quantity + $iterm_to_qty[$r];

                $purchase_items_id = 0;
				$pis = $this->site->getPurchasedItems($iterm_to_id[$r], $warehouse_id, $option_id = NULL);
				foreach ($pis as $pi) {
					$purchase_items_id = $pi->id;
					break;
				}
				$clause = array('purchase_id' => NULL, 'product_code' => $iterm_to_code[$r], 'product_id' => $iterm_to_id[$r], 'warehouse_id' => $warehouse_id);
				
				if ($pis) {
					$this->db->update('purchase_items', array('quantity_balance' => $PurchaseItemsQtyBalance), array('id' => $purchase_items_id));
				} else {
					$clause['quantity'] = 0;
					$clause['item_tax'] = 0;
					$clause['option_id'] = null;
					$clause['transfer_id'] = null;
					$clause['product_name'] = $iterm_to_name[$r];
					$clause['quantity_balance'] = $PurchaseItemsQtyBalance;
					$this->db->insert('purchase_items', $clause);
				}
                // UPDATE PRODUCT QUANTITY
				//$this->erp->print_arrays($iterm_to_code);
				$upPro = $this->site->updateQualityPro(array('quantity' => $PurchaseItemsQtyBalance), $iterm_to_code[$r]);
                if($upPro)
				{
					// UPDATE WAREHOUSE_PRODUCT QUANTITY
					if ($this->site->getWarehouseProducts($iterm_to_id[$r], $warehouse_id)) {
						$this->db->update('warehouses_products', array('quantity' => $EachWarehouseQty), array('product_id' => $iterm_to_id[$r], 'warehouse_id' => $warehouse_id));
					} else {
						$this->db->insert('warehouses_products', array('quantity' => $EachWarehouseQty, 'product_id' => $iterm_to_id[$r], 'warehouse_id' => $warehouse_id));
					}
					// UPDATE PRODUCT_VARIANT quantity
					if(!empty($cIterm_from_uom[$r])){
						$this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $iterm_to_id[$r], 'name' => $iterm_to_uom[$r]));
					}else{
						$this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $iterm_to_id[$r]));
					}
				} else {
					exit('error increase product ');
				}
				
                $this->db->insert('erp_convert_items', array(
                                                        'convert_id' => $idConvert,
                                                        'product_id' => $iterm_to_id[$r],
                                                        'product_code' => $iterm_to_code[$r],
                                                        'product_name' => $iterm_to_name[$r],
                                                        'quantity' => $iterm_to_qty[$r],
                                                        'status' => 'add'));
				
				//$this->site->syncQuantity(NULL, $purchase_items_id);
				//$this->site->syncQuantity(NULL, NULL, NULL, $cIterm_from_id[$r]);
				
				
				//$upPros = $this->site->updateCostPro(array('cost' => $new_cost), $iterm_to_id[$r]);
				
				//$this->db->update('products', array('cost' => $new_cost), array('id' => $iterm_to_id[$r]));
				
				/*echo 'Qty To db'.$products->quantity.'<br/>';
				echo 'cost To db'.$products->cost.'<br/>';
				echo 'Qty input'.$iterm_to_qty[$r].'<br/>';
				echo 'Qty from db'.$cIterm_from_qty[$r].'<br/>';
				echo 'cost from db'.$product_fr->cost.'<br/>';

				if($products->quantity > 1){
					$cost_frist = ($cIterm_from_qty[$r] * $product_fr->cost) / $iterm_to_qty[$r]; 
					echo $cost_frist ;
					$qty_old = $iterm_to_qty[$r] + $products->quantity;
					echo $qty_old;
					$new_cost_from =  ($products->quantity * $products->cost) + $cost_frist / $qty_old[$r];
					echo ($new_cost_from + $cost_frist) / $qty_old;
					die();
				}else{
					$new_cost_from = ($product_fr->quantity * $product_fr->cost) / $products->quantity;
				}*/
				
            }
			$qty_to = implode(',',$iterm_to_qty);
			$qty_toex = explode(',',$qty_to);
			foreach($qty_toex as $QTo){
				if(count($qty_toex) > 1){
					$num = $qty_from - $QTo;
					$final_num = $qty_from - $num;
					$new_cost = $this->site->calculateCONAVCost($id_convert_item, $QTo, $final_num);
					$upPros = $this->site->updateCostPro(array('cost' => $new_cost), $iterm_to_id);
				}else{
					$new_cost = $this->site->calculateCONAVCost($id_convert_item, $QTo, $qty_from);
					$upPros = $this->site->updateCostPro(array('cost' => $new_cost), $iterm_to_id);
				}
			}
			
			if($id_convert_item != 0){
				$items = $this->products_model->getConvertItemsById($id_convert_item);
				$deduct = $this->products_model->getConvertItemsDeduct($id_convert_item);
				$adds = $this->products_model->getConvertItemsAdd($id_convert_item);
				$each_cost = 0;
				$total_item = count($adds);
				$old_qty = '';
				foreach($items as $item){
					if($item->status == 'deduct'){
						$this->db->update('convert_items', array('cost' => $item->tcost), array('product_id' => $item->product_id, 'convert_id' => $item->convert_id));
						$old_qty = $item->c_quantity;
					}else{
						$each_cost = $deduct->tcost / $total_item;
						if($this->db->update('convert_items', array('cost' => $each_cost), array('product_id' => $item->product_id, 'convert_id' => $item->convert_id))){
							//foreach($adds as $add){
								$total_net_unit_cost = $each_cost / $item->c_quantity;
								//$total_quantity += $each_cost;
								//$total_unit_cost += ($pi->unit_cost ? ($pi->unit_cost *  $pi->quantity_balance) : ($pi->net_unit_cost + ($pi->item_tax / $pi->quantity) *  $pi->quantity_balance));
							//}
							
							
							//$new_cost_from = ($old_qty * $product_fr->cost) / $item->c_quantity;
							//$cost_avgs = ($new_cost_from * $item->c_quantity) / ($product_fr->quantity *  $product_fr->cost);
							//$this->db->update('products', array('cost' => $new_cost_from), array('id' => $item->product_id));
							//$avg_net_unit_cost = $total_net_unit_cost / $total_quantity;
							//$avg_unit_cost = $total_unit_cost / $total_quantity;

							//$cost2 = $each_cost * $item->p_cost;
							
							//$product_cost = ($total_net_unit_cost + $cost2) / $total_quantity;
						}
					}
				}
			}
			
            $this->session->set_flashdata('message', lang("item_conitem_convert_success"));
            redirect('products/items_convert');
        }else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$reference = $this->products_model->getReference();
			foreach($reference as $reference_no){
				if($this->site->getReference('con') == $reference_no->reference_no){
					$this->site->updateReference('con'); 
				}
			}
			
			$this->data['conumber'] = $this->site->getReference('con');
			//$this->site->updateReference('con'); 
			$this->data['warehouses'] = $this->site->getAllWarehouses();
			$this->data['tax_rates'] = $this->site->getAllTaxRates();
			$this->data['bom'] = $this->products_model->getAllBoms();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('products')));
			$meta = array('page_title' => lang('items_convert'), 'bc' => $bc);
			$this->page_construct('products/items_convert', $meta, $this->data);
		}
    }
	
	public function testConvert($convert_id, $qty_to, $qty_from){
		$r = $this->site->calculateCONAVCost($convert_id, $qty_to, $qty_from);
		echo 'Average Cost Convert' . $r;
	}
	
	/* Products Return */
	function return_products($warehouse_id = NULL)
    {
        $this->erp->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : NULL;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('return_products')));
        $meta = array('page_title' => lang('return_products'), 'bc' => $bc);
        $this->page_construct('products/return_products', $meta, $this->data);
    }

    function getReturns($warehouse_id = NULL)
    {
        $this->erp->checkPermissions('return_products');
		
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
		
		
        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i>');
        $edit_link = ''; //anchor('sales/edit/$1', '<i class="fa fa-edit"></i>', 'class="reedit"');
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_return_sale") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_return/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";
        $action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $delete_link . '</div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('return_sales') . ".date as date, " . $this->db->dbprefix('sales') . ".reference_no as ref, ABS(" . $this->db->dbprefix('return_items') . ".quantity) as qty, " . $this->db->dbprefix('return_sales') . ".biller, " . $this->db->dbprefix('return_sales') . ".customer, " . $this->db->dbprefix('users') . ".username, " . $this->db->dbprefix('return_sales') . ".id as id")
                ->join('sales', 'sales.id=return_sales.sale_id', 'left')
				->join('return_items', 'return_items.return_id = return_sales.id', 'left')
				->join('users', 'users.id = return_sales.created_by', 'left')
                ->from('return_sales')
                ->group_by('return_sales.id')
                ->where('return_sales.warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select($this->db->dbprefix('return_sales') . ".date as date, " . $this->db->dbprefix('sales') . ".reference_no as ref, ABS(" . $this->db->dbprefix('return_items') . ".quantity) as qty, " . $this->db->dbprefix('return_sales') . ".biller, " . $this->db->dbprefix('return_sales') . ".customer, " . $this->db->dbprefix('users') . ".username, " . $this->db->dbprefix('return_sales') . ".id as id")
                ->join('sales', 'sales.id=return_sales.sale_id', 'left')
				->join('return_items', 'return_items.return_id = return_sales.id','left')
				->join('users', 'users.id = return_sales.created_by', 'left')
                ->from('return_sales')
                ->group_by('return_sales.id');
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin) {
            $this->datatables->where('return_sales.created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('return_sales.customer_id', $this->session->userdata('customer_id'));
        }
		
		if ($user_query) {
			$this->datatables->where('sales.created_by', $user_query);
		}/*
		if ($customer) {
			$this->datatables->where('sales.id', $customer);
		}*/
		if ($reference_no) {
			$this->datatables->where('sales.reference_no', $reference_no);
		}
		if ($biller) {
			$this->datatables->where('sales.biller_id', $biller);
		}
		if ($customer) {
			$this->datatables->where('sales.customer_id', $customer);
		}
		if ($warehouse) {
			$this->datatables->where('sales.warehouse_id', $warehouse);
		}

		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('return_sales').'.date BETWEEN "' . $start_date . '" AND "' . $end_date . '"');
		}
		
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	/*
	function view_return($id = NULL)
    {
        $this->erp->checkPermissions('return_sales');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getReturnByID($id);
        $this->erp->view_rights($inv->created_by);
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllReturnItems($id);
        $this->data['sale'] = $this->sales_model->getInvoiceByID($inv->sale_id);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('view_return')));
        $meta = array('page_title' => lang('view_return_details'), 'bc' => $bc);
        $this->page_construct('products/view_return', $meta, $this->data);
    }*/

	function getDatabyBom_id(){
		$id = $this->input->get('term', TRUE);
		$result = $this->products_model->getAllBom_id($id);
		echo json_encode($result);
	}
	
	
    function product_serial()
    {
        $this->erp->checkPermissions('serial', true, 'products');

        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $data['warehouses'] = $this->site->getAllWarehouses();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('product_serial')));
        $meta = array('page_title' => lang('product_serial'), 'bc' => $bc);
        $this->page_construct('products/product_serial', $meta, $this->data);
    }
	
	
	function product_serial_edit($id=NULL)
    {
        $this->data['edit_product_id'] =$id;
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getAllWarehouses();
		$this->data['product'] =$this->products_model->getproductbyIds($id); 
		$this->data['product_list_serial'] = $this->products_model->getProductserials($id);
		//$this->erp->print_arrays($this->products_model->getProductserial($id));
		$this->data['modal_js'] = $this->site->modal_js();
		$this->load->view($this->theme . 'products/edit_product_serial', $this->data);
    }
	
    function edit_serial($id=null){
		  $serial_number=$this->input->post('serial_number'); 
		  if($this->input->get('id')){
			  $id=$this->input->get('id');
		  } 
		   if ($this->products_model->updateserial($id,$serial_number)){
			      $this->session->set_flashdata('message', lang('serial_was_updated'));
                  redirect($_SERVER["HTTP_REFERER"]);
		  
		   }else{
			     $this->session->set_flashdata('error', lang('updated_no_success'));
                 redirect($_SERVER["HTTP_REFERER"]);
		   }
	}
    
	function getProductSerial($pdf = NULL, $xls = NULL)
    {
        $this->erp->checkPermissions('serial', true, 'products');

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;

        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('adjustments') . ".id as did, " . $this->db->dbprefix('adjustments') . ".product_id as productid, " . $this->db->dbprefix('adjustments') . ".date as date, " . $this->db->dbprefix('products') . ".image as image, " . $this->db->dbprefix('products') . ".code as code, " . $this->db->dbprefix('products') . ".name as pname, " . $this->db->dbprefix('product_variants') . ".name as vname, " . $this->db->dbprefix('adjustments') . ".quantity as quantity, ".$this->db->dbprefix('adjustments') . ".type, " . $this->db->dbprefix('warehouses') . ".name as wh");
            $this->db->from('adjustments');
            $this->db->join('products', 'products.id=adjustments.product_id', 'left');
            $this->db->join('product_variants', 'product_variants.id=adjustments.option_id', 'left');
            $this->db->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');
			$this->db->where('serial_number != "undefined" ');
            $this->db->group_by("adjustments.id")->order_by('adjustments.date desc');
            if ($product) {
                $this->db->where('adjustments.product_id', $product);
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
                $this->excel->getActiveSheet()->setTitle(lang('quantity_adjustments'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('product_variant'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('quantity'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('type'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('warehouse'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->erp->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->pname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->vname);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->quantity);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($data_row->type));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->wh);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $filename = lang('quantity_adjustments');
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

            $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_adjustment") . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . site_url('products/delete_adjustment/$2') . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

            $this->load->library('datatables');
            $this->datatables->select($this->db->dbprefix('serial') . ".id, " .$this->db->dbprefix('brands').".name as brandname, ".$this->db->dbprefix('products') . ".code as code, " . $this->db->dbprefix('products') . ".name as name, " . $this->db->dbprefix('companies') . ".company as company, " . $this->db->dbprefix('warehouses') . ".name as warehouse, " . $this->db->dbprefix('serial') . ".serial_number as number, ".$this->db->dbprefix('serial') . ".serial_status as sstatus");
            $this->datatables->from('serial');
            $this->datatables->join('products', 'products.id=serial.product_id', 'left');
            $this->datatables->join('companies', 'companies.id=serial.biller_id', 'left');
            $this->datatables->join('warehouses', 'warehouses.id=serial.warehouse', 'left');
			$this->datatables->join('brands', 'products.brand_id = brands.id','left');
			$this->db->where('serial_number != "undefined" ');
            $this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit prodcut serial") . "' href='" . site_url('products/product_serial_edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_serial") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('products/deleteserial/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", $this->db->dbprefix('serial') . ".id");
			if ($product) {
                $this->datatables->where('serial.product_id', $product);
            }
            echo $this->datatables->generate();

        }

    }
	function deleteserial($id=null){
		
		$this->erp->checkPermissions('adjustments');
		   if($this->input->get('id')){
			   $id=$this->input->get('id');
		   }
		   if($this->products_model->deleteSerial($id)==true){
			  $this->session->set_flashdata('message', lang('Serial_was_delete'));
              redirect($_SERVER["HTTP_REFERER"]);
		   }else{
			   $this->session->set_flashdata('error', lang('Serial_can_not_delete'));
               redirect($_SERVER["HTTP_REFERER"]);
		   }
		   
	}

}
