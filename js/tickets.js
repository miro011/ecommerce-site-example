window.addEventListener( "load", () => {mainTickets();} );

function mainTickets()
{
    // GLOBAL
    hamburger();
    // GLOBAL
    
    // MAIN
    wrapOriginalContnent();
    setupNewTickets();
    setupTicketReplies();
    // MAIN
    
    // this is done so we can hide the rest of the tickets when either creating a new one or replying to one
    function wrapOriginalContnent()
    {
        let wrapper = `<div id="ticketsOriginal">`;
        wrapper += $(".content").innerHTML;
        wrapper += `</div>`;
        $(".content").innerHTML = wrapper;
    }
    
    function setupNewTickets()
    {
        // first fix the style of the table a bit to look good when the button is added above it
        let styleHtml = `<style>#ticketsRow th {border-top: 3px solid; padding-top: 10px;} #popup input[type="text"] {width: 100%;}</style>`;
        $("body").appendChild(htmlCodeToElement(styleHtml));
        
        // add the new ticket button
        let newTicketBtnHtml = `<p style="margin-top:0px;"><button id="newTicketBtn">New Ticket</button></p>`;
        $("#ticketsRow .block").prepend(htmlCodeToElement(newTicketBtnHtml));
        
        // add the event lister to open the popup
        $("#newTicketBtn").addEventListener("click", ()=>{showPopup("new ticket")});
    }
    
    function setupTicketReplies()
    {
        if ($("#ticketsRow table"))
            $("#ticketsRow table").addEventListener("click", (e)=>{showPopup("ticket reply", e.target)}); 
    }
    
    function showPopup(type, element=null)
    {
        // return clause when displaying the popup with ticket replies
        if (type == "ticket reply" && (element == null || element.nodeName != "BUTTON"))
            return;
        
        // hide original contnent
        $("#ticketsOriginal").classList.toggle("hidden");
        
        
        // CONSTRUCT POPUP
        let popupHtml = "";
        
        let rowHtmlOpening = 
            `<div class="row blue">
                    <div class="rowColumn rowColumn3">
                        <div class="block">`;
        let rowHtmlClosing =
                        `</div>
                    </div>
                </div>`;
            
        if (type == "new ticket")
        {
            popupHtml += 
                rowHtmlOpening +
                    `<p><button id="closeTicketBtn">Close</button></p>
                    <form action="/php/form-processor.php" method="POST" id="submitTicketForm">
                        <p><label>Title</label><input type="text" name="newTicketTitle" id="newTicketTitle"></p>
                        <p><label>Message</label><textarea name="newTicketMsg" id="newTicketMsg"></textarea></p>
                        <input type="hidden" name="new-ticket-form-submitted">
                        <p><input type="hidden" name="form-submitted"><input type="submit"></p>
                    </form>`
                + rowHtmlClosing;
        }
        else if (type == "ticket reply")
        {
            let ticketId = element.parentElement.parentElement.querySelector(".id").textContent;
            let ticketTitle = element.parentElement.previousElementSibling.textContent;
            let msgs = element.nextElementSibling.querySelectorAll(".singleMessageWrapper");
            
            popupHtml += rowHtmlOpening + `<p><button id="closeTicketBtn">Close</button></p>` + rowHtmlClosing; // close button
            popupHtml += rowHtmlOpening + `<h3 style="margin:0px;">${ticketTitle}</h3>` + rowHtmlClosing; // title
            
            // construct the messages
            for(let i=0; i<msgs.length; i++)
            {
                let msgContents = msgs[i].querySelectorAll("p");
                let msgFrom = msgContents[0].textContent;
                let msgText = msgContents[1].textContent;
                // since we're changing the place of things, we will ensure HTML code (if present in the message is not triggered)
                msgText = msgText.replace(/</g, "&lt;");
                msgText = msgText.replace(/>/g, "&gt;");

                popupHtml += rowHtmlOpening + `<h3>${msgFrom}</h3><p>${msgText}</p>` + rowHtmlClosing;
            }
            
            // construct the reply form
            popupHtml += 
                rowHtmlOpening + 
                    `<h3>Reply</h3>
                    <form action="/php/form-processor.php" method="POST" id="submitTicketForm">
                        <p><textarea name="newTicketMsg" id="newTicketMsg"></textarea></p>
                        <input type="hidden" name="ticketid" value="${ticketId}">
                        <input type="hidden" name="ticket-reply-form-submitted">
                        <p><input type="hidden" name="form-submitted"><input type="submit"></p>
                    </form>`
                + rowHtmlClosing;
        }
        
        // wrap the popupHtml with the popup DIV
        popupHtml = `<div id="popup">` + popupHtml;
        popupHtml += `</div>`;
        
        
        // show popup (doing it this way otherise event listerns disappear when messing with innerHTML of the page)
        $(".content").appendChild(htmlCodeToElement(popupHtml));
        
        // add event for validation of forms
        $("#submitTicketForm input[type='submit']").addEventListener("click", (e)=>{validateAndSubmit(e)});
        
        // add event listern for the close button
        $("#closeTicketBtn").addEventListener("click", ()=>{closePopup()});
    }
    
    function validateAndSubmit(event)
    {
        event.preventDefault();
        
        let title = $("#newTicketTitle") ? $("#newTicketTitle").value.trim() : null;
        let msg = $("#newTicketMsg").value.trim();
        
        if (title != null)
        {
            if (title.length > 35) {alert("title too long"); return;}
            if (title.length == 0) {alert("title is empty"); return;}
        }
        else
        {
            // message can be empty with new tickets but not with replies
            if (msg.length == 0) {alert("message is empty"); return;}
            if ($$("#popup .row").length-3 >= 10) {alert("No more replies allowed in this thread. Create a new one"); return;}
        }
        
        if (msg.length >= 1000) {alert("message too long"); return;}
            
        if ($("#newTicketUser") == false)
            $("#submitTicketForm").submit();
    }
    
    function closePopup()
    {
        $("#popup").remove();
        $("#ticketsOriginal").classList.toggle("hidden");
    }
}