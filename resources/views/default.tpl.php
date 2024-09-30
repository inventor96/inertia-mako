<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
	<title>{{ $title }}</title>
	{{ raw:$tags->preload }}
	{{ raw:$tags->css }}
	{{ raw:$tags->js }}
</head>
<body>
	<div id="app" data-page='{{ raw:$page }}'></div>
</body>
</html>