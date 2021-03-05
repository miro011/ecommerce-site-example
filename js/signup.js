window.addEventListener( "load", () => {mainSignup();} );

function mainSignup()
{
    // GLOBAL
    hamburger();
    verticalyCenterElements();
    // GLOBAL
    
    // form validation
    $("#signupForm input[type='submit']").addEventListener("click", (e)=>{validateSignupForm(e)});
    
    function validateSignupForm (event)
    {
        event.preventDefault();
        let firstname = $("#signupForm input[name='firstname']").value.trim();
        let lastname = $("#signupForm input[name='lastname']").value.trim();
        let email = $("#signupForm input[name='email']").value.trim();
        let password = $("#signupForm input[name='password']").value.trim();
        let password2 = $("#signupForm input[name='confirmpass']").value.trim();
        
        
        // 1 - Empty fields
        if (firstname == "" && lastname == "" && email == "" && password == "" && password2 == "")
        {
            alert("Empty fields");
            return;
        }
        else if (firstname == "")
        {
            alert("Empty first name");
            return;
        }
        else if (lastname == "")
        {
            alert("Empty last name");
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
        else if (password2 == "")
        {
            alert("Empty repeat password");
            return;
        }
        
        
        // 2 - Empty spaces check
        else if (firstname.includes(" "))
        {
            alert("Empty spaces in first name");
            return;
        }
        else if (lastname.includes(" "))
        {
            alert("Empty spaces in last name");
            return;
        }
        else if (email.includes(" "))
        {
            alert("Empty spaces in email");
            return;
        }
        else if (password.includes(" "))
        {
            alert("Empty spaces in password");
            return;
        }
        else if (password2.includes(" "))
        {
            alert("Empty spaces in repeat password");
            return;
        }
        
        
        // 3 - Check if fields are too long
        if (firstname.length > 50 || lastname.length > 50 || email.length > 50 || password.length > 100 || password2.length > 100)
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
        
        // 5 - Passwords match test
        if (password != password2)
        {
            alert("Passwords do not match");
            return;
        }
        
        $("#signupForm").submit();
    }
}