<?php

    /**********************************************************************/
    /*                           RESUME THE SESSION                       
    /**********************************************************************/

    session_start();

    /**********************************************************************/
    /*                    FILE NAME & ACCESS RULE FOR PAGE
    /**********************************************************************/

    $CURRENT_PAGE_NAME = basename(__FILE__, '.php');
    $USER_TYPE_REQUIREMENT = "user"; // used by redirect

    /**********************************************************************/
    /*                              REUIREMENTS                           
    /**********************************************************************/

    require('php/sql-config.php');
    require('php/global.php');
    require('php/retrieve_orders_tickets.php');

    /**********************************************************************/
    //                                  MAIN                        
    /**********************************************************************/

    redirect();

    if(admin())
        displayPage("orders", array("orders_admin.js"));
    else
        displayPage("orders");


    /**********************************************************************/
    //                          DISPLAY PAGE

    function orders()
    {   
        // get the error string ready (if there was one)
        $error = getError();
        
        // retrieve the orders
        $html = retrieveOrdersOrTickets("orders");
        $tableHtml = $error . $html["dataHtml"];
        $paginationHtml = $html["paginationHtml"];
        
        // display
        $info = 
        [
            "row" => [ "id" => "ordersRow", "extraClasses" => ["blue"] ],
            "rowColumn" => [ [] ],
            "blockContent" => [$tableHtml]
        ];
        
        createRow($info);
        
        if($tableHtml != "<p>No data</p>") // dont display pagination if no orders
        {
            $info = 
            [
                "row" => [ "id" => "pagination", "extraClasses" => ["blue"] ],
                "rowColumn" => [ [] ],
                "blockContent" => [$paginationHtml]
            ];

            createRow($info);
        }
    }

?>