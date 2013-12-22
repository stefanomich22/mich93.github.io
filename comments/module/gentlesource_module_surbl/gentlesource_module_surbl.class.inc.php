<?php

/** 
 * GentleSource Module
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 * 
 */

define('MODULE_SURBL_LOG_FILENAME',  'spam_log.txt');
define('MODULE_SURBL_LOG_FOLDER',    'logfile/');




/**
 * Dummy Module
 */
class gentlesource_module_surbl extends gentlesource_module_common
{


    /**
     * Text of language file
     */
    var $text = array();

// -----------------------------------------------------------------------------




    /**
     *  Setup
     * 
     * @access public
     */
    function gentlesource_module_surbl()
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'frontend_save_content',
                                        'frontend_recheck_content'
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_surbl_mode',
                                        'module_surbl_log_spam',
                                        )
                                );
        
        // Default values
        $this->add_property('module_surbl_mode',  'off'); // off, reject, moderate
        $this->add_property('module_surbl_log_spam',  'N');
        
        // Get settings from database
        $this->get_settings();

        // Set module status 
        $this->status('module_surbl_mode', 'off');
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
        
        $settings['module_surbl_mode'] = array(
            'type'          => 'radio',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true,
            'option'        => array(
                                'off'       => $this->text['txt_off'],
                                'moderate'  => $this->text['txt_moderate'],
                                'reject'    => $this->text['txt_reject']
                                ),
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
        if ($trigger == 'frontend_save_content'
                or $trigger == 'frontend_recheck_content') {
            require 'Net/DNSBL/SURBL.php';
            $surbl = new Net_DNSBL_SURBL();
            $surbl->setBlacklists(array('multi.surbl.org', 'multi.uribl.com'));
            foreach ($data as $key => $val)
            {   
                // http://www.phpfreaks.com/quickcode/Extract_All_URLs_on_a_Page/15.php
                $urls = '(http|file|ftp)';
                $ltrs = '\w';
                $gunk = '/#~:.?+=&%@!\-';
                $punc = '.:?\-';
                $any = $ltrs . $gunk . $punc;
                preg_match_all("{
                                  \b
                                  $urls   :
                                  [$any] +?
    
    
                                  (?=
                                     [$punc] *
                                     [^$any]
                                    |
                                     $
                                   )
                              }x", $val, $matches); 
                foreach ($matches[0] as $url) 
                {
                    if (strlen($url)<= strlen('https://')) {
                        continue;
                    }
                    if ($surbl->isListed($url)) {
                    
                        // Spam log
                        $folder = $this->get_property('module_path') . MODULE_SURBL_LOG_FOLDER;
                        if (is_writable($folder) 
                                and $this->get_property('module_surbl_log_spam') == 'Y') {
                                                                
                            require_once 'File.php';
                            $fp = new File();
                            
                            if (!is_file($folder . '.htaccess')) {
                                $fp->writeLine($folder . '.htaccess', 'Deny from all');
                            }
                            
                
                            // Log line
                            $url = strip_tags($url);
                            $time = $this->current_timestamp();
                            $line[] = date($settings['text']['txt_date_format'], $time);
                            $line[] = ' ('; 
                            $line[] = date($settings['text']['txt_time_format'], $time);
                            $line[] = ') - '; 
                            $line[] = $url;
                            $line[] = ' - ';
                            $line[] = getenv('REQUEST_URI');
                                        
                            $fp->writeLine($folder . MODULE_SURBL_LOG_FILENAME, join('', $line));
                        }
                        
                        if ($this->get_property('module_surbl_mode') == 'reject') {
                            $additional['frontend_input_status'] = 'rejected';
                            $additional['page_allow_comment'] = 'N';
                            $settings['message']['module_spam_check'] = $this->text['txt_error_spam'];
                            return true;
                        }
                        
                        // Skip check if moderation has already been turned on
                        if ($settings['enable_moderation'] == 'Y'
                                or isset($data['comment_status'])) {
                            return false;
                        }
                        if ($this->get_property('module_surbl_mode') == 'moderate') {
                            $additional['frontend_input_status'] = 'moderated';
                            $data['comment_status'] = 100;
                            $settings['message']['module_spam_check'] = $this->text['txt_notice_moderation'];
                            return true;
                        }
                    }
                }
            }
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
