$(document).ready(function () {
    $('div.FCHolder div.FCData').children().each(function () {
        var FCRemoveButton = $('<button type="button" class="btn btn-danger FCRemove"><i class="glyphicon glyphicon-minus"></i></button>');
        FCRemoveButton.click(function (e) {
            e.preventDefault();
            $(this).parent().remove();
        });
        $(this).append(FCRemoveButton);
    });
    $('div.FCHolder button.FCAdd').click(function (e) {
        e.preventDefault();
        var totalItems = $(this).parent().children().children().length;
        var fData = $(this).parent().find('div.FCData').data('prototype');
        fData = fData.replace(/__name__label__/g, totalItems);
        fData = fData.replace(/__name__/g, totalItems);
        $(this).parent().find('div.FCData').append(fData);
        var FCRemoveButton = $('<button type="button" class="btn btn-danger FCRemove"><i class="glyphicon glyphicon-minus"></i></button>');
        FCRemoveButton.click(function (e) {
            e.preventDefault();
            $(this).parent().remove();
        });
        $(this).parent().find('div.FCData').children(':last').append(FCRemoveButton);
    });
});

function showContentBrowser(ele){
    var cbURL = $(ele).data('url');
    window.open(cbURL, "contentBrowserWindow", "scrollbars,resizable,width=800,height=600");
    return false;
}