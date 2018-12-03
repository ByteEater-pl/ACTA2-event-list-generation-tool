<!DOCTYPE html>
<html xml:lang="en">
<head>
<title>ACTA2 event list generation tool</title>
<script>
"use strict"
var
	$ = document.getElementById.bind(document),
	cities = [
<?php
$conn = new mysqli('localhost', 'protester', 'spisekprochowy', 'ACTA2');
$conn->set_charset('utf8');
$res = $conn->query('SELECT * FROM cities');
while ($row = $res->fetch_row())
	echo "['$row[0]', $row[1], '$row[2]'],";
?>
	],
	countries = [
		["🇧🇪 België / Belgique / Belgien", ["nl-BE", "fr-BE", "de-BE"]],
		["🇨🇿 Česká republika", "cs-CZ"],
		["🇩🇰 Danmark", "da-DK"],
		["🇩🇪 Deutschland", "de-DE"],
		["🇪🇪 Eesti", "et-EE"],
		["🇪🇸 España", "es-ES"],
		["🇫🇷 France", "fr-FR"],
		["🇬🇮 Gibraltar", ["en-GI", "en-GB"]],
		["🇭🇷 Hrvatska", "hr-HR"],
		["🇮🇹 Italia", "it-IT"],
		["🇮🇪 Ireland / Éire", ["en-IE", "ga-IE"]],
		["🇱🇻 Latvija", "lv-LV"],
		["🇱🇹 Lietuva", "lt-LT"],
		["🇱🇺 Lëtzebuerg / Luxembourg / Luxemburg", ["ltz-LU", "fr-LU", "de-LU"]],
		["🇭🇺 Magyarország", "hu-HU"],
		["🇲🇹 Malta", ["mt-MT", "en-MT"]],
		["🇳🇱 Nederland", "nl-NL"],
		["🇦🇹 Österreich", "de-AT"],
		["🇵🇱 Polska", "pl-PL"],
		["🇵🇹 Portugal", "pt-PT"],
		["🇷🇴 România", "ro-RO"],
		["🇸🇰 Slovensko", "sk-SK"],
		["🇸🇮 Slovenija", "sl-SI"],
		["🇫🇮 Suomi / Finland", ["fi-FI", "sv-FI"]],
		["🇸🇪 Sverige", "sv-SE"],
		["🇬🇧 United Kingdom", "en-GB"],
		["🇬🇷 Ελλάδα", "el-GR"],
		["🇨🇾 Κύπρος / Kıbrıs", ["el-CY", "tr-CY"]],
		["🇧🇬 България", "bg-BG"]]
onload = () => {
	for (var [country] of countries)
		$("country_first").appendChild(new Option(country))
	$("country_first").selectedIndex = 18
}

function generate() {
	var
		copy = [...countries.entries()],
		[sel] = copy.splice($("country_first").selectedIndex, 1),
		list = ""
	for (var [i, [country, locales]] of [sel, ...copy]) {
		var inCountry = cities.filter(([, x]) => x == i)
		if (inCountry[0]) list += `

${country.toUpperCase()}:

` + inCountry.sort(([a], [b]) => a.localeCompare(b, locales)).map(([city,, link]) =>
	`• ${city}, ${Intl.DateTimeFormat(sel[1][1],
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
</style>
</head>
<body>
<p>The /ACTA2 directory and its contents are only temporary.<!-- For reusable source code see <a href="https://github.com/ByteEater-pl/ACTA2-event-list-generation-tool">ByteEater's GitHub repository</a>.--></p>
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
<h1>Add event</h1>
TBD
</body>
</html>