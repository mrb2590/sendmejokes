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
                if(response != "Success") {
                    $('#signin-modal #password').val('');
                    $('#signin-modal .modal-alert').html(response);
                    $('#signin-modal .modal-alert').slideDown();
                    setTimeout(function() {
                        $('#signin-modal .modal-alert').slideUp();
                    }, 5000);
                } else {
                    refreshPage(function() {
                        $('#signin-modal').modal('hide');
                        $('#signin-modal #email').val('');
                        $('#signin-modal #password').val('');
                    });
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
                $('html, body, input, textarea').css('cursor', 'auto');
                refreshPage(function() {
                    hideSidebar();
                });
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
                    $('#update-pref-modal #password').val('');
                    $('#update-pref-modal #password2').val('');
                    $('#update-pref-modal .modal-alert').html(response);
                    $('#update-pref-modal .modal-alert').slideDown();
                    setTimeout(function() {
                        $('#update-pref-modal .modal-alert').slideUp();
                    }, 5000);
                } else {
                    refreshPage(function() {
                        $('#update-pref-modal').modal('hide');
                        $('#update-pref-modal #email').val('');
                        $('#update-pref-modal #password-old').val('');
                        $('#update-pref-modal #password').val('');
                        $('#update-pref-modal #password2').val('');
                        $('#update-pref-modal #delete-account').val('');
                        if (response == "Account deleted") {
                        	alertModal('Bye Bye', '<p>Sorry to see you go!</p><p>You can always sign up again to get the latest and greatest jokes!</p>');
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
    * click event - toggle reset password / sign in form
    *******************************************************************/
    $('body').on('click', '.forgot-password-link', function(e) {
        e.preventDefault();
        toggleForgotPasswordForm();
    });

    /*******************************************************************
    * click event - opens/closes update-pref-modal
    *******************************************************************/
    $('body').on('click', '#preferences-link', function(e) {
        e.preventDefault();
        $('#update-pref-modal').modal('show');
    });

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
    } else {
        $('.sidebar').addClass('sidebar-open');
        $('.sidebar-head').removeClass('sidebar-head-hidden');
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