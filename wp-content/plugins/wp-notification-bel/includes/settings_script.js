(function ($) {
    $(document).ready(function () {


        var wnbell_height = $(".wnbell-dropdown-nav").siblings('li').height();

        if (wnbell_height !== null && wnbell_height != 0) {
            $(".wnbell-dropdown-nav").height(wnbell_height);


            $(".wnbell-dropdown-nav").css({
                'display': 'inline-flex',
                'align-items': 'center',
                'justify-content': 'center',
                'flex-wrap': 'wrap',
                'vertical-align': 'top'
            });
        }

    });
    // $(document).on('click', '.wnbell-closebtn-menu', function (e) {
    //     $('.wnbell-dropdown-menu').html('');
    //     $('.wnbell-dropdown-menu-wrap').hide();
    //     $('.wnbell_dropdown_list_ss').hide();
    //     //jQuery('body').css("overflow", "auto");


    //     $('.wnbell_dropdown_list_ss').html('');
    //     $('.wnbell-dropdown-menu').html('');
    // });
    // $(document).on('click', '.wnbell-closebtn', function (e) {
    //     $('.wnbell-dropdown-box').html('');
    //     $('.wnbell-dropdown-box-wrap').hide();
    //     //jQuery('body').css("overflow", "auto");
    // });

})(jQuery);
//const wnb_mediaQuery = window.matchMedia('(max-width: 768px)');
/* <fs_premium_only> */
function wnbell_test_ls() {
    var wnbell_st = 'wnbell_st';
    try {
        sessionStorage.setItem(wnbell_st, wnbell_st);
        sessionStorage.removeItem(wnbell_st);
        return true;
    } catch (e) {
        return false;
    }
}

var wnbell_play_lo = 0;
function wnbell_play_audio_lo(e) {
    if (wnbell_play_lo == 1) {
        if (!jQuery(e.target).parent().hasClass('wnbell-dropdown-menu') && !jQuery(e.target).parent().hasClass('wnbell-dropdown-box')) {
            if (typeof wnbell_test_ls === "function" && wnbell_test_ls() == true && (sessionStorage.getItem("wnbell_play_ls_lo") == "1"
                //    || sessionStorage.getItem("wnbell_play_ls_lo") === null
            )) {
                setTimeout(() => {
                    jQuery('#wnbell-sound').get(0).play();
                    sessionStorage.setItem("wnbell_play_ls_lo", "2");
                    wnbell_play_lo = 0;
                }, 2000);
            }
            else {
                setTimeout(() => {
                    jQuery('#wnbell-sound').get(0).play();
                    wnbell_play_lo = 0;
                }, 2000);

            }
        }
    }
}
var wnbell_play = 0;
var wnbell_unseen_count = 0;


function wnbell_play_audio(e) {
    if (wnbell_play == 1) {
        if (!jQuery(e.target).parent().hasClass('wnbell-dropdown-menu') && !jQuery(e.target).parent().hasClass('wnbell-dropdown-box')) {
            if (typeof wnbell_test_ls === "function" && wnbell_test_ls() == true && (sessionStorage.getItem("wnbell_play_ls") == "1"
                || sessionStorage.getItem("wnbell_play_ls") === null)) {
                setTimeout(() => {
                    jQuery('#wnbell-sound').get(0).play();
                    wnbell_play = 0;
                }, 2000);
            }
            else {
                setTimeout(() => {
                    jQuery('#wnbell-sound').get(0).play();
                    wnbell_play = 0;
                }, 2000);
            }
        }
        wnbell_play = 0;
    }
}
