window.onload = function(e){
    setupCookies();
    setupIcons();
    setupEventListeners();
}

function setupCookies() {
    if(!cookieExists("orderBy")){
        document.cookie = "orderBy=date_submitted;path=/";
    }
    if(!cookieExists("order")){
        document.cookie = "order=asc;path=/";
    }
}

function setupIcons(){
    if(document.getElementsByClassName("orderHeading").length > 0){
        // Table Heading Order By Icon
        var iconArrowDir = getCookieValue("order") == "asc" ? "chevron-up" : "chevron-down";
        var newIcon = document.createElement("span");
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
    var adminAjaxURL = e.target.getAttribute("action");
    var data = {
        "action": "addExpense",
        "category": e.target.querySelector("[name=category]").value,
        "cost": e.target.querySelector("[name=cost]").value,
        "description": e.target.querySelector("[name=description]").value
    };
    //console.log(data);

    sendAjaxRequest(e.target.getAttribute("action"), data, function(response){
       console.log(response);
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

function sendAjaxRequest(reqURL, data, callbackFunction, reqMethod){
    console.log(data);
    /*
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

    $.post(reqURL, data, callbackFunction);
}