<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Transfers_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $warehouse_id, $limit = 10)
    {
        $this->db->select('products.id, image, code, name, product_details, 
							warehouses_products.quantity, cost, tax_rate, type, tax_method,
							COALESCE(is_serial, "") as have_serial,
							COALESCE(
								(
									SELECT 
										GROUP_CONCAT(sp.serial_number) 
									FROM erp_serial as sp 
									WHERE sp.product_id='.$this->db->dbprefix('products').'.id AND 
										sp.warehouse = '.$warehouse_id.' AND
										sp.serial_status = 1
								)
							, 0) as serial_no
						')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("warehouses_products.warehouse_id = '" . $warehouse_id . "' AND warehouses_products.quantity > 0 AND "
                . "(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getComboSerial($id, $warehouse_id)
    {
    	$this->db->select('COALESCE(GROUP_CONCAT(erp_serial.serial_number), "")  as serial')
    			 ->join('products', 'products.code = combo_items.item_code', 'left')
    			 ->join('serial', 'serial.product_id = products.id', 'left')
    			 ->where(array('combo_items.product_id'=>$id, 'products.is_serial > '=>0, 'serial.warehouse'=> $warehouse_id, 'serial.serial_status >'=> 0));
        $q = $this->db->get('combo_items');
        if ($q->num_rows() > 0) {
            $row = $q->row();
            return $row->serial;
        }
        return FALSE;
    }
	
	public function getProductAllSer($product_id, $warehouse_id)
	{
		$this->db->select('GROUP_CONCAT(serial_number) as serial')
				 ->from('serial')
				 ->where(array('product_id'=>$product_id, 'warehouse' => $warehouse_id));
		$q = $this->db->get();
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
	
	public function getSerialProducts($serial)
    {
        $this->db->select('serial_number');
		$q = $this->db->get_where('serial', array('serial_number' => $serial, 'serial_status' => 1), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }

    public function addTransfer($data = array(), $items = array())
    { 
        $status = $data['status'];
        if ($this->db->insert('transfers', $data)) {
            $transfer_id = $this->db->insert_id();
            if ($this->site->getReference('to') == $data['transfer_no']) {
                $this->site->updateReference('to');
            }
            foreach ($items as $item) {
                $item['transfer_id'] 	= $transfer_id;
				$option_id 				= $item['option_id'];
				
				if($option_id){
					$option = $this->transfers_model->getProductOptionByID($option_id);
					$item['quantity_balance'] = $item['quantity'] * $option->qty_unit;
				}
				
				$type = $item['type'];
				unset($item['type']);
				
				$this->db->insert('transfer_items', $item);
				
				if ($status == 'completed') {
					if($type == 'combo'){
						$combo = $this->site->getProductComboItems($item['product_id']);
						foreach($combo as $com){
							$comboItem[] = array(
								'product_id' 		=> $com->id,
								'product_code' 		=> $com->code,
								'product_name' 		=> $com->name,
								'type' 				=> $com->type,
								'transfer_id'		=> $transfer_id,
								'quantity' 			=> $item['quantity'],
								'quantity_balance' 	=> $item['quantity_balance'],
								'warehouse_id' 		=> $item['warehouse_id'],
								'date' 				=> $item['date'],
								'transaction_type'	=> 'transfer',
								'status'			=> 'received'
							);
							$comboItem[] = array(
								'product_id' 		=> $com->id,
								'product_code' 		=> $com->code,
								'product_name' 		=> $com->name,
								'type' 				=> $com->type,
								'transfer_id'		=> $transfer_id,
								'quantity' 			=> (-1) * $item['quantity'],
								'quantity_balance' 	=> (-1) * $item['quantity_balance'],
								'warehouse_id' 		=> $data['from_warehouse_id'],
								'date' 				=> $item['date'],
								'transaction_type'	=> 'transfer',
								'status'			=> 'received'
							);
						}
						$this->db->insert_batch('purchase_items', $comboItem);
						foreach($combo as $com){
							$this->site->syncQuantity(null, null, null, $com->id);
						}
					} else {
						$qty 					= $item['quantity'];
						$item['date'] 			= date('Y-m-d');
						$item['warehouse_id'] 	= $data['to_warehouse_id'];
						$item['type']			= $type;
						$item['transaction_type'] = 'transfer';
						$item['status'] 		= 'received';
						$this->db->insert('purchase_items', $item);
						
						$item['warehouse_id'] 	= $data['from_warehouse_id'];
						$item['quantity'] 		= (-1) * $qty;
						$item['quantity_balance'] = (-1) * $qty;
						$this->db->insert('purchase_items', $item);
						
						$this->site->syncQuantity(null, null, null, $item['product_id']);
					}
				}
            
				if($item['serial_no']){
					$this->db->update('serial', array('warehouse'=>$data['to_warehouse_id']), array('serial_number' => $item['serial_no']) );
				}
				
			}
            return true;
        }
        return false;
    }

    public function updateTransfer($id, $data = array(), $items = array())
    {
        //$ostatus = $this->resetTransferActions($id);
        $status = $data['status'];
        if ($this->db->update('transfers', $data, array('id' => $id))) {
            $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
            $this->db->delete('transfer_items', array('transfer_id' => $id));
			$this->db->delete('purchase_items', array('transfer_id' => $id));

            foreach ($items as $item) {
                $item['transfer_id'] 	= $id;
				$option_id 				= $item['option_id'];
				
				if($option_id){
					$option = $this->transfers_model->getProductOptionByID($option_id);
					$item['quantity_balance'] = $item['quantity'] * $option->qty_unit;
				}
				
				$type = $item['type'];
				unset($item['type']);
				
				$this->db->insert('transfer_items', $item);
				
				if ($status == 'completed') {
					if($type == 'combo'){
						$combo = $this->site->getProductComboItems($item['product_id']);
						foreach($combo as $com){
							$comboItem[] = array(
								'product_id' 		=> $com->id,
								'product_code' 		=> $com->code,
								'product_name' 		=> $com->name,
								'type' 				=> $com->type,
								'transfer_id'		=> $id,
								'quantity' 			=> $item['quantity'],
								'quantity_balance' 	=> $item['quantity_balance'],
								'warehouse_id' 		=> $item['warehouse_id'],
								'date' 				=> $item['date'],
								'transaction_type'	=> 'transfer',
								'status'			=> 'received'
							);
							$comboItem[] = array(
								'product_id' 		=> $com->id,
								'product_code' 		=> $com->code,
								'product_name' 		=> $com->name,
								'type' 				=> $com->type,
								'transfer_id'		=> $id,
								'quantity' 			=> (-1) * $item['quantity'],
								'quantity_balance' 	=> (-1) * $item['quantity_balance'],
								'warehouse_id' 		=> $data['from_warehouse_id'],
								'date' 				=> $item['date'],
								'transaction_type'	=> 'transfer',
								'status'			=> 'received'
							);
						}
						$this->db->insert_batch('purchase_items', $comboItem);
						foreach($combo as $com){
							$this->site->syncQuantity(null, null, null, $com->id);
						}
					} else {
						
						$qty 					= $item['quantity'];
						$item['date'] 			= date('Y-m-d');
						$item['warehouse_id'] 	= $data['to_warehouse_id'];
						$item['type']			= $type;
						$item['transaction_type'] = 'transfer';
						$item['status'] 		= 'received';
						$this->db->insert('purchase_items', $item);
						
						$item['warehouse_id'] 	= $data['from_warehouse_id'];
						$item['quantity'] 		= (-1) * $qty;
						$item['quantity_balance'] = (-1) * $qty;
						$this->db->insert('purchase_items', $item);
						
						$this->site->syncQuantity(null, null, null, $item['product_id']);
					}
				}
            
				if($item['serial_no']){
					$this->db->update('serial', array('warehouse'=>$data['to_warehouse_id']), array('serial_number' => $item['serial_no']) );
				}
				
			}
            return true;
        }
        return false;
    }
	
	public function transferBack($data = array(), $items = array())
    {
        $status = $data['status'];
        if ($this->db->insert('transfers', $data)) {
            $transfer_id = $this->db->insert_id();
            if ($this->site->getReference('to') == $data['transfer_no']) {
                $this->site->updateReference('to');
            }
            foreach ($items as $item) {
                $item['transfer_id'] = $transfer_id;
				$option_id = $item['option_id'];
				
				if($option_id){
					$option = $this->transfers_model->getProductOptionByID($option_id);
					$item['quantity_balance'] = $item['quantity'] * $option->qty_unit;
				}
				
                if ($status == 'completed') {
                    $item['date'] = date('Y-m-d');
                    $item['warehouse_id'] = $data['to_warehouse_id'];
                    $item['status'] = 'received';
                    $this->db->insert('purchase_items', $item);
                } else {
                    $this->db->insert('transfer_items', $item);
                }
                if ($status == 'sent' || $status == 'completed') {
                    $this->syncTransderdItem($item['product_id'], $data['from_warehouse_id'], $item['quantity'], $item['option_id']);
					
					//from warehouse
					$qty = $this->getWareQty($item['product_id'], $data['from_warehouse_id']);
					
					if($qty){
						$final_qty = $qty->quantity - $item['quantity'];
						$this->db->update('warehouses_products', array('quantity' => $final_qty), array('product_id' => $item['product_id'], 'warehouse_id' => $data['from_warehouse_id']));
					}else{
						$final_qty = $qty->quantity - $item['quantity'];
						$this->db->insert('warehouses_products', array('quantity' => $final_qty, 'product_id' => $item['product_id'], 'warehouse_id' => $data['from_warehouse_id']));
					}
					
					//to warehouse
					$to_qty = $this->getWareQty($item['product_id'], $data['to_warehouse_id']);
					if($to_qty){
						$tof_qty = $to_qty->quantity + $item['quantity'];
						$this->db->update('warehouses_products', array('quantity' => $tof_qty), array('product_id' => $item['product_id'], 'warehouse_id' => $data['to_warehouse_id']));
					}else{
						$tof_qty = $to_qty->quantity + $item['quantity'];
						$this->db->insert('warehouses_products', array('quantity' => $tof_qty, 'product_id' => $item['product_id'], 'warehouse_id' => $data['to_warehouse_id']));
					}
					
					
					//update products
					$PQty = $this->getQty($item['product_id']);
					$this->db->update('products', array('quantity' => $PQty->qty), array('id' => $item['product_id']));
                }
            }
            return true;
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

    public function getProductByCategoryID($id)
    {

        $q = $this->db->get_where('products', array('category_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return true;
        }

        return FALSE;
    }

    public function getProductQuantity($product_id, $warehouse = DEFAULT_WAREHOUSE)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return FALSE;
    }

    public function insertQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
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

    public function getTransferByID($id)
    {
        $this->db->select("erp_transfers.*,SUM(COALESCE(erp_transfer_items.quantity)) as total_quantity")
		->join("erp_transfer_items","erp_transfer_items.transfer_id=erp_transfers.id","LEFT") 
		->where("erp_transfers.id",$id)
		->group_by("erp_transfers.id"); 
        $q = $this->db->get('transfers');
        if ($q->num_rows() > 0){
            return $q->row();
        }
        return FALSE;
    }

    public function getAllTransferItems($transfer_id, $status)
    {
		$this->db->select('transfer_items.*, product_variants.name as variant, products.unit')
				 ->from('transfer_items')
				 ->join('products', 'products.id=transfer_items.product_id', 'left')
				 ->join('product_variants', 'product_variants.id=transfer_items.option_id', 'left')
				 ->group_by('transfer_items.id')
				 ->where('transfer_id', $transfer_id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getWarehouseProduct($warehouse_id, $product_id, $variant_id)
    {
        if ($variant_id) {
            $data = $this->getProductWarehouseOptionQty($variant_id, $warehouse_id);
            return $data;
        } else {
            $data = $this->getWarehouseProductQuantity($warehouse_id, $product_id);
            return $data;
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
	
	public function getWarehouseProductCombo($warehouse_id, $product_id)
	{
		$this->db->select('products.code, products.name, 
							('.$this->db->dbprefix('warehouses_products').'.quantity * '.$this->db->dbprefix('combo_items').'.quantity) AS quantity'
						)
				 ->from('combo_items')
				 ->join('products', 'products.code = combo_items.item_code', 'left')
				 ->join('warehouses_products', 'products.id = warehouses_products.product_id', 'left')
				 ->where(array('combo_items.product_id'=> $product_id, 'warehouses_products.warehouse_id' => $warehouse_id));
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
	}

    public function resetTransferActions($id)
    {
        $otransfer = $this->transfers_model->getTransferByID($id);
        $oitems = $this->transfers_model->getAllTransferItems($id, $otransfer->status);
        $ostatus = $otransfer->status;
        if ($ostatus == 'sent' ||$ostatus == 'completed') {
            // $this->db->update('purchase_items', array('warehouse_id' => $otransfer->from_warehouse_id, 'transfer_id' => NULL), array('transfer_id' => $otransfer->id));
            foreach ($oitems as $item) {
                $option_id = (isset($item->option_id) && ! empty($item->option_id)) ? $item->option_id : NULL;
				$qty_balance = 0;
				if($option_id){
					$option = $this->getProductOptionByID($option_id);
					$qty_balance = $item->quantity * $option->qty_unit;
				}
				
                $clause = array('purchase_id' => NULL, 'transfer_id' => NULL, 'product_id' => $item->product_id, 'warehouse_id' => $otransfer->from_warehouse_id, 'option_id' => $option_id);
                $pi = $this->site->getPurchasedItem(array('id' => $item->id));
                if ($ppi = $this->site->getPurchasedItem($clause)) {
					if($option_id){
						$quantity_balance = $ppi->quantity_balance + $qty_balance;
					}else{
						$quantity_balance = $ppi->quantity_balance + $item->quantity;
					}
                    
                    $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), $clause);
                } else {
                    $clause['quantity'] = $item->quantity;
                    $clause['item_tax'] = 0;
					if($option_id){
						$clause['quantity_balance'] = $qty_balance;
					}else{
						$clause['quantity_balance'] = $item->quantity;
					}
                    
                    $this->db->insert('purchase_items', $clause);
                }
            }
        }
        return $ostatus;
    }

    public function deleteTransfer($id)
    {
        $ostatus = $this->resetTransferActions($id);
        $oitems = $this->transfers_model->getAllTransferItems($id, $ostatus);
        $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
        if ($this->db->delete('transfers', array('id' => $id)) && $this->db->delete($tbl, array('transfer_id' => $id))) {
            foreach ($oitems as $item) {
                $this->site->syncQuantity(NULL, NULL, NULL, $item->product_id);
            }
            return true;
        }
        return FALSE;
    }

    public function getProductOptions($product_id, $warehouse_id, $zero_check = TRUE)
    {
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.cost as cost, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->where('warehouses_products_variants.warehouse_id', $warehouse_id)
            ->group_by('product_variants.id');
        if ($zero_check) {
            $this->db->where('warehouses_products_variants.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductComboItems($pid, $warehouse_id)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, warehouses_products.quantity as quantity')
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

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', array('name' => $name, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function getPurchasedItems($product_id, $warehouse_id, $option_id = NULL) {
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->select('id, quantity, quantity_balance, net_unit_cost, unit_cost, item_tax');
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
	
	public function syncTransderdItem($product_id, $warehouse_id, $quantity, $option_id = NULL)
    {		
		if($option_id){
			$option = $this->getProductOptionByID($option_id);
			$quantity = $quantity * $option->qty_unit;
		}
        if ($pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id)) {
            $balance_qty = $quantity;
            foreach ($pis as $pi) {
                if ($balance_qty <= $quantity && $quantity > 0) {
                    if ($pi->quantity_balance >= $quantity) {
                        $balance_qty = $pi->quantity_balance - $quantity;
                        $this->db->update('purchase_items', array('quantity_balance' => $balance_qty), array('id' => $pi->id));
                        $quantity = 0;
                    } elseif ($quantity > 0) {
                        $quantity = $quantity - $pi->quantity_balance;
                        $balance_qty = $quantity;
                        $this->db->update('purchase_items', array('quantity_balance' => 0), array('id' => $pi->id));
                    }
                }
                if ($quantity == 0) { break; }
            }
        } else {
            $clause = array('purchase_id' => NULL, 'transfer_id' => NULL, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
            if ($pi = $this->site->getPurchasedItem($clause)) {
                $quantity_balance = $pi->quantity_balance - $quantity;
                $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), $clause);
            } else {
                $clause['quantity'] = 0;
                $clause['item_tax'] = 0;
                $clause['quantity_balance'] = (0 - $quantity);
                $this->db->insert('purchase_items', $clause);
            }
        }
		//Please don't delete this commend i keep to know the flow
		//Return worng when transfer many time 
        //$this->site->syncQuantity(NULL, NULL, NULL, $product_id);
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function getWareQty($product_id, $warehouse_id){
		$this->db->select('quantity')
				 ->from('warehouses_products')
				 ->where(array('product_id'=>$product_id, 'warehouse_id'=>$warehouse_id));
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
		
	}
	
	public function getOldWareQty($product_id){
		$this->db->select('quantity')
				 ->from('purchase_items')
				 ->where(array('transfer_id'=>$product_id));
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
		
	}
	
	public function getQty($product_id){
		$this->db->select('SUM(quantity) AS qty')
				 ->from('warehouses_products')
				 ->where(array('product_id'=>$product_id));
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
		
	}
	
	public function getDocumentByID($id){
		$this->db->select('attachment, attachment1, attachment2')
				 ->from('transfers')
				 ->where('id',$id);
		$q = $this->db->get();
		if($q->num_rows() > 0){
			return $q->result();
		}
		return false;
	}
}
