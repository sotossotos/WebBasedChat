function startRefreshTimer() {
    updatePeopleList();
    window.setInterval(refreshPage, 1500);
}

function refreshPage() {
    updateCurrentChat();
    updatePeopleList();
}

var receiverId = -1;

function getChatHistory(ele) {
    var activeObjects = document.getElementsByClassName("active");

    //If the length is greater than 1, that means it's not only the Home button that is active
    for (var i = 0; i < activeObjects.length; i++) {
        if (activeObjects[i].id !== "home-button") {
            activeObjects[i].className = "";
        }
    }

    ele.className = "active";

    receiverId = ele.id;

    //Send receiver id to database and display message info
    $.ajax({
        url: "index.php",
        data: {id: receiverId, cmd: 'loadMessage'},
        type: "POST",
        success: function (data) {
            var messageHistoryDisplay = document.getElementsByClassName("profile-content")[0];
            messageHistoryDisplay.innerHTML = data;
            messageHistoryDisplay.scrollTop = messageHistoryDisplay.scrollHeight;
        }
    });

}

function sendMessage() {
    var message = document.getElementById("message").value;

    if (message !== "") {

        //Send receiver id to database and display message info
        $.ajax({
            url: "index.php",
            data: {message: message, cmd: 'sendMessage'},
            type: "POST",
            success: function (data) {

                document.getElementById("message").default = "type a message...";
                document.getElementById("message").value = null;

                updateCurrentChat();

                var messageHistoryDisplay = document.getElementsByClassName("profile-content")[0];
                messageHistoryDisplay.scrollTop = messageHistoryDisplay.scrollHeight;
            }
        });
    }
}

function updateCurrentChat() {
    if (receiverId !== -1) {
        //Send receiver id to database and display message info
        $.ajax({
            url: "index.php",
            data: {id: receiverId, cmd: 'loadMessage'},
            type: "POST",
            success: function (data) {
                var messageHistoryDisplay = document.getElementsByClassName("profile-content")[0];
                messageHistoryDisplay.innerHTML = data;
            }
        });
    }
}

function updatePeopleList() {
    //Send receiver id to database and display message info
    $.ajax({
        url: "index.php",
        data: {id: receiverId, cmd: 'getListPeople'},
        type: "POST",
        success: function (data) {
            document.getElementById("peopleList").innerHTML = data;
        }
    });
}