<?php
use CRM_Mosaicomsgtpl_ExtensionUtil as E;


class CRM_Mosaicomsgtpl_HtmlFilter {

  private $baseUrl = NULL;

  /**
   * Find any image URLs and ensure they're absolute (not relative).
   * Shamelessly copyied from Flexmailer CRM_Mosaico_UrlFilter
   *
   * @param string|array<string> $htmls
   * @return string|array<string>
   *   Filtered HTML, with relative IMG url's changed to absolute URLs.
   */
  public function filterHtml($htmls) {
    // Ex: "https://example.org:8080/subdir/"
    $stdBase = \CRM_Utils_File::addTrailingSlash($this->getBaseUrl(), '/');
    // Ex: "https://example.org:8080"
    $domainBase = $this->createDomainBase($stdBase);

    $callback = function ($matches) use ($stdBase, $domainBase) {
      if (preg_match('/^https?:/', $matches[2]) || empty($matches[2])) {
        return $matches[0];
      }

      if ($matches[2]{0} === '/') {
        return $matches[1] . $domainBase . $matches[2] . $matches[3];
      }
      else {
        return $matches[1] . $stdBase . $matches[2] . $matches[3];
      }
    };

    $htmls = preg_replace_callback(';(\<img [^>]*src *= *")([^">]+)(");i', $callback, $htmls);
    $htmls = preg_replace_callback(';(\<img [^>]*src *= *\')([^">]+)(\');i', $callback, $htmls);
    $htmls = preg_replace_callback(';(\<table [^>]*background *= *")([^">]+)(");i', $callback, $htmls);
    $htmls = preg_replace_callback(';(\<table [^>]*background *= *")([^\'>]+)(\');i', $callback, $htmls);
    // WISHLIST: CSS backgrounds?
    return $htmls;
  }

  /**
   * @return string|NULL
   */
  private function getBaseUrl() {
    if ($this->baseUrl === NULL) {
      return CIVICRM_UF_BASEURL;
    }
    return $this->baseUrl;
  }

  /**
   * @param string $url
   *   Ex: 'https://user:pass@host:port/path?query'.
   * @return string
   *   Ex: 'https://host:port'.
   */
  protected function createDomainBase($url) {
    $result = parse_url($url, PHP_URL_SCHEME);
    $result .= '://';
    $result .= parse_url($url, PHP_URL_HOST);
    $port = parse_url($url, PHP_URL_PORT);
    if ($port) {
      $result .= ":" . $port;
    }
    return $result;
  }
}