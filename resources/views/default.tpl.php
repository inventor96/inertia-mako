<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
	{{ raw:$tags->preload }}
	{{ raw:$tags->css }}
	{{ raw:$tags->js }}
</head>
<body>
	<noscript>Your browser does not support JavaScript or it is disabled. Please enable JavaScript to use this application.</noscript>
	<script data-page="app" type="application/json">{{ raw:$page }}</script>
	<div id="app"></div>
</body>
</html>