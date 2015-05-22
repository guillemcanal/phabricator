<?php

final class PhabricatorGiphyRemarkupRule extends PhutilRemarkupRule {

    const PUBLIC_API_KEY = "dc6zaTOxFJmzC";

    const GIPHY_API_PATH = "http://api.giphy.com/v1";

    const GIPHY_EMBED_PATH = "//giphy.com/embed";

    public function getPriority() {
        return 199.0;
    }

    public function apply($text) {
        return preg_replace_callback(
            '@{gifphy\:(.*?)}@m',
            array($this, 'getGif'),
            $text);
    }

    public function getGif(array $matches) {

        $text_mode = $this->getEngine()->isTextMode();
        $mail_mode = $this->getEngine()->isHTMLMailMode();

        if ($text_mode || $mail_mode) {
            return $matches[0];
        }

        $params = http_build_query(array(
            'api_key' => self::PUBLIC_API_KEY,
            'tags' => $matches[1]
        ));

        $response = json_decode(file_get_contents(self::GIPHY_API_PATH . '/gifs/random?' . $params));

        $iframe = $this->newTag(
            'div',
            array(
                'class' => 'embedded-giphy-gif',
            ),
            $this->newTag(
                'iframe',
                array(
                    'width'       => $response->image_width,
                    'height'      => $response->image_height,
                    'style'       => 'margin: 1em auto; border: 0px;',
                    'src'         => self::GIPHY_EMBED_PATH . '/' . $response->id . '?html5=true',
                    'frameborder' => 0,
                ),
                ''));

        return $this->getEngine()->storeText($iframe);
    }
}