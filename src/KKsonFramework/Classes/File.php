<?php

namespace KKsonFramework\Classes;

use KKsonFramework\Utils\ByteUtils;

class File
{
    private $fp;

    public static function open($filename, $mode)
    {
        $fp = fopen($filename, $mode);

        if($fp === false) {
            throw new \Exception("Failed to open file: $filename");
        }
        return new static($fp);
    }

    public function __construct($fp)
    {
        $this->fp = $fp;
    }

    public function getFilename() {
        $meta_data = stream_get_meta_data($this->fp);
        $filename = $meta_data["uri"];
        return $filename;
    }

    /**
     * @param $length
     * @return string
     * @throws \Exception
     */
    public function read($length) {
        $d = fread($this->fp, $length);
        if($d === false) {
            throw new \Exception("Failed to read $length bytes from file {$this->getFilename()}");
        }
        return $d;
    }

    /**
     * @param $length
     * @return array
     * @throws \Exception
     */
    public function readBytes($length) {
        return ByteUtils::getBytes($this->read($length));
    }

    public function readAll() {
        $content = '';
        while (!feof($this->fp)) {
            $content .= fread($this->fp, 1024);
            $stream_meta_data = stream_get_meta_data($this->fp); //Added line
            if($stream_meta_data['unread_bytes'] <= 0) break; //Added line
        }
        return $content;
    }

    public function readAllBytes() {
        return ByteUtils::getBytes($this->readAll());
    }

    public function close() {
        return fclose($this->fp);
    }

    public function flush() {
        return fflush($this->fp);
    }

    public function write($data, $length = null) {
        return fwrite($this->fp, $data, $length);
    }

    public function seek($offset, $whence = SEEK_SET) {
        return fseek($this->fp, $offset, $whence);
    }

    public function tell() {
        return ftell($this->fp);
    }

}