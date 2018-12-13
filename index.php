<!DOCTYPE html>
<html xml:lang="en">
<head>
<title>ACTA2 event list generation tool</title>
<?php
function holler($t) {
	return "<p style='display: table; margin: auto; border: dotted; padding: 1ex; background: black; color: red; font-size: larger'>$t</p>";
}
$conn = new mysqli('localhost', 'protester', 'fucktheEU', 'ACTA2');
$conn->set_charset('utf8');
if ($_SERVER['REQUEST_METHOD'] == 'POST')
	if ($_POST['pass'] == 'GuyFawkes') {
		$i = (int)$_POST['country'];
		$q = $conn->prepare('SELECT * FROM cities WHERE link = ?');
		$q->bind_param('s', $_POST['link']);
		if ($q->execute()) {
			$q->store_result();
			if ($i--)
				if ($q->num_rows) {
					$q = $conn->prepare('UPDATE cities SET city = ?, country = ? WHERE link = ?');
					$q->bind_param('sis', $_POST['city'], $i, $_POST['link']);
					$msg = $q->execute()
						? "The existing city's data have been updated."
						: "ERROR: Could not update the existing city's data.";
				} else {
					$q = $conn->prepare('INSERT INTO cities VALUES (?, ?, ?)');
					$q->bind_param('sis', $_POST['city'], $i, $_POST['link']);
					$msg = $q->execute()
						? "The new city's data have been saved."
						: "ERROR: Could not save the new city's data.";
			} elseif ($q->num_rows) {
				$q = $conn->prepare('DELETE FROM cities WHERE link = ?');
				$q->bind_param('s', $_POST['link']);
				$msg = $q->execute()
					? "The city's data have been deleted."
					: "ERROR: Could not delete the existing city's data.";
			} else $msg = 'The given city not found in the database. Deletion not performed.';
		} else $msg = "ERROR: Failed database lookup for the given city's potential data.";
	} else $msg = 'ERROR: Wrong password given. Database unmodified.';
$res = $conn->query('SELECT * FROM cities');
if (!$res) die('</head><body>' . holler('ERROR: Could not read cities from the database.') . '</body></html>');
?>
<script>
"use strict"
var
	$ = document.getElementById.bind(document),
	cities = [
<?php
	while ($row = $res->fetch_row())
		echo "['$row[0]', $row[1], '$row[2]'],";
?>
	],
	countries = [...[
		['🇧🇪 België / Belgique / Belgien', ['nl-BE', 'fr-BE', 'de-BE']],
		['🇨🇿 Česká republika', 'cs-CZ'],
		['🇩🇰 Danmark', 'da-DK'],
		['🇩🇪 Deutschland', 'de-DE'],
		['🇪🇪 Eesti', 'et-EE'],
		['🇪🇸 España', 'es-ES'],
		['🇫🇷 France', 'fr-FR'],
		['🇬🇮 Gibraltar', ['en-GI', 'en-GB']],
		['🇭🇷 Hrvatska', 'hr-HR'],
		['🇮🇹 Italia', 'it-IT'],
		['🇮🇪 Ireland / Éire', ['en-IE', 'ga-IE']],
		['🇱🇻 Latvija', 'lv-LV'],
		['🇱🇹 Lietuva', 'lt-LT'],
		['🇱🇺 Lëtzebuerg / Luxembourg / Luxemburg', ['ltz-LU', 'fr-LU', 'de-LU']],
		['🇭🇺 Magyarország', 'hu-HU'],
		['🇲🇹 Malta', ['mt-MT', 'en-MT']],
		['🇳🇱 Nederland', 'nl-NL'],
		['🇦🇹 Österreich', 'de-AT'],
		['🇵🇱 Polska', 'pl-PL'],
		['🇵🇹 Portugal', 'pt-PT'],
		['🇷🇴 România', 'ro-RO'],
		['🇸🇰 Slovensko', 'sk-SK'],
		['🇸🇮 Slovenija', 'sl-SI'],
		['🇫🇮 Suomi / Finland', ['fi-FI', 'sv-FI']],
		['🇸🇪 Sverige', 'sv-SE'],
		['🇬🇧 United Kingdom', 'en-GB'],
		['🇬🇷 Ελλάδα', 'el-GR'],
		['🇨🇾 Κύπρος / Kıbrıs', ['el-CY', 'tr-CY']],
		['🇧🇬 България', 'bg-BG']].entries()]
onload = () => {
	for (var [i, [country]] of countries)
		for (var e of ["country_first", "country"])
			$(e).appendChild(new Option(country, i + 1))
	$("country_first").selectedIndex = 18
}

function generate() {
	var
		copy = [...countries],
		[sel] = copy.splice($("country_first").selectedIndex, 1),
		list = ""
	for (var [i, [country, locales]] of [sel, ...copy]) {
		var inCountry = cities.filter(([, x]) => x == i)
		if (inCountry[0]) list += `

${country.toUpperCase()}:

` + inCountry.sort(([a], [b]) => a.localeCompare(b, sel[1][1])).map(([city,, link]) =>
	`• ${city}, ${Intl.DateTimeFormat(locales,
		{month: $("month").value, day: "numeric", hour: "numeric", minute: "2-digit", timeZoneName: "short"})
		.format(new Date("2019-01-19T13:00Z"))} – ` + link).join(`

`)
	}
	$("list").value = list.slice(2)
	$("list").style.height = $("list").scrollHeight + $("list").offsetHeight - $("list").clientHeight + "px"
	$("list").focus()
	$("list").select()
}
</script>
<style>
label {
	display: block
}
textarea {
	display: block;
	box-sizing: border-box;
	width: 100%
}
form label {
	display: flex;
	width: 100%;
	margin: 0.4em 0;
	align-items: center;
	white-space: nowrap
}
label * {
	margin-left: 1ex;
	flex: 1
}
</style>
</head>
<body>
<p>The /ACTA2 directory and its contents are only temporary. For reusable source code see <a href="https://github.com/ByteEater-pl/ACTA2-event-list-generation-tool">ByteEater's GitHub repository</a>.</p>
<?php if ($msg) echo holler($msg); ?>
<h1>Generate event list</h1>
<label>Select country (will be listed first):
<select id="country_first"></select>
</label>
<label>Select month format:
<select id="month">
<option>numeric</option>
<option>2-digit</option>
<option>narrow</option>
<option selected="">short</option>
<option>long</option>
</select>
</label>
<button onclick="generate()">Generate</button>
<label>Generated list of events to copy:
<textarea id="list" readonly=""></textarea>
</label>
<h1>Add or edit event</h1>
<p>To update or remove an existing event, use textually the same link.</p>
<form method="POST" enctype="multipart/form-data">
<label>Password: <input name="pass" type="password" required=""/></label>
<label>Select country:
<select id="country" name="country" required="" oninput="$('city').required = this.selectedIndex">
<option>none (cancel event)</option>
</select>
</label>
<label>City name:
<input id="city" name="city"/>
</label>
<label>Link:
<input name="link" type="url" required=""/>
</label>
<button>Submit</button>
</form>
</body>
</html>