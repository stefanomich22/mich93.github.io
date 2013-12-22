<?php

/**
 * GentleSource Module
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Filter content
 */
class gentlesource_module_force_line_break extends gentlesource_module_common
{


    /**
     * Text of language file
     */
    var $text = array();

    var $replacement = '';
    var $maximum = 10000;

// -----------------------------------------------------------------------------




    /**
     *  Setup
     *
     * @access public
     */
    function gentlesource_module_force_line_break()
    {
        $this->text = $this->load_language();

        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',
                                array(
                                        'frontend_content',
                                        'backend_content',
                                        )
                                );

        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',
                                array(
                                        'module_force_line_break_active',
                                        'module_force_line_break_type',
                                        'module_force_line_break_max',
                                        'module_force_line_break_urls'
                                        )
                                );

        // Default values
        $this->add_property('module_force_line_break_active',   'N'); // Y/N
        $this->add_property('module_force_line_break_urls',     'N'); // Y/N
        $this->add_property('module_force_line_break_type', 'whitespace');

        // Get settings from database
        $this->get_settings();

        // Set module status
        $this->status('module_force_line_break_active', 'N');
    }

// -----------------------------------------------------------------------------




    /**
     *  Administration
     *
     * @access public
     */
    function administration()
    {
        $settings = array();

        $settings['module_force_line_break_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true
            );

        $settings['module_force_line_break_max'] = array(
            'type'          => 'numeric',
            'label'         => $this->text['txt_maximum_characters'],
            'description'   => $this->text['txt_maximum_characters_description'],
            'required'      => true
            );

        $settings['module_force_line_break_type'] = array(
            'type'          => 'radio',
            'label'         => $this->text['txt_type'],
            'description'   => $this->text['txt_type_description'],
            'required'      => true,
            'option'        => array(
                                'whitespace'    => $this->text['txt_white_space'],
                                'linebreak'     => $this->text['txt_line_break']
                                ),
            );
        $settings['module_force_line_break_urls'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_break_urls'],
            'description'   => $this->text['txt_break_urls_description'],
            'required'      => true
            );
        return $settings;
    }

// -----------------------------------------------------------------------------




    /**
     * Processing the content
     *
     * @access public
     */
    function process($trigger, &$settings, &$data, &$additional)
    {
        if ($this->get_property('module_force_line_break_active') == 'N') {
            return false;
        }

        if ($trigger == 'frontend_content'
                or $trigger == 'backend_content') {
            $this->modify($data);
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Modify content
     *
     * @access public
     */
    function modify(&$input)
    {
        if (!is_array($input) or sizeof($input) <= 0) {
            return false;
        }

        $this->replacement = '<span style="font-size:0;"> </span>';
        if ($this->get_property('module_force_line_break_type') == 'linebreak') {
            $this->replacement = nl2br("\n");
        }
        if ($max = $this->get_property('module_force_line_break_max') and (int)$max >= 0) {
            $this->maximum = $max;
        }

        foreach ($input AS $field => $content)
        {
            if ($content == '') {
                continue;
            }
            if (is_array($content)) {
                continue;
            }

            $temp_content = explode(' ', $content);
            $temp_content = array_map(array($this, 'inject'), $temp_content);
            $input[$field] = join(' ', $temp_content);
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Inject characters
     */
    function inject($content)
    {
        if ($this->get_property('module_force_line_break_urls') == 'N'
                and preg_match('#(@|tp://)#', $content)) {
            return $content;
        }
        if (strlen($content) > $this->maximum) {
            $content = chunk_split($content, $this->maximum, $this->replacement);
        }
        return $content;
    }


}