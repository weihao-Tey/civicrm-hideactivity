// Define a function to remove options with a specific inner HTML content
function removeOptionByString() {
    // Retrieve the string passed from PHP
    var ARRAY = CRM.vars.hideActions;

    // Select the options within the select element
    var options = document.getElementsByName("other_activity")[0].querySelectorAll("option");

    // Loop through the options and remove the option with inner HTML matching myString
    options.forEach(function(option) {
        for (var i = 0; i < ARRAY.length; i++) {
            if (option.innerHTML.trim() === ARRAY[i]) {
                option.remove(); // Remove the option element
            }
        }
    });
}

// Call the function to remove options once the variable is defined
removeOptionByString();





