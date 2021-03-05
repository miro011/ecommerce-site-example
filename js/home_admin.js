window.addEventListener( "load", () => {mainHomeAdmin();} );

function mainHomeAdmin()
{
    // edit sections
    editSections();
    function editSections()
    {
        // add the edit buttons
        let editButtonHtml = `<button type='button' class='editSiteInfoSectionButton'>Edit</button>`;
        $$("#siteInfo .block .sectionInfo").forEach(e => e.appendChild(htmlCodeToElement(editButtonHtml)));
        
        // add event listeners to edit buttons
        $$(".editSiteInfoSectionButton").forEach(elem => elem.addEventListener("click", ()=>{ run(elem); }));

        function run (button)
        {
            let currSectionBlock = button.parentElement.parentElement;
            
            // hide the contents within the block
            currSectionBlock.querySelector(".sectionContent").classList.toggle("hidden");
            currSectionBlock.querySelector(".sectionInfo").classList.toggle("hidden");

            // make copy of the input hidden element within the block (it has the id of the section which is needed)
            let sectionIdHiddenInputHtml = currSectionBlock.querySelector("input[type='hidden']").outerHTML;
            
            // generate the edit form
            let currSectionContent = currSectionBlock.querySelector(".sectionContent").innerHTML.trim();
            let sectionBeingEditedHtml =
                `<div class="sectionBeingEdited">
                    <p><button class="goBack">Cancel</button></p>
                    <form action='/php/form-processor.php' method='POST' class='editSectionForm'>
                        <textarea name='editedSection' placeholder="type something here...">${currSectionContent}</textarea>
                        ${sectionIdHiddenInputHtml}
                        <input type='hidden' name='section-form-submitted' class='hidden'/>
                        <input type='hidden' name='form-submitted' class='hidden'/>
                        <p><input type='submit' value='Submit'/></p>
                    </form>
                </div>`;

            currSectionBlock.appendChild(htmlCodeToElement(sectionBeingEditedHtml));
            
            currSectionBlock.querySelector(".sectionBeingEdited").addEventListener("click", (event)=>{handleForm(event)});
            
            function handleForm(event)
            {
                if (!event.target.classList.contains("goBack") && event.target.getAttribute("type") != "submit")
                    return;
                
                if (event.target.classList.contains("goBack"))
                {
                    currSectionBlock.querySelector(".sectionBeingEdited").remove();
                    currSectionBlock.querySelector(".sectionContent").classList.toggle("hidden");
                    currSectionBlock.querySelector(".sectionInfo").classList.toggle("hidden");
                }   
                else
                {
                    event.preventDefault();
                    if (currSectionBlock.querySelector(".editSectionForm textarea").value.trim() == "")
                    {
                        alert("Please enter something for the section info");
                        return;
                    }
                    
                    currSectionBlock.querySelector(".editSectionForm").submit();
                }
            }
        }
    }
}