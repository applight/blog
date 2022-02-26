<?php

class BlogReader {

    private static $instance;
    private $content;
    
    private function __construct( $contentDir ) {
        self::$instance = $this;
        $this->content = $this->findContent( $contentDir );
        uasort( $this->content, function($a,$b) {
            return filemtime($a) - filemtime($b);
        });
    }

    public static function getInstance( $contentDir = "./content" ) {
        if ( self::$instance != null ) return self::$instance;
        else return new BlogReader( $contentDir );
    }

    private function findContent( $searchDir ) {
        $files = [];
        $dir = opendir($searchDir);

        while( $fileName = readdir($dir) ) {
            if ( $fileName == "." || $fileName == ".." )
                $nop = 'do nothing';
            elseif ( is_dir($fileName) )
                array_merge( $files, $this->findContent($searchDir.'/'.$fileName) );
            else
                array_push( $files, $searchDir.'/'.$fileName );
        }
        closedir($dir);
        return $files;
    }

    private function texPdfName( $fileName ) {
        $pos = strrpos($fileName,"/");
        if ( ! $pos ) $pos = 0;
        $texPos = strrpos($fileName,".tex");
        if ( ! $pos ) $texPos = strlen($fileName);

        return substr($fileName, $pos, $texPos - $pos).".pdf";
    }
    
    private function printFile( $fileName ) {
        $mime =  mime_content_type($fileName);
        if ( ! $mime ) {
            echo "Couldn't get mimetype of " . $fileName . "<br/>";
        }
        switch ( $mime ) {
        case "text/plain":
            echo "<div><h3>". $fileName ."</h3><p>". file_get_contents($fileName) ."</p></div>";
            break;
        case "text/x-tex":
            shell_exec("pdflatex -output-directory=./generatedPdfs -output-format=pdf ".$fileName);
            echo "<div style=\"text-align:center\"><h4>Pdf viewer testing</h4><iframe src=\""
                . "https://docs.google.com/viewer?url="
                . "https://" . $_SERVER['HTTP_HOST'] . "/blog/generatedPdfs"
                . $this->texPdfName( $fileName )
                . "&embedded=true\" frameborder=\"0\" height=\"500px\" width=\"100%\">"
                . "</iframe></div>";
            break;
        default:
            echo mime_content_type($fileName) . "<br/>";
            break;
        }
    }
        
    public function printContent() {
        for ( $i=0; $i < sizeof($this->content); ++$i ) {
            $this->printFile( $this->content[$i] );
        }
    }

}
?>
