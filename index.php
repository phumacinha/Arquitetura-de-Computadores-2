<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="styles/style.css">
    <title>Document</title>
    <script src="http://code.jquery.com/jquery-3.4.1.min.js"></script>
</head>
<body>
    <form id="data" method="post">
        <div><input type="file" name="file" id="file" required></div>
        <div># of BHT entries
            <select name="m" id="m">
                <option value="2">2</option>
                <option value="4">4</option>
                <option value="8">8</option>
                <option value="16">16</option>
                <option value="32">32</option>
                <option value="64">64</option>
            </select></div>
        <div>BHT history size
            <select name="n" id="n">
                <option value="1">1</option>
                <option value="2">2</option>
            </select>
        </div>
        <div>Initial value
            <select name="initialValue" id="initialValue">
                <option value="T">TAKE</option>
                <option value="N">NOT TAKE</option>
            </select>
        </div>

        <input type="submit" value="Go!">
    </form>

    <script src="js/bht.js"></script>

    <div id="response"></div>
</body>
</html>