$(document).ready(function() {
    bindEventListeners();

    $(function () {
        $('[data-toggle="popover"]').popover()
    });
});

/*******************************************************************
* function to bind all event listeners
*******************************************************************/
function bindEventListeners() {
    /*******************************************************************
    * click event - opens/closes sidebar
    *******************************************************************/
    $('body').on('click', '.c-hamburger', function() {
        toggleSidebar();
    });

    /*******************************************************************
    * click event - closes sidebar when clicking outside of it
    *******************************************************************/
    $('body').on('click', '.main', function() {
        if ($('.sidebar').hasClass('sidebar-open')) {
            hideSidebar();
        }
    });

    /*******************************************************************
    * submit event - opens sign up pref modal
    *******************************************************************/
    $('body').on('submit', '#sign-up-form-email', function(e) {
        e.preventDefault();
        $('#home-signup-modal').modal('show');
        $('#sign-up-pref-form [name="email"]').val($('#sign-up-form-email [name="email"]').val());
    });

    /*******************************************************************
    * submit event - ajax call to sign in
    *******************************************************************/
    $('body').on('submit', '#signin-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/sign-in',
            data: $('#signin-form').serialize() + "&submit=submit",
            type: "POST",
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                $('html, body, input, textarea').css('cursor', 'auto');
                if (response.indexOf('.') >= 0) { // if response cantains '.'
                    $('#signin-modal #password').val('');
                    $('#signin-modal .modal-alert').html(response);
                    $('#signin-modal .modal-alert').slideDown();
                    setTimeout(function() {
                        $('#signin-modal .modal-alert').slideUp();
                    }, 5000);
                } else {
                    window.location.href = "/user/" + response;
                }
            }
        });
    });

    /*******************************************************************
    * click event - ajax call to sign out
    *******************************************************************/
    $('body').on('click', '#signout-link', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/sign-out',
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                if (response == 'Redirect') {
                    window.location.replace("/");
                } else {
                    $('html, body, input, textarea').css('cursor', 'auto');
                    refreshPage(function() {
                        hideSidebar();
                    });
                }
            }
        });
    });

    /*******************************************************************
    * submit event - ajax call to create user
    *******************************************************************/
    $('body').on('submit', '#sign-up-pref-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/create-user',
            data: $('#sign-up-pref-form').serialize() + "&submit=submit",
            type: "POST",
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                $('html, body, input, textarea').css('cursor', 'auto');
                if(response != "Success") {
                    $('#home-signup-modal #password').val('');
                    $('#home-signup-modal #password2').val('');
                    $('#home-signup-modal .modal-alert').html(response);
                    $('#home-signup-modal .modal-alert').slideDown();
                    setTimeout(function() {
                        $('#home-signup-modal .modal-alert').slideUp();
                    }, 5000);
                } else {
                    refreshPage(function() {
                        $('#home-signup-modal').modal('hide');
                        $('#home-signup-modal #email').val('');
                        $('#home-signup-modal #password').val('');
                        $('#home-signup-modal #password2').val('');
                        uncheckTile($('#home-signup-modal .checkbox-tile')); //will uncheck all tiles in this modal
                        $('#sign-up-form-email #email').val('');
                        alertModal('Thank you', '<p>Thank you for signing up!</p><p>You will begin recieving jokes shortly!</p><p>Can\'t wait? Go ahead and browse all the jokes we have to offer <a href="/jokes">here</a></p>');
                    });
                }
            }
        });
    });

    /*******************************************************************
    * submit event - ajax call to update user
    *******************************************************************/
    $('body').on('submit', '#update-pref-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/update-user',
            data: $('#update-pref-form').serialize() + "&submit=submit",
            type: "POST",
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                $('html, body, input, textarea').css('cursor', 'auto');
                if(response != "Success" && response != "Account deleted") {
                    $('#password').val('');
                    $('#password2').val('');
                    alertModal('Oops', '<p>' + response + '</p>');
                } else {
                    refreshPage(function() {
                        $('#email').val('');
                        $('#password-old').val('');
                        $('#password').val('');
                        $('#password2').val('');
                        $('#delete-account').val('');
                        if (response == "Account deleted") {
                        	alertModal('Bye Bye', '<p>Sorry to see you go!</p><p>You can always sign up again to get the latest and greatest jokes!</p>');
                            $('body').on('hidden.bs.modal', '#alert-modal', function () {
                                console.log('hello');
                            window.location.replace('/');// redirect when modal is closed
                        })
                        } else {
                            alertModal('Account Updated', '<p>You account has been updated</p>');
                        }
                    });
                }
            }
        });
    });

    /*******************************************************************
    * submit event - ajax call to reset password
    *******************************************************************/
    $('body').on('submit', '#reset-password-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/reset-password',
            data: $('#reset-password-form').serialize() + "&submit=submit",
            type: "POST",
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                $('html, body, input, textarea').css('cursor', 'auto');
                if(response == "Success") {
                    $('#reset-password-form #password1').val('');
                    $('#reset-password-form #password2').val('');
                    $('#reset-password-form #email').val('');
                    alertModal('Success!', '<p>Your password has been reset</p>');
                } else {
                    alertModal('Error', '<p>' + response + '</p>');
                }
            }
        });
    });

    /*******************************************************************
    * submit event - ajax call to send reset password email
    *******************************************************************/
    $('body').on('submit', '#send-reset-password-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/send-reset-password-email',
            data: $('#send-reset-password-form').serialize() + "&submit=submit",
            type: "POST",
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                $('html, body, input, textarea').css('cursor', 'auto');
                if(response == "Success") {
                    $('#send-reset-password-form #email').val('');
                    $('#send-reset-password-form #password').val('');
                    $('#send-reset-password-form .modal-alert').html('An email will be sent if the email you provied is signed up. Use the link in the email to reset your password');
                    $('#send-reset-password-form .modal-alert').slideDown();
                    setTimeout(function() {
                        $('#send-reset-password-form .modal-alert').slideUp();
                    }, 5000);
                } else {
                    $('#send-reset-password-form .modal-alert').html(response);
                    $('#send-reset-password-form .modal-alert').slideDown();
                    setTimeout(function() {
                        $('#send-reset-password-form .modal-alert').slideUp();
                        $('#signin-form .modal-alert').slideUp();
                    }, 5000);
                }
            }
        });
    });

    /*******************************************************************
    * click event - ajax call to upvote
    *******************************************************************/
    $('body').on('click', '.up-vote', function() {
        var voteCountObject = $(this).siblings('.vote-count');
        var downVoteObject = $(this).siblings('.down-vote');
        var jokeID = $(this).parents().eq(2).attr("id");
        var voteCount = parseInt(voteCountObject.html());
        var vote = 0; //holds their current vote status

        if ($(this).hasClass('fa-thumbs-o-up')) {
            //upvote
            $(this).removeClass('fa-thumbs-o-up');
            $(this).addClass('fa-thumbs-up');
            voteCount += 1;
            //if currently downvoted, remove it
            if (downVoteObject.hasClass('fa-thumbs-down')) {
                downVoteObject.removeClass('fa-thumbs-down');
                downVoteObject.addClass('fa-thumbs-o-down');
                voteCount += 1; //removes the original downvote from count as well
            }
            vote = 1;
            //flash count with green
            voteCountObject.removeClass('flash-red');
            voteCountObject.removeClass('flash-green');
            voteCountObject.addClass('flash-green');
        } else {
            //remove upvote
            $(this).removeClass('fa-thumbs-up');
            $(this).addClass('fa-thumbs-o-up');
            voteCountObject.removeClass('flash-red');
            voteCountObject.removeClass('flash-green');
            voteCount -= 1;
            vote = 0;
        }
        voteCountObject.html(voteCount); //update vote count in DOM
        var voteBox = $(this);
        $.ajax({
            url: '/jokes/vote',
            data: "submit=submit&vote=" + vote + "&joke_id=" + jokeID,
            type: "POST",
            success: function(response) {
                if(response != "Success") {
                    $('#signin-modal').modal('show');
                    voteBox.removeClass('fa-thumbs-up');
                    voteBox.addClass('fa-thumbs-o-up');
                    voteCount -= 1; //update vote count in DOM
                    voteCountObject.html(voteCount); //update vote count in DOM
                }
            }
        });
    });

    /*******************************************************************
    * click event - ajax call to downvote
    *******************************************************************/
    $('body').on('click', '.down-vote', function() {
        var voteCountObject = $(this).siblings('.vote-count');
        var upVoteObject = $(this).siblings('.up-vote');
        var jokeID = $(this).parents().eq(2).attr("id");
        var voteCount = parseInt(voteCountObject.html());
        var vote = 0; //holds their current vote status

        if ($(this).hasClass('fa-thumbs-o-down')) {
            //downvote
            $(this).removeClass('fa-thumbs-o-down');
            $(this).addClass('fa-thumbs-down');
            voteCount -= 1;
            //if currently upvoted, remove it
            if (upVoteObject.hasClass('fa-thumbs-up')) {
                upVoteObject.removeClass('fa-thumbs-up');
                upVoteObject.addClass('fa-thumbs-o-up');
                voteCount -= 1; //removes the original upvote from count as well
            }
            vote = -1;
            //flash count with red
            voteCountObject.removeClass('flash-red');
            voteCountObject.removeClass('flash-green');
            voteCountObject.addClass('flash-red');
        } else {
            //remove downvote
            $(this).removeClass('fa-thumbs-down');
            $(this).addClass('fa-thumbs-o-down');
            voteCountObject.removeClass('flash-red');
            voteCountObject.removeClass('flash-green');
            voteCount += 1;
            vote = 0;
        }
        voteCountObject.html(voteCount); //update vote count in DOM
        var voteBox = $(this);
        $.ajax({
            url: '/jokes/vote',
            data: "submit=submit&vote=" + vote + "&joke_id=" + jokeID,
            type: "POST",
            success: function(response) {
                if(response != "Success") {
                    $('#signin-modal').modal('show');
                    voteBox.removeClass('fa-thumbs-down');
                    voteBox.addClass('fa-thumbs-o-down');
                    voteCount += 1; //update vote count in DOM
                    voteCountObject.html(voteCount); //update vote count in DOM
                }
            }
        });
    });

    /*******************************************************************
    * click event - toggle reset password / sign in form
    *******************************************************************/
    $('body').on('click', '.forgot-password-link', function(e) {
        e.preventDefault();
        toggleForgotPasswordForm();
    });

    /*******************************************************************
    * click event - opens/closes update-pref-modal
    *******************************************************************/
    //$('body').on('click', '#preferences-link', function(e) {
    //    e.preventDefault();
    //    $('#update-pref-modal').modal('show');
    //});

    /*******************************************************************
    * click event - checks the checkbox-tile
    *******************************************************************/
    $('body').on('click', '.checkbox-tile', function(e) {
        e.preventDefault();
        toggleTile($(this));
    });

    /*******************************************************************
    * hide event - resets sign in form when it is closed
    * this is for if the reset passsword form is shown,
    * don't want it to still be shown when clicking sign in later
    *******************************************************************/
    $('body').on('hidden.bs.modal', '#signin-modal', function() {
        resetForgotPasswordForm();
    });
}

/*******************************************************************
* function - refresh neccessary parts of page after login/out
*******************************************************************/
function refreshPage(callback) {
	$.ajax( {
	    url : location.pathname,
	    type : 'get',                
	    success : function(response) {
	        $('.sidebar-head').html($('.sidebar-head', response).html());
	        $('#update-pref-modal').html($('#update-pref-modal', response).html());
            $('.masonry').html($('.masonry', response).html());
	        callback();
	    }
	});
}

/*******************************************************************
* function - toggle sidebar
*******************************************************************/
function toggleSidebar() {
    if ($('.sidebar').hasClass('sidebar-open')) {
        $('.sidebar').removeClass('sidebar-open');
        $('.sidebar-head').addClass('sidebar-head-hidden');
        $('.browse-button').removeClass('icon-open');
    } else {
        $('.sidebar').addClass('sidebar-open');
        $('.sidebar-head').removeClass('sidebar-head-hidden');
        $('.browse-button').addClass('icon-open');
    }

    toggleHamburgerIcon();
}

/*******************************************************************
* function - show sidebar
*******************************************************************/
function showSidebar() {
    if (!$('.sidebar').hasClass('sidebar-open')) {
        $('.sidebar').addClass('sidebar-open');
    }
    toggleHamburgerIcon();
}

/*******************************************************************
* function - close sidebar
*******************************************************************/
function hideSidebar() {
    if ($('.sidebar').hasClass('sidebar-open')) {
        $('.sidebar').removeClass('sidebar-open');
    }
    toggleHamburgerIcon();
}

/*******************************************************************
* function - toggle hamburger icon
*******************************************************************/
function toggleHamburgerIcon() {
    if ($('.c-hamburger').hasClass('is-active')) {
        $('.c-hamburger').removeClass('is-active');
    } else {
        $('.c-hamburger').addClass('is-active');
    }
}

/*******************************************************************
* function - toggle alert modal with text
*******************************************************************/
function alertModal(title, text) {
    $('#alert-modal .modal-title').html(title);
    $('#alert-modal .modal-body').html(text);
    $('#alert-modal').modal("show");
}

/*******************************************************************
* function - toggle checkbox tile
*******************************************************************/
function toggleTile(checkboxTile) {
    if (checkboxTile.hasClass('checked')) {
        checkboxTile.removeClass('checked');
        checkboxTile.children().children('input[type="checkbox"]').attr('checked', false);
    } else {
        checkboxTile.addClass('checked');
        checkboxTile.children().children('input[type="checkbox"]').attr('checked', true);
    }
}

/*******************************************************************
* function - check checkbox tile
*******************************************************************/
function checkTile(checkboxTile) {
    if (!checkboxTile.hasClass('checked')) {
        checkboxTile.addClass('checked');
        checkboxTile.children().children('input[type="checkbox"]').attr('checked', true);
    }
}

/*******************************************************************
* function - uncheck checkbox tile
*******************************************************************/
function uncheckTile(checkboxTile) {
    if (checkboxTile.hasClass('checked')) {
        checkboxTile.removeClass('checked');
        checkboxTile.children().children('input[type="checkbox"]').attr('checked', false);
    }
}

/*******************************************************************
* function - toggles singin /reset password form
*******************************************************************/
function toggleForgotPasswordForm() {
    if ($('.forgot-password-link').hasClass('nevermind')) {
        resetForgotPasswordForm();
    } else {
        $('#signin-form-password').slideUp();
        $('#signin-modal .modal-title').html('Forgot Password');
        $('.forgot-password-link').addClass('nevermind');
        $('.forgot-password-link').html('nevermind');
        $('#signin-form button[name="submit"]').html('Reset Password');
        $('#signin-form').attr("id","send-reset-password-form");
    }
}

/*******************************************************************
* function - resets singin /reset password form
*******************************************************************/
function resetForgotPasswordForm() {
    $('#signin-form-password').slideDown();
    $('#signin-modal .modal-title').html('Sign In');
    $('.forgot-password-link').removeClass('nevermind');
    $('.forgot-password-link').html('forgot password?');
    $('#send-reset-password-form button[name="submit"]').html('Sign In');
    $('#send-reset-password-form').attr("id","signin-form");
}