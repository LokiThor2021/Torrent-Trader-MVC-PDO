<?php
function pager($rpp, $count, $href, $opts = array())
{
    $pages = ceil($count / $rpp);

    if (!$opts["lastpagedefault"]) {
        $pagedefault = 0;
    } else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0) {
            $pagedefault = 0;
        }
    }

    if (isset($_GET["page"])) {
        $page = (int) $_GET["page"];
        if ($page < 0) {
            $page = $pagedefault;
        }
    } else {
        $page = $pagedefault;
    }

    $pager = "";
    $mp = $pages - 1;
    $as = "<b>&lt;&lt;&nbsp;" . Lang::T("PREVIOUS") . "</b>";
    if ($page >= 1) {
        $pager .= "<a href=\"{$href}page=" . ($page - 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    } else {
        $pager .= $as;
    }

    $pager .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $as = "<b>" . Lang::T("NEXT") . "&nbsp;&gt;&gt;</b>";
    if ($page < $mp && $mp >= 0) {
        $pager .= "<a href=\"{$href}page=" . ($page + 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    } else {
        $pager .= $as;
    }

    if ($count) {
        $pagerarr = array();
        $dotted = 0;
        $dotspace = 3;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; $i++) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted) {
                    $pagerarr[] = "...";
                }

                $dotted = 1;
                continue;
            }
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = $start + $rpp - 1;
            if ($end > $count) {
                $end = $count;
            }

            $text = "$start&nbsp;-&nbsp;$end";
            if ($i != $page) {
                $pagerarr[] = "<a href=\"{$href}page=$i\"><b>$text</b></a>";
            } else {
                $pagerarr[] = "<b>$text</b>";
            }

        }
        $pagerstr = join(" | ", $pagerarr);
        $pagertop = "<p align=\"center\">$pager<br />$pagerstr</p>\n";
        $pagerbottom = "<p align=\"center\">$pagerstr<br />$pager</p>\n";
    } else {
        $pagertop = "<p align=\"center\">$pager</p>\n";
        $pagerbottom = $pagertop;
    }

    $start = $page * $rpp;

    return array($pagertop, $pagerbottom, "LIMIT $start,$rpp");
}