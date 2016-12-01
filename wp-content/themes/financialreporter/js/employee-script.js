function customWindowOnload(){
    console.log("hello employee");
    loadUserExpenses();
}

function customSetupEventListeners(){
    document.getElementById("addNewExpense").addEventListener("submit", addNewExpenseFormSubmitEvent);
}

function customClickEvent(e){
    if(e.target.classList.contains("removeExpense")){
        sendAjaxRequest({"action": "removeExpense", "expenseId": e.target.id}, function(response){
            console.log(response);
            loadUserExpenses();
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

    sendAjaxRequest(data, function(response){
        console.log(response);
        categoryInput.value = "";
        costInput.value = "";
        descriptionInput.value = "";
        loadUserExpenses();
    });
}

function loadUserExpenses(){
    sendAjaxRequest({"action": "getAllExpensesForCurrentUser"}, function(employeeExpensesResponse){
        //console.log(employeeExpensesResponse);
        document.getElementById("employeeExpenseData").innerHTML = JSON.parse(employeeExpensesResponse).html;
        updateTableHeadingIcons();
    });
}