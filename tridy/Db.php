<?php


class Db {

	// Databázové spojení
    private static $spojeni;

	// Výchozí nastavení ovladače
    private static $nastaveni = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
		PDO::ATTR_EMULATE_PREPARES => false,
	);

	// Připojí se k databázi pomocí daných údajů
    public static function pripoj($host, $uzivatel, $heslo, $databaze) 
    {
		if (!isset(self::$spojeni)) {
			self::$spojeni = @new PDO(
				"mysql:host=$host;dbname=$databaze",
				$uzivatel,
				$heslo,
				self::$nastaveni
			);
		}
	}
	
	public static function close() 
    {
		self::$spojeni = false;
    }
    
/*  
$data = $query->fetch(PDO::FETCH_BOTH);   // vrátí jak indexované, tak asociativní pole (defaultně)
$data = $query->fetch(PDO::FETCH_NUM);    // vrátí pouze indexované pole
$data = $query->fetch(PDO::FETCH_ASSOC);  // vrátí pouze asociativní pole
$data = $query->fetch(PDO::FETCH_OBJ);    // vrátí objekt třídy stdClass
*/
	// Spustí dotaz a vrátí z něj první řádek
    public static function queryRow($dotaz, $parametry = array()) 
    {
		try
		{
		$navrat = self::$spojeni->prepare($dotaz);
		$navrat->execute($parametry);
		return $navrat->fetch(PDO::FETCH_ASSOC);
		}
	    catch(PDOException $e)
		{
			if(__DEBUG__==1)
			{
		      handle_sql_errors("dotaz: ".$dotaz.", parametry: ".$parametry, $e->getMessage());
		    }

		}
		
	}

	// Spustí dotaz a vrátí všechny jeho řádky jako pole asociativních polí
    public static function queryAll($dotaz, $parametry = array()) 
    {
		try
		{ 
		$navrat = self::$spojeni->prepare($dotaz);
		$navrat->execute($parametry);
		return $navrat->fetchAll(PDO::FETCH_ASSOC);
		}
	    catch(PDOException $e)
		{
		    if(__DEBUG__==1)
			{
				handle_sql_errors("dotaz: ".$dotaz.", parametry: ".$parametry, $e->getMessage());
			}

		}
	}

	// Spustí dotaz a vrátí z něj první sloupec prvního řádku
    public static function queryColumn($dotaz, $parametry = array()) 
    {
		try
		{
		$vysledek = self::queryRow($dotaz, $parametry);
		return $vysledek[0];
		}
	    catch(PDOException $e)
		{
		    //handle_sql_errors("dotaz: ".$dotaz.", parametry: ".$parametry, $e->getMessage());

		}
	}

	// Spustí dotaz a vrátí počet ovlivněných řádků
	public static function queryAffected($dotaz, $parametry = array()) 
	{
		try
		{
		$navrat = self::$spojeni->prepare($dotaz);
		$navrat->execute($parametry);
		return $navrat->rowCount();
		}
	    catch(PDOException $e)
		{
		    if(__DEBUG__==1)
			{
				handle_sql_errors("dotaz: ".$dotaz.", parametry: ".$parametry, $e->getMessage());
			}

		}
	}
	


	// Změní řádek v tabulce tak, aby obsahoval data z asociativního pole
	public static function updateS($dotaz,$parametry = array()) {
		$navrat = self::$spojeni->prepare($dotaz);
		$navrat->execute($parametry) or die(print_r($navrat->errorInfo(), true));
		return $navrat->rowCount();
	}
	
	// vloží nový řádek a vrátí jeho ID
	public static function insertRetId($tabulka, $parametry = array()) {
		$dotaz = "INSERT INTO `$tabulka` (`".
		implode('`, `', array_keys($parametry)).
		"`) VALUES (".str_repeat('?,', sizeOf($parametry)-1)."?)";	
		$navrat = self::$spojeni->prepare($dotaz);
		$navrat->execute($parametry) or die(print_r($navrat->errorInfo(), true));
		return self::$spojeni->lastInsertId();
	}
		

	// Změní řádek v tabulce tak, aby obsahoval data z asociativního pole
	public static function update2($tabulka, $hodnoty = array(), $podminka, $parametry = array()) {
		return self::dotaz("UPDATE `$tabulka` SET `".
		implode('` = ?, `', array_keys($hodnoty)).
		"` = ? " . $podminka,
		array_merge(array_values($hodnoty), $parametry));
	}

	// Vrací ID posledně vloženého záznamu
	public static function getLastId()
	{
		return self::$spojeni->lastInsertId();
	}
	
	// vrátí počet
	public static function queryCount($table, $column= 'id')
    {
        $navrat = self::$spojeni->prepare("SELECT $column FROM $table");
		$navrat->execute($parametry) or die(print_r($navrat->errorInfo(), true));
        return $navrat->rowCount();
    }
    
    // vrátí počet, varianta s podmínkou
    public static function queryCount2($table, $column= 'id', $join, $podminka)
    {
		$parametry = array();
		
        $navrat = self::$spojeni->prepare("SELECT $column FROM $table $join WHERE $podminka ");
		$navrat->execute($parametry) or die(print_r($navrat->errorInfo(), true));
        return $navrat->rowCount();
    }
    
    // insert, vrací id uloženého záznamu
    public static function insert($table, $data)
    {
        ksort($data);
        $fieldNames = implode(',', array_keys($data));
        $fieldValues = ':'.implode(', :', array_keys($data));
        try
		{ 
	        $navrat = self::$spojeni->prepare("INSERT INTO $table ($fieldNames) VALUES ($fieldValues)");
	        foreach ($data as $key => $value) 
	        {
	            $navrat->bindValue(":$key", $value);
	        }
	        //$navrat->execute() or die(print_r($navrat->errorInfo(), true));
	        $navrat->execute();
	    }
	    catch(PDOException $e)
		{
		    if(__DEBUG__==1)
			{
				handle_sql_errors("INSERT INTO $table ($fieldNames) VALUES ($fieldValues)", $e->getMessage());
			}

		}

        return self::$spojeni->lastInsertId();
    }
    
  
 
  
    
    // update
    public static function update($table, $data, $where)
    {
        ksort($data);
        $fieldDetails = null;
        foreach ($data as $key => $value) 
        {
            $fieldDetails .= "$key = :$key,";
        }
        $fieldDetails = rtrim($fieldDetails, ',');
        $whereDetails = null;
        $i = 0;
        foreach ($where as $key => $value) 
        {
            if ($i == 0) {
                $whereDetails .= "$key = :$key";
            } else {
                $whereDetails .= " AND $key = :$key";
            }
            $i++;
        }
        $whereDetails = ltrim($whereDetails, ' AND ');
        
        try
		{
	        $navrat = self::$spojeni->prepare("UPDATE $table SET $fieldDetails WHERE $whereDetails");
	        foreach ($data as $key => $value) {
	            $navrat->bindValue(":$key", $value);
	        }
	        foreach ($where as $key => $value) {
	            $navrat->bindValue(":$key", $value);
	        }
	        //$navrat->execute() or die(print_r($navrat->errorInfo(), true));
	        $navrat->execute();
        }
	    catch(PDOException $e)
		{
		    if(__DEBUG__==1)
			{
				handle_sql_errors("INSERT INTO $table ($fieldNames) VALUES ($fieldValues)", $e->getMessage());
			}

		}
        return $navrat->rowCount();
    }
    
    
    
    // delete vrací počet smazaných řádků
    public static function delete($table, $where, $limit=false)
    {
        ksort($where);
        $whereDetails = null;
        $i = 0;
        $uselimit = "";
        foreach ($where as $key => $value) 
        {
            if ($i == 0) {
                $whereDetails .= "$key = :$key";
            } else {
                $whereDetails .= " AND $key = :$key";
            }
            $i++;
        }
        $whereDetails = ltrim($whereDetails, ' AND ');
        
        //if limit is a number use a limit on the query
        if (is_numeric($limit)) {
            $uselimit = "LIMIT $limit";
        }
        $navrat = self::$spojeni->prepare("DELETE FROM $table WHERE $whereDetails $uselimit");
        foreach ($where as $key => $value) 
        {
            $navrat->bindValue(":$key", $value);
        }
        $navrat->execute() or die(print_r($navrat->errorInfo(), true));
        return $navrat->rowCount();
    }
    
    
    
     // delete hromadná no limit, vrací počet smazaných řádků
    public static function deleteAll($table, $where)
    {

        $navrat = self::$spojeni->prepare("DELETE FROM $table WHERE $where ");
        $navrat->execute() or die(print_r($navrat->errorInfo(), true));
        return $navrat->rowCount();
    }
    
    
    public static function truncate($table)
    {

        $navrat = self::$spojeni->prepare("TRUNCATE $table");
        $navrat->execute() or die(print_r($navrat->errorInfo(), true));
        return $navrat->rowCount();
    }
    
    
    
    // lock write table
    public static function lockWrite($table)
    {

        $navrat = self::$spojeni->prepare("LOCK TABLES $table WRITE");
        $navrat->execute() or die(print_r($navrat->errorInfo(), true));
        return $navrat;
    }
    
    public static function lockRead($table)
    {

        $navrat = self::$spojeni->prepare("LOCK TABLES $table READ");
        $navrat->execute() or die(print_r($navrat->errorInfo(), true));
        return $navrat;
    }
    
    
    // UNlock write table
    public static function unlockWrite($table)
    {

        $navrat = self::$spojeni->prepare("UNLOCK TABLES");
        $navrat->execute() or die(print_r($navrat->errorInfo(), true));
        return $navrat;
    }

}
