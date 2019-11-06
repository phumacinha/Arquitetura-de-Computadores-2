function BHT(m, n, initialValue, steps) {
    this.m = m;
    this.n = n;
    this.initialValue = initialValue;
    this.steps = steps;

    
}



function createTable (m, n, initialValue) {
    var table = '<table id="bht">';
    table += "<tr><th>Index</th><th>History</th><th>Prediction</th><th>Correct</th><th>Incorrect</th><th>Precision</th></tr>"; //header
    
    var history = n == 2 ? initialValue+", "+initialValue : initialValue;

    for (var i = 0; i < m; i++) {
        table += "<tr id=\""+(i+1)+"\"><td>"+(i+1)+"</td><td>"+history+"</td><td>"+initialValue+"</td><td>0</td><td>0</td><td>0%</td></tr>";
    }
    table += '</table>';

    return table;
}

function formatLine (line) {
    let result = line[0];
    let index = parseInt(line[1])+1;
    let history = line[2]["history"].join(", ");
    let prediction = line[2]["prediction"];
    let correct = line[2]["correct"];
    let incorrect = line[2]["incorrect"];
    let precision = parseFloat(line[2]["precision"])*100;

    return "<td class=\""+result+"\">"+index+"</td><td>"+history+"</td><td>"+prediction+"</td><td>"+correct+"</td><td>"+incorrect+"</td><td>"+precision.toFixed(2)+"%</td>";
}

function alterTable (index, line) {
    var table = $("#bht");
    table.find(".true").removeClass("true");
    table.find(".false").removeClass("false");
    table.find("#"+index).html(formatLine(line));
    table.find("tr#"+index).addClass(line[0].toString())
}

$("#data").submit(function(e){
    var file_data = $("#file").prop('files')[0];   
    var form_data = new FormData(this);                  
    form_data.append('file', file_data);

    $.ajax({
        url: 'functionsPHP/executeBHT.php', // point to server-side PHP script 
        dataType: 'json',  // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,                         
        type: 'post',
        success: function(iter){
            $("#response").html(createTable(form_data.get("m"), form_data.get("n"), form_data.get("initialValue")));
            var totalCorrect = 0;
            var c = 0;

            iter.forEach(function(line, index){
                setTimeout(function() {
                    alterTable((parseInt(line[1])+1), line)

                    if (line[0] === true) {
                        totalCorrect++;
                    }

                    c++;                    

                    if (c === iter.length) {
                        alert(totalCorrect*100/iter.length);
                    }


                }, 5 * (index));
            });

            
            
        }
    });

    return false
});