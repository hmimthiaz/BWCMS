$(document).ready(function () {
    $('button.FCAdd').click(function (e) {
        e.preventDefault();
        var totalItems = $(this).parent().children().children().length;
        var fData = $(this).parent().find('div.FCData').data('prototype');
        fData = fData.replace(/__name__label__/g, totalItems);
        fData = fData.replace(/__name__/g, totalItems);
        $(this).parent().find('div.FCData').append(fData);
    });


});