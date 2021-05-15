<?php
function encodehtml($s, $linebreaks = true)
{
    $s = str_replace("<", "&lt;", str_replace("&", "&amp;", $s));
    if ($linebreaks) {
        $s = nl2br($s);
    }
    return $s;
}

function format_urls($s)
{
    return preg_replace(
        "/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^<>\s]+)/i",
        "\\1<a href='\\2' target='_blank'>\\2</a>", $s);
}

function format_comment($text)
{
    global $smilies, $smilies1;
    $s = $text;
    $s = htmlspecialchars($s);
    $s = format_urls($s);
    // [*]
    $s = preg_replace("/\[\*\]/", "<li>", $s);
    // [b]Bold[/b]
    $s = preg_replace("/\[b\]((\s|.)+?)\[\/b\]/", "<b>\\1</b>", $s);
    // [i]Italic[/i]
    $s = preg_replace("/\[i\]((\s|.)+?)\[\/i\]/", "<i>\\1</i>", $s);
    // [u]Underline[/u]
    $s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/", "<u>\\1</u>", $s);
    // [u]Underline[/u]
    $s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/i", "<u>\\1</u>", $s);
    // [img]http://www/image.gif[/img]
    $s = preg_replace("/\[img\]((http|https):\/\/[^\s'\"<>]+(\.gif|\.jpg|\.png|\.bmp|\.jpeg))\[\/img\]/i", "<img border='0' src=\"\\1\" alt='' />", $s);
    // [img=http://www/image.gif]
    $s = preg_replace("/\[img=((http|https):\/\/[^\s'\"<>]+(\.gif|\.jpg|\.png|\.bmp|\.jpeg))\]/i", "<img border='0' src=\"\\1\" alt='' />", $s);
    // [color=blue]Text[/color]
    $s = preg_replace(
        "/\[color=([a-zA-Z]+)\]((\s|.)+?)\[\/color\]/i",
        "<font color='\\1'>\\2</font>", $s);
    // [color=#ffcc99]Text[/color]
    $s = preg_replace(
        "/\[color=(#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])\]((\s|.)+?)\[\/color\]/i",
        "<font color='\\1'>\\2</font>", $s);
		
	// [color=rgb(255,0,0)] text [/color] i added 
    $s = preg_replace(
        "/\[color=(.+?)\]\s*((\s|.)+?)\s*\[\/color\]\s*/i",
        "<font color='\\1'>\\2</font>", $s);    
    // [url=http://www.example.com]Text[/url]      i added to fix '' issue
    $s = preg_replace(
        "/\[url=('(|http|ftp|https|ftps|irc):\/\/[^<>\s]+?)'\]((\s|.)+?)\[\/url\]/i",
        "<a href='\\1' target='_blank'>\\3</a>", $s);	
		
    // [url=http://www.example.com]Text[/url]
    $s = preg_replace(
        "/\[url=((http|ftp|https|ftps|irc):\/\/[^<>\s]+?)\]((\s|.)+?)\[\/url\]/i",
        "<a href='\\1' target='_blank'>\\3</a>", $s);
    // [url]http://www.example.com[/url]
    $s = preg_replace(
        "/\[url\]((http|ftp|https|ftps|irc):\/\/[^<>\s]+?)\[\/url\]/i",
        "<a href='\\1' target='_blank'>\\1</a>", $s);
    // [size=4]Text[/size]
    $s = preg_replace(
        "/\[size=([1-7])\]((\s|.)+?)\[\/size\]/i",
        "<font size='\\1'>\\2</font>", $s);
    // [font=Arial]Text[/font]
    $s = preg_replace(
        "/\[font=([a-zA-Z ,]+)\]((\s|.)+?)\[\/font\]/i",
        "<font face=\"\\1\">\\2</font>", $s);
    //[quote]Text[/quote]
    while (preg_match("/\[quote\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i", $s)) {
        $s = preg_replace(
            "/\[quote\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
            "<p class='sub'><b>Quote:</b></p><table class='main' border='1' cellspacing='0' cellpadding='10'><tr><td style='border: 1px black dotted'>\\1</td></tr></table><br />", $s);
    }
    //[quote=Author]Text[/quote]
    while (preg_match("/\[quote=(.+?)\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i", $s)) {
        $s = preg_replace(
            "/\[quote=(.+?)\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
            "<p class='sub'><b>\\1 wrote:</b></p><table class='main' border='1' cellspacing='0' cellpadding='10'><tr><td style='border: 1px black dotted'>\\2</td></tr></table><br />", $s);
    }
    // [spoiler]Text[/spoiler]
    $r = substr(md5($text), 0, 4);
    $i = 0;
    while (preg_match("/\[spoiler\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i", $s)) {
        $s = preg_replace("/\[spoiler\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i",
            "<br /><img src='assets/images/plus.gif' id='pic$r$i' title='Spoiler' onclick='klappe_torrent(\"$r$i\")' alt='' /><div id='k$r$i' style='display: none;'>\\1<br /></div>", $s);
        $i++;
    }
    // [spoiler=Heading]Text[/spoiler]
    while (preg_match("/\[spoiler=(.+?)\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i", $s)) {
        $s = preg_replace("/\[spoiler=(.+?)\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i",
            "<br /><img src='assets/images/plus.gif' id='pic$r$i' title='Spoiler' onclick='klappe_torrent(\"$r$i\")' alt='' /><b>\\1</b><div id='k$r$i' style='display: none;'>\\2<br /></div>", $s);
        $i++;
    }
    //[hide]Link[/hide]
    if (HIDEBBCODE) {
        $id = (int) Input::get("topicid");
        $reply = DB::run("SELECT * FROM forum_posts WHERE topicid=$id AND userid=$_SESSION[id]");
        if ($reply->rowCount() == 0) {
            $s = preg_replace(
                "/\[hide\]\s*((\s|.)+?)\s*\[\/hide\]\s*/i",
                "<p style='border: 3px solid red; width:50%'><font color=red><b>Please reply to view Links</b></font></p>",
                $s
            );
        }
    }
	
	// youtube 
	    $s = preg_replace(
        '#\[youtube\](.+)\[/youtube\]#isU',
        '<iframe width="250" height="140" src="http://www.youtube.com/embed/$1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>',
        $s
    );
	
    // [code]code text[/code]
$s = preg_replace("#\[code\](.+)\[/code\]#isU", 
    "<b> Code : </b><div style='border: 1px #3895D3 solid'><pre><code><div rows='10' style='max-height:400px;white-space: nowrap'  readonly='readonly'>$1</div></code></pre></div><br />", $s);
       // old test
   //$s = preg_replace("#\[code\](.+)\[/code\]#isU", 
   // "<b> Code : </b><div style='border: 1px #3895D3 solid'><pre><code><div rows='10' style='max-height:400px;'  readonly='readonly'>$1</div></code></pre></div><br />", $s);
    
    $s = preg_replace(
        "/\[color1=(.+?)\]\s*((\s|.)+?)\s*\[\/color1\]\s*/i",
        "<font color='\\1'>\\2</font>", $s);


    //[hr]
    $s = preg_replace("/\[hr\]/i", "<hr />", $s);
    //[hr=#ffffff] [hr=red]
    $s = preg_replace("/\[hr=((#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])|([a-zA-z]+))\]/i", "<hr color=\"\\1\"/>", $s);
    //[swf]http://somesite.com/test.swf[/swf]
    $s = preg_replace("/\[swf\]((www.|http:\/\/|https:\/\/)[^\s]+(\.swf))\[\/swf\]/i",
        "<param name='movie' value='\\1'/><embed width='470' height='310' src='\\1'></embed>", $s);
    //[swf=http://somesite.com/test.swf]
    $s = preg_replace("/\[swf=((www.|http:\/\/|https:\/\/)[^\s]+(\.swf))\]/i",
        "<param name='movie' value='\\1'/><embed width='470' height='310' src='\\1'></embed>", $s);
    // Linebreaks
    $s = nl2br($s);
    // Maintain spacing
    $s = str_replace("  ", " &nbsp;", $s);
    // Smilies
    // require_once "smilies_helper.php";
    reset($smilies);
    while (list($code, $url) = thisEach($smilies)) {
        $s = str_replace($code, '<img border="0" src="' . URLROOT . '/assets/images/smilies/' . $url . '" alt="' . $code . '" title="' . $code . '" />', $s);
    }
	reset($smilies1);
    while (list($code, $url) = thisEach($smilies1)) {
        $s = str_replace($code, '<img border="0" src="' . URLROOT . '/sceditor/' . $url . '" alt="' . $code . '" title="' . $code . '" />', $s);
    }
    /* todo php8
    if (OLD_CENSOR) {
        $r = DB::run("SELECT * FROM censor");
        while ($rr = $r->fetch(PDO::FETCH_LAZY)) {
            $s = preg_replace("/" . preg_quote($rr[0]) . "/i", $rr[1], $s);
        }
    } else {
        $f = @fopen("censor.txt", "r");
        if ($f && filesize("censor.txt") != 0) {
            $bw = fread($f, filesize("censor.txt"));
            $badwords = explode("\n", $bw);
            for ($i = 0; $i < count($badwords); ++$i) {
                $badwords[$i] = trim($badwords[$i]);
            }
            $s = str_replace($badwords, "<img src='" . URLROOT . "/assets/images/censored.png' border='0' alt='Censored' title='Censored' />", $s);
        }
        @fclose($f);
    }
    */
    return $s;
}