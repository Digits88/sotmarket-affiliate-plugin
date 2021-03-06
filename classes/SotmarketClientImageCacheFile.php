<?php
/**
 * @ver    0.4
 * @author ������ �������
 **/

class SotmarketClientImageCacheFile extends SotmarketClientCacheFile {
    private $sDefaultImagePath = '';

    public function __construct($config) {
        if (isset($config['defaultImgPath'])) {
            $this->sDefaultImagePath = $config['defaultImgPath'];
        } else {
            $this->sDefaultImagePath = '../default.jpg';
        }
        parent::__construct($config);

        if (isset($config['imgPath'])){

            $this->sTmpPath = $config['imgPath'];
            $this->sLocalCacheDir = $config['imgPath'];
        }
    }

    /**
     * @var string  $sHash          ��� ����
     * @var mixed   $sResult        ���������� � ������� ������������ ������ �� ����
     * @var boolean $bDontTransform �� ��������������� ������
     * @return boolean true ���� ������� ��������� ������ �� ����
     **/
/*
    public function vOutputCache($sHash)
    {
        if (!$this->bCheckCache($sHash)) {
            $this->vOutputDefaultImage();
        }
        $sFileName = $this->sTmpPath . $sHash;
        $sContent = file_get_contents($sFileName);
        try {
            $aResult = unserialize($sContent);
            header('Content-type:' . $aResult['content-type']);
            echo $aResult['content'];
        } catch (Exception $e) {
            $this->vOutputDefaultImage();
        }
        return true;
    }
*/
    /**
     * ���������� ���������� � ������
     **/
    public static function sGetExtensionWithDot($sFileName) {
        return strrchr($sFileName, '.');
    }


    public function get_furl($url) {
        $furl = false;
        // First check response headers
        $headers = get_headers($url);
        // Test for 301 or 302
        if(preg_match('/^HTTP\/\d\.\d\s+(301|302)/',$headers[0])) {
            foreach($headers as $value) {
                if(substr(strtolower($value), 0, 9) == "location:") {
                    $furl = trim(substr($value, 9, strlen($value)));
                }
            }
        }
        // Set final URL
        $furl = ($furl) ? $furl : $url;
        return $furl;
    }
    /**
     * @return true if success
     **/
    public function bSaveRemote($sHash, $sRemoteUrl) {
        if (empty($sRemoteUrl)) return;
        $sRemoteUrl = $this->get_furl($sRemoteUrl);
        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_URL, $sRemoteUrl);
        curl_setopt($oCurl, CURLOPT_USERAGENT, 'sotmarket_wp_imagecache');
        curl_setopt($oCurl, CURLOPT_HEADER, 0);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 4);
        $sContent = curl_exec($oCurl);

        //��������� ��������� ��������
        if (empty($sContent)){
            $sContent = file_get_contents($this->sGetDefaultImagePath());
        }
        $this->vSaveCache($sHash, $sContent, true);
        return true;
    }

    function sGetImagePath($sPostfix) {
        return $this->sTmpPath . $this->sGetRelativePath($sPostfix);
    }

    function sGetDefaultImagePath() {
        return $this->sTmpPath . $this->sDefaultImagePath;
    }
}
