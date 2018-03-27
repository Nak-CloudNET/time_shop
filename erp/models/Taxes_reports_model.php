<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Taxes_reports_model extends CI_Model
{	
	function getConfirmTax(){
	  $this->db->select("YEAR(erp_sale_tax.issuedate) AS yearly,journal_date,journal_location, MONTH(erp_sale_tax.issuedate) AS monthly,SUM(erp_sale_tax.amound) AS amount, SUM(erp_sale_tax.amound_tax) AS amount_tax, SUM(erp_sale_tax.amound_declare) AS amount_dec,erp_companies.company, erp_sale_tax.group_id", FALSE)
				 ->join('erp_companies', 'erp_sale_tax.group_id=erp_companies.id', 'INNER')
				 ->group_by('MONTH(erp_sale_tax.issuedate), erp_sale_tax.group_id');
				
	  $q =$this->db->get('erp_sale_tax');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
	}
	
	
	
	function getConfirmTax_purch(){
	 $this->db->select("companies.company,YEAR(erp_purchase_tax.issuedate) AS yearly,journal_date,journal_location, MONTH(erp_purchase_tax.issuedate) AS monthly,SUM(erp_purchase_tax.amount) AS amount, SUM(erp_purchase_tax.amount_tax) AS amount_tax, SUM(erp_purchase_tax.amount_declear) AS amount_dec,erp_purchase_tax.group_id", FALSE)
				 ->join('companies', 'erp_purchase_tax.group_id=companies.id')
				 ->group_by('MONTH(erp_purchase_tax.issuedate), erp_purchase_tax.group_id');
	  $q =$this->db->get('erp_purchase_tax');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
	}
	
	
	function update_journal_date($data= array()){
	 $update=$this->db->update('sale_tax', array('journal_date' => $data['date']),array('MONTH(issuedate)' => $data['month'],'YEAR(issuedate)' => $data['year']));
	 if ($update) {
            return true;
        }
        return false;	
	}
	
	function update_journal_date_pur($data= array()){
	 $update=$this->db->update('purchase_tax', array('journal_date' => $data['date']),array('MONTH(issuedate)' => $data['month'],'YEAR(issuedate)' => $data['year']));
	 if ($update) {
            return true;
        }
        return false;	
	}
	
	function update_journal_loc_pur($data= array()){
	 $update=$this->db->update('purchase_tax', array('journal_location' => $data['location']),array('MONTH(issuedate)' => $data['month'],'YEAR(issuedate)' => $data['year']));
	 if ($update) {
            return true;
        }
        return false;	
	}
	
	
	function update_journal_loc($data= array()){
	 $update=$this->db->update('sale_tax', array('journal_location' => $data['location']),array('MONTH(issuedate)' => $data['month'],'YEAR(issuedate)' => $data['year']));
	 if ($update) {
            return true;
        }
        return false;	
	}
	
	function company_info($group_id=NULL){
		$this->db->select("company,vat_no,address,state,country,cf1,cf2,cf3,cf4")
	    ->from('companies')
		->where('id',$group_id);
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	
	function v_sale_journal_list($month=NULL,$year=NULL,$group_id=NULL){
		
    $this->db->
	         select($this->db->dbprefix('sale_tax') . ".customer_id,
				" . $this->db->dbprefix('sale_tax') . ".referent_no,
				" . $this->db->dbprefix('sale_tax') . ".group_id,
				" . $this->db->dbprefix('sale_tax') . ".vatin,
				" . $this->db->dbprefix('sale_tax') . ".description,
				" . $this->db->dbprefix('sale_tax') . ".qty,
				" . $this->db->dbprefix('sale_tax') . ".sale_id,
				" . $this->db->dbprefix('sale_tax') . ".non_tax_sale,
				" . $this->db->dbprefix('sale_tax') . ".value_export,
				" . $this->db->dbprefix('sale_tax') . ".tax_value,
				" . $this->db->dbprefix('sale_tax') . ".vat,
				" . $this->db->dbprefix('sales') .".total_items,
				" . $this->db->dbprefix('sale_tax') . ".amound_declare, 
				" . $this->db->dbprefix('sale_tax') . ".amound,
				" . $this->db->dbprefix('sale_tax') . ".amound_tax,
				" . $this->db->dbprefix('sale_tax') . ".journal_date,
				" . $this->db->dbprefix('sale_tax') . ".journal_location,
				MONTH(" . $this->db->dbprefix('sale_tax') . ".issuedate) AS monthly,
				YEAR(" . $this->db->dbprefix('sale_tax') . ".issuedate) AS yearly,
				" . $this->db->dbprefix('sale_tax') . ".issuedate")
                ->from('sale_tax')
                ->join('sales', 'sale_tax.sale_id=sales.id','left')
				->where(array('MONTH ('. $this->db->dbprefix('sale_tax') .'.issuedate)='=>$month,'YEAR ('. $this->db->dbprefix('sale_tax') .'.issuedate)='=>$year,$this->db->dbprefix('sale_tax') .'.group_id='=>$group_id));
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
		
            return $q;
        }
        return FALSE;
	}
	
	function v_purch_journal_list($month=NULL,$year=NULL,$group_id=NULL) {
    $this->db->
	         select($this->db->dbprefix('companies') . ".name,
				" . $this->db->dbprefix('purchase_tax') . ".reference_no,
				" . $this->db->dbprefix('purchase_tax') . ".amount_declear, 
				" . $this->db->dbprefix('purchase_tax') . ".vatin,
				" . $this->db->dbprefix('purchase_tax') . ".description,
				" . $this->db->dbprefix('purchase_tax') . ".qty,
				" . $this->db->dbprefix('purchase_tax') . ".non_tax_pur,
				" . $this->db->dbprefix('purchase_tax') . ".vat,
				" . $this->db->dbprefix('purchase_tax') . ".tax_value,
				" . $this->db->dbprefix('purchase_tax') . ".amount,
				" . $this->db->dbprefix('purchase_tax') . ".amount_tax,
				" . $this->db->dbprefix('purchase_tax') . ".journal_date,
				" . $this->db->dbprefix('purchase_tax') . ".journal_location,
				MONTH(" . $this->db->dbprefix('purchase_tax') . ".issuedate) AS monthly,
				YEAR(" . $this->db->dbprefix('purchase_tax') . ".issuedate) AS yearly,
				" . $this->db->dbprefix('purchase_tax') . ".issuedate")
                ->from('purchase_tax')
                ->join('companies', 'purchase_tax.group_id=companies.id','left')
				->where(array('MONTH ('. $this->db->dbprefix('purchase_tax') .'.issuedate)='=>$month,'YEAR ('. $this->db->dbprefix('purchase_tax') .'.issuedate)='=>$year,$this->db->dbprefix('purchase_tax') .'.group_id='=>$group_id));
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q;
        }
        return FALSE;
	}
	
	public function getSalaryTaxList()
	{
		$this->db->select(
							$this->db->dbprefix('salary_tax').'.id, '.
							$this->db->dbprefix('salary_tax').'.group_id, '.
							$this->db->dbprefix('companies').'.company, '.
							$this->db->dbprefix('salary_tax').'.month, '.
							$this->db->dbprefix('salary_tax').'.year, '.
							$this->db->dbprefix('salary_tax').'.covreturn_start, '.
							$this->db->dbprefix('salary_tax').'.covreturn_end, '.
							$this->db->dbprefix('salary_tax').'.created_date '
						 );
		$this->db->join('companies',$this->db->dbprefix('salary_tax').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$this->db->group_by('salary_tax.id');
		$q = $this->db->get('salary_tax');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getSalaryTaxByID($id = NULL)
	{
		$this->db->select('companies.*,salary_tax.*');
		$this->db->join('companies',$this->db->dbprefix('salary_tax').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$q = $this->db->get_where('salary_tax',array($this->db->dbprefix('salary_tax').'.id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getSalaryTaxFrontByID($id=NULL, $type=NULL)
	{
		$q = $this->db->get_where('salary_tax_front',array($this->db->dbprefix('salary_tax_front').'.salary_tax_id'=>$id,$this->db->dbprefix('salary_tax_front').'.tax_type'=>$type));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getSalaryTaxBackByID($id=NULL, $type=NULL)
	{
		$this->db->select('salary_tax_back.*, users.first_name,users.last_name,users.username,users.nationality_kh');
		$this->db->join('users',$this->db->dbprefix('salary_tax_back').'.empcode = '.$this->db->dbprefix('users').'.id','INNER');
		$q = $this->db->get_where('salary_tax_back',array($this->db->dbprefix('salary_tax_back').'.salary_tax_id'=>$id,$this->db->dbprefix('salary_tax_back').'.tax_type'=>$type));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getWithholdingTaxList()
	{
		$this->db->select(
							$this->db->dbprefix('return_withholding_tax').'.id, '.
							$this->db->dbprefix('return_withholding_tax').'.group_id, '.
							$this->db->dbprefix('companies').'.company, '.
							$this->db->dbprefix('return_withholding_tax').'.month, '.
							$this->db->dbprefix('return_withholding_tax').'.year, '.
							$this->db->dbprefix('return_withholding_tax').'.covreturn_start, '.
							$this->db->dbprefix('return_withholding_tax').'.covreturn_end, '.
							$this->db->dbprefix('return_withholding_tax').'.created_date '
						 );
		$this->db->join('companies',$this->db->dbprefix('return_withholding_tax').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$this->db->group_by('return_withholding_tax.id');
		$q = $this->db->get('return_withholding_tax');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getWithholdingTaxFrontByID($id=NULL, $type=NULL)
	{
		$q = $this->db->get_where('return_withholding_tax_front',array($this->db->dbprefix('return_withholding_tax_front').'.withholding_id'=>$id,$this->db->dbprefix('return_withholding_tax_front').'.type'=>$type));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getWithholdingTaxBackByID($id=NULL, $type=NULL)
	{
		$q = $this->db->get_where('return_withholding_tax_back',array($this->db->dbprefix('return_withholding_tax_back').'.withholding_id'=>$id,$this->db->dbprefix('return_withholding_tax_back').'.type'=>$type));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getValueAddTaxList()
	{
		$this->db->select(
							$this->db->dbprefix('return_value_added_tax').'.id, '.
							$this->db->dbprefix('return_value_added_tax').'.group_id, '.
							$this->db->dbprefix('companies').'.company, '.
							$this->db->dbprefix('return_value_added_tax').'.month, '.
							$this->db->dbprefix('return_value_added_tax').'.year, '.
							$this->db->dbprefix('return_value_added_tax').'.covreturn_start, '.
							$this->db->dbprefix('return_value_added_tax').'.covreturn_end, '.
							$this->db->dbprefix('return_value_added_tax').'.created_date '
						 );
		$this->db->join('companies',$this->db->dbprefix('return_value_added_tax').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$this->db->group_by('return_value_added_tax.id');
		$q = $this->db->get('return_value_added_tax');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getInfoFrontPage($id=NULL)
	{
		$this->db->select('return_value_added_tax.*,companies.*');
		$this->db->join('companies',$this->db->dbprefix('return_value_added_tax').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$q = $this->db->get_where('return_value_added_tax',array($this->db->dbprefix('return_value_added_tax').'.id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getInfoFrontPageWHT($id=NULL)
	{
		$this->db->select('return_withholding_tax.*,companies.*');
		$this->db->join('companies',$this->db->dbprefix('return_withholding_tax').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$q = $this->db->get_where('return_withholding_tax',array($this->db->dbprefix('return_withholding_tax').'.id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	
	
	public function getInfoBackPage($id=NULL, $type=NULL)
	{
		$this->db->select('return_value_added_tax_back.*,products.name, companies.name as supp_name');
		$this->db->join('products',$this->db->dbprefix('return_value_added_tax_back').'.productid = '.$this->db->dbprefix('products').'.code','INNER');
		$this->db->join('companies',$this->db->dbprefix('return_value_added_tax_back').'.supp_exp_inn = '.$this->db->dbprefix('companies').'.id','left');
		$q = $this->db->get_where('return_value_added_tax_back',array($this->db->dbprefix('return_value_added_tax_back').'.value_id'=>$id,$this->db->dbprefix('return_value_added_tax_back').'.type'=>$type));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	function getReturnTaxList()
	{
		$this->db->select(
							$this->db->dbprefix('return_tax_front').'.id, '.
							$this->db->dbprefix('return_tax_front').'.group_id, '.
							$this->db->dbprefix('companies').'.company, '.
							$this->db->dbprefix('return_tax_front').'.month, '.
							$this->db->dbprefix('return_tax_front').'.year, '.
							$this->db->dbprefix('return_tax_front').'.covreturn_start, '.
							$this->db->dbprefix('return_tax_front').'.covreturn_end, '.
							$this->db->dbprefix('return_tax_front').'.created_date '
						 );
		$this->db->join('companies',$this->db->dbprefix('return_tax_front').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$this->db->group_by('return_tax_front.id');
		$q = $this->db->get('return_tax_front');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getReturnTaxFront($id=NULL)
	{
		$this->db->select('return_tax_front.*,companies.*');
		$this->db->join('companies',$this->db->dbprefix('return_tax_front').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$q = $this->db->get_where('return_tax_front',array($this->db->dbprefix('return_tax_front').'.id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getReturnTaxBack($id=NULL, $type=NULL)
	{
		$this->db->select('return_tax_back.*,products.name');
		$this->db->join('products',$this->db->dbprefix('return_tax_back').'.itemcode = '.$this->db->dbprefix('products').'.code','INNER');
		$q = $this->db->get_where('return_tax_back',array($this->db->dbprefix('return_tax_back').'.tax_return_id'=>$id,$this->db->dbprefix('return_tax_back').'.type'=>$type));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	function delete_value_add_tax($id){
		$this->db->where('id', $id);
		if($this->db->delete('erp_return_value_added_tax')){
			$this->db->where('value_id', $id);
			$this->db->delete('erp_return_value_added_tax_back');
			return true;
		}		
	}
	function salary_tax_delete($id){
		$this->db->where('id', $id);
		if($this->db->delete('erp_salary_tax')){
			$this->db->where('salary_tax_id', $id);
			$this->db->delete('erp_salary_tax_back');
			$this->db->where('salary_tax_id', $id);
			$this->db->delete('erp_salary_tax_front');
			return true;
		}		
	}
	
	function prepayment_profit_tax_delete($id){
		$this->db->where('id', $id);
		if($this->db->delete('erp_return_tax_front')){
			$this->db->where('tax_return_id', $id);
			$this->db->delete('erp_return_tax_back');
			return true;
		}		
	}
	
	function withholding_tax_report_delete($id){
		$this->db->where('id', $id);
		if($this->db->delete('erp_return_withholding_tax')){
			$this->db->where('withholding_id', $id);
			$this->db->delete('erp_return_withholding_tax_back');
			
			$this->db->where('withholding_id', $id);
			$this->db->delete('erp_return_withholding_tax_front');
			return true;
		}		
	}
}