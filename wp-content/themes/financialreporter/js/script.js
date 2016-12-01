var adminAjaxURL;

window.onload = function(e){
    adminAjaxURL = document.getElementById("ajax-url").value;
    setupCookies();
    setupEventListeners();
    if(typeof customWindowOnload == "function") {
        customWindowOnload();
    }
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
    if(typeof customSetupEventListeners == "function"){
        customSetupEventListeners();
    }
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
        if(typeof reloadEmployeeExpenses == "function"){
            reloadEmployeeExpenses();
        } else if (typeof reloadEmployerExpenses == "function"){
            reloadEmployerExpenses();
        }
    }
    if(typeof customClickEvent == "function") {
        customClickEvent(e);
    }
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

function sendAjaxRequest(requestParams, file, callbackFunction){
    // REFERENCE - https://developer.mozilla.org/en-US/docs/Using_files_from_web_applications#Example_Uploading_a_user-selected_file
    var formData = new FormData();
    if(file != null){
        formData.append("receipt", file);
    }

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            callbackFunction(JSON.parse(this.responseText));
        }
    };
    xhttp.open("POST", adminAjaxURL + "?" + requestParams, true);
    xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhttp.send(formData);
}