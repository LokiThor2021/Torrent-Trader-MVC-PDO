<?php
echo "<form name='shoutboxform' action='shoutbox?staff' method='post'>";
            echo "<center><table width='100%' border='0' cellpadding='1' cellspacing='1'>";
            echo "<tr class='shoutbox_messageboxback'>";
            echo "<td width='75%' align='center'>";
            echo "<input type='text' name='staffmsg' class='shoutbox_msgbox' />";
            echo "</td>";
            echo "<td>";
            echo "<input type='submit' name='submit' value='".T_("SHOUT")."' class='btn btn-sm btn-primary' />";
            echo "</td>";
            echo "<td>";
            echo '<a href="javascript:PopMoreSmiles(\'shoutboxform\', \'message\');"><small>'.T_("Smilies").'</small></a>';
            echo ' <small>-</small> <a href="javascript:PopMoreTags();"><small>'.T_("TAGS").'</small></a>';
            //echo "<br />";
            echo "<small>-</small> <a href='shoutbox'><small>".T_("REFRESH")."</small></a>";              
            echo " <small>-</small> <a href='".$config['SITEURL']."/shoutbox?history=1' target='_blank'><small>".T_("HISTORY")."</small></a>";
            echo "</td>";
            echo "</tr>";
            echo "</table></center>";
            echo "</form>";