<?php

class Posta {

    private $table = 'posta';

    public function search($mesto, $psc) {

        if ($psc != '') 
        {
            $psc = intval(str_replace(' ', '', $psc));
            $result = mysqli_query($spojeni,"SELECT * FROM `" . $this->table . "` WHERE `PSC` = '" . $psc . "'");
        } 
        else 
        {
            if ($mesto != '') 
            {

                $result = mysqli_query($spojeni,"SELECT * FROM `" . $this->table . "` WHERE NAZ_PROV LIKE '" . addslashes($mesto) . "%'");
            }
            else
                return array();
        }

        $out = array();
        while ($x = mysqli_fetch_assoc($result)) 
        {
            $out[] = $x;
        }

        return $out;
    }

    public function getData($id) {

        $result = mysqli_query($spojeni,"SELECT * FROM `" . $this->table . "` WHERE `ID` = " . intval($id));
        return mysqli_fetch_assoc($result);
    }

    public function getTags() {
        $result = mysqli_query($spojeni,"SELECT NAZ_PROV FROM " . $this->table);
        $out = "";
        while ($x = mysqli_fetch_assoc($result)) 
        {
            $out .= '"' . $x['NAZ_PROV'] . '",';
        }
        
        $out = substr($out, 0, -1);
        return $out;
    }

}
