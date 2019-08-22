$().ready(function () {
    //--------------------------------------------------------------------------
    $("#fileUpload").change(function () {
        var x = document.getElementById("fileUpload").files;
        if (x[0].type == "application/pdf") {
            var form = new FormData();
            form.append('arquivo', $('#fileUpload')[0].files[0]);
            //----------------------------------------------------------------------------------------------------------------------------------------
            $.ajax({
                type: 'POST',
                url: 'Home.php/teste',
                data: form,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.status !== true) {
                        $.notify(data.mensagem, data.notify);
                    }
                }, error: function (data) {
                    $.notify('Erro no Envio', "warning");
                }
            });
        } else {
            $.notify("Arquivo não suportado", "warning");
            $("#fileUpload").val("");
        }
    });
});