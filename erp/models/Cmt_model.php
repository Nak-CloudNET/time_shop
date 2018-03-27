<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Cmt_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();

    }

    public function getAllComments()
    {

        $q = $this->db->get("notifications");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getNotifications()
    {
        $date = date('Y-m-d H:i:s', time());
        $this->db->where("from_date <=", $date);
        $this->db->where("till_date >=", $date);
        if (!$this->Owner) {
            if ($this->Supplier) {
                $this->db->where('scope', 4);
            } elseif ($this->Customer) {
                $this->db->where('scope', 1)->or_where('scope', 3);
            } elseif (!$this->Customer && !$this->Supplier) {
                $this->db->where('scope', 2)->or_where('scope', 3);
            }
        }
        $q = $this->db->get("notifications");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }
	
	public function getNotifications_items($id){
		
		$this->db->SELECT('erp_notifications.comment,erp_notifications.sender,erp_notifications.recipient,erp_notifications.date,erp_users.username')
		         ->join('erp_users','erp_users.id=erp_notifications.created_by','Left');
		$q=$this->db->get_where('erp_notifications',array('erp_notifications.id'=>$id),1);
		if($q->num_rows()>0){
			
			return $q->row();
		}
		return false;
	}
	public function getNotifications_item($id){
		 $this->db->SELECT('erp_notifications_item.item,erp_notifications_item.quantity')
		          ->join('erp_notifications','erp_notifications.id=erp_notifications_item.note_id','Left');
		 $q=$this->db->get_where('erp_notifications_item',array('erp_notifications_item.note_id'=>$id));
		 if($q->num_rows()>0){
			 foreach (($q->result()) as $row) {
                $data[] = $row;
            }
			return $data;
		 }
	}

    public function getCommentByID($id)
    {

        $q = $this->db->get_where("notifications", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;

    }
    public function getCommentItemByID($id)
    {

        $q = $this->db->get_where("notifications_item", array('note_id' => $id) );
        if ($q->num_rows() > 0) {
            return $q->result();
        }

        return FALSE;

    }
    public function addNotification($data, $items)
    {

        if ($this->db->insert("notifications", $data)) {
            $note_id = $this->db->insert_id();
            if ($items) {
                foreach ($items as $item) {
                    $item['note_id'] = $note_id;
                    $this->db->insert('notifications_item', $item);
                }
            }
            return true;
        } else {
            return false;
        }
    }
    public function updateNotification($id, $data, $items)
    {
       // $this->erp->print_arrays($data, $items);
        $this->db->where('id', $id);
        if ($this->db->update("notifications", $data)) {
            if($items){
				$this->db->delete('notifications_item', array('note_id' => $id));
                foreach ($items as $item) {
					$item['note_id'] = $id; 
                    $this->db->insert('notifications_item', $item);
                }
            }
            return true;
        } else {
            return false;
        }
    } 
   
    public function deleteComment($id)
    {
        if ($this->db->delete("notifications", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }


}

/* End of file pts_model.php */
/* Location: ./application/models/pts_types_model.php */
