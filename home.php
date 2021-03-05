<?php

    /**********************************************************************/
    /*                           RESUME THE SESSION                       
    /**********************************************************************/

    session_start();

    /**********************************************************************/
    /*                    FILE NAME & ACCESS RULE FOR PAGE
    /**********************************************************************/

    $CURRENT_PAGE_NAME = basename(__FILE__, '.php');
    $USER_TYPE_REQUIREMENT = "all"; // used by redirect

    /**********************************************************************/
    /*                              REUIREMENTS                           
    /**********************************************************************/

    require('php/sql-config.php');
    require('php/global.php');
    require('php/retrieve_orders_tickets.php');

    /**********************************************************************/
    /*                                  MAIN                           
    /**********************************************************************/

    redirect();

    if(loggedin())
    {
        if (admin())
            displayPage("homeAdmin", array("home_admin.js"));
        else
            displayPage("homeUser");
    }
    else
    {
        displayPage("homeNonUser");
    }


    /**********************************************************************/
    //                            DISPLAY PAGE
    
    function homeNonUser()
    {
        displaySiteInfoSections();
    }

    function homeUser()
    {
        echo getError();
        displaySiteInfoSections("news");
        accountOverview();
    }

    function homeAdmin()
    {
        displaySiteInfoSections();
        accountOverview();
    }

    /* ......................................... */

    // account overview consists of recent orders and tickets, as well as account settings
    function accountOverview()
    {
        // display the orders and tickets sections
        displayOrdersOrTicketsSections("orders");
        displayOrdersOrTicketsSections("tickets");

        // display settings
        displayAccountSettings();
    }

    function displaySiteInfoSections($sectionsToDisplay="all")
    {
        // SET SQL
        $sql["code"] = "SELECT * FROM siteinfo ORDER BY sectionid";

        if($sectionsToDisplay == "news")
        {
            $sql["code"] = "SELECT * FROM siteinfo WHERE sectionname=?";
            $sql["params"] = "news";
        }
        
        // RETRIEVE SECTIONS
        $sections = runSql($sql);
        if(!isset($sections[0]) || empty($sections[0]))
            return;
        
        // DISPLAY THE SECTIONS
        $rowColumnsArray = array();
        $blocksContentArray = array();
        
        // create a single row containing all the columns
        foreach($sections as $section)
        {
            $rowColumnsArray[] = ["extraClasses" => ["rowColumn3"]];
            // before adding the html of the current section, undo the html entities and add the section number to the html
            $section["html"] = "<div class='sectionContent'>" . html_entity_decode($section["html"]) . "</div>";
            $section["html"] .= "<div class='sectionInfo'><input name='sectionid' type='hidden' value='{$section["sectionid"]}'/></div>";
            $blocksContentArray[] = $section["html"];
        }
        
        $info = 
        [
            "row" => ["id" => "siteInfo", "extraClasses" => ["blue"]],
            "rowColumn" => $rowColumnsArray,
            "blockContent" => $blocksContentArray
        ];
        
        createRow($info);
    }

    // display a title row
    function displayTitleRow($title)
    {
        $titleHtml = "<h3>{$title}</h3>";
        
        $info = 
        [
            "row" => ["extraClasses" => ["blue", "titleRow"]],
            "rowColumn" => [ [] ],
            "blockContent" => [$titleHtml]
        ];
        
        createRow($info);
    }

    // display the tickets or orders sections of the home page
    // this includes a title row and the typical stuff
    // parameter is either "orders" or "tickets"
    function displayOrdersOrTicketsSections($ordersOrTickets)
    {
        // first the title
        displayTitleRow("recent {$ordersOrTickets}");
        
        // then the content
        $html = retrieveOrdersOrTickets($ordersOrTickets);
        $rowId = $ordersOrTickets . "Row";
        
        $info = 
        [
            "row" => [ "id" => $rowId, "extraClasses" => ["blue"] ],
            "rowColumn" => [ [] ],
            "blockContent" => [$html["dataHtml"]]
        ];
        
        createRow($info);
    }

    function displayAccountSettings()
    {
        // DISPLAY THE TITLE ROW
        displayTitleRow("settings");
        
        // GENERATE HTML FOR THE SETTINGS COLUMNS
        $passChangeColumnHtml =
            "<h3>change password</h3>
            <form action='/php/form-processor.php' method='POST' id='passwordChangeForm'>
                <p>
                    <input type='password' name='old_password' placeholder='old password here...'/>
                </p>

                <p>
                    <input type='password' name='new_password' placeholder='new password here...'/>
                </p>
                <input type='hidden' name='password-form-submitted' class='hidden'/>
                <input type='hidden' name='form-submitted' class='hidden'/>
                <p><input type='submit' value='Change'/></p>
            </form>";
        
        // DISPLAY IT
        $info = 
        [
            "row" => ["id" => "accountSettings", "extraClasses" => ["blue", "rowPerfectWidth"]],
            "rowColumn" => [ [] ],
            "blockContent" => [$passChangeColumnHtml]
        ];
        
        createRow($info);
    }

?>