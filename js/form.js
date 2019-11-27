var preditor = [];

$("#data").submit(function(e){
    var file_data = $("#file").prop('files')[0];   
    var form_data = new FormData(this);                  
    form_data.append('file', file_data);

    $.ajax({
        url: 'functionsPHP/executeBHT.php',
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,                         
        type: 'post',
        beforeSend: function () {
            $("#response").html('<span class="loading">Loading...</span>');
        },
        success: function(iter){
            $("#response").html("");
            let preditor = new Predictor(form_data.get("m"), form_data.get("historySize"), form_data.get("initialValue"), iter, "#response");
            $("#response").html(preditor.createTable());
        },
        error: function (iter) {
            console.log(iter.responseText);
        }
    });

    return false;
});