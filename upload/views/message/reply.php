<?php
usermenu($_SESSION["id"]);
include 'views/message/messagenavbar.php';
?>
            <br>
            <div><center>
            <form name="form" action="create" method="post">
            <label for="receiver">To</label>
        <input type="text" name="receiver" placeholder="To" value="<?php echo $username; ?>" id="receiver"><br>

        <label for="template">Template:</label>&nbsp;
            <select name="template">
            <option name='0'>---</option>
            <?php foreach ($ress1 as $stmt): ?>
                <option value="<?php echo $stmt['id']; ?>"><?php echo $stmt['subject']; ?></option>
            <?php endforeach;?>
            </select><br>
            <label for="name">Subject</label>
        <input type="text" name="subject" placeholder="Subject" value="<?php echo $row['subject']; ?>" id="subject">

        <?php require_once "helpers/bbcode_helper.php";
print textbbcode("form", "body", "$msg");?>
        <input type="submit"  name="Update" value="create">&nbsp;
        <label>Save Copy In Outbox</label>
        <input type="checkbox" name="save" checked='Checked'>&nbsp;
        <input type="submit" name="Update" value="draft">
        <input type="submit" name="Update" value="template">
       </form>
        </center></div>