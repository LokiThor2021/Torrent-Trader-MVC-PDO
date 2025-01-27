<p class="text-center"><a href='<?php echo URLROOT ?>/admincategories/add'><b>Add New Category</b></a></p><br />
<p class="text-center"><i>Please note that if no image is specified, the category name will be displayed</i></p><br />
<table class='table table-striped table-bordered table-hover'><thead><tr>
<th width='10'>Sort</th>
<th>Parent Cat</th>
<th>Sub Cat</th>
<th class='table_head'>Image</th>
<th width='30'></th>
</tr></thead></tbody>
<?php
while ($row = $data['sql']->fetch(PDO::FETCH_ASSOC)) {
    $id = $row['id'];
    $name = $row['name'];
    $priority = $row['sort_index'];
    $parent = $row['parent_cat'];
    print("<tr><td class='table_col1'>$priority</td><td class='table_col2'>$parent</td><td class='table_col1'>$name</td><td class='table_col2' align='center'>");
    if (isset($row["image"]) && $row["image"] != "") {
        print("<img border=\"0\" src=\"" . URLROOT . "/assets/images/categories/" . $row["image"] . "\" alt=\"" . $row["name"] . "\" />");
    } else {
        print("-");
    }
        print("</td><td class='table_col1'><a href=" . URLROOT . "/admincategories/edit?id=$id>[EDIT]</a> <a href='" . URLROOT . "/admincategories/delete?id=$id'>[DELETE]</a></td></tr>");
}
echo ("</tbody></table></center>");