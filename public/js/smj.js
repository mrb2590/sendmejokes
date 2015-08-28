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
                        $('#home-signup-modal #email').val('');
                        $('#sign-up-form-email #email').val('');
                        $('#home-thankyou-modal').modal('show');
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