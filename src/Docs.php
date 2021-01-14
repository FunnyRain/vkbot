<?php

/**
 * Class Docs
 */
class Docs
{
    /** @var Bot */
    private $bot;
    
    /**
     * Dosc constructor.
     *
     * @param Bot $bot
     */
    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
    }
    
    /**
     * @param string $type
     * @param $peerId
     * @return string
     */
    public function getUploadServer(string $type, $peerId): string
    {
        return $this->bot->api("docs.getMessagesUploadServer", [
            "peer_id" => $peerId,
            "type" => $type
        ])["upload_url"];
    }
    
    /**
     * @param string $path
     * @param string $type
     * @param $peerId
     * @return array|false
     */
    public function getUrlDoc(string $path, string $type, $peerId)
    {
        if (!class_exists('CURLFile', false)) return false;
        if (!file_exists($path)) return false;
        
        $url = $this->getUploadServer($type, $peerId);
        
        $myCurl = curl_init();
        curl_setopt_array($myCurl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: multipart/form-data',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                "file" => new \CURLFile($path),
            ]
        ]);
        $response = json_decode(curl_exec($myCurl), 1);
        
        return $this->bot->api("docs.save", [
                "file" => $response["file"]
            ]
        );
    }
}
