window.addEventListener( "load", () => {mainLogin();} );

function mainLogin()
{
    // GLOBAL
    hamburger();
    verticalyCenterElements();
    // GLOBAL
    
    // form validation
    $("#loginForm input[type='submit']").addEventListener("click", (e)=>{validateLoginForm(e)});
    
    function validateLoginForm (event)
    {
        event.preventDefault();
        let email = $("#loginForm input[name='email']").value.trim();
        let password = $("#loginForm input[name='password']").value.trim();
        
        
        // 1 - Empty fields
        if (email == "" && password == "")
        {
            alert("Empty fields");
            return;
        }
        else if (email == "")
        {
            alert("Empty email");
            return;
        }
        else if (password == "")
        {
            alert("Empty password");
            return;
        }
        
        // 2 - Empty spaces check
        if (email.includes(" "))
        {
            alert("Empty spaces in email");
            return;
        }
        if (password.includes(" "))
        {
            alert("Empty spaces in password");
            return;
        }
        
        // 3 - Check if fields are too long
        if (email.length > 50 || password.length > 100)
        {
            alert("Fields are too long");
            return;
        }
        
        // 4 - Check if email is valid format
        if (!emailIsValid(email))
        {
            alert("Invalid email format");
            return;
        }
        
        $("#loginForm").submit();
    }
}