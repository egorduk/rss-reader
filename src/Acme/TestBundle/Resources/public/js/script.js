$(document).ready(function(){

    var url = $("#form").attr("action");

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url,
        data: "loadActive=",
        success: function(response)
        {
            $.each(response.arrLoadActive, function(i,ind) {
                $(".checkSave").each(function() {
                    if ($(this).attr("value") == ind)
                    {
                        $(this).attr('checked','checked');
                    }
                });
            });
        }
    })


    /*$("#controlForm_sourceId").change(function()
    {
        var id = $(this).select().val();

        if (id != '')
        {
            var url = $("#form").attr("action");

            $.ajax({
                type: "POST",
                url: url,
                data: "editId=" + id,
                success: function(response)
                {
                    if (response != null)
                    {
                        $("#controlForm_fieldName").val(response.name);
                        $("#controlForm_fieldUrl").val(response.url);
                    }
                }
            })
        }
        else
        {
            $("#controlForm_fieldName").val("");
            $("#controlForm_fieldUrl").val("");
        }
    });*/


    $("#viewForm_Save").click(function()
    {
        var arrSaveInd = [];

        $(".checkSave").filter(':checked').each(function() {
            arrSaveInd.push($(this).attr("value"));
        });

        if (!arrSaveInd.length)
        {
            arrSaveInd = -1;
        }

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: url,
            data: "arrSaveInd=" + $.toJSON(arrSaveInd),
            success: function(response)
            {
                window.location.href = $(location).attr('href');
            }
        })
    });


    $("#viewForm_Delete").click(function()
    {
        var arrDeleteInd = [];

        $(".checkDelete").filter(':checked').each(function() {
            arrDeleteInd.push($(this).attr("value"));
        });

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: url,
            data: "arrDeleteInd=" + $.toJSON(arrDeleteInd),
            success: function(response)
            {
                window.location.href = $(location).attr('href');
            }
        })


    });



})