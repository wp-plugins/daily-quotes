<?php
/**
 * PHP File - Created on Sept, 26
 * @copyright Copyright 2014, poornam.com
 * @author    Bobcares
 *
 */
?>

<!-- Added division to display border color -->
<div style="border:10px solid <?php echo $instance['border'] ?>;">
    <div class="quote-widget-color<?php echo $instance['color'] ?>">
        <div class="quote-widget-title"><?php echo $title ?></div>


        <div class="quote-widget-author-details">
            <div class="quote-author-image">
                <img src="<?php echo $author->image ?>" alt="<?php echo $author->name ?> <?php __('Image', $this->plugin_slug) ?>">
            </div>

            <div class="quotes-author">
                <?php echo $author->name ?>

                <div class="quote-widget-author-lived"><?php echo $author->lived ?></div>

            </div>

            <div class="quote-widget-body">
                <?php echo $quote ?>
            </div>


        </div>
    </div>
