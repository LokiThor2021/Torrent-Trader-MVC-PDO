<?php
class Comments extends Controller
{
    public function __construct()
    {
        // $this->userModel = $this->model('User');
    }

    public function index()
    {
        require_once "helpers/bbcode_helper.php";
        dbconn();
        global $config;
        if ($config["MEMBERSONLY"]) {
            loggedinonly();
        }

        $id = (int) ($_GET["id"] ?? 0);
        $type = $_GET["type"];
        $edit = (int) ($_GET["edit"] ?? 0);
        $delete = (int) ($_GET["delete"] ?? 0);

        if ($edit == 1 || $delete == 1 || $_GET["takecomment"] == 'yes') {
            loggedinonly();
        }

        if (!isset($id) || !$id || ($type != "torrent" && $type != "news")) {
            show_error_msg(T_("ERROR"), T_("ERROR"), 1);
        }

        if ($edit == '1') {
            $row = DB::run("SELECT user FROM comments WHERE id=?", [$id])->fetch();

            if (($type == "torrent" && $_SESSION["edit_torrents"] == "no" || $type == "news" && $_SESSION["edit_news"] == "no") && $_SESSION['id'] != $row['user']) {
                show_error_msg(T_("ERROR"), T_("ERR_YOU_CANT_DO_THIS"), 1);
            }

            $save = (int) $_GET["save"];

            if ($save) {
                $text = $_POST['text'];

                $result = DB::run("UPDATE comments SET text=? WHERE id=?", [$text, $id]);
                write_log(class_user_colour($_SESSION['username']) . " has edited comment: ID:$id");
                show_error_msg(T_("COMPLETE"), "Comment Edited OK", 1);
            }

            stdhead("Edit Comment");

            $arr = DB::run("SELECT * FROM comments WHERE id=?", [$id])->fetch();

            begin_frame(T_("EDITCOMMENT"));
            print("<center><b> " . T_("EDITCOMMENT") . " </b><p>\n");
            print("<form method=\"post\" name=\"comment\" action=\"comments?type=$type&amp;edit=1&save=1&amp;id=$id\">\n");
            print textbbcode("comment", "text", htmlspecialchars($arr["text"]));
            print("<p><input type=\"submit\"  value=\"Submit Changes\" /></p></form></center>\n");
            end_frame();
            stdfoot();
            die();
        }

        if ($delete == '1') {
            if ($_SESSION["delete_news"] == "no" && $type == "news" || $_SESSION["delete_torrents"] == "no" && $type == "torrent") {
                show_error_msg(T_("ERROR"), T_("ERR_YOU_CANT_DO_THIS"), 1);
            }

            if ($type == "torrent") {
                $res = DB::run("SELECT torrent FROM comments WHERE id=?", [$id]);
                $row = $res->fetch(PDO::FETCH_ASSOC);
                if ($row["torrent"] > 0) {
                    DB::run("UPDATE torrents SET comments = comments - 1 WHERE id = $row[torrent]");
                }
            }

            DB::run("DELETE FROM comments WHERE id =?", [$id]);
            write_log(class_user_colour($_SESSION['username']) . " has deleted comment: ID: $id");
            show_error_msg(T_("COMPLETE"), "Comment deleted OK", 1);
        }

        stdhead(T_("COMMENTS"));

        //take comment add
        if ($_GET["takecomment"] == 'yes') {
            $body = $_POST['body'];

            if (!$body) {
                show_error_msg(T_("ERROR"), T_("YOU_DID_NOT_ENTER_ANYTHING"), 1);
            }

            if ($type == "torrent") {
                DB::run("UPDATE torrents SET comments = comments + 1 WHERE id = $id");
            }

            $ins = DB::run("INSERT INTO comments (user, " . $type . ", added, text) VALUES (?, ?, ?, ?)", [$_SESSION["id"], $id, get_date_time(), $body]);

            if ($ins) {
                show_error_msg(T_("COMPLETED"), "Your Comment was added successfully.", 0);
            } else {
                show_error_msg(T_("ERROR"), T_("UNABLE_TO_ADD_COMMENT"), 0);
            }
        } //end insert comment

        //NEWS
        if ($type == "news") {
            $res = DB::run("SELECT * FROM news WHERE id =?", [$id]);
            $row = $res->fetch(PDO::FETCH_LAZY);

            if (!$row) {
                show_error_msg(T_("ERROR"), "News id invalid", 0);
                stdfoot();
            }

            begin_frame(T_("NEWS"));
            echo htmlspecialchars($row['title']) . "<br /><br />" . format_comment($row['body']) . "<br />";
            end_frame();
        }

        //TORRENT
        if ($type == "torrent") {
            $res = DB::run("SELECT id, name FROM torrents WHERE id =?", [$id]);
            $row = $res->fetch(PDO::FETCH_LAZY);

            if (!$row) {
                show_error_msg(T_("ERROR"), "News id invalid", 0);
                stdfoot();
            }

            echo "<center><b>" . T_("COMMENTSFOR") . "</b> <a href='$config[SITEURL]/torrents/read?id=" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</a></center><br />";
        }

        begin_frame(T_("COMMENTS"));
        $commcount = DB::run("SELECT COUNT(*) FROM comments WHERE $type =?", [$id])->fetchColumn();

        if ($commcount) {
            list($pagertop, $pagerbottom, $limit) = pager(10, $commcount, "/comments?id=$id&amp;type=$type&amp;");
            $commres = DB::run("SELECT comments.id, text, user, comments.added, avatar, signature, username, title, class, uploaded, downloaded, privacy, donated FROM comments LEFT JOIN users ON comments.user = users.id WHERE $type = $id ORDER BY comments.id $limit");
        } else {
            unset($commres);
        }

        if ($commcount) {
            print($pagertop);
            commenttable($commres, $type);
            print($pagerbottom);
        } else {
            print("<br /><b>" . T_("NOCOMMENTS") . "</b><br />\n");
        }

        echo "<center>";
        echo "<form name=\"comment\" method=\"post\" action=\"comments?type=$type&amp;id=$id&amp;takecomment=yes\">";
        echo textbbcode("comment", "body") . "<br />";
        echo "<input type=\"submit\"  value=\"" . T_("ADDCOMMENT") . "\" />";
        echo "</form></center>";

        end_frame();

        stdfoot();
    }

    public function torrent()
    {
        //require_once("helpers/bbcode_helper.php");
        dbconn();
        global $config;
        $id = (int) $_GET["id"];

        if (!is_valid_id($id)) {
            show_error_msg(T_("ERROR"), T_("THATS_NOT_A_VALID_ID"), 1);
        }
        //check permissions
        if ($config["MEMBERSONLY"]) {
            loggedinonly();
        }
        if ($_SESSION["view_torrents"] == "no") {
            show_error_msg(T_("ERROR"), T_("NO_TORRENT_VIEW"), 1);
        }
        //GET ALL MYSQL VALUES FOR THIS TORRENT
        $res = DB::run("SELECT torrents.anon, torrents.seeders, torrents.banned, torrents.leechers, torrents.info_hash, torrents.filename, torrents.nfo, torrents.last_action, torrents.numratings, torrents.name, torrents.owner, torrents.save_as, torrents.descr, torrents.visible, torrents.size, torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.external, torrents.image1, torrents.image2, torrents.announce, torrents.numfiles, torrents.freeleech, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.numratings, categories.name AS cat_name, torrentlang.name AS lang_name, torrentlang.image AS lang_image, categories.parent_cat as cat_parent, users.username, users.privacy FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN torrentlang ON torrents.torrentlang = torrentlang.id LEFT JOIN users ON torrents.owner = users.id WHERE torrents.id = $id");
        $row = $res->fetch(PDO::FETCH_ASSOC);
        $char1 = 50; //cut length
        $shortname = CutName(htmlspecialchars($row["name"]), $char1);
        //take comment add
        if ($_GET["takecomment"] == 'yes') {
            $body = $_POST['body'];

            if (!$body) {
                show_error_msg(T_("RATING_ERROR"), T_("YOU_DID_NOT_ENTER_ANYTHING"), 1);
            }

            DB::run("UPDATE torrents SET comments = comments + 1 WHERE id = $id");

            $comins = DB::run("INSERT INTO comments (user, torrent, added, text) VALUES (" . $_SESSION["id"] . ", " . $id . ", '" . get_date_time() . "', " . sqlesc($body) . ")");

            if ($comins) {
                autolink(TTURL . "/comments/torrent?id=$id", T_("COMMENT_ADDED"));
            } else {
                autolink(TTURL . "/comments/torrent?id=$id", T_("UNABLE_TO_ADD_COMMENT"));
            }

        } //end insert comment

        stdhead(T_("DETAILS_FOR_TORRENT") . " \"" . $row["name"] . "\"");
        begin_frame(T_("TORRENT_DETAILS_FOR") . " \"" . $shortname . "\"");
        require_once "views/torrent/torrentnavbar.php";
        require_once "helpers/bbcode_helper.php";
        //echo "<p align=center><a class=index href=$config[SITEURL]/torrents-comment.php?id=$id>" .T_("ADDCOMMENT"). "</a></p>\n";

        //  $subrow = $pdo->run("SELECT COUNT(*) FROM comments WHERE torrent = $id")->fetch();
        $commcount = DB::run("SELECT COUNT(*) FROM comments WHERE torrent = $id")->fetchColumn(); //$subrow[0];

        if ($commcount) {
            list($pagertop, $pagerbottom, $limit) = pager(10, $commcount, "comments/torrent?id=$id&amp;");
            $commquery = "SELECT comments.id, text, user, comments.added, avatar, signature, username, title, class, uploaded, downloaded, privacy, donated FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $id ORDER BY comments.id $limit";
            $commres = DB::run($commquery);
        } else {
            unset($commres);
        }

        if ($commcount) {
            print($pagertop);
            commenttable($commres, 'torrent');
            print($pagerbottom);
        } else {
            print("<br /><b>" . T_("NOCOMMENTS") . "</b><br />\n");
        }
        ?>
				<center>
				<form name="comment" method="post" action="<?php echo TTURL; ?>/comments/torrent?id=<?php echo $id; ?>&amp;takecomment=yes">
				<?php echo textbbcode("comment", "body"); ?><br>
				<input type="submit"  value="<?php echo T_("ADDCOMMENT"); ?>" >
				</form></center>
        <?php
        end_frame();
        stdfoot();
    }

}