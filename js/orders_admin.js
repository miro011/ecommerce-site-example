window.addEventListener( "load", () => {mainOrdersAdmin();} );

function mainOrdersAdmin()
{
    // MAIN
    setupBlocksForTheOptions();
    addFilters();
    addCheckBoxesToTable();
    // MAIN
    
    function setupBlocksForTheOptions()
    {
        let html = 
            `<div class='row blue'>
                <div class='rowColumn rowColumn2'>
                    <div id="filtersBlock" class="block">
                    </div>
                </div>

                <div class='rowColumn rowColumn2'>
                    <div id="changeStatusBlock" class="block">
                    </div>
                </div>
            </div>`;
        
        $(".content").prepend(htmlCodeToElement(html));
    }
    
    function addFilters()
    {
        let filtersHtml = 
            `<div>
                <h3>Filter</h3>
                <p>
                    <select>
                      <option value="email">email</option>
                      <option value="orderid">orderid</option>
                      <option value="transaction">transaction</option>
                    </select>
                    <input type="text">
                    <button type='button' id='filterBtn'>Search</button>
                </p>

                <p>
                    <button type='button' id='showPendingBtn'>show pending</button>
                </p>
            </div>`;
        
        $("#filtersBlock").appendChild(htmlCodeToElement(filtersHtml));
        
        // ADD EVENT LISTENERS
        $("#filtersBlock").addEventListener("click", (e)=>{submitForm(e.target)});
        
        function submitForm(element)
        {
            if (element.nodeName != "BUTTON")
                return;

            let dropDown = $("#filtersBlock select");
            let inputText = $("#filtersBlock input[type='text']");
            
            if (element.getAttribute("id") == "showPendingBtn")
            {
                let statusOption = htmlCodeToElement(`<option value="status" selected>status</option>`);
                dropDown.prepend(statusOption);
                inputText.value = "pending";
            }
            
            if (inputText.value == "")
                return;
            
            let formHtml = 
                `<form action='' method='GET' id='filterForm' class='hidden'>
                    <input type='hidden' name='${dropDown.value}' value='${inputText.value}'>
                    <input type='submit'>
                </form>`;
            
            $("body").appendChild(htmlCodeToElement(formHtml));
            $("#filterForm").submit();
        }
    }
    
    function addCheckBoxesToTable()
    {
        if ($("#ordersRow tr") == false) return;
        
        // CREATE CHECKBOX COLUMN IN TABLE
        let allTableRowsArr = $$("#ordersRow tr");
        
        // header row (main) checkbox exception
        allTableRowsArr[0].prepend(htmlCodeToElement(`<th><input type='checkbox' id='selectAllCheckbox'></th>`)); 
        
        for(let i=1; i<allTableRowsArr.length; i++)
        {
            let id = allTableRowsArr[i].querySelector("td").textContent; // the first data cell is the id currently
            allTableRowsArr[i].prepend(htmlCodeToElement(`<td><input type='checkbox' class='checkbox' value='${id}'></td>`));
        }
        
        // ADD MARK SHIPPED & UNSHIPPED BUTTONS
        let buttonHtml = 
            `<div>
                <h3>Change status</h3>
                <p>
                    <button type='button' id='shipBtn'>ship</button>
                    <button type='button' id='unshipBtn'>unship</button>
                </p>
            </div>`;
        $("#changeStatusBlock").appendChild(htmlCodeToElement(buttonHtml));
        
        
        // ADD EVENT LISTENERS
        addSelectAllEventToMainCheckbox();
        addSubmitFormEventToShipButtons();
        
        
        /*********************************************************************/
        // HELPERS
        
        function addSelectAllEventToMainCheckbox()
        {
            let mainCheckBox = $("#selectAllCheckbox");
            mainCheckBox.addEventListener("click", ()=>{run()});
            
            function run()
            {
                let a = mainCheckBox.checked;
                $$(".checkbox").forEach(elem => elem.checked = a);
            }
        }
        
        function addSubmitFormEventToShipButtons()
        {
            $("#shipBtn").addEventListener("click", ()=>{run("ship")});
            $("#unshipBtn").addEventListener("click", ()=>{run("unship")});
            
            function run(action)
            {
                let orderIds = ""; // string containing all order ids seperated by space
                $$(".checkbox").forEach(elem => orderIds += checkedOrderId(elem));
                orderIds = orderIds.trim();
                
                let formHtml = 
                    `<form action='/php/form-processor.php' method='POST' id='shipOrdersFrom' class='hidden'>
                        <input type='hidden' name='selected' value='${orderIds}'>
                        <input type='hidden' name='action' value='${action}'>
                        <input type='hidden' name='ship-orders-form-submitted'>
                        <input type='hidden' name='form-submitted'>
                        <input type='submit'>
                    </form>`;
                
                $("body").appendChild(htmlCodeToElement(formHtml));
                $("#shipOrdersFrom").submit();
                
                function checkedOrderId(elem)
                {
                    if (elem.checked == true)
                        return elem.value + " ";
                    else
                        return "";
                }
            }
        }
    }
}