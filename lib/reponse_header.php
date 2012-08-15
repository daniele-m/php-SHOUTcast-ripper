<?php
  namespace SHOUTcastRipper;

  class ResponseHeader {
    private $content = '';
    private $empty_line_index = null;

    public function write($buffer) {
      $this->content .= $buffer;
    }

    /**
     * Gets the position of the last line of the http header. The last line is "\r\n\r\n".
     */
    private function empty_line_index() {
      if ($this->empty_line_index != null)
        return $this->empty_line_index;
      $index = strpos($this->content, "\r\n\r\n");
      return $index ? $index+4 : null;
    }

    /**
     * Returns true if all the http header has been writed.
     */
    public function is_complete() {
      return !!$this->empty_line_index();
    }

    /**
     * Removes from $content all the bytes after the end of the http header,
     * and returns those bytes.
     */
    public function remove_tail_audio_data() {
      if (!$this->is_complete())
        throw new \Exception("The http header is dirty or incomplete");
      $headers = substr($this->content, 0, $this->empty_line_index());
      $stream = substr($this->content, strlen($headers));
      $this->content = $headers;
      return $stream;
    }

    public function length() {
      return strlen($this->content);
    }

    /**
     * Returns true if there are any bytes after the http header.
     * If true, those bytes are the audio data and they can be getted
     * using the remove_tail_audio_data() method.
     */
    public function contains_audio_data() {
      return $this->is_complete() && $this->empty_line_index()+4 < $this->length();
    }

    public function icy_metaint() {
      if (!$this->is_complete())
        throw new \Exception("The http header is dirty or incomplete");
      $header_name = "icy-metaint";
      if (($end_of_header_name = stripos($this->content, "$header_name:")) === false)
        return null;
      $end_of_header_name += strlen($header_name)+1;
      $end_of_header_value = strpos($this->content, "\r\n", $end_of_header_name);
      return substr($this->content, $end_of_header_name, $end_of_header_value-$end_of_header_name)*1;
    }
  }
?>