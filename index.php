<!DOCTYPE html>
<html xml:lang="en-GB">
<head>
<title>#StopACTA2 event list generation tool</title>
<script>
"use strict"
<?php
function alert($t) {
	return "<p style='display: table; margin: auto; border: dotted; padding: 1ex; background: black; color: red; font-size: larger'>$t</p>";
}
function fatal($t) {
	die('</script></head><body>' . alert($t) . '</body></html>');
}
function escape($s) {
	return str_replace(array('`', '\\', '<', '&'), array('\`', '\u005C', '\u003C'. '\u0026'), $s);
}
$conn = new mysqli('localhost', 'protester', 'fucktheEU', 'ACTA2');
$conn->set_charset('utf8');
if ($_SERVER['REQUEST_METHOD'] == 'POST')
	if ($_POST['pass'] == 'GuyFawkes') {
		$date = str_replace('T', ' ', $_POST['date']) . ':00';
		if (isset($_POST['link'])) {
			$i = (int)$_POST['country'];
			$q = $conn->prepare('SELECT * FROM cities WHERE link = ?');
			$q->bind_param('s', $_POST['link']);
			if ($q->execute() && $q->store_result())
				if ($i--)
					if ($q->num_rows) {
						$q = $conn->prepare('UPDATE cities SET city = ?, country = ? WHERE link = ?');
						$q->bind_param('siss', $_POST['city'], $i, $_POST['link'], $_POST['date']);
						$msg = $q->execute()
							? "The existing city's data have been updated."
							: "ERROR: Could not update the existing city's data.";
					} else {
						$q = $conn->prepare('INSERT INTO cities VALUES (?, ?, ?, ?)');
						$q->bind_param('siss', $_POST['city'], $i, $_POST['link'], $_POST['date']);
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
			else $msg = "ERROR: Failed database lookup for the given city's potential data.";
		} else {
			$q = $conn->prepare('UPDATE settings SET date = ?');
			$q->bind_param('s', $date);
			$msg = $q->execute()
				? "The date has been updated."
				: "ERROR: Could not update the date.";
			$q = $conn->prepare('UPDATE general SET section = ? WHERE country = ? - 1');
			$q->bind_param('si', $_POST['general'], $_POST['country_first']);
			$msg .= $q->execute()
				? " The selected country's general section has been updated."
				: " ERROR: Could not update the selected country's general section.";
		}
	} else $msg = 'ERROR: Wrong password given. Database unmodified.';
$res = $conn->query('SELECT * FROM settings');
if (!$res) fatal('ERROR: Could not read date from the database.');
$row = $res->fetch_row();
echo 'const date =' . strtotime(str_replace(' ', 'T', $row[0]) . '.0+0');
$res = $conn->query('SELECT * FROM general');
if (!$res) fatal('ERROR: Could not read general sections from the database.');
echo ",
	sections = {";
while ($row = $res->fetch_row())
	if (!is_null($row[1]))
		printf('%s: `%s`,', $row[0], escape($row[1]));
echo '}';
$res = $conn->query('SELECT * FROM cities');
if (!$res) fatal('ERROR: Could not read cities from the database.');
echo ",
	cities = [";
while ($row = $res->fetch_row())
	printf('[`%s`, %u, `%s`, "%s"],', escape($row[0]), $row[1], escape($row[2]), $row[3]);
?>
],
	countries = [...[
		["🇧🇪 België / Belgique / Belgien", ["nl-BE", "fr-BE", "de-BE"]],
		["🇨🇦 Canada", ["en-CA", "fr-CA"]],
		["🇨🇿 Česká republika", "cs-CZ"],
		["🇩🇰 Danmark", "da-DK"],
		["🇩🇪 Deutschland", "de-DE"],
		["🇪🇪 Eesti", "et-EE"],
		["🇪🇸 España", "es-ES"],
		["🇫🇷 France", "fr-FR"],
		["🇬🇮 Gibraltar", ["en-GI", "en-GB"]],
		["🇭🇷 Hrvatska", "hr-HR"],
		["🇮🇪 Ireland / Éire", ["en-IE", "ga-IE"]],
		["🇮🇹 Italia", "it-IT"],
		["🇮🇸 Ísland", "is-IS"],
		["🇱🇻 Latvija", "lv-LV"],
		["🇱🇹 Lietuva", "lt-LT"],
		["🇱🇺 Lëtzebuerg / Luxembourg / Luxemburg", ["ltz-LU", "fr-LU", "de-LU"]],
		["🇭🇺 Magyarország", "hu-HU"],
		["🇲🇹 Malta", ["mt-MT", "en-MT"]],
		["🇳🇱 Nederland", "nl-NL"],
		["🇳🇴 Norge / Noreg / Norga", ["nb-NO", "nn-NO", "se-NO"]],
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
		["🇧🇬 България", "bg-BG"]].entries()],
	offset = 60000 * new Date().getTimezoneOffset(),
	$ = document.getElementById.bind(document)
onload = () => {
	for (const [i, [country]] of countries)
		for (const e of ["country_first", "country"])
			$(e).appendChild(new Option(country, i + 1))
	$("country_first").selectedIndex = 21
	updateSection()
	for (const input of document.querySelectorAll("[name=date]"))
		// Here and in the first form's onsubmit an exception will be thrown if the result is out of range.
		input.valueAsNumber = 1000 * date - offset
}

function updateSection() {
	const i = $("country_first").selectedIndex
	$("general").value = sections[i in sections ? i : 21]
	$("general").style.height = 0
	$("general").style.height = $("general").scrollHeight + "px"
}

function generate() {
	const
		copy = [...countries],
		[sel] = copy.splice($("country_first").selectedIndex, 1),
		sortBy = (array, f) =>
			array.sort((a, b) => f(a).localeCompare(f(b), sel[1][1]))
	$("list").value = $("general").value
	for (const [i, [country, locales]] of
		[sel, ...sortBy(copy, c => c[1][0].slice(5))]) {
			const inCountry = cities.filter(([, x]) => x == i)
			if (inCountry[0]) $("list").value += `

${country.toUpperCase()}:

` + sortBy(inCountry, c => c[0]).map(([city,, link, date]) =>
	`• ${city}, ${Intl.DateTimeFormat(locales,
		{month: $("month").value, day: "numeric", hour: "numeric", minute: "2-digit", timeZoneName: "short"})
		.format(new Date(date.replace(" ", "T") + "Z"))} – ` + link).join(`

`)
	}
	$("list").style.height = 0
	$("list").style.height = $("list").scrollHeight + $("list").offsetHeight - $("list").clientHeight + "px"
	$("list").focus()
	$("list").select()
}
</script>
<style>
label {
	display: block;
}
label * {
	margin-left: 1ex;
}
#list {
	box-sizing: border-box;
	width: 100%;
}
form label {
	display: grid;
	grid-template-columns: auto 1fr;
	margin: 0.4em 0;
}
#general {
	grid-area: 2 / 1 / 3 / 3;
}
</style>
</head>
<body>
<p>The /ACTA2 directory and its contents are only temporary. For reusable source code see <a href="https://github.com/ByteEater-pl/ACTA2-event-list-generation-tool">ByteEater's GitHub repository</a>.</p>
<?php if ($msg) echo alert($msg) ?>
<h1>Generate event list</h1>
<form method="POST" enctype="multipart/form-data" onsubmit="$('date').valueAsNumber += offset">
<label>Select country (will be listed first):
<select id="country_first" name="country_first" onchange="updateSection()"></select>
</label>
<label>General section (before first country) text for selected country (whose changing will overwrite this) or default:
<textarea id="general" name="general" required="" maxlength="21843"></textarea>
</label>
<label>Default date and time of the events:
<input name="date" type="datetime-local" required=""/>
</label>
<label>Password (if you wish to use the button):
<input name="pass" type="password" required=""/>
</label>
<button>Update the section for the country and the date on the server 🔃</button>
</form>
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
<label>Password:
<input name="pass" type="password" required=""/>
</label>
<label>Select country:
<select id="country" name="country" required="" onchange="$('city').required = this.selectedIndex">
<option>none (cancel event)</option>
</select>
</label>
<label>City name:
<input id="city" name="city" maxlength="63"/>
</label>
<label>Date and time:
<input name="date" type="datetime-local"/>
</label>
<label>Link:
<input name="link" type="url" required="" maxlength="333"/>
</label>
<button>Submit 🔃</button>
</form>
</body>
</html>
