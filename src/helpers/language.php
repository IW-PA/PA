<?php
// Language helper functions for Budgie

/**
 * Get current language from session or default to English
 */
function getCurrentLanguage() {
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = 'en'; // Default to English
    }
    return $_SESSION['language'];
}

/**
 * Set the current language
 */
function setLanguage($lang) {
    if (in_array($lang, ['fr', 'en'])) {
        $_SESSION['language'] = $lang;
        return true;
    }
    return false;
}

/**
 * Load translations for current language
 */
function loadTranslations($lang = null) {
    if ($lang === null) {
        $lang = getCurrentLanguage();
    }

    $translationFile = SRC_PATH . "/lang/{$lang}.php";

    if (file_exists($translationFile)) {
        return require $translationFile;
    }

    // Fallback to English if language file not found
    return require SRC_PATH . '/lang/en.php';
}

/**
 * Get a translation by key (dot notation supported)
 * Example: t('dashboard.welcome') or t('nav.dashboard')
 */
function t($key, $replace = []) {
    static $translations = null;
    
    if ($translations === null) {
        $translations = loadTranslations();
    }
    
    // Split key by dot notation
    $keys = explode('.', $key);
    $value = $translations;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $key; // Return key if translation not found
        }
        $value = $value[$k];
    }
    
    // Replace placeholders
    if (!empty($replace) && is_string($value)) {
        foreach ($replace as $search => $replacement) {
            $value = str_replace(":{$search}", $replacement, $value);
        }
    }
    
    return $value;
}

/**
 * Get translation or return default if not found
 */
function trans($key, $default = null, $replace = []) {
    $translation = t($key, $replace);
    
    if ($translation === $key && $default !== null) {
        return $default;
    }
    
    return $translation;
}

/**
 * Echo translation (shorthand for echo t())
 */
function e($key, $replace = []) {
    echo t($key, $replace);
}

/**
 * Get available languages
 */
function getAvailableLanguages() {
    return [
        'en' => 'English',
        'fr' => 'Français',
    ];
}

/**
 * Get language name
 */
function getLanguageName($lang = null) {
    if ($lang === null) {
        $lang = getCurrentLanguage();
    }
    
    $languages = getAvailableLanguages();
    return $languages[$lang] ?? 'Unknown';
}
