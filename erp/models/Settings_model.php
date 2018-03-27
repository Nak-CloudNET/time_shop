<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function updateLogo($photo)
    {
        $logo = array('logo' => $photo);
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function updateLoginLogo($photo)
    {
        $logo = array('logo2' => $photo);
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function getSettings()
    {
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getAccountSettings()
    {
        $q = $this->db->get('account_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getDateFormats()
    {
        $q = $this->db->get('date_format');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function updateSetting($data)
    {
        $this->db->where('setting_id', '1');
        if ($this->db->update('settings', $data)) {
            return true;
        }
        return false;
    }

    public function addTaxRate($data)
    {
        if ($this->db->insert('tax_rates', $data)) {
            return true;
        }
        return false;
    }

    public function updateTaxRate($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('tax_rates', $data)) {
            return true;
        }
        return false;
    }

    public function getAllTaxRates()
    {
        $q = $this->db->get('tax_rates');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTaxRateByID($id)
    {
        $q = $this->db->get_where('tax_rates', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addWarehouse($data)
    {
        if ($this->db->insert('warehouses', $data)) {
            return true;
        }
        return false;
    }

    public function updateWarehouse($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('warehouses', $data)) {
            return true;
        }
        return false;
    }

    public function getAllWarehouses()
    {
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseByID($id)
    {
        $q = $this->db->get_where('warehouses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteTaxRate($id)
    {
        if ($this->db->delete('tax_rates', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteInvoiceType($id)
    {
        if ($this->db->delete('invoice_types', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteWarehouse($id)
    {
        if ($this->db->delete('warehouses', array('id' => $id)) && $this->db->delete('warehouses_products', array('warehouse_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function addCustomerGroup($data)
    {
        if ($this->db->insert('customer_groups', $data)) {
            return true;
        }
        return false;
    }

    public function updateCustomerGroup($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('customer_groups', $data)) {
            return true;
        }
        return false;
    }

    public function getAllCustomerGroups()
    {
        $q = $this->db->get('customer_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCustomerGroupByID($id)
    {
        $q = $this->db->get_where('customer_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteCustomerGroup($id)
    {
        if ($this->db->delete('customer_groups', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getGroups()
    {
        $this->db->where('id >', 4);
        $q = $this->db->get('groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getGroupByID($id)
    {
        $q = $this->db->get_where('groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getGroupPermissions($id)
    {
        $q = $this->db->get_where('permissions', array('group_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function GroupPermissions($id)
    {
        $q = $this->db->get_where('permissions', array('group_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return FALSE;
    }

    public function updatePermissions($id, $data = array())
    {
        if ($this->db->update('permissions', $data, array('group_id' => $id)) && $this->db->update('users', array('show_price' => $data['products-price'], 'show_cost' => $data['products-cost']), array('group_id' => $id))) {
            return true;
        }
        return false;
    }

    public function addGroup($data)
    {
        if ($this->db->insert("groups", $data)) {
            $gid = $this->db->insert_id();
            $this->db->insert('permissions', array('group_id' => $gid));
            return $gid;
        }
        return false;
    }

    public function updateGroup($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update("groups", $data)) {
            return true;
        }
        return false;
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

    public function getCurrencyByID($id)
    {
        $q = $this->db->get_where('currencies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addCurrency($data)
    {
        if ($this->db->insert("currencies", $data)) {
            return true;
        }
        return false;
    }

    public function updateCurrency($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update("currencies", $data)) {
            return true;
        }
        return false;
    }

    public function deleteCurrency($id)
    {
        if ($this->db->delete("currencies", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getAllCategories()
    {
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllSubCategories()
    {
        $q = $this->db->get("subcategories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSubcategoryDetails($id)
    {
        $this->db->select("subcategories.code as code, subcategories.name as name, categories.name as parent")
            ->join('categories', 'categories.id = subcategories.category_id', 'left')
            ->group_by('subcategories.id');
        $q = $this->db->get_where("subcategories", array('subcategories.id' => $id));
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
	
	public function getSubcategoryByCode($code)
    {

        $q = $this->db->get_where('subcategories', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getCategoryByID($id)
    {
        $q = $this->db->get_where("categories", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSubCategoryByID($id)
    {
        $q = $this->db->get_where("subcategories", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function addCategories($data)
    {
        if ($this->db->insert_batch('categories', $data)) {
            return true;
        }
        return false;
    }
	
	public function addSubCategories($data)
    {
        if ($this->db->insert_batch('subcategories', $data)) {
            return true;
        }
        return false;
    }

    public function addCategory($name, $code, $brand_id, $photo)
    {
        if ($this->db->insert("categories", array('code' => $code, 'name' => $name, 'image' => $photo, 'brand_id' => $brand_id))) {
            return true;
        }
        return false;
    }

    public function addSubCategory($category, $name, $code, $brand, $photo)
    {
        if ($this->db->insert("subcategories", array('category_id' => $category, 'code' => $code, 'name' => $name, 'image' => $photo, 'brand_id'=>$brand))) {
            return true;
        }
        return false;
    }

    public function updateCategory($id, $data = array(), $photo)
    {
        $categoryData = array('code' => $data['code'], 'name' => $data['name'], 'brand_id' => $data['brand_id']);
        if ($photo) {
            $categoryData['image'] = $photo;
        }
        $this->db->where('id', $id);
        if ($this->db->update("categories", $categoryData)) {
            return true;
        }
        return false;
    }

    public function updateSubCategory($id, $data = array(), $photo)
    {
        $categoryData = array(
            'category_id' => $data['category'],
            'code' => $data['code'],
            'name' => $data['name'],
			'brand_id' => $data['brand_id']
        );
        if ($photo) {
            $categoryData['image'] = $photo;
        }
        $this->db->where('id', $id);
        if ($this->db->update("subcategories", $categoryData)) {
            return true;
        }
        return false;
    }

    public function deleteCategory($id)
    {
        if ($this->db->delete("categories", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteSubCategory($id)
    {
        if ($this->db->delete("subcategories", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getPaypalSettings()
    {
        $q = $this->db->get('paypal');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updatePaypal($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('paypal', $data)) {
            return true;
        }
        return FALSE;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get('skrill');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateSkrill($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('skrill', $data)) {
            return true;
        }
        return FALSE;
    }

    public function checkGroupUsers($id)
    {
        $q = $this->db->get_where("users", array('group_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteGroup($id)
    {
        if ($this->db->delete('groups', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function addVariant($data)
    {
        if ($this->db->insert('variants', $data)) {
            return true;
        }
        return false;
    }

    public function updateVariant($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('variants', $data)) {
            return true;
        }
        return false;
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

    public function getVariantByID($id)
    {
        $q = $this->db->get_where('variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteVariant($id)
    {
        if ($this->db->delete('variants', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function getProductNames($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ' . $this->db->dbprefix('product_variants') . '.name as vname')
            ->where("type != 'combo' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->join('product_variants', 'product_variants.product_id=products.id', 'left')
            ->where('' . $this->db->dbprefix('product_variants') . '.name', NULL)
            ->group_by('products.id')->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function insertBom($data)
    {
        if ($this->db->insert('bom', $data)) {
            $convert_id = $this->db->insert_id();
            return $convert_id;
        }
    }

	public function getConvertItemsById($bom_id){
		$this->db->select('bom_items.product_id, bom_items.bom_id, bom_items.quantity AS c_quantity , (erp_products.cost * erp_bom_items.quantity) AS tcost, bom_items.status, products.cost AS p_cost');
		$this->db->join('products', 'products.id = bom_items.product_id', 'INNER');
		$this->db->where('bom_items.bom_id', $bom_id);
		$query = $this->db->get('bom_items');
		
		if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
	}
	
	public function getConvertItemsDeduct($bom_id){
		$this->db->select('SUM(erp_products.cost * erp_bom_items.quantity) AS tcost, bom_items.status, SUM(erp_bom_items.quantity) as tqty');
		$this->db->join('products', 'products.id = bom_items.product_id', 'INNER');
		$this->db->where('bom_items.bom_id', $bom_id);
		$this->db->where('bom_items.status', 'deduct');
		$query = $this->db->get('bom_items');
		
		if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
	}
	
	public function getConvertItemsAdd($bom_id){
		$this->db->select('bom_items.product_id, bom_items.bom_id, bom_items.quantity AS c_quantity ,(erp_products.cost * erp_bom_items.quantity) AS tcost, bom_items.status');
		$this->db->join('products', 'products.id = bom_items.product_id', 'INNER');
		$this->db->where('bom_items.bom_id', $bom_id);
		$this->db->where('bom_items.status', 'add');
		$query = $this->db->get('bom_items');
		
		if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
	}
	
	public function getBOmByID($id)
    {
        $this->db->select('date, name, sum(erp_bom_items.quantity) as qty, cost, noted, created_by');
		$this->db->from('bom');
		$this->db->join('bom_items', 'bom_items.bom_id = bom.id');
		$this->db->where(array('bom.id'=> $id, 'bom_items.status'=>'add'));
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
             return $query->row();
        }
        return false;
    }
	
	public function getBOmByIDs($id)
    {
        $this->db->select('date, name, quantity, cost, noted, created_by, status, product_name');
		$this->db->from('bom');
		$this->db->join('bom_items', 'bom_items.bom_id = bom.id');
		$this->db->where('bom.id',$id);
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
             return $query->result_array();
        }
        return false;
    }
	
	public function deleteBom($id)
    {
        if ($this->db->delete('bom', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function deleteBom_items($id)
    {
        if ($this->db->delete('bom_items', array('bom_id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function updateBom($id, $data)
    {
        $this->db->where('id', $id);
        if ($this->db->update('bom', $data)) {
            return true;
        }
        return FALSE;
    }
	
	public function updateBom_items($id, $data)
    {
        $this->db->where('product_id', $id);
        if ($this->db->update('bom_items', $data)) {
            return true;
        }
        return FALSE;
    }
	
	public function getRoomByID($id){
		$this->db->select('id,floor,name,ppl_number,description,inactive');
		$this->db->from('suspended');
		$this->db->where('id' , $id);
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
	}
	//=============Insert Suppend===================
	public function addSuppend($data){
		//$this->erp->print_arrays($data);
		if ($this->db->insert('suspended', $data)) {
            return true;
        }
        return false;
	}
	//=============delete Suppend===================
	public function deleteSuppend($id){
		$q = $this->db->delete('suspended', array('id' => $id));
		if($q){
			return true;
		}else{
			return false;
		}
	}
	
	public function updateRooms($id,$data){
		//$this->erp->print_arrays($data);
		$this->db->where('id', $id);
		$q=$this->db->update('suspended', $data);
        if ($q) {
            return true;
        }
        return false;
	}
	
	
	/* New Function */
	public function getExpenseCategoryByID($id)
    {
        $q = $this->db->get_where("expense_categories", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getExpenseCategoryByCode($code)
    {
        $q = $this->db->get_where("expense_categories", array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addExpenseCategory($data)
    {
        if ($this->db->insert("expense_categories", $data)) {
            return true;
        }
        return false;
    }

    public function addExpenseCategories($data)
    {
        if ($this->db->insert_batch("expense_categories", $data)) {
            return true;
        }
        return false;
    }

    public function updateExpenseCategory($id, $data = array())
    {
        if ($this->db->update("expense_categories", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function hasExpenseCategoryRecord($id)
    {
        $this->db->where('category_id', $id);
        return $this->db->count_all_results('expenses');
    }

    public function deleteExpenseCategory($id)
    {
        if ($this->db->delete("expense_categories", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function addUnit($data)
    {
        if ($this->db->insert("units", $data)) {
            return true;
        }
        return false;
    }

    public function updateUnit($id, $data = array())
    {
        if ($this->db->update("units", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteUnit($id)
    {
        if ($this->db->delete("units", array('id' => $id))) {
            $this->db->delete("units", array('base_unit' => $id));
            return true;
        }
        return FALSE;
    }

    public function addPriceGroup($data)
    {
        if ($this->db->insert('price_groups', $data)) {
            return true;
        }
        return false;
    }

    public function updatePriceGroup($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('price_groups', $data)) {
            return true;
        }
        return false;
    }

    public function getAllPriceGroups()
    {
        $q = $this->db->get('price_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPriceGroupByID($id)
    {
        $q = $this->db->get_where('price_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deletePriceGroup($id)
    {
        if ($this->db->delete('price_groups', array('id' => $id)) && $this->db->delete('product_prices', array('price_group_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function setProductPriceForPriceGroup($product_id, $group_id, $price)
    {
        if ($this->getGroupPrice($group_id, $product_id)) {
            if ($this->db->update('product_prices', array('price' => $price), array('price_group_id' => $group_id, 'product_id' => $product_id))) {
                return true;
            }
        } else {
            if ($this->db->insert('product_prices', array('price' => $price, 'price_group_id' => $group_id, 'product_id' => $product_id))) {
                return true;
            }
        }
        return FALSE;
    }

    public function getGroupPrice($group_id, $product_id)
    {
        $q = $this->db->get_where('product_prices', array('price_group_id' => $group_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductGroupPriceByPID($product_id, $group_id)
    {
        $pg = "(SELECT {$this->db->dbprefix('product_prices')}.price as price, {$this->db->dbprefix('product_prices')}.product_id as product_id FROM {$this->db->dbprefix('product_prices')} WHERE {$this->db->dbprefix('product_prices')}.product_id = {$product_id} AND {$this->db->dbprefix('product_prices')}.price_group_id = {$group_id}) GP";

        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, GP.price", FALSE)
        // ->join('products', 'products.id=product_prices.product_id', 'left')
        ->join($pg, 'GP.product_id=products.id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateGroupPrices($data = array())
    {
        foreach ($data as $row) {
            if ($this->getGroupPrice($row['price_group_id'], $row['product_id'])) {
                $this->db->update('product_prices', array('price' => $row['price']), array('product_id' => $row['product_id'], 'price_group_id' => $row['price_group_id']));
            } else {
                $this->db->insert('product_prices', $row);
            }
        }
        return true;
    }

    public function deleteProductGroupPrice($product_id, $group_id)
    {
        if ($this->db->delete('product_prices', array('price_group_id' => $group_id, 'product_id' => $product_id))) {
            return TRUE;
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

    public function addBrand($data)
    {
        if ($this->db->insert("brands", $data)) {
            return true;
        }
        return false;
    }

    public function addBrands($data)
    {
        if ($this->db->insert_batch('brands', $data)) {
            return true;
        }
        return false;
    }

    public function updateBrand($id, $data = array())
    {
        if ($this->db->update("brands", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteBrand($id)
    {
        if ($this->db->delete("brands", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function addCase($data)
    {
        if ($this->db->insert("case", $data)) {
            return true;
        }
        return false;
    }
	
	public function updateCase($id, $data = array())
    {
        if ($this->db->update("case", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
	
	public function deleteCase($id)
    {
        if ($this->db->delete("case", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function addDiameter($data)
    {
        if ($this->db->insert("diameter", $data)) {
            return true;
        }
        return false;
    }
	
	public function updateDiameter($id, $data = array())
    {
        if ($this->db->update("diameter", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
	
	public function deleteDiameter($id)
    {
        if ($this->db->delete("diameter", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	// winding
	
	public function delete_winding($id)
	{
        if ($this->db->delete("winding", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	public function add_winding($data)
    {
        if ($this->db->insert('winding', $data)) {
            return true;
        }
        return false;
    }
	public function update_winding($id,$data){

		$this->db->where('id', $id);
        if ($this->db->update('winding', $data)) {
            return true;
        }
        return false;
	}
	public function addDials($data)
    {
        if ($this->db->insert("dials", $data)) {
            return true;
        }
        return false;
    }
	
	public function updateDials($id, $data = array())
    {
        if ($this->db->update("dials", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
	
	public function deleteDials($id)
    {
        if ($this->db->delete("dials", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function addStraps($data)
    {
        if ($this->db->insert("strap", $data)) {
            return true;
        }
        return false;
    }
	
	public function updateStraps($id, $data = array())
    {
        if ($this->db->update("strap", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
	
	public function deleteStraps($id)
    {
        if ($this->db->delete("strap", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function addWaterResistance($data)
    {
        if ($this->db->insert("water_resistance", $data)) {
            return true;
        }
        return false;
    }
	
	public function updateWaterResistance($id, $data = array())
    {
        if ($this->db->update("water_resistance", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
	
	public function deleteWaterResistance($id)
    {
        if ($this->db->delete("water_resistance", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

	
	public function add_power_reserve($data)
    {
        if ($this->db->insert("power_reserve", $data)) {
            return true;
        }
        return false;
    }
	
	public function update_power_reserve($id, $data = array())
    {
        if ($this->db->update("power_reserve", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
	
	public function delete_power_reserve($id)
    {
        if ($this->db->delete("power_reserve", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	// buckle
	public function delete_buckle($id)
	{
        if ($this->db->delete("erp_buckle", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	public function add_buckle($data)
    {
        if ($this->db->insert('buckle', $data)) {
            return true;
        }
        return false;
    }
	public function getOne_buckle($id){
		$q = $this->db->get_where('buckle', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function update_buckle($id,$data){

		$this->db->where('id', $id);
        if ($this->db->update('buckle', $data)) {
            return true;
        }
        return false;
	}
	public function getBuckle_byID($id)
    {
        $q = $this->db->get_where('buckle', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	// complication
	
public function delete_complication($id)
	{
        if ($this->db->delete("erp_complication", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	public function add_complication($data)
    {
        if ($this->db->insert('complication', $data)) {
            return true;
        }
        return false;
    }
	public function getOne_complication($id){
		$q = $this->db->get_where('complication', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function update_complication($id,$data){

		$this->db->where('id', $id);
        if ($this->db->update('complication', $data)) {
            return true;
        }
        return false;
	}
	public function getComplication_byID($id)
    {
        $q = $this->db->get_where('complication', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

}
