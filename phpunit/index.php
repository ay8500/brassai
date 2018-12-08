<?php
/**
 * Created by PhpStorm.
 * User: Levi
 * Date: 07.12.2018
 * Time: 00:05
 */
include_once 'config.class.php';
include_once 'phpunit.class.php';

$pu = new \phpunit\phpunit();

?>
<html>
    <header>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="//www.gstatic.com/charts/loader.js"></script>
    </header>
    <body>
        <div class="container-fluid well">
            <h3><?php echo(\phpunit\config::$SiteTitle)?></h3>
            <div class="panel-body">
                <div><button class="btn btn-success" onclick="getTestFiles()">Check site for tests</button>
                <button class="btn btn-success" onclick="runAlltests()">Run all unit tests</button></div>
                <div id="filesGauge" style="display: inline-block;width: 700px; height: 400px;"></div>
                <div id="fileGauge" style="display: inline-block; width: 700px; height: 400px;"></div>
            </div>
            <div class="panel-body">
                <div>
                    Files:<span class="badge" style="background-color: green" id="fok">0</span><span class="badge" style="background-color: red" id="ferror">0</span>
                    Tests:<span class="badge" style="background-color: green" id="tok">0</span><span class="badge" style="background-color: red" id="terror">0</span>
                    Asserts:<span class="badge" style="background-color: green" id="aok">0</span><span class="badge" style="background-color: red" id="aerror">0</span>
                </div>
            </div>
            <div class="panel-body" id="console">
                <b>Console</b>
            <div>
        </div>
    </body>
</html>

<script type="text/javascript">
    google.charts.load("current", {packages:["corechart"]});
    google.charts.setOnLoadCallback(drawChart);

    var filesData;
    var filesChart;
    var filesOption;
    var fileData;
    var fileChart;
    var fileOption;
    var fileOk=0; var fileError=0;
    var testOk=0; var testError=0;
    var assertOk=0; var assertError=0;

    function drawChart() {
        filesData = google.visualization.arrayToDataTable([['Task', 'Files'],['',1]]);

        filesOption = {
            title: 'Unit Test files found:',
            allowHtml:true,
            pieHole: 0.4,
            colors: ['gray']
        };
        filesChart = new google.visualization.PieChart(document.getElementById('filesGauge'));
        function selectFilesHandler() {
            var selectedItem = filesChart.getSelection()[0];
            if (selectedItem) {
                aktTestNr=0;resetCounters();
                runTest(selectedItem.row);
            }
        }
        google.visualization.events.addListener(filesChart, 'click', selectFilesHandler);
        filesChart.draw(filesData, filesOption);

        fileData = google.visualization.arrayToDataTable([['Task','Tests' ],['',1]]);
        fileOption = {
            title: 'Asserts in current file:',
            allowHtml:true,
            pieHole: 0.4,
            colors: ['gray']
        };
        fileChart = new google.visualization.PieChart(document.getElementById('fileGauge'));
        function selectFileHandler() {
            var selectedItem = fileChart.getSelection()[0];
            if (selectedItem) {
                resetCounters();
                runTest(aktFileNr,selectedItem.row);
            }
        }
        google.visualization.events.addListener(fileChart, 'click', selectFileHandler);
        fileChart.draw(fileData, fileOption);

        <?php if(in_array($pu->getGetParam("action"),array("autorun","scanfiles"))) {?>
            getTestFiles();
        <?php }?>
    }

    var testFiles;

    function getTestFiles() {
        $.ajax({
            url:'ajaxGetTestFiles.php',
            type:'GET',
            success:function(data){
                testFiles=data;
                while (filesData.getNumberOfRows()>0)
                    filesData.removeRow(0);
                var i=0;
                data.forEach(function(testFile) {
                    filesOption.colors[i++]='blue';
                    filesData.addRow([testFile.file,1]);
                });
                filesOption.title ='Unit test files found:'+filesData.getNumberOfRows();
                filesChart.draw(filesData, filesOption );
                <?php if($pu->getGetParam("action")=="autorun") {?>
                    runAlltests();
                <?php }?>

            },
            error:function(error) {
                alert('error');
            }
        });
    }

    var aktFileNr=0;
    var aktTestNr=0;
    var aktFileError=false;

    function resetCounters() {
        fileOk=0; fileError=0;
        testOk=0; testError=0;
        assertOk=0; assertError=0;
    }

    function runAlltests() {
        aktFileNr=0;aktTestNr=0;aktFileError=false;
        resetCounters();
        console.log(aktFileNr+'-'+aktTestNr);
        for(var i=0;i<filesData.getNumberOfRows();i++) {
            filesOption.colors[i] = 'blue';
        }
        runTest();
    }

    function runTest(oneFileNr,oneTestNr) {
        if (oneFileNr!=null) {
            aktFileNr=oneFileNr;
        }
        if(oneTestNr!=null) aktTestNr=oneTestNr;
        var testFile=testFiles[aktFileNr];
        var result = Array;
        if (aktTestNr==0) {
            aktFileError=false;
            fileOption.title ='Test file:'+testFiles[aktFileNr].file;
            while (fileData.getNumberOfRows()>0)
                fileData.removeRow(0);
            fileData.addRow(["",1]);
            fileOption.colors[0]="blue";
            fileChart.draw(fileData, fileOption);
        }
        $.ajax({
            url:'ajaxUnitTestRun.php?file='+testFile.file+"&dir="+testFile.dir+"&testNr="+aktTestNr,
            type:'GET',
            success:function(data){
                if (aktTestNr == 0 || oneTestNr!=null) {
                    fileOption.title = 'Test file:' + testFiles[aktFileNr].file + '\nTest name:';
                    while (fileData.getNumberOfRows() > 0)
                        fileData.removeRow(0);
                    for (var i = 0; i < data.tests.length; i++) {
                        fileData.addRow([data.tests[i], 1]);
                        fileOption.colors[i] = "blue";
                    }
                }
                setTestResults(data);

                if (data.filestatus=="done") {
                    if (!aktFileError) {
                        filesOption.colors[aktFileNr] = 'green';
                        fileOk++;
                    } else {
                        filesOption.colors[aktFileNr] = 'red';
                        fileError++;
                    }
                    aktTestNr=0;
                    if (oneTestNr==null && oneFileNr==null) {
                        aktFileNr++;
                        if (aktFileNr < filesData.getNumberOfRows() ) {
                            runTest(oneFileNr,oneTestNr);
                        }
                    }

                }
                if (data.filestatus=="running") {

                    filesOption.colors[aktFileNr] = 'yellow';
                    aktTestNr++;
                    if (oneTestNr==null)
                        runTest(oneFileNr,oneTestNr);
                }
                if (data.filestatus=="error") {
                    setErrorToConsole(data.errorMessage);
                    filesOption.colors[aktFileNr] = 'red';
                    aktTestNr=0;aktFileNr++;fileError++;
                    if (aktFileNr < filesData.getNumberOfRows() && oneFileNr==null) {
                        runTest(oneFileNr,oneTestNr);
                    }
                };
                filesChart.draw(filesData, filesOption);
                showResultCounters();
            },
            error:function(error) {
                result.ok = false;
            }
        });
    }

    function setTestResults(data) {
        fileOption.title = 'Test file:' + testFiles[aktFileNr].file;//+'\nTest name:'+data.tests[aktTestNr].name;
        if (data.test == true) {
            fileOption.colors[aktTestNr] = 'green';
            testOk++;
            assertOk += data.assertOk;
        } else {
            fileOption.colors[aktTestNr] = 'red';
            testError++;
            aktFileError=true;
            assertOk += data.assertOk;
            assertError += data.assertError;
        }
        fileChart.draw(fileData, fileOption);
        if (data.echo.length>0) {
            var c= $("<span></span>");
            c.text(data.echo);
            $("#console").append(c);
        }
    }

    function setErrorToConsole(text) {
        var c= $('<div style="color:red"></div>');
        c.text(text);
        $("#console").append(c);
    }

    function showResultCounters() {
        $('#fok').text(fileOk);
        $('#ferror').text(fileError);
        $('#tok').text(testOk);
        $('#terror').text(testError);
        $('#aok').text(assertOk);
        $('#aerror').text(assertError);
    }

</script>