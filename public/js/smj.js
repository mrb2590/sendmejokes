$(document).ready(function() {
    bindEventHandlers();
});
/*******************************************************************
* for hamburger transformicon
*******************************************************************/
(function() {
    "use strict";
    var toggles = document.querySelectorAll(".c-hamburger");
    for (var i = toggles.length - 1; i >= 0; i--) {
        var toggle = toggles[i];
        toggleHandler(toggle);
    };
    function toggleHandler(toggle) {
        toggle.addEventListener( "click", function(e) {
            e.preventDefault();
            (this.classList.contains("is-active") === true) ? this.classList.remove("is-active") : this.classList.add("is-active");
        });
    }
})();

/*******************************************************************
* function to bind all event handlers
*******************************************************************/
function bindEventHandlers() {
    /*******************************************************************
    * click event - opens/closes sidebar
    *******************************************************************/
    $('.c-hamburger').on('click', function() {
        if (!$('.sidebar').hasClass('sidebar-open')) {
            $('.sidebar').addClass('sidebar-open');
        } else {
            $('.sidebar').removeClass('sidebar-open');
        }
        if (!$('.sidebar-head').hasClass('sidebar-head-hidden')) {
            $('.sidebar-head').addClass('sidebar-head-hidden');
        } else {
            $('.sidebar-head').removeClass('sidebar-head-hidden');
        }
    });

    /*******************************************************************
    * submit event - opens sign up pref modal
    *******************************************************************/
    $('#sign-up-form-email').on('submit', function(e) {
        e.preventDefault();
        $('#home-signup-modal').modal('show');
        $('#sign-up-pref-form [name="email"]').val($('#sign-up-form-email [name="email"]').val());
    });
}