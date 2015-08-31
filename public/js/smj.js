$(document).ready(function() {
    bindEventHandlers();
});

/*******************************************************************
* function to bind all event handlers
*******************************************************************/
function bindEventHandlers() {
    /*******************************************************************
    * click event - opens/closes sidebar
    *******************************************************************/
    $('.c-hamburger').on('click', function() {
        toggleSidebar();
    });

    /*******************************************************************
    * submit event - opens sign up pref modal
    *******************************************************************/
    $('#sign-up-form-email').on('submit', function(e) {
        e.preventDefault();
        $('#home-signup-modal').modal('show');
        $('#sign-up-pref-form [name="email"]').val($('#sign-up-form-email [name="email"]').val());
    });

    /*******************************************************************
    * submit event - ajax call to sign in
    *******************************************************************/
    $('#signin-form').on('submit', function(e) {
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
                        reBindEventHandlers();
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
    $('#signout-link').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/sign-out',
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                $('html, body, input, textarea').css('cursor', 'auto');
                refreshPage(function() {
                    reBindEventHandlers();
                    hideSidebar();
                });
            }
        });
    });

    /*******************************************************************
    * submit event - ajax call to create user
    *******************************************************************/
    $('#sign-up-pref-form').on('submit', function(e) {
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
                        reBindEventHandlers();
                        $('#home-signup-modal').modal('hide');
                        $('#home-signup-modal #email').val('');
                        $('#home-signup-modal #password').val('');
                        $('#home-signup-modal #password2').val('');
                        $('#home-signup-modal input[type="checkbox"]').attr('checked', false);
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
    $('#update-pref-form').on('submit', function(e) {
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
                        reBindEventHandlers();
                        $('#update-pref-modal').modal('hide');
                        $('#update-pref-modal #email').val('');
                        $('#update-pref-modal #password-old').val('');
                        $('#update-pref-modal #password').val('');
                        $('#update-pref-modal #password2').val('');
                        if (response == "Account deleted") {
                        	alertModal('Bye Bye', '<p>Sorry to see you go!</p><p>You can always sign up again to get the latest and greatest jokes!</p>');
                        }
                    });
                }
            }
        });
    });

    /*******************************************************************
    * click event - opens/closes update-pref-modal
    *******************************************************************/
    $('#preferences-link').on('click', function(e) {
        e.preventDefault();
        $('#update-pref-modal').modal('show');
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
* function to re-bind all event handlers
*******************************************************************/
function reBindEventHandlers() {
    $('.c-hamburger').off();
    $('#sign-up-form-email').off();
    $('#signin-form').off();
    $('#signout-link').off();
    $('#sign-up-pref-form').off();
    $('#update-pref-form').off();
    $('#preferences-link').off();

    bindEventHandlers();
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