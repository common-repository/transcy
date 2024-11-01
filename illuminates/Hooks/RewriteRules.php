<?php

namespace Illuminate\Hooks;

use Illuminate\Interfaces\IHook;
use Illuminate\Services\AppService;

class RewriteRules implements IHook
{
    protected $appService;

    protected $listLanguages;

    public function __construct()
    {
        $this->appService     = AppService::getInstance();

        $this->listLanguages  = $this->appService->getListLang();
    }
    /**
     * Register Hooks
     *
     * @since 4.4.0
     * @var array
     */
    public function registerHooks()
    {
        if ($this->appService->isSwicherTypeFriendly() && !empty($this->listLanguages)) {
            add_action('init', array($this, 'customLanguageRewriteRules'), 99);
            add_filter('query_vars', array($this, 'customQueryVars'), 99, 1);
        }else{
            add_action('init', array($this, 'flushRewriteRules'), 99);
        }
    }

    public function customLanguageRewriteRules()
    {
        global $wp_rewrite;
        $rewriteRules = get_option('rewrite_rules');
        if (!empty($rewriteRules)) {
            foreach ($rewriteRules as $keyRewrite => $valueRewrite) {
                if (strpos($keyRewrite, 'wc-api') !== false || strpos($keyRewrite, 'wc-auth') !== false || strpos($keyRewrite, 'wp-json') !== false || strpos($keyRewrite, 'wp-sitemap') !== false || strpos($keyRewrite, 'attachment') !== false || strpos($keyRewrite, 'type/') !== false) {
                    continue;
                }
                $hasCheck = explode('/', $keyRewrite);
                $hasCheck = reset($hasCheck);
                if (in_array($hasCheck, $this->listLanguages)) {
                    continue;
                }
                foreach ($this->listLanguages as $lang) {
                    $transcyKey     = sprintf("%s/%s", $lang, $keyRewrite);
                    $transcyValue   = sprintf("%s&lang=%s", $valueRewrite, $lang);
                    add_rewrite_rule($transcyKey, $transcyValue, 'top');
                }
            }
        }
        foreach ($this->listLanguages as $lang) {
            $transcyKey     = sprintf("%s/%s", $lang, '?$');
            $transcyValue   = sprintf('index.php?lang=%s', $lang);
            add_rewrite_rule($transcyKey, $transcyValue, 'top');
        }
        $wp_rewrite->flush_rules();
    }

    public function flushRewriteRules(){
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    public function customQueryVars($vars)
    {
        $vars[] = 'lang';
        return $vars;
    }
}
