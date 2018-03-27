<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Taxes_reports extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            redirect('login');
        }
		if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->lang->load('taxes', $this->Settings->language);
		$this->lang->load('accounts', $this->Settings->language);
        $this->load->library('form_validation');
        $this->load->model('reports_model');
		$this->load->model('taxes_reports_model');
		$this->load->model('sales_model');
		$this->load->model('companies_model');
		$this->load->model('accounts_model');
		$this->load->model('taxes_model');
        
        if(!$this->Owner && !$this->Admin) {
            $gp = $this->site->checkPermissions();
            $this->permission = $gp[0];
            $this->permission[] = $gp[0];
        } else {
            $this->permission[] = NULL;
        }
   }
   
     function index($action = NULL)
    {
        
    }
	
	function purchase_journal_list(){
		$this->erp->checkPermissions();
        $this->data['confirm_tax'] = $this->taxes_reports_model->getConfirmTax_purch();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Purchase Journal List')));
        $meta = array('page_title' => lang('purchase_journal_list'), 'bc' => $bc);
        $this->page_construct('taxes_reports/purchase_journal_list', $meta, $this->data);
	}
	
	function sales_journal_list(){
		$this->erp->checkPermissions();
        
		$this->data['confirm_tax'] = $this->taxes_reports_model->getConfirmTax();
		
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Sales Journal List')));
        $meta = array('page_title' => lang('sales_journal_list'), 'bc' => $bc);
        $this->page_construct('taxes_reports/sales_journal_list', $meta, $this->data);
	}
	
	function sales_jounal_view_form($month=NULL,$year=NULL,$group_id=NULL){
		$this->erp->checkPermissions();
        $this->data['confirm_tax']   = $this->taxes_reports_model->getConfirmTax();
		$this->data['company']       = $this->taxes_reports_model->company_info($group_id);
		$this->data['sales_list']    = $this->taxes_reports_model->v_sale_journal_list($month,$year,$group_id);
		$this->data['exchange_rate'] = $this->taxes_model->getExchangeRate('KHM');
        $this->load->view($this->theme .'taxes_reports/sales_journal_form', $this->data);
	}
	
	
	function purc_jounal_view_form($month=NULL,$year=NULL,$group_id=NULL) {
		$this->erp->checkPermissions();
        $this->data['confirm_tax'] = $this->taxes_reports_model->getConfirmTax_purch();
		$this->data['company'] = $this->taxes_reports_model->company_info($group_id);
		$this->data['purc_list'] = $this->taxes_reports_model->v_purch_journal_list($month,$year,$group_id);
		$this->data['exchange_rate'] = $this->taxes_model->getExchangeRate('KHM');
        $this->load->view($this->theme .'taxes_reports/purchase_journal_form', $this->data);
	}
	
	function update_journal_date(){	
		$this->erp->checkPermissions();
		$data['date']  = $this->input->get('journal_date');
		$data['month'] = $this->input->get('month');;
		$data['year']  = $this->input->get('year');
		$type 		   = $this->input->get('type');
		if($type=='PURC'){
			$help=$this->taxes_reports_model->update_journal_date_pur($data);
		}else if($type=='SALE'){
			$help=$this->taxes_reports_model->update_journal_date($data);
		}
			 
	}
	
	function update_journal_loc(){	
		$this->erp->checkPermissions();
		$data['location']  = $this->input->get('location');
		$data['month']     = $this->input->get('month');
		$data['year']      = $this->input->get('year');
		$type 			   = $this->input->get('type');
		if($type=='PURC'){
			$help=$this->taxes_reports_model->update_journal_loc_pur($data);
		}else if($type=='SALE'){
			$help=$this->taxes_reports_model->update_journal_loc($data);
		}
	}
	
	function tax_salary_list(){
		$this->erp->checkPermissions();
		
        $this->data['salary_taxes'] = $this->taxes_reports_model->getSalaryTaxList();
		
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('tax_salary_list')));
        $meta = array('page_title' => lang('tax_salary_list'), 'bc' => $bc);
        $this->page_construct('taxes_reports/tax_salary_list', $meta, $this->data);
	}
	
	function getSalaryTax() {
		
		$detail_link = anchor('taxes_reports/salary_tax_report/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_report'), array('target' => '_blank'));
        $edit_link = anchor('taxes/salary_tax_edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_salary_tax'), 'class="sledit"');
        $delete_link = "<a href='#' class='po' title='" . lang("delete_report") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('taxes_reports/salary_tax_delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_report') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
		</div></div>';
		
		$this->load->library('datatables');
		$this->datatables->select(
							$this->db->dbprefix('salary_tax').'.id, '.
							$this->db->dbprefix('companies').'.company, '.
							$this->db->dbprefix('salary_tax').'.month, '.
							$this->db->dbprefix('salary_tax').'.year, '.
							$this->db->dbprefix('salary_tax').'.covreturn_start, '.
							$this->db->dbprefix('salary_tax').'.covreturn_end, '.
							$this->db->dbprefix('salary_tax').'.created_date '
						 );
		$this->datatables->join('companies',$this->db->dbprefix('salary_tax').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$this->datatables->group_by('salary_tax.id');
		$this->datatables->from('salary_tax');
		
		$this->datatables->add_column("Actions", $action, $this->db->dbprefix('salary_tax').'.id');
		
        echo $this->datatables->generate();
	}

	function salary_tax_report($id=NULL){
		$this->erp->checkPermissions();
		
        $this->data['salary_tax'] = $this->taxes_reports_model->getSalaryTaxByID($id);
		$this->data['RES'] = $this->taxes_reports_model->getSalaryTaxFrontByID($id,'RE');
		$this->data['NRES'] = $this->taxes_reports_model->getSalaryTaxFrontByID($id,'NRE');
		$this->data['FBS'] = $this->taxes_reports_model->getSalaryTaxFrontByID($id,'FB');
		$this->data['REBS'] = $this->taxes_reports_model->getSalaryTaxBackByID($id,'REB');
		$this->data['FBBS'] = $this->taxes_reports_model->getSalaryTaxBackByID($id,'FBB');
		
        $this->load->view($this->theme .'taxes_reports/salary_tax_report', $this->data);
	}
	
	function value_added_tax() {
		$this->erp->checkPermissions();
		
        //$this->data['value_add_taxes'] = $this->taxes_reports_model->getValueAddTaxList();
		
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('value_added_tax')));
        $meta = array('page_title' => lang('value_added_tax'), 'bc' => $bc);
        $this->page_construct('taxes_reports/value_added_tax', $meta, $this->data);
	}
	
	function getValueAddTaxList()
	{
		$detail_link = anchor('taxes_reports/value_add_tax_report/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_report'), array('target' => '_blank'));
        $edit_link = anchor('taxes/value_added_tax_edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $delete_link = "<a href='#' class='po' title='" . lang("delete_report") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('taxes_reports/value_add_tax_delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_report') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
		</div></div>';
		
		$this->load->library('datatables');
		$this->datatables->select(
							$this->db->dbprefix('return_value_added_tax').'.id, '.
							$this->db->dbprefix('companies').'.company, '.
							$this->db->dbprefix('return_value_added_tax').'.month, '.
							$this->db->dbprefix('return_value_added_tax').'.year, '.
							$this->db->dbprefix('return_value_added_tax').'.covreturn_start, '.
							$this->db->dbprefix('return_value_added_tax').'.covreturn_end, '.
							$this->db->dbprefix('return_value_added_tax').'.created_date '
						 );
		$this->datatables->join('companies',$this->db->dbprefix('return_value_added_tax').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$this->datatables->group_by('return_value_added_tax.id');
		$this->datatables->from('return_value_added_tax');
		
		$this->datatables->add_column("Actions", $action, $this->db->dbprefix('return_value_added_tax').'.id');
		
        echo $this->datatables->generate();
	}

	function value_add_tax_report($id=NULL) {
		$this->erp->checkPermissions();
		
        $this->data['front'] = $this->taxes_reports_model->getInfoFrontPage($id);
		$this->data['back_20'] = $this->taxes_reports_model->getInfoBackPage($id,'20');
		$this->data['back_21'] = $this->taxes_reports_model->getInfoBackPage($id,'21');
		$this->data['back_22'] = $this->taxes_reports_model->getInfoBackPage($id,'22');
		

        $this->load->view($this->theme .'taxes_reports/value_add_tax_report', $this->data);
	}
	function value_add_tax_edit($id=NULL) {
		$this->erp->checkPermissions();
		
        $this->data['front'] = $this->taxes_reports_model->getInfoFrontPage($id);
		$this->data['back_20'] = $this->taxes_reports_model->getInfoBackPage($id,'20');
		$this->data['back_21'] = $this->taxes_reports_model->getInfoBackPage($id,'21');
		$this->data['back_22'] = $this->taxes_reports_model->getInfoBackPage($id,'22');
		

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('value_added_tax')));
        $meta = array('page_title' => lang('value_added_tax'), 'bc' => $bc);
		
       $this->page_construct('taxes/value_added_tax_edit', $meta, $this->data);
	}
	function withholding_tax(){
		$this->erp->checkPermissions();
		//$this->data['withholding_tax'] = $this->taxes_reports_model->getWithholdingTaxList();
		
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('withholding_tax')));
        $meta = array('page_title' => lang('withholding_tax'), 'bc' => $bc);
        $this->page_construct('taxes_reports/withholding_tax', $meta, $this->data);
		
	}
	
	function getWithholdingTaxList()
	{
		$detail_link = anchor('taxes_reports/withholding_tax_report/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_report'), array('target' => '_blank'));
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_report") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('taxes_reports/withholding_tax_report_delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_report') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
		</div></div>';
		
		$this->load->library('datatables');
		$this->datatables->select(
							$this->db->dbprefix('return_withholding_tax').'.id, '.
							$this->db->dbprefix('companies').'.company, '.
							$this->db->dbprefix('return_withholding_tax').'.month, '.
							$this->db->dbprefix('return_withholding_tax').'.year, '.
							$this->db->dbprefix('return_withholding_tax').'.covreturn_start, '.
							$this->db->dbprefix('return_withholding_tax').'.covreturn_end, '.
							$this->db->dbprefix('return_withholding_tax').'.created_date '
						 );
		$this->datatables->join('companies',$this->db->dbprefix('return_withholding_tax').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$this->datatables->group_by('return_withholding_tax.id');
		$this->datatables->from('return_withholding_tax');
		
		$this->datatables->add_column("Actions", $action, $this->db->dbprefix('return_withholding_tax').'.id');
		
        echo $this->datatables->generate();
	}
	
	function withholding_tax_report($id=NULL) {
		$this->erp->checkPermissions();
		
        $this->data['front'] = $this->taxes_reports_model->getInfoFrontPageWHT($id);
		$this->data['front_25'] = $this->taxes_reports_model->getWithholdingTaxFrontByID($id,'TOR25');
		//$this->erp->print_arrays($this->taxes_reports_model->getWithholdingTaxFrontByID($id,'TOR25'));
		$this->data['front_26'] = $this->taxes_reports_model->getWithholdingTaxFrontByID($id,'TOR26');
		$this->data['back_DWTRT'] = $this->taxes_reports_model->getWithholdingTaxBackByID($id,'DWTRT');
		
		$this->data['back_DWTRNT'] = $this->taxes_reports_model->getWithholdingTaxBackByID($id,'DWTRNT');
		//$this->erp->print_arrays($this->taxes_reports_model->getInfoBackPage($group_id,'20'));

        $this->load->view($this->theme .'taxes_reports/withholding_tax_report', $this->data);
	}
	
	
	function prepayment_profit_tax_list()
	{
		$this->erp->checkPermissions();
		
        //$this->data['return_taxes'] = $this->taxes_reports_model->getReturnTaxList();
		
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('prepayment_of_profit_tax_list')));
        $meta = array('page_title' => lang('prepayment_of_profit_tax_list'), 'bc' => $bc);
        $this->page_construct('taxes_reports/prepayment_profit_tax_list', $meta, $this->data);
	}
	
	function getPrepaymentList()
	{
		$detail_link = anchor('taxes_reports/prepayment_profit_tax_report/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_report'), array('target' => '_blank'));
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_report") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('taxes_reports/prepayment_profit_tax_delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_report') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
			<li>' . $delete_link . '</li>
        </ul>
		</div></div>';
		
		$this->load->library('datatables');
		$this->datatables->select(
							$this->db->dbprefix('return_tax_front').'.id, '.
							$this->db->dbprefix('companies').'.company, '.
							$this->db->dbprefix('return_tax_front').'.month, '.
							$this->db->dbprefix('return_tax_front').'.year, '.
							$this->db->dbprefix('return_tax_front').'.covreturn_start, '.
							$this->db->dbprefix('return_tax_front').'.covreturn_end, '.
							$this->db->dbprefix('return_tax_front').'.created_date '
						 );
		$this->datatables->join('companies',$this->db->dbprefix('return_tax_front').'.group_id = '.$this->db->dbprefix('companies').'.id','INNER');
		$this->datatables->group_by('return_tax_front.id');
		$this->datatables->from('return_tax_front');
		
		$this->datatables->add_column("Actions", $action, $this->db->dbprefix('return_tax_front').'.id');
		
        echo $this->datatables->generate();
	}
	
	function prepayment_profit_tax_report($id=NULL)
	{
		$this->erp->checkPermissions();
		
        $this->data['front'] = $this->taxes_reports_model->getReturnTaxFront($id);
		$this->data['back_SGP'] = $this->taxes_reports_model->getReturnTaxBack($id,'SGP');
		$this->data['back_SS'] = $this->taxes_reports_model->getReturnTaxBack($id,'SS');
		//$this->erp->print_arrays($this->taxes_reports_model->getReturnTaxBack($id,'SS'));

        $this->load->view($this->theme .'taxes_reports/prepayment_profit_tax_report', $this->data);
	}
	function value_add_tax_delete($id){
		
		$this->erp->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->taxes_reports_model->delete_value_add_tax($id)) {
			if($this->input->is_ajax_request()) {
                echo lang("value_add_tax_deleted"); die();
            }
            $this->session->set_flashdata('message', lang('value_add_tax_deleted'));
            redirect('taxes_reports/value_added_tax');
        }
	}
	
	
	function salary_tax_delete($id){
		$this->erp->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->taxes_reports_model->salary_tax_delete($id)) {
			if($this->input->is_ajax_request()) {
                echo lang("salary_tax_deleted"); die();
            }
            $this->session->set_flashdata('message', lang('salary_tax_deleted'));
            redirect('taxes_reports/tax_salary_list');
        }
	}
	
	function prepayment_profit_tax_delete($id){
		$this->erp->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->taxes_reports_model->prepayment_profit_tax_delete($id)) {
			if($this->input->is_ajax_request()) {
                echo lang("prepayment_profit_tax_deleted"); die();
            }
            $this->session->set_flashdata('message', lang('prepayment_profit_tax_deleted'));
            redirect('taxes_reports/prepayment_profit_tax_list');
        }
	}
	
	function withholding_tax_report_delete($id){
		$this->erp->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->taxes_reports_model->withholding_tax_report_delete($id)) {
			if($this->input->is_ajax_request()) {
                echo lang("withholding_tax_report_deleted"); die();
            }
            $this->session->set_flashdata('message', lang('withholding_tax_report_deleted'));
            redirect('taxes_reports/withholding_tax');
        }
	}
}