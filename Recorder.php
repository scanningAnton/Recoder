<?php

class Recorder
{
    const LOCK = 'lock';

    public static function startRecording(string $username, string $url) : void {
        echo $username . ' ' . $url . "\n";
        if( ! self::lockExists($username)) {
            self::createLock($username);
            exec("ffmpeg -i $url  video/$username" . '_' . date('Y-m-d_h_i_s').".ts  2>&1 &");
            self::lockRemove($username);
        }
    }

    protected static function createLock(string $filename) : void {
        if(self::directoryExists()) {
            if( ! self::lockExists($filename)) {
                self::lockCreate($filename);
            }
        }
    }

    protected static function directoryExists() : bool {
        if( ! is_dir(self::LOCK)) {
            mkdir(self::LOCK);
        }
        return true;
    }

    protected static function lockExists(string $username) : bool {
        $path = self::LOCK . '/' . $username;
        if(file_exists($path)) {
           return true;
        }
        return false;
    }

    protected static function lockCreate(string $username) : void {
        $path = self::LOCK . '/' . $username;
        if( ! file_exists($path)) {
            touch($path);
        }
    }

    protected static function lockRemove(string $username) : void {
        $path = self::LOCK . '/' . $username;
        if(file_exists($path)) {
            unlink($path);
        }
    }
}
print_r($argv);
if(count($argv) >= 2){
    Recorder::startRecording($argv[1], $argv[2]);
}