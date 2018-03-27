<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Documents extends MY_Controller
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
        $this->load->model('companies_model');
		$this->load->model('accounts_model');
		$this->load->model('documents_model');
		$this->load->model('products_model');
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
        //$this->erp->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('catalog')));
        $meta = array('page_title' => lang('catalog'), 'bc' => $bc);
        $this->page_construct('documents/index', $meta, $this->data);
    }
	
	function check_code_available($term = NULL){
		$term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $row = $this->documents_model->getProductCode($term);
        if ($row) {
            echo 1;
        } else {
            echo 0;
        }
	}
	
	function add()
	{
		$this->erp->checkPermissions('add', true, 'documents');
		$this->form_validation->set_rules('code', $this->lang->line("code"), 'required');
		if ($this->form_validation->run('documents/add') == true) {
			$image = $this->input->post('product_image');
			$product_code = $this->input->post('code');
			$product_name = $this->input->post('product_name');
			$description = $this->input->post('description');
			$brand_id = $this->input->post('brand');
			$cate_id = $this->input->post('category');
			$scate_id = $this->input->post('subcategory');
			$cost = $this->input->post('cost');
			$unit = $this->input->post('unit');
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
			//$this->erp->print_arrays($document);
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
                    redirect("products/add");
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
			
		}

		if ($this->form_validation->run() == true && $sid = $this->documents_model->addDocument($document, $photos)) {
			$this->session->set_flashdata('message', $this->lang->line("document_added"));
			$ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : NULL;
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$this->data['brands'] = $this->site->getAllBrands();
            $this->data['categories'] = $this->site->getAllCategories();
			$this->data['products'] = $this->products_model->getAllProducts();
			$this->data['unit']  = $this->products_model->getUnits();
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'documents/add', $this->data);
		}
	}
	
	function getProducts($id = NULL)
	{
		$product = $this->site->getProductByID($id);
		echo json_encode(array('quantity' => $product->quantity));
	}
	
	function getDocuments()
	{
		//$this->erp->checkPermissions('index', true, 'accounts');

		$this->load->library('datatables');
		$this->datatables
		->select("documents.id,documents.image, documents.product_name, brands.name as bname, categories.name as cname, subcategories.name as sname, documents.price, products.quantity")
		->from("documents")
		->join('products', 'documents.product_code=products.code', 'left')
		->join('brands', 'documents.brand_id=brands.id', 'left')
		->join('categories', 'documents.category_id=categories.id', 'left')
		->join('subcategories', 'documents.subcategory_id=subcategories.id', 'left')
		->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("add_quotation") . "' href='" . site_url('quotes/add/$1') . "'><i class=\"fa fa-save\"></i></a> 
        <a class=\"tip\" title='" . $this->lang->line("add_purchases_order") . "' href='" . site_url('purchases_order/add/0/$1') . "'><i class=\"fa fa-plus-circle\"></i></a> 
		<a class=\"tip\" title='" . $this->lang->line("edit_catalog") . "' href='" . site_url('documents/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_catalog") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('documents/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "documents.id");
		echo $this->datatables->generate();
	}
	
	function modal_view($id = NULL)
    {
		$pr_details = $this->documents_model->getDocumentByID($id);
        $this->data['product'] = $pr_details;
        $this->data['images'] =  $this->documents_model->getDocumentPhoto($id);
		$this->data['brand'] = $this->documents_model->getBrandInfoByID($pr_details->brand_id);
		$this->data['UOM'] = $this->documents_model->getUnitByID($pr_details->unit);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->products_model->getSubCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);

        $this->load->view($this->theme.'documents/modal_view', $this->data);
    }
	
	function edit($id = NULL)
	{
		$this->erp->checkPermissions('edit', true, 'documents');
		$this->form_validation->set_rules('code', $this->lang->line("code"), 'required');
		if ($this->form_validation->run('documents/add') == true) {
			$image = $this->input->post('product_image');
			$product_code = $this->input->post('code');
			$product_name = $this->input->post('product_name');
			$description = $this->input->post('description');
			$brand_id = $this->input->post('brand');
			$cate_id = $this->input->post('category');
			$scate_id = $this->input->post('subcategory');
			$unit = $this->input->post('unit');
			$price = $this->input->post('price');
			$cost = $this->input->post('cost');
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
			//$this->erp->print_arrays($document);
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
                    redirect("products/add");
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
			
		}

		if ($this->form_validation->run() == true && $sid = $this->documents_model->updateDocument($id, $document, $photos)) {
			$this->session->set_flashdata('message', $this->lang->line("document_updated"));
			$ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : NULL;
			redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$catalog = $this->documents_model->getDocumentByID($id);
			//$this->erp->print_arrays($catalog);
			$this->data['id'] = $id;
			$this->data['catalog'] = $catalog;
			$this->data['brands'] = $this->site->getAllBrands();
			$this->data['categories'] = $this->site->getAllCategories();
			$this->data['products'] = $this->products_model->getAllProducts();
			$this->data['unit']  = $this->products_model->getUnits();
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'documents/edit', $this->data);
		}
	}
	
	function delete($id = NULL)
	{
		$this->erp->checkPermissions('delete', TRUE, 'documents');

		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}

		if ($this->documents_model->deleteDocumentByID($id)) {
			echo $this->lang->line("deleted_catalog");
		} else {
			$this->session->set_flashdata('warning', lang('catalog_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
	}
	
	function add_quotation($id = NULL)
	{
		$items = $this->documents_model->getDocumentByID($id);
		$photo = $this->documents_model->getDocumentPhoto($id); 
		$data = array(
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
			$row = $this->documents_model->getProductByCode($item->product_code);
			if (!$row) {
				$row = json_decode('{}');
				$row->tax_method = 0;
			} else {
				unset($row->details, $row->product_details, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
			}
			$row->quantity = 0;
			$combo_items = FALSE;
			if ($row->tax_rate) {
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				$pr[] = array('id' => 1, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'options' => $options, 'makeup_cost' => 0);
			} else {
				$pr[] = array('id' => 1, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => false, 'options' => $options, 'makeup_cost' => 0);
			}
			$this->data['quote_items'] = json_encode($pr);
		}else{
			
		}
		
		$this->page_construct('quotes/add', $meta, $this->data);
	}
}

?>