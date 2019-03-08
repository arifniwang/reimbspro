<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;

class AdminPengajuanController extends \crocodicstudio\crudbooster\controllers\CBController
{

    public function cbInit()
    {
        # START CONFIGURATION DO NOT REMOVE THIS LINE
        $this->table = "pengajuan";
        $this->title_field = "name";
        $this->limit = 20;
        $this->orderby = "id,desc";
        $this->show_numbering = FALSE;
        $this->global_privilege = FALSE;
        $this->button_table_action = TRUE;
        $this->button_action_style = "button_icon";
        $this->button_add = FALSE;
        $this->button_delete = TRUE;
        $this->button_edit = FALSE;
        $this->button_detail = FALSE;
        $this->button_show = TRUE;
        $this->button_filter = FALSE;
        $this->button_export = FALSE;
        $this->button_import = FALSE;
        $this->button_bulk_action = TRUE;
        $this->sidebar_mode = "normal"; //normal,mini,collapse,collapse-mini
        # END CONFIGURATION DO NOT REMOVE THIS LINE

        # START COLUMNS DO NOT REMOVE THIS LINE
        $this->col = [];
        $this->col[] = array("label" => "Pegawai", "name" => "id_users", "join" => "users,name");
        $this->col[] = array("label" => "Nama Pengajuan", "name" => "name");
        $this->col[] = array("label" => "Deskripsi", "name" => "description");
        $this->col[] = array("label" => "Total", "name" => "total_nominal", "callback" => function ($row) {
            return number_format($row->total_nominal, 0, ',', '.');
        });
        $this->col[] = array("label" => "Status", "name" => "status", "callback" => function ($row) {
            if ($row->status == 'Disetujui') {
                return '<span class="badge badge-success">' . $row->status . '</span>';
            } elseif ($row->status == 'Ditolak') {
                return '<span class="badge badge-danger">' . $row->status . '</span>';
            } else {
                return '<span class="badge badge-warning">' . $row->status . '</span>';
            }
        });
        $this->col[] = array("label" => "Tangal Pengajuan", "name" => "created_at");

        # END COLUMNS DO NOT REMOVE THIS LINE
        # START FORM DO NOT REMOVE THIS LINE
        $this->form = [];
        $this->form[] = ["label" => "Pegawai", "name" => "id_users", "type" => "select2", "required" => TRUE,
            "validation" => "required|integer|min:0", "datatable" => "users,name"];
        $this->form[] = ["label" => "Nama Pengajuan", "name" => "name", "type" => "text", "required" => TRUE,
            "validation" => "required|string|min:3|max:70", "placeholder" => "You can only enter the letter only"];
        $this->form[] = ["label" => "Deskripsi", "name" => "description", "type" => "textarea", "required" => TRUE,
            "validation" => "required|string|min:5|max:5000"];
        $this->form[] = ["label" => "Total", "name" => "total_nominal", "type" => "money", "required" => TRUE,
            "validation" => "required|min:1|max:255"];

        # END FORM DO NOT REMOVE THIS LINE

        /*
        | ----------------------------------------------------------------------
        | Sub Module
        | ----------------------------------------------------------------------
        | @label          = Label of action
        | @path           = Path of sub module
        | @foreign_key 	  = foreign key of sub table/module
        | @button_color   = Bootstrap Class (primary,success,warning,danger)
        | @button_icon    = Font Awesome Class
        | @parent_columns = Sparate with comma, e.g : name,created_at
        |
        */
        $this->sub_module = array();
        $this->sub_module[] = ['label' => '', 'path' => 'detail-pengajuan', 'parent_columns' => 'id_users,name,total_nominal,description',
            'parent_columns_alias' => 'Pegawai,Nama Pengajuan,Total,Dekripsi', 'foreign_key' => 'id_pengajuan', 'button_color' => 'primary',
            'button_icon' => 'fa fa-eye'];


        /*
        | ----------------------------------------------------------------------
        | Add More Action Button / Menu
        | ----------------------------------------------------------------------
        | @label       = Label of action
        | @url         = Target URL, you can use field alias. e.g : [id], [name], [title], etc
        | @icon        = Font awesome class icon. e.g : fa fa-bars
        | @color 	   = Default is primary. (primary, warning, succecss, info)
        | @showIf 	   = If condition when action show. Use field alias. e.g : [id] == 1
        |
        */
        $this->addaction = array();
        $this->addaction[] = ['label' => 'Approve', 'url' => CRUDBooster::mainpath('approve/[id]'), 'icon' => 'fa fa-check',
            'color' => 'info', 'showIf' => "[status] == 'Diproses'", 'confirmation' => true];
        $this->addaction[] = ['label' => 'Reject', 'url' => CRUDBooster::mainpath('reject/[id]'), 'icon' => 'fa fa-ban',
            'color' => 'danger', 'showIf' => "[status] == 'Diproses'", 'confirmation' => true];


        /*
        | ----------------------------------------------------------------------
        | Add More Button Selected
        | ----------------------------------------------------------------------
        | @label       = Label of action
        | @icon 	   = Icon from fontawesome
        | @name 	   = Name of button
        | Then about the action, you should code at actionButtonSelected method
        |
        */
        $this->button_selected = array();


        /*
        | ----------------------------------------------------------------------
        | Add alert message to this module at overheader
        | ----------------------------------------------------------------------
        | @message = Text of message
        | @type    = warning,success,danger,info
        |
        */
        $this->alert = array();


        /*
        | ----------------------------------------------------------------------
        | Add more button to header button
        | ----------------------------------------------------------------------
        | @label = Name of button
        | @url   = URL Target
        | @icon  = Icon from Awesome.
        |
        */
        $this->index_button = array();


        /*
        | ----------------------------------------------------------------------
        | Customize Table Row Color
        | ----------------------------------------------------------------------
        | @condition = If condition. You may use field alias. E.g : [id] == 1
        | @color = Default is none. You can use bootstrap success,info,warning,danger,primary.
        |
        */
        $this->table_row_color = array();


        /*
        | ----------------------------------------------------------------------
        | You may use this bellow array to add statistic at dashboard
        | ----------------------------------------------------------------------
        | @label, @count, @icon, @color
        |
        */
        $this->index_statistic = array();


        /*
        | ----------------------------------------------------------------------
        | Add javascript at body
        | ----------------------------------------------------------------------
        | javascript code in the variable
        | $this->script_js = "function() { ... }";
        |
        */
        $js = '';
        if (Request::segment(3) == '') {
            $js .= '
                $("#table_dashboard").find("thead tr th:nth-child(7)").attr("width","150px");
                $("#table_dashboard").find("thead tr th:nth-child(4)").attr("width","400px");
            ';
        }
        $this->script_js = '' . $js;


        /*
        | ----------------------------------------------------------------------
        | Include HTML Code before index table
        | ----------------------------------------------------------------------
        | html code to display it before index table
        | $this->pre_index_html = "<p>test</p>";
        |
        */
        $this->pre_index_html = null;


        /*
        | ----------------------------------------------------------------------
        | Include HTML Code after index table
        | ----------------------------------------------------------------------
        | html code to display it after index table
        | $this->post_index_html = "<p>test</p>";
        |
        */
        $this->post_index_html = null;


        /*
        | ----------------------------------------------------------------------
        | Include Javascript File
        | ----------------------------------------------------------------------
        | URL of your javascript each array
        | $this->load_js[] = asset("myfile.js");
        |
        */
        $this->load_js = array();


        /*
        | ----------------------------------------------------------------------
        | Add css style at body
        | ----------------------------------------------------------------------
        | css code in the variable
        | $this->style_css = ".style{....}";
        |
        */
        $this->style_css = '
            .badge {
                padding: 1px 9px 2px;
                font-size: 12.025px;
                font-weight: bold;
                white-space: nowrap;
                color: #ffffff;
                background-color: #999999;
                -webkit-border-radius: 9px;
                -moz-border-radius: 9px;
                border-radius: 9px;
            }
            .badge:hover {
                color: #ffffff;
                text-decoration: none;
                cursor: pointer;
            }
            .badge-error {
                background-color: #b94a48;
            }
            .badge-error:hover {
                background-color: #953b39;
            }
            .badge-warning {
                background-color: #f89406;
            }
            .badge-warning:hover {
                background-color: #c67605;
            }
            .badge-success {
                background-color: #468847;
            }
            .badge-success:hover {
                background-color: #356635;
            }
            .badge-info {
                background-color: #3a87ad;
            }
            .badge-info:hover {
                background-color: #2d6987;
            }
            .badge-inverse {
                background-color: #333333;
            }
            .badge-inverse:hover {
                background-color: #1a1a1a;
            }
        ';


        /*
        | ----------------------------------------------------------------------
        | Include css File
        | ----------------------------------------------------------------------
        | URL of your css each array
        | $this->load_css[] = asset("myfile.css");
        |
        */
        $this->load_css = array();


    }


    /*
    | ----------------------------------------------------------------------
    | Hook for button selected
    | ----------------------------------------------------------------------
    | @id_selected = the id selected
    | @button_name = the name of button
    |
    */
    public function actionButtonSelected($id_selected, $button_name)
    {
        //Your code here

    }


    /*
    | ----------------------------------------------------------------------
    | Hook for manipulate query of index result
    | ----------------------------------------------------------------------
    | @query = current sql query
    |
    */
    public function hook_query_index(&$query)
    {
        //Your code here

    }

    /*
    | ----------------------------------------------------------------------
    | Hook for manipulate row of index table html
    | ----------------------------------------------------------------------
    |
    */
    public function hook_row_index($column_index, &$column_value)
    {
        //Your code here
    }

    /*
    | ----------------------------------------------------------------------
    | Hook for manipulate data input before add data is execute
    | ----------------------------------------------------------------------
    | @arr
    |
    */
    public function hook_before_add(&$postdata)
    {
        //Your code here

    }

    /*
    | ----------------------------------------------------------------------
    | Hook for execute command after add public static function called
    | ----------------------------------------------------------------------
    | @id = last insert id
    |
    */
    public function hook_after_add($id)
    {
        //Your code here

    }

    /*
    | ----------------------------------------------------------------------
    | Hook for manipulate data input before update data is execute
    | ----------------------------------------------------------------------
    | @postdata = input post data
    | @id       = current id
    |
    */
    public function hook_before_edit(&$postdata, $id)
    {
        //Your code here

    }

    /*
    | ----------------------------------------------------------------------
    | Hook for execute command after edit public static function called
    | ----------------------------------------------------------------------
    | @id       = current id
    |
    */
    public function hook_after_edit($id)
    {
        //Your code here

    }

    /*
    | ----------------------------------------------------------------------
    | Hook for execute command before delete public static function called
    | ----------------------------------------------------------------------
    | @id       = current id
    |
    */
    public function hook_before_delete($id)
    {
        //Your code here

    }

    /*
    | ----------------------------------------------------------------------
    | Hook for execute command after delete public static function called
    | ----------------------------------------------------------------------
    | @id       = current id
    |
    */
    public function hook_after_delete($id)
    {
        //Your code here

    }


    //By the way, you can still create your own method in here... :)


}