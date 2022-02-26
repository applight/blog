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
        if ( ! $texPos ) $texPos = strlen($fileName);

        return substr($fileName, $pos, $texPos - $pos).".pdf";
    }

    private function webifyTextFile( $fileName ) {
        $brs  = str_replace("\n", "<br/>", file_get_contents($fileName));
        $nbsp = str_replace(" ", "&nbsp", str_replace("\t", "&nbsp&nbsp&nbsp&nbsp", $brs));
        return $nbsp;
    }
    
    private function printFile( $fileName ) {
        $mime =  mime_content_type($fileName);
        if ( ! $mime ) {
            echo "<div><h3>Couldn't get mimetype of " . $fileName . "</h3></div>";
            return;
        }
        
        switch ( $mime ) {
        case "text/plain":
            echo "<div><h3>". $fileName ."</h3><p>". $this->webifyTextFile($fileName) ."</p></div>";
            break;

        case "text/x-tex":
            // call pdflatex and create a PDF version of the tex file
            $shell_output = shell_exec("pdflatex -output-directory=./generatedPdfs -output-format=pdf "
                                       .$fileName
                                       ." > ./logs/shell_exec_pdflatex");
            
            file_put_contents("./logs/pdflatex.log", "file name: ".$fileName."\n".$shell_output."\n\n");

            // no break, falls through to 'text/pdf'
            $fileName = "./generatedPdfs" . $this->texPdfName( $fileName );
        case "application/pdf":
            echo '<div><embed src="https://drive.google.com/viewerng/viewer?embedded=true&url='
                . "https://". $_SERVER['HTTP_HOST'] ."/blog/". $fileName .'" type="application/pdf" width="100%" height="500px"></embed></div>';
            break;
            
            
        default:
            echo "<p>" . mime_content_type($fileName) . "<br/></p>";
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
