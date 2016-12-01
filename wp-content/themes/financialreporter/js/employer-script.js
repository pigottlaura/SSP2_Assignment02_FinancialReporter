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

    var requestData = "action=addNewExpenseCategory";
    requestData += "&categoryName=" + categoryNameInput.value;

    sendAjaxRequest(requestData, function(jsonResponse){
        console.log(jsonResponse);
        updateEmployerExpenseCategories(jsonResponse.html);
        categoryNameInput.value = "";
    });
}

function completeExpenseApproval(approvalButton){
    var requestData = "action=expenseApproval";
    requestData += "&expenseId=" + approvalButton.id;
    requestData += "&decision=" + approvalButton.getAttribute("data-decision");

    sendAjaxRequest(requestData, function(jsonResponse){
        console.log(jsonResponse);
        updateEmployerExpenses(jsonResponse.html);
    });
}

function removeExpenseCategory(removalButton) {
    var requestData = "action=removeExpenseCategory";
    requestData += "&categoryId=" + removalButton.id;

    sendAjaxRequest(requestData, function(jsonResponse){
        console.log(jsonResponse);
        updateEmployerExpenseCategories(jsonResponse.html);
    });
}

function updateEmployerExpenses(newExpenseData) {
    document.getElementById("employerExpenseData").innerHTML = newExpenseData;
    updateTableHeadingIcons();
}


function reloadEmployerExpenses() {
    sendAjaxRequest({"action": "getAllExpenses"}, function (jsonResponse) {
        updateEmployerExpenses(jsonResponse.html);
    });
}

function updateEmployerExpenseCategories(newExpenseCategoryData) {
    document.getElementById("employerExpenseCategories").innerHTML = newExpenseCategoryData;
}