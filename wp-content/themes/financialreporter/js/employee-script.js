function customWindowOnload(){
    console.log("hello employee");
}

function customSetupEventListeners(){
    document.getElementById("addNewExpense").addEventListener("submit", addNewExpenseFormSubmitEvent);
}

function customClickEvent(e){
    if(e.target.classList.contains("removeExpense")){
        var requestParams = "action=removeExpense";
        requestParams += "&expenseId=" +  e.target.id;

        sendAjaxRequest(requestParams, null, function(jsonResponse){
            console.log(jsonResponse);
            updateEmployeeExpenses(jsonResponse.html);
        });
    }
}

function addNewExpenseFormSubmitEvent(e){
    e.preventDefault();
    var categoryInput = e.target.querySelector("[name=category]");
    var costInput = e.target.querySelector("[name=cost]");
    var descriptionInput = e.target.querySelector("[name=description]");
    var receiptInput = e.target.querySelector('[name=receipt]');

    var requestParams = "action=addExpense";
    requestParams += "&category=" + categoryInput.value;
    requestParams += "&cost=" + costInput.value;
    requestParams += "&description=" + descriptionInput.value;

    sendAjaxRequest(requestParams, receiptInput.files[0], function(jsonResponse){
        console.log(jsonResponse);
        categoryInput.value = "";
        costInput.value = "";
        descriptionInput.value = "";
        receiptInput.value = "";

        updateEmployeeExpenses(jsonResponse.html);
    });
}

function updateEmployeeExpenses(newUserExpenseData){
    document.getElementById("employeeExpenseData").innerHTML = newUserExpenseData;
    updateTableHeadingIcons();
}


function reloadEmployeeExpenses() {
    var requestParams = "action=getAllExpensesForCurrentUser";
    sendAjaxRequest(requestParams, null, function (jsonResponse) {
        updateEmployeeExpenses(jsonResponse.html);
    });
}