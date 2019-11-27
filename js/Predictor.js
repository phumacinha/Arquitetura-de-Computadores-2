class Predictor {
    objeto = null;
    
    constructor (_m, _historySize, _initialValue, _steps, _selector) {
        this.m = _m;
        this.historySize = _historySize;
        this.initialValue = _initialValue;
        this.steps = _steps;
        this.selector = _selector;
        this.init = true;
        this.end = false;
        this.interval;

        this.running = false;
        this.iter = 0;
    }

    createOptions () {
        let thisClass = this;
        let options = '<div id="options">';
        options += '<input type="button" id="run" value="Run">'
        options += '<input type="button" id="stop" value="Stop">'
        options += '<input type="button" id="prev" value="Previous step">'
        options += '<input type="button" id="next" value="Next step">'
        options += '</div>'

        $(this.selector).on("click", "#options #run", function(){
            thisClass.run();
        })

        $(this.selector).on("click", "#options #stop", function(){
            thisClass.stop();
        })

        $(this.selector).on("click", "#options #next", function(){
            thisClass.stop();
            thisClass.nextStep();
        })

        $(this.selector).on("click", "#options #prev", function(){
            thisClass.stop();
            thisClass.prevStep();
        })
        return options;
    }

    createStatus () {
        return '<div id="status"><div id="iter"><b>Iteration:</b> <span>#</span></div><div id="address"><b>Address:</b> <span>0x</span></div><div id="index"><b>Index:</b> <span></span></div><div id="global-precision"><b>Global precision:</b> <span>'+(this.getGlobalPrecision()*100).toFixed(2) + '%</span></div></div>';
    }

    createTable () {
        this.stop();

        var table = '<table id="bht">';
        table += "<tr><th>Index</th><th>History</th><th>Prediction</th><th>Correct</th><th>Incorrect</th><th>Precision</th></tr>"; //header

        var history = this.historySize == 2 ? [this.initialValue, this.initialValue] : [this.initialValue];

        for (var i = 0; i < this.m; i++) {

            table += "<tr id=\""+i+"\">";
            table += this.formatLine("", i, history.join(", "), this.initialValue, 0, 0, 0);
            table += "</tr>"
        }

        table += '</table>';

        return this.createOptions() + this.createStatus() + table;
    }

    getLine (index=null) {
        if (index === null)
            return this.steps[this.iter];
        else
            return this.steps[index];
    }

    getStepsLength () {
        return this.steps.length;
    }

    selectTable () {
        return $(this.selector+ " table#bht");
    }

    formatLine (result, index, history, prediction, correct, incorrect, precision) {    
        return "<td class=\""+result+"\">"+index+"</td><td>"+history+"</td><td>"+prediction+"</td><td>"+correct+"</td><td>"+incorrect+"</td><td>"+precision.toFixed(2)+"%</td>";
    }

    alterTable (result, index, history, prediction, correct, incorrect, precision) {
        let table = this.selectTable();

        table.find(".true").removeClass("true");
        table.find(".false").removeClass("false");
        table.find("tr#"+index).html(this.formatLine(result, index, history, prediction, correct, incorrect, precision));
        table.find("tr#"+index).addClass(result.toString())
    }

    alterStatus (address, index) {
        let selectorStatus = $(this.selector).find("div#status");
        selectorStatus.find("div#iter span").text("#" + this.iter);
        selectorStatus.find("div#address span").text("0x" + address.toUpperCase());
        selectorStatus.find("div#index span").text(index);
    }

    nextStep () {
        this.init = false;
        this.showGlobalPrecision(false);

        if (this.iter < this.getStepsLength()) {
            let line = this.getLine();

            let result = line[0];
            let index = parseInt(line[1]);
            let history = line[2][1]["history"].join(", ");
            let prediction = line[2][1]["prediction"];
            let correct = line[2][1]["correct"];
            let incorrect = line[2][1]["incorrect"];
            let precision = parseFloat(line[2][1]["precision"])*100;

            this.alterTable(result, index, history, prediction, correct, incorrect, precision);
            
            this.alterStatus(line[3], index);

            this.iter++;
        }
        else {
            this.end = true;
            this.stop();
            this.showGlobalPrecision();
        }
    }

    prevStep () {
        this.end = false;
        this.showGlobalPrecision(false);

        if (this.iter > 0) {
            --this.iter;
            let line = this.getLine();
            console.log(line);

            let result = "";
            let index = parseInt(line[1]);
            let history = line[2][0]["history"].join(", ");
            let prediction = line[2][0]["prediction"];
            let correct = line[2][0]["correct"];
            let incorrect = line[2][0]["incorrect"];
            let precision = parseFloat(line[2][0]["precision"])*100;

            this.alterTable(result, index, history, prediction, correct, incorrect, precision);
            this.alterStatus(line[3], index);

            this.init = this.iter == 0 ? true : false;

            if (!this.init) {
                line = this.getLine(this.iter-1);
                result = line[0];
                index = parseInt(line[1]);
                history = line[2][1]["history"].join(", ");
                prediction = line[2][1]["prediction"];
                correct = line[2][1]["correct"];
                incorrect = line[2][1]["incorrect"];
                precision = parseFloat(line[2][1]["precision"])*100;

                this.alterTable(result, index, history, prediction, correct, incorrect, precision);
                this.alterStatus(line[3], index);
            }
            else
                this.alterStatus("", "");
        }
    }

    run () {
        let thisClass = this;

        if (!this.running) {
            this.interval = setInterval(function(){
                thisClass.running = true;
                thisClass.nextStep();
            }, 100);
        }
    }

    stop () {
        clearInterval(this.interval);
        this.running = false;
    }

    getGlobalPrecision () {
        let totalCorrect = 0;
        this.steps.forEach(function(line){
            if (line[0] === true) {
                totalCorrect++;
            }
        });

        return totalCorrect/this.steps.length;
    }

    showGlobalPrecision (type=true) {
        let selectorGlobalPrecision = $(this.selector).find("div#status div#global-precision");
        
        if (type)
            selectorGlobalPrecision.show();
        else
        selectorGlobalPrecision.hide();
    }
}