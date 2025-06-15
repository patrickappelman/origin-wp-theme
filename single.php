<?php get_header(); 
global $post;
?>

<article class="post-single">

  

  <header class="post-single__header hero hero--post">

  <div class="hero__background">
    <?php echo get_the_post_thumbnail(); ?>
  </div>

    <div class="hero__container fade-up">

    <?php
    // Get the categories
    $categories = get_the_category();
    // If categories exist
    if( $categories ) {
      echo '<div class="hero__meta hero__meta--top">';
      $arr_cats = array();
      foreach($categories AS $cat) {
          echo "<a class='button button--outline button--light' href='" . get_category_link($cat) . "'>" . $cat->name . "</a>";
      }
      echo '</div>';
    }
    ?>
      <h1 class="post-single__title reverse text-display-5 w-full"><?php the_title(); ?></h1>
      <div class="hero__meta hero__meta--bottom">
        <div>
          <i class="fa-solid fa-user" alt="Author"></i> <?php echo get_the_author_meta('display_name', get_post_field('post_author', $post->ID)); ?>
        </div>
        <time datetime="<?php echo get_the_date("Y-m-d"); ?>">
          <i class="fa-solid fa-calendar-days" alt="Published On"></i>
          <?php echo get_the_date("F j, Y"); ?>
        </time>
      </div>
    </div>
  </header>

  <div class="post-single__body prose py-double 2xl:py-single px-half mx-auto lg:px-0 fade-up">
    <?php the_content(); ?>
  </div>

  <footer class="post-single__footer px-half lg:px-0">
    
    <?php 

    // Set terms array
    $arr_terms = array();

    // Add categories to terms array
    // Note: We already got the $categories variable at the beginning of this file.
    foreach($categories AS $cat) {
        $arr_terms[] = array(
            'name' => $cat->name,
            'link' => get_category_link($cat)
        );
    }

    // Get the languages
    $languages = get_the_terms($post->ID,'language');

    // Check for errors first, as 'language' is a custom taxonomy
    if (empty($languages->errors)) {
        // Add languages to terms array
        foreach($languages as $language) {

            $arr_terms[] = array(
                'name' => $language->name,
                'link' => get_term_link( intval($language->term_id),'language')
            );
        }
      
    }
    
    
    // Get the countries
    $countries = get_the_terms($post->ID,'country');

    // Check for errors first, as 'country' is a custom taxonomy
    if(empty($countries->errors)) {
      // Add countries to terms array
      foreach($countries as $country) {

          $arr_terms[] = array(
              'name' => $country->name,
              'link' => get_term_link( intval($country->term_id),'country')
          );
      }
    }
    

    // Get the industries
    $industries = get_the_terms($post->ID,'industry');

    // Check for errors first, as 'industry' is a custom taxonomy
    if(empty($industries->errors)) {
      // Add industries to terms array
      foreach($industries as $industry) {

          $arr_terms[] = array(
              'name' => $industry->name,
              'link' => get_term_link( intval($industry->term_id),'industry')
          );
      }
    }

    if($arr_terms) {

        echo '<section class="post-single__tags pb-double lg:pb-single 2xl:pb-half fade-up"><h6 class="mb-5 font-sans">Tags:</h6><ul class="tag-list">';

        foreach($arr_terms as $term) {
?>
        <li><a href="<?php echo $term['link'] ?>" class="tag-list__tag"><?php echo $term['name'] ?></a></li>
<?php
        }
        echo '</ul></section>';   
    }
    ?>

    <section class="post-single__share pb-double fade-up">
      <h6 class="mb-5 font-sans">Share This Article:</h6>
      <?php 
        global $wp;
        $share_url = urlencode( home_url( $wp->request ) );
        $share_title = urlencode( get_the_title() );
      ?>
      <ul class="share-list">
        <li>
          <a rel="nofollow" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" class="share-list__platform">
            <i class="fa-brands fa-facebook" alt="Facebook Logo"></i>
            <span class="hidden">Share on Facebook</span>
          </a>
        </li>
        <li>
          <a rel="nofollow" href="https://x.com/intent/post?text=<?php echo $share_title; ?>&url=<?php echo $share_url; ?>" class="share-list__platform">
            <i class="fa-brands fa-x-twitter" alt="X Logo"></i>
            <span class="hidden">Share on X</span>
          </a>
        </li>
        <li>
          <a rel="nofollow" href="https://www.linkedin.com/shareArticle?url=<?php echo $share_url; ?>" class="share-list__platform">
            <i class="fa-brands fa-linkedin" alt="LinkedIn Logo"></i>
            <span class="hidden">Share on LinkedIn</span>
          </a>
        </li>
        <li>
          <a rel="nofollow" href="mailto:?subject=<?php echo $share_title . urlencode(" - Origin Recruitment"); ?>&body=<?php echo $share_url; ?>" class="share-list__platform">
            <i class="fa-solid fa-envelope" alt="Email Icon"></i>
            <span class="hidden">Share via email</span>
          </a>
        </li>
        <?php /* <li>
          <a rel="nofollow" href="#" class="share-list__platform">
            <i class="fa-solid fa-link" alt="Link Icon"></i>
            <span class="hidden">Copy link to article</span>
          </a>
        </li> */ ?>
      </ul>
    </section>
  </footer>

<?php

//get the categories a post belongs to
$cats = get_the_category($post->ID);

$cat_array = array();

if ($cats) {
  foreach($cats as $key1 => $cat) {
    $cat_array[$key1] = $cat->slug;
  }
}

//get the tags a post belongs to
$tags = get_the_tags($post->ID);

$tag_array = array();
if ($tags) {
  foreach($tags as $key2 => $tag) {
    $tag_array[$key2] = $tag->slug;
  }
}


// construct the query arguments
$args = array(
    'posts_per_page' => 3,
    'orderby' => 'rand',
    'post__not_in' => array($post->ID),
    'tax_query' => array(
        'relation' => 'OR',
        array(
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => $cat_array,
            'include_children' => false 
        ),
        array(
            'taxonomy' => 'post_tag',
            'field' => 'slug',
            'terms' => $tag_array,
        )
    )
);

$the_query = new WP_Query( $args );

if ( $the_query->have_posts() ) {

?>
  <aside class="post-single__posts-archive pb-single fade-up">
    <header class="px-single text-center">
      <h3>More Insights from Origin</h3>
    </header>
    <section class="layout post-archive pt-double lg:pt-single">
<?php
  while ( $the_query->have_posts() ) {
    $the_query->the_post();

?>

<article class="post-archive__article">
  <a href="<?php the_permalink() ?>" class="post-archive__article-image-wrapper">
    <?php echo get_the_post_thumbnail(); ?>
  </a>
  <header>
    <div class="post-archive__article-meta-row">
      <time datetime="<?php echo get_the_date("Y-m-d"); ?>"><?php echo get_the_date("F j, Y"); ?></time>
      <span>•</span>
      <span>

<?php $categories = get_categories();
            if( $categories ) {
                $arr_cats = array();
                foreach($categories AS $cat) {
                    $arr_cats[] .= "<a class='post-archive__article-category hover:text-gold' href='" . get_category_link($cat) . "'>" . $cat->name . "</a>";
                }
                echo implode(", ", $arr_cats);
            }
?>
      </span>
    </div>
    <h4 class="post-archive__article-title"><?php the_title(); ?></h4>
  </header>
  <div class="post-archive__article-excerpt"><?php the_excerpt(); ?></div>
  <p><a href="<?php the_permalink() ?>" class="button">Read More →</a></p>
</article>

<?php
  }
?>
    </section>
  </aside>
<?php
}

wp_reset_postdata();

?>

</article>

<?php include('cta-bar.php'); ?>

<?php get_footer();