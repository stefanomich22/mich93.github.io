<?php
/**
 * Handle backend calls
 */
class c5t_backend
{
    /**
     * Constructor
     */
    public function __construct()
    {
        if (!isset($_REQUEST['action'])) {
            return false;
        }
        if (method_exists($this, $_REQUEST['action'])) {
            return $this->{$_REQUEST['action']}();
        }
    }

    /**
     * Output microdata
     */
    private function microData()
    {
        if (!isset($_REQUEST['identifier'])) {
            return false;
        }

        global $c5t;

        if ($c5t['activate_rating'] != 'Y') {
            return false;
        }

        if ($c5t['display_rating_microformat'] != 'Y') {
            return false;
        }
        require_once 'comment.class.inc.php';

        if ($page_data = c5t_comment::select_identifier($_REQUEST['identifier'])) {
        } else {
            $page_data = array('rating' => 0, 'rating_number' => 0);
        }

        if ($page_data['identifier_rating_number'] > 0) {
            $page_data['rating'] = round($page_data['identifier_rating_value'] / $page_data['identifier_rating_number'], 1);
        }

        if ($c5t['display_rating_human_readable'] == 'Y') {
            echo '<div id="c5t_rating_text">';
            echo sprintf($c5t['text']['txt_rating_text'], $page_data['rating'], $c5t['rating_top_value'], $page_data['rating_number']);
            echo '</div>';
        }

        echo '  <div itemscope itemtype="http://data-vocabulary.org/Review-aggregate">
    		<span itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">
  				<meta itemprop="average" content="' . $page_data['rating'] . '" />
  				<meta itemprop="best" content="' . $c5t['rating_top_value'] . '" />
    		</span>
    		<meta itemprop="votes" content="' . $page_data['rating_number'] . '" />
  			</div>';
        exit;
    }

}