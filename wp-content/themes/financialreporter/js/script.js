var adminAjaxURL;

window.onload = function(e){
    adminAjaxURL = document.getElementById("ajax-url").value;
    setupCookies();
    setupEventListeners();
    loadUserExpenses();
}

function setupCookies() {
    if(!cookieExists("orderBy")){
        document.cookie = "orderBy=date_submitted;path=/";
    }
    if(!cookieExists("order")){
        document.cookie = "order=asc;path=/";
    }
}

function updateTableHeadingIcons(){
    console.log("Updating table heading icons");
    var orderIndicatorIcon = document.getElementById("orderIndicator");
    if(orderIndicatorIcon != null){
        var orderIndicatorIconParent = orderIndicatorIcon.parentNode;
        orderIndicatorIconParent.removeChild(orderIndicatorIcon);
    }

    if(document.getElementsByClassName("orderHeading").length > 0){
        // Table Heading Order By Icon
        var iconArrowDir = getCookieValue("order") == "asc" ? "chevron-up" : "chevron-down";
        var newIcon = document.createElement("span");
        newIcon.id = "orderIndicator";
        newIcon.className = "glyphicon glyphicon-" + iconArrowDir;
        newIcon.setAttribute("aria-hidden", "true");
        document.getElementById(getCookieValue("orderBy")).classList.add("selected");
        document.getElementById(getCookieValue("orderBy")).appendChild(newIcon);
    }
}

function setupEventListeners(){
    document.addEventListener("click", clickEvent);
    document.getElementById("addNewExpense").addEventListener("submit", addNewExpenseFormSubmitEvent);
}

function clickEvent(e){
    getCookieValue("orderBy");
    if(e.target.classList.contains("orderHeading")){
        if(getCookieValue("orderBy") == e.target.id){
            var swapOrder = getCookieValue("order") == "asc" ? "desc" : "asc";
            setCookieValue("order", swapOrder)
        } else {
            setCookieValue("orderBy", e.target.id);
            setCookieValue("order", "asc");
        }
        location.reload();
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

function cookieExists(name){
    var allCookies = document.cookie.replace(/ /g, "").split(";");
    var result = false;

    for(var i=0; i<allCookies.length; i++) {
        var cookieName = allCookies[i].split("=")[0];
        if (cookieName == name) {
            result = true;
        }
    }
    return result;
}

function getCookieValue(name){
    var allCookies = document.cookie.replace(/ /g, "").split(";");
    var cookieVal = "";

    for(var i=0; i<allCookies.length; i++){
        var cookieName = allCookies[i].split("=")[0];
        if(cookieName == name){
            cookieVal = allCookies[i].split("=")[1];
        }
    }
    return cookieVal;
}

function setCookieValue(name, val){
    document.cookie = name + "=" + val + ";path=/";
}

function sendAjaxRequest(data, callbackFunction){
    /*
    console.log(data);
    var method = reqMethod != null ? reqMethod : "GET";

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            callbackFunction(this.responseText);
        }
    };
    xhttp.open("POST", reqURL, true);
    xhttp.send(data);
    */

    $.post(adminAjaxURL, data, callbackFunction);
}