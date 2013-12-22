<?php

/** 
 * GentleSource Guestbook Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Dummy Module
 * 
 * The class name consist of "gentlesource_module_" plus a self chosen name. The
 * class must always extend the class gentlesource_module_common.
 */
class gentlesource_module_phpinfo extends gentlesource_module_common
{


    /**
     * Text of language file
     */
    var $text = array();

// -----------------------------------------------------------------------------




    /**
     * Module Constructor
     * 
     * @param array setttings Application main setting array
     */
    function gentlesource_module_phpinfo()
    {
        // Load the language file located in the 
        // folder /module/gentlesource_module_*/language/
        $this->text = $this->load_language();

        
        
        // Name and description of the module displayed in the link list
        // and navigation of the admin area
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        
        
        // List of all triggers where the module is to be called
        $this->add_property('trigger',  
                                array(  
                                        'module_demo',
                                        'module_send_file',
                                        )
                                );
        
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_phpinfo_active',
                                        )
                                );
        
        // Set default values
        $this->add_property('module_phpinfo_active',  'N');
        
        
        // Get settings from database
        $this->get_settings();
        
        // Set module status 
        $this->status('module_phpinfo_active', 'N');
    }

// -----------------------------------------------------------------------------




    /**
     * Administration form that is displayed in admin area in the
     * Configuration section
     * 
     * Possible array elements:
     * 
     * type				bool|string|email|numeric|select|radio|textarea|color
     * label 
     * description		
     * required			true|false
     * attribute		Associative array of attributes added to the form field
     * option			Associative array values for radio|select
     * 
     */
    function administration()
    {
        $settings = array();
        
        $settings['module_phpinfo_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true
            );

        return $settings;
    }

// -----------------------------------------------------------------------------




    /**
     * Processing the content - This function will be called when triggered 
     * somewhere within the script.
     * 
     * @param string    $trigger 	Trigger that triggered the module call
     * @param array		$settings	Application main setting array
     * @param arrray	$data		Data to be used/modified
     * @param array		$additional Additinal data to be used/modified
     * 
     */
    function process($trigger, &$settings, &$data, &$additional)
    {
        // Module URL
        $module_url = $_SERVER['PHP_SELF'];
        if (isset($_SERVER['QUERY_STRING']) and $_SERVER['QUERY_STRING'] != '') {
            $module_url .= '?' . $_SERVER['QUERY_STRING'] . '&amp;';
        } else {
            $module_url .= '?m=' . get_class($this) . '&amp;';
        }
        
        if ($trigger == 'module_send_file' 
                and isset($data['module'])
                and trim($data['module']) == get_class($this)
                and isset($settings['_get']['show']) 
                and $settings['_get']['show'] == 'file') {
            
            phpinfo();
            exit;
        }
        if ($trigger == 'module_demo'
                and isset($data['module'])
                and trim($data['module']) == get_class($this)) {
            
            $content = '<p><iframe src="' . $module_url . 'show=file" style="width:100%;height:400px;border:0;padding:0;margin:0;" name="phpinfo"></iframe></p>';
            $this->set_output($trigger, $content);
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
