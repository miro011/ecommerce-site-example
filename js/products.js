window.addEventListener("load", () => {mainProducts()});

function mainProducts()
{
    // GLOBAL
    hamburger();
    // GLOBAL
    
    // Buy button if logged in
    if ($("#buyBtn") !== false)
        $("#buyBtn").addEventListener("click", ()=>{purchase()});
    
    function purchase()
    {
        let productid = window.location.href.split("?")[1].split("&")[0].split("=")[1];
        let formHtml =
            `<form action="/php/form-processor.php" method="POST" id="goToCheckoutForm" class="hidden">
                <p><input type="hidden" name="productid" value="${productid}"><input type="hidden" name="buy-product-form-submitted"><input type="hidden" name="form-submitted"><input type="submit"></p>
            </form>`;
        $("body").appendChild(htmlCodeToElement(formHtml));
        $("#goToCheckoutForm").submit();
    }
}