/**
 * xRay
 *
 * 「xRay」学習用途向きのシンプルなテンプレート
 *
 * @category	template
 * @version 	1.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@lock_template 0
 * @internal 	@modx_category Demo Content
 * @internal    @installset sample
 */
<!DOCTYPE html>
<head>
  <base href="[(site_url)]">
  <meta charset="UTF-8">
  <title>[*pagetitle*]|[(site_name)]</title>
  <meta name="description" content="[*description*]">
  <meta name="keywords" content="[*キーワード*]">
  <link rel="stylesheet" type="text/css" href="assets/templates/xray/style.css">
  <link rel="stylesheet" type="text/css" href="assets/templates/xray/content.css">
</head>
<body>
<div class="wrap">
	<div class="header">
	<header>
	    <h1><img src="assets/templates/xray/images/header_image.png" alt="[(site_name)]" /></h1>
	</header>
	</div>
	<div class="navi">
	<nav>
	    [[Wayfinder
	    	&startId = `0` // ルート階層のリソースが対象
	    	&level   = `1` // １階層のみ
	    ]]
	</nav>
	</div>
	<div class="content">
	<nav>
	    [[TopicPath]]
	</nav>
	<article>
	    <h2>[*pagetitle*]</h2>
	    [*content*]
	</article>
	</div>
	<div class="footer">
	<footer>
	    (c)2012 [(site_name)]<br />
	    Mem : [^m^], MySQL: [^qt^], [^q^] request(s), PHP: [^p^], total: [^t^], document retrieved from [^s^].
	</footer>
	</div>
</div>
</body>
</html>
