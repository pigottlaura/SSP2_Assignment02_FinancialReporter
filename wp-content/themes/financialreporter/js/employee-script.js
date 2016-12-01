function customWindowOnload(){
    console.log("hello employee");
}

function customSetupEventListeners(){
    document.getElementById("addNewExpense").addEventListener("submit", addNewExpenseFormSubmitEvent);
}

function customClickEvent(e){
    if(e.target.classList.contains("removeExpense")){
        var requestData = "action=removeExpense";
        requestData += "&expenseId=" +  e.target.id;

        sendAjaxRequest(requestData, function(jsonResponse){
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

    var requestData = "action=addExpense";
    requestData += "&category=" + categoryInput.value;
    requestData += "&cost=" + costInput.value;
    requestData += "&description=" + descriptionInput.value;

    console.log(receiptInput.files[0]);
    sendAjaxRequest(requestData, function(jsonResponse){
        console.log(jsonResponse);
        categoryInput.value = "";
        costInput.value = "";
        descriptionInput.value = "";

        updateEmployeeExpenses(jsonResponse.html);
    });
}

function updateEmployeeExpenses(newUserExpenseData){
    document.getElementById("employeeExpenseData").innerHTML = newUserExpenseData;
    updateTableHeadingIcons();
}


function reloadEmployeeExpenses() {
    sendAjaxRequest({"action": "getAllExpensesForCurrentUser"}, function (jsonResponse) {
        updateUserExpenses(jsonResponse.html);
    });
}