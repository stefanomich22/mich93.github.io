<?php
/**
 * Handles XML HTTP Requests
 */
class c5t_xhr
{
    /**
     * Are form entries valid
     */
    private $isFormValid = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!isset($_GET['json']) or empty($_GET['json'])) {
            return false;
        }

        $json = json_decode($_GET['json'], true);
        if (isset($json['method']) and method_exists($this, $json['method'])) {
            return $this->{$json['method']}($json['params']);
        }
    }

    /**
     * Load comment list
     */
    private function loadCommentList($params)
    {
        global $c5t;

        if ($c5t['display_comments'] != 'Y') {
            return false;
        }

        if (!isset($params['identifier']) or empty($params['identifier'])) {
            return false;
        }

        if (!isset($params['page']) or empty($params['page'])) {
            $page = 1;
        } else {
            $page = (int) $params['page'];
        }

        $c5t['alternative_template'] = $this->getTemplate($params);

        $c5t_out = new c5t_output('xhr_comment_list.tpl.html');
        $c5t_comment_data = array();

        require 'comment.class.inc.php';
        require 'commentlist.class.inc.php';

        $c5t_list_setup = array(
        	'direction' => $c5t['frontend_order'],
            'limit'     => 0,
            'page' 		=> $page
            );

        if ((int) $c5t['frontend_result_number'] >= 1) {
            $c5t_list_setup['limit'] = (int) $c5t['frontend_result_number'];
            $c5t_out->assign('display_pagination', true);
        }
        $c5t_comment_list = new c5t_comment_list(false, $c5t_list_setup);
        if ($c5t_comment_data_temp = $c5t_comment_list->get_list($params['identifier'])) {
            $c5t_comment_data = $c5t_comment_data_temp;
        }
        $c5t_comment_list_values = $c5t_comment_list->values();

        $c5t_out->assign($c5t_comment_list_values);
        if ($c5t_comment_list_values['result_limit'] > 0){
            $c5t_page = ceil(($c5t_comment_list_values['result_number'] + 1) / $c5t_comment_list_values['result_limit']);
        } else {
            $c5t_page = 1;
        }

        $c5t_out->assign('comment_list', $c5t_comment_data);

        $c5t_rating_images = array();
        if ($c5t['activate_rating'] == 'Y') {
            require_once 'comment.class.inc.php';

            if ($page_data = c5t_comment::select_identifier($params['identifier'])) {
            } else {
                $page_data = array('rating' => 0, 'rating_number' => 0);
            }

            if ($page_data['identifier_rating_number'] > 0) {
                $page_data['rating'] = round($page_data['identifier_rating_value'] / $page_data['identifier_rating_number'], 1);
            }
            $c5t_rating_images = c5t_comment::get_rating_images($page_data);
            $c5t_out->assign('page_data', $page_data);
            $c5t_out->assign('rating_top_value', $c5t['rating_top_value']);
            $c5t_out->assign('rating_images', $c5t_rating_images);
        }

		$c5t_out->assign('template_url', $this->getUrl() . '/template/' . $this->getTemplate($params));
		$this->outputResult('c5t.displayCommentList', $c5t_out->finish_xhr());

    }

    /**
     * Save comment data
     */
    private function saveCommentData($params)
    {
        if (!isset($params['identifier']) or empty($params['identifier'])) {
            exit;
        }

        global $c5t;

        foreach ($params as $key => $val)
        {
            $val = urldecode($val);
            if ($c5t['use_utf8'] == 'Y') {
                $val = utf8_encode($val);
            }
            $params[$key] = $val;
        }

        $c5t['_post'][$c5t['identifier_key']] = $params['identifier'];
        $c5t['_post'] = array_merge($c5t['_post'], $params);
        $c5t['_post']['comment'] = $params['comment'];
        $c5t['_post']['rating'] = (int) $params['rating'];
        $c5t['_post'][$c5t['comment_field_name']] = $params['comment'];
        $_POST = array_merge($_POST, $c5t['_post']);

        $params = array(
            'show_form' => 'yes',
            'identifier' => $params['identifier'],
            'template' => $params['template'],
        );

        $commentForm = $this->getCommentForm($params, $validate = true);

        if ($this->isFormValid == false) {
            $this->outputResult('c5t.displayCommentForm', $commentForm);
            exit;
        }


        require_once 'comment.class.inc.php';
        $c = new c5t_comment;
        if ($c->put()) {
            $params = array(
                'show_form' => 'no',
                'identifier' => $params['identifier'],
            'template' => $params['template'],
            );
            $this->outputResult('c5t.displayCommentForm', $this->getCommentForm($params));
            exit;
        }

        $this->outputResult('c5t.displayCommentForm', $commentForm);
        exit;
    }

    /**
     * Load comment form
     */
    private function loadCommentForm($params)
    {
        if (!isset($params['identifier']) or empty($params['identifier'])) {
            exit;
        }

        global $c5t;

        if ($c5t['display_comment_form'] != 'Y') {
            $this->outputResult('displayCommentForm', 'failed', '');
            return false;
        }

        $params = array(
            'show_form' => 'yes',
            'identifier' => $params['identifier'],
            'template' => $params['template'],
        );

        $this->outputResult('c5t.displayCommentForm', $this->getCommentForm($params));
    }

    /**
     * Get comment form
     */
    private function getCommentForm($params, $validate = false)
    {
        global $c5t;

        $c5t['alternative_template'] = $this->getTemplate($params);

        $c5t_out = new c5t_output('xhr_comment_form.tpl.html');

        $c5t_out->assign('show_form', $params['show_form']);

        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
        require_once 'HTML/QuickForm.php';

        $c5t_form = new HTML_QuickForm('form');

        require 'comment_form.inc.php';

        if ($validate == true) {
            if ($c5t_form->validate()) {
                $this->isFormValid = true;
            } else {
                $c5t['message'][] = $c5t['text']['txt_fill_out_required'];
            }
        }

        $c5t_form->updateElementAttr('save', array('onclick' => 'c5t.submitCommentForm(); return false;'));

        $c5t_form_renderer = new HTML_QuickForm_Renderer_ArraySmarty($c5t_out->get_object, true);
        $c5t_form->accept($c5t_form_renderer);
        $c5t_form_result = $c5t_form_renderer->toArray();

        $c5t_rating_images = array();
        if ($c5t['activate_rating'] == 'Y') {
            require_once 'comment.class.inc.php';

            if ($page_data = c5t_comment::select_identifier($params['identifier'])) {
                $c5t_out->assign('page_data', $page_data);
            } else {
                $page_data = array('rating' => 0, 'rating_number' => 0);
                $c5t_out->assign('page_data', $page_data);
            }

            $page_data['identifier_rating_value'] = (int) $c5t['rating_default_value'];
            $page_data['identifier_rating_number'] = 1;

            $c5t_rating_images = c5t_comment::get_rating_images($page_data);
        }
        $c5t_out->assign('rating_top_value', (int) $c5t['rating_top_value']);
        $c5t_out->assign('rating_default_value', (int) $c5t['rating_default_value']);
        $c5t_out->assign('rating_images', $c5t_rating_images);

        $c5t_out->assign('form', $c5t_form_result);

        $c5t_out->assign('template_url', $this->getUrl() . '/template/' . $this->getTemplate($params));
        return $c5t_out->finish_xhr();
    }

    /**
     * Get template
     */
    private function getTemplate($params)
    {
    	global $c5t;

    	$template = $c5t['default_template'];

    	if (isset($params['template']) and !empty($params['template'])) {
    		$template = $params['template'];
    	}

    	return $template;
    }

    /**
     * Get script URL
     */
    private function getUrl()
    {
    	global $c5t;

    	return $c5t['server_protocol'] . $c5t['server_name'] . dirname(c5t_request_uri());
    }

    /**
     * Output result
     */
    private function outputResult($function, $result = null, $error = null)
    {
        global $c5t;

        $json = addslashes(json_encode(array('result' => $result, 'error' => $error, 'id' => 1)));
        header('Content-type: text/javascript; charset=' . $c5t['text']['txt_charset']);
        echo $function . "('" . $json . "')";
    }
}