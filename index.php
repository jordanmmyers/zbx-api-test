<?php

include 'include/class.ZabbixAPI.php';
$zabbix = new ZabbixAPI();

if (isset($_POST['json'])) {
	$result = $zabbix->getData($_POST['method'], $_POST['json']);
}

?>

<html>

<head>
	<title>Zabbix API Tester</title>
	<link rel="stylesheet" type="text/css" href="include/css.css">
</head>

<body>

<span class="error">
	<?php foreach ($zabbix->errors as $error) {
		echo "{$error}<br />";
	} ?>
</span>
<div id="container">
<div class="left">
<form action="index.php" method="POST">
	<span class="top"><input type="text" name="method" placeholder="API Method" value="<?php echo (isset($_POST['method'])) ? $_POST['method'] : ""; ?>" /></span>
	<textarea name="json"><?php echo (isset($_POST['json'])) ? $_POST['json'] : ""; ?></textarea><br />

	<input type="submit" value="Submit" />
</form>
</div>
<div class="right">
<span class="top">Result:</span>
<pre><?php if (isset($result)) { echo json_encode($result, JSON_PRETTY_PRINT); } ?></pre>
</div>
</div>
</body>

<?php

// Don't forget to log out
unset($zabbix);

?>
