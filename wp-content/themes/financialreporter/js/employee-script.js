// Invoked by the setupEventListeners method in the main script
function customSetupEventListeners(){
    // Adding an event listener, for attempts to submit the addNewExpense form
    document.getElementById("addNewExpense").addEventListener("submit", addNewExpenseFormSubmitEvent);
}

// Invoked by the click event in the main script
function customClickEvent(e){
    // Checking if the target has a class of "removeExpense" i.e.
    // is this a "remove" button on one of the expense
    if(e.target.classList.contains("removeExpense")){

        // Creating a request data object, containing the id
        // of the expense to be removed
        var requestData = {
            expenseId: e.target.id
        };

        // Invoking the sendAjaxRequest of the main script, passing in the
        // required action, request data and callback function (to be invoked
        // when a response is received from the server)
        sendAjaxRequest("removeExpense", requestData, function(responseObject){

            // Checking if the action was successful on the server
            if(responseObject.successful) {
                // Updating the expenses with the HTML returned from the server
                // i.e. as they will no longer contain the removed expense
                updateEmployeeExpenses(responseObject.html);

            } else if(responseObject.errors.length > 0) {
                // If errors were returned fromt the server, then displaying
                // these using the showResponseErrors method of the main script,
                // passing the ID of the element in which to display the errors,
                // along with the errors themselves
                showResponseErrors("generalErrors", responseObject.errors);
            }
        });
    }
}

function addNewExpenseFormSubmitEvent(e){
    // Preventing the form's default action, so that it cannot
    // be submitted
    e.preventDefault();

    // Accessing the relevant inputs, and storing them in temporary
    // variables i.e. so the values can be read from them, and then
    // cleared once a successful response is received from the server
    var categoryInput = e.target.querySelector("[name=category]");
    var costInput = e.target.querySelector("[name=cost]");
    var descriptionInput = e.target.querySelector("[name=description]");
    var receiptInput = e.target.querySelector('[name=receipt]');

    // Creating a request data object, containing the category, cost and
    // description of the expense. Also including the file from the
    // receipt upload option, although this may be empty
    var requestData = {
        category: categoryInput.value,
        cost: costInput.value,
        description: descriptionInput.value,
        receipt: receiptInput.files[0]
    }

    // Invoking the sendAjaxRequest of the main script, passing in the
    // required action, request data and callback function (to be invoked
    // when a response is received from the server)
    sendAjaxRequest("addExpense", requestData, function(responseObject){

        // Checking if the action was successful on the server
        if(responseObject.successful){
            // Clearing all of the inputs in the new expense form
            categoryInput.value = "";
            costInput.value = "";
            descriptionInput.value = "";
            receiptInput.value = "";

            // Updating the expense data with the HTML returned from the server
            // i.e. as it will now contain the new expense
            updateEmployeeExpenses(responseObject.html);

        } else if(responseObject.errors.length > 0) {
            // If errors were returned from the server, then displaying
            // these using the showResponseErrors method of the main script,
            // passing the ID of the element in which to display the errors,
            // along with the errors themselves
            showResponseErrors("addExpenseErrors", responseObject.errors);
        }
    });
}

function updateEmployeeExpenses(newUserExpenseData){
    // Updating the table body of the employees expense data, to the HTML
    // returned from the server
    document.getElementById("employeeExpenseData").innerHTML = newUserExpenseData;

    // Updating the order indicator icon
    updateTableHeadingIcons();
}


function reloadEmployeeExpenses() {
    // Invoking the sendAjaxRequest of the main script, passing in the
    // required action, an empty object for the request data and callback
    // function (to be invoked when a response is received from the server)
    sendAjaxRequest("getAllExpensesForCurrentUser", {}, function (responseObject) {

        // Checking if the action was successful on the server
        if(responseObject.successful) {
            // Updating the expense data with the HTML returned from the server
            // i.e. as the expenses have been reordered
            updateEmployeeExpenses(responseObject.html);

        } else if(responseObject.errors.length > 0) {
            // If errors were returned from the server, then displaying
            // these using the showResponseErrors method of the main script,
            // passing the ID of the element in which to display the errors,
            // along with the errors themselves
            showResponseErrors("generalErrors", responseObject.errors);
        }
    });
}