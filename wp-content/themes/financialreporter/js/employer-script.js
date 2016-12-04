function customSetupEventListeners(){
    if(document.getElementById("addNewExpenseCategory") != null){
        document.getElementById("addNewExpenseCategory").addEventListener("submit", addNewExpenseCategoryFormSubmitEvent);
    }
}

function customClickEvent(e){
    // Determining which button was clicked, based on it's class
    if(e.target.classList.contains("expenseApproval")){
        // Approve / Reject button was clicked (expense)
        completeExpenseApproval(e.target);
    } else if(e.target.classList.contains("removeExpenseCategory")) {
        // Remove button was clicked (expense categories)
        removeExpenseCategory(e.target);
    } else if(e.target.id == "saveEmployerSettings") {
        // Save button was clicked (employer settings
        saveEmployerSettings();
    }
}


function addNewExpenseCategoryFormSubmitEvent(e) {
    // Preventing the form's default action, so that it cannot
    // be submitted
    e.preventDefault();

    // Accessing the category name input, so that the value can be
    // read and sent to the server, and subsequently cleared once
    // it has been added to the database
    var categoryNameInput = e.target.querySelector("[name=categoryName]");

    // Checking that the category name has a length greater than 0
    if(categoryNameInput.value.length > 0) {
        // Creating a request data object, containing the requested
        // category name
        var requestData = {
            categoryName: categoryNameInput.value
        };

        // Invoking the sendAjaxRequest of the main script, passing in the
        // required action, request data and callback function (to be invoked
        // when a response is received from the server)
        sendAjaxRequest("addNewExpenseCategory", requestData, function(responseObject){

            // Checking if the action on the server was successful
            if(responseObject.successful){
                // Passing the response html to update the expense category display
                // for the employer
                updateEmployerExpenseCategories(responseObject.html);

                // Clearing the value of the category name input
                categoryNameInput.value = "";

            } else if(responseObject.errors.length > 0) {
                // If errors were returned from the server, then displaying
                // these using the showResponseErrors method of the main script,
                // passing the ID of the element in which to display the errors,
                // along with the errors themselves
                showResponseErrors("addNewCategoryErrors", responseObject.errors);
            }
        });
    }
}

function completeExpenseApproval(approvalButton){
    // Creating a request data object, containing the id of the expense to
    // be approved, and the decision that was made on it i.e. Approved or
    // Rejected
    var requestData = {
        expenseId: approvalButton.id,
        decision: approvalButton.getAttribute("data-decision")
    };

    // Invoking the sendAjaxRequest of the main script, passing in the
    // required action, request data and callback function (to be invoked
    // when a response is received from the server)
    sendAjaxRequest("expenseApproval", requestData, function(responseObject){

        // Checking if the action was successful on the server
        if(responseObject.successful) {
            // Updating the expenses with the HTML returned from the server
            // i.e. to reflect the approval/rejection of this expense
            updateEmployerExpenses(responseObject.html);

        } else if(responseObject.errors.length > 0) {
            // If errors were returned from the server, then displaying
            // these using the showResponseErrors method of the main script,
            // passing the ID of the element in which to display the errors,
            // along with the errors themselves
            showResponseErrors("generalErrors", responseObject.errors);
        }
    });
}

function removeExpenseCategory(removalButton) {
    // Creating a request data object, containing the id of the category to
    // be deleted
    var requestData = {
        categoryId: removalButton.id
    };

    // Invoking the sendAjaxRequest of the main script, passing in the
    // required action, request data and callback function (to be invoked
    // when a response is received from the server)
    sendAjaxRequest("removeExpenseCategory", requestData, function(responseObject){

        // Checking if the action was successful on the server
        if(responseObject.successful) {
            // Updating the expense categories with the HTML returned from the server
            // i.e. as they will no longer contain the removed category
            updateEmployerExpenseCategories(responseObject.html);

        } else if(responseObject.errors.length > 0) {
            // If errors were returned from the server, then displaying
            // these using the showResponseErrors method of the main script,
            // passing the ID of the element in which to display the errors,
            // along with the errors themselves
            showResponseErrors("generalErrors", responseObject.errors);
        }
    });
}

function saveEmployerSettings() {
    // Creating a request data object, containing the boolean values of
    // each of the Employers settings
    var requestData = {
        deleteDatabaseOnThemeDeactivate: document.querySelector("[name=deleteDatabaseOnThemeDeactivate]").checked,
        receiptsRequiredForAllExpenses: document.querySelector("[name=receiptsRequiredForAllExpenses]").checked
    }

    // Invoking the sendAjaxRequest of the main script, passing in the
    // required action, request data and callback function (to be invoked
    // when a response is received from the server)
    sendAjaxRequest("saveEmployerSettings", requestData, function(responseObject){

        // Checking if any errors occurred on the server
        if(responseObject.errors.length > 0) {
            // If errors were returned from the server, then displaying
            // these using the showResponseErrors method of the main script,
            // passing the ID of the element in which to display the errors,
            // along with the errors themselves
            showResponseErrors("generalErrors", responseObject.errors);
        }
    });
}

function updateEmployerExpenses(newExpenseData) {
    // Updating the table body of the employers expense data, to the HTML
    // returned from the server
    document.getElementById("employerExpenseData").innerHTML = newExpenseData;

    // Updating the order indicator icon
    updateTableHeadingIcons();
}


function reloadEmployerExpenses() {
    // Invoking the sendAjaxRequest of the main script, passing in the
    // required action, request data and callback function (to be invoked
    // when a response is received from the server)
    sendAjaxRequest("getAllExpenses", {}, function (responseObject) {

        // Checking if the action was successful on the server
        if(responseObject.successful) {
            // Updating the expenses with the HTML returned from the server
            // i.e. as the order has been changed
            updateEmployerExpenses(responseObject.html);

        } else if(responseObject.errors.length > 0) {
            // If errors were returned from the server, then displaying
            // these using the showResponseErrors method of the main script,
            // passing the ID of the element in which to display the errors,
            // along with the errors themselves
            showResponseErrors("generalErrors", responseObject.errors);
        }
    });
}

function updateEmployerExpenseCategories(newExpenseCategoryData) {
    // Updating the table body of the employers expense categories data, to the HTML
    // returned from the server
    document.getElementById("employerExpenseCategories").innerHTML = newExpenseCategoryData;
}