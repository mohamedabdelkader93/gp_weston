<?php get_header();?>
<section class="breadcumb-area">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="breadcumb">
                    <h4><?php echo single_post_title(); ?></h4>
                    <ul>
                        <li><a href="<?php echo site_url(); ?>"></a>Home</li> /
                        <li><?php echo single_post_title(); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="blog-area pb-100 pt-100" id="blog">
         <div class="container">
            <div class="row">
               <?php 
                  while(have_posts()){
                     the_post();
                     $archive_year  = get_the_time('Y'); 
                     $archive_month = get_the_time('m'); 
                     $archive_day   = get_the_time('d'); 
                     ?>
                     <div class="col-md-4">
                        <div class="single-blog">
                           <img src="<?php echo the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>" />
                           <div class="post-content">
                              <div class="post-title">
                                 <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                              </div>
                              <div class="pots-meta">
                                 <ul>
                         
                                    <li></li><a href="<?php echo get_day_link( $archive_year, $archive_month, $archive_day); ?>"><?php the_date('Y/m/d'); ?></a></li>
                                    <li><?php the_category(','); ?></li>
                                    <li><?php the_author_posts_link(); ?></li> 
                                 </ul>
                              </div>
                              <?php the_excerpt(); ?>
                              <a href="<?php the_permalink(); ?>" class="box-btn">read more <i class="fa fa-angle-double-right"></i></a>
                           </div>
                        </div>
                     </div>
              <?php 
               }
               ?>
             
       
            </div>
         </div>
      </section>
      <!-- Latest News Area End -->

<?php get_footer(); ?>