<?php namespace MFF\ppanel;

class ITZR {
  public $manifest;
  private $manifestPath;
  public $lang;
  public $bindings;

  public function loadManifest($file) {
    $this->manifestPath = $file;
    $this->manifest = json_decode(file_get_contents($file), true);
  }

  public function setLang($lang) {
    $this->lang = $lang;
    $langPath = dirname($this->manifestPath) . '/' . $this->manifest['languages'][$this->lang]['itzl'];
    $this->bindings = json_decode(file_get_contents($langPath), true);
    if ($this->bindings === null) {
      trigger_error('Parsing lang file ' . $langPath . ' failed', E_USER_ERROR);
    }
  }

  public function strftime($format, $timestamp) {
    $timestamp = $timestamp ? $timestamp : time();
    $oldLc = setlocale(LC_TIME, '0');
    setlocale(LC_TIME, $this->manifest['languages'][$this->lang]['LC_TIME']);
    $r = strftime($format, $timestamp);
    setlocale(LC_TIME, $oldLc);
    return $r;
  }

  ///
  /// Translate and [f]ormat
  ///
  public function f() {
    $args = func_get_args();
    $id = $args[0];
    $tlation = $this->bindings[$id];
    if ($tlation === null) {
      trigger_error('String id "' . $id . '" is untranslated in lang ' . $lang, E_USER_WARNING);
      return $id . '{' . implode(',', array_slice($args, 1)) . '}';
    }
    $args[0] = $tlation;
    return call_user_func_array('sprintf', $args);
  }

  ///
  /// [t]ranslate
  ///
  public function t($id) {
    $tlation = $this->bindings[$id];
    if ($tlation === null) {
      trigger_error('String id "' . $id . '" is untranslated in lang ' . $lang, E_USER_WARNING);
      return $id;
    }
    return $tlation;
  }

  ///
  /// Translate or [n]ull
  ///
  public function n($id) {
    $tlation = $this->bindings[$id];
    if ($tlation === null) {
      trigger_error('String id "' . $id . '" is untranslated in lang ' . $lang, E_USER_WARNING);
    }
    return $tlation;
  }
}

?>