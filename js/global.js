/*****************************************************************/
                        // GLOBAL VARIABLES
/*****************************************************************/

// REGEX -- GLOBAL_EMAIL_REGEX.test(email) != false to test
var GLOBAL_EMAIL_REGEX = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
var GLOBAL_LETTERS_REGEX = /^[a-zA-Z]+$/;
var GLOBAL_DIGITS_REGEX = /^[0-9]+$/;


/*****************************************************************/
                        // MAIN FUNCTIONS
/*****************************************************************/

/**************************** SELECTORS **************************/

// return element -- false if not found
function $ (query)
{
    let element = document.querySelector(query);
    if(element != null) return element;
    else return false;
}

// returns array of elements -- false if not found
function $$ (query)
{
    let elementsArray = document.querySelectorAll(query);
    if(elementsArray.length > 0) return elementsArray;
    else return false;
}


/******************* CREATE ELEMENT FROM HTML ********************/


function htmlCodeToElement(code)
{
    let tempWrapper = document.createElement("div");
    tempWrapper.innerHTML = code;
    tempWrapper = tempWrapper.firstChild;
    
    // for some reason the above removes th and td tags to this is the fix
    if (code.startsWith("<td>") || code.startsWith("<th>"))
    {
        let newParent;
        if (code.startsWith("<td>"))
            newParent = document.createElement("td");
        else
            newParent = document.createElement("th");
        
        newParent.appendChild(tempWrapper);
        tempWrapper = newParent;
    }
    
    return tempWrapper;
}


/*****************************************************************/
                            // PROJECT SPECIFIC
/*****************************************************************/

// vertical center for login and signup
function verticalyCenterElements()
{
    run(); // run when the document loads up
    window.addEventListener("resize", ()=>{run();} ); // run when window is resized
    
    function run()
    {
        let elementsToCenter = $$(".vericallyCenteredRow");
        for(let i=0; i<elementsToCenter.length; i++)
        {
            let marginTop = ($("html").offsetHeight - elementsToCenter[i].offsetHeight) / 2;
            // remove the height of the header
            marginTop = marginTop - $("#header").offsetHeight;
            // ensure the marginTop can't be negative
            if (marginTop < 0) marginTop = 0;
            // apply the style
            elementsToCenter[i].style.marginTop = marginTop + "px";
        } 
    }
}

function emailIsValid(email) 
{
    let regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return regex.test(String(email).toLowerCase());
}

function hamburger()
{
    let hamb = $("#hamburger");
    let mainNav = $("#main-navigation");
    
    visibility();
    window.addEventListener("resize", ()=>{visibility()});
    hamb.addEventListener("click", ()=>{showMenu()});
    
    function visibility()
    {
        if (window.innerWidth > 700) { hideHamb(); showMainNav(); return; }
        
        showHamb();
        hideMainNav();

        function hideHamb()
        {
            if (!hamb.classList.contains("hidden")) hamb.classList.toggle("hidden");
        }
        
        function showHamb()
        {
            if (hamb.classList.contains("hidden")) hamb.classList.toggle("hidden");
        }
        
        function hideMainNav()
        {
            if (!mainNav.classList.contains("hidden")) mainNav.classList.toggle("hidden");
        }
        
        function showMainNav()
        {
            if (mainNav.classList.contains("hidden")) mainNav.classList.toggle("hidden");
        }
    }
    
    function showMenu()
    {
        mainNav.classList.toggle("hidden");
    }
}