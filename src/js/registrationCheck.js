function checkRegistration()
{
    var forename = document.getElementById("register-form").elements["forename"].value;
    var surname = document.getElementById("register-form").elements["surname"].value;
    var username = document.getElementById("register-form").elements["username"].value;
    var password = document.getElementById("register-form").elements["password"].value;
    var email = document.getElementById("register-form").elements["email"].value;

    var errorMessage = "";
    
    if ((forename == null) || (forename == "")) {
        errorMessage += "Enter forename\n";
    }
    if ((surname == null) || (surname == "")) {
        errorMessage += "Enter surname\n";
    }
    if ((username == null) || (username == "")) {
        errorMessage += "Enter username\n";
    }
    if ((password == null) || (password == "")) {
        errorMessage += "Enter password\n";
    }
    if ((email == null) || (email == "")) {
        errorMessage += "Enter email\n";
    }
    if (errorMessage != "") {
        alert(errorMessage);
    }
    return (errorMessage == "");
}