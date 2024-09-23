<html>
<head>
	<title>{{ $title }}</title>
	<link href="{{ $css }}" rel="stylesheet">
	<script src="{{ $js }}" defer></script>
</head>
<body>
	<div id="app" data-page='{{ attribute:$page }}'></div>
</body>
</html>