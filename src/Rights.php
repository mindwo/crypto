<?php
namespace mindwo\pages; 

use DB;
use Auth;

class Rights
{ 
    public static function getIsEditRightsOnItem($list_id, $item_id)
    {
        if ($item_id == 0)
        {
            return false; // item is not editable because it is not created/saved jet
        }

        $workflow_row = DB::table("dx_workflows")->where('list_id', '=', $list_id)->orderBy('step_nr')->first();

        if ($workflow_row)
        {
            // Workflow is defined
            $cur_task = DB::table("dx_tasks")->where('item_id','=',$item_id)->whereNull('task_closed_time')->first();

            if ($cur_task)
            {
                if ($cur_task->task_type_id == 3 && Auth::user()->id == $cur_task->task_employee_id)
                {
                    return true; // workflow is running and current user have doc edit task
                }
                else
                {
                    return false; // workflow is running and current uses does not have edit task                        
                }
            }
            else
            {
                // get list db table name
               $list_row = DB::table("dx_lists")->where("id", "=", $list_id)->first();
               $obj_row = DB::table("dx_objects")->where("id", "=", $list_row->object_id)->first();

               $item_data = DB::table($obj_row->db_name)->where("id","=", $item_id)->where("dx_item_status_id","=",4)->first();

               if ($item_data)
               {
                   return false; // doc is approved, we cant edit approved documents
               }
               else
               {
                   return true;
               }
            }
        }
        else
        {
            return true; // workflow not defined
        }
    }

    public static function getRightsOnList($list_id)
    {
        $rez = null;

        $sql = "
        select
            *
        from
        (
        select 
            max(rl.is_delete_rights) as is_delete_rights,
            max(rl.is_edit_rights) as is_edit_rights,
            max(rl.is_new_rights) as is_new_rights
        from 
            dx_users_roles ur 
            inner join dx_roles_lists rl on ur.role_id = rl.role_id 
        where 
            ur.user_id = :user_id
            AND rl.list_id = :list_id
        ) tb
        where
            tb.is_delete_rights is not null
        limit 0, 1
        ";

        $rights = DB::select($sql, array('user_id' => Auth::user()->id, 'list_id' => $list_id));

        if (count($rights) > 0)
        {
            $rez = $rights[0];
        }

        return $rez;
    }

    public static function getRightsOnPage($page_id)
    {
        $rez = null;

        $sql = "
        select 
            1 as is_right
        from 
            dx_users_roles ur 
            inner join dx_roles_pages rp on ur.role_id = rp.role_id 
        where 
            ur.user_id = :user_id
            AND rp.page_id = :page_id
        limit 0,1
        ";

        $rights = DB::select($sql, array('user_id' => Auth::user()->id, 'page_id' => $page_id));

        if (count($rights) > 0)
        {

            $rez = $rights[0];
        }

        return $rez;
    }  

    /**
     * Nosaka lietotāja tiesības atbilstoši datu avotam. Ja lietotājam nav norādīts neviens datu avots, tad tiesības uz visiem datu avotiem.
     * Ja reģistrā ir definēta kolonna "Datu avots", tad lietotājam pieejami tikai ieraksti atbilstoši lietotāja datu avotam
     * 
     * @param integer $list_id      Reģistra ID, kurā jāpārbauda tiesības uz datu avotu
     * @param string $table_name    Tabulas nosaukums, kurā glabājas reģistra dati
     * @return string SQL WHERE nosacījums atticībā uz datu avotu
     */
    public static function getSQLSourceRights($list_id, $table_name) {

        if (!Auth::user()->source_id) {
            return; // lietotājam piekļuve visiem datu avotiem
        }

        $fld_row = DB::table('dx_lists_fields')
                    ->where('list_id', '=', $list_id)
                    ->where('db_name','=','source_id')
                    ->first();

        $source_where = " ";

        if ($fld_row) {
            $source_where = " AND " . $table_name . ".source_id = " . Auth::user()->source_id . " ";
        }


        if ($table_name == "in_sources") {
            $source_where = " AND " . $table_name . ".id = " . Auth::user()->source_id . " ";
        }

        return $source_where;
    }
}
