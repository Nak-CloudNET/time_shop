<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Quotes_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $warehouse_id, $limit = 15)
    {
        $this->db->select('products.id, code, name, type, warehouses_products.quantity, price, tax_rate, tax_method')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        // if ($this->Settings->overselling) {
            $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        // } else {
        //     $this->db->where("(products.track_quantity = 0 OR warehouses_products.quantity > 0) AND warehouses_products.warehouse_id = '" . $warehouse_id . "' AND "
        //         . "(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        // }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function addDeposit($data, $cdata, $payment = array())
    {
		//$this->erp->print_arrays($data, $cdata, $payment);
        if ($this->db->insert('deposits', $data)) {
				$deposit_id = $this->db->insert_id();
				$this->db->update('companies', $cdata, array('id' => $data['company_id']));
				if($payment){
					$payment['deposit_id'] = $deposit_id;
					if ($this->db->insert('payments', $payment)) {
						if ($this->site->getReference('sp') == $payment['reference_no']) {
							$this->site->updateReference('sp');
						}
						if ($payment['paid_by'] == 'gift_card') {
							$gc = $this->site->getGiftCardByNO($payment['cc_no']);
							$this->db->update('gift_cards', array('balance' => ($gc->balance - $payment['amount'])), array('card_no' => $payment['cc_no']));
						}
						return true;
					}
				}
            return true;
        }
        return false;
    }
	
	public function getQuotesData($quote_id=null){
		$q = $this->db->get_where('erp_quotes',array('id'=>$quote_id));
		if($q->num_rows()>0){
			return $q->row();
		}
		return null;
	}
	
    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getWHProduct($id)
    {
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        $q = $this->db->get_where('products', array('warehouses_products.product_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('quote_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllQuoteItemsWithDetails($quote_id)
    {
        $this->db->select('quote_items.id, quote_items.product_name, quote_items.product_code, quote_items.quantity, quote_items.serial_no, quote_items.tax, quote_items.unit_price, quote_items.val_tax, quote_items.discount_val, quote_items.gross_total, products.details');
        $this->db->join('products', 'products.id=quote_items.product_id', 'left');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('quotes_items', array('quote_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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
        $this->db->select('quote_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant, COALESCE(erp_products.tax_method,0) as method')
            ->join('products', 'products.id=quote_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=quote_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=quote_items.tax_rate_id', 'left')
            ->group_by('quote_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('quote_items', array('quote_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addQuote($data = array(), $items = array(), $payments = array())
    {
		//$this->erp->print_arrays($data, $items, $payments);
        if ($this->db->insert('quotes', $data)) {
            $quote_id = $this->db->insert_id();
            if ($this->site->getReference('qu') == $data['reference_no']) {
                $this->site->updateReference('qu');
            }
            foreach ($items as $item) {
                $item['quote_id'] = $quote_id;
                $this->db->insert('quote_items', $item);
            }
			
			if($payments){
				foreach($payments as $payment){
					$payment['deposit_quote_id'] = $quote_id;
					$payment['reference_no'] = $this->site->getReference('sp');
					$this->db->insert('payments', $payment);
					
					if ($this->site->getReference('sp') == $payment['reference_no']) {
						$this->site->updateReference('sp');
					}
				}
			}
            return true;
        }
        return false;
    }


    public function updateQuote($id, $data, $items = array(), $payments = array())
    {
        if ($this->db->update('quotes', $data, array('id' => $id)) && $this->db->delete('quote_items', array('quote_id' => $id))) {
            foreach ($items as $item) {
                $item['quote_id'] = $id;
                $this->db->insert('quote_items', $item);
            }
			
			if($payments){
				
				foreach($payments as $payment){
					$p = $this->site->getPaymentByReference($payment['reference_no']);
					if ($p){
						$reference_no = $payment['reference_no'];
						$payment['deposit_quote_id'] = $id;
						$this->db->update('payments', $payment, array('deposit_quote_id' => $id, 'reference_no' => $reference_no));
					}else{
						$payment['deposit_quote_id'] = $id;
						$payment['reference_no'] = $this->site->getReference('sp');
						$payment['created_by'] = $this->session->userdata('user_id');
						$this->db->insert('payments', $payment);
						if($this->site->getReference('sp') == $payment['reference_no']){
							$this->site->updateReference('sp');
						}
					}
				}
			}
			
            return true;
        }
        return false;
    }


    public function deleteQuote($id)
    {
        if ($this->db->delete('quote_items', array('quote_id' => $id)) && $this->db->delete('quotes', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function getPaymentByQuoteID($quote_id){
		$q = $this->db->get_where('payments', array('deposit_quote_id' => $quote_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getPaymentForQuote($quote_id){
		$this->db->order_by('note', 'ASC');
		$this->db->limit($limit);
		$this->db->where('deposit_quote_id', $quote_id);
		$this->db->where('(note = "Deposit1" OR note = "Deposit2" OR note = "Deposit3")');
		$q = $this->db->get_where('payments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
	}
	
	public function getFullPaymentByQuoteID($quote_id){
		$q = $this->db->get_where('payments', array('deposit_quote_id' => $quote_id, 'note' => 'Full Payment'), 1);
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

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getProductOptions($product_id, $warehouse_id)
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
	
	public function getQuoteDepositByQuoteID($quote_id){
		$q=$this->db->select("payments.amount AS deposit_amount, (erp_quotes.grand_total - erp_payments.amount) AS balance")
                ->from('quotes')
				->join('payments', 'payments.deposit_quote_id = quotes.id', 'left')
				->where('deposit_quote_id', $quote_id)
				->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getAllServices()
	{
		$q = $this->db->get_where('services');
		if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
	}
	
	public function getAllService($id)
	{
		$this->db->select('services.*, products.name as pro_name, brands.name as brand_name, categories.name as cat_name')
				 ->from('services')
				 ->join('products', 'services.modal = products.id', 'left')
				 ->join('brands', 'services.brand = brands.id', 'left')
				 ->join('categories', 'services.type = categories.id', 'left')
				 ->where('services.id', $id);
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
            return $q->row();
        }
	}
	
	public function getServiceByAjax($id)
	{
		$this->db->select('services.name as id, companies.name as name')
				 ->from('services')
				 ->join('companies', 'services.name = companies.id', 'left')
				 ->where('services.id', $id);
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
            return $q->row();
        }
	}
}
