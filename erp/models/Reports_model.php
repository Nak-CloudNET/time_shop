<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reports_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $limit = 5)
    {
        $this->db->select('id, code, name')
            ->like('name', $term, 'both')
			->or_like('code', $term, 'both');
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
	public function getStockINOUT($product,$category,$warehouse,$wid,$in_out,$year,$month,$per_page,$ob_set){
		$datee = $year.'-'.$month;
		$this->db->select("purchase_items.product_id,products.code,products.name,purchase_items.date,DATE_FORMAT(erp_purchase_items.date, '%Y-%m') as dater,DATE_FORMAT(erp_purchase_items.date, '%d') as datday,units.name as name_unit")
		->join("products","products.id=purchase_items.product_id","LEFT")
		->join("units","units.id=products.unit","LEFT")
		->where("DATE_FORMAT(erp_purchase_items.date, '%Y-%m')=",$datee);
		if($product){
			$this->db->where("purchase_items.product_id",$product);
		}
		if($category){
			$this->db->where("products.category_id",$category);
		}
		if($warehouse){
			$this->db->where("erp_purchase_items.warehouse_id",$warehouse);
		}else{
			if($wid){
				$this->db->where("erp_purchase_items.warehouse_id IN ($wid)");
			}
		}
		$this->db->group_by("purchase_items.product_id")
		->group_by("DATE_FORMAT('erp_purchase_items.date', '%Y-%m')");
		
		 $this->db->limit($per_page,$ob_set); 
		$q = $this->db->get("purchase_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getWareFullByUSER($id){
		if($id){
			$this->db->where("id IN ($id)");
		}
		$q = $this->db->get("erp_warehouses");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getStockINOUTNUM($product,$category,$in_out,$year,$month,$warehouse,$wid){
		
		$datee = $year.'-'.$month;
		$this->db->select("purchase_items.product_id,products.code,products.name,purchase_items.date,DATE_FORMAT(erp_purchase_items.date, '%Y-%m') as dater,DATE_FORMAT(erp_purchase_items.date, '%d') as datday")
		->join("products","products.id=purchase_items.product_id","LEFT")
		->where("DATE_FORMAT(erp_purchase_items.date, '%Y-%m')=",$datee);
		if($product){
			$this->db->where("purchase_items.product_id",$product);
		}
		if($category){
			$this->db->where("products.category_id",$category);
		}
		if($warehouse){
			$this->db->where("erp_purchase_items.warehouse_id",$warehouse);
		}else{
			if($wid){
				$this->db->where("erp_purchase_items.warehouse_id IN ($wid)");
			}
		}
		$this->db->group_by("purchase_items.product_id")
		->group_by("DATE_FORMAT('erp_purchase_items.date', '%Y-%m')");
		$q = $this->db->get("purchase_items");
		if ($q->num_rows() > 0) {
            return $q->num_rows();
        }
        return FALSE;
	}
	public function getProductDaily($date){
		$this->db->select("erp_products.id,DATE_FORMAT(date,'%e') as date,(COALESCE (
		(
			SELECT
				SUM(
					COALESCE (erp_sale_items.quantity, 0)
				) AS out_qty
			FROM
				erp_sale_items
			WHERE
				erp_sale_items.product_id = erp_products.id
		),
		0
	) + COALESCE (
		(
			SELECT
				SUM(
					COALESCE (
						erp_purchase_items.quantity,
						0
					)
				) AS out_qty
			FROM
				erp_purchase_items
			WHERE
				erp_purchase_items.product_id = erp_products.id
		),
		0
	)) as total_qty");
	    $this->db->JOIN('erp_sale_items','erp_sale_items.product_id=erp_products.id','LEFT');
		$this->db->JOIN('erp_sales','erp_sales.id=erp_sale_items.sale_id','LEFT');
	    $this->db->where_in("DATE_FORMAT(date,'%e')",$date);
		$q =$this->db->get('erp_products');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
		
	}
	public function getWareByUserID(){
		$q = $this->db->get_where("users",array("id"=>$this->session->userdata('user_id')),1);
		if ($q->num_rows() > 0) {
            return $q->row()->warehouse_id;
        }
        return FALSE;
	}
	public function getMonthSales($date, $warehouse_id = NULL, $year = NULL, $month = NULL)
    {
        $this->db->select("date, reference_no, customer, total_discount, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status");
		
		if($date) {
            $this->db->where('sales.date', $date);
        }elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('sales.date >=', $year.'-'.$month.'-01 00:00:00');
            $this->db->where('sales.date <=', $year.'-'.$month.'-'.$last_day.' 23:59:59');
        }
		
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
	public function getSaleDailies($date)
    {
        $this->db->select("date, reference_no, customer, sale_status, total_discount, grand_total, paid, (grand_total-paid) as balance, payment_status")
			->where("date >=", $date.' 00:00:00')
			->where("date <=", $date.' 23:55:00');

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
	public function getTotalQty($d){
		$start_date = $d.' 00:00:00';
		$end_date  = $d.' 23:55:00';
		$this->db->select("SUM(COALESCE(quantity,0)) as total_qty")
		->join("erp_sale_items","erp_sale_items.sale_id=erp_sales.id","LEFT")
		->where("date >=",$start_date)
		->where("date <=",$end_date);
		$q =$this->db->get('erp_sales');
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getExespens($year){
		$this->db->select("DATE_FORMAT(date,'%c')as date,SUM(COALESCE(amount,0)) as amount")
		->where("DATE_FORMAT(date,'%Y')",$year)
		->group_by("DATE_FORMAT(date,'%c')")
		->order_by("DATE_FORMAT(date,'%c') ASC");
		$q = $this->db->get('erp_expenses');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getTotalMonthlyQty($year){
		$this->db->select("DATE_FORMAT(date,'%c') as date,SUM(COALESCE(erp_sale_items.quantity,0)) as total_qty")
		->join('erp_sale_items','erp_sale_items.sale_id=erp_sales.id','LEFT')
		->where("DATE_FORMAT(date,'%Y')",$year)
		->group_by("DATE_FORMAT(date,'%c')")
		->order_by("DATE_FORMAT(date,'%c') ASC");
		$q = $this->db->get('erp_sales');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getQtyByWare($pid,$wid,$product2,$category2){
		$this->db->select("COALESCE(erp_warehouses_products.quantity,0) as wqty");
		$this->db->join("products","products.id=warehouses_products.product_id","LEFT");
		if($product2){
			$this->db->where("warehouses_products.product_id",$product2);
		}
		if($category2){
			$this->db->where("products.category_id",$category2);
		}
		$this->db->where("product_id",$pid);
		$this->db->where("warehouse_id",$wid);
		$q = $this->db->get("erp_warehouses_products");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getProduct()
    {
		$this->db->select('id, code, name');
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getAllbrand(){
		$q =$this->db->get('erp_brands');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getBrandExport($id,$start_date,$end_date){
		 
		$this->db->select('erp_brands.id, erp_sales.date, erp_brands.name as brand,SUM(erp_sale_items.quantity) as qty, SUM(erp_sale_items.subtotal) as gt')
		->join('sale_items','sale_items.sale_id=sales.id','left')
        ->join('products', 'products.id=sale_items.product_id','left')
        ->join('brands', 'brands.id=products.brand_id','left') 
        ->where("erp_sales.date BETWEEN  '$start_date' AND '$end_date'")  
		->where("erp_brands.id",$id) 
        ->group_by('erp_brands.id');
		$q = $this->db->get('erp_sales');
		 if ($q->num_rows() > 0){
            return $q->row();
        }
        return FALSE;
	}
	/*public function getAllbrand($search_term='default'){
		 $query=$this->db->select('erp_sale_items.quantity,erp_sales.date,erp_categories.id as cate,erp_sale_items.unit_price,erp_sale_items.subtotal,erp_categories.NAME as category,erp_subcategories.name as subcate,erp_sale_items.product_code,erp_sale_items.product_name,erp_serial.serial_number')
		 ->join('erp_products','erp_products.id=erp_sale_items.product_id','LEFT')
		 ->join('erp_categories','erp_categories.id=erp_products.category_id','LEFT')
		 ->join('erp_subcategories','erp_subcategories.id=erp_products.subcategory_id','LEFT')
		 ->join('erp_brands','erp_brands.id=erp_products.brand_id','LEFT')
		 ->join('erp_serial','erp_serial.id = erp_sale_items.serial_no','LEFT')
		 ->join('erp_sales','erp_sales.id=erp_sale_items.sale_id','LEFT')
		 ->like('erp_categories.id',$search_term['cate'])
		 ->like('erp_sales.date BETWEEN "'. $search_term['d']. '" and "'.$search_term['e'].'"');
		 
		 
		 $q= $this->db->get('erp_sale_items');
		 if($q->num_rows()>0){
			 foreach(($q->result()) as $row){
				 $data[]=$row;
			 }
			 return $data;
		 }
		 return false;
		 
	}*/
	public function getSales(){
		$this->db->select("erp_sales.date,erp_sales.reference_no,erp_sales.customer,erp_warehouses.name as warehouse,erp_sales.biller")
		->join('erp_warehouses','erp_warehouses.id=erp_sales.warehouse_id','LEFT');
		$q = $this->db->get("erp_sales");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
    public function getbrand(){
		   $this->db->select('erp_brands.name as brand')
		            ->join('erp_products','erp_sale_items.product_id = erp_products.id','left')
					->join('erp_brands','erp_products.brand_id = erp_brands.id');
		   $this->db->group_by('erp_brands.id');
			$q = $this->db->get('erp_sale_items');
		 if($q->num_rows()> 0){
			 foreach(($q->result()) as $row){
				 $data[]=$row;
			 }
			 return $data;
		 }
		 return false;
		 
	}
	public function searchbrand(){
		$this->db->select("brands.*");
		$q = $this->db->get("brands");
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

    public function getSalesTotals($customer_id)
    {

        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('customer_id', $customer_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCustomerSales($customer_id)
    {
        $this->db->from('sales')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }

    public function getCustomerQuotes($customer_id)
    {
        $this->db->from('quotes')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }

    public function getCustomerReturns($customer_id)
    {
        $this->db->from('return_sales')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }
	
	public function getCustomerDeposits($company_id)
    {
        $this->db
                ->from('deposits')
                ->join('users', 'users.id=deposits.created_by', 'left')
				->where($this->db->dbprefix('deposits') . ".company_id", $company_id);
        return $this->db->count_all_results();
    }

    public function getStockValue()
    {
        $q = $this->db->query("SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select COALESCE(sum(" . $this->db->dbprefix('warehouses_products') . ".quantity), 0)*price as by_price, COALESCE(sum(" . $this->db->dbprefix('warehouses_products') . ".quantity), 0)*cost as by_cost FROM " . $this->db->dbprefix('products') . " JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id=" . $this->db->dbprefix('products') . ".id GROUP BY " . $this->db->dbprefix('products') . ".id )a");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	 public function getWarehouseStockValue($id)
    {
        $q = $this->db->query("SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select sum(COALESCE(" . $this->db->dbprefix('warehouses_products') . ".quantity, 0))*price as by_price, sum(COALESCE(" . $this->db->dbprefix('warehouses_products') . ".quantity, 0))*cost as by_cost FROM " . $this->db->dbprefix('products') . " JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id=" . $this->db->dbprefix('products') . ".id WHERE " . $this->db->dbprefix('warehouses_products') . ".warehouse_id = ? GROUP BY " . $this->db->dbprefix('products') . ".id )a", array($id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	//chivorn chart stock
	
		
	public function getCategoryStockValue($biller= NULL,$customer= NULL,$start_date= NULL,$end_date= NULL)
    {
		if($biller != NULL){
			$where_biller = " AND erp_sales.biller_id=".$biller;
		}else{
			$where_biller = "";
		}
		if($customer != NULL){
			$where_customer = " AND erp_sales.customer_id=".$customer;
		}else{
			$where_customer = "";
		}
		if($start_date != NULL && $end_date != NULL){
			$where_between_date = " AND erp_sales.date between '$start_date' AND '$end_date'";
		}else{
			$where_between_date = "";
		}
		
		$q = $this->db->query("
			SELECT
				COALESCE (
					sum(
						erp_sale_items.subtotal
					),
					0
				) AS by_price,
				erp_categories.name AS category_name
			FROM
				erp_products
			JOIN erp_warehouses_products ON erp_warehouses_products.product_id = erp_products.id
			JOIN erp_categories ON erp_categories.id = erp_products.category_id
			JOIN erp_sale_items ON erp_sale_items.product_id = erp_products.id
			JOIN erp_sales ON erp_sales.id = erp_sale_items.sale_id WHERE 1=1 $where_biller $where_customer $where_between_date
			GROUP BY
				erp_categories.id");
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
	public function getChartValue()
    {
		$q = $this->db->query("
			SELECT
				accountcode,
				accountname,
				COALESCE (
					sum(
						amount
					),
					0
				) AS total_amount
			FROM
				erp_gl_charts
			LEFT JOIN erp_gl_trans ON erp_gl_trans.account_code = erp_gl_charts.accountcode
			WHERE
				erp_gl_charts.bank = 1
			GROUP BY
				accountcode;");
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
	function getCategory($brand_id){
		$q = $this->db->get('categories');

		$this->db->where('erp_categories.brand_id',$brand_id);
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
    function getbrands(){
        $q = $this->db->get('brands');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    function getProductBrands(){
        $q = $this->db->get('products');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getCategtoryBybrandID($brand_id){
        $this->db->select("categories.id, categories.name")
            ->from('erp_categories')
            ->where('erp_categories.brand_id', $brand_id);

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
    public function getProductBybrandID($brandID,$category_id){
        $this->db->select("products.id, products.name, products.code, products.quantity,products.image")
            ->from('erp_products')
            ->where('erp_products.category_id', $category_id)
            ->where('erp_products.brand_id',$brandID)
            ->where('erp_products.type != "combo"');


        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }


    function getDataReportDetail($id)
	{
		if($id)
		{
			$q = $this->db->query("
								SELECT
									`erp_sales`.`id`,
									`erp_categories`.`id` AS `categoryId`,
									`erp_categories`.`name` AS `categoryName`,
									CONCAT(
										erp_sale_items.product_name,
										' (',
										`erp_sale_items`.`product_code`,
										')'
									) AS productName,
									erp_sale_items.product_id as product_id,
									`erp_products`.`quantity` AS `stockInHand`,
									SUM(erp_sale_items.quantity) AS saleQuantity,
									
									`erp_products`.`cost` AS `unitCost`,
									`erp_sale_items`.`unit_price` AS `unitPrice`,
									sum(
										(
											erp_sale_items.unit_price * erp_sale_items.quantity
										) - (
											CASE
											WHEN discount LIKE '%\%' THEN
												(
													erp_sale_items.unit_price * erp_sale_items.quantity
												) * SUBSTRING_INDEX(discount, '%', 1) / 100
											ELSE
												erp_sale_items.discount
											END
										)
									) AS revenue,
									(
										IFNULL(
											SUM(erp_sale_items.quantity) * erp_products.cost,
											0
										)
									) AS coms,
									sum(
										(
											(
												erp_sale_items.unit_price * erp_sale_items.quantity
											) - (
												CASE
												WHEN discount LIKE '%\%' THEN
													(
														erp_sale_items.unit_price * erp_sale_items.quantity
													) * SUBSTRING_INDEX(discount, '%', 1) / 100
												ELSE
													erp_sale_items.discount
												END
											)
										) - (
											IFNULL(
												erp_sale_items.quantity * erp_products.cost,
												0
											)
										)
									) AS profit,
									sum(
										CASE
										WHEN discount LIKE '%\%' THEN
											(
												erp_sale_items.unit_price * erp_sale_items.quantity
											) * SUBSTRING_INDEX(discount, '%', 1) / 100
										ELSE
											erp_sale_items.discount
										END
									) as total_discount
								FROM
									`erp_sales`
								JOIN `erp_sale_items` ON `erp_sales`.`id` = `erp_sale_items`.`sale_id`
								JOIN `erp_products` ON `erp_sale_items`.`product_id` = `erp_products`.`id`
								JOIN `erp_categories` ON `erp_products`.`category_id` = `erp_categories`.`id`
								WHERE
									`erp_categories`.`id` = '".$id."'
								GROUP BY
									`erp_categories`.`id`,
									`erp_products`.`id`,
									erp_sale_items.unit_price,
									erp_sale_items.discount
			
							")->result();

			$row = '';
			$stockInHand = 0;
			$saleQuantity = 0;
			$unitCost = 0;
			$unitPrice = 0;
			$revenue = 0;
			$coms = 0;
			$profit = 0;
			$grand_discount = 0;
			
			foreach($q as $data_row){
				$stockInHand+=$data_row->stockInHand;
				$saleQuantity+=$data_row->saleQuantity;
				$unitCost+=$data_row->unitCost;
				$unitPrice+=$data_row->unitPrice;
				$revenue+=$data_row->revenue;
				$grand_discount+=$data_row->total_discount;
				$coms+=$data_row->coms;
				$profit+=$data_row->profit;
				$warehouses = $this->getAllWarehouses();
				
				$row.='<tr>';					
					$row.='<td>'.$data_row->productName.'</td>';
					
					$row.='<input type="hidden" value="'.$data_row->product_id.'">';
					foreach($warehouses as $warehouse){
						$warehouseQty = $this->getWHQty($data_row->product_id, $warehouse->id);
						$row.='<td>'.(($warehouseQty > 0)? $this->erp->formatQuantity($warehouseQty):$this->erp->formatQuantity(1)).'</td>';
					}
					$row.='<td>'.$this->erp->formatQuantity($data_row->saleQuantity) .'</td>';
					$row.='<td>'.$this->erp->formatMoney($data_row->unitCost).'</td>';
					$row.='<td>'.$this->erp->formatMoney($data_row->unitPrice) .'</td>';
					$row.='<td>'.$this->erp->formatMoney($data_row->total_discount) .'</td>';
					$row.='<td>'.$this->erp->formatMoney($data_row->revenue) .'</td>';
					$row.='<td>'.$this->erp->formatMoney($data_row->coms) .'</td>';
					$row.='<td>'.$this->erp->formatMoney($data_row->profit) .'</td>';											
				$row.='</tr>';			
			}
			
			$row.='<tr style="font-weight:bold;">';
					$row.='<td style="background-color:pink;text-align:right;padding-right:10px;">'."Grand Total".'</td>';
					
					$row.='<input type="hidden" value="'.$data_row->product_id.'">';
					foreach($warehouses as $warehouse){
						$warehouseQty = $this->getWHQty($data_row->product_id, $warehouse->id);
						$row.='<td>'.(($warehouseQty > 0)? $this->erp->formatQuantity($warehouseQty):$this->erp->formatQuantity(1)).'</td>';
					}
					$row.='<td>'.$this->erp->formatQuantity($saleQuantity) .'</td>';
					$row.='<td>'.$this->erp->formatMoney($unitCost).'</td>';
					$row.='<td>'.$this->erp->formatMoney($unitPrice) .'</td>';
					$row.='<td>'.$this->erp->formatMoney($grand_discount) .'</td>';
					$row.='<td>'.$this->erp->formatMoney($revenue) .'</td>';
					$row.='<td>'.$this->erp->formatMoney($coms) .'</td>';
					$row.='<td>'.$this->erp->formatMoney($profit) .'</td>';											
				$row.='</tr>';
			return $row;
		}else{
			return "Data Not Found";
		}
	}
	
	function getWHQty($product_id, $wh_id){
		$this->db->select('erp_warehouses_products.quantity as wqty');
		$this->db->where(array('erp_warehouses_products.product_id' => $product_id, 'erp_warehouses_products.warehouse_id' => $wh_id));
		$this->db->from('erp_warehouses_products');
		$result = $this->db->get();
		if($result->num_rows()>0){
			return $row;
		}
		return false;
		
	}
	function getwarehouseName(){
		
		$q=$this->db->get('erp_warehouses');
		if($q->num_rows()>0){
			foreach(($q->result()) as $row){
				 $data[]=$row;
			}
			return $data;
		}
		return false;
	}
	function search($keyword)
    {
        $this->db->select('*');
		   $this->db->from('erp_warehouses');
		   $this->db->where('id', $keyword);
		 
		$q = $this->db->get();
		if($q->num_rows()>0){
			
			return $q->row();
		}
		return false;
    }
	
	function getCategoryName($category_id = NULL, $product_id = NULL, $start = NULL, $end = NULL, $biller_id = NULL){
		$this->db->select("categories.id,categories.name,sales.date");
		$this->db->from("sale_items");
		$this->db->join('products','products.id = sale_items.product_id');
		$this->db->join('categories','categories.id = products.category_id');
		$this->db->join('sales','sales.id = sale_items.sale_id');
		if($category_id) {
			$this->db->where('categories.id', $category_id);
		}
		if($product_id){
			$this->db->where('products.id', $product_id);
		}
		if($start && $end) {
			$this->db->where('sales.date >= ' . $start . ' AND sales.date <= ' . $end . ' ');
		}
		if($biller_id) {
			$this->db->where('sales.biller_id', $biller_id);
		}
		$this->db->group_by('categories.id');
		$q = $this->db->get();
		if($q->num_rows() > 0 ) {
			return $q->result();
		}
		return false;
	}
	function getProductName(){
		$q = $this->db->get('products');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getCategoryStockValueById($id, $biller= NULL,$customer= NULL,$start_date= NULL,$end_date= NULL)
    {
		if($biller != NULL){
			$where_biller = " AND erp_sales.biller_id=".$biller;
		}else{
			$where_biller = "";
		}
		if($customer != NULL){
			$where_customer = " AND erp_sales.customer_id=".$customer;
		}else{
			$where_customer = "";
		}
		
		if($start_date != NULL && $end_date != NULL){
			$where_between_date = " AND erp_sales.date between '$start_date' AND '$end_date'";
		}else{
			$where_between_date = "";
		}
		
        $q = $this->db->query("
			SELECT
				COALESCE (
					sum(
						erp_sale_items.subtotal
					),
					0
				) AS by_price,
				erp_categories.name AS category_name
			FROM
				erp_products
			JOIN erp_warehouses_products ON erp_warehouses_products.product_id = erp_products.id
			JOIN erp_categories ON erp_categories.id = erp_products.category_id
			JOIN erp_sale_items ON erp_sale_items.product_id = erp_products.id
			JOIN erp_sales ON erp_sales.id = erp_sale_items.sale_id
			WHERE erp_sale_items.warehouse_id = $id $where_biller $where_customer $where_between_date
			GROUP BY
				erp_categories.id");
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
	public function getChartValueById($id)
    {
        $q = $this->db->query("
SELECT
				accountcode,
				accountname,
				COALESCE (
					sum(
						amount
					),
					0
				) AS total_amount
			FROM
				erp_gl_charts
			LEFT JOIN erp_gl_trans ON erp_gl_trans.account_code = erp_gl_charts.accountcode
			WHERE
				erp_gl_charts.bank = 1 and erp_gl_trans.account_code= $id
			GROUP BY
				accountcode;");
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
	public function getChartDataProfit($biller_id = null, $year = null)
    {
	if($biller_id != null){
		$where_biller_id = "AND erp_gl_trans.biller_id = ".$biller_id;
	}else{
		$where_biller_id = "";
	}
	if($year != null){
		$where_year = "AND YEAR(erp_gl_trans.tran_date) = ".$year;
	}else{
		$where_year = "";
	}
        $myQuery = "SELECT
	I. MONTH,
	COALESCE (I.income, 0) AS income,
	COALESCE (C.cost, 0) AS cost,
	COALESCE (O.operation, 0) AS operation
FROM
	(
		SELECT
			date_format(tran_date, '%Y-%m') MONTH,
			erp_gl_trans.account_code,
			erp_gl_trans.sectionid,
			erp_gl_charts.accountname,
			erp_gl_charts.parent_acc,
			sum(erp_gl_trans.amount) AS income
		FROM
			erp_gl_trans
		INNER JOIN erp_gl_charts ON erp_gl_charts.accountcode = erp_gl_trans.account_code
		WHERE
			erp_gl_trans.tran_date >= date_sub(now(), INTERVAL 12 MONTH)
		AND erp_gl_trans.sectionid IN (40, 70) $where_biller_id $where_year
	
			GROUP BY date_format(tran_date, '%Y-%m'),
			erp_gl_trans.account_code
	) I
LEFT JOIN (
	SELECT
		date_format(tran_date, '%Y-%m') MONTH,
		erp_gl_trans.account_code,
		erp_gl_trans.sectionid,
		erp_gl_charts.accountname,
		erp_gl_charts.parent_acc,
		sum(erp_gl_trans.amount) AS cost
	FROM
		erp_gl_trans
	INNER JOIN erp_gl_charts ON erp_gl_charts.accountcode = erp_gl_trans.account_code
	WHERE
		erp_gl_trans.tran_date >= date_sub(now(), INTERVAL 12 MONTH)
	AND erp_gl_trans.sectionid IN (50) $where_biller_id $where_year

		GROUP BY date_format(tran_date, '%Y-%m'),
		erp_gl_trans.account_code
) C ON I. MONTH = C. MONTH
LEFT JOIN (
	SELECT
		date_format(tran_date, '%Y-%m') MONTH,
		erp_gl_trans.account_code,
		erp_gl_trans.sectionid,
		erp_gl_charts.accountname,
		erp_gl_charts.parent_acc,
		sum(erp_gl_trans.amount) AS operation
	FROM
		erp_gl_trans
	INNER JOIN erp_gl_charts ON erp_gl_charts.accountcode = erp_gl_trans.account_code
	WHERE
		erp_gl_trans.tran_date >= date_sub(now(), INTERVAL 12 MONTH)
	AND erp_gl_trans.sectionid IN (60,80,90) $where_biller_id $where_year
		GROUP BY date_format(tran_date, '%Y-%m'),
		erp_gl_trans.account_code
) O ON O. MONTH = I. MONTH
GROUP BY
	I. MONTH
ORDER BY
	I. MONTH";
        $q = $this->db->query($myQuery);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	// end chivorn
	
    public function getChartData()
    {
        $myQuery = "SELECT S.month,
        COALESCE(S.sales, 0) as sales,
        COALESCE( P.purchases, 0 ) as purchases,
        COALESCE(S.tax1, 0) as tax1,
        COALESCE(S.tax2, 0) as tax2,
        COALESCE( P.ptax, 0 ) as ptax
        FROM (  SELECT  date_format(date, '%Y-%m') Month,
                SUM(total) Sales,
                SUM(product_tax) tax1,
                SUM(order_tax) tax2
                FROM " . $this->db->dbprefix('sales') . "
                WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH )
                GROUP BY date_format(date, '%Y-%m')) S
            LEFT JOIN ( SELECT  date_format(date, '%Y-%m') Month,
                        SUM(product_tax) ptax,
                        SUM(order_tax) otax,
                        SUM(total) purchases
                        FROM " . $this->db->dbprefix('purchases') . "
                        GROUP BY date_format(date, '%Y-%m')) P
            ON S.Month = P.Month
            GROUP BY S.Month
            ORDER BY S.Month";
        $q = $this->db->query($myQuery);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
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
	public function getAllSaleIemsWarehouses()
    {
        $this->db
			->select('warehouses.code')
            ->from('warehouses')
			->join('sale_items', 'warehouses.id = sale_items.warehouse_id')
			->group_by('sale_items.warehouse_id');
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getAllCompanies($group_name) {
        $q = $this->db->get_where('companies', array('group_name' => $group_name));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	 public function getAllCharts()
    {
        $q = $this->db->get('gl_charts');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllCustomers()
    {
        $q = $this->db->get('customers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllBillers()
    {
        $q = $this->db->get('billers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllSuppliers()
    {
        $q = $this->db->get('suppliers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function getDailySales($year, $month)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, 
		SUM( COALESCE( product_tax, 0 ) ) AS tax1, 
		SUM( COALESCE( order_tax, 0 ) ) AS tax2, 
		SUM( COALESCE( grand_total, 0 ) ) AS total, 
		SUM( COALESCE( order_discount, 0 ) ) AS order_discount,
		SUM( COALESCE( shipping, 0 ) ) AS shipping,
		SUM( COALESCE( total_cost, 0 ) ) AS total_cost,
		SUM( COALESCE( total, 0 ) ) AS no_total
			FROM " . $this->db->dbprefix('sales') . "
			WHERE DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
			GROUP BY DATE_FORMAT( date,  '%e' )";
		
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
    public function getStaffDailySales($user_id, $year, $month)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, 
		SUM( COALESCE( product_tax, 0 ) ) AS tax1, 
		SUM( COALESCE( order_tax, 0 ) ) AS tax2, 
		SUM( COALESCE( grand_total, 0 ) ) AS total, 
		SUM( COALESCE( order_discount, 0 ) ) AS order_discount,
		SUM( COALESCE( shipping, 0 ) ) AS shipping,
		SUM( COALESCE( total_cost, 0 ) ) AS total_cost,
		SUM( COALESCE( total, 0 ) ) AS no_total
            FROM " . $this->db->dbprefix('sales') . "
            WHERE created_by = {$user_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getReturnMonthlySales($year){
		$this->db->select("DATE_FORMAT(date,'%c') as date ,SUM(COALESCE(erp_return_sales.grand_total,0)) as return_total,SUM(COALESCE(total_cost,0)) as total_cost")
		->where("DATE_FORMAT(date,'%Y')",$year)
		->group_by("DATE_FORMAT(date,'%c')")
		->order_by("DATE_FORMAT(date,'%c') ASC");
		$q = $this->db->get("erp_return_sales");
		if ($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
    public function getMonthlySales($year)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( total_cost, 0 ) ) AS total_cost, SUM( COALESCE( total, 0 ) ) AS total, SUM( COALESCE( order_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
		FROM " . $this->db->dbprefix('sales') . "  
		WHERE DATE_FORMAT( date,  '%Y' ) =  '{$year}'
		GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getRoomDailySales($room_id, $year, $month)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . "
            WHERE suspend_note = {$room_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getStaffDailySaleman($user_id, $year, $month)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . "
            WHERE (CASE WHEN saleman_by <> '' THEN saleman_by ELSE created_by END) = {$user_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
    public function getRoomMonthlySales($room_id, $year)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . "
            WHERE suspend_note = {$room_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getStaffMonthlySaleman($user_id, $year)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date,SUM(erp_sale_items.quantity) as qty, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . " 
			INNER JOIN " . $this->db->dbprefix('sale_items') . " ON " . $this->db->dbprefix('sales') . ".id = " . $this->db->dbprefix('sale_items') . ".sale_id
            WHERE (CASE WHEN saleman_by <> '' THEN saleman_by ELSE created_by END) = {$user_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStaffMonthlySales($user_id, $year)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . " 
            WHERE created_by = {$user_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchasesTotals($supplier_id)
    {
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('supplier_id', $supplier_id);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSupplierPurchases($supplier_id)
    {
        $this->db->from('purchases')->where('supplier_id', $supplier_id);
        return $this->db->count_all_results();
    }


    public function getRoomPurchases($room_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('suspend_note', $room_id);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getStaffPurchases($user_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('created_by', $user_id);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getStaffSales($user_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('created_by', $user_id);
        $q = $this->db->get('saless');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getStaffSaleman($user_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('saleman_by', $user_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getRoomSales($room_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', FALSE)
            ->where('suspend_note', $room_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getTotalSales($start, $end, $biller_id = NULL)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax', FALSE)
            ->where('sale_status !=', 'pending')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
			if($biller_id != NULL){
				$this->db->where('biller_id', $biller_id);
			}
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalPurchases($start, $end, $biller_id = NULL)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax', FALSE)
            ->where('status', 'received')
			->where('date BETWEEN ' . $start . ' and ' . $end);
			if($biller_id != NULL){
				$this->db->where('biller_id', $biller_id);
			}
        $q = $this->db->get('purchases');
		
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalExpenses($start, $end, $biller_id = NULL)
    {
        $this->db->select('count(id) as total, sum(COALESCE(amount, 0)) as total_amount', FALSE)
            ->where('date BETWEEN ' . $start . ' and ' . $end);
			if($biller_id != NULL){
				$this->db->where('biller_id', $biller_id);
			}
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalPaidAmount($start, $end, $biller_id = NULL)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', FALSE)
            ->where('type', 'sent')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
			if($biller_id != NULL){
				$this->db->where('biller_id', $biller_id);
			}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalReceivedAmount($start, $end, $biller_id = NULL)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', FALSE)
            ->where('type', 'received')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
			if($biller_id != NULL){
				$this->db->where('biller_id', $biller_id);
			}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalReceivedCashAmount($start, $end, $biller_id = NULL)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', FALSE)
            ->where('type', 'received')->where('paid_by', 'cash')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
			if($biller_id != NULL){
				$this->db->where('biller_id', $biller_id);
			}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalReceivedCCAmount($start, $end,$biller_id = NULL)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', FALSE)
            ->where('type', 'received')->where('paid_by', 'CC')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
			if($biller_id != NULL){
				$this->db->where('biller_id', $biller_id);
			}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalReceivedChequeAmount($start, $end, $biller_id = NULL)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', FALSE)
            ->where('type', 'received')->where('paid_by', 'Cheque')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
			if($biller_id != NULL){
				$this->db->where('biller_id', $biller_id);
			}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalReceivedPPPAmount($start, $end, $biller_id = NULL)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', FALSE)
            ->where('type', 'received')->where('paid_by', 'ppp')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
			if($biller_id != NULL){
				$this->db->where('biller_id', $biller_id);
			}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalReceivedStripeAmount($start, $end, $biller_id = NULL)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', FALSE)
            ->where('type', 'received')->where('paid_by', 'stripe')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
			if($biller_id != NULL){
				$this->db->where('biller_id', $biller_id);
			}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTotalReturnedAmount($start, $end, $biller_id = NULL)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', FALSE)
            ->where('type', 'returned')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
			if($biller_id != NULL){
				$this->db->where('biller_id', $biller_id);
			}
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getWarehouseTotals($warehouse_id = NULL)
    {
        $this->db->select('sum(quantity) as total_quantity, count(id) as total_items', FALSE);
        $this->db->where('quantity !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('warehouses_products');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getDailySaleRevenues($date)
    {
        $myQuery = "SELECT 
						SUM( COALESCE( total_items, 0 ) ) AS total_items,
                        SUM( COALESCE( grand_total, 0 ) ) AS total,
						SUM( COALESCE( total, 0 ) ) AS no_total,
                        SUM( COALESCE( total_discount, 0 ) ) AS discount
			FROM " . $this->db->dbprefix('sales') . "
			WHERE DATE_FORMAT( date,  '%Y-%m-%d' ) =  '{$date}'
			GROUP BY DATE_FORMAT( date,  '%e' )";
			
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCosting($date)
    {
        $this->db->select(' SUM( COALESCE( total_cost, 0 ) ) AS cost, 
							SUM( COALESCE( grand_total, 0 ) ) AS sales, 
							SUM( total_tax + shipping + total_cost ) AS net_cost, 
							SUM( total_tax + shipping + grand_total ) AS net_sales') 
				 ->where("date >=", $date.' 00:00:00')
				 ->where("date <=", $date.' 23:55:00');
			
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
	public function getPurchaseing($date)
    {
        $this->db->select("date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status")
			->where("date >=", $date.' 00:00:00')
			->where("date <=", $date.' 23:55:00');

        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
	
	public function getMonthCosting($date, $warehouse_id = NULL, $year = NULL, $month = NULL)
    {
        $this->db->select('SUM( COALESCE( total_cost, 0 ) ) AS cost, SUM( COALESCE( grand_total, 0 ) ) AS sales, SUM( total_tax + shipping + total_cost ) AS net_cost, SUM( total_tax + shipping + grand_total ) AS net_sales', FALSE);
		
		if($date) {
            $this->db->where('sales.date', $date);
        }elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('sales.date >=', $year.'-'.$month.'-01 00:00:00');
            $this->db->where('sales.date <=', $year.'-'.$month.'-'.$last_day.' 23:59:59');
        }

        if ($warehouse_id) {
            //$this->db->join('sales', 'sales.id=costing.sale_id')
            $this->db->where('sales.warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
	public function getMonthPurchaseing($date, $warehouse_id = NULL, $year = NULL, $month = NULL)
    {
        $this->db->select("date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status");
		
		if($date) {
            $this->db->where('purchases.date', $date);
        }elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('purchases.date >=', $year.'-'.$month.'-01 00:00:00');
            $this->db->where('purchases.date <=', $year.'-'.$month.'-'.$last_day.' 23:59:59');
        }
		
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
	
	public function getOrderDiscount($date, $warehouse_id = NULL, $year = NULL, $month = NULL)
    {
        $sdate = $date.' 00:00:00';
        $edate = $date.' 23:59:59';
        $this->db->select('SUM( COALESCE( order_discount, 0 ) ) AS order_discount', FALSE);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year.'-'.$month.'-01 00:00:00');
            $this->db->where('date <=', $year.'-'.$month.'-'.$last_day.' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    } 
	
   
    public function getExpenses($date)
    {
        $sdate = $date.' 00:00:00';
        $edate = $date.' 23:59:59';
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', FALSE)
        ->where('date >=', $sdate)->where('date <=', $edate);

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
	public function getExpense($date, $warehouse_id = NULL, $year = NULL, $month = NULL)
    {
        $sdate = $date.' 00:00:00';
        $edate = $date.' 23:59:59';
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', FALSE);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year.'-'.$month.'-01 00:00:00');
            $this->db->where('date <=', $year.'-'.$month.'-'.$last_day.' 23:59:59');
        }
        

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	
	public function getReturns($date, $warehouse_id = NULL, $year = NULL, $month = NULL)
    {
        $sdate = $date.' 00:00:00';
        $edate = $date.' 23:59:59';
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', FALSE)
        ->where('sale_status', 'returned');
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year.'-'.$month.'-01 00:00:00');
            $this->db->where('date <=', $year.'-'.$month.'-'.$last_day.' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
	public function getSaleDetail($product_code)
    {
        $this->db->order_by('sale_items.id', 'asc');
		$this->db->join('sales', 'sales.id = sale_items.sale_id', 'left');
        $q = $this->db->get_where('sale_items', array('product_code' => $product_code));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getPurchaseDetail($product_code)
    {
		$this->db->select('*');
		$this->db->from('purchase_items');
		$this->db->join('purchases', 'purchase_items.purchase_id = purchases.id');
		$this->db->where('purchase_items.product_code', $product_code);
		$this->db->where('purchase_items.status <>', 'ordered');
        //$this->db->order_by('id', 'asc');
		$q = $this->db->get();
        //$q = $this->db->get_where('purchase_items', array('product_code' => $product_code));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getPurchaseDetailSupplier($product_code, $supplier_id)
    {	
		$this->db->select('*');
		$this->db->from('purchase_items');
		$this->db->join('purchases', 'purchase_items.purchase_id = purchases.id');
		$this->db->where('purchase_items.product_code', $product_code);
		$this->db->where('purchases.supplier_id', $supplier_id);
		$this->db->where('purchase_items.status <>', 'ordered');
        //$this->db->order_by('id', 'asc');
		$q = $this->db->get();
        //$q = $this->db->get_where('purchase_items', array('product_code' => $product_code));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	public function getReturnTotalCostD($year, $month){
		 $this->db->select("DATE_FORMAT(date,'%e')as date,COUNT(*) as total_items_return,SUM(COALESCE(total_cost,0)) as total_cost")
		 ->where("DATE_FORMAT(date,'%y-%m')='{$year}-{$month}'")
		 ->group_by("DATE_FORMAT('date','%e')");
		 $q = $this->db->get('return_sales');
		 if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	public function getReturnTotalCost($date){
		 $this->db->select("COUNT(*) as total_items_return,SUM(COALESCE(total_cost,0)) as total_cost")
		 ->where("date >=", $date.' 00:00:00')
		 ->where("date <=", $date.' 23:55:00');
		 $q = $this->db->get('return_sales');
		 if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	public function getSalesReturnDate($date)
    {
		$this->db->select("SUM(COALESCE( ABS({$this->db->dbprefix('return_sales')}.grand_total), 0 ) ) AS paid, (SELECT SUM(quantity) FROM erp_return_items LEFT JOIN erp_return_sales ON erp_return_items.return_id = erp_return_sales.id WHERE DATE(erp_return_sales.date) = '$date') AS quantity, SUM(( COALESCE( {$this->db->dbprefix('sales')}.order_discount, 0 ) ) ) AS order_discount", FALSE)
			->join('return_sales', 'sales.return_id = return_sales.id', 'right')
			->where("DATE({$this->db->dbprefix('return_sales')}.date) >= ", $date.' 00:00:00')
			->where("DATE({$this->db->dbprefix('return_sales')}.date) <= ", $date.' 23:55:00');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	public function getTotalDiscountDate($date)
    {
		$this->db->select('SUM( COALESCE( total_discount, 0 ) ) AS discount,SUM( COALESCE( order_discount, 0 ) ) AS order_discount', FALSE)
        ->where('DATE(date)', $date);

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	public function Count_Sale_discount($date){
		
		$myQuery = "SELECT count( ( COALESCE( id, 0 ) ) ) AS count_id
				FROM erp_sales 
				WHERE DATE_FORMAT( date,  '%Y-%m-%d' ) =  '{$date}' and order_discount!=''";
			
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	public function getTotalCosts($start, $end, $biller_id = NULL)
    {
        $this->db->select('SUM( COALESCE( purchase_unit_cost, 0 ) * quantity ) AS cost', FALSE)
        ->where('date BETWEEN ' . $start . ' and ' . $end);
        $q = $this->db->get('costing');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	public function getDailyPurchases($year, $month, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . " WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
/*
    public function getmonthlyPurchases()
    {
        $myQuery = "SELECT (CASE WHEN date_format( date, '%b' ) Is Null THEN 0 ELSE date_format( date, '%b' ) END) as month, SUM( COALESCE( total, 0 ) ) AS purchases FROM purchases WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH ) GROUP BY date_format( date, '%b' ) ORDER BY date_format( date, '%m' ) ASC";
        $q = $this->db->query($myQuery);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
*/
    public function getMonthlyPurchases($year, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . " WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStaffDailyPurchases($user_id, $year, $month, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases')." WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStaffMonthlyPurchases($user_id, $year, $warehouse_id = NULL)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . " WHERE ";
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getBiilerByUserID(){
		$q = $this->db->get_where("users",array("id"=>$this->session->userdata('user_id')),1);
		if ($q->num_rows() > 0) {
            return $q->row()->biller_id;
        }
        return FALSE;
	}
	public function getAllProducts(){
		$q = $this->db->get_where('products');
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

    public function getProducts($brand_id, $category_id, $per_page, $offset){
        if($brand_id){
            $this->db->where('brand_id', $brand_id);
        }
        if($category_id){
            $this->db->where('category_id', $category_id);
        }
        $q = $this->db->get_where('products', array('type <>'=> 'combo'), $per_page, $offset);
        if($q->num_rows()>0){
            return $q->result_array();
        }
        return false;
    }

	public function getAllProductsDetailsNUM($pid,$cid){
		$this->db->select("products.*,units.name as uname");
		$this->db->join("units","units.id=products.unit","LEFT");
		if($pid){
			$this->db->where("products.id",$pid);
		}
		if($cid){
			$this->db->where("category_id",$cid);
		}
		
		$q = $this->db->get('products');
		if($q->num_rows()>0){
			return $q->num_rows();
		}
		return false;
	}
	public function getWareFull(){
		$q = $this->db->get("erp_warehouses");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getAllProductsDetails($pid,$cid,$per_page,$ob_set){
		$this->db->select("products.*,units.name as uname");
		$this->db->join("units","units.id=products.unit","LEFT");
		if($pid){
			$this->db->where("products.id",$pid);
		}
		if($cid){
			$this->db->where("category_id",$cid);
		}
		$this->db->where('erp_products.type <> "combo" ');
		$this->db->limit($per_page,$ob_set); 
		$q = $this->db->get('products');
		if($q->num_rows()>0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getReportW($product = NULL, $category = NULL, $supplier = NULL, $start_date = NULL, $end_date = NULL){
		$where_purchase = "where 1=1 AND {$this->db->dbprefix('purchase_items')}.status <> 'ordered' AND {$this->db->dbprefix('purchase_items')}.purchase_id != ''";
		$where_sale='where 1=1';
		if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $end_date ? $this->erp->fld($end_date) : date('Y-m-d');

            $pp = "( SELECT pi.product_id, 
						SUM( pi.quantity * (CASE WHEN pi.option_id <> 0 THEN pi.vqty_unit ELSE 1 END) ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM((CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
                        SUM(pi.unit_cost) AS totalCost,
						SUM(pi.quantity) AS Pquantity,
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity 
									FROM erp_purchase_items 
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									WHERE {$this->db->dbprefix('purchase_items')}.date >= '{$start_date}' AND {$this->db->dbprefix('purchase_items')}.date < '{$end_date}' 
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id 
						GROUP BY pi.product_id ) PCosts";

			$sp = "( SELECT si.product_id, 
						SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)) soldQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						WHERE s.date >= '{$start_date}' AND s.date < '{$end_date}' 
						GROUP BY si.product_id ) PSales";

			$ppb = "( SELECT pi.product_id, 
						SUM( pi.quantity ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity 
									FROM erp_purchase_items 
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									WHERE {$this->db->dbprefix('purchase_items')}.date < '{$start_date}'
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCostsBegin";
            
			$spb = "( SELECT si.product_id, 
						SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)) saleQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						WHERE s.date < '{$start_date}'
						GROUP BY si.product_id ) PSalesBegin";
        } 
		else {
			$current_date = date('Y-m-d');
			$prevouse_date = date('Y').'-'.date('m').'-'.'01';
			$pp = "( SELECT pi.product_id, 
						SUM( pi.quantity * (CASE WHEN pi.option_id <> 0 THEN pi.vqty_unit ELSE 1 END) ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
                        SUM(pi.unit_cost) AS totalCost,
						SUM(pi.quantity) AS Pquantity,
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost ,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity
									FROM {$this->db->dbprefix('purchase_items')} 
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									".$where_purchase." 
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 			
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} GROUP BY product_id 
									) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCosts";

			$sp = "( SELECT si.product_id, 
						COALESCE(SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)),0) soldQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						".$where_sale."
						GROUP BY si.product_id ) PSales";

			
			$ppb = "( SELECT pi.product_id, 
						SUM(pi.quantity) AS purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) * tpi.quantity_balance ) balacneValue, 
						SUM(pi.unit_cost * pi.quantity) totalPurchase, 
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost ,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity
									FROM {$this->db->dbprefix('purchase_items')} 
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									".$where_purchase." 
									AND {$this->db->dbprefix('purchase_items')}.date < '{$prevouse_date}' 
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 			
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCostsBegin";
			
            $spb = "( SELECT si.product_id, 
						COALESCE(SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)),0) saleQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						".$where_sale."
						AND s.date < '{$prevouse_date}'
						GROUP BY si.product_id ) PSalesBegin";
			
        }
						
		$this->db->select($this->db->dbprefix('products') . ".id as product_id, 
				" . $this->db->dbprefix('products') . ".code as product_code, 
				" . $this->db->dbprefix('products') . ".name,
				COALESCE( PCostsBegin.purchasedQty-PSalesBegin.saleQty, 0 ) as BeginPS,
				CONCAT(COALESCE (PCosts.Pquantity, 0)) AS purchased,
				COALESCE( PSales.Squantity, 0 ) + COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
                                ".$this->db->dbprefix('combo_items') . " ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    ) as sold,
				COALESCE (COALESCE (
						PCostsBegin.purchasedQty-PSalesBegin.saleQty,
						0
					)+COALESCE (PCosts.Pquantity, 0) - COALESCE( PSales.Squantity , 0 ) -  COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
								".$this->db->dbprefix('combo_items') . " ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    ) ) AS balance", 
				FALSE)
				 ->from('products')
				 ->join($sp, 'products.id = PSales.product_id', 'left')
				 ->join($pp, 'products.id = PCosts.product_id', 'left')
				 ->join($spb, 'products.id = PSalesBegin.product_id', 'left')
                 ->join($ppb, 'products.id = PCostsBegin.product_id', 'left')
				 ->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				 ->join('categories', 'products.category_id=categories.id', 'left')
				 ->group_by("products.id");
		if($product){
			$this->db->where($this->db->dbprefix('products') . ".id", $product);
		}
		if ($category) {
			$this->db->where($this->db->dbprefix('products') . ".category_id", $category);
		}
		if ($supplier) {
			$this->db->where("products.supplier1 = '".$supplier."' or products.supplier2 = '".$supplier."' or products.supplier3 = '".$supplier."' or products.supplier4 = '".$supplier."' or products.supplier5 = '".$supplier."'");
		}
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;		
	}
	
	public function getInOutByID($id){
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
        if ($this->input->get('in_out')) {
            $in_out = $this->input->get('in_out');
        } else {
            $in_out = NULL;
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
		if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
		if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
			$where_sale='where si.warehouse_id='.$warehouse;
			$where_purchase="where {$this->db->dbprefix('purchase_items')}.warehouse_id=".$warehouse . "AND {$this->db->dbprefix('purchase_items')}.status <> 'ordered'";
        } else {
            $warehouse = NULL;
			$where_purchase = "where 1=1 AND {$this->db->dbprefix('purchase_items')}.status <> 'ordered' AND {$this->db->dbprefix('purchase_items')}.purchase_id != ''";
			//$where_purchase = "where 1=1 AND {$this->db->dbprefix('purchase_items')}.status <> 'ordered'";
			$where_sale='where 1=1';
        }
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $end_date ? $this->erp->fld($end_date) : date('Y-m-d');

            $pp = "( SELECT pi.product_id, 
						SUM( pi.quantity * (CASE WHEN pi.option_id <> 0 THEN pi.vqty_unit ELSE 1 END) ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM((CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
                        SUM(pi.unit_cost) AS totalCost,
						SUM(pi.quantity) AS Pquantity,
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity 
									FROM erp_purchase_items 
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									WHERE {$this->db->dbprefix('purchase_items')}.date >= '{$start_date}' AND {$this->db->dbprefix('purchase_items')}.date < '{$end_date}' 
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id 
						GROUP BY pi.product_id ) PCosts";

			$sp = "( SELECT si.product_id, 
						SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)) soldQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						WHERE s.date >= '{$start_date}' AND s.date < '{$end_date}' 
						GROUP BY si.product_id ) PSales";

			$ppb = "( SELECT pi.product_id, 
						SUM( pi.quantity ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity 
									FROM erp_purchase_items 
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									WHERE {$this->db->dbprefix('purchase_items')}.date < '{$start_date}'
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCostsBegin";
            
			$spb = "( SELECT si.product_id, 
						SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)) saleQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						WHERE s.date < '{$start_date}'
						GROUP BY si.product_id ) PSalesBegin";
        } else {
			$current_date = date('Y-m-d');
			$prevouse_date = date('Y').'-'.date('m').'-'.'01';
            //$pp = "( SELECT pi.product_id, SUM( pi.quantity ) purchasedQty, SUM( tpi.quantity_balance ) balacneQty, SUM( pi.unit_cost * tpi.quantity_balance ) balacneValue, SUM( pi.unit_cost * pi.quantity ) totalPurchase, pi.date as pdate from ( SELECT p.date as date, product_id, purchase_id, SUM(quantity) as quantity, unit_cost from erp_purchase_items JOIN {$this->db->dbprefix('purchases')} p on p.id = {$this->db->dbprefix('purchase_items')}.purchase_id GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi LEFT JOIN ( SELECT product_id, SUM(quantity_balance) as quantity_balance from {$this->db->dbprefix('purchase_items')} GROUP BY product_id ) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCosts";
            //$sp = "( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale, s.date as sdate from " . $this->db->dbprefix('sales') . " s JOIN " . $this->db->dbprefix('sale_items') . " si on s.id = si.sale_id GROUP BY si.product_id ) PSales";
			$pp = "( SELECT pi.product_id, 
						SUM( pi.quantity * (CASE WHEN pi.option_id <> 0 THEN pi.vqty_unit ELSE 1 END) ) purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) *  tpi.quantity_balance ) balacneValue, 
						SUM( pi.unit_cost * pi.quantity ) totalPurchase, 
                        SUM(pi.unit_cost) AS totalCost,
						SUM(pi.quantity) AS Pquantity,
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost ,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity
									FROM {$this->db->dbprefix('purchase_items')} 
									LEFT JOIN " . $this->db->dbprefix('purchases') . " pp 
									ON pp.id = {$this->db->dbprefix('purchase_items')}.purchase_id  
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									".$where_purchase." 
									".$where_p_biller."
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 			
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} GROUP BY product_id 
									) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCosts";

			$sp = "( SELECT si.product_id, 
						COALESCE(SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)),0) soldQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						".$where_sale."
						".$where_s_biller."
						GROUP BY si.product_id ) PSales";

			
			$ppb = "( SELECT pi.product_id, 
						SUM(pi.quantity) AS purchasedQty, 
						SUM( tpi.quantity_balance ) balacneQty, 
						SUM( (CASE WHEN pi.option_id <> 0 THEN pi.vcost ELSE pi.unit_cost END) * tpi.quantity_balance ) balacneValue, 
						SUM(pi.unit_cost * pi.quantity) totalPurchase, 
						pi.date as pdate 
						FROM ( SELECT {$this->db->dbprefix('purchase_items')}.date as date, 
									{$this->db->dbprefix('purchase_items')}.product_id, 
									purchase_id, 
									SUM({$this->db->dbprefix('purchase_items')}.quantity) as quantity, 
									unit_cost ,
									option_id,
									ppv.qty_unit AS vqty_unit,
									ppv.cost AS vcost,
									ppv.quantity AS vquantity
									FROM {$this->db->dbprefix('purchase_items')} 
									LEFT JOIN " . $this->db->dbprefix('purchases') . " pp 
									ON pp.id={$this->db->dbprefix('purchase_items')}.purchase_id  
									JOIN {$this->db->dbprefix('products')} p 
									ON p.id = {$this->db->dbprefix('purchase_items')}.product_id 
									LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
									ON ppv.id={$this->db->dbprefix('purchase_items')}.option_id  
									".$where_purchase." 
									".$where_p_biller."
									AND {$this->db->dbprefix('purchase_items')}.date < '{$prevouse_date}' 
									GROUP BY {$this->db->dbprefix('purchase_items')}.product_id ) pi 			
						LEFT JOIN ( SELECT product_id, 
										SUM(quantity_balance) as quantity_balance 
										FROM {$this->db->dbprefix('purchase_items')} 
										GROUP BY product_id ) tpi on tpi.product_id = pi.product_id GROUP BY pi.product_id ) PCostsBegin";
			
            $spb = "( SELECT si.product_id, 
						COALESCE(SUM( si.quantity*(CASE WHEN si.option_id <> 0 THEN spv.qty_unit ELSE 1 END)),0) saleQty, 
						SUM( si.subtotal ) totalSale, 
						SUM( si.quantity) AS Squantity,
						s.date as sdate
						FROM " . $this->db->dbprefix('sales') . " s 
						JOIN " . $this->db->dbprefix('sale_items') . " si 
						ON s.id = si.sale_id 
						LEFT JOIN " . $this->db->dbprefix('product_variants') . " spv 
						ON spv.id=si.option_id
						".$where_sale."
						".$where_s_biller."
						AND s.date < '{$prevouse_date}'
						GROUP BY si.product_id ) PSalesBegin";
        }
			$year = date('Y');
			$month = date('m');
			$YMD = $this->site->months($year, $month);
			if($YMD->date == ""){
				$LYMD = '0000-00-00';
			}else{
				$LYMD = $YMD->date;
			}
		$this->db->select($this->db->dbprefix('products') . ".id as product_id, 
				" . $this->db->dbprefix('products') . ".code as product_code, 
				" . $this->db->dbprefix('products') . ".name,
				COALESCE ((
					SELECT 
						SUM(
							" . $this->db->dbprefix('purchase_items') . ".quantity_balance
						) AS quantity
					FROM
						". $this->db->dbprefix('purchase_items') ."
					JOIN " . $this->db->dbprefix('products') . "  p ON p.id = " . $this->db->dbprefix('purchase_items') . ".product_id
					LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv ON ppv.id =" . $this->db->dbprefix('purchase_items') . ".option_id 
					WHERE DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m-%d') = '".$LYMD."'
					AND  (p.id) = ".$id."
					AND " . $this->db->dbprefix('purchase_items') . ".status <> 'ordered'
					GROUP BY
						DATE_FORMAT(" . $this->db->dbprefix('purchase_items') . ".date, '%Y-%m'),
						erp_products.id
				), 0 ) as BeginPS,
				COALESCE (" . $this->db->dbprefix('products') . ".quantity, 0) - COALESCE (PCosts.Pquantity, 0) + COALESCE( PSales.Squantity, 0 )
					+ COALESCE (PCosts.Pquantity, 0) AS purchased,
				COALESCE( PSales.Squantity, 0 ) + COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
                                ".$this->db->dbprefix('combo_items') . " ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    ) as sold,
				(COALESCE (" . $this->db->dbprefix('products') . ".quantity, 0) - COALESCE (PCosts.Pquantity, 0) + COALESCE( PSales.Squantity, 0 )
					+ COALESCE (PCosts.Pquantity, 0)) - COALESCE( PSales.Squantity, 0 ) + COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
                                ".$this->db->dbprefix('combo_items') . " ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                    )
					AS balance", 
				FALSE)
				 ->from('products')
				 ->join($sp, 'products.id = PSales.product_id', 'left')
				 ->join($pp, 'products.id = PCosts.product_id', 'left')
				 ->join($spb, 'products.id = PSalesBegin.product_id', 'left')
                 ->join($ppb, 'products.id = PCostsBegin.product_id', 'left')
				 ->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				 ->join('categories', 'products.category_id=categories.id', 'left')
				 ->where('products.id', $id)
				 ->group_by("products.id");
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;		
	}
	
	public function getRoomByID($id){
		$this->db
			->select("id,floor,name,ppl_number,description, CASE WHEN status = 0 THEN 'Active' ELSE 'Close' END AS status")
            ->from("erp_suspended")
			->where("id", $id);
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function getSalemanByID($id){
		$this->db
				->select('username, phone, sum(erp_sales.total) as sale_amount, sum(erp_sales.paid) as sale_paid, (sum(erp_sales.total) - sum(erp_sales.paid)) as balance')
				->from('users')
				->join('sales', 'sales.saleman_by = users.id')
				->where('users.id', $id);
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function getPurchasesByID($id){
		$this->db
				->select($this->db->dbprefix('purchases') . ".date, reference_no, " . $this->db->dbprefix('warehouses') . ".name as wname, supplier, GROUP_CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name SEPARATOR '___') as iname, GROUP_CONCAT(ROUND(" . $this->db->dbprefix('purchase_items') . ".quantity) SEPARATOR '___') as iqty, grand_total, paid, (grand_total-paid) as balance, " . $this->db->dbprefix('purchases') . ".status", FALSE)
				->from('purchases')
				->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
				->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
				->where('purchases.id', $id)
                ->group_by('purchases.id')
                ->order_by('purchases.date desc');
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function getPaymentsByID($id){
		$this->db
				->select($this->db->dbprefix('payments') . ".id as idd, ". $this->db->dbprefix('sales') . ".suspend_note as noted, ". $this->db->dbprefix('payments'). ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, " . $this->db->dbprefix('sales') . ".reference_no as sale_ref, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, " . $this->db->dbprefix('payments') . ".note,paid_by,amount, type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
				->where('payments.id', $id)
                ->group_by('payments.id');
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function getProjectsByID($id){
		$this->db
				->select($this->db->dbprefix('companies') . ".id as idd, company, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(" . $this->db->dbprefix('sales') . ".grand_total), 0) as total_amount, (COALESCE(sum(" . $this->db->dbprefix('sales') . ".grand_total), 0) * (" . $this->db->dbprefix('companies') . ".cf6/100)) as total_earned, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.biller_id=companies.id')
                ->where('companies.group_name', 'biller')
                ->group_by('companies.id');
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	public function getsubcategoires(){
		$this->db->select("subcategories.*");
		$this->db->from("subcategories");
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getSupplierByID($id){
		$this->db
				->select($this->db->dbprefix('companies') . ".id as idd, company, name, phone, email, count(" . $this->db->dbprefix('purchases') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('purchases', 'purchases.supplier_id=companies.id')
                ->where(array('companies.group_name'=> 'supplier', 'companies.id'=> $id))
                ->group_by('companies.id');
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function getCustomersByID($id){
		$this->db
				->select($this->db->dbprefix('companies') . ".id as idd, company, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.customer_id=companies.id')
                ->where('companies.id', $id)
                ->group_by('companies.id');
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function getProfitByID($id){
		$this->db
				->select("erp_sales.id, date, reference_no, suspend_note, biller, customer, grand_total, paid, (grand_total-paid) as balance,
				COALESCE (
					(
						SELECT
							SUM(cost * " . $this->db->dbprefix('sale_items') . ".quantity)
						FROM
							erp_sale_items
						INNER JOIN erp_products ON erp_products.id = erp_sale_items.product_id
						WHERE
							erp_sale_items.sale_id = erp_sales.id
					),
					0
				) AS total_cost,
				COALESCE (
					COALESCE (
						(
							grand_total
						),
						0
					) - COALESCE (
						(
							SELECT
								SUM(cost * " . $this->db->dbprefix('sale_items') . ".quantity)
							FROM
								" . $this->db->dbprefix('sale_items') . "
							INNER JOIN " . $this->db->dbprefix('products') . " ON " . $this->db->dbprefix('products') . ".id = " . $this->db->dbprefix('sale_items') . ".product_id
							WHERE
								" . $this->db->dbprefix('sale_items') . ".sale_id = " . $this->db->dbprefix('sales') . ".id
						),
						0
					)
				) AS profit, payment_status", FALSE)
				->from('sales')
				->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
				->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
				->join('companies', 'companies.id=sales.customer_id','left')                
				->join('customer_groups','customer_groups.id=companies.customer_group_id','left')
				->where('erp_sales.id', $id)
				->group_by('sales.id');
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function getSalesByID($id){
		$this->db
				->select("erp_sales.id, date, reference_no, biller, customer,GROUP_CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name SEPARATOR '___') as iname, GROUP_CONCAT(ROUND(".$this->db->dbprefix('sale_items') . ".quantity) SEPARATOR '___') as iqty, grand_total, paid, (grand_total-paid) as balance, payment_status", FALSE)
				->from('sales')
				->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
				->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
				->join('companies', 'companies.id=sales.customer_id','left')                
				->join('customer_groups','customer_groups.id=companies.customer_group_id','left')
				->where('erp_sales.id', $id)
				->group_by('sales.id');
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function getSalesExportByID($id){
		$this->db
				->select("erp_sales.id, date, reference_no, biller, customer,GROUP_CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name SEPARATOR '\n') as iname, GROUP_CONCAT(ROUND(".$this->db->dbprefix('sale_items') . ".quantity) SEPARATOR '\n') as iqty, grand_total, paid, (grand_total-paid) as balance, payment_status, SUM(".$this->db->dbprefix('sale_items') . ".quantity) as total_qty", FALSE)
				->from('sales')
				->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
				->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
				->join('companies', 'companies.id=sales.customer_id','left')                
				->join('customer_groups','customer_groups.id=companies.customer_group_id','left')
				->where('erp_sales.id', $id)
				->group_by('sales.id');
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}


	public function getCategoryByID($id){
		$pp = "( SELECT pp.category_id as category, pi.product_id, SUM( pi.quantity ) purchasedQty, SUM( pi.net_unit_cost * pi.quantity ) totalPurchase from " . $this->db->dbprefix('products') . " pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi on pp.id = pi.product_id 
                group by pp.category_id
                ) PCosts";
            $sp = "( SELECT sp.category_id as category, si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from " . $this->db->dbprefix('products') . " sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si on sp.id = si.product_id 
                group by sp.category_id 
                ) PSales";
				
		$this->db
                ->select($this->db->dbprefix('categories') . ".id as cidd, " .$this->db->dbprefix('categories') . ".code, " . $this->db->dbprefix('categories') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left')
				->where('categories.id', $id)
				->group_by('categories.id');
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function getWarehouseByID($id){
		$this->db->select('id, code, name, quantity');
		$this->db->from('products');
		$this->db->where('id', $id);
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;		
	}
	
	public function getProductByID($id){
		$pp = "( SELECT 
					pi.date as date, 
					pi.product_id, 
					pi.purchase_id, 
					COALESCE(SUM(CASE WHEN pi.purchase_id <> 0 THEN (pi.quantity*(CASE WHEN ppv.qty_unit <> 0 THEN ppv.qty_unit ELSE 1 END)) ELSE 0 END),0) as purchasedQty, 
					SUM(pi.quantity_balance) as balacneQty, 
					SUM((CASE WHEN pi.option_id <> 0 THEN ppv.cost ELSE pi.net_unit_cost END) * pi.quantity_balance ) balacneValue, 
					SUM( pi.unit_cost * (CASE WHEN pi.purchase_id <> 0 THEN pi.quantity ELSE 0 END) ) totalPurchase
					FROM {$this->db->dbprefix('purchase_items')} pi 
					LEFT JOIN {$this->db->dbprefix('purchases')} p 
					ON p.id = pi.purchase_id
					LEFT JOIN " . $this->db->dbprefix('product_variants') . " ppv 
					ON ppv.id=pi.option_id ".$where_purchase." 
					WHERE pi.status <> 'ordered'
					GROUP BY pi.product_id ) PCosts";
		$sp = "( SELECT 
					si.product_id, 
					SUM( si.quantity*(CASE WHEN pv.qty_unit <> 0 THEN pv.qty_unit ELSE 1 END)) soldQty, 
					SUM( si.subtotal ) totalSale, 
					s.date as sdate FROM " . $this->db->dbprefix('sales') . " s 
					INNER JOIN " . $this->db->dbprefix('sale_items') . " si 
					ON s.id = si.sale_id 
					LEFT JOIN " . $this->db->dbprefix('product_variants') . " pv 
					ON pv.id=si.option_id ".$where_sale." 
					GROUP BY si.product_id ) PSales";
		$this->db
                ->select($this->db->dbprefix('products') . ".id AS idd, " . $this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
				COALESCE( PCosts.purchasedQty, 0 ) AS qpurchase, COALESCE( PCosts.totalPurchase, 0 ) AS ppurchased,
				COALESCE (PSales.soldQty, 0) + COALESCE (
                        (
                            SELECT
                                SUM(si.quantity * ci.quantity)
                            FROM
                                erp_combo_items ci
                            INNER JOIN erp_sale_items si ON si.product_id = ci.product_id
                            WHERE
                                ci.item_code = ".$this->db->dbprefix('products') . ".code
                        ),
                        0
                ) AS qsale,
                COALESCE (PSales.totalSale, 0) AS psold,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit,
				COALESCE( PCosts.balacneQty, 0 ) as qbalance, COALESCE( PCosts.balacneValue, 0 ) as pbalance", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')				
				->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
				->join('categories', 'products.category_id=categories.id', 'left')
				->where('products.id', $id)
				->group_by("products.id");
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;	
	}

    function getQuantityByID($id){
        $this->db
             ->select('code, name, quantity, alert_quantity')
             ->from('products')
             ->where('alert_quantity > quantity', NULL)
             ->where(array('track_quantity'=> 1, 'products.id' => $id));
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;    
    }

    function getRegisterByID($id){
        $this->db
             ->select("date, closed_at, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, '<br>', " . $this->db->dbprefix('users') . ".email) as user, cash_in_hand, CONCAT(total_cc_slips, ' (', total_cc_slips_submitted, ')') as c_slips, CONCAT(total_cheques, ' (', total_cheques_submitted, ')') as cheques, CONCAT(total_cash, ' (', total_cash_submitted, ')') as cash, note", FALSE)
             ->from("pos_register")
             ->where("pos_register.id", $id)
             ->join('users', 'users.id=pos_register.user_id', 'left');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false; 
    }
}
