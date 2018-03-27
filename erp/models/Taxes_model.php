<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Taxes_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
	
	public function saveWithholdingdTax($WHT = array(), $WTR = array(), $WTNR = array(),$DWTR = array(),$DWTNR = array()) {
		if($WHT) {
			if ($this->db->insert('return_withholding_tax', $WHT)) {
				$return_id = $this->db->insert_id();
				foreach ($WTR as $WTRData) {
					$WTRData['withholding_id'] = $return_id;
					$this->db->insert('return_withholding_tax_front', $WTRData);
				}
				foreach ($WTNR as $WTNRData) {
					$WTNRData['withholding_id'] = $return_id;
					$this->db->insert('return_withholding_tax_front', $WTNRData);
				}
				foreach ($DWTR as $DWTRData) {
					$DWTRData['withholding_id'] = $return_id;
					$this->db->insert('return_withholding_tax_back', $DWTRData);
				}
				foreach ($DWTNR as $DWTNRData) {
					$DWTNRData['withholding_id'] = $return_id;
					$this->db->insert('return_withholding_tax_back', $DWTNRData);
				}
				return true;
			}
			return false;
		}
		return false;
	}
	
	
	public function saveValueAddedTax($VAT = array(), $SPNSM = array(), $GSE = array(),$GSLS = array()) {
		if($VAT) {
			if ($this->db->insert('return_value_added_tax', $VAT)) {
				$return_id = $this->db->insert_id();
				foreach ($SPNSM as $spData) {
					$spData['value_id'] = $return_id;
					$this->db->insert('return_value_added_tax_back', $spData);
				}
				foreach ($GSE as $gsData) {
					$gsData['value_id'] = $return_id;
					$this->db->insert('return_value_added_tax_back', $gsData);
				}
				foreach ($GSLS as $gslData) {
					$gslData['value_id'] = $return_id;
					$this->db->insert('return_value_added_tax_back', $gslData);
				}
				return true;
			}
			return false;
		}
		return false;
	}
	

	public function SelectEnterprise()
	{
		$q = $this->db->get_where('companies', array('group_id' => NULL));
         if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getAllUsers() {
		$q = $this->db->get('users');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
	}
	
	public function getEmployeeByID($id = NULL)
	{
		$this->db->select('users.id, users.username, users.first_name_kh, users.last_name_kh, users.nationality_kh, pack_lists.description');
        $this->db->join('pack_lists', 'pack_lists.id = users.pack_id', 'inner');
        $q = $this->db->get_where('users', array('users.id' => $id));
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addSalaryTax($salary_tax = array(),$RE = array(),$NRE = array(),$FB = array(),$REB = array(),$FBB = array())
	{
		$help = false;
		if($salary_tax) {
			if($this->db->insert('salary_tax', $salary_tax)) {
				$salary_tax_id = $this->db->insert_id();
				if($RE) {
					foreach($RE as $ore) {
						$ore['salary_tax_id'] = $salary_tax_id;
						if($this->db->insert('salary_tax_front', $ore)){
							$help = true;
						}
					}
				}
				if($NRE) {
					$NRE['salary_tax_id'] = $salary_tax_id;
					if($this->db->insert('salary_tax_front', $NRE)){
						$help = true;
					}
				}
				if($FB) {
					$FB['salary_tax_id'] = $salary_tax_id;
					if($this->db->insert('salary_tax_front', $FB)){
						$help = true;
					}
				}
				if($REB) {
					foreach($REB as $oreb) {
						$oreb['salary_tax_id'] = $salary_tax_id;
						if($this->db->insert('salary_tax_back', $oreb)){
							$help = true;
						}
					}
				}
				if($FBB) {
					foreach($FBB as $ofbb){
						$ofbb['salary_tax_id'] = $salary_tax_id;
						if($this->db->insert('salary_tax_back', $ofbb)){
							$help = true;
						}
					}
				}
				$help = true;
			}
		}
		if($help) {
			return true;
		}else{
			return false;
		}
	}
	
	public function editSalaryTax($id = NULL,$salary_tax = array(),$RE = array(),$NRE = array(),$FB = array(),$REB = array(),$FBB = array())
	{
		$help = false;
		if($salary_tax) {
			if($this->db->update('salary_tax', $salary_tax, array('id' => $id))) {
				$this->db->delete('salary_tax_front', array('salary_tax_id' => $id));
				$this->db->delete('salary_tax_back', array('salary_tax_id' => $id));
				if($RE) {
					foreach($RE as $ore) {
						$ore['salary_tax_id'] = $id;
						if($this->db->insert('salary_tax_front', $ore)){
							$help = true;
						}
					}
				}
				if($NRE) {
					$NRE['salary_tax_id'] = $id;
					if($this->db->insert('salary_tax_front', $NRE)){
						$help = true;
					}
				}
				if($FB) {
					$FB['salary_tax_id'] = $id;
					if($this->db->insert('salary_tax_front', $FB)){
						$help = true;
					}
				}
				if($REB) {
					foreach($REB as $oreb) {
						$oreb['salary_tax_id'] = $id;
						if($this->db->insert('salary_tax_back', $oreb)){
							$help = true;
						}
					}
				}
				if($FBB) {
					foreach($FBB as $ofbb){
						$ofbb['salary_tax_id'] = $id;
						if($this->db->insert('salary_tax_back', $ofbb)){
							$help = true;
						}
					}
				}
				$help = true;
			}
		}
		if($help) {
			return true;
		}else{
			return false;
		}
	}
		
	public function getEnterpriceByID($id = NULL)
	{
		$q = $this->db->get_where('companies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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
	
	public function SupplierList(){
		$q = $this->db->get_where('companies', array('group_name' => 'supplier'));
         if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function SelectEnterpriseId($id)
	{
		$q = $this->db->get_where('companies', array('group_id' => NULL,'id'=>$id));
         if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	
	public function getPurchaseTaxes($id)
    {
		$this->db->select('id, date, reference_no, (grand_total - order_tax) as amount, order_tax as amount_declear, supplier_id, warehouse_id, order_tax_id');
		$this->db->from('purchases');
		$this->db->where_in('id', $id);
		$this->db->order_by('date','desc');
        $q = $this->db->get();
         if ($q->num_rows() > 0) {
            return $q;
        }
		return FALSE;
    }
	
	public function getExchangeRate($id) {
		$this->db->select('rate');
		$this->db->from('currencies');
		$this->db->where('code', $id);
        $q = $this->db->get();
         if ($q->num_rows() > 0) {
            return $q->row();
        }
		return FALSE;
	}
	
	public function addPurchasingTax($data = array()) {
		if($this->db->insert('purchase_tax', $data)) {
			$this->db->update('purchases', array('reference_no_tax' => $data['reference_no'], 'tax_status' => 'confirmed'), array('id' => $data['purchase_id']));
			return true;
		}
		return false;
	}
	
	/*Add Sale Tax Model*/
	public function getCombineTaxById($id)
    {
		$this->db->select('id, date, reference_no, biller, customer,total_tax,warehouse_id,order_tax_id, sale_status,grand_total,(grand_total-total_tax) as balance');
		$this->db->from('sales');
		$this->db->where_in('id', $id);
        $q = $this->db->get();
         if ($q->num_rows() > 0) {
            return $q;
        }
		return FALSE;
    }
	
	 public function addTax($data = array())
    {
        if ($this->db->insert('sale_tax', $data)) {
			$this->db->update('sales', array('reference_no_tax' => $data['referent_no'],'tax_status'=>'confirmed'), array('id' => $data['sale_id']));
			
            return true;
        }
        return false;
    }
	/*End Add Sale Tax*/
	public function getConditionTax(){
		$this->db->where('id','1');
		$q=$this->db->get('condition_tax');
		return $q->result();
	}
	public function getConditionTaxById($id){
		$this->db->where('id',$id);
		$q=$this->db->get('condition_tax');
		return $q->row();
	}
	public function update_exchange_tax_rate($id,$data){
		$this->db->where('id',$id);
		$update=$this->db->update('condition_tax',$data);
		if($update){
			return true;
		}
	}
	public function addReturnTax($return_tax = array(), $SGP = array(), $SS = array()) {
		if($return_tax) {
			if ($this->db->insert('return_tax_front', $return_tax)) {
				$return_id = $this->db->insert_id();
				foreach ($SGP as $osgb) {
					$osgb['tax_return_id'] = $return_id;
					$this->db->insert('return_tax_back', $osgb);
				}
				foreach ($SS as $oss) {
					$oss['tax_return_id'] = $return_id;
					$this->db->insert('return_tax_back', $oss);
				}
				return true;
			}
			return false;
		}
		return false;
	}	
	
	public function getAccountSections(){
		$this->db->select("sectionid,sectionname");
		$section = $this->db->get("gl_sections");
		if($section->num_rows() > 0){
			return $section->result_array();	
		}
		return false;
	}
	
	public function addChartAccount($data){
		if ($this->db->insert('gl_charts_tax', $data)) {
            return true;
        }
        return false;
	}
	
	public function updateChartAccount($id,$data){
		//$this->erp->print_arrays($data);
		$this->db->where('accountcode', $id);
		$q=$this->db->update('gl_charts_tax', $data);
        if ($q) {
            return true;
        }
        return false;
	}
	
	public function deleteChartAccount($id){
		$q = $this->db->delete('gl_charts_tax', array('accountcode' => $id));
		if($q){
			return true;
		} else{
			return false;
		}
	}
	
	public function getChartAccountByID($id){
		$this->db->select('gl_charts_tax.accountcode,gl_charts_tax.accountname,gl_charts_tax.accountname_kh,gl_charts_tax.sectionid,gl_sections.sectionname');
		$this->db->from('gl_charts_tax');
		$this->db->join('gl_sections', 'gl_sections.sectionid=gl_charts_tax.sectionid','INNER');
		$this->db->where('gl_charts_tax.accountcode' , $id);
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
}
