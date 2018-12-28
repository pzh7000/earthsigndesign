<?php
/*
* Template Name: Projects Archive
*/
?>

<?php get_header(); ?>

<main role="main">
  <!-- section -->
  <section>
    <div class="page-content-archive">

      <h1 class="archive">Projects</h1>

      <div class="flex-container">

        <?php get_template_part('loop'); ?>

        <?php get_template_part('pagination'); ?>

      </div>

    </div>

  </section>
  <!-- /section -->
</main>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
