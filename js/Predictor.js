class Predictor {    
    constructor (_m, _n, _historySize, _initialValue, _steps, _selector) {
        this.m = _m;
        this.n = _n;
        this.historySize = _historySize;
        this.initialValue = _initialValue;
        this.steps = _steps;
        this.selector = _selector;
        this.init = true;
        this.end = false;
        this.interval;

        this.running = false;
        this.iter = 0;

        $(this.selector).html(this.createTable());
    }

    createOptions () {
        var self = this

        let options = '<div class="col-auto px-0" id="options">'
        options += '<button id="run-fastly" class="btn">Run fastly</button>'
        options += '<button id="run-slowly" class="btn">Run slowly</button>'
        options += '<button id="stop" class="btn">Stop</button>'
        options += '<button id="prev" class="btn">Previous step</button>'
        options += '<button id="next" class="btn">Next step</button>'
        options += '<button id="reset" class="btn">Reset</button>'
        options += '</div>'


        $(this.selector).on("click", "#options #run-slowly", function(e) {
            e.stopImmediatePropagation();
            self.stop();
            self.run();
        })

        $(this.selector).on("click", "#options #run-fastly", function(e) {
            e.stopImmediatePropagation();
            self.stop();
            self.run(0);
        })

        $(this.selector).on("click", "#options #stop", function(e) {
            e.stopImmediatePropagation();
            self.stop();
        })

        $(this.selector).on("click", "#options #next", function(e) {
            e.stopImmediatePropagation();
             self.stop();
            self.nextStep();
        })

        $(this.selector).on("click", "#options #prev", function(e) {
            e.stopImmediatePropagation();
            self.stop();
            self.prevStep();
        })

        $(this.selector).on("click", "#options #reset", function(e) {
            e.stopImmediatePropagation();
            self.resetSimulator();
        })

        return options;
    }

    resetSimulator () {
        this.stop()
        this.running = false
        this.iter = 0
        
        $(this.selector).html(this.createTable());
    }

    createStatus () {
        return '<div class="col px-0 d-flex align-items-center justify-content-center" id="local">\
            <div class="content">\
                <div id="iter"><b>Iteration:</b> <span>#2</span></div>\
                <div id="address"><b>Address:</b> <span>0xB77B5D54</span></div>\
                <div id="index"><b>Index:</b> <span>8</span></div>\
            </div>\
        </div>'
    }

    createPercent () {
        return '<div class="col px-0 d-flex align-items-center justify-content-center" id="global">\
            <div class="content">\
                <div id="percent"><i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i><span>0.00%<span></div>\
                Global precision\
            </div>\
        </div>';
    }

    createHeader () {
        let header = '<div class="content rounded mx-auto d-table">\
        <div class="row justify-content-center mx-0">'
        header += this.createOptions()
        header += '<div class="col-auto" id="details">\
                <div class="row justify-content-center rounded-top d-flex align-items-center details-title">Details</div>\
                <div class="row rounded-bottom" id="status">'
        header += this.createStatus()
        header += this.createPercent()
        header += '</div></div></div>'

        return header
    }

    createTable () {
        this.stop();

        var table = '<div class="row justify-content-center">\
            <table id="ht" align="center">';
        table += "<tr>\
            <th><div>Index</div></th>\
            <th><div>History</div></th>\
            <th><div>Prediction</div></th>\
            <th><div>Correct</div></th>\
            <th><div>Incorrect</div></th>\
            <th><div>Precision</div></th>\
        </tr>"; //header

        var history = this.historySize == 2 ? [this.initialValue, this.initialValue] : [this.initialValue];

        for (var i = 0; i < this.m; i++) {

            table += "<tr id=\""+i+"\">";
            table += this.formatLine("", i, history.join(", "), this.initialValue, 0, 0, 0);
            table += "</tr>"
        }

        table += '</table>';

        return this.createHeader() + table;
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
        return $(this.selector+ " table#ht");
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
        let selectorStatus = $(this.selector).find("div#local");
        selectorStatus.find("div#iter span").text("#" + this.iter);
        selectorStatus.find("div#address span").text("0x" + address.toUpperCase());
        selectorStatus.find("div#index span").text(index);
    }

    alterPercent (percent, correct=null) {
        let selectorPercent = $(this.selector).find("div#global");
        selectorPercent.removeClass('true').removeClass('false')
        selectorPercent.find(".fa").hide()
        if (correct === true) { 
            selectorPercent.addClass('true')
            selectorPercent.find(".fa-caret-up").show()
        }
        else if (correct === false) {
            selectorPercent.addClass('false')
            selectorPercent.find(".fa-caret-down").show()
        }
        selectorPercent.find("div#percent span").text((percent*100).toFixed(2)+"%");
    }

    nextStep () {
        this.init = false;

        if (this.iter < this.getStepsLength()) {
            let line = this.getLine();

            let result = line[0];
            let index = parseInt(line[1]);
            let history = line[2][1]["history"].join(", ");
            let prediction = line[2][1]["prediction"];
            let correct = line[2][1]["correct"];
            let incorrect = line[2][1]["incorrect"];
            let precision = parseFloat(line[2][1]["precision"])*100;
            let globalPrecision = parseFloat(line[4]);

            this.alterTable(result, index, history, prediction, correct, incorrect, precision);
            
            this.alterStatus(line[3], index);
            this.alterPercent (globalPrecision, result)

            ++this.iter;

            if (this.iter == this.getStepsLength()) {
                this.end = true;
                this.stop();
            }
        }
        else {
            this.end = true;
            this.stop();
        }
    }

    prevStep () {
        this.end = false;

        if (this.iter > 0) {
            --this.iter;
            let line = this.getLine();

            let result = "";
            let index = parseInt(line[1]);
            let history = line[2][0]["history"].join(", ");
            let prediction = line[2][0]["prediction"];
            let correct = line[2][0]["correct"];
            let incorrect = line[2][0]["incorrect"];
            let precision = parseFloat(line[2][0]["precision"])*100;
            let globalPrecision = parseFloat(line[4]);

            this.init = this.iter == 0 ? true : false;

            this.alterTable(result, index, history, prediction, correct, incorrect, precision);
            this.alterStatus(line[3], index);
            this.alterPercent (globalPrecision)

            if (!this.init) {
                line = this.getLine(this.iter-1);
                result = line[0];
                index = parseInt(line[1]);
                history = line[2][1]["history"].join(", ");
                prediction = line[2][1]["prediction"];
                correct = line[2][1]["correct"];
                incorrect = line[2][1]["incorrect"];
                precision = parseFloat(line[2][1]["precision"])*100;
                let globalPrecision = parseFloat(line[4]);

                this.alterTable(result, index, history, prediction, correct, incorrect, precision);
                this.alterStatus(line[3], index);
                this.alterPercent (globalPrecision, result)
            }
            else {
                this.alterStatus("", "");
                this.alterPercent(0)
            }
        }
    }

    run (speed=100) {
        var self = this;

        if (!this.running) {
            this.interval = setInterval(function(){
                self.running = true;
                self.nextStep();
            }, speed);
        }
    }

    stop () {
        clearInterval(this.interval);
        this.running = false;
    }
}