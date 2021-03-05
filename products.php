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

    /**********************************************************************/
    //                                  MAIN                        
    /**********************************************************************/

    // no redirect needed since access is to all
    
    if (isset($_GET["productid"]) && !empty($_GET["productid"]) && getProductInfo($_GET["productid"]) != false)
    {
        if(loggedin() && admin())
            displayPage("singleProduct", array("products_admin.js"));
        else
            displayPage("singleProduct");
    }
    else
    {
        if(loggedin() && admin())
            displayPage("products", array("products_admin.js"));
        else
            displayPage("products");
    }

    /**********************************************************************/
    //                          DISPLAY PAGE

    function products()
    {   
        // get the error string ready (if there was one)
        echo getError();
        
        // retrieve the prodcuts
        $sql["code"] = "SELECT productid, title, imglink FROM products";
        $products = runSql($sql);
        
        $rowColumnsArray = array();
        $blockContentArray = array();
        
        if ($products != false)
        {
            for ($i=0; $i<count($products); $i++)
            {
                $rowColumnsArray[$i] = ["extraClasses" => ["rowColumn6"]]; // I want 6 products max per row
                $blockContentArray[$i] = 
                    "<a href='/products.php?productid={$products[$i]["productid"]}&name={$products[$i]["title"]}'>
                        <img src='{$products[$i]["imglink"]}' alt='{$products[$i]["title"]}'>
                        <p>{$products[$i]["title"]}</p>
                    </a>";
            }
        }
        else
        {
            $rowColumnsArray[0] = array();
            $blockContentArray[0] = "<p>No products found</p>";
        }
        
        // display
        $info = 
        [
            "row" => [ "id" => "productsRow", "extraClasses" => ["blue"] ],
            "rowColumn" => $rowColumnsArray,
            "blockContent" => $blockContentArray
        ];
        
        createRow($info);
    }

    function singleProduct()
    {
        // get the error string ready (if there was one)
        echo getError();
        
        // get the product info
        $product = getProductInfo($_GET["productid"])[0];
        
        // set up the buy button (not shown for non-users)
        $buyButtonHtml = "";
        if(loggedin())
            $buyButtonHtml = "<button id='buyBtn'>Buy \${$product["price"]}</button>";
        
        // display
        // there is going to be 2 columns - 1 with the title, image and buy button and one with the description
        $columnOneHtml = 
            "<h3 id='productTitle'>{$product["title"]}</h3>
            <p><img id='productImg' src='{$product["imglink"]}' alt='{$product["title"]}'></p>
            <p>{$buyButtonHtml}</p>";
        
        $columnTwoHtml = 
            "<h3>Description</h3>
            <p id='productDescription'>{$product["description"]}</p>";
        
        $info = 
        [
            "row" => [ "id" => "singleProductRow", "extraClasses" => ["blue"] ],
            "rowColumn" => [[],[]],
            "blockContent" => [$columnOneHtml, $columnTwoHtml]
        ];
        
        createRow($info);
    }

?>