<?php

    /**********************************************************************/
    /*                              GLOBAL VARS                           */
    /**********************************************************************/
    
    define('GLOBALS', populatGlobalsArray());

    // GLOBAL array contains all the global variables
    function populatGlobalsArray()
    {
        $sql["code"] = "SELECT * FROM globals";
        $globals = runSql($sql); // [0] => [attr => value]
        $globalsFormatted = array(); // attr => value || admin => blah@mail.com
        
        foreach($globals as $variable)
            $globalsFormatted[$variable["attr"]] = $variable["value"];
        
        return $globalsFormatted;
    }

    /**********************************************************************/
    /*                         DISPLAY GENERIC PAGE
    /**********************************************************************/

    // displayPage ("displayLogin");
    // displayPage ("displayLogin", array("paypal.js", "stripe.js"));
    // the $funcitonToDisplay variable is the name of the function from the specific php file to display
    function displayPage ($funcitonToDisplay, $extraScriptsNames=array())
    {
        global $CURRENT_PAGE_NAME;
        
        // generate the style and scripts for the specific page
        $pageSpecificStyle = '<link rel="stylesheet" href="/css/' . $CURRENT_PAGE_NAME . '.css">';
        $pageSpecificScript = '<script src="/js/' . $CURRENT_PAGE_NAME . '.js"></script>';
        
        $extraScriptsHtml = "";
        if(!empty($extraScriptsNames))
        {
            foreach ($extraScriptsNames as $script)
                $extraScriptsHtml .= '<script src="/js/' . $script . '"></script>' . "\n";
        }
        
        // generate the title for the page (same as the php file name - the .php)
        $title = $CURRENT_PAGE_NAME;

        
        // Display the page
        ?>
        <!DOCTYPE html>
        <html lang="en">  
            <head>
                <meta charset="UTF-8">
                <title><?=$title?></title>
                <link rel="stylesheet" href="/css/global.css">
                <?=$pageSpecificStyle?>
                <script src="/js/global.js"></script>
                <?=$pageSpecificScript?>
                <?=$extraScriptsHtml?>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="shortcut icon" href="" type="image/x-icon">
            </head>

            <body>
                <?php displayHeader(); ?>
                <div class="content_wrapper">
                    <div class="content">
                            <?php call_user_func($funcitonToDisplay) ?>
                    </div>
                </div>       
            </body>
        </html>
        <?php
    }


    // HELPER for displayPage()
    function displayHeader()
    {
        global $CURRENT_PAGE_NAME;
        
        // the names of the php files for links in header without extension
        $headerLinks = headerLinkNamesGenerator();
        
        // convert the links to html (give the current one its active class)
        foreach ($headerLinks as $key => $link)
        {
            if ($link == $CURRENT_PAGE_NAME)
                $headerLinks[$key] = '<a href="/' . $link . '.php" class="currHeaderItem">' . $link . '</a>';
            else
                $headerLinks[$key] = '<a href="/' . $link . '.php">' . $link . '</a>';
        }
        
        // convert $headerLinks into a string
        $headerLinks = implode("\n", $headerLinks);
        
        // now that the links are set up, we need the HTML for the other parts of the header, such as the logo, parent divs etc.
        $blockCotnent = 
            '<img id="hamburger" class="hidden" src="/img/hamburger.png">' . 
            '<div id="main-navigation">' . $headerLinks . '</div>';
        
        // display the header
        $headerRowInfo = 
        [
            "row" => ["id" => "header"],
            "rowColumn" => [ [] ],
            "blockContent" => [$blockCotnent]
        ];
        
        createRow($headerRowInfo);
    }

    // HELPER for displayHeader()
    // Generates a list of links that need to be displayed in the header based on whether the user is logged in, whether they are the admin etc.
    function headerLinkNamesGenerator()
    {
        // array containing all page names and their cattegory
        $linkNamesArray = 
        [
            'all' => ['home', 'products'],
            'user' => ['orders', 'tickets', 'signout'],
            'admin' => ['setup'],
            'nonuser' => ['login', 'signup']
        ];
        
        // figure out what cattegory of page names is needed
        if(loggedin())
            $linkCategoryNeeded = "user";
        else
            $linkCategoryNeeded = "nonuser";
        
        // add the needed cattegory of page names to the generated array
        if(loggedin() && admin())
            $generatedLinkNamesArray = array_merge($linkNamesArray['all'], $linkNamesArray[$linkCategoryNeeded], $linkNamesArray["admin"]);
        else
            $generatedLinkNamesArray = array_merge($linkNamesArray['all'], $linkNamesArray[$linkCategoryNeeded]);
        
        return $generatedLinkNamesArray;
    }


    /**********************************************************************/
    /*                              DISPLAY ROWS
    /**********************************************************************/


    /*
        $info = 
        [
            "row" =>
            [
                "id" => "blah",
                "extraClasses" => ["one", "two", "three"]
            ],

            "rowColumn" =>
            [
                [
                    "id" => "first",
                    "extraClasses" => ["one", "two", "three"]
                ],
                
                [
                    "id" => "second",
                    "extraClasses" => ["one", "two", "three"]
                ]
            ],

            "blockContent" => 
            [
                $htmlString1,
                $htmlString2
            ]
        ];
    */

    // HELPER for displayHeader and page specific display functions
    // in the rowColumnInfo, there can be multiple columns, or just one
    // $blockContent is an array of strings to put inside of each block (which goes in each rowColumn) in consequtive order
        // it must match the number of rowColumns
    function createRow($info=array())
    {
        // Check if everything matches up
        if (!isset($info["rowColumn"]) || empty($info["rowColumn"]))
            return;
        
        if (!isset($info["blockContent"]) || empty($info["blockContent"]))
            return;
        
        if(sizeof($info["rowColumn"]) != sizeof($info["blockContent"]))
            return;
        
        // setup the extra stuff for the row
        $rowId = "";
        $rowExtraClasses = "";
        
        if (isset($info["row"]["id"]) && !empty($info["row"]["id"]))
            $rowId = 'id="' . $info["row"]["id"] . '"';
        
        if (isset($info["row"]["extraClasses"]) && !empty($info["row"]["extraClasses"]))
            $rowExtraClasses = implode(" ", $info["row"]["extraClasses"]);
        
        
        // setup up the default classes for the rowColumn based on the number of rowColumns OR if a custom rowColumnX is provided
        if(isset($info["rowColumn"][0]["extraClasses"]) && preg_grep("/^rowColumn[0-9]+$/", $info["rowColumn"][0]["extraClasses"]))
            $rowColumnDefaultClasses = "rowColumn";
        else
            $rowColumnDefaultClasses = "rowColumn rowColumn" . sizeof($info["rowColumn"]);
        
        
        // setup the extra stuff for the rowColumn/s
        $rowColumnIds = array();
        $rowColumnExtraClasses = array();
        
        for ($i=0; $i < sizeof($info["rowColumn"]); $i++)
        {
            $rowColumn = $info["rowColumn"][$i];
            
            $rowColumnIds[$i] = "";
            if (isset($rowColumn["id"]) && !empty($rowColumn["id"]))
                $rowColumnIds[$i] =  'id="' . $rowColumn["id"] . '"';

            $rowColumnExtraClasses[$i] = "";
            if (isset($rowColumn["extraClasses"]) && !empty($rowColumn["extraClasses"]))
                $rowColumnExtraClasses[$i] = implode(" ", $rowColumn["extraClasses"]);
        }
        
        
        // display
        echo
            "<div {$rowId} class='row {$rowExtraClasses}'>";
            
        for ($i=0; $i < sizeof($info["rowColumn"]); $i++)
        {
            echo 
                    "<div {$rowColumnIds[$i]} class='{$rowColumnDefaultClasses} {$rowColumnExtraClasses[$i]}'>
                        <div class='block'>
                            {$info["blockContent"][$i]}
                        </div>
                    </div>";
        }
        
        echo 
            "</div>";
    }


    /**********************************************************************/
    /*                                REDIRECT
    /**********************************************************************/

    // If a page requres a user to be loggied in and they aren't, they get redirected to him and vice versa
    function redirect()
    {
        global $USER_TYPE_REQUIREMENT;
        
        if 
        (
            ($USER_TYPE_REQUIREMENT == "user" && !loggedin()) ||
            ($USER_TYPE_REQUIREMENT == "nonuser" && loggedin()) ||
            ($USER_TYPE_REQUIREMENT == "admin" && (!loggedin() || !admin()))
        )
        {
            header("Location: /home.php");
            exit();
        }
    }



    /**********************************************************************/
    /*                                 ERROR                              */
    /**********************************************************************/

    // If an error is encountered, call this function. It will set $_SESSION["error"], exit and redirect
    // error("Unable to login");
    // error("Unable to login", "../first.php");
    function error($errorMessage="")
    {
        if (!empty($errorMessage)) $_SESSION["error"] = $errorMessage;
        if (isset($_SERVER['HTTP_REFERER'])) header('Location: ' . $_SERVER['HTTP_REFERER']);
        else header("Location: /index.php");
        exit();
    }

    // Used to display the error (if there was one) in the specific page
    // it is displayed right at the top below the header - always
    function getError()
    {
        // Since the error row will be displayed anyway, hide it if there is no error
        if(isset($_SESSION["error"]))
        {
            $error = "<p class='error'>" . $_SESSION["error"] . "</p>";
            unset($_SESSION["error"]);
            return $error;
        }
        else
            return "";
    }


    /**********************************************************************/
    /*                       BASICS CHECKS FOR PAGES                      */
    /**********************************************************************/

    function loggedin()
    {
        return isset($_SESSION["user"]);
    }

    function admin()
    {
        return $_SESSION["user"] == GLOBALS["admin"];
    }

    function form()
    {
        return isset($_POST["form-submitted"]);
    }


    /**********************************************************************/
    /*                                 OTHER                              */
    /**********************************************************************/

    // Get the maximum characters allowed in a SQL table's column VARCHAR(50)
    function getMaxCharsSqlTableCol($tableName, $columnName)
    {
        $sql["code"] = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ? AND COLUMN_NAME = ?;";
        $sql["params"] = array($tableName, $columnName);
        $response = runSql($sql);
        return $response[0]["CHARACTER_MAXIMUM_LENGTH"];
    }

    function getUseridFromEmail($email)
    {
        $sql["code"] = "SELECT userid FROM users WHERE email=?";
        $sql["params"] = array($email);
        $response = runSql($sql);
        return $response[0]["userid"];
    }

    function getNameFromEmail($email)
    {
        $sql["code"] = "SELECT firstname FROM users WHERE email=?";
        $sql["params"] = array($email);
        $response = runSql($sql);
        return $response[0]["firstname"];
    }

    function getEmailFromUserid($userid)
    {
        $sql["code"] = "SELECT email FROM users WHERE userid=?";
        $sql["params"] = array($userid);
        $response = runSql($sql);
        return $response[0]["email"];
    }

    function getProductInfo($productid)
    {
        $sql["code"] = "SELECT * FROM products WHERE productid=?";
        $sql["params"] = array($productid);
        return runSql($sql);
    }

?>