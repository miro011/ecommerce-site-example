window.addEventListener( "load", () => {mainHome();} );

function mainHome()
{
    // GLOBAL
    hamburger();
    // GLOBAL
    
    // MAIN
    if ($("#passwordChangeForm")) // if logged in basically
    {
        wrapOriginalContnent();
        setupTicketReplies();
        $("#passwordChangeForm input[type='submit']").addEventListener("click", (e)=>{validatePassForm(e)});
    }
    // MAIN
    
    
    // this is done so we can hide the rest of the tickets when either creating a new one or replying to one
    function wrapOriginalContnent()
    {
        let wrapper = `<div id="homeOriginal">`;
        wrapper += $(".content").innerHTML;
        wrapper += `</div>`;
        $(".content").innerHTML = wrapper;
    }
    
    function setupTicketReplies()
    {
        if ($("#ticketsRow table"))
            $("#ticketsRow table").addEventListener("click", (e)=>{showPopup(e.target)}); 
        
        function showPopup(element=null)
        {
            // return clause when displaying the popup with ticket replies
            if (element == null || element.nodeName != "BUTTON")
                return;

            // hide original contnent
            $("#homeOriginal").classList.toggle("hidden");


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
            
            
            // wrap the popupHtml with the popup DIV
            popupHtml = `<div id="popup">` + popupHtml;
            popupHtml += `</div>`;


            // show popup (doing it this way otherise event listerns disappear when messing with innerHTML of the page)
            $(".content").appendChild(htmlCodeToElement(popupHtml));

            // add event for validation of forms
            $("#submitTicketForm input[type='submit']").addEventListener("click", (e)=>{validateTicketReply(e)});

            // add event listern for the close button
            $("#closeTicketBtn").addEventListener("click", ()=>{closePopup()});
        }
        
        function validateTicketReply(event)
        {
            event.preventDefault();

            let msg = $("#newTicketMsg").value.trim();

            if (msg.length == 0) {alert("message is empty"); return;}
            if ($$("#popup .row").length-3 >= 10) {alert("No more replies allowed in this thread. Create a new one"); return;}
            if (msg.length >= 1000) {alert("message too long"); return;}

            $("#submitTicketForm").submit();
        }

        function closePopup()
        {
            $("#popup").remove();
            $("#homeOriginal").classList.toggle("hidden");
        }
    }
    
    function validatePassForm(event)
    {
        event.preventDefault();
        let oldPassword = $("#passwordChangeForm input[name='old_password']").value.trim();
        let newPassword = $("#passwordChangeForm input[name='new_password']").value.trim();
        
        if (oldPassword == "" && newPassword == "")
        {
            alert("No passwords entered");
            return;
        }
        else if (oldPassword == "")
        {
            alert("You must enter your old password");
            return;
        }
        else if (newPassword == "")
        {
            alert("You must enter a new password");
            return;
        }
        
        if (oldPassword.length > 100 || newPassword.length > 100)
        {
            alert("Password/s are too long");
            return;
        }
        
        $("#passwordChangeForm").submit();
    }
}