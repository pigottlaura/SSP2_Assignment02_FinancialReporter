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
    } else if(e.target.id == "saveEmployerSettings") {
        saveEmployerSettings();
    }
}


function addNewExpenseCategoryFormSubmitEvent(e) {
    // Preventing the form's default action, so that it cannot/
    // be submitted
    e.preventDefault();

    // Accessing the category name input, so that the value can be
    // read and sent to the server, and subsequently cleared once
    // it has been added to the database
    var categoryNameInput = e.target.querySelector("[name=categoryName]");

    if(categoryNameInput.value.length > 0) {
        var requestData = {
            categoryName: categoryNameInput.value
        };

        // Sending an ajax
        sendAjaxRequest("addNewExpenseCategory", requestData, function(jsonResponse){
            console.log(jsonResponse);

            // Checking if the action on the server was successful
            if(jsonResponse.successful){
                // Passing the response html to update the expense category display
                // for the employer
                updateEmployerExpenseCategories(jsonResponse.html);

                // Clearing the value of the category name input
                categoryNameInput.value = "";

            } else if(jsonResponse.errors.length > 0) {
                showResponseErrors("addNewCategoryErrors", jsonResponse.errors);
            }
        });
    }
}

function completeExpenseApproval(approvalButton){
    var requestData = {
        expenseId: approvalButton.id,
        decision: approvalButton.getAttribute("data-decision")
    };

    sendAjaxRequest("expenseApproval", requestData, function(jsonResponse){
        console.log(jsonResponse);

        if(jsonResponse.successful) {
            updateEmployerExpenses(jsonResponse.html);
        } else if(jsonResponse.errors.length > 0) {
            showResponseErrors("generalErrors", jsonResponse.errors);
        }
    });
}

function removeExpenseCategory(removalButton) {
    var requestData = {
        categoryId: removalButton.id
    };

    sendAjaxRequest("removeExpenseCategory", requestData, function(jsonResponse){
        console.log(jsonResponse);

        if(jsonResponse.successful) {
            updateEmployerExpenseCategories(jsonResponse.html);
        } else if(jsonResponse.errors.length > 0) {
            showResponseErrors("generalErrors", jsonResponse.errors);
        }
    });
}

function saveEmployerSettings() {
    var requestData = {
        deleteDatabaseOnThemeDeactivate: document.querySelector("[name=deleteDatabaseOnThemeDeactivate]").checked,
        receiptsRequiredForAllExpenses: document.querySelector("[name=receiptsRequiredForAllExpenses]").checked
    }
    console.log(requestData);
    sendAjaxRequest("saveEmployerSettings", requestData, function(jsonResponse){
        console.log(jsonResponse);
        if(jsonResponse.successful) {
            //updateEmployerSettings(jsonResponse.html);
        } else if(jsonResponse.errors.length > 0) {
            showResponseErrors("generalErrors", jsonResponse.errors);
        }
    });
}

function updateEmployerExpenses(newExpenseData) {
    document.getElementById("employerExpenseData").innerHTML = newExpenseData;
    updateTableHeadingIcons();
}


function reloadEmployerExpenses() {
    sendAjaxRequest("getAllExpenses", {}, function (jsonResponse) {
        if(jsonResponse.successful) {
            updateEmployerExpenses(jsonResponse.html);
        } else if(jsonResponse.errors.length > 0) {
            showResponseErrors("generalErrors", jsonResponse.errors);
        }
    });
}

function updateEmployerExpenseCategories(newExpenseCategoryData) {
    document.getElementById("employerExpenseCategories").innerHTML = newExpenseCategoryData;
}