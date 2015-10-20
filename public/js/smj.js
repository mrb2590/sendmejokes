/**
 * SendMeJokes (http://www.sendmejokes.com/)
 */

$(document).ready(function() {
    bindEventListeners();
});

/*******************************************************************
* function to bind all event listeners
*******************************************************************/
function bindEventListeners() {
    /*******************************************************************
    * bind joke categories popover
    *******************************************************************/
    $(document).popover({
        selector: '.categories',
        html: true,
        placement: 'top',
        content: 'blank',
        trigger: 'focus'
    });

    /*******************************************************************
    * click event - opens/closes sidebar
    *******************************************************************/
    $(document).on('click', '.c-hamburger', function() {
        toggleSidebar();
    });

    /*******************************************************************
    * click event - closes sidebar when clicking outside of it
    *******************************************************************/
    $(document).on('click', '.main', function() {
        if ($('.sidebar').hasClass('sidebar-open')) {
            hideSidebar();
        }
    });

    /*******************************************************************
    * submit event - opens sign up pref modal
    *******************************************************************/
    $(document).on('submit', '#sign-up-form-email', function(e) {
        e.preventDefault();
        var email = $(this).find('#email').val()
        if (email == '' || email.indexOf('.') == -1 || email.indexOf('@') == -1) {
            alertModal('Oops', 'Invalid emaill address');
        } else {
            $('#home-signup-modal').modal('show');
            $('#sign-up-pref-form [name="email"]').val($('#sign-up-form-email [name="email"]').val());
        }
    });

    /*******************************************************************
    * submit event - ajax call to sign in
    *******************************************************************/
    $(document).on('submit', '#signin-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/sign-in',
            data: $(this).serialize() + "&submit=submit",
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
                    window.location.href = "/user/view/" + response;
                }
            }
        });
    });

    /*******************************************************************
    * click event - ajax call to sign out
    *******************************************************************/
    $(document).on('click', '#signout-link', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/sign-out',
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                window.location.replace("/");
            }
        });
    });

    /*******************************************************************
    * submit event - ajax call to create user
    *******************************************************************/
    $(document).on('submit', '#sign-up-pref-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/create-user',
            data: $(this).serialize() + "&submit=submit",
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
    $(document).on('submit', '#update-pref-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/update-user',
            data: $(this).serialize() + "&submit=submit",
            type: "POST",
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                $('html, body, input, textarea').css('cursor', 'auto');
                if (response == 'Username updated') {
                    alertModal('Success', '<p>Your username has been updated!</p>');
                    $(document).on('hidden.bs.modal', '#alert-modal', function () {
                        window.location.replace('/user/view/' + $('#username').val());// redirect when modal is closed
                    });
                } else if (response == "Account deleted") {
                    alertModal('Bye Bye', '<p>Sorry to see you go!</p><p>You can always sign up again to get the latest and greatest jokes!</p>');
                    $(document).on('hidden.bs.modal', '#alert-modal', function () {
                        window.location.replace('/');// redirect when modal is closed
                    });
                } else if (response == 'Success') {
                    $('#email').val('');
                    $('#password-old').val('');
                    $('#password').val('');
                    $('#password2').val('');
                    $('#delete-account').val('');
                    refreshPage(function() {
                        alertModal('Account Updated', '<p>You account has been updated</p>');
                    });
                } else {
                    $('#password').val('');
                    $('#password2').val('');
                    alertModal('Oops', '<p>' + response + '</p>');
                }
            }
        });
    });

    /*******************************************************************
    * submit event - ajax call to reset password
    *******************************************************************/
    $(document).on('submit', '#reset-password-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/reset-password',
            data: $(this).serialize() + "&submit=submit",
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
    $(document).on('submit', '#send-reset-password-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/send-reset-password-email',
            data: $(this).serialize() + "&submit=submit",
            type: "POST",
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                $('html, body, input, textarea').css('cursor', 'auto');
                if(response == "Success") {
                    $('#send-reset-password-form #email').val('');
                    $('#send-reset-password-form #password').val('');
                    $('#signin-modal').modal('hide');
                    alertModal('Reset Password', 'An email will be sent to reset your password');
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
    $(document).on('click', '.vote', function() {
        var upOrDown = ($(this).hasClass('up-vote')) ? 'up' : 'down';
        var oppositeUpOrDown = (upOrDown == 'up') ? 'down' : 'up';
        var color = ($(this).hasClass('up-vote')) ? 'green' : 'red';
        var oppositeColor = (color == 'green') ? 'red' : 'green';
        var voteCountObject = $(this).siblings('.vote-count');
        var oppositeVoteObject = $(this).siblings('.' + oppositeUpOrDown + '-vote');
        var jokeID = $(this).parents().eq(2).attr("id");
        var voteCount = parseInt(voteCountObject.html());
        var vote = 0; //holds their current vote status

        if ($(this).hasClass('fa-thumbs-o-' + upOrDown)) {
            //upvote/downvote
            $(this).removeClass('fa-thumbs-o-' + upOrDown);
            $(this).addClass('fa-thumbs-' + upOrDown);
            voteCount = (upOrDown == 'up') ? voteCount + 1 : voteCount - 1;
            //if currently up/down voted, remove it
            if (oppositeVoteObject.hasClass('fa-thumbs-' + oppositeUpOrDown)) {
                oppositeVoteObject.removeClass('fa-thumbs-' + oppositeUpOrDown);
                oppositeVoteObject.addClass('fa-thumbs-o-' + oppositeUpOrDown);
                voteCount = (upOrDown == 'up') ? voteCount + 1 : voteCount - 1; //removes the original downvote from count as well
            }
            vote = (upOrDown == 'up') ? 1 : -1;
            //flash count with color
            voteCountObject.removeClass('flash-' + oppositeColor);
            voteCountObject.removeClass('flash-' + color);
            voteCountObject.addClass('flash-' + color);
        } else {
            //remove upvote/downvote
            $(this).removeClass('fa-thumbs-' + upOrDown);
            $(this).addClass('fa-thumbs-o-' + upOrDown);
            voteCountObject.removeClass('flash-' + oppositeColor);
            voteCountObject.removeClass('flash-' + color);
            voteCount = (upOrDown == 'up') ? voteCount - 1 : voteCount + 1;
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
                    voteBox.removeClass('fa-thumbs-' + upOrDown);
                    voteBox.addClass('fa-thumbs-o-' + upOrDown);
                    voteCount = (upOrDown == 'up') ? voteCount - 1 : voteCount + 1; //update vote count in DOM
                    voteCountObject.html(voteCount); //update vote count in DOM
                }
            }
        });
    });

    /*******************************************************************
    * submit event - ajax call to add joke
    *******************************************************************/
    $(document).on('submit', '#add-joke-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/jokes/add',
            data: $(this).serialize() + "&submit=submit",
            type: "POST",
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                $('html, body, input, textarea').css('cursor', 'auto');
                if(response == "Success") {
                    $('#joke').val('');
                    $('#answer').val('');
                    uncheckTile($('#add-joke-form .checkbox-tile')); //will uncheck all tiles in this modal
                }
                alertModal('Alert', response);
            }
        });
    });

    /*******************************************************************
    * popover event - ajax call to get share buttons
    *******************************************************************/
    //$(document).on('inserted.bs.popover', '.share', function() {
    //    var joke_id = $(this).attr('id');
    //    $.ajax({
    //        context: this,
    //        url: '/jokes/get-share-buttons/',
    //        data: 'joke_id=' + joke_id + '&submit=submit',
    //        type: 'POST',
    //        success: function(response) {
    //            $(this).siblings('.popover').find('.popover-content').html(response)
    //            FB.XFBML.parse(this.parentNode);
    //        }
    //    });
    //});

    /*******************************************************************
    * click event - show/blur joke answer
    *******************************************************************/
    $(document).on('click', '.joke-answer', function() {
        if ($(this).hasClass('text-blurred')) {
            $(this).removeClass('text-blurred');
        } else {
            $(this).addClass('text-blurred');
        }
    });

    /*******************************************************************
    * popover event - appends categories to popover for joke
    *******************************************************************/
    $(document).on('inserted.bs.popover', '.categories', function() {
        var categories = $(this).siblings('.categories-popover').html();
        $(this).siblings('.popover').find('.popover-content').html(categories);
    });

    /*******************************************************************
    * submit event - ajax call to remove joke
    *******************************************************************/
    $(document).on('submit', '#remove-joke-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/jokes/remove',
            data: $(this).serialize() + "&submit=submit",
            type: "POST",
            beforeSend: function() {
                $('html, body, input, textarea').css('cursor', 'progress');
            },
            success: function(response) {
                $('html, body, input, textarea').css('cursor', 'auto');
                if(response == "Success") {
                    $('#joke_id').val('');
                }
                alertModal('Alert', response);
            }
        });
    });

    /*******************************************************************
    * submit event - ajax call get joke categories 
    *******************************************************************/
    $(document).on('submit', '#get-joke-categories-form', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/jokes/manage/',
            data: $(this).serialize(),
            type: "GET",
            cache: false,
            beforeSend: function() {
                $('#update-jokes-categories-well .panel-body').addClass('text-center');
                $('#update-jokes-categories-well .panel-body').css('line-height', '224px');
                $('#update-jokes-categories-well .panel-body').html('<i class="fa fa-spinner fa-pulse lg-font"></i>');
                $('#update-jokes-well').hide();
                $('#update-jokes-categories-well').show();
            },
            success: function(response) {
                $('#update-jokes-categories-well').html($('#update-jokes-categories-well', response).html());
            }
        });
    });

    /*******************************************************************
    * submit event - ajax call update joke categories 
    *******************************************************************/
    $(document).on('submit', '#update-joke-categories-form', function(e) {
        e.preventDefault();
        var joke_id = $('#hidden_joke_id').val();
        $.ajax({
            url: '/jokes/update-joke-categories/',
            data: $(this).serialize() + '&joke_id=' + joke_id + '&submit=submit',
            type: "POST",
            beforeSend: function() {
                $('#update-jokes-well form').addClass('text-center');
                $('#update-jokes-well form').css('line-height', '224px');
                $('#update-jokes-well form').html('<i class="fa fa-spinner fa-pulse lg-font"></i>');
            },
            success: function(response) {
                if (response == 'Success') {
                    refreshPage(function() {
                        alertModal('Success', 'Joke Categories have been updated');
                    });
                } else {
                    refreshPage(function() {
                        alertModal('Alert', response);
                    });
                }
            }
        });
    });

    /*******************************************************************
    * click event - ajax call reset update joke categories form
    *******************************************************************/
    $(document).on('click', '#update-jc-back-btn', function(e) {
        e.preventDefault();
        $('#hidden_joke_id').val('');
        $('#joke_id2').val('');
        $('#update-jokes-categories-well').hide();
        $('#update-jokes-well').show();
    });

    /*******************************************************************
    * scroll event - ajax call load more jokes 
    *******************************************************************/
    if (window.location.pathname.indexOf('/jokes') != -1 || window.location.pathname.indexOf('/search') != -1) {
        $(window).on('scroll', function(e) {
            e.stopPropagation()
            if($(window).scrollTop() + $(window).height() == $(document).height()) {
                if (+$('#page-number').val() < +$('#max-pages').val()) {
                    $('#joke-loader').html('<i class="fa fa-spinner fa-pulse"></i>');
                    var currentPage = $('#page-number').val();
                    $('#page-number').val(+$('#page-number').val() + 1);
                    var q = (window.location.search == '') ? '?' : '&';
                    $.ajax({
                        url: window.location.pathname + window.location.search + q + 'page=' + (+currentPage + 1),
                        type: "GET",
                        success: function(response) {
                            $('.masonry').last().after($('.masonry', response)[0].outerHTML);
                            $('#joke-loader').html('');
                        }
                    });
                }
            }
        });
    }

    /*******************************************************************
    * click event - toggle reset password / sign in form
    *******************************************************************/
    $(document).on('click', '.forgot-password-link', function(e) {
        e.preventDefault();
        toggleForgotPasswordForm();
    });

    /*******************************************************************
    * click event - opens/closes update-pref-modal
    *******************************************************************/
    //$(document).on('click', '#preferences-link', function(e) {
    //    e.preventDefault();
    //    $('#update-pref-modal').modal('show');
    //});

    /*******************************************************************
    * click event - checks the checkbox-tile
    *******************************************************************/
    $(document).on('click', '.checkbox-tile', function(e) {
        e.preventDefault();
        var exclude = ($(this).hasClass('add-exclude')) ? true : false;
        console.log(exclude);
        toggleTile($(this), exclude);
    });

    /*******************************************************************
    * hide event - resets sign in form when it is closed
    * this is for if the reset passsword form is shown,
    * don't want it to still be shown when clicking sign in later
    *******************************************************************/
    $(document).on('hidden.bs.modal', '#signin-modal', function() {
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
            $('#update-jokes-well').html($('#update-jokes-well', response).html());
            if(callback) {
	           callback();
           }
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
        $('.browse-button').removeClass('fixed-position');
    } else {
        $('.c-hamburger').addClass('is-active');
        $('.browse-button').addClass('fixed-position');
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
function toggleTile(checkboxTile, excludeFlag) {
    if (checkboxTile.hasClass('checked')) {
        checkboxTile.removeClass('checked');
        checkboxTile.children().children('input.add-category').prop('checked', false);
        if (excludeFlag) {
            checkboxTile.addClass('exclude');
            checkboxTile.children().children('input.exclude-category').prop('checked', true);
        }
    } else if (excludeFlag && checkboxTile.hasClass('exclude')) {
        checkboxTile.removeClass('exclude');
        checkboxTile.children().children('input.exclude-category').prop('checked', false);
    } else {
        checkboxTile.addClass('checked');
        checkboxTile.children().children('input.add-category').prop('checked', true);
    }
}

/*******************************************************************
* function - check checkbox tile
*******************************************************************/
function checkTile(checkboxTile) {
    if (!checkboxTile.hasClass('checked')) {
        checkboxTile.addClass('checked');
        checkboxTile.children().children('input.add-category').prop('checked', true);
    }
}

/*******************************************************************
* function - uncheck checkbox tile
*******************************************************************/
function uncheckTile(checkboxTile) {
    if (checkboxTile.hasClass('checked')) {
        checkboxTile.removeClass('checked');
        checkboxTile.children().children('input[type="checkbox"]').prop('checked', false);
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