<?php
class Groups
{

    public static function getAllById($group_id)
    {
        $stmt = DB::run("SELECT * FROM `groups` WHERE group_id=?", [$group_id]);
        return $stmt;
    }

    public static function getAll()
    {
        $stmt = DB::run("SELECT * from `groups` ORDER BY group_id");
        return $stmt;
    }

    public static function getStaff()
    {
        $stmt = DB::run("SELECT `users`.`id`, `users`.`username`, `users`.`class`, `users`.`last_access`
                         FROM `users`
                         INNER JOIN `groups`
                         ON `users`.`class` = `groups`.`group_id`
                         WHERE `users`.`enabled` =? AND `users`.`status` =? AND `groups`.`staff_page` =?
                         ORDER BY `username`",
                        ['yes', 'confirmed', 'yes']);
        return $stmt;
    }

    public static function getStaffLevel($where)
    {
        $row = DB::run("SELECT `group_id`, `level`, `staff_public`
                        FROM `groups`
                        WHERE `staff_page` = 'yes' $where
                        ORDER BY `staff_sort`");
        return $row;
    }

    public static function getGroups()
    {
        $row = DB::run("SELECT group_id, level FROM `groups` ORDER BY group_id ASC"); //  ORDER BY group_id ASC
        return $row;
    }

    public static function getGroupsearch($query, $limit)
    {
        $row = DB::run("SELECT users.*, groups.level FROM users INNER JOIN `groups` ON groups.group_id=users.class WHERE $query ORDER BY username $limit");
        return $row;
    }

    
    // Function That Returns The Group Name
    public static function get_user_class_name($i)
    {
        if ($i == $_SESSION["class"]) {
           return $_SESSION["level"];
        }
        $res = DB::run("SELECT level FROM `groups` WHERE group_id=" . $i . "");
        $row = $res->fetch(PDO::FETCH_LAZY);
        return $row[0];
    }

    // Function To List Groups Of Members Of The Database
    public static function classlist()
    {
        $ret = array();
        $res = DB::run("SELECT * FROM `groups` ORDER BY group_id ASC");
        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
           $ret[] = $row;
        }
        return $ret;
    }
    
    public static function deleteId($group_id)
    {
        DB::run("DELETE FROM `groups` WHERE group_id=?", [$group_id]);
    }
    
}