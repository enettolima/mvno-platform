<html>
	<head>
	</head>
	<body>
<script language="javascript" type="text/JavaScript">
        function getLog(log, lines) {
                var url = "getLogFile.php?log=" + log + "&lines=" + lines;
                request.open("GET", url, true);
                request.onreadystatechange = updatePage;
                request.send(null);
        }

        function tail(command,log,lines) {

								if (typeof timer !== 'undefined'){
                        clearTimeout(timer);
								}
								
                if (command == "error-log") {
                        document.getElementById("error-log").disabled = true;
                        document.getElementById("access-log").disabled = false;
                        document.getElementById("watchStop").disabled = false;
                        timer = setInterval(function() {getLog('/var/log/apache2/error.log',lines);},1000);
                } else if (command == "access-log") {
                        document.getElementById("error-log").disabled = false;
                        document.getElementById("access-log").disabled = true;
                        document.getElementById("watchStop").disabled = false;
												timer = setInterval(function() {getLog('/var/log/apache2/access.log',lines);},1000);
								} else {
                        document.getElementById("error-log").disabled = false;
                        document.getElementById("access-log").disabled = false;
                        document.getElementById("watchStop").disabled = true;
                        clearTimeout(timer);
                }
        }

        function updatePage() {
                if (request.readyState == 4) {
                        if (request.status == 200) {
                                var currentLogValue = request.responseText.split("\n");
                                eval(currentLogValue);

                                document.getElementById("log").innerHTML = currentLogValue;
                        }
                }
        }

        var request = (window.XMLHttpRequest) ? new XMLHttpRequest() : (window.ActiveXObject ? new window.ActiveXObject("Microsoft.XMLHTTP") : false);
</script>
<td style="width:100px;">Watch</td>
                        <td>
                                <input type="button" style="width:120px; 0px" id="error-log" name="error-log" value="Apache Error Log" onclick="tail('error-log','', '30');">
                                <input type="button" style="width:120px; 0px" id="access-log" name="access-log" value="Apache Access Log" onclick="tail('access-log','', '30');">
                                <input type="button" style="width:40px; 0px" id="watchStop" name="watch" value="Stop" disabled=true onclick="tail('stop','','');">
                        </td>

<div id="log" style="width:100%; height:90%; overflow:auto;"></div>
	</body>
</html>
