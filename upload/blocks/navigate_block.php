<?php
if ($_SESSION['loggedin'] == true) {
	$title = T_("NAVIGATION");
    $blockId = "b-" . sha1($title);
    ?>
    <div class="card">
    <div class="card-header">
        <?php echo $title ?>
        <a data-toggle="collapse" href="#" class="showHide" id="<?php echo $blockId; ?>" style="float: right;"></a>
    </div>
    <div class="card-body slidingDiv<?php echo $blockId; ?>">
    <!-- content -->
    <div class="list-group">
	<a href='<?php echo TTURL; ?>/index' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("HOME"); ?></a>
	<a href='<?php echo TTURL; ?>/topten' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("Top 10"); ?></a>
    <?php
if ($_SESSION["view_torrents"] == "yes" || !$config["MEMBERSONLY"]) {?>
	<a href='<?php echo TTURL; ?>/torrents/browse' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("BROWSE_TORRENTS"); ?></a>
	<a href='<?php echo TTURL; ?>/torrents/today' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("TODAYS_TORRENTS"); ?></a>
	<a href='<?php echo TTURL; ?>/torrents/search' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("SEARCH"); ?></a>
	<a href='<?php echo TTURL; ?>/torrents/needseed' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("TORRENT_NEED_SEED"); ?></a>
<?php }
    if ($_SESSION["edit_torrents"] == "yes") {?>
	<a href='<?php echo TTURL; ?>/torrents/import' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("MASS_TORRENT_IMPORT"); ?></a>
<?php }
    if ($_SESSION['loggedin'] == true && $_SESSION["view_users"] == "yes") {?>
	<a href='<?php echo TTURL; ?>/teams/index' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("TEAMS"); ?></a>
	<a href='<?php echo TTURL; ?>/group/member' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("MEMBERS"); ?></a>
<?php }?>
	<a href='<?php echo TTURL; ?>/rules' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("SITE_RULES"); ?></a>
	<a href='<?php echo TTURL; ?>/faq' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("FAQ"); ?></a>
<?php if ($_SESSION['loggedin'] == true && $_SESSION["view_users"] == "yes") {?>
	<a href='<?php echo TTURL; ?>/group/staff' class="list-group-item"><i class="fa fa-chevron-right"></i> <?php echo T_("STAFF"); ?></a>
<?php }?>
    </div>
	<!-- end content -->
	</div>
</div>
<br />
<?php
}