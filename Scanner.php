<?php
require_once 'Recorder.php';
class Scanner
{
    const WISH_LIST = 'wishlist.txt';
    const GRABBER_URL = 'https://www.cam4.com/directoryCams?directoryJson=true&online=true&url=true&username=';
    const GIST = 'https://gist.githubusercontent.com/scanningAnton/cb4a2bac7bc5b570a99c6210ad7e36a7/raw';

    public function run() {
        $this->refreshWishList();
        $wish_list = $this->readWishList();
        if(sizeof($wish_list) > 0) {
            while(true){
                $active_cache = [];
                foreach($wish_list as $username => $line) {
                    $json = file_get_contents(self::GRABBER_URL . $username);
                    $obj = json_decode($json);

                    foreach($obj->users as $elem) {
                        $username = strtolower($elem->username);
                        $url = $elem->hlsPreviewUrl;
                        $tags = $elem->showTags;

                        if($url === '' || $username === '') {
                            echo "$username has no active url.\n";
                            continue;
                        }
                        $active_cache["$username"] = [
                            'url' => $url,
                            'tags' => $tags,
                            'timestamp' => time()
                        ];
                        #exec("php Recorder.php $username $url > /dev/null 2>&1 &");
                        echo("ffmpeg -i $url  video/$username" . '_' . date('Y-m-d_h_i_s').".ts \n");

                    }
                }
                $time_out = rand(30, 120);
                echo "Sleeping for $time_out seconds.\n";
                sleep($time_out);
                #exit();
            }
        }
    }

    /**
     * @return array|false
     */
    protected function readWishList()
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
        }
        return $wish_list;
    }

    protected function refreshWishList()
    {
        $wishlist = file_get_contents(self::GIST);
        if(strlen($wishlist) > 0) {
            file_put_contents(self::WISH_LIST, $wishlist);
        }
    }
}
$recorder = new Scanner();
$recorder->run();