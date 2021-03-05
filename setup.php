<?php

    /**********************************************************************/
    /*                           RESUME THE SESSION                       
    /**********************************************************************/

    session_start();

    /**********************************************************************/
    /*                    FILE NAME & ACCESS RULE FOR PAGE
    /**********************************************************************/

    $CURRENT_PAGE_NAME = basename(__FILE__, '.php');
    // $USER_TYPE_REQUIREMENT not defined here - see below

    /**********************************************************************/
    /*                              REUIREMENTS                           
    /**********************************************************************/

    require('php/sql-config.php');
    require('php/global.php');

    /**********************************************************************/
    /*                                  MAIN                           
    /**********************************************************************/

    specialRedirect();

    displayPage("setup");


    // The reason why $USER_TYPE_REQUIREMENT is not defined here and why this page uses a seperate function
    // is because if the admin is not setup yet, a user should be able to set it up. 
    // They must however go directly to this page. There is no menu item for it
    function specialRedirect()
    {   
        if 
        (
            (!loggedin()) ||
            (!empty(GLOBALS["admin"]) && !admin())
        )
        {
            header("Location: /home.php");
            exit();
        }
    }

    /**********************************************************************/
    //                          DISPLAY PAGE

    function setup()
    {   
        $blockContent = "<form action='/php/form-processor.php' method='POST' id='setupForm'>";
        
        foreach (GLOBALS as $key => $value)
        {
            $blockContent .= 
                "<p>
                    <label for='{$key}'>{$key}:</label>
                    <input type='text' name='{$key}' value='{$value}'/>
                </p>";
        }
        
        // add the error is there was one
        $blockContent .= getError();
        
        // add the form submit button to the block contnent
        $blockContent .= "<p><input name='setup-form-submitted' type='hidden'/><input name='form-submitted' type='hidden'/><input type='submit' value='submit'/></p>";
        
        $info = 
        [
            "row" => ["extraClasses" => ["blue", "rowPerfectWidth", "vericallyCenteredRow"]],
            "rowColumn" => [ [] ],
            "blockContent" => [$blockContent]
        ];

        createRow($info);
        
        $blockContent .= "</form>";
    }

?>