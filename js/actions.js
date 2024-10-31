var page_actions = function($){

    var html_click_avail = true;

    $('.push-monkey-send-push').click(function(ev){

        ev.preventDefault();
        $('#push_monkey_confirmation_modal').modal();
    });

    $('.push_monkey_submit').click(function(ev){

        $('#push-monkey-send-push-form').submit();
    });

    /* PANELS */
    $(".panel-fullscreen").on("click",function(){
        panel_fullscreen($, $(this).parents(".panel"));
        return false;
    });
    /* EOF PANELS */

    /* MESSAGES LOADING */
    $(".messages .item").each(function(index){
        var elm = $(this);
        setInterval(function(){
            elm.addClass("item-visible");
        },index*300);
    });
    /* END MESSAGES LOADING */

    /* PAGE TABBED */
    $(".page-tabs a").on("click",function(){
        $(".page-tabs a").removeClass("active");
        $(this).addClass("active");
        $(".page-tabs-item").removeClass("active");
        $($(this).attr("href")).addClass("active");
        return false;
    });
    /* END PAGE TABBED */

    /* PAGE MODE TOGGLE */
    $(".page-mode-toggle").on("click",function(){
        page_mode_boxed();
        return false;
    });
    /* END PAGE MODE TOGGLE */
}

jQuery(document).ready(function($){

    page_actions($);
});

function page_mode_boxed(){
    $("body").toggleClass("page-container-boxed");
}

/* PANEL FUNCTIONS */
function panel_fullscreen($, panel){

    if(panel.hasClass("panel-fullscreened")){
        panel.removeClass("panel-fullscreened").unwrap();
        panel.find(".panel-body,.chart-holder").css("height","");
        panel.find(".panel-fullscreen .fa").removeClass("fa-compress").addClass("fa-expand");

        $(window).resize();
    }else{
        var head    = panel.find(".panel-heading");
        var body    = panel.find(".panel-body");
        var footer  = panel.find(".panel-footer");
        var hplus   = 30;

        if(body.hasClass("panel-body-table") || body.hasClass("padding-0")){
            hplus = 0;
        }
        if(head.length > 0){
            hplus += head.height()+21;
        }
        if(footer.length > 0){
            hplus += footer.height()+21;
        }

        panel.find(".panel-body,.chart-holder").height($(window).height() - hplus);


        panel.addClass("panel-fullscreened").wrap('<div class="panel-fullscreen-wrap"></div>');
        panel.find(".panel-fullscreen .fa").removeClass("fa-expand").addClass("fa-compress");

        $(window).resize();
    }
}
/* EOF PANEL FUNCTIONS */

/* NEW OBJECT(GET SIZE OF ARRAY) */
Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};
/* EOF NEW OBJECT(GET SIZE OF ARRAY) */
