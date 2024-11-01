<?php

namespace TranscyAdmin\Hooks;

use Illuminate\Interfaces\IHook;
use Illuminate\Utils\Helper;
use Illuminate\Services\AppService;

class Enqueue implements IHook
{
    protected $distPath = TRANSCY_ASSETS_FOLDER_PATH  . '/dist';
    protected $distURL  = TRANSCY_ASSETS_FOLDER  . '/dist';
    protected $suffix   = '';

    public function registerHooks()
    {
        $this->suffix = Helper::getSuffix();
        if (!empty($this->suffix)) {
            $this->distPath = sprintf('%s-%s', $this->distPath, str_replace('-', '', $this->suffix));
            $this->distURL  = sprintf('%s-%s', $this->distURL, str_replace('-', '', $this->suffix));
        }

        add_action('admin_enqueue_scripts', array($this, 'addScript'), 99, 1);
        add_action('admin_head', array($this, 'addJsScriptTracking'));
        add_action('admin_footer', array($this, 'addNoscriptGoogleTag'), 1);
    }

    public function addScript($hook)
    {
        $screen = get_current_screen();
        $ver    = time();

        //Load frontend dashboard
        if ($hook == 'toplevel_page_transcy-dashboard') {
            $adminJsFE = opendir($this->distPath . '/js');
            while ($file = readdir($adminJsFE)) {
                $tmp = explode(".", $file);
                if (end($tmp) == 'js') {
                    wp_enqueue_script('transcy-' . reset($tmp),  $this->distURL  . '/js/' . $file, '', $ver, true);
                }
            }

            $adminCssFE = opendir($this->distPath . '/css');
            while ($file = readdir($adminCssFE)) {
                $tmp = explode(".", $file);
                if (end($tmp) == 'css') {
                    wp_enqueue_style('transcy-' . reset($tmp),  $this->distURL  . '/css/' . $file, array(), $ver);
                }
            }
        }

        //Load model deactive plugin in admin
        wp_enqueue_script('transcy-deactive-modal-js',  TRANSCY_ASSETS_FOLDER . '/js/transcy-deactive-modal' . $this->suffix . '.js', array('jquery'), $ver, true);
        wp_enqueue_style('transcy-deactive-modal-css',  TRANSCY_ASSETS_FOLDER . '/css/transcy-deactive-modal' . $this->suffix . '.css', array(), $ver);

        //Load admin script
        wp_enqueue_script('transcy-admin-js',  TRANSCY_ASSETS_FOLDER . '/js/transcy-admin.js', array('jquery'), $ver);

        //Remove unnecessary js 
        global $pagenow;
        if ($pagenow == 'admin.php' && $_GET['page'] == 'transcy-dashboard') {
            wp_dequeue_script('wpcfto_metaboxes.js');
            wp_deregister_script('wpcfto_metaboxes.js');
            wp_deregister_script('vue-resource.js');
            wp_deregister_script('vue2-datepicker.js');
            wp_deregister_script('vue-select.js');
            wp_deregister_script('vue2-editor.js');
            wp_deregister_script('vue2-color.js');
            wp_deregister_script('vue-draggable.js');
            wp_deregister_script('vue.js');
        }
    }

    public function addJsScriptTracking()
    {
        $appService     = AppService::getInstance();
        ?>
        <script>
            var transcy_url         = '<?php echo Helper::siteUrl(); ?>';
            var transcy_domain      = '<?php echo Helper::getDomain(); ?>';
            var transcy_apikey      = '<?php echo $appService->getKey(); ?>';
            var transcy_token       = '<?php echo $appService->generateToken(); ?>';
            var transcy_version     = '<?php echo Helper::getTranscyVersion(); ?>';
            var transcy_woo_version = '<?php echo Helper::getWooVersion(); ?>';
            var transcy_wp_version  = '<?php echo Helper::getWPVersion(); ?>';
        </script>

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600&display=swap" rel="stylesheet" />

        <!-- Google Tag Manager -->
        <script>
            (function(w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start': new Date().getTime(),
                    event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', 'GTM-5XCKN6G');
        </script>
        <!-- End Google Tag Manager -->

        <script type="text/javascript">
            (function(c, l, a, r, i, t, y) {
                c[a] = c[a] || function() {
                    (c[a].q = c[a].q || []).push(arguments)
                };
                t = l.createElement(r);
                t.async = 1;
                t.src = "https://www.clarity.ms/tag/" + i;
                y = l.getElementsByTagName(r)[0];
                y.parentNode.insertBefore(t, y);
            })(window, document, "clarity", "script", "h8dkyo1st2");
        </script>

        <!--Google Analytics-->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-76JNHLF915"></script>

        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag("js", new Date());

            gtag("config", 'G-76JNHLF915');
        </script>
        <!--End Google Analytics-->

        <!--Amplitude-->
        <script type="text/javascript">
            ! function() {
                "use strict";
                ! function(e, t) {
                    var n = e.amplitude || {
                        _q: [],
                        _iq: {}
                    };
                    if (n.invoked) e.console && console.error && console.error("Amplitude snippet has been loaded.");
                    else {
                        var r = function(e, t) {
                                e.prototype[t] = function() {
                                    return this._q.push({
                                        name: t,
                                        args: Array.prototype.slice.call(arguments, 0)
                                    }), this
                                }
                            },
                            s = function(e, t, n) {
                                return function(r) {
                                    e._q.push({
                                        name: t,
                                        args: Array.prototype.slice.call(n, 0),
                                        resolve: r
                                    })
                                }
                            },
                            o = function(e, t, n) {
                                e[t] = function() {
                                    if (n) return {
                                        promise: new Promise(s(e, t, Array.prototype.slice.call(arguments)))
                                    }
                                }
                            },
                            i = function(e) {
                                for (var t = 0; t < m.length; t++) o(e, m[t], !1);
                                for (var n = 0; n < g.length; n++) o(e, g[n], !0)
                            };
                        n.invoked = !0;
                        var u = t.createElement("script");
                        u.type = "text/javascript", u.integrity = "sha384-x0ik2D45ZDEEEpYpEuDpmj05fY91P7EOZkgdKmq4dKL/ZAVcufJ+nULFtGn0HIZE", u.crossOrigin = "anonymous", u.async = !0, u.src = "https://cdn.amplitude.com/libs/analytics-browser-2.0.0-min.js.gz", u.onload = function() {
                            e.amplitude.runQueuedFunctions || console.log("[Amplitude] Error: could not load SDK")
                        };
                        var a = t.getElementsByTagName("script")[0];
                        a.parentNode.insertBefore(u, a);
                        for (var c = function() {
                                return this._q = [], this
                            }, p = ["add", "append", "clearAll", "prepend", "set", "setOnce", "unset", "preInsert", "postInsert", "remove", "getUserProperties"], l = 0; l < p.length; l++) r(c, p[l]);
                        n.Identify = c;
                        for (var d = function() {
                                return this._q = [], this
                            }, f = ["getEventProperties", "setProductId", "setQuantity", "setPrice", "setRevenue", "setRevenueType", "setEventProperties"], v = 0; v < f.length; v++) r(d, f[v]);
                        n.Revenue = d;
                        var m = ["getDeviceId", "setDeviceId", "getSessionId", "setSessionId", "getUserId", "setUserId", "setOptOut", "setTransport", "reset", "extendSession"],
                            g = ["init", "add", "remove", "track", "logEvent", "identify", "groupIdentify", "setGroup", "revenue", "flush"];
                        i(n), n.createInstance = function(e) {
                            return n._iq[e] = {
                                _q: []
                            }, i(n._iq[e]), n._iq[e]
                        }, e.amplitude = n
                    }
                }(window, document)
            }();
            amplitude.init('a3f1d1305204f2c2ef81e97dd24d9b81');
        </script>

        <link rel="stylesheet" type="text/css" charset="UTF-8" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css" />
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css" />
        <?php
    }

    public function addNoscriptGoogleTag()
    {
        ?>
        <!-- Google Tag Manager (noscript) -->
        <script>
            jQuery(document).ready(function($) {
                $(document.body).prepend('<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5XCKN6G" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>');
            });
        </script>
        <!-- End Google Tag Manager (noscript) -->

        <!-- Modal Deactivate -->
        <div id="tc-modal__deactive--plugin"></div>
        <?php
    }
}
