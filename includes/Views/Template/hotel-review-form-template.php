<?php
namespace ADReviewManager\Views;
use ADReviewManager\Classes\Helper;
use ADReviewManager\Services\ArrayHelper as Arr;
/**
 * Return a html review template for the food review form
 * get the reviews and form data and render the template
 */
$per_page = Arr::get($pagination, 'per_page');
$enablePagination = Arr::get($pagination, 'enable');
if ($enablePagination == 'true') {
    $total_page = ceil($total_reviews / $per_page);
}
function getAverageRatingByCategories ($reviews) {
    $categoriesReview = [];
    $total_average_rating = 0;
    $summary_by_rating = [];
    foreach ($reviews as $review) {
        $ratings = Arr::get($review, 'meta.formFieldData.ratings', []);
        $total_average_rating += Arr::get($review, 'average_rating', 0);
        foreach ($ratings as $rating) {
            $label = Arr::get($rating, 'label');
            $value = Arr::get($rating, 'value');
            if (!isset($categoriesReview[$label])) {
                $categoriesReview[$label] = [];
            }
            
            $categoriesReview[$label]['total_rating'] = Arr::get($categoriesReview[$label], 'total_rating', 0) + $value;
            $categoriesReview[$label]['count_ratings'] = Arr::get($categoriesReview[$label], 'count_ratings', 0) + 1;
            $summary_by_rating[$value] = Arr::get($summary_by_rating, $value, 0) + 1;

        }
    }

    return array(
        'categoriesReview' => $categoriesReview,
        'total_average_rating' => number_format($total_average_rating / count($reviews), 2),
        'summary_by_rating' => $summary_by_rating
    );
}

function getReviewLabel ($rating) {
    if ($rating >= 4.5) {
        return 'Excellent';
    } else if ($rating >= 4) {
        return 'Very Good';
    } else if ($rating >= 3.5) {
        return 'Good';
    } else if ($rating >= 3) {
        return 'Fair';
    } else if ($rating >= 2.5) {
        return 'Average';
    } else if ($rating >= 2) {
        return 'Below Average';
    } else {
        return 'Poor';
    }
}
?>
<div data-form-id="<?php echo $form->ID ?>" class="review-template_settings_wrapper">
    <div class="review-template">
        <?php
        if (empty($reviews)) {
            echo '<p>No reviews yet</p>';
        } else { // Add the else condition here
            $reviewStats = getAverageRatingByCategories($reviews);
            $categoriesReviews = Arr::get($reviewStats, 'categoriesReview', []);
            $summary_by_rating = Arr::get($reviewStats, 'summary_by_rating', []);
            $total_average_rating = Arr::get($reviewStats, 'total_average_rating', 0);
            $review_label = getReviewLabel($total_average_rating); 
        ?>
        <div class="adrm_review_summary">
            <div class="adrm_review_summary_rating">
                <h6>Average rating</h6>
                <div class="avg_rating">
                    <h4><?php echo $total_average_rating ?></h4>
                    <h6>/ 5</h4>
                </div>
                <span class="review-label"><?php echo $review_label ?></span>
                <div class="total-review-number">
                    <span class="icon"></span>
                    From <?php echo $total_reviews  ?> Reviews
                </div>
            </div>
            <div class="adrm_review_categories_stats">

                <?php foreach($categoriesReviews as $key => $categoriesReview) {
                    $average_rating = number_format(Arr::get($categoriesReview, 'total_rating', 0) / Arr::get($categoriesReview, 'count_ratings', 1), 2);
                    $review_percentage = ($average_rating / 5) * 100;
                ?>
                <div class="adrm-progress-bar">
                    <div class="progress">
                        <div class="bar" style="width: <?php echo $review_percentage . "%" ?>">
                            <p class="percent"><?php echo $average_rating ?></p>
                        </div>
                    </div>
                    <p style="margin: 3px 0;"><?php echo $key ?></p>
                </div>
                <?php } ?>
  
            </div>
            <div class="adrm_review_categories_summary">
                <span>Rating</span>
                <div class="rating-summary-with-total">
                    <?php
                        foreach($summary_by_rating as $key => $summary) {
                            $average_rating = Arr::get($categoriesReview, 'total_rating', 0) / Arr::get($categoriesReview, 'count_ratings', 1);
                            $review_label = getReviewLabel($key);
                            echo '<div class="rating-summary-item">';
                            echo '<input type="checkbox" disabled />';
                            echo '<span>' . $review_label . '/' . $key . ' ('. $summary .')' .'</span>';
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>
        </div>
        <div class="review-filters">
            <div class="review-filter">
                <label for="review-sort">Sort by:</label>
                <select class="adrm-sort-input" name="review-sort" id="review-sort">
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>
            <div class="review-filter">
                <label for="review-filter">Filter by:</label>
                <div class="filter-radio-inputs">

                    <input class="adrm-filter-by-star" type="radio" name="review-filter" id="all" value="all" checked>
                    <label for="all">All</label>

                    <input class="adrm-filter-by-star"  type="radio" name="review-filter" id="1-star" value="1">
                    <label  for="1-star"><p>*</p>1</label>

                    <input class="adrm-filter-by-star" type="radio" name="review-filter" id="2-star" value="2">
                    <label for="2-star"><p>*</p>2</label>

                    <input class="adrm-filter-by-star" type="radio" name="review-filter" id="3-star" value="3">
                    <label for="3-star"><p>*</p>3</label>

                    <input class="adrm-filter-by-star" type="radio" name="review-filter" id="4-star" value="4">
                    <label for="4-star"><p>*</p>4</label>

                    <input class="adrm-filter-by-star" type="radio" name="review-filter" id="5-star" value="5">
                    <label for="5-star"><p>*</p>5</label>

                </div>
            </div>
        </div>
        <div class="adrm_food_review_template_wrapper">
            <?php foreach ($reviews as $review) {
                $average_rating = Arr::get($review, 'average_rating');
                $created_at = Arr::get($review, 'created_at');
                $review = Arr::get($review, 'meta.formFieldData', []);
                $ratings = Arr::get($review, 'ratings', []);
            ?>
            <div class="adrm_food_review_template">
                <div class="adrm-reviewer-info">
                    <div class="adrm-reviewer-avatar">
                    <?php echo get_avatar(Arr::get($review, 'email'), 96) ?>
                    </div>
                    <div class="adrm-reviewer-name">
                        <span><?php echo Arr::get($review, 'name'); ?></span>
                    </div>
                    <div class="adrm-reviewer-email">
                        <span><?php echo Arr::get($review, 'email'); ?></span>
                    </div>

                </div>
                <div class="adrm-review-body">
                    <div class="adrm-review-rating">
                        <div class="adrm-star-rating">
                            <?php for ($i = 1; $i <= 5; $i++) { ?>
                                <label name="rating" class="<?php echo $i <= $average_rating ? 'active' : ''; ?>" value="<?php echo $i; ?>">★</label>
                            <?php } ?>
                        </div>
                        <span class="adrm-review-date"> Reviewed <?php echo (new Helper)->formatDate($created_at); ?></span>
                    </div>
                    <div class="adrm-review-content">
                        <p><?php echo Arr::get($review, 'message'); ?></p>
                    </div>
                    <div class="review-categories">
                        <?php foreach ($ratings as $rating) { ?>
                            <div class="adrm-star-rating">
                                <?php for ($i = 1; $i <= 5; $i++) { ?>
                                    <label name="rating" class="<?php echo $i <= Arr::get($rating, 'value') ? 'active' : ''; ?>" value="<?php echo $i; ?>">★</label>
                                <?php } ?>
                                <p><?php echo Arr::get($rating, 'label'); ?></p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php if($enablePagination == 'true') {?>
        <div class="adrm-pagination">
            <button class="adrm-prev-page">Prev</button>
            <ul class="adrm-page-numbers">
                <?php for($i = 1; $i <= $total_page; $i++) { ?>
                    <li class="adrm-page-number <?php echo $i == 1 ? 'active' : '' ?>"><?php echo $i ?></li>
                <?php } ?>
            </ul>
            <button class="adrm-next-page">Next</button>
        </div>
        <?php } ?>

        <?php } ?>
    </div>
</div>