<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Purchases_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->default_biller_id = $this->site->default_biller_id();
    }
	
	public function getProductOptions1($product_id, $warehouse_id)
    {
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->where('warehouses_products_variants.warehouse_id', $warehouse_id)
            ->where('warehouses_products_variants.quantity >', 0)
            ->group_by('product_variants.id');
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getPurchaseOrderByID($id)
    {
        $q = $this->db->get_where('purchases_order', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getAllPurchaseOrderItems_order($purchase_id)
    {
        $this->db->select('	purchase_order_items.id,
							purchase_order_items.purchase_order_id,
							purchase_order_items.transfer_id,
							purchase_order_items.product_id,
							purchase_order_items.product_code,
							purchase_order_items.product_name,
							purchase_order_items.option_id,
							purchase_order_items.net_unit_cost,
							purchase_order_items.warehouse_id,
							purchase_order_items.item_tax,
							purchase_order_items.tax_rate_id,
							purchase_order_items.tax,
							purchase_order_items.discount,
							purchase_order_items.item_discount,
							purchase_order_items.expiry,
							purchase_order_items.subtotal,
							purchase_order_items.quantity,
							purchase_order_items.quantity_balance,
							purchase_order_items.date,
							purchase_order_items.`status`,
							purchase_order_items.unit_cost,
							purchase_order_items.real_unit_cost,
							purchase_order_items.quantity_received,
							purchase_order_items.supplier_part_no,
							purchase_order_items.supplier_id,
							tax_rates.code as tax_code, 
							tax_rates.name as tax_name, 
							tax_rates.rate as tax_rate, 
							products.unit, 
							products.price, 
							products.details as details,
							products.image,
							products.name as pname, 
							product_variants.name as variant,companies.name')
            ->join('products', 'products.id=purchase_order_items.product_id', 'left')
			->join('companies', 'companies.id=purchase_order_items.supplier_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_order_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_order_items.tax_rate_id', 'left')
			->where('purchase_order_items.quantity != purchase_order_items.quantity_received')
            ->group_by('purchase_order_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_order_items', array('purchase_order_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getSuppliersByID($id){
		$this->db->select('erp_purchases.supplier,erp_purchases.supplier_id'); 
		$q=$this->db->get_where('erp_purchases',array('erp_purchases.id'=>$id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	} 
	public function getProductNames($term, $standard, $combo, $digital, $service, $category, $limit = 15)
    {
        $this->db->select('products.*');
		$this->db->where("(type = 'standard' OR type = 'service') AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%') AND inactived <> 1");
		if($this->Owner || $this->admin){
                
        }else{
            if($standard != ""){
                $this->db->where("products.type <> 'standard' ");
            }
            if($combo != ""){
                $this->db->where("products.type <> 'combo' ");
            }
            if($digital != ""){
                $this->db->where("products.type <> 'digital' ");
            }
            if($service != ""){
                $this->db->where("products.type <> 'service' ");
            }
            if($category != ""){
                $this->db->where("products.category_id NOT IN (".$category.") ");
            }
		}
		
		$this->db->order_by('code', 'DESC');
        $this->db->where("products.type <> 'combo' ");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getReferenceno($id){
		 $this->db->select('reference_no');
		 $q = $this->db->get_where('erp_purchases',array('id'=>$id),1);
		  if ($q->num_rows() > 0) {
            return $q->row();
        }
		return FALSE;
	}
	public function getProductNumber($term, $standard, $combo, $digital, $service, $category, $limit = 15)
    {
		if(preg_match('/\s/', $term))
		{
			$name = explode(" ", $term);
			$first = $name[0];
			//$this->db->select('*')
            //->group_by('products.id');
			$this->db->select('products.*, COALESCE(erp_serial.serial_number, "") as sep');
			$this->db->join('serial', 'serial.product_id = products.id', 'left');
			$this->db->where('code', $first);
			$this->db->limit($limit);
			if($this->Owner || $this->admin){
                
            }else{
                if($standard != ""){
                    $this->db->where("products.type <> 'standard' ");
                }
                if($combo != ""){
                    $this->db->where("products.type <> 'combo' ");
                }
                if($digital != ""){
                    $this->db->where("products.type <> 'digital' ");
                }
                if($service != ""){
                    $this->db->where("products.type <> 'service' ");
                }
                if($category != ""){
                    $this->db->where("products.category_id NOT IN (".$category.") ");
                }
            }
            $this->db->where("products.type <> 'combo' ");
			$q = $this->db->get('products');
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
				return $data;
			}
		}else
		{
			$this->db->select('products.*, COALESCE(erp_serial.serial_number, "") as sep, warehouses_products.quantity')
					 ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
					 ->join('serial', 'serial.product_id = products.id', 'left')
					 ->group_by('products.id');
			$this->db->where("(code LIKE '%" . $term . "%')");
			if($this->Owner || $this->admin){
                
            }else{
                if($standard != ""){
                    $this->db->where("products.type <> 'standard' ");
                }
                if($combo != ""){
                    $this->db->where("products.type <> 'combo' ");
                }
                if($digital != ""){
                    $this->db->where("products.type <> 'digital' ");
                }
                if($service != ""){
                    $this->db->where("products.type <> 'service' ");
                }
                if($category != ""){
                    $this->db->where("products.category_id NOT IN (".$category.") ");
                }
            }
            $this->db->where("products.type <> 'combo' ");
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
	
	public function getUnits(){
		$this->db->select()
				 ->from('units');
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
	
	public function getExpenseByCode($code)
    {
        $q = $this->db->get_where('expenses', array('account_code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
	function getExpenseByReference($ref){
		$q = $this->db->get_where('expenses', array('reference' => $ref), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
	}
	
	public function addExpenses($data = array())
    {
		if ($this->db->insert_batch('expenses', $data)) {
            foreach($data as $item){
				if ($this->site->getReference('ex') == $item['reference']) {
					$this->site->updateReference('ex');
				}
			}
        }
        return false;
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
        return FALSE;
    }

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductsByCode($code)
    {
        $this->db->select('*')->from('products')->like('code', $code, 'both');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductByName($name)
    {
        $q = $this->db->get_where('products', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateProductQuantity($product_id, $quantity, $warehouse_id, $product_cost)
    {
        if ($this->addQuantity($product_id, $warehouse_id, $quantity)) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function calculateAndUpdateQuantity($item_id, $product_id, $quantity, $warehouse_id, $product_cost)
    {
        if ($this->updatePrice($product_id, $product_cost) && $this->calculateAndAddQuantity($item_id, $product_id, $warehouse_id, $quantity)) {
            return true;
        }
        return false;
    }

    public function calculateAndAddQuantity($item_id, $product_id, $warehouse_id, $quantity)
    {

        if ($this->getProductQuantity($product_id, $warehouse_id)) {
            $quantity_details = $this->getProductQuantity($product_id, $warehouse_id);
            $product_quantity = $quantity_details['quantity'];
            $item_details = $this->getItemByID($item_id);
            $item_quantity = $item_details->quantity;
            $after_quantity = $product_quantity - $item_quantity;
            $new_quantity = $after_quantity + $quantity;
            if ($this->updateQuantity($product_id, $warehouse_id, $new_quantity)) {
                return TRUE;
            }
        } else {

            if ($this->insertQuantity($product_id, $warehouse_id, $quantity)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function addQuantity($product_id, $warehouse_id, $quantity)
    {

        if ($this->getProductQuantity($product_id, $warehouse_id)) {
            $warehouse_quantity = $this->getProductQuantity($product_id, $warehouse_id);
            $old_quantity = $warehouse_quantity['quantity'];
            $new_quantity = $old_quantity + $quantity;

            if ($this->updateQuantity($product_id, $warehouse_id, $new_quantity)) {
                return TRUE;
            }
        } else {

            if ($this->insertQuantity($product_id, $warehouse_id, $quantity)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function insertQuantity($product_id, $warehouse_id, $quantity)
    {
        $productData = array(
            'product_id' => $product_id,
            'warehouse_id' => $warehouse_id,
            'quantity' => $quantity
        );
        if ($this->db->insert('warehouses_products', $productData)) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->update('warehouses_products', array('quantity' => $quantity), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
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

    public function updatePrice($id, $unit_cost)
    {
        if ($this->db->update('products', array('cost' => $unit_cost), array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getAllPurchases()
    {
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getStatus($id){
		$this->db->select("erp_purchases.status,erp_purchases.id");
		$q = $this->db->get_where('erp_purchases', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
    public function getAllPurchaseItems($purchase_id)
    {
        $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant,companies.name')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
			->join('companies', 'companies.id=purchase_items.supplier_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getPurchasedItems($product_id, $warehouse_id, $option_id = NULL)
    {
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->select('id, quantity, quantity_balance, net_unit_cost, item_tax');
        $this->db->where('product_id', $product_id)->where('warehouse_id', $warehouse_id)->where('quantity_balance !=', 0);
        if ($option_id) {
            $this->db->where('option_id', $option_id);
        }
        $this->db->group_by('id');
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getVariantQtyById($id) {
		$q = $this->db->get_where('product_variants', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getVariantQtyByProductId($product_id) {
		$q = $this->db->get_where('product_variants', array('product_id' => $product_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

    public function getItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
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
    
    public function paymentByPurchaseID($purchase_id){
        $this->db->select('purchase_id');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id), 1);
        if($q->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
	public function deleteSerialBySerial($arr)
    {
		foreach ($arr as $arr_serial) {
            $this->db->where('serial_number', $arr_serial);
            $this->db->delete('serial');
        }
        return TRUE;
    }
	
	public function returnPurchase($data = array(), $items = array())
    {       
		
        $purchase_items = $this->site->getAllPurchaseItems($data['purchase_id']);
        if ($this->db->insert('return_purchases', $data)) {
            $return_id = $this->db->insert_id();
            if ($this->site->getReference('rep') == $data['reference_no']) {
                $this->site->updateReference('rep');
            }
            foreach ($items as $item) {
				
				$purchase_item_id = $item['purchase_item_id'];
				$purchase_id = $item['purchase_id'];
				unset($item['purchase_item_id']);
				unset($item['purchase_id']);
				$this->db->insert('purchase_items', $item);
				
				$item['purchase_item_id'] = $purchase_item_id;
				$item['purchase_id'] = $purchase_id;
				unset($item['quantity_balance']);
				unset($item['transaction_type']);
				
                $item['return_id'] = $return_id;
                $this->db->insert('return_purchase_items', $item);				
				$serial_number = $item['serial_no'];
				$get_serial = explode(',', $serial_number);
				$this->deleteSerialBySerial($get_serial);
            /*    if ($purchase_item = $this->getPurcahseItemByID($item['purchase_item_id'])) {
                    $nqty = $purchase_item->quantity - $item['quantity'];
                    $bqty = $purchase_item->quantity_balance - $item['quantity'];
                    $rqty = $purchase_item->quantity_received - $item['quantity'];
                    $tax = $purchase_item->unit_cost - $purchase_item->net_unit_cost;
                    $discount = $purchase_item->item_discount / $purchase_item->quantity;
                    $item_tax = $tax * $nqty;
                    $item_discount = $discount * $nqty;
                    $subtotal = $purchase_item->unit_cost * $nqty;

                   // $this->db->update('purchase_items', array('quantity_balance' => $bqty, 'quantity_received' => $rqty, 'item_tax' => $item_tax, 'item_discount' => $item_discount, 'subtotal' => $subtotal), array('id' => $item['purchase_item_id']));
                    $this->db->update('purchase_items', array('quantity_balance' => $bqty, 'quantity_received' => $rqty), array('id' => $item['purchase_item_id']));
                }*/
            }
			
            $is_payment = $this->paymentByPurchaseID($data['purchase_id']);
            if($is_payment){
                $payment = array(
                    'date' => $data['date'],
                    'purchase_id' => $data['purchase_id'],
                    'reference_no' => $this->site->getReference('pp'),
                    'amount' => $data['grand_total'],
                    'paid_by' => 'cash',
                    'created_by' => $this->session->userdata('user_id'),
                    'type' => 'received',
                    'note' => $data['note'] ? 'Returned: '. $data['note'] : 'Returned',
                    'purchase_return_id' => $return_id,
                    'biller_id' => $this->default_biller_id
                );
                $this->db->insert('payments', $payment);
                $this->site->updateReference('pp');
            }

            $this->calculatePurchaseTotalsReturn($data['purchase_id'], $return_id, $data['surcharge']);
            $this->site->syncQuantity(NULL, NULL, $purchase_items);
            $this->site->syncQuantity(NULL, $data['purchase_id']);
            return true;
        }
        return false;
    }
	
	/* Purchases Return */
	public function returnPurchases($data = array(), $items = array())
    {
        if ($this->db->insert('return_purchases', $data)) {
            $return_id = $this->db->insert_id();
            if ($this->site->getReference('rep') == $data['reference_no']) {
                $this->site->updateReference('rep');
            }
            foreach ($items as $item) {
                $item['return_id'] = $return_id;
                $purchase_id = $item['purchase_item_id'];
                $item['purchase_id'] = $purchase_id;
                $purchase_item = $this->getPurcahseItemByPurchaseIDProductID($purchase_id, $item['product_id']);       
                $item['purchase_item_id'] = $purchase_item->id;       
                $this->db->insert('return_purchase_items', $item);
				$warehouse_id = $item['warehouse_id'];
                
                $purchase_items = $this->site->getAllPurchaseItems($purchase_id);

                if ($purchase_item) {
                        $nqty = $purchase_item->quantity - $item['quantity'];
                        $bqty = $purchase_item->quantity_balance - $item['quantity'];
                        $rqty = $purchase_item->quantity_received - $item['quantity'];
                        $tax = $purchase_item->unit_cost - $purchase_item->net_unit_cost;
                        $discount = $purchase_item->item_discount / $purchase_item->quantity;
                        $item_tax = $tax * $nqty;
                        $item_discount = $discount * $nqty;
                        $subtotal = $purchase_item->unit_cost * $nqty;
                        //$this->db->update('purchase_items', array('quantity_balance' => $bqty, 'quantity_received' => $rqty, 'item_tax' => $item_tax, 'item_discount' => $item_discount, 'subtotal' => $subtotal), array('id' => $item['purchase_item_id']));
                        $this->db->update('purchase_items', array('quantity_balance' => $bqty, 'quantity_received' => $rqty), array('id' => $item['purchase_item_id']));
                }
                $is_payment = $this->paymentByPurchaseID($purchase_id);
                if($is_payment){
                    $payment = array(
                        'date' => $data['date'],
                        'purchase_id' => $purchase_id,
                        'reference_no' => $this->site->getReference('pp'),
                        'amount' => $data['grand_total'],
                        'paid_by' => 'cash',
                        'created_by' => $this->session->userdata('user_id'),
                        'type' => 'received',
                        'note' => $data['note'] ? 'Returned: '. $data['note'] : 'Returned',
                        'purchase_return_id' => $return_id,
                        'biller_id' => $this->default_biller_id
                    );
                    $this->db->insert('payments', $payment);
                    $this->site->updateReference('pp');
                }
                $this->calculatePurchaseTotalsReturn($purchase_id, $return_id, $data['surcharge']);
                //$this->site->syncQuantity(NULL, NULL, $purchase_items);
				if($purchase_id > 0){
					$this->site->syncQuantity(NULL, $purchase_id);
				}else{
					$pr = $this->site->getProductByID($item['product_id']);
					$pr_quantity = $pr->quantity - $item['quantity'];
					if ($this->db->update('products', array('quantity' => $pr_quantity), array('id' => $item['product_id']))) {
						if ($this->site->getWarehouseProducts($item['product_id'], $warehouse_id)) {
							$this->db->update('warehouses_products', array('quantity' => $pr_quantity), array('product_id' => $item['product_id'], 'warehouse_id' => $warehouse_id));
						} else {
							if( ! $pr_quantity) { $pr_quantity = 0; }
							$this->db->insert('warehouses_products', array('quantity' => $pr_quantity, 'product_id' => $item['product_id'], 'warehouse_id' => $warehouse_id));
						}
					}
				}
            }
            return true;
        }
        return false;
    }

    public function calculatePurchaseTotals($id, $return_id, $surcharge)
    {
        $purchase = $this->getPurchaseByID($id);
        $items = $this->getAllPurchaseItems($id);
        if (!empty($items)) {
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            foreach ($items as $item) {
                $product_tax += $item->item_tax;
                $product_discount += $item->item_discount;
                $total += $item->net_unit_cost * $item->quantity;
            }
            if ($purchase->order_discount_id) {
                $percentage = '%';
                $order_discount_id = $purchase->order_discount_id;
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = (($total + $product_tax) * (Float)($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            }
            if ($purchase->order_tax_id) {
                $order_tax_id = $purchase->order_tax_id;
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            }
            $total_discount = $order_discount + $product_discount;
            $total_tax = $product_tax + $order_tax;
            $grand_total = $total + $total_tax + $purchase->shipping - $order_discount + $surcharge;
            $data = array(
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'return_id' => $return_id,
                'surcharge' => $surcharge
            );

            if ($this->db->update('purchases', $data, array('id' => $id))) {
                return true;
            }
        } else {
            //$this->db->delete('purchases', array('id' => $id));
        }
        return FALSE;
    }
	
	public function calculatePurchaseTotalsReturn($id, $return_id, $surcharge)
    {
        $purchase = $this->getPurchaseByID($id);
        $items = $this->getAllPurchaseItems($id);
        if (!empty($items)) {
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            foreach ($items as $item) {
                $product_tax += $item->item_tax;
                $product_discount += $item->item_discount;
                $total += $item->net_unit_cost * $item->quantity;
            }
            if ($purchase->order_discount_id) {
                $percentage = '%';
                $order_discount_id = $purchase->order_discount_id;
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = (($total + $product_tax) * (Float)($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            }
            if ($purchase->order_tax_id) {
                $order_tax_id = $purchase->order_tax_id;
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            }
            $total_discount = $order_discount + $product_discount;
            $total_tax = $product_tax + $order_tax;
            $grand_total = $total + $total_tax + $purchase->shipping - $order_discount + $surcharge;
            $data = array(
                //'total' => $total,
                //'product_discount' => $product_discount,
                //'order_discount' => $order_discount,
                //'total_discount' => $total_discount,
                //'product_tax' => $product_tax,
                //'order_tax' => $order_tax,
                //'total_tax' => $total_tax,
                //'grand_total' => $grand_total,
                'return_id' => $return_id,
                'surcharge' => $surcharge,
				'status' => 'returned'
            );
            /*
            $data = array(
                'return_id' => $return_id,
                'surcharge' => $surcharge
            );
            */

            if ($this->db->update('purchases', $data, array('id' => $id))) {
                return true;
            }
        } else {
            //$this->db->delete('purchases', array('id' => $id));
        }
        return FALSE;
    }
	
	public function getPurcahseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getPurcahseItemByPurchaseID($id)
    {
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getPurcahseItemByPurchaseIDProductID($id, $product_id)
    {
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function updatePurchaseItem($id, $qty, $purchase_item_id, $product_id = NULL, $warehouse_id = NULL, $option_id = NULL)
    {
        if ($id) {
            if($pi = $this->getPurchaseItemByID($id)) {
                $pr = $this->site->getProductByID($pi->product_id);
                if ($pr->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($pr->id, $pi->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        if($combo_item->type == 'standard') {
                            $cpi = $this->site->getPurchasedItem(array('product_id' => $combo_item->id, 'warehouse_id' => $pi->warehouse_id, 'option_id' => NULL));
                            $bln = $pi->quantity_balance + ($qty*$combo_item->qty);
                            $this->db->update('purchase_items', array('quantity_balance' => $bln), array('id' => $combo_item->id));
                        }
                    }
                } else {
                    $bln = $pi->quantity_balance + $qty;
                    $this->db->update('purchase_items', array('quantity_balance' => $bln), array('id' => $id));
                }
            }
        } else {
            if ($purchase_item = $this->getPurchaseItemByID($purchase_item_id)) {
                $option_id = isset($purchase_item->option_id) && !empty($purchase_item->option_id) ? $purchase_item->option_id : NULL;
                $clause = array('product_id' => $purchase_item->product_id, 'warehouse_id' => $purchase_item->warehouse_id, 'option_id' => $option_id);
                if ($pi = $this->site->getPurchasedItem($clause)) {
                    $quantity_balance = $pi->quantity_balance+$qty;
                    $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                } else {
                    $clause['purchase_id'] = NULL;
                    $clause['transfer_id'] = NULL;
                    $clause['quantity'] = 0;
                    $clause['quantity_balance'] = $qty;
                    $this->db->insert('purchase_items', $clause);
                }
            }
            if (! $sale_item && $product_id) {
                $pr = $this->site->getProductByID($product_id);
                $clause = array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
                if ($pr->type == 'standard') {
                    if ($pi = $this->site->getPurchasedItem($clause)) {
                        $quantity_balance = $pi->quantity_balance+$qty;
                        $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                    } else {
                        $clause['purchase_id'] = NULL;
                        $clause['transfer_id'] = NULL;
                        $clause['quantity'] = 0;
                        $clause['quantity_balance'] = $qty;
                        $this->db->insert('purchase_items', $clause);
                    }
                } elseif ($pr->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($pr->id, $warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        $clause = array('product_id' => $combo_item->id, 'warehouse_id' => $warehouse_id, 'option_id' => NULL);
                        if($combo_item->type == 'standard') {
                            if ($pi = $this->site->getPurchasedItem($clause)) {
                                $quantity_balance = $pi->quantity_balance+($qty*$combo_item->qty);
                                $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), $clause);
                            } else {
                                $clause['transfer_id'] = NULL;
                                $clause['purchase_id'] = NULL;
                                $clause['quantity'] = 0;
                                $clause['quantity_balance'] = $qty;
                                $this->db->insert('purchase_items', $clause);
                            }
                        }
                    }
                }
            }
        }
    }
	public function getPurcahseItemByPO($id)
    {
        $this->db->select('SUM(erp_purchase_items.quantity_balance) as quantity_balance');
		$this->db->join('purchase_items','purchase_items.purchase_id = purchases.id');
		$q = $this->db->get_where('purchases', array('purchase_order_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getQuantityPurchaseOrderItem($id)
    {
        $this->db->select('SUM(erp_purchase_order_items.quantity) as quantity');
		$q = $this->db->get_where('erp_purchase_order_items', array('purchase_order_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
    public function getProductComboItems($pid, $warehouse_id)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
    }
    public function getPurchaseItemIdByPurchaseID($purchase_id, $product_id = null)
    {
        $this->db->select('id');
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            $q = $q->row();
            return $q->id;
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
	public function getSerial($id,$pid){
		  $this->db
				->select(serial_number)
				->from('erp_serial')
				->join('erp_products', 'erp_products.id=erp_serial.product_id', 'left')
				->where('erp_serial.product_id ', $id AND 'erp_serial.purchase_id',$pid);

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
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
    public function getPurchaseByID($id)
    {
        $q = $this->db->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getPurchaseByRef($ref)
    {
        $q = $this->db->get_where('purchases', array('reference_no' => $ref), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getPurchaseIDByRef($ref)
    {
        $this->db->select('id', False);
        $q = $this->db->get_where('purchases', array('reference_no' => $ref), 1);
        if ($q->num_rows() > 0) {
            $q = $q->row();
            return $q->id;
        }
        return FALSE;
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                return TRUE;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
                return TRUE;
            }
        }
        return FALSE;
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
	 public function addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $related_products=NULL)
    {
		//$this->erp->print_arrays($data, $items, $warehouse_qty, $product_attributes, $photos);
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
			
			if ($related_products) {
				foreach ($related_products as $related_product) {
                    $this->db->insert('related_products', $related_product);
                }
			}

            return true;
        }

        return false;

    }

    public function resetProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                return TRUE;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function getOverSoldCosting($product_id)
    {
        $q = $this->db->get_where('costing', array('overselling' => 1));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function addPurchase($data, $items, $purchase_order_id)
    {
		if ($this->db->insert('purchases', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('po') == $data['reference_no']) {
                $this->site->updateReference('po');
            }
			
            foreach ($items as $item) {
				
				$item['purchase_id'] = $purchase_id;
				if($item['option_id'] != 0) {
					$row = $this->getVariantQtyById($item['option_id']);
					$item['real_unit_cost'] = $item['real_unit_cost'] / $row->qty_unit;
				}
                
				if($item['type'] == 'service'){
                    unset($item['type']);
                    $item['quantity'] = 1;
                    $item['quantity_balance'] = 1;
                }
                
                $this->db->insert('purchase_items', $item);
				
				/* update to qty_received */
				
				if($purchase_order_id) {
					$this->db->set('quantity_received', $item['quantity_balance'].' + `quantity_received`',false);
					$this->db->where(array('purchase_order_id' => $purchase_order_id, 'product_id' => $item['product_id']));
					$this->db->update('purchase_order_items');
				}
				
				/* Prevent from ordered status */
				if($data['status'] == 'received'){
					$this->db->like('code', $item['product_code']);
					$this->db->update('products', array('cost' => $item['real_unit_cost']));
					$this->site->updateComboCost($item['product_code']);
				}
				
                if($item['option_id'] != 0) {
					$this->db->set('quantity', $item['quantity'].' * `qty_unit`');
					$this->db->set('cost', $item['real_unit_cost'].' / `qty_unit`');
					$this->db->where(array('id' => $item['option_id'], 'product_id' => $item['product_id']));
					$this->db->update('product_variants');
                }
            }
			
			if($purchase_order_id) {
				$purchase_item 			= $this->getPurcahseItemByPO($purchase_order_id);
				$purchase_order_item 	= $this->getQuantityPurchaseOrderItem($purchase_order_id);
				$total_qty_balance 		= $purchase_item->quantity_balance;
				$total_qty         		= $purchase_order_item->quantity;
				if($total_qty_balance >= $total_qty) {
					$status = array('status' => 'completed');
				}else if($total_qty_balance > 0 && $total_qty_balance < $total_qty) {
					$status = array('status' => 'partial');
				}else{
					$status = array('status' => 'ordered');
				}				
				$this->db->update('purchases_order', $status, array('id' => $purchase_order_id));
			}
			
            if ($data['status'] == 'received') {
                $this->site->syncQuantity(NULL, $purchase_id);
            }
			
            return $purchase_id;
        }
        return false;
    }
	
	public function addSerial($serial, $addSerial){
		foreach ($serial as $item) {
			$sp = explode(',', $item['serial_number']);
			foreach($sp as $ser){
				if($ser != "" or $ser != 'undefined'){
					$serials = array(
						'product_id'    => $item['product_id'],
						'serial_number' => $ser,
						'warehouse'     => $item['warehouse'],
						'biller_id'     => $item['biller_id'],
						'serial_status' => 1,
						'purchase_id' => $addSerial
					);
					$this->db->insert('serial', $serials);
				}
			}
		}
		return false;
	}
	
	public function UpdateSerial($serial, $purchase_id){
		foreach($serial as $item){
			$this->db->delete('serial', array('product_id' => $item['product_id'], 'purchase_id' => $purchase_id));
		}
		foreach ($serial as $item) {
			$sp = explode(',', $item['serial_number']);
			
			foreach($sp as $ser){
				if($ser != ""){
					$serials = array(
						'product_id'    => $item['product_id'],
						'serial_number' => $ser,
						'warehouse'     => $item['warehouse'],
						'biller_id'     => $item['biller_id'],
						'serial_status' => 1,
                        'purchase_id' => $purchase_id
					);
					
					$this->db->insert('serial', $serials);
				}
			}
			
			//$this->erp->print_arrays($serial);
		}
		return false;
	}
	
	public function addPurchaseImport($data, $items)
    {
		if ($this->db->insert('purchases', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('po') == $data['reference_no']) {
                $this->site->updateReference('po');
            }
			
            foreach ($items as $item) {
				$item['purchase_id'] = $purchase_id;

                $this->db->insert('purchase_items', $item);
				
				/* Prevent from ordered status */
				/*
				if($data['status'] == 'received' || $data['status'] == 'pending'){
					$this->db->update('products', array('cost' => $item['net_unit_cost'], 'quantity' => $item['quantity']), array('id' => $item['product_id']));
				}
				*/

				/*
                if($item['option_id'] != 0) {
					$this->db->set('quantity', $item['quantity'].' * `qty_unit`');
					$this->db->set('cost', $item['real_unit_cost'].' / `qty_unit`');
                    //$this->db->update('product_variants', array('cost' => $item['real_unit_cost']), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
					$this->db->where(array('id' => $item['option_id'], 'product_id' => $item['product_id']));
					$this->db->update('product_variants');
                }
				*/
            }

            if ($data['status'] == 'received') {
                $this->site->syncQuantity(NULL, $purchase_id);
            }
            return true;
        }
        return false;
    }
	public function addPurchaseItemImport($items, $old_reference)
    {
        $purchase = $this->getPurchaseItemByRef($old_reference);
		if($items){
            foreach ($items as $item) {
				$item['purchase_id'] = $purchase->purchase_id;
                $this->db->insert('purchase_items', $item);
				
				$pur_update = array(
					'total' => $item['subtotal'] + $purchase->total,
					'grand_total' => $item['subtotal'] + $purchase->grand_total,
					'updated_by' => $this->session->userdata('user_id')
				);
				$this->db->update('purchases', $pur_update, array('id' => $item['purchase_id']));
				
				/* Prevent from ordered status */
				$this->db->update('products', array('cost' => $item['net_unit_cost'], 'quantity' => $item['quantity']), array('id' => $item['product_id']));

            }
			if ($data['status'] == 'received') {
                $this->site->syncQuantity(NULL, $purchase->purchase_id);
            }
            return true;
        }
        return false;
    }

    public function updatePurchase($id, $data, $items = array())
    {       
		$opurchase = $this->getPurchaseByID($id);		
        $oitems = $this->getAllPurchaseItems($id);
		$purchase_order_id = $opurchase->purchase_order_id;
        if ($this->db->update('purchases', $data, array('id' => $id)) && $this->db->delete('purchase_items', array('purchase_id' => $id)) && $this->db->delete('serial', array('purchase_id' => $id))) {
            $purchase_id = $id;
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
				
				// Update price
				$price = $item['price'];
				unset($item['price']);
				
                $this->db->insert('purchase_items', $item);
				
				/* update quantity received in purchase order item */
				
				if($purchase_order_id) {
					$quantity_balance = $this->getSynBalanceQuantity($purchase_order_id, $item['product_id']);
					$this->db->set('quantity_received', $quantity_balance, false);
					$this->db->where(array('purchase_order_id' => $purchase_order_id, 'product_id' => $item['product_id']));
					$this->db->update('purchase_order_items');
				}

				if($data['status'] == 'received'){
					$this->db->update('products', array('cost' => $item['real_unit_cost'], 'price' => $price), array('id' => $item['product_id']));
					$this->site->updateComboCost($item['product_code']);
				}
				
            }
			
			if($purchase_order_id) {
				$purchase_item = $this->getPurcahseItemByPO($purchase_order_id);
				$purchase_order_item = $this->getQuantityPurchaseOrderItem($purchase_order_id);
				$total_qty_balance = $purchase_item->quantity_balance;
				$total_qty         = $purchase_order_item->quantity;
				if($total_qty_balance >= $total_qty) {
					$status = array('status' => 'completed');
				}else if($total_qty_balance > 0 && $total_qty_balance < $total_qty) {
					$status = array('status' => 'partial');
				}else{
					$status = array('status' => 'ordered');
				}				
				$this->db->update('purchases_order', $status, array('id' => $purchase_order_id));
			}
			
			if($opurchase->payment_status == 'paid'){
				//$this->db->update('payments', array('amount' => $data['grand_total']), array('purchase_id' => $id));
				$total_balance = $data['grand_total'] - $opurchase->grand_total;
				if($total_balance != 0){
					$this->db->update('purchases', array('payment_status' => 'pending'), array('id' => $id));
				}else{
					$this->db->update('purchases', array('payment_status' => 'paid'), array('id' => $id));
				}
			}

            if ($opurchase->status == 'received') {
                $this->site->syncQuantity(NULL, NULL, $oitems);
            }
            if ($data['status'] == 'received') {
                $this->site->syncQuantity(NULL, $id);
            }
            return true;
        }
        return false;
    }

    public function deletePurchase($id)
    {
        $purchase_items = $this->site->getAllPurchaseItems($id);

        if ($this->db->delete('purchase_items', array('purchase_id' => $id)) && $this->db->delete('purchases', array('id' => $id))) {
            $this->db->delete('payments', array('purchase_id' => $id));
            $this->site->syncQuantity(NULL, NULL, $purchase_items);
            return true;
        }
        return FALSE;
    }
	
	public function getPurchaseItemByRef($purchase_ref)
    {
        $this->db->select('purchase_items.id AS purchase_item_id, purchase_items.product_id ,purchases.id AS purchase_id, purchases.reference_no AS purchase_reference, purchases.total, purchases.grand_total');
        $this->db->join('purchase_items', 'purchase_items.purchase_id = purchases.id', 'inner');
        $q = $this->db->get_where('purchases', array('purchases.reference_no' => $purchase_ref));
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchasePayments($purchase_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getPaymentByPurchaseID($id)
    {
        $q = $this->db->get_where('payments', array('purchase_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
	public function getWarehouseIDByCode($code)
    {
		$this->db->select('id, code');
        $q = $this->db->get_where('warehouses', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            $q = $q->row();
			return $q->id;
        }

        return FALSE;
    }

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
	public function getCurrentBalance($id, $pur_id)
	{
		$this->db->select('id, amount')
				 ->order_by('id', 'asc');
		$q = $this->db->get_where('payments', array('purchase_id' => $pur_id));
		if($q->num_rows() > 0) {
			foreach (($q->result()) as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return FALSE;
	}
	
    public function getPaymentsForPurchase($purchase_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addPayment($data = array())
    {
		$purchase_id = $data['purchase_id'];
		
		$payment = $this->site->getPaymentByPurchaseID($purchase_id);
		

		if ($this->db->insert('payments', $data)) {
			if ($this->site->getReference('pp') == $data['reference_no']) {
				$this->site->updateReference('pp');
			}
			$this->site->syncPurchasePayments($data['purchase_id']);
			return true;
		}

        return false;
    }

    public function updatePayment($id, $data = array())
    {
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $this->site->syncPurchasePayments($data['purchase_id']);
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
            $this->site->syncPurchasePayments($opay->purchase_id);
            return true;
        }
        return FALSE;
    }

    public function getProductOptions($product_id)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', array('name' => $name, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getExpenseByID($id)
    {
        $q = $this->db->get_where('expenses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getExpenses($id)
    {
		$this->db
				->select($this->db->dbprefix('expenses') . ".id as id, date, reference, erp_expense_categories.name ,expenses.amount, note, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as user, attachment", false)
				->from('expenses')
				->join('users', 'users.id=expenses.created_by', 'left')
				->join('erp_expense_categories','erp_expense_categories.id=erp_expenses.account_code','LEFT')
				->join('gl_trans', 'gl_trans.account_code = expenses.account_code', 'left')
				->where('expenses.id', $id)
				->group_by('expenses.id');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addExpense($data = array(), $payment = array())
    {
        if ($this->db->insert('expenses', $data)) {
            $expense_id = $this->db->insert_id();
            if ($this->site->getReference('ex') == $data['reference']) {
                $this->site->updateReference('ex');
            }
            if($payment){
                $payment['expense_id'] = $expense_id;
                $payment['reference_no'] = $this->site->getReference('pay');
                $this->db->insert('payments', $payment);
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
            }
            return true;
        }
        return false;
    }

    public function updateExpense($id, $data = array(), $data_payment = array())
    {
        if ($this->db->update('expenses', $data, array('id' => $id))) {
            $this->db->update('payments', $data_payment, array('expense_id' => $id));
            return true;
        }
        return false;
    }

    public function deleteExpense($id)
    {
        if ($this->db->delete('expenses', array('id' => $id))) {
            $this->db->update('payments', array('amount' => 0), array('expense_id' => $id));
            return true;
        }
        return FALSE;
    }
	
	public function check_expense_reference($ref){
		$this->db->where('reference', $ref);
		$query = $this->db->get('expenses');
		if($query->num_rows() > 0){
			return true;
		}else{
			return false;
		}
	}

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllQuoteItems($quote_id)
    {
        $q = $this->db->get_where('quote_items', array('quote_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getStaff()
    {
        if ($this->Admin) {
            $this->db->where('group_id !=', 1);
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getPurchasesReferences($term, $limit = 10)
    {
        $this->db->select('reference_no');
        $this->db->where("(reference_no LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    
    public function getPurchaseItemByRefPID($purchase_ref, $product_id)
    {
        $this->db->select('purchase_items.quantity');
        $this->db->join('purchase_items', 'purchase_items.purchase_id = purchases.id', 'inner');
        $q = $this->db->get_where('purchases', array('purchases.reference_no' => $purchase_ref, 'purchase_items.product_id' => $product_id));
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getCombinePaymentById($id)
    {
		$this->db->select('id, date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status');
		$this->db->from('purchases');
		$this->db->where_in('id', $id);
        $q = $this->db->get();
         if ($q->num_rows() > 0) {
            return $q;
        }
		return FALSE;
    }

	public function getSupplierSuggestions($term, $limit = 10)
    {
        $this->db->select("id, CONCAT(company, ' (', name, ')') as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', array('group_name' => 'supplier'), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    
    public function getReturnPurchaseByPurchaseID($purchase_id)
    {
        $this->db->select('SUM(quantity_balance) AS quantity_balance')
            ->join('purchase_items', 'purchase_items.purchase_id = purchases.id', 'left');
        $q = $this->db->get_where('purchases', array('purchases.id' => $purchase_id), 1);
        if ($q->num_rows() > 0) {
            $q = $q->row();
            echo $q->quantity_balance;
            exit();
        }
    }
	
	public function getKHM(){
		$q = $this->db->get_where('currencies', array('code'=> 'KHM'), 1);
		if($q->num_rows() > 0){
			$q = $q->row();
            return $q->rate;
		}
	}
	
	//====================== Import Purchase ===================//
	
	public function import_csv($invoice, $products){
		
		foreach($invoice as $purchase){
			$this->db->insert('purchases', $purchase);
			$this->site->updateReference('po');
		}
		foreach($products as $pur_item){
			
			$pur = $this->getPurchaseByRef($pur_item['reference_no']);
			$pur_item['purchase_id'] = $pur->id;
			unset($pur_item['reference_no']);
			if($pur_item['serial_no']){
				$data = array(
					'serial_number' => $pur_item['serial_no'],
					'serial_status' => '1',
					'product_id' 	=> $pur_item['product_id'],
					'purchase_id'	=> $pur->id,
					'warehouse'		=> $pur_item['warehouse_id'],
					'biller_id'		=> $this->default_biller_id
				);
				$this->db->insert('serial', $data);
			}
			
			$this->db->insert('purchase_items', $pur_item); 
			$this->site->syncQuantity(null, $pur->id);
		}
		
		return FALSE;
	}
	
	public function getSerialProducts($serial)
    {
        $this->db->select('serial_number');
		$q = $this->db->get_where('serial', array('serial_number' => $serial), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }
	
	public function getSerialByPurchaseId($id)
    {       
		$q = $this->db->get_where('serial', array('purchase_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }	
	
	public function getAllPurchaseReturnItems($return_id)
    {
        $this->db->select('return_purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details,companies.name')
			->join('return_purchases', 'return_purchases.id = return_purchase_items.return_id', 'left')
            ->join('products', 'products.id = return_purchase_items.product_id', 'left')
			->join('companies', 'companies.id = return_purchases.supplier_id', 'left')
            ->join('tax_rates', 'tax_rates.id = return_purchase_items.tax_rate_id', 'left')           
            ->group_by('return_purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('return_purchase_items', array('return_id' => $return_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getReturnByID($id)
    {
        $q = $this->db->get_where('return_purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getAllReturnItems($id)
    {
        $q = $this->db->get_where('return_purchase_items', array('return_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getSynBalanceQuantity($po_id, $product_id) {
        $this->db->select("SUM(COALESCE(erp_purchase_items.quantity_balance, 0)) as quantity_balance", False);
		$this->db->join('purchase_items', 'purchase_items.purchase_id = purchases.id', 'left');
        $this->db->where(array('purchases.purchase_order_id' => $po_id, 'purchase_items.product_id' => $product_id)); 
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->quantity_balance;
        }
        return 0;
    }
	
	//================= Check Expese Pro ================//
	public function getProductExpense($id){
		$q = $this->db->get_where('expense_categories',array('id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	//======================= End =======================//
}
