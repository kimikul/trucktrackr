$(document).ready(function() {
    $('#time input').ptTimeSelect({
        popupImage: 'Select Time'
    });

    // click behavior
    $.tablesorter.addWidget({
        id: "click",

        format: function(table) {
                $("tr:gt(0)").click(function() {
                $(".click").removeClass("click");
                $(this).addClass("click");
            });

        }
    });

    // hovering behavior
    $.tablesorter.addWidget({
        id: "hover",

        format: function(table) {
            $("tr",$(table)).hover(function () {
                $(this).addClass("hover");
            },
            function () {
                $(this).removeClass("hover");
            });
        }
    });

    $('.tablesorter').tablesorter(
    {
        widthFixed: true,
        headers: {
                2: {
                    sorter:'text'
                },
                4: {
                    sorter: false
                }
            },
        widgets: ['click', 'zebra', 'hover']
    });

    $("#filters").hide();

    $(".showHideFiltersLink").click(function()
    {
        $("#filters").slideToggle("slow");
        $(this).toggleClass("active");
    });

});

function checkAllTypes() {
    var value = false;
    if(document.search.type0.checked==true)
        value=true;
    document.search.type1.checked=value;
    document.search.type2.checked=value;
    document.search.type3.checked=value;
    document.search.type4.checked=value;
    document.search.type5.checked=value;
    document.search.type6.checked=value;
    document.search.type7.checked=value;
    document.search.type8.checked=value;
    document.search.type9.checked=value;
    document.search.type10.checked=value;
    document.search.type11.checked=value;
    document.search.type12.checked=value;
    document.search.type13.checked=value;
    document.search.type14.checked=value;
    document.search.type15.checked=value;
    document.search.type16.checked=value;
    document.search.type17.checked=value;
}

function useAddress(address) {
    address = address.replace(/_/g,' ');
    document.forms['search'].elements['address'].value = address;
}
