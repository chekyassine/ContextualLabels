<?php

// 🛠 Full error reporting and log setup
ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', dirname(__FILE__) . '/debug.log');
error_log("[ContextualLabelsPlugin] Plugin loaded");

class ContextualLabelsPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = ['initialize'];
	protected $_filters = array('display_elements');

    private static $overrides = [];
    private static $overrideLoaded = false;



private static function getLang()
{
    $config = Zend_Registry::get('bootstrap')->getResource('Config');
    $locale = isset($config->locale->name) ? $config->locale->name : 'en';
    $lang = strtoupper(substr($locale, 0, 2));
    error_log("[CL] 🌍 Detected language: $lang");
    return $lang;
}


public function filterDisplayElements($elementsBySet)
{
    error_log("[CL] ✅ filterDisplayElements triggered");

    $key = is_admin_theme() ? 'admin' : 'public';
    $type = get_current_record('item')->getItemType()->name;
	$lang = self::getLang();
    $file = dirname(__FILE__) . "/config/{$type}_{$lang}.txt";

    if (!file_exists($file)) return $elementsBySet;

    $map = [];
    $f = fopen($file, 'r');
    while (($row = fgetcsv($f)) !== false) {
        if (count($row) < 3) continue;
        list($term, $label, $desc) = $row;
        $map[trim($term)] = [trim($label), trim($desc)];
    }
    fclose($f);

    // Replace labels
    if (isset($elementsBySet['Dublin Core'])) {
        foreach ($elementsBySet['Dublin Core'] as $field => $values) {
            if (isset($map[$field])) {
                list($newLabel, $desc) = $map[$field];
                unset($elementsBySet['Dublin Core'][$field]);
                $elementsBySet['Dublin Core'][$newLabel] = $values;
                error_log("[CL] ✅ Renamed '$field' to '$newLabel'");
            }
        }
    }

    return $elementsBySet;
}

	
    public function hookInitialize($args)
    {
        error_log("[CL] ✅ hookInitialize triggered");

        $fields = ['Abstract','Access Rights'
		 ,'Accrual Method','Accrual Periodicity','Accrual Policy','Alternative Title','Audience','Audience Education Level','Bibliographic Citation','Conforms To','Contributor','Coverage','Creator','Date','Date Accepted','Date Available','Date Copyrighted','Date Created','Date Issued','Date Modified','Date Submitted','Date Valid','Description','Extent','Format','Has Format','Has Part','Has Version','Identifier','Instructional Method','Is Format Of','Is Part Of','Is Referenced By','Is Replaced By','Is Required By','Is Version Of','Language','License','Mediator','Medium','Provenance','Publisher','References','Relation','Replaces','Requires','Rights','Rights Holder','Source','Spatial Coverage','Subject','Table Of Contents','Temporal Coverage','Title','Type'
		];

        foreach ($fields as $field) {
            add_filter(
                ['ElementForm', 'Item', 'Dublin Core', $field],
                ['ContextualLabelsPlugin', 'relabelField']
            );

            error_log("[CL] ➕ Filter registered for: $field");
        }
		
		
		error_log("[CL] ➕ Generic ElementDisplay filter registered");

    }






    public static function relabelField($components, $args)
    {
        $fieldName = $args['element']->name;
        error_log("[CL] 🔄 relabelField called for: $fieldName");

        // Load override if needed
        if (!self::$overrideLoaded) {
            self::loadOverrideFromItem();
        }

        if (!isset(self::$overrides[$fieldName])) {
            error_log("[CL] ℹ️ No override for: $fieldName");
            return $components;
        }

        list($label, $desc) = self::$overrides[$fieldName];
        error_log("[CL] ✅ Applying override for $fieldName: $label / $desc");

        $components['label'] = "<label>$label</label>";
        $components['description'] = "<p class='description'>$desc</p>";
        return $components;
    }

    private static function loadOverrideFromItem()
    {
        error_log("[CL] 🧠 Attempting to load override (lazy)");

        $item = get_current_record('item', false);
        if (!$item) {
            error_log("[CL] ❌ get_current_record('item') returned null");
            return;
        }

        $type = $item->getItemType();
        if (!$type) {
            error_log("[CL] ❌ Item has no type");
            return;
        }

        $typeName = $type->name;
		$lang = self::getLang();
        $file = dirname(__FILE__) . "/config/{$typeName}_{$lang}.txt";
        error_log("[CL] 🔍 Looking for override file: $file");

        if (!file_exists($file)) {
            error_log("[CL] ❌ File not found: $file");
            return;
        }

        $map = [];
        $f = fopen($file, 'r');
        while (($row = fgetcsv($f)) !== false) {
            if (count($row) < 3) continue;
            list($term, $label, $desc) = $row;
            $term = trim($term);  // Expecting UI field names like "Date Created"
            $map[$term] = [trim($label), trim($desc)];
            error_log("[CL] ✅ Loaded override: $term → $label / $desc");
        }
        fclose($f);

        self::$overrides = $map;
        self::$overrideLoaded = true;
        error_log("[CL] 🧠 Override map fully loaded");
    }
}
