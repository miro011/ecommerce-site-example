<?php

    // MAIN
    // retrieves tickets or orders, converts it to html and also creates pagination
    function retrieveOrdersOrTickets($table)
    {
        // SETUP INITIAL THINGS
        $resultsPerPage = getNumberOfResultsPerPage();
        $currPage = getCurPage();
        $orderByColumn = getOrderByColumnName($table);
        
        
        // GENERATE THE SQL
        $sql = getGenericSql($table);
        $countSql = $sql;
        $countSql["code"] = str_replace('SELECT *', "SELECT count(*)", $countSql["code"]);
        // adjust the SELECT for the SQL and add the ORDER BY
        $sql["code"] = sqlSelectionAdjuster($sql["code"], $table);
        $sql["code"] .= " ORDER BY date DESC, {$orderByColumn} DESC"; //$sql["code"] .= " ORDER BY {$orderByColumn} DESC";
        
        
        // INITATE PAGINATION AND APPLY LIMITS TO THE SQL
        $numPages = paginationSetNumPages($resultsPerPage, $countSql);
        $limits = paginationSetLimitsArray($resultsPerPage, $numPages);
        // if the page requested is higher than the max page, set page to 1
        if (!isset($limits[$currPage])) $currPage = 1;
        // add limits to the $sql
        $sql["code"] .= " LIMIT {$limits[$currPage]["start"]},{$limits[$currPage]["rowcount"]}";
        
        
        // RETRIEVE ORDERS OR TICKETS, FIX EMAILS & GENERATE PAGINATION HTML
        $response = runSql($sql);
        $response = changeUseridResultsToEmails($response);
        $response = convertResponseToHtml($response);
        $paginationHtml = getPaginationHtml($currPage, $numPages, $table);
        
        
        // RETURN THE RESULTING ARRAY
        $result = array();
        $result["dataHtml"] = $response;
        $result["paginationHtml"] = $paginationHtml;
        return $result;
    }

    // Get the number of results based on whether this is the home page or the specific orders/tickets page
    function getNumberOfResultsPerPage()
    {
        global $CURRENT_PAGE_NAME;
        if ($CURRENT_PAGE_NAME == "home") return 3;
        else if ($CURRENT_PAGE_NAME == "orders") return 18;
        else return 14;
    }

    // Set the page - either 1 or whatever the current page is based on $_GET (unset for future loop to work properly)
    function getCurPage()
    {
        $currPage = 1;
        
        if(isset($_GET["page"]))
        {
            $currPage = $_GET["page"];
            unset($_GET["page"]); 
        }
        
        return $currPage;
    }
    
    // order by the PK of the table you're diplsying for || tickets => ticketid || orders => orderid
    function getOrderByColumnName($table)
    {
        return substr($table, 0, -1) . "id";
    }

    /*▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽*/

    // HELPER MAIN
    // get the generic SQL - SELECT * FROM table WHERE id=2;
    // also covers admin searches
    function getGenericSql($table)
    {
        $sql = array();
        $sql["code"] = "";
        $sql["params"] = array();
        
        if(adminIsSearchingForSomething())
        {
            searchingForEmailFix();
            $sql["code"] = "SELECT * FROM {$table} WHERE";
            $counter = 1; // used for adding "AND clasues to each condition"
            
            foreach ($_GET as $key => $value)
            {
                // add an AND clause after firt iteration
                if ($counter > 1)
                    $sql["code"] .= " AND";
                
                // trim the thing being searched for just in case
                $value = trim($value);
                
                // add to sql
                $sql["code"] .= " {$key}=?";
                $sql["params"][] = $value;

                $counter += 1;
            }
        }
        // admin and not searching for anything
        else if (admin())
        {
            $sql["code"] = "SELECT * FROM {$table}"; // show all orders or tickets
        }
        // regular user
        else
        {
            $userid = getUseridFromEmail($_SESSION["user"]);
            $sql["code"] .= "SELECT * FROM {$table} WHERE userid=?";
            $sql["params"][] = $userid;
        }
        
        return $sql;
    }

    // return true if admin searching for something based on the count of $_GET
    function adminIsSearchingForSomething()
    {
        return admin() && count($_GET) > 0;
        // get is greater than zero not 1 because by the time this is called $_GET["page"] has been unset
    }

    // if admin is searching for for a user email, convert it to id, so you can find it in orders or tickets table
    function searchingForEmailFix()
    {
        if(isset($_GET["email"]))
        {
            $_GET["userid"] = getUseridFromEmail($_GET["email"]);
            unset($_GET["email"]);
        }
    }

    /*△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△*/

    // Selects the exact columns needed based on user type and table in question
    function sqlSelectionAdjuster($sql, $table)
    {
        // all columns are needed when displaying for admins, so all we care about are non admins
        if (!admin())
        {
            if ($table == "orders")
                $sql = str_replace('SELECT *', "SELECT orderid, date, product, quantity, total, status", $sql);
            else if ($table == "tickets")
                $sql = str_replace('SELECT *', "SELECT ticketid, date, title, msgs, status", $sql);
        }
        
        return $sql;
    }

    // Set the number of pages needed
    function paginationSetNumPages($resultsPerPage, $countSql)
    {
        // Get the number of results from the SQL provided
        $numResults = runSql($countSql);
        $numResults = $numResults[0]["count(*)"];
        
        $numPages = $numResults / $resultsPerPage;
        // This right here is saying that if $numPages is not a whole number add 1 and remove the decimal
        // 2.21 => 3.21 => 3
        if (floor($numPages) != $numPages || $numPages == 0)
            $numPages = $numPages + 1;
        
        $numPages = (int)$numPages;
        
        return $numPages;
    }

    // Set the limits array - used for sql LIMIT => [page][start] || [page][rowcount]
    function paginationSetLimitsArray($resultsPerPage, $numPages)
    {
        // MYSQL LIMIT -> LIMIT offeset,rowcount (offset is where you start, rowcount is the # of rows) - offsets are 0 indexed
        $arr = array();
        $start = 0;
        $rowCount = $resultsPerPage;
        
        for($i=0; $i<$numPages; $i++)
        {
            $arr[$i+1]["start"] = $start;
            $arr[$i+1]["rowcount"] = $rowCount;
            
            // set up for next iteration
            $start = $start + $rowCount;
        }
        
        return $arr;
    }

    // change the userids from the result to emails if admin
    function changeUseridResultsToEmails($response)
    {
        if (admin() && $response != false)
            for($i=0; $i<sizeof($response); $i++)
                $response[$i]["userid"] = getEmailFromUserid($response[$i]["userid"]);
        
        return $response;
    }

    /*▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽*/
    
    // HELPER MAIN
    // generate the html for pagination ($forPage is the same as the table name - "orders", "tickets")
    function getPaginationHtml($currPage, $numPages, $forPage)
    {
        $pagesNumbersArray = getPageNumbersArray($currPage, $numPages);
        $basePageHref = getBasePageHref($forPage);
        $pageHtmlArray = getPageHtmlArray($currPage, $pagesNumbersArray, $basePageHref);
        
        // convert the arrray back into a string
        $pagesHTMLString = implode("\n", $pageHtmlArray);
        
        return $pagesHTMLString;
    }

    // create the page numbers -  1 .. 49 50 [51] 52 53 .. 100 <<>> 1 2 3 [4] 5 6 .. 100
    function getPageNumbersArray($currPage, $numPages)
    {
        // MAX NUMBER OF NEIGHBORS ON EACH SIDE OF THE CURRENT PAGE
        $maxNeighborsOnEachSide = 2;
        $numNeighborsLeft = min($maxNeighborsOnEachSide, $currPage - 1); // { 1 [2] } => min(2, 1) => 1
        $numNeighborsRight = min($maxNeighborsOnEachSide, $numPages - $currPage); // { [5] 6 } => min(2, 1) => 1
        
        
        // GENERATE STRING WITH PAGE NUMBERS
        $pagesString = " ";
        
        // left
        for($i=$numNeighborsLeft; $i>0; $i--)
        {
            $pageNum = $currPage - $i;
            $pagesString .= "{$pageNum} ";
        }
        
        // add current page
        $pagesString .= "{$currPage} ";
        
        // right
        for($i=1; $i<=$numNeighborsRight; $i++)
        {
            $pageNum = $currPage + $i;
            $pagesString .= "{$pageNum} ";
        }
        
        // add the page 1 and last page (in case they weren't added above => 54 55 [56] 57 58)
        // if they weren't added, it means that the current page is too far away from either, so we will also add the dots
        // 54 55 [56] 57 58 becomes => 1 .. 54 55 [56] 57 58 .. 100
        if (strpos($pagesString, " 1 ") === false)
        {
            // prevent this: 1 .. 2 3 [4]
            if($currPage - $numNeighborsLeft == 2)
                $pagesString = "1" . $pagesString;
            else
                $pagesString = "1 .." . $pagesString;
        }
        
        if (strpos($pagesString, "{$numPages} ") === false)
        {
            // prevent this: [1] 2 3 .. 4
            if($currPage + $numNeighborsRight == $numPages - 1)
                $pagesString .= "{$numPages}";
            else
                $pagesString .= ".. {$numPages}";
        }
        
        // trim the pages tring because there could be a space after the last page
        $pagesString = trim($pagesString);
        
        // CONVERT STRING TO ARRAY AND RETURN IT
        return explode(" ", $pagesString);
    }

    // returns the base href for the pages. Page numbers not added - ends in page=
    function getBasePageHref($forPage)
    {
        $href = "/{$forPage}.php?";
        
        // before adding page= , add any other GET filters that may be present when searching for stuff
        if (count($_GET) > 0)
            foreach ($_GET as $key => $value)
                if ($key != "page")
                    $href .= "{$key}={$value}&";
        
        $href .= "page=";
        
        return $href;
    }

    function getPageHtmlArray($currPage, $pagesNumbersArray, $basePageHref)
    {
        // convert $currPage for the upcoming comparisons
        $currPage = "{$currPage}";
        $pageHtmlArray = array();
        
        foreach ($pagesNumbersArray as $pageNumber)
        {
            $href = $basePageHref . $pageNumber;
            
            if($pageNumber == $currPage)
                $pageHtmlArray[] = "<li class='paginationCurrent'><a href='#'>{$pageNumber}</a></li>";
            else if($pageNumber == "..")
                $pageHtmlArray[] = "<li class='paginationDots'>..</li>";
            else
                $pageHtmlArray[] = "<li><a href='{$href}'>{$pageNumber}</a></li>";
        }
        
        return $pageHtmlArray;
    }

    /*△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△*/

    /*▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽▽*/

    // HELPER MAIN
    // Converts the sql response containing all the orders/tickets info into html
    function convertResponseToHtml($response)
    {        
        if (empty($response))
        {
            $html = "<p>No data</p>";
        }
        else
        {
            $html = "<table style='width:100%; text-align: center;'>";
            $html .= tableHeaderHtml($response);
            $html .= tableDataHtml($response);
            $html .= "</table>";
        }
        
        return $html;
    }

    function tableHeaderHtml($response)
    {
        $tableHeaderHtml = "<tr>";
        
        foreach ($response[0] as $columnName => $value)
            $tableHeaderHtml .= "<th>{$columnName}</th>";
        
        $tableHeaderHtml .= "</tr>";
        
        // replace the "userid" column name, which now actually has emails with "email"
        $tableHeaderHtml = str_replace("<th>userid</th>", "<th>email</th>", $tableHeaderHtml);
        // replace orderid or ticketid column names with just "id"
        $tableHeaderHtml = str_replace("<th>orderid</th>", "<th>id</th>", $tableHeaderHtml);
        $tableHeaderHtml = str_replace("<th>ticketid</th>", "<th>id</th>", $tableHeaderHtml);
        
        return $tableHeaderHtml;
    }

    // converts the orders/tickets data into html
    function tableDataHtml($response)
    {
        $tableDataHtml = "";
        
        foreach ($response as $row)
        {
            $tableDataHtml .= "<tr>";
            
            foreach ($row as $columnName => $value)
            {
                if ($columnName == "msgs") // messages from tickets are stored in the form of an array in the table so they need special attention
                    $tableDataHtml .= cellMsgsHtml($value);
                else if ($columnName == "ticketid" || $columnName == "orderid")
                    $tableDataHtml .= "<td class='id'>{$value}</td>";
                else
                    $tableDataHtml .= "<td>{$value}</td>";
            }
            
            $tableDataHtml .= "</tr>";
        }
        
        return $tableDataHtml;
    }

    // Converts the array of msgs into proper html
    function cellMsgsHtml($msgs)
    {
        $msgs = str_replace("&quot;", '"', $msgs);
        $msgs = json_decode($msgs, true);
        
        $html = 
            "<td class='popupCell'>
                <button type='button'>View</button>
                <div class='hidden'>";
        
        // just in case
        foreach ($msgs as $msg)
        {
            if (empty(trim($msg["msg"])))
                continue;
            
            $msg["user"] = getNameFromEmail($msg["user"]);
            $html .= 
                "<div class='singleMessageWrapper'>
                    <p>{$msg["user"]}</p>
                    <p>{$msg["msg"]}</p>
                </div>";
        }

        $html .= 
                "</div>
            </td>";
        
        return $html;
    }

    /*△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△△*/

?>