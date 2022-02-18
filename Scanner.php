<?php
require_once 'Recorder.php';
class Scanner
{
    const WISH_LIST = 'wishlist.txt';
    #const GRABBER_URL = 'https://www.cam4.com/directoryCams?directoryJson=true&online=true&url=true&username=';
    const GRABBER_URL = 'https://www.cam4.com/rest/v1.0/profile/';
    const GIST = 'https://gist.githubusercontent.com/scanningAnton/cb4a2bac7bc5b570a99c6210ad7e36a7/raw';

    public function run() {
            while(true){
                $this->refreshWishList();
                $wish_list = $this->readWishList();
                if(sizeof($wish_list) > 0) {
                    $this->checkOnlineOverApi($wish_list);
                    $time_out = rand(60, 300);
                    echo "Sleeping for $time_out seconds.\n";
                    sleep($time_out);
            }
        }
    }

    protected function readWishList() : array
    {
        $wish_list = [];
        if (file_exists(self::WISH_LIST)) {
            $wish_list = file(self::WISH_LIST, FILE_IGNORE_NEW_LINES);
            $fp = @fopen(self::WISH_LIST, 'r');

            if ($fp) {
                $wish_list = explode("\n", fread($fp, filesize(self::WISH_LIST)));
                $wish_list = array_map('strtolower', $wish_list);
            }
            $wish_list = array_flip($wish_list);
            echo sprintf("Found %s entries in wishlist.\n", count($wish_list));
        }
        if($wish_list === false){
            return [];
        }
        return $wish_list;
    }

    protected function refreshWishList() : void
    {
        $new_list = file_get_contents(self::GIST . '?' . time());
        if(strlen($new_list) > 0) {
            file_put_contents(self::WISH_LIST, $new_list);
        }
    }

    /**
     * @param array $wish_list
     */
    public function checkOnlineOverApi(array $wish_list): void
    {
        $active_cache = [];
        foreach ($wish_list as $username => $line) {
            $json = file_get_contents(self::GRABBER_URL . $username . '/streamInfo');
            if(strlen($json) > 0){
                $obj = json_decode($json);

                $url = $obj->cdnURL;
                if( $url === "" ){
                    $url = $obj->edgeURL;
                }

                if ($url === '' || $username === '') {
                    echo "$username has no active url.\n";
                    continue;
                }
                $active_cache["$username"] = [
                    'url' => $url,
                    'timestamp' => time()
                ];
                exec("php Recorder.php $username $url > /dev/null 2>&1 &");
                echo("ffmpeg -i $url  video/$username" . '_' . date('Y-m-d_h_i_s') . ".ts \n");
            }
        }
    }
}
$recorder = new Scanner();
$recorder->run();
