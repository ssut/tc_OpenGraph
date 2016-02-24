<?php
function print_opengraph_meta_tags($target) {
    global $configVal, $blog, $entry;

    $config = Setting::fetchConfigVal($configVal);
    init_config($config);

    $context = Model_Context::getInstance();
    $blog_id = $context->getProperty('blog.id');
    $blog_url = $context->getProperty('uri.blog');
    $blog_title = $context->getProperty('blog.title');

    $short_content = UTF8::lessenAsEm(removeAllTags(stripHTML($entry['content'])), 150);
    
    $protocol = 'http://';
    if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
    }
    else {
        $protocol = 'http://';
    }
    
    $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    $og = false;
    $twitter = false;
    if($config['enableOpenGraph'] === '1') $og = true;
    if($config['enableTwitter'] === '1' && !empty($config['twitterAccount'])) $twitter = true;

    $header = CRLF;
    if(!empty($config['facebookID'])) {
        $header .= '<meta name="fb:app_id" content="' . htmlspecialchars($config['facebookID']) . '" />' . CRLF;
    }

    if($og) {
        $header .= '<meta name="og:site_name" content="' . htmlspecialchars($blog_title) . '" />' . CRLF;
        $header .= '<meta name="og:type" content="blog" />' . CRLF;
        $header .= '<meta name="og:title" content="' . htmlspecialchars($entry['title']) . '" />' . CRLF;
        $header .= '<meta name="og:url" content="' . htmlspecialchars($url) . '" />' . CRLF;
        $header .= '<meta name="og:description" content="' . htmlspecialchars($short_content) . '" />' . CRLF;
    }

    if($twitter) {
        $header .= '<meta name="twitter:domain" content="' . htmlspecialchars($_SERVER['HTTP_HOST']) . '" />' . CRLF;
        $header .= '<meta name="twitter:card" content="summary" />' . CRLF;
        $header .= '<meta name="twitter:site" content="@' . htmlspecialchars($config['twitterAccount']) . '" />' . CRLF;
        $header .= '<meta name="twitter:title" content="' . htmlspecialchars($entry['title']) . '" />' . CRLF;
        $header .= '<meta name="twitter:url" content="' . htmlspecialchars($url) . '" />' . CRLF;
        $header .= '<meta name="twitter:description" content="' . htmlspecialchars($short_content) . '" />' . CRLF;
    }

    if(!empty($entry['title'])) {

        $tc_image = '/\[##_(1R|1L|1C|2C|3C|iMazing|Gallery)\|([^|]*)\.(gif|jpg|jpeg|png|bmp)\|.*_##\]/i';
        $html_image = '/<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1/im';
        if(preg_match($tc_image, $entry['content'], $matches)) {
            $image_url = $default_url . '/attach/' . $blog_id . '/' . $matches[2] . '.' . $matches[3];

            if($og) {
                $header .= '<meta name="og:image" content="' . $image_url . '" />' . CRLF;
            }

            if($twitter) {
                $header .= '<meta name="twitter:image" content="' . $image_url . '" />' . CRLF;
            }
        } else if(stripos($entry['content'], '<img') !== false) {
            if(preg_match($html_image, $entry['content'], $matches)) {
                if(is_array($matches) && !empty($matches)) {
                    $image_url = $matches[2];

                    if($og) {
                        $header .= '<meta name="og:image" content="' . $image_url . '" />' . CRLF;
                    }

                    if($twitter) {
                        $header .= '<meta name="twitter:image" content="' . $image_url . '" />' . CRLF;
                    }
                }
            }
        }

        unset($tc_image);
        unset($html_image);
    }

    $target = $target . $header;
    return $target;
}

function opengraph_dataset($data) {
    $cfg = Setting::fetchConfigVal($data);
    return true;
}

function init_config(&$config) {
    $null = is_null($config);
    $config = array(
        'enableOpenGraph' => $null ? 1 : $config['enableOpenGraph'],
        'enableTwitter' => $null ? 0 : $config['enableTwitter'],
        'facebookID' => $null ? '' : $config['facebookID'],
        'twitterAccount' => $null ? '' : $config['twitterAccount']
    );
}
?>
