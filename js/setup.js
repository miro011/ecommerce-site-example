window.addEventListener( "load", () => {mainSetup();} );

function mainSetup()
{
    // GLOBAL
    hamburger();
    verticalyCenterElements();
    // GLOBAL
    
    
    // form validation
    $("#setupForm input[type='submit']").addEventListener("click", (e)=>{validateSetupForm(e)});
    
    function validateSetupForm (event)
    {
        event.preventDefault();
        
        let inputFields = $$("#setupForm input[type='text']");
        let validationSuccess = true;
        
        for (let i=0; i < inputFields.length; i++)
            {
                if (inputFields[i].value.trim() == "") continue;
                
                console.log(inputFields[i]);
                
                // 1 - Empty spaces check
                if (inputFields[i].value.trim().includes(" "))
                {
                    alert(`Empty spaces in ${inputFields[i].getAttribute("name")}`);
                    validationSuccess = false;
                    break; 
                }
                
                // 2 - Length check
                if (inputFields[i].value.trim().length > 100)
                {
                    alert(`${inputFields[i].getAttribute("name")} is too long`);
                    validationSuccess = false;
                    break; 
                }
                
                // 3 - Check if email is valid format
                if (inputFields[i].getAttribute("name") == "admin" || inputFields[i].getAttribute("name").includes("email"))
                {
                    if (!emailIsValid(inputFields[i].value.trim()))
                    {
                        alert(`Invalid email format in ${inputFields[i].getAttribute("name")}`);
                        validationSuccess = false;
                        break;
                    }
                }
            }
        
        if (validationSuccess)
            $("#setupForm").submit();
    }
}