<?php

   Style::begin(Lang::T("Carousel"));  ?>
   <div id="recipeCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner" role="listbox">
             <?php $i=0; foreach ($data['sql'] as $row) {  ?>
             <?php if ($i==0) {$set_ = 'active'; } else {$set_ = ''; } ?>
    
             <div class='carousel-item <?php echo $set_; ?>'>
             <div class="col-md-1">
                <div class="card">
                    <div class="card-img">
                        <?php
                        if ($row["image1"] == 1){
                            $img = "".URLROOT."/assets/images/misc/default_avatar.png";
                        } elseif ($row["image1"] != ""){
                            $img = data_uri(UPLOADDIR."/images/".$row["image1"], $row['image1']);
                        }
                        ?> 
                        <a href='<?php echo URLROOT; ?>/torrent?id=<?php echo $row["id"] ?>'><img src="<?php echo $img; ?>"  width="100" height="250" ></a>
                        <div><font color="#00cc00"><b>S</b></font> <?php echo $row['seeders']; ?><font color="#FF0000"><b> L </b></font><?php echo $row['leechers']; ?></div>
                    </div>
                </div>
            </div>
            </div>
        <?php $i++; } ?>
    </div>
        <a class="carousel-control-prev bg-transparent w-aut" href="#recipeCarousel" role="button" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </a>
        <a class="carousel-control-next bg-transparent w-aut" href="#recipeCarousel" role="button" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </a>
    </div>
    <?php
    Style::end();
