window.addEventListener( "load", () => {mainTicketsAdmin();} );

function mainTicketsAdmin()
{
    // MAIN
    setupBlocksForTheOptions();
    addFilters();
    addCheckBoxesToTable();
    ticketFix();
    // MAIN
    
    function setupBlocksForTheOptions()
    {
        let html = 
            `<div class='row blue' id="toolsRow">
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
                      <option value="ticketid">ticketid</option>
                    </select>
                    <input type="text">
                    <button type='button' id='filterBtn'>Search</button>
                </p>

                <p>
                    <button type='button' id='showPendingBtn'>show pending</button>
                    <button type='button' id='showWaitingBtn'>show waiting</button>
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
                dropDown.prepend(htmlCodeToElement(`<option value="status" selected="selected">status</option>`));
                inputText.value = "pending";
            }
            else if (element.getAttribute("id") == "showWaitingBtn")
            {
                dropDown.prepend(htmlCodeToElement(`<option value="status" selected="selected">status</option>`));
                inputText.value = "waiting";
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
        if ($("#ticketsRow tr") == false) return;
        
        // CREATE CHECKBOX COLUMN IN TABLE
        let allTableRowsArr = $$("#ticketsRow tr");
        
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
                    <button type='button' id='markAnsweredBtn'>mark anwered</button>
                </p>
            </div>`;
        
        $("#changeStatusBlock").appendChild(htmlCodeToElement(buttonHtml));
        
        
        // ADD EVENT LISTENERS
        addSelectAllEventToMainCheckbox();
        addSubmitFormEventToMarkAnsweredButton();
        
        
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
        
        function addSubmitFormEventToMarkAnsweredButton()
        {
            $("#markAnsweredBtn").addEventListener("click", ()=>{run()});
            
            function run(action)
            {
                let ticketsIds = ""; // string containing all order ids seperated by space
                $$(".checkbox").forEach(elem => ticketsIds += checkedTicketId(elem));
                ticketsIds = ticketsIds.trim();
                
                let formHtml = 
                    `<form action='/php/form-processor.php' method='POST' id='markAnsweredFrom' class='hidden'>
                        <input type='hidden' name='selected' value='${ticketsIds}'>
                        <input type='hidden' name='mark-answered-form-submitted'>
                        <input type='hidden' name='form-submitted'>
                        <input type='submit'>
                    </form>`;
                
                $("body").appendChild(htmlCodeToElement(formHtml));
                $("#markAnsweredFrom").submit();
                
                function checkedTicketId(elem)
                {
                    if (elem.checked == true)
                        return elem.value + " ";
                    else
                        return "";
                }
            }
        }
    }
    
    // adds the text field to send to a specific user and hides the tools 
    // (these things go on top of what the other even listerns from the non-admin version of this script do)
    function ticketFix()
    {
        $("#newTicketBtn").addEventListener("click", ()=>{newTicketFix()});
        if ($("#ticketsRow table"))
            $("#ticketsRow table").addEventListener("click", (e)=>{ticketReplyFix(e.target)});
        
        
        function newTicketFix()
        {
            hideUnhideToolsRow();
            $("#submitTicketForm").prepend(htmlCodeToElement(`<p><label>User</label><input type="text" name="newTicketUser" id="newTicketUser"></p>`));
            
            $("#closeTicketBtn").addEventListener("click", ()=>{hideUnhideToolsRow()});
            $("input[type='submit']").addEventListener("click", (e)=>{extraValidationForAdmins(e)});
            
            function extraValidationForAdmins(event)
            {
                event.preventDefault();
                let validationReseult = run();
                if (typeof validationReseult == "string") {alert(validationReseult); return;}
                $("#submitTicketForm").submit();
                
                function run()
                {
                    let emailProvided = $("#newTicketUser").value.trim();
                    if (emailProvided == "") return "no user to send message to provided";
                    if (emailProvided.length > 50) return "user provided's email is too long";
                    if (!emailIsValid(emailProvided)) return "invalid email format";
                    return true;
                }
            }
        }
        
        function ticketReplyFix(element)
        {
            if (element.nodeName != "BUTTON")
                return;
            
            hideUnhideToolsRow();
            $("#closeTicketBtn").addEventListener("click", ()=>{hideUnhideToolsRow()});
        }
        
        function hideUnhideToolsRow()
        {
            $("#toolsRow").classList.toggle("hidden");
        } 
    }
}