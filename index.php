<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,600,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/style.css">
    <title>BHT Simulator</title>
    <script src="js/jquery-3.4.1.min.js"></script>
</head>
<body>
    <header>BHT SIMULATOR</header>
    <menu>
        <form id="data" method="post">
            <div class="file">Select a file: <input type="file" name="file" id="file" required></div>
            
            <div class="lineSelects">
                <div># of indexes:
                    <select name="m" id="m">
                        <option value="2">2</option>
                        <option value="4">4</option>
                        <option value="8">8</option>
                        <option value="16">16</option>
                        <option value="32">32</option>
                        <option value="64">64</option>
                    </select>
                </div>
                <div>History size:
                    <select name="historySize" id="historySize">
                        <option value="1">1</option>
                        <option value="2">2</option>
                    </select>
                </div>
                <div>Initial value:
                    <select name="initialValue" id="initialValue">
                        <option value="T">TAKE</option>
                        <option value="N">NOT TAKE</option>
                    </select>
                </div>
            </div>
            

            <input type="submit" value="Simulate">
        </form>
    </menu>

    <script src="js/Predictor.js"></script>
    <script src="js/form.js"></script>

    <div id="response"></div>
</body>
</html>