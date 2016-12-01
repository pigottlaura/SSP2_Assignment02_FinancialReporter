function customWindowOnload(){
    console.log("hello employer");
}

function customSetupEventListeners(){
    if(document.getElementById("addNewExpenseCategory") != null){
        document.getElementById("addNewExpenseCategory").addEventListener("submit", addNewExpenseCategoryFormSubmitEvent);
    }
}

function customClickEvent(e){
    if(e.target.classList.contains("expenseApproval")){
        completeExpenseApproval(e.target);
    } else if(e.target.classList.contains("removeExpenseCategory")) {
        removeExpenseCategory(e.target);
    }
}

function addNewExpenseCategoryFormSubmitEvent(e) {
    e.preventDefault();
    var categoryNameInput = e.target.querySelector("[name=categoryName]");

    var requestParams = "action=addNewExpenseCategory";
    requestParams += "&categoryName=" + categoryNameInput.value;

    sendAjaxRequest(requestParams, null, function(jsonResponse){
        console.log(jsonResponse);
        updateEmployerExpenseCategories(jsonResponse.html);
        categoryNameInput.value = "";
    });
}

function completeExpenseApproval(approvalButton){
    var requestParams = "action=expenseApproval";
    requestParams += "&expenseId=" + approvalButton.id;
    requestParams += "&decision=" + approvalButton.getAttribute("data-decision");

    sendAjaxRequest(requestParams, null, function(jsonResponse){
        console.log(jsonResponse);
        updateEmployerExpenses(jsonResponse.html);
    });
}

function removeExpenseCategory(removalButton) {
    var requestParams = "action=removeExpenseCategory";
    requestParams += "&categoryId=" + removalButton.id;

    sendAjaxRequest(requestParams, null, function(jsonResponse){
        console.log(jsonResponse);
        updateEmployerExpenseCategories(jsonResponse.html);
    });
}

function updateEmployerExpenses(newExpenseData) {
    document.getElementById("employerExpenseData").innerHTML = newExpenseData;
    updateTableHeadingIcons();
}


function reloadEmployerExpenses() {
    var requestParams = "action=getAllExpenses";
    sendAjaxRequest(requestParams, null, function (jsonResponse) {
        updateEmployerExpenses(jsonResponse.html);
    });
}

function updateEmployerExpenseCategories(newExpenseCategoryData) {
    document.getElementById("employerExpenseCategories").innerHTML = newExpenseCategoryData;
}