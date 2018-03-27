<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            redirect('login');
        }
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
		$this->load->library('upload');
		$this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '4096';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'erp_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
        $this->lang->load('notifications', $this->Settings->language);
        $this->load->library('form_validation');
        $this->load->model('cmt_model');

    }

    function index()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('notifications')));
        $meta = array('page_title' => lang('notifications'), 'bc' => $bc);
        $this->page_construct('notifications/index', $meta, $this->data);
    }

    function getNotifications()
    {

        $this->load->library('datatables');
        $this->datatables
            ->select("id, comment, date, from_date, till_date")
            ->from("notifications")
            //->where('notification', 1)
            ->add_column("Actions", "<center><a href='" . site_url('notifications/edit/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang("edit_notification") . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_notification") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('notifications/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
        //$this->datatables->unset_column('id');
        echo $this->datatables->generate();
    }

    function add()	{
		$this->erp->checkPermissions();
		$this->load->helper('security');
        $this->form_validation->set_rules('comment', lang("comment"), 'required|min_length[3]');
		$this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			$images=$this->input->post('image');
            $recipient = $this->input->post('recipient')?$this->input->post('recipient'):$this->session->userdata('user_name');

            $i = sizeof($_POST['items']);
            for ($r = 0; $r < $i; $r++) {

                $items[] = array(
                        'item'        => $_POST['items'][$r],
                        'quantity'    => $_POST['quantity'][$r],
                    );

            }

			$data = array(	
				'recipient'     => $recipient,	
				'created_by'    => $this->session->userdata('user_id') ? $this->session->userdata('user_id') :NULL,
				'sender'        => $this->input->post('sender'),
				'comment'       => $this->input->post('comment'),
				'from_date'     => $this->input->post('from_date') ? $this->erp->fld($this->input->post('from_date')) : NULL,
				'till_date'     => $this->input->post('to_date') ? $this->erp->fld($this->input->post('to_date')) : NULL,
				'scope'         => $this->input->post('scope')
			);

            //$this->erp->print_arrays($data, $items);
		
        } elseif ($this->input->post('submit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("notifications");
        }

        if ($this->form_validation->run() == true && $this->cmt_model->addNotification($data, $items)) {
            $this->session->set_flashdata('message', lang("notification_added"));
            redirect("notifications");
        } else {

            $this->data['comment'] = array(
                'name'      => 'comment',
                'id'        => 'comment',
                'type'      => 'textarea',
                'class'     => 'form-control',
                'required'  => 'required',
                'value'     => $this->form_validation->set_value('comment'),
            );

            $this->data['error'] = validation_errors();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'notifications/add', $this->data);

        }
    }

    function edit($id = NULL)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }

        $this->form_validation->set_rules('comment', lang("notifications"), 'required|min_length[3]');
        $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        if ($this->form_validation->run() == true) {

            $i = sizeof($_POST['items']);
            for ($r = 0; $r < $i; $r++) {
				$items[] = array(
					'item'        => $_POST['items'][$r],
					'quantity'    => $_POST['quantity'][$r],
				);

            }

            $data = array(
			    'created_by'    => $this->session->userdata('user_id') ? $this->session->userdata('user_id') :NULL,
                'comment' => $this->input->post('comment')? $this->input->post('comment') :NULL,				
				'recipient' =>$this->input->post('recipient')?$this->input->post('recipient') :NULL,
				'sender' =>$this->input->post('sender') ? $this->input->post('sender') :NULL,		
				'comment' => $this->input->post('comment') ? $this->input->post('comment') :NULL,	
                'from_date' => $this->input->post('from_date') ? $this->erp->fld($this->input->post('from_date')) : NULL,
                'till_date' => $this->input->post('to_date') ? $this->erp->fld($this->input->post('to_date')) : NULL,
                'scope' => $this->input->post('scope'),
            );
			//$this->erp->print_arrays($items);
            /*
			$this->load->library('upload');
			if ($_FILES['product_image']['size'] > 0) {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                //$config['max_width'] = $this->Settings->iwidth;
                //$config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("notification");
                }
                $photo = $this->upload->file_name;
                $data['images'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;

                $config['maintain_ratio'] = TRUE;
                //$config['width'] = $this->Settings->twidth;
                //$config['height'] = $this->Settings->theight;
				$config['width'] = $this->Settings->iwidth;
                $config['height'] = $this->Settings->iheight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
				copy($config['new_image'] , $config['source_image']);
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'right';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }else{
				$data['images'] = $this->input->post('exsit_image');
			}
            */
        } elseif ($this->input->post('submit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("notifications");
        }

        if ($this->form_validation->run() == true && $this->cmt_model->updateNotification($id, $data, $items)) {

            $this->session->set_flashdata('message', lang("notification_updated"));
            redirect("notifications");

        } else {

            $comment   = $this->cmt_model->getCommentByID($id);
            $note_item = $this->cmt_model->getCommentItemByID($id);

            $this->data['comment'] = array(
                'name'     => 'comment',
                'id'       => 'comment',
                'type'     => 'textarea',
                'class'    => 'form-control',
                'required' => 'required',
                'value'    => $this->form_validation->set_value('comment', $comment->comment),
            );


            $this->data['notification'] = $comment;
            $this->data['note_item']    = $note_item;
            $this->data['id']           = $id;
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['error']        = validation_errors();
            $this->load->view($this->theme . 'notifications/edit', $this->data);

        }
    }

    function delete($id = NULL)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->cmt_model->deleteComment($id)) {
            echo lang("notifications_deleted");
        }
    }

	function modal_view($id =null){
		$biller_id = $this->site->default_biller_id();
		$this->data['biller'] = $this->site->getCompanyByID($biller_id);
        $this->data['notification']=$this->cmt_model->getNotifications_items($id);		
	//	$this->erp->print_arrays($this->cmt_model->getNotifications_items($id));
		$this->data['notification_item'] =$this->cmt_model->getNotifications_item($id);
		$this->load->view($this->theme . 'notifications/modal_view', $this->data);
	}
	
}
