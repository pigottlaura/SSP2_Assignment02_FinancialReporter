// Creating a global variable, in which to store the adminAjaxURL,
// so that request can be made to the WP server
var adminAjaxURL;

// Window on load function, invoked when the page first loads
window.onload = function(e){
    // Initialising the adminAjaxURL, based on a hidden input
    // declared at the bottom of every HTML page
    adminAjaxURL = document.getElementById("ajax-url").value;

    // Setting up the initial values for all cookies (if they
    // don't already exist)
    setupCookies();

    // Setting up any required event listeners
    setupEventListeners();

    // Initialising the order indicator icon
    updateTableHeadingIcons();
}

function setupCookies() {
    // Checking if each of the cookies already exists, and if
    // they don't then creating them
    if(!cookieExists("orderBy")){
        document.cookie = "orderBy=date_submitted;path=/";
    }
    if(!cookieExists("order")){
        document.cookie = "order=asc;path=/";
    }
}

function updateTableHeadingIcons(){
    // Checking that we are on a page that contains orderHeadings
    if(document.getElementsByClassName("orderHeading").length > 0){
        // Checking if an indicator icon already exists
        var orderIndicatorIcon = document.getElementById("orderIndicator");

        // If the indicator icon doesn't yet exist, then creating it,
        // and if it does exist, removing it from it's current parent
        if(orderIndicatorIcon == null) {
            // Creating a new span element, to act as the
            orderIndicatorIcon = document.createElement("span");
            orderIndicatorIcon.id = "orderIndicator";
            orderIndicatorIcon.setAttribute("aria-hidden", "true");
        } else {
            // Accessing the current parent of the icon, and removing it
            // from it. Also removing the "selected" class from the
            // parent
            var orderIndicatorIconParent = orderIndicatorIcon.parentNode;
            orderIndicatorIconParent.classList.remove("selected");
            orderIndicatorIconParent.removeChild(orderIndicatorIcon);
        }

        // Determining which direction the arrow should point based on the
        // order i.e. up or down, and appending this to the class name of the
        // icon
        var iconArrowDir = getCookieValue("order") == "asc" ? "chevron-up" : "chevron-down";
        orderIndicatorIcon.className = "glyphicon glyphicon-" + iconArrowDir;

        // Accessing the current column by which the expense data will be sorted,
        // and adding the "selected" class to it. Also appending the order indicator
        // icon as a child of it
        document.getElementById(getCookieValue("orderBy")).classList.add("selected");
        document.getElementById(getCookieValue("orderBy")).appendChild(orderIndicatorIcon);
    }
}

function setupEventListeners(){
    document.addEventListener("click", clickEvent);

    // Checking if a customSetupEventListeners method exists i.e.
    // as declared in a custom Employer or Employee script, and
    // invoking it if it does. This is so these custom scripts
    // can utilise this method, without creating duplication
    // setupEventListeners methods
    if(typeof customSetupEventListeners == "function"){
        customSetupEventListeners();
    }
}

function clickEvent(e){
    // Checking if this click occurred on an order heading i.e. one of the
    // headings on the expense data
    if(e.target.classList.contains("orderHeading")){
        // Checking if the current order by column id, matches the one
        // that was just clicked i.e. the user wants to view the expenses
        // based on the same column, but in the opposite order
        if(getCookieValue("orderBy") == e.target.id){
            // Swapping the order value i.e. if it was ascending, it will now
            // be descending and visa versa
            var swapOrder = getCookieValue("order") == "asc" ? "desc" : "asc";

            // Setting the order cookie value to the new order defined above
            setCookieValue("order", swapOrder)
        } else {
            // Since this was not the current order column, setting the order
            // by cookie to the new value, and reverting the order to ascending
            setCookieValue("orderBy", e.target.id);
            setCookieValue("order", "asc");
        }

        // Checking if a reload method for either an employer or employee exists
        // i.e. as declared in a custom Employer or Employee script, and
        // invoking it if it does. This is so the expense can be reloaded,
        // to reflect the new sort orders defined above
        if(typeof reloadEmployeeExpenses == "function"){
            reloadEmployeeExpenses();
        } else if (typeof reloadEmployerExpenses == "function"){
            reloadEmployerExpenses();
        }
    }

    // Checking if a customClickEvent method exists i.e.
    // as declared in a custom Employer or Employee script, and
    // invoking it if it does. This is so these custom scripts
    // can utilise this method, without creating duplication
    // click events
    if(typeof customClickEvent == "function") {
        customClickEvent(e);
    }
}

function cookieExists(name){
    // Accessing the cookie string of the document. Removing any
    // spaces, and then splitting the values based on the ";" i.e.
    // so that an array of name/value pairs will be created, one
    // for each individual cookie
    var allCookies = document.cookie.replace(/ /g, "").split(";");

    // Assuming the cookie does not exist, unless proved otherwise
    var result = false;

    // Looping through the cookie array generated above
    for(var i=0; i<allCookies.length; i++) {
        // Determining the name of the cookie, by splitting
        // the current cookie at the "=" and taking the first
        // part of the resulting array
        var cookieName = allCookies[i].split("=")[0];

        // If this cookie name matches the name passed to the method,
        // then this cookie exists
        if (cookieName == name) {
            result = true;
        }
    }

    // Returning the result to the caller
    return result;
}

function getCookieValue(name){
    // Accessing the cookie string of the document. Removing any
    // spaces, and then splitting the values based on the ";" i.e.
    // so that an array of name/value pairs will be created, one
    // for each individual cookie
    var allCookies = document.cookie.replace(/ /g, "").split(";");

    // Creating an empty string, in which to store the value of
    // the cookie
    var cookieVal = "";

    // Looping through the cookie array created above
    for(var i=0; i<allCookies.length; i++){
        // Determining the name of the cookie, by splitting
        // the current cookie at the "=" and taking the first
        // part of the resulting array
        var cookieName = allCookies[i].split("=")[0];

        // Checking if the current cookie's name, matches the
        // one passed to the method
        if(cookieName == name){
            // Accessing the value of the cookie, by splitting
            // the current cookie at the "=" and taking the second
            // part of the resulting array
            cookieVal = allCookies[i].split("=")[1];
        }
    }

    // Returning the cookie's value to the caller
    return cookieVal;
}

function setCookieValue(name, val){
    // Updating (or creating) the cookie, as specified by the name
    // and value passed to the method
    document.cookie = name + "=" + val + ";path=/";
}

function sendAjaxRequest(action, requestData, callbackFunction){
    // Clearing any errors that are currently on screen
    clearErrors();

    // Creating a new form data object, so that elements other
    // than plain text key/value pairs can be passed to the
    // server i.e. files
    var formData = new FormData();

    // Looping through each of the properties of the requestData
    // object, and appending them to the form data object, with
    // their name as the key and their value as the value
    for(var data in requestData){
        formData.append(data, requestData[data]);
    }

    // Creating a new XMLHttpRequest object, so that an ajax request
    // can be made to the server
    var xhttp = new XMLHttpRequest();

    // Adding an event listener, which will detect all ready state changes
    xhttp.onreadystatechange = function() {
        // Checking if a response has been received from the server,
        // and that it was completed successfully
        if (this.readyState == 4 && this.status == 200) {
            // Invoking the callback function, as passed to this method,
            // as passing it the response object (as parsed from the JSON
            // response text)
            callbackFunction(JSON.parse(this.responseText));
        }
    };

    // Opening the above request, as a POST request to the server. Appending
    // the required action as a parameter to the query string, as Wordpress
    // does not appear to detect the action if passed within the request
    // body i.e. in the form data. Setting this request to be asynchronous
    xhttp.open("POST", adminAjaxURL + "?action=" + action, true);

    // Setting a request header, to indicate that this request was made using
    // a XMLHttpRequest (again, as Wordpress does not seem to detect the request
    // otherwise)
    xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");

    // Sending the request, including the form data object in the body of the request
    xhttp.send(formData);
}

function showResponseErrors(ulId, errors) {
    // Accessing the element, into which these errors should be displayed.
    // Throughout this theme, all errors will be displayed in an unordered
    // list, and so any element identified here should be a UL
    var displayInUl = document.getElementById(ulId);

    // Looping through all of the errors
    for(var i=0; i < errors.length; i++){
        // Creating a new list item, adding the error as it's text content,
        // and appending it to the display element (as defined above)
        var newErrorLi = document.createElement("li");
        newErrorLi.textContent = errors[i];
        displayInUl.appendChild(newErrorLi);
    }
}

function clearErrors() {
    // Accessing all error display elements on the page
    var errorUls = document.getElementsByClassName("errors");

    // Looping through all the error display elements
    for(var i=0; i < errorUls.length; i++) {
        // Clearing an errors that currently exist within them
        errorUls[i].innerHTML = "";
    }
}