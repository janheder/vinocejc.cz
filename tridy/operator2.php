<?php

class operator {

    private $table = 'posta2';

    public function search($mesto, $psc) {
        mysql_query("SET NAMES 'utf-8'");
        if ($psc != '') {
            $psc = intval(str_replace(' ', '', $psc));
            $result = mysql_query("SELECT * FROM `" . $this->table . "` WHERE `PSC` = '" . $psc . "'");
        } else {
            if ($mesto != '') {
//                $result = mysql_query("SELECT * FROM `" . $this->table . "` WHERE NAZ_PROV = '" . $mesto . "'");
//                $foo = mysql_fetch_assoc($result);
//                if ($foo == false) {
//                    $result = mysql_query("SELECT * FROM `" . $this->table . "` WHERE NAZ_PROV LIKE '" . $mesto . "%'");
//                }
//                else
//                    return array($foo);
                $result = mysql_query("SELECT * FROM `" . $this->table . "` WHERE NAZ_PROV LIKE '" . addslashes($mesto) . "%'");
            }else
                return array();
        }

        $out = array();
        while ($x = mysql_fetch_assoc($result)) {
            $out[] = $x;
        }

        return $out;
    }

    public function getData($id) {
        mysql_query("SET NAMES 'utf-8'");
        $result = mysql_query("SELECT * FROM `" . $this->table . "` WHERE `ID` = " . intval($id));
        return mysql_fetch_assoc($result);
    }

    public function getTags() {
        $result = mysql_query("SELECT NAZ_PROV FROM " . $this->table);
        $out = "";
        while ($x = mysql_fetch_assoc($result)) {
            $out .= '"' . $x['NAZ_PROV'] . '",';
        }
        $out = substr($out, 0, -1);
        return $out;
    }

}
