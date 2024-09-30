<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
	<title>{{ $title }}</title>
	<link href="{{ $css }}" rel="stylesheet">
	<script src="{{ $js }}" defer></script>
</head>
<body>
	<div id="app" data-page='{{ attribute:$page }}'></div>
</body>
</html>