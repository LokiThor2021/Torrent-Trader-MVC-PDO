<?php
class Announce
{
    // Get Date
    public static function get_date_time($timestamp = 0)
    {
        if ($timestamp) {
            return date("Y-m-d H:i:s", $timestamp);
        } else {
            return gmdate("Y-m-d H:i:s");
        }

    }

    // Get Time
    public static function gmtime()
    {
        return strtotime(self::get_date_time());
    }

    // Debug ( log error in database, for arrays use  $debug = json_encode($arr); write_log($debug); )
    public static function debug_log($text)
    {
        if (_DEBUG) {
            $log = json_encode($text);
            $added = self::get_date_time();
            DB::run("INSERT INTO sqlerr (time, txt) VALUES(?,?)", [$added, $log]);
        } else {
            //die(track("Error With Debug"));
        }
        return;
    }

    // quick response
    public static function response($list, $c = 0, $i = 0)
    {
        if (is_string($list)) { //Did we get a string? Return an error to the client
            return 'd14:failure reason' . strlen($list) . ':' . $list . 'e';
        }
        $p = ''; //Peer directory
        foreach ($list as $d) { //Runs for each client
            $pid = '';
            if (!isset($_GET['no_peer_id'])) { //Send out peer_ids in the reply
                $real_id = hex2bin($d[2]);
                $pid = '7:peer id' . strlen($real_id) . ':' . $real_id;
            }
            $p .= 'd2:ip' . strlen($d[0]) . ':' . $d[0] . $pid . '4:porti' . $d[1] . 'ee';
        }
        //Add some other paramters in the dictionary and merge with peer list
        $r = 'd8:intervali' . _INTERVAL . 'e12:min intervali' . _INTERVAL_MIN . 'e8:completei' . $c . 'e10:incompletei' . $i . 'e5:peersl' . $p . 'ee';
        return $r;
    }

    // Check Whats Connecting (no browsers allowed)
    public static function checkagent($agent)
    {
        if (preg_match("/^Mozilla|^Opera|^Links|^Lynx/i", $agent)) {
            die("No");
        }
        $stmt = DB::run("SELECT agent_name FROM clients")->fetchAll();
        $useragent = substr($_GET['peer_id'], 0, 8);
        foreach ($stmt as $bannedclient) {
            if (@strpos($useragent, $bannedclient) !== false) {
                die(self::response('Client is banned'));
            }
        }
        return $agent;
    }

    // Check Passkey
    public static function checkpasskey($passkey)
    {
        if (empty($passkey)) {
            die(self::response("no passkey"));
        } elseif (strlen($passkey) != 32) {
            die(self::response("Invalid passkey (" . strlen($passkey) . " - $passkey)"));
        }
        return $passkey;
    }

    // Get Respones From Client
    public static function checkClientFields()
    {
        // Is $_GET Empty
        if (empty($_GET)) {
            die(self::response("no get"));
        }
        // Needed Fields
        foreach (['info_hash', 'peer_id', 'port', 'uploaded', 'downloaded', 'left'] as $key) {
            $client[$key] = $_GET[$key];
            if (!isset($client[$key])) {
                die(self::response("missing key $client[$key]"));
            }
        }
        // Check info_hash & peer_id correct
        foreach (array("info_hash", "peer_id") as $key) {
            if (strlen($client[$key]) != 20) {
                die(self::response("short peer_id or hash"));
            }
        }
        // Check Correct Values
        foreach (['uploaded', 'downloaded', 'left'] as $key) {
            if (!is_numeric($client[$key]) || $client[$key] < 0) {
                die(self::response("wierd number"));
            }
        }
        // only event needed others options or old ??? no_peer_id in resp but rest not used main ones are info_hash, peer_id, ip, port, uploaded, downloaded, left, event
        foreach (['event', 'no_peer_id', 'compact', 'numwant', 'corrupt', 'key'] as $key) {
            $client[$key] = $_GET[$key];
        }
        foreach (['numwant', 'corrupt', 'no_peer_id', 'compact'] as $key) {
            if (!is_numeric($client[$key]) || $client[$key] < 0) {
                die(self::response("wrong option input"));
            }
        }
        // Check Event
        if (!in_array(strtolower($client['event']), ['started', 'completed', 'stopped', 'paused', ''])) {
            die(self::response("no event"));
        }
        // Check Port
        if (!ctype_digit($client['port']) || $client['port'] < 1 || $client['port'] > 65535) {
            die(self::response('Invalid client port'));
        }
        // Check IP
        $client['ip'] = $_SERVER["REMOTE_ADDR"];
        // bin2hex info_hash
        $client['info_hash'] = bin2hex($client['info_hash']);
        // bin2hex peer_id
        $client['peer_id'] = bin2hex($client['peer_id']);
        // Check 20 Byte Hex String
        if (strlen($client['info_hash']) != 40) {
            die(self::response("Invalid info hash hex value."));
        } elseif (strlen($client['peer_id']) != 40) {
            die(self::response("Invalid peer id hex value."));
        }
        return $client;
    }

    // Check User
    public static function UserCheck($passkey)
    {
        $stmt = DB::run("SELECT u.id, u.class, u.uploaded, u.downloaded, u.enabled, u.status, u.ip, u.passkey, g.can_download, g.maxslots
                FROM users u
                INNER JOIN `groups` g
                ON u.class = g.group_id
                WHERE u.passkey=? LIMIT 1", [$passkey]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            die(self::response("Cannot locate a user with that passkey!"));
        }
        if ($user["can_download"] == "no") {
            die(self::response("You do not have permission to download."));
        }
        if (!$user["passkey"] == $passkey) {
            die(self::response("Can NOT find user passkey."));
        }
        if (!$user["enabled"] == 'yes') {
            die(self::response("not enabled."));
        }
        if (!$user["status"] == 'confirmed') {
            die(self::response("status not confirmed."));
        }
        return $user;
    }

    // Check Torrent
    public static function TorrentCheck($info_hash)
    {
        $stmt = DB::run("SELECT id, info_hash, banned, freeleech, seeders + leechers
                    AS numpeers, UNIX_TIMESTAMP(added)
                    AS ts, seeders, leechers, times_completed
                    FROM torrents WHERE info_hash=?", [$info_hash]);
        $torrent = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$torrent) {
            die(self::response("Torrent not found on this hash = $info_hash"));
        }
        if ($torrent["banned"] == 'yes') {
            die(self::response("Torrent has been banned - hash = $info_hash"));
        }
        return $torrent;
    }

    // Check If Peer Already In Database
    public static function CheckIfPeer($torrentid, $peer_id, $passkey)
    {
        $peer = DB::run("SELECT seeder, UNIX_TIMESTAMP(last_action) AS ez, peer_id, ip, port, uploaded, downloaded, userid, passkey FROM peers
                 WHERE torrent = ? AND peer_id = ? AND passkey = ?", [$torrentid, $peer_id, $passkey])->fetch(PDO::FETCH_ASSOC);
        return $peer;
    }

    // Completed Event
    public static function Completed($userid, $torrentid)
    {
        DB::run("INSERT INTO completed (userid, torrentid, date) VALUES (?,?,?) ON DUPLICATE KEY UPDATE date = ?", [$userid, $torrentid, self::get_date_time(), self::get_date_time()]);
        DB::run("UPDATE LOW_PRIORITY `snatched` SET `completed` = ? WHERE `tid` = ? AND `uid` = ? AND `utime` = ?", [1, $torrentid, $userid, self::get_date_time()]);
        $completed = 1;
        return $completed;
    }

    // // Check Max Download Slots
    public static function MaxSlots($user = [])
    {
        $countslot = DB::run("SELECT DISTINCT torrent
                          FROM peers WHERE userid =? AND seeder=?", [$user['id'], 'no']);
        $slot = $countslot->rowCount();
        self::debug_log($slot);
        if ($slot >= $user['maxslots']) {
            die(self::response("Maximum Slot exceeded! You may only download $slot torrent at a time."));
        }
    }

    // Use Client To Insert New Peer
    public static function InsertPeer($passkey, $seeder, $userid, $agent, $torrent = [], $client = [])
    {
        DB::run("INSERT INTO peers (connectable, torrent, peer_id, ip, passkey, port, uploaded, downloaded, to_go, started, last_action, seeder, userid, client)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)", ['yes', $torrent['id'], $client['peer_id'], $client['ip'], $passkey, $client['port'], $client['uploaded'], $client['downloaded'], $client['left'], self::get_date_time(), self::get_date_time(), $seeder, $userid, $agent]);

    }

    // New Peer So Add Snatched
    public static function InsertSnatched($userid, $torrentid)
    {
        DB::run("INSERT INTO `snatched` (`uid`, `tid`, `stime`, `utime`) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE `utime` = ?", [$userid, $torrentid, self::gmtime(), self::gmtime(), self::gmtime()]);
    }

    // Current Peer So Update User
    public static function UpdateUser($userid, $upthis, $downthis = false)
    {
        if (!$downthis) {
            DB::run("UPDATE users SET uploaded = uploaded + ? WHERE id=?", [$upthis, $userid]);
        } else {
            DB::run("UPDATE users SET uploaded = uploaded + ?, downloaded = downloaded + ? WHERE id=?", [$upthis, $downthis, $userid]);
        }
    }

    // Current Peer So Update Snatched
    public static function UpdateSnatched($userid, $torrentid, $elapsed, $downthis, $upthis)
    {
        DB::run("UPDATE LOW_PRIORITY `snatched` SET `uload` = `uload` + '$upthis', `dload` = `dload` + '$downthis', `utime` = '" . self::gmtime() . "', `ltime` = `ltime` + '$elapsed' WHERE `tid` = ? AND `uid` = ?", [$torrentid, $userid]);
    }

    // Current Peer So Update User
    public static function DeletePeer($torrentid, $peer_id)
    {
        DB::run("DELETE FROM peers WHERE  torrent = ? AND peer_id = ?", [$torrentid, $peer_id]);
    }

    // Current Peer So Update Peer
    public static function UpdatePeer($passkey, $agent, $seeder, $torrentid, $client = [])
    {
        DB::run("UPDATE peers SET ip = ?, passkey = ?, port = ?, uploaded = ?, downloaded = ?, to_go = ?, last_action = ?, client = ?, seeder = ? WHERE torrent = ? AND peer_id = ?",
            [$client['ip'], $passkey, $client['port'], $client['uploaded'], $client['downloaded'], $client['left'], self::get_date_time(), $agent, $seeder, $torrentid, $client['peer_id']]);
    }

    // Update Torrent
    public static function UpdateTorrent($leechers, $seeders, $completed, $torrent = [])
    {
        if ($torrent["banned"] != "yes") {
            DB::run("UPDATE torrents SET last_action = ?, leechers = ?, seeders = ?, times_completed = ?, visible = ?
                 WHERE id=?", [self::get_date_time(), $leechers, $seeders, $completed, 'yes', $torrent['id']]);
        }
    }
}
