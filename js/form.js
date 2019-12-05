function checkValuesOfN (mValues) {
    let selectInputN = $("form#ght-data select[name='n']");
    let selectInputM = $("form#ght-data select[name='m']");
    let mSelected = selectInputM.val();

    let options = '';
    let selected = '';
    mValues.forEach(function(m){
        selected = m == mSelected ? ' selected' : '';
        if (Math.pow(2, selectInputN.val()) <= m)
            options += '<option value="'+m+'"'+selected+'>'+m+'</option>';
    });

    selectInputM.html(options);
}

$(document).ready(function(){
    let mValues = [2, 4, 8, 16, 32, 64]
    checkValuesOfN(mValues)

    $("form#ght-data select[name='n']").on('change', function(){
        checkValuesOfN(mValues)
    })


    $('.file-group').click(function(){
        $(this).parent().find('.input-file')[0].click();
    });
    
    $('.input-file').change(function() {
        let fileName = $(this)[0].files[0].name;
        $(this).parent().find('.name-of-file').text(fileName);
    })

    // RESIZE IFRAME
    $('div#response iframe').on('load', function(){
        $(this).height($(this).contents().height()+"px");
    });
})