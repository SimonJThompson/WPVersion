<?php

    class WPVersion {

        /*
         * check
         *
         * Performs a number of increasingly aggressive checks on a 
         * target domain to try and identify the WordPress version.
         *
         * @domain (string) The domain to perform the lookup for.
         * @return (string)
         */
        public function check($domain) {

            $version = false;

            // Tidy up the domain
            $domain = 'http://'.$domain.'/';

            // Feed first - most sites seem to have this enabled.
            if(!$version) $version = $this->checkFeedReferences($domain);

            // Now try looking at the site source code.
            if(!$version) $version = $this->checkCodeReferences($domain);

            return $version;
        }

        private function checkCodeReferences($domain) {

            // Get homepage source code
            $html = $this->get($domain);
            if(!$html) return false;

            // Check meta tags
            preg_match('/content="WordPress (\*|\d+(\.\d+){0,2}(\.\*)?)"/', $html, $matches);
            if($matches) return $matches[1];

            // Check for references to ?ver - requires more precise version numbers
            preg_match('/wp-emoji-release.min.js\?ver=(\*|\d+(\.\d+){1,2}(\.\*)?)/', $html, $matches);
            if($matches) return $matches[1];

            return false;
        }

        private function checkFeedReferences($domain) {

            // Get feed file
            $html = $this->get($domain.'feed/');
            if(!$html) return false;

            // Check for generator tag
            preg_match('/wordpress.org\/\?v=(\*|\d+(\.\d+){0,2}(\.\*)?)/', $html, $matches);
            if($matches) return $matches[1];            

            return false;
        }

        private function get($url) {

            // Use Chrome UA to help prevent instant blocks from security plugins
            $headers = array(
                'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            );

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            return $result;
        }
    }
