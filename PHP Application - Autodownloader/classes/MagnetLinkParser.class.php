<?
// It'd be nice if I could get this working: https://github.com/danfolkes/Magnet2Torrent
// If it's just one it is name, otherwise it's files
// Just use this when Pirate bay has no File listing for a torrent.... or actually let's use it for all so we can better gauge things

namespace MediaCenter;

class MagnetLinkParser {

    public $torrent = '';
    public $files = array();
    public $torrent_link = '';

    function __construct($magnet_link, $torrent_link=false){
        // GET Torrent from Magnet link using "Magnet 2 Torrent" site 
        if (empty($torrent_link)){
            $postdata = http_build_query(
                array('magnet'=>$magnet_link)
            );
            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                )
            );
            $context  = stream_context_create($opts);
            $result = @file_get_contents("http://magnet2torrent.com/upload/", false, $context);
            if (empty($result)){
                return;
            }
            $this->torrent_link = str_replace("Location: ","",$http_response_header[8]);
            // print_r($http_response_header);
            $content = @file_get_contents($this->torrent_link);
            if (empty($content)){
                return;
            }
            $torrent = @gzinflate( substr($content,10,-8) ); // This has a data error sometimes so we block it?
       
       // Get the Torrent directly from The Pirate Bay
        } else {
            $this->torrent_link = $torrent_link;
            Debug::msg("Fetching Torrent: ".$this->torrent_link);
            $torrent = @file_get_contents($this->torrent_link);
            if (empty($torrent)){
                Debug::msg("Problem fetching Torrent: ".$this->torrent_link);
                return;
            }
        }

        $this->torrent = $torrent;
        if (!empty($this->torrent)){
            Debug::msg("Decoding Torrent: ".$this->torrent_link);
            $this->decode_torrent();
        }
    }

    function decode_torrent(){
        // require_once 'torrent_decoder.class.php';
        $decoder = new torrent_decoder($this->torrent);
        $torrent = $decoder->decode();

        if (!empty($torrent['info']['files'])){
            foreach ($torrent['info']['files'] as $index => $file){
                $this->files[] = array(
                    'size' => $file['length'],
                    'path' => implode("/",$file['path']) // path is an array by dir...
                );
                // if ($size < 1000) continue; // Don't really need this if we're comparing file extension
                // If !is_video() continue;
                // If multiple files
            }
        } else {
            $this->files[] = array(
                'size' => $torrent['info']['length'],
                'path' => $torrent['info']['name']
            );
        }
    }

}

 
class torrent_decoder {
    private $contents = '';
    private $pos = 0;
    
    /**
     * When initiated the raw contents of the .torrent file are held 
     * in class member $contents.
     *
     * @access public
     * @param $file - filename of torrent
     * @return void
     */
    function __construct($file)
    {
        // $this->contents = @file_get_contents($file);
    	$this->contents = $file;
    }
    
    /**
     * Starts the decoding method(s).
     * Throws exception if contents cannot be opened, is empty, or file cannot
     * be found.
     *
     * @access public
     * @param void
     * @return array
     */
    function decode()
    {
        if (empty($this->contents))
        {
            throw new \exception('Torrent file is empty, cannot be opened, or cannot be found.');
            return;
        }
        
        $ret = $this->doChar();
        return $ret;
    }
    
    /**
     * Processes character at internal pointer position to check for an identifier.
     * Possible identifiers are 'd', 'l', 'i', and 0-9
     * Throws exception if an unknown character identifier is found.
     *
     * @access private
     * @param void
     * @return mixed
     */
    private function doChar()
    {    
        while ($this->contents[$this->pos] != 'e')
        {
            if ($this->contents[$this->pos] == 'd')
            {
                return $this->doDict();
            }
            elseif ($this->contents[$this->pos] == 'l')
            {
                return $this->doList();
            }
            elseif ($this->contents[$this->pos] == 'i')
            {
                return $this->doInt();
            }
            else
            {
                if (is_numeric($this->contents[$this->pos]))
                {
                    return $this->doString();
                } else
                {
                    throw new \exception('Unknown character \'' . $this->contents[$this->pos] . '\' at position ' . $this->pos);
                    return;
                }
            }
        }
    }
    
    /**
     * Processes dictionary 'd' identifier.
     *
     * @access private
     * @param void
     * @return array
     */
    private function doDict()
    {
        $ret = array();
        $this->pos++;

        while ($this->contents[$this->pos] != 'e')
        {
            $key = $this->doString();

            if ($this->contents[$this->pos] == 'd')
            {
                $ret[$key] = $this->doDict();
            }
            elseif ($this->contents[$this->pos] == 'l')
            {
                $ret[$key] = $this->doList();
            }
            elseif ($this->contents[$this->pos] == 'i')
            {
                $ret[$key] = $this->doInt();
            } else
            {
                if (is_numeric($this->contents[$this->pos]))
                {
                    $ret[$key] = $this->doString();
                } else
                {
                    throw new \exception('Unknown character \'' . $this->contents[$this->pos] . '\' at position ' . $this->pos);
                    return;
                }
            }
        }
        
        $this->pos++;
        
        return $ret;
    }
    
    /**
     * Processes strings found.
     *
     * @access private
     * @param void
     * @return string
     */
    private function doString()
    {
        $strlen = '';
        
        while (is_numeric($this->contents[$this->pos]))
        {
            $strlen .= $this->contents[$this->pos];
            $this->pos++;
        }
        
        if ($this->contents[$this->pos] == ':')
        {
            $this->pos++;
        }
        
        $strlen = intval($strlen);
        $str = substr($this->contents, $this->pos, $strlen);
        $this->pos = $this->pos + $strlen;
        
        return $str;
    }
    
    /**
     * Processes list 'l' identifiers and returns an array of 
     * items found in the list.
     *
     * @access private
     * @param void
     * @return array
     */
    private function doList()
    {
        $ret = array();
        $this->pos++;
        
        while ($this->contents[$this->pos] != 'e')
        {
            $ret[] = $this->doChar();
        }
        
        $this->pos++;

        return $ret;
    }
    
    /**
     * Processes integer 'i' identifier.
     *
     * @access private
     * @param void
     * @return integer
     */
    private function doInt()
    {
        $this->pos++;
        $int = '';
        
        while ($this->contents[$this->pos] != 'e')
        {
            $int .= $this->contents[$this->pos];
            $this->pos++;
        }
        
        $int = intval($int);
        $this->pos++;
        
        return $int;
    }
}