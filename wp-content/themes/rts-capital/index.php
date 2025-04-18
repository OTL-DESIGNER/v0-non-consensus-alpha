<?php get_header(); ?>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <?php if(have_posts()) : ?>
                <?php while(have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
                
                <div class="pagination">
                    <?php echo paginate_links(); ?>
                </div>
            <?php else : ?>
                <p>No content found.</p>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>

