<?php

namespace App\Models;

class Expenses_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'expenses';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $expenses_table = $this->db->prefixTable('expenses');
        $expense_categories_table = $this->db->prefixTable('expense_categories');
        $projects_table = $this->db->prefixTable('projects');
        $users_table = $this->db->prefixTable('users');
        $taxes_table = $this->db->prefixTable('taxes');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $expenses_table.id=$id";
        }
        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($expenses_table.expense_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $start_timesheet_filter = $this->_get_clean_value($options, "start_timesheet_filter");
        $end_timesheet_filter = $this->_get_clean_value($options, "end_timesheet_filter");
        if ($start_timesheet_filter && $end_timesheet_filter) {
            $where .= " AND ($expenses_table.start_timesheet_filter >= '$start_timesheet_filter' AND $expenses_table.end_timesheet_filter <= '$end_timesheet_filter') ";
        }

        $category_id = $this->_get_clean_value($options, "category_id");
        if ($category_id) {
            $where .= " AND $expenses_table.category_id=$category_id";
        }

        $project_id = $this->_get_clean_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $expenses_table.project_id=$project_id";
        }

        $user_id = $this->_get_clean_value($options, "user_id");
        if ($user_id) {
            $where .= " AND $expenses_table.user_id=$user_id";
        }

        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $expenses_table.client_id=$client_id";
        }

        $recurring = $this->_get_clean_value($options, "recurring");
        if ($recurring) {
            $where .= " AND $expenses_table.recurring=1";
        }


        $group_by = $this->_get_clean_value($options, "group_by");
        $group_general = " $expenses_table.id";
        if ($group_by === "member") {
            $group_general = "$expenses_table.user_id";
        } else if ($group_by === "member/project") {
            $group_general = " $expenses_table.user_id, $expenses_table.project_id";
        } else if ($group_by === "project") {
            $group_general = " $expenses_table.project_id";
        }

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("expenses", $custom_fields, $expenses_table, $custom_field_filter);
        $select_custom_fields = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fields = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $sql = "SELECT 
                    $expenses_table.id,
                    $expenses_table.expense_date,
                    $expenses_table.category_id,
                    $expenses_table.title,
                    $expenses_table.description,
                    SUM($expenses_table.amount) as amount,
                    $expenses_table.start_timesheet_filter,
                    $expenses_table.end_timesheet_filter,
                    $expenses_table.files,
                    $expenses_table.tax_id,
                    $expenses_table.tax_id2,
                    $expenses_table.client_id,
                    $expenses_table.project_id,
                    $expenses_table.user_id, 
                    $expenses_table.recurring,
                    $expenses_table.next_recurring_date,
                    $expenses_table.no_of_cycles,
                    $expenses_table.no_of_cycles_completed,
                    $expenses_table.recurring_expense_id,
                    $expenses_table.repeat_every,
                    $expenses_table.repeat_type,
                    $expenses_table.deleted,
                    $expense_categories_table.title as category_title, 
                    CONCAT($users_table.first_name, ' ', $users_table.last_name) AS linked_user_name,
                    $users_table.image AS linked_user_avatar,
                    $clients_table.company_name AS linked_client_name,
                    $projects_table.title AS project_title,
                    tax_table.percentage AS tax_percentage,
                    tax_table2.percentage AS tax_percentage2
                    $select_custom_fields
        FROM $expenses_table
        LEFT JOIN $expense_categories_table ON $expense_categories_table.id= $expenses_table.category_id
        LEFT JOIN $clients_table ON $clients_table.id= $expenses_table.client_id
        LEFT JOIN $projects_table ON $projects_table.id= $expenses_table.project_id
        LEFT JOIN $users_table ON $users_table.id= $expenses_table.user_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $expenses_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $expenses_table.tax_id2
            $join_custom_fields
        WHERE $expenses_table.deleted=0 $where $custom_fields_where GROUP BY $group_general";
        return $this->db->query($sql);
    }

    function get_income_expenses_info($options = array()) {
        $expenses_table = $this->db->prefixTable('expenses');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoices_table = $this->db->prefixTable('invoices');
        $taxes_table = $this->db->prefixTable('taxes');
        $clients_table = $this->db->prefixTable('clients');
        $info = new \stdClass();
        
        $where_income = "";
        $where_expenses = "";
        $year = $this->_get_clean_value($options, "year");
        if($year){
            $where_expenses .= " AND YEAR($expenses_table.expense_date)='$year'";
            $where_income .= " AND YEAR($invoice_payments_table.payment_date)='$year'";
        }

        $income_sql = "SELECT SUM($invoice_payments_table.amount) as total_income, 
            (SELECT $clients_table.currency FROM $clients_table WHERE $clients_table.id=(
                SELECT $invoices_table.client_id FROM $invoices_table WHERE $invoices_table.id=$invoice_payments_table.invoice_id
                )
            ) AS currency
        FROM $invoice_payments_table
        WHERE $invoice_payments_table.deleted=0 AND $invoice_payments_table.invoice_id IN(SELECT $invoices_table.id FROM $invoices_table WHERE $invoices_table.deleted=0) $where_income
        GROUP BY currency";
        $income_result = $this->db->query($income_sql)->getResult();

        $expenses_sql = "SELECT SUM($expenses_table.amount + IFNULL(tax_table.percentage,0)/100*IFNULL($expenses_table.amount,0) + IFNULL(tax_table2.percentage,0)/100*IFNULL($expenses_table.amount,0)) AS total_expenses
        FROM $expenses_table
        LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table ON tax_table.id = $expenses_table.tax_id
        LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table2 ON tax_table2.id = $expenses_table.tax_id2
        WHERE $expenses_table.deleted=0 $where_expenses";
        $expenses = $this->db->query($expenses_sql)->getRow();
        
        //prepare income
        $total_income = 0;
        foreach ($income_result as $income){
            $total_income += get_converted_amount($income->currency, $income->total_income);
        }

        $info->income = $total_income;
        $info->expneses = $expenses->total_expenses;
        return $info;
    }

    function get_yearly_expenses_chart($year, $project_id = 0) {
        $expenses_table = $this->db->prefixTable('expenses');
        $taxes_table = $this->db->prefixTable('taxes');

        $where = "";
        if ($project_id) {
            $where = " AND $expenses_table.project_id=$project_id";
        }

        $expenses = "SELECT SUM($expenses_table.amount + IFNULL(tax_table.percentage,0)/100*IFNULL($expenses_table.amount,0) + IFNULL(tax_table2.percentage,0)/100*IFNULL($expenses_table.amount,0)) AS total, MONTH($expenses_table.expense_date) AS month
        FROM $expenses_table
        LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table ON tax_table.id = $expenses_table.tax_id
        LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table2 ON tax_table2.id = $expenses_table.tax_id2
        WHERE $expenses_table.deleted=0 AND YEAR($expenses_table.expense_date)= $year $where
        GROUP BY MONTH($expenses_table.expense_date)";

        return $this->db->query($expenses)->getResult();
    }

    //get the recurring expenses which are ready to renew as on a given date
    function get_renewable_expenses($date) {
        $expenses_table = $this->db->prefixTable('expenses');

        $sql = "SELECT * FROM $expenses_table
                        WHERE $expenses_table.deleted=0 AND $expenses_table.recurring=1
                        AND $expenses_table.next_recurring_date IS NOT NULL AND $expenses_table.next_recurring_date<='$date'
                        AND ($expenses_table.no_of_cycles < 1 OR ($expenses_table.no_of_cycles_completed < $expenses_table.no_of_cycles ))";

        return $this->db->query($sql);
    }

    function get_summary_details($options = array()) {
        $expenses_table = $this->db->prefixTable('expenses');
        $expense_categories_table = $this->db->prefixTable('expense_categories');
        $taxes_table = $this->db->prefixTable('taxes');
        $where = "";

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($expenses_table.expense_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $sql = "SELECT SUM($expenses_table.amount) AS amount, SUM(IFNULL(tax_table.percentage,0)/100*IFNULL($expenses_table.amount,0)) AS tax, SUM(IFNULL(tax_table2.percentage,0)/100*IFNULL($expenses_table.amount,0)) AS tax2, $expense_categories_table.title AS category_title
        FROM $expenses_table
        LEFT JOIN $expense_categories_table ON $expense_categories_table.id= $expenses_table.category_id
        LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table ON tax_table.id = $expenses_table.tax_id
        LEFT JOIN (SELECT $taxes_table.id, $taxes_table.percentage FROM $taxes_table) AS tax_table2 ON tax_table2.id = $expenses_table.tax_id2
        WHERE $expenses_table.deleted=0 $where
        GROUP BY $expenses_table.category_id";

        return $this->db->query($sql);
    }

}
