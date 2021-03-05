<?php

    /**********************************************************************/
    /*                           RESUME THE SESSION                       
    /**********************************************************************/

    session_start();

    /**********************************************************************/
    /*                    FILE NAME & ACCESS RULE FOR PAGE
    /**********************************************************************/

    $CURRENT_PAGE_NAME = basename(__FILE__, '.php');
    $USER_TYPE_REQUIREMENT = "nonuser"; // used by redirect

    /**********************************************************************/
    /*                              REUIREMENTS                           
    /**********************************************************************/

    require('php/sql-config.php');
    require('php/global.php');

    /**********************************************************************/
    //                                  MAIN                        
    /**********************************************************************/

    redirect();

    displayPage("login");


    /**********************************************************************/
    //                          DISPLAY PAGE

    function login()
    {   
        // get the error string ready (if there was one)
        $error = getError();
        
        // generate the login html
        $loginHTML = 
            "<form action='/php/form-processor.php' method='POST' id='loginForm'>
                <p>
                    <label for='email'>Email:</label>
                    <input type='text' name='email' id='email'/>
                </p>

                <p>
                    <label for='password'>Password:</label>
                    <input type='password' name='password' id='password'/>
                </p>
                
                {$error}

                <p>
                    <input type='hidden' name='login-form-submitted'/>
                    <input type='hidden' name='form-submitted'/>
                    <input type='submit' value='Login' id='loginFormSubmit'/>
                </p>
            </form>";
        
        // display it
        $info = 
        [
            "row" => [ "extraClasses" => ["blue", "rowPerfectWidth", "vericallyCenteredRow"] ],
            "rowColumn" => [ [] ],
            "blockContent" => [$loginHTML]
        ];
        
        createRow($info);
    }

?>