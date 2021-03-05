<?php

    /*00000000000000000000000000000000000000000000000000000000000000000000*/
    /*                               EXTENSION                            */
    /*00000000000000000000000000000000000000000000000000000000000000000000*/

    define('DBHOST', 'localhost');
    define('DBNAME', 'dbvtfvupc7bfph');
    define('DBUSER', 'uvcetzvzm8jiu');
    define('DBPASS', 'qzfD4PuAd"ryd&wmLgZPYT');
    define('DBCONNSTRING',"mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8mb4;");


    // establish connection to database    
    $connection = eastablishConnectionToSql(DBCONNSTRING, DBUSER, DBPASS);

    function eastablishConnectionToSql ($connString, $user, $password)
    {
        $pdo = new PDO($connString,$user,$password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;      
    }

    // sql["code"] = "select * from table ..."
    // sql["params"] = ["one", "two"] OR "one, two"
    function runSql ($sql) 
    {
        global $connection;
        
        // if $sql is not an array
        if(!is_array($sql))
            return false;
        
        // if no sql command given return false
        if(!isset($sql["code"]) || empty($sql["code"]))
            return false;
        
        // if no params given
        if (!isset($sql["params"]) || empty($sql["params"]))
            $sql["params"] = array();
        
        // Convert the params to an array if string format
        if (!is_array($sql["params"]) && !empty($sql["params"]))
            $sql["params"] = array_map('trim', explode(',', $sql["params"]));
        
        
        if
        (
            strpos(strtolower($sql["code"]), 'insert into') !== false ||
            strpos(strtolower($sql["code"]), 'delete from') !== false ||
            strpos(strtolower($sql["code"]), 'update') !== false
        )
            return runTransaction($sql);
        else
            return runStandardProcedure($sql);
    }


    function runTransaction ($sql)
    {
        global $connection;
        
        // htmlentities on all params
        foreach($sql["params"] as $key => $param)
            $sql["params"][$key] = htmlentities($param);

        $connection -> beginTransaction();
        
        try
        {
            runStandardProcedure($sql, true);
            $connection -> commit();
        }
        catch (Exception $e)
        {
            $connection -> rollback();
        }
        
        return true;
    }

    function runStandardProcedure($sql, $transaction=false)
    {
        global $connection;
        
        if (sizeof($sql["params"]) > 0) 
        {
            // Use a prepared statement if parameters are present
            $response = $connection->prepare($sql["code"]);
            $executedOk = $response->execute($sql["params"]);
            if (!$executedOk) throw new PDOException;
        }
        
        else 
        {
            // Execute a normal query     
            $response = $connection->query($sql["code"]); 
            if (!$response) throw new PDOException;
        }

        // if the statement contains something convert to array and return it, else return false
        if ($response -> rowCount() && $transaction == false)
            return $response->fetchAll(PDO::FETCH_ASSOC);
        else
            return false;
    }

?>