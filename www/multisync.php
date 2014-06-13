<html>
<head>
<?php include 'common/menuHead.inc'; ?>
<title>Falcon PI Player - FPP</title>
<?php

require_once("config.php");

?>
<script>
	function updateMultiSyncRemotes() {
		var remotes = "";
		$('input.remoteCheckbox').each(function() {
			if ($(this).is(":checked")) {
				if (remotes != "") {
					remotes += ",";
				}
				remotes += $(this).attr("name");
			}
		});

		$.get("fppjson.php?command=setSetting&key=MultiSyncRemotes&value=" + remotes
		).success(function() {
			$.jGrowl("Remote List Saved: '" + remotes + "'");
		}).fail(function() {
			DialogError("Save Remotes", "Save Failed");
		});
	}

	function parseFPPSystems(data) {
		$('#fppSystems tbody').empty();

		if (settings['fppMode'] == 'master') {
			$('#legend').append("<br>&#x2713; - Sync Remote FPP with this Master instance");
		}

		var remotes = [];
		if (typeof settings['MultiSyncRemotes'] !== 'undefined') {
			var tarr = settings['MultiSyncRemotes'].split(',');
			for (var i = 0; i < tarr.length; i++) {
				remotes[tarr[i]] = 1;
			}
		}

		for (var i = 0; i < data.length; i++) {
			var star = "";
			var link = "";
			if (data[i].Local)
			{
				link = data[i].HostName;
				star = "*";
			} else {
				link = "<a href='http://" + data[i].IP + "/'>" + data[i].HostName + "</a>";
				if ((settings['fppMode'] == 'master') &&
						(data[i].fppMode == "remote"))
				{
					star = "<input type='checkbox' class='remoteCheckbox' name='" + data[i].IP + "'";
					if (typeof remotes[data[i].IP] !== 'undefined')
						star += " checked";
					star += " onClick='updateMultiSyncRemotes();'>";
				}
			}

			var fppMode = 'Player';
			if (data[i].fppMode == 'bridge')
				fppMode = 'Bridge';
			else if (data[i].fppMode == 'master')
				fppMode = 'Master';
			else if (data[i].fppMode == 'remote')
				fppMode = 'Remote';

			var newRow = "<tr>" +
				"<td align='center'>" + star + "</td>" +
				"<td>" + link + "</td>" +
				"<td>" + data[i].IP + "</td>" +
				"<td>" + data[i].Platform + "</td>" +
				"<td>" + fppMode + "</td>" +
				"</tr>";
			$('#fppSystems tbody').append(newRow);
		}
	}

	function getFPPSystems() {
		$.get("/fppjson.php?command=getFPPSystems", function(data) {
			parseFPPSystems(data);
		});
	}

	function refreshFPPSystems() {
		setTimeout(function() { getFPPSystems(); }, 1000);
	}

	function setHostName() {
		$.get("fppjson.php?command=setSetting&key=HostName&value="
			+ $('#hostName').val()
		).success(function() {
			$.jGrowl("HostName Saved");
			refreshFPPSystems();
		}).fail(function() {
			DialogError("Save HostName", "Save Failed");
		});
	}
</script>
<style>
#fppSystems{
	border: 1px;
}

.masterHeader{
	width: 15%;
}

.masterValue{
	width: 40%;
}

.masterButton{
	text-align: right;
	width: 25%;
}
</style>
</head>
<body>
<div id="bodyWrapper">
	<?php include 'menu.inc'; ?>
	<br/>
	<div id="uilocalconfig" class="settings">
		<fieldset>
			<legend>Local Configuration</legend>
			<table width="100%">
				<tr>
					<td class='masterHeader'> FPPD Mode: </td>
					<td class='masterValue'>
				  	<select id="selFPPDmode" onChange="SetFPPDmode();">
							<option id="optFPPDmode_Player" value="2">Player (Standalone)</option>
							<option id="optFPPDmode_Master" value="6">Player (Master)</option>
							<option id="optFPPDmode_Remote" value="8">Player (Remote)</option>
							<option id="optFPPDmode_Bridge" value="1">Bridge</option>
						</select></td>
					<td class='masterButton'>&nbsp;</td>
				</tr>
				<tr>
					<td class='masterHeader'> FPPD Status: </td>
					<td class='masterValue' id = "daemonStatus"></td>
					<td class='masterButton'><input type="button" id="btnDaemonControl" class ="buttons" value="" onClick="ControlFPPD(); refreshFPPSystems();"></td>
				</tr>
				<tr>
					<td class='masterHeader'> FPP Time: </td>
					<td id = "fppTime" colspan = "3"></td>
				</tr>
				<tr>
					<td class='masterHeader'> Host Name: </td>
					<td colspan = "3">
						<input id='hostName' value='<? if (isset($settings['HostName'])) echo $settings['HostName']; else echo 'FPP'; ?>' size='20' maxlength='20'> <input type='button' class='buttons' value='Save' onClick='setHostName();'>
					</td>
				</tr>
			</table>
		</fieldset>
	</div>
	<br />
	<div id="uifppsystems" class="settings">
		<fieldset>
			<legend>FPP Systems</legend>
			<table id='fppSystems' cellspacing='5'>
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th>System Name</th>
						<th>IP Address</th>
						<th>Platform</th>
						<th>Mode</th>
					</tr>
				</thead>
				<tbody>
					<tr><td colspan=5 align='center'><b>Loading...</b></td></tr>
				</tbody>
			</table>
			<hr>
			<font size=-1>
				<span id='legend'>
				* - Local System
				</span>
			</font>
		</fieldset>
	</div>
</div>
<?php include 'common/footer.inc'; ?>

<script>

$(document).ready(function() {
	GetFPPDmode();
	setInterval(updateFPPStatus,1000)
	getFPPSystems();
});

</script>


</body>
</html>
