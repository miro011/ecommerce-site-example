window.addEventListener( "load", () => {mainProductsAdmin();} );


function mainProductsAdmin()
{
    if ($("#productsRow"))
        setupAddProduct();
    else if ($("#singleProductRow"))
        setupEditDeleteProduct();
    
    function setupAddProduct()
    {
        // First create the row where products will be added, which will also display the add product button
        $(".content").prepend(htmlCodeToElement(getRowHtml("addProductRow")));
        
        // Add and setup the add product button
        let addProductBtnHtml = `<button id="addProductBtn">Add Product</button>`;
        $("#addProductRow .block").appendChild(htmlCodeToElement(addProductBtnHtml));
        $("#addProductBtn").addEventListener("click", ()=>{displayAddProductForm()});
        
        function displayAddProductForm()
        {
            $("#productsRow").classList.toggle("hidden");
            $("#addProductBtn").classList.toggle("hidden");
            
            let addProductFormHtml =
                `<div>
                    <button id="backBtn">Go Back</button>
                    <form action="/php/form-processor.php" method="POST" id="addNewProductForm">
                        <p><h3>Title</h3><input type="text" name="title"></p>
                        <p><h3>Image Name</h3><input type="text" name="imgLink" value="example.png"></p>
                        <p><h3>Price</h3><input type="number" name="price"></p>
                        <p><h3>Description</h3><textarea name="description"></textarea></p>
                        <p><input type="hidden" name="add-product-form-submitted"><input type="hidden" name="form-submitted"><input type="submit"></p>
                    </form>
                </div>`;
            
            $("#addProductRow .block").appendChild(htmlCodeToElement(addProductFormHtml));
            
            $("#backBtn").addEventListener("click", ()=>{goBack()});
            $("input[type='submit']").addEventListener("click", (e)=>{validateAddEditProductForm(e)});
            
            function goBack()
            {
                $("#productsRow").classList.toggle("hidden");
                $("#addProductBtn").classList.toggle("hidden");
                $("#addNewProductForm").parentElement.remove();
            }
        }
    }
    
    function setupEditDeleteProduct()
    {
        // First create the rows for the edit and delete buttons
        $(".content").prepend(htmlCodeToElement(getRowHtml("editDeleteProductRow")));
        let buttonsHtml = `<div><button id="editProductBtn" style="margin-right: 10px;">Edit</button><button id="deleteProductBtn">Delete</button></div>`;
        $("#editDeleteProductRow .block").appendChild(htmlCodeToElement(buttonsHtml));
        
        // activate the buttons
        $("#editProductBtn").addEventListener("click", ()=>{displayEditProductForm()});
        $("#deleteProductBtn").addEventListener("click", ()=>{displayDeleteConfirmation()});
        
        function displayEditProductForm()
        {
            $("#editProductBtn").classList.toggle("hidden");
            $("#deleteProductBtn").classList.toggle("hidden");
            $("#singleProductRow").classList.toggle("hidden");
            
            let productid = window.location.href.split("?")[1].split("&")[0].split("=")[1];
            let title = $("#productTitle").textContent;
            let imgLink = $("#productImg").getAttribute("src").replace(/^\//g, "");
            let description = $("#productDescription").textContent;
            let price = $("#buyBtn").textContent.split("$")[1].trim();
            
            let html = 
                `<div id="editProductWrapper">
                    <button id="backBtn">Go back</button>
                    <form action="/php/form-processor.php" method="POST" id="editProductForm">
                        <p><h3>Title</h3><input type="text" name="title" value="${title}"></p>
                        <p><h3>ImgLink</h3><input type="text" name="imgLink" value="${imgLink}"></p>
                        <p><h3>Price</h3><input type="number" name="price" value="${price}"></p>
                        <p><h3>Description</h3><textarea name="description">${description}</textarea></p>
                        <p><input type="hidden" name="productid" value="${productid}"><input type="hidden" name="edit-product-form-submitted"><input type="hidden" name="form-submitted"><input type="submit"></p>
                    </form>
                </div>`;
            
            $("#editDeleteProductRow .block").appendChild(htmlCodeToElement(html));
            
            $("#backBtn").addEventListener("click", ()=>{goBack()});
            $("input[type='submit']").addEventListener("click", (e)=>{validateAddEditProductForm(e)});
            
            function goBack()
            {
                $("#editProductBtn").classList.toggle("hidden");
                $("#deleteProductBtn").classList.toggle("hidden");
                $("#singleProductRow").classList.toggle("hidden");
                $("#editProductWrapper").remove();
            }
        }
        
        function displayDeleteConfirmation()
        {
            if (confirm("Are you sure you want to delete this product?"))
            {
                let productid = window.location.href.split("?")[1].split("&")[0].split("=")[1];
                let formHtml = 
                    `<form action="/php/form-processor.php" method="POST" id="deleteProductForm" class="hidden">
                        <input type="hidden" name="productid" value="${productid}">
                        <input type="hidden" name="delete-product-form-submitted">
                        <input type="hidden" name="form-submitted">
                        <input type="submit">
                    </form>`;
                
                $("body").appendChild(htmlCodeToElement(formHtml));
                $("#deleteProductForm").submit();
            }
        }
    }
    
    function getRowHtml(id)
    {
        let html =
            `<div class="row blue" id="${id}">
                <div class="rowColumn rowColumn1">
                    <div class="block">
                    </div>
                </div>
            </div>`;
        
        return html;
    }
    
    function validateAddEditProductForm(event)
    {
        event.preventDefault();
        
        let title = $("input[name='title']").value.trim();
        let imgLink = $("input[name='imgLink']").value.trim();
        let price = $("input[name='price']").value.trim();
        let description = $("textarea").value.trim();
        
        // 1 - Empty Check
        if (title == "" || imgLink == "" || price == "" || description == "") {alert("empty fields"); return;}
        
        // 2 - imgLink check
        if (!imgLink.includes(".jpg") && !imgLink.includes(".png")) {alert("invalid imgage link"); return;}
        
        // 3 - price check
        if (isNaN(price)) {alert("invalid price"); return;}
        
        // 3 - length check
        if (title.length > 50) {alert("title too long"); return;}
        if (imgLink.length > 25) {alert("imgage link too long"); return;}
        
        event.target.parentElement.parentElement.submit();
    }
}