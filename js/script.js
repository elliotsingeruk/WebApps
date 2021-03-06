function onLoad() {
    hidePopover();
    getName();
}
function hidePopover(){
    $(document).click(function (e) {
        if (($('.popover').has(e.target).length == 0) || $(e.target).is('.close')) {
            $('#email').popover('hide');
            $('#email').popover({
                trigger: 'manual'
            })
        }
    });
}
function pageSetup(){

}
function getName(){
    var jwtArray = parseJwt();
    if(jwtArray == false){
        //if no jwt is present, run the following
       $('#signinButton').html('<button class="btn btn-outline-success my-2 my-sm-0" data-toggle="modal" data-target="#loginModal" onclick="signIn()">Sign In/Sign Up</button>');
    } else {
        //if a jwt is present, run the following
        $('#signinButton').html('<div class="dropdown">' +
        '<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
          jwtArray.firstName + " " + jwtArray.lastName +
        '</button>' +
        '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">' +
          '<a class="dropdown-item" onclick="getUser()" href="#">My Account</a>' +
          '<a class="dropdown-item" href="#">Saved Publications</a>' +
          '<button type="button" data-toggle="modal" data-target="#signOutConfirm" class="btn btn-info dropdown-item">Sign Out</button>' +
        '</div>' +
      '</div>')
    }
}
function getUser(){
    var jwtArray = parseJwt();
    if(jwtArray == false){
        reAuth();
    } else {
        $.ajax({
            type: "get",
            url: '/api/user/get.php?id=' + jwtArray.id,
            success: function (data) {
                if(data == "Invalid Token"){
                    reAuth();
                } else {
               var json = JSON.parse(data);
               $('#editEmail').val(json.email);
               $('#editFirstName').val(json.firstName);
               $('#editLastName').val(json.lastName);
               $('#userAccount').modal('show');           
                }     
            }
        });
    }
}



function signOut(){
    //remove the jwt stored as a cookie
    Cookies.remove('access');
    //hide the sign out modal after the cookie has been removed
    $('#signOutConfirm').modal('hide');
    //re-run the javascript to update the page
    onLoad();
}

function signUp() {
    //Unhide the hidden extra fields for registration of new user when the no account link is clicked
    $(".hidden-label").css("display", "inline");
    $("#confirmPassword").attr('type', 'password');
    $("#firstName").attr('type', 'text');
    $("#lastName").attr('type', 'text');
    $("#modalTitle").empty().append("Sign Up");
    $("#submitSignIn").html('<button type="submit" class="btn btn-primary" id="submitSignIn" onclick="signUpPost()">Sign Up</button>')
    $("#signUpPrompt").empty().append("Already a user? Click here to sign in")
    $("#signUpPrompt").on("click", signIn);
}
function signIn() {
    $('#signInForm')[0].reset();
    //Revert the sign up form to the sign in form
    $(".hidden-label").css("display", "none");
    $("#confirmPassword").attr('type', 'hidden');
    $("#firstName").attr('type', 'hidden');
    $("#lastName").attr('type', 'hidden');
    $("#modalTitle").empty().append("Sign In");
    $("#submitSignIn").html('<button type="submit" class="btn btn-primary" id="submitSignIn" onclick="logInPost()">Sign In</button>')
    $("#signUpPrompt").empty().append("No account? Click here to sign up")
    $("#signUpPrompt").on("click", signUp);
}
function logInPost(){
    var form = $('#signInForm')
    $.ajax({
        type: "POST",
        url: '/api/auth/login.php',
        data: form.serialize(),
        success: function (data) {
            var response = JSON.parse(data);
            if(response.message === "OK"){
                Cookies.set('access', response.access)
                $('#signInForm')[0].reset();
                $('#loginModal').modal('hide');
                onLoad();
            } else {
                $('#email').popover({trigger: 'focus'})
                $('#email').attr("data-content", response.message)
                $('#email').popover('show')
            }
        }
    })
}
function signUpPost(){
    var form = $('#signInForm')
    $.ajax({
        type: "POST",
        url: '/api/user/new.php',
        data: form.serialize(),
        success: function (data) {
            var response = JSON.parse(data);
            if(response.message === "OK"){
                signIn();
                $('#email').popover({trigger: 'focus'})
                $('#email').attr("data-content", "Sign in with your new account below")
                $('#email').popover('show')
            } else {
                $('#email').popover({trigger: 'focus'})
                $('#email').attr("data-content", response.message)
                $('#email').popover('show')
            }
        }
    })
}
/*
function signInPost(type) {
    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "type");
    switch (type) {
        case 0: hiddenField.setAttribute("value", 0);
            break;
        case 1: hiddenField.setAttribute("value", 1);
            break;
    }
    document.getElementById('signInForm').appendChild(hiddenField);
    var form = $('#signInForm');
    var dest = "";
    if (type === 0) {
        dest = "login.php";
    } else {
        dest = "signup.php";
    }
    $.ajax({
        type: "POST",
        url: '/api/user/' + dest,
        data: form.serialize(),
        success: function (data) {
            if (type === 0) {
                if ((data.substring(0, 4)) === "jwt:"){
                    Cookies.remove('jwt');
                    Cookies.set('jwt', data.substring(4));
                    $('#signInForm')[0].reset();
                    $('#loginModal').modal('hide');
                    onLoad();
                } else {
                    $('#signInForm')[0].reset();
                    $('#email').popover('show');
                    $('#email').attr("data-content", "The entered email or password is incorrect");
                    $('#email').popover({
                        trigger: 'focus'
                    })
                }

            } else {
                    if (data === "200") {
                        signIn();
                        $('#email').popover('show');
                        $('#email').attr("data-content", "Your account was successfully created, sign in below");
                        $('#email').popover({
                            trigger: 'focus'
                        });
                    } else {
                        $('#email').popover('show');
                        $('#email').attr("data-content", data);
                        $('#email').popover({
                            trigger: 'focus'
                        });
                    }
                }
            
        }
    });
}
*/
function parseJwt() {
    var token = Cookies.get("access");
    if(token != null){
    var base64Url = token.split('.')[1];
    var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    var jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
    return JSON.parse(jsonPayload);
} else {
    return false;
}
}
function reAuth(){
    alert("Session has expired, please sign in again");
    signIn();
    $('#loginModal').modal('show');
}