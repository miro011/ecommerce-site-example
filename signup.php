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
    //                             REUIREMENTS                           
    /**********************************************************************/

    require('php/sql-config.php');
    require('php/global.php');

    /**********************************************************************/
    //                                  MAIN                        
    /**********************************************************************/

    redirect();

    displayPage("signup");

    /**********************************************************************/
    //                          DISPLAY PAGE

    function signup()
    {   
        // get the error string ready (if there was one)
        $error = getError();
        
        // generate the signup html
        $signupHTML = 
            "<form action='/php/form-processor.php' method='POST' id='signupForm'>
                <p>
                    <label for='firstname'>First Name:</label>
                    <input type='text' name='firstname' id='firstname'/>
                </p>

                <p>
                    <label for='lastname'>Last Name:</label>
                    <input type='text' name='lastname' id='lastname'/>
                </p>

                <p>
                    <label for='email'>Email:</label>
                    <input type='text' name='email' id='email'/>
                </p>

                <p>
                    <label for='password'>Password:</label>
                    <input type='password' name='password' id='password'/>
                </p>

                <p>
                    <label for='confirmpass'>Confirm Password:</label>
                    <input type='password' name='confirmpass' id='repPassword'/>
                </p>
                
                {$error}

                <p>
                    <input name='signup-form-submitted' type='hidden'/>
                    <input name='form-submitted' type='hidden'/>
                    <input type='submit' value='Signup' id='signupFormSubmit'/>
                </p>
            </form>";
        
        // display it
        $info = 
        [
            "row" => [ "extraClasses" => ["blue", "rowPerfectWidth", "vericallyCenteredRow"] ],
            "rowColumn" => [ [] ],
            "blockContent" => [$signupHTML]
        ];
        
        createRow($info);
    }
    

?>