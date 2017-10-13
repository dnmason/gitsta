<div id="post-<?php the_ID(); ?>" <?php post_class('item'); ?>>
    <h2 style="margin-top: 0;">
        <a href="<?php the_permalink(); ?>" name="<?php the_ID(); ?>" rel="bookmark" class="post-title"><?php the_title(); ?></a>
    </h2>
    <div name="meta">
        <p class="text-muted">
            <i class="fa fa-calendar"></i>
            <?php
            if(trim(get_the_title()) != "") {
                echo get_the_date();
            } else {
                echo '<a href="'. get_the_permalink() .'" name="'. get_the_ID() .'" rel="bookmark">'. get_the_date() .'</a>';
            }
            ?>

            <i class="fa fa-user" style="margin-left: 20px;"></i>
            <?php
            echo get_the_author(', ');
            ?>

            <i class="fa fa-folder-open" style="margin-left: 20px;"></i>
            <?php
            echo get_the_category_list(', ');
            ?>
        </p>
    </div>
</div>
