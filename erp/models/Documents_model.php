<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Documents_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
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
	public function getDocumentByID($id = NULL)
	{
		$q = $this->db->get_where('documents', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getAddDocument($id = NULL)
	{
		$q = $this->db->get_where('documents', array('id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getProductByCode($code)
	{
		$q = $this->db->get_where('products', array('code' => $code));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getDocumentPhoto($id = NULL)
	{
		$q = $this->db->get_where('document_photos', array('document_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
	}
	
	public function getProductCode($term)
    {
        $this->db->select('product_code');
		$q = $this->db->get_where('documents', array('product_code' => $term), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }
	
	public function getBrandByID($id = NULL)
	{
		$q = $this->db->get_where('brands', array('id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
	}
	public function getBrandInfoByID($id = NULL)
	{
		$q = $this->db->get_where('brands', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
	}
	public function getUnitByID($id = NULL)
	{
		$q = $this->db->get_where('units', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
	}
	public function deleteDocumentByID($id = NULL){
		$q = $this->db->delete('documents', array('id' => $id));
		if($q){
			$this->db->delete('document_photos', array('document_id' => $id));
			return true;
		} else{
			return false;
		}
	}

	public function updateDocument($id, $data, $items)
	{
		//$this->erp->print_arrays($data);
		if ($this->db->update('documents', $data, array('id' => $id))) {
			if ($items) {
                foreach ($items as $photo) {
                    $this->db->insert('document_photos', array('document_id' => $product_id, 'photo' => $photo));
                }
            }
			return true;
        }
        return false;
	}

	public function checkProduct($code)
	{
		$q = $this->db->get_where('products', array('code' => $code));
        if ($q->num_rows() > 0) {
            return TRUE;
        }
        return FALSE;
	}
}
