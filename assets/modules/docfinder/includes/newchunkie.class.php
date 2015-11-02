<?php

/**
 * newChunkie
 *
 * Copyright 2013 by Thomas Jakobi <thomas.jakobi@partout.info>
 *
 * newChunkie is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * newChunkie is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * newChunkie; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package chunkie
 * @subpackage classfile
 * @version 1.0.3
 *
 * newChunkie Class.
 *
 * This class bases loosely on the Chunkie class idea for MODX Evolution by
 * Armand "bS" Pondman <apondman@zerobarrier.nl>
 */
class newChunkie
{

    /**
     * A reference to the modX instance
     * @var modX $modx
     */
    public $modx;

    /**
     * A reference to the PHx instance
     * @var PHxParser $phx
     */
    public $phx;

    /**
     * The name of a MODX chunk for the row template (could be prefixed by
     * @FILE, @INLINE or @CHUNK). Chunknames starting with '@FILE ' are loading
     * a chunk from the filesystem (prefixed by $basepath). Chunknames starting
     * with '@INLINE ' contain the template code itself.
     *
     * @var string $tpl
     * @access private
     */
    private $tpl;

    /**
     * The name of a MODX chunk for the wrapper template (could be prefixed by
     * @FILE, @INLINE or @CHUNK). Chunknames starting with '@FILE ' are loading
     * a chunk from the filesystem (prefixed by $basepath). Chunknames starting
     * with '@INLINE ' contain the template code itself.
     *
     * @var string $tpl
     * @access private
     */
    private $tplWrapper;

    /**
     * The name of current rendering queue.
     *
     * @var string $queue
     * @access private
     */
    private $queue;

    /**
     * The prepared templates for all rendering queues.
     *
     * @var array $templates
     * @access private
     */
    private $templates;

    /**
     * Global options.
     *
     * @var array $options
     * @access private
     */
    private $options;

    /**
     * A collection of all placeholders.
     * @var array $placeholders
     * @access private
     */
    private $placeholders;

    /**
     * The current depth of the placeholder keypath.
     * @var array $depth
     * @access private
     */
    private $depth;

    /**
     * Profile informations for all rendering queues.
     *
     * @var array $profile
     * @access private
     */
    private $profile;

    /**
     * newChunkie constructor
     *
     * @param DocumentParser $modx The DocumentParser instance.
     * @param array $config An array of configuration options. Optional.
     */
    function __construct(&$modx, $config = array())
    {
        $this->modx = & $modx;

        if (!class_exists("PHxParser")) {
            include_once(strtr(realpath(dirname(__FILE__)) . "/phx.parser.class.inc.php", '\\', '/'));
        }
        $this->phx = new PHxParser();
        $this->options['phxcheck'] = (version_compare($this->phx->version, '2.0.0', '>=')) ? 1 : 0;

        $this->depth = 0;
        // Basepath @FILE is prefixed with
        $this->options['basepath'] = MODX_BASE_PATH . (isset($config['basepath']) ? $config['basepath'] : '');
        $this->options['maxdepth'] = (integer)(isset($config['maxdepth']) ? $config['maxdepth'] : 4);

        $this->options['parseLazy'] = (boolean)(isset($config['parseLazy']) ? $config['parseLazy'] : false);
        $this->options['profile'] = (boolean)(isset($config['profile']) ? $config['profile'] : false);
        $this->tpl = $this->getTemplateChunk($config['tpl']);
        $this->tplWrapper = $this->getTemplateChunk($config['tplWrapper']);
        $this->queue = (isset($config['queue']) ? $config['queue'] : 'default');
        $this->placeholders = array();
        $this->templates = array();
        $this->profile = array();
    }

    /**
     * Set an option.
     *
     * @access public
     * @param string $key The option key.
     * @param string $value The  option value.
     *
     * following option keys are valid:
     * - basepath: The basepath @FILE is prefixed with.
     * - maxdepth: The maximum depth of the placeholder keypath.
     * - parseLazy: Uncached MODX tags are not parsed inside of newChunkie.
     * - profile: profile preparing/rendering times.
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * Set current rendering queue.
     *
     * @access public
     * @param string $queue The name of the queue.
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * Get current rendering queue.
     *
     * @access public
     * @return string Current rendering queue.
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Change the template for rendering.
     *
     * @access public
     * @param string $tpl The new template string for rendering.
     * @param boolean $wrapper Set wrapper template if true.
     */
    public function setTpl($tpl, $wrapper = false)
    {
        // Mask uncached elements if parseLazy is set
        if ($this->options['parseLazy']) {
            $tpl = str_replace('[[!', '[[¡', $tpl);
        }
        if (!$wrapper) {
            $this->tpl = $tpl;
        } else {
            $this->tplWrapper = $tpl;
        }
    }

    /**
     * Change the wrapper template for rendering.
     *
     * @access public
     * @param string $tpl The new wrapper template string for rendering.
     */
    public function setTplWrapper($tpl)
    {
        $this->setTpl($tpl, true);
    }

    /**
     * Fill placeholder array with values. If $value contains a nested
     * array the key of the subarray is prefixed to the placeholder key
     * separated by dot sign.
     *
     * @access public
     * @param string $value The value(s) the placeholder array is filled
     * with. If $value contains an array, all elements of the array are
     * filled into the placeholder array using key/value. If one array
     * element contains a subarray the function will be called recursive
     * prefixing $keypath with the key of the subarray itself.
     * @param string $key The key $value will get in the placeholder array
     * if it is not an array, otherwise $key will be used as $keypath.
     * @param string $keypath The string separated by dot sign $key will
     * be prefixed with.
     * @param string $queue The queue name
     */
    public function setPlaceholders($value = '', $key = '', $keypath = '', $queue = '')
    {
        if ($this->depth > $this->options['maxdepth']) {
            return;
        }
        $queue = ($queue != '') ? $queue : $this->queue;
        $keypath = ($keypath !== '') ? strval($keypath) . '.' . $key : $key;
        if (is_array($value)) {
            $this->depth++;
            foreach ($value as $subkey => $subval) {
                $this->setPlaceholders($subval, $subkey, $keypath, $queue);
            }
            $this->depth--;
        } else {
            $this->placeholders[$queue][$keypath] = $value;
        }
    }

    /**
     * Add one value to the placeholder array with its key.
     *
     * @access public
     * @param string $key The key for the placeholder added.
     * @param string $value The value for the placeholder added.
     * @param string $queue The queue name.
     */
    public function setPlaceholder($key, $value, $queue = '')
    {
        $queue = !empty($queue) ? $queue : $this->queue;
        if (is_array($value)) {
            $this->depth++;
            foreach ($value as $subkey => $subval) {
                $this->setPlaceholders($subval, $subkey, $key, $queue);
            }
            $this->depth--;
        } else {
            $this->placeholders[$queue][$key] = $value;
        }
    }

    /**
     * Get the placeholder array.
     *
     * @access public
     * @param string $queue The queue name.
     * @return array The placeholders.
     */
    public function getPlaceholders($queue = '')
    {
        $queue = !empty($queue) ? $queue : $this->queue;
        return $this->placeholders[$queue];
    }

    /**
     * Get a placeholder value by key.
     *
     * @access public
     * @param string $key The key for the returned placeholder.
     * @param string $queue The queue name.
     * @return string The placeholder.
     */
    public function getPlaceholder($key, $queue = '')
    {
        $queue = !empty($queue) ? $queue : $this->queue;
        return $this->placeholders[$queue][$key];
    }

    /**
     * Clear the placeholder array.
     *
     * @access public
     * @param string $queue The queue name.
     */
    public function clearPlaceholders($queue = '')
    {
        $queue = !empty($queue) ? $queue : $this->queue;
        unset($this->placeholders[$queue]);
    }

    /**
     * Get the templates array.
     *
     * @access public
     * @param string $queue The queue name.
     * @return array The placeholders.
     */
    public function getTemplates($queue = '')
    {
        $queue = !empty($queue) ? $queue : $this->queue;
        return $this->templates[$queue];
    }

    /**
     * Clear the templates array.
     *
     * @access public
     * @param string $queue The queue name.
     */
    public function clearTemplates($queue = '')
    {
        $queue = !empty($queue) ? $queue : $this->queue;
        unset($this->templates[$queue]);
    }

    /**
     * Prepare the current template with key based placeholders. Replace
     * placeholders array (only full placeholder tags are replaced - tags with
     * modifiers remaining untouched - these were processed in $this->process)
     * later.
     *
     * @access public
     * @param string $key The key to prepend to the placeholder names.
     * @param array $placeholders The placeholders
     * @param string $queue The queue name.
     */
    public function prepareTemplate($key = '', array $placeholders = array(), $queue = '')
    {
        $queue = !empty($queue) ? $queue : $this->queue;
        if ($this->options['profile']) {
            $this->profile[$queue]['prepare'] = isset($this->profile[$queue]['prepare']) ? $this->profile[$queue]['prepare'] : 0;
            $start = microtime(true);
        }
        $keypath = explode('.', $key);

        // Fill keypath based templates array
        if (!isset($this->templates[$queue])) {
            $this->templates[$queue] = new stdClass();
            $this->templates[$queue]->templates = array();
            $this->templates[$queue]->wrapper = (!empty($this->tplWrapper)) ? $this->tplWrapper : '[+wrapper+]';
        }
        $current = & $this->templates[$queue];

        // Prepare default templates
        $currentkeypath = '';
        foreach ($keypath as $currentkey) {
            $currentkeypath .= $currentkey . '.';
            if (!isset($current->templates[$currentkey])) {
                $current->templates[$currentkey] = new stdClass();
                $current->templates[$currentkey]->templates = array();
                $current->templates[$currentkey]->wrapper = (!empty($this->tplWrapper)) ? $this->tplWrapper : '[+wrapper+]';
                $current->templates[$currentkey]->template = '[+' . trim($currentkeypath, '.') . '+]';
            }
            $current = & $current->templates[$currentkey];
        }
        if (!empty($this->tpl)) {
            // Set curent template
            $current->template = $this->tpl;
            // Replace placeholders array (only full placeholder tags are replaced)
            if (empty($placeholders)) {
                $placeholders = $this->getPlaceholders($queue);
                foreach ($placeholders as $k => $v) {
                    $k = str_replace($key . '.', '', $k);
                    $current->template = str_replace('[+' . $k . '+]', $v, $current->template);
                }
            } else {
                foreach ($placeholders as $k => $v) {
                    $current->template = str_replace('[+' . $k . '+]', $v, $current->template);
                }
            }
            // Replace remaining placeholders with key based placeholders
            $current->template = str_replace('[+', '[+' . ltrim($key . '.', '.'), $current->template);
        } else {
            $current->template = '';
        }
        if (!$current->wrapper) {
            $current->wrapper = (!empty($this->tplWrapper)) ? $this->tplWrapper : '[+wrapper+]';
        }
        unset($current);
        if ($this->options['profile']) {
            $end = microtime(true);
            $this->profile[$queue]['prepare'] += $end - $start;
        }
    }

    /**
     * Recursive sort the templates object by key.
     *
     * @access public
     * @param stdClass $object The templates object to sort.
     */
    private function templatesSortRecursive(stdClass &$object)
    {
        foreach ($object->templates as &$value) {
            if (is_object($value)) {
                $this->templatesSortRecursive($value);
            }
        }
        ksort($object->templates);
    }

    /**
     * Join the templates object by recursive wrapping templates.
     *
     * @access public
     * @param stdClass $object The object to flatten.
     * @param string $prefix Top-level prefix.
     * @param string $outputSeparator Separator between two joined elements.
     * @return array Joined elements in array with top-level prefix key
     */
    private function templatesJoinRecursive(stdClass $object, $prefix = '', $outputSeparator = "\r\n")
    {
        if (!empty($object->templates)) {
            $flat = array();
            foreach ($object->templates as $key => $value) {
                $flat = array_merge($flat, $this->templatesJoinRecursive($value, $prefix . $key . '.', $outputSeparator));
            }
            if ($prefix) {
                $return = array(trim($prefix, '.') => str_replace('[+wrapper+]', str_replace('[+' . trim($prefix, '.') . '+]', implode($outputSeparator, $flat), $object->template), $object->wrapper));
                foreach ($flat as $key => $value) {
                    $return = str_replace('[+' . $key . '+]', $value, $return);
                }
            }
        } else {
            $return = array(trim($prefix, '.') => str_replace('[+wrapper+]', $object->template, $object->wrapper));
        }
        return $return;
    }

    /**
     * Get profiling value.
     *
     * @access public
     * @param string $type The profiling type.
     * @param string $queue The queue name.
     * @return array The profiling value.
     *
     * following profiling types are valid:
     * - prepare: Time for preparing the templates object.
     * - render: Time for rendering the templates object.
     */
    public function getProfile($type, $queue = '')
    {
        $queue = !empty($queue) ? $queue : $this->queue;
        $output = $this->profile[$queue][$type];
        $this->profile[$queue][$type] = 0;
        return $output;
    }

    /**
     * Set PHx variables recursive with keypath.
     *
     * @access private
     * @param mixed $value The current value to set in the PHx variable.
     * @param string $key The key for the current value.
     * @param string $path The keypath for the current value.
     */
    private function setPHxVariables($value = '', $key = '', $path = '')
    {
        $keypath = !empty($path) ? $path . "." . $key : $key;
        if (is_array($value)) {
            foreach ($value as $subkey => $subval) {
                $this->setPHxVariables($subval, $subkey, $keypath);
            }
        } else {
            $this->phx->setPHxVariable($keypath, $value);
        }
    }

    /**
     * Process the current queue with the queue placeholders.
     *
     * @access public
     * @param string $queue The queue name.
     * @param string $outputSeparator Separator between two joined elements.
     * @param boolean $clear Clear queue after process.
     * @return string Processed template.
     */
    public function process($queue = '', $outputSeparator = "\r\n", $clear = true)
    {
        $queue = !empty($queue) ? $queue : $this->queue;

        if ($this->options['phxcheck']) {
            if ($this->options['profile']) {
                $this->profile[$queue]['render'] = isset($this->profile[$queue]['render']) ? $this->profile[$queue]['render'] : 0;
                $start = microtime(true);
            }
            if (!empty($this->templates[$queue])) {
                // Recursive join templates object
                $templates = array();
                foreach ($this->templates[$queue]->templates as $key => $value) {
                    $templates = array_merge($templates, $this->templatesJoinRecursive($value, $key . '.', $outputSeparator));
                }
                $template = implode($outputSeparator, $templates);

                // Process the whole template
                $this->phx->placeholders = array();
                $this->setPHxVariables($this->placeholders[$queue]);
                $output = $this->phx->Parse($template);

                // Unmask uncached elements (will be parsed outside of this)
                if ($this->options['parseLazy']) {
                    $output = str_replace(array('[[¡'), array('[[!'), $output);
                }
            } else {
                $output = '';
            }
            if ($clear) {
                $this->clearPlaceholders($queue);
                $this->clearTemplates($queue);
            }
            if ($this->options['profile']) {
                $end = microtime(true);
                $this->profile[$queue]['render'] += $end - $start;
            }
        } else {
            $output = '<div style="border: 1px solid red;font-weight: bold;margin: 10px;padding: 5px;">';
            $output .= 'Error! This MODx installation is running an older version of the PHx plugin.<br /><br />';
            $output .= 'Please update PHx to version 2.0.0 or higher.<br />OR - Disable the PHx plugin in the MODx Manager. (Elements -> Manage Elements -> Plugins)';
            $output .= '</div>';
        }
        return $output;
    }

    /**
     * Get a template chunk. All chunks retrieved by this function are
     * cached in $modx->chunkieCache for later reusage.
     *
     * @access public
     * @param string $tpl The name of a MODX chunk (could be prefixed by
     * @FILE, @CODE or @CHUNK). Chunknames starting with '@FILE' are
     * loading a chunk from the filesystem (prefixed by $basepath).
     * Chunknames starting with '@CODE' contain the template code itself.
     * @return string The template chunk.
     */
    public function getTemplateChunk($tpl)
    {
        switch (true) {
            case (substr($tpl, 0, 5) == '@FILE'):
                $filename = trim(substr($tpl, 5), ' :');
                if (!isset($this->modx->chunkieCache['@FILE'])) {
                    $this->modx->chunkieCache['@FILE'] = array();
                }
                if (!empty($filename) && !array_key_exists($filename, $this->modx->chunkieCache['@FILE'])) {
                    if (file_exists($this->options['basepath'] . $filename)) {
                        $template = file_get_contents($this->options['basepath'] . $filename);
                    } else {
                        $template = '';
                    }
                    $this->modx->chunkieCache['@FILE'][$filename] = $template;
                } else {
                    $template = !empty($filename) ? $this->modx->chunkieCache['@FILE'][$filename] : '';
                }
                break;
            case (substr($tpl, 0, 5) == '@CODE'):
                $template = trim(substr($tpl, 5), ' :');
                break;
            default:
                if (substr($tpl, 0, 6) == '@CHUNK') {
                    $chunkname = trim(substr($tpl, 6), ' :');
                } else {
                    $chunkname = $tpl;
                }
                if (!isset($this->modx->chunkieCache['@CHUNK'])) {
                    $this->modx->chunkieCache['@CHUNK'] = array();
                }
                if (!empty($chunkname) && !array_key_exists($chunkname, $this->modx->chunkieCache['@CHUNK'])) {
                    $chunk = $this->modx->getChunk($chunkname);
                    $template = ($chunk) ? $chunk : $chunkname;
                    $this->modx->chunkieCache['@CHUNK'][$chunkname] = $template;
                } else {
                    $template = (!empty($chunkname)) ? $this->modx->chunkieCache['@CHUNK'][$chunkname] : '';
                }
                break;
        }
        return $template;
    }

}
