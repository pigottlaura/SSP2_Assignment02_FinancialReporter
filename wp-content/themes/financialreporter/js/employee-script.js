function customWindowOnload(){
    console.log("hello employee");
}

function customSetupEventListeners(){
    document.getElementById("addNewExpense").addEventListener("submit", addNewExpenseFormSubmitEvent);
}

function customClickEvent(e){
    if(e.target.classList.contains("removeExpense")){
        sendAjaxRequest({"action": "removeExpense", "expenseId": e.target.id}, function(jsonResponse){
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

    var data = {
        "action": "addExpense",
        "category": categoryInput.value,
        "cost": costInput.value,
        "description": descriptionInput.value
    };

    sendAjaxRequest(data, function(jsonResponse){
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