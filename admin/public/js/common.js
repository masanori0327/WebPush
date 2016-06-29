$(document).ready(function(){
    $('.btn-state').click(function () {
        var btn = $(this);
        btn.button('loading');
    });
    sinkFooter();
    $(window).resize(function() {
        sinkFooter();
    });
});

function sinkFooter(){
    var windowHeight = $(window).height();
    $("#footer").css("top","0px");
    var footerTop = $("#footer").offset().top;
    var footerHeight =$("#footer").height();
    if(footerHeight+footerTop<windowHeight){
        $("#footer").css("position","relative");
        $("#footer").css("top",windowHeight-footerHeight-footerTop+"px");
    }
}
