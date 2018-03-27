<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Products_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function insertConvert($data)
    {
        if ($this->db->insert('convert', $data)) {
            $convert_id = $this->db->insert_id();
			
			if ($this->site->getReference('con') == $data['reference_no']) {
				$this->site->updateReference('con');
			}
            return $convert_id;
        }
    }
	public function getProductStatus($id){
		
		$this->db->select("erp_products.type,erp_products.id");
		$q = $this->db->get_where("erp_products",array('erp_products.id'=>$id),1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function updateConvert($id, $data) {
        if ($this->db->update('convert', $data, array('id' => $id))) {
            return true;
        }
        return false;
	}
	public function getproductbyIds($id){
		$this->db->select('erp_products.code')
		         ->join('erp_products','erp_products.id=erp_serial.product_id','left');
	    $q=$this->db->get_where('erp_serial',array('erp_serial.id'=>$id),1);
		if($q->num_rows()>0){
			return $q->row();
		}
		return false;
	}
	public function exportserail($id){
		$this->db->select('erp_serial.id,erp_brands.name as brandname,erp_products.code as pro_code,erp_products.name as product_name,erp_companies.company,erp_warehouses.name as warehouse,erp_serial.serial_number as number,erp_serial.serial_status')
		        ->join('erp_products','erp_products.id=erp_serial.product_id','left')
				->join('erp_companies','erp_companies.id=erp_serial.biller_id','left')
				->join('erp_brands','erp_brands.id=erp_products.brand_id','left')
				->join('erp_warehouses','erp_warehouses.id=erp_serial.warehouse','left');
		$q=$this->db->get_where('erp_serial',array('erp_serial.id'=>$id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
	}
	public function deleteSerial($id){
		$this->db->where('erp_serial.id',$id);
		if($this->db->delete('erp_serial')){ 
			return true;
		} 
	}

    //========= Add Function to delete / Add on adjust stock =========

    public function deleteSerialByArr($arr)
    {
        foreach ($arr as $id) {
            $this->db->where('id',$id);
            $this->db->delete('serial');
        }
        return TRUE;
    }
	public function deleteSerialBySerial($arr)
    {
        foreach ($arr as $id) {
            $this->db->where('serial_number', $id);
            $this->db->delete('serial');
        }
        return TRUE;
    }
    public function addSerialNumber($arr, $product_id, $warehouse_id, $biller_id)
    {
        $array = explode(',', $arr);
        foreach ($array as $serial) {
            $data = array(
                    'product_id'    => $product_id,
                    'serial_number' => $serial,
                    'warehouse'     => $warehouse_id,
                    'biller_id'     => $biller_id,
                    'serial_status'     => 1
                );
            $this->db->insert('serial', $data);
        }
        return TRUE;
        
    }

    //=========================== End ===============================

	public function getConvertByID($id)
    {
		$this->db->select("convert.id, convert.date, convert.reference_no, SUM(".$this->db->dbprefix('convert_items').".quantity) AS Quantity, products.cost, convert_items.product_name, convert.noted, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as user, convert.created_by, convert.warehouse_id")
             ->join('users', 'users.id=convert.created_by', 'left')
			 ->join('convert_items', 'convert.id=convert_items.convert_id', 'left')
			 ->join('products', 'convert_items.product_id = products.id');
        $q = $this->db->get_where('convert', array('convert.id' => $id,'convert_items.status ='=>'add'), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function ConvertDeduct($id)
    {
        $this->db->select('product_name,'.$this->db->dbprefix('convert_items').'.quantity AS Cquantity,'.$this->db->dbprefix('convert_items').'.cost AS Ccost,'.$this->db->dbprefix('products').'.cost AS Pcost')
				->join('products', 'products.id=convert_items.product_id');
		$q = $this->db->get_where('convert_items', array('convert_id' => $id, 'status' => 'deduct'));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
	
	public function ConvertAdd($id)
    {
       $this->db->select('product_name,'.$this->db->dbprefix('convert_items').'.quantity AS Cquantity,'.$this->db->dbprefix('convert_items').'.cost AS Ccost,'.$this->db->dbprefix('products').'.cost AS Pcost')
				->join('products', 'products.id=convert_items.product_id');
		$q = $this->db->get_where('convert_items', array('convert_id' => $id, 'status' => 'add'));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
	
	public function getConvert_ItemByID($id)
    {
		$this->db->select('convert_items.id, convert_items.convert_id, 
							convert_items.product_id, convert_items.product_code, 
							convert_items.product_name, convert_items.quantity, convert_items.cost, convert_items.status, products.unit')
							->join('products', 'products.id = convert_items.product_id', 'left');
		$q = $this->db->get_where("convert_items", array('convert_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function deleteConvert($id)
    {
        if ($this->db->delete('convert', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function deleteConvert_items($id)
    {
        if ($this->db->delete('convert_items', array('convert_id' => $id))) {
            return true;
        }
        return FALSE;
    }
	public function deleteConvert_itemsByPID($id, $product_id)
    {
        if ($this->db->delete('convert_items', array('convert_id' => $id, 'product_id' => $product_id))) {
            return true;
        }
        return FALSE;
    }

    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }
	
	public function addDocument($document, $photos)
	{
		 if ($this->db->insert('documents', $document)) {
            $product_id = $this->db->insert_id();
			
			if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('document_photos', array('document_id' => $product_id, 'photo' => $photo));
                }
            }
			return true;
		 }
		 return false;
	}
	
	public function getgallary_image($id=null){
		 $this->db->SELECT('erp_product_photos.photo')
		          ->join('erp_products','erp_products.id=erp_product_photos.product_id','LEFT');
		$q= $this->db->get_where('erp_product_photos',array('erp_products.id'=>$id));
		if($q->num_rows()>0){
			foreach(($q->result()) as $row){
				$data[] = $row;
			}
           return $data;
        }
        return FALSE;
	}
	public function getproduct($id = null){
		$this->db->SELECT('erp_products.name as product_name,erp_brands.id as brand_id,erp_categories.id as cate_id,erp_subcategories.id as subcate_id,erp_units.id as unit_id,erp_products.cost,erp_products.code,erp_products.price,erp_products.image,erp_products.is_serial,erp_product_photos.photo,erp_products.product_details,erp_brands.name as brand,erp_categories.name as category,erp_subcategories.name as subcategory,erp_units.name as unit')
		->join('erp_brands','erp_products.brand_id = erp_brands.id','LEFT')
		->join('erp_categories','erp_categories.id = erp_products.category_id','LEFT')
		->join('erp_subcategories','erp_subcategories.id = erp_products.subcategory_id','LEFT')
		->join('erp_units','erp_units.id = erp_products.unit','LEFT')
		->join('erp_product_photos','erp_product_photos.product_id = erp_products.id','LEFT');
		 $q = $this->db->get_where('erp_products',array('erp_products.id' => $id),1);
		
        if ($q->num_rows() > 0) {
           return $q->row();
        }
		return false;
	}
	public function getCategoryProducts($category_id)
    {
        $q = $this->db->get_where('products', array('category_id' => $category_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSubCategoryProducts($subcategory_id)
    {
        $q = $this->db->get_where('products', array('subcategory_id' => $subcategory_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
	public function getProductWithCategory($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('categories') . '.name as category')
        ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductOptions($pid)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductOptionsWithWH($pid)
    {
        $this->db->select($this->db->dbprefix('product_variants') . '.*, ' . $this->db->dbprefix('warehouses') . '.name as wh_name, ' . $this->db->dbprefix('warehouses') . '.id as warehouse_id, ' . $this->db->dbprefix('warehouses_products_variants') . '.quantity as wh_qty, ' . $this->db->dbprefix('product_variants') . '.qty_unit, ('.$this->db->dbprefix('product_variants').'.cost * '.$this->db->dbprefix('product_variants').'.qty_unit) AS variant_cost ')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->join('warehouses', 'warehouses.id=warehouses_products_variants.warehouse_id', 'left')
            ->group_by(array('' . $this->db->dbprefix('product_variants') . '.id', '' . $this->db->dbprefix('warehouses_products_variants') . '.warehouse_id'))
            ->order_by('product_variants.qty_unit DESC');
        $q = $this->db->get_where('product_variants', array('product_variants.product_id' => $pid, 'warehouses_products_variants.quantity !=' => NULL));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getProductComboItems($pid)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . 
						$this->db->dbprefix('products') . '.code as code, ' . 
						$this->db->dbprefix('products') . '.quantity as qty, ' . 
						$this->db->dbprefix('products') . '.name as name, ' .
						$this->db->dbprefix('products') . '.cost as cost, ' .	
						$this->db->dbprefix('combo_items') . '.unit_price as price, ' . 
						$this->db->dbprefix('combo_items') . '.quantity as cqty')
				 ->join('products', 'products.code=combo_items.item_code', 'left')
				 ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', array('product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
    }
	
	public function getProductCombos($pid, $pcode)
    {
		$code = $this->erp->subLastStr($pcode);
        $this->db->select('products.name, products.code, products.price, products.image, erp_categories.`name` AS category_name, erp_subcategories.`name` AS subcategory_name')
				 ->join('combo_items', 'products.code=combo_items.item_code', 'left')
				 ->join('categories', 'products.category_id=categories.id', 'left')
				 ->join('subcategories', 'products.subcategory_id=subcategories.id', 'left')
				 ->where('combo_items.product_id', $pid)
				 ->where('combo_items.item_code !=', $code);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

    public function getProductByID($id)
    {
		$this->db->select('products.*, units.name as un_name')
				 ->join('units', 'units.id = products.unit', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function has_purchase($product_id, $warehouse_id = NULL)
    {
        if($warehouse_id) { $this->db->where('warehouse_id', $warehouse_id); }
        $q = $this->db->get_where('purchase_items', array('product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function getProductDetails($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . '.name, ' . $this->db->dbprefix('categories') . '.code as category_code, cost, price, quantity, alert_quantity')
            ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductDetail($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, 
			' . $this->db->dbprefix('tax_rates') . '.rate as tax_rate_code, 
			' . $this->db->dbprefix('categories') . '.name as category_name, 
			' . $this->db->dbprefix('subcategories') . '.name as subcategory_name,
			' . $this->db->dbprefix('brands') . '.name as brand,
			' . $this->db->dbprefix('units') . '.name as unit_name, 
			(CASE WHEN '.$this->db->dbprefix('products').'.tax_method = 0 THEN "inclusive" ELSE "exclusive" END) as tax_method, "" as variant,
			' . $this->db->dbprefix('warehouses') . '.name as wname,
			' . $this->db->dbprefix('case') . '.name as case_meterial,
			' . $this->db->dbprefix('dials') . '.name as dial,
			' . $this->db->dbprefix('strap') . '.name as strap,
			' . $this->db->dbprefix('water_resistance') . '.name as water,
			' . $this->db->dbprefix('winding') . '.name as winding,
			' . $this->db->dbprefix('power_reserve') . '.name as power,
			' . $this->db->dbprefix('buckle') . '.name as buckle,
			' . $this->db->dbprefix('complication') . '.name as complications,
			' . $this->db->dbprefix('diameter') . '.name as diameter')
            ->join('tax_rates', 'tax_rates.id=products.tax_rate', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->join('subcategories', 'subcategories.id=products.subcategory_id', 'left')
			->join('brands', 'brands.id=products.brand_id', 'left')
			->join('units', 'units.id=products.unit', 'left')
			->join('case', 'case.id=products.cf1', 'left')
			->join('diameter', 'diameter.id=products.cf2', 'left')
			->join('dials', 'dials.id=products.cf3', 'left')
			->join('strap', 'strap.id=products.cf4', 'left')
			->join('water_resistance', 'water_resistance.id=products.cf5', 'left')
			->join('winding', 'winding.id=products.cf6', 'left')
			->join('power_reserve', 'power_reserve.id=products.cf7', 'left')
			->join('buckle', 'buckle.id=products.cf8', 'left')
			->join('complication', 'complication.id=products.cf9', 'left')
			->join('warehouses', 'warehouses.id=products.warehouse', 'left')
			->group_by("products.id")->order_by('products.id desc');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductByCategoryID($id)
    {

        $q = $this->db->get_where('products', array('category_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return true;
        }

        return FALSE;
    }

    public function getAllWarehousesWithPQ($product_id)
    {
        $this->db->select('' . $this->db->dbprefix('warehouses') . '.*, ' . $this->db->dbprefix('warehouses_products') . '.quantity, ' . $this->db->dbprefix('warehouses_products') . '.rack')
            ->join('warehouses_products', 'warehouses_products.warehouse_id=warehouses.id', 'left')
            ->where('warehouses_products.product_id', $product_id)
            ->group_by('warehouses.id');
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where('warehouses.id', $this->session->userdata('warehouse_id'));
		}
		
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductPhotos($id)
    {
        $q = $this->db->get_where("product_photos", array('product_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }
	public function getOptionId($product_id,$name)
    {

        $q = $this->db->get_where('product_variants', array('product_id' => $product_id, 'name'=>$name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
	public function getProductVariantByOptionID($option_id)
    {
        $q = $this->db->get_where('product_variants', array('id' => $option_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
    public function getProductByCode($code)
    {
		$q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $related_products = NULL, $q_code = NULL)
    {
		//$this->erp->print_arrays($data, $items, $warehouse_qty, $product_attributes, $photos, $q_code);
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();

            if ($items) {
                foreach ($items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('combo_items', $item);
                }
            }

            if ($data['type'] == 'combo' || $data['type'] == 'service') {
                $warehouses = $this->site->getAllWarehouses();
                foreach ($warehouses as $warehouse) {
                    $this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0));
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    if (isset($wh_qty['quantity']) && ! empty($wh_qty['quantity'])) {
                        $this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $wh_qty['warehouse_id'], 'quantity' => $wh_qty['quantity'], 'rack' => $wh_qty['rack']));

                        if (!$product_attributes) {
                            $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
                            $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
                            $unit_cost = $data['cost'];
                            if ($tax_rate) {
                                if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                    if ($data['tax_method'] == '0') {
                                        $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                        $net_item_cost = $data['cost'] - $pr_tax_val;
                                        $item_tax = $pr_tax_val * $wh_qty['quantity'];
                                    } else {
                                        $net_item_cost = $data['cost'];
                                        $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                        $unit_cost = $data['cost'] + $pr_tax_val;
                                        $item_tax = $pr_tax_val * $wh_qty['quantity'];
                                    }
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $item_tax = $tax_rate->rate;
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = 0;
                            }

                            $subtotal = (($net_item_cost * $wh_qty['quantity']) + $item_tax);

                            $item = array(
                                'product_id' => $product_id,
                                'product_code' => $data['code'],
                                'product_name' => $data['name'],
                                'net_unit_cost' => $net_item_cost,
                                'unit_cost' => $unit_cost,
                                'quantity' => $wh_qty['quantity'],
                                'quantity_balance' => $wh_qty['quantity'],
                                'item_tax' => $item_tax,
                                'tax_rate_id' => $tax_rate_id,
                                'tax' => $tax,
                                'subtotal' => $subtotal,
                                'warehouse_id' => $wh_qty['warehouse_id'],
                                'date' => date('Y-m-d'),
                                'status' => 'received',
                            );
                            $this->db->insert('purchase_items', $item);
                            $this->site->syncProductQty($product_id, $wh_qty['warehouse_id']);
                        }
                    }
                }
            }

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {
                    $pr_attr_details = $this->getPrductVariantByPIDandName($product_id, $pr_attr['name']);

                    $pr_attr['product_id'] = $product_id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    if ($pr_attr_details) {
                        $option_id = $pr_attr_details->id;
                    } else {
                        $this->db->insert('product_variants', $pr_attr);
                        $option_id = $this->db->insert_id();
                    }
                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']));

                        $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
                        $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
                        $unit_cost = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost = $data['cost'] + $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item = array(
                            'product_id' => $product_id,
                            'product_code' => $data['code'],
                            'product_name' => $data['name'],
                            'net_unit_cost' => $net_item_cost,
                            'unit_cost' => $unit_cost,
                            'quantity' => $pr_attr['quantity'],
                            'option_id' => $option_id,
                            'quantity_balance' => $pr_attr['quantity'],
                            'item_tax' => $item_tax,
                            'tax_rate_id' => $tax_rate_id,
                            'tax' => $tax,
                            'subtotal' => $subtotal,
                            'warehouse_id' => $variant_warehouse_id,
                            'date' => date('Y-m-d'),
                            'status' => 'received',
                        );
                        $this->db->insert('purchase_items', $item);

                    }

                    $this->site->syncVariantQty($option_id, $variant_warehouse_id);
                }
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', array('product_id' => $product_id, 'photo' => $photo));
                }
            }
			
			if($q_code){
				$this->db->update('quote_items', array('product_id'=>$product_id, 'product_type' => $data['type']), array('product_code'=>$q_code) );
			}
			
			if ($related_products) {
				foreach ($related_products as $related_product) {
                    $this->db->insert('related_products', $related_product);
                }
			}
			
            return true;
        }

        return false;

    }

    public function getPrductVariantByPIDandName($product_id, $name)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id, 'name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function addAjaxProduct($data)
    {

        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
            return $this->getProductByID($product_id);
        }

        return false;

    }
	
	public function insertSerials($data)
	{
		if($this->db->insert_batch('serial', $data)){
			return true;
		}
		return false;
	}
	
	public function checkserial($product_id){
		$q = $this->db->get_where('serial', array('product_id' => $product_id, 'serial_status !=' => 1), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
	}
	
	public function getSerialbyId($id){
		$q = $this->db->get_where('serial', array('id'=> $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
	}

    public function add_products($serial = array(), $products = array(), $combo = array(), $combo_item = array(), $type, $brand, $category, $subcategory, $units, $cases, $diameters, $dials, $straps, $waters, $winds, $powers, $buckles, $complication)
    {
		//$this->erp->print_arrays($complication);
		if(!empty($brand)){
			$this->db->insert_batch('brands', $brand);
		}
		if(!empty($category)){
			$categories = array();
			foreach($category as $cate){
				$bra = $this->getBrandByName($cate['brand_id']);
				$categories[] = array('brand_id'=>$bra->id, 'name'=>$cate['name']);
			}
			$this->db->insert_batch('categories', $categories);
		}
		if(!empty($subcategory)){
			$subcategories = array();
			foreach($subcategory as $sub){
				$bra = $this->getBrandByName($sub['brand_id']);
				$cat = $this->getCategoryByName($sub['category_id']);
				$subcategories[] = array('brand_id'=>$bra->id, 'category_id'=>$cat->id, 'name'=>$sub['name']);
			}
			$this->db->insert_batch('subcategories', $subcategories);
		}
		if(!empty($units)){
			$this->db->insert_batch('units', $units);
		}
		if(!empty($cases)){
			$this->db->insert_batch('case', $cases);
		}
		if(!empty($diameters)){
			$this->db->insert_batch('diameter', $diameters);
		}
		if(!empty($dials)){
			$this->db->insert_batch('dials', $dials);
		}
		if(!empty($straps)){
			$this->db->insert_batch('strap', $straps);
		}
		if(!empty($waters)){
			$this->db->insert_batch('water_resistance', $waters);
		}
		if(!empty($winds)){
			$this->db->insert_batch('winding', $winds);
		}
		if(!empty($powers)){
			$this->db->insert_batch('power_reserve', $powers);
		}
		if(!empty($buckles)){
			$this->db->insert_batch('buckle', $buckles);
		}
		if(!empty($complication)){
			$this->db->insert_batch('complication', $complication);
		}
        if (!empty($products)) {
			//$this->erp->print_arrays($products);
			foreach ($products as $product) {
				$variants = explode('|', $product['variants']);
				unset($product['variants']);
				unset($product['pcost']);
				unset($product['pprice']);
				
				unset($product['ccode1']);
				unset($product['cprice1']);
				unset($product['ccode2']);
				unset($product['cprice2']);
				unset($product['ccode3']);
				unset($product['cprice3']);
				unset($product['ccode4']);
				unset($product['cprice4']);
				
				$brad = $this->products_model->getBrandByName(trim($product['brand_id']));
				$product['brand_id'] = $brad->id;
				$catd = $this->products_model->getCategoryByName(trim($product['category_id']));
				$product['category_id'] = $catd->id;
				$subd = $this->products_model->getSubcategoryByName(trim($product['subcategory_id']));
				$product['subcategory_id'] = $subd->id;
				$unit = $this->products_model->getUnitByName(trim($product['unit']));
				$product['unit'] = $unit->id;
				$case = $this->products_model->getCaseByName(trim($product['cf1']));
				$product['cf1'] = $case->id;
				$diameter = $this->products_model->getDiameterByName(trim($product['cf2']));
				$product['cf2'] = $diameter->id;
				$dial = $this->products_model->getDialByName(trim($product['cf3']));
				$product['cf3'] = $dial->id;
				$strap = $this->products_model->getStrapByName(trim($product['cf4']));
				$product['cf4'] = $strap->id;
				$water = $this->products_model->getWaterRByName(trim($product['cf5']));
				$product['cf5'] = $water->id;
				$wind = $this->products_model->getWindingByName(trim($product['cf6']));
				$product['cf6'] = $wind->id;
				$power = $this->products_model->getPowerReserveByName(trim($product['cf7']));
				$product['cf7'] = $power->id;
				$buckle = $this->products_model->getBuckleByName(trim($product['cf8']));
				$product['cf8'] = $buckle->id;
				$compli = $this->products_model->getComplicationByName(trim($product['cf9']));
				$product['cf9'] = $compli->id;
				//$this->erp->print_arrays($product);
				if ($this->db->insert('products', $product)) {
					$product_id = $this->db->insert_id();
					foreach ($variants as $variant) {
						if ($variant && trim($variant) != '') {
							$vat = array('product_id' => $product_id, 'name' => trim($variant), 'qty_unit' => 1);
							$this->db->insert('product_variants', $vat);
						}
					}
				}
			}
        }
		if(!empty($combo)){
			$i = 0;
			$cid = array();
			foreach ($combo as $combos) {
				$brad = $this->getBrandByName(trim($combos['brand_id']));
				$combos['brand_id'] = $brad->id;
				$catd = $this->getCategoryByName(trim($combos['category_id']));
				$combos['category_id'] = $catd->id;
				$subd = $this->getSubcategoryByName(trim($combos['subcategory_id']));
				$combos['subcategory_id'] = $subd->id;
				$unit = $this->getUnitByName(trim($combos['unit']));
				$combos['unit'] = $unit->id;
				$case = $this->getCaseByName(trim($combos['cf1']));
				$combos['cf1'] = $case->id;
				$diameter = $this->getDiameterByName(trim($combos['cf2']));
				$combos['cf2'] = $diameter->id;
				$dial = $this->getDialByName(trim($combos['cf3']));
				$combos['cf3'] = $dial->id;
				$strap = $this->getStrapByName(trim($combos['cf4']));
				$combos['cf4'] = $strap->id;
				$water = $this->getWaterRByName(trim($combos['cf5']));
				$combos['cf5'] = $water->id;
				$wind = $this->getWindingByName(trim($combos['cf6']));
				$combos['cf6'] = $wind->id;
				$power = $this->getPowerReserveByName(trim($combos['cf7']));
				$combos['cf7'] = $power->id;
				$buckle = $this->getBuckleByName(trim($combos['cf8']));
				$combos['cf8'] = $buckle->id;
				$compli = $this->getComplicationByName(trim($combos['cf9']));
				$combos['cf9'] = $compli->id;
				
				$variants = explode('|', $combos['variants']);
				$cost  = $combos['pcost'];
				$price = $combos['pprice'];
				unset($combos['variants']);
				unset($combos['pcost']);
				unset($combos['pprice']);
				
				$com1 = array();
				$com2 = array();
				$com3 = array();
				$com3 = array();
				
				$ccode1  = $combos['ccode1'];
				$ccode2  = $combos['ccode2'];
				$ccode3  = $combos['ccode3'];
				$ccode4  = $combos['ccode4'];
				$cprice1 = $combos['cprice1'];
				$cprice2 = $combos['cprice2'];
				$cprice3 = $combos['cprice3'];
				$cprice4 = $combos['cprice4'];
				
				$com1[] = array(
					'item_code' => $ccode1,
					'quantity'  =>'1',
					'unit_price'=>$cprice1
				);
				$com2[] = array(
					'item_code' => $ccode2,
					'quantity'  =>'1',
					'unit_price'=>$cprice2
				);
				$com3[] = array(
					'item_code' => $ccode3,
					'quantity'  =>'1',
					'unit_price'=>$cprice3
				);
				$com4[] = array(
					'item_code' => $ccode4,
					'quantity'  =>'1',
					'unit_price'=>$cprice4
				);
				
				$com = array_merge_recursive($com1, $com2, $com3, $com4);
				
				unset($combos['ccode1']);
				unset($combos['cprice1']);
				unset($combos['ccode2']);
				unset($combos['cprice2']);
				unset($combos['ccode3']);
				unset($combos['cprice3']);
				unset($combos['ccode4']);
				unset($combos['cprice4']);      
				
				if($this->db->insert('products', $combos)){
					$combo_id = $this->db->insert_id();
					$cid[] = $combo_id;
					$code = explode('-',$combos['code']);
                    $code_last = array_pop($code);
                    $codes = array(implode('-', $code), $code_last);

                    $name = explode('-',$combos['name']);
                    $name_last = array_pop($name);
                    $names = array(implode(' - ', $name), $name_last);

					$combos['code'] = $codes[0];
					$combos['name'] = $names[0];
                    
					$combos['cost'] = $cost;
					$combos['price'] = $price;
					$combos['type'] = 'standard';
					
					if ($this->db->insert('products', $combos)) {
						foreach ($variants as $variant) {
							if ($variant && trim($variant) != '') {
								$vat = array('product_id' => $product_id, 'name' => trim($variant), 'qty_unit' => 1);
								$this->db->insert('product_variants', $vat);
							}
						}
						$this->db->insert('combo_items', array('product_id'=>$combo_id, 'item_code'=>$codes[0], 'quantity'=>'1', 'unit_price'=>$price));
						$c_item = array_unique($com, SORT_REGULAR);
						foreach($c_item as $com_item){
							$com_item['product_id'] = $combo_id;
							if($com_item['item_code'] != ''){
								$this->db->insert('combo_items', $com_item);
							}
						}
					}
					
				}
				
				$i++;
			}
			$a = 0;
			foreach($combo_item as $combo_items){
				if($combo_items['code'] != ''){
					$amount_cid = count($cid);
					if($amount_cid > 1){
						$c_id = $cid[$a];
					}else{
						$c_id = $cid[0];
					}
					$brad = $this->getBrandByName(trim($combo_items['brand_id']));
					$combo_items['brand_id'] = $brad->id;
					$catd = $this->getCategoryByName(trim($combo_items['category_id']));
					$combo_items['category_id'] = $catd->id;
					$subd = $this->getSubcategoryByName(trim($combo_items['subcategory_id']));
					$combo_items['subcategory_id'] = $subd->id;
					$unit = $this->getUnitByName(trim($combo_items['unit']));
					$combo_items['unit'] = $unit->id;
					
					if(!$this->getProductByCode($combo_items['code'])){
						$this->db->insert('products', $combo_items);
					}
					$a++;
				}
			}
		}
        return false;
    }

    public function getProductNames($term, $limit = 5)
    {
		$this->db->select('products.*, units.name as uname');
		$this->db->join('units', 'units.id = products.unit');
        $this->db->where("type = 'standard' AND (erp_products.name LIKE '%" . $term . "%' OR erp_products.code LIKE '%" . $term . "%' OR  concat(erp_products.name, ' (', erp_products.code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getProductNumber($term, $limit = 5)
    {
		if(preg_match('/\s/', $term))
		{
			$name = explode(" ", $term);
			$first = $name[0];
			$this->db->select('*')
            ->group_by('products.id');
			$this->db->where('code', $first);
			$this->db->limit($limit);
			$q = $this->db->get('products');
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
				return $data;
			}
		}else
		{			
			$this->db->where("type = 'standard' AND (code LIKE '%" . $term . "%')");
			$this->db->limit($limit);
			$q = $this->db->get('products');
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
				return $data;
			}
		}
	}
	
	public function getProductCode($term)
    {
        $this->db->select('code');
		$q = $this->db->get_where('products', array('code' => $term), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }
	
	public function getSerialProducts($serial)
    {
        $this->db->select('serial_number');
		$q = $this->db->get_where('serial', array('serial_number' => $serial), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }
	
	public function getProductsCode($term)
    {
        $this->db->select();
		$q = $this->db->get_where('products', array('code' => $term), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

	public function getSubcategoryByName($name)
    {
        $q = $this->db->get_where('subcategories', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
	public function updateProductDetail($detail , $code){
		
		$this->db->update('products', $detail, array('code' => $code));
		return true;
	}
	
    public function updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants, $related_products=NULL)
    {
		//$this->erp->print_arrays($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants);
        if ($this->db->update('products', $data, array('id' => $id))) {

            if ($items) {
                $this->db->delete('combo_items', array('product_id' => $id));
                foreach ($items as $item) {
                    $item['product_id'] = $id;
                    $this->db->insert('combo_items', $item);
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    $this->db->update('warehouses_products', array('rack' => $wh_qty['rack']), array('product_id' => $id, 'warehouse_id' => $wh_qty['warehouse_id']));
                }
            }

            if ($update_variants) {
                $this->db->update_batch('product_variants', $update_variants, 'id');
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', array('product_id' => $id, 'photo' => $photo));
                }
            }

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {

                    $pr_attr['product_id'] = $id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    $this->db->insert('product_variants', $pr_attr);
                    $option_id = $this->db->insert_id();

                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']));

                        $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
                        $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
                        $unit_cost = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost = $data['cost'] + $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item = array(
                            'product_id' => $id,
                            'product_code' => $data['code'],
                            'product_name' => $data['name'],
                            'net_unit_cost' => $net_item_cost,
                            'unit_cost' => $unit_cost,
                            'quantity' => $pr_attr['quantity'],
                            'option_id' => $option_id,
                            'quantity_balance' => $pr_attr['quantity'],
                            'item_tax' => $item_tax,
                            'tax_rate_id' => $tax_rate_id,
                            'tax' => $tax,
                            'subtotal' => $subtotal,
                            'warehouse_id' => $variant_warehouse_id,
                            'date' => date('Y-m-d'),
                            'status' => 'received',
                        );
                        $this->db->insert('purchase_items', $item);

                    }
                }
            }
			
			if ($related_products) {
				foreach ($related_products as $related_product) {
                    $this->db->insert('related_products', $related_product);
                }
			}

            $this->site->syncQuantity(NULL, NULL, NULL, $id);
            return true;
        } else {
            return false;
        }
    }

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            if ($this->db->update('warehouses_products_variants', array('quantity' => $quantity), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        }
        return FALSE;
    }

    public function getPurchasedItemDetails($product_id, $warehouse_id, $option_id = NULL)
    {
        $q = $this->db->get_where('purchase_items', array('product_id' => $product_id, 'option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchasedItemDetailsWithOption($product_id, $warehouse_id, $option_id)
    {
        $q = $this->db->get_where('purchase_items', array('product_id' => $product_id, 'purchase_id' => NULL, 'transfer_id' => NULL, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updatePrice($data = array(), $combo = array())
    {
        $this->db->update_batch('products', $combo, 'code');
        $this->db->update_batch('products', $data, 'code');
    }
	
	public function updateQuantityExcel($data = array())
    {
		if ($this->db->update_batch('products', $data, 'code')) {
            return true;
        } else {
            return false;
        }
    }
	
	public function updateQuantityExcelWarehouse($data = array())
    {
		foreach($data as $value){
			$que_data=array('quantity'=>$value['quantity']);
			$where = array(
			'product_id'=>$value['product_id'],
			'warehouse_id'=>$value['warehouse_id']
			);
			$this->db->update('warehouses_products',$que_data,$where);
		}
		return true;
    }
	
	public function updateQuantityExcelVar($data = array())
    {
		foreach($data as $value){
			$que_data=array('quantity'=>$value['quantity'],'option_id'=>$value['option_id']);
			$where = array(
			'product_id'=>$value['product_id'],
			'warehouse_id'=>$value['warehouse_id']
			);
			$this->db->update('warehouses_products_variants',$que_data,$where);
		}
		return true;
    }
	
	public function updateQuantityExcelPurchase($data = array())
    {
		$pur_data 	= array();
		$combo		= array();
		foreach($data as $value){
			$this->db->select('*');
			$this->db->from('products');
			$this->db->where(array('id'=>$value['product_id']));
			$prod=$this->db->get();
			
			//========== Get Product Id From Combo =============//
			$this->db->select('*');
			$this->db->from('combo_items');
			$this->db->where(array('item_code'=>$prod->row()->code));
			$comb = $this->db->get();
			//====================== End =======================//
			
            if ($comb->num_rows() > 0) {
                $combo_item = $this->site->getProductComboItems($comb->row()->product_id);
                foreach($combo_item as $comboItem){
                    $pur_data[] = array(
                        'product_id'        => $comboItem->id,
                        'product_code'      => $comboItem->code,
                        'product_name'      => $comboItem->name,
                        'warehouse_id'      => $value['warehouse_id'],
                        'option_id'         => $value['option_id'],
                        'quantity'          => $value['quantity_balance'],
                        'quantity_balance'  => $value['quantity_balance'],
                        'quantity_received' => $value['quantity_balance'],
                        'net_unit_cost'     => $comboItem->cost,
                        'unit_cost'         => $comboItem->cost,
                        'real_unit_cost'    => $comboItem->cost,
                        'subtotal'          => $comboItem->cost * $value['quantity_balance'],
                        'status'            => 'received',
                        'transaction_type'  => 'opening',
                        'date'              => date('Y-m-d')
                    );
                }
                
                //=================== Get Id Combo ==================//
                $combo_id = 0;
                $this->db->select('*,
                    "combo" as product_type, 
                    "'.$value['warehouse_id'].'" as warehouse_id'
                );
                $this->db->from('combo_items');
                $this->db->where(array('item_code' => $prod->row()->code));
                $combo[] = $this->db->get()->result();
                //======================== End ======================//
            } else {
                $combo_item = $this->site->getProductByIDs($value['product_id']);
                foreach($combo_item as $comboItem){
                    $pur_data[] = array(
                        'product_id'        => $comboItem->id,
                        'product_code'      => $comboItem->code,
                        'product_name'      => $comboItem->name,
                        'warehouse_id'      => $value['warehouse_id'],
                        'option_id'         => $value['option_id'],
                        'quantity'          => $value['quantity_balance'],
                        'quantity_balance'  => $value['quantity_balance'],
                        'quantity_received' => $value['quantity_balance'],
                        'net_unit_cost'     => $comboItem->cost,
                        'unit_cost'         => $comboItem->cost,
                        'real_unit_cost'    => $comboItem->cost,
                        'subtotal'          => $comboItem->cost * $value['quantity_balance'],
                        'status'            => 'received',
                        'transaction_type'  => 'opening',
                        'date'              => date('Y-m-d')
                    );
                }
                
                //=================== Get Id Combo ==================//
                $combo_id = 0;
                $this->db->select('products.id as product_id, 
                    "standard" as product_type, 
                    "'.$value['warehouse_id'].'" as warehouse_id'
                );
                $this->db->from('products');
                $this->db->where(array('id' => $value['product_id']));
                $combo[] = $this->db->get()->result();
                //======================== End ======================//          
            }

		}
		//$this->erp->print_arrays($pur_data, $combo);
		$this->db->insert_batch('purchase_items', $pur_data);
		
		foreach($combo as $comItem){
			$this->site->syncQuantity(NULL, NULL, $comItem);
		}

		return true;
    }
	
	public function insertSerialkey($data){
		if ($this->db->insert_batch('serial', $data)){
			return true;
		}else{
			return FALSE;
		}
	}
	
	public function updateSerialkey($data){
		foreach($data as $value){
			$product_id = $value['product_id'];
			unset($value['serial_status']);
			unset($value['product_id']);
			//$this->erp->print_arrays($value);
			$this->db->update('serial', $value , array('product_id' => $product_id));
		}
		return TRUE;
	}

    public function checkCombo($code){
        $q = $this->db->get_where('combo_items', array('item_code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
    public function deleteProduct($id)
    {
		$type = $this->getProductByID($id);
		$pro  = $this->site->getPurchaseItemsByPro($id);
		/*
		if($pro){
			$this->session->set_flashdata('error', lang("product_have_transaction"));
            redirect('products');
		}
		*/
		if($type->type == 'combo'){
			$this->db->delete('combo_items', array('product_id'=>$id));
		}else{
            /*if($this->checkCombo($type->code)){
                $this->session->set_flashdata('error', lang("this_combo"));
                redirect('products');
            }*/
			$this->db->delete('combo_items', array('item_code'=>$type->code) );
        }
        if ($this->db->delete('products', array('id' => $id)) && $this->db->delete('warehouses_products', array('product_id' => $id)) && $this->db->delete('warehouses_products_variants', array('product_id' => $id)) && $this->db->delete('purchase_items', array('product_id' => $id)) && $this->db->delete('serial', array('product_id' => $id)) ) {
            return true;
        }
        return FALSE;
    }

    public function totalCategoryProducts($category_id)
    {
        $q = $this->db->get_where('products', array('category_id' => $category_id));

        return $q->num_rows();
    }

	public function getBrandByID($id)
    {
        $q = $this->db->get_where('brands', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
	public function getBrandByName($name)
    {
        $q = $this->db->get_where('brands', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
    public function getCategoryByCode($code)
    {
        $q = $this->db->get_where('categories', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
	public function getCategoryByID($id)
    {
        $q = $this->db->get_where('categories', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
	public function getCategoryByName($name)
    {
        $q = $this->db->get_where('categories', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getSubcategoryByCode($code)
    {

        $q = $this->db->get_where('subcategories', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

	public function getSubcategoryByID($id)
    {
        $q = $this->db->get_where('subcategories', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getSubCategories()
    {
        $this->db->select('id as id, name as text');
        $q = $this->db->get("subcategories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }

        return FALSE;
    }
	
	public function getCategoriesForBrandID($brand_id)
    {
        $this->db->select('id as id, name as text');
        $q = $this->db->get_where("categories", array('brand_id' => $brand_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }

        return FALSE;
    }

    public function getSubCategoriesForCategoryID($category_id)
    {
        $this->db->select('id as id, name as text');
        $q = $this->db->get_where("subcategories", array('category_id' => $category_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }

        return FALSE;
    }

    public function getSubCategoriesByCategoryID($category_id)
    {
        $q = $this->db->get_where("subcategories", array('category_id' => $category_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }

        return FALSE;
    }

    public function getAdjustmentByID($id)
    {
        $q = $this->db->get_where('adjustments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function syncAdjustment($data = array())
    {

        if(! empty($data)) {

			$pr = $this->site->getProductByID($data['product_id']);
			$item = array(
				'product_id' 		=> $data['product_id'],
				'product_code' 		=> $pr->code,
				'product_name' 		=> $pr->name,
				'net_unit_cost' 	=> 0,
				'unit_cost' 		=> 0,
				'quantity' 			=> 0,
				'adjustment_id'		=> $data['adjustment_id'],
				'option_id' 		=> $data['option_id'],
				'quantity_balance' 	=> $data['type'] == 'subtraction' ? (0 - $data['quantity']) : $data['quantity'],
				'item_tax' 			=> 0,
				'tax_rate_id' 		=> 0,
				'tax' 				=> '',
				'subtotal' 			=> 0,
				'warehouse_id' 		=> $data['warehouse_id'],
				'date' 				=> date('Y-m-d'),
				'status' 			=> 'received',
				'type'				=> 'adjustment'
			);
			$this->db->insert('purchase_items', $item);
            
            $this->site->syncQuantity(null, null, null, $data['product_id']);
        }
    }

    public function reverseAdjustment($id)
    {
        if ($adjustment = $this->getAdjustmentByID($id)) {

            $this->db->delete('purchase_items', array('adjustment_id'=>$adjustment->id) );
			
			if($adjustment->serial_number){
				if ($adjustment->type == "addition"){
					$adjust = explode(',', $adjustment->serial_number);
					$this->db->where_in('serial_number', $adjust);
					$this->db->delete('serial');
				} else {
					$this->addSerialNumber($adjustment->serial_number, $adjustment->product_id, $adjustment->warehouse_id, $adjustment->biller_id);
				}
			}
			
            $this->site->syncProductQty($adjustment->product_id, $adjustment->warehouse_id);
            if ($adjustment->option_id) {
                $this->site->syncVariantQty($adjustment->option_id, $adjustment->warehouse_id, $adjustment->product_id);
            }
        }
    }

    public function addAdjustment($data, $serial_id = NULL)
    {
		//$this->erp->print_arrays($data);
		$p = $this->getProductByID($data['product_id']);
		$serial = $this->site->getProductByID($data['product_id']);
		$have_serial = $serial->is_serial;
		$cost = $p->cost;
		$total_cost = 0;
		if($data['option_id']){
			$option = $this->getProductVariantByOptionID($data['option_id']);
			$total_cost = $cost * ($data['quantity'] * $option->qty_unit);
		}else{
			$total_cost = $cost * $data['quantity'];
		}
		
		$data['cost'] = $cost;
		$data['total_cost'] = $total_cost;
        if ($this->db->insert('adjustments', $data)) {
			$insert_id = $this->db->insert_id();
			$data['adjustment_id'] = $insert_id;
            $this->syncAdjustment($data);
			if($have_serial){
				if($data['type'] == 'addition'){
					$this->addSerialNumber($data['serial_number'], $data['product_id'], $data['warehouse_id'], $data['biller_id']);
				}else{
					
					$this->deleteSerialByArr($serial_id);
				}
			}
            return true;
        }
        return false;
    }

    public function updateAdjustment($id, $data, $serial_array = NULL)
    {
		//$this->erp->print_arrays($data, $serial_array);
        $adj_items 		= $this->getAdjustmentByID($id);
		$get_serial 	= $adj_items->serial_number;
		$serial_number 	= explode(',', $get_serial);
		$old_quantity   = $adj_items->quantity;
		$serial 		= $this->site->getProductByID($data['product_id']);
		$have_serial 	= $serial->is_serial;
		$this->reverseAdjustment($id);
        if ($this->db->update('adjustments', $data, array('id' => $id))) {
			$data['adjustment_id'] = $id;
            $this->syncAdjustment($data);
			if($have_serial){
				if($data['type'] == 'addition'){
					$this->addSerialNumber($data['serial_number'], $data['product_id'], $data['warehouse_id'], $data['biller_id']);
				}else{
					$this->deleteSerialBySerial($serial_array);
				}
			}
            return true;
        }
        return false;
    }

    public function deleteAdjustment($id)
    {
        $this->reverseAdjustment($id);
        if ( $this->db->delete('adjustments', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getProductQuantity($product_id, $warehouse)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return FALSE;
    }

    public function addQuantity($product_id, $warehouse_id, $quantity, $rack = NULL)
    {

        if ($this->getProductQuantity($product_id, $warehouse_id)) {
            if ($this->updateQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return TRUE;
            }
        } else {
            if ($this->insertQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function insertQuantity($product_id, $warehouse_id, $quantity, $rack = NULL)
    {
        if ($this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity, 'rack' => $rack))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity, $rack = NULL)
    {
        $data = $rack ? array('quantity' => $quantity, 'rack' => $rack) : $data = array('quantity' => $quantity);
        if ($this->db->update('warehouses_products', $data, array('product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function products_count($category_id, $subcategory_id = NULL)
    {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->from('products');
        return $this->db->count_all_results();
    }

    public function fetch_products($category_id, $limit, $start, $subcategory_id = NULL)
    {

        $this->db->limit($limit, $start);
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->order_by("id", "asc");
        $query = $this->db->get("products");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function syncVariantQty($option_id)
    {
        $wh_pr_vars = $this->getProductWarehouseOptions($option_id);
        $qty = 0;
        foreach ($wh_pr_vars as $row) {
            $qty += $row->quantity;
        }
        if ($this->db->update('product_variants', array('quantity' => $qty), array('id' => $option_id))) {
            return TRUE;
        }
        return FALSE;
    }

    public function getProductWarehouseOptions($option_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function setRack($data)
    {
        if ($this->db->update('warehouses_products', array('rack' => $data['rack']), array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']))) {
            return TRUE;
        }
        return FALSE;
    }

    public function getSoldQty($id)
    {
        $this->db->select("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('sale_items') . ".quantity ) as sold, SUM( " . $this->db->dbprefix('sale_items') . ".subtotal ) as amount")
            ->from('sales')
            ->join('sale_items', 'sales.id=sale_items.sale_id', 'left')
            ->group_by("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('sale_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
            ->order_by("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchasedQty($id)
    {
        $this->db->select("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('purchase_items') . ".quantity ) as purchased, SUM( " . $this->db->dbprefix('purchase_items') . ".subtotal ) as amount")
            ->from('purchases')
            ->join('purchase_items', 'purchases.id=purchase_items.purchase_id', 'left')
            ->group_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('purchase_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
            ->order_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllVariants()
    {
        $q = $this->db->get('variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	/* ------ Project ----- */
	public function getProjects($id = null){
		$q = $this->db->get('variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	/* ------ Print Barcode Label ------ */
    /* Error
	public function getProductsForPrinting($term){
		$this->db->where('code', $term);
		$query = $this->db->get('products');
		
		if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
		
	}
    */
    
    public function getProductsForPrinting($term, $limit = 5)
    {
        $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getConvertItemsById($convert_id){
		$this->db->select('convert_items.product_id,convert_items.convert_id,convert_items.quantity AS c_quantity ,(erp_products.cost * erp_convert_items.quantity) AS tcost, convert_items.status, products.cost AS p_cost');
		$this->db->join('products', 'products.id = convert_items.product_id', 'INNER');
		$this->db->where('convert_items.convert_id', $convert_id);
		$query = $this->db->get('convert_items');
		
		if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
	}
	
	public function getConvertItemsByIDPID($convert_id, $product_id){
		$this->db->where('convert_id', $convert_id);
		$this->db->where('product_id', $product_id);
		$query = $this->db->get('convert_items');
		if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
	}
	
	public function getConvertItemsAdd($convert_id){
		$this->db->select('convert_items.product_id,convert_items.convert_id, convert_items.quantity AS c_quantity ,(erp_products.cost * erp_convert_items.quantity) AS tcost, convert_items.status');
		$this->db->join('products', 'products.id = convert_items.product_id', 'INNER');
		$this->db->where('convert_items.convert_id', $convert_id);
		$this->db->where('convert_items.status', 'add');
		$query = $this->db->get('convert_items');
		
		if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
	}
	
	public function getConvertItemsDeduct($convert_id){
		$this->db->select('SUM(erp_products.cost * erp_convert_items.quantity) AS tcost, convert_items.status');
		$this->db->join('products', 'products.id = convert_items.product_id', 'INNER');
		$this->db->where('convert_items.convert_id', $convert_id);
		$this->db->where('convert_items.status', 'deduct');
		$query = $this->db->get('convert_items');
		
		if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
	}
	
	public function getAllBoms(){
		$q = $this->db->get('bom');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
	}
	
	public function getAllBom_id($id){
		$this->db->select()
				 ->join('bom_items', 'bom.id = bom_items.bom_id')
				 ->where('bom.id',$id);
		$q = $this->db->get('bom');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
	}

	public function getReference(){
		$this->db->select('reference_no')
				 ->order_by('date', 'desc')
				 ->limit(1);
		$q = $this->db->get('convert');
        if ($q->num_rows() > 0) {
			foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getAllCurrencies()
    {
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getWarehouseQty($product_id, $warehouse_id){
        $this->db->select('SUM(quantity) as quantity')
                 ->from('warehouses_products')
                 ->where(array('product_id'=>$product_id, 'warehouse_id'=>$warehouse_id));
        $q = $this->db->get();
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
	
	public function getRate($rate){
		$this->db->select('*')
                 ->from('tax_rates')
                 ->like('code', $rate);
        $q = $this->db->get();
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
	}
	
	public function getUnits(){
		$this->db->select()
				 ->from('units');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
	
	public function getUnitByName($name){
		$this->db->select()->from('units')->where('name', $name);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getCases(){
		$this->db->select()
				 ->from('case');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
	
	public function getDiameters(){
		$this->db->select()
				 ->from('diameter');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
	
	public function getDials(){
		$this->db->select()
				 ->from('dials');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
	
	public function getStraps(){
		$this->db->select()
				 ->from('strap');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
	
	public function getWater(){
		$this->db->select()
				 ->from('water_resistance');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
	
	public function getWinding(){
		$this->db->select()
				 ->from('winding');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
	public function getPowerReserve(){
		$this->db->select()
				 ->from('power_reserve');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
	public function getBuckle(){
		$this->db->select()
				 ->from('buckle');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
	public function getComplication(){
		$this->db->select()
				 ->from('complication');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
	public function getStrapByProductID($code = NULL) {
		$q = $this->db->get_where('related_products', array('product_code' => $code));
        if ($q->num_rows() > 0) {
            //foreach (($q->result()) as $row) {
                //$data[] = $row;
            //}
            return $q->result();
        }
        return FALSE;
	}
	// cf by id
	
	public function getWindingByID($id){
		$this->db->select()
				 ->from('winding')
				 ->where('id',$id);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getWindingByName($name){
		$this->db->select()
				 ->from('winding')
				 ->where('name',$name);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getPowerReserveByID($id){
		$this->db->select()
				 ->from('power_reserve')
				 ->where('id',$id);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getPowerReserveByName($name){
		$this->db->select()
				 ->from('power_reserve')
				 ->where('name',$name);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getBuckleByID($id){
		$this->db->select()
				 ->from('buckle')
				 ->where('id',$id);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getBuckleByName($name){
		$this->db->select()
				 ->from('buckle')
				 ->where('name',$name);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getComplicationByID($id){
		$this->db->select()
				 ->from('complication')
				 ->where('id',$id);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getComplicationByName($name){
		$this->db->select()
				 ->from('complication')
				 ->where('name',$name);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getCaseByID($id){
		$this->db->select()
				 ->from('case')
				 ->where('id',$id);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getCaseByName($name){
		$this->db->select()
				 ->from('case')
				 ->where('name',$name);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getDiameterByID($id){
		$this->db->select()
				 ->from('diameter')
				 ->where('id',$id);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getDiameterByName($name){
		$this->db->select()
				 ->from('diameter')
				 ->where('name',$name);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getDialByID($id){
		$this->db->select()
				 ->from('dials')
				 ->where('id',$id);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getDialByName($name){
		$this->db->select()
				 ->from('dials')
				 ->where('name',$name);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getStrapByID($id){
		$this->db->select()
				 ->from('strap')
				 ->where('id',$id);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getStrapByName($name){
		$this->db->select()
				 ->from('strap')
				 ->where('name',$name);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getWaterRByID($id){
		$this->db->select()
				 ->from('water_resistance')
				 ->where('id',$id);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getWaterRByName($name){
		$this->db->select()
				 ->from('water_resistance')
				 ->where('name',$name);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getProductserials($id){
		$this->db->select('erp_serial.serial_number')
				 ->from('serial')
				 ->where('id',$id);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
    public function updateserial($id,$data){
		   $this->db->set('serial_number',$data); 
		   $this->db->where('id',$id); 
		   if($this->db->update('erp_serial')){
			   return true;
		   } 
	}
	public function getProductserial($id){
		$this->db->select()
				 ->from('serial')
				 ->where(array('product_id'=> $id, 'serial_status <> ' => 0) );
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}

    public function getSerialSale($id){
        $this->db->select('serial.serial_number, serial.serial_status')
                 ->from('combo_items')
                 ->join('products', 'products.code = combo_items.item_code', 'left')
                 ->join('serial', 'serial.product_id = products.id', 'left')
                 ->where( array('combo_items.product_id'=>$id, 'serial.serial_number != '=>'', 'serial.serial_status <> '=>0) );
        $q = $this->db->get();
        if($q->num_rows() > 0){
            return $q->result();
        }
        return false;
    }

    public function update_products($serial = array(), $products = array(), $combo = array(), $combo_item = array(), $type, $brand, $category, $subcategory, $units, $cases, $diameters, $dials, $straps, $waters, $winds, $powers, $buckles, $complication)
    {
		//$this->erp->print_arrays($products, $combo, $combo_item, $type, $brand, $category, $subcategory, $units, $cases, $diameters, $dials, $straps, $waters, $winds, $powers, $buckles, $complication);
		if(!empty($brand)){
			$this->db->insert_batch('brands', $brand);
		}
		if(!empty($category)){
			$categories = array();
			foreach($category as $cate){
				$bra = $this->getBrandByName($cate['brand_id']);
				$categories[] = array('brand_id'=>$bra->id, 'name'=>$cate['name']);
			}
			$this->db->insert_batch('categories', $categories);
		}
		if(!empty($subcategory)){
			$subcategories = array();
			foreach($subcategory as $sub){
				$bra = $this->getBrandByName($sub['brand_id']);
				$cat = $this->getCategoryByName($sub['category_id']);
				$subcategories[] = array('brand_id'=>$bra->id, 'category_id'=>$cat->id, 'name'=>$sub['name']);
			}
			$this->db->insert_batch('subcategories', $subcategories);
		}
		if(!empty($units)){
			$this->db->insert_batch('units', $units);
		}
		if(!empty($cases)){
			$this->db->insert_batch('case', $cases);
		}
		if(!empty($diameters)){
			$this->db->insert_batch('diameter', $diameters);
		}
		if(!empty($dials)){
			$this->db->insert_batch('dials', $dials);
		}
		if(!empty($straps)){
			$this->db->insert_batch('strap', $straps);
		}
		if(!empty($waters)){
			$this->db->insert_batch('water_resistance', $waters);
		}
		if(!empty($winds)){
			$this->db->insert_batch('winding', $winds);
		}
		if(!empty($powers)){
			$this->db->insert_batch('power_reserve', $powers);
		}
		if(!empty($buckles)){
			$this->db->insert_batch('buckle', $buckles);
		}
		if(!empty($complication)){
			$this->db->insert_batch('complication', $complication);
		}
        if (!empty($products)) {
			//$this->erp->print_arrays($products);
			foreach ($products as $product) {
				$variants = explode('|', $product['variants']);
				unset($product['variants']);
				unset($product['pcost']);
				unset($product['pprice']);
				
				unset($product['ccode1']);
				unset($product['cprice1']);
				unset($product['ccode2']);
				unset($product['cprice2']);
				unset($product['ccode3']);
				unset($product['cprice3']);
				unset($product['ccode4']);
				unset($product['cprice4']);
				
				$brad = $this->products_model->getBrandByName(trim($product['brand_id']));
				$product['brand_id'] = $brad->id;
				$catd = $this->products_model->getCategoryByName(trim($product['category_id']));
				$product['category_id'] = $catd->id;
				$subd = $this->products_model->getSubcategoryByName(trim($product['subcategory_id']));
				$product['subcategory_id'] = $subd->id;
				$unit = $this->products_model->getUnitByName(trim($product['unit']));
				$product['unit'] = $unit->id;
				$case = $this->products_model->getCaseByName(trim($product['cf1']));
				$product['cf1'] = $case->id;
				$diameter = $this->products_model->getDiameterByName(trim($product['cf2']));
				$product['cf2'] = $diameter->id;
				$dial = $this->products_model->getDialByName(trim($product['cf3']));
				$product['cf3'] = $dial->id;
				$strap = $this->products_model->getStrapByName(trim($product['cf4']));
				$product['cf4'] = $strap->id;
				$water = $this->products_model->getWaterRByName(trim($product['cf5']));
				$product['cf5'] = $water->id;
				$wind = $this->products_model->getWindingByName(trim($product['cf6']));
				$product['cf6'] = $wind->id;
				$power = $this->products_model->getPowerReserveByName(trim($product['cf7']));
				$product['cf7'] = $power->id;
				$buckle = $this->products_model->getBuckleByName(trim($product['cf8']));
				$product['cf8'] = $buckle->id;
				$compli = $this->products_model->getComplicationByName(trim($product['cf9']));
				$product['cf9'] = $compli->id;
				//$this->erp->print_arrays($product);
				if ($this->db->update('products', $product, array('code'=>$product['code']))) {
					$product_id = $this->getProductsCode($product['code']);
					foreach ($variants as $variant) {
						if ($variant && trim($variant) != '') {
							$vat = array('name' => trim($variant), 'qty_unit' => 1);
							$this->db->update('product_variants', $vat, array('product_id' => $product_id->id));
						}
					}
				}
			}
        }
		if(!empty($combo)){
			$i = 0;
			$cid = array();
			foreach ($combo as $combos) {
				$brad = $this->getBrandByName(trim($combos['brand_id']));
				$combos['brand_id'] = $brad->id;
				$catd = $this->getCategoryByName(trim($combos['category_id']));
				$combos['category_id'] = $catd->id;
				$subd = $this->getSubcategoryByName(trim($combos['subcategory_id']));
				$combos['subcategory_id'] = $subd->id;
				$unit = $this->getUnitByName(trim($combos['unit']));
				$combos['unit'] = $unit->id;
				$case = $this->getCaseByName(trim($combos['cf1']));
				$combos['cf1'] = $case->id;
				$diameter = $this->getDiameterByName(trim($combos['cf2']));
				$combos['cf2'] = $diameter->id;
				$dial = $this->getDialByName(trim($combos['cf3']));
				$combos['cf3'] = $dial->id;
				$strap = $this->getStrapByName(trim($combos['cf4']));
				$combos['cf4'] = $strap->id;
				$water = $this->getWaterRByName(trim($combos['cf5']));
				$combos['cf5'] = $water->id;
				$wind = $this->getWindingByName(trim($combos['cf6']));
				$combos['cf6'] = $wind->id;
				$power = $this->getPowerReserveByName(trim($combos['cf7']));
				$combos['cf7'] = $power->id;
				$buckle = $this->getBuckleByName(trim($combos['cf8']));
				$combos['cf8'] = $buckle->id;
				$compli = $this->getComplicationByName(trim($combos['cf9']));
				$combos['cf9'] = $compli->id;
				
				$variants = explode('|', $combos['variants']);
				$cost  = $combos['pcost'];
				$price = $combos['pprice'];
				unset($combos['variants']);
				unset($combos['pcost']);
				unset($combos['pprice']);
				
				$com1 = array();
				$com2 = array();
				$com3 = array();
				$com3 = array();
				
				$ccode1  = $combos['ccode1'];
				$ccode2  = $combos['ccode2'];
				$ccode3  = $combos['ccode3'];
				$ccode4  = $combos['ccode4'];
				$cprice1 = $combos['cprice1'];
				$cprice2 = $combos['cprice2'];
				$cprice3 = $combos['cprice3'];
				$cprice4 = $combos['cprice4'];
				
				$com1[] = array(
					'item_code' => $ccode1,
					'quantity'  =>'1',
					'unit_price'=>$cprice1
				);
				$com2[] = array(
					'item_code' => $ccode2,
					'quantity'  =>'1',
					'unit_price'=>$cprice2
				);
				$com3[] = array(
					'item_code' => $ccode3,
					'quantity'  =>'1',
					'unit_price'=>$cprice3
				);
				$com4[] = array(
					'item_code' => $ccode4,
					'quantity'  =>'1',
					'unit_price'=>$cprice4
				);
				
				$com = array_merge_recursive($com1, $com2, $com3, $com4);
				
				unset($combos['ccode1']);
				unset($combos['cprice1']);
				unset($combos['ccode2']);
				unset($combos['cprice2']);
				unset($combos['ccode3']);
				unset($combos['cprice3']);
				unset($combos['ccode4']);
				unset($combos['cprice4']); 
				
				//$this->erp->print_arrays($combos, $combo);
				
				if($this->db->update('products', $combos, array('code'=>$combos['code']))){
					$combo_id = $this->getProductsCode($combos['code']);
					$cid[] = $combo_id->id;
					$code = explode('-',$combos['code']);
                    $code_last = array_pop($code);
                    $codes = array(implode('-', $code), $code_last);

                    $name = explode('-',$combos['name']);
                    $name_last = array_pop($name);
                    $names = array(implode(' - ', $name), $name_last);

					$combos['code'] = $codes[0];
					$combos['name'] = $names[0];

					$combos['cost'] = $cost;
					$combos['price'] = $price;
					$combos['type'] = 'standard';
					if ($this->db->update('products', $combos, array('code'=> $combos['code']))) {
						$product_id = $this->getProductsCode($combos['code']);
						foreach ($variants as $variant) {
							if ($variant && trim($variant) != '') {
								$vat = array('name' => trim($variant), 'qty_unit' => 1);
								$this->db->update('product_variants', $vat, array('product_id' => $product_id));
							}
						}
						
						$this->db->delete('combo_items', array('product_id'=>$combo_id->id));
						
						$this->db->insert('combo_items', array('product_id'=>$combo_id->id, 'item_code'=>$codes[0], 'quantity'=>'1', 'unit_price'=>$price));

						$c_item = array_unique($com, SORT_REGULAR);
						foreach($c_item as $com_item){
							$com_item['product_id'] = $combo_id->id;
							if($com_item['item_code'] != ''){
								$this->db->insert('combo_items', $com_item);
							}
						}
					}
				}
				$i++;
			}
			$a = 0;
			foreach($combo_item as $combo_items){
				if($combo_items['code'] != ''){
					$brad = $this->getBrandByName(trim($combo_items['brand_id']));
					$combo_items['brand_id'] = $brad->id;
					$catd = $this->getCategoryByName(trim($combo_items['category_id']));
					$combo_items['category_id'] = $catd->id;
					$subd = $this->getSubcategoryByName(trim($combo_items['subcategory_id']));
					$combo_items['subcategory_id'] = $subd->id;
					$unit = $this->getUnitByName(trim($combo_items['unit']));
					$combo_items['unit'] = $unit->id;
					
					$this->db->update('products', $combo_items, array('code'=>$combo_items['code']));
					
					//$this->db->update('combo_items', array('unit_price'=>$combo_items['price']), array('item_code'=>$combo_items['code']));
					$a++;
				}
			}
		}
		
        return false;
    }
	
    public function getProductSerialByWarehouse($warehouse_id, $product_id)
    {
        $q = $q=$this->db->get_where('serial',array('product_id'=>$product_id, 'warehouse'=>$warehouse_id, 'serial_status >'=> 0));
        if($q->num_rows()>0){
            return $q->result();
        }
        return false;
    }
	
	public function checkSerialProdduct($product_id, $serial_number){
		$q = $q=$this->db->get_where('serial',array('product_id'=>$product_id, 'serial_number'=>$serial_number));
        if($q->num_rows()>0){
            return $q->row();
        }
        return false;
	}
	
	public function getAdjustmentSerialByWarehouse($warehouse_id, $product_id)
    {
        $q = $q=$this->db->get_where('adjustments',array('product_id'=>$product_id, 'warehouse_id'=>$warehouse_id));
        if($q->num_rows()>0){
            return $q->result();
        }
        return false;
    }
	
	public function getProductByQuoteId($code)
	{
		$this->db->select('product_code as code, product_name as name, unit_price as price, tax_rate_id as tax_rate, "standard" as type');
		$q = $q=$this->db->get_where('quote_items', array('product_code'=>$code));
        if($q->num_rows()>0){
            return $q->row();
        }
        return false;
	}
	
}
