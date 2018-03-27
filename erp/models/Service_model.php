<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Service_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
	
	public function addServices($data)
	{
		if ($this->db->insert('services', $data)) {
            return true;
        }
        return false;
	}
	
	public function updateServices($id, $data)
	{
		//$this->db->update('table', data, where)
		if ($this->db->update('services', $data, array('id' => $id))) {
            return true;
        }
        return false;
	}
	
	public function deleteServices($id)
	{
		//$this->db->delete('table', where)
		if($this->db->delete('services', array('id' => $id))){
			return true;
		}
		return false;
	}
	
	public function getServiceById($id)
	{
		$q = $this->db->get_where('services', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
	}
	
	public function getAllService($id)
	{
		$this->db->select('services.*, products.name as pro_name, brands.name as brand_name, companies.name as cname')
				 ->from('services')
				 ->join('products', 'services.modal = products.id', 'left')
				 ->join('brands', 'services.brand = brands.id', 'left')
				 ->join('companies', 'services.name = companies.id', 'left')
				 ->where('services.id', $id);
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
            return $q->row();
        }
	}		
	
	public function getRef()
	{		
		$this->db->select('MAX(service_number) as num')				 
				 ->from('services');		
		$q = $this->db->get();		
		if($q->num_rows() > 0){			
			return $q->row();		
		}		
		return false;	
	}		
	
	public function service_num($id)
	{		
		$this->db->select('service_number')				 
				 ->from('services')				 
				 ->where('id', $id);		
		$q = $this->db->get();		
		if ($q->num_rows() > 0) {            
			return $q->row();        
		}	
	}
	
	public function separatestring($string)
	{
		$res = preg_replace("/[^0-9]/", "", $string);
		return $res;
	}
	
	public function getAllCustomers()
	{
		$this->db->select('*')
				 ->from('companies')
				 ->where('group_name', 'customer');
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
}
