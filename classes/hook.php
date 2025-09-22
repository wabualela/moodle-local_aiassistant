<?php
namespace local_aiassistant;

class hook {
    public static function before_footer() {
        global $PAGE;

        if (!get_config('local_aiassistant', 'enable')) {
            return;
        }

        if (!has_capability('local/aiassistant:view', \context_system::instance())) {
            return;
        }

        $PAGE->requires->js_call_amd('local_aiassistant/assistant', 'init');
        $PAGE->requires->css('/local/aiassistant/styles.css');
    }
}