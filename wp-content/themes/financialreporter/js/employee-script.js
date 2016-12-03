function customWindowOnload(){
    console.log("hello employee");
}

function customSetupEventListeners(){
    document.getElementById("addNewExpense").addEventListener("submit", addNewExpenseFormSubmitEvent);
}

function customClickEvent(e){
    if(e.target.classList.contains("removeExpense")){
        var requestData = {
            expenseId: e.target.id
        };

        sendAjaxRequest("removeExpense", requestData, function(jsonResponse){
            console.log(jsonResponse);

            if(jsonResponse.successful) {
                updateEmployeeExpenses(jsonResponse.html);
            } else if(jsonResponse.errors.length > 0) {
                showResponseErrors("generalErrors", jsonResponse.errors);
            }
        });
    }
}

function addNewExpenseFormSubmitEvent(e){
    e.preventDefault();
    var categoryInput = e.target.querySelector("[name=category]");
    var costInput = e.target.querySelector("[name=cost]");
    var descriptionInput = e.target.querySelector("[name=description]");
    var receiptInput = e.target.querySelector('[name=receipt]');

    var requestData = {
        category: categoryInput.value,
        cost: costInput.value,
        description: descriptionInput.value,
        receipt: receiptInput.files[0]
    }

    sendAjaxRequest("addExpense", requestData, function(jsonResponse){
        console.log(jsonResponse);

        if(jsonResponse.successful){
            categoryInput.value = "";
            costInput.value = "";
            descriptionInput.value = "";
            receiptInput.value = "";

            updateEmployeeExpenses(jsonResponse.html);
        } else if(jsonResponse.errors.length > 0) {
            showResponseErrors("addExpenseErrors", jsonResponse.errors);
        }
    });
}

function updateEmployeeExpenses(newUserExpenseData){
    document.getElementById("employeeExpenseData").innerHTML = newUserExpenseData;
    updateTableHeadingIcons();
}


function reloadEmployeeExpenses() {
    sendAjaxRequest("getAllExpensesForCurrentUser", {}, function (jsonResponse) {
        console.log(jsonResponse);
        if(jsonResponse.successful) {
            updateEmployeeExpenses(jsonResponse.html);
        } else if(jsonResponse.errors.length > 0) {
            showResponseErrors("generalErrors", jsonResponse.errors);
        }
    });
}