<?php

    /**********************************************************************/
    /*                           RESUME THE SESSION                       
    /**********************************************************************/

    session_start();

    /**********************************************************************/
    /*                              REUIREMENTS                           
    /**********************************************************************/

    require('sql-config.php');
    require('global.php');

    /**********************************************************************/
    /*                                ACTIVATE                           
    /**********************************************************************/

    if (isset($_POST["form-submitted"])) processForm();

    function processForm()
    {
        validationAndExecute();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    /**********************************************************************/
    /*                             VALIDATION                           
    /**********************************************************************/

    /*--------------------------------------------------------------------*/
                                    // MAIN

    // Call the right validation function based on the form submited and go through a bunch of validation steps
    // if all pass, the right execution function is ran
    function validationAndExecute()
    {
        if (isset($_POST["password-form-submitted"])) validatePasswordForm();
        else if (isset($_POST["section-form-submitted"])) validateSectionForm();
        else if (isset($_POST["login-form-submitted"])) validateLoginForm();
        else if (isset($_POST["ship-orders-form-submitted"])) validateShipOrders();
        else if (isset($_POST["add-product-form-submitted"]) || isset($_POST["edit-product-form-submitted"])) validateAddEditProduct();
        else if (isset($_POST["delete-product-form-submitted"])) validateDeleteProduct();
        else if (isset($_POST["buy-product-form-submitted"])) validateBuyProduct();
        else if (isset($_POST["setup-form-submitted"])) validateSetup();
        else if (isset($_POST["signup-form-submitted"])) validateSignup();
        else if (isset($_POST["mark-answered-form-submitted"])) validateMarkAnswered();
        else if(isset($_POST["new-ticket-form-submitted"])) validateNewTicket();
        else if(isset($_POST["ticket-reply-form-submitted"])) validateTicketReply();
        else error(); // if no condition is met
    }

    /*--------------------------------------------------------------------*/
                                    // HELPERS

    function validatePasswordForm()
    {
        if(!loggedin()) error();
        
        // get the email and password from users table
        $sql["code"] = "SELECT email, password FROM users WHERE email=?";
        $sql["params"] = array($_SESSION["user"]);
        $response = runSql($sql);

        // 1 - check for things being set or empty
        if(!isset($_POST["old_password"]) || empty($_POST["old_password"]) || !isset($_POST["new_password"]) || empty($_POST["new_password"]))
            error("*** Empty password field/s ***");

        // 2 - check if old passowrd is valid
        if(!password_verify($_POST["old_password"], $response[0]["password"]))
            error("*** Wrong password ***");

        // 3 - check length of new password
        if (strlen($_POST['new_password']) > getMaxCharsSqlTableCol("users", "password"))
            error("*** New password too long ***");
        
        // If all steps pass, execute
        changePassword();
    }

    function validateSectionForm()
    {
        if(!loggedin() || !admin()) error();
        
        // 1 - ensure there is a section number and that it is valid
        if (!isset($_POST["sectionid"]) || empty($_POST["sectionid"]))
            error("*** section ID empty or missing ***");

        $sql["code"] = "SELECT sectionid FROM siteinfo WHERE sectionid=?";
        $sql["params"] = array($_POST["sectionid"]);
        $response = runSql($sql);

        if($response == false)
            error("*** Invalid Section ID ***");

        // 2 - ensure the textarea containing the html is set
        if (!isset($_POST["editedSection"]))
            error("*** section html missing ***");

        // 3 - ensure the html in the text area doesn't exceed max length
        if (strlen($_POST['editedSection']) > getMaxCharsSqlTableCol("siteinfo", "html"))
            error("*** edited section html too long ***");
        
        // If all steps pass, execute
        updateSection();
    }

    function validateLoginForm()
    {
        if(loggedin()) error();
        
        // 1 - Empty fields check
        if(!isset($_POST['email']) || !isset($_POST['password']) || empty($_POST['email']) || empty($_POST['password']))
            error("*** Error logging in. Empty fields ***");
         
        // TRIM
        $_POST['email'] = trim($_POST['email']);
        // TRIM
        
        // 2 - Check for empty spaces
        if (strpos($_POST['email'], " "))
            error("*** Error logging in. Empty space/s in email ***");
        if (strpos($_POST['password'], " "))
            error("*** Error logging in. Empty space/s in password ***");
        
        
        // 3 - Check if any field exceeds max length (based on the sql table from the database)
        if (strlen($_POST['email']) > getMaxCharsSqlTableCol("users", "email"))
            error("*** Error logging in. Email too long ***");
        if (strlen($_POST['password']) > getMaxCharsSqlTableCol("users", "password"))
            error("*** Error logging in. Password too long ***");
        
        
        // 4 - check if email is valid
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
            error("*** Error logging in. Invalid email ***");
        
        
        // 5 - Verify login info is valid
        $sql["code"] = "SELECT email, password FROM users WHERE email=?";
        $sql["params"] = array($_POST["email"]);
        $statement = runSql($sql);
        
        if (!$statement || !password_verify($_POST['password'], $statement[0]["password"]))
            error("*** Error logging in. Invalid info ***");
        
        // If all steps pass, execute
        login();
    }

    function validateShipOrders()
    {
        if(!loggedin() || !admin()) error();
        
        if(!isset($_POST["selected"]) || !isset($_POST["action"]))
            error("*** Missing form elements ***");

        if (empty($_POST["selected"]))
            error("*** Nothing selected ***");

        if($_POST["action"] != "ship" && $_POST["action"] != "unship")
            error("*** No action specified ***");
        
        // If all steps pass, execute
        shipOrders();
    }

    function validateAddEditProduct()
    {
        if(!loggedin() || !admin()) error();
            
        // 1 - check if everything is set not empty
        if (!isset($_POST["title"]) || !isset($_POST["imgLink"]) || !isset($_POST["description"]) || !isset($_POST["price"]))
            error("*** Missing elements ***");

        if (empty($_POST["title"]) || empty($_POST["imgLink"]) || empty($_POST["description"]) || empty($_POST["price"]))
            error("*** Empty fields ***");

        // TRIM
        $_POST["title"] = trim($_POST["title"]);
        $_POST["imgLink"] = trim($_POST["imgLink"]);
        $_POST["description"] = trim($_POST["description"]);
        $_POST["price"] = trim($_POST["price"]);

        // 1.1 - specific validation for edit product
        if (isset($_POST["edit-product-form-submitted"]))
        {
            // 1.1.1 check if productid is there
            if (!isset($_POST["productid"]) || empty($_POST["productid"]))
                error("*** Missing product id ***");

            // 1.1.2 check if productid is valid
            if (getProductInfo($_POST["productid"]) == false)
                error("*** Invalid product id  ***");
        }

        // 2 - Length of fields check
        if (strlen($_POST['title']) > getMaxCharsSqlTableCol("products", "title"))
            error("*** Title too long ***");

        if (strlen($_POST['imgLink']) > getMaxCharsSqlTableCol("products", "imglink"))
            error("*** Image link too long  ***");

        if (strlen($_POST['description']) > getMaxCharsSqlTableCol("products", "description"))
            error("*** Description too long  ***");

        // 3 - Check if price provided is valid
        if (!is_numeric($_POST["price"]))
            error("*** Invalid price  ***");

        // 4 - Check if the image link provided is valid
        if (!file_exists("../img/" . $_POST["imgLink"]))
            error("*** Invalid image link. Make sure you create the image first. ***");
        
        // If all steps pass, execute
        if (isset($_POST["edit-product-form-submitted"])) editProduct();
        else addProduct();
    }

    function validateDeleteProduct()
    {
        if(!loggedin() || !admin()) error();
        
        // 1 check if productid is there
        if (!isset($_POST["productid"]) || empty($_POST["productid"]))
            error("*** Could not delete product - missing product id ***");

        // 2 check if productid is valid
        if (getProductInfo($_POST["productid"]) == false)
            error("*** Could not delete product - invalid product id  ***");
        
        // If all steps pass, execute
        deleteProduct();
    }

    function validateBuyProduct()
    {
        if(!loggedin()) error();
        
        if (!isset($_POST["productid"]) || empty($_POST["productid"]) || getProductInfo($_POST["productid"]) == false)
            error("*** Error purchasing product - invalid productid ***");
        
        // If all steps pass, execute
        buyProduct();
    }

    function validateSetup()
    {    
        if (!loggedin() || (!empty(GLOBALS["admin"]) && !admin())) error();
        
        foreach (GLOBALS as $key => $value)
        {
            // 1 - Check whether or not the name attrs of the fields were altered (they're supposed to be the same as the ones in GLOBALS)
            if (!isset($_POST[$key]))
                error("*** Name attribute of {$key} input field was tampered with ***");
            
            // TRIM
            $_POST[$key] = trim($_POST[$key]);
            
            // 2 - Check for empty spaces
            if (strpos($_POST[$key], " "))
                error("*** Empty space/s in {$key} input field ***");
            
            // 3 - Check if the field exceeds max length (based on the sql table from the database)
            if (strlen($_POST[$key]) > getMaxCharsSqlTableCol("globals", "value"))
                error("*** Field {$key} is too long ***");
        }
        
        // 4 - check if admin email exists (if set)
        if (!empty($_POST["admin"]))
        {
            $sql["code"] = "SELECT email FROM users WHERE email=?";
            $sql["params"] = array($_POST['admin']);
            $statement = runSql($sql);

            if (!$statement)
                error("*** You must create an account first, before you make it the administrator ***");
        }
        
        // If all steps pass, execute
        updateSetup();
    }

    function validateSignup()
    {
        // 1 - Empty fields check
        if 
        (
            !isset($_POST['firstname']) || 
            !isset($_POST['lastname']) || 
            !isset($_POST['email']) || 
            !isset($_POST['password']) || 
            !isset($_POST['confirmpass']) ||
            empty($_POST['firstname']) || 
            empty($_POST['lastname']) || 
            empty($_POST['email']) || 
            empty($_POST['password']) || 
            empty($_POST['confirmpass'])
        )
            error("*** Error signing up. Empty fields ***");
         
        
        // TRIM
        $_POST['firstname'] = trim($_POST['firstname']);
        $_POST['lastname'] = trim($_POST['lastname']);
        $_POST['email'] = trim($_POST['email']);
        // TRIM
        
        
        // 2 - Check for empty spaces
        if (strpos($_POST['firstname'], " "))
            error("*** Error signing up. Empty space/s in first email ***");
        if (strpos($_POST['lastname'], " "))
            error("*** Error signing up. Empty space/s in last name ***");
        if (strpos($_POST['email'], " "))
            error("*** Error signing up. Empty space/s in email ***");
        if (strpos($_POST['password'], " "))
            error("*** Error signing up. Empty space/s in password ***");
        
        
        // 3 - Check if any field exceeds max length (based on the sql table from the database)
        if (strlen($_POST['firstname']) > getMaxCharsSqlTableCol("users", "firstname"))
            error("*** Error signing up. First name too long ***");
        if (strlen($_POST['lastname']) > getMaxCharsSqlTableCol("users", "lastname"))
            error("*** Error signing up. Last name too long ***");
        if (strlen($_POST['email']) > getMaxCharsSqlTableCol("users", "email"))
            error("*** Error signing up. Email too long ***");
        if (strlen($_POST['password']) > getMaxCharsSqlTableCol("users", "password"))
            error("*** Error signing up. Password too long ***");
        
        
        // 4 - check if first name, last name and email are valid
        if(!preg_match("/^([a-zA-Z' ]+)$/", $_POST['firstname']))
            error("*** Error signing up. Invalid fist name ***");
        if(!preg_match("/^([a-zA-Z' ]+)$/", $_POST['lastname']))
            error("*** Error signing up. Invalid last name ***");
        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
            error("*** Error signing up. Invalid email ***");
        
        
        // 5 - Passwords match test
        if($_POST['password'] != $_POST['confirmpass'])
            error("*** Error signing up. Passwords do not match ***");
        
        // 6 - Email exists test
        $sql["code"] = "SELECT email FROM users WHERE email=?";
        $sql["params"] = array($_POST["email"]);
        $statement = runSql($sql);
        if ($statement != false)
            error("*** Error signing up. Email already exists ***");
        
        // If all steps pass, execute
        signUserUp();
    }

    function validateMarkAnswered()
    {
        if(!loggedin() || !admin()) error();
        
        if(!isset($_POST["selected"]))
            error("*** Missing form elements ***");

        if(empty($_POST["selected"]))
            error("*** Nothing selected ***");
        
        // If all steps pass, execute
        markTicketsAsAnswered();
    }

    function validateNewTicket()
    {
        if(!loggedin()) error();
        
        // 1 - check if everything is set and non empty (message can be, but title can not)
        if (!isset($_POST["newTicketTitle"]) || empty($_POST["newTicketTitle"]) || !isset($_POST["newTicketMsg"]))
            error("*** Empty fields ***");

        // TRIM
        $_POST["newTicketTitle"] = trim($_POST["newTicketTitle"]);
        $_POST["newTicketMsg"] = trim($_POST["newTicketMsg"]);


        // 2 - specific validation per user type
        if(admin())
        {
            // 2.1.1 - check if user is provided
            if (!isset($_POST["newTicketUser"]) || empty($_POST["newTicketUser"]))
                error("*** No user provided ***");

            // TRIM
            $_POST["newTicketUser"] = trim($_POST["newTicketUser"]);

            // 2.1.2 - check if user if valid
            if (strlen($_POST['newTicketUser']) > getMaxCharsSqlTableCol("users", "email") || !userAdminProvidedIsValid())
                error("*** Invalid user provided ***");
        }
        else
        {
            // 2.2.1 - check whether or not the user has exhausted their limits for the day. We will set it to 5
            $dateToday = date("Y-m-d");
            $sql["code"] = "SELECT ticketid FROM tickets WHERE date=? AND userid=?";
            $sql["params"] = array($dateToday, getUseridFromEmail($_SESSION["user"]));
            $response = runSql($sql);

            if (is_array($response) && count($response) >= 5)
                error("*** You can not post more tickets today ***");
        }

        // 3 - length of fields
        if (strlen($_POST['newTicketTitle']) > 35)
            error("*** Title too long ***");
        if (strlen($_POST['newTicketMsg']) > 1000)
            error("*** Message too long ***");
        
        // If all steps pass, execute
        addNewTicket();
    }

    function validateTicketReply()
    {
        if(!loggedin()) error();
        
        // 1 Check if message is provided
        if (!isset($_POST["newTicketMsg"]) || empty($_POST["newTicketMsg"]))
            error("*** No message provided ***");

        // TRIM
        $_POST["newTicketMsg"] = trim($_POST["newTicketMsg"]);

        // 2 Check if ticketid has been provided
        if (!isset($_POST["ticketid"]) || empty($_POST["ticketid"]))
            error("*** Error posting the ticket ***");

        // 3 Check if ticketid is valid
        $sql["code"] = "SELECT ticketid FROM tickets WHERE ticketid=?";
        $sql["params"] = array($_POST["ticketid"]);
        $response = runSql($sql);
        if ($response == false)
            error("*** Can't reply to invalid ticket ***");

        // 2 Specific validation per user type (nothing for admins here)
        if(!admin())
        {
            // 2.1.1 Check if the right user is replying to the ticket
            $sql["code"] = "SELECT userid FROM tickets WHERE ticketid=?";
            $sql["params"] = array($_POST["ticketid"]);
            $response = runSql($sql);

            if ($_SESSION["user"] != getEmailFromUserid($response[0]["userid"]))
                error("*** You don't have permission to reply to this ticket ***");
        }

        // 4 - length of fields
        if (strlen($_POST['newTicketMsg']) > 1000)
            error("*** Ticket reply too long ***");

        // 5 Check if max number of comments have been reached for the ticket. We will use 10 as the max
        $sql["code"] = "SELECT msgs FROM tickets WHERE ticketid=?";
        $sql["params"] = array($_POST["ticketid"]);
        $msgs = str_replace("&quot;", '"', runSql($sql)[0]["msgs"]);
        $msgs = json_decode($msgs, true);

        if (count($msgs) >= 10)
            error("*** Max number of replies exceeded for this ticket ***");
        
        // If all steps pass, execute
        addTicketReply();
    }

    /**********************************************************************/
    /*                             EXECUTION                           
    /**********************************************************************/

    function changePassword()
    {
        $password = password_hash($_POST["new_password"], PASSWORD_BCRYPT, ['cost' => 12]);
        $sql["code"] = "UPDATE users SET password=? WHERE email=?";
        $sql["params"] = array($password, $_SESSION["user"]);
        runSql($sql);
    }

    function updateSection()
    {
        $sql["code"] = "UPDATE siteinfo SET html=? WHERE sectionid=?";
        $sql["params"] = array($_POST["editedSection"], $_POST["sectionid"]);
        runSql($sql);
    }

    function login()
    {
        $_SESSION["user"] = $_POST["email"];
    }

    function shipOrders()
    {
        if ($_POST["action"] == "ship")
            $status = "shipped";
        else
            $status = "pending";

        $orderIdsArray = explode(" ", $_POST["selected"]);
        foreach ($orderIdsArray as $orderId)
        {
            $sql["code"] = "UPDATE orders SET status=? WHERE orderid=?";
            $sql["params"] = array($status, $orderId);
            runSql($sql);
        }
    }

    function addProduct()
    {
        $imgLink = "img/" . $_POST["imgLink"];
        $sql["code"] = "INSERT INTO products(title, description, imglink, price) VALUES (?,?,?,?)";
        $sql["params"] = array($_POST["title"], $_POST["description"], $imgLink, $_POST["price"]);
        runSql($sql);
    }

    function editProduct()
    {
        $imgLink = "img/" . $_POST["imgLink"];
        $sql["code"] = "UPDATE products SET title=?, description=?, imglink=?, price=? WHERE productid=?";
        $sql["params"] = array($_POST["title"], $_POST["description"], $imgLink, $_POST["price"], $_POST["productid"]);
        runSql($sql);
    }

    function deleteProduct()
    {
        $sql["code"] = "DELETE FROM products WHERE productid=?";
        $sql["params"] = array($_POST["productid"]);
        runSql($sql);
    }

    function buyProduct()
    {
        $sql["code"] = "SELECT * FROM products WHERE productid=?";
        $sql["params"] = array($_POST["productid"]);
        $product = runSql($sql)[0];

        $userid = getUseridFromEmail($_SESSION["user"]);

        $sql["code"] = 
            "INSERT INTO orders(userid, date, product, quantity, total, paymentmethod, transaction, status) 
            VALUES (?,?,?,?,?,?,?,?)";
        $sql["params"] = array($userid, date("Y-m-d"), $product["title"], 1, $product["price"], "none", "none", "pending");
        runSql($sql);

        header("Location: /orders.php");
        exit();
    }

    function updateSetup()
    {
        // before inserting the new globals, I will delete the previous ones
        $sql["code"] = "DELETE FROM globals";
        runSql($sql);
        
        unset($_POST["form-submitted"]);
        unset($_POST["setup-form-submitted"]);

        $sql["code"] = "INSERT INTO globals (attr, value) VALUES (?,?)";

        // clean up the input fields of any injections and add them
        foreach ($_POST as $key => $value)
        {
            $sql["params"] = array($key, $value);
            runSql($sql);
        }
    }

    function signUserUp()
    {
        $password = password_hash($_POST["password"], PASSWORD_BCRYPT, ['cost' => 12]);
        $sql["code"] = "INSERT INTO users (email, password, firstname, lastname) VALUES (?,?,?,?)";
        $sql["params"] = array($_POST["email"], $password, $_POST["firstname"], $_POST["lastname"]);
        runSql($sql);
        
        $_SESSION["user"] = $_POST["email"];
    }

    function markTicketsAsAnswered()
    {
        $status = "answered";
        $ticketIdsArray = explode(" ", $_POST["selected"]);
        foreach ($ticketIdsArray as $ticketId)
        {
            $sql["code"] = "UPDATE tickets SET status=? WHERE ticketid=?";
            $sql["params"] = array($status, $ticketId);
            runSql($sql);
        }
    }

    function addNewTicket()
    {
        // if admin created the ticket, set the userid to the id of the provided user and status to "Waiting for user"
        if(admin())
        {
            $userid = getUseridFromEmail($_POST["newTicketUser"]);
            $status = "waiting";
        }
        else
        {
            $userid = getUseridFromEmail($_SESSION["user"]);
            $status = "pending";
        }

        // create a new msgs field array
        $msgs = array();
        $msgs[0]["user"] = $_SESSION["user"];
        $msgs[0]["msg"] = $_POST["newTicketMsg"];
        $msgs = json_encode($msgs);

        // flash the new ticket
        $sql["code"] = "INSERT INTO tickets(userid, date, title, msgs, status) VALUES (?,?,?,?,?)";
        $sql["params"] = array($userid, date("Y-m-d"), $_POST["newTicketTitle"], $msgs, $status);
        runSql($sql);
    }

    function addTicketReply()
    {
        // get the ticket that needes to be modified
        $sql["code"] = "SELECT * FROM tickets WHERE ticketid=?";
        $sql["params"] = array($_POST["ticketid"]);
        $response = runSql($sql);
        
        // get the msgs field, convert it to array and add the new msg
        $msgs = str_replace("&quot;", '"', $response[0]["msgs"]);
        $msgs = json_decode($msgs, true);
        $newIndex = count($msgs);
        $msgs[$newIndex]["user"] = $_SESSION["user"];
        $msgs[$newIndex]["msg"] = $_POST["newTicketMsg"];
        
        // determine the status of the ticket
        if(onlyAdminReplies($msgs))
            $status = "waiting";
        else if (admin())
            $status = "answered";
        else
            $status = "pending";
        
        // decode msgs
        $msgs = json_encode($msgs);

        // flash the changes
        $sql["code"] = "UPDATE tickets SET date=?, msgs=?, status=? WHERE ticketid=?";
        $sql["params"] = array(date("Y-m-d"), $msgs, $status, $_POST["ticketid"]);
        runSql($sql);
    }

    /**********************************************************************/
    /*                             OTHERS                           
    /**********************************************************************/

    function userAdminProvidedIsValid()
    {
        $sql["code"] = "SELECT email FROM users WHERE email=?";
        $sql["params"] = array($_POST["newTicketUser"]);
        $response = runSql($sql);
        
        if($response === false) return false;
        else return true;
    }

    function onlyAdminReplies($msgsArr)
    {
        $onlyAdminReplies = true;
        
        foreach ($msgsArr as $msg)
        {
            if ($msg["user"] != GLOBALS["admin"])
            {
                $onlyAdminReplies = false;
                break;
            }
        }
        
        return $onlyAdminReplies;
    }
    

?>