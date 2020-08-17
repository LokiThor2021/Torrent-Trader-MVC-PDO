<?php
echo '<div class="table">';
echo '<div class="shoutbox_contain">
            <table class="table" border="0" style="width: 99%; table-layout:fixed">';
$query = 'SELECT * FROM shoutbox WHERE staff = 0 ORDER BY msgid DESC LIMIT 20';

$result = DB::run($query);
$alt = false;

while ($row = $result->fetch(PDO::FETCH_LAZY)) {
    if ($alt) {
        echo '<tr class="shoutbox_noalt">';
        $alt = false;
    } else {
        echo '<tr class="shoutbox_alt">';
        $alt = true;
    }

    // below shouts
    echo '<td style="font-size: 12px; width: 11%;">';
    // date, time, delete, user part
    echo "<div align='left' style='float: left'>";
    echo date('jS M,  g:ia', utc_to_tz_time($row['date']));
    $ol3 = DB::run("SELECT avatar FROM users WHERE id=" . $row["userid"])->fetch(PDO::FETCH_ASSOC);
    $av = $ol3['avatar'];
    if (!empty($av)) {
        $av = "<img src='" . $ol3['avatar'] . "' alt='my_avatar' width='20' height='20'>";
    } else {
        $av = "<img src='images/default_avatar.png' alt='my_avatar' width='20' height='20'>";
    }
    if ($row['userid'] == 0) {
        $av = "<img src='images/default_avatar.png' alt='default_avatar' width='20' height='20'>";
    }
    // message part
    echo '</td><td>'.$av.'<a href="' . $config['SITEURL'] . '/users/profile?id=' . $row['userid'] . '" target="_parent"><b>' . class_user_colour($row['user']) . ':</b></a>&nbsp;&nbsp;' . nl2br(format_comment($row['message']));
    
    echo '<divclass="float-right">';
        //echo "&nbsp<a href='" . $config['SITEURL'] . "/shoutbox?reply=" . $row['msgid'] . "' style='font-size: 12px'>[R]</a>";        
        if ($_SESSION['class'] > $config['Uploader']) {
            echo "&nbsp<a href='" . $config['SITEURL'] . "/shoutbox?edit=" . $row['msgid'] . "' style='font-size: 12px'>[E]</a>";
            echo "&nbsp<a href='" . $config['SITEURL'] . "/shoutbox?delete=" . $row['msgid'] . "' style='font-size: 12px'>[D]</a>";
        } elseif ($_SESSION['username'] == $row['user']) {
            $now = gmtime();
            $ts = strtotime($row["date"]);
            if ($ts + 300 > $now){
                echo "&nbsp<a href='" . $config['SITEURL'] . "/shoutbox?quickedit=" . $row['msgid'] . "' style='font-size: 12px'>[E]</a>";  
            }           
        }

    echo "</div>";
    
    echo '</td></tr>';
}

echo '</table></div><br/>';
